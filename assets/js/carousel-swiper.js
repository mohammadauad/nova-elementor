/**
 * NOVA Carousel (Swiper) Widget
 * Implémentation Swiper.js basée sur la configuration du widget NOVA Carousel.
 */

(function ($) {
	'use strict';

	const DEBUG = false;
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
	/**
	 * Bornes de drag : Swiper 11 (min=0, max=négatif) + recalcul DOM (scrollWidth) pour atteindre la dernière slide.
	 */
	function getSwiperTranslateBounds(swiperInstance) {
		let lower = 0;
		let upper = 0;
		if (swiperInstance && typeof swiperInstance.minTranslate === 'function') {
			const a = swiperInstance.minTranslate();
			const b = swiperInstance.maxTranslate();
			if (typeof a === 'number' && !isNaN(a) && typeof b === 'number' && !isNaN(b)) {
				lower = Math.min(a, b);
				upper = Math.max(a, b);
			}
		}
		const grid = swiperInstance && swiperInstance.snapGrid;
		if (grid && grid.length) {
			let gmin = grid[0];
			let gmax = grid[0];
			for (let i = 1; i < grid.length; i++) {
				if (grid[i] < gmin) gmin = grid[i];
				if (grid[i] > gmax) gmax = grid[i];
			}
			lower = Math.min(lower, gmin);
			upper = Math.max(upper, gmax);
		}
		const el = swiperInstance && swiperInstance.el;
		const wrapper = swiperInstance && swiperInstance.wrapperEl;
		if (el && wrapper) {
			const containerW = el.clientWidth || 0;
			const contentW = wrapper.scrollWidth || 0;
			if (contentW > containerW + 1) {
				const overflowMin = containerW - contentW;
				lower = Math.min(lower, overflowMin);
			}
		}
		return { lower: lower, upper: upper };
	}

	function clampSwiperTranslate(swiperInstance, value) {
		if (!swiperInstance) {
			return value;
		}
		const bounds = getSwiperTranslateBounds(swiperInstance);
		return Math.max(Math.min(value, bounds.upper), bounds.lower);
	}

	const DEBUG_ENABLED = (function () {
		try {
			if (window.NOVA_SWIPER_DEBUG === true) return true;
			if (typeof localStorage !== 'undefined' && localStorage.getItem('NOVA_SWIPER_DEBUG') === '1') return true;
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

	/**
	 * Debug ultra-détaillé (slider 1 Fan Deck par défaut).
	 * Activer : ?nova_swiper_debug=1
	 * Filtrer  : ?nova_swiper_debug_widget=ebe2c8c  (ou "all" pour tous les carousels)
	 * Console  : localStorage.setItem('NOVA_SWIPER_DEBUG','1')
	 */
	const ULTRA_DEBUG_WIDGET_IDS = (function () {
		if (!DEBUG_ENABLED) {
			return null;
		}
		try {
			const readParam = function (search) {
				if (!search) {
					return '';
				}
				return new URLSearchParams(search).get('nova_swiper_debug_widget') || '';
			};
			let raw = '';
			try { raw = readParam(window.location && window.location.search); } catch (e) {}
			if (!raw) {
				try {
					if (window.self !== window.top && window.top) {
						raw = readParam(window.top.location && window.top.location.search);
					}
				} catch (e) {}
			}
			if (!raw && window.parent) {
				try { raw = readParam(window.parent.location && window.parent.location.search); } catch (e) {}
			}
			if (window.NOVA_SWIPER_DEBUG_WIDGET) {
				raw = String(window.NOVA_SWIPER_DEBUG_WIDGET);
			}
			if (raw === 'all' || raw === '*') {
				return [];
			}
			if (raw) {
				return raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
			}
			// Slider 1 Fan Deck (id Elementor connu sur la page d'accueil)
			return ['ebe2c8c'];
		} catch (e) {
			return ['ebe2c8c'];
		}
	})();

	const ultraDebugSeq = {};

	const shouldUltraDebugWidget = function (widgetId, config) {
		if (!DEBUG_ENABLED) {
			return false;
		}
		if (ULTRA_DEBUG_WIDGET_IDS === null) {
			return false;
		}
		if (ULTRA_DEBUG_WIDGET_IDS.length === 0) {
			return true;
		}
		const id = widgetId ? String(widgetId) : '';
		if (ULTRA_DEBUG_WIDGET_IDS.some(function (needle) { return id.indexOf(needle) !== -1; })) {
			return true;
		}
		if (config && (config.swiperFanDeckEnabled === true || config.swiperFanDeckEnabled === 'yes')) {
			return true;
		}
		return false;
	};

	const ultraDebugNextSeq = function (widgetId) {
		const key = widgetId || '_';
		ultraDebugSeq[key] = (ultraDebugSeq[key] || 0) + 1;
		return ultraDebugSeq[key];
	};

	const snapshotTouchEvent = function (e) {
		if (!e) {
			return null;
		}
		const out = {
			type: e.type,
			pointerType: e.pointerType,
			pointerId: e.pointerId,
			button: e.button,
			clientX: e.clientX,
			clientY: e.clientY,
			pageX: e.pageX,
			pageY: e.pageY,
			target: e.target && e.target.className ? String(e.target.className).slice(0, 120) : (e.target && e.target.nodeName),
		};
		if (e.changedTouches && e.changedTouches.length) {
			out.changedTouches = Array.from(e.changedTouches).map(function (t) {
				return { id: t.identifier, x: t.clientX, y: t.clientY };
			});
		}
		return out;
	};

	const snapshotSwiperState = function (swiperInstance) {
		if (!swiperInstance || swiperInstance.destroyed) {
			return { destroyed: true };
		}
		const data = swiperInstance.touchEventsData || {};
		const bounds = getSwiperTranslateBounds(swiperInstance);
		return {
			enabled: swiperInstance.enabled,
			isLocked: swiperInstance.isLocked,
			destroyed: swiperInstance.destroyed,
			allowTouchMove: swiperInstance.allowTouchMove,
			params: {
				allowTouchMove: swiperInstance.params && swiperInstance.params.allowTouchMove,
				simulateTouch: swiperInstance.params && swiperInstance.params.simulateTouch,
				touchEventsTarget: swiperInstance.params && swiperInstance.params.touchEventsTarget,
				centeredSlides: swiperInstance.params && swiperInstance.params.centeredSlides,
				spaceBetween: swiperInstance.params && swiperInstance.params.spaceBetween,
				slidesPerView: swiperInstance.params && swiperInstance.params.slidesPerView,
				speed: swiperInstance.params && swiperInstance.params.speed,
				threshold: swiperInstance.params && swiperInstance.params.threshold,
				preventClicks: swiperInstance.params && swiperInstance.params.preventClicks,
			},
			translate: swiperInstance.translate,
			getTranslate: typeof swiperInstance.getTranslate === 'function' ? swiperInstance.getTranslate() : null,
			bounds: bounds,
			activeIndex: swiperInstance.activeIndex,
			realIndex: swiperInstance.realIndex,
			snapIndex: swiperInstance.snapIndex,
			previousIndex: swiperInstance.previousIndex,
			isBeginning: swiperInstance.isBeginning,
			isEnd: swiperInstance.isEnd,
			progress: swiperInstance.progress,
			swipeDirection: swiperInstance.swipeDirection,
			touchEventsData: {
				isTouched: data.isTouched,
				isMoved: data.isMoved,
				startMoving: data.startMoving,
				isScrolling: data.isScrolling,
				pointerId: data.pointerId,
				touchId: data.touchId,
				currentTranslate: data.currentTranslate,
				startTranslate: data.startTranslate,
				allowThresholdMove: data.allowThresholdMove,
			},
			touches: swiperInstance.touches ? {
				startX: swiperInstance.touches.startX,
				startY: swiperInstance.touches.startY,
				currentX: swiperInstance.touches.currentX,
				currentY: swiperInstance.touches.currentY,
				diff: swiperInstance.touches.diff,
			} : null,
			snapGrid: {
				length: (swiperInstance.snapGrid || []).length,
				first5: (swiperInstance.snapGrid || []).slice(0, 5),
				last3: (swiperInstance.snapGrid || []).slice(-3),
			},
			slidesLength: swiperInstance.slides ? swiperInstance.slides.length : 0,
			dom: {
				containerW: swiperInstance.el ? swiperInstance.el.clientWidth : null,
				wrapperScrollW: swiperInstance.wrapperEl ? swiperInstance.wrapperEl.scrollWidth : null,
				sliderClasses: swiperInstance.el ? swiperInstance.el.className : '',
				hasNovaDragging: swiperInstance.el ? swiperInstance.el.classList.contains('nova-swiper-dragging') : false,
				releaseGuardBound: swiperInstance.el ? !!swiperInstance.el.novaDragReleaseGuardBound : false,
			},
			ctor: {
				NovaSwiperBundle: !!window.NovaSwiperBundle,
				globalSwiper: typeof window.Swiper !== 'undefined',
			},
		};
	};

	const ultraLog = function (widgetId, phase, payload, level) {
		if (!shouldUltraDebugWidget(widgetId)) {
			return;
		}
		const seq = ultraDebugNextSeq(widgetId);
		const ts = (typeof performance !== 'undefined' && performance.now) ? performance.now().toFixed(1) : Date.now();
		const label = '[NOVA Slider #' + (widgetId || '?') + ' | ' + seq + ' | ' + ts + 'ms] ' + phase;
		const fn = level === 'warn' ? console.warn : (level === 'error' ? console.error : console.log);
		try {
			fn('%c' + label, 'color:#0666DD;font-weight:700', payload !== undefined ? payload : '');
		} catch (e) {
			fn(label, payload);
		}
	};

	if (DEBUG_ENABLED) {
		try {
			console.log(
				'%c[NOVA Swiper ULTRA DEBUG ON]',
				'color:#FFD034;background:#123B09;padding:4px 8px;font-weight:bold',
				{
					widgetFilter: ULTRA_DEBUG_WIDGET_IDS && ULTRA_DEBUG_WIDGET_IDS.length
						? ULTRA_DEBUG_WIDGET_IDS
						: 'ALL carousels',
					hint: 'Ajoutez ?nova_swiper_debug=1&nova_swiper_debug_widget=ebe2c8c à l’URL (ou localStorage NOVA_SWIPER_DEBUG=1)',
				}
			);
		} catch (e) {}
	}

	/**
	 * Log complet de la config (widget + options Swiper) sans activer tout le debug Swiper.
	 * Activer sur le site : ?nova_carousel_swiper_config=1
	 * ou localStorage.setItem('NOVA_CAROUSEL_SWIPER_LOG_CONFIG','1')
	 * ou window.NOVA_CAROUSEL_SWIPER_LOG_CONFIG = true
	 */
	const shouldLogNovaCarouselSwiperConfig = function () {
		try {
			if (window.NOVA_CAROUSEL_SWIPER_LOG_CONFIG === true) return true;
			if (typeof localStorage !== 'undefined' && localStorage.getItem('NOVA_CAROUSEL_SWIPER_LOG_CONFIG') === '1') return true;
			const params = new URLSearchParams(window.location && window.location.search ? window.location.search : '');
			return params.get('nova_carousel_swiper_config') === '1';
		} catch (e) {
			return false;
		}
	};

	const log = function () {
		if (DEBUG_ENABLED) console.log('[NOVA Swiper]', ...arguments);
	};
	const warn = function () {
		if (DEBUG_ENABLED) console.warn('[NOVA Swiper]', ...arguments);
	};

	const getSwiperConstructor = function () {
		return window.NovaSwiperBundle || window.Swiper;
	};

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

			log('Script chargé. isEditor:', self.isElementorEditor(), '| Swiper:', typeof Swiper !== 'undefined', '| NovaSwiperBundle:', !!window.NovaSwiperBundle, '| elementorFrontend:', typeof elementorFrontend !== 'undefined');

			const registerHook = function () {
				if (window.NovaCarouselSwiperElementReadyHooked) {
					return;
				}
				if (typeof elementorFrontend === 'undefined' || !elementorFrontend.hooks) {
					log('elementorFrontend pas encore prêt, retry...');
					setTimeout(registerHook, 100);
					return;
				}

				window.NovaCarouselSwiperElementReadyHooked = true;
				log('elementorFrontend disponible — enregistrement du hook frontend/element_ready (une seule fois)');

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

			// Fallback DOM ready (un seul passage + retry léger si Elementor charge tard)
			$(document).ready(function () {
				const scanSwiperWidgets = function () {
					$('.elementor-widget-nova-carousel-swiper .nova-carousel-widget').each(function () {
						self.initInstance($(this));
					});
				};
				scanSwiperWidgets();
				setTimeout(scanSwiperWidgets, 400);
			});
		},

		/**
		 * Garantir Swiper bundle complet (module Touch). Elementor expose souvent un Swiper sans drag.
		 */
		ensureSwiper: function (callback) {
			const finish = function () {
				const Ctor = getSwiperConstructor();
				if (typeof Ctor === 'undefined') {
					warn('ensureSwiper: constructeur Swiper introuvable');
					return;
				}
				if (!window.NovaSwiperBundle) {
					window.NovaSwiperBundle = Ctor;
				}
				log('Swiper prêt (NovaSwiperBundle:', !!window.NovaSwiperBundle, ')');
				callback();
			};

			if (window.NovaSwiperBundle) {
				finish();
				return;
			}

			if (typeof Swiper !== 'undefined') {
				// Script inline après nova-swiper-bundle OU Swiper global déjà = bundle
				const scriptBundle = document.querySelector('script[src*="swiper-bundle"]');
				if (scriptBundle) {
					window.NovaSwiperBundle = Swiper;
					finish();
					return;
				}
				// Swiper Elementor sans bundle : charger le bundle nous-mêmes
				warn('Swiper détecté sans swiper-bundle — chargement du bundle complet pour le drag');
			}

			this.loadSwiperBundle(finish);
		},

		/**
		 * Retire les clés Owl du JSON partagé ; force 1 slide par geste Swiper.
		 */
		sanitizeSwiperConfig: function (config) {
			if (!config || typeof config !== 'object') {
				return {};
			}
			const clean = {};
			Object.keys(config).forEach(function (key) {
				if (/^owl/i.test(String(key))) {
					return;
				}
				clean[key] = config[key];
			});
			clean.slidesToScroll = 1;
			clean.slidesToScrollTablet = 1;
			clean.slidesToScrollMobile = 1;
			clean.carouselEngine = 'swiper';
			return clean;
		},

		loadSwiperBundle: function (callback) {
			if (window.NovaCarouselSwiperBundleLoading) {
				const wait = setInterval(function () {
					if (window.NovaSwiperBundle) {
						clearInterval(wait);
						callback();
					}
				}, 50);
				setTimeout(function () { clearInterval(wait); }, 8000);
				return;
			}
			window.NovaCarouselSwiperBundleLoading = true;

			if (!document.querySelector('link[href*="swiper-bundle.min.css"]')) {
				const cssLink = document.createElement('link');
				cssLink.rel = 'stylesheet';
				cssLink.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
				document.head.appendChild(cssLink);
			}

			const jsScript = document.createElement('script');
			jsScript.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
			jsScript.onload = function () {
				window.NovaCarouselSwiperBundleLoading = false;
				window.NovaSwiperBundle = window.Swiper;
				log('swiper-bundle chargé (fallback)');
				callback();
			};
			jsScript.onerror = function () {
				window.NovaCarouselSwiperBundleLoading = false;
				warn('Échec chargement swiper-bundle');
			};
			document.head.appendChild(jsScript);
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

			config = self.sanitizeSwiperConfig(config);

			if (shouldLogNovaCarouselSwiperConfig()) {
				try {
					const wid = $widget.data('widget-id') || $widget.attr('data-widget-id') || '';
					console.log('[NOVA Carousel Swiper] data-slider-config (parsé depuis le widget)', wid, JSON.parse(JSON.stringify(config)));
				} catch (e) {
					console.log('[NOVA Carousel Swiper] data-slider-config (objet non sérialisable)', config);
				}
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

		/**
		 * Détecte une largeur de carte fixe (ex. 410px Elementor) sur la slide.
		 */
		detectFixedSlideWidth: function (slideEl) {
			if (!slideEl || typeof window.getComputedStyle !== 'function') {
				return 0;
			}
			const cs = window.getComputedStyle(slideEl);
			const w = parseFloat(cs.width);
			const minW = parseFloat(cs.minWidth);
			const maxW = parseFloat(cs.maxWidth);
			if (!isNaN(w) && w >= 60) {
				if (!isNaN(minW) && minW >= 60 && Math.abs(minW - w) < 2) {
					return Math.round(w);
				}
				if (!isNaN(maxW) && maxW >= 60 && Math.abs(maxW - w) < 2) {
					return Math.round(w);
				}
			}
			return 0;
		},

		/**
		 * Prépare les slides avant init Swiper (largeurs mesurées, pas de margin inline parasite).
		 */
		prepareSlidesForSwiper: function ($slider, $slides, mode) {
			if (!$slider || !$slider.length || !$slides || !$slides.length) {
				return;
			}

			$slider.removeClass('nova-swiper-slides-auto nova-swiper-slides-fixed');

			$slides.each(function () {
				this.style.marginRight = '';
				this.style.marginLeft = '';
			});

			if (mode === 'auto') {
				$slider.addClass('nova-swiper-slides-auto');
				this.syncAutoSlideWidths($slides);
			} else {
				$slider.addClass('nova-swiper-slides-fixed');
				$slides.each(function () {
					this.style.width = '';
					this.style.flexShrink = '';
				});
			}
		},

		/**
		 * Mesure les largeurs réelles (Elementor 410px + padding) et les fixe pour Swiper slidesPerView:auto.
		 */
		syncAutoSlideWidths: function ($slides) {
			if (!$slides || !$slides.length) {
				return;
			}
			$slides.each(function () {
				const slide = this;
				const item = slide.querySelector && slide.querySelector(':scope > .nova-carousel-item');
				const measureEl = item || slide;
				slide.style.width = '';
				slide.style.flexShrink = '0';
				slide.style.boxSizing = 'border-box';
				if (item) {
					item.style.width = '';
					item.style.flexShrink = '0';
					item.style.boxSizing = 'border-box';
				}
				const w = Math.round(measureEl.getBoundingClientRect().width);
				if (w >= 40) {
					slide.style.width = w + 'px';
				}
			});
		},

		getCarouselItemFromSlide: function (slideEl) {
			if (!slideEl) {
				return null;
			}
			if (slideEl.classList && slideEl.classList.contains('nova-carousel-item')) {
				return slideEl;
			}
			return slideEl.querySelector(':scope > .nova-carousel-item') || null;
		},

		unwrapFanDeckFaces: function ($slider) {
			if (!$slider || !$slider.length) {
				return;
			}
			$slider.find('.nova-fan-deck-rotate, .nova-fan-deck-face').each(function () {
				const wrap = this;
				const item = wrap.closest('.nova-carousel-item');
				if (!item) {
					return;
				}
				while (wrap.firstChild) {
					item.insertBefore(wrap.firstChild, wrap);
				}
				wrap.remove();
			});
		},

		/**
		 * Désactive le drag natif HTML5 sur les <img>/<video> du slider.
		 * Sinon le navigateur déclenche un dragstart natif → pointercancel ~50 ms
		 * → Swiper interrompt le drag (logs : pointercancel + translate: 0).
		 */
		disableNativeImageDrag: function ($slider) {
			if (!$slider || !$slider.length || !$slider[0]) {
				return;
			}
			const root = $slider[0];
			if (root.novaImgDragDisabled) {
				root.querySelectorAll('img, video').forEach(function (el) {
					el.setAttribute('draggable', 'false');
				});
				return;
			}
			root.novaImgDragDisabled = true;
			root.querySelectorAll('img, video').forEach(function (el) {
				el.setAttribute('draggable', 'false');
			});
			root.addEventListener('dragstart', function (e) {
				const t = e.target;
				if (t && (t.tagName === 'IMG' || t.tagName === 'VIDEO' || (t.closest && t.closest('.nova-carousel-item')))) {
					e.preventDefault();
				}
			}, true);
		},

		ensureSwiperSlideWrappers: function ($slider) {
			if (!$slider || !$slider.length) {
				return;
			}
			const stripSlideClasses = function (el) {
				if (!el || !el.classList) {
					return;
				}
				el.classList.remove(
					'swiper-slide',
					'swiper-slide-active',
					'swiper-slide-next',
					'swiper-slide-prev',
					'swiper-slide-visible',
					'swiper-slide-duplicate',
					'swiper-slide-duplicate-active',
					'swiper-slide-duplicate-next',
					'swiper-slide-duplicate-prev'
				);
				el.removeAttribute('data-swiper-slide-index');
			};

			$slider.find('.nova-carousel-item').each(function () {
				const item = this;
				const parent = item.parentElement;
				if (parent && parent.classList.contains('swiper-slide') && parent.querySelector(':scope > .nova-carousel-item') === item) {
					stripSlideClasses(item);
					return;
				}
				if (!item.classList.contains('swiper-slide')) {
					return;
				}
				const wrapper = document.createElement('div');
				wrapper.className = 'swiper-slide';
				const host = item.parentNode;
				if (!host) {
					return;
				}
				host.insertBefore(wrapper, item);
				stripSlideClasses(item);
				wrapper.appendChild(item);
			});
		},

		isFanDeckWidget: function ($widget) {
			return !!($widget && $widget.length && $widget.hasClass('nova-fan-deck-mode'));
		},

		isFanDeckSwiperEl: function (swiperInstance) {
			const el = swiperInstance && swiperInstance.el;
			return !!(el && el.closest && el.closest('.nova-fan-deck-mode'));
		},

		/**
		 * Recalcule snapGrid sans perdre la position (updateSlides seul remet translate à 0).
		 */
		refreshSwiperSnap: function (swiperInstance, $slides, options) {
			if (!swiperInstance || swiperInstance.destroyed) {
				return;
			}
			options = options || {};
			const el = swiperInstance.el;
			if (el && el.classList && el.classList.contains('nova-swiper-dragging')) {
				return;
			}

			const prevTranslate = typeof swiperInstance.getTranslate === 'function'
				? swiperInstance.getTranslate()
				: swiperInstance.translate;

			const fanDeck = this.isFanDeckSwiperEl(swiperInstance);

			if (options.syncWidths === true && !fanDeck) {
				if ($slides && $slides.length && swiperInstance.params && swiperInstance.params.slidesPerView === 'auto') {
					this.syncAutoSlideWidths($slides);
				} else if (swiperInstance.slides && swiperInstance.params && swiperInstance.params.slidesPerView === 'auto') {
					this.syncAutoSlideWidths($(swiperInstance.slides));
				}
			}

			swiperInstance.updateSize();
			if (!fanDeck) {
				swiperInstance.updateSlides();
			}

			if (typeof prevTranslate === 'number' && !isNaN(prevTranslate)) {
				const restored = clampSwiperTranslate(swiperInstance, prevTranslate);
				swiperInstance.setTransition(0);
				swiperInstance.setTranslate(restored);
				swiperInstance.translate = restored;
			}

			if (swiperInstance.isLocked && typeof swiperInstance.unlock === 'function') {
				swiperInstance.unlock();
			}

			this.fixCarouselEndReach(swiperInstance);

			if (swiperInstance.navigation && typeof swiperInstance.navigation.update === 'function') {
				swiperInstance.navigation.update();
			}
		},

		/**
		 * Permet d'aligner la dernière slide dans le viewport (slidesOffsetAfter + recalcul Swiper).
		 */
		fixCarouselEndReach: function (swiperInstance) {
			if (!swiperInstance || swiperInstance.destroyed) {
				return;
			}
			if (this.isFanDeckSwiperEl(swiperInstance)) {
				return;
			}
			const el = swiperInstance.el;
			const slides = swiperInstance.slides;
			if (!el || !slides || !slides.length) {
				return;
			}

			const containerW = el.getBoundingClientRect().width || el.clientWidth || 0;
			const lastSlide = slides[slides.length - 1];
			const lastW = lastSlide
				? (lastSlide.getBoundingClientRect().width || lastSlide.offsetWidth || 0)
				: 0;
			const offsetAfter = Math.max(0, Math.round(containerW - lastW));

			if (!swiperInstance.params || swiperInstance.params.slidesOffsetAfter === offsetAfter) {
				return;
			}

			const prevTranslate = typeof swiperInstance.getTranslate === 'function'
				? swiperInstance.getTranslate()
				: swiperInstance.translate;

			swiperInstance.params.slidesOffsetAfter = offsetAfter;
			swiperInstance.updateSize();
			swiperInstance.updateSlides();

			if (typeof prevTranslate === 'number' && !isNaN(prevTranslate)) {
				const restored = clampSwiperTranslate(swiperInstance, prevTranslate);
				swiperInstance.setTranslate(restored);
				swiperInstance.translate = restored;
			}
		},

		/**
		 * Swiper 11 : si pointerup/touchend est ignoré (pointerId / touchId), isTouched reste true
		 * et le slider suit la souris jusqu'au prochain clic. Force la fin du drag au relâchement.
		 */
		forceEndSwiperDrag: function (swiperInstance, sourceEvent) {
			if (!swiperInstance || swiperInstance.destroyed) {
				return;
			}
			const data = swiperInstance.touchEventsData;
			if (!data || !data.isTouched) {
				return;
			}
			try {
				const widEl = swiperInstance.el && swiperInstance.el.closest('[data-widget-id]');
				const wid = widEl ? (widEl.getAttribute('data-widget-id') || '') : '';
				ultraLog(wid, 'forceEndSwiperDrag (isTouched encore true)', {
					before: snapshotSwiperState(swiperInstance),
					event: snapshotTouchEvent(sourceEvent),
				}, 'warn');
			} catch (e) {}

			const speed = (swiperInstance.params && swiperInstance.params.speed) || 600;
			const touches = swiperInstance.touches || {};
			const px = sourceEvent && typeof sourceEvent.pageX === 'number'
				? sourceEvent.pageX
				: (touches.currentX || 0);
			const py = sourceEvent && typeof sourceEvent.pageY === 'number'
				? sourceEvent.pageY
				: (touches.currentY || 0);

			// Swiper 11 ignore pointerup si touchId ou pointerId ne correspondent pas → isTouched bloqué.
			data.pointerId = null;
			data.touchId = null;

			if (typeof swiperInstance.onTouchEnd === 'function') {
				try {
					const touchObj = {
						identifier: 0,
						clientX: px,
						clientY: py,
						pageX: px,
						pageY: py,
					};
					swiperInstance.onTouchEnd({
						type: 'touchend',
						changedTouches: [touchObj],
						target: (sourceEvent && sourceEvent.target) || swiperInstance.el,
					});
				} catch (e) {}
				if (data.isTouched) {
					try {
						swiperInstance.onTouchEnd({
							type: 'pointerup',
							pointerType: 'mouse',
							pointerId: 1,
							clientX: px,
							clientY: py,
							pageX: px,
							pageY: py,
							target: (sourceEvent && sourceEvent.target) || swiperInstance.el,
						});
					} catch (err) {}
				}
			}

			if (data.isTouched) {
				const wasMoved = data.isMoved;
				data.isTouched = false;
				data.isMoved = false;
				data.startMoving = false;
				data.preventTouchMoveFromPointerMove = false;
				data.pointerId = null;
				data.touchId = null;
				swiperInstance.setTransition(speed);
				if (wasMoved && typeof swiperInstance.slideToClosest === 'function') {
					swiperInstance.slideToClosest(speed);
				}
				if (swiperInstance.params && swiperInstance.params.grabCursor &&
					typeof swiperInstance.setGrabCursor === 'function') {
					swiperInstance.setGrabCursor(false);
				}
			}

			if (swiperInstance.el) {
				swiperInstance.el.classList.remove('nova-swiper-dragging');
			}
		},

		/**
		 * Classe dragging + touch-action:none (sans setPointerCapture sur le slider :
		 * la capture retarget les events vers .swiper et casse le popup délégué).
		 */
		bindSwiperPointerCaptureFix: function (swiperInstance, sliderEl) {
			if (!swiperInstance || !sliderEl || sliderEl.novaPointerCaptureFixBound) {
				return;
			}
			sliderEl.novaPointerCaptureFixBound = true;

			const endDragClass = function () {
				sliderEl.classList.remove('nova-swiper-dragging');
			};

			sliderEl.addEventListener('pointerdown', function (e) {
				if (e.pointerType === 'mouse') {
					sliderEl.classList.add('nova-swiper-dragging');
				}
			}, true);

			sliderEl.addEventListener('pointerup', endDragClass, true);
			sliderEl.addEventListener('pointercancel', endDragClass, true);
		},

		/**
		 * Correctif Swiper 11 + souris : si pointerup manque après pointercancel, finir sur mouseup document.
		 * Ne pas utiliser buttons===0 sur mousemove (faux positif après pointercancel).
		 */
		bindSwiperDragReleaseGuard: function (swiperInstance, sliderEl) {
			if (!swiperInstance || !sliderEl || sliderEl.novaDragReleaseGuardBound) {
				return;
			}
			const self = this;
			sliderEl.novaDragReleaseGuardBound = true;

			let sessionActive = false;

			const endSession = function () {
				if (!sessionActive) {
					return;
				}
				sessionActive = false;
				window.removeEventListener('pointerup', onRelease, true);
				window.removeEventListener('mouseup', onRelease, true);
				window.removeEventListener('touchend', onRelease, true);
				window.removeEventListener('blur', onRelease);
			};

			const finishDrag = function (e, reason) {
				const data = swiperInstance.touchEventsData;
				if (!data || !data.isTouched) {
					endSession();
					return;
				}
				const widEl = sliderEl.closest('[data-widget-id]');
				ultraLog(widEl ? widEl.getAttribute('data-widget-id') : '', 'touchRelease: ' + reason, null);
				endSession();
				if (data.isMoved) {
					self.forceEndSwiperDrag(swiperInstance, e);
				} else {
					data.isTouched = false;
					data.isMoved = false;
					data.pointerId = null;
					data.touchId = null;
					sliderEl.classList.remove('nova-swiper-dragging');
				}
			};

			const onRelease = function (e) {
				finishDrag(e, 'mouseup/pointerup');
			};

			const beginSession = function (e) {
				if (sessionActive || swiperInstance.destroyed) {
					return;
				}
				const data = swiperInstance.touchEventsData;
				if (!data || !data.isTouched) {
					return;
				}
				if (e && e.pointerType === 'mouse') {
					data.touchId = null;
				}
				sliderEl.classList.add('nova-swiper-dragging');
				sessionActive = true;
				window.addEventListener('pointerup', onRelease, true);
				window.addEventListener('mouseup', onRelease, true);
				window.addEventListener('touchend', onRelease, true);
				window.addEventListener('blur', onRelease);
			};

			swiperInstance.on('touchStart', beginSession);
			swiperInstance.on('touchEnd', function () {
				endSession();
				sliderEl.classList.remove('nova-swiper-dragging');
			});
		},

		/**
		 * Trace complète touch/drag/snap pour diagnostiquer le slider 1 (Fan Deck).
		 */
		bindSwiperUltraDebug: function (swiperInstance, $widget, $slider, config) {
			if (!DEBUG_ENABLED || !swiperInstance || !$slider || !$slider[0]) {
				return;
			}
			const widgetId = ($widget && ($widget.data('widget-id') || $widget.attr('data-widget-id'))) || '';
			if (!shouldUltraDebugWidget(widgetId, config)) {
				return;
			}
			const sliderEl = $slider[0];
			if (sliderEl.novaUltraDebugBound) {
				ultraLog(widgetId, 'bindSwiperUltraDebug: déjà actif (skip)', null);
				return;
			}
			sliderEl.novaUltraDebugBound = true;

			const fanDeck = !!(config && (config.swiperFanDeckEnabled === true || config.swiperFanDeckEnabled === 'yes'));

			ultraLog(widgetId, '═══ ULTRA DEBUG INIT ═══', {
				fanDeck: fanDeck,
				config: config ? JSON.parse(JSON.stringify(config)) : null,
				swiper: snapshotSwiperState(swiperInstance),
				dom: {
					sliderHTML: sliderEl.outerHTML ? sliderEl.outerHTML.slice(0, 280) + '…' : '',
					slideCount: $slider.find('.swiper-slide').length,
					duplicateCount: $slider.find('.swiper-slide-duplicate').length,
					widgetClasses: $widget.attr('class'),
				},
			});

			const phases = [
				'touchStart', 'touchMove', 'touchEnd', 'touchMoveOpposite',
				'sliderFirstMove', 'sliderMove', 'setTranslate',
				'transitionStart', 'transitionEnd',
				'slideChange', 'activeIndexChange', 'snapIndexChange',
				'reachBeginning', 'reachEnd', 'fromEdge', 'toEdge',
				'click', 'tap', 'doubleTap',
			];

			phases.forEach(function (phase) {
				swiperInstance.on(phase, function (e) {
					const payload = {
						event: snapshotTouchEvent(e),
						swiper: snapshotSwiperState(swiperInstance),
					};
					if (phase === 'setTranslate' || phase === 'touchMove' || phase === 'sliderMove') {
						// Éviter flood : log 1 sur 8 pour move
						sliderEl.novaUltraMoveLog = (sliderEl.novaUltraMoveLog || 0) + 1;
						if (sliderEl.novaUltraMoveLog % 8 !== 0) {
							return;
						}
						payload.throttled = true;
						payload.moveCount = sliderEl.novaUltraMoveLog;
					}
					ultraLog(widgetId, 'swiper:' + phase, payload);
				});
			});

			// Événements DOM bruts sur le slider (capture)
			['pointerdown', 'pointermove', 'pointerup', 'pointercancel', 'mousedown', 'mousemove', 'mouseup', 'touchstart', 'touchmove', 'touchend'].forEach(function (type) {
				sliderEl.addEventListener(type, function (e) {
					if (type.indexOf('move') !== -1) {
						sliderEl.novaUltraDomMoveLog = (sliderEl.novaUltraDomMoveLog || 0) + 1;
						if (sliderEl.novaUltraDomMoveLog % 10 !== 0) {
							return;
						}
					}
					ultraLog(widgetId, 'dom:' + type + ' (slider)', {
						event: snapshotTouchEvent(e),
						swiperTouch: snapshotSwiperState(swiperInstance).touchEventsData,
						translate: swiperInstance.translate,
					});
				}, type.indexOf('down') !== -1 || type.indexOf('start') !== -1 ? { capture: true } : false);
			});

			// Document : détecter mouseup/pointerup perdus
			document.addEventListener('pointerup', function (e) {
				const data = swiperInstance.touchEventsData;
				if (!data || !data.isTouched) {
					return;
				}
				const inSlider = sliderEl.contains(e.target);
				ultraLog(widgetId, 'doc:pointerup PENDANT isTouched=true', {
					inSlider: inSlider,
					event: snapshotTouchEvent(e),
					swiper: snapshotSwiperState(swiperInstance),
					warning: !inSlider ? 'Relâchement HORS slider — risque drag bloqué' : null,
				}, inSlider ? 'log' : 'warn');
			}, true);

			document.addEventListener('mouseup', function (e) {
				const data = swiperInstance.touchEventsData;
				if (!data || !data.isTouched) {
					return;
				}
				ultraLog(widgetId, 'doc:mouseup PENDANT isTouched=true', {
					event: snapshotTouchEvent(e),
					swiper: snapshotSwiperState(swiperInstance),
				}, 'warn');
			}, true);

			// Polling court après init : détecter isTouched coincé
			let pollCount = 0;
			const poll = setInterval(function () {
				pollCount += 1;
				if (pollCount > 40 || swiperInstance.destroyed) {
					clearInterval(poll);
					return;
				}
				const data = swiperInstance.touchEventsData;
				if (data && data.isTouched && !sliderEl.matches(':active')) {
					ultraLog(widgetId, '⚠ STUCK isTouched=true sans :active', {
						swiper: snapshotSwiperState(swiperInstance),
					}, 'warn');
				}
			}, 500);

			ultraLog(widgetId, 'Listeners ultra debug attachés', { phases: phases.length });
		},

		/**
		 * Desktop (souris) : pointer drag. Mobile = touch natif Swiper (allowTouchMove).
		 */
		shouldUseNovaPointerDrag: function () {
			try {
				if (typeof window.matchMedia !== 'function') {
					return !('ontouchstart' in window);
				}
				if (window.matchMedia('(pointer: coarse)').matches) {
					return false;
				}
				return window.matchMedia('(pointer: fine)').matches;
			} catch (e) {
				return !('ontouchstart' in window);
			}
		},

		/**
		 * Fin de drag : snap Swiper natif ; dernière carte si proche de la fin du scroll.
		 */
		snapAfterPointerDrag: function (swiperInstance, speed) {
			if (!swiperInstance || swiperInstance.destroyed) {
				return;
			}
			const speedVal = typeof speed === 'number' ? speed : (swiperInstance.params.speed || 600);
			const current = typeof swiperInstance.getTranslate === 'function'
				? swiperInstance.getTranslate()
				: swiperInstance.translate;
			const bounds = getSwiperTranslateBounds(swiperInstance);
			const lastIdx = swiperInstance.slides ? swiperInstance.slides.length - 1 : 0;

			if (lastIdx > 0 && typeof current === 'number' && current <= bounds.lower + 60) {
				swiperInstance.slideTo(lastIdx, speedVal);
				return;
			}

			if (typeof swiperInstance.slideToClosest === 'function') {
				swiperInstance.slideToClosest(speedVal);
				return;
			}
			if (typeof swiperInstance.slideTo === 'function') {
				swiperInstance.slideTo(swiperInstance.activeIndex, speedVal);
			}
		},

		/**
		 * Drag souris / tactile via Pointer Events (évite overlay .nova-carousel-item-link + conflits Lenis/Swiper).
		 * Swiper allowTouchMove reste false pour ne pas doubler les handlers.
		 */
		bindNovaPointerDrag: function (sliderEl, swiperInstance) {
			if (!sliderEl || !swiperInstance || sliderEl.novaPointerDragBound) {
				return;
			}
			const carouselApi = this;
			sliderEl.novaPointerDragBound = true;

			let isDragging = false;
			let startX = 0;
			let startY = 0;
			let startTranslate = 0;
			let moved = false;
			const dragThreshold = 6;

			const isInteractiveTarget = function (target) {
				if (!target || !target.closest) {
					return false;
				}
				return !!target.closest(
					'.nova-carousel-nav, .swiper-button-prev, .swiper-button-next, .swiper-pagination, .swiper-scrollbar, button, input, textarea, select, label'
				);
			};

			const setLenisPrevent = function (on) {
				if (on) {
					sliderEl.classList.add('lenis-prevent');
				} else {
					sliderEl.classList.remove('lenis-prevent');
				}
			};

			const finishDrag = function (e) {
				if (!isDragging) {
					return;
				}
				isDragging = false;
				sliderEl.classList.remove('nova-swiper-dragging');
				setLenisPrevent(false);
				if (e && e.pointerId !== undefined && sliderEl.releasePointerCapture) {
					try {
						sliderEl.releasePointerCapture(e.pointerId);
					} catch (err) {}
				}

				if (!moved) {
					const slide = e && e.target && e.target.closest ? e.target.closest('.swiper-slide') : null;
					if (slide) {
						const link = slide.querySelector('.nova-carousel-item-link[href]');
						const href = link && link.getAttribute('href');
						if (href && href !== '#' && !isInteractiveTarget(e.target)) {
							window.location.href = href;
						}
					}
					return;
				}

				const speed = swiperInstance.params && swiperInstance.params.speed
					? swiperInstance.params.speed
					: 600;
				swiperInstance.setTransition(speed);
				if (carouselApi && typeof carouselApi.snapAfterPointerDrag === 'function') {
					carouselApi.snapAfterPointerDrag(swiperInstance, speed);
				} else if (typeof swiperInstance.slideToClosest === 'function') {
					swiperInstance.slideToClosest(speed);
				} else if (typeof swiperInstance.slideTo === 'function') {
					swiperInstance.slideTo(swiperInstance.activeIndex, speed);
				}
				if (typeof sliderEl.novaFanDeckRefreshRotations === 'function') {
					requestAnimationFrame(function () {
						sliderEl.novaFanDeckRefreshRotations(true);
					});
				}
			};

			sliderEl.addEventListener('pointerdown', function (e) {
				if (swiperInstance.destroyed) {
					return;
				}
				if (e.isPrimary === false) {
					return;
				}
				if (typeof e.button === 'number' && e.button !== 0) {
					return;
				}
				if (isInteractiveTarget(e.target)) {
					return;
				}

				isDragging = true;
				moved = false;
				sliderEl.novaPointerDragLoggedMove = false;
				startX = e.clientX;
				startY = e.clientY;
				startTranslate = typeof swiperInstance.getTranslate === 'function'
					? swiperInstance.getTranslate()
					: swiperInstance.translate;

				sliderEl.classList.add('nova-swiper-dragging');
				setLenisPrevent(true);
				swiperInstance.setTransition(0);

				if (e.pointerId !== undefined && sliderEl.setPointerCapture) {
					try {
						sliderEl.setPointerCapture(e.pointerId);
					} catch (err) {}
				}

				if (DEBUG_ENABLED) {
					const wid = sliderEl.closest('[data-widget-id]');
					const id = wid ? (wid.getAttribute('data-widget-id') || '') : '';
					log('[NOVA Swiper' + (id ? ' ' + id : '') + '] pointerdown', { x: startX, y: startY, translate: startTranslate });
				}
			});

			sliderEl.addEventListener('pointermove', function (e) {
				if (!isDragging || swiperInstance.destroyed) {
					return;
				}
				const dx = e.clientX - startX;
				const dy = e.clientY - startY;

				if (!moved) {
					if (Math.abs(dx) < dragThreshold && Math.abs(dy) < dragThreshold) {
						return;
					}
					if (Math.abs(dy) > Math.abs(dx) * 1.85) {
						isDragging = false;
						sliderEl.classList.remove('nova-swiper-dragging');
						setLenisPrevent(false);
						return;
					}
					moved = true;
				}

				e.preventDefault();
				const next = clampSwiperTranslate(swiperInstance, startTranslate + dx);
				// Ne pas appeler updateActiveIndex pendant le drag : Swiper remet translate à 0 à chaque frame.
				swiperInstance.setTransition(0);
				swiperInstance.setTranslate(next);
				swiperInstance.translate = next;

				if (DEBUG_ENABLED && moved && !sliderEl.novaPointerDragLoggedMove) {
					sliderEl.novaPointerDragLoggedMove = true;
					const wid = sliderEl.closest('[data-widget-id]');
					const id = wid ? (wid.getAttribute('data-widget-id') || '') : '';
					const b = getSwiperTranslateBounds(swiperInstance);
					log('[NOVA Swiper' + (id ? ' ' + id : '') + '] pointerdrag', {
						dx: dx,
						translate: next,
						bounds: b,
						wrapperScrollW: swiperInstance.wrapperEl ? swiperInstance.wrapperEl.scrollWidth : null,
						containerW: swiperInstance.el ? swiperInstance.el.clientWidth : null,
					});
				}
			}, { passive: false });

			sliderEl.addEventListener('pointerup', finishDrag);
			sliderEl.addEventListener('pointercancel', finishDrag);
			sliderEl.addEventListener('lostpointercapture', finishDrag);
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
				} else {
					const hasSwiperInst = !!($slider[0] && $slider[0].swiper && !$slider[0].swiper.destroyed);
					if (!hasSwiperInst) {
						const config = self.parseJsonMaybe($widget.data('nova-carousel-swiper-config')) || self.parseJsonMaybe($widget.data('slider-config')) || {};
						self.ensureSwiper(function () {
							self.clearGridInlineStyles($slider);
							self.buildSwiper($widget, $slider, config);
						});
					}
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
			let inst = null;
			try {
				inst = $slider[0] && $slider[0].swiper ? $slider[0].swiper : null;
				if (inst && !inst.destroyed) {
					inst.novaFanDeckSwiperBound = false;
					inst.destroy(true, true);
				}
			} catch (e) {}

			$slider.find('.swiper-slide-duplicate').remove();

			const $wrapper = $slider.children('.swiper-wrapper').length
				? $slider.children('.swiper-wrapper')
				: $slider.find('> .swiper > .swiper-wrapper').first();

			const unwrapToItems = function ($nodes) {
				$nodes.each(function () {
					const node = this;
					const $item = $(node).children('.nova-carousel-item').first();
					if ($item.length) {
						$slider.append($item);
					} else {
						$slider.append(node);
					}
				});
			};

			if ($wrapper && $wrapper.length) {
				const $children = $wrapper.children();
				$slider.empty();
				unwrapToItems($children);
			} else {
				const $nestedWrapper = $slider.find('.swiper-wrapper').first();
				if ($nestedWrapper.length) {
					const $children = $nestedWrapper.children();
					$slider.empty();
					unwrapToItems($children);
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
			$slider.removeClass('swiper swiper-initialized swiper-horizontal swiper-vertical swiper-backface-hidden lenis-prevent');
			if ($widget && $widget.length) {
				$widget.removeClass('lenis-prevent');
			}
			if ($slider && $slider.length) {
				$slider.removeClass('lenis-prevent');
			}
			if ($slider[0]) {
				$slider[0].novaPointerDragBound = false;
				$slider[0].novaDragReleaseGuardBound = false;
				$slider.removeClass('nova-swiper-dragging');
				delete $slider[0].novaFanDeckRefreshRotations;
				try { delete $slider[0].swiper; } catch (e) { $slider[0].swiper = undefined; }
			}
			$slider.off('.fanDeck');
			$widget.removeClass('nova-fan-deck-mode');
			$widget.removeData('nova-fan-deck-ui-bound');
			$widget.removeData('nova-carousel-swiper-initialized');
		},

		/**
		 * Secours : événement click Swiper (allowClick) quand le clic natif est bloqué.
		 */
		bindSwiperPopupClick: function (swiperInstance, $widget) {
			if (!swiperInstance || swiperInstance.novaPopupClickBound || !$widget || !$widget.length) {
				return;
			}
			const widgetId = $widget.data('widget-id') || '';
			const $overlay = $('#nova-popup-overlay-' + widgetId);
			if (!$overlay.length) {
				return;
			}
			swiperInstance.novaPopupClickBound = true;
			swiperInstance.on('click', function (swiper, e) {
				if (!swiper.allowClick || !e || !e.target) {
					return;
				}
				const item = e.target.closest('.nova-carousel-item[data-popup-index]');
				if (!item) {
					return;
				}
				if ($(e.target).closest('a[href]').length && !$(e.target).closest('.nova-popup-btn').length) {
					return;
				}
				const idx = item.getAttribute('data-popup-index');
				if (idx === null || idx === undefined) {
					return;
				}
				$overlay.find('.nova-popup-content').hide();
				const $content = $overlay.find('.nova-popup-content[data-popup-index="' + idx + '"]');
				$content.show();
				$overlay.attr('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
				$overlay[0].offsetHeight; // eslint-disable-line no-unused-expressions
				$overlay.addClass('nova-popup-open');
				const video = $content.find('video.nova-popup-video')[0];
				if (video) {
					video.currentTime = 0;
					video.play().catch(function () {});
				}
			});
		},

		/**
		 * Popup au clic sur les slides avec item_popup_enable = yes.
		 */
		initPopup: function ($widget) {
			const widgetId = $widget.data('widget-id') || '';
			if (!widgetId) {
				return;
			}

			const $overlay = $('#nova-popup-overlay-' + widgetId);
			if (!$overlay.length) {
				return;
			}

			const TAP_MOVE_MAX = 10;

			function closePopup() {
				$overlay.removeClass('nova-popup-open');
				$overlay.attr('aria-hidden', 'true');
				document.body.style.overflow = '';
				$overlay.find('video.nova-popup-video').each(function () {
					this.pause();
				});
			}

			function openPopupForItem($item) {
				const idx = $item.data('popup-index');
				if (idx === undefined) {
					return;
				}

				$overlay.find('.nova-popup-content').hide();
				const $content = $overlay.find('.nova-popup-content[data-popup-index="' + idx + '"]');
				$content.show();

				$overlay.attr('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
				$overlay[0].offsetHeight; // eslint-disable-line no-unused-expressions
				$overlay.addClass('nova-popup-open');

				const $video = $content.find('video.nova-popup-video');
				if ($video.length) {
					const video = $video[0];
					video.currentTime = 0;
					video.play().catch(function () {});
				}
			}

			function isBlockedLinkClick($target) {
				return $target.closest('a[href]').length && !$target.closest('.nova-popup-btn').length;
			}

			$widget.off('.novaPopup');

			// Tap / clic : closest() car setPointerCapture / Swiper peuvent retarget le slider
			$widget.on('pointerdown.novaPopup', '.nova-carousel-item[data-popup-index]', function (e) {
				if (isBlockedLinkClick($(e.target))) {
					return;
				}
				this._novaPopupTap = { x: e.clientX, y: e.clientY, moved: false };
			});

			$widget.on('pointermove.novaPopup', '.nova-carousel-item[data-popup-index]', function (e) {
				const t = this._novaPopupTap;
				if (!t || t.moved) {
					return;
				}
				if (Math.hypot(e.clientX - t.x, e.clientY - t.y) > TAP_MOVE_MAX) {
					t.moved = true;
				}
			});

			$widget.on('pointerup.novaPopup', '.nova-carousel-slider', function (e) {
				const $item = $(e.target).closest('.nova-carousel-item[data-popup-index]');
				if (!$item.length || isBlockedLinkClick($(e.target))) {
					return;
				}
				const t = $item[0]._novaPopupTap;
				$item[0]._novaPopupTap = null;
				if (t && t.moved) {
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				openPopupForItem($item);
			});

			$widget.on('click.novaPopup', '.nova-carousel-slider', function (e) {
				if (window.PointerEvent) {
					return;
				}
				const $item = $(e.target).closest('.nova-carousel-item[data-popup-index]');
				if (!$item.length || isBlockedLinkClick($(e.target))) {
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				openPopupForItem($item);
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
			const self = this;
			const buildWidgetId = $widget.data('widget-id') || $widget.attr('data-widget-id') || '';
			const SwiperCtor = getSwiperConstructor();
			ultraLog(buildWidgetId, 'buildSwiper START', {
				config: config ? JSON.parse(JSON.stringify(config)) : null,
				SwiperCtor: typeof SwiperCtor !== 'undefined',
				NovaSwiperBundle: !!window.NovaSwiperBundle,
				existingSwiper: !!($slider[0] && $slider[0].swiper),
			});
			log('buildSwiper appelé. SwiperCtor:', typeof SwiperCtor !== 'undefined', '| NovaSwiperBundle:', !!window.NovaSwiperBundle, '| widget-id:', buildWidgetId);

			if (typeof SwiperCtor === 'undefined') {
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
				const existing = $slider[0].swiper;
				self.ensureSwiperSlideWrappers($slider);
				self.unwrapFanDeckFaces($slider);
				self.disableNativeImageDrag($slider);
				if (existing.isLocked && typeof existing.unlock === 'function') {
					existing.unlock();
				}
				existing.allowTouchMove = true;
				if (existing.params) {
					existing.params.allowTouchMove = true;
					existing.params.simulateTouch = true;
				}
				const fanDeckEarly = bool(config.swiperFanDeckEnabled, false);
				const fanEarly = bool(config.swiperFanEnabled, false);
				if (fanDeckEarly) {
					$widget.addClass('nova-fan-deck-mode');
					if (existing.params) {
						existing.params.slideToClickedSlide = false;
						existing.params.preventClicks = false;
						existing.params.preventClicksPropagation = false;
					}
					self.applyFanDeckMode($widget, $slider, existing, config);
				} else {
					$widget.removeClass('nova-fan-deck-mode');
					if (fanEarly) {
						self.applyFanMode($widget, $slider, existing, config);
					} else {
						$widget.removeClass('nova-fan-mode');
						self.clearNovaSlideRotateTransforms($slider);
						self.refreshSwiperSnap(existing, $slider.find('.swiper-slide'), { syncWidths: false });
					}
				}
				if ($slider[0]) {
					self.bindSwiperPointerCaptureFix(existing, $slider[0]);
					if (!$slider[0].novaDragReleaseGuardBound) {
						self.bindSwiperDragReleaseGuard(existing, $slider[0]);
					}
					if (!$slider[0].novaUltraDebugBound) {
						self.bindSwiperUltraDebug(existing, $widget, $slider, config);
					}
					if ($widget.find('.nova-carousel-item[data-popup-index]').length) {
						self.bindSwiperPopupClick(existing, $widget);
					}
				}
				ultraLog(buildWidgetId, 'buildSwiper EARLY RETURN (swiper existant)', {
					swiper: snapshotSwiperState(existing),
				});
				return;
			}
			if ($widget.data('nova-carousel-swiper-building')) {
				return;
			}
			$widget.data('nova-carousel-swiper-building', true);
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
				$widget.removeData('nova-carousel-swiper-building');
				return;
			}

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

			// Nettoyer d'éventuelles anciennes classes Swiper sur les items
			$items.removeClass('swiper-slide');

			const $wrapper = $('<div class=\"swiper-wrapper\"></div>');

			$items.each(function () {
				const $item = $(this);
				const $slide = $('<div class="swiper-slide"></div>');
				$slide.append($item);
				$wrapper.append($slide);
			});

			this.unwrapFanDeckFaces($slider);

			const fanDeckModeEarly = bool(config.swiperFanDeckEnabled, false);
			if (fanDeckModeEarly) {
				$widget.addClass('nova-fan-deck-mode');
			}

			// Remplacer le contenu du slider par une structure Swiper propre
			$slider.empty().addClass('swiper lenis-prevent').append($wrapper);
			$widget.addClass('lenis-prevent');

			const $slides = $wrapper.children('.swiper-slide');

			// Créer la pagination à l'intérieur du slider si nécessaire
			if (config.showDots === true || config.showDots === 'yes') {
				if ($slider.find('.swiper-pagination').length === 0) {
					$slider.append('<div class=\"swiper-pagination\"></div>');
				}

			}

			// Calcul des valeurs Swiper à partir de la config existante (0 = non défini côté Elementor).
			const normalizeSpv = function (value, fallback) {
				const n = parseInt(value, 10);
				return n > 0 ? n : fallback;
			};
			const slidesPerViewDesktop = normalizeSpv(config.slidesToShow, 3);
			const slidesPerViewTablet = normalizeSpv(config.slidesToShowTablet, Math.min(slidesPerViewDesktop, 2));
			const slidesPerViewMobile = normalizeSpv(config.slidesToShowMobile, 1);

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
			const viewportW = typeof window !== 'undefined'
				? (window.innerWidth || document.documentElement.clientWidth || 0)
				: 1024;
			const isNarrowViewport = viewportW < 768;
			const effect = config.swiperEffect || 'slide';

			const slidesPerViewMode = config.swiperSlidesPerViewMode || 'fixed';
			let autoWidthEnabled =
				slidesPerViewMode === 'auto' ||
				bool(config.swiperAutoWidth, false);

			// Cartes Elementor en px (ex. 410px) : largeur sur .nova-carousel-item → forcer mode auto.
			if (!autoWidthEnabled && $slides.length) {
				const probe = self.getCarouselItemFromSlide($slides[0]) || $slides[0];
				const fixedW = self.detectFixedSlideWidth(probe);
				if (fixedW > 0) {
					autoWidthEnabled = true;
					log('Largeur fixe détectée sur la carte (' + fixedW + 'px) → slidesPerView auto + autoWidth');
				}
			}

			let effectiveSlidesPerViewMode = autoWidthEnabled ? 'auto' : slidesPerViewMode;

			// Mesurer les slides dans le DOM (styles Elementor appliqués) avant new Swiper().
			self.prepareSlidesForSwiper($slider, $slides, effectiveSlidesPerViewMode);

			const centeredSlides = bool(config.swiperCenteredSlides, false);
			const grabCursor = bool(config.swiperGrabCursor, false);
			const freeMode = bool(config.swiperFreeMode, false);
			const freeModeSticky = bool(config.swiperFreeModeSticky, false);
			const freeModeMomentum = bool(config.swiperFreeModeMomentum, true);
			const mousewheelEnabled = bool(config.swiperMousewheelEnabled, false);
			const pointerCoarse = typeof window.matchMedia === 'function' && window.matchMedia('(pointer: coarse)').matches;
			// Sur téléphone, le module mousewheel reçoit souvent des événements wheel / inertie → rafales de slideNext.
			const skipMousewheelOnCoarseMobile = !!(mousewheelEnabled && isNarrowViewport && pointerCoarse);

			const rewind = bool(config.swiperRewind, false);
			const slideToClickedSlide = bool(config.swiperSlideToClickedSlide, false);
			const watchOverflow = false;
			const autoHeight = bool(config.swiperAutoHeight, false);

			// Mode Fan
			const fanEnabled = bool(config.swiperFanEnabled, false);
			const fanOverlap = fanEnabled ? (parseInt(config.swiperFanOverlap, 10) || -20) : spaceBetween;

			// Mode Fan Deck
			const fanDeckEnabled = bool(config.swiperFanDeckEnabled, false);
			const fanDeckOverlap = fanDeckEnabled ? (parseInt(config.swiperFanDeckOverlap, 10) || -20) : spaceBetween;

			// Fan deck : drag natif Swiper (allowTouchMove + simulateTouch), comme le carousel témoignages.
			// Le pointer drag custom + allowTouchMove:false cassait le drag sur desktop.
			const allowTouchMove = true;
			const simulateTouch = true;
			const hasPopupSlides = $widget.find('.nova-carousel-item[data-popup-index]').length > 0;

			// Résolution du spaceBetween effectif
			const effectiveSpaceBetween = fanDeckEnabled ? fanDeckOverlap : (fanEnabled ? fanOverlap : spaceBetween);
			// Mobile : même idée que nova-brands (brands.js) — espace max 15 sur le plus petit breakpoint
			const spaceBetweenMobileBrands = (!fanEnabled && !fanDeckEnabled)
				? Math.min(effectiveSpaceBetween, 15)
				: effectiveSpaceBetween;
			// centeredSlides forcé si Fan Deck avec carte centrale droite
			const fanDeckCenterUpright = bool(config.swiperFanDeckCenterUpright, true);
			const effectiveCenteredSlides = fanDeckEnabled ? (fanDeckCenterUpright ? true : centeredSlides) : (fanEnabled ? true : centeredSlides);
			const finalCenteredSlides = (autoWidthEnabled && !fanEnabled && !fanDeckEnabled)
				? false
				: effectiveCenteredSlides;

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
				mousewheelEnabled,
				skipMousewheelOnCoarseMobile,
				pointerCoarse,
				rewind,
				loop,
				fanDeckEnabled,
			});

			// Swiper : un geste = une slide (slidesToScroll Elementor = slideBy Owl, ignoré ici).
			let gMobile = 1;
			let gTablet = 1;
			let gDesktop = 1;
			if (slidesPerGroupMobile > 1 || slidesPerGroupTablet > 1 || slidesPerGroupDesktop > 1) {
				log('slidesPerGroup forcé à 1 (ancien slidesToScroll Owl ignoré)');
			}

			// Vitesse : >800 ms + snap donne une impression de « saut » après un petit drag (ex. 2000 ms dans Elementor).
			const swiperSpeed = parseInt(config.speed, 10) || 600;
			const swiperSpeedCap = freeMode ? swiperSpeed : (pointerCoarse || isNarrowViewport ? 700 : 900);
			const swiperSpeedResolved = Math.min(swiperSpeed, swiperSpeedCap);
			if (swiperSpeed > swiperSpeedResolved) {
				log('Vitesse plafonnée', swiperSpeed + 'ms → ' + swiperSpeedResolved + 'ms (réglage Elementor trop élevé pour un swipe fluide)');
			}
			if (mousewheelEnabled && !skipMousewheelOnCoarseMobile && swiperSpeed > 1200) {
				try {
					console.warn(
						'[NOVA Carousel Swiper]',
						buildWidgetId || '(widget)',
						'Vitesse transition',
						swiperSpeed + 'ms',
						'avec molette : risque de sauts cumulés (Lenis / deltas). Recommandé : 400–800 ms dans Elementor (réglage Speed).'
					);
				} catch (e) {}
			}

			// Base Swiper alignée sur nova-brands (assets/js/brands.js initSlider ~L341+),
			// en conservant les options Elementor (effet, rewind, grab, etc.) et la molette/Lenis ci-dessous.
			const swiperConfig = {
				direction,
				effect,
				loop: loop && !rewind, // loop incompatible avec rewind
				speed: swiperSpeedResolved,
				spaceBetween: effectiveSpaceBetween,
				// Hors éditeur : observer désactivé — sinon un update() après les classes Swiper peut recalculer
				// le snapGrid et ramener translate à 0 (rebond -2008 → 0 dans les logs).
				observer: this.isElementorEditor(),
				observeParents: this.isElementorEditor(),
				centeredSlides: finalCenteredSlides,
				grabCursor: grabCursor || true,
				rewind,
				slideToClickedSlide: fanDeckEnabled ? false : ((fanEnabled) ? true : slideToClickedSlide),
				allowTouchMove,
				simulateTouch,
				touchEventsTarget: 'wrapper',
				followFinger: true,
				touchReleaseOnEdges: true,
				threshold: 5,
				touchStartPreventDefault: false,
				preventClicks: fanDeckEnabled ? false : !hasPopupSlides,
				preventClicksPropagation: fanDeckEnabled ? false : !hasPopupSlides,
				resistance: true,
				resistanceRatio: 0.85,
				watchOverflow,
				autoHeight,
				initialSlide: 0,
				slidesPerGroupSkip: 0,
				slidesPerGroupAuto: false,
				roundLengths: effectiveSlidesPerViewMode !== 'auto',
				on: {
					init: function () {
						if (!fanDeckEnabled) {
							self.fixCarouselEndReach(this);
						}
					},
					resize: function () {
						if (!fanDeckEnabled) {
							self.fixCarouselEndReach(this);
						}
					},
					breakpoint: function () {
						if (!fanDeckEnabled) {
							self.fixCarouselEndReach(this);
						}
					},
				},
			};
			// Fan deck : centeredSlidesBounds ramène translate à 0 après drag (conflit overlap négatif).
			if (!freeMode && finalCenteredSlides && !fanDeckEnabled) {
				swiperConfig.centerInsufficientSlides = true;
				swiperConfig.centeredSlidesBounds = true;
			}
			if (direction !== 'vertical' && mousewheelEnabled && !skipMousewheelOnCoarseMobile) {
				swiperConfig.nested = true;
			}

			// Pas de surcharge touch (passiveListeners / touchStartPreventDefault) : défauts Swiper = drag OK.

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
				swiperConfig.autoWidth = true;
			} else {
				swiperConfig.slidesPerView = slidesPerViewMobile;
				swiperConfig.slidesPerGroup = gMobile;
				swiperConfig.breakpoints = {
					0: {
						slidesPerView: slidesPerViewMobile,
						slidesPerGroup: gMobile,
						spaceBetween: spaceBetweenMobileBrands,
					},
					768: {
						slidesPerView: slidesPerViewTablet,
						slidesPerGroup: gTablet,
						spaceBetween: effectiveSpaceBetween,
					},
					1024: {
						slidesPerView: slidesPerViewDesktop,
						slidesPerGroup: gDesktop,
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

			// Mousewheel (desktop / trackpad). Désactivé sur mobile tactile : évite les sauts liés aux wheel synthétiques.
			if (mousewheelEnabled && !skipMousewheelOnCoarseMobile) {
				const td = parseFloat(config.swiperMousewheelThresholdDelta);
				const tt = parseFloat(config.swiperMousewheelThresholdTime);
				swiperConfig.mousewheel = {
					enabled: true,
					invert: !!config.swiperMousewheelInvert,
					forceToAxis: !!config.swiperMousewheelForceToAxis,
					sensitivity: parseFloat(config.swiperMousewheelSensitivity) || 1,
					releaseOnEdges: true,
					// Inertie trackpad / Lenis : debounce entre événements wheel (API Swiper 9+)
					thresholdDelta: (!isNaN(td) && td > 0) ? td : 48,
					thresholdTime: (!isNaN(tt) && tt > 0) ? tt : 380,
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

			if (shouldLogNovaCarouselSwiperConfig()) {
				try {
					console.log('[NOVA Carousel Swiper] options passées à new Swiper()', buildWidgetId, JSON.parse(JSON.stringify(swiperConfig)));
				} catch (e) {
					console.log('[NOVA Carousel Swiper] options passées à new Swiper()', buildWidgetId, swiperConfig);
				}
			}

			try {
				swiperInstance = new SwiperCtor($slider[0], swiperConfig);
			} catch (e) {
				$widget.removeData('nova-carousel-swiper-building');
				return;
			}

			$widget.removeData('nova-carousel-swiper-building');

			if (swiperInstance) {
				this.instances.push({
					widget: $widget,
					swiper: swiperInstance,
				});

				const widgetId = $widget.data('widget-id') || $widget.attr('data-widget-id') || '';
				const debugPrefix = widgetId ? `[NOVA Swiper ${widgetId}]` : '[NOVA Swiper]';
				const d = function () {
					if (DEBUG_ENABLED) console.log(debugPrefix, ...arguments);
				};

				swiperInstance.allowTouchMove = allowTouchMove;
				if (swiperInstance.params) {
					swiperInstance.params.allowTouchMove = allowTouchMove;
					swiperInstance.params.simulateTouch = simulateTouch;
				}
				if (fanDeckEnabled && swiperInstance.enabled === false && typeof swiperInstance.enable === 'function') {
					swiperInstance.enable();
				}
				self.bindSwiperPointerCaptureFix(swiperInstance, $slider[0]);
				if (!$slider[0].novaDragReleaseGuardBound) {
					self.bindSwiperDragReleaseGuard(swiperInstance, $slider[0]);
				}
				if (!$slider[0].novaUltraDebugBound) {
					self.bindSwiperUltraDebug(swiperInstance, $widget, $slider, config);
				}
				if (hasPopupSlides) {
					self.bindSwiperPopupClick(swiperInstance, $widget);
				}
				self.disableNativeImageDrag($slider);
				ultraLog(buildWidgetId, 'buildSwiper new Swiper OK', { swiper: snapshotSwiperState(swiperInstance) });
				const $slidesRef = $slider.find('.swiper-slide');
				let refreshPass = 0;
				let refreshTimer;
				const runRefresh = function (syncWidths) {
					refreshPass += 1;
					self.disableNativeImageDrag($slider);
					if (fanDeckEnabled) {
						const $fdSlides = $slidesRef.not('.swiper-slide-duplicate');
						if (
							refreshPass === 1 &&
							swiperInstance.params &&
							swiperInstance.params.slidesPerView === 'auto' &&
							$fdSlides.length
						) {
							self.syncAutoSlideWidths($fdSlides);
						}
						swiperInstance.updateSize();
						if ($slider[0] && typeof $slider[0].novaFanDeckRefreshRotations === 'function') {
							$slider[0].novaFanDeckRefreshRotations(false);
						}
						if (swiperInstance.navigation && typeof swiperInstance.navigation.update === 'function') {
							swiperInstance.navigation.update();
						}
						return;
					}
					self.refreshSwiperSnap(swiperInstance, $slidesRef, {
						syncWidths: syncWidths === true || refreshPass === 1,
					});
					if (DEBUG_ENABLED && refreshPass >= 2) {
						d('bounds après mesure DOM', getSwiperTranslateBounds(swiperInstance), {
							wrapperScrollW: swiperInstance.wrapperEl ? swiperInstance.wrapperEl.scrollWidth : null,
							containerW: swiperInstance.el ? swiperInstance.el.clientWidth : null,
							snapCount: (swiperInstance.snapGrid || []).length,
						});
					}
				};
				// Fan deck avant refresh : évite updateSlides sans rotations + conflits clic Swiper
				if (fanDeckEnabled) {
					this.applyFanDeckMode($widget, $slider, swiperInstance, config);
				}

				requestAnimationFrame(function () {
					runRefresh(true);
					requestAnimationFrame(function () {
						runRefresh(false);
					});
				});
				$slider.find('img').on('load.nova-swiper-' + widgetId, function () {
					clearTimeout(refreshTimer);
					refreshTimer = setTimeout(function () {
						runRefresh(false);
					}, 120);
				});
				$(window).one('load.nova-swiper-' + widgetId, function () {
					runRefresh(false);
				});

				if (DEBUG_ENABLED) {
					try {
						d('Init state', {
							params: {
								slidesPerView: swiperInstance.params.slidesPerView,
								slidesPerGroup: swiperInstance.params.slidesPerGroup,
								allowTouchMove: swiperInstance.params.allowTouchMove,
								touchEventsTarget: swiperInstance.params.touchEventsTarget,
								simulateTouch: swiperInstance.params.simulateTouch,
								isLocked: swiperInstance.isLocked,
								enabled: swiperInstance.enabled,
								watchOverflow: swiperInstance.params.watchOverflow,
								ctorIsNovaBundle: !!window.NovaSwiperBundle,
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
						const spv = swiperInstance.params.slidesPerView;
						const slideCount = swiperInstance.slides ? swiperInstance.slides.length : 0;
						const expectedSnaps = (spv === 'auto' && slideCount > 0)
							? slideCount
							: ((typeof spv === 'number' && spv >= 1 && slideCount > 0)
								? Math.max(1, slideCount - Math.floor(spv) + 1)
								: snapSummary.length);
						const step0 = snapSummary.length > 1 ? Math.abs(snapSummary[1] - snapSummary[0]) : 0;
						d('snapGrid (first 12)', snapSummary, {
							slidesPerView: spv,
							autoWidth: swiperInstance.params.autoWidth,
							slidesPerGroup: swiperInstance.params.slidesPerGroup,
							slideCount,
							snapPositions: snapSummary.length,
							expectedSnapPositions: expectedSnaps,
							snapStepPx: step0,
							hint: spv === 'auto'
								? 'Mode auto : 1 snap ≈ largeur carte (' + step0 + 'px) + spaceBetween. Garder width en px sur .swiper-slide.nova-carousel-item.'
								: ((typeof spv === 'number' && spv > 1)
									? 'slidesPerView=' + spv + ' : 1 swipe = 1 carte qui entre. Pour 1 seule visible : Slides à afficher = 1.'
									: 'slidesPerView=1 : un swipe = une slide.'),
						});
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

		paintCarouselItemRotation: function (item, angle, animate, transitionCSS, noTransitionCSS) {
			if (!item) {
				return;
			}
			item.style.transformOrigin = 'center bottom';
			item.style.transition = animate ? transitionCSS : noTransitionCSS;
			item.style.transform = 'rotate(' + angle + 'deg) scale(1)';
		},

		clearNovaSlideRotateTransforms: function ($slider) {
			if (!$slider || !$slider.length) {
				return;
			}
			this.unwrapFanDeckFaces($slider);
			$slider.find('.nova-carousel-item').each(function () {
				this.style.transform = '';
				this.style.boxShadow = '';
				this.style.zIndex = '';
				this.style.transition = '';
				delete this.dataset.novaFanAngle;
				delete this.dataset.novaFanOrigZIndex;
				delete this.dataset.novaFanDeckAngle;
				delete this.dataset.novaFanDeckBaseAngle;
				delete this.dataset.novaFanDeckOrigZIndex;
			});
			$slider.find('.swiper-slide').each(function () {
				this.style.transform = '';
				this.style.zIndex = '';
			});
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
			if (!swiperInstance || swiperInstance.destroyed || !$slider || !$slider[0]) {
				return;
			}
			const self = this;

			const fanDeckWidgetId = ($widget && ($widget.data('widget-id') || $widget.attr('data-widget-id'))) || '';
			ultraLog(fanDeckWidgetId, 'applyFanDeckMode START', {
				swiper: snapshotSwiperState(swiperInstance),
			});

			const baseAngle = parseFloat(config.swiperFanDeckAngle) || 3;
			const angleStep = parseFloat(config.swiperFanDeckAngleStep) || 1;
			const hoverScale = parseFloat(config.swiperFanDeckHoverScale) || 1.08;
			const hoverRotRange = config.swiperFanDeckHoverRotationRange !== undefined
				? parseFloat(config.swiperFanDeckHoverRotationRange)
				: 2;
			const duration = parseInt(config.swiperFanDeckTransitionDuration, 10) || 400;
			const centerUpright = config.swiperFanDeckCenterUpright !== false && config.swiperFanDeckCenterUpright !== 'no';
			const hoverShadow = config.swiperFanDeckHoverShadow !== false && config.swiperFanDeckHoverShadow !== 'no';
			const overflowVisible = config.swiperFanDeckOverflowVisible !== false && config.swiperFanDeckOverflowVisible !== 'no';

			$widget.addClass('nova-fan-deck-mode');
			self.unwrapFanDeckFaces($slider);
			self.ensureSwiperSlideWrappers($slider);

			if (overflowVisible) {
				$slider.css({
					overflow: 'hidden',
					paddingTop: '32px',
					paddingBottom: '32px',
					boxSizing: 'border-box',
				});
				$slider.closest('.nova-carousel-slider-wrapper').css('overflow', 'visible');
				$slider.closest('.nova-carousel-container').css('overflow', 'visible');
				$widget.css('overflow', 'visible');
			}

			const easing = 'cubic-bezier(0.25, 1, 0.5, 1)';
			const transitionCSS = 'transform ' + duration + 'ms ' + easing + ', box-shadow ' + duration + 'ms ' + easing;
			const noTransitionCSS = 'none';

			let isReady = false;
			let hoveredSlideEl = null;

			const getFanDeckSlide = function (el) {
				if (!el) {
					return null;
				}
				return el.classList && el.classList.contains('nova-carousel-item') ? el : el.closest('.nova-carousel-item');
			};

			const paintFanDeckTransform = function (item, angle, animate) {
				self.paintCarouselItemRotation(item, angle, animate, transitionCSS, noTransitionCSS);
			};

			const ensureBaseDeckAngle = function (item, index) {
				if (!item) {
					return 0;
				}
				if (item.dataset.novaFanDeckBaseAngle) {
					return parseFloat(item.dataset.novaFanDeckBaseAngle) || 0;
				}
				const maxAngleCap = Math.max(baseAngle + angleStep * 6, baseAngle + 2);
				const sign = index % 2 === 0 ? -1 : 1;
				const spread = baseAngle + (index % 5) * angleStep * 0.4;
				const jitter = Math.random() * angleStep * 2.5;
				let angle = sign * (spread + jitter);
				angle = Math.max(-maxAngleCap, Math.min(maxAngleCap, angle));
				if (Math.abs(angle) < baseAngle * 0.5) {
					angle = sign * baseAngle;
				}
				item.dataset.novaFanDeckBaseAngle = String(angle);
				return angle;
			};

			const applyDeckRotations = function (animate) {
				if ($slider[0].classList.contains('nova-swiper-dragging')) {
					ultraLog(fanDeckWidgetId, 'applyDeckRotations SKIP (drag en cours)', {
						animate: animate,
					});
					return;
				}
				const items = Array.from($slider[0].querySelectorAll(
					'.swiper-slide:not(.swiper-slide-duplicate) > .nova-carousel-item'
				));
				const total = items.length;
				if (!total) {
					return;
				}

				const activeIndex = typeof swiperInstance.activeIndex === 'number' ? swiperInstance.activeIndex : 0;

				items.forEach(function (item, i) {
					if (hoveredSlideEl && item === hoveredSlideEl) {
						return;
					}
					let angle = ensureBaseDeckAngle(item, i);
					if (centerUpright && i === activeIndex) {
						angle = 0;
					}
					const zIdx = String(total - Math.abs(i - activeIndex));
					const slideWrap = item.parentElement;
					item.dataset.novaFanDeckAngle = String(angle);
					item.dataset.novaFanDeckOrigZIndex = zIdx;
					item.style.position = 'relative';
					item.style.height = 'auto';
					item.style.overflow = 'visible';
					item.style.transform = '';
					item.style.boxShadow = '';
					if (slideWrap && slideWrap.classList.contains('swiper-slide')) {
						slideWrap.style.zIndex = zIdx;
						slideWrap.style.overflow = 'visible';
					}
					paintFanDeckTransform(item, angle, animate);
				});

				$slider[0].querySelectorAll('.swiper-slide-duplicate > .nova-carousel-item').forEach(function (dupe) {
					const origIndex = parseInt(dupe.dataset.swiperSlideIndex, 10);
					if (isNaN(origIndex) || !items[origIndex]) {
						return;
					}
					const orig = items[origIndex];
					const angle = parseFloat(orig.dataset.novaFanDeckAngle) || 0;
					dupe.dataset.novaFanDeckAngle = String(angle);
					dupe.dataset.novaFanDeckBaseAngle = orig.dataset.novaFanDeckBaseAngle || String(angle);
					const dupeWrap = dupe.parentElement;
					if (dupeWrap) {
						dupeWrap.style.zIndex = orig.parentElement ? orig.parentElement.style.zIndex : '';
					}
					paintFanDeckTransform(dupe, angle, animate);
				});
			};

			$slider[0].novaFanDeckRefreshRotations = applyDeckRotations;

			if (!swiperInstance.novaFanDeckSwiperBound) {
				swiperInstance.novaFanDeckSwiperBound = true;
				const sliderEl = $slider[0];
				swiperInstance.on('touchStart', function () {
					if (sliderEl) {
						sliderEl.classList.add('nova-swiper-dragging');
					}
					ultraLog(fanDeckWidgetId, 'fanDeck: touchStart (+nova-swiper-dragging)', {
						swiper: snapshotSwiperState(swiperInstance),
					});
				});
				swiperInstance.on('touchEnd', function () {
					ultraLog(fanDeckWidgetId, 'fanDeck: touchEnd', {
						swiper: snapshotSwiperState(swiperInstance),
					});
					requestAnimationFrame(function () {
						applyDeckRotations(isReady);
					});
				});
				swiperInstance.on('slideChange', function () {
					applyDeckRotations(isReady);
				});
				swiperInstance.on('activeIndexChange', function () {
					applyDeckRotations(isReady);
				});
				swiperInstance.on('transitionEnd', function () {
					applyDeckRotations(isReady);
				});
				swiperInstance.on('resize', function () {
					applyDeckRotations(isReady);
				});

			}

			if (!$widget.data('nova-fan-deck-ui-bound')) {
				$widget.data('nova-fan-deck-ui-bound', true);

				$slider.on('mouseenter.fanDeck', '.nova-carousel-item', function () {
					const item = getFanDeckSlide(this);
					if (!item) {
						return;
					}
					hoveredSlideEl = item;
					if (!item.dataset.novaFanDeckOrigZIndex) {
						item.dataset.novaFanDeckOrigZIndex = item.style.zIndex || '1';
					}
					const hoverAngle = (Math.random() * hoverRotRange * 2) - hoverRotRange;
					item.style.transition = transitionCSS;
					item.style.transformOrigin = 'center bottom';
					item.style.transform = 'rotate(' + hoverAngle + 'deg) scale(' + hoverScale + ')';
					const slideWrap = item.parentElement;
					if (slideWrap && slideWrap.classList.contains('swiper-slide')) {
						slideWrap.style.zIndex = '999';
					}
					if (hoverShadow) {
						item.style.boxShadow = '0 12px 28px rgba(0,0,0,0.18)';
					}
				});

				$slider.on('mouseleave.fanDeck', '.nova-carousel-item', function () {
					const item = getFanDeckSlide(this);
					if (!item) {
						return;
					}
					if (hoveredSlideEl === item) {
						hoveredSlideEl = null;
					}
					const baseAngleStored = parseFloat(item.dataset.novaFanDeckBaseAngle);
					const activeIdx = typeof swiperInstance.activeIndex === 'number' ? swiperInstance.activeIndex : 0;
					const itemList = Array.from($slider[0].querySelectorAll(
						'.swiper-slide:not(.swiper-slide-duplicate) > .nova-carousel-item'
					));
					const slideIndex = itemList.indexOf(item);
					let angle = !isNaN(baseAngleStored) ? baseAngleStored : (parseFloat(item.dataset.novaFanDeckAngle) || 0);
					if (centerUpright && slideIndex === activeIdx) {
						angle = 0;
					}
					item.style.boxShadow = '';
					const slideWrap = item.parentElement;
					if (slideWrap && slideWrap.classList.contains('swiper-slide')) {
						slideWrap.style.zIndex = item.dataset.novaFanDeckOrigZIndex || '1';
					}
					paintFanDeckTransform(item, angle, true);
				});
			}

			applyDeckRotations(false);
			requestAnimationFrame(function () {
				applyDeckRotations(false);
			});
			setTimeout(function () {
				applyDeckRotations(false);
				isReady = true;
			}, 300);
		},

		/**
		 * Mode Fan : rotation aléatoire sur chaque slide + redressement au hover.
		 */
		applyFanMode: function ($widget, $slider, swiperInstance, config) {
			const self = this;
			const angleMin = parseFloat(config.swiperFanAngleMin) || -5;
			const angleMax = parseFloat(config.swiperFanAngleMax) || 5;
			const hoverScale = parseFloat(config.swiperFanHoverScale) || 1.15;
			const duration = parseInt(config.swiperFanTransitionDuration, 10) || 400;
			const overflowVisible = config.swiperFanOverflowVisible !== false &&
				config.swiperFanOverflowVisible !== 'no';

			$widget.addClass('nova-fan-mode');
			self.unwrapFanDeckFaces($slider);
			self.ensureSwiperSlideWrappers($slider);

			if (overflowVisible) {
				$slider.css('overflow', 'visible');
				$slider.closest('.nova-carousel-slider-wrapper, .nova-carousel-widget').css('overflow', 'visible');
			}

			const easing = 'cubic-bezier(0.25, 1, 0.5, 1)';
			const transitionCSS = 'transform ' + duration + 'ms ' + easing + ', box-shadow ' + duration + 'ms ' + easing;
			const noTransitionCSS = 'none';

			let isReady = false;

			const applyRotations = function (animate) {
				$slider[0].querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate) > .nova-carousel-item').forEach(function (item) {
					if (!item.dataset.novaFanAngle) {
						const angle = Math.random() * (angleMax - angleMin) + angleMin;
						item.dataset.novaFanAngle = String(angle);
					}
					const angle = parseFloat(item.dataset.novaFanAngle);
					item.style.position = 'relative';
					self.paintCarouselItemRotation(item, angle, animate, transitionCSS, noTransitionCSS);
				});
			};

			applyRotations(false);

			setTimeout(function () {
				applyRotations(false);
				isReady = true;
			}, 300);

			swiperInstance.on('slideChange', function () {
				applyRotations(isReady);
			});
			swiperInstance.on('update', function () {
				applyRotations(isReady);
			});

			$slider.off('mouseenter.fanMode mouseleave.fanMode');
			$slider.on('mouseenter.fanMode', '.nova-carousel-item', function () {
				const item = this;
				if (!item.dataset.novaFanOrigZIndex) {
					item.dataset.novaFanOrigZIndex = item.style.zIndex || '1';
				}
				item.style.transition = transitionCSS;
				item.style.transform = 'rotate(0deg) scale(' + hoverScale + ')';
				item.style.transformOrigin = 'center bottom';
				item.style.zIndex = '10';
				item.style.boxShadow = '0 15px 30px rgba(0,0,0,0.2)';
				const slideWrap = item.parentElement;
				if (slideWrap && slideWrap.classList.contains('swiper-slide')) {
					slideWrap.style.zIndex = '10';
				}
			});

			$slider.on('mouseleave.fanMode', '.nova-carousel-item', function () {
				const item = this;
				const angle = parseFloat(item.dataset.novaFanAngle) || 0;
				const origZ = item.dataset.novaFanOrigZIndex || '1';
				item.style.zIndex = origZ;
				item.style.boxShadow = '';
				const slideWrap = item.parentElement;
				if (slideWrap && slideWrap.classList.contains('swiper-slide')) {
					slideWrap.style.zIndex = '';
				}
				self.paintCarouselItemRotation(item, angle, true, transitionCSS, noTransitionCSS);
			});
		},
	};

	window.NovaCarouselSwiper = NovaCarouselSwiper;
	window.NOVA_SWIPER_SNAPSHOT = snapshotSwiperState;
	window.NOVA_SWIPER_ULTRA_LOG = ultraLog;

	// Démarrer
	NovaCarouselSwiper.init();

})(jQuery);
