/**
 * NOVA Cards Widget - Animation
 * Version: 1.0.0
 */

(function ($) {
	'use strict';
	window.DEBUG = false;

	// Helper function to conditionally log (only if DEBUG is true)
	function debugLog() {
		if (window.DEBUG) {
		}
	}
	function debugWarn() {
		if (window.DEBUG) {
		}
	}
	function debugError() {
		if (window.DEBUG) {
		}
	}

	const NOVACards = {
		instances: [],

		isElementorEditor: function () {
			const resultFrontend = typeof elementorFrontend !== 'undefined'
				&& typeof elementorFrontend.isEditMode === 'function'
				&& elementorFrontend.isEditMode();
			const doc = document.documentElement || document.body;
			const hasDocClass = !!(doc && doc.classList && (doc.classList.contains('elementor-editor-active') || doc.classList.contains('elementor-edit-mode')));
			const hasBodyClass = !!(document.body && document.body.classList && (document.body.classList.contains('elementor-editor-active') || document.body.classList.contains('elementor-edit-mode')));
			let hasParentClass = false;
			try {
				if (window.self !== window.top && window.top.document && window.top.document.body) {
					const c = window.top.document.body.classList;
					hasParentClass = !!(c && (c.contains('elementor-editor-active') || c.contains('elementor-edit-mode')));
				}
			} catch (e) { /* cross-origin */ }
			const isEditor = !!(resultFrontend || hasDocClass || hasBodyClass || hasParentClass);
			return isEditor;
		},

		/**
		 * Initialize all card widgets
		 */
		init: function () {
			// Multiple initialization strategies for robustness
			if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
				// Elementor hook
				elementorFrontend.hooks.addAction('frontend/element_ready/nova-cards.default', function ($scope) {
					const $widget = $scope.find('.nova-cards-widget');
					if ($widget.length > 0) {
						NOVACards.initInstance($widget);
					}
				});
			}

			// DOM ready fallback
			$(document).ready(function () {
				$('.nova-cards-widget').each(function () {
					const $widget = $(this);
					NOVACards.initInstance($widget, false);
				});
			});

			// Window load fallback
			$(window).on('load', function () {
				$('.nova-cards-widget').each(function () {
					const $widget = $(this);
					NOVACards.initInstance($widget, true);
				});
			});

			// Elementor editor specific: re-initialize on panel changes
			if (this.isElementorEditor() && typeof elementor !== 'undefined') {
				// Re-initialize when widget is selected
				elementor.hooks.addAction('panel/open_editor/widget/nova-cards', function (panel, model, view) {
					setTimeout(function () {
						const $widget = view.$el.find('.nova-cards-widget');
						if ($widget.length > 0) {
							$widget.removeData('nova-cards-initialized');
							NOVACards.initInstance($widget);
						}
					}, 100);
				});

				// Re-initialize when settings are changed
				elementor.hooks.addAction('panel/widget/updated', function (panel, model, view) {
					if (model && model.get('widgetType') === 'nova-cards') {
						setTimeout(function () {
							const $widget = view.$el.find('.nova-cards-widget');
							if ($widget.length > 0) {
								$widget.removeData('nova-cards-initialized');
								NOVACards.initInstance($widget);
							}
						}, 300);
					}
				});

				// Re-initialize when element is selected (clicked) - additional hook
				elementor.hooks.addAction('element:before:refresh', function (view) {
					if (view && view.model && view.model.get('widgetType') === 'nova-cards') {
						setTimeout(function () {
							const $widget = view.$el.find('.nova-cards-widget');
							if ($widget.length > 0) {
								$widget.removeData('nova-cards-initialized');
								NOVACards.initInstance($widget);
							}
						}, 200);
					}
				});

				// Re-initialize when element is selected
				elementor.hooks.addAction('panel/open_editor/widget', function (panel, model, view) {
					if (model && model.get('widgetType') === 'nova-cards') {
						setTimeout(function () {
							const $widget = view.$el.find('.nova-cards-widget');
							if ($widget.length > 0) {
								$widget.removeData('nova-cards-initialized');
								NOVACards.initInstance($widget);
							}
						}, 100);
					}
				});

				// Also listen for element selection (click) - reapply grid immediately
				elementor.hooks.addAction('element:before:refresh', function (view) {
					if (view && view.model && view.model.get('widgetType') === 'nova-cards') {
						setTimeout(function () {
							const $widget = view.$el.find('.nova-cards-widget');
							if ($widget.length > 0) {
								$widget.removeData('nova-cards-initialized');
								NOVACards.initInstance($widget);
							}
						}, 200);
					}
				});

				// Re-initialize on element ready in editor
				if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
					elementorFrontend.hooks.addAction('frontend/element_ready/nova-cards.default', function ($scope) {
						setTimeout(function () {
							const $widget = $scope.find('.nova-cards-widget');
							if ($widget.length > 0) {
								$widget.removeData('nova-cards-initialized');
								NOVACards.initInstance($widget);
							}
						}, 100);
					});
				}
			}
		},

		/**
		 * Initialize a single cards widget instance
		 */
		initInstance: function ($widget, forceReinit) {
			// Vérifier que le widget existe
			if (!$widget || $widget.length === 0) {
				debugWarn('NOVA Cards: Widget element not found');
				return;
			}

			// Skip if already initialized (unless forced)
			if (!forceReinit && $widget.data('nova-cards-initialized')) {
				return;
			}

			debugLog('NOVA Cards: Initializing widget', $widget[0], 'forceReinit:', forceReinit);

			$widget.data('nova-cards-initialized', true);

			// Parse animation config
			let animationConfig = $widget.data('animation-config') || {};
			if (typeof animationConfig === 'string') {
				try {
					const decoded = animationConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
					animationConfig = JSON.parse(decoded);
				} catch (e) {
					debugError('NOVA Cards - Error parsing animation config:', e, animationConfig);
					animationConfig = {};
				}
			}

			// Parse grid config
			let gridConfig = $widget.data('grid-config') || {};
			if (typeof gridConfig === 'string') {
				try {
					const decoded = gridConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
					gridConfig = JSON.parse(decoded);
				} catch (e) {
					debugError('NOVA Cards - Error parsing grid config:', e, gridConfig);
					gridConfig = {};
				}
			}

			// Debug log
			if (window.DEBUG) {
				debugLog('NOVA Cards: Grid config parsed:', gridConfig, 'from data:', $widget.data('grid-config'));
			}

			// Apply grid configuration immediately
			this.applyGridConfig($widget, gridConfig);

			// Apply SVG aspect ratio to cards
			this.applySvgAspectRatio($widget);

			// Reveal wrapper so CSS knows JS is ready and takes over animation
			$widget.addClass('nova-js-ready');

			// Initialize animations
			if (animationConfig.enable !== false) {
				this.initAnimations($widget, animationConfig);
			} else {
				// If animation disabled, show all cards immediately
				$widget.find('.nova-card-item').addClass('animated');
			}

			// Initialize hover effects for creative background
			this.initHoverEffects($widget);

			// Initialize GSAP hover animation for background + overlay cards
			this.initBackgroundOverlayHover($widget);

			// Initialize filters if enabled
			if ($widget.data('enable-filters') === 'yes') {
				this.initFilters($widget);
			}
		},

		/**
		 * Apply grid configuration
		 * This function is public so it can be called directly from global scope
		 */
		applyGridConfig: function ($widget, config) {
			// Ne pas appliquer le grid dans l'éditeur Elementor (BO)
			if (this.isElementorEditor()) {
				return;
			}
			const $grid = $widget.find('.nova-cards-grid');
			if ($grid.length === 0) {
				if (window.DEBUG) {
					debugWarn('NOVA Cards: Grid element not found in widget:', $widget[0]);
				}
				return;
			}

			// Function to update grid columns based on viewport
			const self = this;
			const updateGridColumns = () => {
				const width = window.innerWidth;
				const gridMode = config.mode || 'auto';
				let gridTemplateColumns = '';
				let columnWidth = 300; // Default, will be updated in pixels mode

				if (gridMode === 'pixels') {
					// Mode pixels: utiliser repeat(auto-fit, minmax(XXXpx, 1fr))
					columnWidth = parseInt(config.column_width) || 300;

					// In Elementor editor, always use desktop column width for preview
					if (self.isElementorEditor()) {
						columnWidth = parseInt(config.column_width) || 300;
						if (window.DEBUG) {
							debugLog('NOVA Cards Editor: Applying grid column width (px):', columnWidth, 'from config:', config);
						}
					} else {
						// Determine column width based on viewport for frontend
						if (width <= 768) {
							columnWidth = parseInt(config.column_width_mobile) || parseInt(config.column_width) || 200;
						} else if (width <= 1024) {
							columnWidth = parseInt(config.column_width_tablet) || parseInt(config.column_width) || 250;
						} else {
							// Desktop: use the configured column width
							columnWidth = parseInt(config.column_width) || 300;
						}
					}

					// Ensure column width is a valid number
					if (isNaN(columnWidth) || columnWidth < 100) {
						columnWidth = 300;
					}

					// If auto-center is enabled, use auto-fill with fixed width to allow centering
					// Otherwise, use auto-fit with 1fr to stretch columns to fill available space
					if (config.auto_center === true || config.auto_center === 'yes' || config.auto_center === 1) {
						// Use auto-fill with fixed width to allow justify-content: center to work
						// auto-fill creates empty columns that can be centered, unlike auto-fit
						gridTemplateColumns = `repeat(auto-fill, minmax(${columnWidth}px, ${columnWidth}px))`;
					} else {
						// Use auto-fit with 1fr to stretch columns to fill available space
						gridTemplateColumns = `repeat(auto-fit, minmax(${columnWidth}px, 1fr))`;
					}

					if (window.DEBUG) {
						debugLog('NOVA Cards: Using pixels mode, column width:', columnWidth, 'px', 'auto-center:', config.auto_center);
					}
				} else {
					// Mode auto: utiliser repeat(n, 1fr)
					let columns = parseInt(config.columns) || 3;

					// In Elementor editor, always use desktop columns for preview
					if (self.isElementorEditor()) {
						// Use the configured desktop columns, not the default
						columns = parseInt(config.columns) || 3;
						if (window.DEBUG) {
							debugLog('NOVA Cards Editor: Applying grid columns:', columns, 'from config:', config);
						}
					} else {
						// Determine columns based on viewport for frontend
						if (width <= 768) {
							columns = parseInt(config.columns_mobile) || parseInt(config.columns) || 1;
						} else if (width <= 1024) {
							columns = parseInt(config.columns_tablet) || parseInt(config.columns) || 2;
						} else {
							// Desktop: use the configured columns
							columns = parseInt(config.columns) || 3;
						}
					}

					// Ensure columns is a valid number
					if (isNaN(columns) || columns < 1) {
						columns = 3;
					}

					gridTemplateColumns = `repeat(${columns}, 1fr)`;

					if (window.DEBUG) {
						debugLog('NOVA Cards: Using auto mode, columns:', columns);
					}
				}

				// Apply grid columns with !important to override default CSS
				// Use setProperty with important flag for better compatibility
				if ($grid.length > 0 && $grid[0]) {
					// Set data attribute for CSS fallback FIRST (before style, so CSS can apply immediately)
					if (gridMode === 'auto') {
						const match = gridTemplateColumns.match(/\d+/);
						const columns = match ? parseInt(match[0]) : 3;
						$grid.attr('data-grid-columns', columns);
					} else {
						$grid.attr('data-grid-mode', 'pixels');
						$grid.removeAttr('data-grid-columns');
					}

					// Apply via inline style with !important - use multiple methods for maximum compatibility
					try {
						$grid[0].style.setProperty('grid-template-columns', gridTemplateColumns, 'important');
					} catch (e) {
						// Fallback if setProperty fails
						$grid[0].style.gridTemplateColumns = gridTemplateColumns;
					}

					// Also set via direct style property
					$grid[0].style.gridTemplateColumns = gridTemplateColumns;

					// Also set via jQuery as additional fallback
					$grid.css('grid-template-columns', gridTemplateColumns + ' !important');

					// Apply auto-center for pixels mode if enabled
					// Use justify-items instead of justify-content for grid centering
					if (gridMode === 'pixels' && (config.auto_center === true || config.auto_center === 'yes' || config.auto_center === 1)) {
						try {
							$grid[0].style.setProperty('justify-items', 'center', 'important');
						} catch (e) {
							$grid[0].style.justifyItems = 'center';
						}
						$grid.css('justify-items', 'center !important');

						if (window.DEBUG) {
							debugLog('NOVA Cards: Applied auto-center (justify-items: center) for pixels mode, config.auto_center:', config.auto_center);
						}
					}

					// Force a reflow to ensure the style is applied
					$grid[0].offsetHeight;

					// Set the style attribute directly as string to ensure it sticks (both editor and frontend)
					const currentStyle = $grid[0].getAttribute('style') || '';
					let gridStyle = `grid-template-columns: ${gridTemplateColumns} !important;`;

					// Add justify-items if auto-center is enabled (use justify-items for grid, not justify-content)
					if (gridMode === 'pixels' && (config.auto_center === true || config.auto_center === 'yes' || config.auto_center === 1)) {
						gridStyle += ' justify-items: center !important;';
					}

					// Preserve justify-content from Elementor control (don't remove it)
					// The justify-content control should work independently

					// Only update if not already present
					if (!currentStyle.includes('grid-template-columns')) {
						// Preserve existing justify-content if present
						const existingJustifyContent = currentStyle.match(/justify-content[^;]*;?/gi);
						if (existingJustifyContent) {
							gridStyle += ' ' + existingJustifyContent[0];
						}
						$grid[0].setAttribute('style', gridStyle + (currentStyle ? ' ' + currentStyle : ''));
					} else {
						// Replace existing grid-template-columns
						let updatedStyle = currentStyle.replace(/grid-template-columns[^;]*;?/gi, `grid-template-columns: ${gridTemplateColumns} !important;`);
						// Also handle justify-items (use justify-items for grid, not justify-content)
						if (gridMode === 'pixels' && (config.auto_center === true || config.auto_center === 'yes' || config.auto_center === 1)) {
							if (updatedStyle.includes('justify-items')) {
								updatedStyle = updatedStyle.replace(/justify-items[^;]*;?/gi, 'justify-items: center !important;');
							} else {
								updatedStyle += ' justify-items: center !important;';
							}
						}
						// DON'T remove justify-content - let Elementor control handle it
						// The justify-content from Elementor CSS will work alongside our JavaScript centering
						$grid[0].setAttribute('style', updatedStyle);
					}

					if (window.DEBUG) {
						const computed = window.getComputedStyle($grid[0]).gridTemplateColumns;
						debugLog('NOVA Cards: Applied grid template columns:', gridTemplateColumns, 'to grid:', $grid[0], 'config:', config, 'computed style:', computed, 'inline style:', $grid[0].style.gridTemplateColumns);
					}

					// Center items that are alone on their row (for pixels mode with auto-center)
					// Use requestAnimationFrame to ensure CSS is applied first
					if (gridMode === 'pixels' && (config.auto_center === true || config.auto_center === 'yes' || config.auto_center === 1)) {
						requestAnimationFrame(() => {
							setTimeout(() => {
								self.centerLoneItems($grid, columnWidth);
							}, 50);
						});
					}
				} else {
					if (window.DEBUG) {
						debugWarn('NOVA Cards: Grid element not found when trying to apply columns');
					}
				}
			};

			// Initial update - always apply immediately
			updateGridColumns();

			// In editor, also try multiple times to ensure it sticks
			if (self.isElementorEditor()) {
				setTimeout(() => {
					updateGridColumns();
				}, 100);
				setTimeout(() => {
					updateGridColumns();
				}, 300);
				setTimeout(() => {
					updateGridColumns();
				}, 600);
			}

			// Update on resize (with debounce)
			let resizeTimer;
			const resizeHandlerName = 'resize.nova-cards-' + ($widget.attr('data-id') || Date.now());
			$(window).off(resizeHandlerName).on(resizeHandlerName, () => {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(() => {
					updateGridColumns();
				}, 250);
			});

			// In Elementor editor, also listen for element changes
			if (self.isElementorEditor()) {
				// Store update function for reuse
				const gridUpdateFn = updateGridColumns;

				// Re-apply when element is updated in editor
				if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
					elementorFrontend.hooks.addAction('frontend/element_ready/nova-cards.default', function ($scope) {
						const $editorWidget = $scope.find('.nova-cards-widget');
						if ($editorWidget.length > 0 && $editorWidget[0] === $grid[0].closest('.nova-cards-widget')) {
							setTimeout(() => {
								gridUpdateFn();
							}, 100);
						}
					});
				}

				// Listen for panel updates and widget selection
				if (typeof elementor !== 'undefined' && elementor.hooks) {
					// When widget is selected/opened in panel
					elementor.hooks.addAction('panel/open_editor/widget', function (panel, model, view) {
						if (model && model.get('widgetType') === 'nova-cards') {
							setTimeout(() => {
								gridUpdateFn();
							}, 150);
						}
					});

					// When widget settings are updated
					elementor.hooks.addAction('panel/widget/updated', function (panel, model, view) {
						if (model && model.get('widgetType') === 'nova-cards') {
							setTimeout(() => {
								gridUpdateFn();
							}, 300);
						}
					});

					// When element is selected (clicked) - use elementorFrontend hooks
					if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
						elementorFrontend.hooks.addAction('frontend/element/before_render', function (view) {
							if (view && view.model && view.model.get('widgetType') === 'nova-cards') {
								setTimeout(() => {
									gridUpdateFn();
								}, 200);
							}
						});
					}
				}

				// Also listen for element selection via jQuery
				$(document).on('click', '.elementor-element[data-widget_type="nova-cards.default"]', function () {
					setTimeout(() => {
						gridUpdateFn();
					}, 100);
				});

				// Listen for element selection via elementorFrontend - reapply grid config
				if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
					elementorFrontend.hooks.addAction('frontend/element_ready/nova-cards.default', function ($scope) {
						setTimeout(() => {
							const $editorWidget = $scope.find('.nova-cards-widget');
							if ($editorWidget.length > 0) {
								// Re-apply grid config when element is ready
								const gridConfigData = $editorWidget.data('grid-config') || {};
								let gridConfig = gridConfigData;
								if (typeof gridConfigData === 'string') {
									try {
										const decoded = gridConfigData.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
										gridConfig = JSON.parse(decoded);
									} catch (e) {
										debugError('NOVA Cards - Error parsing grid config on selection:', e);
										return;
									}
								}
								self.applyGridConfig($editorWidget, gridConfig);
							}
						}, 150);
					});
				}

				// Set up a periodic check in editor to ensure grid is always applied
				// This is more reliable than MutationObserver for Elementor editor
				if (!window.NOVACardsGridChecker && self.isElementorEditor()) {
					window.NOVACardsGridChecker = setInterval(function () {
						if (self.isElementorEditor()) {
							$('.nova-cards-widget').each(function () {
								const $widget = $(this);
								const $grid = $widget.find('.nova-cards-grid');
								if ($grid.length > 0) {
									const hasStyle = $grid[0].hasAttribute('style');
									const styleValue = $grid[0].getAttribute('style') || '';
									const hasGridColumns = styleValue.includes('grid-template-columns');
									const hasDataAttr = $grid.attr('data-grid-columns');

									// Always reapply if style or data attribute is missing
									if (!hasStyle || !hasGridColumns || !hasDataAttr) {
										// Grid style missing, reapply
										const gridConfigData = $widget.data('grid-config') || {};
										let gridConfig = gridConfigData;
										if (typeof gridConfigData === 'string') {
											try {
												const decoded = gridConfigData.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
												gridConfig = JSON.parse(decoded);
											} catch (e) {
												return;
											}
										}
										self.applyGridConfig($widget, gridConfig);
									}
								}
							});
						} else {
							// Stop checking when not in editor
							if (window.NOVACardsGridChecker) {
								clearInterval(window.NOVACardsGridChecker);
								window.NOVACardsGridChecker = null;
							}
						}
					}, 200); // Check every 200ms in editor for faster response
				}
			}
		},

		/**
		 * Initialiser les filtres
		 */
		initFilters: function ($widget) {
			const $filtersContainer = $widget.find('.nova-cards-filters');
			const $filters = $widget.find('.nova-cards-filter-item');
			const hideFilterAllMobile = $widget.data('hide-filter-all-mobile') === 'yes';

			if ($filtersContainer.length === 0 || $filters.length === 0) {
				return;
			}

			const self = this;

			// Appliquer un filtre
			const applyFilter = function ($filter) {
				const filterValue = $filter.attr('data-filter') || '*';

				// Mettre à jour l'état actif des boutons
				$widget.find('.nova-cards-filter-item').removeClass('active');
				$filter.addClass('active');

				// Requêter les items en temps réel (robuste après re-render)
				const $currentItems = $widget.find('.nova-card-item');

				if (filterValue === '*') {
					// Tout afficher
					$currentItems.each(function () {
						const $item = $(this);
						$item.removeClass('nova-filter-hidden').css('display', '');
					});
				} else {
					// Filtrer par classe CSS (data-filter = ".slug")
					const filterClass = filterValue.replace(/^\./, '');
					$currentItems.each(function () {
						const $item = $(this);
						if ($item.hasClass(filterClass)) {
							$item.removeClass('nova-filter-hidden').css('display', '');
						} else {
							$item.addClass('nova-filter-hidden').css('display', 'none');
						}
					});
				}

				// Réappliquer la grille après filtrage
				setTimeout(function () {
					let gridConfig = $widget.data('grid-config') || {};
					if (typeof gridConfig === 'string') {
						try {
							const decoded = gridConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
							gridConfig = JSON.parse(decoded);
						} catch (e) {
							gridConfig = {};
						}
					}
					self.applyGridConfig($widget, gridConfig);
				}, 100);
			};

			// Gérer l'affichage du bouton "Tout" sur mobile
			const handleMobileFilterAll = function () {
				if (!hideFilterAllMobile) {
					return;
				}
				const isMobile = window.innerWidth <= 767;
				const $filterAll = $widget.find('.nova-cards-filter-item[data-filter="*"]');
				const $firstFilter = $widget.find('.nova-cards-filter-item:not([data-filter="*"])').first();

				if (isMobile) {
					$filterAll.hide();
					if ($filterAll.hasClass('active') && $firstFilter.length > 0) {
						applyFilter($firstFilter);
					}
				} else {
					$filterAll.show();
				}
			};

			handleMobileFilterAll();

			const widgetId = $widget.attr('data-id') || Math.random().toString(36).substr(2, 9);
			let resizeTimer;
			$(window).off('resize.nova-cards-filters-' + widgetId)
				.on('resize.nova-cards-filters-' + widgetId, function () {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(handleMobileFilterAll, 250);
				});

			// Event delegation depuis le widget — sans doublons même en cas de re-init
			$widget.off('click.nova-cards-filter', '.nova-cards-filter-item')
				.on('click.nova-cards-filter', '.nova-cards-filter-item', function (e) {
					e.preventDefault();
					applyFilter($(this));
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

			const $items = $grid.find('.nova-card-item');
			if ($items.length === 0) {
				return;
			}

			// Get grid column gap (horizontal gap)
			const computedStyle = window.getComputedStyle($grid[0]);
			const gap = parseFloat(computedStyle.columnGap) || parseFloat(computedStyle.gap) || 30;

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
				// Keep other styles but remove grid-column-start
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
					// Calculate the starting column to center the items
					const startColumn = Math.ceil((itemsPerRow - row.length) / 2) + 1;

					// Apply grid-column-start to the first item to position it at the center
					const firstItem = row[0];

					try {
						firstItem.$item[0].style.setProperty('grid-column-start', startColumn, 'important');
					} catch (e) {
						firstItem.$item[0].style.gridColumnStart = startColumn;
					}

					// Also set via jQuery
					firstItem.$item.css('grid-column-start', startColumn + ' !important');

					if (window.DEBUG) {
						debugLog('NOVA Cards: Centering lone items, row items:', row.length, 'itemsPerRow:', itemsPerRow, 'grid-column-start:', startColumn);
					}
				} else {
					// Reset grid-column-start for complete rows
					row.forEach(function (item) {
						item.$item[0].style.gridColumnStart = '';
						const currentStyle = item.$item[0].getAttribute('style') || '';
						if (currentStyle.includes('grid-column-start')) {
							const updatedStyle = currentStyle.replace(/grid-column-start[^;]*;?/gi, '');
							item.$item[0].setAttribute('style', updatedStyle.trim());
						}
					});
				}
			});
		},

		/**
		 * Apply SVG aspect ratio to cards with creative background
		 */
		applySvgAspectRatio: function ($widget) {
			const $cards = $widget.find('.nova-card-item[data-svg-url]');

			if ($cards.length === 0) {
				return;
			}

			$cards.each(function () {
				const $card = $(this);
				const svgUrl = $card.attr('data-svg-url');

				if (!svgUrl) {
					return;
				}

				// Create an image element to load the SVG
				const img = new Image();

				img.onload = function () {
					// Get natural dimensions
					const naturalWidth = img.naturalWidth || img.width;
					const naturalHeight = img.naturalHeight || img.height;

					if (naturalWidth > 0 && naturalHeight > 0) {
						// Calculate aspect ratio
						const aspectRatio = naturalWidth / naturalHeight;

						// Apply aspect ratio to the card
						$card.css('aspect-ratio', aspectRatio);
					}
				};

				img.onerror = function () {
					// If image fails to load, try to fetch SVG as XML to get viewBox
					fetch(svgUrl)
						.then(response => response.text())
						.then(svgText => {
							const parser = new DOMParser();
							const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
							const svgElement = svgDoc.querySelector('svg');

							if (svgElement) {
								// Try to get viewBox or width/height attributes
								const viewBox = svgElement.getAttribute('viewBox');
								let width = parseFloat(svgElement.getAttribute('width'));
								let height = parseFloat(svgElement.getAttribute('height'));

								// If viewBox exists, use it
								if (viewBox) {
									const viewBoxValues = viewBox.split(/\s+/);
									if (viewBoxValues.length >= 4) {
										const viewBoxWidth = parseFloat(viewBoxValues[2]);
										const viewBoxHeight = parseFloat(viewBoxValues[3]);
										if (viewBoxWidth > 0 && viewBoxHeight > 0) {
											width = viewBoxWidth;
											height = viewBoxHeight;
										}
									}
								}

								// Apply aspect ratio if we have valid dimensions
								if (width > 0 && height > 0) {
									const aspectRatio = width / height;
									$card.css('aspect-ratio', aspectRatio);
								}
							}
						})
						.catch(function (error) {
							if (window.DEBUG) {
								debugWarn('NOVA Cards: Failed to load SVG for aspect ratio:', svgUrl, error);
							}
						});
				};

				// Set src to trigger load
				img.src = svgUrl;
			});
		},

		/**
		 * Initialize card animations
		 */
		initAnimations: function ($widget, config) {
			const $cards = $widget.find('.nova-card-item');
			const self = this;
			let animationTimeouts = []; // Store timeouts to cancel them if needed

			if ($cards.length === 0) {
				return;
			}

			if (this.isElementorEditor()) {
				$cards.each(function () {
					const $card = $(this);
					$card.addClass('animated').css({
						opacity: '',
						transform: '',
						filter: '',
						transition: ''
					});
				});
				return;
			}

			// Get translateY from config or use default
			const translateY = config.translateY !== undefined ? parseInt(config.translateY) : 30;

			// Helper function to reset animation state for all cards
			const resetAnimation = function () {
				// Clear all pending timeouts
				animationTimeouts.forEach(function (timeout) {
					clearTimeout(timeout);
				});
				animationTimeouts = [];

				$cards.each(function () {
					const $card = $(this);
					$card.removeClass('animated');
					$card.css({
						opacity: '0',
						transform: `translateY(${translateY}px)`,
						filter: 'blur(5px)',
						transition: 'none'
					});
				});
			};

			// Set initial state (translateY is already applied in resetAnimation)
			resetAnimation();

			// Use Intersection Observer for better performance
			if ('IntersectionObserver' in window) {
				const observer = new IntersectionObserver((entries) => {
					entries.forEach((entry) => {
						if (entry.isIntersecting) {
							// Trigger animations when widget enters viewport
							self.animateCards($widget, config, animationTimeouts);
							// Unobserve after animation starts to avoid re-triggering
							observer.unobserve(entry.target);
						} else {
							// Reset animation state when leaving viewport
							resetAnimation();
						}
					});
				}, {
					threshold: 0.1,
					rootMargin: '50px',
				});

				observer.observe($widget[0]);

				// Also check immediately if widget is already visible (for widgets above the fold)
				setTimeout(() => {
					const rect = $widget[0].getBoundingClientRect();
					const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
					if (isVisible) {
						this.animateCards($widget, config, animationTimeouts);
						observer.unobserve($widget[0]);
					}
				}, 100);
			} else {
				// Fallback for browsers without Intersection Observer
				this.animateCards($widget, config, animationTimeouts);
			}
		},

		/**
		 * Animate cards (simultaneous or staggered)
		 */
		animateCards: function ($widget, config, animationTimeouts) {
			const $cards = $widget.find('.nova-card-item');
			const delay = parseInt(config.delay) || 0;
			let duration = parseInt(config.duration);
			if (!duration || duration <= 0) {
				duration = 800;
			}

			// Initialize animationTimeouts array if not provided
			if (!animationTimeouts) {
				animationTimeouts = [];
			}

			const transitionValue = `opacity ${duration}ms ease, transform ${duration}ms ease, filter ${duration}ms ease`;

			if (config.simultaneous === true || config.simultaneous === 'yes') {
				// Animate all cards at the same time
				const timeout = setTimeout(() => {
					$cards.each(function () {
						const $card = $(this);
						$card.css('transition', transitionValue);

						// Force reflow
						$card[0].offsetHeight;

						// Remove inline styles and add animated class
						$card.addClass('animated');
					});
				}, delay);
				animationTimeouts.push(timeout);
			} else {
				// Animate cards one after another (cascade)
				const stagger = parseInt(config.stagger) || 500;

				$cards.each(function (index) {
					const $card = $(this);
					const cardDelay = delay + (index * stagger);

					const timeout = setTimeout(() => {
						$card.css('transition', transitionValue);

						// Force reflow
						$card[0].offsetHeight;

						// Remove inline styles and add animated class
						$card.addClass('animated');
					}, cardDelay);
					animationTimeouts.push(timeout);
				});
			}
		},

		/**
		 * Initialize hover effects for creative background cards
		 */
		initHoverEffects: function ($widget) {
			// Parse hover config
			let hoverConfig = $widget.data('hover-config') || {};
			if (typeof hoverConfig === 'string') {
				try {
					const decoded = hoverConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'");
					hoverConfig = JSON.parse(decoded);
				} catch (e) {
					debugError('NOVA Cards - Error parsing hover config:', e);
					hoverConfig = {};
				}
			}

			// Check if hover is enabled
			if (!hoverConfig.enable || !hoverConfig.transformations || hoverConfig.transformations.length === 0) {
				return;
			}

			const self = this;
			const duration = hoverConfig.duration || 500;
			const transformations = hoverConfig.transformations || [];

			// Apply transition duration to CSS
			$widget.find('.nova-card-creative-background').css('transition', `transform ${duration}ms ease`);

			// For each card with creative background
			$widget.find('.nova-card-item.has-creative-background').each(function () {
				const $card = $(this);
				const $background = $card.find('.nova-card-creative-background');

				if ($background.length === 0) {
					return;
				}

				// Store original transform
				const originalTransform = $background.css('transform') || 'none';

				// Generate random transformation for this card
				const getRandomTransform = function () {
					const availableTransforms = [];

					if (transformations.includes('rotate')) {
						// Random rotation between -180 and 180 degrees
						const rotation = (Math.random() * 360) - 180;
						availableTransforms.push(`rotate(${rotation}deg)`);
					}

					if (transformations.includes('scaleX')) {
						// Random flip (scaleX -1 or 1)
						const scaleX = Math.random() > 0.5 ? -1 : 1;
						availableTransforms.push(`scaleX(${scaleX})`);
					}

					// Combine transforms randomly
					if (availableTransforms.length > 0) {
						// Randomly select 1 or all transforms
						if (availableTransforms.length > 1 && Math.random() > 0.5) {
							// Use all transforms
							return availableTransforms.join(' ');
						} else {
							// Use one random transform
							return availableTransforms[Math.floor(Math.random() * availableTransforms.length)];
						}
					}

					return 'none';
				};

				// Apply hover effect
				$card.on('mouseenter', function () {
					const hoverTransform = getRandomTransform();
					$background.css('transform', hoverTransform);
				});

				$card.on('mouseleave', function () {
					// Return to original transform
					$background.css('transform', originalTransform);
				});
			});
		},

		/**
		 * Initialize GSAP hover animation for background + overlay cards.
		 * Reads hoverImageScale, hoverContentGsap, hoverContentInitialPosition
		 * from data-animation-config set by PHP.
		 *
		 * HOW POSITIONING WORKS:
		 *  - .nova-card-content is position:absolute inside the card.
		 *  - We animate CSS top/bottom properties (% of CARD height) — not transforms.
		 *  - "initial position" = where content sits before hover.
		 *  - On hover  → content slides to vertical center of card.
		 *  - On leave  → content returns to its initial position.
		 */
		initBackgroundOverlayHover: function ($widget) {
			// Read animation config from PHP
			let animConfig = $widget.data('animation-config') || {};
			if (typeof animConfig === 'string') {
				try {
					animConfig = JSON.parse(animConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'"));
				} catch (e) {
					animConfig = {};
				}
			}

			const hoverImageScale = animConfig.hoverImageScale !== false; // default true
			const hoverContentGsap = animConfig.hoverContentGsap !== false; // default true
			const initialPosition = animConfig.hoverContentInitialPosition || 'bottom-center';

			// If both are disabled, nothing to do
			if (!hoverImageScale && !hoverContentGsap) {
				return;
			}

			// Only for background_overlay cards
			const $cards = $widget.find('.nova-card-item').not('.nova-card-item--image-top');
			if ($cards.length === 0) {
				return;
			}

			// ─── Position map ─────────────────────────────────────────────────────
			// Each entry defines:
			//   top/bottom  : CSS absolute position (% of card)
			//   yPercent    : GSAP yPercent (self-relative offset, e.g. -50 = visual centering)
			//   textAlign   : text alignment within the content block
			// "auto" in top/bottom means GSAP clears that property.
			//
			// On hover, content always animates to "center" = { top:'50%', bottom:'auto', yPercent:-50 }
			// ─────────────────────────────────────────────────────────────────────
			const positionMap = {
				'bottom-center': { top: 'auto', bottom: '0%', yPercent: 0, textAlign: 'center' },
				'bottom-left': { top: 'auto', bottom: '0%', yPercent: 0, textAlign: 'left' },
				'bottom-right': { top: 'auto', bottom: '0%', yPercent: 0, textAlign: 'right' },
				'center-center': { top: '50%', bottom: 'auto', yPercent: -50, textAlign: 'center' },
				'center-left': { top: '50%', bottom: 'auto', yPercent: -50, textAlign: 'left' },
				'center-right': { top: '50%', bottom: 'auto', yPercent: -50, textAlign: 'right' },
				'top-center': { top: '0%', bottom: 'auto', yPercent: 0, textAlign: 'center' },
				'top-left': { top: '0%', bottom: 'auto', yPercent: 0, textAlign: 'left' },
				'top-right': { top: '0%', bottom: 'auto', yPercent: 0, textAlign: 'right' },
			};

			const initPos = positionMap[initialPosition] || positionMap['bottom-center'];

			// Hover target: always vertical center of card
			const hoverPos = { top: '50%', bottom: 'auto', yPercent: -50 };

			const gsapAvailable = typeof gsap !== 'undefined';

			$cards.each(function () {
				const $card = $(this);
				const $bg = $card.find('.nova-card-background');
				const $overlay = $card.find('.nova-card-overlay');
				const $content = $card.find('.nova-card-content');

				// ── Image zoom (CSS class, no GSAP) ──────────────────────────────
				if (hoverImageScale && $bg.length) {
					$card.addClass('nova-hover-image-scale');
				}

				// ── Content GSAP animation ─────────────────────────────────────────
				if (!hoverContentGsap || !gsapAvailable || $overlay.length === 0 || $content.length === 0) {
					return;
				}

				// Positionner le contenu selon la config initiale (sans animation)
				// On utilise uniquement bottom/top fixes + textAlign, pas yPercent float
				const textAlignMap = {
					'bottom-center': 'center', 'bottom-left': 'left', 'bottom-right': 'right',
					'center-center': 'center', 'center-left': 'left', 'center-right': 'right',
					'top-center': 'center', 'top-left': 'left', 'top-right': 'right',
				};
				const isTopInit = initialPosition.startsWith('top');
				const isCenterInit = initialPosition.startsWith('center');
				const textAlign = textAlignMap[initialPosition] || 'center';

				// Paramètres CSS de position initiale (pas animés, juste posés une fois)
				if (isTopInit) {
					gsap.set($content[0], { top: 0, bottom: 'auto', y: 0, textAlign });
				} else if (isCenterInit) {
					gsap.set($content[0], { top: '50%', bottom: 'auto', y: '-50%', textAlign });
				} else {
					// bottom (défaut)
					gsap.set($content[0], { top: 'auto', bottom: 0, y: 0, textAlign });
				}

				// Performance hints — transform seulement, pas top/bottom
				$overlay.css({ 'will-change': 'transform', 'transform-origin': 'bottom center' });
				$content.css({ 'will-change': 'transform' });

				// ── Calcul du déplacement vers le centre au hover ─────────────────
				// On anime uniquement `y` (translateY px) — pas de changement de top/bottom
				// Ce qui évite le problème d'interpolation GSAP avec "auto"
				const getHoverY = function () {
					const cardH = $card[0].offsetHeight;
					const contentH = $content[0].offsetHeight;
					if (isTopInit) {
						// Depuis le haut : descendre jusqu'au centre
						return (cardH / 2) - (contentH / 2);
					} else if (isCenterInit) {
						// Déjà au centre : pas de déplacement
						return 0;
					} else {
						// Depuis le bas : monter jusqu'au centre
						return -((cardH / 2) - (contentH / 2));
					}
				};

				// ── Hover IN ──────────────────────────────────────────────────────
				$card.off('mouseenter.novaHover').on('mouseenter.novaHover', function () {
					gsap.to($overlay[0], {
						scaleY: 1.12,
						duration: animConfig.hoverDurationIn || 0.6,
						ease: 'power3.out',
					});
					gsap.to($content[0], {
						y: getHoverY(),
						duration: animConfig.hoverDurationIn || 0.6,
						ease: 'power3.out',
						delay: 0.05,
					});
				});

				// ── Hover OUT ─────────────────────────────────────────────────────
				$card.off('mouseleave.novaHover').on('mouseleave.novaHover', function () {
					gsap.to($overlay[0], {
						scaleY: 1,
						duration: animConfig.hoverDurationOut || 0.5,
						ease: 'power3.in',
					});
					// Retour à la position initiale (y = 0 sauf si isCenterInit)
					gsap.to($content[0], {
						y: isCenterInit ? 0 : 0,
						duration: animConfig.hoverDurationOut || 0.5,
						ease: 'power3.in',
					});
				});

			});
		}
	};

	// Initialize on load
	NOVACards.init();

	// Initialize for Elementor editor
	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction('frontend/element_ready/nova-cards.default', function ($scope) {
			const $widget = $scope.find('.nova-cards-widget');
			if ($widget.length > 0) {
				// Force reinit in editor to ensure grid is applied
				NOVACards.initInstance($widget, true);
			}
		});
	}

	// Also initialize on Elementor editor preview
	if (typeof elementor !== 'undefined' && elementor.on) {
		elementor.on('preview:loaded', function () {
			setTimeout(function () {
				$('.nova-cards-widget').each(function () {
					const $widget = $(this);
					$widget.removeData('nova-cards-initialized');
					NOVACards.initInstance($widget, true);
				});
			}, 500);
		});

		// Also listen for preview refresh
		elementor.on('preview:refresh', function () {
			setTimeout(function () {
				$('.nova-cards-widget').each(function () {
					const $widget = $(this);
					$widget.removeData('nova-cards-initialized');
					NOVACards.initInstance($widget, true);
				});
			}, 300);
		});
	}

	// Make available globally for debugging
	window.NOVACards = NOVACards;

})(jQuery);

