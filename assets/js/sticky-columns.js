(function ($) {
	'use strict';

	/**
	 * EWD Sticky Columns – SYNCHRONISÉ AVEC SCROLLBAR NATIVE
	 * Force la synchronisation entre scroll natif et Lenis
	 */

	const EWDStickyScrollTrigger = {
		instances: [],

		debug: function (event, data) {
			if (window.EWDStickyDebug && typeof console !== 'undefined') {
				console.log('[EWD STICKY]', event, data || '');
			}
		},

		killAll: function () {
			this.instances.forEach(st => {
				if (st && st.kill) st.kill();
			});
			this.instances = [];
		},

		init: function () {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				this.debug('init-skipped-missing-deps');
				return;
			}

			gsap.registerPlugin(ScrollTrigger);

			ScrollTrigger.config({
				autoRefreshEvents: "visibilitychange,DOMContentLoaded,load,resize"
			});

			const wrappers = document.querySelectorAll('.ewd-sticky-wrapper');
			if (!wrappers.length) {
				this.debug('init-no-wrappers');
				return;
			}
			this.debug('init-start', { wrapperCount: wrappers.length, viewport: window.innerWidth + 'x' + window.innerHeight });

			const scroller = window; // Changed from document.body to window to avoid scroll conflicts

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
			this.debug('init-refresh-called');
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
			if (window.innerWidth < 992) {
				this.debug('instance-skipped-mobile', { wrapperIndex: wrapperIndex });
				return;
			}

			const colRect = stickyCol.getBoundingClientRect();

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

			const st = ScrollTrigger.create({
				trigger: triggerEl,
				scroller: scroller,
				pin: stickyCol,
				// Stabilise native scrolling: reserve space + use fixed pinning.
				// "transform" pinning + pinSpacing:false can cause scroll jumps/rollback feelings on some layouts.
				pinSpacing: true,
				pinType: 'fixed',
				/* pinReparent: true, */ // REMOVED TO PREVENT SCROLL BLOCKING CONFLICTS
				start: () => 'top top+=' + configuredOffset + 'px',
				endTrigger: endTriggerEl,
				end: () => {
					const triggerHeight = Math.max(triggerEl.offsetHeight || 0, endTriggerEl.offsetHeight || 0);
					const stickyHeight = stickyCol.offsetHeight || 0;
					const scrollDistance = Math.max(1, triggerHeight - stickyHeight - configuredOffset - bottomOffset);
					EWDStickyScrollTrigger.debug('instance-end-distance', {
						wrapperIndex: wrapperIndex,
						triggerHeight: triggerHeight,
						stickyHeight: stickyHeight,
						configuredOffset: configuredOffset,
						bottomOffset: bottomOffset,
						scrollDistance: scrollDistance
					});
					return '+=' + scrollDistance;
				},
				scrub: false,
				anticipatePin: 0,
				fastScrollEnd: true,
				invalidateOnRefresh: true,
				markers: false,

				onEnter: function () {
					EWDStickyScrollTrigger.debug('pin-enter', { wrapperIndex: wrapperIndex });
					if (pinnedClass) stickyCol.classList.add(pinnedClass);
				},
				onLeave: function () {
					EWDStickyScrollTrigger.debug('pin-leave', { wrapperIndex: wrapperIndex });
					if (pinnedClass) stickyCol.classList.remove(pinnedClass);
				},
				onEnterBack: function () {
					EWDStickyScrollTrigger.debug('pin-enter-back', { wrapperIndex: wrapperIndex });
					if (pinnedClass) stickyCol.classList.add(pinnedClass);
				},
				onLeaveBack: function () {
					EWDStickyScrollTrigger.debug('pin-leave-back', { wrapperIndex: wrapperIndex });
					if (pinnedClass) stickyCol.classList.remove(pinnedClass);
				},
				onUpdate: function (self) {
					const now = window.performance && performance.now ? performance.now() : Date.now();
					if (!self._novaLastLog || now - self._novaLastLog > 120) {
						self._novaLastLog = now;
						const currentScroll = typeof self.scroll === 'function' ? self.scroll() : (window.scrollY || 0);
						const prevScroll = typeof self._novaPrevScroll === 'number' ? self._novaPrevScroll : currentScroll;
						const delta = currentScroll - prevScroll;
						self._novaPrevScroll = currentScroll;

						if (Math.abs(delta) > 45) {
							EWDStickyScrollTrigger.debug('pin-large-delta', {
								wrapperIndex: wrapperIndex,
								delta: Number(delta.toFixed(2)),
								progress: Number(self.progress.toFixed(4)),
								direction: self.direction
							});
						}
					}
				}
			});

			this.debug('instance-created', {
				wrapperIndex: wrapperIndex,
				configuredOffset: configuredOffset,
				bottomOffset: bottomOffset,
				width: Number(colWidth.toFixed(2)),
				pinnedClass: pinnedClass
			});
			this.instances.push(st);
		}
	};

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
		if (window.NOVA_NATIVE_SCROLL_MODE === true) {
			return;
		}
		if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
			setTimeout(runStickyInit, 100);
			return;
		}

		// Check if Lenis is already initialized or ready
		if (window.NOVALenisScroll && window.NOVALenisScroll.scrollerProxyApplied === true) {
			EWDStickyScrollTrigger.init();
			return;
		}

		// Fallback: wait for Lenis or timeout
		var waited = 0;
		var maxWait = 1500; // Reduced timeout
		var interval = 50;  // More frequent checks
		var checkInterval = setInterval(function () {
			waited += interval;
			if (window.NOVALenisScroll && window.NOVALenisScroll.scrollerProxyApplied === true) {
				clearInterval(checkInterval);
				EWDStickyScrollTrigger.init();
				return;
			}
			if (waited >= maxWait) {
				clearInterval(checkInterval);
				EWDStickyScrollTrigger.init();
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
