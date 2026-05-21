/**
 * NOVA Smooth Scroll - Lenis Integration
 * Version: 4.0.0 - Scroll anomaly fixed
 *
 * Fixes applied:
 * - Lenis CDN now properly loaded via PHP (lenis v1.1.x)
 * - Removed deprecated ScrollTrigger.scrollerProxy() → replaced with
 *   ScrollTrigger.normalizeScroll() + lenis.on('scroll', ScrollTrigger.update)
 * - Removed obsolete `smooth: true` option (Lenis v1+ always smooth)
 * - normalizeWheel: true to prevent cross-browser jitter
 * - Single init path (window load) to avoid double-init race condition
 * - Scrollbar drag sync simplified and reliable
 */

(function ($) {
	'use strict';

	// ── Debug flag ────────────────────────────────────────────────────────────
	if (typeof window !== 'undefined' && window.NOVALenisDebug !== true) {
		try {
			if (new URLSearchParams(window.location.search).get('nova_debug_scroll') === '1') {
				window.NOVALenisDebug = true;
			}
		} catch (e) { /* ignore */ }
	}

	// ── Main controller ───────────────────────────────────────────────────────
	const NOVALenisScroll = {

		lenis: null,
		rafId: null,
		gsapTickerCallback: null,
		isScrollbarDragging: false,
		isInitialized: false,
		scrollerProxyApplied: false, // Flag for compatibility with sticky-columns.js
		resizeTimer: null,

		// ── Debug helper ──────────────────────────────────────────────────────
		log: function (event, data) {
			if (window.NOVALenisDebug && typeof console !== 'undefined') {
				console.log('[NOVA LENIS]', event, data || '');
			}
		},

		// ── Init ──────────────────────────────────────────────────────────────
		init: function () {
			// Global hard reset mode: keep native browser scroll only.
			window.NOVA_NATIVE_SCROLL_MODE = true;

			// TEMPORARILY DISABLED AS REQUESTED
			this.enableNativeScrollStabilizer();
			this.log('Lenis is temporarily disabled');
			this.isInitialized = true;
			this.scrollerProxyApplied = true; // Still set this so other scripts proceed with native scroll
			return;

			if (this.isInitialized) return;

			if (typeof Lenis === 'undefined') {
				this.log('Lenis not found — aborting');
				return;
			}

			// Respect prefers-reduced-motion
			if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
				this.log('prefers-reduced-motion — skipping Lenis');
				return;
			}

			this.removeCSSConflicts();
			this.createLenis();
			this.startTicker();
			this.syncScrollTrigger();
			this.handleScrollbarDrag();
			this.handleAnchorLinks();
			this.handleResize();

			this.isInitialized = true;
			this.scrollerProxyApplied = true; // Signal to other scripts (like sticky columns) that we are ready
			this.log('init-complete');
		},

		// ── Native wheel stabilizer (fallback when Lenis is disabled) ─────────
		// Neutralizes tiny opposite-direction scroll corrections ("scroll rollback").
		enableNativeScrollStabilizer: function () {
			if (window.NOVA_DISABLE_SCROLL_STABILIZER === true) return;
			if (window.__NOVA_NATIVE_SCROLL_STABILIZER_ACTIVE__ === true) return;
			window.__NOVA_NATIVE_SCROLL_STABILIZER_ACTIVE__ = true;

			const self = this;
			window.addEventListener('wheel', function (e) {
				if (e.defaultPrevented) return;
				if (e.ctrlKey) return; // do not interfere with browser zoom gesture

				const dy = typeof e.deltaY === 'number' ? e.deltaY : 0;
				if (dy === 0) return;

				const startY = window.scrollY || document.documentElement.scrollTop || 0;
				requestAnimationFrame(function () {
					const endY = window.scrollY || document.documentElement.scrollTop || 0;
					const actual = endY - startY;
					if (actual === 0) return;

					const opposite = (dy > 0 && actual < 0) || (dy < 0 && actual > 0);
					// Only correct tiny rollback artifacts; avoid fighting intentional large moves.
					if (!opposite || Math.abs(actual) > 20) return;

					window.scrollTo({ top: startY, behavior: 'auto' });
					self.log('native-stabilizer-corrected', { deltaY: dy, actualDelta: actual, startY: startY, endY: endY });
				});
			}, { passive: true, capture: true });

			this.log('native-stabilizer-enabled');
		},

		// ── Remove CSS that fights Lenis ──────────────────────────────────────
		removeCSSConflicts: function () {
			document.documentElement.style.scrollBehavior = 'auto';
			document.body.style.scrollBehavior = 'auto';

			// Ensure we don't have a double scrollbar or a shifting layout
			/* 
			if (CSS.supports('scrollbar-gutter', 'stable')) {
				document.documentElement.style.scrollbarGutter = 'stable';
			}
			*/

			document.documentElement.style.overflowX = 'hidden';
			document.documentElement.style.overflowY = 'auto';
			this.log('css-conflicts-removed');
		},

		// ── Create Lenis instance ─────────────────────────────────────────────
		createLenis: function () {
			if (this.lenis) {
				this.lenis.destroy();
				this.lenis = null;
			}

			this.lenis = new Lenis({
				// Duration / easing
				duration: 1.1,
				easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); },

				// Orientation
				orientation: 'vertical',
				gestureOrientation: 'vertical',

				// Wheel — normalizeWheel:true prevents cross-browser delta jitter
				wheelMultiplier: 1,
				touchMultiplier: 1.8,
				normalizeWheel: true,

				// Touch: keep native feel on mobile
				smoothTouch: false,

				// Prevent Lenis on elements that need native scroll
				prevent: function (node) {
					return node.classList.contains('lenis-prevent') ||
						!!node.closest('.lenis-prevent');
				}
			});

			this.log('lenis-created');
		},

		// ── RAF / GSAP ticker ─────────────────────────────────────────────────
		startTicker: function () {
			const self = this;

			if (typeof gsap !== 'undefined') {
				// GSAP ticker: drives Lenis at the same cadence as GSAP animations
				this.gsapTickerCallback = function (time) {
					self.lenis.raf(time * 1000);
				};
				gsap.ticker.add(this.gsapTickerCallback);
				// lagSmoothing(0) prevents GSAP from skipping frames after tab focus
				gsap.ticker.lagSmoothing(0);
				this.log('ticker: gsap');
			} else {
				// Fallback native RAF
				const raf = function (time) {
					self.lenis.raf(time);
					self.rafId = requestAnimationFrame(raf);
				};
				this.rafId = requestAnimationFrame(raf);
				this.log('ticker: native RAF');
			}
		},

		// ── ScrollTrigger sync ────────────────────────────────────────────────
		// Modern approach: no scrollerProxy, just update ST on every Lenis tick.
		syncScrollTrigger: function () {
			if (typeof ScrollTrigger === 'undefined') return;

			const self = this;

			// Tell ScrollTrigger to update its scroll position on every Lenis frame
			this.lenis.on('scroll', function () {
				ScrollTrigger.update();
			});

			// After a resize, refresh ST so pin spacers are recalculated
			ScrollTrigger.addEventListener('refresh', function () {
				if (self.lenis) self.lenis.resize();
			});

			// Initial refresh once everything is laid out
			setTimeout(function () {
				ScrollTrigger.refresh();
				self.log('ScrollTrigger initial refresh');
			}, 100);

			this.log('ScrollTrigger sync ready');
		},

		// ── Scrollbar drag ────────────────────────────────────────────────────
		// When the user drags the native scrollbar, Lenis must be paused then
		// re-synced to the native scroll position on release.
		handleScrollbarDrag: function () {
			const self = this;

			const scrollbarWidth = function () {
				return window.innerWidth - document.documentElement.clientWidth;
			};

			document.addEventListener('mousedown', function (e) {
				const sbw = scrollbarWidth();
				if (!sbw || sbw <= 0) return;
				if (e.clientX < window.innerWidth - sbw - 2) return;

				self.isScrollbarDragging = true;
				if (self.lenis) self.lenis.stop();
				self.log('scrollbar-drag-start');
			}, { passive: true });

			window.addEventListener('mouseup', function () {
				if (!self.isScrollbarDragging) return;
				self.isScrollbarDragging = false;

				if (!self.lenis) return;

				// Snap Lenis to where the native scroll ended up
				const nativeY = window.scrollY || document.documentElement.scrollTop || 0;
				self.lenis.scrollTo(nativeY, { immediate: true, force: true });
				self.lenis.start();

				if (typeof ScrollTrigger !== 'undefined') ScrollTrigger.update();
				self.log('scrollbar-drag-end', { restoredTo: nativeY });
			}, { passive: true });
		},

		// ── Anchor links ──────────────────────────────────────────────────────
		handleAnchorLinks: function () {
			const self = this;

			document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
				anchor.addEventListener('click', function (e) {
					const href = this.getAttribute('href');

					if (href === '#' || href === '#top') {
						e.preventDefault();
						self.lenis.scrollTo(0, { duration: 1.4 });
						return;
					}

					const target = document.querySelector(href);
					if (!target) return;

					e.preventDefault();
					const header = document.querySelector('#masthead, .site-header, header');
					const offset = header ? -header.offsetHeight : 0;
					self.lenis.scrollTo(target, { offset: offset, duration: 1.4 });
				});
			});
		},

		// ── Resize ────────────────────────────────────────────────────────────
		handleResize: function () {
			const self = this;

			window.addEventListener('resize', function () {
				clearTimeout(self.resizeTimer);
				self.resizeTimer = setTimeout(function () {
					if (self.lenis) self.lenis.resize();
					if (typeof ScrollTrigger !== 'undefined') {
						setTimeout(function () { ScrollTrigger.refresh(); }, 50);
					}
					self.log('resize handled');
				}, 250);
			});
		},

		// ── Public API ────────────────────────────────────────────────────────
		scrollToTop: function () {
			if (this.lenis) this.lenis.scrollTo(0, { duration: 1.4 });
		},

		scrollToElement: function (target, offset) {
			if (!this.lenis || !target) return;
			const el = typeof target === 'string' ? document.querySelector(target) : target;
			if (el) this.lenis.scrollTo(el, { offset: offset || 0, duration: 1.4 });
		},

		stop: function () { if (this.lenis) this.lenis.stop(); },
		start: function () { if (this.lenis) this.lenis.start(); },

		destroy: function () {
			if (this.rafId) { cancelAnimationFrame(this.rafId); this.rafId = null; }
			if (typeof gsap !== 'undefined' && this.gsapTickerCallback) {
				gsap.ticker.remove(this.gsapTickerCallback);
				this.gsapTickerCallback = null;
			}
			clearTimeout(this.resizeTimer);
			if (this.lenis) { this.lenis.destroy(); this.lenis = null; }

			document.documentElement.style.scrollBehavior = '';
			document.body.style.scrollBehavior = '';

			this.isInitialized = false;
			this.log('destroyed');
		}
	};

	// ── Bootstrap ─────────────────────────────────────────────────────────────
	// Init on window load (not DOMContentLoaded) so all layout is settled,
	// images are sized, and GSAP/ScrollTrigger are fully ready.
	$(window).on('load', function () {
		NOVALenisScroll.init();

		// Sync to browser-restored scroll position (back/forward navigation)
		if (NOVALenisScroll.lenis) {
			const nativeY = window.scrollY || document.documentElement.scrollTop || 0;
			if (nativeY > 10) {
				NOVALenisScroll.lenis.scrollTo(nativeY, { immediate: true });
				NOVALenisScroll.log('restored scroll position', nativeY);
			}
		}
	});

	// ── Elementor editor: re-sync after widget renders ─────────────────────
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend === 'undefined') return;

		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			if (!NOVALenisScroll.lenis) return;
			NOVALenisScroll.lenis.resize();
			if (typeof ScrollTrigger !== 'undefined') {
				setTimeout(function () { ScrollTrigger.refresh(); }, 150);
			}
		});
	});

	// ── Global exposure ───────────────────────────────────────────────────────
	window.NOVALenisScroll = NOVALenisScroll;
	window.scrollToTop = function () { NOVALenisScroll.scrollToTop(); };
	window.scrollToElement = function (target, offset) { NOVALenisScroll.scrollToElement(target, offset); };

})(jQuery);
