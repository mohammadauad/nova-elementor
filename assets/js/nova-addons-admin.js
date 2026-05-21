/**
 * NOVA Addons for Elementor - Menu Items Helper & SVG Fix
 * Shine brighter with every page.
 */

// Log IMMEDIATELY when script file is parsed (before jQuery wrapper) – désactivé pour éviter le bruit console

// Prevent multiple initializations
if (typeof window.NOVA_Addons_Initialized !== 'undefined' && window.NOVA_Addons_Initialized === true) {
    // silencieux en production
} else {
    window.NOVA_Addons_Initialized = true;

(function($) {
    'use strict';
    
    // Log initial désactivé (trop verbeux)
    
    // Debug global désactivé par défaut
    if (typeof window.NOVA_DEBUG === 'undefined') {
        window.NOVA_DEBUG = false;
    }
    
    try {
        if (window.NOVA_DEBUG && typeof console !== 'undefined') {
        }
        
        // Check if required variables are available - try multiple possible variable names
        var novaAddonsConfig = null;
        if (typeof NOVA_addons !== 'undefined') {
            novaAddonsConfig = NOVA_addons;
            if (window.NOVA_DEBUG && typeof console !== 'undefined') {
            }
        } else {
            if (window.NOVA_DEBUG && typeof console !== 'undefined') {
            }
        }
        
        // Store config globally for easy access
        window.NOVA_Addons_Config = novaAddonsConfig;
        if (window.NOVA_DEBUG && typeof console !== 'undefined') {
        }
    } catch (e) {
        if (window.NOVA_DEBUG && typeof console !== 'undefined') {
        }
    }

    // ===================================================================
    // PARTIE 1: CHARGEMENT DES MENU ITEMS
    // ===================================================================

    // Fonction pour charger les éléments de menu via AJAX
    function loadMenuItems(menuSlug, callback) {
        if (!menuSlug || menuSlug === '') {
            if (window.NOVA_DEBUG && typeof console !== 'undefined') {
            }
            if (callback) callback([]);
            return;
        }
        
        // Get config from global or local variable
        var config = window.NOVA_Addons_Config || (typeof NOVA_addons !== 'undefined' ? NOVA_addons : null);
        
        if (!config) {
            if (window.NOVA_DEBUG && typeof console !== 'undefined') {
            }
            // Try to get ajaxurl from WordPress default
            config = {
                ajaxurl: (typeof ajaxurl !== 'undefined') ? ajaxurl : '/wp-admin/admin-ajax.php',
                nonce: ''
            };
            if (window.NOVA_DEBUG && typeof console !== 'undefined') {
            }
        }
        
        if (!config || !config.ajaxurl) {
            if (window.NOVA_DEBUG && typeof console !== 'undefined') {
            }
            if (callback) callback([]);
            return;
        }

        if (window.NOVA_DEBUG && typeof console !== 'undefined') {
        }

        $.ajax({
            url: config.ajaxurl,
            type: 'POST',
            data: {
                action: 'NOVA_get_menu_items',
                menu_slug: menuSlug,
                nonce: config.nonce || ''
            },
            success: function(response) {
                if (window.NOVA_DEBUG && typeof console !== 'undefined') {
                }
                if (response.success && callback) {
                    callback(response.data);
                } else {
                    if (window.NOVA_DEBUG && typeof console !== 'undefined') {
                    }
                    if (callback) callback([]);
                }
            },
            error: function(xhr, status, error) {
                if (window.NOVA_DEBUG && typeof console !== 'undefined') {
                }
                if (callback) callback([]);
            }
        });
    }

    // Fonction pour mettre à jour tous les SELECT2 de menu items
    function updateAllMenuItemSelects(menuItems) {
        // Trouver tous les selects de menu items
        var $selects = $('select[data-setting="menu_item_title"]');
        
        $selects.each(function() {
            var $select = $(this);
            var currentValue = $select.val();
            
            // Vider et reconstruire les options
            $select.empty();
            
            // Option vide
            $select.append('<option value="">Select a menu item...</option>');
            
            // Ajouter les items du menu
            if (menuItems && menuItems.length > 0) {
                // Si currentValue est un titre (ancienne donnée), trouver l'ID correspondant
                if (currentValue && currentValue !== '') {
                    var matchingItem = menuItems.find(function(item) {
                        return item.text === currentValue || item.id === currentValue;
                    });
                    if (matchingItem && matchingItem.id !== currentValue) {
                        // La valeur sauvegardée est un titre, convertir en ID
                        currentValue = matchingItem.id;
                        if (window.NOVA_DEBUG) {
                        }
                    }
                }
                
                menuItems.forEach(function(item) {
                    var $option = $('<option></option>')
                        .attr('value', item.id)
                        .text(item.text);
                    
                    if (currentValue && item.id === currentValue) {
                        $option.attr('selected', 'selected');
                    }
                    
                    $select.append($option);
                });
            }
            
            // Réinitialiser SELECT2 si disponible
            if ($select.data('select2')) {
                $select.select2('destroy');
            }
            
            // Réinitialiser select2 avec les nouvelles options
            $select.select2({
                placeholder: 'Select a menu item...',
                allowClear: true,
                minimumResultsForSearch: -1 // Désactiver la recherche si pas d'options
            });
            
            // Restaurer la valeur et déclencher le change pour que Elementor la sauvegarde
            if (currentValue) {
                $select.val(currentValue).trigger('change.select2');
                
                // Mettre à jour le titre du repeater row
                var $option = $select.find('option[value="' + currentValue + '"]');
                if ($option.length > 0) {
                    var optionText = $option.text();
                    if (optionText && optionText !== 'Select a menu item...') {
                        var $repeaterRow = $select.closest('.elementor-repeater-fields');
                        var $titleButton = $repeaterRow.find('.elementor-repeater-row-item-title');
                        if ($titleButton.length > 0) {
                            $titleButton.text(optionText);
                        }
                    }
                }
            }
        });
        
        // Mettre à jour tous les titres après la mise à jour des selects
        setTimeout(updateRepeaterRowTitles, 100);
    }
    
    // Fonction pour charger les options d'un select spécifique avant l'ouverture
    function ensureSelectOptionsLoaded($select) {
        // Vérifier si le select a déjà des options (sauf l'option vide)
        var hasOptions = $select.find('option').length > 1;
        
        if (!hasOptions) {
            // Le select n'a pas d'options, charger depuis le menu
            var $menuSelect = $select.closest('.elementor-control').siblings('.elementor-control-menu_slug').find('select[data-setting="menu_slug"]');
            if ($menuSelect.length === 0) {
                // Chercher dans le widget parent
                $menuSelect = $select.closest('.elementor-controls-content').find('select[data-setting="menu_slug"]');
            }
            
            if ($menuSelect.length > 0) {
                var menuSlug = $menuSelect.val();
                if (menuSlug && menuSlug !== '') {
                    // Charger les options pour ce select spécifique
                    loadMenuItems(menuSlug, function(menuItems) {
                        var currentValue = $select.val();
                        $select.empty();
                        $select.append('<option value="">Select a menu item...</option>');
                        
                        if (menuItems && menuItems.length > 0) {
                            menuItems.forEach(function(item) {
                                var $option = $('<option></option>')
                                    .attr('value', item.id)
                                    .text(item.text);
                                
                                if (currentValue && item.id === currentValue) {
                                    $option.attr('selected', 'selected');
                                }
                                
                                $select.append($option);
                            });
                            
                            // Réinitialiser select2
                            if ($select.data('select2')) {
                                $select.select2('destroy');
                            }
                            
                            $select.select2({
                                placeholder: 'Select a menu item...',
                                allowClear: true
                            });
                            
                            if (currentValue) {
                                $select.val(currentValue).trigger('change.select2');
                            }
                        }
                    });
                }
            }
        }
    }

    // Fonction pour charger les menu items du menu actuellement sélectionné
    function loadCurrentMenuItems() {
        var $menuSelect = $('select[data-setting="menu_slug"]');
        
        if ($menuSelect.length === 0) {
            return false;
        }
        
        var menuSlug = $menuSelect.val();
        
        if (!menuSlug || menuSlug === '') {
            return false;
        }
        
        loadMenuItems(menuSlug, updateAllMenuItemSelects);
        return true;
    }

    // Écouter les changements de menu
    $(document).on('change', 'select[data-setting="menu_slug"]', function() {
        var menuSlug = $(this).val();
        
        if (menuSlug && menuSlug !== '') {
            loadMenuItems(menuSlug, function(menuItems) {
                updateAllMenuItemSelects(menuItems);
                setTimeout(updateRepeaterRowTitles, 200);
            });
        } else {
            // Si le menu est vidé, vider aussi les selects de menu items
            var $widget = $(this).closest('.elementor-controls-content');
            $widget.find('select[data-setting="menu_item_title"]').each(function() {
                var $select = $(this);
                $select.empty();
                $select.append('<option value="">Select a menu item...</option>');
                if ($select.data('select2')) {
                    $select.select2('destroy');
                    $select.select2({
                        placeholder: 'Select a menu item...',
                        allowClear: true
                    });
                }
            });
        }
    });

    // Hook Elementor - Quand un widget s'ouvre
    function setupElementorHooks() {
        // Ensure Elementor is fully loaded before setting up hooks
        if (typeof elementor === 'undefined' || !elementor.hooks || typeof elementor.hooks.addAction !== 'function') {
            if (window.NOVA_DEBUG) {
            }
            return false;
        }
        
        if (window.NOVA_DEBUG) {
        }
        
        try {
            elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
                try {
                    if (!model || !model.get) return;
                var widgetType = model.get('widgetType');
                
                    if (window.NOVA_DEBUG) {
                    }
                    
                    if (widgetType === 'nova-mega-menu' || widgetType === 'nova-icon-menu') {
                        if (window.NOVA_DEBUG) {
                        }
                        
                        // Fonction pour charger les options du widget
                        function loadWidgetMenuItems() {
                            if (window.NOVA_DEBUG) {
                            }
                            
                        loadCurrentMenuItems();
                            
                        // Vérifier aussi si un menu est déjà sélectionné
                            var $menuSelect = $('#elementor-panel-content-wrapper').find('select[data-setting="menu_slug"]');
                        if ($menuSelect.length > 0 && $menuSelect.val()) {
                            var menuSlug = $menuSelect.val();
                                if (window.NOVA_DEBUG) {
                                }
                            loadMenuItems(menuSlug, function(menuItems) {
                                updateAllMenuItemSelects(menuItems);
                                setTimeout(updateRepeaterRowTitles, 200);
                            });
                            } else {
                                if (window.NOVA_DEBUG) {
                                }
                            }
                            
                            // Charger les options pour tous les selects existants dans le panneau
                            var $selects = $('#elementor-panel-content-wrapper').find('select[data-setting="menu_item_title"]');
                            if (window.NOVA_DEBUG) {
                            }
                            
                            $selects.each(function() {
                                loadOptionsBeforeSelect2Init($(this));
                            });
                            
                            // Mettre à jour les titres après un court délai
                            setTimeout(updateRepeaterRowTitles, 500);
                        }
                        
                        // Essayer de charger immédiatement
                        setTimeout(loadWidgetMenuItems, 50);
                        setTimeout(loadWidgetMenuItems, 200);
                        setTimeout(loadWidgetMenuItems, 500);
                        setTimeout(loadWidgetMenuItems, 1000);
                        
                        // Note: Les écouteurs d'onglets globaux sont déjà en place plus bas dans le code
                        // Pas besoin d'ajouter un écouteur spécifique ici
                    }
                } catch (e) {
                    if (window.NOVA_DEBUG) {
                    }
                }
            });
            
            // Hook pour les repeaters
            elementor.hooks.addAction('panel/open_editor/repeater', function(panel, model, view) {
                try {
                setTimeout(function() {
                    loadCurrentMenuItems();
                    var $menuSelect = $('select[data-setting="menu_slug"]');
                    if ($menuSelect.length > 0 && $menuSelect.val()) {
                        var menuSlug = $menuSelect.val();
                        loadMenuItems(menuSlug, function(menuItems) {
                            updateAllMenuItemSelects(menuItems);
                            setTimeout(updateRepeaterRowTitles, 200);
                        });
                    }
                }, 100);
                setTimeout(function() {
                    loadCurrentMenuItems();
                    setTimeout(updateRepeaterRowTitles, 300);
                }, 300);
                } catch (e) {
                    // Silently fail
                }
            });
            
            // Hook pour mettre à jour les titres quand un repeater row est rendu
            elementor.hooks.addAction('panel/repeater/item/rendered', function(panel, model, view) {
                try {
                    setTimeout(function() {
                        updateRepeaterRowTitles();
                    }, 200);
                } catch (e) {
                    // Silently fail
                }
            });
            
            // Hook pour s'assurer que les options sont chargées avant la sauvegarde
            elementor.hooks.addAction('document/save/set-is-saving', function(document, isSaving) {
                if (isSaving) {
                    if (window.NOVA_DEBUG) {
                    }
                    
                    // Avant la sauvegarde, s'assurer que tous les selects ont leurs options avec les IDs
                    var $allSelects = $('#elementor-panel-content-wrapper').find('select[data-setting="menu_item_title"]');
                    if ($allSelects.length > 0) {
                        // Charger les options pour tous les selects
                        $allSelects.each(function() {
                            var $select = $(this);
                            var currentValue = $select.val();
                            
                            // Vérifier si les options utilisent des titres au lieu d'IDs
                            var hasTitleValues = false;
                            $select.find('option').each(function() {
                                var optionValue = $(this).attr('value');
                                if (optionValue && optionValue !== '' && isNaN(parseInt(optionValue))) {
                                    // La valeur n'est pas un nombre, c'est probablement un titre
                                    hasTitleValues = true;
                                }
                            });
                            
                            // Si les options utilisent des titres, recharger avec les IDs
                            if (hasTitleValues) {
                                if (window.NOVA_DEBUG) {
                                }
                                // Réinitialiser le cache pour forcer le rechargement
                                var selectId = $select.attr('id') || $select.attr('data-select2-id') || $select.attr('data-temp-id');
                                if (selectId) {
                                    loadedSelects.delete(selectId);
                                }
                                ensureSelectOptionsLoaded($select);
                            } else if (currentValue && currentValue !== '') {
                                // Vérifier si la valeur actuelle existe dans les options
                                var $option = $select.find('option[value="' + currentValue + '"]');
                                if ($option.length === 0) {
                                    // L'option n'existe pas, recharger les options
                                    if (window.NOVA_DEBUG) {
                                    }
                                    ensureSelectOptionsLoaded($select);
                                }
                            } else {
                                // Pas de valeur, mais s'assurer que les options sont chargées
                                if ($select.find('option').length <= 1) {
                                    if (window.NOVA_DEBUG) {
                                    }
                                    ensureSelectOptionsLoaded($select);
                                }
                            }
                        });
                    }
                }
            });
            
            // Hook pour les changements de section
            elementor.hooks.addAction('panel/open_editor/section', function(panel, model, view) {
                try {
                    setTimeout(function() {
                        loadCurrentMenuItems();
                        
                        // Vérifier si la section contient des contrôles de menu items
                        var $section = $('.elementor-panel-section.elementor-open');
                        var $menuItemSelects = $section.find('select[data-setting="menu_item_title"]');
                        
                        if ($menuItemSelects.length > 0) {
                            $menuItemSelects.each(function() {
                                var $select = $(this);
                                ensureSelectOptionsLoaded($select);
                                
                                // Si select2 est déjà initialisé, recharger les options
                                if ($select.data('select2')) {
                                    var currentValue = $select.val();
                                    var $menuSelect = $select.closest('.elementor-controls-content').find('select[data-setting="menu_slug"]');
                                    
                                    if ($menuSelect.length > 0 && $menuSelect.val()) {
                                        var menuSlug = $menuSelect.val();
                                        loadMenuItems(menuSlug, function(menuItems) {
                                            $select.select2('destroy');
                                            $select.empty();
                                            $select.append('<option value="">Select a menu item...</option>');
                                            
                                            if (menuItems && menuItems.length > 0) {
                                                menuItems.forEach(function(item) {
                                                    var $option = $('<option></option>')
                                                        .attr('value', item.id)
                                                        .text(item.text);
                                                    
                                                    if (currentValue && item.id === currentValue) {
                                                        $option.attr('selected', 'selected');
                                                    }
                                                    
                                                    $select.append($option);
                                                });
                                            }
                                            
                                            $select.select2({
                                                placeholder: 'Select a menu item...',
                                                allowClear: true
                                            });
                                            
                                            if (currentValue) {
                                                $select.val(currentValue).trigger('change.select2');
                                            }
                                        });
                                    }
                                } else {
                                    loadOptionsBeforeSelect2Init($select);
                                }
                            });
                        }
                    }, 150);
                } catch (e) {
                    // Silently fail
                }
            });
            
            // Setup tab change listener
            setupTabChangeListener();
            
            return true;
        } catch (e) {
            return false;
        }
    }
    
    // Wait for Elementor to be fully ready before setting up hooks
    var elementorWaitAttempts = 0;
    var maxElementorWaitAttempts = 50; // 5 seconds max
    function waitForElementor() {
        elementorWaitAttempts++;
        
        if (window.NOVA_DEBUG && elementorWaitAttempts === 1) {
        }
        
        if (typeof elementor !== 'undefined' && elementor.hooks && typeof elementor.hooks.addAction === 'function') {
            if (window.NOVA_DEBUG) {
            }
            // Elementor is ready, set up hooks
            if (setupElementorHooks()) {
                if (window.NOVA_DEBUG) {
                }
                return;
            } else {
                if (window.NOVA_DEBUG) {
                }
            }
        } else {
            if (window.NOVA_DEBUG && elementorWaitAttempts <= 3) {
            }
        }
        
        // If not ready and haven't exceeded max attempts, wait a bit and try again
        if (elementorWaitAttempts < maxElementorWaitAttempts) {
            setTimeout(waitForElementor, 100);
        } else {
            if (window.NOVA_DEBUG) {
            }
        }
    }
    
    // Start waiting for Elementor when DOM is ready
    $(document).ready(function() {
        try {
            if (window.NOVA_DEBUG) {
            }
            
            // Wait a bit for Elementor to initialize
            setTimeout(function() {
                try {
                    waitForElementor();
                } catch (e) {
                }
            }, 200);
        } catch (e) {
        }
    });
    
    // Also try on window load as fallback
        $(window).on('load', function() {
        try {
            if (window.NOVA_DEBUG) {
            }
            setTimeout(function() {
                try {
                    waitForElementor();
                } catch (e) {
                }
            }, 100);
        } catch (e) {
        }
    });
    
    // Force initialization check every second for the first 5 seconds
    var initCheckCount = 0;
    var initCheckInterval = setInterval(function() {
        try {
            initCheckCount++;
            if (initCheckCount > 5) {
                clearInterval(initCheckInterval);
                return;
            }
            
            if (typeof elementor !== 'undefined' && elementor.hooks) {
                if (window.NOVA_DEBUG) {
                }
                if (setupElementorHooks()) {
                    clearInterval(initCheckInterval);
                }
            } else {
                if (window.NOVA_DEBUG && initCheckCount === 1) {
                }
            }
        } catch (e) {
        }
    }, 1000);

    // Écouter les clics sur les panel headings
    $(document).on('click', '.elementor-panel-heading', function() {
        var title = $(this).find('.elementor-panel-heading-title').text();
        
        if (title.includes('Menu') || title.includes('Content')) {
            setTimeout(loadCurrentMenuItems, 100);
            setTimeout(loadCurrentMenuItems, 300);
        }
    });
    
    // Fonction pour recharger les options d'un onglet
    function reloadTabMenuItems($tabContent) {
        if (!$tabContent || $tabContent.length === 0) return;
        
        // Vérifier si le contenu de l'onglet contient des selects de menu items
        var $menuItemSelects = $tabContent.find('select[data-setting="menu_item_title"]');
        
        if ($menuItemSelects.length > 0) {
            // L'onglet contient des contrôles de menu items, recharger les options
            loadCurrentMenuItems();
            
            // Recharger les options pour chaque select individuellement
            $menuItemSelects.each(function() {
                var $select = $(this);
                
                // Réinitialiser le cache pour ce select pour forcer le rechargement
                var selectId = $select.attr('id') || $select.attr('data-select2-id') || $select.attr('data-temp-id');
                if (selectId) {
                    loadedSelects.delete(selectId);
                }
                
                ensureSelectOptionsLoaded($select);
                
                // Si select2 est déjà initialisé, détruire et réinitialiser
                if ($select.data('select2')) {
                    var currentValue = $select.val();
                    $select.select2('destroy');
                    
                    // Recharger les options - chercher dans plusieurs contextes
                    var $menuSelect = $select.closest('.elementor-controls-content').find('select[data-setting="menu_slug"]');
                    if ($menuSelect.length === 0) {
                        // Chercher dans le panneau entier
                        $menuSelect = $('#elementor-panel-content-wrapper').find('select[data-setting="menu_slug"]');
                    }
                    
                    if ($menuSelect.length > 0 && $menuSelect.val()) {
                        var menuSlug = $menuSelect.val();
                        loadMenuItems(menuSlug, function(menuItems) {
                            $select.empty();
                            $select.append('<option value="">Select a menu item...</option>');
                            
                            if (menuItems && menuItems.length > 0) {
                                menuItems.forEach(function(item) {
                                    var $option = $('<option></option>')
                                        .attr('value', item.id)
                                        .text(item.text);
                                    
                                    if (currentValue && item.id === currentValue) {
                                        $option.attr('selected', 'selected');
                                    }
                                    
                                    $select.append($option);
                                });
                            }
                            
                            // Réinitialiser select2
                            $select.select2({
                                placeholder: 'Select a menu item...',
                                allowClear: true
                            });
                            
                            if (currentValue) {
                                $select.val(currentValue).trigger('change.select2');
                            }
                        });
                    }
                } else {
                    // Select2 pas encore initialisé, charger les options avant
                    loadOptionsBeforeSelect2Init($select);
                }
            });
        }
    }
    
    // Fonction pour forcer le rechargement des sections
    function forceReloadSections() {
        // Vérifier si on est dans l'éditeur Elementor
        if (typeof elementor === 'undefined' || !elementor.panels) {
            return;
        }
        
        try {
            // Obtenir le widget actuellement édité
            var currentWidget = null;
            try {
                currentWidget = elementor.panels.currentView?.currentView?.model;
            } catch (e) {
                // Try alternative path
                try {
                    currentWidget = elementor.panels.currentView?.model;
                } catch (e2) {
                    // Silently fail
                }
            }
            
            if (!currentWidget) {
                return;
            }
            
            var widgetType = currentWidget.get('widgetType');
            
            // Si c'est un widget nova-icon-menu ou nova-mega-menu
            if (widgetType === 'nova-icon-menu' || widgetType === 'nova-mega-menu') {
                var $activeTab = $('.elementor-panel-tab.elementor-active');
                var activeTabName = $activeTab.data('tab') || $activeTab.attr('data-tab') || '';
                var $activeTabContent = $('.elementor-tab-content[data-tab="' + activeTabName + '"]');
                
                // Vérifier si les sections sont vides ou manquantes
                var $sections = $activeTabContent.find('.elementor-panel-section');
                
                // Si aucune section n'est trouvée, essayer de forcer le rechargement via Elementor
                if ($sections.length === 0) {
                    try {
                        // Essayer de déclencher un événement Elementor pour forcer le rechargement
                        if (elementor.hooks && typeof elementor.hooks.doAction === 'function') {
                            elementor.hooks.doAction('panel/widget/updated', elementor.panels.currentView, currentWidget);
                        }
                    } catch (e) {
                        // Silently fail
                    }
                } else {
                    // Vérifier chaque section
                    $sections.each(function() {
                        var $section = $(this);
                        var $sectionContent = $section.find('.elementor-panel-section-content');
                        
                        // Si la section est ouverte mais vide, essayer de la forcer à se recharger
                        if ($section.hasClass('elementor-open') && $sectionContent.length > 0 && $sectionContent.children().length === 0) {
                            // Cliquer sur le header pour fermer et rouvrir
                            var $heading = $section.find('.elementor-panel-heading');
                            if ($heading.length > 0) {
                                $heading.trigger('click');
                                setTimeout(function() {
                                    $heading.trigger('click');
                                }, 100);
                            }
                        }
                    });
                }
            }
        } catch (e) {
            // Silently fail
        }
    }
    
    // Écouter les changements d'onglets dans Elementor
    $(document).on('click', '.elementor-panel-tab', function() {
        var $tab = $(this);
        var tabName = $tab.data('tab') || $tab.attr('data-tab') || '';
        
        // Attendre que l'onglet soit activé et le contenu soit visible
        setTimeout(function() {
            // Vérifier si l'onglet actif contient des contrôles de menu items
            var $activeTab = $('.elementor-panel-tab.elementor-active');
            var activeTabName = $activeTab.data('tab') || $activeTab.attr('data-tab') || '';
            var $activeTabContent = $('.elementor-tab-content[data-tab="' + activeTabName + '"]');
            
            // Recharger les options pour cet onglet
            reloadTabMenuItems($activeTabContent);
            
            // Aussi vérifier dans tout le panneau au cas où
            reloadTabMenuItems($('#elementor-panel-content-wrapper'));
            
            // Forcer le rechargement des sections si nécessaire
            forceReloadSections();
        }, 100);
        
        // Réessayer après un délai plus long
        setTimeout(function() {
            var $activeTab = $('.elementor-panel-tab.elementor-active');
            var activeTabName = $activeTab.data('tab') || $activeTab.attr('data-tab') || '';
            var $activeTabContent = $('.elementor-tab-content[data-tab="' + activeTabName + '"]');
            reloadTabMenuItems($activeTabContent);
            forceReloadSections();
        }, 300);
        
        // Réessayer une dernière fois
        setTimeout(function() {
            forceReloadSections();
        }, 600);
    });
    
    // Écouter aussi les événements Elementor pour les changements d'onglets
    function setupTabChangeListener() {
        if (typeof elementor === 'undefined' || !elementor.hooks) {
            return;
        }
        
        try {
            // Écouter quand une section s'ouvre (cela se produit aussi quand on change d'onglet)
            elementor.hooks.addAction('panel/open_editor/section', function(panel, model, view) {
                setTimeout(function() {
                    // Vérifier si la section contient des contrôles de menu items
                    var $section = $('.elementor-panel-section.elementor-open');
                    var $menuItemSelects = $section.find('select[data-setting="menu_item_title"]');
                    
                    if ($menuItemSelects.length > 0) {
                        loadCurrentMenuItems();
                        
                        $menuItemSelects.each(function() {
                            var $select = $(this);
                            ensureSelectOptionsLoaded($select);
                            
                            // Si select2 est déjà initialisé, recharger les options
                            if ($select.data('select2')) {
                                var currentValue = $select.val();
                                var $menuSelect = $select.closest('.elementor-controls-content').find('select[data-setting="menu_slug"]');
                                
                                // Si pas trouvé, chercher dans le panneau entier
                                if ($menuSelect.length === 0) {
                                    $menuSelect = $('#elementor-panel-content-wrapper').find('select[data-setting="menu_slug"]');
                                }
                                
                                if ($menuSelect.length > 0 && $menuSelect.val()) {
                                    var menuSlug = $menuSelect.val();
                                    loadMenuItems(menuSlug, function(menuItems) {
                                        $select.select2('destroy');
                                        $select.empty();
                                        $select.append('<option value="">Select a menu item...</option>');
                                        
                                        if (menuItems && menuItems.length > 0) {
                                            menuItems.forEach(function(item) {
                                                var $option = $('<option></option>')
                                                    .attr('value', item.id)
                                                    .text(item.text);
                                                
                                                if (currentValue && item.id === currentValue) {
                                                    $option.attr('selected', 'selected');
                                                }
                                                
                                                $select.append($option);
                                            });
                                        }
                                        
                                        $select.select2({
                                            placeholder: 'Select a menu item...',
                                            allowClear: true
                                        });
                                        
                                        if (currentValue) {
                                            $select.val(currentValue).trigger('change.select2');
                                        }
                                    });
                                }
                            } else {
                                loadOptionsBeforeSelect2Init($select);
                            }
                        });
                    }
                    
                    // Aussi vérifier l'onglet actif
                    var $activeTab = $('.elementor-panel-tab.elementor-active');
                    if ($activeTab.length > 0) {
                        var activeTabName = $activeTab.data('tab') || $activeTab.attr('data-tab') || '';
                        var $activeTabContent = $('.elementor-tab-content[data-tab="' + activeTabName + '"]');
                        reloadTabMenuItems($activeTabContent);
                    }
                    
                    // Forcer le rechargement des sections si nécessaire
                    forceReloadSections();
                }, 150);
            });
        } catch (e) {
            // Silently fail
        }
    }

    // Observer les changements dans le DOM pour détecter les nouveaux selects
    var menuItemsObserver = null;
    var loadedSelects = new Set(); // Track which selects have already been loaded
    function setupMenuItemsObserver() {
        // Only set up observer if Elementor is ready and we're in the editor
        if (typeof elementor === 'undefined' || !elementor.config) {
            return;
        }
        
        if (menuItemsObserver) {
            return; // Already set up
        }
        
        try {
            menuItemsObserver = new MutationObserver(function(mutations) {
                try {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                                if (node && node.nodeType === 1) {
                        var $newSelects = $(node).find('select[data-setting="menu_item_title"]');
                        if ($newSelects.length > 0) {
                            setTimeout(loadCurrentMenuItems, 100);
                        }
                    }
                });
            }
        });
                } catch (e) {
                    // Silently fail
                }
            });
        } catch (e) {
            // Silently fail
        }
    }
    
    // Fonction pour charger les options d'un select AVANT qu'Elementor n'initialise select2
    function loadOptionsBeforeSelect2Init($select) {
        if (!$select || $select.length === 0) return;
        
        var selectId = $select.attr('id') || $select.attr('data-select2-id');
        if (!selectId) {
            // Generate a temporary ID if none exists
            selectId = 'temp-' + Math.random().toString(36).substr(2, 9);
            $select.attr('data-temp-id', selectId);
        }
        
        // Vérifier si ce select a déjà été chargé
        if (loadedSelects.has(selectId)) {
            // Vérifier si le select a toujours des options
            var hasOptions = $select.find('option').length > 1;
            if (hasOptions) {
                if (window.NOVA_DEBUG) {
                }
                return; // Déjà chargé et a des options
            } else {
                // Options perdues, retirer du cache et recharger
                loadedSelects.delete(selectId);
            }
        }
        
        // Vérifier si le select a déjà des options (plus que juste l'option vide)
        var hasOptions = $select.find('option').length > 1;
        
        if (hasOptions) {
            // Marquer comme chargé
            loadedSelects.add(selectId);
            if (window.NOVA_DEBUG) {
            }
            return; // Déjà des options, pas besoin de charger
        }
        
        if (window.NOVA_DEBUG) {
        }
        
        // Trouver le menu select
        var $menuSelect = $select.closest('.elementor-control').siblings('.elementor-control-menu_slug').find('select[data-setting="menu_slug"]');
        if ($menuSelect.length === 0) {
            $menuSelect = $select.closest('.elementor-controls-content').find('select[data-setting="menu_slug"]');
        }
        if ($menuSelect.length === 0) {
            $menuSelect = $select.closest('.elementor-panel').find('select[data-setting="menu_slug"]');
        }
        
        if ($menuSelect.length > 0 && $menuSelect.val()) {
            var menuSlug = $menuSelect.val();
            
            if (window.NOVA_DEBUG || window.DEBUG) {
                // Loading options for select before select2 init
            }
            
                                // Get config
                                var config = window.NOVA_Addons_Config || (typeof NOVA_addons !== 'undefined' ? NOVA_addons : null);
                                
                                if (!config || !config.ajaxurl) {
                                    if (window.NOVA_DEBUG || window.DEBUG) {
                                    }
                                    return;
                                }
                                
                                if (window.NOVA_DEBUG) {
                                }
                                
                                // Charger les options de manière synchrone
                                $.ajax({
                                    url: config.ajaxurl,
                                    type: 'POST',
                                    async: false, // Synchrone pour charger avant l'initialisation de select2
                                    data: {
                                        action: 'NOVA_get_menu_items',
                                        menu_slug: menuSlug,
                                        nonce: config.nonce
                                    },
                success: function(response) {
                    if (window.NOVA_DEBUG) {
                    }
                    
                    if (response.success && response.data && response.data.length > 0) {
                        var currentValue = $select.val();
                        
                        // Si currentValue est un titre (ancienne donnée), trouver l'ID correspondant
                        if (currentValue && currentValue !== '') {
                            var matchingItem = response.data.find(function(item) {
                                return item.text === currentValue || item.id === currentValue;
                            });
                            if (matchingItem && matchingItem.id !== currentValue) {
                                // La valeur sauvegardée est un titre, convertir en ID
                                currentValue = matchingItem.id;
                                if (window.NOVA_DEBUG) {
                                }
                            }
                        }
                        
                        $select.empty();
                        $select.append('<option value="">Select a menu item...</option>');
                        
                        response.data.forEach(function(item) {
                            var $option = $('<option></option>')
                                .attr('value', item.id)
                                .text(item.text);
                            
                            if (currentValue && item.id === currentValue) {
                                $option.attr('selected', 'selected');
                            }
                            
                            $select.append($option);
                        });
                        
                        // Marquer comme chargé après succès
                        var selectId = $select.attr('id') || $select.attr('data-select2-id') || $select.attr('data-temp-id');
                        if (selectId) {
                            loadedSelects.add(selectId);
                        }
                        
                        if (window.NOVA_DEBUG) {
                        }
                        
                        // Réinitialiser select2 si nécessaire
                        if ($select.data('select2')) {
                            $select.select2('destroy');
                        }
                        
                        // Réinitialiser select2 avec les nouvelles options
                        $select.select2({
                            placeholder: 'Select a menu item...',
                            allowClear: true
                        });
                        
                        // Définir la valeur et déclencher le change pour que Elementor la sauvegarde
                        if (currentValue) {
                            $select.val(currentValue).trigger('change.select2');
                            
                            // Mettre à jour le titre du repeater row
                            var $option = $select.find('option[value="' + currentValue + '"]');
                            if ($option.length > 0) {
                                var optionText = $option.text();
                                if (optionText && optionText !== 'Select a menu item...') {
                                    var $repeaterRow = $select.closest('.elementor-repeater-fields');
                                    var $titleButton = $repeaterRow.find('.elementor-repeater-row-item-title');
                                    if ($titleButton.length > 0) {
                                        $titleButton.text(optionText);
                                    }
                                }
                            }
                            
                            // Aussi déclencher l'événement Elementor pour forcer la sauvegarde
                            // Utiliser setTimeout pour s'assurer que select2 est initialisé
                            setTimeout(function() {
                                $select.trigger('change');
                                
                                // Forcer la mise à jour du modèle Elementor si disponible
                                try {
                                    var controlView = $select.closest('.elementor-control').data('elementor-control-view');
                                    if (controlView && controlView.model) {
                                        controlView.model.set('menu_item_title', currentValue);
                                    }
                                } catch (e) {
                                    if (window.NOVA_DEBUG) {
                                    }
                                }
                            }, 100);
                        }
                    } else {
                        if (window.NOVA_DEBUG) {
                        }
                    }
                },
                error: function(xhr, status, error) {
                    if (window.NOVA_DEBUG) {
                    }
                }
            });
        }
    }

    // Intercepter la sélection dans select2 pour s'assurer que l'ID est sauvegardé et mettre à jour le titre du repeater
    $(document).on('select2:select', 'select[data-setting="menu_item_title"]', function(e) {
        var $select = $(this);
        var selectedValue = e.params.data.id;
        var selectedText = e.params.data.text || '';
        
        if (window.NOVA_DEBUG) {
        }
        
        // S'assurer que la valeur sélectionnée est bien l'ID (pas le titre)
        if (selectedValue && selectedValue !== '') {
            // Vérifier si c'est un nombre (ID) ou un texte (titre)
            if (isNaN(parseInt(selectedValue))) {
                // C'est un titre, trouver l'ID correspondant
                var $option = $select.find('option[value="' + selectedValue + '"]');
                if ($option.length === 0) {
                    // L'option n'existe pas, recharger les options
                    ensureSelectOptionsLoaded($select);
                }
            }
            
            // Mettre à jour le titre du repeater row avec le texte du menu item
            if (selectedText) {
                var $repeaterRow = $select.closest('.elementor-repeater-fields');
                var $titleButton = $repeaterRow.find('.elementor-repeater-row-item-title');
                if ($titleButton.length > 0) {
                    $titleButton.text(selectedText);
                }
            }
            
            // Déclencher le change pour que Elementor sauvegarde
            setTimeout(function() {
                $select.trigger('change');
            }, 50);
        }
    });
    
    // Mettre à jour les titres des repeater rows existants après le chargement des options
    function updateRepeaterRowTitles() {
        if (window.NOVA_DEBUG) {
        }
        
        $('select[data-setting="menu_item_title"]').each(function() {
            var $select = $(this);
            var $repeaterRow = $select.closest('.elementor-repeater-fields');
            var $titleButton = $repeaterRow.find('.elementor-repeater-row-item-title');
            
            if ($titleButton.length === 0) {
                return; // Pas de bouton titre, passer au suivant
            }
            
            var currentTitle = $titleButton.text();
            var currentValue = $select.val();
            
            if (window.NOVA_DEBUG) {
            }
            
            // Si le titre est un ID numérique, utiliser cet ID comme valeur
            if (/^\d+$/.test(currentTitle) && (!currentValue || currentValue === '')) {
                currentValue = currentTitle;
                if (window.NOVA_DEBUG) {
                }
            }
            
            if (currentValue && currentValue !== '') {
                // Trouver l'option correspondante
                var $option = $select.find('option[value="' + currentValue + '"]');
                if ($option.length > 0) {
                    var optionText = $option.text();
                    if (optionText && optionText !== 'Select a menu item...') {
                        // Mettre à jour le titre du repeater row
                        if (currentTitle !== optionText) {
                            $titleButton.text(optionText);
                            if (window.NOVA_DEBUG) {
                            }
                        }
                        
                        // S'assurer que la valeur est sélectionnée dans le select
                        if (!$select.val() || $select.val() !== currentValue) {
                            $select.val(currentValue).trigger('change.select2');
                        }
                    } else {
                        if (window.NOVA_DEBUG) {
                        }
                    }
                } else {
                    if (window.NOVA_DEBUG) {
                    }
                    // Les options ne sont pas encore chargées, forcer le chargement
                    if ($select.find('option').length <= 1) {
                        ensureSelectOptionsLoaded($select);
                        // Réessayer après le chargement
                        setTimeout(function() {
                            updateRepeaterRowTitles();
                        }, 500);
                    }
                }
            } else if (/^\d+$/.test(currentTitle)) {
                // Le titre est un ID mais le select n'a pas de valeur, charger les options
                if (window.NOVA_DEBUG) {
                }
                ensureSelectOptionsLoaded($select);
                // Réessayer après le chargement
                setTimeout(function() {
                    updateRepeaterRowTitles();
                }, 500);
            }
        });
    }
    
    // Intercepter l'ouverture de select2 pour charger les options si nécessaire
    $(document).on('select2:open', 'select[data-setting="menu_item_title"]', function(e) {
        var $select = $(this);
        
        // Vérifier si les options utilisent des titres au lieu d'IDs
        var hasTitleValues = false;
        $select.find('option').each(function() {
            var optionValue = $(this).attr('value');
            if (optionValue && optionValue !== '' && isNaN(parseInt(optionValue))) {
                // La valeur n'est pas un nombre, c'est probablement un titre
                hasTitleValues = true;
            }
        });
        
        // Si les options utilisent des titres, recharger avec les IDs
        if (hasTitleValues) {
            if (window.NOVA_DEBUG) {
            }
            // Réinitialiser le cache pour forcer le rechargement
            var selectId = $select.attr('id') || $select.attr('data-select2-id') || $select.attr('data-temp-id');
            if (selectId) {
                loadedSelects.delete(selectId);
            }
            ensureSelectOptionsLoaded($select);
            return; // Ne pas continuer, les options seront rechargées
        }
        
        // Vérifier si le select a des options (plus que juste l'option vide)
        var hasOptions = $select.find('option').length > 1;
        if (!hasOptions) {
            // Charger les options de manière synchrone si possible
            var $menuSelect = $select.closest('.elementor-control').siblings('.elementor-control-menu_slug').find('select[data-setting="menu_slug"]');
            if ($menuSelect.length === 0) {
                $menuSelect = $select.closest('.elementor-controls-content').find('select[data-setting="menu_slug"]');
            }
            
            if ($menuSelect.length > 0) {
                var menuSlug = $menuSelect.val();
                if (menuSlug && menuSlug !== '') {
                                    // Get config
                                    var config = window.NOVA_Addons_Config || (typeof NOVA_addons !== 'undefined' ? NOVA_addons : null);
                                    
                                    if (!config || !config.ajaxurl) {
                                        return;
                                    }
                                    
                    // Charger immédiatement
                    $.ajax({
                                        url: config.ajaxurl,
                        type: 'POST',
                        async: false, // Synchrone pour charger avant l'ouverture
                        data: {
                            action: 'NOVA_get_menu_items',
                            menu_slug: menuSlug,
                                            nonce: config.nonce
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                var currentValue = $select.val();
                                
                                // Si currentValue est un titre (ancienne donnée), trouver l'ID correspondant
                                if (currentValue && currentValue !== '') {
                                    var matchingItem = response.data.find(function(item) {
                                        return item.text === currentValue || item.id === currentValue;
                                    });
                                    if (matchingItem && matchingItem.id !== currentValue) {
                                        // La valeur sauvegardée est un titre, convertir en ID
                                        currentValue = matchingItem.id;
                                        if (window.NOVA_DEBUG) {
                                        }
                                    }
                                }
                                
                                $select.empty();
                                $select.append('<option value="">Select a menu item...</option>');
                                
                                response.data.forEach(function(item) {
                                    var $option = $('<option></option>')
                                        .attr('value', item.id)
                                        .text(item.text);
                                    
                                    if (currentValue && item.id === currentValue) {
                                        $option.attr('selected', 'selected');
                                    }
                                    
                                    $select.append($option);
                                });
                                
                                // Réinitialiser select2
                                if ($select.data('select2')) {
                                    $select.select2('destroy');
                                }
                                
                                $select.select2({
                                    placeholder: 'Select a menu item...',
                                    allowClear: true
                                });
                                
                                if (currentValue) {
                                    $select.val(currentValue).trigger('change.select2');
                                    
                                    // Déclencher aussi l'événement Elementor pour forcer la sauvegarde
                                    setTimeout(function() {
                                        $select.trigger('change');
                                    }, 50);
                                }
                            }
                        }
                    });
                }
            }
        }
    });
    
    // Observer les selects qui sont ajoutés au DOM
    var selectObserver = null;
    function setupSelectObserver() {
        // Only set up observer if Elementor is ready
        if (typeof elementor === 'undefined') {
            return;
        }
        
        if (selectObserver) {
            return; // Already set up
        }
        
        try {
            selectObserver = new MutationObserver(function(mutations) {
                try {
        mutations.forEach(function(mutation) {
                        if (mutation.addedNodes && mutation.addedNodes.length) {
            mutation.addedNodes.forEach(function(node) {
                                if (node && node.nodeType === 1) {
                                    // Chercher les selects directement dans le node ajouté
                                    var $selects = $(node).find('select[data-setting="menu_item_title"]');
                                    // Aussi vérifier si le node lui-même est un select
                                    if ($(node).is('select[data-setting="menu_item_title"]')) {
                                        $selects = $selects.add($(node));
                                    }
                                    
                                    if ($selects.length > 0) {
                                        // Charger les options IMMÉDIATEMENT avant qu'Elementor n'initialise select2
                                        $selects.each(function() {
                                            var $select = $(this);
                                            // Charger les options de manière synchrone AVANT l'initialisation de select2
                                            loadOptionsBeforeSelect2Init($select);
                                            
                                            // Aussi essayer après un court délai au cas où
                        setTimeout(function() {
                                                if (!$select.data('select2')) {
                                                    loadOptionsBeforeSelect2Init($select);
                                                } else {
                                                    // Select2 déjà initialisé, utiliser la méthode normale
                                                    if ($select.find('option').length <= 1) {
                                                        ensureSelectOptionsLoaded($select);
                                                    }
                                                }
                                                
                                                // Mettre à jour les titres après le chargement des options
                                                setTimeout(updateRepeaterRowTitles, 200);
                                            }, 50);
                                        });
                                        
                                        // Mettre à jour les titres après un court délai
                                        setTimeout(updateRepeaterRowTitles, 300);
                    }
                }
            });
                        }
        });
                } catch (e) {
                    // Silently fail
                }
    });
    
    // Observer le body pour les nouveaux selects
            if ($('body').length > 0 && document.body) {
        selectObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
            }
        } catch (e) {
            // Silently fail
        }
    }

    // Démarrer l'observation
    $(document).ready(function() {
        // Wait for Elementor to be ready before doing anything
        function initWhenReady() {
            if (typeof elementor === 'undefined' || !elementor.config) {
                setTimeout(initWhenReady, 100);
                return;
            }
            
            try {
                // Set up observers
                setupMenuItemsObserver();
                setupSelectObserver();
                setupTabChangeListener();
                
                // Charger immédiatement les options pour tous les selects existants
                var $existingSelects = $('select[data-setting="menu_item_title"]');
                
                $existingSelects.each(function() {
                    loadOptionsBeforeSelect2Init($(this));
                });
                
        // Charger immédiatement
        loadCurrentMenuItems();
        
        // Mettre à jour les titres après le chargement initial
        setTimeout(function() {
            updateRepeaterRowTitles();
        }, 500);
        
        // Réessayer plusieurs fois
                setTimeout(function() {
                    loadCurrentMenuItems();
                    $('select[data-setting="menu_item_title"]').each(function() {
                        loadOptionsBeforeSelect2Init($(this));
                    });
                    // Mettre à jour les titres après le chargement
                    setTimeout(updateRepeaterRowTitles, 300);
                }, 200);
                setTimeout(function() {
                    loadCurrentMenuItems();
                    $('select[data-setting="menu_item_title"]').each(function() {
                        loadOptionsBeforeSelect2Init($(this));
                    });
                    // Mettre à jour les titres après le chargement
                    setTimeout(updateRepeaterRowTitles, 300);
                }, 500);
                setTimeout(function() {
                    loadCurrentMenuItems();
                    $('select[data-setting="menu_item_title"]').each(function() {
                        loadOptionsBeforeSelect2Init($(this));
                    });
                }, 1000);
                setTimeout(function() {
                    loadCurrentMenuItems();
                    $('select[data-setting="menu_item_title"]').each(function() {
                        loadOptionsBeforeSelect2Init($(this));
                    });
                }, 2000);
        
        // Observer le conteneur Elementor
        setTimeout(function() {
                    try {
            var container = document.querySelector('#elementor-panel-content-wrapper');
                        if (container && menuItemsObserver) {
                menuItemsObserver.observe(container, {
                    childList: true,
                    subtree: true
                });
                        }
                    } catch (e) {
                        // Silently fail
            }
        }, 1000);
                } catch (e) {
                    // Silently fail
                }
        }
        
        // Start initialization
        setTimeout(initWhenReady, 300);
        
        // Écouter les changements de valeur du menu select directement
        $(document).on('change', 'select[data-setting="menu_slug"]', function() {
            var menuSlug = $(this).val();
            if (menuSlug && menuSlug !== '') {
                // Charger immédiatement les options pour tous les selects de menu items
                loadMenuItems(menuSlug, function(menuItems) {
                    // Trouver tous les selects de menu items dans le même widget
                    var $widget = $(this).closest('.elementor-controls-content');
                    if ($widget.length === 0) {
                        $widget = $(this).closest('.elementor-panel');
                    }
                    
                    $widget.find('select[data-setting="menu_item_title"]').each(function() {
                        var $select = $(this);
                        var currentValue = $select.val();
                        
                        $select.empty();
                        $select.append('<option value="">Select a menu item...</option>');
                        
                        if (menuItems && menuItems.length > 0) {
                            menuItems.forEach(function(item) {
                                var $option = $('<option></option>')
                                    .attr('value', item.id)
                                    .text(item.text);
                                
                                if (currentValue && item.id === currentValue) {
                                    $option.attr('selected', 'selected');
                                }
                                
                                $select.append($option);
                            });
                            
                            // Réinitialiser select2
                            if ($select.data('select2')) {
                                $select.select2('destroy');
                            }
                            
                            $select.select2({
                                placeholder: 'Select a menu item...',
                                allowClear: true
                            });
                            
                            if (currentValue) {
                                $select.val(currentValue).trigger('change.select2');
                            }
                        }
                    });
                }.bind(this));
            }
        });
    });

    // ===================================================================
    // PARTIE 2: FIX DE LA SÉLECTION SVG - NOUVELLE APPROCHE
    // ===================================================================

    var storedMediaId = null;
    var originalWpMedia = null;

    /**
     * Extrait l'ID du média depuis différentes structures
     */
    function extractMediaId(data) {
        if (!data) return null;
        
        if (typeof data === 'number' || (typeof data === 'string' && !isNaN(data))) {
            return parseInt(data);
        }
        
        if (typeof data === 'object') {
            // Essayer value.id
            if (data.value && typeof data.value === 'object' && data.value.id) {
                return parseInt(data.value.id);
            }
            // Essayer value direct
            if (data.value && !isNaN(data.value)) {
                return parseInt(data.value);
            }
            // Essayer id direct
            if (data.id && !isNaN(data.id)) {
                return parseInt(data.id);
            }
        }
        
        return null;
    }

    /**
     * Applique la sélection dans le modal média WordPress
     */
    function applyMediaSelection(attachmentId) {
        if (!attachmentId || typeof wp === 'undefined' || !wp.media || !wp.media.frame) {
            return;
        }

        try {
            var frame = wp.media.frame;
            var selection = frame.state().get('selection');
            
            if (selection) {
                selection.reset();
                var attachment = wp.media.attachment(attachmentId);
                attachment.fetch();
                selection.add(attachment);
            }
        } catch (error) {
            // Erreur silencieuse
        }
    }

    /**
     * Intercepte tous les clics sur les boutons média des contrôles ICONS
     */
    function interceptIconsMediaButtons() {
        $(document).off('click.NOVA-svg-fix').on('click.NOVA-svg-fix', 
            '.elementor-control-type-icons .elementor-control-media__tool, ' +
            '.elementor-control-type-icons .elementor-control-media__preview, ' +
            '.elementor-control-type-icons .elementor-control-media-area',
            function(e) {
                var $control = $(this).closest('.elementor-control-type-icons');
                var controlName = $control.data('control-name');
                
                // Méthode 1: Récupérer depuis l'input hidden
                var $input = $control.find('input[type="hidden"]');
                var inputValue = $input.val();
                
                var mediaId = null;
                
                if (inputValue) {
                    try {
                        var parsed = JSON.parse(inputValue);
                        mediaId = extractMediaId(parsed);
                    } catch (e) {
                        // Erreur silencieuse
                    }
                }
                
                // Méthode 2: Récupérer depuis l'attribut data-value
                if (!mediaId) {
                    var $iconType = $control.find('[data-value]');
                    if ($iconType.length) {
                        var dataValue = $iconType.data('value');
                        mediaId = extractMediaId(dataValue);
                    }
                }
                
                // Méthode 3: Récupérer depuis l'image preview
                if (!mediaId) {
                    var $preview = $control.find('.elementor-control-media__preview');
                    var bgImage = $preview.css('background-image');
                    
                    if (bgImage && bgImage !== 'none') {
                        // Extraire l'URL et essayer de trouver l'ID dans l'URL
                        var urlMatch = bgImage.match(/wp-content\/uploads\/.*$/);
                    }
                }
                
                if (mediaId) {
                    storedMediaId = mediaId;
                    
                    // Attendre l'ouverture du modal et appliquer la sélection
                    var attemptCount = 0;
                    var checkInterval = setInterval(function() {
                        attemptCount++;
                        
                        if (typeof wp !== 'undefined' && wp.media && wp.media.frame) {
                            clearInterval(checkInterval);
                            
                            // Appliquer immédiatement
                            applyMediaSelection(storedMediaId);
                            
                            // Réappliquer après de courts délais
                            setTimeout(function() { applyMediaSelection(storedMediaId); }, 50);
                            setTimeout(function() { applyMediaSelection(storedMediaId); }, 150);
                            setTimeout(function() { applyMediaSelection(storedMediaId); }, 300);
                            setTimeout(function() { applyMediaSelection(storedMediaId); }, 500);
                        }
                        
                        if (attemptCount > 40) { // 2 secondes max
                            clearInterval(checkInterval);
                        }
                    }, 50);
                }
            }
        );
    }

    // Initialiser au chargement
    $(document).ready(function() {
        interceptIconsMediaButtons();
    });

    // Réinitialiser quand Elementor ouvre un panel
    function setupElementorHooksForIcons() {
        if (typeof elementor === 'undefined' || !elementor.hooks || typeof elementor.hooks.addAction !== 'function') {
            return false;
        }
        
        try {
            elementor.hooks.addAction('panel/open_editor/widget', function(panel, model, view) {
                try {
                setTimeout(interceptIconsMediaButtons, 100);
                } catch (e) {
                    // Silently fail
                }
            });
            
            elementor.hooks.addAction('panel/open_editor/repeater', function(panel, model, view) {
                try {
                setTimeout(interceptIconsMediaButtons, 100);
                } catch (e) {
                    // Silently fail
                }
            });
            
            return true;
        } catch (e) {
            return false;
        }
    }
    
    // Wait for Elementor before setting up icon hooks
    var elementorIconsWaitAttempts = 0;
    var maxElementorIconsWaitAttempts = 50; // 5 seconds max
    function waitForElementorForIcons() {
        elementorIconsWaitAttempts++;
        
        if (setupElementorHooksForIcons()) {
            return;
        }
        
        if (elementorIconsWaitAttempts < maxElementorIconsWaitAttempts) {
            setTimeout(waitForElementorForIcons, 100);
        }
    }
    
    $(document).ready(function() {
        setTimeout(waitForElementorForIcons, 200);
    });

})(jQuery);

} // End of initialization check