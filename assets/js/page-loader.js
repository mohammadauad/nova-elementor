/**
 * NOVA Page Loader - Version Optimisée pour Performance
 * Phase 1: COLOR1 visible immédiatement
 * Phase 2: COLOR2 grandit de height 0 → 100vh (de bas vers haut)
 * Version: 2.0.0 - Performance Optimized
 */

(function () {
	'use strict';

	// ✅ Performance: Disable console logs in production (set window.DEBUG = true for debugging)
	const DEBUG = window.DEBUG || false;
	const log = DEBUG ? console.log.bind(console) : function () { };

	// ✅ Initialize GSAP performance optimizations if available
	if (typeof gsap !== 'undefined') {
		// Performance optimizations for GSAP
		gsap.config({
			force3D: true,
			nullTargetWarn: false,
			autoSleep: 60, // Auto-sleep timelines after 60 seconds of inactivity
			units: { lineHeight: '' }
		});

		// Smoothing for better performance
		gsap.ticker.lagSmoothing(500, 33);
	}

	log('📄 PAGE-LOADER.JS: Script loaded');
	log('🔍 GSAP available:', typeof gsap !== 'undefined');

	// ✅ Get config from window
	const config = window.nova_loader_config || {
		color1: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
		color2: '#DB002B',
		phase2Delay: 0.1,
		phase2Duration: 0.3,
		phase3Duration: 1.0,
		animDelay: 0
	};

	// ✅ Force numeric conversion
	const animConfig = {
		color1: config.color1,
		color2: config.color2,
		phase2Delay: parseFloat(config.phase2Delay),
		phase2Duration: parseFloat(config.phase2Duration),
		phase3Duration: parseFloat(config.phase3Duration),
		animDelay: parseInt(config.animDelay, 10)
	};

	log('🚀 Page Loader Config:', animConfig);

	// ✅ Main animation function
	function startAnimation() {
		log('🎯 Page Loader: Starting animation');

		const heightPx = window.innerHeight;
		const heightStr = heightPx + 'px';

		// ✅ PHASE 1: Create loader with COLOR1 (visible immediately, full height)
		let loader = document.getElementById('nova-page-loader');
		if (!loader) {
			log('⚠️  Loader not in DOM, creating it');
			loader = document.createElement('div');
			loader.id = 'nova-page-loader';
			// PHASE 1: Loader visible immediately with color1
			loader.style.cssText = `
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100vh;
				background: ${animConfig.color1};
				z-index: 999997;
				margin: 0;
				padding: 0;
				will-change: transform;
				transform: translateZ(0);
				backface-visibility: hidden;
			`;
			document.body.insertBefore(loader, document.body.firstChild);
		} else {
			// If loader exists, ensure it has color1 and full height
			loader.style.background = animConfig.color1;
			loader.style.height = '100vh';
			loader.style.top = '0';
			loader.style.bottom = 'auto';
			loader.style.zIndex = '999997';
			loader.style.willChange = 'transform';
			loader.style.transform = 'translateZ(0)';
			loader.style.backfaceVisibility = 'hidden';
		}

		// ✅ PHASE 2: Create overlay with COLOR2 (starts at height: 0px, will animate)
		// Overlay is at same DOM level as loader, with higher z-index to be on top
		let overlay = document.getElementById('nova-page-loader-overlay');
		if (!overlay) {
			overlay = document.createElement('div');
			overlay.id = 'nova-page-loader-overlay';
			// PHASE 2: Overlay starts with height: 0px, will animate to full height
			overlay.style.cssText = `
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				height: 0px;
				background: ${animConfig.color2};
				z-index: 999998;
				overflow: hidden;
				margin: 0;
				padding: 0;
				will-change: height, transform;
				transform: translateZ(0);
				backface-visibility: hidden;
			`;
			document.body.insertBefore(overlay, document.body.firstChild);
		} else {
			// If overlay exists, reset it for Phase 2
			overlay.style.height = '0px';
			overlay.style.bottom = '0';
			overlay.style.top = 'auto';
			overlay.style.background = animConfig.color2;
			overlay.style.position = 'fixed';
			overlay.style.zIndex = '999998';
			overlay.style.willChange = 'height, transform';
			overlay.style.transform = 'translateZ(0)';
			overlay.style.backfaceVisibility = 'hidden';
		}

		log('✅ PHASE 1: Loader created with COLOR1');
		log('   Element: #nova-page-loader');
		log('   Background:', animConfig.color1);
		log('   Visible immediately (full height)');

		log('✅ PHASE 2: Overlay created with COLOR2');
		log('   Element: #nova-page-loader-overlay');
		log('   Position: bottom=0, width=100%, height=0px');
		log('   Background:', animConfig.color2);
		log('   Will animate: height 0px → ' + heightStr + ' (from bottom to top)');
		log('   Animation based on pixels (px)');
		log('   Viewport height:', heightStr);


		// Calculate total delay
		const totalDelay = animConfig.animDelay + (animConfig.phase2Delay * 1000);
		log('⏱️  Total delay before animation:', totalDelay + 'ms');

		// Start Phase 2 animation after delay
		setTimeout(() => {
			log('\n═══════════════════════════════════════════════');
			log('▶️  PHASE 2 START: #nova-page-loader-overlay expansion from BOTTOM');
			log('   Structure:');
			log('     - #nova-page-loader (COLOR1) = visible, full height, z-index: 999997');
			log('     - #nova-page-loader-overlay (COLOR2) = will grow from bottom to top, z-index: 999998 (higher)');
			log('   Method: height animation in PIXELS (px)');
			log('   From: height = 0px (invisible)');
			log('   To: height = ' + heightStr + ' (full screen)');
			log('   Direction: bottom to top (grows upward)');
			log('   Duration:', animConfig.phase2Duration + 's');
			log('═══════════════════════════════════════════════');

			// Check if GSAP is available
			if (typeof gsap !== 'undefined') {
				log('✅ Using GSAP animation with height in PIXELS (px)');

				// Set initial state: height = 0px
				gsap.set(overlay, {
					height: '0px',
					bottom: '0px',
					top: 'auto',
					force3D: true
				});
				log('   GSAP set: height = 0px, bottom = 0px');

				// Wait a tick for set to apply
				requestAnimationFrame(() => {
					// PHASE 2: Animate height from 0px to viewport height (in pixels)
					// This expands from bottom to top (because positioned at bottom: 0)
					gsap.to(overlay, {
						height: heightStr, // height in pixels (e.g., "945px")
						duration: animConfig.phase2Duration,
						ease: 'power2.out',
						force3D: true, // ✅ Performance: Use GPU acceleration
						onStart: () => {
							log('\n🎬 GSAP ANIMATION STARTED');
							const startHeight = window.getComputedStyle(overlay).height;
							log('   Computed height at start:', startHeight);
							log('   Target height:', heightStr);
						},
						onUpdate: function () {
							if (DEBUG) {
								const currentHeight = window.getComputedStyle(overlay).height;
								const progress = Math.round(this.progress() * 100);
								if (progress % 20 === 0) { // Log every 20%
									log('📈 Progress:', progress + '% | height:', currentHeight);
								}
							}
						},
						onComplete: () => {
							const finalHeight = window.getComputedStyle(overlay).height;
							log('\n✅ PHASE 2 COMPLETE (GSAP)');
							log('   Final computed height:', finalHeight);
							log('   Expected:', heightStr);
							log('   #nova-page-loader-overlay now covers entire screen (from bottom to top)');
							log('   COLOR2 should now be visible over COLOR1');
							log('═══════════════════════════════════════════════\n');

							// ✅ Performance: Remove will-change after animation
							gsap.set(overlay, { willChange: 'auto' });

							// ✅ Start Phase 3 automatically after Phase 2
							startPhase3();
						}
					});
				});

			} else {
				log('⚠️  GSAP not available - using CSS transition with height in PIXELS (px)');

				// CSS Animation using height in pixels
				// 1. Ensure starting state: height = 0px
				overlay.style.height = '0px';
				overlay.style.bottom = '0px';
				overlay.style.top = 'auto';
				log('   CSS initial: height = 0px, bottom = 0px');

				// 2. Force reflow to register the 0px height
				const reflow2 = overlay.offsetHeight;
				log('   Forced reflow, measured height:', reflow2 + 'px');

				// 3. Add transition for height
				overlay.style.transition = `height ${animConfig.phase2Duration}s ease-out`;
				log('   Added transition for height');

				// 4. Small delay then trigger animation
				setTimeout(() => {
					log('\n🎬 CSS ANIMATION STARTED');
					log('   Setting height to ' + heightStr + ' (in pixels)...');

					// Trigger animation: expand from bottom to top (height in pixels)
					overlay.style.height = heightStr;

					// Log computed style immediately after
					if (DEBUG) {
						requestAnimationFrame(() => {
							const computedHeight = window.getComputedStyle(overlay).height;
							log('   height right after change:', computedHeight);
						});
					}

					// Monitor progress (only in DEBUG mode)
					if (DEBUG) {
						let progressChecks = 0;
						const maxChecks = 10;
						const checkInterval = (animConfig.phase2Duration * 1000) / maxChecks;

						const progressMonitor = setInterval(() => {
							progressChecks++;
							const currentHeight = window.getComputedStyle(overlay).height;
							const progress = Math.round((progressChecks / maxChecks) * 100);
							log('📈 ~' + progress + '% | height:', currentHeight);

							if (progressChecks >= maxChecks) {
								clearInterval(progressMonitor);
							}
						}, checkInterval);
					}

					// Check completion
					setTimeout(() => {
						const finalHeight = window.getComputedStyle(overlay).height;
						log('\n✅ PHASE 2 COMPLETE (CSS)');
						log('   Final computed height:', finalHeight);
						log('   Expected:', heightStr);
						log('   #nova-page-loader-overlay should cover entire screen (from bottom to top)');
						log('   COLOR2 should now be visible over COLOR1');
						log('═══════════════════════════════════════════════\n');

						// ✅ Performance: Remove will-change after animation
						overlay.style.willChange = 'auto';

						// ✅ Start Phase 3 automatically after Phase 2
						startPhase3();
					}, animConfig.phase2Duration * 1000 + 50);

				}, 50); // Small delay after setting transition
			}
		}, totalDelay);
	}

	// ✅ Phase 3: Reduce both loader and overlay to height 0px
	function startPhase3() {
		const heightPx = window.innerHeight;
		const heightStr = heightPx + 'px';

		log('\n═══════════════════════════════════════════════');
		log('▶️  PHASE 3 START: Reducing both elements to height 0px');
		log('   Actions:');
		log('     1. #nova-page-loader (COLOR1): height 100vh → 0px');
		log('     2. #nova-page-loader-overlay (COLOR2): height ' + heightStr + ' → 0px');
		log('   Duration:', animConfig.phase3Duration + 's');
		log('═══════════════════════════════════════════════');

		const loader = document.getElementById('nova-page-loader');
		const overlay = document.getElementById('nova-page-loader-overlay');

		if (!loader || !overlay) {
			log('⚠️  Phase 3: Loader or overlay not found');
			return;
		}

		// Get current heights
		const loaderHeight = window.getComputedStyle(loader).height;
		const overlayHeight = window.getComputedStyle(overlay).height;

		// ✅ Performance: Set will-change for animation
		loader.style.willChange = 'height, transform';
		overlay.style.willChange = 'height, transform';

		// Ensure both elements start from top: 0 (will animate top + height together)
		loader.style.top = 'auto';
		loader.style.bottom = '0';
		overlay.style.top = 'auto';
		overlay.style.bottom = '0';

		// Force reflow to register the positioning
		const reflow = loader.offsetHeight;

		log('   Will animate top + height simultaneously for bottom-to-top reduction');
		log('   Loader current height:', loaderHeight);
		log('   Overlay current height:', overlayHeight);

		// Check if GSAP is available
		if (typeof gsap !== 'undefined') {
			log('✅ Using GSAP animation for Phase 3');

			// Wait a frame to ensure positioning is applied
			requestAnimationFrame(() => {
				// Animate both elements simultaneously: top + height
				// This creates the effect of reduction from bottom to top
				gsap.to([loader, overlay], {
					height: '0px',
					bottom: heightStr, // Move top down as height reduces (creates bottom-to-top effect)
					duration: animConfig.phase3Duration,
					ease: 'power2.in',
					force3D: true, // ✅ Performance: Use GPU acceleration
					onStart: () => {
						log('\n🎬 PHASE 3 ANIMATION STARTED (GSAP)');
						const loaderStartHeight = window.getComputedStyle(loader).height;
						const overlayStartHeight = window.getComputedStyle(overlay).height;
						log('   Loader initial height:', loaderStartHeight);
						log('   Overlay initial height:', overlayStartHeight);
					},
					onUpdate: function () {
						if (DEBUG) {
							const loaderCurrent = window.getComputedStyle(loader).height;
							const overlayCurrent = window.getComputedStyle(overlay).height;
							const progress = Math.round(this.progress() * 100);
							if (progress % 25 === 0) { // Log every 25%
								log('📈 Progress:', progress + '% | Loader:', loaderCurrent, '| Overlay:', overlayCurrent);
							}
						}
					},
					onComplete: () => {
						const loaderFinal = window.getComputedStyle(loader).height;
						const overlayFinal = window.getComputedStyle(overlay).height;
						log('\n✅ PHASE 3 COMPLETE (GSAP)');
						log('   Loader final height:', loaderFinal);
						log('   Overlay final height:', overlayFinal);
						log('   Both elements should now be hidden (height: 0px)');
						log('   Page loader animation complete!');
						log('═══════════════════════════════════════════════\n');

						// ✅ Performance: Remove will-change after animation
						gsap.set([loader, overlay], {
							willChange: 'auto',
							force3D: false
						});

						// Remove elements from DOM after animation
						setTimeout(() => {
							if (loader.parentNode) {
								loader.parentNode.removeChild(loader);
							}
							if (overlay.parentNode) {
								overlay.parentNode.removeChild(overlay);
							}
							log('✅ Page loader elements removed from DOM');
						}, 100);
					}
				});
			});

		} else {
			log('⚠️  GSAP not available - using CSS transition for Phase 3');

			// Ensure positioning
			loader.style.top = '0';
			loader.style.bottom = 'auto';
			overlay.style.top = '0';
			overlay.style.bottom = 'auto';

			// CSS Animation - animate both top and height
			loader.style.transition = `height ${animConfig.phase3Duration}s ease-in, top ${animConfig.phase3Duration}s ease-in`;
			overlay.style.transition = `height ${animConfig.phase3Duration}s ease-in, top ${animConfig.phase3Duration}s ease-in`;

			// Wait a frame to ensure positioning is applied
			requestAnimationFrame(() => {
				setTimeout(() => {
					log('\n🎬 PHASE 3 ANIMATION STARTED (CSS)');
					const loaderStartHeight = window.getComputedStyle(loader).height;
					const overlayStartHeight = window.getComputedStyle(overlay).height;
					log('   Loader initial height:', loaderStartHeight);
					log('   Overlay initial height:', overlayStartHeight);
					log('   Animating height to 0px and top to ' + heightStr + '...');

					// Trigger animation: reduce height and move top down simultaneously
					loader.style.height = '0px';
					loader.style.top = heightStr;
					overlay.style.height = '0px';
					overlay.style.top = heightStr;

					// Monitor progress (only in DEBUG mode)
					if (DEBUG) {
						let progressChecks = 0;
						const maxChecks = 8;
						const checkInterval = (animConfig.phase3Duration * 1000) / maxChecks;

						const progressMonitor = setInterval(() => {
							progressChecks++;
							const loaderCurrent = window.getComputedStyle(loader).height;
							const overlayCurrent = window.getComputedStyle(overlay).height;
							const progress = Math.round((progressChecks / maxChecks) * 100);
							log('📈 ~' + progress + '% | Loader:', loaderCurrent, '| Overlay:', overlayCurrent);

							if (progressChecks >= maxChecks) {
								clearInterval(progressMonitor);
							}
						}, checkInterval);
					}

					// Check completion
					setTimeout(() => {
						const loaderFinal = window.getComputedStyle(loader).height;
						const overlayFinal = window.getComputedStyle(overlay).height;
						log('\n✅ PHASE 3 COMPLETE (CSS)');
						log('   Loader final height:', loaderFinal);
						log('   Overlay final height:', overlayFinal);
						log('   Both elements should now be hidden (height: 0px)');
						log('   Page loader animation complete!');
						log('════════════════════════════════════════════════\n');

						// ✅ Performance: Remove will-change after animation
						loader.style.willChange = 'auto';
						overlay.style.willChange = 'auto';

						// Remove elements from DOM after animation
						setTimeout(() => {
							if (loader.parentNode) {
								loader.parentNode.removeChild(loader);
							}
							if (overlay.parentNode) {
								overlay.parentNode.removeChild(overlay);
							}
							log('✅ Page loader elements removed from DOM');
						}, 100);
					}, animConfig.phase3Duration * 1000 + 50);

				}, 50);
			});
		}
	}

	// ✅ Start when document is ready
	if (document.readyState === 'loading') {
		log('⏳ Waiting for DOMContentLoaded...');
		document.addEventListener('DOMContentLoaded', () => {
			log('✅ DOMContentLoaded fired');
			startAnimation();
		});
	} else {
		log('✅ Document already ready');
		startAnimation();
	}
})();

// ✅ Script initialization complete (only log in DEBUG mode)
if (window.DEBUG) {
	// Page Loader: Script initialization complete
}