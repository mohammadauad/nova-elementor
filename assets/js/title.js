/**
 * NOVA Title Widget - Animation
 * Version: 1.0.0
 */

(function ($) {
	'use strict';

	// Désactiver tous les console.* de ce fichier pour éviter le bruit dans la console.
	// On redéfinit un "console" local silencieux sans impacter window.console.
	var console = {
		log: function () { },
		warn: function () { },
		error: function () { }
	};

	// Logs détaillés : mettre à true pour déboguer (utilise window.console).
	var NOVA_TITLE_DEBUG = true;
	function debugLog() {
		if (!NOVA_TITLE_DEBUG || !window.console || !window.console.log) return;
		window.console.log.apply(window.console, ['[NOVA Title]'].concat(Array.prototype.slice.call(arguments)));
	}
	function debugWarn() {
		if (!NOVA_TITLE_DEBUG || !window.console || !window.console.warn) return;
		window.console.warn.apply(window.console, ['[NOVA Title]'].concat(Array.prototype.slice.call(arguments)));
	}

	const NOVATitle = {
		instances: [],

		/**
		 * Initialize a single title instance
		 */
		initInstance: function ($widget) {
			// Skip if already initialized
			if ($widget.data('nova-title-initialized')) {
				return;
			}

			// Vérifier que le widget existe
			if (!$widget || $widget.length === 0) {
				return;
			}

			$widget.data('nova-title-initialized', true);

			let animationConfig = $widget.data('animation-config') || {};

			// Si animationConfig est une string JSON, la parser
			if (typeof animationConfig === 'string') {
				try {
					// Décode les entités HTML (comme &quot;)
					const decoded = animationConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
					animationConfig = JSON.parse(decoded);
				} catch (e) {
					// Error parsing animation config
					animationConfig = {};
				}
			}

			// Initialize main widget animation (block animation)
			if (animationConfig.enable !== false) {
				this.initWidgetAnimation($widget, animationConfig);
			} else {
				// If animation disabled, show immediately
				$widget.addClass('animated');
			}

			// Initialize decoration icons scroll animation
			this.initTitleIconsScroll($widget);

			// Reveal wrapper so CSS knows JS is ready and takes over animation
			$widget.addClass('nova-js-ready');

			// Initialize styled words animations
			this.initStyledWordsAnimations($widget);
		},

		/**
		 * Icônes décoratives : animation de position au scroll (initial → cible)
		 */
		initTitleIconsScroll: function ($widget) {
			const configRaw = $widget.data('title-icons-config');
			const hasWrapper = !!$widget.find('.nova-title-icons-wrapper').length;
			if (!configRaw || !hasWrapper) {
				return;
			}

			let config = configRaw;
			if (typeof config === 'string') {
				try {
					config = JSON.parse(config.replace(/&quot;/g, '"').replace(/&#39;/g, "'"));
				} catch (e) {
					return;
				}
			}
			if (!Array.isArray(config) || config.length === 0) {
				return;
			}

			const $icons = $widget.find('.nova-title-decoration-icon');
			if ($icons.length !== config.length) {
				return;
			}

			// Détection éditeur : parent = Elementor editor OU le widget utilise les variables CSS (--pos-x) pour la position (PHP ne les met qu’en mode éditeur).
			const inIframe = window.self !== window.top;
			const parentIsEditor = inIframe && !!(window.parent.elementor && window.parent.elementor.config && window.parent.elementor.config.is_edit_mode);
			const firstIconTransform = $icons.length > 0 ? ($icons.eq(0)[0].style.transform || '') : '';
			const styleUsesCssVars = firstIconTransform.indexOf('var(--pos-x') !== -1 || firstIconTransform.indexOf('var(--nova-icon-tx') !== -1;
			const isEditor = parentIsEditor || styleUsesCssVars;

			const applyScrollState = function (iconIndex, progress, logApply) {
				const c = config[iconIndex];
				const $icon = $icons.eq(iconIndex);
				if (!$icon.length || !c) return;

				// Fonction pour obtenir le device actuel basé sur la largeur de la fenêtre
				const getCurrentDevice = function() {
					const width = window.innerWidth;
					// Breakpoints Elementor par défaut
					if (width < 768) {
						return 'mobile';
					} else if (width < 1025) {
						return 'tablet';
					}
					return 'desktop';
				};

				// Obtenir le device actuel
				const device = getCurrentDevice();
				
				// Récupérer les valeurs pour le device actuel (avec fallback sur desktop)
				const deviceConfig = c[device] || c.desktop || c;
				
				// Si la config n'a pas de structure responsive (ancien format), utiliser directement
				const posX = deviceConfig.posX !== undefined ? deviceConfig.posX : (c.posX || 0);
				const posY = deviceConfig.posY !== undefined ? deviceConfig.posY : (c.posY || 0);
				const unitX = deviceConfig.unitX || c.unitX || 'px';
				const unitY = deviceConfig.unitY || c.unitY || 'px';
				const scrollX = deviceConfig.scrollX !== undefined ? deviceConfig.scrollX : (c.scrollX || 0);
				const scrollY = deviceConfig.scrollY !== undefined ? deviceConfig.scrollY : (c.scrollY || 0);
				const scrollUnitX = deviceConfig.scrollUnitX || c.scrollUnitX || unitX;
				const scrollUnitY = deviceConfig.scrollUnitY || c.scrollUnitY || unitY;

				// Lire les CSS vars si disponibles (valeurs live depuis l'éditeur)
				const computedStyle = window.getComputedStyle($icon[0]);
				const cssVarTx = computedStyle.getPropertyValue('--nova-icon-tx').trim();
				const cssVarTy = computedStyle.getPropertyValue('--nova-icon-ty').trim();

				// Parser la valeur CSS var (ex: "20px" → 20, "px")
				const parseCssVal = function(cssVal, fallbackSize, fallbackUnit) {
					if (!cssVal) return { size: fallbackSize, unit: fallbackUnit };
					const match = cssVal.match(/^(-?[\d.]+)(px|%|em|rem|vw|vh)?$/);
					if (match) return { size: parseFloat(match[1]), unit: match[2] || fallbackUnit };
					return { size: fallbackSize, unit: fallbackUnit };
				};

				const initX = parseCssVal(cssVarTx, posX, unitX);
				const initY = parseCssVal(cssVarTy, posY, unitY);

				const x = initX.size + (scrollX - initX.size) * progress;
				const y = initY.size + (scrollY - initY.size) * progress;
				const ux = initX.unit || unitX;
				const uy = initY.unit || unitY;
				const alignH = c.alignH || 'left';
				const alignV = c.alignV || 'top';
				const tx = (alignH === 'center') ? ('calc(-50% + ' + x + ux + ')') : (x + ux);
				const ty = (alignV === 'center') ? ('calc(-50% + ' + y + uy + ')') : (y + uy);
				const transformStr = 'translate(' + tx + ', ' + ty + ')';
				$icon[0].style.transform = transformStr;
			};

			const setTransition = function (iconIndex, duration) {
				const $icon = $icons.eq(iconIndex);
				if ($icon.length && duration > 0) {
					$icon[0].style.transition = 'transform ' + duration + 'ms ease';
				}
			};

			// En éditeur : ne pas écraser le transform (position initiale gérée par --pos-x / --pos-y). En front : appliquer la position initiale depuis la config.
			if (!isEditor) {
				for (let i = 0; i < config.length; i++) {
					setTransition(i, config[i].duration || 800);
					applyScrollState(i, 0);
				}
			}

			// En éditeur : appliquer les transforms responsives en fonction du device actuel
			if (isEditor) {
				// Fonction pour obtenir les settings actuels du widget depuis Elementor
				const getWidgetSettings = function() {
					if (!window.parent || !window.parent.elementor) return null;
					
					try {
						// Trouver le widget ID depuis l'élément
						const widgetElement = $widget.closest('.elementor-element');
						if (!widgetElement.length) return null;
						
						const widgetId = widgetElement.data('id');
						if (!widgetId) return null;
						
						// Obtenir le modèle du widget
						const widgetModel = window.parent.elementor.getPreviewView().getElementModel(widgetId);
						if (!widgetModel) return null;
						
						return widgetModel.get('settings').attributes;
					} catch (e) {
						console.log('[NOVA Title] Could not get widget settings:', e);
						return null;
					}
				};
				
				// Fonction pour appliquer les transforms responsives dans l'éditeur
				const applyEditorResponsiveTransforms = function() {
					// Obtenir le device actuel depuis Elementor
					const getCurrentDevice = function() {
						// En mode éditeur, utiliser l'API Elementor pour obtenir le device preview
						if (window.parent && window.parent.elementor) {
							try {
								const elementorDevice = window.parent.elementor.channels.deviceMode.request('currentMode');
								// Elementor retourne 'desktop', 'tablet', 'mobile', 'tablet_extra', 'mobile_extra', 'laptop', 'widescreen'
								// On normalise vers nos 3 modes
								if (elementorDevice === 'mobile' || elementorDevice === 'mobile_extra') {
									return 'mobile';
								} else if (elementorDevice === 'tablet' || elementorDevice === 'tablet_extra') {
									return 'tablet';
								}
								return 'desktop';
							} catch (e) {
								// Fallback si l'API n'est pas disponible
								console.log('[NOVA Title] Could not get Elementor device mode:', e);
							}
						}
						
						// Fallback: utiliser la largeur de la fenêtre
						const width = window.innerWidth;
						if (width < 768) {
							return 'mobile';
						} else if (width < 1025) {
							return 'tablet';
						}
						return 'desktop';
					};
					
					const device = getCurrentDevice();
					console.log('[NOVA Title] Applying transforms for device:', device);
					
					// Essayer d'obtenir les settings actuels depuis Elementor
					const settings = getWidgetSettings();
					
					for (let i = 0; i < config.length; i++) {
						const c = config[i];
						const $icon = $icons.eq(i);
						if (!$icon.length || !c) continue;
						
						let posX, posY, unitX, unitY;
						
						// Si on a accès aux settings Elementor, lire les valeurs actuelles
						if (settings && settings.title_icons && settings.title_icons[i]) {
							const iconSettings = settings.title_icons[i];
							
							// Dans un repeater Elementor, les valeurs responsive sont stockées avec des suffixes:
							// position_x (desktop), position_x_tablet, position_x_mobile
							const getRepeaterResponsiveValue = function(key, device, defaultVal) {
								const suffix = device === 'desktop' ? '' : '_' + device;
								const control = iconSettings[key + suffix] || iconSettings[key];
								
								if (!control) return defaultVal;
								
								return {
									size: control.size !== undefined && control.size !== '' ? parseFloat(control.size) : defaultVal.size,
									unit: control.unit || defaultVal.unit
								};
							};
							
							const posXData = getRepeaterResponsiveValue('position_x', device, {size: 0, unit: 'px'});
							const posYData = getRepeaterResponsiveValue('position_y', device, {size: 0, unit: 'px'});
							
							posX = posXData.size;
							posY = posYData.size;
							unitX = posXData.unit;
							unitY = posYData.unit;
							
							console.log('[NOVA Title] Icon', i, 'from settings - device:', device, 'key_tablet:', 'position_x_' + device, 'posX:', posX, unitX, 'posY:', posY, unitY);
							console.log('[NOVA Title] raw position_x:', JSON.stringify(iconSettings['position_x']), 'position_x_tablet:', JSON.stringify(iconSettings['position_x_tablet']));
						} else {
							// Fallback: utiliser les valeurs de la config (depuis data attribute)
							const deviceConfig = c[device] || c.desktop || c;
							posX = deviceConfig.posX !== undefined ? deviceConfig.posX : (c.posX || 0);
							posY = deviceConfig.posY !== undefined ? deviceConfig.posY : (c.posY || 0);
							unitX = deviceConfig.unitX || c.unitX || 'px';
							unitY = deviceConfig.unitY || c.unitY || 'px';
							
							console.log('[NOVA Title] Icon', i, 'from config - device:', device, 'posX:', posX, unitX, 'posY:', posY, unitY);
						}
						
						const alignH = c.alignH || 'left';
						const alignV = c.alignV || 'top';
						
						// Calculer le transform
						const tx = (alignH === 'center') ? ('calc(-50% + ' + posX + unitX + ')') : (posX + unitX);
						const ty = (alignV === 'center') ? ('calc(-50% + ' + posY + unitY + ')') : (posY + unitY);
						const transformStr = 'translate(' + tx + ', ' + ty + ')';
						$icon[0].style.transform = transformStr;
						
						// Appliquer width/height responsive depuis les settings
						if (settings && settings.title_icons && settings.title_icons[i]) {
							const iconSettings = settings.title_icons[i];
							const wSuffix = device === 'desktop' ? '' : '_' + device;
							const wData = iconSettings['icon_image_width' + wSuffix] || iconSettings['icon_image_width'];
							const hData = iconSettings['icon_image_height' + wSuffix] || iconSettings['icon_image_height'];
							
							if (wData && wData.size) {
								$icon[0].style.setProperty('--nova-icon-w-current', wData.size + (wData.unit || 'px'));
							}
							if (hData && hData.size) {
								$icon[0].style.setProperty('--nova-icon-h-current', hData.size + (hData.unit || 'px'));
							}
						}
					}
				};
				
				// Appliquer immédiatement
				applyEditorResponsiveTransforms();
				
				// Écouter les changements de device mode dans Elementor
				if (window.parent && window.parent.elementor) {
					try {
						window.parent.elementor.channels.deviceMode.on('change', function() {
							console.log('[NOVA Title] Device mode changed, reapplying transforms');
							setTimeout(function() {
								applyEditorResponsiveTransforms();
							}, 50);
						});
					} catch (e) {
						console.log('[NOVA Title] Could not listen to device mode changes:', e);
					}
				}
				
				// Fallback: écouter les changements de taille de fenêtre
				let resizeTimer;
				const resizeHandler = function() {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(function() {
						applyEditorResponsiveTransforms();
					}, 100);
				};
				window.addEventListener('resize', resizeHandler);
				
				return;
			}

			// Déclencher quand le haut du bloc atteint N px/vh/% du haut de la page (configurable dans le widget).
			if ('IntersectionObserver' in window) {
				// Récupérer la valeur et l'unité depuis les data attributes
				var triggerValue = parseFloat($widget.data('title-icons-trigger-value'));
				var triggerUnit = $widget.data('title-icons-trigger-unit') || 'px';

				// Valider et définir des valeurs par défaut
				if (isNaN(triggerValue) || triggerValue < 0) {
					triggerValue = 82;
					triggerUnit = 'px';
				}

				// Fonction pour calculer la valeur en pixels selon l'unité
				var calculateTriggerPx = function () {
					var triggerTopPx = triggerValue;
					if (triggerUnit === '%' || triggerUnit === 'vh') {
						// Pour % et vh, calculer en fonction de la hauteur de la fenêtre
						triggerTopPx = (triggerValue / 100) * window.innerHeight;
					}
					// Pour 'px', utiliser directement la valeur
					return triggerTopPx;
				};

				// Calculer la position initiale
				var triggerTopPx = calculateTriggerPx();
				var rootBottom = -(window.innerHeight - triggerTopPx);
				var observerOpts = { threshold: 0, rootMargin: '0px 0px ' + rootBottom + 'px 0px' };

				// progress=0 → position initiale (2 premiers réglages). progress=1 → position animation (2 derniers réglages). Bloc EN VUE au trigger → animation (progress=1).
				var observer = new IntersectionObserver(function (entries) {
					entries.forEach(function (entry) {
						const progress = entry.isIntersecting ? 1 : 0;
						const ratio = (entry.intersectionRatio !== undefined) ? entry.intersectionRatio : (entry.isIntersecting ? 1 : 0);
						for (let i = 0; i < config.length; i++) {
							setTransition(i, config[i].duration || 800);
							applyScrollState(i, progress, true);
						}
					});
				}, observerOpts);
				observer.observe($widget[0]);

				// Gérer le redimensionnement de la fenêtre pour les unités % et vh ET pour les changements de device
				var resizeTimer;
				var resizeHandler = function () {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(function () {
						// Recalculer les positions pour le nouveau device
						for (let i = 0; i < config.length; i++) {
							// Obtenir l'état actuel (visible ou non)
							const rect = $widget[0].getBoundingClientRect();
							const isVisible = rect.top < triggerTopPx && rect.bottom > 0;
							const progress = isVisible ? 1 : 0;
							applyScrollState(i, progress, false);
						}
						
						// Recalculer le trigger si nécessaire (pour % et vh)
						if (triggerUnit === '%' || triggerUnit === 'vh') {
							var newTriggerTopPx = calculateTriggerPx();
							if (Math.abs(newTriggerTopPx - triggerTopPx) > 5) {
								triggerTopPx = newTriggerTopPx;
								observer.disconnect();
								var newRootBottom = -(window.innerHeight - triggerTopPx);
								var newObserverOpts = { threshold: 0, rootMargin: '0px 0px ' + newRootBottom + 'px 0px' };
								observer = new IntersectionObserver(function (entries) {
									entries.forEach(function (entry) {
										const progress = entry.isIntersecting ? 1 : 0;
										for (let i = 0; i < config.length; i++) {
											setTransition(i, config[i].duration || 800);
											applyScrollState(i, progress, true);
										}
									});
								}, newObserverOpts);
								observer.observe($widget[0]);
							}
						}
					}, 250);
				};
				window.addEventListener('resize', resizeHandler);
			} else {
				for (let i = 0; i < config.length; i++) {
					applyScrollState(i, 0, false);
				}
			}
		},

		/**
		 * Initialize main widget block animation
		 */
		initWidgetAnimation: function ($widget, config) {
			const self = this;

			// Get translateY from config or use default
			const translateY = config.translateY !== undefined ? parseInt(config.translateY) : 30;

			// Helper function to reset animation state
			const resetAnimation = function () {
				$widget.removeClass('animated');
				$widget.css({
					opacity: '0',
					transform: `translateY(${translateY}px)`,
					filter: 'blur(5px)',
					transition: 'none'
				});
			};

			// Helper function to trigger animation
			const triggerAnimation = function () {
				const delay = parseInt(config.delay) || 0;
				let duration = parseInt(config.duration);
				// If duration is 0 or not set, use default 800ms
				if (!duration || duration <= 0) {
					duration = 800;
				}

				setTimeout(() => {
					// Set transition first
					$widget.css({
						transition: `opacity ${duration}ms ease, transform ${duration}ms ease, filter ${duration}ms ease`
					});

					// Force reflow to ensure transition is applied
					$widget[0].offsetHeight;

					// Remove inline styles explicitly using removeProperty
					if ($widget[0] && $widget[0].style) {
						$widget[0].style.removeProperty('opacity');
						$widget[0].style.removeProperty('transform');
						$widget[0].style.removeProperty('filter');
					}

					// Also remove via jQuery as fallback
					$widget.css({
						opacity: '',
						transform: '',
						filter: ''
					});

					// Add animated class which will trigger the CSS transition
					$widget.addClass('animated');

					// Force another reflow to ensure the class is applied
					$widget[0].offsetHeight;
				}, delay);
			};

			// Set initial state (translateY is applied in resetAnimation)
			resetAnimation();

			// Use Intersection Observer for better performance
			if ('IntersectionObserver' in window) {
				const observer = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							// Trigger animation when entering viewport
							triggerAnimation();
						} else {
							// Reset animation state when leaving viewport
							resetAnimation();
						}
					});
				}, {
					threshold: 0.1,
					rootMargin: '50px',
				});

				observer.observe($widget[0]);
			} else {
				// Fallback for browsers without Intersection Observer
				triggerAnimation();
			}
		},

		/**
		 * Initialize styled words animations
		 */
		initStyledWordsAnimations: function ($widget) {
			const self = this;
			const $styledWords = $widget.find('.nova-styled-word-animated');


			if ($styledWords.length === 0) {
				return;
			}

			// Vérifier si le widget a déjà été animé (pour éviter les animations multiples)
			if ($widget.data('nova-styled-words-animated')) {
				return;
			}

			// Préparer tous les mots animés
			const animatedWords = [];

			$styledWords.each(function () {
				const $word = $(this);
				const trigger = $word.data('animation-trigger') || 'on_scroll_refresh';
				const duration = parseInt($word.data('animation-duration')) || 500;
				// Récupérer les délais spécifiques selon le déclencheur
				const delay = parseInt($word.data('animation-delay')) || 0; // Pour on_load et on_hover
				const delayScroll = parseInt($word.data('animation-delay-scroll')) || 0; // Pour on_scroll
				const delayRefresh = parseInt($word.data('animation-delay-refresh')) || 0; // Pour on_scroll_refresh
				const timing = $word.data('animation-timing') || 'ease';
				const initialStyles = $word.data('initial-styles') || '';
				const finalStyles = $word.data('final-styles') || '';

				const animationProperties = ($word.data('animation-properties') || '').split(',').map(p => p.trim());
				const hasRotation = animationProperties.includes('rotation');


				// Appliquer les styles initiaux si définis (TOUJOURS au chargement, même pour on_scroll)
				if (initialStyles) {
					// Parser les styles initiaux et les appliquer
					const stylePairs = initialStyles.split(';').filter(s => s.trim());
					let transitionApplied = false;

					// Pour transform, retirer d'abord toute valeur existante pour éviter les conflits
					if (hasRotation && $word[0] && $word[0].style) {
						// Retirer toute valeur transform existante du style inline HTML
						$word[0].style.removeProperty('transform');
					}

					stylePairs.forEach(function (stylePair) {
						const colonIndex = stylePair.indexOf(':');
						if (colonIndex > 0) {
							const property = stylePair.substring(0, colonIndex).trim();
							let value = stylePair.substring(colonIndex + 1).trim();

							// Retirer !important si présent
							const hasImportant = value.indexOf('!important') !== -1;
							if (hasImportant) {
								value = value.replace(/!important/gi, '').trim();
							}

							// Appliquer la transition en premier
							if (property === 'transition' && !transitionApplied) {
								if ($word[0] && $word[0].style) {
									$word[0].style.transition = value;
									if (delay > 0) {
										$word[0].style.transitionDelay = delay + 'ms';
									}
									transitionApplied = true;
								}
							} else if (property && value && property !== 'transition') {
								// Utiliser setProperty pour pouvoir appliquer !important si nécessaire
								// Pour transform, TOUJOURS utiliser !important pour forcer l'état initial
								if (hasImportant || property === 'transform') {
									if ($word[0] && $word[0].style) {
										// TOUJOURS utiliser !important pour forcer l'état initial (surtout pour transform)
										$word[0].style.setProperty(property, value, 'important');
										if (property === 'transform') {
											// Vérifier immédiatement après application
											setTimeout(function () {
											}, 10);
										}
									}
								} else {
									$word.css(property, value);
								}
							}
						}
					});

					// Si pas de transition dans les styles initiaux, l'ajouter manuellement
					if (!transitionApplied && duration > 0) {
						const properties = ($word.data('animation-properties') || '').split(',').map(p => p.trim());
						const timing = $word.data('animation-timing') || 'ease';
						const transitionProps = [];


						properties.forEach(function (prop) {
							const propMap = {
								'background_color': 'background-color',
								'text_color': 'color',
								'padding': 'padding',
								'border_radius': 'border-radius',
								'border_width': 'border-width',
								'border_color': 'border-color',
								'box_shadow': 'box-shadow',
								'rotation': 'transform'
							};
							if (propMap[prop.trim()]) {
								transitionProps.push(propMap[prop.trim()]);
							}
						});


						if (transitionProps.length > 0) {
							const transitionValue = transitionProps.join(', ') + ' ' + duration + 'ms ' + timing;
							if ($word[0] && $word[0].style) {
								$word[0].style.transition = transitionValue;
								if (delay > 0) {
									$word[0].style.transitionDelay = delay + 'ms';
								}
							}
						} else {
						}
					} else {
					}
				}

				// Fonction helper pour appliquer les styles finaux
				const applyFinalStyles = function () {

					if (finalStyles) {
						// Parser les styles finaux (peuvent contenir !important)
						// Note: Les styles peuvent être séparés par ';' ou par des espaces si formatés différemment
						const stylePairs = finalStyles.split(';').filter(s => s.trim());

						// Filtrer pour ne garder qu'un seul transform (le dernier)
						const transformPairs = [];
						const otherPairs = [];

						stylePairs.forEach(function (stylePair) {
							const colonIndex = stylePair.indexOf(':');
							if (colonIndex > 0) {
								const property = stylePair.substring(0, colonIndex).trim();
								if (property === 'transform') {
									transformPairs.push(stylePair);
								} else {
									otherPairs.push(stylePair);
								}
							}
						});

						// Si plusieurs transform, ne garder que le dernier
						if (transformPairs.length > 1) {
							transformPairs.splice(0, transformPairs.length - 1);
						}

						// Combiner les autres styles avec le transform (en dernier)
						const finalStylePairs = otherPairs.concat(transformPairs);

						// Utiliser requestAnimationFrame pour s'assurer que la transition est prête
						requestAnimationFrame(function () {
							finalStylePairs.forEach(function (stylePair) {
								const colonIndex = stylePair.indexOf(':');
								if (colonIndex > 0) {
									const property = stylePair.substring(0, colonIndex).trim();
									let value = stylePair.substring(colonIndex + 1).trim();

									// Retirer !important si présent (jQuery.css() ne le supporte pas directement)
									const hasImportant = value.indexOf('!important') !== -1;
									if (hasImportant) {
										value = value.replace(/!important/gi, '').trim();
									}

									if (property && value && property !== 'transition') {
										// TOUJOURS utiliser setProperty avec !important pour les styles finaux lors de l'animation
										if ($word[0] && $word[0].style) {
											// Pour transform, retirer d'abord l'ancienne valeur pour éviter les conflits
											if (property === 'transform') {
												$word[0].style.removeProperty('transform');
											}

											// TOUJOURS forcer !important pour tous les styles finaux appliqués lors de l'animation
											// Même si hasImportant est false, on force !important pour transform
											if (property === 'transform' || hasImportant) {
												$word[0].style.setProperty(property, value, 'important');
											} else {
												$word[0].style.setProperty(property, value);
											}

											if (property === 'transform') {
												// Forcer un reflow pour s'assurer que le style est appliqué
												$word[0].offsetHeight;
											}
										}
									}
								}
							});

							// Vérification finale après requestAnimationFrame
							setTimeout(function () {
							}, 50);
						});

					} else {
						// Si pas de data-final-styles, retirer les styles initiaux pour révéler les styles inline finaux
						if (initialStyles) {
							const stylePairs = initialStyles.split(';').filter(s => s.trim());
							stylePairs.forEach(function (stylePair) {
								const colonIndex = stylePair.indexOf(':');
								if (colonIndex > 0) {
									const property = stylePair.substring(0, colonIndex).trim();
									if (property && property !== 'transition') {
										// Retirer le style inline pour laisser le style final s'appliquer
										$word.css(property, '');
										// Aussi retirer via setProperty pour s'assurer que c'est bien retiré
										if ($word[0] && $word[0].style) {
											$word[0].style.removeProperty(property);
										}
									}
								}
							});
						}
					}
				};

				// Stocker les données pour déclenchement ultérieur avec les délais appropriés
				const wordDelay = trigger === 'on_scroll' ? delayScroll : (trigger === 'on_scroll_refresh' ? delayRefresh : delay);
				animatedWords.push({
					$word: $word,
					trigger: trigger,
					delay: delay,
					delayScroll: delayScroll,
					delayRefresh: delayRefresh,
					wordDelay: wordDelay, // Délai à utiliser selon le déclencheur
					applyFinalStyles: applyFinalStyles,
					initialStyles: initialStyles
				});

				// Gérer selon le déclencheur
				if (trigger === 'on_load') {
					// Animation au chargement
					setTimeout(function () {
						applyFinalStyles();
						$word.addClass('animated nova-animated');
					}, delay);
				} else if (trigger === 'on_hover') {
					// Animation au survol - appliquer les styles finaux au hover
					$word.addClass('nova-hover-ready');
					$word.on('mouseenter', function () {
						applyFinalStyles();
						$(this).addClass('nova-hover-animated');
					});
					$word.on('mouseleave', function () {
						// Optionnel : retourner aux styles initiaux au mouseleave
						if (initialStyles) {
							const stylePairs = initialStyles.split(';').filter(s => s.trim());
							stylePairs.forEach(function (stylePair) {
								const colonIndex = stylePair.indexOf(':');
								if (colonIndex > 0) {
									const property = stylePair.substring(0, colonIndex).trim();
									const value = stylePair.substring(colonIndex + 1).trim();
									if (property && value && property !== 'transition') {
										$word.css(property, value);
									}
								}
							});
						}
						$(this).removeClass('nova-hover-animated');
					});
				}
				// Note: on_scroll sera géré globalement pour tout le widget ci-dessous
			});

			// Gérer les animations on_scroll et on_scroll_refresh au niveau du widget entier
			// Séparer les animations on_scroll et on_scroll_refresh pour utiliser leurs délais respectifs

			const scrollAnimatedWords = animatedWords.filter(function (item) {
				return item.trigger === 'on_scroll';
			});

			const scrollRefreshAnimatedWords = animatedWords.filter(function (item) {
				return item.trigger === 'on_scroll_refresh';
			});

			// Fonction pour déclencher les animations avec leurs délais respectifs
			const triggerScrollAnimations = function (wordsArray, triggerType, isRefresh) {
				const dataKey = 'nova-styled-words-animated-' + triggerType;
				if ($widget.data(dataKey)) {
					return;
				}

				// Marquer le widget comme animé pour ce type de déclencheur
				$widget.data(dataKey, true);

				// Déclencher toutes les animations avec leurs délais respectifs
				wordsArray.forEach(function (wordData) {
					// Utiliser le bon délai selon le type et si c'est un refresh ou un scroll
					let delayToUse = 0;
					if (triggerType === 'on_scroll') {
						// Pour on_scroll, toujours utiliser delayScroll
						delayToUse = wordData.delayScroll || 0;
					} else if (triggerType === 'on_scroll_refresh') {
						// Pour on_scroll_refresh, utiliser delayRefresh si refresh, sinon delayScroll si scroll
						if (isRefresh) {
							delayToUse = wordData.delayRefresh || 0;
						} else {
							delayToUse = wordData.delayScroll || 0;
						}
					} else {
						delayToUse = wordData.delay || 0;
					}

					setTimeout(function () {
						// Forcer un reflow pour s'assurer que la transition fonctionne
						wordData.$word[0].offsetHeight;
						wordData.applyFinalStyles();
						wordData.$word.addClass('nova-scroll-animated');
					}, delayToUse);
				});
			};

			// Fonction pour vérifier si le widget est visible
			const checkIfVisible = function () {
				if ($widget.length === 0 || !$widget[0]) {
					return false;
				}

				const rect = $widget[0].getBoundingClientRect();
				const windowHeight = window.innerHeight || document.documentElement.clientHeight;
				const isVisible = rect.top < windowHeight && rect.bottom > 0;

				return isVisible;
			};

			// Gérer les animations on_scroll_refresh (scroll + refresh si visible)
			if (scrollRefreshAnimatedWords.length > 0) {
				// Vérifier immédiatement si le widget est déjà visible au chargement (refresh)
				const isAlreadyVisible = checkIfVisible();
				if (isAlreadyVisible) {
					triggerScrollAnimations(scrollRefreshAnimatedWords, 'on_scroll_refresh', true); // true = refresh
				} else {
					// Utiliser Intersection Observer pour détecter quand le widget devient visible (scroll)
					if ('IntersectionObserver' in window) {
						const observerRefresh = new IntersectionObserver(function (entries) {
							entries.forEach(function (entry) {
								if (entry.isIntersecting && !$widget.data('nova-styled-words-animated-on_scroll_refresh')) {
									triggerScrollAnimations(scrollRefreshAnimatedWords, 'on_scroll_refresh', false); // false = scroll
									observerRefresh.unobserve(entry.target);
								}
							});
						}, {
							threshold: 0.1,
							rootMargin: '0px',
						});

						observerRefresh.observe($widget[0]);
					} else {
						// Fallback pour les navigateurs sans Intersection Observer
						// Utiliser addEventListener natif avec passive: true pour éviter les violations
						const scrollHandlerRefresh = function () {
							if (!$widget.data('nova-styled-words-animated-on_scroll_refresh') && checkIfVisible()) {
								triggerScrollAnimations(scrollRefreshAnimatedWords, 'on_scroll_refresh', false); // false = scroll
								window.removeEventListener('scroll', scrollHandlerRefresh, { passive: true });
							}
						};
						window.addEventListener('scroll', scrollHandlerRefresh, { passive: true });

						// Vérifier immédiatement au cas où (refresh)
						if (checkIfVisible()) {
							triggerScrollAnimations(scrollRefreshAnimatedWords, 'on_scroll_refresh', true); // true = refresh
						}
					}
				}
			}

			// Gérer les animations on_scroll (seulement au scroll, pas au refresh)
			if (scrollAnimatedWords.length > 0) {
				// Utiliser Intersection Observer pour détecter quand le widget devient visible
				if ('IntersectionObserver' in window) {
					const observerScroll = new IntersectionObserver(function (entries) {
						entries.forEach(function (entry) {
							if (entry.isIntersecting && !$widget.data('nova-styled-words-animated-on_scroll')) {
								triggerScrollAnimations(scrollAnimatedWords, 'on_scroll', false); // false = scroll
								observerScroll.unobserve(entry.target);
							}
						});
					}, {
						threshold: 0.1,
						rootMargin: '0px',
					});

					observerScroll.observe($widget[0]);
				} else {
					// Fallback pour les navigateurs sans Intersection Observer
					// Utiliser addEventListener natif avec passive: true pour éviter les violations
					const scrollHandlerScroll = function () {
						if (!$widget.data('nova-styled-words-animated-on_scroll') && checkIfVisible()) {
							triggerScrollAnimations(scrollAnimatedWords, 'on_scroll', false); // false = scroll
							window.removeEventListener('scroll', scrollHandlerScroll, { passive: true });
						}
					};
					window.addEventListener('scroll', scrollHandlerScroll, { passive: true });
				}
			}
		},

		/**
		 * Initialize all title instances
		 */
		initInstances: function () {
			const self = this;
			$('.nova-title-widget').each(function (index, element) {
				self.initInstance($(element));
			});
		},

		/**
		 * Reinitialize (for Elementor editor)
		 */
		reinit: function () {
			$('.nova-title-widget').each((index, element) => {
				const $widget = $(element);
				$widget.data('nova-title-initialized', false);
			});
			this.initInstances();
		},
	};

	/**
	 * Initialize - Multiple strategies for maximum compatibility
	 */

	// Strategy 1: Elementor frontend hook (preferred for Elementor widgets)
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/nova-title.default',
				function ($scope) {
					const $widget = $scope.find('.nova-title-widget');
					if ($widget.length && !$widget.data('nova-title-initialized')) {
						NOVATitle.initInstance($widget);
					}
				}
			);
		}
	});

	// Strategy 2: If Elementor frontend is already loaded (register immediately)
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/nova-title.default',
			function ($scope) {
				const $widget = $scope.find('.nova-title-widget');
				if ($widget.length && !$widget.data('nova-title-initialized')) {
					NOVATitle.initInstance($widget);
				}
			}
		);

		// Also try to initialize existing widgets
		setTimeout(function () {
			$('.nova-title-widget').each(function () {
				const $widget = $(this);
				if (!$widget.data('nova-title-initialized')) {
					NOVATitle.initInstance($widget);
				}
			});
		}, 100);
	}

	// Strategy 3: DOM ready fallback (for non-Elementor pages or static HTML)
	$(document).ready(function () {
		// Wait a bit for Elementor to initialize first
		setTimeout(function () {
			const widgets = $('.nova-title-widget');
			if (widgets.length > 0) {
				widgets.each(function () {
					const $widget = $(this);
					if (!$widget.data('nova-title-initialized')) {
						NOVATitle.initInstance($widget);
					}
				});
			}
		}, 1500);
	});

	// Strategy 4: Window load fallback (last resort)
	$(window).on('load', function () {
		setTimeout(function () {
			const widgets = $('.nova-title-widget');
			if (widgets.length > 0) {
				widgets.each(function () {
					const $widget = $(this);
					if (!$widget.data('nova-title-initialized')) {
						NOVATitle.initInstance($widget);
					}
				});
			}
		}, 500);
	});

	/**
	 * Expose globally
	 */
	window.NOVATitle = NOVATitle;

	// Debug helper: Force initialization manually
	window.NOVATitleForceInit = function () {
		$('.nova-title-widget').each(function () {
			const $widget = $(this);
			$widget.data('nova-title-initialized', false);
			NOVATitle.initInstance($widget);
		});
	};

})(jQuery);

