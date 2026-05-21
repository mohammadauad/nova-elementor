/**
 * NOVA Gallery Widget - JavaScript
 */

(function ($) {
	'use strict';

	const NOVAGallery = {
		instances: [],

		/**
		 * Check if we're in Elementor editor
		 */
		isElementorEditor: function () {
			return typeof elementor !== 'undefined' && elementor.config && elementor.config.is_rtl !== undefined;
		},

		/**
		 * Initialize all gallery widgets
		 */
		init: function () {
			this.initInstances();
		},

		/**
		 * Initialize all instances
		 */
		initInstances: function () {
			$('.nova-gallery-widget').each(function () {
				const $widget = $(this);
				if (!$widget.data('NOVA-gallery-initialized')) {
					NOVAGallery.initInstance($widget);
				}
			});
		},

		/**
		 * Initialize a single gallery instance
		 */
		initInstance: function ($widget) {
			if ($widget.data('NOVA-gallery-initialized')) {
				return;
			}
			$widget.data('NOVA-gallery-initialized', true);

			// Configuration
			const columns = parseInt($widget.data('columns')) || 4;
			const columnsTablet = parseInt($widget.data('columns-tablet')) || 3;
			const columnsMobile = parseInt($widget.data('columns-mobile')) || 2;
			const enableFiltersRaw = $widget.data('enable-filters');
			const enableFilters = enableFiltersRaw === 'yes';
			const enablePopup = $widget.data('enable-popup') === 'yes';

			// Initializing widget

			// Parse grid config (for pixels mode)
			let gridConfig = $widget.data('grid-config') || {};
			if (typeof gridConfig === 'string') {
				try {
					const decoded = gridConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
					gridConfig = JSON.parse(decoded);
				} catch (e) {
					// Error parsing grid config
					gridConfig = {};
				}
			}

			// Appliquer la grille responsive
			this.applyGrid($widget, columns, columnsTablet, columnsMobile, gridConfig);

			// Reveal wrapper so CSS knows JS is ready and takes over animation
			$widget.addClass('nova-js-ready');

			// Initialiser les filtres si activés
			if (enableFilters) {
				this.initFilters($widget);
				this.initStickyFilters($widget);
			}

			// Initialiser le popup si activé
			if (enablePopup) {
				this.initPopup($widget);
			}

			// Réappliquer la grille lors du redimensionnement (avec debounce)
			let resizeTimer;
			const resizeHandlerName = 'resize.NOVA-gallery-' + ($widget.attr('id') || Date.now());
			$(window).off(resizeHandlerName).on(resizeHandlerName, function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					NOVAGallery.applyGrid($widget, columns, columnsTablet, columnsMobile, gridConfig);
				}, 250);
			});

			// In Elementor editor, also listen for panel updates
			if (this.isElementorEditor()) {
				if (typeof elementor !== 'undefined' && elementor.hooks) {
					elementor.hooks.addAction('panel/open_editor/widget/nova-gallery', function () {
						setTimeout(function () {
							NOVAGallery.applyGrid($widget, columns, columnsTablet, columnsMobile, gridConfig);
						}, 200);
					});
				}
			}

			// Store instance
			NOVAGallery.instances.push({
				widget: $widget,
				destroy: function () {
					$(window).off('resize.NOVA-gallery-' + $widget.attr('id'));
					$widget.data('NOVA-gallery-initialized', false);
				}
			});
		},

		/**
		 * Appliquer la grille responsive
		 */
		applyGrid: function ($widget, columns, columnsTablet, columnsMobile, gridConfig) {
			const $grid = $widget.find('.nova-gallery-grid');
			if ($grid.length === 0) {
				if (window.DEBUG) {
					// Grid element not found
				}
				return;
			}

			const self = this;
			const windowWidth = $(window).width();

			// Ensure gridConfig is an object
			if (!gridConfig || typeof gridConfig !== 'object') {
				gridConfig = {};
			}

			const gridMode = gridConfig.mode || 'auto';
			let gridTemplateColumns = '';
			let columnWidth = 300;

			if (gridMode === 'pixels') {
				// Mode pixels: utiliser repeat(auto-fill, minmax(XXXpx, XXXpx)) ou repeat(auto-fit, minmax(XXXpx, 1fr))
				columnWidth = parseInt(gridConfig.column_width) || 300;

				// In Elementor editor, always use desktop column width for preview
				if (self.isElementorEditor()) {
					columnWidth = parseInt(gridConfig.column_width) || 300;
				} else {
					// Determine column width based on viewport for frontend
					if (windowWidth < 768) {
						columnWidth = parseInt(gridConfig.column_width_mobile) || parseInt(gridConfig.column_width) || 200;
					} else if (windowWidth < 1024) {
						columnWidth = parseInt(gridConfig.column_width_tablet) || parseInt(gridConfig.column_width) || 250;
					} else {
						columnWidth = parseInt(gridConfig.column_width) || 300;
					}
				}

				// Ensure column width is a valid number
				if (isNaN(columnWidth) || columnWidth < 100) {
					columnWidth = 300;
				}

				// If auto-center is enabled, use auto-fill with fixed width to allow centering
				if (gridConfig.auto_center === true || gridConfig.auto_center === 'yes' || gridConfig.auto_center === 1) {
					gridTemplateColumns = `repeat(auto-fill, minmax(${columnWidth}px, ${columnWidth}px))`;
				} else {
					gridTemplateColumns = `repeat(auto-fit, minmax(${columnWidth}px, 1fr))`;
				}

				if (window.DEBUG) {
					// Using pixels mode
				}
			} else {
				// Mode auto: utiliser repeat(n, 1fr)
				// In Elementor editor, always use desktop columns for preview
				let cols = columns;
				if (self.isElementorEditor()) {
					cols = columns;
					if (window.DEBUG) {
						// Using desktop columns
					}
				} else {
					// Determine columns based on viewport for frontend
					if (windowWidth < 768) {
						cols = columnsMobile;
					} else if (windowWidth < 1024) {
						cols = columnsTablet;
					} else {
						cols = columns;
					}
				}

				// Ensure columns is a valid number
				if (isNaN(cols) || cols < 1) {
					cols = 4;
				}

				gridTemplateColumns = `repeat(${cols}, 1fr)`;

				if (window.DEBUG) {
					// Using auto mode
				}
			}

			// Apply grid columns with !important to override default CSS
			try {
				$grid[0].style.setProperty('grid-template-columns', gridTemplateColumns, 'important');
			} catch (e) {
				$grid[0].style.gridTemplateColumns = gridTemplateColumns;
			}

			// Also set via jQuery as fallback
			$grid.css('grid-template-columns', gridTemplateColumns + ' !important');

			// Set data attribute for CSS fallback
			if (gridMode === 'auto') {
				const match = gridTemplateColumns.match(/\d+/);
				const cols = match ? parseInt(match[0]) : 4;
				$grid.attr('data-grid-columns', cols);
				$grid.removeAttr('data-grid-mode');
			} else {
				$grid.attr('data-grid-mode', 'pixels');
				$grid.removeAttr('data-grid-columns');
			}

			// Apply auto-center for pixels mode if enabled
			if (gridMode === 'pixels' && (gridConfig.auto_center === true || gridConfig.auto_center === 'yes' || gridConfig.auto_center === 1)) {
				try {
					$grid[0].style.setProperty('justify-items', 'center', 'important');
				} catch (e) {
					$grid[0].style.justifyItems = 'center';
				}
				$grid.css('justify-items', 'center !important');

				// Center items that are alone on their row
				requestAnimationFrame(() => {
					setTimeout(() => {
						self.centerLoneItems($grid, columnWidth);
					}, 50);
				});
			}

			// In editor, also set the style attribute directly as string to ensure it sticks
			const currentStyle = $grid[0].getAttribute('style') || '';
			let gridStyle = `grid-template-columns: ${gridTemplateColumns} !important;`;

			// Add justify-items if auto-center is enabled
			if (gridMode === 'pixels' && (gridConfig.auto_center === true || gridConfig.auto_center === 'yes' || gridConfig.auto_center === 1)) {
				gridStyle += ' justify-items: center !important;';
			}

			if (!currentStyle.includes('grid-template-columns')) {
				$grid[0].setAttribute('style', gridStyle + (currentStyle ? ' ' + currentStyle : ''));
			} else {
				let updatedStyle = currentStyle.replace(/grid-template-columns[^;]*;?/gi, `grid-template-columns: ${gridTemplateColumns} !important;`);
				if (gridMode === 'pixels' && (gridConfig.auto_center === true || gridConfig.auto_center === 'yes' || gridConfig.auto_center === 1)) {
					if (updatedStyle.includes('justify-items')) {
						updatedStyle = updatedStyle.replace(/justify-items[^;]*;?/gi, 'justify-items: center !important;');
					} else {
						updatedStyle += ' justify-items: center !important;';
					}
				}
				$grid[0].setAttribute('style', updatedStyle);
			}

			if (window.DEBUG) {
				// Applied grid template columns
			}
		},

		/**
		 * Initialiser les filtres
		 */
		initFilters: function ($widget) {
			const $filtersContainer = $widget.find('.nova-gallery-filters');
			const $filters = $widget.find('.nova-gallery-filter-item');
			const $items = $widget.find('.nova-gallery-item');
			const hideFilterAllMobile = $widget.data('hide-filter-all-mobile') === 'yes';

			// initFilters called

			if ($filtersContainer.length > 0) {
				// Filters container HTML
			}

			if ($filters.length === 0) {
				// No filters found
				return;
			}

			if ($items.length === 0) {
				// No items found
				return;
			}

			// Fonction pour appliquer le filtre (sans déclencher l'événement click)
			const applyFilter = function ($filter) {
				const filterValue = $filter.data('filter');

				// Mettre à jour l'état actif
				$filters.removeClass('active');
				$filter.addClass('active');

				// Filtrer les items
				if (filterValue === '*') {
					// Afficher tous les items
					$items.each(function () {
						const $item = $(this);
						if ($item.hasClass('hidden')) {
							$item.css('display', '');
							$item[0].offsetHeight;
							$item.removeClass('hidden').addClass('fade-in');
							setTimeout(function () {
								$item.removeClass('fade-in');
							}, 500);
						}
					});
				} else {
					// Filtrer par classe
					const filterClass = filterValue.replace('.', '');

					$items.each(function () {
						const $item = $(this);
						const hasClass = $item.hasClass(filterClass) || $item.is(filterValue);

						if (hasClass) {
							if ($item.hasClass('hidden')) {
								$item.css('display', '');
								$item[0].offsetHeight;
								$item.removeClass('hidden').addClass('fade-in');
								setTimeout(function () {
									$item.removeClass('fade-in');
								}, 500);
							}
						} else {
							if (!$item.hasClass('hidden')) {
								$item.addClass('hidden');
								setTimeout(function () {
									$item.css('display', 'none');
								}, 500);
							}
						}
					});
				}

				// Réappliquer la grille après le filtrage
				setTimeout(function () {
					const columns = parseInt($widget.data('columns')) || 4;
					const columnsTablet = parseInt($widget.data('columns-tablet')) || 3;
					const columnsMobile = parseInt($widget.data('columns-mobile')) || 2;
					let gridConfig = $widget.data('grid-config') || {};
					if (typeof gridConfig === 'string') {
						try {
							const decoded = gridConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
							gridConfig = JSON.parse(decoded);
						} catch (e) {
							gridConfig = {};
						}
					}
					NOVAGallery.applyGrid($widget, columns, columnsTablet, columnsMobile, gridConfig);
					$widget.trigger('filterChanged.nova-gallery');
				}, 350);
			};

			// Fonction pour gérer l'affichage du filtre "Tout" sur mobile
			const handleMobileFilterAll = function () {
				if (!hideFilterAllMobile) {
					return;
				}

				const isMobile = window.innerWidth <= 767;
				const $filterAll = $filters.filter('[data-filter="*"]');
				const $firstFilterAfterAll = $filters.not('[data-filter="*"]').first();

				if (isMobile) {
					// Masquer le filtre "Tout" sur mobile
					$filterAll.hide();

					// Si le filtre "Tout" est actif, activer le premier filtre après "Tout"
					if ($filterAll.hasClass('active')) {
						if ($firstFilterAfterAll.length > 0) {
							applyFilter($firstFilterAfterAll);
						}
					}
				} else {
					// Afficher le filtre "Tout" sur desktop
					$filterAll.show();
				}
			};

			// Appliquer au chargement
			handleMobileFilterAll();

			// Réappliquer lors du redimensionnement
			let resizeTimer;
			$(window).on('resize', function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(handleMobileFilterAll, 250);
			});

			$filters.on('click', function (e) {
				e.preventDefault();
				const $filter = $(this);
				applyFilter($filter);
			});
		},

		/**
		 * Center items that are alone on their row in pixels mode
		 * This function detects items that don't fill a complete row and centers them
		 */
		centerLoneItems: function ($grid, columnWidth) {
			if (!$grid || $grid.length === 0) {
				return;
			}

			const $items = $grid.find('.nova-gallery-item:not(.hidden)');
			if ($items.length === 0) {
				return;
			}

			// Get grid gap
			const gap = parseFloat(window.getComputedStyle($grid[0]).gap) || 20;

			// Calculate how many columns fit in the container width
			const containerWidth = $grid[0].offsetWidth;
			const itemsPerRow = Math.floor((containerWidth + gap) / (columnWidth + gap));

			if (itemsPerRow <= 0) {
				return;
			}

			// Reset all grid-column-start styles first
			$items.each(function () {
				const $item = $(this);
				$item[0].style.gridColumnStart = '';
				const currentStyle = $item[0].getAttribute('style') || '';
				if (currentStyle.includes('grid-column-start')) {
					const updatedStyle = currentStyle.replace(/grid-column-start[^;]*;?/gi, '').trim();
					if (updatedStyle) {
						$item[0].setAttribute('style', updatedStyle);
					} else {
						$item[0].removeAttribute('style');
					}
				}
			});

			// Force a reflow to get accurate positions
			$grid[0].offsetHeight;

			// Group items by their row position
			const rows = [];
			let currentRow = [];
			let lastTop = null;

			$items.each(function (index) {
				const $item = $(this);
				const rect = $item[0].getBoundingClientRect();
				const gridRect = $grid[0].getBoundingClientRect();
				const top = rect.top - gridRect.top;

				if (lastTop === null || Math.abs(top - lastTop) < 5) {
					// Same row (with 5px tolerance)
					currentRow.push({
						$item: $item,
						index: index,
						left: rect.left - gridRect.left
					});
					lastTop = top;
				} else {
					// New row
					if (currentRow.length > 0) {
						rows.push(currentRow);
					}
					currentRow = [{
						$item: $item,
						index: index,
						left: rect.left - gridRect.left
					}];
					lastTop = top;
				}
			});

			// Add the last row
			if (currentRow.length > 0) {
				rows.push(currentRow);
			}

			// Center items in rows that don't fill the complete row
			rows.forEach(function (row) {
				if (row.length < itemsPerRow && row.length > 0) {
					// This row doesn't fill completely, center it
					const startColumn = Math.ceil((itemsPerRow - row.length) / 2) + 1;
					const firstItem = row[0];

					try {
						firstItem.$item[0].style.setProperty('grid-column-start', startColumn, 'important');
					} catch (e) {
						firstItem.$item[0].style.gridColumnStart = startColumn;
					}

					firstItem.$item.css('grid-column-start', startColumn + ' !important');

					if (window.DEBUG) {
						// Centering lone items
					}
				} else {
					// Reset grid-column-start for complete rows
					row.forEach(function (item) {
						item.$item[0].style.gridColumnStart = '';
						const currentStyle = item.$item[0].getAttribute('style') || '';
						if (currentStyle.includes('grid-column-start')) {
							const updatedStyle = currentStyle.replace(/grid-column-start[^;]*;?/gi, '').trim();
							if (updatedStyle) {
								item.$item[0].setAttribute('style', updatedStyle);
							} else {
								item.$item[0].removeAttribute('style');
							}
						}
					});
				}
			});
		},

		/**
		 * Initialiser le sticky des filtres via GSAP ScrollTrigger
		 */
		initStickyFilters: function ($widget) {
			var $filters = $widget.find('.nova-filters-sticky');
			if (!$filters.length) return;

			var isSticky = function () {
				var w = window.innerWidth;
				if (w <= 767) return $filters.data('sticky-mobile') === 'yes';
				if (w <= 1024) return $filters.data('sticky-tablet') === 'yes';
				return $filters.data('sticky') === 'yes';
			};

			var getOffset = function () {
				var w = window.innerWidth;
				if (w <= 767) return parseInt($filters.data('sticky-offset-mobile')) || parseInt($filters.data('sticky-offset')) || 0;
				if (w <= 1024) return parseInt($filters.data('sticky-offset-tablet')) || parseInt($filters.data('sticky-offset')) || 0;
				return parseInt($filters.data('sticky-offset')) || 0;
			};

			// Fallback CSS si GSAP non disponible
			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				var applyCSS = function () {
					if (isSticky()) {
						$filters[0].style.position = 'sticky';
						$filters[0].style.top = getOffset() + 'px';
						$filters[0].style.zIndex = '100';
					} else {
						$filters[0].style.position = '';
						$filters[0].style.top = '';
					}
				};
				applyCSS();
				window.addEventListener('resize', applyCSS);
				return;
			}

			// GSAP ScrollTrigger pin
			var st = ScrollTrigger.create({
				trigger: $filters[0],
				start: function () { return isSticky() ? 'top top+=' + getOffset() : 'top top+=99999'; },
				endTrigger: $widget[0],
				end: function () { return isSticky() ? 'bottom top+=' + getOffset() : 'bottom top+=99999'; },
				pin: isSticky(),
				pinSpacing: false,
				onRefresh: function (self) {
					self.pin = isSticky();
				},
			});

			window.addEventListener('resize', function () {
				if (st) st.refresh();
			});
		},

		/**
		 * Initialiser le popup d'image avec slider
		 */
		initPopup: function ($widget) {
			// Le popup est maintenant dans le body, pas dans le widget
			const $popup = $('body').find('.nova-gallery-popup').first();
			if ($popup.length === 0) {
				if (window.DEBUG) {
					// Popup element not found
				}
				return;
			}

			// Remove any existing event handlers to avoid duplicates
			$widget.off('click', '.nova-gallery-item[data-popup-image]');

			const $items = $widget.find('.nova-gallery-item[data-popup-image]');
			if ($items.length === 0) {
				if (window.DEBUG) {
					// No items with popup data found
				}
				return;
			}

			const $popupImage = $popup.find('.nova-gallery-popup-image');
			const $popupTitle = $popup.find('.nova-gallery-popup-title');
			const $popupCounter = $popup.find('.nova-gallery-popup-counter');
			const $popupClose = $popup.find('.nova-gallery-popup-close');
			const $popupPrev = $popup.find('.nova-gallery-popup-prev');
			const $popupNext = $popup.find('.nova-gallery-popup-next');
			const $popupOverlay = $popup.find('.nova-gallery-popup-overlay');

			let currentIndex = 0;
			let images = [];

			// Collecter toutes les images visibles (non filtrées)
			function updateImagesList() {
				images = [];
				// Re-find items in case they were filtered or added dynamically
				const $currentItems = $widget.find('.nova-gallery-item[data-popup-image]');
				$currentItems.filter(':visible').each(function () {
					const $item = $(this);
					const imageUrl = $item.data('popup-image');
					const imageTitle = $item.data('popup-title') || '';
					if (imageUrl) {
						images.push({
							url: imageUrl,
							title: imageTitle,
							$item: $item
						});
					}
				});
			}

			// Ouvrir le popup avec une image spécifique
			function openPopup(index) {
				if (images.length === 0) {
					return;
				}

				currentIndex = Math.max(0, Math.min(index, images.length - 1));
				const image = images[currentIndex];

				$popupImage.attr('src', image.url).attr('alt', image.title);
				$popupTitle.text(image.title);
				$popupCounter.text((currentIndex + 1) + ' / ' + images.length);

				// Afficher/masquer les boutons de navigation
				if (images.length <= 1) {
					$popupPrev.hide();
					$popupNext.hide();
				} else {
					$popupPrev.show();
					$popupNext.show();
				}

				$popup.fadeIn(300);
				$('body').css('overflow', 'hidden');
			}

			// Fermer le popup
			function closePopup() {
				$popup.fadeOut(300);
				$('body').css('overflow', '');
			}

			// Image suivante
			function nextImage() {
				if (images.length === 0) return;
				currentIndex = (currentIndex + 1) % images.length;
				openPopup(currentIndex);
			}

			// Image précédente
			function prevImage() {
				if (images.length === 0) return;
				currentIndex = (currentIndex - 1 + images.length) % images.length;
				openPopup(currentIndex);
			}

			// Gestion des clics sur les items (utilisation de la délégation d'événements)
			$widget.on('click', '.nova-gallery-item[data-popup-image]', function (e) {
				e.preventDefault();
				e.stopPropagation();

				// Re-find items in case they were filtered
				const $allItems = $widget.find('.nova-gallery-item[data-popup-image]');
				updateImagesList();

				if (images.length === 0) {
					return;
				}

				// Trouver l'index de l'item cliqué
				const $clickedItem = $(this);
				let clickedIndex = -1;
				images.forEach(function (img, index) {
					if (img.$item && img.$item[0] === $clickedItem[0]) {
						clickedIndex = index;
					}
				});

				if (clickedIndex >= 0) {
					openPopup(clickedIndex);
				} else if (images.length > 0) {
					// Fallback: ouvrir la première image
					openPopup(0);
				}
			});

			// Bouton fermer
			$popupClose.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				closePopup();
			});

			// Overlay (fermer au clic)
			$popupOverlay.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				closePopup();
			});

			// Bouton suivant
			$popupNext.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				nextImage();
			});

			// Bouton précédent
			$popupPrev.on('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				prevImage();
			});

			// Navigation au clavier
			$(document).on('keydown.nova-gallery-popup', function (e) {
				if ($popup.is(':visible')) {
					if (e.key === 'Escape') {
						closePopup();
					} else if (e.key === 'ArrowRight') {
						nextImage();
					} else if (e.key === 'ArrowLeft') {
						prevImage();
					}
				}
			});

			// Mettre à jour la liste des images après un filtrage
			if ($widget.data('enable-filters') === 'yes') {
				$widget.on('filterChanged.nova-gallery', function () {
					setTimeout(function () {
						updateImagesList();
					}, 100);
				});
			}
		}
	};

	// Initialize on load
	$(document).ready(function () {
		NOVAGallery.init();
	});

	// Re-initialize on Elementor preview refresh
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction('frontend/element_ready/nova-gallery.default', function ($scope) {
			setTimeout(function () {
				$scope.find('.nova-gallery-widget').each(function () {
					const $widget = $(this);
					if (!$widget.data('NOVA-gallery-initialized')) {
						NOVAGallery.initInstance($widget);
					}
				});
			}, 100);
		});

		// Fallback for global hook
		elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
			setTimeout(function () {
				$('.nova-gallery-widget').each(function () {
					const $widget = $(this);
					if (!$widget.data('NOVA-gallery-initialized')) {
						NOVAGallery.initInstance($widget);
					}
				});
			}, 100);
		});
	}

	// Make available globally
	window.NOVAGallery = NOVAGallery;

})(jQuery);

