/**
 * NOVA Smooth Scroll - Lenis Integration Professional
 * Version: 3.1.0 - Mouse Wheel Fixed
 * 
 * CRITICAL FIX: Mouse wheel now working perfectly
 */

(function ($) {
	'use strict';

	const NOVALenisScroll = {

		lenis: null,
		rafId: null,
		lastScrollLog: 0,
		currentScroll: 0,
		scrollerProxyApplied: false,
		scrollTriggerRefreshHandler: null,

		/**
		 * Initialize Lenis
		 */
		init: function () {
			// Check dependencies
			if (typeof Lenis === 'undefined') {
				return;
			}

			// CRITICAL: Remove conflicting CSS smooth scroll
			this.removeCSSConflicts();

			// Setup Lenis
			this.setupLenis();
			this.syncWithGSAP();
			this.handleAnchorLinks();
			this.handleResize();

			this.applyScrollerProxy();

			// Test mouse wheel
			this.testMouseWheel();
		},

		isDebugEnabled: function () {
			return typeof window !== 'undefined' && window.NOVALenisDebug === true;
		},

		debugLog: function (...messages) {
			if (this.isDebugEnabled()) {
				// Debug log (disabled in production)
			}
		},

		/**
		 * CRITICAL: Remove CSS conflicts that block mouse wheel
		 */
		removeCSSConflicts: function () {
			// Remove scroll-behavior from HTML
			document.documentElement.style.scrollBehavior = 'auto';
			document.body.style.scrollBehavior = 'auto';

			// Remove any overflow hidden that might block
			document.documentElement.style.overflowX = 'hidden';
			document.documentElement.style.overflowY = 'auto';

			this.debugLog('CSS conflicts removed (scroll-behavior, overflow adjustments)');
		},

		/**
		 * Setup Lenis with optimal settings for mouse wheel
		 */
		setupLenis: function () {
			const self = this;

			// Destroy existing instance if any
			if (this.lenis) {
				this.debugLog('Destroying existing Lenis instance before re-init');
				this.lenis.destroy();
			}

			this.debugLog('Creating Lenis instance', {
				duration: 1.2,
				wheelMultiplier: 1.0,
				touchMultiplier: 2,
				smooth: true,
				smoothTouch: false
			});

			// Create Lenis instance with WHEEL OPTIMIZED config
			this.lenis = new Lenis({
				// Core settings
				duration: 0,
				easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
				orientation: 'vertical',
				gestureOrientation: 'vertical',
				smooth: true,

				// CRITICAL WHEEL SETTINGS
				wheelMultiplier: 1.0,
				touchMultiplier: 2,
				normalizeWheel: true,

				// Touch settings
				smoothTouch: false,

				// Advanced
				infinite: false,
				autoResize: true,
				prevent: (node) => {
					return node.classList.contains('lenis-prevent') ||
						node.closest('.lenis-prevent');
				}
			});

			this.currentScroll = typeof this.lenis.scroll === 'number'
				? this.lenis.scroll
				: (window.scrollY || document.documentElement.scrollTop || 0);

			// CRITICAL: Listen to scroll events for ScrollTrigger
			this.lenis.on('scroll', (e) => {
				if (typeof e.scroll === 'number') {
					this.currentScroll = e.scroll;
				}
				if (this.isDebugEnabled()) {
					const now = window.performance && performance.now ? performance.now() : Date.now();
					if (!this.lastScrollLog || now - this.lastScrollLog > 120) {
						this.lastScrollLog = now;
						this.debugLog('Lenis scroll event', {
							scroll: typeof e.scroll === 'number' ? Number(e.scroll.toFixed(2)) : e.scroll,
							velocity: typeof e.velocity === 'number' ? Number(e.velocity.toFixed(3)) : e.velocity
						});
					}
				}

				if (typeof ScrollTrigger !== 'undefined') {
					ScrollTrigger.update();
				}
			});

			// Start RAF loop - Use GSAP ticker if available, otherwise native RAF
			if (typeof gsap !== 'undefined') {
				// Use GSAP ticker for better synchronization with ScrollTrigger
				gsap.ticker.add((time) => {
					self.lenis.raf(time * 1000);
				});
				gsap.ticker.lagSmoothing(0);
				this.debugLog('Lenis hooked into GSAP ticker');
			} else {
				// Fallback to native RAF if GSAP not available
				this.debugLog('GSAP unavailable, using native requestAnimationFrame for Lenis');
				this.startRAF();
			}
		},

		/**
		 * Start RequestAnimationFrame loop (fallback if GSAP not available)
		 */
		startRAF: function () {
			const self = this;

			// Cancel existing RAF if any
			if (this.rafId) {
				cancelAnimationFrame(this.rafId);
			}

			function raf(time) {
				if (self.lenis) {
					self.lenis.raf(time);
					self.rafId = requestAnimationFrame(raf);
				}
			}

			this.rafId = requestAnimationFrame(raf);
			this.debugLog('Native RAF loop started for Lenis');
		},

		/**
		 * Sync Lenis with GSAP ScrollTrigger
		 */
		syncWithGSAP: function () {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				return;
			}

			// ScrollTrigger is already updated via lenis.on('scroll') in setupLenis
			// This method is kept for compatibility but main sync happens in setupLenis
		},

		/**
		 * Handle anchor links with smooth scroll
		 */
		handleAnchorLinks: function () {
			const self = this;

			document.querySelectorAll('a[href^="#"]').forEach(anchor => {
				anchor.addEventListener('click', function (e) {
					const href = this.getAttribute('href');

					if (href === '#' || href === '#top') {
						e.preventDefault();
						self.debugLog('Anchor intercepted (#/#top)', { href });
						self.lenis.scrollTo(0, {
							offset: 0,
							duration: 1.5
						});
						return;
					}

					const target = document.querySelector(href);
					if (target) {
						e.preventDefault();

						const header = document.querySelector('#masthead, .site-header, header');
						const offset = header ? -header.offsetHeight : 0;

						self.debugLog('Anchor intercepted', {
							href,
							targetTop: target.getBoundingClientRect ? Number(target.getBoundingClientRect().top.toFixed(2)) : null,
							offset
						});

						self.lenis.scrollTo(target, {
							offset: offset,
							duration: 1.5
						});
					}
				});
			});
		},

		/**
		 * Handle window resize
		 */
		handleResize: function () {
			const self = this;
			let resizeTimer;

			window.addEventListener('resize', function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					if (self.lenis) {
						self.lenis.resize();
						self.debugLog('Lenis resize triggered after window resize');
					}

					if (typeof ScrollTrigger !== 'undefined') {
						ScrollTrigger.refresh();
						self.debugLog('ScrollTrigger refresh triggered after window resize');
					}
				}, 250);
			});
		},

		applyScrollerProxy: function () {
			if (typeof ScrollTrigger === 'undefined') {
				this.debugLog('ScrollTrigger unavailable, postponing scrollerProxy setup');
				setTimeout(() => this.applyScrollerProxy(), 500);
				return;
			}

			if (!this.lenis) {
				this.debugLog('Lenis instance not ready, postponing scrollerProxy');
				setTimeout(() => this.applyScrollerProxy(), 250);
				return;
			}

			if (this.scrollerProxyApplied) {
				this.debugLog('scrollerProxy already applied');
				return;
			}

			const self = this;

			/**
			 * IMPORTANT ROBUSTNESS GUARD:
			 * Certaines versions/combinations de ScrollTrigger + Lenis peuvent
			 * lancer une erreur interne (ex: "Cannot read properties of undefined (reading 'indexOf')")
			 * lors de l'appel à scrollerProxy. On encapsule donc tout dans un try/catch
			 * pour éviter de casser tout le JS frontend (dont le slider Swiper).
			 */
			try {
				ScrollTrigger.scrollerProxy(document.body, {
					scrollTop(value) {
						if (typeof value !== 'undefined') {
							self.lenis.scrollTo(value, { immediate: true });
						}
						return self.lenis ? self.currentScroll : (window.scrollY || document.documentElement.scrollTop || 0);
					},
					getBoundingClientRect() {
						return {
							top: 0,
							left: 0,
							width: window.innerWidth,
							height: window.innerHeight
						};
					},
					pinType: document.body.style.transform ? 'transform' : 'fixed'
				});

				ScrollTrigger.defaults({ scroller: document.body });
				this.debugLog('ScrollTrigger defaults updated to use document.body as scroller');

				if (!this.scrollTriggerRefreshHandler) {
					this.scrollTriggerRefreshHandler = () => {
						if (self.lenis) {
							self.lenis.resize();
						}
					};
					ScrollTrigger.addEventListener('refresh', this.scrollTriggerRefreshHandler);
				}

				this.scrollerProxyApplied = true;
				this.debugLog('ScrollTrigger scrollerProxy applied for document.body');

				// Force an initial refresh so existing triggers use the proxy
				setTimeout(() => {
					ScrollTrigger.refresh();
					this.debugLog('ScrollTrigger refresh triggered after scrollerProxy setup');
				}, 0);
			} catch (e) {
				this.scrollerProxyApplied = false;
				return;
			}
		},

		/**
		 * Test mouse wheel detection
		 */
		testMouseWheel: function () {
			let wheelDetected = false;

			const testWheel = (e) => {
				if (!wheelDetected) {
					wheelDetected = true;
					window.removeEventListener('wheel', testWheel);
					this.debugLog('Mouse wheel input detected', {
						deltaY: typeof e.deltaY === 'number' ? Number(e.deltaY.toFixed(2)) : e.deltaY
					});
				}
			};

			window.addEventListener('wheel', testWheel, { passive: true });

			// Auto-remove after 5 seconds
			setTimeout(() => {
				window.removeEventListener('wheel', testWheel);
				this.debugLog('Mouse wheel test listener removed (timeout reached)');
			}, 5000);
		},

		/**
		 * Public methods
		 */
		scrollToTop: function () {
			if (this.lenis) {
				this.lenis.scrollTo(0, { duration: 1.5 });
			}
		},

		scrollToElement: function (target, offset = 0) {
			if (this.lenis && target) {
				const element = typeof target === 'string' ? document.querySelector(target) : target;
				if (element) {
					this.lenis.scrollTo(element, {
						offset: offset,
						duration: 1.5
					});
				}
			}
		},

		stop: function () {
			if (this.lenis) {
				this.lenis.stop();
			}
		},

		start: function () {
			if (this.lenis) {
				this.lenis.start();
			}
		},

		destroy: function () {
			this.debugLog('Destroying Lenis controller and cleaning up');
			if (this.rafId) {
				cancelAnimationFrame(this.rafId);
			}

			if (this.lenis) {
				this.lenis.destroy();
				this.lenis = null;
			}

			if (typeof ScrollTrigger !== 'undefined' && this.scrollTriggerRefreshHandler) {
				ScrollTrigger.removeEventListener('refresh', this.scrollTriggerRefreshHandler);
				this.scrollTriggerRefreshHandler = null;
			}

			this.scrollerProxyApplied = false;

			// Restore CSS
			document.documentElement.style.scrollBehavior = '';
			document.body.style.scrollBehavior = '';
		}

	};

	/**
	 * Initialize on DOM ready
	 */
	$(document).ready(function () {
		NOVALenisScroll.debugLog('Document ready → init Lenis sequence start');
		NOVALenisScroll.init();
	});

	/**
	 * Wait for full page load before starting
	 */
	$(window).on('load', function () {
		if (NOVALenisScroll.lenis) {
			// Restauration du scroll par le navigateur au refresh : synchroniser Lenis sur le scroll natif
			var nativeScroll = window.scrollY || document.documentElement.scrollTop || 0;
			var lenisScroll = typeof NOVALenisScroll.lenis.scroll === 'number' ? NOVALenisScroll.lenis.scroll : 0;
			if (nativeScroll > 0 && Math.abs(nativeScroll - lenisScroll) > 10) {
				NOVALenisScroll.lenis.scrollTo(nativeScroll, { immediate: true });
				NOVALenisScroll.currentScroll = nativeScroll;
				NOVALenisScroll.debugLog('Window load → Lenis synced to restored scroll', nativeScroll);
				window.dispatchEvent(new CustomEvent('novalenis-scroll-restored', { detail: { scrollY: nativeScroll } }));
			}
			setTimeout(function () {
				NOVALenisScroll.lenis.resize();
				if (typeof ScrollTrigger !== 'undefined') {
					ScrollTrigger.refresh();
				}
				NOVALenisScroll.debugLog('Window load complete → Lenis resize + ScrollTrigger refresh');
			}, 100);
		}
	});

	/**
	 * Elementor integration
	 */
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined') {
			elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
				if (NOVALenisScroll.lenis) {
					setTimeout(function () {
						NOVALenisScroll.lenis.resize();
						if (typeof ScrollTrigger !== 'undefined') {
							ScrollTrigger.refresh();
						}
						NOVALenisScroll.debugLog('Elementor frontend init → Lenis resize + ScrollTrigger refresh');
					}, 100);
				}
			});
		}
	});

	/**
	 * Expose globally
	 */
	window.NOVALenisScroll = NOVALenisScroll;
	window.scrollToTop = function () { NOVALenisScroll.scrollToTop(); };
	window.scrollToElement = function (target, offset) { NOVALenisScroll.scrollToElement(target, offset); };

})(jQuery);


/**
 * 🔧 DEBUGGING CHECKLIST
 * ======================
 * 
 * Si la molette ne marche toujours pas:
 * 
 * 1. Ouvrez la console et tapez:
 *    NOVALenisScroll.lenis
 *    (doit afficher un objet, pas null)
 * 
 * 2. Vérifiez les événements wheel:
 *    window.addEventListener('wheel', (e) => console.log('Wheel:', e.deltaY));
 * 
 * 3. Vérifiez le CSS:
 *    console.log(getComputedStyle(document.documentElement).overflow);
 *    (doit être "visible" ou "auto", pas "hidden")
 * 
 * 4. Testez manuellement:
 *    NOVALenisScroll.lenis.scrollTo(1000);
 * 
 * 5. Vérifiez les conflits:
 *    - Plugins de cache
 *    - Autres smooth scroll scripts
 *    - Extensions navigateur
 * 
 * 6. Dans votre CSS, supprimez ou commentez:
 *    html { scroll-behavior: smooth; }
 * 
 * 7. Vérifiez l'ordre des scripts dans le HTML:
 *    jQuery → GSAP → ScrollTrigger → Lenis → Ce script
 */