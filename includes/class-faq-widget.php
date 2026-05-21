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
 * Widget NOVA FAQ - Accordion FAQ avec toutes les configurations possibles
 */
class FAQ_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-faq';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA FAQ', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-accordion';
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
		return [ 'nova-faq-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-faq-script' ];
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
				'default' => 'no',
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => esc_html__( 'Texte du titre', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Questions Fréquentes', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez le titre', 'NOVA-addons' ),
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Balise HTML', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => [
					'h1' => esc_html__( 'H1', 'NOVA-addons' ),
					'h2' => esc_html__( 'H2', 'NOVA-addons' ),
					'h3' => esc_html__( 'H3', 'NOVA-addons' ),
					'h4' => esc_html__( 'H4', 'NOVA-addons' ),
					'h5' => esc_html__( 'H5', 'NOVA-addons' ),
					'h6' => esc_html__( 'H6', 'NOVA-addons' ),
					'div' => esc_html__( 'DIV', 'NOVA-addons' ),
					'span' => esc_html__( 'SPAN', 'NOVA-addons' ),
				],
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section FAQ Items
		$this->start_controls_section(
			'section_faq_items',
			[
				'label' => esc_html__( 'Questions FAQ', 'NOVA-addons' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'faq_question',
			[
				'label' => esc_html__( 'Question', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez la question', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'faq_answer',
			[
				'label' => esc_html__( 'Réponse', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez la réponse', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'faq_default_open',
			[
				'label' => esc_html__( 'Ouvrir par défaut', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->add_control(
			'faq_list',
			[
				'label' => esc_html__( 'Liste des questions', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ faq_question }}}',
			]
		);

		$this->end_controls_section();

		// Section Configuration
		$this->start_controls_section(
			'section_configuration',
			[
				'label' => esc_html__( 'Configuration', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'accordion_type',
			[
				'label' => esc_html__( 'Type d\'accordéon', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'single',
				'options' => [
					'single' => esc_html__( 'Un seul ouvert à la fois', 'NOVA-addons' ),
					'multiple' => esc_html__( 'Plusieurs peuvent être ouverts', 'NOVA-addons' ),
				],
				'description' => esc_html__( 'Détermine si plusieurs items peuvent être ouverts simultanément.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'icon_type',
			[
				'label' => esc_html__( 'Type d\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'plus_minus',
				'options' => [
					'plus_minus' => esc_html__( 'Plus/Minus', 'NOVA-addons' ),
					'arrow' => esc_html__( 'Flèche', 'NOVA-addons' ),
					'chevron' => esc_html__( 'Chevron', 'NOVA-addons' ),
					'custom' => esc_html__( 'Personnalisé', 'NOVA-addons' ),
					'none' => esc_html__( 'Aucun', 'NOVA-addons' ),
				],
			]
		);

		$this->add_control(
			'icon_open',
			[
				'label' => esc_html__( 'Icône ouverte', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-minus',
					'library' => 'fa-solid',
				],
				'condition' => [
					'icon_type' => 'custom',
				],
			]
		);

		$this->add_control(
			'icon_closed',
			[
				'label' => esc_html__( 'Icône fermée', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition' => [
					'icon_type' => 'custom',
				],
			]
		);

		$this->add_control(
			'icon_position',
			[
				'label' => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left' => esc_html__( 'Gauche', 'NOVA-addons' ),
					'right' => esc_html__( 'Droite', 'NOVA-addons' ),
				],
				'condition' => [
					'icon_type!' => 'none',
				],
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label' => esc_html__( 'Vitesse d\'animation (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'ms' ],
				'range' => [
					'ms' => [
						'min' => 100,
						'max' => 2000,
						'step' => 50,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 300,
				],
			]
		);

		$this->add_control(
			'animation_type',
			[
				'label' => esc_html__( 'Type d\'animation', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => esc_html__( 'Slide (Défilement)', 'NOVA-addons' ),
					'fade' => esc_html__( 'Fade (Fondu)', 'NOVA-addons' ),
					'none' => esc_html__( 'Aucune', 'NOVA-addons' ),
				],
			]
		);

		$this->add_responsive_control(
			'spacing_between',
			[
				'label' => esc_html__( 'Espacement entre items', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
					'em' => [ 'min' => 0, 'max' => 5 ],
				],
				'default' => [ 'size' => 15, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'(tablet){{WRAPPER}} .nova-faq-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'(mobile){{WRAPPER}} .nova-faq-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Titre Principal
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Style - Titre Principal', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Typographie', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-title' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .nova-faq-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'default' => 'left',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Question
		$this->start_controls_section(
			'section_style_question',
			[
				'label' => esc_html__( 'Style - Question', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'question_typography',
				'label' => esc_html__( 'Typographie', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-question, {{WRAPPER}} .nova-faq-question h1, {{WRAPPER}} .nova-faq-question h2, {{WRAPPER}} .nova-faq-question h3, {{WRAPPER}} .nova-faq-question h4, {{WRAPPER}} .nova-faq-question h5, {{WRAPPER}} .nova-faq-question h6, {{WRAPPER}} .nova-faq-question p',
			]
		);

		$this->add_control(
			'question_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-question, {{WRAPPER}} .nova-faq-question h1, {{WRAPPER}} .nova-faq-question h2, {{WRAPPER}} .nova-faq-question h3, {{WRAPPER}} .nova-faq-question h4, {{WRAPPER}} .nova-faq-question h5, {{WRAPPER}} .nova-faq-question h6, {{WRAPPER}} .nova-faq-question p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'question_color_active',
			[
				'label' => esc_html__( 'Couleur (Active)', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item.active .nova-faq-question' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'question_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-question',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'question_background_active',
				'label' => esc_html__( 'Fond (Active)', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-item.active .nova-faq-question',
			]
		);

		$this->add_responsive_control(
			'question_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-question' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'question_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-question',
			]
		);

		$this->add_responsive_control(
			'question_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-question' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'question_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-question',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'question_box_shadow_active',
				'label' => esc_html__( 'Ombre (Active)', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-item.active .nova-faq-question',
			]
		);

		$this->end_controls_section();

		// Section Style - Réponse
		$this->start_controls_section(
			'section_style_answer',
			[
				'label' => esc_html__( 'Style - Réponse', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'answer_typography',
				'label' => esc_html__( 'Typographie', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-answer',
			]
		);

		$this->add_control(
			'answer_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-answer' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'answer_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-answer',
			]
		);

		$this->add_responsive_control(
			'answer_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-answer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'answer_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-answer',
			]
		);

		$this->add_responsive_control(
			'answer_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-answer' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Icône
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Style - Icône', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'icon_type!' => 'none',
				],
			]
		);

		$this->add_control(
			'icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
					'em' => [
						'min' => 0.5,
						'max' => 5,
					],
				],
				'default' => [
					'size' => 16,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-icon' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-faq-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-faq-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_color_active',
			[
				'label' => esc_html__( 'Couleur (Active)', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item.active .nova-faq-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-faq-item.active .nova-faq-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'icon_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'icon_background_active',
				'label' => esc_html__( 'Fond (Active)', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-item.active .nova-faq-icon',
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_rotation',
			[
				'label' => esc_html__( 'Rotation (Active)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range' => [
					'deg' => [
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => 180,
					'unit' => 'deg',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item.active .nova-faq-icon' => 'transform: rotate({{SIZE}}{{UNIT}});',
				],
				'condition' => [
					'icon_type' => [ 'arrow', 'chevron' ],
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Item FAQ
		$this->start_controls_section(
			'section_style_item',
			[
				'label' => esc_html__( 'Style - Item FAQ', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-item',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background_active',
				'label' => esc_html__( 'Fond (Active)', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-item.active',
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_border_active',
				'label' => esc_html__( 'Bordure (Active)', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-item.active',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_box_shadow_active',
				'label' => esc_html__( 'Ombre (Active)', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-item.active',
			]
		);

		$this->add_responsive_control(
			'item_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'item_transition',
			[
				'label' => esc_html__( 'Durée de transition (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'ms' ],
				'range' => [
					'ms' => [
						'min' => 0,
						'max' => 2000,
						'step' => 50,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 300,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-item' => 'transition-duration: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Container
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Style - Container', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-faq-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-faq-container',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-container',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-faq-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-faq-container',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Affiche le widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['faq_list'] ) ) {
			return;
		}

		$accordion_type = isset( $settings['accordion_type'] ) ? $settings['accordion_type'] : 'single';
		$icon_type = isset( $settings['icon_type'] ) ? $settings['icon_type'] : 'plus_minus';
		$icon_position = isset( $settings['icon_position'] ) ? $settings['icon_position'] : 'right';
		$animation_speed = isset( $settings['animation_speed']['size'] ) ? $settings['animation_speed']['size'] : 300;
		$animation_type = isset( $settings['animation_type'] ) ? $settings['animation_type'] : 'slide';

		// Configuration pour JavaScript
		$faq_config = [
			'accordionType' => $accordion_type,
			'animationSpeed' => (int) $animation_speed,
			'animationType' => $animation_type,
		];

		?>
		<div class="nova-faq-widget" data-faq-config="<?php echo esc_attr( wp_json_encode( $faq_config ) ); ?>" data-icon-type="<?php echo esc_attr( $icon_type ); ?>">
			<?php if ( ! empty( $settings['show_title'] ) && $settings['show_title'] === 'yes' && ! empty( $settings['title_text'] ) ) : ?>
				<?php
				$title_tag = isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h2';
				?>
				<<?php echo esc_attr( $title_tag ); ?> class="nova-faq-title">
					<?php echo wp_kses_post( $settings['title_text'] ); ?>
				</<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>

			<div class="nova-faq-container">
				<?php foreach ( $settings['faq_list'] as $index => $item ) : 
					$is_open = ! empty( $item['faq_default_open'] ) && $item['faq_default_open'] === 'yes';
					$item_id = 'nova-faq-item-' . $this->get_id() . '-' . $index;
				?>
					<div class="nova-faq-item<?php echo $is_open ? ' active' : ''; ?>" data-item-index="<?php echo esc_attr( $index ); ?>">
						<div class="nova-faq-question<?php echo $icon_position === 'left' ? ' icon-left' : ' icon-right'; ?>" role="button" tabindex="0" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $item_id ); ?>">
							<?php if ( $icon_type !== 'none' && $icon_position === 'left' ) : ?>
								<span class="nova-faq-icon"<?php if ( $icon_type === 'custom' ) : ?> data-icon-open="<?php echo esc_attr( wp_json_encode( $settings['icon_open'] ?? [] ) ); ?>" data-icon-closed="<?php echo esc_attr( wp_json_encode( $settings['icon_closed'] ?? [] ) ); ?>"<?php endif; ?>>
									<?php echo $this->render_icon( $icon_type, $is_open, $settings ); ?>
								</span>
							<?php endif; ?>
							
							<span class="nova-faq-question-text">
								<?php echo wp_kses_post( $item['faq_question'] ); ?>
							</span>

							<?php if ( $icon_type !== 'none' && $icon_position === 'right' ) : ?>
								<span class="nova-faq-icon"<?php if ( $icon_type === 'custom' ) : ?> data-icon-open="<?php echo esc_attr( wp_json_encode( $settings['icon_open'] ?? [] ) ); ?>" data-icon-closed="<?php echo esc_attr( wp_json_encode( $settings['icon_closed'] ?? [] ) ); ?>"<?php endif; ?>>
									<?php echo $this->render_icon( $icon_type, $is_open, $settings ); ?>
								</span>
							<?php endif; ?>
						</div>
						
						<div class="nova-faq-answer-wrapper" id="<?php echo esc_attr( $item_id ); ?>"<?php echo ! $is_open ? ' style="display: none;"' : ''; ?>>
							<div class="nova-faq-answer">
								<?php echo wp_kses_post( $item['faq_answer'] ); ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Rend l'icône selon le type
	 *
	 * @param string $icon_type Type d'icône.
	 * @param bool   $is_open   Si l'item est ouvert.
	 * @param array  $settings   Settings du widget.
	 * @return string
	 */
	private function render_icon( $icon_type, $is_open, $settings ) {
		if ( $icon_type === 'custom' ) {
			if ( $is_open && ! empty( $settings['icon_open'] ) ) {
				Icons_Manager::render_icon( $settings['icon_open'], [ 'aria-hidden' => 'true' ] );
			} elseif ( ! $is_open && ! empty( $settings['icon_closed'] ) ) {
				Icons_Manager::render_icon( $settings['icon_closed'], [ 'aria-hidden' => 'true' ] );
			}
		} elseif ( $icon_type === 'plus_minus' ) {
			if ( $is_open ) {
				// Minus icon (horizontal line only)
				echo '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			} else {
				// Plus icon (both lines)
				echo '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
			}
		} elseif ( $icon_type === 'arrow' ) {
			echo '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		} elseif ( $icon_type === 'chevron' ) {
			if ( $is_open ) {
				echo '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			} else {
				echo '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 10L8 6L12 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			}
		}
	}
}
