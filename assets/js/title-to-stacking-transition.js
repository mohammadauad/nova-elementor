/**
 * NOVA Title to Stacking Cards Transition
 * 
 * Animation spécifique pour les blocs contenant:
 * - Un widget nova-title (premier widget)
 * - Un widget nova-stacking-cards-2 (deuxième widget)
 * 
 * Comportement:
 * 1. Quand on arrive au bloc, fixer le scroll au premier widget (nova-title) pour 100vh
 * 2. Après 100vh de scroll, transition fade smooth vers le deuxième widget (nova-stacking-cards-2)
 */

(function ($) {
	'use strict';

	const NOVATitleToStackingTransition = {
		instances: new WeakMap(),
		debugPrefix: '[NOVA Title→Stacking Transition]',

		/**
		 * Entry point
		 */
		onDocumentReady: function () {
			if (!this.dependenciesReady()) {
				return;
			}

			this.bindElementor();
			this.init(document);
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

			// Écouter quand les widgets sont prêts
			elementorFrontend.hooks.addAction('frontend/element_ready/global', () => {
				setTimeout(() => {
					this.init(document);
					if (typeof ScrollTrigger !== 'undefined') {
						ScrollTrigger.refresh();
					}
				}, 100);
			});
		},

		/**
		 * Initialize transitions in context
		 */
		init: function (context) {
			const scope = context || document;
			const $scope = scope instanceof jQuery ? scope : $(scope);

			// Trouver tous les containers Elementor qui contiennent les deux widgets
			const containers = this.findTransitionContainers($scope);

			if (!containers.length) {
				this.debugLog(null, 'Aucun container de transition trouvé');
				return;
			}

			this.debugLog(null, `Trouvé ${containers.length} container(s) de transition`);

			containers.forEach((container) => {
				this.createTransition(container);
			});
		},

		/**
		 * Find containers that have both nova-title and nova-stacking-cards-2 widgets
		 */
		findTransitionContainers: function ($scope) {
			const containers = [];

			// Chercher tous les widgets nova-title
			const titleWidgets = $scope.find('.elementor-widget-nova-title').toArray();

			titleWidgets.forEach((titleWidget) => {
				// Vérifier si la transition est activée pour ce widget
				const $titleWidget = $(titleWidget);
				const titleWidgetElement = $titleWidget.find('.nova-title-widget').get(0);

				if (!titleWidgetElement) {
					return;
				}

				const stackingTransitionEnabled = titleWidgetElement.getAttribute('data-stacking-transition') === 'yes';

				if (!stackingTransitionEnabled) {
					// La transition n'est pas activée pour ce widget
					return;
				}

				// Trouver le container parent Elementor (e-con)
				const container = $titleWidget.closest('.e-con').get(0);

				if (!container) {
					return;
				}

				// Vérifier si ce container contient aussi un widget nova-stacking-cards-2
				const stackingWidget = $(container).find('.elementor-widget-nova-stacking-cards-2').get(0);

				if (stackingWidget) {
					// Vérifier que ce container n'a pas déjà été initialisé
					if (!this.instances.has(container)) {
						containers.push({
							container: container,
							titleWidget: titleWidgetElement,
							stackingWidget: stackingWidget
						});
					}
				} else {
					// Widget title avec transition activée mais pas de stacking widget dans le container
					this.debugLog(container, 'Transition activée mais widget nova-stacking-cards-2 non trouvé dans le container');
				}
			});

			return containers;
		},

		/**
		 * Create transition animation for a container
		 */
		createTransition: function (config) {
			const { container, titleWidget, stackingWidget } = config;

			// Vérifier que les éléments existent
			if (!container || !titleWidget || !stackingWidget) {
				this.debugLog(container, 'Éléments manquants, abandon');
				return;
			}

			// Vérifier si déjà initialisé
			if (this.instances.has(container)) {
				this.debugLog(container, 'Transition déjà initialisée');
				return;
			}

			// Vérifier reduced motion
			const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			if (reducedMotion) {
				this.debugLog(container, 'Mode prefers-reduced-motion détecté → pas de transition');
				return;
			}

			// Mobile - pas de transition
			if (window.innerWidth <= 767) {
				this.debugLog(container, 'Mode mobile détecté → pas de transition');
				return;
			}

			this.debugLog(container, 'Création de la transition');

			// Préparer les éléments pour l'animation
			const $titleWidget = $(titleWidget);
			const $stackingWidget = $(stackingWidget);
			const $container = $(container);

			// S'assurer que le container a une position relative
			$container.css({
				position: 'relative',
				minHeight: '200vh' // Au moins 200vh pour permettre le scroll
			});

			// Initialiser les états
			// Le widget title est visible au début
			gsap.set($titleWidget, {
				opacity: 1,
				visibility: 'visible',
				zIndex: 2
			});

			// Le widget stacking est caché au début
			gsap.set($stackingWidget, {
				opacity: 0,
				visibility: 'hidden',
				zIndex: 1
			});

			// Créer la timeline avec ScrollTrigger
			// On pin le widget title pour fixer la section pendant 100vh
			// Le widget title reste fixe pendant le scroll, puis on fait la transition fade
			const timeline = gsap.timeline({
				scrollTrigger: {
					trigger: container,
					start: 'top top',
					end: '+=100vh', // 100vh de scroll
					scrub: 1.2, // Smooth scrubbing
					pin: $titleWidget[0], // Pinner le widget title (premier widget)
					pinSpacing: true,
					anticipatePin: 1,
					markers: false, // Mettre à true pour debug
					invalidateOnRefresh: true,
					onEnter: () => {
						this.debugLog(container, 'ScrollTrigger enter - Title visible');
					},
					onUpdate: (self) => {
						const progress = self.progress;
						// Transition fade smooth pendant le scroll
						// À 0%: title visible (opacity 1), stacking caché (opacity 0)
						// À 100%: title caché (opacity 0), stacking visible (opacity 1)

						// Transition progressive sur toute la durée
						// Utiliser une courbe d'easing pour une transition plus smooth
						const easeProgress = this.easeInOutCubic(progress);
						const titleOpacity = 1 - easeProgress;
						const stackingOpacity = easeProgress;

						// Appliquer les opacités avec une transition smooth
						gsap.set($titleWidget, {
							opacity: titleOpacity,
							visibility: titleOpacity > 0.01 ? 'visible' : 'hidden',
							pointerEvents: titleOpacity > 0.01 ? 'auto' : 'none'
						});

						gsap.set($stackingWidget, {
							opacity: stackingOpacity,
							visibility: stackingOpacity > 0.01 ? 'visible' : 'hidden',
							pointerEvents: stackingOpacity > 0.01 ? 'auto' : 'none'
						});
					},
					onLeave: () => {
						this.debugLog(container, 'ScrollTrigger leave - Stacking visible');
						// S'assurer que le stacking est visible après la transition
						gsap.set($titleWidget, {
							opacity: 0,
							visibility: 'hidden',
							pointerEvents: 'none'
						});
						gsap.set($stackingWidget, {
							opacity: 1,
							visibility: 'visible',
							pointerEvents: 'auto'
						});
					},
					onLeaveBack: () => {
						this.debugLog(container, 'ScrollTrigger leave back - Title visible');
						// S'assurer que le title est visible quand on revient en arrière
						gsap.set($titleWidget, {
							opacity: 1,
							visibility: 'visible',
							pointerEvents: 'auto'
						});
						gsap.set($stackingWidget, {
							opacity: 0,
							visibility: 'hidden',
							pointerEvents: 'none'
						});
					}
				}
			});

			// Stocker l'instance
			this.instances.set(container, {
				timeline: timeline,
				titleWidget: titleWidget,
				stackingWidget: stackingWidget
			});

			this.debugLog(container, 'Transition créée avec succès');
		},

		/**
		 * Easing function for smooth transition (cubic ease-in-out)
		 */
		easeInOutCubic: function (t) {
			return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
		},

		/**
		 * Check if debug is enabled
		 */
		isDebugEnabled: function (element) {
			if (window.NOVATitleToStackingDebug === true) {
				return true;
			}
			return false;
		},

		/**
		 * Debug log helper
		 */
		debugLog: function (element, message, payload) {
			if (!this.isDebugEnabled(element)) {
				return;
			}
			if (!window.console || typeof console.log !== 'function') {
				return;
			}
			const elementId = element && element.id ? '#' + element.id : '';
			if (payload !== undefined) {
				console.log(this.debugPrefix + (elementId ? ' ' + elementId : ''), message, payload);
			} else {
				console.log(this.debugPrefix + (elementId ? ' ' + elementId : ''), message);
			}
		}
	};

	// Initialize on DOM ready
	$(document).ready(() => {
		NOVATitleToStackingTransition.onDocumentReady();
	});

	// Refresh ScrollTrigger on full page load
	window.addEventListener('load', () => {
		if (typeof ScrollTrigger !== 'undefined') {
			setTimeout(() => {
				NOVATitleToStackingTransition.init(document);
				ScrollTrigger.refresh();
			}, 200);
		}
	});

	// Expose globally
	window.NOVATitleToStackingTransition = NOVATitleToStackingTransition;

})(jQuery);
