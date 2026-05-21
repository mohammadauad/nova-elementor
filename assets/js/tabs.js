/**
 * NOVA Tabs Widget
 */
(function ($) {
	'use strict';

	function stopAutoplay($widget) {
		var requestId = $widget.data('autoplay-request-id');
		if (requestId) {
			cancelAnimationFrame(requestId);
			$widget.removeData('autoplay-request-id');
		}
	}

	function startAutoplay($widget) {
		var duration = parseInt($widget.attr('data-autoplay-speed')) || 5000;
		var start = null;

		function tick(timestamp) {
			// Check if widget is still in view before continuing animation
			if ($widget.data('autoplay-paused')) {
				start = null; // Reset start so it resumes correctly when visible
				$widget.data('autoplay-request-id', requestAnimationFrame(tick));
				return;
			}

			if (!start) start = timestamp;
			var progress = timestamp - start;
			var p = Math.min(progress / duration, 1);

			// Synchronize all buttons that are active (there might be duplicates in different panels)
			var $activeBtns = $widget.find('.nova-tab-btn.nova-tab-active');
			$activeBtns.each(function() {
				this.style.setProperty('--nova-tab-progress', p);
			});

			if (p < 1) {
				$widget.data('autoplay-request-id', requestAnimationFrame(tick));
			} else {
				// Switch to next tab
				var currentIndex = parseInt($activeBtns.first().attr('data-tab'));
				// We look at the first nav to find the count
				var $firstNav = $widget.find('.nova-tabs-nav').first();
				var totalTabs = $firstNav.find('.nova-tab-btn').length;
				var nextIndex = (currentIndex + 1) % totalTabs;
				
				// Trigger click on the next tab button
				$widget.find('.nova-tab-btn[data-tab="' + nextIndex + '"]').first().trigger('click');
			}
		}

		$widget.data('autoplay-request-id', requestAnimationFrame(tick));
	}

	function initContentFade($widget) {
		if ($widget.attr('data-content-fade') !== '1') {
			return;
		}
		if ($widget.data('nova-tabs-fade-inited')) {
			return;
		}
		$widget.data('nova-tabs-fade-inited', true);

		var reducedMotion = typeof window.matchMedia === 'function' &&
			window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reducedMotion) {
			$widget.addClass('nova-tabs--fade-in');
			return;
		}

		var trigger = $widget.attr('data-content-fade-trigger') || 'both';
		var node = $widget[0];

		// Préparer l'état caché initial avant tout paint visible.
		$widget.addClass('nova-tabs--fade-armed');

		var armed = false;
		function fireIn() {
			if (armed) return;
			armed = true;
			// Two-RAF pour garantir que l'état "armed" est peint avant la transition.
			requestAnimationFrame(function () {
				requestAnimationFrame(function () {
					$widget.removeClass('nova-tabs--fade-armed').addClass('nova-tabs--fade-in');
				});
			});
		}

		if (trigger === 'load') {
			if (document.readyState === 'complete') {
				fireIn();
			} else {
				window.addEventListener('load', fireIn, { once: true });
			}
			return;
		}

		// trigger === 'scroll' OR 'both'
		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (entries, obs) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						fireIn();
						obs.unobserve(entry.target);
					}
				});
			}, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
			io.observe(node);

			if (trigger === 'both') {
				// Filet de sécurité : si jamais l'utilisateur ne scrolle pas et que
				// le widget est déjà au-dessus du fold, on déclenche au load.
				var safetyFire = function () {
					var rect = node.getBoundingClientRect();
					var vh = window.innerHeight || document.documentElement.clientHeight;
					if (rect.top < vh && rect.bottom > 0) {
						fireIn();
						io.unobserve(node);
					}
				};
				if (document.readyState === 'complete') {
					safetyFire();
				} else {
					window.addEventListener('load', safetyFire, { once: true });
				}
			}
		} else {
			// Pas d'IO → on déclenche au load.
			if (document.readyState === 'complete') {
				fireIn();
			} else {
				window.addEventListener('load', fireIn, { once: true });
			}
		}
	}

	function initTabs($widget) {
		var isAutoplay = $widget.attr('data-autoplay') === 'true';
		var fadeReplay = $widget.attr('data-content-fade') === '1' &&
			$widget.attr('data-content-fade-replay') === '1';

		initContentFade($widget);

		// Handle tab clicks
		$widget.off('click', '.nova-tab-btn').on('click', '.nova-tab-btn', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var index = $btn.attr('data-tab');
			var $panels = $widget.find('.nova-tab-panel');
			var widgetId = $widget.attr('id').replace('nova-tabs-', '');
			var $panel = $widget.find('#nova-tab-' + widgetId + '-' + index);

			if ($btn.hasClass('nova-tab-active')) return;

			// Reset progress on all buttons before switching
			$widget.find('.nova-tab-btn').each(function() {
				this.style.setProperty('--nova-tab-progress', 0);
			});

			// Update ALL copies of the buttons
			$widget.find('.nova-tab-btn').removeClass('nova-tab-active').attr('aria-selected', 'false');
			$widget.find('.nova-tab-btn[data-tab="' + index + '"]').addClass('nova-tab-active').attr('aria-selected', 'true');

			// Update panels
			$panels.removeClass('nova-tab-panel-active').attr('aria-hidden', 'true');
			$panel.addClass('nova-tab-panel-active').attr('aria-hidden', 'false');

			// Replay du fade sur le panel actif (option content_fade_replay).
			if (fadeReplay) {
				// Retire/ré-applique l'animation CSS en réinitialisant la classe.
				$panel.removeClass('nova-tab-panel-active');
				// reflow forcé
				void $panel[0].offsetWidth;
				$panel.addClass('nova-tab-panel-active');
			}

			// Restart autoplay if enabled
			if (isAutoplay) {
				stopAutoplay($widget);
				startAutoplay($widget);
			}
		});

		// Intersection Observer for autoplay
		if (isAutoplay && 'IntersectionObserver' in window) {
			var observer = new IntersectionObserver(function(entries) {
				entries.forEach(function(entry) {
					if (entry.isIntersecting) {
						// Resume or start autoplay
						$widget.data('autoplay-paused', false);
						if (!$widget.data('autoplay-request-id')) {
							startAutoplay($widget);
						}
					} else {
						// Pause autoplay when out of view
						$widget.data('autoplay-paused', true);
					}
				});
			}, { threshold: 0.2 }); // Trigger when 20% visible

			observer.observe($widget[0]);
		} else if (isAutoplay) {
			// Fallback if IntersectionObserver is not supported
			stopAutoplay($widget);
			startAutoplay($widget);
		}
	}

	$(document).ready(function () {
		$('.nova-tabs-widget').each(function () {
			initTabs($(this));
		});
	});

	// Elementor editor support
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction('frontend/element_ready/nova-tabs.default', function ($scope) {
			var $widget = $scope.find('.nova-tabs-widget');
			if ($widget.length) {
				initTabs($widget);
			}
		});
	}

})(jQuery);
