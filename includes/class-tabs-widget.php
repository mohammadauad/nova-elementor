<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Repeater;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Icons_Manager;

if (!defined('ABSPATH'))
	exit;

class Tabs_Widget extends Widget_Base
{

	public function get_name()
	{
		return 'nova-tabs';
	}
	public function get_title()
	{
		return esc_html__('NOVA Tabs', 'NOVA-addons');
	}
	public function get_icon()
	{
		return 'eicon-tabs';
	}
	public function get_categories()
	{
		return ['NOVA-addons'];
	}
	public function get_style_depends()
	{
		return ['nova-tabs-style'];
	}
	public function get_script_depends()
	{
		return ['nova-tabs-script'];
	}

	/**
	 * Get all NOVA Title widgets from the current page/document (safe version for register_controls).
	 *
	 * @return array Array of widget options [id => label]
	 */
	protected function get_nova_title_widgets_options_safe()
	{
		$options = [
			'' => esc_html__('— Sélectionner —', 'NOVA-addons'),
		];

		// Early return if Elementor is not fully loaded
		if (!class_exists('\Elementor\Plugin') || !\Elementor\Plugin::$instance) {
			return $options;
		}

		try {
			// Get current document/post ID
			$post_id = get_the_ID();

			// Try multiple methods to get post ID
			if (!$post_id) {
				if (isset($_GET['post'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$post_id = intval($_GET['post']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			if (!$post_id) {
				if (isset($_REQUEST['elementor-preview'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$post_id = intval($_REQUEST['elementor-preview']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}
			}

			if (!$post_id) {
				global $post;
				if (isset($post->ID)) {
					$post_id = $post->ID;
				}
			}

			if (!$post_id) {
				return $options;
			}

			if (!\Elementor\Plugin::$instance->documents) {
				return $options;
			}

			$document = \Elementor\Plugin::$instance->documents->get($post_id, false);
			if (!$document || !$document->is_built_with_elementor()) {
				return $options;
			}

			$elements_data = $document->get_elements_data();
			if (empty($elements_data) || !is_array($elements_data)) {
				return $options;
			}

			$this->find_nova_title_widgets($elements_data, $options);

		} catch (\Exception $e) {
			return $options;
		}

		return $options;
	}

	protected function get_nova_title_widgets_options()
	{
		return $this->get_nova_title_widgets_options_safe();
	}

	protected function get_elementor_templates_options()
	{
		$options = [
			'' => esc_html__('— Aucun —', 'NOVA-addons'),
		];

		if (!class_exists('\Elementor\Plugin')) {
			return $options;
		}

		$templates = \Elementor\Plugin::$instance->templates_manager->get_source('local')->get_items();
		if (!empty($templates) && is_array($templates)) {
			foreach ($templates as $template) {
				if (empty($template['template_id'])) {
					continue;
				}
				$options[$template['template_id']] = $template['title'] . ' (ID ' . $template['template_id'] . ')';
			}
		}

		return $options;
	}

	protected function find_nova_title_widgets($elements, &$options)
	{
		if (!is_array($elements)) {
			return;
		}

		foreach ($elements as $element) {
			if (isset($element['widgetType']) && 'nova-title' === $element['widgetType']) {
				$widget_id = isset($element['id']) ? $element['id'] : '';
				if (empty($widget_id)) {
					continue;
				}

				$label = esc_html__('NOVA Title', 'NOVA-addons') . ' #' . substr($widget_id, 0, 8);

				if (isset($element['settings']['text_1']) && !empty($element['settings']['text_1'])) {
					$text_preview = wp_strip_all_tags($element['settings']['text_1']);
					$text_preview = mb_substr($text_preview, 0, 50);
					if (!empty($text_preview)) {
						$label = $text_preview . '...';
					}
				} elseif (isset($element['settings']['text_2']) && !empty($element['settings']['text_2'])) {
					$text_preview = wp_strip_all_tags($element['settings']['text_2']);
					$text_preview = mb_substr($text_preview, 0, 50);
					if (!empty($text_preview)) {
						$label = $text_preview . '...';
					}
				}

				$options[$widget_id] = $label;
			}

			if (isset($element['elements']) && is_array($element['elements'])) {
				$this->find_nova_title_widgets($element['elements'], $options);
			}
		}
	}

	protected function render_nova_title_widget($widget_id)
	{
		if (empty($widget_id)) {
			return '';
		}

		$post_id = get_the_ID();
		if (!$post_id) {
			return '';
		}

		$document = \Elementor\Plugin::$instance->documents->get($post_id);
		if (!$document || !$document->is_built_with_elementor()) {
			return '';
		}

		$elements_data = $document->get_elements_data();
		if (empty($elements_data)) {
			return '';
		}

		$widget_data = $this->find_widget_by_id($elements_data, $widget_id);
		if (empty($widget_data)) {
			return '';
		}

		try {
			$widget_instance = \Elementor\Plugin::$instance->elements_manager->create_element_instance($widget_data);
			if (!$widget_instance) {
				return '';
			}

			ob_start();
			$widget_instance->render_content();
			$html = ob_get_clean();

			return $html;
		} catch (\Exception $e) {
			return '';
		}
	}

	protected function find_widget_by_id($elements, $widget_id)
	{
		if (!is_array($elements)) {
			return false;
		}

		foreach ($elements as $element) {
			if (isset($element['id']) && $element['id'] === $widget_id) {
				return $element;
			}

			if (isset($element['elements']) && is_array($element['elements'])) {
				$found = $this->find_widget_by_id($element['elements'], $widget_id);
				if (false !== $found) {
					return $found;
				}
			}
		}

		return false;
	}

	protected function register_controls()
	{

		// ── LAYOUT / MISE EN PAGE ─────────────────────────────────
		$this->start_controls_section('section_layout', ['label' => esc_html__('Mise en page', 'NOVA-addons')]);

		$this->add_control('order_heading', [
			'label' => esc_html__('Structure du Panneau (Tabs Content)', 'NOVA-addons'),
			'type' => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_responsive_control('panel_direction', [
			'label' => esc_html__('Direction du contenu', 'NOVA-addons'),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'column' => ['title' => 'Vertical', 'icon' => 'eicon-arrow-down'],
				'row' => ['title' => 'Horizontal', 'icon' => 'eicon-arrow-right'],
			],
			'default' => 'column',
			'selectors' => [
				'{{WRAPPER}} .nova-tab-panel-inner' => 'flex-direction: {{VALUE}};',
			],
		]);

		$this->add_responsive_control('panel_wrap', [
			'label' => esc_html__('Flex Wrap (si Horizontal)', 'NOVA-addons'),
			'type' => Controls_Manager::SWITCHER,
			'return_value' => 'wrap',
			'selectors' => [
				'{{WRAPPER}} .nova-tab-panel-inner' => 'flex-wrap: {{VALUE}};',
			],
		]);

		$this->add_responsive_control('panel_align', [
			'label' => esc_html__('Alignement (Items)', 'NOVA-addons'),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => ['title' => 'Début', 'icon' => 'eicon-align-start-v'],
				'center' => ['title' => 'Centre', 'icon' => 'eicon-align-center-v'],
				'flex-end' => ['title' => 'Fin', 'icon' => 'eicon-align-end-v'],
			],
			'selectors' => [
				'{{WRAPPER}} .nova-tab-panel-inner' => 'align-items: {{VALUE}};',
			],
		]);

		$elements = [
			'tabs' => 'Tabs Nav',
			'fixed_text' => 'Texte Fixe',
			'text1' => 'Texte 1',
			'text2' => 'Texte 2',
			'image' => 'Image',
			'icon' => 'Icône',
			'link' => 'Lien',
		];

		$this->add_control('panel_order_heading', [
			'label' => esc_html__('Gestion des Éléments (Panneau)', 'NOVA-addons'),
			'type' => Controls_Manager::HEADING,
		]);

		$this->add_responsive_control('panel_layout_type', [
			'label' => esc_html__('Type de structure', 'NOVA-addons'),
			'type' => Controls_Manager::SELECT,
			'options' => [
				'flat' => 'Flexible (1 seule zone)',
				'2_col' => '2 Colonnes (Gauche / Droite)',
			],
			'default' => 'flat',
		]);

		$this->add_responsive_control('panel_2_col_gap', [
			'label' => esc_html__('Espacement entre Colonnes', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%'],
			'range' => ['px' => ['min' => 0, 'max' => 100]],
			'selectors' => ['{{WRAPPER}} .nova-tab-panel-inner' => 'gap: {{SIZE}}{{UNIT}};'],
			'condition' => ['panel_layout_type' => '2_col'],
		]);

		$this->add_responsive_control('panel_left_width', [
			'label' => esc_html__('Largeur Colonne Gauche', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['%' => ['min' => 10, 'max' => 90]],
			'selectors' => ['{{WRAPPER}} .nova-tab-col-left' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;'],
			'condition' => ['panel_layout_type' => '2_col'],
		]);

		$this->add_responsive_control('panel_right_width', [
			'label' => esc_html__('Largeur Colonne Droite', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['%' => ['min' => 10, 'max' => 90]],
			'selectors' => ['{{WRAPPER}} .nova-tab-col-right' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;'],
			'condition' => ['panel_layout_type' => '2_col'],
		]);

		foreach ($elements as $key => $label) {
			$this->add_control("{$key}_col", [
				'label' => $label . ' -> Colonne',
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => ['title' => 'Gauche', 'icon' => 'eicon-h-align-left'],
					'right' => ['title' => 'Droite', 'icon' => 'eicon-h-align-right'],
				],
				'default' => 'left',
				'condition' => ['panel_layout_type' => '2_col'],
			]);

			$selector = '';
			if ($key === 'fixed_text') {
				$selector = '{{WRAPPER}} .nova-tabs-fixed-text';
			} elseif ($key === 'tabs') {
				$selector = '{{WRAPPER}} .nova-tabs-nav';
			} else {
				$selector = "{{WRAPPER}} .nova-tab-content-{$key}";
			}

			$this->add_responsive_control("order_{$key}", [
				'label' => $label . ' : Ordre',
				'type' => Controls_Manager::NUMBER,
				'selectors' => [
					$selector => 'order: {{VALUE}};',
				],
			]);
		}

		$this->end_controls_section();

		// ── CONTENU FIXE ──────────────────────────────────────────
		$this->start_controls_section('section_fixed_text', ['label' => esc_html__('Contenu Fixe Externe', 'NOVA-addons')]);

		$this->add_control('fixed_text_source', [
			'label' => esc_html__('Source du Contenu', 'NOVA-addons'),
			'type' => Controls_Manager::SELECT,
			'default' => 'wysiwyg',
			'options' => [
				'wysiwyg' => esc_html__('Éditeur de texte classique', 'NOVA-addons'),
				'nova_title' => esc_html__('Widget NOVA Title de la page', 'NOVA-addons'),
				'elementor_template' => esc_html__('Modèle Elementor', 'NOVA-addons'),
			],
		]);

		$this->add_control('global_fixed_text', [
			'label' => esc_html__('Texte Fixe', 'NOVA-addons'),
			'type' => Controls_Manager::WYSIWYG,
			'description' => esc_html__('Ce texte sera affiché en continu, indépendamment des tabs.', 'NOVA-addons'),
			'condition' => ['fixed_text_source' => 'wysiwyg'],
		]);

		$this->add_control(
			'fixed_text_template',
			[
				'label' => esc_html__('Choisir un modèle', 'NOVA-addons'),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_elementor_templates_options(),
				'default' => '',
				'condition' => [
					'fixed_text_source' => 'elementor_template',
				],
			]
		);

		$this->add_control(
			'selected_nova_title_widget_id',
			[
				'label' => esc_html__('Sélectionner le widget NOVA Title', 'NOVA-addons'),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_nova_title_widgets_options_safe(),
				'condition' => [
					'fixed_text_source' => 'nova_title',
				],
				'description' => esc_html__('Choisissez le widget NOVA Title à afficher. Le widget sélectionné sera automatiquement caché à sa position d\'origine. Rechargez la page s\'il ne s\'affiche pas.', 'NOVA-addons'),
			]
		);

		$this->add_control('fixed_text_positioning', [
			'label' => esc_html__('Positionnement', 'NOVA-addons'),
			'type' => Controls_Manager::SELECT,
			'options' => [
				'static' => 'Flux normal (Intégré aux colonnes)',
				'absolute' => 'Absolu (Flottant libre)',
			],
			'default' => 'static',
			'selectors' => [
				'{{WRAPPER}} .nova-tabs-widget' => 'position: relative;',
				'{{WRAPPER}} .nova-tabs-fixed-text' => 'position: {{VALUE}}; z-index: 10;',
			],
		]);

		$this->add_responsive_control('fixed_text_align_self', [
			'label' => esc_html__('Alignement interne', 'NOVA-addons'),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => ['title' => 'Début', 'icon' => 'eicon-align-start-v'],
				'center' => ['title' => 'Centre', 'icon' => 'eicon-align-center-v'],
				'flex-end' => ['title' => 'Fin', 'icon' => 'eicon-align-end-v'],
			],
			'selectors' => ['{{WRAPPER}} .nova-tabs-fixed-text' => 'align-self: {{VALUE}};'],
			'condition' => ['fixed_text_positioning' => 'static'],
		]);

		$this->add_responsive_control('fixed_text_pos_x', [
			'label' => esc_html__('Position Horizontale (Gauche)', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['px' => ['min' => -500, 'max' => 1000]],
			'selectors' => ['{{WRAPPER}} .nova-tabs-fixed-text' => 'left: {{SIZE}}{{UNIT}};'],
			'condition' => ['fixed_text_positioning' => 'absolute'],
		]);

		$this->add_responsive_control('fixed_text_pos_y', [
			'label' => esc_html__('Position Verticale (Haut)', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vh'],
			'range' => ['px' => ['min' => -500, 'max' => 1000]],
			'selectors' => ['{{WRAPPER}} .nova-tabs-fixed-text' => 'top: {{SIZE}}{{UNIT}};'],
			'condition' => ['fixed_text_positioning' => 'absolute'],
		]);

		// Removed Order from here as it's now in the Layout section

		$this->add_responsive_control('fixed_text_width', [
			'label' => esc_html__('Largeur du Texte Fixe', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['%' => ['min' => 10, 'max' => 100]],
			'selectors' => ['{{WRAPPER}} .nova-tabs-fixed-text' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;'],
		]);

		$this->add_responsive_control('fixed_text_margin', [
			'label' => esc_html__('Marge', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tabs-fixed-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('tabs_content_order', [
			'label' => esc_html__('Ordre de la zone Tabs Content', 'NOVA-addons'),
			'type' => Controls_Manager::NUMBER,
			'selectors' => ['{{WRAPPER}} .nova-tabs-content' => 'order: {{VALUE}};'],
		]);

		$this->add_responsive_control('tabs_content_width', [
			'label' => esc_html__('Largeur de la zone Tabs Content', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['%' => ['min' => 10, 'max' => 100]],
			'selectors' => ['{{WRAPPER}} .nova-tabs-content' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;'],
		]);

		$this->end_controls_section();

		// ── CONTENT ──────────────────────────────────────────────
		$this->start_controls_section('section_tabs', ['label' => esc_html__('Tabs', 'NOVA-addons')]);

		$repeater = new Repeater();

		$repeater->add_control('tab_title', [
			'label' => esc_html__('Titre du tab', 'NOVA-addons'),
			'type' => Controls_Manager::TEXT,
			'default' => esc_html__('Tab', 'NOVA-addons'),
		]);

		$repeater->add_control('tab_icon', [
			'label' => esc_html__('Icône du tab', 'NOVA-addons'),
			'type' => Controls_Manager::ICONS,
			'skin' => 'inline',
			'label_block' => false,
		]);

		$repeater->add_control('content_text_1', [
			'label' => esc_html__('Texte 1', 'NOVA-addons'),
			'type' => Controls_Manager::WYSIWYG,
			'default' => '',
			'separator' => 'before',
		]);

		$repeater->add_control('content_text_2', [
			'label' => esc_html__('Texte 2', 'NOVA-addons'),
			'type' => Controls_Manager::WYSIWYG,
			'default' => '',
		]);

		$repeater->add_control('content_image', [
			'label' => esc_html__('Images', 'NOVA-addons'),
			'type'  => Controls_Manager::GALLERY,
			'show_label' => true,
		]);

		$repeater->add_control('content_icon', [
			'label' => esc_html__('Icône du contenu', 'NOVA-addons'),
			'type' => Controls_Manager::ICONS,
			'skin' => 'inline',
			'label_block' => false,
		]);

		$repeater->add_control('content_link', [
			'label' => esc_html__('Lien', 'NOVA-addons'),
			'type' => Controls_Manager::URL,
			'placeholder' => 'https://...',
		]);

		$repeater->add_control('content_link_text', [
			'label' => esc_html__('Texte du lien', 'NOVA-addons'),
			'type' => Controls_Manager::TEXT,
			'default' => '',
		]);

		$this->add_control('tabs', [
			'label' => esc_html__('Tabs', 'NOVA-addons'),
			'type' => Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'default' => [
				['tab_title' => 'Tab 1', 'content_text_1' => '<p>Contenu du tab 1</p>'],
				['tab_title' => 'Tab 2', 'content_text_1' => '<p>Contenu du tab 2</p>'],
			],
			'title_field' => '{{{ tab_title }}}',
		]);

		$this->add_control('active_tab', [
			'label' => esc_html__('Tab actif par défaut', 'NOVA-addons'),
			'type' => Controls_Manager::NUMBER,
			'default' => 1,
			'min' => 1,
		]);

		$this->add_control('autoplay', [
			'label' => esc_html__('Autoplay', 'NOVA-addons'),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => esc_html__('Oui', 'NOVA-addons'),
			'label_off' => esc_html__('Non', 'NOVA-addons'),
			'return_value' => 'yes',
			'default' => 'no',
			'separator' => 'before',
		]);

		$this->add_control('autoplay_speed', [
			'label' => esc_html__('Vitesse Autoplay (ms)', 'NOVA-addons'),
			'type' => Controls_Manager::NUMBER,
			'default' => 5000,
			'min' => 1000,
			'step' => 100,
			'condition' => [
				'autoplay' => 'yes',
			],
		]);

		$this->add_control('progress_bar_color', [
			'label' => esc_html__('Couleur barre de progression', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .nova-tab-progress-fill' => 'background-color: {{VALUE}};',
			],
			'condition' => [
				'autoplay' => 'yes',
			],
		]);

		$this->add_responsive_control('progress_bar_height', [
			'label' => esc_html__('Épaisseur barre de progression', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px'],
			'range' => ['px' => ['min' => 1, 'max' => 20]],
			'default' => ['size' => 3, 'unit' => 'px'],
			'selectors' => [
				'{{WRAPPER}} .nova-tab-progress' => 'height: {{SIZE}}{{UNIT}};',
			],
			'condition' => [
				'autoplay' => 'yes',
			],
		]);

		// ── Animation fade du contenu ────────────────────────────
		$this->add_control('content_fade_heading', [
			'label' => esc_html__('Animation du contenu', 'NOVA-addons'),
			'type' => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_control('content_fade', [
			'label' => esc_html__('Activer l\'animation fade-in', 'NOVA-addons'),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => esc_html__('Oui', 'NOVA-addons'),
			'label_off' => esc_html__('Non', 'NOVA-addons'),
			'return_value' => 'yes',
			'default' => 'no',
			'description' => esc_html__('Anime le contenu des tabs à l\'apparition (premier rendu et au scroll quand le widget entre dans le viewport).', 'NOVA-addons'),
		]);

		$this->add_control('content_fade_trigger', [
			'label' => esc_html__('Déclencheur', 'NOVA-addons'),
			'type' => Controls_Manager::SELECT,
			'default' => 'both',
			'options' => [
				'load' => esc_html__('Au chargement de la page', 'NOVA-addons'),
				'scroll' => esc_html__('À l\'entrée dans le viewport (scroll)', 'NOVA-addons'),
				'both' => esc_html__('Au chargement ET au scroll (selon visibilité)', 'NOVA-addons'),
			],
			'condition' => ['content_fade' => 'yes'],
		]);

		$this->add_control('content_fade_replay', [
			'label' => esc_html__('Rejouer à chaque changement de tab', 'NOVA-addons'),
			'type' => Controls_Manager::SWITCHER,
			'label_on' => esc_html__('Oui', 'NOVA-addons'),
			'label_off' => esc_html__('Non', 'NOVA-addons'),
			'return_value' => 'yes',
			'default' => 'no',
			'condition' => ['content_fade' => 'yes'],
		]);

		$this->add_control('content_fade_duration', [
			'label' => esc_html__('Durée (ms)', 'NOVA-addons'),
			'type' => Controls_Manager::NUMBER,
			'default' => 700,
			'min' => 100,
			'max' => 4000,
			'step' => 50,
			'condition' => ['content_fade' => 'yes'],
		]);

		$this->add_control('content_fade_delay', [
			'label' => esc_html__('Délai (ms)', 'NOVA-addons'),
			'type' => Controls_Manager::NUMBER,
			'default' => 0,
			'min' => 0,
			'max' => 3000,
			'step' => 50,
			'condition' => ['content_fade' => 'yes'],
		]);

		$this->add_control('content_fade_translate_y', [
			'label' => esc_html__('Décalage vertical initial (px)', 'NOVA-addons'),
			'type' => Controls_Manager::NUMBER,
			'default' => 20,
			'min' => 0,
			'max' => 200,
			'step' => 2,
			'description' => esc_html__('0 = pas de translation, fade uniquement.', 'NOVA-addons'),
			'condition' => ['content_fade' => 'yes'],
		]);

		$this->end_controls_section();

		// ── STYLE TABS ───────────────────────────────────────────
		$this->start_controls_section('section_style_tabs', [
			'label' => esc_html__('Tabs', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_responsive_control('tabs_gap', [
			'label' => esc_html__('Espacement entre tabs', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', 'em'],
			'range' => ['px' => ['min' => 0, 'max' => 60]],
			'default' => ['size' => 8, 'unit' => 'px'],
			'selectors' => ['{{WRAPPER}} .nova-tabs-nav' => 'gap: {{SIZE}}{{UNIT}};'],
		]);

		$this->add_responsive_control('tabs_align', [
			'label' => esc_html__('Alignement', 'NOVA-addons'),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => ['title' => 'Gauche', 'icon' => 'eicon-text-align-left'],
				'center' => ['title' => 'Centre', 'icon' => 'eicon-text-align-center'],
				'flex-end' => ['title' => 'Droite', 'icon' => 'eicon-text-align-right'],
				'stretch' => ['title' => 'Étiré', 'icon' => 'eicon-text-align-justify'],
			],
			'default' => 'flex-start',
			'selectors' => ['{{WRAPPER}} .nova-tabs-nav' => 'justify-content: {{VALUE}};'],
		]);

		$this->add_group_control(Group_Control_Typography::get_type(), [
			'name' => 'tab_typography',
			'selector' => '{{WRAPPER}} .nova-tab-btn',
		]);

		$this->add_responsive_control('tab_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('tab_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		// Normal / Active tabs
		$this->start_controls_tabs('tab_style_tabs');

		$this->start_controls_tab('tab_normal', ['label' => esc_html__('Normal', 'NOVA-addons')]);
		$this->add_control('tab_color', [
			'label' => esc_html__('Couleur texte', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-btn' => 'color: {{VALUE}};'],
		]);
		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'tab_bg',
			'selector' => '{{WRAPPER}} .nova-tab-btn',
		]);
		$this->add_group_control(Group_Control_Border::get_type(), [
			'name' => 'tab_border',
			'selector' => '{{WRAPPER}} .nova-tab-btn',
		]);
		$this->end_controls_tab();

		$this->start_controls_tab('tab_active', ['label' => esc_html__('Actif', 'NOVA-addons')]);
		$this->add_control('tab_color_active', [
			'label' => esc_html__('Couleur texte', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-btn.nova-tab-active' => 'color: {{VALUE}};'],
		]);
		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'tab_bg_active',
			'selector' => '{{WRAPPER}} .nova-tab-btn.nova-tab-active',
		]);
		$this->add_group_control(Group_Control_Border::get_type(), [
			'name' => 'tab_border_active',
			'selector' => '{{WRAPPER}} .nova-tab-btn.nova-tab-active',
		]);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control('tab_static_border_heading', [
			'label' => esc_html__('Bordure inférieure (statique)', 'NOVA-addons'),
			'type' => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_responsive_control('tab_static_border_width', [
			'label' => esc_html__('Épaisseur bordure', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px'],
			'range' => ['px' => ['min' => 0, 'max' => 10]],
			'default' => ['size' => 1, 'unit' => 'px'],
			'selectors' => [
				'{{WRAPPER}} .nova-tab-btn' => '--nova-tab-border-width: {{SIZE}}{{UNIT}}; border-bottom-width: {{SIZE}}{{UNIT}} !important; border-bottom-style: solid;',
			],
		]);

		$this->add_control('tab_static_border_color', [
			'label' => esc_html__('Couleur bordure', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'default' => '#e1e1e1',
			'selectors' => [
				'{{WRAPPER}} .nova-tab-btn' => 'border-bottom-color: {{VALUE}} !important;',
			],
		]);

		$this->end_controls_section();

		// ── STYLE CONTENT ────────────────────────────────────────
		$this->start_controls_section('section_style_content', [
			'label' => esc_html__('Contenu', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'content_bg',
			'selector' => '{{WRAPPER}} .nova-tab-panel',
		]);

		$this->add_responsive_control('content_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('content_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_group_control(Group_Control_Box_Shadow::get_type(), [
			'name' => 'content_shadow',
			'selector' => '{{WRAPPER}} .nova-tab-panel',
		]);

		$this->add_responsive_control('content_gap', [
			'label' => esc_html__('Espacement entre éléments', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', 'em'],
			'range' => ['px' => ['min' => 0, 'max' => 80]],
			'selectors' => ['{{WRAPPER}} .nova-tab-panel-inner' => 'gap: {{SIZE}}{{UNIT}};'],
		]);

		$this->add_responsive_control('content_margin_top', [
			'label' => esc_html__('Marge haut (entre tabs et contenu)', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', 'em'],
			'range' => ['px' => ['min' => 0, 'max' => 100]],
			'default' => ['size' => 24, 'unit' => 'px'],
			'selectors' => ['{{WRAPPER}} .nova-tabs-content' => 'margin-top: {{SIZE}}{{UNIT}};'],
		]);

		$this->end_controls_section();

		// ── STYLE COLONNE GAUCHE ─────────────────────────────────
		$this->start_controls_section('section_style_col_left', [
			'label' => esc_html__('Colonne Gauche', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
			'condition' => ['panel_layout_type' => '2_col'],
		]);
		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'col_left_bg',
			'selector' => '{{WRAPPER}} .nova-tab-col-left',
		]);
		$this->add_responsive_control('col_left_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-col-left' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);
		$this->add_responsive_control('col_left_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-col-left' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);
		$this->add_group_control(Group_Control_Border::get_type(), [
			'name' => 'col_left_border',
			'selector' => '{{WRAPPER}} .nova-tab-col-left',
		]);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), [
			'name' => 'col_left_shadow',
			'selector' => '{{WRAPPER}} .nova-tab-col-left',
		]);
		$this->end_controls_section();

		// ── STYLE COLONNE DROITE ─────────────────────────────────
		$this->start_controls_section('section_style_col_right', [
			'label' => esc_html__('Colonne Droite', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
			'condition' => ['panel_layout_type' => '2_col'],
		]);
		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'col_right_bg',
			'selector' => '{{WRAPPER}} .nova-tab-col-right',
		]);
		$this->add_responsive_control('col_right_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-col-right' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);
		$this->add_responsive_control('col_right_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-col-right' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);
		$this->add_group_control(Group_Control_Border::get_type(), [
			'name' => 'col_right_border',
			'selector' => '{{WRAPPER}} .nova-tab-col-right',
		]);
		$this->add_group_control(Group_Control_Box_Shadow::get_type(), [
			'name' => 'col_right_shadow',
			'selector' => '{{WRAPPER}} .nova-tab-col-right',
		]);
		$this->end_controls_section();

		// ── STYLE TEXTE 1 ────────────────────────────────────────
		$this->start_controls_section('section_style_text1', [
			'label' => esc_html__('Texte 1', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(Group_Control_Typography::get_type(), [
			'name' => 'text1_typography',
			'selector' => '{{WRAPPER}} .nova-tab-content-text-1, {{WRAPPER}} .nova-tab-content-text-1 *',
		]);

		$this->add_control('text1_color', [
			'label' => esc_html__('Couleur', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-1, {{WRAPPER}} .nova-tab-content-text-1 *' => 'color: {{VALUE}};'],
		]);

		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'text1_bg',
			'selector' => '{{WRAPPER}} .nova-tab-content-text-1',
		]);

		$this->add_responsive_control('text1_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-1' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('text1_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-1' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('text1_align', [
			'label' => esc_html__('Alignement', 'NOVA-addons'),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'left' => ['title' => 'Gauche', 'icon' => 'eicon-text-align-left'],
				'center' => ['title' => 'Centre', 'icon' => 'eicon-text-align-center'],
				'right' => ['title' => 'Droite', 'icon' => 'eicon-text-align-right'],
			],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-1' => 'text-align: {{VALUE}};'],
		]);

		$this->add_responsive_control('text1_margin', [
			'label' => esc_html__('Marge', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('text1_width', [
			'label' => esc_html__('Largeur', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['%' => ['min' => 10, 'max' => 100]],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-1' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;'],
		]);

		$this->end_controls_section();

		// ── STYLE TEXTE 2 ────────────────────────────────────────
		$this->start_controls_section('section_style_text2', [
			'label' => esc_html__('Texte 2', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(Group_Control_Typography::get_type(), [
			'name' => 'text2_typography',
			'selector' => '{{WRAPPER}} .nova-tab-content-text-2, {{WRAPPER}} .nova-tab-content-text-2 *',
		]);

		$this->add_control('text2_color', [
			'label' => esc_html__('Couleur', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-2, {{WRAPPER}} .nova-tab-content-text-2 *' => 'color: {{VALUE}};'],
		]);

		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'text2_bg',
			'selector' => '{{WRAPPER}} .nova-tab-content-text-2',
		]);

		$this->add_responsive_control('text2_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('text2_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-2' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('text2_align', [
			'label' => esc_html__('Alignement', 'NOVA-addons'),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'left' => ['title' => 'Gauche', 'icon' => 'eicon-text-align-left'],
				'center' => ['title' => 'Centre', 'icon' => 'eicon-text-align-center'],
				'right' => ['title' => 'Droite', 'icon' => 'eicon-text-align-right'],
			],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-2' => 'text-align: {{VALUE}};'],
		]);

		$this->add_responsive_control('text2_margin', [
			'label' => esc_html__('Marge', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('text2_width', [
			'label' => esc_html__('Largeur', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['%' => ['min' => 10, 'max' => 100]],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-text-2' => 'width: {{SIZE}}{{UNIT}}; flex-shrink: 0;'],
		]);

		$this->end_controls_section();

		// ── STYLE IMAGE ──────────────────────────────────────────
		$this->start_controls_section('section_style_image', [
			'label' => esc_html__('Images', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_responsive_control('images_columns', [
			'label'   => esc_html__('Colonnes', 'NOVA-addons'),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 6,
			'default' => 1,
			'selectors' => [
				'{{WRAPPER}} .nova-tab-content-image' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
			],
		]);

		$this->add_responsive_control('images_gap', [
			'label'      => esc_html__('Espacement entre images', 'NOVA-addons'),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => ['px', 'em'],
			'range'      => ['px' => ['min' => 0, 'max' => 80]],
			'default'    => ['size' => 12, 'unit' => 'px'],
			'selectors'  => [
				'{{WRAPPER}} .nova-tab-content-image' => 'gap: {{SIZE}}{{UNIT}};',
			],
		]);

		$this->add_responsive_control('image_width', [
			'label' => esc_html__('Largeur', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vw'],
			'range' => ['px' => ['min' => 0, 'max' => 1000]],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-image img' => 'width: {{SIZE}}{{UNIT}};'],
		]);

		$this->add_responsive_control('image_height', [
			'label' => esc_html__('Hauteur', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', '%', 'vh'],
			'range' => ['px' => ['min' => 0, 'max' => 1000]],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-image img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;'],
		]);

		$this->add_responsive_control('image_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_group_control(Group_Control_Box_Shadow::get_type(), [
			'name' => 'image_shadow',
			'selector' => '{{WRAPPER}} .nova-tab-content-image img',
		]);

		$this->add_responsive_control('image_margin', [
			'label' => esc_html__('Marge', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		// ── Style de chaque item (figure) ─────────────────────────
		$this->add_control('image_item_heading', [
			'label'     => esc_html__('Style de chaque item', 'NOVA-addons'),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_group_control(Group_Control_Background::get_type(), [
			'name'     => 'image_item_bg',
			'label'    => esc_html__('Arrière-plan', 'NOVA-addons'),
			'selector' => '{{WRAPPER}} .nova-tab-image-item',
		]);

		$this->add_responsive_control('image_item_padding', [
			'label'      => esc_html__('Padding', 'NOVA-addons'),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors'  => ['{{WRAPPER}} .nova-tab-image-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('image_item_border_radius', [
			'label'      => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors'  => ['{{WRAPPER}} .nova-tab-image-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_group_control(Group_Control_Border::get_type(), [
			'name'     => 'image_item_border',
			'selector' => '{{WRAPPER}} .nova-tab-image-item',
		]);

		$this->add_group_control(Group_Control_Box_Shadow::get_type(), [
			'name'     => 'image_item_shadow',
			'selector' => '{{WRAPPER}} .nova-tab-image-item',
		]);

		$this->add_responsive_control('image_item_align', [
			'label'   => esc_html__('Alignement du contenu', 'NOVA-addons'),
			'type'    => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => ['title' => 'Gauche',  'icon' => 'eicon-align-start-h'],
				'center'     => ['title' => 'Centre',  'icon' => 'eicon-align-center-h'],
				'flex-end'   => ['title' => 'Droite',  'icon' => 'eicon-align-end-h'],
			],
			'selectors' => ['{{WRAPPER}} .nova-tab-image-item' => 'align-items: {{VALUE}};'],
		]);

		// ── Flex ──────────────────────────────────────────────────
		$this->add_control('image_item_flex_heading', [
			'label'     => esc_html__('Flex', 'NOVA-addons'),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_responsive_control('image_item_direction', [
			'label'   => esc_html__('Direction', 'NOVA-addons'),
			'type'    => Controls_Manager::CHOOSE,
			'options' => [
				'column'      => ['title' => esc_html__('Vertical', 'NOVA-addons'),           'icon' => 'eicon-arrow-down'],
				'row'         => ['title' => esc_html__('Horizontal', 'NOVA-addons'),          'icon' => 'eicon-arrow-right'],
				'column-reverse' => ['title' => esc_html__('Vertical inversé', 'NOVA-addons'), 'icon' => 'eicon-arrow-up'],
				'row-reverse' => ['title' => esc_html__('Horizontal inversé', 'NOVA-addons'),  'icon' => 'eicon-arrow-left'],
			],
			'default'   => 'column',
			'selectors' => ['{{WRAPPER}} .nova-tab-image-item' => 'flex-direction: {{VALUE}};'],
		]);

		$this->add_responsive_control('image_item_justify', [
			'label'   => esc_html__('Justify Content', 'NOVA-addons'),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'flex-start'    => 'flex-start',
				'center'        => 'center',
				'flex-end'      => 'flex-end',
				'space-between' => 'space-between',
				'space-around'  => 'space-around',
				'space-evenly'  => 'space-evenly',
			],
			'selectors' => ['{{WRAPPER}} .nova-tab-image-item' => 'justify-content: {{VALUE}};'],
		]);

		$this->add_responsive_control('image_item_align_items', [
			'label'   => esc_html__('Align Items', 'NOVA-addons'),
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'flex-start' => 'flex-start',
				'center'     => 'center',
				'flex-end'   => 'flex-end',
				'stretch'    => 'stretch',
				'baseline'   => 'baseline',
			],
			'selectors' => ['{{WRAPPER}} .nova-tab-image-item' => 'align-items: {{VALUE}};'],
		]);

		$this->add_responsive_control('image_item_gap', [
			'label'      => esc_html__('Gap (entre image et titre)', 'NOVA-addons'),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => ['px', 'em'],
			'range'      => ['px' => ['min' => 0, 'max' => 80]],
			'selectors'  => ['{{WRAPPER}} .nova-tab-image-item' => 'gap: {{SIZE}}{{UNIT}};'],
		]);

		// ── Position du caption ───────────────────────────────────
		$this->add_control('image_caption_position_heading', [
			'label'     => esc_html__('Position du titre/caption', 'NOVA-addons'),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_control('show_image_caption', [
			'label'        => esc_html__('Afficher la légende', 'NOVA-addons'),
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__('Oui', 'NOVA-addons'),
			'label_off'    => esc_html__('Non', 'NOVA-addons'),
			'return_value' => 'yes',
			'default'      => 'yes',
		]);

		$this->add_control('image_caption_position', [
			'label'   => esc_html__('Emplacement', 'NOVA-addons'),
			'type'    => Controls_Manager::CHOOSE,
			'options' => [
				'before' => ['title' => esc_html__('Avant l\'image', 'NOVA-addons'), 'icon' => 'eicon-arrow-up'],
				'after'  => ['title' => esc_html__('Après l\'image', 'NOVA-addons'),  'icon' => 'eicon-arrow-down'],
			],
			'default' => 'after',
			'condition' => [
				'show_image_caption' => 'yes',
			],
		]);

		$this->add_control('image_caption_heading', [
			'label'     => esc_html__('Titre / Caption', 'NOVA-addons'),
			'type'      => Controls_Manager::HEADING,
			'separator' => 'before',
		]);

		$this->add_control('image_caption_note', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__('Le titre affiché sous chaque image est lu depuis le champ "Légende" (ou "Titre") de la médiathèque WordPress.', 'NOVA-addons'),
			'content_classes' => 'elementor-descriptor',
		]);

		$this->add_control('image_caption_tag', [
			'label'   => esc_html__('Balise HTML', 'NOVA-addons'),
			'type'    => Controls_Manager::SELECT,
			'default' => 'figcaption',
			'options' => [
				'figcaption' => 'figcaption',
				'p'          => 'p',
				'span'       => 'span',
				'h1'         => 'h1',
				'h2'         => 'h2',
				'h3'         => 'h3',
				'h4'         => 'h4',
				'h5'         => 'h5',
				'h6'         => 'h6',
				'div'        => 'div',
			],
		]);

		$this->add_group_control(Group_Control_Typography::get_type(), [
			'name'     => 'image_caption_typography',
			'selector' => '{{WRAPPER}} .nova-tab-image-caption',
		]);

		$this->add_control('image_caption_color', [
			'label'     => esc_html__('Couleur', 'NOVA-addons'),
			'type'      => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-image-caption' => 'color: {{VALUE}};'],
		]);

		$this->add_responsive_control('image_caption_align', [
			'label'   => esc_html__('Alignement', 'NOVA-addons'),
			'type'    => Controls_Manager::CHOOSE,
			'options' => [
				'left'   => ['title' => 'Gauche', 'icon' => 'eicon-text-align-left'],
				'center' => ['title' => 'Centre', 'icon' => 'eicon-text-align-center'],
				'right'  => ['title' => 'Droite', 'icon' => 'eicon-text-align-right'],
			],
			'selectors' => ['{{WRAPPER}} .nova-tab-image-caption' => 'text-align: {{VALUE}};'],
		]);

		$this->add_responsive_control('image_caption_margin', [
			'label'      => esc_html__('Marge', 'NOVA-addons'),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors'  => ['{{WRAPPER}} .nova-tab-image-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->end_controls_section();

		// ── STYLE ICÔNE CONTENU ──────────────────────────────────
		$this->start_controls_section('section_style_content_icon', [
			'label' => esc_html__('Icône du contenu', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_responsive_control('content_icon_size', [
			'label' => esc_html__('Taille', 'NOVA-addons'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px', 'em'],
			'range' => ['px' => ['min' => 8, 'max' => 200]],
			'selectors' => [
				'{{WRAPPER}} .nova-tab-content-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .nova-tab-content-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			],
		]);

		$this->add_control('content_icon_color', [
			'label' => esc_html__('Couleur', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .nova-tab-content-icon' => 'color: {{VALUE}};',
				'{{WRAPPER}} .nova-tab-content-icon svg' => 'fill: {{VALUE}};',
			],
		]);

		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'content_icon_bg',
			'selector' => '{{WRAPPER}} .nova-tab-content-icon',
		]);

		$this->add_responsive_control('content_icon_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('content_icon_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('content_icon_margin', [
			'label' => esc_html__('Marge', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->end_controls_section();

		// ── STYLE LIEN ───────────────────────────────────────────
		$this->start_controls_section('section_style_link', [
			'label' => esc_html__('Lien', 'NOVA-addons'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(Group_Control_Typography::get_type(), [
			'name' => 'link_typography',
			'selector' => '{{WRAPPER}} .nova-tab-content-link a',
		]);

		$this->start_controls_tabs('link_tabs');

		$this->start_controls_tab('link_normal', ['label' => esc_html__('Normal', 'NOVA-addons')]);
		$this->add_control('link_color', [
			'label' => esc_html__('Couleur', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-content-link a' => 'color: {{VALUE}};'],
		]);
		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'link_bg',
			'selector' => '{{WRAPPER}} .nova-tab-content-link a',
		]);
		$this->add_group_control(Group_Control_Border::get_type(), [
			'name' => 'link_border',
			'selector' => '{{WRAPPER}} .nova-tab-content-link a',
		]);
		$this->end_controls_tab();

		$this->start_controls_tab('link_hover', ['label' => esc_html__('Hover', 'NOVA-addons')]);
		$this->add_control('link_color_hover', [
			'label' => esc_html__('Couleur', 'NOVA-addons'),
			'type' => Controls_Manager::COLOR,
			'selectors' => ['{{WRAPPER}} .nova-tab-content-link a:hover' => 'color: {{VALUE}};'],
		]);
		$this->add_group_control(Group_Control_Background::get_type(), [
			'name' => 'link_bg_hover',
			'selector' => '{{WRAPPER}} .nova-tab-content-link a:hover',
		]);
		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control('link_padding', [
			'label' => esc_html__('Padding', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'separator' => 'before',
			'selectors' => ['{{WRAPPER}} .nova-tab-content-link a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('link_border_radius', [
			'label' => esc_html__('Rayon de bordure', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-link a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->add_responsive_control('link_margin', [
			'label' => esc_html__('Marge', 'NOVA-addons'),
			'type' => Controls_Manager::DIMENSIONS,
			'size_units' => ['px', 'em', '%'],
			'selectors' => ['{{WRAPPER}} .nova-tab-content-link' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
		]);

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$tabs = $settings['tabs'];
		$active = max(1, (int) $settings['active_tab']) - 1;
		$widget_id = $this->get_id();

		if (empty($tabs))
			return;

		$autoplay = $settings['autoplay'] === 'yes';
		$autoplay_speed = !empty($settings['autoplay_speed']) ? $settings['autoplay_speed'] : 5000;

		$content_fade = isset($settings['content_fade']) && $settings['content_fade'] === 'yes';
		$content_fade_trigger = $content_fade ? (in_array(($settings['content_fade_trigger'] ?? 'both'), ['load', 'scroll', 'both'], true) ? $settings['content_fade_trigger'] : 'both') : '';
		$content_fade_replay = $content_fade && isset($settings['content_fade_replay']) && $settings['content_fade_replay'] === 'yes';
		$content_fade_duration = $content_fade ? max(100, min(4000, (int) ($settings['content_fade_duration'] ?? 700))) : 0;
		$content_fade_delay = $content_fade ? max(0, min(3000, (int) ($settings['content_fade_delay'] ?? 0))) : 0;
		$content_fade_translate_y = $content_fade ? max(0, min(200, (int) ($settings['content_fade_translate_y'] ?? 20))) : 0;

		ob_start();
		?>
		<div class="nova-tabs-nav" role="tablist">
			<?php foreach ($tabs as $i => $tab):
				$is_active = ($i === $active);
				$tab_id = 'nova-tab-' . $widget_id . '-' . $i;
				?>
				<button class="nova-tab-btn<?php echo $is_active ? ' nova-tab-active' : ''; ?>" role="tab"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr($tab_id); ?>" data-tab="<?php echo esc_attr($i); ?>">
					<?php if (!empty($tab['tab_icon']['value'])): ?>
						<span class="nova-tab-btn-icon">
							<?php Icons_Manager::render_icon($tab['tab_icon'], ['aria-hidden' => 'true']); ?>
						</span>
					<?php endif; ?>
					<span class="nova-tab-btn-text"><?php echo esc_html($tab['tab_title']); ?></span>
					<?php if ($autoplay): ?>
						<span class="nova-tab-progress">
							<span class="nova-tab-progress-fill"></span>
						</span>
					<?php endif; ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php
		$tabs_nav_html = ob_get_clean();
		?>
		<div class="nova-tabs-widget<?php echo $content_fade ? ' nova-tabs--content-fade' : ''; ?>" id="nova-tabs-<?php echo esc_attr($widget_id); ?>"
			data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>"
			data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
			<?php if ($content_fade): ?>
			data-content-fade="1"
			data-content-fade-trigger="<?php echo esc_attr($content_fade_trigger); ?>"
			data-content-fade-replay="<?php echo $content_fade_replay ? '1' : '0'; ?>"
			data-content-fade-duration="<?php echo esc_attr((string) $content_fade_duration); ?>"
			data-content-fade-delay="<?php echo esc_attr((string) $content_fade_delay); ?>"
			data-content-fade-translate-y="<?php echo esc_attr((string) $content_fade_translate_y); ?>"
			style="--nova-tabs-fade-duration:<?php echo (int) $content_fade_duration; ?>ms;--nova-tabs-fade-delay:<?php echo (int) $content_fade_delay; ?>ms;--nova-tabs-fade-translate-y:<?php echo (int) $content_fade_translate_y; ?>px;"
			<?php endif; ?>>

			<div class="nova-tabs-content">
				<?php foreach ($tabs as $i => $tab):
					$is_active = ($i === $active);
					$tab_id = 'nova-tab-' . $widget_id . '-' . $i;
					$link_url = !empty($tab['content_link']['url']) ? $tab['content_link']['url'] : '';
					$link_target = !empty($tab['content_link']['is_external']) ? '_blank' : '_self';
					?>
					<div class="nova-tab-panel<?php echo $is_active ? ' nova-tab-panel-active' : ''; ?>"
						id="<?php echo esc_attr($tab_id); ?>" role="tabpanel"
						aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>">
						<div class="nova-tab-panel-inner" <?php echo ($settings['panel_layout_type'] === '2_col') ? 'style="display: flex; flex-direction: row;"' : ''; ?>>
							<?php
							$elements_html = [
								'tabs' => $tabs_nav_html,
								'fixed_text' => '',
								'icon' => '',
								'image' => '',
								'text1' => '',
								'text2' => '',
								'link' => '',
							];

							if ($settings['fixed_text_source'] === 'nova_title' && !empty($settings['selected_nova_title_widget_id'])) {
								$widget_id_title = $settings['selected_nova_title_widget_id'];
								$html = $this->render_nova_title_widget($widget_id_title);
								if (!empty($html)) {
									// Add the original elementor-element-ID class to preserve CSS targeting
									$html = '<div class="elementor-element elementor-element-' . esc_attr($widget_id_title) . ' elementor-widget elementor-widget-nova-title" data-id="' . esc_attr($widget_id_title) . '" data-element_type="widget" data-widget_type="nova-title.default"><div class="elementor-widget-container">' . $html . '</div></div>';
								}
								$elements_html['fixed_text'] = '<div class="nova-tabs-fixed-text">' . $html . '</div>';
							} else if ($settings['fixed_text_source'] === 'elementor_template' && !empty($settings['fixed_text_template'])) {
								$template_id = intval($settings['fixed_text_template']);
								if (class_exists('\Elementor\Plugin')) {
									$html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id);
									$elements_html['fixed_text'] = '<div class="nova-tabs-fixed-text">' . $html . '</div>';
								}
							} else if (!empty($settings['global_fixed_text'])) {
								$elements_html['fixed_text'] = '<div class="nova-tabs-fixed-text">' . wp_kses_post($settings['global_fixed_text']) . '</div>';
							}

							if (!empty($tab['content_icon']['value'])) {
								ob_start();
								Icons_Manager::render_icon($tab['content_icon'], ['aria-hidden' => 'true']);
								$icon_val = ob_get_clean();
								$elements_html['icon'] = '<div class="nova-tab-content-icon">' . $icon_val . '</div>';
							}

							if (!empty($tab['content_image'])) {
								$imgs_html = '';
								$show_caption = ! empty( $settings['show_image_caption'] ) && 'yes' === $settings['show_image_caption'];
								$caption_tag = ! empty( $settings['image_caption_tag'] ) ? $settings['image_caption_tag'] : 'figcaption';
								// Whitelist pour éviter toute injection
								$allowed_tags = [ 'figcaption', 'p', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ];
								if ( ! in_array( $caption_tag, $allowed_tags, true ) ) {
									$caption_tag = 'figcaption';
								}
								$caption_position = ! empty( $settings['image_caption_position'] ) ? $settings['image_caption_position'] : 'after';

								foreach ( $tab['content_image'] as $img ) {
									if ( ! empty( $img['url'] ) ) {
										$caption = '';
										if ( $show_caption && ! empty( $img['id'] ) ) {
											$attachment = get_post( $img['id'] );
											if ( $attachment ) {
												$caption = trim( $attachment->post_excerpt );
												if ( empty( $caption ) ) {
													$caption = trim( $attachment->post_title );
												}
											}
										}

										$caption_html = ( $show_caption && ! empty( $caption ) )
											? '<' . $caption_tag . ' class="nova-tab-image-caption">' . esc_html( $caption ) . '</' . $caption_tag . '>'
											: '';

										$img_html = '<img src="' . esc_url( $img['url'] ) . '" alt="' . esc_attr( $caption ) . '">';

										$imgs_html .= '<figure class="nova-tab-image-item">';
										if ( $show_caption && $caption_position === 'before' ) {
											$imgs_html .= $caption_html . $img_html;
										} else {
											$imgs_html .= $img_html . $caption_html;
										}
										$imgs_html .= '</figure>';
									}
								}
								if ( $imgs_html ) {
									$elements_html['image'] = '<div class="nova-tab-content-image">' . $imgs_html . '</div>';
								}
							}

							if (!empty($tab['content_text_1'])) {
								$elements_html['text1'] = '<div class="nova-tab-content-text-1">' . wp_kses_post($tab['content_text_1']) . '</div>';
							}

							if (!empty($tab['content_text_2'])) {
								$elements_html['text2'] = '<div class="nova-tab-content-text-2">' . wp_kses_post($tab['content_text_2']) . '</div>';
							}

							if ($link_url && !empty($tab['content_link_text'])) {
								$elements_html['link'] = '<div class="nova-tab-content-link"><a href="' . esc_url($link_url) . '" target="' . esc_attr($link_target) . '">' . esc_html($tab['content_link_text']) . '</a></div>';
							}

							if ($settings['panel_layout_type'] === '2_col') {
								$left_html = '';
								$right_html = '';

								foreach (['tabs', 'fixed_text', 'icon', 'image', 'text1', 'text2', 'link'] as $key) {
									if (empty($elements_html[$key]))
										continue;

									$col = isset($settings["{$key}_col"]) ? $settings["{$key}_col"] : 'left';
									if ($col === 'right') {
										$right_html .= $elements_html[$key];
									} else {
										$left_html .= $elements_html[$key];
									}
								}

								echo '<div class="nova-tab-col-left" style="display:flex; flex-direction:column; flex-grow:1;">' . $left_html . '</div>';
								echo '<div class="nova-tab-col-right" style="display:flex; flex-direction:column; flex-grow:1;">' . $right_html . '</div>';
							} else {
								foreach ($elements_html as $html) {
									echo $html;
								}
							}
							?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
		<?php
		$selected_nova_title_widget_id = !empty($settings['selected_nova_title_widget_id']) ? $settings['selected_nova_title_widget_id'] : '';
		if ($settings['fixed_text_source'] === 'nova_title' && !empty($selected_nova_title_widget_id)): ?>
			<style>
				/* Hide the selected NOVA Title widget from its original position */
				.elementor-element-<?php echo esc_attr($selected_nova_title_widget_id); ?>,
				.elementor-element[data-id="<?php echo esc_attr($selected_nova_title_widget_id); ?>"] {
					display: none !important;
					visibility: hidden !important;
					opacity: 0 !important;
					height: 0 !important;
					overflow: hidden !important;
				}

				.nova-tabs-fixed-text .elementor-element[data-id="<?php echo esc_attr($selected_nova_title_widget_id); ?>"] {
					display: block !important;
					visibility: visible !important;
					opacity: 1 !important;
					height: auto !important;
					overflow: visible !important;
				}
			</style>
			<script>
				(function ($) {
					'use strict';
					function initInjectedNovaTitleTabs() {
						var $wrapper = $('#nova-tabs-<?php echo esc_js($widget_id); ?>');
						var $injectedWidget = $wrapper.find('.nova-tabs-fixed-text .elementor-widget-nova-title[data-id="<?php echo esc_js($selected_nova_title_widget_id); ?>"] .nova-title-widget');

						if ($injectedWidget.length > 0 && typeof window.NOVATitle !== 'undefined') {
							$injectedWidget.data('nova-title-initialized', false);
							window.NOVATitle.initInstance($injectedWidget);

							if (window.NOVATitle.initStyledWordsAnimations) {
								var $widgetContainer = $injectedWidget.closest('.elementor-widget-nova-title');
								if ($widgetContainer.length) {
									window.NOVATitle.initStyledWordsAnimations($widgetContainer);
								}
							}
						}
					}

					$(document).ready(function () {
						setTimeout(initInjectedNovaTitleTabs, 100);
						setTimeout(initInjectedNovaTitleTabs, 500);
						$(window).on('load', function () {
							setTimeout(initInjectedNovaTitleTabs, 200);
						});
					});

					if (typeof elementorFrontend !== 'undefined') {
						$(window).on('elementor/frontend/init', function () {
							setTimeout(initInjectedNovaTitleTabs, 200);
						});
					}
				})(jQuery);
			</script>
		<?php endif; ?>
	<?php
	}
}
