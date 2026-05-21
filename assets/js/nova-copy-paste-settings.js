/**
 * NOVA Addons – Copy / Paste Settings
 *
 * Approach: MutationObserver on #elementor-panel-inner to detect when
 * the panel switches to an element editor page, then inject the toolbar.
 *
 * This bypasses unreliable hook timing issues entirely.
 */
(function (jq) {
    'use strict';

    /* ─── Constants ──────────────────────────────────────────────────────── */
    var STORAGE_KEY_PREFIX = 'nova_cp_v3_';
    var TOOLBAR_ID         = 'nova-cp-toolbar';
    var MODAL_ID           = 'nova-cp-modal';
    var STRUCTURAL_TYPES   = ['container', 'section', 'column'];
    var SKIP_KEYS          = ['_id', '_column_size', '__dynamic__', '__globals__', 'isInner'];

    /* ─── State ──────────────────────────────────────────────────────────── */
    var _currentModel   = null;
    var _injectTimer    = null;
    var _observer       = null;
    var _hooksInstalled = false;

    /* ─── Expose for debug ───────────────────────────────────────────────── */
    window.novaCP = {
        getModel:    function () { return _currentModel; },
        inject:      function () { injectToolbar(_currentModel); },
        forceDetect: function () { detectAndInject(); },
    };

    /* ─── Model helpers ──────────────────────────────────────────────────── */
    function getElementId(model) {
        if (!model) return '';
        var elType = model.get('elType') || '';
        if (STRUCTURAL_TYPES.indexOf(elType) !== -1) return elType;
        return model.get('widgetType') || elType;
    }

    function isSupported(model) {
        if (!model) return false;
        var elType     = model.get('elType') || '';
        var widgetType = model.get('widgetType') || '';
        return STRUCTURAL_TYPES.indexOf(elType) !== -1 ||
               (widgetType && widgetType.indexOf('nova-') === 0);
    }

    /* ─── Get current model from Elementor state ─────────────────────────── */
    function getCurrentModel() {
        try {
            // Method 1: via panel view
            var panel = elementor.getPanelView && elementor.getPanelView();
            if (panel) {
                var page = panel.getCurrentPageView && panel.getCurrentPageView();
                if (page && page.model && page.model.get('elType')) {
                    return page.model;
                }
            }
        } catch (e) {}

        try {
            // Method 2: via selection
            var selected = elementor.selection && elementor.selection.getElements && elementor.selection.getElements();
            if (selected && selected.length) {
                return selected[0];
            }
        } catch (e) {}

        try {
            // Method 3: via channels
            var edited = elementor.channels && elementor.channels.editor &&
                         elementor.channels.editor.request('editor:editedElement');
            if (edited) return edited;
        } catch (e) {}

        return null;
    }

    /* ─── Detect panel state from DOM ────────────────────────────────────── */
    /**
     * Returns true if the panel is currently showing an element editor
     * (not the widget list, not the page settings).
     */
    function isPanelInEditorMode() {
        // When editing an element, Elementor shows a page with tabs (Content/Style/Advanced)
        // The navigation tabs are the clearest indicator
        return jq('.elementor-panel-navigation').length > 0 &&
               jq('.elementor-panel-navigation-tab').length > 0;
    }

    /* ─── Clipboard ──────────────────────────────────────────────────────── */
    function storageKey(id) { return STORAGE_KEY_PREFIX + id; }

    function readClipboard(id) {
        try { return JSON.parse(localStorage.getItem(storageKey(id))); }
        catch (e) { return null; }
    }

    function writeClipboard(id, payload) {
        localStorage.setItem(storageKey(id), JSON.stringify(payload));
    }

    /* ─── Controls / tabs ────────────────────────────────────────────────── */
    function getControlsByTab(model) {
        var byTab      = {};
        var widgetType = model.get('widgetType') || '';
        var controls   = {};

        if (widgetType && elementor.widgetsCache && elementor.widgetsCache[widgetType]) {
            controls = elementor.widgetsCache[widgetType].controls || {};
        }
        if (!Object.keys(controls).length) {
            try {
                var sm = model.get('settings');
                if (sm && sm.controls) controls = sm.controls;
            } catch (e) {}
        }

        Object.keys(controls).forEach(function (key) {
            var tab = ((controls[key].tab) || 'content').toLowerCase();
            if (!byTab[tab]) byTab[tab] = {};
            byTab[tab][key] = controls[key];
        });
        return byTab;
    }

    function getAvailableTabs(model) {
        var byTab = getControlsByTab(model);
        var tabs  = Object.keys(byTab);
        ['style', 'advanced'].forEach(function (t) {
            if (tabs.indexOf(t) === -1) tabs.push(t);
        });
        var order = ['content', 'layout', 'style', 'advanced'];
        tabs.sort(function (a, b) {
            return (order.indexOf(a) === -1 ? 99 : order.indexOf(a)) -
                   (order.indexOf(b) === -1 ? 99 : order.indexOf(b));
        });
        return tabs;
    }

    function collectSettings(model, tabs) {
        var allSettings = model.get('settings').toJSON();
        var byTab       = getControlsByTab(model);
        if (!Object.keys(byTab).length) return filterInternalKeys(allSettings);

        var out = {};
        tabs.forEach(function (tab) {
            Object.keys(byTab[tab] || {}).forEach(function (key) {
                if (key in allSettings && SKIP_KEYS.indexOf(key) === -1) {
                    out[key] = allSettings[key];
                }
            });
        });
        return Object.keys(out).length ? out : filterInternalKeys(allSettings);
    }

    function filterInternalKeys(s) {
        var out = {};
        Object.keys(s).forEach(function (k) {
            if (SKIP_KEYS.indexOf(k) === -1) out[k] = s[k];
        });
        return out;
    }

    /* ─── Apply settings ─────────────────────────────────────────────────── */
    function applySettings(model, settings) {
        var sm = model.get('settings');
        Object.keys(settings).forEach(function (k) { sm.set(k, settings[k]); });
        sm.trigger('change');
        model.trigger('change');
        try { elementor.saver.setFlagEditorChange(true); } catch (e) {}
    }

    /* ─── Button styles ──────────────────────────────────────────────────── */
    function elBtnStyle(variant) {
        var base = {
            display:        'inline-flex',
            alignItems:     'center',
            justifyContent: 'center',
            height:         '28px',
            padding:        '0 12px',
            fontSize:       '11px',
            fontWeight:     '500',
            fontFamily:     'Roboto, Arial, sans-serif',
            borderRadius:   '3px',
            border:         '1px solid transparent',
            cursor:         'pointer',
            lineHeight:     '1',
            letterSpacing:  '0.3px',
            transition:     'all .15s',
            whiteSpace:     'nowrap',
            gap:            '5px',
            boxSizing:      'border-box',
        };
        if (variant === 'primary') {
            return jq.extend({}, base, { background: '#4a90d9', color: '#fff', borderColor: '#4a90d9' });
        }
        if (variant === 'success') {
            return jq.extend({}, base, { background: '#27ae60', color: '#fff', borderColor: '#27ae60' });
        }
        return jq.extend({}, base, { background: '#fff', color: '#555d6b', borderColor: '#d5d8dc' });
    }

    var SVG_COPY  = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
    var SVG_PASTE = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>';

    /* ─── Modal ──────────────────────────────────────────────────────────── */
    function removeModal() {
        jq('#' + MODAL_ID).remove();
        jq('#nova-cp-overlay').remove();
    }

    function showTabModal(model, mode, onConfirm) {
        removeModal();

        var tabs       = getAvailableTabs(model);
        var isCopy     = mode === 'copy';
        var clipboard  = isCopy ? null : readClipboard(getElementId(model));
        var copiedTabs = clipboard ? (clipboard.tabs || []) : [];
        var elType     = model.get('elType') || '';

        var defaultSelected = ['style', 'advanced'];
        if (STRUCTURAL_TYPES.indexOf(elType) !== -1) defaultSelected.push('layout');

        var tabLabels = { content: 'Content', layout: 'Layout', style: 'Style', advanced: 'Avancé' };

        jq('<div>', { id: 'nova-cp-overlay' })
            .css({ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.55)', zIndex: 99998 })
            .on('click', removeModal)
            .appendTo('body');

        var $modal = jq('<div>', { id: MODAL_ID }).css({
            position: 'fixed', top: '50%', left: '50%',
            transform: 'translate(-50%,-50%)',
            zIndex: 99999, width: '270px',
            background: '#fff', borderRadius: '5px',
            boxShadow: '0 6px 30px rgba(0,0,0,.3)',
            fontFamily: 'Roboto, Arial, sans-serif',
            overflow: 'hidden',
        }).appendTo('body');

        /* header */
        jq('<div>').css({
            background: '#f7f7f7', borderBottom: '1px solid #e6e6e6',
            padding: '11px 14px', display: 'flex',
            alignItems: 'center', justifyContent: 'space-between',
        }).append(
            jq('<span>').text(isCopy ? 'Copier les réglages' : 'Coller les réglages')
                .css({ fontSize: '12px', fontWeight: '600', color: '#1f2937' }),
            jq('<button>').html('&#x2715;').css({
                background: 'none', border: 'none', cursor: 'pointer',
                fontSize: '14px', color: '#9ca3af', padding: '0',
            }).on('click', removeModal)
        ).appendTo($modal);

        /* body */
        var $body = jq('<div>').css({ padding: '12px 14px' }).appendTo($modal);

        jq('<p>').html(
            isCopy ? 'Sélectionnez les onglets à copier :'
                   : 'Onglets copiés : <b>' + (copiedTabs.join(', ') || '—') + '</b>'
        ).css({ fontSize: '11px', color: '#6b7280', margin: '0 0 10px' }).appendTo($body);

        var $list = jq('<div>').css({ display: 'flex', flexDirection: 'column', gap: '5px' }).appendTo($body);

        tabs.forEach(function (tab) {
            var available = isCopy ? true : copiedTabs.indexOf(tab) !== -1;
            var checked   = defaultSelected.indexOf(tab) !== -1 && available;

            var $row = jq('<label>').css({
                display: 'flex', alignItems: 'center', gap: '8px',
                cursor: available ? 'pointer' : 'default',
                opacity: available ? 1 : 0.35,
                padding: '5px 8px', borderRadius: '4px',
                background: checked ? '#eff6ff' : 'transparent',
                border: '1px solid ' + (checked ? '#bfdbfe' : '#e5e7eb'),
            });

            var $cb = jq('<input>').attr({ type: 'checkbox', value: tab, checked: checked, disabled: !available });
            $cb.on('change', function () {
                $row.css(jq(this).is(':checked')
                    ? { background: '#eff6ff', borderColor: '#bfdbfe' }
                    : { background: 'transparent', borderColor: '#e5e7eb' });
            });

            $row.append($cb, jq('<span>').text(tabLabels[tab] || tab)
                .css({ fontSize: '12px', color: '#374151', fontWeight: '500' }));
            $list.append($row);
        });

        /* footer */
        var $footer = jq('<div>').css({
            padding: '10px 14px', borderTop: '1px solid #e6e6e6',
            display: 'flex', justifyContent: 'flex-end', gap: '7px', background: '#f9fafb',
        }).appendTo($modal);

        jq('<button>').text('Annuler').css(elBtnStyle('secondary')).on('click', removeModal).appendTo($footer);
        jq('<button>').text(isCopy ? 'Copier' : 'Coller').css(elBtnStyle('primary')).on('click', function () {
            var sel = [];
            $list.find('input:checked').each(function () { sel.push(jq(this).val()); });
            if (!sel.length) { alert('Sélectionnez au moins un onglet.'); return; }
            removeModal();
            onConfirm(sel);
        }).appendTo($footer);
    }

    /* ─── Toolbar ────────────────────────────────────────────────────────── */
    function removeToolbar() { jq('#' + TOOLBAR_ID).remove(); }

    function injectToolbar(model) {
        removeToolbar();
        if (!model || !isSupported(model)) return;

        var elementId    = getElementId(model);
        var clipboard    = readClipboard(elementId);
        var hasClipboard = !!clipboard;

        var $bar = jq('<div>', { id: TOOLBAR_ID }).css({
            display:      'flex',
            alignItems:   'center',
            gap:          '6px',
            padding:      '7px 10px',
            background:   '#f7f7f7',
            borderBottom: '1px solid #e6e6e6',
            boxSizing:    'border-box',
            width:        '100%',
            flexShrink:   '0',
        });

        jq('<span>').text('NOVA:').css({
            fontSize: '10px', fontWeight: '700', color: '#9ca3af',
            textTransform: 'uppercase', letterSpacing: '0.5px', flexShrink: 0,
        }).appendTo($bar);

        var $copyBtn = jq('<button>').css(elBtnStyle('secondary'))
            .attr('title', 'Copier les réglages')
            .append(jq(SVG_COPY), jq('<span>').text('Copier'))
            .appendTo($bar);

        var $pasteBtn = jq('<button>').css(elBtnStyle(hasClipboard ? 'primary' : 'secondary'))
            .attr('title', hasClipboard ? 'Coller les réglages copiés' : 'Aucun réglage copié')
            .prop('disabled', !hasClipboard)
            .append(jq(SVG_PASTE), jq('<span>').text('Coller'))
            .appendTo($bar);

        if (!hasClipboard) $pasteBtn.css({ opacity: '0.4', cursor: 'not-allowed' });

        if (hasClipboard) {
            var count = Object.keys(clipboard.settings || {}).length;
            jq('<span>').text(count + ' · ' + (clipboard.tabs || []).join(', ')).css({
                fontSize: '10px', color: '#9ca3af', marginLeft: 'auto', whiteSpace: 'nowrap',
            }).appendTo($bar);
        }

        /* Copy */
        $copyBtn.on('click', function () {
            var m = _currentModel; if (!m) return;
            showTabModal(m, 'copy', function (tabs) {
                writeClipboard(getElementId(m), {
                    elementId:   getElementId(m),
                    elementType: m.get('elType') || '',
                    widgetType:  m.get('widgetType') || '',
                    tabs:        tabs,
                    settings:    collectSettings(m, tabs),
                    copiedAt:    new Date().toISOString(),
                });
                $copyBtn.css(elBtnStyle('success')).find('span').text('Copié ✓');
                setTimeout(function () {
                    $copyBtn.css(elBtnStyle('secondary')).find('span').text('Copier');
                    injectToolbar(m);
                }, 1600);
            });
        });

        /* Paste */
        $pasteBtn.on('click', function () {
            if (!hasClipboard) return;
            var m = _currentModel; if (!m) return;
            var eid = getElementId(m);
            var cb  = readClipboard(eid);
            if (!cb) { alert('Aucun réglage copié pour ce type (' + eid + ').'); return; }
            if (cb.elementId !== eid) {
                alert('Réglages copiés pour "' + cb.elementId + '", pas pour "' + eid + '".');
                return;
            }
            showTabModal(m, 'paste', function (tabs) {
                var byTab   = getControlsByTab(m);
                var toApply = {};
                tabs.forEach(function (tab) {
                    Object.keys(byTab[tab] || {}).forEach(function (k) {
                        if (k in cb.settings) toApply[k] = cb.settings[k];
                    });
                });
                if (!Object.keys(toApply).length) toApply = cb.settings;

                if (!confirm('Coller ' + Object.keys(toApply).length + ' réglages (' + tabs.join(', ') + ') ?\nAnnulable avec Ctrl+Z.')) return;

                try { elementor.history.history.startItemTracking({ type: 'change', title: 'NOVA Paste Settings' }); } catch (e) {}
                applySettings(m, toApply);
                try { elementor.history.history.endItemTracking(); } catch (e) {}

                $pasteBtn.css(elBtnStyle('success')).find('span').text('Collé ✓');
                setTimeout(function () {
                    $pasteBtn.css(elBtnStyle('primary')).find('span').text('Coller');
                }, 1500);
            });
        });

        /* ── Inject: find the panel navigation tabs, insert just before them ── */
        /*
         * Real DOM structure confirmed:
         *   #elementor-panel-inner
         *     #elementor-panel-content-wrapper
         *       #elementor-panel-page-editor  (when editing an element)
         *         .elementor-panel-navigation  ← tabs row
         *         .elementor-panel-content     ← controls
         *
         * We insert our bar just before .elementor-panel-navigation
         * so it appears at the very top of the editor page.
         */
        var $nav = jq('.elementor-panel-navigation');
        if ($nav.length) {
            $bar.insertBefore($nav);
            return;
        }

        /* fallbacks */
        var $pageEditor = jq('#elementor-panel-page-editor');
        if ($pageEditor.length) { $pageEditor.prepend($bar); return; }

        var $wrapper = jq('#elementor-panel-content-wrapper');
        if ($wrapper.length) { $wrapper.prepend($bar); return; }

        jq('#elementor-panel').prepend($bar);
    }

    /* ─── Core detection loop ────────────────────────────────────────────── */
    function detectAndInject() {
        if (!isPanelInEditorMode()) {
            removeToolbar();
            _currentModel = null;
            return;
        }

        var model = getCurrentModel();
        if (!model) return;

        // Same model, toolbar already there → skip
        if (model === _currentModel && jq('#' + TOOLBAR_ID).length) return;

        _currentModel = model;
        injectToolbar(model);
    }

    /* ─── MutationObserver on panel inner ────────────────────────────────── */
    function startObserver() {
        var $panelInner = jq('#elementor-panel-inner');
        if (!$panelInner.length) {
            setTimeout(startObserver, 300);
            return;
        }

        if (_observer) _observer.disconnect();

        _observer = new MutationObserver(function () {
            clearTimeout(_injectTimer);
            _injectTimer = setTimeout(detectAndInject, 80);
        });

        _observer.observe($panelInner[0], { childList: true, subtree: true });
    }

    /* ─── Elementor hooks (belt + suspenders) ────────────────────────────── */
    function setupHooks() {
        if (typeof elementor === 'undefined' || !elementor.hooks) return false;
        if (_hooksInstalled) return true;
        _hooksInstalled = true;

        var hookHandler = function (_p, model) {
            _currentModel = model;
            clearTimeout(_injectTimer);
            _injectTimer = setTimeout(function () { injectToolbar(model); }, 150);
        };

        elementor.hooks.addAction('panel/open_editor/widget',    hookHandler);
        elementor.hooks.addAction('panel/open_editor/container', hookHandler);
        elementor.hooks.addAction('panel/open_editor/section',   hookHandler);
        elementor.hooks.addAction('panel/open_editor/column',    hookHandler);

        elementor.hooks.addAction('panel/close_editor', function () {
            _currentModel = null;
            removeToolbar();
            removeModal();
        });

        return true;
    }

    /* ─── Boot ───────────────────────────────────────────────────────────── */
    var _bootAttempts = 0;
    function boot() {
        _bootAttempts++;
        setupHooks(); // try hooks (may fail silently if not ready)
        startObserver();

        // Also poll every 500ms for the first 10s as extra safety net
        if (_bootAttempts < 20) {
            setTimeout(function () {
                setupHooks();
                detectAndInject();
            }, _bootAttempts * 500);
        }
    }

    jq(document).ready(function () { setTimeout(boot, 400); });
    jq(window).on('load', function () { setTimeout(boot, 200); });

}(jQuery));
