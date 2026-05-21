/**
 * Nova Scroll Hero - Version Améliorée avec Animations Fluides
 * Version: 5.0.0 - Smooth Professional Animations
 */

(function ($) {
	'use strict';

	const NovaScrollHero = {

		config: {
			heroSelector: '.nova-scroll-hero',
			textSelector: '.nova-scroll-hero-text',
			imageContainerSelector: '.nova-scroll-hero-image-container',
			overlaySelector: '.nova-scroll-hero-overlay',
			overlayTextSelector: '.nova-overlay-text',
			buttonSelector: '.nova-scroll-hero-button'
		},

		timelines: [],
		initialized: false,
		intersectionObserver: null,

		/**
		 * Initialize
		 */
		init: function () {
			if (this.initialized || !this.validateDependencies()) {
				return;
			}

			gsap.registerPlugin(ScrollTrigger);

			// ⚡ Fix FOUC: apply initial sizes immediately so the image
			// never shows at 100vw/100vh before GSAP runs
			this.applyInitialSizes();

			// Performance optimizations for GSAP
			gsap.config({
				force3D: true,
				nullTargetWarn: false,
				autoSleep: 60,
				units: { lineHeight: '' }
			});

			// Smoothing + ScrollTrigger perf tweaks
			gsap.ticker.lagSmoothing(500, 33);

			if (typeof ScrollTrigger !== 'undefined') {
				ScrollTrigger.config({
					ignoreMobileResize: true,
					refreshPriority: -1
				});
				ScrollTrigger.defaults({
					anticipatePin: 1,
					refreshPriority: -1
				});
			}

			// Use Intersection Observer for lazy initialization
			this.initWithObserver();
		},

		/**
		 * ⚡ Apply initial dimensions synchronously to ALL heroes right away.
		 * Now that CSS selectors handle initial dimensions, this just ensures
		 * GSAP reads the CSS-computed values instead of overriding them.
		 */
		applyInitialSizes: function () {
			const self = this;
			$(this.config.heroSelector).each(function () {
				const heroElement = this;
				const $imageContainer = $(heroElement).find(self.config.imageContainerSelector);
				if ($imageContainer.length === 0) return;

				// CSS already sets width/height via Elementor selectors
				// Just ensure border-radius and transform are set
				gsap.set($imageContainer[0], {
					borderRadius: '8px',
					scale: 1,
					transformOrigin: 'center center'
				});

				// Also set CSS variables for CSS-driven styles
				self.applySizeVariables(heroElement);
			});
		},

		/**
		 * Initialize with Intersection Observer for better performance
		 */
		initWithObserver: function () {
			const $heroes = $(this.config.heroSelector);

			if ($heroes.length === 0) {
				console.warn('[NovaScrollHero] No .nova-scroll-hero elements found on page!');
				return;
			}

			// In Elementor editor, don't use Intersection Observer (everything is visible)
			const isElementorEditor = typeof elementorFrontend !== 'undefined' && elementorFrontend.isEditMode();

			// Check if Intersection Observer is supported and we're not in editor
			if ('IntersectionObserver' in window && !isElementorEditor) {
				const observerOptions = {
					root: null,
					rootMargin: '50px', // Start loading 50px before entering viewport
					threshold: 0.01
				};

				this.intersectionObserver = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							this.animateHero(entry.target);
							this.intersectionObserver.unobserve(entry.target);
						}
					});
				}, observerOptions);

				// Observe all heroes
				$heroes.each((index, element) => {
					this.intersectionObserver.observe(element);
				});
			} else {
				// Fallback for browsers without Intersection Observer or in editor mode
				this.setupAnimations();
			}

			this.initialized = true;
		},

		/**
		 * Validate dependencies
		 */
		validateDependencies: function () {
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				return false;
			}
			return true;
		},

		/**
		 * Setup animations (fallback method)
		 */
		setupAnimations: function () {
			const $heroes = $(this.config.heroSelector);

			if ($heroes.length === 0) {
				return;
			}

			$heroes.each((index, element) => {
				this.animateHero(element);
			});
		},

		/**
		 * Get responsive dimension (like stacking cards)
		 */
		getResponsiveDimension: function (heroElement, axis) {
			if (!heroElement || !heroElement.dataset) {
				return null;
			}

			const dataset = heroElement.dataset;
			const viewport = window.innerWidth;

			// Breakpoints Elementor : mobile < 768, tablet <= 1199, desktop > 1199
			if (viewport <= 767 && dataset[axis + 'MobileValue'] && parseFloat(dataset[axis + 'MobileValue']) > 0) {
				const value = dataset[axis + 'MobileValue'];
				const unit = dataset[axis + 'MobileUnit'] || dataset[axis + 'Unit'];
				if (value && parseFloat(value) > 0) {
					return { value: parseFloat(value), unit: unit || 'px' };
				}
			} else if (viewport <= 1199 && dataset[axis + 'TabletValue'] && parseFloat(dataset[axis + 'TabletValue']) > 0) {
				const value = dataset[axis + 'TabletValue'];
				const unit = dataset[axis + 'TabletUnit'] || dataset[axis + 'Unit'];
				if (value && parseFloat(value) > 0) {
					return { value: parseFloat(value), unit: unit || 'px' };
				}
			}

			// Fallback vers desktop
			const value = dataset[axis + 'Value'];
			const unit = dataset[axis + 'Unit'];

			if (!value || value === '' || value === undefined) {
				return null;
			}

			return {
				value: parseFloat(value),
				unit: unit || 'px'
			};
		},

		/**
		 * Apply size variables (like stacking cards)
		 */
		applySizeVariables: function (heroElement) {
			const width = this.getResponsiveDimension(heroElement, 'widthInitial');
			const height = this.getResponsiveDimension(heroElement, 'heightInitial');

			if (width && Number.isFinite(width.value)) {
				heroElement.style.setProperty('--initial-width', width.value + width.unit);
			}

			if (height && Number.isFinite(height.value)) {
				heroElement.style.setProperty('--initial-height', height.value + height.unit);
			}
		},

		/**
		 * Get responsive initial scale value
		 */
		getInitialScaleSetting: function (heroElement) {
			if (!heroElement || !heroElement.dataset) {
				return null;
			}

			const dataset = heroElement.dataset;
			const viewport = window.innerWidth;

			let scale = dataset.initialScale ? parseFloat(dataset.initialScale) : null;

			// Breakpoints Elementor : mobile < 768, tablet <= 1199, desktop > 1199
			if (viewport <= 767 && dataset.initialScaleMobile) {
				scale = parseFloat(dataset.initialScaleMobile);
			} else if (viewport <= 1199 && dataset.initialScaleTablet) {
				scale = parseFloat(dataset.initialScaleTablet);
			}

			if (!Number.isFinite(scale)) {
				return null;
			}

			return Math.min(Math.max(scale, 0.05), 1);
		},

		/**
		 * Get responsive initial dimensions
		 */
		getStartDimensions: function (heroElement) {
			const viewportWidth = window.innerWidth;
			const viewportHeight = window.innerHeight;

			// Get responsive dimension using new system (like stacking cards)
			const widthDim = this.getResponsiveDimension(heroElement, 'widthInitial');
			const heightDim = this.getResponsiveDimension(heroElement, 'heightInitial');

			let width, height;

			if (widthDim) {
				if (widthDim.unit === 'vw') {
					width = (widthDim.value / 100) * viewportWidth;
				} else if (widthDim.unit === '%') {
					width = (widthDim.value / 100) * viewportWidth;
				} else {
					width = widthDim.value; // Already in px
				}
			} else {
				// Fallback
				width = 1061;
			}

			if (heightDim) {
				if (heightDim.unit === 'vh') {
					height = (heightDim.value / 100) * viewportHeight;
				} else if (heightDim.unit === '%') {
					height = (heightDim.value / 100) * viewportHeight;
				} else {
					height = heightDim.value; // Already in px
				}
			} else {
				// Fallback
				height = 700;
			}

			return {
				width,
				height,
				widthValue: widthDim ? widthDim.value : 1061,
				widthUnit: widthDim ? widthDim.unit : 'px',
				heightValue: heightDim ? heightDim.value : 700,
				heightUnit: heightDim ? heightDim.unit : 'px'
			};
		},

		/**
		 * Get responsive max dimensions
		 */
		getMaxDimensions: function (heroElement) {
			const viewportWidth = window.innerWidth;
			const viewportHeight = window.innerHeight;
			const $hero = $(heroElement);

			let width, height, widthUnit, heightUnit;

			// Breakpoints cohérents avec getResponsiveDimension : <= 767 mobile, <= 1199 tablet, > 1199 desktop
			if (viewportWidth <= 767) {
				width = parseFloat($hero.data('width-max-mobile')) || parseFloat($hero.data('width-max-tablet')) || parseFloat($hero.data('width-max')) || 100;
				widthUnit = $hero.data('width-max-mobile-unit') || $hero.data('width-max-tablet-unit') || $hero.data('width-max-unit') || 'vw';
				height = parseFloat($hero.data('height-max-mobile')) || parseFloat($hero.data('height-max-tablet')) || parseFloat($hero.data('height-max')) || 100;
				heightUnit = $hero.data('height-max-mobile-unit') || $hero.data('height-max-tablet-unit') || $hero.data('height-max-unit') || 'vh';
			} else if (viewportWidth <= 1199) {
				width = parseFloat($hero.data('width-max-tablet')) || parseFloat($hero.data('width-max')) || 100;
				widthUnit = $hero.data('width-max-tablet-unit') || $hero.data('width-max-unit') || 'vw';
				height = parseFloat($hero.data('height-max-tablet')) || parseFloat($hero.data('height-max')) || 100;
				heightUnit = $hero.data('height-max-tablet-unit') || $hero.data('height-max-unit') || 'vh';
			} else {
				width = parseFloat($hero.data('width-max')) || 100;
				widthUnit = $hero.data('width-max-unit') || 'vw';
				height = parseFloat($hero.data('height-max')) || 100;
				heightUnit = $hero.data('height-max-unit') || 'vh';
			}

			console.log('[NovaScrollHero] getMaxDimensions — viewport:', viewportWidth, '| raw values:', { width, widthUnit, height, heightUnit });

			// Convert to pixels
			let widthPx, heightPx;

			if (widthUnit === 'vw') {
				widthPx = (width / 100) * viewportWidth;
			} else if (widthUnit === '%') {
				widthPx = (width / 100) * viewportWidth;
			} else {
				widthPx = width;
			}

			if (heightUnit === 'vh') {
				heightPx = (height / 100) * viewportHeight;
			} else if (heightUnit === '%') {
				heightPx = (height / 100) * viewportHeight;
			} else {
				heightPx = height;
			}

			console.log('[NovaScrollHero] getMaxDimensions — converted px:', { widthPx, heightPx });

			return {
				width: widthPx,
				height: heightPx,
				widthValue: width,
				widthUnit: widthUnit,
				heightValue: height,
				heightUnit: heightUnit
			};
		},

		/**
		 * Get Elementor widget settings
		 */
		getElementorSettings: function ($widget) {
			try {
				if (typeof elementorFrontend === 'undefined' || !elementorFrontend.isEditMode()) {
					// Frontend mode - try to get from data attributes
					return {
						animation_scrub: parseFloat($widget.data('animation_scrub')) || 1.5,
						animation_text_delay: parseInt($widget.data('animation_text_delay')) || 0,
						animation_expand_start: parseInt($widget.data('animation_expand_start')) || 5,
						animation_overlay_start: parseInt($widget.data('animation_overlay_start')) || 70,
					};
				}
			} catch (e) {
				// Silent fail, use defaults
			}

			// Return defaults
			return {
				animation_scrub: 1.5,
				animation_text_delay: 0,
				animation_expand_start: 5,
				animation_overlay_start: 70,
			};
		},

		/**
		 * Animate hero - Enhanced Professional Animations
		 */
		animateHero: function (heroElement) {
			// Skip if already initialized
			if ($(heroElement).data('nova-scroll-initialized')) {
				return;
			}
			$(heroElement).data('nova-scroll-initialized', true);

			try {
				const $hero = $(heroElement);
				const $text = $hero.find(this.config.textSelector);
				const $imageContainer = $hero.find(this.config.imageContainerSelector);
				const $overlay = $hero.find(this.config.overlaySelector);
				const $overlayTexts = $overlay.find(this.config.overlayTextSelector).filter(function () {
					return $(this).text().trim().length > 0;
				});
				const $button = $hero.find(this.config.buttonSelector);

				// ✅ Get Elementor settings for this widget
				const $widget = $hero.closest('.elementor-widget-nova-scroll-hero');
				const settings = this.getElementorSettings($widget);

				// Check sticky CSS on image wrapper
				const $imageWrapper = $hero.find('.nova-scroll-hero-image-wrapper');
				if ($imageWrapper.length) {
					const wrapperCS = window.getComputedStyle($imageWrapper[0]);
				}

				// Audit ancestors for overflow issues
				let _p = heroElement.parentElement;
				while (_p && _p !== document.documentElement) {
					const _cs = window.getComputedStyle(_p);
					if (_cs.overflow !== 'visible' || _cs.overflowX !== 'visible' || _cs.overflowY !== 'visible') {
						console.warn('[NovaScrollHero] OVERFLOW PROBLEM on ancestor:', _p.className || _p.tagName, '| overflow:', _cs.overflow, '| overflowX:', _cs.overflowX, '| overflowY:', _cs.overflowY);
						// Fix it
						_p.style.setProperty('overflow', 'visible', 'important');
						_p.style.setProperty('overflow-x', 'visible', 'important');
						_p.style.setProperty('overflow-y', 'visible', 'important');
					}
					_p = _p.parentElement;
				}

				// Use settings or defaults
				const textAnimStart = (settings.animation_text_delay || 0) / 100;
				const expandStart = (settings.animation_expand_start || 5) / 100;
				const overlayStart = (settings.animation_overlay_start || 70) / 100;

				if ($imageContainer.length === 0) {
					return;
				}

				// ✅ Apply size variables initially (like stacking cards)
				this.applySizeVariables(heroElement);

				// Get responsive dimensions
				const startDims = this.getStartDimensions(heroElement);
				const maxDims = this.getMaxDimensions(heroElement);
				const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

				// ===== DEBUG CONFIG =====
				console.log('[NovaScrollHero] === CONFIG DEBUG ===');
				console.log('[NovaScrollHero] viewport:', window.innerWidth, 'x', window.innerHeight);
				console.log('[NovaScrollHero] dataset:', JSON.parse(JSON.stringify(heroElement.dataset)));
				console.log('[NovaScrollHero] startDims:', startDims);
				console.log('[NovaScrollHero] maxDims:', maxDims);
				console.log('[NovaScrollHero] initialScaleSetting (raw):', this.getInitialScaleSetting(heroElement));
				console.log('[NovaScrollHero] settings:', settings);
				console.log('[NovaScrollHero] expandStart:', expandStart, '| overlayStart:', overlayStart);

				// Vérification : startDims doit être < maxDims pour que l'expand fonctionne
				const isExpandValid = maxDims.width >= startDims.width && maxDims.height >= startDims.height;
				console.log('[NovaScrollHero] isExpandValid (maxDims >= startDims):', isExpandValid,
					'| maxW:', maxDims.width.toFixed(0), '>= startW:', startDims.width.toFixed(0),
					'| maxH:', maxDims.height.toFixed(0), '>= startH:', startDims.height.toFixed(0));
				if (!isExpandValid) {
					console.warn('[NovaScrollHero] ⚠️ PROBLÈME: startDims > maxDims → animation inversée ! Vérifiez les valeurs dans Elementor.');
				}
				console.log('[NovaScrollHero] === END CONFIG DEBUG ===');

				// ✅ Auto-correction : si maxDims < startDims, forcer 100vw/100vh
				if (!isExpandValid) {
					console.log('[NovaScrollHero] → Auto-correction: maxDims forcé à 100vw/100vh');
					maxDims.width = window.innerWidth;
					maxDims.height = window.innerHeight;
					maxDims.widthValue = 100;
					maxDims.widthUnit = 'vw';
					maxDims.heightValue = 100;
					maxDims.heightUnit = 'vh';
				}



				// ============================================
				// 📝 ANIMATION TEXTE INITIAL - ScrollTrigger SÉPARÉ
				// ============================================
				const $textBefore = $(heroElement).siblings('.nova-scroll-hero-text').first();
				if ($textBefore.length > 0) {
					const textElement = $textBefore[0];

					// ScrollTrigger pour le texte initial
					gsap.to(textElement, {
						scrollTrigger: {
							trigger: textElement,
							start: 'top top', // ✅ Quand le TOP du texte arrive au top de la viewport
							end: 'top top+=100px',
							scrub: 1.2,
							markers: false,
							invalidateOnRefresh: true,
							filter: 'blur(5px)' // 🔹 Flou initial
						},
						y: '-200px',
						opacity: 0,
						ease: 'power2.in',
						duration: 1,
						filter: 'blur(0px)' // 🔹 Flou initial
					});
				}

				// Get scrub value from settings for smooth animation
				const scrubValue = settings.animation_scrub || 1.5;

				// 🎯 MASTER TIMELINE - Pour l'image et overlay
				const masterTL = gsap.timeline({
					scrollTrigger: {
						trigger: heroElement,
						start: 'top top',
						end: 'bottom bottom',
						scrub: scrubValue,
						markers: false,
						invalidateOnRefresh: true,
						fastScrollEnd: true,
						onRefreshInit: () => { this.applySizeVariables(heroElement); },
						onRefresh: () => { this.applySizeVariables(heroElement); }
					}
				});


				// ============================================
				// 📐 PHASE 2: IMAGE GRANDIT (0% → 40%)
				// ✅ Comme stacking cards : utilise SCALE au lieu de width/height
				// Commence aux dimensions initiales configurées, puis expand par scale
				// ============================================
				// Calculate initial scale based on ratio between initial and max dimensions
				// Like stacking cards: start with scale < 1, then expand to scale: 1
				const initialScaleSetting = this.getInitialScaleSetting(heroElement);
				const initialScaleX = maxDims.width ? (startDims.width / maxDims.width) : 0.7;
				const initialScaleY = maxDims.height ? (startDims.height / maxDims.height) : 0.7;
				// Use the configured scale if provided, otherwise the smaller ratio
				let initialScale = initialScaleSetting !== null ? initialScaleSetting : Math.min(initialScaleX, initialScaleY);
				if (!Number.isFinite(initialScale) || initialScale <= 0) {
					initialScale = 0.7;
				}
				initialScale = Math.min(Math.max(initialScale, 0.05), 1);

				if (reduceMotion) {
					gsap.set($imageContainer[0], {
						width: maxDims.widthValue + maxDims.widthUnit,
						height: maxDims.heightValue + maxDims.heightUnit,
						borderRadius: '0px',
						scale: 1,
						willChange: 'auto'
					});
				} else {
					// ✅ Read initial dimensions from CSS (set by Elementor selectors) — no FOUC
					const cssWidth = $imageContainer[0].offsetWidth || startDims.width;
					const cssHeight = $imageContainer[0].offsetHeight || startDims.height;

					gsap.set($imageContainer[0], {
						borderRadius: '8px',
						scale: 1,
						transformOrigin: 'center center',
						force3D: true,
						willChange: 'transform, width, height, border-radius'
					});

					// Animate from CSS initial dimensions to max dimensions
					masterTL.fromTo($imageContainer[0],
						{
							width: cssWidth,
							height: cssHeight,
							borderRadius: '8px'
						},
						{
							width: maxDims.widthValue + maxDims.widthUnit,
							height: maxDims.heightValue + maxDims.heightUnit,
							borderRadius: '0px',
							ease: 'power2.inOut',
							duration: 0.5,
							force3D: true,
							onComplete: () => {
								gsap.set($imageContainer[0], { willChange: 'auto', force3D: false });
							}
						}, 0);
				}


				// ============================================
				// 🎨 PHASE 3: OVERLAY BACKGROUND - FADE SEULEMENT (50%)
				// ============================================

				if ($overlay.length > 0) {
					// Overlay background fade in seulement (pas de montée)
					masterTL.to($overlay[0], {
						opacity: 1,
						duration: reduceMotion ? 0 : 0.5,
						ease: 'power2.out',
						willChange: 'opacity',
						onComplete: () => gsap.set($overlay[0], { willChange: 'auto' })
					}, 0.5);
				}

				// ============================================
				// 📝 PHASE 4: TOUS LES CONTENUS ENSEMBLE (70%)
				// ============================================

				// Textes avec montée de bas en haut
				if ($overlayTexts.length > 0) {
					$overlayTexts.each((i, textElement) => {
						gsap.set(textElement, { willChange: 'transform, opacity' });
						masterTL.fromTo(textElement,
							{
								opacity: reduceMotion ? 1 : 0,
								y: reduceMotion ? 0 : 80
							},
							{
								opacity: 1,
								y: 0,
								duration: reduceMotion ? 0 : 0.5,
								ease: 'power3.out',
								onComplete: () => gsap.set(textElement, { willChange: 'auto' })
							},
							0.7
						);
					});
				}

				// ============================================
				// 🔘 BOUTON - Même moment que les textes (70%)
				// ============================================
				if ($button.length > 0) {
					gsap.set($button[0], { willChange: 'transform, opacity' });
					masterTL.fromTo($button[0],
						{
							opacity: reduceMotion ? 1 : 0,
							y: reduceMotion ? 0 : 80
						},
						{
							opacity: 1,
							y: 0,
							duration: reduceMotion ? 0 : 0.5,
							ease: 'power3.out',
							onComplete: () => gsap.set($button[0], { willChange: 'auto' })
						},
						0.7
					);
				}

				// ============================================
				// 🎬 PHASE 6: MAINTIEN FINAL (85% → 100%)
				// ============================================
				masterTL.to({}, { duration: 0.15 }, 0.85);

				this.timelines.push(masterTL);

			} catch (error) {
				// Silent error handling
			}
		},

		/**
		 * Destroy all timelines
		 */
		destroy: function () {
			// Disconnect Intersection Observer if exists
			if (this.intersectionObserver) {
				this.intersectionObserver.disconnect();
				this.intersectionObserver = null;
			}

			this.timelines.forEach(tl => {
				if (tl && tl.scrollTrigger) {
					tl.scrollTrigger.kill();
				}
				if (tl) {
					tl.kill();
				}
			});

			this.timelines = [];

			if (typeof ScrollTrigger !== 'undefined') {
				ScrollTrigger.getAll().forEach(trigger => {
					const target = trigger && trigger.vars ? trigger.vars.trigger : null;
					const pin = trigger ? trigger.pin : null;

					const isHeroTrigger =
						(target && target.classList && (target.classList.contains('nova-scroll-hero-text') || target.classList.contains('nova-scroll-hero'))) ||
						(target && target.closest && target.closest('.nova-scroll-hero')) ||
						(pin && pin.closest && pin.closest('.nova-scroll-hero'));

					if (isHeroTrigger) {
						trigger.kill();
					}
				});
			}

			// Reset initialized flag
			$(this.config.heroSelector).removeData('nova-scroll-initialized');
			this.initialized = false;
		},

		/**
		 * Reinitialize
		 */
		reinit: function () {
			this.destroy();
			// Use requestAnimationFrame for smoother reinit
			if (window.requestAnimationFrame) {
				requestAnimationFrame(() => {
					this.initialized = false;
					this.init();
					if (typeof ScrollTrigger !== 'undefined') {
						ScrollTrigger.refresh();
					}
				});
			} else {
				setTimeout(() => {
					this.initialized = false;
					this.init();
					if (typeof ScrollTrigger !== 'undefined') {
						ScrollTrigger.refresh();
					}
				}, 100);
			}
		}

	};

	/**
	 * Initialize on DOM ready
	 */
	$(document).ready(function () {
		NovaScrollHero.init();
	});

	/**
	 * Refresh on page load
	 */
	$(window).on('load', function () {
		if (typeof ScrollTrigger !== 'undefined') {
			ScrollTrigger.refresh();
		}
	});

	/**
	 * Elementor integration
	 */
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined') {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/nova-scroll-hero.default',
				function () {
					NovaScrollHero.reinit();
				}
			);
		}
	});

	/**
	 * Optimized resize handler with requestAnimationFrame
	 */
	let resizeTimer;
	let lastWidth = window.innerWidth;
	let resizeRAF = null;

	function handleResize() {
		if (resizeRAF) {
			cancelAnimationFrame(resizeRAF);
		}

		resizeRAF = requestAnimationFrame(function () {
			const currentWidth = window.innerWidth;

			if (Math.abs(currentWidth - lastWidth) > 50) {
				lastWidth = currentWidth;
				// ✅ Re-apply size variables on resize (like stacking cards)
				const $heroes = $(NovaScrollHero.config.heroSelector);
				$heroes.each(function () {
					NovaScrollHero.applySizeVariables(this);
				});
				NovaScrollHero.reinit();
			}
		});
	}

	// Use passive event listener for better performance
	$(window).on('resize', handleResize);

	/**
	 * Expose globally
	 */
	window.NovaScrollHero = NovaScrollHero;

})(jQuery);


