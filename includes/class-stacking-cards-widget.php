<?php
namespace Nova_Addons_Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Stacking Cards.
 */
class Stacking_Cards_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-stacking-cards';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Stacking Cards', 'NOVA-addons' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-carousel';
	}

	/**
	 * Widget categories.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'NOVA-addons' ];
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'stack', 'cards', 'animation', 'gsap', 'lenis', 'scroll', 'sticky', 'NOVA' ];
	}

	/**
	 * Style dependencies.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'NOVA-stacking-cards-style' ];
	}

	/**
	 * Script dependencies.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'gsap', 'gsap-scrolltrigger', 'NOVA-stacking-cards-script' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {

		/**
		 * Layout & container settings.
		 */
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Mise en page', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'stack_width',
			[
				'label' => esc_html__( 'Largeur du bloc', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 320, 'max' => 2000 ],
					'%'  => [ 'min' => 30,  'max' => 100 ],
					'vw' => [ 'min' => 40,  'max' => 100 ],
				],
				'default' => [
					'size' => 100,
					'unit' => 'vw',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards' => '--stack-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'stack_height',
			[
				'label' => esc_html__( 'Hauteur du bloc', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [
					'px' => [ 'min' => 400, 'max' => 1600 ],
					'%'  => [ 'min' => 50,  'max' => 100 ],
					'vh' => [ 'min' => 50,  'max' => 120 ],
				],
				'default' => [
					'size' => 100,
					'unit' => 'vh',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards' => '--stack-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'stack_padding',
			[
				'label' => esc_html__( 'Padding interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards__inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '40',
					'right' => '40',
					'bottom' => '40',
					'left' => '40',
					'unit' => 'px',
				],
			]
		);

		$this->add_control(
			'stack_background_color',
			[
				'label' => esc_html__( 'Couleur d’arrière-plan', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_radius',
			[
				'label' => esc_html__( 'Arrondi des cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 80 ],
					'%'  => [ 'min' => 0, 'max' => 50 ],
				],
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card' => '--card-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_shadow_strength',
			[
				'label' => esc_html__( 'Ombre des cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.05 ],
				],
				'default' => [
					'size' => 0.35,
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card' => '--card-shadow-strength: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'first_card_initial_scale',
			[
				'label' => esc_html__( 'Échelle initiale - première carte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [],
				'range' => [
					'' => [
						'min' => 0.1,
						'max' => 1,
						'step' => 0.05,
					],
				],
				'default' => [
					'size' => 0.7,
				],
				'description' => esc_html__( 'Définit la taille de départ de la première carte avant son expansion.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'content_alignment',
			[
				'label' => esc_html__( 'Alignement horizontal du contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_flex_direction',
			[
				'label' => esc_html__( 'Direction du contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'column' => [
						'title' => esc_html__( 'Vertical', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'row' => [
						'title' => esc_html__( 'Horizontal', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
				],
				'default' => 'column',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_vertical_alignment',
			[
				'label' => esc_html__( 'Alignement vertical du contenu', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Haut', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bas', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'flex-end',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_gap',
			[
				'label' => esc_html__( 'Espacement des éléments', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 120 ],
					'em' => [ 'min' => 0, 'max' => 8, 'step' => 0.1 ],
					'rem' => [ 'min' => 0, 'max' => 8, 'step' => 0.1 ],
				],
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_text_align',
			[
				'label' => esc_html__( 'Alignement du texte', 'NOVA-addons' ),
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
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'show_navigation',
			[
				'label' => esc_html__( 'Afficher la navigation', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'nav_alignment',
			[
				'label' => esc_html__( 'Alignement de la navigation', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'condition' => [
					'show_navigation' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_position',
			[
				'label' => esc_html__( 'Position verticale', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'bottom' => [
						'title' => esc_html__( 'Bas', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-down',
					],
					'top' => [
						'title' => esc_html__( 'Haut', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-up',
					],
				],
				'default' => 'bottom',
				'condition' => [
					'show_navigation' => 'yes',
				],
				'prefix_class' => 'NOVA-stacking-nav-pos-',
			]
		);

		$this->add_responsive_control(
			'nav_spacing',
			[
				'label' => esc_html__( 'Décalage vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 160 ],
					'em' => [ 'min' => 0, 'max' => 10 ],
					'rem' => [ 'min' => 0, 'max' => 10 ],
				],
				'default' => [
					'size' => 32,
					'unit' => 'px',
				],
				'condition' => [
					'show_navigation' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav' => '--NOVA-stacking-nav-offset: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Cards repeater.
		 */
		$this->start_controls_section(
			'section_cards',
			[
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'card_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Titre de la carte', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_subtitle',
			[
				'label' => esc_html__( 'Sous-titre', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Sous-titre inspirant', 'NOVA-addons' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Ajoutez ici un texte descriptif pour cette carte.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_icon_heading',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_icon_type',
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
			'card_icon',
			[
				'label' => esc_html__( 'Icône (bibliothèque)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'condition' => [
					'card_icon_type' => 'icon',
				],
				'label_block' => true,
				'description' => esc_html__( 'Icône issue de la bibliothèque (Font Awesome, SVG).', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_icon_image',
			[
				'label' => esc_html__( 'Image', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'media_types' => [ 'image', 'svg' ],
				'button_text' => esc_html__( 'Choisir une image', 'NOVA-addons' ),
				'description' => esc_html__( 'Formats acceptés : JPG, PNG, WEBP, AVIF, ICO, SVG…', 'NOVA-addons' ),
				'condition' => [
					'card_icon_type' => 'image',
				],
				'default' => [
					'url' => '',
				],
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_media',
			[
				'label' => esc_html__( 'Image de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'card_overlay_color',
			[
				'label' => esc_html__( 'Couleur d’overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'linear-gradient(270deg, rgba(0, 0, 0, 0) 35.14%, rgba(0, 0, 0, 0.5) 99.7%)',
			]
		);

		$repeater->add_control(
			'card_button_text',
			[
				'label' => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_button_link',
			[
				'label' => esc_html__( 'Lien du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => 'https://example.com',
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'cards',
			[
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'card_title' => esc_html__( 'Immersion totale', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Créez une introduction remarquable avec une image pleine hauteur et un message fort.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'Découvrir', 'NOVA-addons' ),
					],
					[
						'card_title' => esc_html__( 'Narration visuelle', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Révélez votre storytelling étape par étape pour capter l’attention.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'Explorer', 'NOVA-addons' ),
					],
					[
						'card_title' => esc_html__( 'CTA percutant', 'NOVA-addons' ),
						'card_description' => esc_html__( 'Terminez avec un appel à l’action clair et inspirant.', 'NOVA-addons' ),
						'card_button_text' => esc_html__( 'Contactez-nous', 'NOVA-addons' ),
					],
				],
				'title_field' => '{{{ card_title }}}',
			]
		);

		$this->end_controls_section();

		/**
		 * Style - titres.
		 */
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Titres', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur du titre', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__title *' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Espace sous le titre', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 120 ],
				],
				'default' => [
					'size' => 16,
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - sous-titres.
		 */
		$this->start_controls_section(
			'section_style_subtitle',
			[
				'label' => esc_html__( 'Sous-titres', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'subtitle_typography',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__subtitle',
			]
		);

		$this->add_control(
			'subtitle_color',
			[
				'label' => esc_html__( 'Couleur du sous-titre', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#f5f5f5',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__subtitle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'subtitle_spacing',
			[
				'label' => esc_html__( 'Espace sous le sous-titre', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 120 ],
				],
				'default' => [
					'size' => 12,
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__subtitle' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - description.
		 */
		$this->start_controls_section(
			'section_style_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__description',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#f8f8f8',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__description' => 'color: {{VALUE}};',
					'{{WRAPPER}} .NOVA-stacking-card__description p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'description_max_width',
			[
				'label' => esc_html__( 'Largeur max du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [ 'min' => 200, 'max' => 1200 ],
					'%'  => [ 'min' => 20,  'max' => 100 ],
				],
				'default' => [
					'size' => 640,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__description' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - bouton.
		 */
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#111111',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Survol', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color_hover',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Arrondi', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default' => [
					'top' => '16',
					'right' => '28',
					'bottom' => '16',
					'left' => '28',
					'unit' => 'px',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - icônes.
		 */
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'icon_typography',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__icon',
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .NOVA-stacking-card__icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 20, 'max' => 120 ],
				],
				'default' => [
					'size' => 44,
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/**
		 * Style - navigation.
		 */
		$this->start_controls_section(
			'section_style_navigation',
			[
				'label' => esc_html__( 'Navigation', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_navigation' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'navigation_typography',
				'selector' => '{{WRAPPER}} .NOVA-stacking-cards-nav-button',
			]
		);

		$this->add_control(
			'nav_icon_filter',
			[
				'label' => esc_html__( 'Filtre CSS (icônes)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: grayscale(1)', 'NOVA-addons' ),
				'description' => esc_html__( 'Applique un filtre CSS aux icônes de navigation.', 'NOVA-addons' ),
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-icon svg' => 'filter: {{VALUE}};',
					'{{WRAPPER}} .NOVA-stacking-cards-nav-icon img' => 'filter: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_icon_filter_hover',
			[
				'label' => esc_html__( 'Filtre CSS (hover)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: drop-shadow(0 0 4px rgba(255,255,255,0.6))', 'NOVA-addons' ),
				'description' => esc_html__( 'Filtre appliqué lorsque le bouton est survolé.', 'NOVA-addons' ),
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button:hover .NOVA-stacking-cards-nav-icon svg' => 'filter: {{VALUE}};',
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button:hover .NOVA-stacking-cards-nav-icon img' => 'filter: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_icon_filter_active',
			[
				'label' => esc_html__( 'Filtre CSS (actif)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: brightness(1.1)', 'NOVA-addons' ),
				'description' => esc_html__( 'Filtre appliqué lorsque le bouton est actif.', 'NOVA-addons' ),
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button.is-active .NOVA-stacking-cards-nav-icon svg' => 'filter: {{VALUE}};',
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button.is-active .NOVA-stacking-cards-nav-icon img' => 'filter: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_icon_size',
			[
				'label' => esc_html__( 'Taille des icônes', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 8, 'max' => 100 ],
					'em' => [ 'min' => 0.5, 'max' => 6, 'step' => 0.1 ],
					'rem' => [ 'min' => 0.5, 'max' => 6, 'step' => 0.1 ],
				],
				'default' => [
					'size' => 1,
					'unit' => 'rem',
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .NOVA-stacking-cards-nav-icon svg' => 'width: 1em; height: 1em;',
					'{{WRAPPER}} .NOVA-stacking-cards-nav-icon img' => 'width: 1.4em; height: 1.4em;',
				],
			]
		);

		$this->add_responsive_control(
			'nav_button_gap',
			[
				'label' => esc_html__( 'Espacement icône / texte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 80 ],
					'em' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ],
					'rem' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_list_gap',
			[
				'label' => esc_html__( 'Espacement entre éléments', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 80 ],
					'em' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ],
					'rem' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nav_list_direction',
			[
				'label' => esc_html__( 'Orientation', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row' => [
						'title' => esc_html__( 'Horizontale', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'column' => [
						'title' => esc_html__( 'Verticale', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
				],
				'default' => 'row',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_list_wrap',
			[
				'label' => esc_html__( 'Retour à la ligne', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'wrap',
				'options' => [
					'wrap' => esc_html__( 'Autoriser', 'NOVA-addons' ),
					'nowrap' => esc_html__( 'Interdire', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'flex-wrap: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_list_align_items',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Haut', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bas', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_list_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 100, 'max' => 2000 ],
					'%'  => [ 'min' => 10,  'max' => 100 ],
					'vw' => [ 'min' => 10,  'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'nav_list_full_width!' => 'yes',
				],
			]
		);

		$this->add_control(
			'nav_list_full_width',
			[
				'label' => esc_html__( 'Largeur auto (max-content)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'return_value' => 'yes',
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'max-width: max-content;',
				],
			]
		);

		$this->add_responsive_control(
			'nav_list_padding',
			[
				'label' => esc_html__( 'Padding du bloc', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nav_list_background',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'nav_list_border',
				'selector' => '{{WRAPPER}} .NOVA-stacking-cards-nav-list',
			]
		);

		$this->add_responsive_control(
			'nav_list_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-list' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'nav_list_shadow',
				'selector' => '{{WRAPPER}} .NOVA-stacking-cards-nav-list',
			]
		);

		$this->start_controls_tabs( 'tabs_navigation_style' );

		$this->start_controls_tab(
			'tab_navigation_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'navigation_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_background',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button' => 'background-color: {{VALUE}};',
				],
			]
		);


		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_navigation_hover',
			[
				'label' => esc_html__( 'Survol', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'navigation_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_background_hover',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_border_color_hover',
			[
				'label' => esc_html__( 'Couleur de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_navigation_active',
			[
				'label' => esc_html__( 'Actif', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'navigation_color_active',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button.is-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_background_active',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button.is-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'navigation_border_color_active',
			[
				'label' => esc_html__( 'Couleur de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button.is-active' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'navigation_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'navigation_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-cards-nav-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'navigation_border',
				'selector' => '{{WRAPPER}} .NOVA-stacking-cards-nav-button',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_content_block',
			[
				'label' => esc_html__( 'Bloc contenu', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_block_direction',
			[
				'label' => esc_html__( 'Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'column' => [
						'title' => esc_html__( 'Verticale', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'row' => [
						'title' => esc_html__( 'Horizontale', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_align_items',
			[
				'label' => esc_html__( 'Alignement horizontal', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_justify',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Haut', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Bas', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
					'space-between' => [
						'title' => esc_html__( 'Distribué', 'NOVA-addons' ),
						'icon' => 'eicon-justify-space-between-h',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_text_align',
			[
				'label' => esc_html__( 'Alignement du texte', 'NOVA-addons' ),
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
					'justify' => [
						'title' => esc_html__( 'Justifié', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_gap',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 160 ],
					'em' => [ 'min' => 0, 'max' => 8, 'step' => 0.1 ],
					'rem' => [ 'min' => 0, 'max' => 8, 'step' => 0.1 ],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'content_block_max_auto',
			[
				'label' => esc_html__( 'Largeur auto (max-content)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'return_value' => 'yes',
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'max-width: max-content;',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 100, 'max' => 2000 ],
					'%'  => [ 'min' => 10,  'max' => 100 ],
					'vw' => [ 'min' => 10,  'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'content_block_max_auto!' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'content_block_align_self',
			[
				'label' => esc_html__( 'Position horizontale', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-right',
					],
					'stretch' => [
						'title' => esc_html__( 'Étendre', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-stretch',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'align-self: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'content_block_background',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'content_block_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'color: {{VALUE}};',
					'{{WRAPPER}} .NOVA-stacking-card__content a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'content_block_border',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__content',
			]
		);

		$this->add_responsive_control(
			'content_block_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .NOVA-stacking-card__content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'content_block_shadow',
				'selector' => '{{WRAPPER}} .NOVA-stacking-card__content',
			]
		);

		$this->end_controls_section();

		/**
		 * Animation settings.
		 */
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$this->add_control(
			'animation_scrub',
			[
				'label' => esc_html__( 'Fluidité du scroll (scrub)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1.2,
				],
				'description' => esc_html__( 'Plus la valeur est élevée, plus la transition est douce.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_step_duration',
			[
				'label' => esc_html__( 'Durée par carte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ ],
				'range' => [
					'px' => [
						'min' => 0.2,
						'max' => 1.5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.9,
				],
				'description' => esc_html__( 'Durée relative de la timeline pour chaque transition de carte.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_scroll_multiplier',
			[
				'label' => esc_html__( 'Distance de scroll par carte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ ],
				'range' => [
					'px' => [
						'min' => 0.6,
						'max' => 1.8,
						'step' => 0.05,
					],
				],
				'default' => [
					'size' => 1.1,
				],
				'description' => esc_html__( 'Multiplier appliqué à la hauteur du bloc pour déterminer la durée totale du pin.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_previous_lift',
			[
				'label' => esc_html__( 'Remontée de la carte précédente (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 160,
					],
				],
				'default' => [
					'size' => 60,
					'unit' => 'px',
				],
				'description' => esc_html__( 'Déplacement de la carte précédente vers le haut pour suggérer la pile.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'animation_previous_scale',
			[
				'label' => esc_html__( 'Échelle de la carte précédente', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ ],
				'range' => [
					'px' => [
						'min' => 0.8,
						'max' => 1,
						'step' => 0.01,
					],
				],
				'default' => [
					'size' => 0.94,
				],
			]
		);

		$this->add_control(
			'animation_previous_opacity',
			[
				'label' => esc_html__( 'Opacité de la carte précédente (%)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 60,
					'unit' => '%',
				],
			]
		);

		$this->add_control(
			'animation_ease',
			[
				'label' => esc_html__( 'Easing', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'power2.out',
				'options' => [
					'power1.out' => 'power1.out',
					'power2.out' => 'power2.out',
					'power3.out' => 'power3.out',
					'power4.out' => 'power4.out',
					'back.out(1.4)' => 'back.out(1.4)',
					'expo.out' => 'expo.out',
				],
			]
		);

		$this->add_control(
			'animation_additional_vh',
			[
				'label' => esc_html__( 'Scroll supplémentaire après la dernière carte (vh)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'vh' ],
				'range' => [
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 10,
					'unit' => 'vh',
				],
			]
		);

		$this->add_control(
			'animation_debug_markers',
			[
				'label' => esc_html__( 'Afficher les marqueurs ScrollTrigger', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$cards = isset( $settings['cards'] ) && is_array( $settings['cards'] ) ? $settings['cards'] : [];
		if ( empty( $cards ) ) {
			return;
		}

		$wrapper_id = 'NOVA-stacking-cards-' . wp_unique_id();

		// Dimensions (desktop/tablet/mobile).
		$width_value        = isset( $settings['stack_width']['size'] ) ? $settings['stack_width']['size'] : 100;
		$width_unit         = isset( $settings['stack_width']['unit'] ) ? $settings['stack_width']['unit'] : 'vw';
		$width_tablet_value = isset( $settings['stack_width_tablet']['size'] ) ? $settings['stack_width_tablet']['size'] : '';
		$width_tablet_unit  = isset( $settings['stack_width_tablet']['unit'] ) ? $settings['stack_width_tablet']['unit'] : '';
		$width_mobile_value = isset( $settings['stack_width_mobile']['size'] ) ? $settings['stack_width_mobile']['size'] : '';
		$width_mobile_unit  = isset( $settings['stack_width_mobile']['unit'] ) ? $settings['stack_width_mobile']['unit'] : '';

		$height_value        = isset( $settings['stack_height']['size'] ) ? $settings['stack_height']['size'] : 100;
		$height_unit         = isset( $settings['stack_height']['unit'] ) ? $settings['stack_height']['unit'] : 'vh';
		$height_tablet_value = isset( $settings['stack_height_tablet']['size'] ) ? $settings['stack_height_tablet']['size'] : '';
		$height_tablet_unit  = isset( $settings['stack_height_tablet']['unit'] ) ? $settings['stack_height_tablet']['unit'] : '';
		$height_mobile_value = isset( $settings['stack_height_mobile']['size'] ) ? $settings['stack_height_mobile']['size'] : '';
		$height_mobile_unit  = isset( $settings['stack_height_mobile']['unit'] ) ? $settings['stack_height_mobile']['unit'] : '';

		$radius_value = isset( $settings['card_radius']['size'] ) ? $settings['card_radius']['size'] : 24;
		$radius_unit  = isset( $settings['card_radius']['unit'] ) ? $settings['card_radius']['unit'] : 'px';

		$shadow_strength = isset( $settings['card_shadow_strength']['size'] ) ? $settings['card_shadow_strength']['size'] : 0.35;

		$scrub_value             = isset( $settings['animation_scrub']['size'] ) ? (float) $settings['animation_scrub']['size'] : 1.2;
		$step_duration           = isset( $settings['animation_step_duration']['size'] ) ? (float) $settings['animation_step_duration']['size'] : 0.9;
		$scroll_multiplier       = isset( $settings['animation_scroll_multiplier']['size'] ) ? (float) $settings['animation_scroll_multiplier']['size'] : 1.1;
		$previous_lift           = isset( $settings['animation_previous_lift']['size'] ) ? (float) $settings['animation_previous_lift']['size'] : 60;
		$previous_scale          = isset( $settings['animation_previous_scale']['size'] ) ? (float) $settings['animation_previous_scale']['size'] : 0.94;
		$previous_opacity_slider = isset( $settings['animation_previous_opacity']['size'] ) ? (float) $settings['animation_previous_opacity']['size'] : 60;
		$previous_opacity        = max( 0, min( 1, $previous_opacity_slider / 100 ) );
		$additional_vh           = isset( $settings['animation_additional_vh']['size'] ) ? (float) $settings['animation_additional_vh']['size'] : 10;
		$animation_ease          = ! empty( $settings['animation_ease'] ) ? $settings['animation_ease'] : 'power2.out';
		$debug_markers           = isset( $settings['animation_debug_markers'] ) && 'yes' === $settings['animation_debug_markers'];

		$style_vars = [
			'--stack-width: ' . esc_attr( $width_value . $width_unit ),
			'--stack-height: ' . esc_attr( $height_value . $height_unit ),
			'--card-radius: ' . esc_attr( $radius_value . $radius_unit ),
			'--card-shadow-strength: ' . esc_attr( $shadow_strength ),
		];
		$show_navigation = isset( $settings['show_navigation'] ) && 'yes' === $settings['show_navigation'];
		$first_initial_scale = 0.7;
		$first_expand_enabled = isset( $settings['first_card_expand_enabled'] ) ? $settings['first_card_expand_enabled'] === 'yes' : false;
		if ( isset( $settings['first_card_initial_scale'] ) && is_array( $settings['first_card_initial_scale'] ) && isset( $settings['first_card_initial_scale']['size'] ) ) {
			$first_initial_scale = max( 0.1, min( 1, (float) $settings['first_card_initial_scale']['size'] ) );
			$first_expand_enabled = true;
		}
		$first_expand_duration = 0.6;

		?>
		<div class="NOVA-stacking-cards-wrapper" id="<?php echo esc_attr( $wrapper_id ); ?>">
			<div
				class="NOVA-stacking-cards"
				data-card-count="<?php echo esc_attr( count( $cards ) ); ?>"
				data-scrub="<?php echo esc_attr( $scrub_value ); ?>"
				data-step-duration="<?php echo esc_attr( $step_duration ); ?>"
				data-scroll-multiplier="<?php echo esc_attr( $scroll_multiplier ); ?>"
				data-previous-lift="<?php echo esc_attr( $previous_lift ); ?>"
				data-previous-scale="<?php echo esc_attr( $previous_scale ); ?>"
				data-previous-opacity="<?php echo esc_attr( $previous_opacity ); ?>"
				data-additional-vh="<?php echo esc_attr( $additional_vh ); ?>"
				data-animation-ease="<?php echo esc_attr( $animation_ease ); ?>"
				data-debug="<?php echo esc_attr( $debug_markers ? '1' : '0' ); ?>"
				data-first-initial-scale="<?php echo esc_attr( $first_initial_scale ); ?>"
				data-first-expand-duration="<?php echo esc_attr( $first_expand_duration ); ?>"
				data-first-expand-enabled="<?php echo esc_attr( $first_expand_enabled ? '1' : '0' ); ?>"
				data-stack-width-value="<?php echo esc_attr( $width_value ); ?>"
				data-stack-width-unit="<?php echo esc_attr( $width_unit ); ?>"
				data-stack-width-tablet-value="<?php echo esc_attr( $width_tablet_value ); ?>"
				data-stack-width-tablet-unit="<?php echo esc_attr( $width_tablet_unit ); ?>"
				data-stack-width-mobile-value="<?php echo esc_attr( $width_mobile_value ); ?>"
				data-stack-width-mobile-unit="<?php echo esc_attr( $width_mobile_unit ); ?>"
				data-stack-height-value="<?php echo esc_attr( $height_value ); ?>"
				data-stack-height-unit="<?php echo esc_attr( $height_unit ); ?>"
				data-stack-height-tablet-value="<?php echo esc_attr( $height_tablet_value ); ?>"
				data-stack-height-tablet-unit="<?php echo esc_attr( $height_tablet_unit ); ?>"
				data-stack-height-mobile-value="<?php echo esc_attr( $height_mobile_value ); ?>"
				data-stack-height-mobile-unit="<?php echo esc_attr( $height_mobile_unit ); ?>"
				style="<?php echo esc_attr( implode( ';', $style_vars ) ); ?>"
			>
				<?php if ( $show_navigation && count( $cards ) > 1 ) : ?>
					<nav class="NOVA-stacking-cards-nav" aria-label="<?php esc_attr_e( 'Navigation des cartes', 'NOVA-addons' ); ?>">
						<ul class="NOVA-stacking-cards-nav-list">
							<?php foreach ( $cards as $index => $card ) : ?>
								<?php
								$nav_icon_html      = '';
								$nav_card_icon_type = isset( $card['card_icon_type'] ) ? $card['card_icon_type'] : 'icon';

								if ( 'icon' === $nav_card_icon_type && ! empty( $card['card_icon']['value'] ) ) {
									$icon_value = $card['card_icon']['value'];
									if ( is_array( $icon_value ) && ! empty( $icon_value['url'] ) ) {
										$nav_icon_html = sprintf(
											'<img src="%1$s" alt="" />',
											esc_url( $icon_value['url'] )
										);
									} else {
										ob_start();
										Icons_Manager::render_icon( $card['card_icon'], [ 'aria-hidden' => 'true' ] );
										$nav_icon_html = ob_get_clean();
									}
								} elseif ( 'image' === $nav_card_icon_type && ! empty( $card['card_icon_image']['url'] ) ) {
									$nav_icon_html = sprintf(
										'<img src="%1$s" alt="" />',
										esc_url( $card['card_icon_image']['url'] )
									);
								}

								$nav_label = isset( $card['card_subtitle'] ) ? $card['card_subtitle'] : '';
								if ( empty( $nav_label ) && ! empty( $card['card_title'] ) ) {
									$nav_label = wp_strip_all_tags( $card['card_title'] );
								}
								if ( empty( $nav_label ) ) {
									$nav_label = sprintf( esc_html__( 'Carte %d', 'NOVA-addons' ), $index + 1 );
								}
								?>
								<li class="NOVA-stacking-cards-nav-item">
									<button
										type="button"
										class="NOVA-stacking-cards-nav-button<?php echo 0 === $index ? ' is-active' : ''; ?>"
										data-nav-index="<?php echo esc_attr( $index ); ?>"
										aria-label="<?php echo esc_attr( sprintf( __( 'Afficher la carte %d', 'NOVA-addons' ), $index + 1 ) ); ?>"
									>
										<?php if ( ! empty( $nav_icon_html ) ) : ?>
											<span class="NOVA-stacking-cards-nav-icon" aria-hidden="true">
												<?php echo $nav_icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</span>
										<?php endif; ?>
										<span class="NOVA-stacking-cards-nav-text">
											<?php echo esc_html( $nav_label ); ?>
										</span>
									</button>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
				<div class="NOVA-stacking-cards__inner">
					<?php
					foreach ( $cards as $index => $card ) :
						$card_overlay_color = isset( $card['card_overlay_color'] ) ? $card['card_overlay_color'] : 'rgba(0, 0, 0, 0.45)';
						$card_icon_type     = isset( $card['card_icon_type'] ) ? $card['card_icon_type'] : 'icon';

						$card_class = [
							'NOVA-stacking-card',
							'NOVA-stacking-card--card-' . ( $index + 1 ),
						];
						if ( ! empty( $card['_id'] ) ) {
							$card_class[] = 'elementor-repeater-item-' . esc_attr( $card['_id'] );
						}
						if ( 0 === $index ) {
							$card_class[] = 'is-active';
						}

						$image_html = '';
						if ( ! empty( $card['card_media']['id'] ) ) {
							$image_html = wp_get_attachment_image(
								intval( $card['card_media']['id'] ),
								'full',
								false,
								[
									'class' => 'NOVA-stacking-card__image',
									'loading' => $index === 0 ? 'eager' : 'lazy',
									'decoding' => $index === 0 ? 'sync' : 'async',
								]
							);
						} elseif ( ! empty( $card['card_media']['url'] ) ) {
							$image_url = esc_url( $card['card_media']['url'] );
							$image_html = '<img class="NOVA-stacking-card__image" src="' . $image_url . '" alt="" loading="' . ( 0 === $index ? 'eager' : 'lazy' ) . '" decoding="' . ( 0 === $index ? 'sync' : 'async' ) . '"/>';
						}

						$button_text = isset( $card['card_button_text'] ) ? $card['card_button_text'] : '';
						$button_link = isset( $card['card_button_link'] ) ? $card['card_button_link'] : [];
						$button_key  = 'stacking-card-button-' . $index;
						$has_button  = ! empty( $button_text ) && ! empty( $button_link['url'] );

						if ( $has_button ) {
							$this->add_render_attribute( $button_key, 'class', 'NOVA-stacking-card__button' );
							$this->add_link_attributes( $button_key, $button_link );
						}
						?>
						<article
							class="<?php echo esc_attr( implode( ' ', $card_class ) ); ?>"
							data-card-index="<?php echo esc_attr( $index ); ?>"
							style="--card-overlay: <?php echo esc_attr( $card_overlay_color ); ?>;"
						>
							<div class="NOVA-stacking-card__media">
								<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span class="NOVA-stacking-card__overlay"></span>
							</div>
							<div class="NOVA-stacking-card__content">
								<?php
								if ( ! empty( $card['card_title'] ) ) :
									?>
									<div class="NOVA-stacking-card__title">
										<?php echo wp_kses_post( $card['card_title'] ); ?>
									</div>
									<?php
								endif;

								if ( ! empty( $card['card_description'] ) ) :
									?>
									<div class="NOVA-stacking-card__description">
										<?php echo wp_kses_post( $card['card_description'] ); ?>
									</div>
									<?php
								endif;

								if ( $has_button ) :
									?>
									<a <?php echo $this->get_render_attribute_string( $button_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php echo esc_html( $button_text ); ?>
									</a>
									<?php
								endif;
								?>
							</div>
						</article>
						<?php
					endforeach;
					?>
				</div>
			</div>
		</div>
		<?php
	}
}


