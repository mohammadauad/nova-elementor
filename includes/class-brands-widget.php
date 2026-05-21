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
	 * RÃ©cupÃ¨re le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-brands';
	}

	/**
	 * RÃ©cupÃ¨re le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Brands', 'NOVA-addons' );
	}

	/**
	 * RÃ©cupÃ¨re l'icÃ´ne du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-carousel';
	}

	/**
	 * RÃ©cupÃ¨re les catÃ©gories du widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'NOVA-addons' ];
	}

	/**
	 * RÃ©cupÃ¨re les dÃ©pendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'swiper', 'e-swiper', 'nova-brands-style' ];
	}

	/**
	 * RÃ©cupÃ¨re les dÃ©pendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'swiper', 'nova-brands-script' ];
	}

	/**
	 * Enregistre les contrÃ´les du widget.
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
				'description' => esc_html__( 'DÃ©sactivÃ© = grille sur tous les Ã©crans. ActivÃ© = rÃ©glages ci-dessous et affichage responsive.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'brands_responsive_display_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'Affichage responsive (slider / grille)', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'brands_display_mode_mobile',
			[
				'label' => esc_html__( 'Mode â€” Mobile (<768px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'slider',
				'options' => [
					'slider' => esc_html__( 'Slider', 'NOVA-addons' ),
					'grid'   => esc_html__( 'Grille', 'NOVA-addons' ),
				],
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'brands_display_mode_tablet',
			[
				'label' => esc_html__( 'Mode â€” Tablette (768â€“1024px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'slider',
				'options' => [
					'slider' => esc_html__( 'Slider', 'NOVA-addons' ),
					'grid'   => esc_html__( 'Grille', 'NOVA-addons' ),
				],
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'brands_display_mode_desktop',
			[
				'label' => esc_html__( 'Mode â€” Bureau (>1024px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'slider',
				'options' => [
					'slider' => esc_html__( 'Slider', 'NOVA-addons' ),
					'grid'   => esc_html__( 'Grille', 'NOVA-addons' ),
				],
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_marquee_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label' => esc_html__( 'DÃ©filement continu (type bandeau d\'actualitÃ©s)', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_marquee_enable',
			[
				'label' => esc_html__( 'DÃ©filement automatique continu', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => '',
				'description' => esc_html__( 'Les logos dÃ©filent en boucle sans pause entre chaque slide (style fil d\'actualitÃ©s). DÃ©sactive l\'autoplay classique.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_marquee_speed',
			[
				'label' => esc_html__( 'DurÃ©e d\'un passage (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 8000,
				'min' => 2000,
				'max' => 60000,
				'step' => 500,
				'description' => esc_html__( 'Plus la valeur est Ã©levÃ©e, plus le dÃ©filement est lent.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
					'slider_marquee_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_marquee_pause_hover',
			[
				'label' => esc_html__( 'Pause au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'enable_slider' => 'yes',
					'slider_marquee_enable' => 'yes',
				],
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
					'slider_marquee_enable' => '',
				],
			]
		);

		$this->add_control(
			'slider_autoplay_delay',
			[
				'label' => esc_html__( 'DÃ©lai autoplay (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3000,
				'min' => 1000,
				'max' => 10000,
				'step' => 100,
				'condition' => [
					'enable_slider' => 'yes',
					'slider_autoplay' => 'yes',
					'slider_marquee_enable' => '',
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

		// Un seul add_responsive_control : gÃ©nÃ¨re desktop + _tablet + _mobile (Ã©vite "Cannot redeclare control")
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
				'description' => esc_html__( 'Nombre de logos visibles. IgnorÃ© si "Largeur automatique" est activÃ©.', 'NOVA-addons' ),
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
				'label' => esc_html__( 'Navigation (flÃ¨ches)', 'NOVA-addons' ),
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

		// Section Grid (breakpoints en mode grille ou slider global dÃ©sactivÃ©)
		$this->start_controls_section(
			'section_grid',
			[
				'label' => esc_html__( 'Grille', 'NOVA-addons' ),
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
				'label' => esc_html__( 'Marge infÃ©rieure', 'NOVA-addons' ),
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
				'label' => esc_html__( 'OpacitÃ©', 'NOVA-addons' ),
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
				'label' => esc_html__( 'OpacitÃ© au survol', 'NOVA-addons' ),
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
				'label' => esc_html__( 'Filtre CSS (Ã©tat normal)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'grayscale(1) brightness(0.6)',
				'description' => esc_html__( 'Filtre CSS appliquÃ© aux logos au repos. Exemple : grayscale(1), brightness(0.7) contrast(1.2), ou une chaÃ®ne complÃ¨te de filtre pour recoloriser les logos.', 'NOVA-addons' ),
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
				'description' => esc_html__( 'Filtre CSS appliquÃ© lorsque lâ€™utilisateur survole le logo. Exemple : none, grayscale(0) brightness(1), ou un filtre qui change la couleur (via invert, sepia, hue-rotate, etc.).', 'NOVA-addons' ),
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
				'label' => esc_html__( 'Marge supÃ©rieure', 'NOVA-addons' ),
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
				'label' => esc_html__( 'DÃ©lai entre logos (ms)', 'NOVA-addons' ),
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
				'description' => esc_html__( 'Distance de translation verticale au dÃ©marrage de l\'animation', 'NOVA-addons' ),
				'condition' => [
					'animation_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'animation_duration',
			[
				'label' => esc_html__( 'DurÃ©e (ms)', 'NOVA-addons' ),
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
	 * Mode d'affichage par breakpoint (slider|grid).
	 *
	 * @param array $settings Widget settings.
	 * @return array<string,string>
	 */
	protected function get_brands_display_mode_config( array $settings ) {
		$sanitize = function ( $value ) {
			return in_array( $value, [ 'slider', 'grid' ], true ) ? $value : 'slider';
		};

		if ( empty( $settings['enable_slider'] ) || 'yes' !== $settings['enable_slider'] ) {
			return [
				'mobile'  => 'grid',
				'tablet'  => 'grid',
				'desktop' => 'grid',
			];
		}

		return [
			'mobile'  => $sanitize( $settings['brands_display_mode_mobile'] ?? 'slider' ),
			'tablet'  => $sanitize( $settings['brands_display_mode_tablet'] ?? 'slider' ),
			'desktop' => $sanitize( $settings['brands_display_mode_desktop'] ?? 'slider' ),
		];
	}

	/**
	 * Markup Swiper (variante slider).
	 *
	 * @param array $brands_list Liste des marques.
	 * @param array $slider_config Config slider pour le JS.
	 * @return void
	 */
	protected function render_brands_slider_variant( array $brands_list, array $slider_config ) {
		$marquee_class = ! empty( $slider_config['marquee'] ) ? ' nova-brands-swiper--marquee' : '';
		?>
		<div class="nova-brands-variant nova-brands-variant--slider">
			<div class="nova-brands-container nova-brands-slider">
				<div class="swiper nova-brands-swiper<?php echo esc_attr( $marquee_class ); ?>">
					<div class="swiper-wrapper">
						<?php foreach ( $brands_list as $brand ) : ?>
							<div class="swiper-slide">
								<?php $this->render_brand_item( $brand ); ?>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if ( ! empty( $slider_config['navigation'] ) && empty( $slider_config['marquee'] ) ) : ?>
						<div class="swiper-button-next"></div>
						<div class="swiper-button-prev"></div>
					<?php endif; ?>
					<?php if ( ! empty( $slider_config['pagination'] ) && empty( $slider_config['marquee'] ) ) : ?>
						<div class="swiper-pagination"></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Markup grille (variante grid).
	 *
	 * @param array $brands_list Liste des marques.
	 * @param int   $grid_columns Colonnes desktop.
	 * @param int   $grid_columns_tablet Colonnes tablette.
	 * @param int   $grid_columns_mobile Colonnes mobile.
	 * @return void
	 */
	protected function render_brands_grid_variant( array $brands_list, $grid_columns, $grid_columns_tablet, $grid_columns_mobile ) {
		?>
		<div class="nova-brands-variant nova-brands-variant--grid">
			<div class="nova-brands-container nova-brands-grid">
				<div class="nova-brands-grid-inner" style="--grid-cols:<?php echo esc_attr( $grid_columns ); ?>;--grid-cols-tablet:<?php echo esc_attr( $grid_columns_tablet ); ?>;--grid-cols-mobile:<?php echo esc_attr( $grid_columns_mobile ); ?>;--grid-cols-current:<?php echo esc_attr( $grid_columns ); ?>;">
					<?php foreach ( $brands_list as $index => $brand ) : ?>
						<div class="nova-brands-item" data-index="<?php echo esc_attr( $index ); ?>">
							<?php $this->render_brand_item( $brand ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
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

		// Configuration grid (quand slider dÃ©sactivÃ©)
		$grid_columns        = isset( $settings['grid_columns'] ) ? intval( $settings['grid_columns'] ) : 4;
		$grid_columns_tablet = isset( $settings['grid_columns_tablet'] ) ? intval( $settings['grid_columns_tablet'] ) : 3;
		$grid_columns_mobile = isset( $settings['grid_columns_mobile'] ) ? intval( $settings['grid_columns_mobile'] ) : 2;
		if ( ! $grid_columns ) $grid_columns = 4;
		if ( ! $grid_columns_tablet ) $grid_columns_tablet = $grid_columns;
		if ( ! $grid_columns_mobile ) $grid_columns_mobile = 2;

		// Configuration slider
		$auto_width = $settings['slider_auto_width'] === 'yes';
		
		// RÃ©cupÃ©rer les valeurs responsive (peuvent Ãªtre un tableau avec 'size', 'tablet', 'mobile' ou directement un nombre)
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

		$marquee_enable = $enable_slider && ! empty( $settings['slider_marquee_enable'] ) && 'yes' === $settings['slider_marquee_enable'];

		$slider_config = [
			'autoplay' => ! $marquee_enable && $settings['slider_autoplay'] === 'yes',
			'autoplayDelay' => intval( $settings['slider_autoplay_delay'] ),
			'loop' => $marquee_enable ? true : ( $settings['slider_loop'] === 'yes' ),
			'speed' => intval( $settings['slider_speed'] ),
			'autoWidth' => $auto_width || $marquee_enable,
			'slidesPerView' => ( $auto_width || $marquee_enable ) ? 'auto' : $slides_per_view,
			'slidesPerViewTablet' => ( $auto_width || $marquee_enable ) ? 'auto' : $slides_per_view_tablet,
			'slidesPerViewMobile' => ( $auto_width || $marquee_enable ) ? 'auto' : $slides_per_view_mobile,
			'spaceBetween' => intval( $settings['slider_space_between'] ),
			'navigation' => ! $marquee_enable && $settings['slider_navigation'] === 'yes',
			'pagination' => ! $marquee_enable && $settings['slider_pagination'] === 'yes',
			'marquee' => $marquee_enable,
			'marqueeSpeed' => isset( $settings['slider_marquee_speed'] ) ? intval( $settings['slider_marquee_speed'] ) : 8000,
			'marqueePauseOnHover' => ! isset( $settings['slider_marquee_pause_hover'] ) || 'yes' === $settings['slider_marquee_pause_hover'],
		];

		$display_mode_config = $this->get_brands_display_mode_config( $settings );

		// Animation config
		$animation_config = [
			'enable' => $animation_enable,
			'delay' => intval( $settings['animation_delay'] ),
			'duration' => intval( $settings['animation_duration'] ),
			'translateY' => isset( $settings['animation_translate_y'] ) ? intval( $settings['animation_translate_y'] ) : 30,
		];

		?>
		<?php
		$brands_fallback_mode = $enable_slider ? ( $display_mode_config['desktop'] ?? 'slider' ) : 'grid';
		?>
		<div class="nova-brands-widget nova-brands-fallback-<?php echo esc_attr( sanitize_html_class( $brands_fallback_mode ) ); ?>"
			data-enable-slider="<?php echo esc_attr( $enable_slider ? '1' : '0' ); ?>"
			data-display-mode-config="<?php echo esc_attr( wp_json_encode( $display_mode_config ) ); ?>"
			data-slider-config="<?php echo esc_attr( wp_json_encode( $slider_config ) ); ?>"
			data-animation-config="<?php echo esc_attr( wp_json_encode( $animation_config ) ); ?>">
			<?php if ( ! empty( $content_text ) ) : ?>
				<div class="nova-brands-text">
					<?php echo wp_kses_post( $content_text ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $brands_list ) ) : ?>
				<?php
				$this->render_brands_slider_variant( $brands_list, $slider_config );
				$this->render_brands_grid_variant( $brands_list, $grid_columns, $grid_columns_tablet, $grid_columns_mobile );
				?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Rendu d'un item de logo
	 */
	private function render_brand_item( $brand ) {
		// VÃ©rifier diffÃ©rentes structures possibles pour l'image
		$logo_url = '';
		if ( isset( $brand['brand_logo']['url'] ) ) {
			$logo_url = $brand['brand_logo']['url'];
		} elseif ( isset( $brand['brand_logo']['id'] ) ) {
			// Si on a un ID, rÃ©cupÃ©rer l'URL
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

