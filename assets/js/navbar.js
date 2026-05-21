/**
 * Nova Header Menu Animation
 * Version: 1.0.0
 * 
 * Features:
 * - Menu caché au chargement avec animation width
 * - Icon rotation (90deg quand caché)
 * - Toggle au click
 * - Hover pour afficher temporairement
 * - Animations GSAP fluides
 */

(function ($) {
	'use strict';

	const NovaHeaderMenu = {

		// Selectors
		$menu: null,
		$icon: null,
		$iconImg: null,

		// State
		isMenuOpen: false,
		isHovering: false,
		hoverTimeout: null,
		isReady: false, // ✅ NEW: Protection pour éviter les animations fantômes au refresh
		isInitialized: false, // ✅ NEW: Variable pour vérifier si l'init a déjà été appelé
		hoverAnimationEnabled: true, // ✅ NEW: Track if hover animation is enabled

		// Config
		config: {
			menuSelector: '#top-header-2',
			iconSelector: 'div#top-header-icone',
			iconImgSelector: 'div#top-header-icone img',
			animDuration: 1.0, // ✅ AUGMENTÉ À 1s pour smooth
			hoverEasing: 'power2.out', // ✅ NEW: Easing function
			hoverDelay: 300
		},

		/**
		 * Check if viewport is desktop (>= 992px)
		 */
		isDesktop: function () {
			const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
			return viewportWidth >= 992;
		},

		/**
		 * Initialize
		 */
		init: function () {
			// ✅ PROTECT: Ne pas relancer l'init si déjà initialisé
			if (this.isInitialized) {
				return;
			}

			// ✅ CHECK: Ne pas initialiser si viewport < 992px
			if (!this.isDesktop()) {
				// Sur mobile/tablette, laisser le menu visible par défaut
				this.$menu = $(this.config.menuSelector);
				if (this.$menu.length > 0) {
					this.$menu[0].style.width = 'auto';
					this.$menu[0].style.overflow = 'visible';
				}
				return;
			}

			// ✅ READ HOVER ANIMATION SETTING FROM PHP
			if (typeof NovaAddonsSettings !== 'undefined') {
				this.hoverAnimationEnabled = NovaAddonsSettings.hoverAnimationEnabled === 'yes';
			}

			// ✅ READ ANIMATION DURATIONS FROM PHP (ONLY IF HOVER IS ENABLED)
			if (this.hoverAnimationEnabled && typeof NovaAddonsSettings !== 'undefined') {
				if (NovaAddonsSettings.hoverDuration) {
					this.config.animDuration = parseFloat(NovaAddonsSettings.hoverDuration);
				}

				if (NovaAddonsSettings.hoverEasing) {
					this.config.hoverEasing = NovaAddonsSettings.hoverEasing;
				}
			}

			// Get elements
			this.$menu = $(this.config.menuSelector);
			this.$icon = $(this.config.iconSelector);
			this.$iconImg = $(this.config.iconImgSelector);

			// Check if elements exist
			if (this.$menu.length === 0 || this.$icon.length === 0) {
				return;
			}

			// ✅ KILL ALL CSS TRANSITIONS - Force via JS
			this.$menu[0].style.transition = 'none !important';
			this.$menu[0].style.animation = 'none !important';
			this.$icon[0].style.transition = 'none !important';
			this.$icon[0].style.animation = 'none !important';
			this.$iconImg[0].style.transition = 'none !important';
			this.$iconImg[0].style.animation = 'none !important';

			// ✅ SET INITIAL ROTATION (via GSAP, no animation)
			gsap.set(this.$iconImg[0], { rotation: 0 });

			// Setup initial state
			this.setupInitialState();

			// ✅ IF HOVER ANIMATION IS DISABLED - STOP HERE, DON'T BIND EVENTS
			if (!this.hoverAnimationEnabled) {
				// Menu width: auto is already set in setupInitialState()
				// ✅ FORCE width: auto with !important via inline style
				this.$menu[0].style.cssText = 'width: auto !important; overflow: visible !important;';
				this.$icon[0].style.cssText = 'pointer-events: auto;'; // Enable icon clicks if needed
				this.isInitialized = true;
				return;
			}

			// Bind events (ONLY IF HOVER IS ENABLED)
			this.bindEvents();

			this.isInitialized = true; // ✅ MARKER D'INITIALISATION

			// ✅ DELAY isReady to avoid early hover/leave triggers (ONLY IF HOVER IS ENABLED)
			const self = this;
			if (self.hoverAnimationEnabled) {
				setTimeout(function () {
					self.isReady = true;
					self.hideMenu(true);
				}, 1500); // 1500ms delay
			} else {
				// If hover is disabled, menu is already in final state
				self.isReady = true;
			}
		},

		/**
		 * Setup initial state (menu hidden on load)
		 */
		setupInitialState: function () {
			// ✅ Mettre width AUTO (menu OUVERT par défaut)
			this.$menu[0].style.width = 'auto';
			this.$menu[0].style.overflow = 'visible';
			this.$menu[0].style.opacity = '1';

			// ✅ Pas de rotation CSS - Laisser GSAP la contrôler !
			// GSAP va la mettre à 0deg via gsap.set dans init()

			this.isMenuOpen = true; // ✅ Menu est OUVERT par défaut
		},

		/**
		 * Get menu width (helper function)
		 */
		getMenuWidth: function () {
			// Temporarily show to measure
			gsap.set(this.$menu[0], { width: 'auto', visibility: 'hidden' });
			const width = this.$menu.width();

			gsap.set(this.$menu[0], { width: 0, visibility: 'visible' });

			return width;
		},

		/**
		 * Bind events
		 */
		bindEvents: function () {
			const self = this;

			// Click on icon to toggle
			this.$icon.on('click', function (e) {
				e.preventDefault();
				self.toggleMenu();
			});

			// Hover on header container
			const $header = $('#top-header');

			$header.on('mouseenter', function () {
				self.onHeaderHover();
			});

			$header.on('mouseleave', function () {
				self.onHeaderLeave();
			});
		},

		/**
		 * Toggle menu open/close
		 */
		toggleMenu: function () {
			// ✅ PROTECT: Ne pas animer si pas desktop
			if (!this.isDesktop()) {
				return;
			}

			// ✅ Vérifier la largeur RÉELLE du menu (pas juste la variable)
			const currentWidth = gsap.getProperty(this.$menu[0], 'width');

			if (currentWidth > 0) {
				// Menu est ouvert → le fermer
				this.hideMenu(true);
			} else {
				// Menu est fermé → l'ouvrir
				this.showMenu(true);
			}
		},

		/**
		 * Show menu
		 */
		showMenu: function (isPermanent = false) {
			const self = this;

			// Kill any running animations
			gsap.killTweensOf(this.$menu[0]);
			gsap.killTweensOf(this.$iconImg[0]);

			// ✅ GET THE ACTUAL WIDTH (with small delay to ensure DOM is ready)
			setTimeout(function () {
				const targetWidth = self.getMenuWidth();

				// Animate menu width SMOOTHLY (NOT height)
				gsap.to(self.$menu[0], {
					width: targetWidth,
					opacity: 1,
					duration: self.config.animDuration,
					ease: self.config.hoverEasing,
					onStart: function () {
						// ✅ Apply !important to width and overflow DURING animation
						self.$menu[0].style.setProperty('overflow', 'hidden', 'important');
						self.$menu[0].style.setProperty('width', targetWidth + 'px', 'important');
					},
					onUpdate: function () {
						// ✅ Reapply !important to width at every frame (GSAP may override it)
						self.$menu[0].style.setProperty('width', self.$menu[0].style.width, 'important');
					},
					onComplete: function () {
						// ✅ Force width: auto and overflow with !important after animation
						self.$menu[0].style.setProperty('width', 'auto', 'important');
						self.$menu[0].style.setProperty('overflow', 'visible', 'important');
					}
				});

				// Rotate icon to 0deg
				gsap.to(self.$iconImg[0], {
					rotation: 0,
					duration: self.config.animDuration,
					ease: self.config.hoverEasing
				});
			}, 50); // 50ms delay

			if (isPermanent) {
				this.isMenuOpen = true;
			}
		},

		/**
		 * Hide menu
		 */
		hideMenu: function (isPermanent = false) {

			// ✅ PROTECT: Ne pas fermer si déjà fermé
			const currentWidth = gsap.getProperty(this.$menu[0], 'width');
			if (currentWidth === 0 || currentWidth === '0px') {
				return;
			}

			const self = this;

			// Kill any running animations
			gsap.killTweensOf(this.$menu[0]);
			gsap.killTweensOf(this.$iconImg[0]);

			// Animate menu width to 0 SMOOTHLY (NOT height)
			gsap.to(this.$menu[0], {
				width: 0,
				opacity: 1,
				duration: this.config.animDuration,
				ease: this.config.hoverEasing,
				onStart: function () {
					// ✅ Apply !important to width and overflow DURING animation
					self.$menu[0].style.setProperty('overflow', 'hidden', 'important');
					self.$menu[0].style.setProperty('width', '0', 'important');
				},
				onUpdate: function () {
					// ✅ Reapply !important to width at every frame (GSAP may override it)
					self.$menu[0].style.setProperty('width', self.$menu[0].style.width, 'important');
				},
				onComplete: function () {
					// ✅ Force width: 0 and overflow with !important after animation
					self.$menu[0].style.setProperty('width', '0', 'important');
					self.$menu[0].style.setProperty('overflow', 'hidden', 'important');
				}
			});

			// Rotate icon to 90deg
			gsap.to(self.$iconImg[0], {
				rotation: 90,
				duration: self.config.animDuration,
				ease: self.config.hoverEasing
			});

			if (isPermanent) {
				this.isMenuOpen = false;
			}

		},

		/**
		 * On header hover
		 */
		onHeaderHover: function () {
			const self = this;

			// ✅ PROTECT: Ne pas animer si pas desktop
			if (!this.isDesktop()) {
				return;
			}

			// ✅ PROTECT: Ne pas animer si pas prêt
			if (!this.isReady) {
				return;
			}

			// ✅ NEW: Check if hover animation is enabled
			if (!this.hoverAnimationEnabled) {
				return;
			}

			// Clear any existing timeout
			clearTimeout(this.hoverTimeout);

			this.isHovering = true;

			// Only show if menu is closed
			if (!this.isMenuOpen) {
				this.hoverTimeout = setTimeout(function () {
					self.showMenu(false);
				}, this.config.hoverDelay);
			}
		},

		/**
		 * On header leave
		 */
		onHeaderLeave: function () {
			// ✅ PROTECT: Ne pas animer si pas desktop
			if (!this.isDesktop()) {
				return;
			}

			// ✅ PROTECT: Ne pas animer si pas prêt
			if (!this.isReady) {
				return;
			}

			// ✅ NEW: Check if hover animation is enabled
			if (!this.hoverAnimationEnabled) {
				return;
			}

			// Clear any existing timeout
			clearTimeout(this.hoverTimeout);

			this.isHovering = false;

			// Only hide if menu was opened by hover (not by click)
			if (!this.isMenuOpen) {
				this.hideMenu(false);
			}
		},

		/**
		 * Destroy
		 */
		destroy: function () {
			// Unbind events
			this.$icon.off('click');
			$('#top-header').off('mouseenter mouseleave');

			// Reset styles
			gsap.set(this.$menu[0], { clearProps: 'all' });
			gsap.set(this.$iconImg[0], { clearProps: 'all' });
		}

	};

	/**
	 * Initialize on DOM ready
	 */
	$(document).ready(function () {
		// Wait a bit for GSAP to be ready
		setTimeout(function () {
			NovaHeaderMenu.init();
		}, 100);
	});

	/**
	 * Reinitialize on Elementor preview
	 */
	$(window).on('elementor/frontend/init', function () {
		if (typeof elementorFrontend !== 'undefined') {
			elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
				setTimeout(function () {
					NovaHeaderMenu.init();
				}, 100);
			});
		}
	});

	/**
	 * Handle window resize - check responsive state and refresh animation
	 */
	let resizeTimer;
	let lastDesktopState = null;

	function handleResize() {
		const isNowDesktop = NovaHeaderMenu.isDesktop();
		const $menu = $(NovaHeaderMenu.config.menuSelector);

		// Vérifier et corriger l'état à chaque resize
		if (isNowDesktop) {
			// Mode Desktop : vérifier que l'animation est initialisée
			if (!NovaHeaderMenu.isInitialized) {
				// Réinitialiser si pas encore initialisé
				NovaHeaderMenu.isInitialized = false;
				NovaHeaderMenu.isReady = false;
				setTimeout(function () {
					NovaHeaderMenu.init();
				}, 100);
			} else if (NovaHeaderMenu.isInitialized && NovaHeaderMenu.isReady) {
				// Vérifier que le menu est dans le bon état
				if ($menu.length > 0) {
					const currentWidth = gsap.getProperty($menu[0], 'width');
					// Si le menu devrait être fermé mais est ouvert, le corriger
					if (!NovaHeaderMenu.isMenuOpen && currentWidth > 0 && currentWidth !== '0px') {
						// Tuer les animations en cours et forcer l'état fermé
						gsap.killTweensOf($menu[0]);
						gsap.set($menu[0], { width: 0 });
						$menu[0].style.setProperty('width', '0', 'important');
						$menu[0].style.setProperty('overflow', 'hidden', 'important');
					}
					// Si le menu devrait être ouvert mais est fermé, le corriger
					if (NovaHeaderMenu.isMenuOpen && (currentWidth === 0 || currentWidth === '0px')) {
						// Tuer les animations en cours et forcer l'état ouvert
						gsap.killTweensOf($menu[0]);
						const targetWidth = NovaHeaderMenu.getMenuWidth();
						gsap.set($menu[0], { width: targetWidth });
						$menu[0].style.setProperty('width', 'auto', 'important');
						$menu[0].style.setProperty('overflow', 'visible', 'important');
					}
				}
			}
		} else {
			// Mode Mobile : menu doit être toujours visible
			if ($menu.length > 0) {
				// Tuer toutes les animations GSAP
				gsap.killTweensOf($menu[0]);
				gsap.killTweensOf($(NovaHeaderMenu.config.iconImgSelector)[0]);

				// Forcer le menu visible
				$menu[0].style.width = 'auto';
				$menu[0].style.overflow = 'visible';
				$menu[0].style.opacity = '1';

				// Réinitialiser l'icône
				const $iconImg = $(NovaHeaderMenu.config.iconImgSelector);
				if ($iconImg.length > 0) {
					gsap.set($iconImg[0], { rotation: 0 });
				}
			}

			// Si on passe de desktop à mobile, réinitialiser les états
			if (lastDesktopState === true) {
				NovaHeaderMenu.isInitialized = false;
				NovaHeaderMenu.isReady = false;
				NovaHeaderMenu.isMenuOpen = true;
			}
		}

		// Mettre à jour l'état connu
		lastDesktopState = isNowDesktop;
	}

	$(window).on('resize', function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(handleResize, 250);
	});

	// Initialiser l'état au chargement
	$(document).ready(function () {
		lastDesktopState = NovaHeaderMenu.isDesktop();
	});

	/**
	 * Expose globally
	 */
	window.NovaHeaderMenu = NovaHeaderMenu;

})(jQuery);


/**
 * CSS OPTIONNEL pour améliorer les performances
 * Ajoutez dans votre CSS:
 */

/*
#top-header-2 {
	will-change: width, opacity;
	backface-visibility: hidden;
	transform: translateZ(0);
}

#top-header-3 img {
	will-change: transform;
	backface-visibility: hidden;
	transform: translateZ(0);
	transition: transform 0.3s ease;
}

#top-header-3 {
	cursor: pointer;
	user-select: none;
}

#top-header-3:hover {
	opacity: 1.8;
}
*/