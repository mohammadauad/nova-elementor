/**
 * NOVA Features Widget - Animation
 * Version: 1.0.0
 */

(function ($) {
	'use strict';

	const NOVAFeatures = {
		instances: [],

		isElementorEditor: function () {
			return typeof elementorFrontend !== 'undefined'
				&& typeof elementorFrontend.isEditMode === 'function'
				&& elementorFrontend.isEditMode();
		},

		/**
		 * Initialize all feature widgets
		 */
		init: function () {
			// Elementor hook
			if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
				elementorFrontend.hooks.addAction('frontend/element_ready/nova-features.default', function ($scope) {
					const $widget = $scope.find('.nova-features-widget');
					if ($widget.length > 0) {
						NOVAFeatures.initInstance($widget);
					}
				});
			}

			// Immediate check if Elementor is already loaded
			if (typeof elementorFrontend !== 'undefined') {
				$('.nova-features-widget').each(function () {
					const $widget = $(this);
					if (!$widget.data('nova-features-initialized')) {
						NOVAFeatures.initInstance($widget);
					}
				});
			}

			// DOM ready fallback
			$(document).ready(function () {
				$('.nova-features-widget').each(function () {
					const $widget = $(this);
					if (!$widget.data('nova-features-initialized')) {
						NOVAFeatures.initInstance($widget);
					}
				});
			});
		},

		/**
		 * Initialize a single widget instance
		 */
		initInstance: function ($widget) {
			if ($widget.data('nova-features-initialized')) {
				return;
			}

			$widget.data('nova-features-initialized', true);

			const animationConfig = $widget.data('animation-config');
			if (!animationConfig || !animationConfig.enable) {
				// No animation, just show items
				$widget.find('.nova-feature-item').css({
					'opacity': '1',
					'transform': 'translateY(0)',
					'filter': 'blur(0)'
				});
				return;
			}

			// Set initial state
			const $items = $widget.find('.nova-feature-item');
			$items.css({
				'opacity': '0',
				'transform': 'translateY(' + animationConfig.translateY + 'px)',
				'filter': 'blur(5px)',
				'transition': 'opacity ' + animationConfig.duration + 'ms ease, transform ' + animationConfig.duration + 'ms ease, filter ' + animationConfig.duration + 'ms ease'
			});

			// Reveal wrapper so CSS knows JS is ready and takes over layout
			$widget.addClass('nova-js-ready');

			// Animate
			if (animationConfig.simultaneous) {
				// All items animate at once
				setTimeout(function () {
					$items.css({
						'opacity': '1',
						'transform': 'translateY(0)',
						'filter': 'blur(0)'
					});
				}, animationConfig.delay);
			} else {
				// Staggered animation
				$items.each(function (index) {
					const $item = $(this);
					setTimeout(function () {
						$item.css({
							'opacity': '1',
							'transform': 'translateY(0)',
							'filter': 'blur(0)'
						});
					}, animationConfig.delay + (index * animationConfig.stagger));
				});
			}
		}
	};

	// Initialize on page load
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', NOVAFeatures.init);
	} else {
		NOVAFeatures.init();
	}

	// Re-initialize on Elementor preview refresh
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			setTimeout(function () {
				$('.nova-features-widget').each(function () {
					const $widget = $(this);
					if (!$widget.data('nova-features-initialized')) {
						NOVAFeatures.initInstance($widget);
					}
				});
			}, 100);
		});
	}

})(jQuery);

