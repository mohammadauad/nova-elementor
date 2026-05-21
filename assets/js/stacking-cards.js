(function ($) {
	'use strict';

	const NOVAStackingCards = {
		selectors: {
			widget: '.nova-stacking-cards',
			card: '.nova-stacking-card',
			nav: '.nova-stacking-cards-nav',
			navButton: '.nova-stacking-cards-nav-button'
		},
		timelines: new WeakMap(),
		refreshCallbacks: new WeakMap(),
		resizeTimer: null,
		debugPrefix: '[NOVA Stacking Cards]',

		onDocumentReady: function () {
			if (window.NOVA_NATIVE_SCROLL_MODE === true) {
				return;
			}
			if (!this.dependenciesReady()) {
				return;
			}

			this.bindElementor();
			this.init(document);
			this.attachResizeHandler();
		},

		dependenciesReady: function () {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				this.debugLog(null, 'GSAP ou ScrollTrigger manquant → arrêt.');
				return false;
			}
			gsap.registerPlugin(ScrollTrigger);
			this.debugLog(null, 'Dépendances GSAP détectées, initialisation...');
			return true;
		},

		bindElementor: function () {
			if (typeof elementorFrontend === 'undefined' || !elementorFrontend.hooks) {
				return;
			}

			elementorFrontend.hooks.addAction('frontend/element_ready/nova-stacking-cards.default', ($scope) => {
				this.init($scope[0]);
			});

			// Refresh ScrollTrigger when panel updates controls.
			elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
				if (typeof ScrollTrigger !== 'undefined') {
					setTimeout(() => ScrollTrigger.refresh(), 50);
				}
			});
		},

		init: function (context) {
			return; // TEMPORARILY DISABLED TO DEBUG SCROLL
			const scope = context || document;
			const $scope = scope instanceof jQuery ? scope : $(scope);
			let widgets = $scope.find(this.selectors.widget).toArray();

			// When scope itself is the widget.
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

		createInstance: function (widgetEl) {
			if (!widgetEl || widgetEl.dataset.NOVAStackingReady === '1') {
				this.debugLog(widgetEl, 'Instance déjà initialisée, on ignore.');
				return;
			}

			const cards = widgetEl.querySelectorAll(this.selectors.card);
			if (!cards.length) {
				this.debugLog(widgetEl, 'Aucune carte détectée, abandon.');
				return;
			}

			this.applySizeVariables(widgetEl);
			this.debugLog(widgetEl, 'Variables CSS appliquées après initialisation.');

			const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			if (reducedMotion) {
				this.debugLog(widgetEl, 'Mode prefers-reduced-motion détecté → fallback statique.');
				this.handleReducedMotion(widgetEl, cards);
				widgetEl.dataset.NOVAStackingReady = '1';
				return;
			}

			if (cards.length === 1) {
				this.debugLog(widgetEl, 'Une seule carte détectée → aucun scroll nécessaire.');
				this.handleSingleCard(cards[0]);
				widgetEl.dataset.NOVAStackingReady = '1';
				return;
			}

			const config = this.getConfig(widgetEl);
			this.debugLog(widgetEl, 'Configuration extraite', config);

			const wrapper = widgetEl.closest('.nova-stacking-cards-wrapper');
			const navElement = wrapper ? wrapper.querySelector(this.selectors.nav) : null;
			const navButtons = navElement ? Array.from(navElement.querySelectorAll(this.selectors.navButton)) : [];
			const firstCard = cards.length ? cards[0] : null;
			const firstCardContent = firstCard ? firstCard.querySelector('.nova-stacking-card__content') : null;

			if (navButtons.length) {
				navButtons.forEach((button) => {
					button.addEventListener('click', (event) => {
						event.preventDefault();
						const targetIndex = parseInt(button.dataset.navIndex, 10);
						if (!Number.isNaN(targetIndex)) {
							this.scrollToCard(widgetEl, targetIndex);
						}
					});
				});
			}

			const firstExpandEnabled = !!config.firstExpandEnabled;
			const firstInitialScale = firstExpandEnabled ? (config.firstInitialScale || 0.7) : 1;

			if (firstExpandEnabled) {
				if (firstCardContent) {
					gsap.set(firstCardContent, { opacity: 0 });
				}
				if (navElement) {
					gsap.set(navElement, { opacity: 0 });
				}
			}

			cards.forEach((card, index) => {
				if (index === 0) {
					card.classList.add('is-active');
					const initialStyles = {
						yPercent: 0,
						opacity: 1,
						scale: firstInitialScale,
						transformOrigin: 'center center'
					};

					gsap.set(card, initialStyles);
				} else {
					card.classList.remove('is-active');
					gsap.set(card, {
						yPercent: 110,
						opacity: 0,
						scale: 1
					});
				}
			});

			const scrollDistanceFn = () => {
				const distance = Math.max(this.computeScrollDistance(widgetEl, cards.length, config), 100);
				this.debugLog(widgetEl, 'Distance de scroll calculée', {
					distance,
					cardCount: cards.length
				});
				return '+=' + distance;
			};

			const timeline = gsap.timeline({
				defaults: {
					ease: config.ease
				},
				scrollTrigger: {
					trigger: widgetEl,
					start: 'top top',
					end: scrollDistanceFn,
					scrub: config.scrub,
					pin: true,
					pinSpacing: true,
					pinType: 'fixed',
					anticipatePin: 1,
					markers: config.debug,
					invalidateOnRefresh: true,
					onRefreshInit: () => {
						this.debugLog(widgetEl, 'ScrollTrigger refresh init');
						this.applySizeVariables(widgetEl);
					},
					onRefresh: () => {
						this.debugLog(widgetEl, 'ScrollTrigger refresh');
						this.applySizeVariables(widgetEl);
					},
					onEnter: () => {
						this.debugLog(widgetEl, 'ScrollTrigger enter');
						this.applySizeVariables(widgetEl);
					},
					onEnterBack: () => {
						this.debugLog(widgetEl, 'ScrollTrigger enter back');
						this.applySizeVariables(widgetEl);
					},
					onLeave: () => {
						this.debugLog(widgetEl, 'ScrollTrigger leave');
						this.markCompleted(widgetEl);
					},
					onLeaveBack: () => {
						this.debugLog(widgetEl, 'ScrollTrigger leave back');
						this.removeCompleted(widgetEl);
					}
				}
			});

			let timelinePosition = 0;
			const cardTimes = [];

			if (firstCard && firstExpandEnabled && config.firstCardExpandDuration > 0) {
				timeline.to(firstCard, {
					scale: 1,
					duration: config.firstCardExpandDuration,
					ease: config.ease
				}, timelinePosition);
				timelinePosition += config.firstCardExpandDuration;

				if (firstCardContent) {
					timeline.to(firstCardContent, {
						opacity: 1,
						duration: 0.3,
						ease: 'power1.out'
					}, timelinePosition);
				}

				if (navElement) {
					timeline.to(navElement, {
						opacity: 1,
						duration: 0.3,
						ease: 'power1.out'
					}, timelinePosition);
				}

				timelinePosition += 0.3;
				cardTimes[0] = timelinePosition;
			} else {
				if (firstCardContent) {
					gsap.set(firstCardContent, { opacity: 1 });
				}
				if (navElement) {
					gsap.set(navElement, { opacity: 1 });
				}
				cardTimes[0] = 0;
			}

			for (let index = 1; index < cards.length; index++) {
				const card = cards[index];
				const previousCard = cards[index - 1];

				timeline.to(card, {
					yPercent: 0,
					opacity: 1,
					duration: config.stepDuration
				}, timelinePosition);

				timeline.to(previousCard, {
					y: -config.previousLift,
					scale: 1,
					opacity: 1,
					duration: config.stepDuration
				}, timelinePosition);

				timelinePosition += config.stepDuration;
				cardTimes[index] = timelinePosition;
			}

			timeline.add(() => this.applySizeVariables(widgetEl), timelinePosition);

			this.observeImages(widgetEl);

			widgetEl.classList.add('nova-js-ready');
			widgetEl.dataset.NOVAStackingReady = '1';

			const entry = {
				timeline,
				cardTimes,
				navButtons,
				cards,
				activeIndex: -1
			};

			this.timelines.set(widgetEl, entry);

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
					this.setActiveCard(widgetEl, cards, activeIndex, navButtons);
				}
			};

			timeline.eventCallback('onUpdate', syncActiveState);
			syncActiveState();

			this.debugLog(widgetEl, 'Timeline créée et ScrollTrigger actif.', {
				totalDuration: timelinePosition,
				cardCount: cards.length,
				totalTriggers: typeof ScrollTrigger !== 'undefined' ? ScrollTrigger.getAll().length : 'n/a'
			});

			const refreshHandler = () => this.applySizeVariables(widgetEl);
			ScrollTrigger.addEventListener('refreshInit', refreshHandler);
			this.refreshCallbacks.set(widgetEl, refreshHandler);
		},

		handleSingleCard: function (card) {
			card.classList.add('is-active');
			card.style.position = 'relative';
			card.style.inset = 'auto';
		},

		handleReducedMotion: function (widgetEl, cards) {
			widgetEl.classList.add('nova-stacking-cards--reduced');
			cards.forEach((card) => {
				card.style.position = 'relative';
				card.style.inset = 'auto';
				card.style.transform = 'none';
				card.style.opacity = '1';
				card.classList.add('is-active');
			});
		},

		setActiveCard: function (widgetEl, cards, index, navButtons) {
			cards.forEach((card, idx) => {
				if (idx === index) {
					card.classList.add('is-active');
				} else {
					card.classList.remove('is-active');
				}
			});

			if (navButtons && navButtons.length) {
				navButtons.forEach((button, idx) => {
					if (idx === index) {
						button.classList.add('is-active');
						button.setAttribute('aria-current', 'true');
					} else {
						button.classList.remove('is-active');
						button.removeAttribute('aria-current');
					}
				});
			}

			this.debugLog(widgetEl, 'Carte active mise à jour', { index });
		},

		scrollToCard: function (widgetEl, index) {
			const entry = this.timelines.get(widgetEl);
			if (!entry || !entry.timeline) {
				return;
			}

			const { timeline, cardTimes, navButtons, cards } = entry;
			if (typeof index !== 'number' || index < 0 || index >= cardTimes.length) {
				return;
			}

			const duration = timeline.duration();
			if (!duration) {
				return;
			}

			const targetTime = cardTimes[index];
			const targetProgress = duration === 0 ? 0 : targetTime / duration;
			const st = timeline.scrollTrigger;

			if (st && typeof st.start === 'number' && typeof st.end === 'number') {
				const targetScroll = st.start + (st.end - st.start) * targetProgress;
				if (window.NOVALenisScroll && NOVALenisScroll.lenis) {
					NOVALenisScroll.lenis.scrollTo(targetScroll, { duration: 1 });
				} else {
					window.scrollTo({ top: targetScroll, behavior: 'smooth' });
				}
			} else {
				timeline.tweenTo(targetTime);
			}

			entry.activeIndex = index;
			this.setActiveCard(widgetEl, cards, index, navButtons);

			this.debugLog(widgetEl, 'Navigation vers la carte', { index, targetTime, targetProgress });
		},

		markCompleted: function (widgetEl) {
			widgetEl.classList.add('is-complete');
		},

		removeCompleted: function (widgetEl) {
			widgetEl.classList.remove('is-complete');
		},

		computeScrollDistance: function (widgetEl, cardsCount, config) {
			const stackHeight = Math.max(this.getStackHeightPx(widgetEl), window.innerHeight);
			const cardsDistance = Math.max(cardsCount - 1, 1) * stackHeight * config.scrollMultiplier;
			const extra = this.convertToPixels(config.additionalVH, 'vh');
			return cardsDistance + extra;
		},

		getConfig: function (widgetEl) {
			const dataset = widgetEl.dataset;
			return {
				scrub: this.toFloat(dataset.scrub, 1.2),
				stepDuration: this.toFloat(dataset.stepDuration, 0.9),
				scrollMultiplier: this.toFloat(dataset.scrollMultiplier, 1.1),
				previousLift: this.toFloat(dataset.previousLift, 60),
				previousScale: this.toFloat(dataset.previousScale, 0.94),
				previousOpacity: this.toFloat(dataset.previousOpacity, 0.6),
				additionalVH: this.toFloat(dataset.additionalVh, 10),
				firstInitialScale: this.toFloat(dataset.firstInitialScale, 0.7),
				firstCardExpandDuration: this.toFloat(dataset.firstExpandDuration, 0.8),
				firstExpandEnabled: dataset.firstExpandEnabled === '1',
				ease: dataset.animationEase || 'power2.out',
				debug: dataset.debug === '1'
			};
		},

		toFloat: function (value, fallback) {
			const parsed = parseFloat(value);
			return Number.isFinite(parsed) ? parsed : fallback;
		},

		getStackHeightPx: function (widgetEl) {
			const rect = widgetEl.getBoundingClientRect();
			if (rect && rect.height) {
				return rect.height;
			}

			const dimension = this.getResponsiveDimension(widgetEl, 'stackHeight');
			if (!dimension) {
				return window.innerHeight;
			}

			return this.convertToPixels(dimension.value, dimension.unit);
		},

		getResponsiveDimension: function (widgetEl, axis) {
			if (!widgetEl || !widgetEl.dataset) {
				return null;
			}

			const dataset = widgetEl.dataset;
			const viewport = window.innerWidth;

			let value = dataset[axis + 'Value'];
			let unit = dataset[axis + 'Unit'];

			if (viewport <= 767 && dataset[axis + 'MobileValue']) {
				value = dataset[axis + 'MobileValue'];
				unit = dataset[axis + 'MobileUnit'] || unit;
			} else if (viewport <= 1024 && dataset[axis + 'TabletValue']) {
				value = dataset[axis + 'TabletValue'];
				unit = dataset[axis + 'TabletUnit'] || unit;
			}

			if (value === undefined || value === null || value === '') {
				return null;
			}

			return {
				value: parseFloat(value),
				unit: unit || 'px'
			};
		},

		applySizeVariables: function (widgetEl) {
			const width = this.getResponsiveDimension(widgetEl, 'stackWidth');
			const height = this.getResponsiveDimension(widgetEl, 'stackHeight');

			if (width && Number.isFinite(width.value)) {
				widgetEl.style.setProperty('--stack-width', width.value + width.unit);
			}

			if (height && Number.isFinite(height.value)) {
				widgetEl.style.setProperty('--stack-height', height.value + height.unit);
			}

			// Re-apply Elementor CSS custom properties for navigation and icons
			this.reapplyElementorStyles(widgetEl);
		},

		reapplyElementorStyles: function (widgetEl) {
			// Force browser to recalculate Elementor inline CSS for navigation controls
			const wrapper = widgetEl.closest('.elementor-widget-nova-stacking-cards');
			if (!wrapper) {
				return;
			}

			// Force recalculation for navigation elements
			const navList = widgetEl.querySelector('.nova-stacking-cards-nav-list');
			const navButtons = widgetEl.querySelectorAll('.nova-stacking-cards-nav-button');
			const navIcons = widgetEl.querySelectorAll('.nova-stacking-cards-nav-icon');

			// Force reflow by reading computed styles
			if (navList) {
				void navList.offsetHeight;
				const computed = window.getComputedStyle(navList);
				// Re-read critical properties to ensure they're applied
				void computed.gap;
				void computed.flexDirection;
				void computed.justifyContent;
				void computed.alignItems;
			}

			if (navButtons.length) {
				navButtons.forEach(button => {
					void button.offsetHeight;
					const computed = window.getComputedStyle(button);
					// Re-read critical properties
					void computed.padding;
					void computed.backgroundColor;
					void computed.color;
					void computed.borderRadius;
				});
			}

			if (navIcons.length) {
				navIcons.forEach(icon => {
					void icon.offsetHeight;
					const computed = window.getComputedStyle(icon);
					void computed.filter;
				});
			}

			this.debugLog(widgetEl, 'Styles Elementor ré-appliqués pour la navigation');
		},

		convertToPixels: function (value, unit) {
			if (!Number.isFinite(value)) {
				return 0;
			}

			switch (unit) {
				case 'vh':
					return (value / 100) * window.innerHeight;
				case 'vw':
					return (value / 100) * window.innerWidth;
				case '%':
					return (value / 100) * window.innerHeight;
				default:
					return value;
			}
		},

		observeImages: function (widgetEl) {
			const images = widgetEl.querySelectorAll('img');
			if (!images.length || typeof ScrollTrigger === 'undefined') {
				return;
			}

			let pending = images.length;
			this.debugLog(widgetEl, 'Observation des images pour rafraîchir ScrollTrigger', { pending });
			const refresh = () => {
				pending -= 1;
				this.debugLog(widgetEl, 'Image chargée → pending', { pending });
				if (pending <= 0) {
					this.debugLog(widgetEl, 'Toutes les images sont chargées → ScrollTrigger.refresh()');
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

		attachResizeHandler: function () {
			$(window).on('resize', () => {
				clearTimeout(this.resizeTimer);
				this.resizeTimer = setTimeout(() => {
					this.handleResize();
				}, 120);
			});
		},

		handleResize: function () {
			const widgets = document.querySelectorAll(this.selectors.widget);
			widgets.forEach((widget) => {
				this.applySizeVariables(widget);
			});

			if (typeof ScrollTrigger !== 'undefined') {
				this.debugLog(null, 'Fenêtre redimensionnée → ScrollTrigger.refresh()');
				ScrollTrigger.refresh();
			}

			if (window.NOVALenisScroll && NOVALenisScroll.lenis) {
				this.debugLog(null, 'Fenêtre redimensionnée → Lenis.resize()');
				NOVALenisScroll.lenis.resize();
			}
		},

		isDebugEnabled: function (widgetEl) {
			if (window.NOVAStackingCardsDebug === true) {
				return true;
			}

			if (widgetEl && widgetEl.dataset && widgetEl.dataset.debug === '1') {
				return true;
			}

			return false;
		},

		debugLog: function (widgetEl, message, payload) {
			if (!this.isDebugEnabled(widgetEl)) {
				return;
			}

			if (!window.console || typeof console.log !== 'function') {
				return;
			}

			const widgetId = widgetEl && widgetEl.id ? '#' + widgetEl.id : '';
			if (payload !== undefined) {
			} else {
			}
		}
	};

	$(document).ready(() => {
		NOVAStackingCards.onDocumentReady();
	});

	window.addEventListener('load', () => {
		if (typeof ScrollTrigger !== 'undefined') {
			ScrollTrigger.refresh();
		}
	});

	window.NOVAStackingCards = NOVAStackingCards;

})(jQuery);

