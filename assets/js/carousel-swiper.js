/**
 * NOVA Carousel (Swiper) Widget
 * Implémentation Swiper.js basée sur la configuration du widget NOVA Carousel.
 */

(function ($) {
	'use strict';

	const DEBUG = true;
	const EDITOR_ACTIVE = (function () {
		try {
			const doc = document.documentElement || document.body;
			const hasDocClass = !!(doc && doc.classList && (doc.classList.contains('elementor-editor-active') || doc.classList.contains('elementor-edit-mode')));
			const hasBodyClass = !!(document.body && document.body.classList && (document.body.classList.contains('elementor-editor-active') || document.body.classList.contains('elementor-edit-mode')));
			let hasParentClass = false;
			try {
				if (window.self !== window.top && window.top && window.top.document && window.top.document.body) {
					const c = window.top.document.body.classList;
					hasParentClass = !!(c && (c.contains('elementor-editor-active') || c.contains('elementor-edit-mode')));
				}
			} catch (e) {}
			return !!(hasDocClass || hasBodyClass || hasParentClass);
		} catch (e) {}
		return false;
	})();
	const DEBUG_ENABLED = (function () {
		try {
			if (window.NOVA_SWIPER_DEBUG === true) return true;
			if (typeof localStorage !== 'undefined' && localStorage.getItem('NOVA_SWIPER_DEBUG') === '1') return true;
			if (EDITOR_ACTIVE) return true;

			const trySearch = function (search) {
				if (!search) return false;
				const params = new URLSearchParams(search);
				return params.get('nova_swiper_debug') === '1';
			};

			try {
				if (trySearch(window.location && window.location.search)) return true;
			} catch (e) {}
			try {
				if (window.self !== window.top && window.top && trySearch(window.top.location && window.top.location.search)) return true;
			} catch (e) {}
			try {
				if (window.parent && trySearch(window.parent.location && window.parent.location.search)) return true;
			} catch (e) {}
		} catch (e) {}
		return false;
	})();

	const log = function () {
		if (DEBUG && DEBUG_ENABLED) console.log('[NOVA Swiper]', ...arguments);
	};
	const warn = function () {
		if (DEBUG && DEBUG_ENABLED) console.warn('[NOVA Swiper]', ...arguments);
	};

	if (DEBUG && DEBUG_ENABLED) {
		try {
			console.log('[NOVA Swiper] debug ON', {
				src: 'carousel-swiper.js',
				search: window.location && window.location.search ? window.location.search : '',
			});
		} catch (e) {}
	}

	const NovaCarouselSwiper = {
		instances: [],

		isElementorEditor: function () {
			const resultFrontend = typeof elementorFrontend !== 'undefined'
				&& typeof elementorFrontend.isEditMode === 'function'
				&& elementorFrontend.isEditMode();
			const doc = document.documentElement || document.body;
			const hasDocClass = !!(doc && doc.classList && (doc.classList.contains('elementor-editor-active') || doc.classList.contains('elementor-edit-mode')));
			const hasBodyClass = !!(document.body && document.body.classList && (document.body.classList.contains('elementor-editor-active') || document.body.classList.contains('elementor-edit-mode')));
			let hasParentClass = false;
			try {
				if (window.self !== window.top && window.top.document && window.top.document.body) {
					const c = window.top.document.body.classList;
					hasParentClass = !!(c && (c.contains('elementor-editor-active') || c.contains('elementor-edit-mode')));
				}
			} catch (e) { /* cross-origin */ }
			const isEditor = !!(resultFrontend || hasDocClass || hasBodyClass || hasParentClass);
			return isEditor;
		},

		/**
		 * Initialisation globale
		 */
		init: function () {
			const self = this;

			log('Script chargé. isEditor:', self.isElementorEditor(), '| Swiper disponible:', typeof Swiper !== 'undefined', '| elementorFrontend:', typeof elementorFrontend !== 'undefined');

			const registerHook = function () {
				if (typeof elementorFrontend === 'undefined' || !elementorFrontend.hooks) {
					log('elementorFrontend pas encore prêt, retry...');
					setTimeout(registerHook, 100);
					return;
				}

				log('elementorFrontend disponible — enregistrement du hook frontend/element_ready');

				elementorFrontend.hooks.addAction('frontend/element_ready/nova-carousel-swiper.default', function ($scope) {
					log('Hook frontend/element_ready déclenché, scope:', $scope);
					const $widget = $scope.find('.nova-carousel-widget');
					log('Widget trouvé dans scope:', $widget.length, $widget);
					if ($widget.length) {
						self.initInstance($widget);
					} else {
						warn('Aucun .nova-carousel-widget trouvé dans le scope');
					}
				});
			};

			// Tenter immédiatement, puis via les hooks Elementor
			registerHook();

			// Fallback : quand elementorFrontend est initialisé
			$(window).on('elementor/frontend/init', function () {
				log('elementor/frontend/init event fired');
				registerHook();
			});

			// Fallback DOM ready — scanner les widgets déjà présents
			$(document).ready(function () {
				const $widgets = $('.elementor-widget-nova-carousel-swiper .nova-carousel-widget');
				log('DOM ready — widgets trouvés:', $widgets.length);
				$widgets.each(function () {
					self.initInstance($(this));
				});

				// Retry après un délai pour les widgets rendus après le DOM ready
				setTimeout(function () {
					const $w = $('.elementor-widget-nova-carousel-swiper .nova-carousel-widget');
					log('DOM ready +500ms — widgets trouvés:', $w.length);
					$w.each(function () {
						self.initInstance($(this));
					});
				}, 500);

				setTimeout(function () {
					const $w = $('.elementor-widget-nova-carousel-swiper .nova-carousel-widget');
					log('DOM ready +1500ms — widgets trouvés:', $w.length);
					$w.each(function () {
						self.initInstance($(this));
					});
				}, 1500);
			});
		},

		/**
		 * Charger Swiper depuis Elementor/CDN si nécessaire
		 */
		ensureSwiper: function (callback) {
			if (typeof Swiper !== 'undefined') {
				log('Swiper déjà disponible');
				callback();
				return;
			}

			log('Swiper non disponible, polling...');
			let attempts = 0;
			const maxAttempts = 50; // 5s max
			const interval = setInterval(() => {
				attempts++;
				if (typeof Swiper !== 'undefined') {
					log('Swiper trouvé après', attempts, 'tentatives');
					clearInterval(interval);
					callback();
				} else if (attempts >= maxAttempts) {
					clearInterval(interval);
					warn('Swiper introuvable après 5s — chargement CDN fallback');

					// Fallback : charger Swiper depuis CDN
					if (window.NovaCarouselSwiperLoading) {
						return;
					}
					window.NovaCarouselSwiperLoading = true;

					// CSS
					if (!document.querySelector('link[href*="swiper-bundle.min.css"]')) {
						const cssLink = document.createElement('link');
						cssLink.rel = 'stylesheet';
						cssLink.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
						document.head.appendChild(cssLink);
					}

					// JS
					const jsScript = document.createElement('script');
					jsScript.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
					jsScript.onload = () => {
						window.NovaCarouselSwiperLoading = false;
						log('Swiper chargé depuis CDN');
						if (typeof Swiper !== 'undefined') {
							callback();
						}
					};
					jsScript.onerror = () => {
						window.NovaCarouselSwiperLoading = false;
						warn('Échec chargement Swiper CDN');
					};
					document.head.appendChild(jsScript);
				}
			}, 100);
		},

		/**
		 * Initialiser une instance
		 */
		initInstance: function ($widget) {
			const self = this;

			if (!$widget || !$widget.length) {
				warn('initInstance: $widget vide');
				return;
			}

			if ($widget.data('nova-carousel-swiper-bound')) {
				self.applyDisplayMode($widget);
				return;
			}

			const $slider = $widget.find('.nova-carousel-slider');
			if ($slider.length === 0) {
				warn('initInstance: .nova-carousel-slider introuvable dans', $widget);
				return;
			}

			// Récupérer la config JSON déjà générée par le widget Carousel
			const configData = $widget.data('slider-config');
			if (!configData) {
				warn('initInstance: data-slider-config absent sur', $widget);
				return;
			}

			let config = {};
			if (typeof configData === 'string') {
				try {
					config = JSON.parse(configData);
					log('Config parsée depuis string JSON');
				} catch (e) {
					warn('initInstance: erreur parse JSON config', e);
					return;
				}
			} else {
				config = configData;
				log('Config déjà objet JS');
			}

			$widget.data('nova-carousel-swiper-bound', true);
			$widget.data('nova-carousel-swiper-config', config);

			// ── Hover media swap (vidéo) ───────────────────────────────────
			this.initHoverMediaSwap($widget);

			// ── Cursor tooltip (texte qui suit le curseur) ─────────────────
			this.initCursorTooltip($widget);

			// ── Popup au clic ──────────────────────────────────────────────
			this.initPopup($widget);

			const widgetId = $widget.data('widget-id') || $widget.attr('data-widget-id') || '';
			let resizeTimer;
			$(window).on('resize.nova-carousel-swiper-' + widgetId, function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					self.applyDisplayMode($widget);
				}, 150);
			});

			self.applyDisplayMode($widget);
		},

		parseJsonMaybe: function (data) {
			if (!data) return null;
			if (typeof data === 'string') {
				try {
					return JSON.parse(data);
				} catch (e) {
					return null;
				}
			}
			return data;
		},

		getViewportMode: function ($widget) {
			const cfg = this.parseJsonMaybe($widget.data('display-mode-config')) || {};
			const body = document.body;
			if (body && body.classList) {
				if (body.classList.contains('elementor-device-mobile')) return cfg.mobile || cfg.tablet || cfg.desktop || 'slider';
				if (body.classList.contains('elementor-device-tablet')) return cfg.tablet || cfg.desktop || 'slider';
				if (body.classList.contains('elementor-device-desktop')) return cfg.desktop || 'slider';
			}

			const width = window.innerWidth || document.documentElement.clientWidth || 0;
			if (width <= 767) return cfg.mobile || cfg.tablet || cfg.desktop || 'slider';
			if (width <= 1024) return cfg.tablet || cfg.desktop || 'slider';
			return cfg.desktop || 'slider';
		},

		clearGridInlineStyles: function ($slider) {
			if (!$slider || !$slider.length || !$slider[0] || !$slider[0].style) return;
			$slider[0].style.removeProperty('display');
			$slider[0].style.removeProperty('grid-template-columns');
			$slider[0].style.removeProperty('justify-content');
			$slider[0].style.removeProperty('gap');
			$slider[0].style.removeProperty('--grid-cols-current');
		},

		applyDisplayMode: function ($widget) {
			const self = this;
			if (!$widget || !$widget.length) return;

			const $slider = $widget.find('.nova-carousel-slider');
			if ($slider.length === 0) return;

			const desiredMode = self.getViewportMode($widget);
			const currentMode = $widget.data('nova-carousel-display-mode') || '';

			if (desiredMode === currentMode) {
				if (desiredMode === 'grid') {
					const hasSwiperDom = $slider.hasClass('swiper') || $slider.find('.swiper-wrapper').length > 0;
					const hasSwiperInst = !!($slider[0] && $slider[0].swiper && !$slider[0].swiper.destroyed);
					if (hasSwiperDom || hasSwiperInst) {
						self.destroySwiperInstance($widget, $slider);
					}
					self.clearGridInlineStyles($slider);
				}
				return;
			}

			if (desiredMode === 'grid') {
				self.destroySwiperInstance($widget, $slider);
				self.clearGridInlineStyles($slider);
				$widget.data('nova-carousel-display-mode', 'grid');
				return;
			}

			const config = self.parseJsonMaybe($widget.data('nova-carousel-swiper-config')) || self.parseJsonMaybe($widget.data('slider-config')) || {};
			self.ensureSwiper(function () {
				self.clearGridInlineStyles($slider);
				self.buildSwiper($widget, $slider, config);
				$widget.data('nova-carousel-display-mode', 'slider');
			});
		},

		destroySwiperInstance: function ($widget, $slider) {
			try {
				const inst = $slider[0] && $slider[0].swiper ? $slider[0].swiper : null;
				if (inst && !inst.destroyed) {
					inst.destroy(true, true);
				}
			} catch (e) {}

			$slider.find('.swiper-slide-duplicate').remove();

			const $wrapper = $slider.children('.swiper-wrapper').length
				? $slider.children('.swiper-wrapper')
				: $slider.find('> .swiper > .swiper-wrapper').first();

			if ($wrapper && $wrapper.length) {
				const $children = $wrapper.children().removeClass('swiper-slide');
				$slider.empty();
				$children.each(function () {
					$slider.append(this);
				});
			} else {
				const $nestedWrapper = $slider.find('.swiper-wrapper').first();
				if ($nestedWrapper.length) {
					const $children = $nestedWrapper.children().removeClass('swiper-slide');
					$slider.empty();
					$children.each(function () {
						$slider.append(this);
					});
				}
			}

			$slider.find('.nova-carousel-item')
				.removeClass('swiper-slide swiper-slide-active swiper-slide-next swiper-slide-prev swiper-slide-visible')
				.removeAttr('data-swiper-slide-index');

			$slider.find('.nova-carousel-item').each(function () {
				if (!this || !this.style) return;
				this.style.removeProperty('width');
				this.style.removeProperty('margin-right');
				this.style.removeProperty('height');
				this.style.removeProperty('transform');
				this.style.removeProperty('transition-duration');
				this.style.removeProperty('transition-property');
				this.style.removeProperty('flex-shrink');
				this.style.removeProperty('order');
			});

			$slider.find('.swiper-pagination, .swiper-scrollbar').remove();
			$slider.find('.swiper').removeClass('swiper swiper-initialized swiper-horizontal swiper-vertical swiper-backface-hidden');
			$slider.removeClass('swiper swiper-initialized swiper-horizontal swiper-vertical swiper-backface-hidden');
			if ($slider[0] && $slider[0].swiper) {
				try { delete $slider[0].swiper; } catch (e) { $slider[0].swiper = undefined; }
			}
			$widget.removeData('nova-carousel-swiper-initialized');
		},

		/**
		 * Popup au clic sur les slides avec item_popup_enable = yes.
		 */
		initPopup: function ($widget) {
			const widgetId = $widget.data('widget-id') || '';
			if (!widgetId) return;

			const $overlay = $('#nova-popup-overlay-' + widgetId);
			if (!$overlay.length) return;

			const $box = $overlay.find('.nova-popup-box');

			// ── Ouvrir le popup ────────────────────────────────────────────
			$widget.on('click', '.nova-carousel-item[data-popup-index]', function (e) {
				// Ne pas ouvrir si clic sur un lien interne
				if ($(e.target).closest('a').length && !$(e.target).closest('.nova-popup-btn').length) return;

				const idx = $(this).data('popup-index');
				if (idx === undefined) return;

				// Afficher le bon contenu
				$overlay.find('.nova-popup-content').hide();
				const $content = $overlay.find('.nova-popup-content[data-popup-index="' + idx + '"]');
				$content.show();

				// Ouvrir
				$overlay.attr('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';

				// Forcer reflow puis animer
				$overlay[0].offsetHeight; // eslint-disable-line no-unused-expressions
				$overlay.addClass('nova-popup-open');

				// Relancer la vidéo si autoplay activé (ne fonctionne qu'au 1er chargement sinon)
				const $video = $content.find('video.nova-popup-video');
				if ($video.length) {
					const video = $video[0];
					video.currentTime = 0;
					video.play().catch(function () {
						// Autoplay bloqué par le navigateur (politique autoplay) — silencieux
					});
				}
			});

			// ── Fermer : bouton close ──────────────────────────────────────
			$overlay.on('click', '.nova-popup-close', function () {
				closePopup();
			});

			// ── Fermer : clic sur l'overlay (hors box) ────────────────────
			$overlay.on('click', function (e) {
				if (!$(e.target).closest('.nova-popup-box').length) {
					closePopup();
				}
			});

			// ── Fermer : touche Escape ─────────────────────────────────────
			$(document).on('keydown.nova-popup-' + widgetId, function (e) {
				if (e.key === 'Escape' && $overlay.hasClass('nova-popup-open')) {
					closePopup();
				}
			});

			function closePopup() {
				$overlay.removeClass('nova-popup-open');
				$overlay.attr('aria-hidden', 'true');
				document.body.style.overflow = '';

				// Pause toutes les vidéos du popup à la fermeture
				$overlay.find('video.nova-popup-video').each(function () {
					this.pause();
				});
			}

			// ── Bouton mute custom (vidéo hébergée) ───────────────────────
			// Délégation sur document pour éviter les problèmes de scope overlay
			$(document).off('click.nova-mute-' + widgetId).on('click.nova-mute-' + widgetId, '.nova-popup-mute-btn', function (e) {
				e.preventDefault();
				e.stopPropagation();

				const $container = $(this).closest('.nova-popup-video-container');
				const $video = $container.find('video.nova-popup-video');
				if (!$video.length) return;

				const video = $video[0];
				video.muted = !video.muted;
				$container.attr('data-muted', video.muted ? '1' : '0');
			});
		},

		/**
		 * Gérer le play/pause des vidéos hover sur les slides.
		 * Le swap visuel est géré par CSS (:hover), mais les vidéos
		 * doivent être démarrées/arrêtées manuellement.
		 */
		initHoverMediaSwap: function ($widget) {
			$widget.on('mouseenter', '.nova-carousel-item-image.has-hover-media', function () {
				const $hoverVideo = $(this).find('video.nova-carousel-media--hover');
				if ($hoverVideo.length) {
					const video = $hoverVideo[0];
					video.currentTime = 0;
					video.play().catch(() => { }); // ignore autoplay policy errors
				}
				// Pause la vidéo initiale si présente
				const $initVideo = $(this).find('video.nova-carousel-media--initial');
				if ($initVideo.length) {
					$initVideo[0].pause();
				}
			});

			$widget.on('mouseleave', '.nova-carousel-item-image.has-hover-media', function () {
				const $hoverVideo = $(this).find('video.nova-carousel-media--hover');
				if ($hoverVideo.length) {
					$hoverVideo[0].pause();
					$hoverVideo[0].currentTime = 0;
				}
				// Reprendre la vidéo initiale si présente
				const $initVideo = $(this).find('video.nova-carousel-media--initial');
				if ($initVideo.length) {
					$initVideo[0].play().catch(() => { });
				}
			});
		},

		/**
		 * Tooltip qui suit le curseur pour les items avec hover text.
		 * Un seul élément tooltip est créé dans le body et réutilisé.
		 * La classe nova-tooltip-{widgetId} permet à Elementor de cibler
		 * le tooltip avec ses selectors CSS générés.
		 */
		initCursorTooltip: function ($widget) {
			const widgetId = $widget.data('widget-id') || '';

			if (!$('#nova-cursor-tooltip').length) {
				$('<div>', { id: 'nova-cursor-tooltip' })
					.css({
						position: 'fixed',
						pointerEvents: 'none',
						zIndex: 99999,
						opacity: 0,
						textAlign: 'center',
						willChange: 'transform',
						// Forcer le rendu GPU sur pixel entier
						transform: 'translate3d(0px, 0px, 0) rotate(0deg) scale(0.85)',
					})
					.appendTo('body');
			}

			const $tooltip = $('#nova-cursor-tooltip');
			const tooltip = $tooltip[0];
			let rafId = null;
			let isVisible = false;

			// Position cible (curseur)
			let targetX = 0, targetY = 0;
			// Position courante (suit avec retard)
			let currentX = 0, currentY = 0;
			// Vitesse pour la rotation
			let velX = 0, velY = 0;
			let prevTargetX = 0, prevTargetY = 0;

			const configData = $widget.data('slider-config');
			const cfg = configData
				? (typeof configData === 'string' ? JSON.parse(configData) : configData)
				: {};
			const offsetX = parseFloat(cfg.hoverTooltipOffsetX) || 16;
			const offsetY = parseFloat(cfg.hoverTooltipOffsetY) || -8;

			// ── Paramètres de l'animation "fleur dans le vent" ────────────
			const LERP = 0.10;  // vitesse de rattrapage (0.05 = très lent, 0.2 = rapide)
			const ROT_FACTOR = 0.04;  // amplitude de la rotation selon la vitesse
			const MAX_ROT = 12;    // rotation max en degrés
			const SCALE_SHOW = 1;
			const SCALE_HIDE = 0.75;

			// ── Boucle d'animation ─────────────────────────────────────────
			const animate = function () {
				if (!isVisible) return;

				// Lerp : position courante → cible
				currentX += (targetX - currentX) * LERP;
				currentY += (targetY - currentY) * LERP;

				// Vitesse = différence entre cible et position courante
				velX = targetX - currentX;
				velY = targetY - currentY;

				// Rotation légère selon la direction horizontale du mouvement
				const rot = Math.max(-MAX_ROT, Math.min(MAX_ROT, velX * ROT_FACTOR));

				// Arrondir à 2 décimales pour éviter les micro-jitters
				const x = Math.round(currentX * 100) / 100;
				const y = Math.round(currentY * 100) / 100;
				const r = Math.round(rot * 100) / 100;

				tooltip.style.transform = `translate3d(${x}px, ${y}px, 0) rotate(${r}deg) scale(${SCALE_SHOW})`;

				rafId = requestAnimationFrame(animate);
			};

			// ── Mise à jour de la cible au mousemove ───────────────────────
			$(document).on('mousemove.nova-tooltip', function (e) {
				if (!isVisible) return;

				const h = tooltip.offsetHeight || 0;

				// Cible = à droite du curseur, centré verticalement
				targetX = e.clientX + offsetX;
				targetY = e.clientY - h / 2 + offsetY;
			});

			// ── Mouseenter ─────────────────────────────────────────────────
			$widget.on('mouseenter', '.nova-carousel-item-image.has-hover-text', function (e) {
				const $hoverText = $(this).find('.nova-carousel-hover-text');
				if (!$hoverText.length) return;

				const html = $hoverText.find('.nova-carousel-hover-text__inner').html();

				$tooltip
					.removeClass(function (i, cls) {
						return (cls.match(/nova-tooltip-\S+/g) || []).join(' ');
					})
					.addClass(widgetId ? 'nova-tooltip-' + widgetId : '')
					.html(html);

				// Initialiser la position courante au curseur (pas d'animation depuis (0,0))
				const h = tooltip.offsetHeight || 0;
				currentX = e.clientX + offsetX;
				currentY = e.clientY - h / 2 + offsetY;
				targetX = currentX;
				targetY = currentY;

				tooltip.style.transform = `translate3d(${Math.round(currentX)}px, ${Math.round(currentY)}px, 0) rotate(0deg) scale(${SCALE_SHOW})`;

				isVisible = true;
				$tooltip.css({ opacity: 1 });

				cancelAnimationFrame(rafId);
				rafId = requestAnimationFrame(animate);
			});

			// ── Mouseleave ─────────────────────────────────────────────────
			$widget.on('mouseleave', '.nova-carousel-item-image.has-hover-text', function () {
				isVisible = false;
				cancelAnimationFrame(rafId);
				$tooltip.css({ opacity: 0 });
				// Reset scale via transform
				tooltip.style.transform = `translate3d(${Math.round(currentX)}px, ${Math.round(currentY)}px, 0) rotate(0deg) scale(${SCALE_HIDE})`;
			});
		},

		/**
		 * Construire et initialiser Swiper sur le slider
		 */
		buildSwiper: function ($widget, $slider, config) {
			log('buildSwiper appelé. Swiper disponible:', typeof Swiper !== 'undefined', '| widget-id:', $widget.data('widget-id'));

			if (typeof Swiper === 'undefined') {
				warn('buildSwiper: Swiper undefined, abandon');
				return;
			}

			const desiredMode = this.getViewportMode($widget);
			if (desiredMode === 'grid') {
				this.destroySwiperInstance($widget, $slider);
				$widget.data('nova-carousel-display-mode', 'grid');
				return;
			}

			if ($slider[0] && $slider[0].swiper && !$slider[0].swiper.destroyed) {
				return;
			}
			$widget.data('nova-carousel-swiper-initialized', true);

			/**
			 * 1) Nettoyage complet de toute structure Owl Carousel éventuelle
			 * (classes, wrappers, styles inline) pour partir d'un DOM propre.
			 */
			if (
				$slider.hasClass('owl-carousel') ||
				$slider.find('.owl-stage-outer').length ||
				$slider.find('.owl-stage').length
			) {

				// Supprimer les classes Owl sur le slider
				$slider
					.removeClass('owl-carousel owl-loaded owl-drag')
					.removeAttr('style');

				// Dérouler les wrappers Owl tout en conservant le contenu
				$slider.find('.owl-stage-outer').children().unwrap();
				$slider.find('.owl-stage').children().unwrap();

				// Retirer les wrappers .owl-item tout en gardant les slides internes
				$slider.find('.owl-item').each(function () {
					$(this).children().unwrap();
				});

			}

			/**
			 * 2) Recréer systématiquement la structure Swiper
			 *    - on ne se fie PAS aux anciennes classes .swiper-initialized
			 *      ni à d'anciens wrappers potentiels.
			 */
			let $items = $slider.find('.nova-carousel-item');
			if ($items.length === 0) {
				return;
			}

			// Nettoyer d'éventuelles anciennes classes Swiper sur les items
			$items.removeClass('swiper-slide');

			const $wrapper = $('<div class=\"swiper-wrapper\"></div>');

			$items.each(function () {
				const $item = $(this);
				$item.addClass('swiper-slide');
				$wrapper.append($item);
			});

			// Remplacer le contenu du slider par une structure Swiper propre
			$slider.empty().addClass('swiper').append($wrapper);

			// Créer la pagination à l'intérieur du slider si nécessaire
			if (config.showDots === true || config.showDots === 'yes') {
				if ($slider.find('.swiper-pagination').length === 0) {
					$slider.append('<div class=\"swiper-pagination\"></div>');
				}

			}

			// Calcul des valeurs Swiper à partir de la config existante
			const slidesPerViewDesktop = parseInt(config.slidesToShow, 10) || 3;
			const slidesPerViewTablet = parseInt(config.slidesToShowTablet, 10) || Math.min(slidesPerViewDesktop, 2);
			const slidesPerViewMobile = parseInt(config.slidesToShowMobile, 10) || 1;

			const slidesPerGroupDesktop = parseInt(config.slidesToScroll, 10) || 1;
			const slidesPerGroupTablet = parseInt(config.slidesToScrollTablet, 10) || 1;
			const slidesPerGroupMobile = parseInt(config.slidesToScrollMobile, 10) || 1;

			const spaceBetween = config.spaceBetween !== undefined && config.spaceBetween !== null
				? parseInt(config.spaceBetween, 10)
				: 20; // défaut uniquement si absent

			const loop =
				config.infiniteLoop === true ||
				config.infiniteLoop === 'true' ||
				config.infiniteLoop === 'yes' ||
				config.loop === true ||
				config.loop === 'true' ||
				config.loop === 'yes';

			const autoplayEnabled =
				config.autoplay === true ||
				config.autoplay === 'true' ||
				config.autoplay === 'yes';

			// Paramètres Swiper avancés venant du widget
			const direction = config.swiperDirection || 'horizontal';
			const effect = config.swiperEffect || 'slide';
			const bool = function (value, defaultValue) {
				if (value === undefined || value === null || value === '') return !!defaultValue;
				if (value === true || value === false) return value;
				if (value === 1 || value === 0) return value === 1;
				if (typeof value === 'string') {
					const v = value.toLowerCase();
					if (v === 'yes' || v === 'true' || v === '1') return true;
					if (v === 'no' || v === 'false' || v === '0') return false;
				}
				return !!value;
			};

			const slidesPerViewMode = config.swiperSlidesPerViewMode || 'fixed';
			const autoWidthEnabled = bool(config.owlAutoWidth, false);
			const effectiveSlidesPerViewMode = autoWidthEnabled ? 'auto' : slidesPerViewMode;

			const centeredSlides = bool(config.swiperCenteredSlides, false);
			const grabCursor = bool(config.swiperGrabCursor, false);
			const freeMode = bool(config.swiperFreeMode, false);
			const freeModeSticky = bool(config.swiperFreeModeSticky, false);
			const freeModeMomentum = bool(config.swiperFreeModeMomentum, true);
			const rewind = bool(config.swiperRewind, false);
			const slideToClickedSlide = bool(config.swiperSlideToClickedSlide, false);
			const allowTouchMove = bool(config.swiperAllowTouchMove, true);
			const simulateTouch = bool(config.swiperSimulateTouch, true);
			const watchOverflow = bool(config.swiperWatchOverflow, true);
			const autoHeight = bool(config.swiperAutoHeight, false);

			// Mode Fan
			const fanEnabled = bool(config.swiperFanEnabled, false);
			const fanOverlap = fanEnabled ? (parseInt(config.swiperFanOverlap, 10) || -20) : spaceBetween;

			// Mode Fan Deck
			const fanDeckEnabled = bool(config.swiperFanDeckEnabled, false);
			const fanDeckOverlap = fanDeckEnabled ? (parseInt(config.swiperFanDeckOverlap, 10) || -20) : spaceBetween;

			// Résolution du spaceBetween effectif
			const effectiveSpaceBetween = fanDeckEnabled ? fanDeckOverlap : (fanEnabled ? fanOverlap : spaceBetween);
			// centeredSlides forcé si Fan Deck avec carte centrale droite
			const fanDeckCenterUpright = bool(config.swiperFanDeckCenterUpright, true);
			const effectiveCenteredSlides = fanDeckEnabled ? (fanDeckCenterUpright ? true : centeredSlides) : (fanEnabled ? true : centeredSlides);
			const finalCenteredSlides = (autoWidthEnabled && !fanEnabled && !fanDeckEnabled) ? false : effectiveCenteredSlides;

			log('Config widget:', JSON.parse(JSON.stringify(config || {})));
			log('Computed:', {
				direction,
				effect,
				slidesPerViewMode: effectiveSlidesPerViewMode,
				autoWidthEnabled,
				centeredSlides,
				effectiveCenteredSlides,
				finalCenteredSlides,
				freeMode,
				rewind,
				loop,
			});

			const swiperConfig = {
				direction,
				effect,
				loop: loop && !rewind, // loop incompatible avec rewind
				speed: parseInt(config.speed, 10) || 500,
				spaceBetween: effectiveSpaceBetween,
				observer: true,
				observeParents: true,
				centeredSlides: finalCenteredSlides,
				grabCursor,
				rewind,
				slideToClickedSlide: (fanEnabled || fanDeckEnabled) ? true : slideToClickedSlide,
				allowTouchMove,
				simulateTouch,
				watchOverflow,
				autoHeight,
				initialSlide: 0,
				preventInteractionOnTransition: true,
				threshold: 10,
				longSwipesRatio: 0.65,
				longSwipesMs: 300,
			};

			// Free Mode
			if (freeMode) {
				swiperConfig.freeMode = {
					enabled: true,
					sticky: freeModeSticky,
					momentum: freeModeMomentum,
				};
			}

			// Gestion du mode slidesPerView
			if (effectiveSlidesPerViewMode === 'auto') {
				swiperConfig.slidesPerView = 'auto';
				swiperConfig.slidesPerGroup = 1;
			} else {
				swiperConfig.slidesPerView = slidesPerViewMobile;
				swiperConfig.slidesPerGroup = slidesPerGroupMobile;
				swiperConfig.breakpoints = {
					0: {
						slidesPerView: slidesPerViewMobile,
						slidesPerGroup: slidesPerGroupMobile,
						spaceBetween: effectiveSpaceBetween,
					},
					768: {
						slidesPerView: slidesPerViewTablet,
						slidesPerGroup: slidesPerGroupTablet,
						spaceBetween: effectiveSpaceBetween,
					},
					1024: {
						slidesPerView: slidesPerViewDesktop,
						slidesPerGroup: slidesPerGroupDesktop,
						spaceBetween: effectiveSpaceBetween,
					},
				};
			}

			// console.log('[NOVA Swiper] swiperConfig final:', JSON.parse(JSON.stringify(swiperConfig)));
			log('swiperConfig final:', JSON.parse(JSON.stringify(swiperConfig)));

			if (autoplayEnabled) {
				swiperConfig.autoplay = {
					delay: parseInt(config.autoplaySpeed, 10) || 3000,
					disableOnInteraction: false,
					pauseOnMouseEnter: config.pauseOnHover === true || config.pauseOnHover === 'yes',
				};
			}

			// Navigation : réutiliser les boutons existants s'ils sont présents
			if (config.showArrows === true || config.showArrows === 'yes') {
				let $prevBtn = $widget.find('.nova-carousel-prev, .swiper-button-prev').first();
				let $nextBtn = $widget.find('.nova-carousel-next, .swiper-button-next').first();

				if ($prevBtn.length && $nextBtn.length) {
					swiperConfig.navigation = {
						prevEl: $prevBtn[0],
						nextEl: $nextBtn[0],
					};

				}
			}

			// Pagination
			if (config.showDots === true || config.showDots === 'yes') {
				const $pagination = $slider.find('.swiper-pagination').first();
				if ($pagination.length) {
					const paginationType = config.swiperPaginationType || 'bullets';
					const paginationClickable = config.swiperPaginationClickable !== false;
					const paginationDynamicBullets = !!config.swiperPaginationDynamicBullets;

					swiperConfig.pagination = {
						el: $pagination[0],
						type: paginationType,
						clickable: paginationClickable,
					};

					if (paginationType === 'bullets' && paginationDynamicBullets) {
						swiperConfig.pagination.dynamicBullets = true;
					}

				}
			}

			// Scrollbar
			if (config.swiperScrollbarEnabled === true || config.swiperScrollbarEnabled === 'yes') {
				let $scrollbar = $slider.find('.swiper-scrollbar').first();
				if ($scrollbar.length === 0) {
					$scrollbar = $('<div class="swiper-scrollbar"></div>');
					$slider.append($scrollbar);
				}

				swiperConfig.scrollbar = {
					el: $scrollbar[0],
					draggable: config.swiperScrollbarDraggable !== false,
					hide: config.swiperScrollbarHide !== false,
				};

			}

			// Keyboard
			if (config.swiperKeyboardEnabled === true || config.swiperKeyboardEnabled === 'yes') {
				swiperConfig.keyboard = {
					enabled: true,
					onlyInViewport: config.swiperKeyboardOnlyInViewport !== false,
					pageUpDown: config.swiperKeyboardPageUpDown !== false,
				};
			}

			// Mousewheel
			if (config.swiperMousewheelEnabled === true || config.swiperMousewheelEnabled === 'yes') {
				swiperConfig.mousewheel = {
					enabled: true,
					invert: !!config.swiperMousewheelInvert,
					forceToAxis: !!config.swiperMousewheelForceToAxis,
					sensitivity: parseFloat(config.swiperMousewheelSensitivity) || 1,
					releaseOnEdges: true, // ✅ Permet de continuer le scroll de la page quand le slider est fini
				};
			}

			// Lazy loading
			if (config.swiperLazyEnabled === true || config.swiperLazyEnabled === 'yes') {
				swiperConfig.lazy = {
					loadPrevNext: config.swiperLazyLoadPrevNext !== false,
					loadOnTransitionStart: false,
				};
			}

			// Parallax
			if (config.swiperParallaxEnabled === true || config.swiperParallaxEnabled === 'yes') {
				swiperConfig.parallax = {
					enabled: true,
				};
			}

			// Grid
			if (config.swiperGridEnabled === true || config.swiperGridEnabled === 'yes') {
				swiperConfig.grid = {
					rows: parseInt(config.swiperGridRows, 10) || 2,
					fill: config.swiperGridFill || 'column',
				};
			}

			// Coverflow Effect
			if (effect === 'coverflow') {
				swiperConfig.coverflowEffect = {
					rotate: parseInt(config.swiperCoverflowRotate, 10) || 50,
					stretch: parseInt(config.swiperCoverflowStretch, 10) || 0,
					depth: parseInt(config.swiperCoverflowDepth, 10) || 100,
					scale: parseFloat(config.swiperCoverflowScale) || 1,
					slideShadows: config.swiperCoverflowSlideShadows !== false,
				};
			}

			// Cards Effect
			if (effect === 'cards') {
				swiperConfig.cardsEffect = {
					perSlideRotate: parseFloat(config.swiperCardsPerSlideRotate) || 2,
					perSlideOffset: parseInt(config.swiperCardsPerSlideOffset, 10) || 8,
					rotate: config.swiperCardsRotate !== false,
					slideShadows: config.swiperCardsSlideShadows !== false,
				};
			}

			// Fade Effect
			if (effect === 'fade') {
				swiperConfig.fadeEffect = {
					crossFade: config.swiperFadeCrossFade !== false,
				};
			}

			let swiperInstance;

			try {

				swiperInstance = new Swiper($slider[0], swiperConfig);
			} catch (e) {

				return;
			}

			if (swiperInstance) {
				this.instances.push({
					widget: $widget,
					swiper: swiperInstance,
				});

				const widgetId = $widget.data('widget-id') || $widget.attr('data-widget-id') || '';
				const debugPrefix = widgetId ? `[NOVA Swiper ${widgetId}]` : '[NOVA Swiper]';
				const d = function () {
					if (DEBUG && DEBUG_ENABLED) console.log(debugPrefix, ...arguments);
				};

				// Forcer l'affichage du premier slide à gauche si centeredSlides est désactivé
				if (!swiperConfig.centeredSlides && swiperInstance.slides && swiperInstance.slides.length > 0) {
					// Attendre que Swiper soit complètement initialisé
					setTimeout(() => {
						if (swiperInstance && !swiperInstance.destroyed) {
							swiperInstance.slideTo(0, 0); // Aller au slide 0 sans animation
							// Forcer la mise à jour de la position
							swiperInstance.update();
						}
					}, 100);
				}

				if (DEBUG && DEBUG_ENABLED) {
					try {
						d('Init state', {
							params: {
								slidesPerView: swiperInstance.params.slidesPerView,
								slidesPerGroup: swiperInstance.params.slidesPerGroup,
								centeredSlides: swiperInstance.params.centeredSlides,
								loop: swiperInstance.params.loop,
								rewind: swiperInstance.params.rewind,
								freeMode: swiperInstance.params.freeMode,
								speed: swiperInstance.params.speed,
								spaceBetween: swiperInstance.params.spaceBetween,
							},
							slides: swiperInstance.slides ? swiperInstance.slides.length : 0,
							activeIndex: swiperInstance.activeIndex,
							realIndex: swiperInstance.realIndex,
							snapIndex: swiperInstance.snapIndex,
						});

						const snapSummary = (swiperInstance.snapGrid || []).slice(0, 12);
						d('snapGrid (first 12)', snapSummary);
					} catch (e) {}

					const logState = function (label) {
						try {
							d(label, {
								activeIndex: swiperInstance.activeIndex,
								realIndex: swiperInstance.realIndex,
								previousIndex: swiperInstance.previousIndex,
								snapIndex: swiperInstance.snapIndex,
								translate: swiperInstance.translate,
								progress: swiperInstance.progress,
								isBeginning: swiperInstance.isBeginning,
								isEnd: swiperInstance.isEnd,
								swipeDirection: swiperInstance.swipeDirection,
								touches: swiperInstance.touches ? { startX: swiperInstance.touches.startX, startY: swiperInstance.touches.startY, currentX: swiperInstance.touches.currentX, currentY: swiperInstance.touches.currentY } : null,
							});
						} catch (e) {}
					};

					swiperInstance.on('touchStart', function () { logState('touchStart'); });
					swiperInstance.on('touchEnd', function () { logState('touchEnd'); });
					swiperInstance.on('transitionStart', function () { logState('transitionStart'); });
					swiperInstance.on('transitionEnd', function () { logState('transitionEnd'); });
					swiperInstance.on('slideChange', function () { logState('slideChange'); });
					swiperInstance.on('snapIndexChange', function () { logState('snapIndexChange'); });
					swiperInstance.on('setTranslate', function () { logState('setTranslate'); });
				}

				// ── Mode Fan Deck : rotation alternée style photo de groupe ────
				const fanDeckEnabled = bool(config.swiperFanDeckEnabled, false);
				if (fanDeckEnabled) {
					// console.log('[NOVA Swiper] Mode Fan Deck activé', {
					// 	angle: config.swiperFanDeckAngle,
					// 	step: config.swiperFanDeckAngleStep,
					// 	overlap: config.swiperFanDeckOverlap,
					// });
					this.applyFanDeckMode($widget, $slider, swiperInstance, config);
				}

				// ── Mode Fan : rotation aléatoire (seulement si Fan Deck inactif) ──
				const fanEnabled = bool(config.swiperFanEnabled, false);
				if (fanEnabled && !fanDeckEnabled) {
					// console.log('[NOVA Swiper] Mode Fan activé');
					this.applyFanMode($widget, $slider, swiperInstance, config);
				}

				if (!fanEnabled && !fanDeckEnabled) {
					// console.log('[NOVA Swiper] Aucun mode Fan actif');
				}
			}
		},

		/**
		 * Mode Fan Deck : rotation alternée pair/impair selon la position dans le slider.
		 * Reproduit l'effet "photo de groupe" de l'image de référence.
		 *
		 * Logique :
		 *  - On calcule la position de chaque slide par rapport au centre visible
		 *  - Les slides à gauche du centre penchent à gauche (angle négatif)
		 *  - Les slides à droite du centre penchent à droite (angle positif)
		 *  - L'angle augmente selon la distance au centre (angleStep)
		 *  - La slide centrale reste droite si centerUpright = true
		 *  - Au hover : redressement à 0° + scale + ombre optionnelle
		 */
		applyFanDeckMode: function ($widget, $slider, swiperInstance, config) {
			const baseAngle = parseFloat(config.swiperFanDeckAngle) || 3;
			const angleStep = parseFloat(config.swiperFanDeckAngleStep) || 1;
			const hoverScale = parseFloat(config.swiperFanDeckHoverScale) || 1.08;
			const hoverRotRange = config.swiperFanDeckHoverRotationRange !== undefined
				? parseFloat(config.swiperFanDeckHoverRotationRange)
				: 2; // ±2° par défaut
			const duration = parseInt(config.swiperFanDeckTransitionDuration, 10) || 400;
			const centerUpright = config.swiperFanDeckCenterUpright !== false && config.swiperFanDeckCenterUpright !== 'no';
			const hoverShadow = config.swiperFanDeckHoverShadow !== false && config.swiperFanDeckHoverShadow !== 'no';
			const overflowVisible = config.swiperFanDeckOverflowVisible !== false && config.swiperFanDeckOverflowVisible !== 'no';

			$widget.addClass('nova-fan-deck-mode');

			if (overflowVisible) {
				// Forcer overflow visible sur toute la chaîne de parents jusqu'au widget
				$slider.css('overflow', 'visible');
				$slider.find('.swiper-wrapper').css('overflow', 'visible');
				$slider.closest('.nova-carousel-slider-wrapper').css('overflow', 'visible');
				$slider.closest('.nova-carousel-container').css('overflow', 'visible');
				$widget.css('overflow', 'visible');
				// Couvrir aussi les wrappers Elementor directs
				$widget.parent().css('overflow', 'visible');
			}

			const easing = 'cubic-bezier(0.25, 1, 0.5, 1)';
			const transitionCSS = `transform ${duration}ms ${easing}, box-shadow ${duration}ms ${easing}, z-index 0s`;
			const noTransitionCSS = 'none'; // utilisé pendant l'init pour éviter l'animation parasite

			// Flag : true = Swiper est prêt, on peut animer
			let isReady = false;

			/**
			 * Applique les rotations directement sur .swiper-slide
			 * Chaque slide reçoit un angle aléatoire unique mémorisé dans dataset.
			 * @param {boolean} animate
			 */
			const applyDeckRotations = (animate) => {
				const slides = Array.from($slider[0].querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate)'));
				const total = slides.length;
				if (!total) return;

				slides.forEach((slide, i) => {
					// Générer un angle aléatoire unique une seule fois par slide
					if (slide.dataset.novaFanDeckAngle === undefined || slide.dataset.novaFanDeckAngle === '') {
						// Angle aléatoire entre -baseAngle et +baseAngle, avec variation par angleStep
						const maxAngle = baseAngle + i * angleStep * 0.3;
						const angle = (Math.random() * maxAngle * 2) - maxAngle;
						slide.dataset.novaFanDeckAngle = angle;
					}

					const angle = parseFloat(slide.dataset.novaFanDeckAngle);
					const zIdx = String(total - i);

					slide.dataset.novaFanDeckOrigZIndex = zIdx;
					slide.style.zIndex = zIdx;
					slide.style.position = 'relative';
					slide.style.transformOrigin = 'center center';
					slide.style.transition = animate ? transitionCSS : noTransitionCSS;
					slide.style.transform = `rotate(${angle}deg) scale(1)`;
				});

				// Slides dupliquées (loop) — copier l'angle de l'original
				const dupes = $slider[0].querySelectorAll('.swiper-slide-duplicate');
				dupes.forEach((dupe) => {
					const origIndex = parseInt(dupe.dataset.swiperSlideIndex, 10);
					if (!isNaN(origIndex) && slides[origIndex]) {
						const angle = parseFloat(slides[origIndex].dataset.novaFanDeckAngle) || 0;
						dupe.style.transformOrigin = 'center center';
						dupe.style.transition = animate ? transitionCSS : noTransitionCSS;
						dupe.style.transform = `rotate(${angle}deg) scale(1)`;
						dupe.style.zIndex = slides[origIndex].style.zIndex;
					}
				});
			};

			// ── Init sans animation ────────────────────────────────────────
			// Appliquer immédiatement sans transition (Swiper n'est pas encore stable)
			applyDeckRotations(false);

			// Swiper déclenche 'init' une fois qu'il est complètement prêt
			// (slides clonées créées, position initiale calculée)
			swiperInstance.on('init', () => {
				applyDeckRotations(false);
			});

			// Après un court délai, activer les transitions et recalculer
			// (couvre les cas où 'init' est déjà passé avant notre listener)
			setTimeout(() => {
				applyDeckRotations(false); // recalcul final sans animation
				isReady = true;            // à partir d'ici, les slideChange animent
			}, 300);

			// ── Changements de slide (avec animation) ─────────────────────
			swiperInstance.on('slideChange', () => {
				applyDeckRotations(isReady); // animate seulement si prêt
			});

			swiperInstance.on('update', () => {
				applyDeckRotations(isReady);
			});

			swiperInstance.on('resize', () => {
				applyDeckRotations(isReady);
			});

			// ── Hover : rotation aléatoire légère + scale centré ──────────
			$slider.on('mouseenter', '.swiper-slide', function () {
				if (!this.dataset.novaFanDeckOrigZIndex) {
					this.dataset.novaFanDeckOrigZIndex = this.style.zIndex || '1';
				}
				const hoverAngle = (Math.random() * hoverRotRange * 2) - hoverRotRange;
				this.style.transition = transitionCSS;
				this.style.transformOrigin = 'center center';
				this.style.transform = `rotate(${hoverAngle}deg) scale(${hoverScale})`;
				this.style.zIndex = '999';
				if (hoverShadow) this.style.boxShadow = '0 12px 28px rgba(0,0,0,0.18)';
			});

			$slider.on('mouseleave', '.swiper-slide', function () {
				const angle = parseFloat(this.dataset.novaFanDeckAngle) || 0;
				const origZ = this.dataset.novaFanDeckOrigZIndex || '1';
				this.style.transition = transitionCSS;
				this.style.transformOrigin = 'center center';
				this.style.transform = `rotate(${angle}deg) scale(1)`;
				this.style.zIndex = origZ;
				this.style.boxShadow = '';
			});
		},

		/**
		 * Mode Fan : rotation aléatoire sur chaque slide + redressement au hover.
		 */
		applyFanMode: function ($widget, $slider, swiperInstance, config) {
			const angleMin = parseFloat(config.swiperFanAngleMin) || -5;
			const angleMax = parseFloat(config.swiperFanAngleMax) || 5;
			const hoverScale = parseFloat(config.swiperFanHoverScale) || 1.15;
			const duration = parseInt(config.swiperFanTransitionDuration, 10) || 400;
			const overflowVisible = config.swiperFanOverflowVisible !== false &&
				config.swiperFanOverflowVisible !== 'no';

			$widget.addClass('nova-fan-mode');

			if (overflowVisible) {
				$slider.css('overflow', 'visible');
				$slider.closest('.nova-carousel-slider-wrapper, .nova-carousel-widget').css('overflow', 'visible');
			}

			const easing = 'cubic-bezier(0.25, 1, 0.5, 1)';
			const transitionCSS = `transform ${duration}ms ${easing}, box-shadow ${duration}ms ${easing}`;
			const noTransitionCSS = 'none';

			let isReady = false;

			const applyRotations = (animate) => {
				const slides = $slider[0].querySelectorAll('.swiper-slide');
				slides.forEach((slide) => {
					if (!slide.dataset.novaFanAngle) {
						const angle = Math.random() * (angleMax - angleMin) + angleMin;
						slide.dataset.novaFanAngle = angle;
					}
					const angle = parseFloat(slide.dataset.novaFanAngle);
					slide.style.transformOrigin = 'center bottom';
					slide.style.transition = animate ? transitionCSS : noTransitionCSS;
					slide.style.transform = `rotate(${angle}deg) scale(1)`;
					slide.style.position = 'relative';
				});
			};

			// Init sans animation
			applyRotations(false);

			setTimeout(() => {
				applyRotations(false);
				isReady = true;
			}, 300);

			swiperInstance.on('slideChange', () => applyRotations(isReady));
			swiperInstance.on('update', () => applyRotations(isReady));

			$slider.on('mouseenter', '.swiper-slide', function () {
				if (!this.dataset.novaFanOrigZIndex) {
					this.dataset.novaFanOrigZIndex = this.style.zIndex || '1';
				}
				this.style.transition = transitionCSS;
				this.style.transform = `rotate(0deg) scale(${hoverScale})`;
				this.style.transformOrigin = 'center bottom';
				this.style.zIndex = '10';
				this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
			});

			$slider.on('mouseleave', '.swiper-slide', function () {
				const angle = parseFloat(this.dataset.novaFanAngle) || 0;
				const origZ = this.dataset.novaFanOrigZIndex || '1';
				this.style.transition = transitionCSS;
				this.style.transform = `rotate(${angle}deg) scale(1)`;
				this.style.transformOrigin = 'center bottom';
				this.style.zIndex = origZ;
				this.style.boxShadow = '';
			});
		},
	};

	// Démarrer
	NovaCarouselSwiper.init();

})(jQuery);
