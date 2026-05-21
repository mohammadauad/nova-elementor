/**
 * NOVA Icon Menu - Frontend JavaScript
 * Version: 1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * Initialise le menu NOVA Icon Menu
	 */
	var NOVAIconMenu = {

		/**
		 * Initialisation
		 */
		init: function () {
			this.cleanupEmptySubmenus();
			this.setupDropdowns();
			this.setupClickOutside();
			this.setupHover();
			this.setupMobileMenu();
			this.setupAccessibility();
		},

		/**
		 * Supprimer les flèches et classes de sous-menu pour les éléments vides
		 */
		cleanupEmptySubmenus: function () {
			var self = this;
			$('.nova-icon-menu-list .nova-has-submenu, .nova-icon-menu-list .menu-item-has-children').each(function () {
				var $li = $(this);
				var $submenu = $li.children('.sub-menu');
				
				// Si le sous-menu n'existe pas ou n'a pas d'enfants réels
				if ($submenu.length === 0 || $submenu.children('li').length === 0) {
					$li.removeClass('nova-has-submenu menu-item-has-children');
					$li.find('.NOVA-dropdown-icon').remove();
					// S'assurer que le lien n'a plus la classe de toggle
					$li.children('a').removeClass('nova-submenu-toggle');
				}
			});
		},

		/**
		 * Configuration des dropdowns
		 */
		setupDropdowns: function () {
			var self = this;

			// setupDropdowns called

			// ✅ IMPORTANT: Retirer les anciens handlers avant d'en ajouter de nouveaux
			// Cela évite les conflits quand setupDropdowns() est appelé plusieurs fois
			$(document).off('click.NOVAIconMenu', '.nova-icon-menu-list *');
			$(document).off('click.NOVAIconMenu', '.nova-icon-menu-list .nova-has-submenu > a');
			$(document).off('click.NOVAIconMenu', '.nova-icon-menu-list .nova-submenu-toggle');
			$(document).off('click.NOVAIconMenu', '.nova-icon-menu-list .nova-submenu-toggle *');

			// ✅ Aussi retirer les handlers directs (si on les a ajoutés)
			$('.nova-icon-menu-list .nova-has-submenu > a').off('click.NOVAIconMenuDirect');
			$('.nova-icon-menu-list .nova-submenu-toggle').off('click.NOVAIconMenuDirect');

			// Vérifier les éléments disponibles
			var $submenuToggles = $('.nova-icon-menu-list .nova-submenu-toggle');
			var $hasSubmenuLinks = $('.nova-icon-menu-list .nova-has-submenu > a');
			// Elements found

			// Handle all clicks in menu

			// ✅ Gérer les clics sur les liens avec sous-menus (mobile et desktop)
			// Utiliser des sélecteurs séparés pour mieux déboguer
			// ✅ Utiliser namespace pour pouvoir les retirer facilement
			$(document).on('click.NOVAIconMenu', '.nova-icon-menu-list .nova-has-submenu > a', function (e) {
				self.handleSubmenuClick.call(this, e, self);
			});

			$(document).on('click.NOVAIconMenu', '.nova-icon-menu-list .nova-submenu-toggle', function (e) {
				// ✅ Ne pas stopPropagation ici, laisser handleSubmenuClick gérer
				self.handleSubmenuClick.call(this, e, self);
			});

			// Aussi gérer les clics sur les éléments enfants du toggle (icône, texte, etc.)
			$(document).on('click.NOVAIconMenu', '.nova-icon-menu-list .nova-submenu-toggle *', function (e) {
				var $toggle = $(this).closest('.nova-submenu-toggle');
				if ($toggle.length) {
					e.preventDefault();
					e.stopPropagation();
					// ✅ Utiliser trigger avec namespace pour déclencher notre handler
					$toggle.trigger('click.NOVAIconMenu');
				}
			});

			// ✅ AUSSI attacher des handlers DIRECTS sur les éléments dans le popup mobile
			// Cela garantit que les clics fonctionnent même si la délégation d'événements échoue
			var $popupMenu = $('.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list');
			if ($popupMenu.length > 0) {
				var $popupToggles = $popupMenu.find('.nova-submenu-toggle');
				var $popupLinks = $popupMenu.find('.nova-has-submenu > a');

				$popupToggles.each(function () {
					var $toggle = $(this);
					$toggle.off('click.NOVAIconMenuDirect').on('click.NOVAIconMenuDirect', function (e) {
						self.handleSubmenuClick.call(this, e, self);
					});
				});

				$popupLinks.each(function () {
					var $link = $(this);
					$link.off('click.NOVAIconMenuDirect').on('click.NOVAIconMenuDirect', function (e) {
						self.handleSubmenuClick.call(this, e, self);
					});
				});
			}
		},

		/**
		 * Gérer le clic sur un élément de sous-menu
		 */
		handleSubmenuClick: function (e, self) {
			var $target = $(e.target);
			var $this = $(this);
			// ✅ Si le clic est sur un enfant du toggle, utiliser le parent li directement
			if (!$this.is('a') && !$this.hasClass('nova-submenu-toggle')) {
				$this = $this.closest('a, .nova-submenu-toggle');
			}

			var $parent = $this.parent('li');
			var $submenu = $parent.children('.sub-menu');

			// ✅ FIX: Si l'élément n'a pas de sous-menu, laisser le comportement par défaut (lien normal)
			if (!$submenu.length) {
				return;
			}

			// ✅ Chercher le widget scope avec plusieurs sélecteurs possibles
			var $widgetScope = $this.closest('.elementor-widget-nova-icon-menu').first();
			if (!$widgetScope.length) {
				$widgetScope = $this.closest('.elementor-element').first();
			}
			if (!$widgetScope.length) {
				$widgetScope = $this.closest('.nova-icon-menu-container').closest('.elementor-element').first();
			}

			var isSubmenuBelow = $widgetScope.hasClass('nova-submenu-below') ||
				$parent.closest('.nova-submenu-below').length > 0 ||
				$this.closest('.nova-submenu-below').length > 0;

			var triggerIsClick = $widgetScope.hasClass('nova-submenu-trigger-click') ||
				$widgetScope.hasClass('nova-submenu-trigger-both');


			// Vérifier si on est dans le popup mobile (même sur grand écran, dans le popup on veut le comportement mobile)
			var isInMobilePopup = $this.closest('.nova-mobile-breadcrumb-template-wrapper').length > 0 ||
				$this.closest('.nova-mobile-breadcrumb-content').length > 0;
			var isMobile = $(window).width() <= 768 || self.isTouchDevice() || isInMobilePopup;

			// ✅ LOGIQUE DE SÉPARATION :
			// 1. Si clic sur le titre (span), on redirige.
			// 2. Sinon (icône dropdown), on fait le toggle.
			var isTitleClick = $target.closest('.nova-icon-menu-title').length > 0;

			// Si le sous-menu existe
			if ($submenu.length) {
				// ✅ Mode "en dessous" nécessite toujours le click (même si hover est activé, le click doit fonctionner)
				// Sur mobile (toujours click) OU si trigger desktop inclut click OU si mode "en dessous"
				if (isMobile || triggerIsClick || isSubmenuBelow) {

					if (isTitleClick) {
						// Laisser la redirection naturelle
						return true;
					} else {
						// Clic ailleurs (icône, etc) : on fait le toggle
						e.preventDefault();
						e.stopPropagation();
						e.stopImmediatePropagation();

						// Empêcher la navigation si le sous-menu n'est pas déjà ouvert
						if (!$parent.hasClass('nova-submenu-open')) {

							// Fermer les autres sous-menus au même niveau
							$parent.siblings('.nova-submenu-open').removeClass('nova-submenu-open')
								.children('.sub-menu').css('display', ''); // Retirer le style inline pour laisser le CSS gérer

							// Ouvrir ce sous-menu - utiliser la classe CSS plutôt que style inline
							$parent.addClass('nova-submenu-open');
							// ✅ Retirer tout style inline pour laisser le CSS avec !important gérer
							$submenu.css('display', '');

							// ✅ Forcer un reflow pour s'assurer que le CSS s'applique
							$submenu[0].offsetHeight;

						} else {
							// Si déjà ouvert, fermer
							// ✅ FIX: On s'assure que la classe est retirée pour changer l'icône
							$parent.removeClass('nova-submenu-open');
							// ✅ Retirer le style inline pour laisser le CSS gérer (display:none par défaut en mode "en dessous")
							$submenu.css('display', '');
						}
					}
				}
			}
		},

		/**
		 * Fermer les sous-menus en cliquant en dehors
		 */
		setupClickOutside: function () {
			var self = this;
			$(document).off('click.NOVAIconMenu touchstart.NOVAIconMenu');
			$(document).on('click.NOVAIconMenu touchstart.NOVAIconMenu', function (e) {
				// Ne pas fermer si le clic est dans le menu ou sur l'accordion
				if ($(e.target).closest('.nova-icon-menu-list').length ||
					$(e.target).closest('.nova-submenu-toggle').length ||
					$(e.target).closest('.NOVA-dropdown-icon').length ||
					$(e.target).closest('.nova-accordion-trigger').length ||
					$(e.target).closest('.nova-accordion-menu-body').length) {
					return;
				}

				var isInMobilePopup = $(e.target).closest('.nova-mobile-breadcrumb-template-wrapper').length > 0 ||
					$(e.target).closest('.nova-mobile-breadcrumb-content').length > 0;
				var isMobile = $(window).width() <= 768 || self.isTouchDevice() || isInMobilePopup;

				var $clickedWidget = $(e.target).closest('.elementor-widget-nova-icon-menu');
				var isSubmenuBelow = $clickedWidget.length && (
					$clickedWidget.hasClass('nova-submenu-below') ||
					$clickedWidget.find('.nova-submenu-below').length > 0
				);

				if (isMobile || isSubmenuBelow) {
					$('.nova-submenu-open').removeClass('nova-submenu-open')
						.children('.sub-menu').css('display', '');
				}
			});
		},

		/**
		 * Configuration du hover desktop
		 */
		setupHover: function () {
			// Sur desktop, utiliser le hover (déjà géré par CSS)
			$('.nova-icon-menu-list .nova-has-submenu').hover(
				function () {
					$(this).addClass('nova-submenu-hover');
				},
				function () {
					$(this).removeClass('nova-submenu-hover');
				}
			);
		},

		/**
		 * Configuration du menu mobile
		 */
		setupMobileMenu: function () {
			// Vérifier si on est en mode mobile
			this.checkMobileMode();

			// Recheck on resize
			var resizeTimer;
			$(window).on('resize', function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					NOVAIconMenu.checkMobileMode();
				}, 250);
			});
		},

		/**
		 * Vérifier le mode mobile
		 */
		checkMobileMode: function () {
			var windowWidth = $(window).width();
			var $menus = $('.nova-icon-menu-container');

			$menus.each(function () {
				var $menu = $(this);

				if (windowWidth <= 768) {
					$menu.addClass('nova-mobile-mode');
				} else {
					$menu.removeClass('nova-mobile-mode');
					// Réinitialiser les sous-menus ouverts
					$menu.find('.nova-submenu-open').removeClass('nova-submenu-open')
						.children('.sub-menu').css('display', '');
				}
			});
		},

		/**
		 * Configuration de l'accessibilité
		 */
		setupAccessibility: function () {
			// Ajouter les attributs ARIA
			$('.nova-icon-menu-list .nova-has-submenu > a').attr('aria-haspopup', 'true')
				.attr('aria-expanded', 'false');

			// Mettre à jour aria-expanded au hover/clic
			$('.nova-icon-menu-list .nova-has-submenu').on('mouseenter click', function () {
				$(this).children('a').attr('aria-expanded', 'true');
			}).on('mouseleave', function () {
				$(this).children('a').attr('aria-expanded', 'false');
			});

			// Navigation au clavier
			$('.nova-icon-menu-list a').on('keydown', function (e) {
				var $this = $(this);
				var $parent = $this.parent('li');
				var $submenu = $parent.children('.sub-menu');

				// Entrée ou Espace: ouvrir/fermer le sous-menu
				if (e.keyCode === 13 || e.keyCode === 32) {
					if ($submenu.length) {
						e.preventDefault();
						$this.trigger('click');
					}
				}

				// Flèche bas: aller au premier élément du sous-menu
				if (e.keyCode === 40 && $submenu.length && $submenu.is(':visible')) {
					e.preventDefault();
					$submenu.find('a').first().focus();
				}

				// Flèche haut: aller à l'élément parent
				if (e.keyCode === 38) {
					e.preventDefault();
					var $parentMenuItem = $parent.parent('.sub-menu').parent('li');
					if ($parentMenuItem.length) {
						$parentMenuItem.children('a').focus();
					}
				}

				// Échap: fermer le sous-menu
				if (e.keyCode === 27) {
					var $openSubmenu = $this.closest('.nova-submenu-open');
					if ($openSubmenu.length) {
						$openSubmenu.removeClass('nova-submenu-open')
							.children('.sub-menu').slideUp(300);
						$openSubmenu.children('a').focus();
					}
				}
			});
		},

		/**
		 * Détecter si c'est un appareil tactile
		 */
		isTouchDevice: function () {
			return ('ontouchstart' in window) ||
				(navigator.maxTouchPoints > 0) ||
				(navigator.msMaxTouchPoints > 0);
		},

		/**
		 * Animation des sous-menus (optionnel)
		 */
		animateSubmenu: function ($submenu, action) {
			if (action === 'show') {
				$submenu.stop(true, true).slideDown(300);
			} else {
				$submenu.stop(true, true).slideUp(300);
			}
		}
	};

	// Exposer NOVAIconMenu globalement
	if (typeof window !== 'undefined') {
		try {
			window.NOVAIconMenu = NOVAIconMenu;
		} catch (e) {
			// Error
		}
	}

	/**
	 * Détecter quand le menu est chargé dans le popup mobile
	 */
	function setupMobilePopupDetection() {

		// ✅ Debounce pour éviter les appels multiples
		var lastSetupTime = 0;
		var setupDebounceDelay = 500; // Minimum 500ms entre deux setups
		var hasSetupForPopup = false;

		var setupMenuInPopup = function () {
			var now = Date.now();
			if (now - lastSetupTime < setupDebounceDelay && hasSetupForPopup) {
				return;
			}
			lastSetupTime = now;

			var $mobileMenu = $('.nova-mobile-breadcrumb-template-wrapper .nova-icon-menu-list');
			if ($mobileMenu.length > 0 && !hasSetupForPopup) {
				NOVAIconMenu.setupDropdowns();
				$mobileMenu.closest('.nova-icon-menu-container').addClass('nova-mobile-mode');
				hasSetupForPopup = true;

				// Réinitialiser l'accordion pour les éléments du popup avec scope
				var $popupScope = $mobileMenu.closest('.nova-mobile-breadcrumb-content');
				if (!$popupScope.length) $popupScope = $mobileMenu.closest('.nova-mobile-breadcrumb-template-wrapper');
				setupAccordion($popupScope.length ? $popupScope : null);
			}
		};

		// Observer les changements dans le DOM pour détecter le chargement du menu dans le popup
		if (typeof MutationObserver !== 'undefined') {
			var observer = new MutationObserver(function (mutations) {
				setupMenuInPopup();
			});

			// Observer le body pour détecter l'ajout du popup
			observer.observe(document.body, {
				childList: true,
				subtree: true
			});

			// MutationObserver configured
		}

		// Écouter les événements Elementor pour le chargement de templates
		if (typeof elementorFrontend !== 'undefined') {
			$(document).on('elementor/popup/show', function () {
				hasSetupForPopup = false;
				setTimeout(function () {
					setupMenuInPopup();
					var $popupScope = $('.nova-mobile-breadcrumb-content');
					setupAccordion($popupScope.length ? $popupScope : null);
				}, 150);
			});
		}

		// Écouter la fermeture du popup pour réinitialiser le flag
		$(document).on('click', '.nova-mobile-breadcrumb-close, .nova-mobile-breadcrumb-overlay', function () {
			setTimeout(function () {
				hasSetupForPopup = false;
			}, 100);
		});

		// Fallback: vérifier périodiquement
		var checkInterval = setInterval(function () {
			setupMenuInPopup();
		}, 1000);

		// Vérifier immédiatement
		setTimeout(setupMenuInPopup, 100);
		setTimeout(setupMenuInPopup, 500);
		setTimeout(setupMenuInPopup, 1000);
	}

	/**
	 * Accordion: toggle menu body on title click
	 * @param {jQuery} $scope - Optional scope to limit search (e.g. popup container)
	 */
	function setupAccordion($scope) {
		var $container = ($scope && $scope.length) ? $scope : $(document);
		var $triggers = $container.find('.nova-accordion-trigger');

		$triggers.each(function () {
			var $trigger = $(this);
			$trigger.off('click.NOVAAccordion');
			$trigger.find('*').off('click.NOVAAccordion');

			var $body = $trigger.nextAll('.nova-accordion-menu-body').first();
			if (!$body.length) {
				$body = $trigger.parent().find('.nova-accordion-menu-body').first();
			}
			if (!$body.length) return;

			// Appliquer l'état initial défini par le PHP
			// Utiliser data-accordion-default comme source de vérité (résistant au cache)
			var defaultState = $body.data('accordion-default');
			var isOpen = (defaultState === 'open') || (!$body.hasClass('nova-accordion-closed') && defaultState !== 'closed');
			
			if (isOpen) {
				$body.removeClass('nova-accordion-closed');
				$body[0].style.setProperty('display', 'block', 'important');
				$trigger.addClass('nova-accordion-open');
			}

			// Activer les transitions après un délai
			var $bodyRef = $body;
			setTimeout(function () {
				$bodyRef.addClass('nova-accordion-animated');
			}, 300);

			$trigger.on('click.NOVAAccordion', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				toggleAccordion($trigger, $bodyRef);
			});

			$trigger.find('*').on('click.NOVAAccordion', function (e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				toggleAccordion($trigger, $bodyRef);
			});
		});
	}

	function toggleAccordion($trigger, $body) {
		if (!$body || !$body.length) return;

		if ($trigger.hasClass('nova-accordion-open')) {
			$trigger.removeClass('nova-accordion-open');
			$body.addClass('nova-accordion-closed');
			$body[0].style.removeProperty('display');
			$body[0].style.removeProperty('max-height');
			$body[0].style.removeProperty('opacity');
			$body[0].style.removeProperty('overflow');
		} else {
			$trigger.addClass('nova-accordion-open');
			$body.removeClass('nova-accordion-closed');
			$body[0].style.setProperty('display', 'block', 'important');
			$body[0].style.setProperty('max-height', 'none', 'important');
			$body[0].style.setProperty('opacity', '1', 'important');
			$body[0].style.setProperty('overflow', 'visible', 'important');
		}
	}

	/**
	 * Initialisation au chargement du DOM
	 */
	$(document).ready(function () {
		NOVAIconMenu.init();
		setupMobilePopupDetection();
		// Exposer setupAccordion globalement (après sa définition)
		window.setupAccordionFn = setupAccordion;
		// Délai pour s'assurer que le DOM est complet et que les autres handlers sont enregistrés
		setTimeout(setupAccordion, 50);
	});

	/**
	 * Réinitialisation pour Elementor Preview
	 */
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/nova-icon-menu.default', function ($scope) {
			NOVAIconMenu.init();
			setupMobilePopupDetection();
			setTimeout(setupAccordion, 50);
		});
	});

})(jQuery);



