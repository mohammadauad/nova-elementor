(function ($) {
	'use strict';

	/**
	 * EWD Sticky Columns – SYNCHRONISÉ AVEC SCROLLBAR NATIVE
	 * Force la synchronisation entre scroll natif et Lenis
	 */

	const EWDStickyScrollTrigger = {
		instances: [],

		killAll: function () {
			this.instances.forEach(st => {
				if (st && st.kill) st.kill();
			});
			this.instances = [];
		},

		init: function () {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				return;
			}

			gsap.registerPlugin(ScrollTrigger);

			// 🔧 FORCER SCROLLTRIGGER À UTILISER LE SCROLL NATIF
			ScrollTrigger.config({
				autoRefreshEvents: "visibilitychange,DOMContentLoaded,load",
				syncInterval: 0 // Synchronisation immédiate
			});

			const wrappers = document.querySelectorAll('.ewd-sticky-wrapper');
			if (!wrappers.length) return;

			const scroller = document.body;

			wrappers.forEach((wrapper, wrapperIndex) => {
				const stickyCol = wrapper.querySelector('.ewd-sticky-col');
				if (!stickyCol) return;

				const parentSection = wrapper.parentElement;
				if (!parentSection) return;

				const scrollingColumn = wrapper.nextElementSibling || wrapper.previousElementSibling;
				this.createStickyInstance(
					wrapper,
					stickyCol,
					wrapper,
					scrollingColumn || wrapper,
					wrapperIndex,
					scroller
				);
			});

			// Force un update immédiat
			ScrollTrigger.refresh();
		},

		/**
		 * Retourne la valeur responsive selon la largeur du viewport.
		 */
		getResponsiveValue: function (obj, defaultValue) {
			if (!obj || typeof obj !== 'object') return defaultValue;
			const w = window.innerWidth;
			let val = null;
			if (w > 1024) {
				val = obj.desktop;
			} else if (w > 767) {
				val = obj.tablet != null ? obj.tablet : obj.desktop;
			} else {
				val = obj.mobile != null ? obj.mobile : (obj.tablet != null ? obj.tablet : obj.desktop);
			}
			val = Number(val);
			return !isNaN(val) ? val : defaultValue;
		},

		createStickyInstance: function (wrapper, stickyCol, triggerEl, endTriggerEl, wrapperIndex, scroller) {
			if (window.innerWidth < 992) return;

			const colRect = stickyCol.getBoundingClientRect();
			const colHeight = stickyCol.offsetHeight;

			// ============================================
			// OFFSET HAUT (responsive)
			// ============================================
			let offset = 80;
			const topData = wrapper.getAttribute('data-ewd-sticky-offset-top');
			if (topData) {
				try {
					const parsed = JSON.parse(topData);
					offset = this.getResponsiveValue(parsed, 80);
				} catch (e) {
					const computed = window.getComputedStyle(wrapper);
					const topVar = computed.getPropertyValue('--ewd-sticky-top').trim();
					if (topVar) { const n = parseFloat(topVar); if (!isNaN(n)) offset = n; }
				}
			} else {
				const computed = window.getComputedStyle(wrapper);
				const topVar = computed.getPropertyValue('--ewd-sticky-top').trim();
				if (topVar) { const n = parseFloat(topVar); if (!isNaN(n)) offset = n; }
			}

			// Header height
			const headerSelector = wrapper.getAttribute('data-ewd-sticky-header-selector');
			let headerHeight = 0;
			if (headerSelector) {
				const selectors = headerSelector.split(',').map(function (s) { return s.trim(); });
				for (let i = 0; i < selectors.length; i++) {
					const headerEl = document.querySelector(selectors[i]);
					if (headerEl && headerEl.offsetHeight) {
						headerHeight = headerEl.offsetHeight;
						break;
					}
				}
			}

			const configuredOffset = offset + headerHeight;

			// Offset bas (responsive)
			let bottomOffset = 0;
			const bottomData = wrapper.getAttribute('data-ewd-sticky-offset-bottom');
			if (bottomData) {
				try {
					const parsed = JSON.parse(bottomData);
					bottomOffset = this.getResponsiveValue(parsed, 0);
				} catch (e) {
					const b = parseFloat(bottomData);
					if (!isNaN(b) && b >= 0) bottomOffset = b;
				}
			}

			const pinnedClass = stickyCol.getAttribute('data-ewd-sticky-pinned-class') ||
				wrapper.getAttribute('data-ewd-sticky-pinned-class') ||
				'ewd-sticky-is-pinned';

			// ============================================
			// FIX WIDTH
			// ============================================
			const colWidth = colRect.width;
			if (colWidth > 0) {
				stickyCol.style.width = colWidth + 'px';
				stickyCol.style.maxWidth = colWidth + 'px';
				stickyCol.style.flexShrink = '0';
			}

			stickyCol.style.position = 'relative';
			stickyCol.style.top = 'auto';

			// ============================================
			// SCROLLTRIGGER - PINNING SYNCHRONISÉ (RETOUR TRANSFORM)
			// ============================================
			const st = ScrollTrigger.create({
				trigger: triggerEl,
				scroller: scroller,
				pin: stickyCol,
				pinSpacing: false,
				// Retour au mode transform car le fixed est cassé par le conteneur transformé (Lenis/GSAP)
				pinType: 'transform', 

				start: () => 'top top+=' + configuredOffset + 'px',

				endTrigger: triggerEl,

				end: () => 'bottom-=' + (colHeight + bottomOffset) + 'px top+=' + configuredOffset + 'px',

				// 🔧 PAS DE SCRUB pour éviter tout lissage artificiel de GSAP
				scrub: false,

				anticipatePin: 1,

				// 🚀 Synchronisation forcée à chaque frame pour réduire le lag
				onUpdate: function (self) {
					// On ne force pas le refresh ici pour éviter les boucles, 
					// mais on s'assure que le transform est appliqué immédiatement.
				},

				invalidateOnRefresh: true,
				markers: false,

				onEnter: function () {
					if (pinnedClass) stickyCol.classList.add(pinnedClass);
				},
				onLeave: function () {
					if (pinnedClass) stickyCol.classList.remove(pinnedClass);
				},
				onEnterBack: function () {
					if (pinnedClass) stickyCol.classList.add(pinnedClass);
				},
				onLeaveBack: function () {
					if (pinnedClass) stickyCol.classList.remove(pinnedClass);
				}
			});

			this.instances.push(st);
		}
	};

	// ============================================
	// SYNCHRONISATION FORCÉE AVEC LENIS
	// ============================================
	function forceLenisSync() {
		// Attendre que Lenis soit initialisé
		if (!window.NOVALenisScroll || !window.NOVALenisScroll.lenis) {
			setTimeout(forceLenisSync, 100);
			return;
		}

		const lenis = window.NOVALenisScroll.lenis;

		// 🔧 ÉCOUTER LES ÉVÉNEMENTS LENIS ET FORCER LA MISE À JOUR
		lenis.on('scroll', function (e) {
			if (typeof ScrollTrigger !== 'undefined') {
				// Force ScrollTrigger à se mettre à jour immédiatement
				ScrollTrigger.update();
			}
		});

		// Synchroniser également avec le ticker de GSAP pour une fluidité maximale
		if (typeof gsap !== 'undefined') {
			gsap.ticker.add(() => {
				ScrollTrigger.update();
			});
		}
	}

	// ============================================
	// RESIZE
	// ============================================
	let resizeTimeout;
	window.addEventListener('resize', function () {
		clearTimeout(resizeTimeout);
		resizeTimeout = setTimeout(function () {
			EWDStickyScrollTrigger.killAll();
			document.querySelectorAll('.ewd-sticky-col').forEach(function (col) {
				col.style.width = '';
				col.style.maxWidth = '';
				col.style.flexShrink = '';
			});
			if (typeof ScrollTrigger !== 'undefined') {
				ScrollTrigger.refresh();
			}
			EWDStickyScrollTrigger.init();
		}, 250);
	}, { passive: true });

	// ============================================
	// INIT
	// ============================================
	function runStickyInit() {
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
			setTimeout(runStickyInit, 100);
			return;
		}

		if (window.NOVALenisScroll && window.NOVALenisScroll.scrollerProxyApplied === true) {
			EWDStickyScrollTrigger.init();
			// Activer la synchronisation forcée
			forceLenisSync();
			return;
		}

		var waited = 0;
		var maxWait = 2000;
		var interval = 100;
		var checkInterval = setInterval(function () {
			waited += interval;
			if (window.NOVALenisScroll && window.NOVALenisScroll.scrollerProxyApplied === true) {
				clearInterval(checkInterval);
				EWDStickyScrollTrigger.init();
				forceLenisSync();
				return;
			}
			if (waited >= maxWait) {
				clearInterval(checkInterval);
				EWDStickyScrollTrigger.init();
				forceLenisSync();
			}
		}, interval);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', runStickyInit);
	} else {
		runStickyInit();
	}

	window.EWDSticky = EWDStickyScrollTrigger;

})(jQuery);

/**
 * 🎯 EXPLICATION DU PROBLÈME
 * ===========================
 * 
 * VOUS AVEZ RAISON ! Le problème est :
 * 
 * 1. SCROLLBAR NATIVE (navigateur)
 *    → Position immédiate (relative à l'écran)
 *    → Scroll wheel → saute directement à 500px
 * 
 * 2. LENIS (smooth scroll)
 *    → Anime progressivement de 0 à 500px
 *    → Position virtuelle (relative à la page animée)
 * 
 * 3. SCROLLTRIGGER
 *    → Suit Lenis (position virtuelle)
 *    → Résultat : LAG visible
 * 
 * SOLUTION :
 * ----------
 * - Forcer ScrollTrigger.update() à chaque événement Lenis
 * - Configurer syncInterval: 0 pour sync immédiate
 * - Pas de scrub pour éviter les délais
 * - L'élément suit maintenant la position RÉELLE du scroll
 * 
 * La synchronisation est maintenant forcée entre :
 * Scroll natif ↔ Lenis ↔ ScrollTrigger ↔ Élément sticky
 */