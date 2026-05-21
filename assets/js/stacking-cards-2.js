(function ($) {
	'use strict';

	/**
	 * NOVA Stacking Cards 2
	 * 
	 * Professional stacking cards animation with two-column layout.
	 * Follows the proven architecture of stacking-cards.js
	 * 
	 * Key features:
	 * - Cards slide up from below, covering previous cards
	 * - Previous cards get scale/blur/opacity effect
	 * - Proper scroll distance for smooth animation
	 * - Lenis smooth scroll compatible
	 */
	const NOVAStackingCards2 = {
		selectors: {
			wrapper: '.nova-stacking-cards-2-wrapper',
			variantStacking: '.nova-stacking-cards-2-variant--stacking',
			variantSlider: '.nova-stacking-cards-2-variant--slider',
			owlSlider: '.nova-sc2-slider.owl-carousel',
			widget: '.nova-stacking-cards-2',
			holder: '.nova-stacking-cards-2__holder',
			card: '.nova-stacking-card-2'
		},
		timelines: new WeakMap(),
		refreshCallbacks: new WeakMap(),
		resizeTimer: null,
		debugPrefix: '[NOVA Stacking Cards 2]',

		/**
		 * Entry point
		 */
		onDocumentReady: function () {
			if (!this.dependenciesReady()) {
				return;
			}

			this.bindElementor();
			this.init(document);
			this.attachResizeHandler();
		},

		/**
		 * Check GSAP dependencies
		 */
		dependenciesReady: function () {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				this.debugLog(null, 'GSAP ou ScrollTrigger manquant → arrêt.');
				return false;
			}
			gsap.registerPlugin(ScrollTrigger);

			// Fix mobile scroll: force ScrollTrigger to use native scroll
			ScrollTrigger.config({
				ignoreMobileResize: true,
				autoRefreshEvents: 'visibilitychange,DOMContentLoaded,load'
			});

			this.debugLog(null, 'Dépendances GSAP détectées, initialisation...');
			return true;
		},

		/**
		 * Bind Elementor frontend hooks
		 */
		bindElementor: function () {
			if (typeof elementorFrontend === 'undefined' || !elementorFrontend.hooks) {
				return;
			}

			elementorFrontend.hooks.addAction('frontend/element_ready/nova-stacking-cards-2.default', ($scope) => {
				const $wrapper = $scope.find(this.selectors.wrapper);
				if ($wrapper.length) {
					const stackingEl = $wrapper[0].querySelector(
						this.selectors.variantStacking + ' ' + this.selectors.widget
					);
					if (stackingEl) {
						this.destroyInstance(stackingEl);
					}
					this.destroySc2Owl($wrapper[0]);
					delete $wrapper[0].dataset.novaSc2DisplayMode;
					this.applyDisplayMode($wrapper[0]);
					return;
				}
				const widgetEl = $scope.find(this.selectors.widget)[0] || $scope[0];
				this.destroyInstance(widgetEl);
				this.init($scope[0]);
			});

			elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
				if (typeof ScrollTrigger !== 'undefined') {
					setTimeout(() => ScrollTrigger.refresh(), 50);
				}
			});
		},

		/**
		 * Destroy a widget instance (kill ScrollTrigger + GSAP timeline)
		 */
		destroyInstance: function (widgetEl) {
			if (!widgetEl) return;

			const entry = this.timelines.get(widgetEl);
			if (entry && entry.timeline) {
				const st = entry.timeline.scrollTrigger;
				if (st) st.kill();
				entry.timeline.kill();
				this.timelines.delete(widgetEl);
			}

			// Remove refresh callback
			const refreshHandler = this.refreshCallbacks.get(widgetEl);
			if (refreshHandler && typeof ScrollTrigger !== 'undefined') {
				ScrollTrigger.removeEventListener('refreshInit', refreshHandler);
				this.refreshCallbacks.delete(widgetEl);
			}

			// Reset initialization flag so createInstance can run again
			if (widgetEl.dataset) {
				delete widgetEl.dataset.novaStacking2Ready;
			}
		},

		/**
		 * Initialize widgets in context
		 */
		init: function (context) {
			const scope = context || document;
			const $scope = scope instanceof jQuery ? scope : $(scope);
			let wrappers = $scope.find(this.selectors.wrapper).toArray();

			if ($scope.length && $scope.is(this.selectors.wrapper)) {
				wrappers.push($scope[0]);
			}

			if (wrappers.length) {
				wrappers.forEach((wrapper) => {
					this.applyDisplayMode(wrapper);
				});
				return;
			}

			let widgets = $scope.find(this.selectors.widget).toArray();

			if ($scope.length && $scope.is(this.selectors.widget)) {
				widgets.push($scope[0]);
			}

			if (!widgets.length) {
				this.debugLog(null, 'Aucun widget trouvé dans le scope', { scope });
				return;
			}

			widgets.forEach((widget) => {
				this.createInstance(widget);
			});
		},

		/**
		 * Parse JSON from data attribute (string or object).
		 */
		parseJsonMaybe: function (raw) {
			if (!raw) {
				return null;
			}
			if (typeof raw === 'object') {
				return raw;
			}
			try {
				return JSON.parse(raw);
			} catch (e) {
				return null;
			}
		},

		/**
		 * Active mode for current viewport (aligné sur le carrousel Swiper / Elementor preview).
		 */
		getViewportMode: function (wrapperEl) {
			const cfg =
				this.parseJsonMaybe(
					wrapperEl && wrapperEl.getAttribute('data-display-mode-config')
				) || {};
			const body = document.body;
			if (body && body.classList) {
				if (body.classList.contains('elementor-device-mobile')) {
					return cfg.mobile || cfg.tablet || cfg.desktop || 'stacking';
				}
				if (body.classList.contains('elementor-device-tablet')) {
					return cfg.tablet || cfg.desktop || 'stacking';
				}
				if (body.classList.contains('elementor-device-desktop')) {
					return cfg.desktop || 'stacking';
				}
			}
			const width = window.innerWidth || document.documentElement.clientWidth || 0;
			if (width <= 767) {
				return cfg.mobile || cfg.tablet || cfg.desktop || 'stacking';
			}
			if (width <= 1024) {
				return cfg.tablet || cfg.desktop || 'stacking';
			}
			return cfg.desktop || 'stacking';
		},

		/**
		 * Affiche la bonne variante HTML et ne lance GSAP que sur le mode stacking.
		 */
		applyDisplayMode: function (wrapperEl) {
			if (!wrapperEl || !wrapperEl.classList || !wrapperEl.classList.contains('nova-stacking-cards-2-wrapper')) {
				return;
			}
			const desired = this.getViewportMode(wrapperEl);
			const prev = wrapperEl.dataset.novaSc2DisplayMode || '';
			const stackingEl = wrapperEl.querySelector(
				this.selectors.variantStacking + ' ' + this.selectors.widget
			);

			if (prev === desired) {
				return;
			}

			if (prev === 'slider') {
				this.destroySc2Owl(wrapperEl);
			}

			if (prev === 'stacking' && stackingEl) {
				this.destroyInstance(stackingEl);
			}

			wrapperEl.classList.remove(
				'nova-stacking-cards-2-wrapper--mode-stacking',
				'nova-stacking-cards-2-wrapper--mode-slider',
				'nova-stacking-cards-2-wrapper--mode-grid'
			);
			wrapperEl.classList.add('nova-stacking-cards-2-wrapper--mode-' + desired);
			wrapperEl.dataset.novaSc2DisplayMode = desired;
			wrapperEl.setAttribute('data-sc2-mode-applied', '1');

			if (desired === 'stacking' && stackingEl) {
				this.createInstance(stackingEl);
			} else if (stackingEl) {
				this.destroyInstance(stackingEl);
			}

			if (desired === 'slider') {
				this.scheduleInitSc2Owl(wrapperEl);
			} else {
				this.destroySc2Owl(wrapperEl);
			}

			if (typeof ScrollTrigger !== 'undefined') {
				ScrollTrigger.refresh();
			}
		},

		/**
		 * Détruit Owl sur la variante slider du wrapper.
		 */
		destroySc2Owl: function (wrapperEl) {
			if (!wrapperEl) {
				return;
			}
			delete wrapperEl.dataset.sc2SliderImgEqBound;
			this.clearSc2SliderEqualHeights(wrapperEl);
			const $slider = $(wrapperEl).find(this.selectors.owlSlider);
			if (!$slider.length) {
				return;
			}
			if ($slider.hasClass('owl-loaded')) {
				try {
					$slider.trigger('destroy.owl.carousel');
				} catch (e) {
					try {
						$slider.owlCarousel('destroy');
					} catch (e2) {}
				}
				$slider.removeClass('owl-loaded');
			}
		},

		/**
		 * Retire les hauteurs inline imposées par l’égalisation slider.
		 */
		clearSc2SliderEqualHeights: function (wrapperEl) {
			if (!wrapperEl) {
				return;
			}
			wrapperEl.querySelectorAll('.nova-stacking-cards-2-variant--slider .nova-stacking-card-2').forEach((el) => {
				el.style.minHeight = '';
				el.style.height = '';
			});
		},

		/**
		 * Slider : toutes les cartes prennent la hauteur de la plus haute (toutes slides du carrousel).
		 */
		equalizeSc2SliderCardHeights: function (wrapperEl) {
			if (!wrapperEl) {
				return;
			}
			const sliderRoot = wrapperEl.querySelector('.nova-stacking-cards-2-variant--slider');
			if (!sliderRoot) {
				return;
			}
			const cards = sliderRoot.querySelectorAll('.owl-item > .item > .nova-stacking-card-2');
			if (!cards.length) {
				return;
			}
			cards.forEach((el) => {
				el.style.minHeight = '';
				el.style.height = '';
			});
			window.requestAnimationFrame(function () {
				let max = 0;
				cards.forEach((el) => {
					const h = Math.ceil(el.getBoundingClientRect().height);
					if (h > max) {
						max = h;
					}
				});
				if (max < 2) {
					return;
				}
				cards.forEach((el) => {
					el.style.minHeight = max + 'px';
				});
				const $owl = $(sliderRoot).find('.owl-carousel.owl-loaded');
				if ($owl.length) {
					try {
						$owl.trigger('refresh.owl.carousel');
					} catch (e) {}
				}
				if (typeof ScrollTrigger !== 'undefined') {
					ScrollTrigger.refresh();
				}
			});
		},

		/**
		 * Recalcul hauteur quand les images du slider finissent de charger.
		 */
		bindSc2SliderEqualizeOnImages: function (wrapperEl) {
			if (!wrapperEl || wrapperEl.dataset.sc2SliderImgEqBound === '1') {
				return;
			}
			const sliderRoot = wrapperEl.querySelector('.nova-stacking-cards-2-variant--slider');
			if (!sliderRoot) {
				return;
			}
			const imgs = sliderRoot.querySelectorAll('img');
			const self = this;
			const run = function () {
				self.equalizeSc2SliderCardHeights(wrapperEl);
			};
			imgs.forEach((img) => {
				if (img.complete) {
					return;
				}
				img.addEventListener('load', run, { once: true });
				img.addEventListener('error', run, { once: true });
			});
			wrapperEl.dataset.sc2SliderImgEqBound = '1';
		},

		/**
		 * Attendre Owl (chargement async des scripts).
		 */
		waitForOwl: function (callback) {
			let attempts = 0;
			const timer = setInterval(() => {
				if (typeof $.fn.owlCarousel !== 'undefined') {
					clearInterval(timer);
					callback();
				} else if (++attempts >= 40) {
					clearInterval(timer);
				}
			}, 100);
		},

		/**
		 * Initialise Owl sur .nova-sc2-slider.owl-carousel (une fois visible).
		 */
		initSc2Owl: function (wrapperEl) {
			const self = this;
			if (!wrapperEl) {
				return;
			}
			const run = () => {
				const $slider = $(wrapperEl).find(self.selectors.owlSlider);
				if (!$slider.length || $slider.hasClass('owl-loaded')) {
					return;
				}
				const cfg = self.parseJsonMaybe($slider.attr('data-sc2-owl-config')) || {};
				const navIconPrev =
					'<span class="nova-sc2-owl-nav-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
				const navIconNext =
					'<span class="nova-sc2-owl-nav-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';

				const marginDesktop = Math.max(0, parseInt(cfg.marginDesktop, 10) || 0);
				const marginTablet = Math.max(0, parseInt(cfg.marginTablet, 10) || marginDesktop);
				const marginMobile = Math.max(0, parseInt(cfg.marginMobile, 10) || marginTablet);

				const owlBase = {
					nav: !!cfg.nav,
					dots: !!cfg.dots,
					loop: !!cfg.loop,
					smartSpeed: 480,
					navText: [navIconPrev, navIconNext],
					navRewind: false,
					onResized: function () {
						const wrap = $slider.closest('.nova-stacking-cards-2-wrapper')[0];
						if (wrap) {
							self.equalizeSc2SliderCardHeights(wrap);
						}
						if (typeof ScrollTrigger !== 'undefined') {
							ScrollTrigger.refresh();
						}
					}
				};

				try {
					$slider.owlCarousel(
						Object.assign({}, owlBase, {
							items: 1,
							autoWidth: true,
							margin: marginDesktop,
							responsive: {
								0: {
									margin: marginMobile,
									autoWidth: true
								},
								768: {
									margin: marginTablet,
									autoWidth: true
								},
								1024: {
									margin: marginDesktop,
									autoWidth: true
								}
							},
							onInitialized: function () {
								window.setTimeout(function () {
									$slider.find('.owl-item').css('width', '');
									$slider.trigger('refresh.owl.carousel');
									const wrap = $slider.closest('.nova-stacking-cards-2-wrapper')[0];
									window.setTimeout(function () {
										if (wrap) {
											self.equalizeSc2SliderCardHeights(wrap);
											self.bindSc2SliderEqualizeOnImages(wrap);
										}
										window.setTimeout(function () {
											if (wrap) {
												self.equalizeSc2SliderCardHeights(wrap);
											}
										}, 200);
									}, 90);
									if (typeof ScrollTrigger !== 'undefined') {
										ScrollTrigger.refresh();
									}
								}, 50);
							}
						})
					);
				} catch (e) {
					return;
				}

				if (typeof ScrollTrigger !== 'undefined') {
					ScrollTrigger.refresh();
				}
			};

			if (typeof $.fn.owlCarousel === 'undefined') {
				this.waitForOwl(run);
				return;
			}
			run();
		},

		/**
		 * Laisser le navigateur appliquer display:block sur la variante avant init Owl.
		 */
		scheduleInitSc2Owl: function (wrapperEl) {
			const self = this;
			window.setTimeout(() => {
				window.requestAnimationFrame(() => {
					self.initSc2Owl(wrapperEl);
				});
			}, 60);
		},

		/**
		 * Create animation instance for a widget
		 */
		createInstance: function (widgetEl) {
			// Prevent double initialization
			if (!widgetEl || widgetEl.dataset.novaStacking2Ready === '1') {
				this.debugLog(widgetEl, 'Instance déjà initialisée, on ignore.');
				return;
			}

			// Detect Elementor editor mode
			const isEditor = !!(
				(typeof elementor !== 'undefined' && elementor.isEditMode && elementor.isEditMode()) ||
				(window.parent && window.parent !== window && window.parent.elementor && window.parent.elementor.isEditMode && window.parent.elementor.isEditMode()) ||
				document.body.classList.contains('elementor-editor-active')
			);

			// MOBILE DEBUG
			const isMobile = window.innerWidth <= 767;

			const cards = Array.from(widgetEl.querySelectorAll(this.selectors.card));

			if (!cards.length) {
				this.debugLog(widgetEl, 'Aucune carte détectée, abandon.');
				return;
			}

			// In editor mode: show all cards statically (no GSAP pin/scroll)
			if (isEditor) {
				this.handleEditorMode(widgetEl, cards);
				widgetEl.dataset.novaStacking2Ready = '1';
				return;
			}

			// Check reduced motion preference
			const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

			if (reducedMotion) {
				this.debugLog(widgetEl, 'Mode prefers-reduced-motion détecté → fallback statique.');
				this.handleReducedMotion(widgetEl, cards);
				widgetEl.dataset.novaStacking2Ready = '1';
				return;
			}

			// Single card - no animation needed
			if (cards.length === 1) {
				this.debugLog(widgetEl, 'Une seule carte détectée → aucun scroll nécessaire.');
				this.handleSingleCard(cards[0]);
				widgetEl.dataset.novaStacking2Ready = '1';
				return;
			}

			// Mobile - animation enabled (same as desktop)

			const config = this.getConfig(widgetEl);
			this.debugLog(widgetEl, 'Configuration extraite', config);

			// Set initial card states
			// Cards are hidden by container's overflow:hidden (not opacity)
			// yPercent: 200 ensures cards are completely below the 100vh container
			const totalCards = cards.length;

			cards.forEach((card, index) => {
				if (index === 0) {
					// First card: visible, GSAP positions it at stickyTop
					card.classList.add('is-active');
					gsap.set(card, {
						yPercent: 0,
						y: config.stickyTop,
						scale: 1,
						transformOrigin: 'center center'
					});
				} else {
					// Other cards: way below (200% = 2x card height down)
					card.classList.remove('is-active');
					gsap.set(card, {
						yPercent: 200,
						y: 0,
						scale: 1,
						transformOrigin: 'center center'
					});
				}
			});

			// Mobile: fixer la hauteur avec window.innerHeight (évite le problème 100vh avec barre d'adresse)
			if (window.innerWidth <= 767) {
				const fixedHeight = window.innerHeight + 'px';
				widgetEl.style.height = fixedHeight;
			}

			// Calculate scroll distance (like original stacking-cards.js)
			const scrollDistanceFn = () => {
				const stackHeight = Math.max(widgetEl.offsetHeight, window.innerHeight);
				const distance = Math.max((totalCards - 1) * stackHeight * config.scrollMultiplier, 100);

				this.debugLog(widgetEl, 'Distance de scroll calculée', {
					distance,
					stackHeight,
					cardCount: totalCards,
					multiplier: config.scrollMultiplier
				});
				return '+=' + distance;
			};

			// Create GSAP timeline with ScrollTrigger (like original)
			// Pin the widget itself (100vh container) at top of viewport
			const timeline = gsap.timeline({
				defaults: {
					ease: 'none'
				},
				scrollTrigger: {
					trigger: widgetEl,
					start: 'top top',
					end: scrollDistanceFn,
					scrub: config.scrub,
					pin: true,
					anticipatePin: 1,
					markers: config.debug,
					invalidateOnRefresh: true,
					// Fix mobile: refresh après resize de la barre d'adresse
					onRefresh: (self) => {
						this.debugLog(widgetEl, 'ScrollTrigger refresh');
						// Recalculer la hauteur sur mobile
						if (window.innerWidth <= 767) {
							const newHeight = window.innerHeight + 'px';
							widgetEl.style.height = newHeight;
							// console.log('[NOVA Stacking Cards 2] MOBILE: Height refreshed to', newHeight);
						}
					},
					onEnter: () => {
						// console.log('[NOVA Stacking Cards 2] ScrollTrigger ENTER');
						this.debugLog(widgetEl, 'ScrollTrigger enter');
					},
					onLeave: () => {
						// console.log('[NOVA Stacking Cards 2] ScrollTrigger LEAVE');
						this.debugLog(widgetEl, 'ScrollTrigger leave');
						widgetEl.classList.add('is-complete');
					},
					onLeaveBack: () => {
						// console.log('[NOVA Stacking Cards 2] ScrollTrigger LEAVE BACK');
						this.debugLog(widgetEl, 'ScrollTrigger leave back');
						widgetEl.classList.remove('is-complete');
					},
					onUpdate: (self) => {
						if (window.innerWidth <= 767) {
							// console.log('[NOVA Stacking Cards 2] ScrollTrigger UPDATE - progress:', self.progress.toFixed(3));
						}
					}
				}
			});

			// console.log('[NOVA Stacking Cards 2] Timeline created, ScrollTrigger:', timeline.scrollTrigger);

			let timelinePosition = 0;
			const cardTimes = [0]; // First card visible from start

			// Calcul du décalage en pixels pour le stacking offset
			// Le CSS top est remplacé par GSAP y pour un contrôle total
			// Carte 0 → y: stickyTop
			// Carte 1 → y: stickyTop + 1 * stackOffset
			// Carte 2 → y: stickyTop + 2 * stackOffset
			const stackOffset = config.stacked ? config.stackOffset : 0;
			const stickyTop = config.stickyTop;

			// Build animation for each card transition
			// console.log('[NOVA Stacking Cards 2] Building card animations... stickyTop:', stickyTop, 'stackOffset:', stackOffset);
			for (let i = 1; i < totalCards; i++) {
				const currentCard = cards[i];

				// Position finale : stickyTop + i * stackOffset
				const finalY = stickyTop + i * stackOffset;

				timeline.to(currentCard, {
					yPercent: 0,
					y: finalY,
					duration: config.stepDuration
				}, timelinePosition);

				// console.log('[NOVA Stacking Cards 2] Card', i, '→ finalY:', finalY, '(stickyTop:', stickyTop, '+ i*stackOffset:', i * stackOffset, ')');
				timelinePosition += config.stepDuration;
				cardTimes[i] = timelinePosition;
			}

			// console.log('[NOVA Stacking Cards 2] Total timeline duration:', timelinePosition);
			// console.log('[NOVA Stacking Cards 2] cardTimes:', cardTimes);

			// Reveal wrapper so CSS knows JS is ready and takes over animation
			widgetEl.classList.add('nova-js-ready');
			// console.log('[NOVA Stacking Cards 2] Added class: nova-js-ready');

			// Mark as initialized
			widgetEl.dataset.novaStacking2Ready = '1';
			// console.log('[NOVA Stacking Cards 2] Marked as initialized');
			// console.log('[NOVA Stacking Cards 2] === MOBILE DEBUG END ===');

			// First card may be NOVA Title: we fade its title when the second card is almost stacked
			const firstCard = cards[0];
			const isNovaTitleFirstCard = firstCard && firstCard.classList.contains('nova-stacking-card-2--nova-title-replaced');
			const novaTitleContent = isNovaTitleFirstCard
				? firstCard.querySelector('.nova-stacking-card-2__nova-title-content')
				: null;
			if (novaTitleContent) {
				gsap.set(novaTitleContent, { opacity: 1 });
			}

			const entry = {
				timeline,
				cardTimes,
				cards,
				activeIndex: 0,
				novaTitleContent: novaTitleContent || null
			};

			this.timelines.set(widgetEl, entry);

			// Fade thresholds: when second card is "almost stacked" (progress 0.2 → 0.6), fade NOVA title out
			const novaFadeStart = 0.2;
			const novaFadeEnd = 0.6;

			// Sync active card state on scroll
			const syncActiveState = () => {
				const currentTime = timeline.time();
				let activeIndex = 0;

				for (let i = 0; i < cardTimes.length; i++) {
					if (currentTime >= cardTimes[i] - 0.01) {
						activeIndex = i;
					}
				}

				if (entry.activeIndex !== activeIndex) {
					entry.activeIndex = activeIndex;
					this.setActiveCard(cards, activeIndex);
				}

				// Fade NOVA Title content when second card is almost stacked on first card
				if (entry.novaTitleContent && totalCards >= 2 && cardTimes.length >= 2) {
					const stepDuration = cardTimes[1] - cardTimes[0];
					const progress = stepDuration > 0
						? Math.max(0, Math.min(1, (currentTime - cardTimes[0]) / stepDuration))
						: 0;
					let opacity = 1;
					if (progress <= novaFadeStart) {
						opacity = 1;
					} else if (progress >= novaFadeEnd) {
						opacity = 0;
					} else {
						opacity = 1 - (progress - novaFadeStart) / (novaFadeEnd - novaFadeStart);
					}
					gsap.set(entry.novaTitleContent, { opacity });
				}
			};

			timeline.eventCallback('onUpdate', syncActiveState);
			syncActiveState();

			// this.debugLog(widgetEl, 'Timeline créée et ScrollTrigger actif.', {
			// 	totalDuration: timelinePosition,
			// 	cardCount: totalCards,
			// 	totalTriggers: typeof ScrollTrigger !== 'undefined' ? ScrollTrigger.getAll().length : 'n/a'
			// });

			// Watch for images loading
			this.observeImages(widgetEl);

			// Register refresh callback
			const refreshHandler = () => this.debugLog(widgetEl, 'Refresh callback triggered');
			ScrollTrigger.addEventListener('refreshInit', refreshHandler);
			this.refreshCallbacks.set(widgetEl, refreshHandler);

			// Force refresh après un court délai (fix mobile)
			setTimeout(() => {
				ScrollTrigger.refresh();
			}, 300);
		},

		/**
		 * Handle single card
		 */
		handleSingleCard: function (card) {
			card.classList.add('is-active');
			card.style.position = 'relative';
			card.style.inset = 'auto';
		},

		/**
		 * Handle editor mode - show all cards as a static vertical list
		 * No GSAP pin/scroll so the editor layout stays intact
		 */
		handleEditorMode: function (widgetEl, cards) {
			widgetEl.classList.add('nova-stacking-cards-2--editor');
			// Override height so the editor doesn't show a 100vh black box
			widgetEl.style.height = 'auto';
			widgetEl.style.overflow = 'visible';

			const holder = widgetEl.querySelector(this.selectors.holder);
			if (holder) {
				holder.style.position = 'relative';
				holder.style.top = 'auto';
				holder.style.left = 'auto';
				holder.style.transform = 'none';
				holder.style.height = 'auto';
				holder.style.display = 'flex';
				holder.style.flexDirection = 'column';
				holder.style.gap = '24px';
			}

			cards.forEach((card) => {
				card.style.position = 'relative';
				card.style.inset = 'auto';
				card.style.transform = 'none';
				card.style.opacity = '1';
				card.style.height = 'auto';
				card.style.minHeight = 'var(--card-height, 500px)';
				card.classList.add('is-active');
			});

			widgetEl.classList.add('nova-js-ready');
		},

		/**
		 * Handle reduced motion / mobile - show all cards stacked
		 */
		handleReducedMotion: function (widgetEl, cards) {
			widgetEl.classList.add('nova-stacking-cards-2--reduced');
			cards.forEach((card) => {
				card.style.position = 'relative';
				card.style.inset = 'auto';
				card.style.transform = 'none';
				card.style.opacity = '1';
				card.classList.add('is-active');
			});
		},

		/**
		 * Set active card (for pointer-events and aria)
		 * No z-index manipulation - DOM order handles stacking
		 */
		setActiveCard: function (cards, index) {
			cards.forEach((card, idx) => {
				if (idx === index) {
					card.classList.add('is-active');
				} else {
					card.classList.remove('is-active');
				}
			});
		},

		/**
		 * Get configuration from data attributes
		 */
		getConfig: function (widgetEl) {
			const dataset = widgetEl.dataset;
			const vw = window.innerWidth;

			// Sélectionner la valeur de stack offset selon le breakpoint actuel
			// Breakpoints Elementor : mobile < 768, tablet 768–1024, desktop > 1024
			let stackOffset, stackOffsetUnit, stickyTop;
			if (vw < 768) {
				stackOffset = this.toFloat(dataset.stackOffsetMobile, this.toFloat(dataset.stackOffset, 0));
				stackOffsetUnit = dataset.stackOffsetUnitMobile || dataset.stackOffsetUnit || 'px';
				stickyTop = this.toFloat(dataset.stickyTopMobile, this.toFloat(dataset.stickyTop, 80));
			} else if (vw < 1025) {
				stackOffset = this.toFloat(dataset.stackOffsetTablet, this.toFloat(dataset.stackOffset, 0));
				stackOffsetUnit = dataset.stackOffsetUnitTablet || dataset.stackOffsetUnit || 'px';
				stickyTop = this.toFloat(dataset.stickyTopTablet, this.toFloat(dataset.stickyTop, 80));
			} else {
				stackOffset = this.toFloat(dataset.stackOffset, 0);
				stackOffsetUnit = dataset.stackOffsetUnit || 'px';
				stickyTop = this.toFloat(dataset.stickyTop, 80);
			}

			// Convertir en pixels si l'unité n'est pas px
			if (stackOffsetUnit === 'vh') {
				stackOffset = (window.innerHeight / 100) * stackOffset;
			}

			return {
				scrub: this.toFloat(dataset.scrub, 1.2),
				stepDuration: this.toFloat(dataset.stepDuration, 0.9),
				scrollMultiplier: this.toFloat(dataset.scrollMultiplier, 1.1),
				debug: dataset.debug === '1',
				stacked: dataset.stacked === '1',
				stackOffset: stackOffset,
				stackOffsetUnit: stackOffsetUnit,
				stickyTop: stickyTop
			};
		},

		/**
		 * Parse float with fallback
		 */
		toFloat: function (value, fallback) {
			const parsed = parseFloat(value);
			return Number.isFinite(parsed) ? parsed : fallback;
		},

		/**
		 * Watch images for loading and refresh ScrollTrigger
		 */
		observeImages: function (widgetEl) {
			const images = widgetEl.querySelectorAll('img');
			if (!images.length || typeof ScrollTrigger === 'undefined') {
				return;
			}

			let pending = images.length;
			const refresh = () => {
				pending -= 1;
				if (pending <= 0) {
					// this.debugLog(widgetEl, 'Toutes les images chargées → ScrollTrigger.refresh()');
					setTimeout(() => ScrollTrigger.refresh(), 60);
				}
			};

			images.forEach((img) => {
				if (img.complete) {
					refresh();
				} else {
					img.addEventListener('load', refresh, { once: true });
					img.addEventListener('error', refresh, { once: true });
				}
			});
		},

		/**
		 * Attach window resize handler
		 */
		attachResizeHandler: function () {
			$(window).on('resize', () => {
				clearTimeout(this.resizeTimer);
				this.resizeTimer = setTimeout(() => {
					this.handleResize();
				}, 120);
			});
		},

		/**
		 * Handle window resize
		 */
		handleResize: function () {
			if (typeof ScrollTrigger !== 'undefined') {
				ScrollTrigger.refresh();
			}

			if (window.NOVALenisScroll && NOVALenisScroll.lenis) {
				NOVALenisScroll.lenis.resize();
			}

			document.querySelectorAll(this.selectors.wrapper).forEach((wrapper) => {
				this.applyDisplayMode(wrapper);
			});

			document.querySelectorAll('.nova-stacking-cards-2-wrapper--mode-slider').forEach((wrapper) => {
				this.equalizeSc2SliderCardHeights(wrapper);
			});
		},

		/**
		 * Check if debug is enabled
		 */
		isDebugEnabled: function (widgetEl) {
			if (window.NOVAStackingCards2Debug === true) {
				return true;
			}
			if (widgetEl && widgetEl.dataset && widgetEl.dataset.debug === '1') {
				return true;
			}
			return false;
		},

		/**
		 * Debug log helper
		 * (désactivé en production : on ne fait plus de console.log ici)
		 */
		debugLog: function (widgetEl, message, payload) {
			// Intentionnellement vide pour éviter le bruit dans la console.
			return;
		}
	};

	// Initialize on DOM ready
	$(document).ready(() => {
		NOVAStackingCards2.onDocumentReady();
	});

	// Refresh ScrollTrigger on full page load
	window.addEventListener('load', () => {
		if (typeof ScrollTrigger !== 'undefined') {
			ScrollTrigger.refresh();
		}
	});

	// Expose globally
	window.NOVAStackingCards2 = NOVAStackingCards2;

})(jQuery);
