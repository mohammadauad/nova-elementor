<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Repeater;
use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Features - Colonnes avec icônes, titres et descriptions
 */
class Features_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-features';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Features', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-info-box';
	}

	/**
	 * Récupère les catégories du widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'NOVA-addons' ];
	}

	/**
	 * Récupère les dépendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-features-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-features-script' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// Section Titre Principal
		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__( 'Titre Principal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => esc_html__( 'Afficher le titre', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => esc_html__( 'Texte du titre', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez le titre', 'NOVA-addons' ),
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Features
		$this->start_controls_section(
			'section_features',
			[
				'label' => esc_html__( 'Features', 'NOVA-addons' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'feature_icon_type',
			[
				'label' => esc_html__( 'Type d\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options' => [
					'icon' => [
						'title' => esc_html__( 'Icône', 'NOVA-addons' ),
						'icon'  => 'eicon-star',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'NOVA-addons' ),
						'icon'  => 'eicon-image-bold',
					],
					'none' => [
						'title' => esc_html__( 'Aucun', 'NOVA-addons' ),
						'icon'  => 'eicon-ban',
					],
				],
				'default' => 'icon',
			]
		);

		$repeater->add_control(
			'feature_icon',
			[
				'label' => esc_html__( 'Icône (bibliothèque)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'condition' => [
					'feature_icon_type' => 'icon',
				],
				'label_block' => true,
				'description' => esc_html__( 'Icône issue de la bibliothèque (Font Awesome, SVG).', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'feature_icon_image',
			[
				'label' => esc_html__( 'Image', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'media_types' => [ 'image', 'svg' ],
				'button_text' => esc_html__( 'Choisir une image', 'NOVA-addons' ),
				'description' => esc_html__( 'Formats acceptés : JPG, PNG, WEBP, AVIF, ICO, SVG…', 'NOVA-addons' ),
				'condition' => [
					'feature_icon_type' => 'image',
				],
				'default' => [
					'url' => '',
				],
			]
		);

		$repeater->add_control(
			'feature_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez le titre', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'feature_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez la description', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'feature_link',
			[
				'label' => esc_html__( 'Lien', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://votre-lien.com', 'NOVA-addons' ),
				'show_external' => true,
				'default' => [
					'url' => '',
					'is_external' => false,
					'nofollow' => false,
				],
			]
		);

		$this->add_control(
			'features_list',
			[
				'label' => esc_html__( 'Liste des features', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'feature_title' => '',
						'feature_description' => '',
					],
					[
						'feature_title' => '',
						'feature_description' => '',
					],
					[
						'feature_title' => '',
						'feature_description' => '',
					],
					[
						'feature_title' => '',
						'feature_description' => '',
					],
				],
				'title_field' => '{{{ feature_title }}}',
			]
		);

		$this->end_controls_section();

		// Section Layout
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '4',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'desktop_default' => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
					],
				],
				'default' => [
					'size' => 30,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-features-grid' => 'gap: {{SIZE}}{{UNIT}}; --grid-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'columns_separator',
			[
				'label' => esc_html__( 'Séparateur entre colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		// Section Animation
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_enable',
			[
				'label' => esc_html__( 'Activer l\'animation', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'animation_simultaneous',
			[
				'label' => esc_html__( 'Animation simultanée', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non (Cascade)', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_delay',
			[
				'label' => esc_html__( 'Délai initial (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 100,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_stagger',
			[
				'label' => esc_html__( 'Délai entre items (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 100,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'description' => esc_html__( 'Temps entre chaque animation d\'item (si cascade activée)', 'NOVA-addons' ),
				'condition' => [
					'animation_enable' => 'yes',
					'animation_simultaneous' => '',
				],
			]
		);

		$this->add_control(
			'animation_translate_y',
			[
				'label' => esc_html__( 'Translation Y (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 30,
				'min' => 0,
				'max' => 9999,
				'step' => 5,
				'description' => esc_html__( 'Distance de translation verticale au démarrage de l\'animation', 'NOVA-addons' ),
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_duration',
			[
				'label' => esc_html__( 'Durée (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 800,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Container
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-features-widget' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_align_horizontal',
			[
				'label' => esc_html__( 'Alignement horizontal', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .nova-features-widget' => '{{VALUE}}',
				],
				'selectors_dictionary' => [
					'left' => 'margin-left: 0 !important; margin-right: auto !important;',
					'center' => 'margin-left: auto !important; margin-right: auto !important;',
					'right' => 'margin-left: auto !important; margin-right: 0 !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-features-widget',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'selector' => '{{WRAPPER}} .nova-features-widget',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .nova-features-widget',
			]
		);

		$this->end_controls_section();

		// Section Style - Titre Principal
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Titre Principal', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-features-title, {{WRAPPER}} .nova-features-title h1, {{WRAPPER}} .nova-features-title h2, {{WRAPPER}} .nova-features-title h3, {{WRAPPER}} .nova-features-title h4, {{WRAPPER}} .nova-features-title h5, {{WRAPPER}} .nova-features-title h6' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'title_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-features-title',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .nova-features-title, {{WRAPPER}} .nova-features-title h1, {{WRAPPER}} .nova-features-title h2, {{WRAPPER}} .nova-features-title h3, {{WRAPPER}} .nova-features-title h4, {{WRAPPER}} .nova-features-title h5, {{WRAPPER}} .nova-features-title h6',
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-title, {{WRAPPER}} .nova-features-title h1, {{WRAPPER}} .nova-features-title h2, {{WRAPPER}} .nova-features-title h3, {{WRAPPER}} .nova-features-title h4, {{WRAPPER}} .nova-features-title h5, {{WRAPPER}} .nova-features-title h6' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_align',
			[
				'label' => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-features-title, {{WRAPPER}} .nova-features-title h1, {{WRAPPER}} .nova-features-title h2, {{WRAPPER}} .nova-features-title h3, {{WRAPPER}} .nova-features-title h4, {{WRAPPER}} .nova-features-title h5, {{WRAPPER}} .nova-features-title h6' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'title_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'selectors' => [
					'{{WRAPPER}} .nova-features-title, {{WRAPPER}} .nova-features-title h1, {{WRAPPER}} .nova-features-title h2, {{WRAPPER}} .nova-features-title h3, {{WRAPPER}} .nova-features-title h4, {{WRAPPER}} .nova-features-title h5, {{WRAPPER}} .nova-features-title h6' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'title_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1200,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'condition' => [
					'title_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-features-title, {{WRAPPER}} .nova-features-title h1, {{WRAPPER}} .nova-features-title h2, {{WRAPPER}} .nova-features-title h3, {{WRAPPER}} .nova-features-title h4, {{WRAPPER}} .nova-features-title h5, {{WRAPPER}} .nova-features-title h6' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Grid
		$this->start_controls_section(
			'section_style_grid',
			[
				'label' => esc_html__( 'Grid', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
					],
				],
				'default' => [
					'size' => 30,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-features-grid' => 'gap: {{SIZE}}{{UNIT}}; --grid-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'grid_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-features-grid' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'grid_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-grid' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'grid_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-grid' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'grid_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-features-grid',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'grid_border',
				'selector' => '{{WRAPPER}} .nova-features-grid',
			]
		);

		$this->add_control(
			'grid_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-features-grid' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'grid_box_shadow',
				'selector' => '{{WRAPPER}} .nova-features-grid',
			]
		);

		$this->end_controls_section();

		// Section Style - Feature Item
		$this->start_controls_section(
			'section_style_feature',
			[
				'label' => esc_html__( 'Feature Item', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'feature_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_align',
			[
				'label' => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_display',
			[
				'label' => esc_html__( 'Display', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => 'flex',
					'block' => 'block',
					'inline-block' => 'inline-block',
					'grid' => 'grid',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_flex_direction',
			[
				'label' => esc_html__( 'Direction Flex', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'column' => [
						'title' => esc_html__( 'Colonne', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'row' => [
						'title' => esc_html__( 'Ligne', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
				],
				'default' => 'column',
				'condition' => [
					'feature_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Espace entre', 'NOVA-addons' ),
					'space-around' => esc_html__( 'Espace autour', 'NOVA-addons' ),
					'space-evenly' => esc_html__( 'Espace égal', 'NOVA-addons' ),
				],
				'condition' => [
					'feature_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Étirer', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Ligne de base', 'NOVA-addons' ),
				],
				'condition' => [
					'feature_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'feature_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item .nova-feature-icon, {{WRAPPER}} .nova-feature-item .nova-feature-title, {{WRAPPER}} .nova-feature-item .nova-feature-description' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'feature_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition' => [
					'feature_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item .nova-feature-icon, {{WRAPPER}} .nova-feature-item .nova-feature-title, {{WRAPPER}} .nova-feature-item .nova-feature-description' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'feature_border',
				'selector' => '{{WRAPPER}} .nova-feature-item',
			]
		);

		$this->add_responsive_control(
			'feature_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default' => [
					'top' => '',
					'right' => '',
					'bottom' => '',
					'left' => '',
					'unit' => 'px',
					'isLinked' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'feature_box_shadow',
				'selector' => '{{WRAPPER}} .nova-feature-item',
			]
		);

		$this->start_controls_tabs( 'tabs_feature_style' );

		$this->start_controls_tab(
			'tab_feature_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'feature_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-feature-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_feature_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'feature_background_hover',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-feature-item:hover',
			]
		);

		$this->add_control(
			'feature_hover_transition',
			[
				'label' => esc_html__( 'Transition (s)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range' => [
					's' => [
						'min' => 0,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.3,
					'unit' => 's',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item' => 'transition: all {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Section Style - Icône
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-feature-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 200,
					],
					'em' => [
						'min' => 0.5,
						'max' => 10,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 10,
					],
				],
				'default' => [
					'size' => 48,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-feature-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-feature-icon img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-icon' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'icon_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'condition' => [
					'icon_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-icon' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Titre Feature
		$this->start_controls_section(
			'section_style_feature_title',
			[
				'label' => esc_html__( 'Titre Feature', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'feature_title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-title, {{WRAPPER}} .nova-feature-title h1, {{WRAPPER}} .nova-feature-title h2, {{WRAPPER}} .nova-feature-title h3, {{WRAPPER}} .nova-feature-title h4, {{WRAPPER}} .nova-feature-title h5, {{WRAPPER}} .nova-feature-title h6' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'feature_title_typography',
				'selector' => '{{WRAPPER}} .nova-feature-title, {{WRAPPER}} .nova-feature-title h1, {{WRAPPER}} .nova-feature-title h2, {{WRAPPER}} .nova-feature-title h3, {{WRAPPER}} .nova-feature-title h4, {{WRAPPER}} .nova-feature-title h5, {{WRAPPER}} .nova-feature-title h6',
			]
		);

		$this->add_responsive_control(
			'feature_title_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-title, {{WRAPPER}} .nova-feature-title h1, {{WRAPPER}} .nova-feature-title h2, {{WRAPPER}} .nova-feature-title h3, {{WRAPPER}} .nova-feature-title h4, {{WRAPPER}} .nova-feature-title h5, {{WRAPPER}} .nova-feature-title h6' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'feature_title_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-title, {{WRAPPER}} .nova-feature-title h1, {{WRAPPER}} .nova-feature-title h2, {{WRAPPER}} .nova-feature-title h3, {{WRAPPER}} .nova-feature-title h4, {{WRAPPER}} .nova-feature-title h5, {{WRAPPER}} .nova-feature-title h6' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_control(
			'feature_title_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-title, {{WRAPPER}} .nova-feature-title h1, {{WRAPPER}} .nova-feature-title h2, {{WRAPPER}} .nova-feature-title h3, {{WRAPPER}} .nova-feature-title h4, {{WRAPPER}} .nova-feature-title h5, {{WRAPPER}} .nova-feature-title h6' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'feature_title_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition' => [
					'feature_title_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-title, {{WRAPPER}} .nova-feature-title h1, {{WRAPPER}} .nova-feature-title h2, {{WRAPPER}} .nova-feature-title h3, {{WRAPPER}} .nova-feature-title h4, {{WRAPPER}} .nova-feature-title h5, {{WRAPPER}} .nova-feature-title h6' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Description
		$this->start_controls_section(
			'section_style_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#a0d995',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-description' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .nova-feature-description',
			]
		);

		$this->add_responsive_control(
			'description_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'description_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_control(
			'description_max_width_auto',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-description' => 'max-width: none !important; width: auto !important;',
				],
			]
		);

		$this->add_responsive_control(
			'description_max_width',
			[
				'label' => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition' => [
					'description_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-description' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Séparateur
		$this->start_controls_section(
			'section_style_separator',
			[
				'label' => esc_html__( 'Séparateur', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item.has-separator::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'separator_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'default' => [
					'size' => 1,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-feature-item.has-separator::after' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['features_list'] ) ) {
			return;
		}

		$columns = isset( $settings['columns'] ) ? $settings['columns'] : '4';
		$columns_tablet = isset( $settings['columns_tablet'] ) && ! empty( $settings['columns_tablet'] ) ? $settings['columns_tablet'] : $columns;
		$columns_mobile = isset( $settings['columns_mobile'] ) && ! empty( $settings['columns_mobile'] ) ? $settings['columns_mobile'] : '1';
		$show_separator = $settings['columns_separator'] === 'yes';

		// Animation config
		$animation_config = [
			'enable' => isset( $settings['animation_enable'] ) && $settings['animation_enable'] === 'yes',
			'simultaneous' => isset( $settings['animation_simultaneous'] ) && $settings['animation_simultaneous'] === 'yes',
			'delay' => isset( $settings['animation_delay'] ) ? intval( $settings['animation_delay'] ) : 100,
			'duration' => isset( $settings['animation_duration'] ) ? intval( $settings['animation_duration'] ) : 800,
			'stagger' => isset( $settings['animation_stagger'] ) ? intval( $settings['animation_stagger'] ) : 100,
			'translateY' => isset( $settings['animation_translate_y'] ) ? intval( $settings['animation_translate_y'] ) : 30,
		];

		// Get gap value for separator positioning (priorité à grid_gap, sinon gap)
		$gap_value = 30;
		$gap_unit = 'px';
		
		// Vérifier grid_gap (responsive peut avoir _desktop, _tablet, _mobile)
		$gap_setting = null;
		
		// Essayer grid_gap d'abord
		if ( isset( $settings['grid_gap'] ) && is_array( $settings['grid_gap'] ) ) {
			$gap_setting = $settings['grid_gap'];
		} elseif ( isset( $settings['grid_gap']['size'] ) ) {
			// Si c'est directement un objet avec size
			$gap_setting = $settings['grid_gap'];
		}
		
		// Sinon essayer gap
		if ( ! $gap_setting ) {
			if ( isset( $settings['gap'] ) && is_array( $settings['gap'] ) ) {
				$gap_setting = $settings['gap'];
			} elseif ( isset( $settings['gap']['size'] ) ) {
				$gap_setting = $settings['gap'];
			}
		}
		
		if ( $gap_setting && is_array( $gap_setting ) ) {
			if ( isset( $gap_setting['size'] ) && $gap_setting['size'] !== '' && $gap_setting['size'] !== null ) {
				$gap_value = floatval( $gap_setting['size'] );
			}
			if ( isset( $gap_setting['unit'] ) && $gap_setting['unit'] !== '' && $gap_setting['unit'] !== null ) {
				$gap_unit = $gap_setting['unit'];
			}
		}
		
		// Debug: s'assurer qu'on a une valeur valide
		if ( $gap_value <= 0 ) {
			$gap_value = 30;
		}
		
		// Build inline styles for responsive columns
		$grid_style = sprintf(
			'--columns-desktop: %d; --columns-tablet: %d; --columns-mobile: %d; --grid-gap: %s%s;',
			esc_attr( $columns ),
			esc_attr( $columns_tablet ),
			esc_attr( $columns_mobile ),
			esc_attr( $gap_value ),
			esc_attr( $gap_unit )
		);

		?>
		<div class="nova-features-widget" 
			 data-animation-config="<?php echo esc_attr( wp_json_encode( $animation_config ) ); ?>">
			<?php if ( ! empty( $settings['show_title'] ) && $settings['show_title'] === 'yes' && ! empty( $settings['title_text'] ) ) : ?>
				<div class="nova-features-title"><?php echo wp_kses_post( $settings['title_text'] ); ?></div>
			<?php endif; ?>

			<div class="nova-features-grid" 
				 style="<?php echo esc_attr( $grid_style ); ?> grid-template-columns: repeat(var(--columns-desktop), 1fr);"
				 data-columns="<?php echo esc_attr( $columns ); ?>"
				 data-columns-tablet="<?php echo esc_attr( $columns_tablet ); ?>"
				 data-columns-mobile="<?php echo esc_attr( $columns_mobile ); ?>">
				<?php foreach ( $settings['features_list'] as $index => $item ) : ?>
					<?php
					$feature_link = isset( $item['feature_link'] ) ? $item['feature_link'] : [];
					$feature_url = ! empty( $feature_link['url'] ) ? esc_url( $feature_link['url'] ) : '';
					$feature_link_attrs = '';
					if ( ! empty( $feature_url ) ) {
						$target = ! empty( $feature_link['is_external'] ) ? ' target="_blank"' : '';
						$nofollow = ! empty( $feature_link['nofollow'] ) ? ' rel="nofollow"' : '';
						$feature_link_attrs = $target . $nofollow;
					}

				$icon_type = isset( $item['feature_icon_type'] ) ? $item['feature_icon_type'] : 'icon';
				$has_icon = $icon_type !== 'none';
				$is_last = ( $index + 1 ) === count( $settings['features_list'] );
				$show_sep = $show_separator && ! $is_last;
				$item_classes = [ 'nova-feature-item' ];
				if ( $show_sep ) {
					$item_classes[] = 'has-separator';
				}
				?>
				<div class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
						<?php if ( ! empty( $feature_url ) ) : ?>
							<a href="<?php echo esc_url( $feature_url ); ?>" <?php echo $feature_link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="nova-feature-link">
						<?php endif; ?>

						<?php if ( $has_icon ) : ?>
							<div class="nova-feature-icon">
								<?php
								if ( $icon_type === 'icon' && ! empty( $item['feature_icon']['value'] ) ) {
									Icons_Manager::render_icon( $item['feature_icon'], [ 'aria-hidden' => 'true' ] );
								} elseif ( $icon_type === 'image' && ! empty( $item['feature_icon_image']['url'] ) ) {
									echo '<img src="' . esc_url( $item['feature_icon_image']['url'] ) . '" alt="' . esc_attr( $item['feature_title'] ) . '" />';
								}
								?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $item['feature_title'] ) ) : ?>
							<div class="nova-feature-title"><?php echo wp_kses_post( $item['feature_title'] ); ?></div>
						<?php endif; ?>

						<?php if ( ! empty( $item['feature_description'] ) ) : ?>
							<div class="nova-feature-description"><?php echo wp_kses_post( $item['feature_description'] ); ?></div>
						<?php endif; ?>

					<?php if ( ! empty( $feature_url ) ) : ?>
						</a>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}

