<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Brands - Logos avec option slider
 */
class Brands_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-brands';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Brands', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-carousel';
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
		return [ 'swiper', 'e-swiper', 'nova-brands-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'swiper', 'nova-brands-script' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// Section Contenu - Texte
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenu', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'content_text',
			[
				'label' => esc_html__( 'Texte', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Nos partenaires et clients', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez votre texte ici...', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// Section Logos
		$this->start_controls_section(
			'section_brands',
			[
				'label' => esc_html__( 'Logos', 'NOVA-addons' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'brand_logo',
			[
				'label' => esc_html__( 'Logo', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'brand_name',
			[
				'label' => esc_html__( 'Nom (alt text)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Brand Name', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Nom de la marque', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'brand_text',
			[
				'label' => esc_html__( 'Texte de la marque', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => '',
				'placeholder' => esc_html__( 'Texte descriptif court', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'brand_link',
			[
				'label' => esc_html__( 'Lien', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://votre-lien.com', 'NOVA-addons' ),
				'show_external' => true,
				'default' => [
					'url' => '',
					'is_external' => true,
					'nofollow' => true,
				],
			]
		);

		$this->add_control(
			'brands_list',
			[
				'label' => esc_html__( 'Liste des logos', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'brand_name' => esc_html__( 'Brand 1', 'NOVA-addons' ),
					],
					[
						'brand_name' => esc_html__( 'Brand 2', 'NOVA-addons' ),
					],
					[
						'brand_name' => esc_html__( 'Brand 3', 'NOVA-addons' ),
					],
				],
				'title_field' => '{{{ brand_name }}}',
			]
		);

		$this->end_controls_section();

		// Section Slider
		$this->start_controls_section(
			'section_slider',
			[
				'label' => esc_html__( 'Slider', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'enable_slider',
			[
				'label' => esc_html__( 'Activer le slider', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'description' => esc_html__( 'Afficher les logos en slider/carousel', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'slider_autoplay',
			[
				'label' => esc_html__( 'Autoplay', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_autoplay_delay',
			[
				'label' => esc_html__( 'Délai autoplay (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3000,
				'min' => 1000,
				'max' => 10000,
				'step' => 100,
				'condition' => [
					'enable_slider' => 'yes',
					'slider_autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_loop',
			[
				'label' => esc_html__( 'Boucle infinie', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_speed',
			[
				'label' => esc_html__( 'Vitesse transition (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 600,
				'min' => 100,
				'max' => 3000,
				'step' => 100,
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_auto_width',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Les slides auront une largeur automatique selon leur contenu', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		// Un seul add_responsive_control : génère desktop + _tablet + _mobile (évite "Cannot redeclare control")
		$this->add_responsive_control(
			'slider_slides_per_view',
			[
				'label' => esc_html__( 'Slides par vue', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'min' => 1,
				'max' => 12,
				'description' => esc_html__( 'Nombre de logos visibles. Ignoré si "Largeur automatique" est activé.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
					'slider_auto_width' => '',
				],
			]
		);

		$this->add_control(
			'slider_space_between',
			[
				'label' => esc_html__( 'Espacement (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 30,
				'min' => 0,
				'max' => 100,
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_navigation',
			[
				'label' => esc_html__( 'Navigation (flèches)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_pagination',
			[
				'label' => esc_html__( 'Pagination (points)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Section Grid (quand slider désactivé)
		$this->start_controls_section(
			'section_grid',
			[
				'label' => esc_html__( 'Grid', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => '',
				],
			]
		);

		$this->add_responsive_control(
			'grid_columns',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'min' => 1,
				'max' => 12,
			]
		);

		$this->add_responsive_control(
			'grid_rows',
			[
				'label' => esc_html__( 'Lignes', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'tablet_default' => 0,
				'mobile_default' => 0,
				'min' => 0,
				'max' => 12,
				'description' => esc_html__( '0 = automatique', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label' => esc_html__( 'Espacement (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'default' => [ 'size' => 30, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-grid-inner' => 'gap: {{SIZE}}{{UNIT}}; --grid-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'grid_align',
			[
				'label' => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [ 'title' => esc_html__( 'Gauche', 'NOVA-addons' ), 'icon' => 'eicon-text-align-left' ],
					'center'     => [ 'title' => esc_html__( 'Centre', 'NOVA-addons' ), 'icon' => 'eicon-text-align-center' ],
					'flex-end'   => [ 'title' => esc_html__( 'Droite', 'NOVA-addons' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-brands-grid-inner' => 'justify-items: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( 'Style Texte', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .nova-brands-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_typography',
				'selector' => '{{WRAPPER}} .nova-brands-text',
			]
		);

		$this->add_responsive_control(
			'text_align',
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
					'{{WRAPPER}} .nova-brands-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_margin_bottom',
			[
				'label' => esc_html__( 'Marge inférieure', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 40,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-brands-text > *' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Logos
		$this->start_controls_section(
			'section_style_brands',
			[
				'label' => esc_html__( 'Style Logos', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'logo_max_width',
			[
				'label' => esc_html__( 'Largeur max (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 300,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 150,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'logo_max_height',
			[
				'label' => esc_html__( 'Hauteur max (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 30,
						'max' => 200,
					],
				],
				'default' => [
					'size' => 80,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo img' => 'max-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'logo_opacity',
			[
				'label' => esc_html__( 'Opacité', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.6,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'logo_opacity_hover',
			[
				'label' => esc_html__( 'Opacité au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo:hover img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_control(
			'logo_filter_normal',
			[
				'label' => esc_html__( 'Filtre CSS (état normal)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'grayscale(1) brightness(0.6)',
				'description' => esc_html__( 'Filtre CSS appliqué aux logos au repos. Exemple : grayscale(1), brightness(0.7) contrast(1.2), ou une chaîne complète de filtre pour recoloriser les logos.', 'NOVA-addons' ),
				'dynamic' => [
					'active' => false,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo img' => 'filter: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'logo_filter_hover',
			[
				'label' => esc_html__( 'Filtre CSS au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'none',
				'description' => esc_html__( 'Filtre CSS appliqué lorsque l’utilisateur survole le logo. Exemple : none, grayscale(0) brightness(1), ou un filtre qui change la couleur (via invert, sepia, hue-rotate, etc.).', 'NOVA-addons' ),
				'dynamic' => [
					'active' => false,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo:hover img, {{WRAPPER}} .nova-brands-logo:focus img' => 'filter: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'logo_border',
				'selector' => '{{WRAPPER}} .nova-brands-logo',
			]
		);

		$this->add_responsive_control(
			'logo_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'logo_box_shadow',
				'selector' => '{{WRAPPER}} .nova-brands-logo',
			]
		);

		$this->add_responsive_control(
			'logo_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'logo_transition',
			[
				'label' => esc_html__( 'Transition (s)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.3,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-logo' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Texte de la marque
		$this->start_controls_section(
			'section_style_brand_item_text',
			[
				'label' => esc_html__( 'Style Texte des logos', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'brand_item_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .nova-brands-item-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'brand_item_text_typography',
				'selector' => '{{WRAPPER}} .nova-brands-item-text',
			]
		);

		$this->add_responsive_control(
			'brand_item_text_margin_top',
			[
				'label' => esc_html__( 'Marge supérieure', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-brands-item-text' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'brand_item_text_align',
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
					'{{WRAPPER}} .nova-brands-item-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Animation
		$this->start_controls_section(
			'section_animation',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'animation_enable',
			[
				'label' => esc_html__( 'Activer animation', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'animation_delay',
			[
				'label' => esc_html__( 'Délai entre logos (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 100,
				'min' => 0,
				'max' => 500,
				'condition' => [
					'animation_enable' => 'yes',
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
	}

	/**
	 * Rendu du widget
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$content_text = $settings['content_text'];
		$brands_list = $settings['brands_list'];
		$enable_slider = $settings['enable_slider'] === 'yes';
		$animation_enable = $settings['animation_enable'] === 'yes';

		// Configuration grid (quand slider désactivé)
		$grid_columns        = isset( $settings['grid_columns'] ) ? intval( $settings['grid_columns'] ) : 4;
		$grid_columns_tablet = isset( $settings['grid_columns_tablet'] ) ? intval( $settings['grid_columns_tablet'] ) : 3;
		$grid_columns_mobile = isset( $settings['grid_columns_mobile'] ) ? intval( $settings['grid_columns_mobile'] ) : 2;
		if ( ! $grid_columns ) $grid_columns = 4;
		if ( ! $grid_columns_tablet ) $grid_columns_tablet = $grid_columns;
		if ( ! $grid_columns_mobile ) $grid_columns_mobile = 2;

		// Configuration slider
		$auto_width = $settings['slider_auto_width'] === 'yes';
		
		// Récupérer les valeurs responsive (peuvent être un tableau avec 'size', 'tablet', 'mobile' ou directement un nombre)
		$slides_per_view = isset( $settings['slider_slides_per_view'] ) ? $settings['slider_slides_per_view'] : 4;
		$slides_per_view_tablet = isset( $settings['slider_slides_per_view_tablet'] ) ? $settings['slider_slides_per_view_tablet'] : 3;
		$slides_per_view_mobile = isset( $settings['slider_slides_per_view_mobile'] ) ? $settings['slider_slides_per_view_mobile'] : 2;
		
		// Si c'est un tableau (structure responsive Elementor)
		if ( is_array( $slides_per_view ) && isset( $slides_per_view['size'] ) ) {
			$slides_per_view = intval( $slides_per_view['size'] );
		} else {
			$slides_per_view = intval( $slides_per_view );
		}
		
		if ( is_array( $slides_per_view_tablet ) && isset( $slides_per_view_tablet['size'] ) ) {
			$slides_per_view_tablet = intval( $slides_per_view_tablet['size'] );
		} else {
			$slides_per_view_tablet = intval( $slides_per_view_tablet );
		}
		
		if ( is_array( $slides_per_view_mobile ) && isset( $slides_per_view_mobile['size'] ) ) {
			$slides_per_view_mobile = intval( $slides_per_view_mobile['size'] );
		} else {
			$slides_per_view_mobile = intval( $slides_per_view_mobile );
		}

		$slider_config = [
			'autoplay' => $settings['slider_autoplay'] === 'yes',
			'autoplayDelay' => intval( $settings['slider_autoplay_delay'] ),
			'loop' => $settings['slider_loop'] === 'yes',
			'speed' => intval( $settings['slider_speed'] ),
			'autoWidth' => $auto_width,
			'slidesPerView' => $auto_width ? 'auto' : $slides_per_view,
			'slidesPerViewTablet' => $auto_width ? 'auto' : $slides_per_view_tablet,
			'slidesPerViewMobile' => $auto_width ? 'auto' : $slides_per_view_mobile,
			'spaceBetween' => intval( $settings['slider_space_between'] ),
			'navigation' => $settings['slider_navigation'] === 'yes',
			'pagination' => $settings['slider_pagination'] === 'yes',
		];

		// Animation config
		$animation_config = [
			'enable' => $animation_enable,
			'delay' => intval( $settings['animation_delay'] ),
			'duration' => intval( $settings['animation_duration'] ),
			'translateY' => isset( $settings['animation_translate_y'] ) ? intval( $settings['animation_translate_y'] ) : 30,
		];

		?>
		<div class="nova-brands-widget" 
			data-enable-slider="<?php echo esc_attr( $enable_slider ? '1' : '0' ); ?>"
			data-slider-config='<?php echo wp_json_encode( $slider_config ); ?>'
			data-animation-config='<?php echo wp_json_encode( $animation_config ); ?>'>
			
			<?php if ( ! empty( $content_text ) ) : ?>
				<div class="nova-brands-text">
					<?php echo wp_kses_post( $content_text ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $brands_list ) ) : ?>
				<div class="nova-brands-container<?php echo $enable_slider ? ' nova-brands-slider' : ' nova-brands-grid'; ?>">
					<?php if ( $enable_slider ) : ?>
						<div class="swiper nova-brands-swiper">
							<div class="swiper-wrapper">
								<?php foreach ( $brands_list as $brand ) : ?>
									<div class="swiper-slide">
										<?php $this->render_brand_item( $brand ); ?>
									</div>
								<?php endforeach; ?>
							</div>
							<?php if ( $slider_config['navigation'] ) : ?>
								<div class="swiper-button-next"></div>
								<div class="swiper-button-prev"></div>
							<?php endif; ?>
							<?php if ( $slider_config['pagination'] ) : ?>
								<div class="swiper-pagination"></div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="nova-brands-grid-inner" style="--grid-cols:<?php echo esc_attr( $grid_columns ); ?>;--grid-cols-tablet:<?php echo esc_attr( $grid_columns_tablet ); ?>;--grid-cols-mobile:<?php echo esc_attr( $grid_columns_mobile ); ?>;--grid-cols-current:<?php echo esc_attr( $grid_columns ); ?>;">
							<?php foreach ( $brands_list as $index => $brand ) : ?>
								<div class="nova-brands-item" data-index="<?php echo esc_attr( $index ); ?>">
									<?php $this->render_brand_item( $brand ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Rendu d'un item de logo
	 */
	private function render_brand_item( $brand ) {
		// Vérifier différentes structures possibles pour l'image
		$logo_url = '';
		if ( isset( $brand['brand_logo']['url'] ) ) {
			$logo_url = $brand['brand_logo']['url'];
		} elseif ( isset( $brand['brand_logo']['id'] ) ) {
			// Si on a un ID, récupérer l'URL
			$logo_url = wp_get_attachment_image_url( $brand['brand_logo']['id'], 'full' );
		} elseif ( is_numeric( $brand['brand_logo'] ) ) {
			// Si c'est directement un ID
			$logo_url = wp_get_attachment_image_url( $brand['brand_logo'], 'full' );
		} elseif ( is_string( $brand['brand_logo'] ) && ! empty( $brand['brand_logo'] ) ) {
			// Si c'est directement une URL
			$logo_url = $brand['brand_logo'];
		}

		// Si toujours pas d'URL, utiliser l'image placeholder
		if ( empty( $logo_url ) ) {
			$logo_url = \Elementor\Utils::get_placeholder_image_src();
		}

		$logo_alt = isset( $brand['brand_name'] ) ? $brand['brand_name'] : 'Brand Logo';
		$link = isset( $brand['brand_link'] ) ? $brand['brand_link'] : [];

		$link_url = isset( $link['url'] ) ? $link['url'] : '';
		$link_target = isset( $link['is_external'] ) && $link['is_external'] ? ' target="_blank"' : '';
		$link_nofollow = isset( $link['nofollow'] ) && $link['nofollow'] ? ' rel="nofollow"' : '';

		$logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $logo_alt ) . '" />';

		if ( ! empty( $link_url ) ) {
			echo '<a href="' . esc_url( $link_url ) . '" class="nova-brands-logo"' . $link_target . $link_nofollow . '>' . $logo_html . '</a>';
		} else {
			echo '<div class="nova-brands-logo">' . $logo_html . '</div>';
		}

		$brand_text = isset( $brand['brand_text'] ) ? $brand['brand_text'] : '';
		if ( ! empty( $brand_text ) ) {
			echo '<div class="nova-brands-item-text">' . wp_kses_post( nl2br( $brand_text ) ) . '</div>';
		}
	}
}

