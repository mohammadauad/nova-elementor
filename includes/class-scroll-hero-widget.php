<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget Nova Scroll Hero avec animation GSAP.
 */
class Scroll_Hero_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-scroll-hero';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Nova Scroll Hero', 'nova-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-animation';
	}

	/**
	 * Récupère les catégories du widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'nova-addons' ];
	}

	/**
	 * Récupère les dépendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-scroll-hero-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'gsap', 'gsap-scrolltrigger', 'nova-scroll-hero-script', 'nova-navbar-script' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// Section Contenu - Premier Texte (restauré)
		$this->start_controls_section(
			'section_first_text',
			[
				'label' => esc_html__( 'Premier Texte', 'nova-addons' ),
			]
		);

		$this->add_control(
			'first_text',
			[
				'label' => esc_html__( 'Contenu', 'nova-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Votre premier paragraphe ici...', 'nova-addons' ),
			]
		);

		$this->end_controls_section();

		// Section Style - Premier Texte (restauré)
		$this->start_controls_section(
			'section_style_first_text',
			[
				'label' => esc_html__( 'Style - Premier Texte', 'nova-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'first_text_typography',
				'selector' => '{{WRAPPER}} .nova-scroll-hero-text',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'first_text_background',
				'label' => esc_html__( 'Background', 'nova-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-scroll-hero-text',
			]
		);

		$this->add_control(
			'first_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'first_text_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 300, 'max' => 2000 ],
					'%'  => [ 'min' => 10,  'max' => 100 ],
				],
				'default' => [ 'size' => 1200, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-text' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'first_text_align',
			[
				'label' => esc_html__( 'Alignement', 'nova-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [ 'title' => esc_html__( 'Gauche', 'nova-addons' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Centre', 'nova-addons' ), 'icon' => 'eicon-text-align-center' ],
					'right' => [ 'title' => esc_html__( 'Droite', 'nova-addons' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default' => 'left',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'first_text_padding',
			[
				'label' => esc_html__( 'Padding', 'nova-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [ 'top' => '80', 'right' => '40', 'bottom' => '120', 'left' => '40', 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'first_text_margin_bottom',
			[
				'label' => esc_html__( 'Marge inférieure', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 400 ] ],
				'default' => [ 'size' => 120, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Contenu - Image
		$this->start_controls_section(
			'section_image',
			[
				'label' => esc_html__( 'Image', 'nova-addons' ),
			]
		);

		$this->add_control(
			'background_image',
			[
				'label' => esc_html__( 'Image de fond', 'nova-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		// Dimensions initiales (avant scroll)
		$this->add_control(
			'image_dimensions_initial_heading',
			[
				'label' => esc_html__( 'Dimensions Initiales (avant scroll)', 'nova-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'image_width_initial',
			[
				'label' => esc_html__( 'Largeur initiale', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', '%' ],
				'range' => [
					'px' => [
						'min' => 300,
						'max' => 2000,
						'step' => 1,
					],
					'vw' => [
						'min' => 30,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 30,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 1061,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-image-container' => 'width: {{SIZE}}{{UNIT}};',
				],
				'description' => esc_html__( 'Largeur de l\'image avant l\'animation du scroll (px, vw ou %)', 'nova-addons' ),
			]
		);

		$this->add_responsive_control(
			'image_height_initial',
			[
				'label' => esc_html__( 'Hauteur initiale', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', '%' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1500,
						'step' => 1,
					],
					'vh' => [
						'min' => 30,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 30,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 700,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-image-container' => 'height: {{SIZE}}{{UNIT}};',
				],
				'description' => esc_html__( 'Hauteur de l\'image avant l\'animation du scroll (px, vh ou %)', 'nova-addons' ),
			]
		);

		$this->add_responsive_control(
			'image_initial_scale',
			[
				'label'       => esc_html__( 'Échelle initiale (0.1 - 1)', 'nova-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0.1,
				'max'         => 1,
				'step'        => 0.05,
				'default'     => 0.7,
				'description' => esc_html__( 'Définit l\'échelle de départ de l\'image avant son expansion (comme Stacking Cards).', 'nova-addons' ),
			]
		);

		// Dimensions maximales (après animation)
		$this->add_control(
			'image_dimensions_max_heading',
			[
				'label' => esc_html__( 'Dimensions Maximales (après animation)', 'nova-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_width_max',
			[
				'label' => esc_html__( 'Largeur maximale', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', '%' ],
				'range' => [
					'px' => [
						'min' => 500,
						'max' => 4000,
						'step' => 1,
					],
					'vw' => [
						'min' => 50,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 50,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => 'vw',
				],
				'description' => esc_html__( 'Largeur maximale de l\'image après l\'animation (px, vw ou %)', 'nova-addons' ),
			]
		);

		$this->add_responsive_control(
			'image_height_max',
			[
				'label' => esc_html__( 'Hauteur maximale', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', '%' ],
				'range' => [
					'px' => [
						'min' => 300,
						'max' => 3000,
						'step' => 1,
					],
					'vh' => [
						'min' => 50,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 50,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => 'vh',
				],
				'description' => esc_html__( 'Hauteur maximale de l\'image après l\'animation (px, vh ou %)', 'nova-addons' ),
			]
		);

		$this->add_control(
			'image_lcp_priority',
			[
				'label' => esc_html__( 'Image LCP (Priorité haute)', 'nova-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'nova-addons' ),
				'label_off' => esc_html__( 'Non', 'nova-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Activez si cette image est le Largest Contentful Paint (LCP) de la page. Améliore les performances Core Web Vitals.', 'nova-addons' ),
			]
		);

		$this->end_controls_section();

		// Section Contenu - Contenu sur l'image
		$this->start_controls_section(
			'section_overlay_content',
			[
				'label' => esc_html__( 'Contenu sur l\'image', 'nova-addons' ),
			]
		);

		$this->add_control(
			'overlay_text_1',
			[
				'label' => esc_html__( 'Description 1', 'nova-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Première description...', 'nova-addons' ),
			]
		);

		$this->add_control(
			'overlay_text_2',
			[
				'label' => esc_html__( 'Description 2', 'nova-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Deuxième description...', 'nova-addons' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Texte du bouton', 'nova-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'En savoir plus', 'nova-addons' ),
			]
		);

		$this->add_control(
			'button_link',
			[
				'label' => esc_html__( 'Lien du bouton', 'nova-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://example.com', 'nova-addons' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Image Container
		$this->start_controls_section(
			'section_image_style',
			[
				'label' => esc_html__( 'Container Image', 'nova-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__( 'Hauteur', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range' => [
					'px' => [
						'min' => 300,
						'max' => 1000,
					],
					'vh' => [
						'min' => 30,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 600,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'nova-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px', 'isLinked' => false ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Contenu Overlay
		$this->start_controls_section(
			'section_overlay_style',
			[
				'label' => esc_html__( 'Contenu Overlay', 'nova-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Background Overlay
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'overlay_background',
				'label' => esc_html__( 'Background Overlay', 'nova-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-scroll-hero-overlay::before',
				'default' => [
					'background' => [
						'color' => 'rgba(0, 0, 0, 0.5)',
					],
				],
			]
		);

		$this->add_control(
			'overlay_background_opacity',
			[
				'label' => esc_html__( 'Opacité Background (%)', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 50,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay::before' => 'opacity: calc({{SIZE}} / 100);',
				],
			]
		);

		$this->add_control(
			'overlay_typography_heading',
			[
				'label' => esc_html__( 'Typographie', 'nova-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'overlay_text_typography',
				'label' => esc_html__( 'Typographie Générale', 'nova-addons' ),
				'selector' => '{{WRAPPER}} .nova-scroll-hero-overlay-text',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'overlay_text_1_typography',
				'label' => esc_html__( 'Typographie - Description 1', 'nova-addons' ),
				'selector' => '{{WRAPPER}} .nova-overlay-text-1',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'overlay_text_2_typography',
				'label' => esc_html__( 'Typographie - Description 2', 'nova-addons' ),
				'selector' => '{{WRAPPER}} .nova-overlay-text-2',
			]
		);

		$this->add_control(
			'overlay_colors_heading',
			[
				'label' => esc_html__( 'Couleurs', 'nova-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'overlay_text_color',
			[
				'label' => esc_html__( 'Couleur Texte Générale', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_text_1_color',
			[
				'label' => esc_html__( 'Couleur - Description 1', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-overlay-text-1' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_text_2_color',
			[
				'label' => esc_html__( 'Couleur - Description 2', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-overlay-text-2 *' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'overlay_padding',
			[
				'label' => esc_html__( 'Padding', 'nova-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px', 'isLinked' => false ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// ✅ NOUVEAU: Configuration du Layout pour overlay-content
		$this->add_control(
			'overlay_content_layout_heading',
			[
				'label' => esc_html__( 'Layout Overlay Content', 'nova-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		// Max Width pour Overlay
		$this->add_responsive_control(
			'overlay_max_width',
			[
				'label' => esc_html__( 'Max Width', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 2000,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Display Type
		$this->add_responsive_control(
			'overlay_content_display',
			[
				'label' => esc_html__( 'Display', 'nova-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'flex' => esc_html__( 'Flex', 'nova-addons' ),
					'block' => esc_html__( 'Block', 'nova-addons' ),
					'inline-flex' => esc_html__( 'Inline Flex', 'nova-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'display: {{VALUE}};',
				],
			]
		);

		// Flex Direction
		$this->add_responsive_control(
			'overlay_content_flex_direction',
			[
				'label' => esc_html__( 'Direction', 'nova-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'row',
				'options' => [
					'row' => esc_html__( 'Row (→)', 'nova-addons' ),
					'column' => esc_html__( 'Column (↓)', 'nova-addons' ),
					'row-reverse' => esc_html__( 'Row Reverse (←)', 'nova-addons' ),
					'column-reverse' => esc_html__( 'Column Reverse (↑)', 'nova-addons' ),
				],
				'condition' => [
					'overlay_content_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		// Justify Content (alignement horizontal)
		$this->add_responsive_control(
			'overlay_content_justify_content',
			[
				'label' => esc_html__( 'Justification', 'nova-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'nova-addons' ),
					'center' => esc_html__( 'Centre', 'nova-addons' ),
					'flex-end' => esc_html__( 'Fin', 'nova-addons' ),
					'space-between' => esc_html__( 'Espace entre', 'nova-addons' ),
					'space-around' => esc_html__( 'Espace autour', 'nova-addons' ),
					'space-evenly' => esc_html__( 'Espace égal', 'nova-addons' ),
				],
				'condition' => [
					'overlay_content_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		// Align Items (alignement vertical)
		$this->add_responsive_control(
			'overlay_content_align_items',
			[
				'label' => esc_html__( 'Alignement', 'nova-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'stretch',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'nova-addons' ),
					'center' => esc_html__( 'Centre', 'nova-addons' ),
					'flex-end' => esc_html__( 'Fin', 'nova-addons' ),
					'stretch' => esc_html__( 'Étirer', 'nova-addons' ),
					'baseline' => esc_html__( 'Baseline', 'nova-addons' ),
				],
				'condition' => [
					'overlay_content_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'align-items: {{VALUE}};',
				],
			]
		);

		// Gap (espacement entre éléments)
		$this->add_responsive_control(
			'overlay_content_gap',
			[
				'label' => esc_html__( 'Gap (Espacement)', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
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
					'size' => 20,
					'unit' => 'px',
				],
				'condition' => [
					'overlay_content_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Flex Wrap
		$this->add_responsive_control(
			'overlay_content_flex_wrap',
			[
				'label' => esc_html__( 'Retour à la ligne', 'nova-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'nowrap',
				'options' => [
					'nowrap' => esc_html__( 'Pas de retour', 'nova-addons' ),
					'wrap' => esc_html__( 'Retour', 'nova-addons' ),
					'wrap-reverse' => esc_html__( 'Retour inversé', 'nova-addons' ),
				],
				'condition' => [
					'overlay_content_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-overlay-content' => 'flex-wrap: {{VALUE}};',
				],
			]
		);

		// Max Width pour Texte 1
		$this->add_control(
			'overlay_text_1_max_width',
			[
				'label' => esc_html__( 'Max Width - Description 1', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1200,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-overlay-text-left' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Max Width pour Texte 2
		$this->add_control(
			'overlay_text_2_max_width',
			[
				'label' => esc_html__( 'Max Width - Description 2', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1200,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-overlay-text-right' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Bouton
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Bouton', 'nova-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .nova-scroll-hero-button',
			]
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab(
			'button_normal',
			[
				'label' => esc_html__( 'Normal', 'nova-addons' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#007bff',
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-button' => 'background: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_hover',
			[
				'label' => esc_html__( 'Hover', 'nova-addons' ),
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color_hover',
			[
				'label' => esc_html__( 'Couleur de fond', 'nova-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-button:hover' => 'background: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'nova-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default' => [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px', 'isLinked' => false ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'nova-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'unit' => 'px', 'isLinked' => false ],
				'selectors' => [
					'{{WRAPPER}} .nova-scroll-hero-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .nova-scroll-hero-button',
			]
		);

		$this->end_controls_section();

		// ✅ SECTION ANIMATION
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'nova-addons' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			]
		);

		// Scrub duration
		$this->add_control(
			'animation_scrub',
			[
				'label' => esc_html__( 'Animation Scrub (smoothness)', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1.5,
					'unit' => 'px',
				],
				'description' => esc_html__( 'Plus élevé = plus fluide (0 = no smoothing)', 'nova-addons' ),
			]
		);

		// Text animation delay
		$this->add_control(
			'animation_text_delay',
			[
				'label' => esc_html__( 'Text Animation Start (%)', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'description' => esc_html__( 'À quel pourcentage du scroll l\'animation du texte commence', 'nova-addons' ),
			]
		);

		// Image expand start
		$this->add_control(
			'animation_expand_start',
			[
				'label' => esc_html__( 'Image Expand Start (%)', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
				],
				'default' => [
					'size' => 5,
					'unit' => 'px',
				],
				'description' => esc_html__( 'À quel pourcentage du scroll l\'expansion de l\'image commence', 'nova-addons' ),
			]
		);

		// Overlay fade start
		$this->add_control(
			'animation_overlay_start',
			[
				'label' => esc_html__( 'Overlay Fade Start (%)', 'nova-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
				],
				'default' => [
					'size' => 70,
					'unit' => 'px',
				],
				'description' => esc_html__( 'À quel pourcentage du scroll le contenu overlay commence à s\'afficher', 'nova-addons' ),
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Affiche le widget sur le front-end.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$first_text    = isset( $settings['first_text'] ) ? $settings['first_text'] : '';
		$image_url     = isset( $settings['background_image']['url'] ) ? $settings['background_image']['url'] : '';
		$is_lcp        = isset( $settings['image_lcp_priority'] ) && $settings['image_lcp_priority'] === 'yes';
		$desc_text_1   = isset( $settings['overlay_text_1'] ) ? $settings['overlay_text_1'] : '';
		$desc_text_2   = isset( $settings['overlay_text_2'] ) ? $settings['overlay_text_2'] : '';
		$button_text   = isset( $settings['button_text'] ) ? $settings['button_text'] : 'Discover';
		$button_link   = isset( $settings['button_link']['url'] ) ? $settings['button_link']['url'] : '#';

		// Preload LCP image in head for better performance
		if ( $is_lcp && ! empty( $image_url ) ) {
			// Get full size image URL for preload (better for LCP)
			$preload_image_url = $image_url;
			if ( ! empty( $settings['background_image']['id'] ) ) {
				$full_image_url = wp_get_attachment_image_url( intval( $settings['background_image']['id'] ), 'full' );
				if ( $full_image_url ) {
					$preload_image_url = $full_image_url;
				}
			}
			
			// Check if image is external (needs crossorigin)
			$parsed_url = wp_parse_url( $preload_image_url );
			$home_parsed = wp_parse_url( home_url() );
			$home_host = isset( $home_parsed['host'] ) ? $home_parsed['host'] : '';
			$is_external = $parsed_url && isset( $parsed_url['host'] ) && $parsed_url['host'] !== $home_host;
			
			// Check if wp_head has already been executed
			$wp_head_done = did_action( 'wp_head' );
			
			if ( ! $wp_head_done ) {
				// wp_head not executed yet - add to wp_head action (high priority to run early)
				add_action( 'wp_head', function() use ( $preload_image_url, $is_external ) {
					// Output preload with high priority
					$crossorigin = $is_external ? ' crossorigin="anonymous"' : '';
					echo '<link rel="preload" as="image" href="' . esc_url( $preload_image_url ) . '" fetchpriority="high"' . $crossorigin . '>' . "\n";
				}, 1 );
				
				// Also add resource hint for DNS prefetch if external image
				if ( $is_external && isset( $parsed_url['host'] ) ) {
					add_action( 'wp_head', function() use ( $parsed_url ) {
						echo '<link rel="dns-prefetch" href="//' . esc_attr( $parsed_url['host'] ) . '">' . "\n";
					}, 0 );
				}
			} else {
				// wp_head already executed - output directly in current position (fallback)
				// Note: This won't be in <head> but still helps with priority loading
				$crossorigin = $is_external ? ' crossorigin="anonymous"' : '';
				echo '<link rel="preload" as="image" href="' . esc_url( $preload_image_url ) . '" fetchpriority="high"' . $crossorigin . '>' . "\n";
				
				// DNS prefetch for external images
				if ( $is_external && isset( $parsed_url['host'] ) ) {
					echo '<link rel="dns-prefetch" href="//' . esc_attr( $parsed_url['host'] ) . '">' . "\n";
				}
			}
		}

		// 1. Premier texte - au-dessus du scroll hero
		if ( ! empty( $first_text ) ) {
			echo '<div class="nova-scroll-hero-text">';
				echo wp_kses_post( $first_text );
			echo '</div>';
		}

		// 2. SCROLL HERO - Contient SEULEMENT l'image
		// Get image dimensions from settings
		// Dimensions initiales (avec unités comme stacking cards)
		$width_initial = isset( $settings['image_width_initial']['size'] ) ? floatval( $settings['image_width_initial']['size'] ) : 1061;
		$width_initial_unit = isset( $settings['image_width_initial']['unit'] ) ? $settings['image_width_initial']['unit'] : 'px';
		$width_initial_tablet = isset( $settings['image_width_initial_tablet']['size'] ) ? floatval( $settings['image_width_initial_tablet']['size'] ) : $width_initial;
		$width_initial_tablet_unit = isset( $settings['image_width_initial_tablet']['unit'] ) ? $settings['image_width_initial_tablet']['unit'] : $width_initial_unit;
		$width_initial_mobile = isset( $settings['image_width_initial_mobile']['size'] ) ? floatval( $settings['image_width_initial_mobile']['size'] ) : $width_initial;
		$width_initial_mobile_unit = isset( $settings['image_width_initial_mobile']['unit'] ) ? $settings['image_width_initial_mobile']['unit'] : $width_initial_unit;
		
		$height_initial = isset( $settings['image_height_initial']['size'] ) ? floatval( $settings['image_height_initial']['size'] ) : 700;
		$height_initial_unit = isset( $settings['image_height_initial']['unit'] ) ? $settings['image_height_initial']['unit'] : 'px';
		$height_initial_tablet = isset( $settings['image_height_initial_tablet']['size'] ) ? floatval( $settings['image_height_initial_tablet']['size'] ) : $height_initial;
		$height_initial_tablet_unit = isset( $settings['image_height_initial_tablet']['unit'] ) ? $settings['image_height_initial_tablet']['unit'] : $height_initial_unit;
		$height_initial_mobile = isset( $settings['image_height_initial_mobile']['size'] ) ? floatval( $settings['image_height_initial_mobile']['size'] ) : $height_initial;
		$height_initial_mobile_unit = isset( $settings['image_height_initial_mobile']['unit'] ) ? $settings['image_height_initial_mobile']['unit'] : $height_initial_unit;

		$initial_scale        = isset( $settings['image_initial_scale'] ) ? floatval( $settings['image_initial_scale'] ) : 0.7;
		$initial_scale        = ( $initial_scale > 0 && $initial_scale <= 1 ) ? $initial_scale : 0.7;
		$initial_scale_tablet = isset( $settings['image_initial_scale_tablet'] ) ? floatval( $settings['image_initial_scale_tablet'] ) : $initial_scale;
		$initial_scale_tablet = ( $initial_scale_tablet > 0 && $initial_scale_tablet <= 1 ) ? $initial_scale_tablet : $initial_scale;
		$initial_scale_mobile = isset( $settings['image_initial_scale_mobile'] ) ? floatval( $settings['image_initial_scale_mobile'] ) : $initial_scale;
		$initial_scale_mobile = ( $initial_scale_mobile > 0 && $initial_scale_mobile <= 1 ) ? $initial_scale_mobile : $initial_scale;
		
		// Dimensions maximales
		$width_max = isset( $settings['image_width_max']['size'] ) ? floatval( $settings['image_width_max']['size'] ) : 100;
		$width_max_unit = isset( $settings['image_width_max']['unit'] ) ? $settings['image_width_max']['unit'] : 'vw';
		// If value is 0 but unit is vw/vh/%, interpret as 100%
		if ( $width_max == 0 && ( $width_max_unit === 'vw' || $width_max_unit === 'vh' || $width_max_unit === '%' ) ) {
			$width_max = 100;
		}
		$width_max_tablet = isset( $settings['image_width_max_tablet']['size'] ) && floatval( $settings['image_width_max_tablet']['size'] ) > 0
			? floatval( $settings['image_width_max_tablet']['size'] ) : $width_max;
		$width_max_tablet_unit = isset( $settings['image_width_max_tablet']['size'] ) && floatval( $settings['image_width_max_tablet']['size'] ) > 0
			? ( isset( $settings['image_width_max_tablet']['unit'] ) ? $settings['image_width_max_tablet']['unit'] : $width_max_unit )
			: $width_max_unit;

		$width_max_mobile = isset( $settings['image_width_max_mobile']['size'] ) && floatval( $settings['image_width_max_mobile']['size'] ) > 0
			? floatval( $settings['image_width_max_mobile']['size'] ) : $width_max;
		$width_max_mobile_unit = isset( $settings['image_width_max_mobile']['size'] ) && floatval( $settings['image_width_max_mobile']['size'] ) > 0
			? ( isset( $settings['image_width_max_mobile']['unit'] ) ? $settings['image_width_max_mobile']['unit'] : $width_max_unit )
			: $width_max_unit;

		$height_max = isset( $settings['image_height_max']['size'] ) ? floatval( $settings['image_height_max']['size'] ) : 100;
		$height_max_unit = isset( $settings['image_height_max']['unit'] ) ? $settings['image_height_max']['unit'] : 'vh';
		if ( $height_max == 0 ) {
			$height_max = 100;
			$height_max_unit = 'vh';
		}

		$height_max_tablet = isset( $settings['image_height_max_tablet']['size'] ) && floatval( $settings['image_height_max_tablet']['size'] ) > 0
			? floatval( $settings['image_height_max_tablet']['size'] ) : $height_max;
		$height_max_tablet_unit = isset( $settings['image_height_max_tablet']['size'] ) && floatval( $settings['image_height_max_tablet']['size'] ) > 0
			? ( isset( $settings['image_height_max_tablet']['unit'] ) ? $settings['image_height_max_tablet']['unit'] : $height_max_unit )
			: $height_max_unit;

		$height_max_mobile = isset( $settings['image_height_max_mobile']['size'] ) && floatval( $settings['image_height_max_mobile']['size'] ) > 0
			? floatval( $settings['image_height_max_mobile']['size'] ) : $height_max;
		$height_max_mobile_unit = isset( $settings['image_height_max_mobile']['size'] ) && floatval( $settings['image_height_max_mobile']['size'] ) > 0
			? ( isset( $settings['image_height_max_mobile']['unit'] ) ? $settings['image_height_max_mobile']['unit'] : $height_max_unit )
			: $height_max_unit;
		
		// Build data attributes (comme stacking cards avec value et unit)
		$data_attrs = array(
			'data-width-initial-value' => $width_initial,
			'data-width-initial-unit' => $width_initial_unit,
			'data-width-initial-tablet-value' => $width_initial_tablet,
			'data-width-initial-tablet-unit' => $width_initial_tablet_unit,
			'data-width-initial-mobile-value' => $width_initial_mobile,
			'data-width-initial-mobile-unit' => $width_initial_mobile_unit,
			'data-height-initial-value' => $height_initial,
			'data-height-initial-unit' => $height_initial_unit,
			'data-height-initial-tablet-value' => $height_initial_tablet,
			'data-height-initial-tablet-unit' => $height_initial_tablet_unit,
			'data-height-initial-mobile-value' => $height_initial_mobile,
			'data-height-initial-mobile-unit' => $height_initial_mobile_unit,
			'data-initial-scale' => $initial_scale,
			'data-initial-scale-tablet' => $initial_scale_tablet,
			'data-initial-scale-mobile' => $initial_scale_mobile,
			'data-width-max' => $width_max,
			'data-width-max-unit' => $width_max_unit,
			'data-width-max-tablet' => $width_max_tablet,
			'data-width-max-tablet-unit' => $width_max_tablet_unit,
			'data-width-max-mobile' => $width_max_mobile,
			'data-width-max-mobile-unit' => $width_max_mobile_unit,
			'data-height-max' => $height_max,
			'data-height-max-unit' => $height_max_unit,
			'data-height-max-tablet' => $height_max_tablet,
			'data-height-max-tablet-unit' => $height_max_tablet_unit,
			'data-height-max-mobile' => $height_max_mobile,
			'data-height-max-mobile-unit' => $height_max_mobile_unit,
		);
		
		$data_attrs_string = '';
		foreach ( $data_attrs as $key => $value ) {
			$data_attrs_string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		
		echo '<div class="nova-scroll-hero"' . $data_attrs_string . '>';
			if ( ! empty( $image_url ) ) {
				// Wrapper de l'image
				// Build inline CSS variables for instant initial size (prevents FOUC before JS runs)
				$inline_style = sprintf(
					'style="--initial-width: %s%s; --initial-height: %s%s;"',
					esc_attr( $width_initial ),
					esc_attr( $width_initial_unit ),
					esc_attr( $height_initial ),
					esc_attr( $height_initial_unit )
				);
				echo '<div class="nova-scroll-hero-image-wrapper">';
					echo '<div class="nova-scroll-hero-image-container" ' . $inline_style . '>';
						// Determine loading attributes based on LCP priority
						$loading_attr = $is_lcp ? 'eager' : 'lazy';
						$fetchpriority_attr = $is_lcp ? 'high' : 'low';
						
						// Get image dimensions for LCP optimization
						$img_width = 1061;
						$img_height = 700;
						if ( ! empty( $settings['background_image']['id'] ) ) {
							$img_metadata = wp_get_attachment_metadata( intval( $settings['background_image']['id'] ) );
							if ( $img_metadata && isset( $img_metadata['width'] ) && isset( $img_metadata['height'] ) ) {
								$img_width = $img_metadata['width'];
								$img_height = $img_metadata['height'];
							}
						}
						
						// Use responsive image if attachment ID available
						if ( ! empty( $settings['background_image']['id'] ) ) {
							$img_id = intval( $settings['background_image']['id'] );
							$img_html = wp_get_attachment_image(
								$img_id,
								'full',
								false,
								[
									'class' => 'nova-scroll-hero-image',
									'loading' => $loading_attr,
									'decoding' => $is_lcp ? 'sync' : 'async', // Sync decoding for LCP images
									'fetchpriority' => $fetchpriority_attr,
									'width' => $img_width,
									'height' => $img_height,
									'sizes' => '(max-width: 1200px) 100vw, 100vw'
								]
							);
							// Fallback to URL if wp_get_attachment_image failed
							if ( ! empty( $img_html ) ) {
								echo $img_html;
							} else {
								echo '<img class="nova-scroll-hero-image" src="' . esc_url( $image_url ) . '" alt="" width="' . esc_attr( $img_width ) . '" height="' . esc_attr( $img_height ) . '" loading="' . esc_attr( $loading_attr ) . '" decoding="' . esc_attr( $is_lcp ? 'sync' : 'async' ) . '" fetchpriority="' . esc_attr( $fetchpriority_attr ) . '" sizes="100vw">';
							}
						} else {
							echo '<img class="nova-scroll-hero-image" src="' . esc_url( $image_url ) . '" alt="" width="' . esc_attr( $img_width ) . '" height="' . esc_attr( $img_height ) . '" loading="' . esc_attr( $loading_attr ) . '" decoding="' . esc_attr( $is_lcp ? 'sync' : 'async' ) . '" fetchpriority="' . esc_attr( $fetchpriority_attr ) . '" sizes="100vw">';
						}
						
						// Contenu overlay
						echo '<div class="nova-scroll-hero-overlay">';
							echo '<div class="nova-scroll-hero-overlay-content">';
								if ( ! empty( $desc_text_1 ) ) {
									echo '<div class="nova-overlay-text nova-overlay-text-left nova-overlay-text-1">' . wp_kses_post( $desc_text_1 ) . '</div>';
								}
								if(! empty( $desc_text_1 ) || ! empty( $desc_text_2 )){
									echo '<div class="nova-overlay-text nova-overlay-text-right">';
										if ( ! empty( $desc_text_2 ) ) {
											echo '<div class="nova-overlay-text nova-overlay-text-2">' . wp_kses_post( $desc_text_2 ) . '</div>';
										}
										if ( ! empty( $button_text ) ) {
											echo '<a href="' . esc_url( $button_link ) . '" class="nova-scroll-hero-button">' . esc_html( $button_text ) . '</a>';
										}
									echo '</div>';
								}
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</div>';
			}
		echo '</div>';
	}
}
