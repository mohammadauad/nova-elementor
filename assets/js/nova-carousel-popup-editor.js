/**
 * NOVA Carousel — Popup Editor Helper
 *
 * Cache / montre les champs "Contenu Popup" dans le repeater
 * selon l'état du toggle global `popup_enable`.
 *
 * Stratégie :
 * - CSS injecté dans l'éditeur cache les champs par défaut
 * - Quand popup_enable = yes, une classe sur le body les révèle
 * - MutationObserver ré-applique à chaque rendu du panneau
 */
(function () {
	'use strict';

	var WIDGET_TYPES = ['nova-carousel-swiper', 'nova-carousel'];

	var BODY_CLASS_ENABLED = 'nova-popup-fields-enabled';

	var CSS_ID = 'nova-popup-editor-css';

	// CSS : cache par défaut, révèle quand body a la classe
	var CSS_RULES = [
		/* Cacher par défaut */
		'.elementor-control-item_popup_content_heading,',
		'.elementor-control-item_popup_media_type,',
		'.elementor-control-item_popup_image,',
		'.elementor-control-item_popup_video_type,',
		'.elementor-control-item_popup_video_url,',
		'.elementor-control-item_popup_video_hosted,',
		'.elementor-control-item_popup_video_autoplay,',
		'.elementor-control-item_popup_video_mute,',
		'.elementor-control-item_popup_video_loop,',
		'.elementor-control-item_popup_text1,',
		'.elementor-control-item_popup_text2,',
		'.elementor-control-item_popup_link,',
		'.elementor-control-item_popup_link_text,',
		'.elementor-control-item_popup_btn_icon,',
		'.elementor-control-item_popup_btn_icon_position',
		'{ display: none !important; }',
		/* Révéler quand popup activé */
		'body.nova-popup-fields-enabled .elementor-control-item_popup_content_heading,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_media_type,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_image,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_video_type,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_video_url,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_video_hosted,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_video_autoplay,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_video_mute,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_video_loop,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_text1,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_text2,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_link,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_link_text,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_btn_icon,',
		'body.nova-popup-fields-enabled .elementor-control-item_popup_btn_icon_position',
		'{ display: block !important; }',
	].join('\n');

	var currentModel = null;
	var popupEnabled = false;
	var observer     = null;
	var hooksReady   = false;

	// ── CSS injection ─────────────────────────────────────────────────────

	function injectCSS() {
		if (document.getElementById(CSS_ID)) return;
		var style       = document.createElement('style');
		style.id        = CSS_ID;
		style.type      = 'text/css';
		style.textContent = CSS_RULES;
		document.head.appendChild(style);
	}

	// ── Body class ────────────────────────────────────────────────────────

	function applyBodyClass(enabled) {
		if (enabled) {
			document.body.classList.add(BODY_CLASS_ENABLED);
		} else {
			document.body.classList.remove(BODY_CLASS_ENABLED);
		}
	}

	// ── Helpers ───────────────────────────────────────────────────────────

	function isNovaCarousel() {
		if (!currentModel) return false;
		return WIDGET_TYPES.indexOf(currentModel.get('widgetType')) !== -1;
	}

	function readPopupEnabled() {
		if (!currentModel) return false;
		var settings = currentModel.get('settings');
		if (!settings) return false;
		return settings.get('popup_enable') === 'yes';
	}

	// ── MutationObserver ──────────────────────────────────────────────────

	function startObserver() {
		if (observer) return;
		var panel = document.getElementById('elementor-panel-content-wrapper');
		if (!panel) return;

		observer = new MutationObserver(function () {
			if (isNovaCarousel()) {
				applyBodyClass(popupEnabled);
			}
		});

		observer.observe(panel, { childList: true, subtree: true });
	}

	function stopObserver() {
		if (observer) {
			observer.disconnect();
			observer = null;
		}
	}

	// ── Gestion du modèle ─────────────────────────────────────────────────

	function bindModel(model) {
		currentModel = model;

		if (!isNovaCarousel()) {
			stopObserver();
			applyBodyClass(false);
			return;
		}

		// Lire l'état initial
		popupEnabled = readPopupEnabled();
		applyBodyClass(popupEnabled);

		// Écouter les changements du toggle en temps réel
		var settings = model.get('settings');
		if (settings) {
			settings.off('change:popup_enable.nova-popup');
			settings.on('change:popup_enable.nova-popup', function () {
				popupEnabled = readPopupEnabled();
				applyBodyClass(popupEnabled);
			});
		}

		startObserver();
	}

	// ── Hooks Elementor ───────────────────────────────────────────────────

	function setupHooks() {
		if (hooksReady) return;
		if (typeof elementor === 'undefined' || !elementor.hooks) return;

		hooksReady = true;

		// Widget ouvert dans le panneau
		elementor.hooks.addAction('panel/open_editor/widget', function (panel, model) {
			bindModel(model);
		});

		// Panneau fermé
		elementor.hooks.addAction('panel/close_editor', function () {
			currentModel = null;
			stopObserver();
			applyBodyClass(false);
		});

		// Fallback : écouter aussi les changements via l'API Backbone d'Elementor
		// pour les cas où le hook panel/open_editor/widget ne se déclenche pas
		if (elementor.channels && elementor.channels.editor) {
			elementor.channels.editor.on('change', function (view) {
				if (view && view.model) {
					var type = view.model.get('widgetType');
					if (type && WIDGET_TYPES.indexOf(type) !== -1 && view.model !== currentModel) {
						bindModel(view.model);
					}
				}
			});
		}
	}

	// ── Polling fallback ──────────────────────────────────────────────────
	// Elementor peut être lent à s'initialiser — on réessaie jusqu'à ce que
	// les hooks soient en place.

	var attempts = 0;

	function trySetup() {
		attempts++;
		injectCSS();
		setupHooks();

		if (!hooksReady && attempts < 30) {
			setTimeout(trySetup, 300);
		}
	}

	// ── Init ──────────────────────────────────────────────────────────────

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', trySetup);
	} else {
		trySetup();
	}

	window.addEventListener('load', function () {
		setTimeout(trySetup, 200);
		setTimeout(trySetup, 1000);
	});

}());
