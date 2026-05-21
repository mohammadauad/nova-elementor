/**
 * NOVA Carousel Widget - Owl Carousel
 * Version: 4.0.0
 */
(function ($) {
	'use strict';

	var NOVACarousel = {
		instances: [],

		isEditor: function () {
			if (typeof elementorFrontend !== 'undefined' && typeof elementorFrontend.isEditMode === 'function' && elementorFrontend.isEditMode()) return true;
			try {
				if (window.self !== window.top && window.top.document.body.classList.contains('elementor-editor-active')) return true;
			} catch (e) {}
			return false;
		},

		init: function () {
			var self = this;
			if (typeof $.fn.owlCarousel === 'undefined') {
				var attempts = 0;
				var check = setInterval(function () {
					if (typeof $.fn.owlCarousel !== 'undefined') { clearInterval(check); self.initInstances(); }
					else if (++attempts >= 30) clearInterval(check);
				}, 100);
			} else {
				this.initInstances();
			}
		},

		initInstances: function () {
			var self = this;

			if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
				elementorFrontend.hooks.addAction('frontend/element_ready/nova-carousel.default', function ($scope) {
					var $widget = $scope.find('.nova-carousel-widget');
					if ($widget.length) self.initInstance($widget);
				});
			}

			$(document).ready(function () {
				$('.nova-carousel-widget').each(function () {
					var $widget = $(this);
					if (!$widget.data('nova-initialized')) self.initInstance($widget);
				});
			});
		},

		initInstance: function ($widget) {
			// Mode grid
			if ($widget.hasClass('grid-mode')) {
				var $grid = $widget.find('.nova-carousel-grid');
				if ($grid.length) this.initGrid($grid);
				return;
			}

			// Détruire l'instance existante si elle existe (re-render dans l'éditeur)
			var $slider = $widget.find('.nova-carousel-slider');
			if ($slider.length && $slider.hasClass('owl-loaded')) {
				try { $slider.owlCarousel('destroy'); } catch(e) {}
			}
			$widget.removeData('nova-initialized');

			if ($widget.data('nova-initialized')) return;

			var $slider = $widget.find('.nova-carousel-slider');
			if (!$slider.length) return;

			var configData = $widget.data('slider-config');
			if (!configData) return;

			var config = typeof configData === 'string' ? JSON.parse(configData) : configData;

			var spaceBetween = parseInt(config.spaceBetween) || 20;

			// Icônes nav personnalisées
			var prevIcon = config.navPrevIcon || '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			var nextIcon = config.navNextIcon || '<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

			var owlConfig = {
				// Base
				items:              config.slidesToShow || 3,
				slideBy:            config.slidesToScroll || 1,
				margin:             spaceBetween,
				smartSpeed:         config.speed || 500,
				// Loop / autoplay
				loop:               config.loop || false,
				autoplay:           config.autoplay || false,
				autoplayTimeout:    config.autoplaySpeed || 3000,
				autoplayHoverPause: config.pauseOnHover !== false,
				// Navigation
				nav:                config.showArrows || false,
				dots:               config.showDots || false,
				navText:            [prevIcon, nextIcon],
				navRewind:          config.owlNavRewind !== false,
				navSpeed:           config.owlNavSpeed || false,
				dotsSpeed:          config.owlDotsSpeed || false,
				// Comportement
				center:             config.owlCenter || false,
				stagePadding:       config.owlStagePadding || 0,
				autoWidth:          config.owlAutoWidth || false,
				autoHeight:         config.owlAutoHeight || false,
				rtl:                config.owlRtl || false,
				mouseDrag:          config.owlMouseDrag !== false,
				touchDrag:          config.owlTouchDrag !== false,
				pullDrag:           config.owlPullDrag !== false,
				freeDrag:           config.owlFreeDrag || false,
				// Lazy load
				lazyLoad:           config.owlLazyLoad || false,
				// Animations CSS
				animateOut:         config.owlAnimateOut || false,
				animateIn:          config.owlAnimateIn || false,
				// Divers
				startPosition:      config.owlStartPosition || 0,
				fluidSpeed:         config.owlFluidSpeed || false,
				// Responsive — désactivé si autoWidth (sinon Owl écrase les largeurs)
				responsive: config.owlAutoWidth ? false : {
					0: {
						items:   config.slidesToShowMobile || 1,
						slideBy: config.slidesToScrollMobile || 1,
						margin:  spaceBetween
					},
					768: {
						items:   config.slidesToShowTablet || 2,
						slideBy: config.slidesToScrollTablet || 1,
						margin:  spaceBetween
					},
					1024: {
						items:   config.slidesToShow || 3,
						slideBy: config.slidesToScroll || 1,
						margin:  spaceBetween
					}
				},
				onInitialized: function () {
					NOVACarousel.attachNav($widget, $slider);
				}
			};

			// Si navigation personnalisée existe, désactiver la nav Owl par défaut
			if ($widget.find('.nova-carousel-prev, .nova-carousel-next').length) {
				owlConfig.nav = false;
			}

			try {
				$slider.owlCarousel(owlConfig);
				
				// Si autoWidth: forcer la largeur sur les owl-items après init
				if (config.owlAutoWidth) {
					setTimeout(function() {
						$slider.find('.owl-item').css('width', '');
						$slider.trigger('refresh.owl.carousel');
					}, 50);
				}
			} catch (e) {
				return;
			}

			$widget.data('nova-initialized', true);
			this.instances.push({ widget: $widget, slider: $slider });
		},

		attachNav: function ($widget, $slider) {
			var $prev = $widget.find('.nova-carousel-prev');
			var $next = $widget.find('.nova-carousel-next');
			if (!$prev.length || !$next.length) return;

			$prev.off('click.nova').on('click.nova', function (e) {
				e.preventDefault();
				var owl = $slider.data('owl.carousel');
				if (owl) owl.prev();
			});
			$next.off('click.nova').on('click.nova', function (e) {
				e.preventDefault();
				var owl = $slider.data('owl.carousel');
				if (owl) owl.next();
			});
		},

		initGrid: function ($grid) {
			var el = $grid[0];
			var getVar = function (name) {
				var m = (el.getAttribute('style') || '').match(new RegExp(name + '\\s*:\\s*(\\d+)'));
				return m ? parseInt(m[1]) : null;
			};
			var apply = function () {
				var device = 'desktop';
				if (window.innerWidth < 768) device = 'mobile';
				else if (window.innerWidth < 1025) device = 'tablet';
				var cols = getVar('--grid-cols') || 3;
				var active = device === 'mobile' ? (getVar('--grid-cols-mobile') || 1) : device === 'tablet' ? (getVar('--grid-cols-tablet') || cols) : cols;
				el.style.setProperty('--grid-cols-current', active);
			};
			apply();
			window.addEventListener('resize', function () { setTimeout(apply, 100); });
		}
	};

	$(document).ready(function () { NOVACarousel.init(); });
	window.NOVACarousel = NOVACarousel;

})(jQuery);
