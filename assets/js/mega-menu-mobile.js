/**
 * NOVA Mega Menu - Mobile Breadcrumb Handler
 * Version: 1.0.0
 */

(function ($) {
	'use strict';

	function setupIconMenuManually() {
		// Nettoyer les éléments vides
		$('.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-has-submenu, .nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .menu-item-has-children').each(function () {
			var $li = $(this);
			var $submenu = $li.children('.sub-menu');
			if ($submenu.length === 0 || $submenu.children('li').length === 0) {
				$li.removeClass('nova-has-submenu menu-item-has-children');
				$li.find('.NOVA-dropdown-icon').remove();
				$li.children('a').removeClass('nova-submenu-toggle');
			}
		});

		$(document).off('click.NOVAMegaMenuMobile', '.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-has-submenu > a');
		$(document).off('click.NOVAMegaMenuMobile', '.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-submenu-toggle');
		$(document).off('click.NOVAMegaMenuMobile', '.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-submenu-toggle *');

		$(document).on('click.NOVAMegaMenuMobile', '.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-has-submenu > a', function (e) {
			var $target = $(e.target);
			var $this = $(this);
			var $parent = $this.parent('li');
			var $submenu = $parent.children('.sub-menu');

			// ✅ LOGIQUE DE SÉPARATION :
			// 1. Si clic sur le titre (span), on laisse la redirection se faire.
			// 2. Sinon (icône dropdown), on fait le toggle.
			var isTitleClick = $target.closest('.nova-icon-menu-title').length > 0;

			if ($submenu.length && $submenu.children().length > 0) {
				if (isTitleClick) {
					// Redirection normale vers le href
					return true;
				} else {
					// Toggle du sous-menu
					e.preventDefault();
					e.stopPropagation();
					if (!$parent.hasClass('nova-submenu-open')) {
						$parent.siblings('.nova-submenu-open').removeClass('nova-submenu-open').children('.sub-menu').css('display', 'none');
						$parent.addClass('nova-submenu-open');
						$submenu.css('display', 'block');
					} else {
						$parent.removeClass('nova-submenu-open');
						$submenu.css('display', 'none');
					}
				}
			}
		});

		$(document).on('click.NOVAMegaMenuMobile', '.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-submenu-toggle', function (e) {
			var $target = $(e.target);
			var $this = $(this);
			var $parent = $this.parent('li');
			var $submenu = $parent.children('.sub-menu');

			// ✅ LOGIQUE DE SÉPARATION :
			var isIconClick = $target.closest('.NOVA-dropdown-icon').length > 0;

			if (isIconClick && $submenu.length && $submenu.children().length > 0) {
				e.preventDefault();
				e.stopPropagation();
				if (!$parent.hasClass('nova-submenu-open')) {
					$parent.siblings('.nova-submenu-open').removeClass('nova-submenu-open').children('.sub-menu').css('display', 'none');
					$parent.addClass('nova-submenu-open');
					$submenu.css('display', 'block');
				} else {
					$parent.removeClass('nova-submenu-open');
					$submenu.css('display', 'none');
				}
			}
		});

		$(document).on('click.NOVAMegaMenuMobile', '.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list .nova-submenu-toggle *', function (e) {
			var $toggle = $(this).closest('.nova-submenu-toggle');
			if ($toggle.length) {
				e.preventDefault();
				e.stopPropagation();
				$toggle.trigger('click.NOVAMegaMenuMobile');
			}
		});
	}

	function applyBreakpoints() {
		var viewportWidth = window.innerWidth || document.documentElement.clientWidth;

		$('.elementor-widget-nova-mega-menu, .elementor-widget-nova-icon-menu').each(function () {
			var $widgetWrapper = $(this);
			var $menuContainer = $widgetWrapper.find('.nova-mega-menu-container, .nova-icon-menu-container');
			var $breadcrumbWrapper = $widgetWrapper.find('.nova-mobile-breadcrumb-wrapper');
			var $breadcrumb = $widgetWrapper.find('.nova-mobile-breadcrumb');

			if ($breadcrumb.length > 0) {
				var $toggle = $breadcrumb.find('.nova-mobile-breadcrumb-toggle');
				var breakpointDesktop = parseInt($breadcrumb.data('breakpoint-desktop')) || 1024;

				if (viewportWidth <= breakpointDesktop) {
					$toggle.css('display', 'block');
					if ($breadcrumbWrapper.length > 0) {
						$breadcrumbWrapper.css('display', 'flex');
					}
					$menuContainer.css('display', 'none');
				} else {
					$toggle.css('display', 'none');
					if ($breadcrumbWrapper.length > 0) {
						$breadcrumbWrapper.css('display', 'none');
					}
					$menuContainer.css('display', '');
				}
			} else {
				$menuContainer.css('display', '');
			}
		});
	}

	function applyPopupAnimation($content, isOpening) {
		if (typeof gsap === 'undefined') {
			$content.toggleClass('active', isOpening);
			return;
		}

		var animationType = $content.data('animation-type') || 'slide';
		var duration = parseFloat($content.data('animation-duration')) || 0.3;
		var easing = $content.data('animation-easing') || 'power2.out';
		var delay = parseFloat($content.data('animation-delay')) || 0;

		gsap.killTweensOf($content[0]);

		// Special handling for expand mode
		if (animationType === 'expand') {
			if (isOpening) {
				// Force d'abord la position relative via GSAP (écrase le CSS de base)
				gsap.set($content[0], {
					position: 'relative',
					top: 'auto',
					left: 'auto',
					width: '100%',
					maxWidth: '100%',
					overflow: 'hidden',
					visibility: 'visible',
					opacity: 1,
					x: 0,
					y: 0,
					rotation: 0,
					scale: 1,
					pointerEvents: 'auto',
					height: 0
				});
				$content.addClass('active');

				// Mesure la hauteur naturelle
				var tempHeight = gsap.getProperty($content[0], 'height');
				gsap.set($content[0], { height: 'auto' });
				var naturalHeight = $content[0].scrollHeight;
				gsap.set($content[0], { height: tempHeight || 0 });

				// Anime vers la hauteur naturelle
				gsap.to($content[0], {
					height: naturalHeight,
					duration: duration,
					delay: delay,
					ease: easing === 'none' ? 'none' : easing,
					onComplete: function () {
						gsap.set($content[0], { height: 'auto', overflow: 'visible' });
					}
				});
			} else {
				var currentHeight = $content[0].scrollHeight;
				gsap.set($content[0], { height: currentHeight, overflow: 'hidden' });
				gsap.to($content[0], {
					height: 0,
					duration: duration,
					delay: delay,
					ease: easing === 'none' ? 'none' : easing,
					onComplete: function () {
						$content.removeClass('active');
						gsap.set($content[0], { pointerEvents: 'none' });
					}
				});
			}
			return;
		}

		// Original animation logic for other modes
		var animMap = {
			'slide': { x: '-100%', opacity: 1 },
			'fade': { opacity: 0 },
			'scale': { scale: 0.8, opacity: 0 },
			'slide-up': { y: '100%', opacity: 1 },
			'slide-down': { y: '-100%', opacity: 1 },
			'slide-right': { x: '100%', opacity: 1 },
			'rotate': { rotation: -180, opacity: 0 },
			'flip': { rotationY: -90, opacity: 0 }
		};

		if (isOpening) {
			var fromVars = animMap[animationType] || animMap['slide'];
			gsap.set($content[0], fromVars);
			$content.css({ visibility: 'visible', pointerEvents: 'auto' });
			$content.addClass('active');
			gsap.to($content[0], { x: 0, y: 0, scale: 1, rotation: 0, rotationY: 0, opacity: 1, duration: duration, delay: delay, ease: easing === 'none' ? 'none' : easing });
		} else {
			var closeMap = {
				'slide': { x: '-100%', opacity: 1 },
				'fade': { opacity: 0 },
				'scale': { scale: 0.8, opacity: 0 },
				'slide-up': { y: '-100%', opacity: 1 },
				'slide-down': { y: '100%', opacity: 1 },
				'slide-right': { x: '100%', opacity: 1 },
				'rotate': { rotation: 180, opacity: 0 },
				'flip': { rotationY: 90, opacity: 0 }
			};
			var toVars = closeMap[animationType] || closeMap['slide'];
			toVars.duration = duration;
			toVars.delay = delay;
			toVars.ease = easing === 'none' ? 'none' : easing;
			toVars.onComplete = function () {
				$content.removeClass('active');
				$content.css({ visibility: 'hidden', pointerEvents: 'none' });
			};
			gsap.to($content[0], toVars);
		}
	}

	function initMobileBreadcrumb() {
		$('.nova-mobile-breadcrumb').each(function () {
			var $breadcrumb = $(this);
			var $toggle = $breadcrumb.find('.nova-mobile-breadcrumb-toggle');
			var $overlay = $breadcrumb.find('.nova-mobile-breadcrumb-overlay');

			// Find content - check if breadcrumb is in wrapper first
			var $wrapper = $breadcrumb.closest('.nova-mobile-breadcrumb-wrapper');
			var $content;

			if ($wrapper.length > 0) {
				// New structure: content is next to wrapper
				$content = $wrapper.next('.nova-mobile-breadcrumb-content');
			} else {
				// Old structure: content is next to breadcrumb
				$content = $breadcrumb.next('.nova-mobile-breadcrumb-content');
			}

			if ($content.length === 0) {
				console.warn('Nova Mobile Breadcrumb: Content element not found');
				return;
			}

			var $close = $content.find('.nova-mobile-breadcrumb-close');

			// Initialize popup state (hidden)
			if (typeof gsap !== 'undefined') {
				var animationType = $content.data('animation-type') || 'slide';

				// Special initialization for expand mode
				if (animationType === 'expand') {
					// Supprimer la classe CSS de protection anti-flash
					$content.removeClass('nova-expand-hidden');
					// GSAP force tous les styles inline — écrase le CSS de base (position:fixed, transform, etc.)
					gsap.set($content[0], {
						position: 'relative',
						top: 'auto',
						left: 'auto',
						width: '100%',
						maxWidth: '100%',
						height: 0,
						overflow: 'hidden',
						visibility: 'visible',
						opacity: 1,
						x: 0,
						y: 0,
						rotation: 0,
						scale: 1,
						pointerEvents: 'none'
					});
				} else {
					// Original initialization for other modes
					var initMap = {
						'slide': { x: '-100%', opacity: 1 },
						'fade': { opacity: 0 },
						'scale': { scale: 0.8, opacity: 0 },
						'slide-up': { y: '100%', opacity: 1 },
						'slide-down': { y: '-100%', opacity: 1 },
						'slide-right': { x: '100%', opacity: 1 },
						'rotate': { rotation: -180, opacity: 0 },
						'flip': { rotationY: -90, opacity: 0 }
					};
					gsap.set($content[0], initMap[animationType] || initMap['slide']);
				}
			}

			// État ouvert/fermé pour le toggle
			var isOpen = false;

			// Track if accordion default state has been applied (only needed once)
			var accordionDefaultApplied = false;

			// Applique l'état "ouvert par défaut" des accordions dans le popup
			function applyAccordionDefaults() {
				var $bodies = $content.find('.nova-accordion-menu-body');

				$bodies.each(function () {
					var $body = $(this);
					var $trigger = $body.prevAll('.nova-accordion-trigger').first();
					if (!$trigger.length) {
						$trigger = $body.parent().find('.nova-accordion-trigger').first();
					}

					var defaultState = $body.data('accordion-default');
					var shouldOpen = (defaultState === 'open');

					if (shouldOpen) {
						$body.removeClass('nova-accordion-closed');
						$body[0].style.setProperty('display', 'block', 'important');
						$body[0].style.setProperty('max-height', 'none', 'important');
						$body[0].style.setProperty('opacity', '1', 'important');
						$body[0].style.setProperty('overflow', 'visible', 'important');
						if ($trigger.length) $trigger.addClass('nova-accordion-open');
					}
				});
				$toggle[0].style.removeProperty('background');
			}

			// Open breadcrumb
			$toggle.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();

				var animationType = $content.data('animation-type') || 'slide';

				// Toggle pour le mode expand
				if (animationType === 'expand' && isOpen) {
					closeBreadcrumb();
					return;
				}

				isOpen = true;

				// Switcher les icônes : cacher l'icône open, montrer l'icône close
				$toggle.addClass('nova-breadcrumb-is-open');
				$('body').addClass('nova-breadcrumb-open');
				$('body').addClass('nova-mega-menu-displayed');

				// Only show overlay and lock body scroll for non-expand modes
				if (animationType !== 'expand') {
					$overlay.addClass('active');
					/* 
					if (window.NOVALenisScroll && typeof window.NOVALenisScroll.stop === 'function') {
						window.NOVALenisScroll.stop();
					} else {
						$('body').css('overflow', 'hidden');
					}
					*/
				}

				applyPopupAnimation($content, true);

				// ✅ Appliquer l'état par défaut via timeout — fonctionne même sur téléphone réel
				// Délais multiples pour couvrir les cas où le rendu est lent
				if (!accordionDefaultApplied) {
					setTimeout(applyAccordionDefaults, 50);
					setTimeout(applyAccordionDefaults, 200);
					setTimeout(applyAccordionDefaults, 500);
					accordionDefaultApplied = true;
				}

				// Init menu events after popup opens
				var checkAttempts = 0;
				var maxAttempts = 20;
				var checkInterval = setInterval(function () {
					checkAttempts++;
					var $mobileMenu = $content.find('.nova-icon-menu-list');

					if ($mobileMenu.length > 0) {
						var $menuContainer = $mobileMenu.closest('.nova-icon-menu-container');
						$menuContainer.css({ 'display': 'block', 'visibility': 'visible' });
						$mobileMenu.css('display', 'flex');

						if (typeof window.NOVAIconMenu !== 'undefined' && typeof window.NOVAIconMenu.init === 'function') {
							clearInterval(checkInterval);
							$menuContainer.addClass('nova-mobile-mode');
							window.NOVAIconMenu.init();
							if (typeof window.setupAccordionFn === 'function') {
								window.setupAccordionFn($content);
							}
						} else if (checkAttempts >= maxAttempts) {
							clearInterval(checkInterval);
							$menuContainer.addClass('nova-mobile-mode');
							setupIconMenuManually();
							if (typeof window.setupAccordionFn === 'function') {
								window.setupAccordionFn($content);
							}
						}
					} else if (checkAttempts >= maxAttempts) {
						clearInterval(checkInterval);
					}
				}, 100);
			});

			// Close breadcrumb
			function closeBreadcrumb() {
				isOpen = false;

				// Réinitialiser les icônes
				$toggle.removeClass('nova-breadcrumb-is-open');
				$('body').removeClass('nova-breadcrumb-open');
				$('body').removeClass('nova-mega-menu-displayed');

				var animationType = $content.data('animation-type') || 'slide';

				// Only manage overlay and body scroll for non-expand modes
				if (animationType !== 'expand') {
					$overlay.removeClass('active');
					/*
					if (window.NOVALenisScroll && typeof window.NOVALenisScroll.start === 'function') {
						window.NOVALenisScroll.start();
					} else {
						$('body').css('overflow', '');
					}
					*/
				}

				applyPopupAnimation($content, false);
			}

			$close.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				closeBreadcrumb();
			});

			$overlay.on('click', function (e) {
				if ($(e.target).is($overlay)) {
					closeBreadcrumb();
				}
			});

			$(document).on('keydown', function (e) {
				if (e.key === 'Escape' && $content.hasClass('active')) {
					closeBreadcrumb();
				}
			});

			$content.on('click', function (e) {
				if ($(e.target).closest('.nova-icon-menu-list, .nova-mega-menu-list, .nova-menu-list').length) {
					return;
				}
				e.stopPropagation();
			});
		});

		applyBreakpoints();
	}

	$(document).ready(function () {
		if (typeof window.NOVAIconMenu === 'undefined') {
			var waitAttempts = 0;
			var waitInterval = setInterval(function () {
				waitAttempts++;
				if (typeof window.NOVAIconMenu !== 'undefined' && typeof window.NOVAIconMenu.init === 'function') {
					clearInterval(waitInterval);
					initMobileBreadcrumb();
				} else if (waitAttempts >= 30) {
					clearInterval(waitInterval);
					initMobileBreadcrumb();
				}
			}, 50);
		} else {
			initMobileBreadcrumb();
		}
	});

	var resizeTimer;
	$(window).on('resize', function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(applyBreakpoints, 250);
	});

	function bindElementorHooks() {
		try {
			if (typeof elementorFrontend === 'undefined' || !elementorFrontend) return false;
			if (!elementorFrontend.hooks || typeof elementorFrontend.hooks.addAction !== 'function') return false;

			elementorFrontend.hooks.addAction('frontend/element_ready/nova-mega-menu.default', function () {
				initMobileBreadcrumb();
			});
			elementorFrontend.hooks.addAction('frontend/element_ready/nova-icon-menu.default', function () {
				initMobileBreadcrumb();
			});

			return true;
		} catch (e) {
			return false;
		}
	}

	$(window).on('elementor/frontend/init', function () {
		var attempts = 0;
		var tryBind = function () {
			attempts++;
			if (bindElementorHooks()) return;
			if (attempts >= 40) return;
			setTimeout(tryBind, 100);
		};
		tryBind();
	});

	(function () {
		var attempts = 0;
		var tryBind = function () {
			attempts++;
			if (bindElementorHooks()) return;
			if (attempts >= 40) return;
			setTimeout(tryBind, 100);
		};
		tryBind();
	})();

})(jQuery);
