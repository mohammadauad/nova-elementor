/**
 * NOVA Gallery Multi Filters Widget - JavaScript
 */
(function ($) {
	'use strict';

	const NOVAMultiGallery = {
		instances: [],

		isElementorEditor: function () {
			return typeof elementor !== 'undefined' && elementor.config && elementor.config.is_rtl !== undefined;
		},

		init: function () {
			this.initInstances();
		},

		initInstances: function () {
			const $widgets = $('.nova-gallery-widget').has('.nova-gallery-multi-filters-container');
			$widgets.each(function () {
				const $widget = $(this);
				if (!$widget.data('NOVA-multi-gallery-initialized')) {
					NOVAMultiGallery.initInstance($widget);
				}
			});
		},

		initInstance: function ($widget) {
			if ($widget.data('NOVA-multi-gallery-initialized')) return;
			$widget.data('NOVA-multi-gallery-initialized', true);

			const widgetId      = $widget.attr('id') || 'no-id';
			const columns       = parseInt($widget.data('columns')) || 4;
			const columnsTablet = parseInt($widget.data('columns-tablet')) || 3;
			const columnsMobile = parseInt($widget.data('columns-mobile')) || 2;
			const enableFilters = $widget.data('enable-filters') === 'yes';
			const enablePopup   = $widget.data('enable-popup') === 'yes';
			const paginationType = $widget.data('pagination') || 'none';
			const perPage        = parseInt($widget.data('per-page')) || 6;

			console.log('[NOVA Gallery] initInstance()', { widgetId, columns, columnsTablet, columnsMobile, enableFilters, enablePopup, paginationType, perPage });

			let gridConfig = $widget.data('grid-config') || {};
			if (typeof gridConfig === 'string') {
				try { gridConfig = JSON.parse(gridConfig.replace(/&quot;/g, '"').replace(/&#39;/g, "'")); }
				catch (e) { gridConfig = {}; }
			}

			this.applyGrid($widget, columns, columnsTablet, columnsMobile, gridConfig);
			$widget.addClass('nova-js-ready');
			this.initFiltersAndPagination($widget, paginationType, perPage, enableFilters);
			this.initStickyFilters($widget);
			if (enablePopup) this.initPopup($widget);

			let resizeTimer;
			$(window).off('resize.NOVA-multi-gallery-' + widgetId).on('resize.NOVA-multi-gallery-' + widgetId, function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					NOVAMultiGallery.applyGrid($widget, columns, columnsTablet, columnsMobile, gridConfig);
				}, 250);
			});

			NOVAMultiGallery.instances.push({ widget: $widget });
		},

		applyGrid: function ($widget, columns, columnsTablet, columnsMobile, gridConfig) {
			const $grid = $widget.find('.nova-gallery-grid');
			if (!$grid.length) return;

			const windowWidth = $(window).width();
			const gridMode    = gridConfig.mode || 'auto';
			let gridTemplateColumns = '';
			let columnWidth = 300;

			if (gridMode === 'pixels') {
				columnWidth = parseInt(gridConfig.column_width) || 300;
				if (!this.isElementorEditor()) {
					if (windowWidth < 768)       columnWidth = parseInt(gridConfig.column_width_mobile) || columnWidth;
					else if (windowWidth < 1024) columnWidth = parseInt(gridConfig.column_width_tablet) || columnWidth;
				}
				if (isNaN(columnWidth) || columnWidth < 100) columnWidth = 300;
				const autoCenter = gridConfig.auto_center === true || gridConfig.auto_center === 'yes' || gridConfig.auto_center === 1;
				gridTemplateColumns = autoCenter
					? 'repeat(auto-fill, minmax(' + columnWidth + 'px, ' + columnWidth + 'px))'
					: 'repeat(auto-fit, minmax(' + columnWidth + 'px, 1fr))';
			} else {
				let cols = columns;
				if (!this.isElementorEditor()) {
					if (windowWidth < 768)       cols = columnsMobile;
					else if (windowWidth < 1024) cols = columnsTablet;
				}
				if (isNaN(cols) || cols < 1) cols = 4;
				gridTemplateColumns = 'repeat(' + cols + ', 1fr)';
			}

			try { $grid[0].style.setProperty('grid-template-columns', gridTemplateColumns, 'important'); }
			catch (e) { $grid[0].style.gridTemplateColumns = gridTemplateColumns; }
		},

		initFiltersAndPagination: function ($widget, paginationType, perPage, enableFilters) {
			const $filterGroups       = $widget.find('.nova-gallery-filter-group');
			const $items              = $widget.find('.nova-gallery-item');
			const hideFilterAllMobile = $widget.data('hide-filter-all-mobile') === 'yes';

			console.log('[NOVA Gallery] initFiltersAndPagination()', {
				filterGroups: $filterGroups.length, items: $items.length, paginationType, perPage, enableFilters
			});

			if (!$items.length) return;

			// ── Init Owl / scroll sliders ──────────────────────────────────────
			if (enableFilters) {
				$filterGroups.find('.nova-gallery-filters-slider').each(function (sliderIndex) {
					const $slider = $(this);

					if (typeof $.fn.owlCarousel === 'undefined') return;

					const breakpoint = ($slider.data('owl-breakpoint') || 'all').toString();

					function shouldBeActive() {
						const w = window.innerWidth || $(window).width();
						if (breakpoint === 'mobile')        return w <= 767;
						if (breakpoint === 'tablet')        return w <= 1024;
						if (breakpoint === 'tablet_mobile') return w <= 1024;
						if (breakpoint === 'desktop')       return w > 1024;
						return true;
					}

					function cleanupOwl() {
						if ($slider.hasClass('owl-loaded')) {
							$slider.trigger('destroy.owl.carousel');
							$slider.removeClass('owl-loaded owl-drag owl-grab');
						}
						$slider.find('.owl-stage-outer').children().unwrap();
						$slider.find('.owl-stage').children().unwrap();
						$slider.find('.owl-item').children().unwrap();
						$slider.find('.nova-gallery-filter-wrap').each(function () {
							if ($(this).parent()[0] !== $slider[0]) $(this).appendTo($slider);
						});
						$slider.find('.owl-nav, .owl-dots').remove();
						$slider.children(':not(.nova-gallery-filter-wrap)').each(function () {
							if (!$(this).find('.nova-gallery-filter-wrap').length) $(this).remove();
						});
					}

					function initOwl() {
						cleanupOwl();
						$slider.removeClass('nova-scroll-native').css({
							display: '', 'flex-wrap': '', 'overflow-x': '', 'overflow-y': '',
							'-webkit-overflow-scrolling': '', 'scroll-behavior': '', gap: '', cursor: ''
						});

						const w        = window.innerWidth || $(window).width();
						const isMobile = w <= 767;
						const isTablet = w > 767 && w <= 1024;

						const mgnD = parseInt($slider.data('owl-margin'), 10)        || 10;
						const mgnT = parseInt($slider.data('owl-margin-tablet'), 10) || 0;
						const mgnM = parseInt($slider.data('owl-margin-mobile'), 10) || 0;
						const margin = isMobile ? (mgnM || mgnT || mgnD) : isTablet ? (mgnT || mgnD) : mgnD;

						const itmD = parseInt($slider.data('owl-items'), 10)        || 0;
						const itmT = parseInt($slider.data('owl-items-tablet'), 10) || 0;
						const itmM = parseInt($slider.data('owl-items-mobile'), 10) || 0;
						const items = isMobile ? (itmM || itmT || itmD) : isTablet ? (itmT || itmD) : itmD;

						const mwD = parseInt($slider.data('filter-item-min-width'), 10)        || 0;
						const mwT = parseInt($slider.data('filter-item-min-width-tablet'), 10) || 0;
						const mwM = parseInt($slider.data('filter-item-min-width-mobile'), 10) || 0;
						const itemMinW = isMobile ? (mwM || mwT || mwD) : isTablet ? (mwT || mwD) : mwD;

						const loopD = ($slider.data('owl-loop') || '').toString() === 'yes';
						const loopT = ($slider.data('owl-loop-tablet') || '').toString() === 'yes';
						const loopM = ($slider.data('owl-loop-mobile') || '').toString() === 'yes';
						const loop      = isMobile ? loopM : isTablet ? loopT : loopD;
						const autoWidth = ($slider.data('owl-autowidth') || '').toString() !== 'no';
						const freeDrag  = ($slider.data('owl-freedrag') || '').toString() === 'yes';
						const disableDragBp = ($slider.data('owl-disabledrag') || 'none').toString();

						const dragDisabled = disableDragBp === 'all'
							|| (disableDragBp === 'desktop'        && w > 1024)
							|| (disableDragBp === 'tablet'         && w > 767 && w <= 1024)
							|| (disableDragBp === 'mobile'         && w <= 767)
							|| (disableDragBp === 'tablet_desktop' && w > 767)
							|| (disableDragBp === 'mobile_tablet'  && w <= 1024);

						if (itemMinW > 0) $slider.find('.nova-gallery-filter-wrap').css('min-width', itemMinW + 'px');
						else              $slider.find('.nova-gallery-filter-wrap').css('min-width', '');

						const totalItems = $slider.find('.nova-gallery-filter-wrap').length;
						const $group   = $slider.closest('.nova-gallery-filter-group');
						const $prevBtn = $group.find('.nova-gallery-owl-prev');
						const $nextBtn = $group.find('.nova-gallery-owl-next');

						// ── Mode scroll CSS natif: autoWidth + pas de loop + pas d'items fixe ──
						if (items === 0 && autoWidth && !loop) {
							console.log('🔵 [Slider #' + sliderIndex + '] MODE: scroll natif', { items, autoWidth, loop, margin, dragDisabled, totalItems });
							$slider.addClass('nova-scroll-native').css({
								display: 'flex', 'flex-wrap': 'nowrap',
								'overflow-x': dragDisabled ? 'hidden' : 'auto', 'overflow-y': 'hidden',
								'-webkit-overflow-scrolling': 'touch', 'scroll-behavior': 'smooth',
								gap: margin + 'px', cursor: dragDisabled ? '' : 'grab'
							});
							$slider.find('.nova-gallery-filter-wrap').css('flex-shrink', '0');

							if (!dragDisabled) {
								var isDragging = false, startX = 0, startSL = 0;
								var el = $slider[0];
								el.addEventListener('mousedown', function (e) {
									isDragging = true; startX = e.clientX; startSL = el.scrollLeft;
									$slider.css({ cursor: 'grabbing', 'user-select': 'none' });
									e.preventDefault();
								});
								document.addEventListener('mouseup', function () {
									if (!isDragging) return;
									isDragging = false;
									$slider.css({ cursor: 'grab', 'user-select': '' });
								});
								document.addEventListener('mousemove', function (e) {
									if (!isDragging) return;
									el.scrollLeft = startSL - (e.clientX - startX);
								});
								el.addEventListener('touchstart', function (e) {
									startX = e.touches[0].clientX; startSL = el.scrollLeft;
								}, { passive: true });
								el.addEventListener('touchmove', function (e) {
									el.scrollLeft = startSL - (e.touches[0].clientX - startX);
								}, { passive: true });
							}

							var scrollStep = $slider.width() * 0.6;
							if ($prevBtn.length) {
								$prevBtn.off('click.owlnav').on('click.owlnav', function () { $slider[0].scrollLeft -= scrollStep; });
								$prevBtn.css('display', '');
							}
							if ($nextBtn.length) {
								$nextBtn.off('click.owlnav').on('click.owlnav', function () { $slider[0].scrollLeft += scrollStep; });
								$nextBtn.css('display', '');
							}
							return;
						}

						// ── Mode Owl Carousel ──
						var $first = $slider.find('.nova-gallery-filter-wrap').first();
						var avgW   = $first.length ? ($first.outerWidth(true) || 120) : 120;
						var vis    = Math.max(1, Math.floor(($slider.width() || 300) / (avgW + margin)));
						if (vis >= totalItems && totalItems > 1) vis = totalItems - 1;
						// loop=true: limiter items à max 50% du total pour avoir assez de clones
						if (loop && totalItems > 2 && vis > Math.floor(totalItems / 2)) {
							vis = Math.max(1, Math.floor(totalItems / 2));
						}

						const effectiveFreeDrag = !dragDisabled && (freeDrag || autoWidth);
						// loop=true + autoWidth=true: Owl ne peut pas cloner → forcer autoWidth=false
						const useAutoWidth  = items === 0 && autoWidth && !loop;
						const computedItems = items > 0 ? items : vis;

						console.log('🟠 [Slider #' + sliderIndex + '] MODE: Owl', {
							items, autoWidth, loop, margin, dragDisabled, totalItems,
							vis, computedItems, effectiveFreeDrag, useAutoWidth
						});

						const owlOptions = {
							items:      computedItems,
							autoWidth:  useAutoWidth,
							slideBy:    1,
							margin,
							loop,
							rewind:     !loop,
							freeDrag:   effectiveFreeDrag,
							mouseDrag:  !dragDisabled,
							touchDrag:  !dragDisabled,
							pullDrag:   !dragDisabled,
							smartSpeed: 300,
							dots:       false,
							nav:        false,
							stagePadding: 0,
						};

						try {
							$slider.owlCarousel(owlOptions);
							if (useAutoWidth) $slider.addClass('owl-autowidth');
							else              $slider.removeClass('owl-autowidth');
						} catch (e) {
							console.error('[NOVA Gallery] Slider #' + sliderIndex + ' Owl init failed:', e);
						}

						if ($prevBtn.length) {
							$prevBtn.off('click.owlnav').on('click.owlnav', function () { $slider.trigger('prev.owl.carousel'); });
							$prevBtn.css('display', '');
						}
						if ($nextBtn.length) {
							$nextBtn.off('click.owlnav').on('click.owlnav', function () { $slider.trigger('next.owl.carousel'); });
							$nextBtn.css('display', '');
						}
					}

					function disableOwl() {
						cleanupOwl();
						$slider.find('.nova-gallery-filter-wrap').css('min-width', '');
						$slider.closest('.nova-gallery-filter-group').find('.nova-gallery-owl-prev, .nova-gallery-owl-next').css('display', 'none');
					}

					if (shouldBeActive()) initOwl(); else disableOwl();

					let resizeTimer;
					let wasActive = shouldBeActive();
					$(window).on('resize.owlslider' + sliderIndex, function () {
						clearTimeout(resizeTimer);
						resizeTimer = setTimeout(function () {
							const active = shouldBeActive();
							if (active && !wasActive)  { initOwl();   wasActive = true; }
							else if (!active && wasActive) { disableOwl(); wasActive = false; }
						}, 200);
					});
				});
			}

			// ── Pagination + Filter logic ──────────────────────────────────────
			const $pagination = $widget.find('.nova-gallery-pagination');
			let currentPage     = 1;
			let currentSelector = '*';

			const renderItems = function () {
				const $matched   = currentSelector === '*' ? $items : $items.filter(currentSelector);
				const $unmatched = currentSelector === '*' ? $() : $items.not(currentSelector);

				$unmatched.addClass('hidden').css('display', 'none').removeClass('pagination-hidden');

				if (paginationType !== 'none') {
					const total      = $matched.length;
					const totalPages = Math.ceil(total / perPage);

					$matched.each(function (i) {
						if (i < currentPage * perPage) {
							$(this).removeClass('hidden pagination-hidden').css('display', '');
						} else {
							$(this).removeClass('hidden').addClass('pagination-hidden').css('display', 'none');
						}
					});

					if (paginationType === 'load_more') {
						const $btn = $pagination.find('.nova-gallery-load-more');
						currentPage * perPage >= total ? $btn.hide() : $btn.show();
					} else {
						let html = '';
						for (let p = 1; p <= totalPages; p++) {
							html += '<button class="nova-gallery-page-btn' + (p === currentPage ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
						}
						$pagination.find('.nova-gallery-pages').html(html);
						totalPages <= 1 ? $pagination.hide() : $pagination.show();
					}
				} else {
					$matched.removeClass('hidden pagination-hidden').css('display', '');
				}

				const cols  = parseInt($widget.data('columns')) || 4;
				const colsT = parseInt($widget.data('columns-tablet')) || 3;
				const colsM = parseInt($widget.data('columns-mobile')) || 2;
				let gc = $widget.data('grid-config') || {};
				if (typeof gc === 'string') {
					try { gc = JSON.parse(gc.replace(/&quot;/g, '"').replace(/&#39;/g, "'")); } catch (e) { gc = {}; }
				}
				NOVAMultiGallery.applyGrid($widget, cols, colsT, colsM, gc);
				$widget.trigger('filterChanged.nova-gallery');
			};

			$pagination.on('click', '.nova-gallery-load-more', function () {
				currentPage++;
				renderItems();
			});

			$pagination.on('click', '.nova-gallery-page-btn', function () {
				currentPage = parseInt($(this).data('page'));
				renderItems();
				$('html, body').animate({ scrollTop: $widget.offset().top - 80 }, 300);
			});

			// ── Multi-filter logic ─────────────────────────────────────────────
			const applyMultiFilters = function () {
				let combinedSelector = '';
				$filterGroups.each(function () {
					const filterValue = $(this).find('.nova-gallery-filter-item.active').data('filter');
					if (filterValue && filterValue !== '*') combinedSelector += filterValue;
				});
				currentSelector = combinedSelector === '' ? '*' : combinedSelector;
				currentPage     = 1;
				renderItems();
			};

			renderItems();

			if (enableFilters) {
				const handleMobileFilterAll = function () {
					if (!hideFilterAllMobile) return;
					const isMobile = window.innerWidth <= 767;
					$filterGroups.each(function () {
						const $group       = $(this);
						const $filterAll   = $group.find('[data-filter="*"]');
						const $firstNonAll = $group.find('.nova-gallery-filter-item').not('[data-filter="*"]').first();
						if (isMobile) {
							$filterAll.closest('.nova-gallery-filter-wrap').hide();
							if ($filterAll.hasClass('active') && $firstNonAll.length) {
								$filterAll.removeClass('active');
								$firstNonAll.addClass('active');
							}
						} else {
							$filterAll.closest('.nova-gallery-filter-wrap').show();
						}
					});
					if (isMobile) applyMultiFilters();
				};

				handleMobileFilterAll();
				let resizeTimer;
				$(window).on('resize', function () {
					clearTimeout(resizeTimer);
					resizeTimer = setTimeout(handleMobileFilterAll, 250);
				});

				$widget.on('click', '.nova-gallery-filter-item', function (e) {
					e.preventDefault();
					const $filter     = $(this);
					const filterValue = $filter.data('filter');
					const $group      = $filter.closest('.nova-gallery-filter-group');
					$group.find('.nova-gallery-filter-item').removeClass('active');
					$group.find('.nova-gallery-filter-item[data-filter="' + filterValue + '"]').addClass('active');
					applyMultiFilters();
				});
			}
		},

		initStickyFilters: function ($widget) {
			var $filters = $widget.find('.nova-filters-sticky');
			if (!$filters.length) return;

			var isSticky = function () {
				var w = window.innerWidth;
				if (w <= 767)  return $filters.data('sticky-mobile') === 'yes';
				if (w <= 1024) return $filters.data('sticky-tablet') === 'yes';
				return $filters.data('sticky') === 'yes';
			};

			var getOffset = function () {
				var w = window.innerWidth;
				if (w <= 767)  return parseInt($filters.data('sticky-offset-mobile')) || parseInt($filters.data('sticky-offset')) || 0;
				if (w <= 1024) return parseInt($filters.data('sticky-offset-tablet')) || parseInt($filters.data('sticky-offset')) || 0;
				return parseInt($filters.data('sticky-offset')) || 0;
			};

			if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
				var applyCSS = function () {
					if (isSticky()) {
						$filters[0].style.position = 'sticky';
						$filters[0].style.top      = getOffset() + 'px';
						$filters[0].style.zIndex   = '100';
					} else {
						$filters[0].style.position = '';
						$filters[0].style.top      = '';
					}
				};
				applyCSS();
				window.addEventListener('resize', applyCSS);
				return;
			}

			var st = ScrollTrigger.create({
				trigger:     $filters[0],
				start:       function () { return isSticky() ? 'top top+=' + getOffset() : 'top top+=99999'; },
				endTrigger:  $widget[0],
				end:         function () { return isSticky() ? 'bottom top+=' + getOffset() : 'bottom top+=99999'; },
				pin:         isSticky(),
				pinSpacing:  false,
				onRefresh:   function (self) { self.pin = isSticky(); },
			});

			window.addEventListener('resize', function () { if (st) st.refresh(); });
		},

		initPopup: function ($widget) {
			const $popup = $('body').find('.nova-gallery-popup').first();
			if (!$popup.length) return;

			const $items = $widget.find('.nova-gallery-item[data-popup-image]');
			if (!$items.length) return;

			const $popupImage   = $popup.find('.nova-gallery-popup-image');
			const $popupTitle   = $popup.find('.nova-gallery-popup-title');
			const $popupCounter = $popup.find('.nova-gallery-popup-counter');
			const $popupClose   = $popup.find('.nova-gallery-popup-close');
			const $popupPrev    = $popup.find('.nova-gallery-popup-prev');
			const $popupNext    = $popup.find('.nova-gallery-popup-next');
			const $popupOverlay = $popup.find('.nova-gallery-popup-overlay');

			let currentIndex = 0, images = [];

			function updateImagesList() {
				images = [];
				$widget.find('.nova-gallery-item[data-popup-image]').filter(':visible').each(function () {
					const url = $(this).data('popup-image');
					if (url) images.push({ url, title: $(this).data('popup-title') || '', $item: $(this) });
				});
			}

			function openPopup(index) {
				if (!images.length) return;
				currentIndex = Math.max(0, Math.min(index, images.length - 1));
				const img = images[currentIndex];
				$popupImage.attr('src', img.url).attr('alt', img.title);
				$popupTitle.text(img.title);
				$popupCounter.text((currentIndex + 1) + ' / ' + images.length);
				images.length <= 1 ? ($popupPrev.hide(), $popupNext.hide()) : ($popupPrev.show(), $popupNext.show());
				$popup.fadeIn(300);
				$('body').css('overflow', 'hidden');
			}

			function closePopup() { $popup.fadeOut(300); $('body').css('overflow', ''); }
			function nextImage()  { if (images.length) openPopup((currentIndex + 1) % images.length); }
			function prevImage()  { if (images.length) openPopup((currentIndex - 1 + images.length) % images.length); }

			$widget.on('click', '.nova-gallery-item[data-popup-image]', function (e) {
				e.preventDefault(); e.stopPropagation();
				updateImagesList();
				if (!images.length) return;
				const $clicked = $(this);
				let idx = -1;
				images.forEach(function (img, i) { if (img.$item && img.$item[0] === $clicked[0]) idx = i; });
				openPopup(idx >= 0 ? idx : 0);
			});

			$popupClose.on('click',   function (e) { e.preventDefault(); e.stopPropagation(); closePopup(); });
			$popupOverlay.on('click', function (e) { e.preventDefault(); e.stopPropagation(); closePopup(); });
			$popupNext.on('click',    function (e) { e.preventDefault(); e.stopPropagation(); nextImage(); });
			$popupPrev.on('click',    function (e) { e.preventDefault(); e.stopPropagation(); prevImage(); });

			$(document).on('keydown.nova-gallery-popup', function (e) {
				if ($popup.is(':visible')) {
					if (e.key === 'Escape')          closePopup();
					else if (e.key === 'ArrowRight') nextImage();
					else if (e.key === 'ArrowLeft')  prevImage();
				}
			});

			if ($widget.data('enable-filters') === 'yes') {
				$widget.on('filterChanged.nova-gallery', function () { setTimeout(updateImagesList, 100); });
			}
		}
	};

	$(document).ready(function () {
		console.log('[NOVA Gallery] document.ready — jQuery:', typeof $, '| owlCarousel:', typeof $.fn.owlCarousel);
		NOVAMultiGallery.init();
	});

	if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
		elementorFrontend.hooks.addAction('frontend/element_ready/nova-gallery-multi-filters.default', function ($scope) {
			setTimeout(function () {
				$scope.find('.nova-gallery-widget').has('.nova-gallery-multi-filters-container').each(function () {
					const $widget = $(this);
					if (!$widget.data('NOVA-multi-gallery-initialized')) NOVAMultiGallery.initInstance($widget);
				});
			}, 100);
		});
	}

	window.NOVAMultiGallery = NOVAMultiGallery;

})(jQuery);
