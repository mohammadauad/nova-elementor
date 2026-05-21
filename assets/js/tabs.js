/**
 * NOVA Tabs Widget
 */
(function ($) {
	'use strict';

	function initTabs($widget) {
		// Event delegation allows reliable clicking even on duplicated items inside shown/hidden panels
		$widget.off('click', '.nova-tab-btn').on('click', '.nova-tab-btn', function (e) {
			e.preventDefault();
			var $btn   = $(this);
			var index  = $btn.attr('data-tab');
			var $panels = $widget.find('.nova-tab-panel');
			var $panel = $panels.filter('[id$="-' + index + '"]'); // Better matching by ID suffix

			// If filter fallback
			if ($panel.length === 0) {
				$panel = $panels.eq(index);
			}

			if ($btn.hasClass('nova-tab-active')) return;

			// Dynamically find and update ALL copies of the buttons
			$widget.find('.nova-tab-btn').removeClass('nova-tab-active').attr('aria-selected', 'false');
			$widget.find('.nova-tab-btn[data-tab="' + index + '"]').addClass('nova-tab-active').attr('aria-selected', 'true');

			// Update panels
			$panels.removeClass('nova-tab-panel-active').attr('aria-hidden', 'true');
			$panel.addClass('nova-tab-panel-active').attr('aria-hidden', 'false');
		});
	}

	$(document).ready(function () {
		$('.nova-tabs-widget').each(function () {
			initTabs($(this));
		});
	});

	// Elementor editor support
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction('frontend/element_ready/nova-tabs.default', function ($scope) {
			$scope.find('.nova-tabs-widget').each(function () {
				initTabs($(this));
			});
		});
	}

})(jQuery);
