/**
 * NOVA SHUFFLE CARD - Solution personnalisée avec effet Cards
 * Supporte navigation par boutons ET drag/swipe
 * Alternative à Swiper.js pour l'effet cards
 */

(function ($) {
	'use strict';

	class NovaShuffleCard {
		constructor(element, options = {}) {
			this.container = element;
			this.$container = $(element);
			this.options = {
				speed: options.speed || 400,
				rotate: options.rotate !== false,
				perSlideOffset: options.perSlideOffset || 8,
				perSlideRotate: options.perSlideRotate || 2,
				maxVisible: options.maxVisible || 3,
				loop: options.loop !== false,
				...options
			};

			this.currentIndex = 0;
			this.isAnimating = false;
			this.slides = [];
			this.totalSlides = 0;

			// Touch/Drag state
			this.touchStartX = 0;
			this.touchStartY = 0;
			this.isDragging = false;
			this.dragOffset = 0;
			this.startIndex = 0;

			this.init();
		}

		init() {
			// Récupérer les slides
			this.$slides = this.$container.find('.nova-shuffle-card-slide');
			this.totalSlides = this.$slides.length;

			if (this.totalSlides === 0) {
				return;
			}

			// Préparer le conteneur
			// S'assurer que le conteneur a une hauteur minimale basée sur la première carte
			const firstSlide = this.$slides.first();
			if (firstSlide.length) {
				const slideHeight = firstSlide.outerHeight(true);
				this.$container.css({
					position: 'relative',
					overflow: 'visible',
					perspective: '1200px',
					minHeight: slideHeight || 'auto'
				});
			} else {
				this.$container.css({
					position: 'relative',
					overflow: 'visible',
					perspective: '1200px'
				});
			}

			// Initialiser chaque slide
			this.$slides.each((index, slide) => {
				const $slide = $(slide);
				$slide.css({
					position: 'absolute',
					top: 0,
					left: 0,
					width: '100%',
					height: '100%',
					transformStyle: 'preserve-3d',
					transformOrigin: 'center center',
					// Animation plus fluide et professionnelle
					transition: `transform ${this.options.speed}ms cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity ${this.options.speed}ms ease-out, z-index 0ms linear`,
					willChange: 'transform, opacity',
					backfaceVisibility: 'hidden',
					WebkitBackfaceVisibility: 'hidden',
					// S'assurer que le contenu ne déborde pas pour éviter l'overlap
					overflow: 'hidden',
					// Améliorer les performances
					transform: 'translateZ(0)',
					WebkitTransform: 'translateZ(0)',
					// Isolation pour créer un contexte de stacking propre
					isolation: 'isolate'
				});

				this.slides.push({
					element: slide,
					$element: $slide,
					index: index
				});
			});

			// Position initiale
			this.updatePositions(false);

			// Setup navigation
			this.setupNavigation();

			// Setup pagination
			this.setupPagination();

			// Touch/Drag support
			this.setupTouch();
		}

		updatePositions(animate = true) {
			const current = this.currentIndex;


			this.slides.forEach((slide, index) => {
				const offset = this.getRelativePosition(index, current);
				const transform = this.getTransform(offset);
				const zIndex = this.getZIndex(offset);
				const opacity = this.getOpacity(offset);
				const absOffset = Math.abs(offset);


				if (!animate) {
					slide.$element.css('transition', 'none');
				}

				// Appliquer les styles avec gestion de la visibilité
				// Pour éviter le chevauchement de texte, on cache complètement les cartes avec opacity très faible
				slide.$element.css({
					transform: transform,
					zIndex: zIndex,
					opacity: opacity,
					pointerEvents: offset === 0 ? 'auto' : 'none',
					// Cacher complètement les cartes avec opacity < 0.1 pour éviter le ghosting
					visibility: opacity >= 0.1 ? 'visible' : 'hidden',
					// S'assurer que le contenu ne déborde pas
					overflow: 'hidden',
					// Empêcher le contenu de se superposer
					willChange: offset === 0 ? 'transform, opacity' : 'auto'
				});

				// S'assurer que le contenu de la carte (nova-shuffle-card-item) ne déborde pas
				const $cardItem = slide.$element.find('.nova-shuffle-card-item');
				if ($cardItem.length) {
					$cardItem.css({
						overflow: 'hidden',
						position: 'relative',
						zIndex: offset === 0 ? 'auto' : 'inherit',
						// Isolation pour créer un nouveau contexte de stacking et éviter l'overlap
						isolation: 'isolate'
					});
				}

				if (!animate) {
					// Force reflow pour appliquer les changements immédiatement
					slide.element.offsetHeight;
					slide.$element.css('transition', '');
				}
			});

		}

		getRelativePosition(slideIndex, currentIndex) {
			// Calculer la position relative (peut être négative)
			let diff = slideIndex - currentIndex;

			// Wrap autour si loop activé
			if (this.options.loop) {
				if (diff > this.totalSlides / 2) {
					diff -= this.totalSlides;
				} else if (diff < -this.totalSlides / 2) {
					diff += this.totalSlides;
				}
			}

			return diff;
		}

		getTransform(offset) {
			if (offset === 0) {
				// Slide actuelle - centrée et au premier plan, pas de rotation
				return 'translate3d(0, 0, 0) rotateZ(0deg) scale(1)';
			}

			const isNext = offset > 0; // offset > 0 = carte suivante (à droite), offset < 0 = carte précédente (à gauche)
			const absOffset = Math.abs(offset);

			// Limiter à maxVisible - masquer complètement les cartes trop loin
			if (absOffset > this.options.maxVisible) {
				return 'translate3d(0, 0, -500px) scale(0.5)';
			}

			// Calcul des transformations pour l'effet "cards" visible
			const translateY = absOffset * this.options.perSlideOffset;
			// Augmenter translateZ pour mieux séparer les cartes et éviter l'overlap
			const translateZ = -absOffset * 50; // Plus d'espacement en Z pour mieux séparer les cartes

			// Rotation alternée : cartes à droite (offset > 0) = rotation positive (clockwise)
			// cartes à gauche (offset < 0) = rotation négative (counter-clockwise)
			const rotateZ = this.options.rotate
				? (isNext ? absOffset : -absOffset) * this.options.perSlideRotate
				: 0;

			// Scale légèrement réduit pour les cartes en arrière-plan
			const scale = Math.max(0.88, 1 - (absOffset * 0.06)); // Réduction plus importante pour mieux séparer

			const transform = `translate3d(0, ${translateY}px, ${translateZ}px) rotateZ(${rotateZ}deg) scale(${scale})`;

			return transform;
		}

		getZIndex(offset) {
			let zIndex;

			if (offset === 0) {
				zIndex = 1000; // Carte active : z-index très élevé pour être au-dessus de tout
			} else {
				const absOffset = Math.abs(offset);
				// S'assurer que les cartes en arrière-plan ont un z-index beaucoup plus bas
				// pour éviter tout chevauchement avec la carte active
				if (absOffset > this.options.maxVisible) {
					zIndex = 0;
				} else {
					// Décrémenter de 50 pour chaque carte pour créer une séparation claire
					zIndex = 1000 - (absOffset * 50);
				}
			}


			return zIndex;
		}

		getOpacity(offset) {
			const absOffset = Math.abs(offset);
			let opacity;

			if (absOffset === 0) {
				opacity = 1; // Carte active : complètement opaque
			} else if (absOffset > this.options.maxVisible) {
				opacity = 0; // Carte trop éloignée : invisible
			} else if (absOffset === 1) {
				opacity = 0.3; // Première carte derrière : très transparente pour éviter l'overlap
			} else if (absOffset === 2) {
				opacity = 0.15; // Deuxième carte : presque invisible
			} else if (absOffset === 3) {
				opacity = 0.05; // Troisième carte : très faible
			} else {
				opacity = 0; // Autres cartes : invisibles
			}


			return opacity;
		}

		slideTo(index, animate = true) {

			if (this.isAnimating) {
				return;
			}

			// Normaliser l'index
			const originalIndex = index;
			if (this.options.loop) {
				index = ((index % this.totalSlides) + this.totalSlides) % this.totalSlides;
			} else {
				index = Math.max(0, Math.min(index, this.totalSlides - 1));
			}


			if (index === this.currentIndex) {
				return;
			}

			this.isAnimating = true;
			const oldIndex = this.currentIndex;
			this.currentIndex = index;


			// Mettre à jour les positions avec animation
			this.updatePositions(animate);

			// Débloquer après animation avec un petit délai pour s'assurer que l'animation est terminée
			const timeoutDuration = this.options.speed + 100;

			setTimeout(() => {
				this.isAnimating = false;
				// Forcer une mise à jour finale pour s'assurer que tout est correctement positionné
				this.updatePositions(false);
				this.onSlideChange();
			}, timeoutDuration); // Buffer pour s'assurer que l'animation est complète
		}

		next() {

			if (this.options.loop) {
				const nextIndex = (this.currentIndex + 1) % this.totalSlides;
				this.slideTo(nextIndex);
			} else {
				if (this.currentIndex < this.totalSlides - 1) {
					const nextIndex = this.currentIndex + 1;
					this.slideTo(nextIndex);
				}
			}
		}

		prev() {

			if (this.options.loop) {
				const prevIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides;
				this.slideTo(prevIndex);
			} else {
				if (this.currentIndex > 0) {
					const prevIndex = this.currentIndex - 1;
					this.slideTo(prevIndex);
				}
			}
		}

		setupNavigation() {
			// Chercher les boutons dans le widget parent
			const $widget = this.$container.closest('.nova-shuffle-card-widget');
			const $next = $widget.find('.nova-shuffle-card-nav-next, .swiper-button-next');
			const $prev = $widget.find('.nova-shuffle-card-nav-prev, .swiper-button-prev');

			// Supprimer les anciens handlers
			$next.off('click.nova-shuffle');
			$prev.off('click.nova-shuffle');

			$next.on('click.nova-shuffle', (e) => {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				if (!this.isAnimating && this.totalSlides > 0) {
					this.next();
				}
				return false;
			});

			$prev.on('click.nova-shuffle', (e) => {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				if (!this.isAnimating && this.totalSlides > 0) {
					this.prev();
				}
				return false;
			});

			// Mettre à jour l'état des boutons
			this.updateNavigationButtons($next, $prev);
		}

		updateNavigationButtons($next, $prev) {
			if (!this.options.loop) {
				if (this.currentIndex === 0) {
					$prev.addClass('disabled');
				} else {
					$prev.removeClass('disabled');
				}

				if (this.currentIndex >= this.totalSlides - 1) {
					$next.addClass('disabled');
				} else {
					$next.removeClass('disabled');
				}
			} else {
				$next.removeClass('disabled');
				$prev.removeClass('disabled');
			}
		}

		setupTouch() {
			const $container = this.$container;
			const container = this.container; // Élément DOM natif
			let startX = 0;
			let startY = 0;
			let isDragging = false;
			let currentX = 0;
			let startIndex = 0;
			const self = this;

			// Stocker les handlers pour pouvoir les supprimer dans destroy()
			this._touchHandlers = this._touchHandlers || {};

			// Touch start - utiliser addEventListener natif avec passive: true
			const touchStartHandler = (e) => {
				if (self.isAnimating) return;

				// Ignorer si le clic vient d'un bouton de navigation
				if ($(e.target).closest('.nova-shuffle-card-nav-next, .nova-shuffle-card-nav-prev, .swiper-button-next, .swiper-button-prev').length) {
					return;
				}

				const touch = e.touches[0];
				startX = touch.clientX;
				startY = touch.clientY;
				currentX = startX;
				isDragging = false;
				startIndex = self.currentIndex;
			};

			this._touchHandlers.touchStart = touchStartHandler;
			container.addEventListener('touchstart', touchStartHandler, { passive: true });

			// Mouse down
			$container.on('mousedown', (e) => {
				if (this.isAnimating) return;

				// Ignorer si le clic vient d'un bouton de navigation
				if ($(e.target).closest('.nova-shuffle-card-nav-next, .nova-shuffle-card-nav-prev, .swiper-button-next, .swiper-button-prev').length) {
					return;
				}

				startX = e.clientX;
				startY = e.clientY;
				currentX = startX;
				isDragging = false;
				startIndex = this.currentIndex;
			});

			// Touch move - utiliser addEventListener natif avec passive: false (on a besoin de preventDefault)
			const touchMoveHandler = (e) => {
				if (!startX || self.isAnimating) return;

				const touch = e.touches[0];
				const diffX = touch.clientX - startX;
				const diffY = Math.abs(touch.clientY - startY);

				// Détecter le drag horizontal
				if (Math.abs(diffX) > 10 && Math.abs(diffX) > diffY) {
					isDragging = true;
					currentX = touch.clientX;

					// Appliquer le drag en temps réel
					const dragOffset = diffX;
					self.applyDragOffset(dragOffset);

					e.preventDefault();
				}
			};

			this._touchHandlers.touchMove = touchMoveHandler;
			container.addEventListener('touchmove', touchMoveHandler, { passive: false });

			// Mouse move
			$container.on('mousemove', (e) => {
				if (!startX || this.isAnimating) return;

				const diffX = e.clientX - startX;
				const diffY = Math.abs(e.clientY - startY);

				// Détecter le drag horizontal
				if (Math.abs(diffX) > 10 && Math.abs(diffX) > diffY) {
					isDragging = true;
					currentX = e.clientX;

					// Appliquer le drag en temps réel
					const dragOffset = diffX;
					this.applyDragOffset(dragOffset);
				}
			});

			// Touch end - utiliser addEventListener natif avec passive: true
			const touchEndHandler = (e) => {
				if (!startX || self.isAnimating) return;

				const touch = e.changedTouches[0];
				const diffX = touch.clientX - startX;
				const threshold = 50; // Seuil minimum pour déclencher le slide

				if (isDragging && Math.abs(diffX) > threshold) {
					if (diffX > 0) {
						self.prev();
					} else {
						self.next();
					}
				} else {
					// Annuler le drag
					self.updatePositions(true);
				}

				// Reset
				startX = 0;
				startY = 0;
				isDragging = false;
				currentX = 0;
			};

			container.addEventListener('touchend', touchEndHandler, { passive: true });

			// Mouse up
			$container.on('mouseup', (e) => {
				if (!startX || this.isAnimating) return;

				const diffX = e.clientX - startX;
				const threshold = 50; // Seuil minimum pour déclencher le slide

				if (isDragging && Math.abs(diffX) > threshold) {
					if (diffX > 0) {
						this.prev();
					} else {
						this.next();
					}
				} else {
					// Annuler le drag
					this.updatePositions(true);
				}

				// Reset
				startX = 0;
				startY = 0;
				isDragging = false;
				currentX = 0;
			});

			// Mouse leave (annuler le drag si la souris sort)
			$container.on('mouseleave', () => {
				if (isDragging) {
					this.updatePositions(true);
					startX = 0;
					startY = 0;
					isDragging = false;
					currentX = 0;
				}
			});
		}

		applyDragOffset(offset) {
			// Appliquer un offset de drag à la slide active
			const activeSlide = this.slides[this.currentIndex];
			if (!activeSlide) return;

			const baseTransform = this.getTransform(0);
			// Extraire les valeurs de base
			const translateX = offset;

			activeSlide.$element.css({
				transform: `translate3d(${translateX}px, 0, 0) rotateZ(0deg) scale(1)`,
				transition: 'none'
			});
		}

		onSlideChange() {

			// Callback personnalisable
			if (this.options.onSlideChange) {
				this.options.onSlideChange(this.currentIndex);
			}

			// Mettre à jour les boutons de navigation
			const $widget = this.$container.closest('.nova-shuffle-card-widget');
			const $next = $widget.find('.nova-shuffle-card-nav-next, .swiper-button-next');
			const $prev = $widget.find('.nova-shuffle-card-nav-prev, .swiper-button-prev');
			this.updateNavigationButtons($next, $prev);

			// Mettre à jour la pagination si elle existe
			this.updatePagination();
		}

		updatePagination() {
			const $widget = this.$container.closest('.nova-shuffle-card-widget');
			const $pagination = $widget.find('.swiper-pagination, .nova-shuffle-card-pagination');

			if ($pagination.length) {
				$pagination.find('.swiper-pagination-bullet, .pagination-bullet').removeClass('active');
				$pagination.find('.swiper-pagination-bullet, .pagination-bullet').eq(this.currentIndex).addClass('active');
			}
		}

		setupPagination() {
			const $widget = this.$container.closest('.nova-shuffle-card-widget');
			const $pagination = $widget.find('.swiper-pagination, .nova-shuffle-card-pagination');

			if ($pagination.length) {
				// Supprimer les anciens handlers
				$pagination.find('.swiper-pagination-bullet, .pagination-bullet').off('click.nova-shuffle');

				// Ajouter les handlers
				$pagination.find('.swiper-pagination-bullet, .pagination-bullet').on('click.nova-shuffle', (e) => {
					e.preventDefault();
					e.stopPropagation();

					const $bullet = $(e.currentTarget);
					const index = parseInt($bullet.data('index')) || $bullet.index();

					if (!this.isAnimating && index !== this.currentIndex) {
						this.slideTo(index);
					}
				});
			}
		}

		destroy() {
			// Supprimer les event listeners natifs
			if (this._touchHandlers && this.container) {
				if (this._touchHandlers.touchStart) {
					this.container.removeEventListener('touchstart', this._touchHandlers.touchStart, { passive: true });
				}
				if (this._touchHandlers.touchMove) {
					this.container.removeEventListener('touchmove', this._touchHandlers.touchMove, { passive: false });
				}
				if (this._touchHandlers.touchEnd) {
					this.container.removeEventListener('touchend', this._touchHandlers.touchEnd, { passive: true });
				}
			}

			this.$container.off();
			this.$slides.each((index, slide) => {
				$(slide).attr('style', '');
			});
		}
	}

	// Initialisation pour Elementor
	function initNovaShuffleCard($scope) {
		// Chercher le slider dans le scope
		let $sliders = $scope.find ? $scope.find('.nova-shuffle-card-slider') : $('.nova-shuffle-card-slider', $scope);

		// Si le scope lui-même est un slider
		if ($scope.is && $scope.is('.nova-shuffle-card-slider')) {
			$sliders = $sliders.add($scope);
		}

		$sliders.each(function () {
			const $container = $(this);

			// Vérifier si déjà initialisé
			if ($container.data('nova-shuffle-card-initialized')) {
				return;
			}

			// Récupérer la config depuis le widget parent
			const $widget = $container.closest('.nova-shuffle-card-widget');

			const configData = $widget.data('swiper-config') || $widget.attr('data-swiper-config');

			let config = {};
			if (configData) {
				try {
					config = typeof configData === 'string' ? JSON.parse(configData) : configData;
				} catch (e) {
					// Erreur silencieuse
				}
			}

			// Mapper la config Swiper vers notre config
			const options = {
				speed: config.speed || 400,
				rotate: config.cardsEffect?.rotate !== false,
				perSlideOffset: config.cardsEffect?.perSlideOffset || 8,
				perSlideRotate: config.cardsEffect?.perSlideRotate || 2,
				maxVisible: 3,
				loop: config.loop !== false,
				onSlideChange: function (index) {
					console.log(`[ANIMATION] onSlideChange callback - newIndex: ${index}`);
				}
			};

			// Initialiser
			try {
				const instance = new NovaShuffleCard(this, options);
				$container.data('nova-shuffle-card', instance);
				$container.data('nova-shuffle-card-initialized', true);
			} catch (e) {
				console.error(`[ANIMATION] Erreur lors de l'initialisation:`, e);
			}
		});
	}

	// Init sur DOM ready
	$(document).ready(function () {
		$('.nova-shuffle-card-slider').each(function () {
			initNovaShuffleCard($(this));
		});
	});

	// Elementor support
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/nova-shuffle-card.default',
			function ($scope) {
				initNovaShuffleCard($scope);
			}
		);

		// Fallback pour tous les widgets
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/widget',
			function ($scope) {
				if ($scope.find('.nova-shuffle-card-slider').length || $scope.find('.nova-shuffle-card-widget').length) {
					initNovaShuffleCard($scope);
				}
			}
		);
	}

	// Fallback supplémentaire après un délai
	setTimeout(function () {
		$('.nova-shuffle-card-slider').each(function () {
			const $container = $(this);
			if (!$container.data('nova-shuffle-card-initialized')) {
				initNovaShuffleCard($container);
			}
		});
	}, 1000);

	// Exporter la classe
	window.NovaShuffleCard = NovaShuffleCard;

})(jQuery);
