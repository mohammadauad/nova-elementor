/**
 * NOVA Brands Widget - Slider et Animations
 * Version: 1.0.0
 */

(function($) {
	'use strict';

	const NovaBrands = {
		instances: [],
		isDebug: function () {
			try {
				if (window.NOVA_DEBUG_BRANDS === true) return true;
				if (window.NOVA_DEBUG === true) return true;
				if (localStorage.getItem('NOVA_DEBUG_BRANDS') === '1') return true;
				const params = new URLSearchParams(window.location.search || '');
				return params.has('nova_debug_brands');
			} catch (e) {
				return false;
			}
		},
		debug: function () {
			if (!this.isDebug() || typeof console === 'undefined' || !console.log) return;
			try {
				console.log.apply(console, arguments);
			} catch (e) {}
		},

	/**
	 * Initialize
	 */
	init: function() {
		// Wait for DOM and Swiper to be ready
		if (typeof jQuery === 'undefined') {
			return;
		}

		// Wait for Swiper to be available
		if (typeof Swiper === 'undefined') {
			// Try to wait a bit for Swiper to load (Elementor loads it)
			let attempts = 0;
			const maxAttempts = 50; // 5 seconds max wait
			const checkSwiper = setInterval(() => {
				attempts++;
				if (typeof Swiper !== 'undefined') {
					clearInterval(checkSwiper);
					this.initInstances();
				} else if (attempts >= maxAttempts) {
					clearInterval(checkSwiper);
					this.loadSwiper();
				}
			}, 100);
		} else {
			this.initInstances();
		}
	},

		/**
		 * Load Swiper from CDN (fallback only)
		 */
		loadSwiper: function() {
			// Check if already loading
			if (window.NovaBrandsLoadingSwiper) {
				return;
			}
			window.NovaBrandsLoadingSwiper = true;

			// Load CSS only if not already loaded
			if (!document.querySelector('link[href*="swiper"]')) {
				const cssLink = document.createElement('link');
				cssLink.rel = 'stylesheet';
				cssLink.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
				document.head.appendChild(cssLink);
			}

			// Load JS
			const jsScript = document.createElement('script');
			jsScript.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
			jsScript.onload = () => {
				window.NovaBrandsLoadingSwiper = false;
				this.initInstances();
			};
			jsScript.onerror = () => {
				window.NovaBrandsLoadingSwiper = false;
			};
			document.head.appendChild(jsScript);
		},

	/**
	 * Initialize all brand instances
	 */
	initInstances: function() {
		const self = this;
		
		// Use jQuery if available, otherwise vanilla JS
		if (typeof jQuery !== 'undefined') {
			jQuery('.nova-brands-widget').each(function(index, element) {
				self.initInstance(jQuery(element));
			});
		} else {
			// Fallback to vanilla JS
			document.querySelectorAll('.nova-brands-widget').forEach((element) => {
				// Create a jQuery-like object for compatibility
				const $element = typeof jQuery !== 'undefined' ? jQuery(element) : {
					find: (selector) => {
						const found = element.querySelectorAll(selector);
						return {
							length: found.length,
							each: (callback) => {
								found.forEach((el, i) => callback(i, el));
							},
							css: (props) => {
								if (typeof props === 'string') {
									return window.getComputedStyle(element)[props];
								}
								Object.keys(props).forEach(key => {
									element.style[key] = props[key];
								});
							},
							data: (key, value) => {
								if (value !== undefined) {
									element.dataset[key.replace(/-/g, '')] = value;
									return this;
								}
								return element.dataset[key.replace(/-/g, '')];
							}
						};
					},
					data: (key, value) => {
						if (value !== undefined) {
							element.dataset[key.replace(/-/g, '')] = value;
							return $element;
						}
						return element.dataset[key.replace(/-/g, '')];
					}
				};
				self.initInstance($element);
			});
		}
	},

		/**
		 * Initialize a single brand instance
		 */
		initInstance: function($widget) {
			// Skip if already initialized
			if ($widget.data('nova-brands-initialized')) {
				return;
			}
			
			// Vérifier que le widget existe
			if (!$widget || $widget.length === 0) {
				// Widget element not found
				return;
			}
			
		
			
			$widget.data('nova-brands-initialized', true);

			const enableSlider = $widget.data('enable-slider') === '1' || $widget.data('enable-slider') === 1;
			let sliderConfig = $widget.data('slider-config') || {};
			const animationConfig = $widget.data('animation-config') || {};
			
			// Si sliderConfig est une string JSON, la parser
			if (typeof sliderConfig === 'string') {
				try {
					// Décode les entités HTML (comme &quot;)
					const decoded = sliderConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
					sliderConfig = JSON.parse(decoded);
				} catch (e) {
					// Error parsing slider config
					sliderConfig = {};
				}
			}

			// Initialize main widget animation (block animation)
			if (animationConfig.enable !== false) {
				this.initWidgetAnimation($widget, animationConfig);
			} else {
				// If animation disabled, show immediately
				$widget.addClass('animated');
			}

			// Initialize slider if enabled
			if (enableSlider) {
				// Wait for Swiper to be available
				if (typeof Swiper !== 'undefined') {
					this.initSlider($widget, sliderConfig);
				} else {
					// Wait for Swiper to load
					let swiperAttempts = 0;
					const maxSwiperAttempts = 50;
					const checkSwiper = setInterval(() => {
						swiperAttempts++;
						if (typeof Swiper !== 'undefined') {
							clearInterval(checkSwiper);
							this.initSlider($widget, sliderConfig);
						} else if (swiperAttempts >= maxSwiperAttempts) {
							clearInterval(checkSwiper);
							// Swiper not found after waiting
						}
					}, 100);
				}
			} else {
				// Grid mode: appliquer les colonnes responsive dans l'éditeur
				this.initGrid($widget);
			}

			// Initialize individual item animations (for grid layout only, optional)
			// Note: Individual animations are disabled by default, only block animation is used
			// if (animationConfig.enable !== false && !enableSlider && animationConfig.animateItems) {
			// 	this.initAnimations($widget, animationConfig);
			// }
		},

		/**
		 * Initialize main widget block animation
		 */
		initWidgetAnimation: function($widget, config) {
			const self = this;
			
			// Get translateY from config or use default
			const translateY = config.translateY !== undefined ? parseInt(config.translateY) : 30;
			
			// Helper function to reset animation state
			const resetAnimation = function() {
				$widget.removeClass('animated');
				$widget.css({
					opacity: '0',
					transform: `translateY(${translateY}px)`,
					filter: 'blur(5px)',
					transition: 'none'
				});
			};
			
			// Helper function to trigger animation
			const triggerAnimation = function() {
				const delay = parseInt(config.delay) || 0;
				let duration = parseInt(config.duration);
				// If duration is 0 or not set, use default 800ms
				if (!duration || duration <= 0) {
					duration = 800;
				}
				
				setTimeout(() => {
					// Set transition first
					$widget.css({
						transition: `opacity ${duration}ms ease, transform ${duration}ms ease, filter ${duration}ms ease`
					});
					
					// Force reflow to ensure transition is applied
					$widget[0].offsetHeight;
					
					// Remove inline styles and add animated class
					$widget.css({
						opacity: '',
						transform: '',
						filter: ''
					});
					
					// Add animated class which will trigger the CSS transition
					$widget.addClass('animated');
				}, delay);
			};
			
			// Set initial state (translateY is applied in resetAnimation)
			resetAnimation();

			// Use Intersection Observer for better performance
			if ('IntersectionObserver' in window) {
				const observer = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							// Trigger animation when entering viewport
							triggerAnimation();
						} else {
							// Reset animation state when leaving viewport
							resetAnimation();
						}
					});
				}, {
					threshold: 0.1,
					rootMargin: '50px',
				});

				observer.observe($widget[0]);
			} else {
				// Fallback for browsers without Intersection Observer
				triggerAnimation();
			}
		},

		/**
		 * Initialize Swiper slider
		 */
		initSlider: function($widget, config) {
			
			const $swiper = $widget.find('.nova-brands-swiper');

			if ($swiper.length === 0) {
				// Swiper container not found
				return;
			}

			// Check if Swiper is already initialized on this element
			if ($swiper[0].swiper) {
				return;
			}

			// Check if Swiper class is available
			if (typeof Swiper === 'undefined') {
				// Swiper class is not defined
				return;
			}

			const animationConfig = $widget.data('animation-config') || {};
			const widgetId = $widget.attr('data-id') || $widget.attr('id') || '(unknown)';
			this.debug('[NOVA Brands] initSlider start', { widgetId, config, animationConfig });

			// Swiper configuration
			const autoWidth = config.autoWidth === true || config.autoWidth === 'true' || config.autoWidth === 'yes';
			
			// Note: Les breakpoints Swiper fonctionnent comme des media queries min-width
			// Le breakpoint le plus bas est utilisé par défaut, puis les plus grands s'appliquent
			// Convertir les valeurs en nombres pour s'assurer qu'elles sont correctes (sauf si autoWidth)
			let slidesPerView = config.slidesPerView;
			let slidesPerViewTablet = config.slidesPerViewTablet;
			let slidesPerViewMobile = config.slidesPerViewMobile;
			
			if (!autoWidth) {
				slidesPerView = parseInt(slidesPerView) || 4;
				slidesPerViewTablet = parseInt(slidesPerViewTablet) || 3;
				slidesPerViewMobile = parseInt(slidesPerViewMobile) || 2;
			}
			
			
			const spaceBetween = parseInt(config.spaceBetween) || 30;
			
			// Configuration de base
			const swiperConfig = {
				spaceBetween: spaceBetween,
				loop: config.loop === true || config.loop === 'true' || config.loop === 'yes',
				speed: parseInt(config.speed) || 600,
				initialSlide: 0,
				watchOverflow: true,
				centeredSlidesBounds: true,
				centerInsufficientSlides: true,
				autoplay: (config.autoplay === true || config.autoplay === 'true' || config.autoplay === 'yes') ? {
					delay: parseInt(config.autoplayDelay) || 3000,
					disableOnInteraction: false,
					pauseOnMouseEnter: true,
				} : false,
			};
			
			// Si autoWidth est activé, utiliser slidesPerView: 'auto'
			if (autoWidth) {
				swiperConfig.slidesPerView = 'auto';
				swiperConfig.autoWidth = true;
			} else {
				swiperConfig.slidesPerView = slidesPerViewMobile; // Valeur par défaut (mobile)
				swiperConfig.breakpoints = {
					// Mobile (0px+) - Valeur par défaut
					0: {
						slidesPerView: slidesPerViewMobile,
						spaceBetween: 15,
					},
					// Tablet (768px+)
					768: {
						slidesPerView: slidesPerViewTablet,
						spaceBetween: spaceBetween,
					},
					// Desktop (1024px+)
					1024: {
						slidesPerView: slidesPerView,
						spaceBetween: spaceBetween,
					},
				};
			}
			
			// Observer pour mettre à jour lors des changements DOM
			swiperConfig.observer = true;
			swiperConfig.observeParents = true;
			
			// Swiper event handlers
			const self = this;
			swiperConfig.on = {
				init: function() {
					self.debug('[NOVA Brands] Swiper init', {
						widgetId,
						activeIndex: this.activeIndex,
						realIndex: this.realIndex,
						isBeginning: this.isBeginning,
						isEnd: this.isEnd,
						params: this.params
					});
					// Ensure all slides are visible (they inherit animation from widget)
					if (this.slides && this.slides.length > 0) {
						this.slides.forEach((slide) => {
							slide.style.opacity = '1';
							slide.style.transform = 'translateY(0)';
							slide.style.filter = 'blur(0)';
						});
					}
				},
				slideChangeTransitionStart: function() {
					self.debug('[NOVA Brands] slideChangeTransitionStart', {
						widgetId,
						activeIndex: this.activeIndex,
						realIndex: this.realIndex,
						isBeginning: this.isBeginning,
						isEnd: this.isEnd,
					});
					// Ensure all slides are visible
					if (!this.slides) return;
					
					this.slides.forEach((slide) => {
						slide.style.opacity = '1';
						slide.style.transform = 'translateY(0)';
						slide.style.filter = 'blur(0)';
					});
				},
				slideChangeTransitionEnd: function() {
					self.debug('[NOVA Brands] slideChangeTransitionEnd', {
						widgetId,
						activeIndex: this.activeIndex,
						realIndex: this.realIndex,
						isBeginning: this.isBeginning,
						isEnd: this.isEnd,
					});
					// Ensure all slides are visible
					if (!this.slides) return;
					
					this.slides.forEach((slide) => {
						slide.style.opacity = '1';
						slide.style.transform = 'translateY(0)';
						slide.style.filter = 'blur(0)';
					});
				},
				reachBeginning: function () {
					self.debug('[NOVA Brands] reachBeginning', { widgetId, activeIndex: this.activeIndex, realIndex: this.realIndex });
				},
				reachEnd: function () {
					self.debug('[NOVA Brands] reachEnd', { widgetId, activeIndex: this.activeIndex, realIndex: this.realIndex });
				},
				fromEdge: function () {
					self.debug('[NOVA Brands] fromEdge', { widgetId, activeIndex: this.activeIndex, realIndex: this.realIndex });
				},
			};

			// Add navigation if enabled
			if (config.navigation) {
				swiperConfig.navigation = {
					nextEl: $swiper.find('.swiper-button-next')[0],
					prevEl: $swiper.find('.swiper-button-prev')[0],
				};
			}

			// Add pagination if enabled
			if (config.pagination) {
				swiperConfig.pagination = {
					el: $swiper.find('.swiper-pagination')[0],
					clickable: true,
				};
			}

			// Slides don't need individual animation since the whole widget is animated
			// Keep slides visible by default (they inherit from widget animation)
			$swiper.find('.swiper-slide').each(function() {
				const $slide = $(this);
				$slide.css({
					opacity: '1',
					transform: 'translateY(0)',
					filter: 'blur(0)'
				});
			});

			// Add class for auto-width mode
			if (autoWidth) {
				$swiper.addClass('swiper-auto-width');
			}

			// Initialize Swiper
			let swiper;
			try {
				if (typeof Swiper === 'undefined') {
					// Swiper is not defined
					return;
				}
				
				
				swiper = new Swiper($swiper[0], swiperConfig);
				this.debug('[NOVA Brands] Swiper created', { widgetId, swiper });
				
				// Store reference for debugging
				$swiper[0].swiper = swiper;
				
			} catch (error) {
				// Error initializing Swiper
				return;
			}

			// Vérifier et corriger slidesPerView après initialisation si nécessaire (only if not autoWidth)
			if (!autoWidth) {
				setTimeout(() => {
					const currentWidth = window.innerWidth;
					let expectedSlides = slidesPerViewMobile;
					
					if (currentWidth >= 1024) {
						expectedSlides = slidesPerView;
					} else if (currentWidth >= 768) {
						expectedSlides = slidesPerViewTablet;
					}
					
					if (swiper && swiper.params && swiper.params.slidesPerView !== expectedSlides) {
						// Forcer la mise à jour
						swiper.params.slidesPerView = expectedSlides;
						swiper.update();
					}
				}, 100);
			}



			// Store instance
			if (swiper) {
				this.instances.push({
					widget: $widget,
					swiper: swiper,
				});
			}

			// Handle resize - update Swiper when window resizes
			if (swiper) {
				let resizeTimer;
				$(window).on('resize.nova-brands-' + this.instances.length, () => {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(() => {
						if (swiper && typeof swiper.update === 'function') {
							swiper.update();
							if (typeof swiper.updateSlidesClasses === 'function') {
								swiper.updateSlidesClasses();
							}
						}
					}, 250);
				});
			}
		},

		/**
		 * Initialize grid responsive (for editor device mode)
		 */
		initGrid: function($widget) {
			const $grid = $widget.find('.nova-brands-grid-inner');
			if (!$grid.length) return;

			const gridEl = $grid[0];

			// Lire les valeurs depuis les attributs data ou style inline
			// getPropertyValue fonctionne pour les CSS vars dans style inline
			const getInlineVar = function(el, varName) {
				// Méthode 1: getPropertyValue (fonctionne si la var est dans style inline)
				const val = el.style.getPropertyValue(varName).trim();
				if (val) return parseInt(val);
				// Méthode 2: parser l'attribut style directement
				const styleAttr = el.getAttribute('style') || '';
				const match = styleAttr.match(new RegExp(varName.replace('--', '--') + '\\s*:\\s*(\\d+)'));
				if (match) return parseInt(match[1]);
				return null;
			};

			const applyGridDevice = function() {
				let device = 'desktop';

				// En mode éditeur, utiliser l'API Elementor
				if (window.parent && window.parent.elementor) {
					try {
						const mode = window.parent.elementor.channels.deviceMode.request('currentMode');
						if (mode === 'mobile' || mode === 'mobile_extra') device = 'mobile';
						else if (mode === 'tablet' || mode === 'tablet_extra') device = 'tablet';
					} catch(e) {}
				} else {
					// Frontend: utiliser la largeur de fenêtre
					if (window.innerWidth < 768) device = 'mobile';
					else if (window.innerWidth < 1025) device = 'tablet';
				}

				const cols       = getInlineVar(gridEl, '--grid-cols') || 4;
				const colsTablet = getInlineVar(gridEl, '--grid-cols-tablet') || cols;
				const colsMobile = getInlineVar(gridEl, '--grid-cols-mobile') || 2;

				let activeCols = cols;
				if (device === 'tablet') activeCols = colsTablet;
				else if (device === 'mobile') activeCols = colsMobile;

				gridEl.style.setProperty('--grid-cols-current', activeCols);
			};

			applyGridDevice();

			// Écouter les changements de device dans l'éditeur
			if (window.parent && window.parent.elementor) {
				try {
					window.parent.elementor.channels.deviceMode.on('change', function() {
						setTimeout(applyGridDevice, 50);
					});
				} catch(e) {}
			}

			// Fallback resize pour le frontend
			window.addEventListener('resize', function() {
				setTimeout(applyGridDevice, 100);
			});
		},

		/**
		 * Initialize animations for grid layout
		 */
		initAnimations: function($widget, config) {
			const $items = $widget.find('.nova-brands-item');

			if ($items.length === 0) {
				return;
			}

			// Ensure initial state is set
			$items.each(function() {
				const $item = $(this);
				if (!$item.hasClass('animated')) {
					$item.css({
						opacity: '0',
						transform: 'translateY(30px)',
						filter: 'blur(5px)'
					});
				}
			});

			// Use Intersection Observer for better performance
			if ('IntersectionObserver' in window) {
				const observer = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							const $item = $(entry.target);
							const index = $items.index($item);
							const delay = index * (config.delay || 100);
							const duration = config.duration || 800;
							
							setTimeout(() => {
								$item.addClass('animated');
								$item.css({
									transition: `opacity ${duration}ms ease, transform ${duration}ms ease, filter ${duration}ms ease`
								});
								observer.unobserve(entry.target);
							}, delay);
						}
					});
				}, {
					threshold: 0.1,
					rootMargin: '50px',
				});

				$items.each((index, item) => {
					observer.observe(item);
				});
			} else {
				// Fallback for browsers without Intersection Observer
				$items.each((index, item) => {
					const $item = $(item);
					const delay = index * (config.delay || 100);
					const duration = config.duration || 800;
					
					setTimeout(() => {
						$item.addClass('animated');
						$item.css({
							transition: `opacity ${duration}ms ease, transform ${duration}ms ease, filter ${duration}ms ease`
						});
					}, delay);
				});
			}
		},

		/**
		 * Reinitialize (for Elementor editor)
		 */
		reinit: function() {
			$('.nova-brands-widget').each((index, element) => {
				const $widget = $(element);
				$widget.data('nova-brands-initialized', false);
				
				// Destroy existing Swiper instances
				this.instances.forEach((instance) => {
					if (instance.widget[0] === element && instance.swiper) {
						instance.swiper.destroy(true, true);
					}
				});
			});

			this.instances = [];
			this.initInstances();
		},
	};

	/**
	 * Initialize - Multiple strategies for maximum compatibility
	 */
	
	// Strategy 1: Elementor frontend hook (preferred for Elementor widgets)
	$(window).on('elementor/frontend/init', function() {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			elementorFrontend.hooks.addAction(
				'frontend/element_ready/nova-brands.default',
				function($scope) {
					const $widget = $scope.find('.nova-brands-widget');
					if ($widget.length && !$widget.data('nova-brands-initialized')) {
						NovaBrands.initInstance($widget);
					}
				}
			);
		}
	});
	
	// Strategy 2: If Elementor frontend is already loaded (register immediately)
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/nova-brands.default',
			function($scope) {
				const $widget = $scope.find('.nova-brands-widget');
				if ($widget.length && !$widget.data('nova-brands-initialized')) {
					NovaBrands.initInstance($widget);
				}
			}
		);
		
		// Also try to initialize existing widgets
		setTimeout(function() {
			$('.nova-brands-widget').each(function() {
				const $widget = $(this);
				if (!$widget.data('nova-brands-initialized')) {
					NovaBrands.initInstance($widget);
				}
			});
		}, 100);
	}
	
	// Strategy 3: DOM ready fallback (for non-Elementor pages or static HTML)
	$(document).ready(function() {
		// Wait a bit for Elementor and Swiper to initialize first
		setTimeout(function() {
			// If Elementor is not available or widget not initialized via hooks
			const widgets = $('.nova-brands-widget');
			if (widgets.length > 0) {

				widgets.each(function() {
					const $widget = $(this);
					if (!$widget.data('nova-brands-initialized')) {

						NovaBrands.initInstance($widget);
					}
				});
			}
		}, 1500);
	});
	
	// Strategy 4: Window load fallback (last resort)
	$(window).on('load', function() {
		setTimeout(function() {
			const widgets = $('.nova-brands-widget');
			if (widgets.length > 0) {

				widgets.each(function() {
					const $widget = $(this);
					if (!$widget.data('nova-brands-initialized')) {
			
						NovaBrands.initInstance($widget);
					}
				});
			}
		}, 500);
	});
	
	// Strategy 5: Force initialization after Elementor frontend is ready
	if (typeof elementorFrontend !== 'undefined') {
		$(window).on('elementor/frontend/init', function() {
			setTimeout(function() {
				$('.nova-brands-widget').each(function() {
					const $widget = $(this);
					if (!$widget.data('nova-brands-initialized')) {
				
						NovaBrands.initInstance($widget);
					}
				});
			}, 100);
		});
	}

	/**
	 * Expose globally
	 */
	window.NovaBrands = NovaBrands;
	
	// Debug helper: Force initialization manually
	window.NovaBrandsForceInit = function() {
		$('.nova-brands-widget').each(function() {
			const $widget = $(this);
			$widget.data('nova-brands-initialized', false);
			NovaBrands.initInstance($widget);
		});
	};
	


})(jQuery);
