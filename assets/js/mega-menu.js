/**
 * NOVA Mega Menu - Frontend JavaScript
 * Handles hover and click interactions for mega menu panels
 * With professional header width expansion animation
 */

(function ($) {
	'use strict';

	// Désactiver les logs de debug pour ce fichier (mega menu) uniquement.
	// Les logs sont activés seulement si window.NOVA_MEGA_MENU_DEBUG === true.
	// Important: le check est fait à chaque appel (si tu actives le flag après chargement, ça marche).
	var debugLog = function () {
		if (!(window.NOVA_MEGA_MENU_DEBUG && window.console && typeof window.console.log === 'function')) {
			return;
		}
		window.console.log.apply(window.console, arguments);
	};

	var NOVAMegaMenu = {

		// Store timeouts for each menu item
		closeTimeouts: {},

		setHeaderWidthImportant: function (widthPx) {
			if (!this.$header || this.$header.length === 0) {
				return;
			}
			var el = this.$header[0];
			if (!el || !el.style || typeof el.style.setProperty !== 'function') {
				return;
			}
			el.style.setProperty('width', widthPx + 'px', 'important');
			el.style.setProperty('min-width', widthPx + 'px', 'important');
			el.style.setProperty('max-width', widthPx + 'px', 'important');
		},

		clearHeaderWidthImportant: function () {
			if (!this.$header || this.$header.length === 0) {
				return;
			}
			var el = this.$header[0];
			if (!el || !el.style || typeof el.style.removeProperty !== 'function') {
				return;
			}
			el.style.removeProperty('width');
			el.style.removeProperty('min-width');
			el.style.removeProperty('max-width');
		},

		forcePanelHidden: function ($panel) {
			if (!$panel || $panel.length === 0) {
				return;
			}
			var el = $panel[0];
			if (!el || !el.style || typeof el.style.setProperty !== 'function') {
				return;
			}
			el.style.setProperty('display', 'block', 'important');
			el.style.setProperty('visibility', 'hidden', 'important');
			el.style.setProperty('opacity', '0', 'important');
			el.style.setProperty('pointer-events', 'none', 'important');
		},

		clearPanelForcedHidden: function ($panel) {
			if (!$panel || $panel.length === 0) {
				return;
			}
			var el = $panel[0];
			if (!el || !el.style || typeof el.style.removeProperty !== 'function') {
				return;
			}
			el.style.removeProperty('display');
			el.style.removeProperty('visibility');
			el.style.removeProperty('opacity');
			el.style.removeProperty('pointer-events');
		},

		// Animation settings
		animationSettings: {
			expandDuration: 400,    // Header expand duration in ms
			panelFadeDuration: 300, // Panel fade in duration in ms
			panelSlideDuration: 350,// Panel height slide duration in ms
			collapseDelay: 300,     // Delay before collapsing when mouse leaves
		},

		// Current state
		isExpanded: false,
		isAnimating: false,
		originalHeaderWidth: null,
		originalHeaderMinWidth: null,
		originalHeaderMaxWidth: null,
		$header: null,
		currentOpenItem: null,

		/**
		 * Initialize all mega menu instances
		 */
		init: function () {
			var self = this;
			var $megaMenuItems = $('.nova-menu-list > li.nova-has-mega-menu');


			if ($megaMenuItems.length === 0) {
				return;
			}

			// Get header element and store original width
			this.$header = $('div#header');
			if (this.$header.length === 0) {
				this.$header = $('header#header, header.site-header, #masthead, .site-header').first();
			}

			if (this.$header.length > 0) {
				// Store original width and min-width
				this.originalHeaderWidth = this.$header.outerWidth();
				this.originalHeaderMinWidth = this.$header.css('min-width');
				this.originalHeaderMaxWidth = this.$header.css('max-width');
			}

			// Remove backdrop if exists (from previous version)
			$('.nova-mega-menu-backdrop').remove();

			// Clear all timeouts
			$.each(self.closeTimeouts, function (key, timeout) {
				clearTimeout(timeout);
			});
			self.closeTimeouts = {};

			// Remove all old handlers
			$megaMenuItems.off('.novaMegaMenu');
			$megaMenuItems.find('> a').off('.novaMegaMenu');
			$('.nova-mega-menu-panel').off('.novaMegaMenu');
			$(document).off('.novaMegaMenu');
			$(window).off('.novaMegaMenu');


			// Setup hover for desktop - menu item
			$megaMenuItems.on('mouseenter.novaMegaMenu', function () {
				var $li = $(this);
				var $panel = $li.find('> .nova-mega-menu-panel');
				var itemId = $li.attr('id') || 'item-' + $li.index();


				// Cancel ALL close timeouts
				self.cancelAllTimeouts();

				if ($panel.length > 0 && !self.isMobileWidth()) {
					self.openMegaMenu($li, $panel);
				}
			});

			$megaMenuItems.on('mouseleave.novaMegaMenu', function () {
				var $li = $(this);
				var $panel = $li.find('> .nova-mega-menu-panel');
				var itemId = $li.attr('id') || 'item-' + $li.index();


				if (!self.isMobileWidth() && $panel.length > 0) {
					self.scheduleClose($li, $panel);
				}
			});

			// Setup hover for desktop - panel itself
			$('.nova-mega-menu-panel').on('mouseenter.novaMegaMenu', function () {
				var $panel = $(this);
				var $li = $panel.closest('li.nova-has-mega-menu');
				var itemId = $li.attr('id') || 'item-' + $li.index();


				// Cancel ALL close timeouts - user is still in the menu
				self.cancelAllTimeouts();
			});

			$('.nova-mega-menu-panel').on('mouseleave.novaMegaMenu', function () {
				var $panel = $(this);
				var $li = $panel.closest('li.nova-has-mega-menu');
				var itemId = $li.attr('id') || 'item-' + $li.index();


				if (!self.isMobileWidth()) {
					self.scheduleClose($li, $panel);
				}
			});

			// Also track header hover to prevent close
			if (this.$header && this.$header.length > 0) {
				this.$header.on('mouseenter.novaMegaMenu', function () {
					if (self.isExpanded) {
						self.cancelAllTimeouts();
					}
				});
			}

			// Click outside to close
			$(document).on('click.novaMegaMenu', function (e) {
				if (!$(e.target).closest('.nova-menu-list > li.nova-has-mega-menu').length &&
					!$(e.target).closest('.nova-mega-menu-panel').length &&
					!$(e.target).closest('div#header').length &&
					!$(e.target).closest('.nova-mobile-breadcrumb-content').length) {
					self.closeAllMegaMenus();
				}
			});

			// Close on resize
			$(window).on('resize.novaMegaMenu', function () {
				// Update original header width on resize
				if (self.$header && self.$header.length > 0 && !self.isExpanded) {
					self.originalHeaderWidth = self.$header.outerWidth();
					self.originalHeaderMinWidth = self.$header.css('min-width');
					self.originalHeaderMaxWidth = self.$header.css('max-width');
				}
				self.closeAllMegaMenus();
			});

			// Gestion des sous-menus natifs (items sans .nova-mega-menu-panel)
			var $nativeSubMenuItems = $('.nova-menu-list > li.menu-item-has-children:not(.nova-has-mega-menu)');
			$nativeSubMenuItems.off('mouseenter.novaNative mouseleave.novaNative');
			$nativeSubMenuItems.on('mouseenter.novaNative', function () {
				$(this).addClass('nova-submenu-open');
			}).on('mouseleave.novaNative', function () {
				$(this).removeClass('nova-submenu-open');
			});

		},

		/**
		 * Cancel all pending timeouts
		 */
		cancelAllTimeouts: function () {
			var self = this;
			$.each(this.closeTimeouts, function (key, timeout) {
				clearTimeout(timeout);
			});
			this.closeTimeouts = {};
		},

		/**
		 * Schedule close with delay
		 */
		scheduleClose: function ($li, $panel) {
			var self = this;

			// Clear any existing close timeout
			if (this.closeTimeouts['close']) {
				clearTimeout(this.closeTimeouts['close']);
			}

			this.closeTimeouts['close'] = setTimeout(function () {
				// Double-check nothing is being hovered
				var $megaMenuItems = $('.nova-menu-list > li.nova-has-mega-menu');
				var anyHovered = false;

				$megaMenuItems.each(function () {
					var $item = $(this);
					var $itemPanel = $item.find('> .nova-mega-menu-panel');
					if ($item.is(':hover') || $itemPanel.is(':hover')) {
						anyHovered = true;
						return false;
					}
				});

				// Also check if header is hovered
				if (self.$header && self.$header.is(':hover')) {
					anyHovered = true;
				}

				if (!anyHovered) {
					self.closeMegaMenu();
				} else {
				}

				delete self.closeTimeouts['close'];
			}, this.animationSettings.collapseDelay);

		},

		/**
		 * Open mega menu with header width expansion animation
		 */
		openMegaMenu: function ($li, $panel) {
			var self = this;

			// Guard: skip if this exact item is already open and displayed
			if (this.currentOpenItem && this.currentOpenItem[0] === $li[0] && $li.hasClass('nova-mega-menu-displayed')) {
				return;
			}

			// If already animating, wait
			if (this.isAnimating) {
				setTimeout(function () {
					self.openMegaMenu($li, $panel);
				}, 50);
				return;
			}

			this.isAnimating = true;

			// Close other mega menus first (without animation)
			$('.nova-menu-list > li.nova-has-mega-menu').not($li).removeClass('nova-mega-menu-displayed');

			// Store current open item
			this.currentOpenItem = $li;

			// Position panel
			this.positionPanel($li, $panel);

			// STEP 1: Measure panel width (temporarily show it invisibly)
			if (!self.currentOpenItem || self.currentOpenItem[0] !== $li[0]) {
				self.isAnimating = false;
				return;
			}

			// Mesure sans flash : on force visibility:hidden + position:absolute
			// sans toucher à display, pour que le CSS display:none !important ne cause pas de flash
			$panel[0].style.setProperty('visibility', 'hidden', 'important');
			$panel[0].style.setProperty('opacity', '0', 'important');
			$panel[0].style.setProperty('pointer-events', 'none', 'important');
			$panel[0].style.setProperty('display', 'block', 'important');
			$panel[0].style.setProperty('position', 'fixed', 'important');

			var panelWidth = $panel.outerWidth();

			// Remettre dans l'état caché CSS (on retire les styles inline — le CSS reprend)
			$panel[0].style.removeProperty('display');
			$panel[0].style.removeProperty('visibility');
			$panel[0].style.removeProperty('opacity');
			$panel[0].style.removeProperty('pointer-events');
			$panel[0].style.removeProperty('position');

			debugLog('[NOVA Mega Menu] STEP 1: measured panelWidth=' + panelWidth);

			// STEP 2: Resize header to panelWidth with GSAP
			if (self.$header && self.$header.length > 0 && panelWidth > 0 && typeof gsap !== 'undefined') {
				var el = self.$header[0];
				el.style.setProperty('transition', 'none', 'important');
				debugLog('[NOVA Mega Menu] STEP 2: GSAP resize header to ' + panelWidth);
				gsap.to(el, {
					width: panelWidth,
					minWidth: panelWidth,
					maxWidth: panelWidth,
					duration: self.animationSettings.expandDuration / 1000,
					ease: 'power2.inOut',
					onComplete: function () {
						debugLog('[NOVA Mega Menu] STEP 3: header resize done, apply classes + slide panel open');
						// STEP 3: Apply classes — CSS makes panel visible
						$li.addClass('nova-mega-menu-displayed');
						$('body').addClass('nova-mega-menu-displayed');

						// STEP 4: Slide panel open (height 0 → real, fast start smooth end)
						var panelEl = $panel[0];
						gsap.set(panelEl, { height: 0, overflow: 'hidden', opacity: 1, willChange: 'height' });
						var realHeight = panelEl.scrollHeight;
						debugLog('[NOVA Mega Menu] STEP 4: slide panel 0 → ' + realHeight);
						gsap.to(panelEl, {
							height: realHeight,
							duration: 0.4,
							ease: 'circ.out',
							onComplete: function () {
								gsap.set(panelEl, { clearProps: 'height,overflow,willChange' });
								self.isExpanded = true;
								self.isAnimating = false;
							}
						});
					}
				});
			} else {
				// Fallback: apply classes immediately
				$li.addClass('nova-mega-menu-displayed');
				$('body').addClass('nova-mega-menu-displayed');
				self.isExpanded = true;
				self.isAnimating = false;
			}
		},

		/**
		 * Close mega menu with collapse animation
		 */
		closeMegaMenu: function () {
			var self = this;

			if (this.isAnimating) {
				setTimeout(function () {
					self.closeMegaMenu();
				}, 100);
				return;
			}

			this.isAnimating = true;
			this.currentOpenItem = null;

			var $panel = $('.nova-menu-list > li.nova-has-mega-menu.nova-mega-menu-displayed > .nova-mega-menu-panel');
			var $li = $panel.closest('li.nova-has-mega-menu');

			if (typeof gsap !== 'undefined' && $panel.length > 0) {
				var panelEl = $panel[0];
				var currentHeight = $panel.outerHeight();

				debugLog('[NOVA Mega Menu] CLOSE STEP 1: slide panel ' + currentHeight + ' → 0');
				gsap.set(panelEl, { overflow: 'hidden', height: currentHeight, willChange: 'height' });
				gsap.to(panelEl, {
					height: 0,
					duration: 0.35,
					ease: 'circ.in',
					onComplete: function () {
						debugLog('[NOVA Mega Menu] CLOSE STEP 2: panel collapsed, remove classes + collapse header');

						// Remove classes
						$('body').removeClass('nova-mega-menu-displayed');
						$li.removeClass('nova-mega-menu-displayed');

						// Reset panel inline styles — laisser le CSS display:none !important reprendre
						$panel[0].style.removeProperty('display');
						$panel[0].style.removeProperty('visibility');
						$panel[0].style.removeProperty('opacity');
						$panel[0].style.removeProperty('pointer-events');
						$panel[0].style.removeProperty('transition');
						gsap.set(panelEl, { clearProps: 'height,overflow,opacity,willChange' });

						// Collapse header with GSAP
						if (self.$header && self.$header.length > 0 && self.originalHeaderWidth) {
							var el = self.$header[0];
							el.style.setProperty('transition', 'none', 'important');
							gsap.to(el, {
								width: self.originalHeaderWidth,
								minWidth: self.originalHeaderWidth,
								maxWidth: self.originalHeaderWidth,
								duration: self.animationSettings.expandDuration / 1000,
								ease: 'power2.inOut',
								onComplete: function () {
									self.isExpanded = false;
									self.clearHeaderWidthImportant();
									gsap.set(el, { clearProps: 'width,minWidth,maxWidth' });
									self.isAnimating = false;
								}
							});
						} else {
							self.isExpanded = false;
							self.isAnimating = false;
						}
					}
				});
			} else {
				// Fallback without GSAP
				$('body').removeClass('nova-mega-menu-displayed');
				$li.removeClass('nova-mega-menu-displayed');
				$panel.css({ 'display': '', 'visibility': '', 'opacity': '', 'pointer-events': '', 'transition': '' });
				if (self.$header && self.$header.length > 0) {
					self.clearHeaderWidthImportant();
				}
				self.isExpanded = false;
				self.isAnimating = false;
			}
		},

		/**
		 * Close all mega menus immediately
		 */
		closeAllMegaMenus: function () {
			// Cancel all timeouts
			this.cancelAllTimeouts();

			// Reset current item
			this.currentOpenItem = null;

			// Hide all panels immediately
			$('.nova-menu-list > li.nova-has-mega-menu').removeClass('nova-mega-menu-displayed');
			$('body').removeClass('nova-mega-menu-displayed');

			// Reset header width
			if (this.$header && this.$header.length > 0) {
				this.clearHeaderWidthImportant();
			}

			// Reset panels — laisser le CSS display:none !important reprendre
			$('.nova-mega-menu-panel').each(function () {
				this.style.removeProperty('display');
				this.style.removeProperty('visibility');
				this.style.removeProperty('opacity');
				this.style.removeProperty('pointer-events');
				this.style.removeProperty('transition');
			});

			this.isExpanded = false;
			this.isAnimating = false;
		},

		/**
		 * Position the panel below the menu item (for position: fixed)
		 */
		positionPanel: function ($li, $panel) {
			if ($panel.length === 0) return;

			var headerHeight = this.$header ? this.$header.outerHeight() : ($('header').outerHeight() || 80);
			headerHeight = headerHeight + 13;
			$panel.css('top', headerHeight + 'px');
		},

		/**
		 * Check if viewport is mobile width (not touch device)
		 */
		isMobileWidth: function () {
			return $(window).width() <= 768;
		}
	};

	// Initialize on DOM ready
	$(document).ready(function () {
		console.log('[NOVA MegaMenu] mega-menu.js chargé — version 1.1.4');
		NOVAMegaMenu.init();

		// Handler délégué pour le click mobile — attaché UNE SEULE FOIS ici
		// Fonctionne pour tous les éléments présents et futurs (popup mobile inclus)
		$(document).on('click.novaMegaMobileClick', '.nova-menu-list > li.nova-has-mega-menu > a, .nova-menu-list > li.nova-has-mega-menu > a *', function (e) {
			var $target = $(e.target);
			var $link = $(this).is('a') ? $(this) : $(this).closest('a');
			var $li = $link.closest('li.nova-has-mega-menu');
			var $panel = $li.find('> .nova-mega-menu-panel');

			var isInMobilePopup = $link.closest('.nova-mobile-breadcrumb-content').length > 0 ||
				$link.closest('.nova-mobile-breadcrumb-template-wrapper').length > 0;
			var isMobile = $(window).width() <= 768;

			// ✅ LOGIQUE DE SÉPARATION :
			// 1. Si clic sur le titre (span), on redirige vers le href.
			// 2. Sinon (icône ou reste de l'élément), on fait le toggle du menu.
			var isTitleClick = $target.closest('.nova-mega-menu-title').length > 0;

			if ((isMobile || isInMobilePopup)) {
				if (isTitleClick) {
					// Clic sur le titre : on laisse la redirection naturelle se faire
					return true;
				} else {
					// Clic ailleurs (icône, etc) : on fait le toggle
					e.preventDefault();
					e.stopPropagation();

					var isDisplayed = $li.hasClass('nova-mega-menu-displayed');

					// Fermer tous les autres
					$('.nova-menu-list > li.nova-has-mega-menu').not($li).removeClass('nova-mega-menu-displayed');

					if (isDisplayed) {
						// ✅ FIX: On s'assure que la classe est retirée IMMÉDIATEMENT pour que l'icône change
						$li.removeClass('nova-mega-menu-displayed');
						NOVAMegaMenu.closeMegaMenu();
					} else {
						NOVAMegaMenu.openMegaMenu($li, $panel);
					}
				}
			}
		});
	});

	// Re-initialize for Elementor Preview
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined') {
			elementorFrontend.hooks.addAction('frontend/element_ready/nova-mega-menu.default', function () {
				setTimeout(function () {
					NOVAMegaMenu.init();
				}, 100);
			});
		}
	});

	window.NOVAMegaMenu = NOVAMegaMenu;

})(jQuery);