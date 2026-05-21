<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Icons_Manager;
use \Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Carousel - Carousel avec titre, texte, bouton et cartes
 */
class Carousel_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-carousel';
	}

	/**
	 * Constructeur du widget
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );
		
		// Hook pour ajouter le CSS personnalisé lors de la génération du CSS
		add_action( 'elementor/element/parse_css', [ $this, 'add_typography_css_to_element' ], 10, 2 );
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Carousel', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-slider';
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
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		// NE PAS ajouter Swiper ici - il sera chargé conditionnellement dans render() uniquement pour shuffle-card
		return [ 'owl-carousel', 'nova-carousel-script' ];
	}

	/**
	 * Récupère les dépendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		// NE PAS ajouter Swiper ici - il sera chargé conditionnellement dans render() uniquement pour shuffle-card
		return [ 'owl-carousel', 'owl-carousel-theme', 'nova-carousel-style' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// Section Source de données
		$this->start_controls_section(
			'section_data_source',
			[
				'label' => esc_html__( 'Source de données', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'data_source',
			[
				'label' => esc_html__( 'Source', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => [
					'manual' => esc_html__( 'Manuel', 'NOVA-addons' ),
					'post_type' => esc_html__( 'Post Type', 'NOVA-addons' ),
				],
			]
		);

		// Options pour Post Type
		$this->add_control(
			'post_type',
			[
				'label' => esc_html__( 'Post Type', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $this->get_post_types(),
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Nombre de posts', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 50,
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'order_by',
			[
				'label' => esc_html__( 'Trier par', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date' => esc_html__( 'Date', 'NOVA-addons' ),
					'title' => esc_html__( 'Titre', 'NOVA-addons' ),
					'menu_order' => esc_html__( 'Ordre du menu', 'NOVA-addons' ),
					'rand' => esc_html__( 'Aléatoire', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => esc_html__( 'Ordre', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC' => esc_html__( 'Croissant', 'NOVA-addons' ),
					'DESC' => esc_html__( 'Décroissant', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		// Options d'affichage pour Post Type
		$this->add_control(
			'show_post_title',
			[
				'label' => esc_html__( 'Afficher le titre', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'post_title_tag',
			[
				'label' => esc_html__( 'Balise HTML du titre', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => [
					'h1' => esc_html__( 'H1', 'NOVA-addons' ),
					'h2' => esc_html__( 'H2', 'NOVA-addons' ),
					'h3' => esc_html__( 'H3', 'NOVA-addons' ),
					'h4' => esc_html__( 'H4', 'NOVA-addons' ),
					'h5' => esc_html__( 'H5', 'NOVA-addons' ),
					'h6' => esc_html__( 'H6', 'NOVA-addons' ),
					'p' => esc_html__( 'Paragraphe (P)', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
					'show_post_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_post_excerpt',
			[
				'label' => esc_html__( 'Afficher l\'extrait', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label' => esc_html__( 'Longueur de l\'extrait', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 20,
				'min' => 5,
				'max' => 100,
				'condition' => [
					'data_source' => 'post_type',
					'show_post_excerpt' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_post_date',
			[
				'label' => esc_html__( 'Afficher la date', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'show_post_author',
			[
				'label' => esc_html__( 'Afficher l\'auteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'show_post_item_button',
			[
				'label' => esc_html__( 'Afficher un bouton par post', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'post_item_button_text',
			[
				'label' => esc_html__( 'Texte du bouton (post)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Lire plus', 'NOVA-addons' ),
				'condition' => [
					'data_source' => 'post_type',
					'show_post_item_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'post_item_button_icon',
			[
				'label' => esc_html__( 'Icône du bouton (post)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'eicon-chevron-right',
					'library' => 'eicons',
				],
				'condition' => [
					'data_source' => 'post_type',
					'show_post_item_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'post_item_button_icon_position',
			[
				'label' => esc_html__( 'Position de l\'icône (post)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'before' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
					'after' => esc_html__( 'Après le texte', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
					'show_post_item_button' => 'yes',
				],
			]
		);

		// Source d'image pour Post Type
		$this->add_control(
			'image_source',
			[
				'label' => esc_html__( 'Source de l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'featured',
				'options' => [
					'featured' => esc_html__( 'Image à la une', 'NOVA-addons' ),
					'acf' => esc_html__( 'Champ ACF', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'acf_image_field',
			[
				'label' => esc_html__( 'Clé du champ ACF (Image)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: image_field', 'NOVA-addons' ),
				'condition' => [
					'data_source' => 'post_type',
					'image_source' => 'acf',
				],
			]
		);

		$this->end_controls_section();

		// Section Contenu Principal
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Contenu Principal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'title_text',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez le titre', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'description_text',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez la description', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez le texte du bouton', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_link',
			[
				'label' => esc_html__( 'Lien du bouton', 'NOVA-addons' ),
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

		$this->end_controls_section();

		// Section Configuration du Slider
		$this->start_controls_section(
			'section_slider_settings',
			[
				'label' => esc_html__( 'Configuration du Slider', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'slides_to_show',
			[
				'label' => esc_html__( 'Slides à afficher', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'slides_to_scroll',
			[
				'label' => esc_html__( 'Slides à faire défiler', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'slider_speed',
			[
				'label' => esc_html__( 'Vitesse de transition (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'ms' ],
				'range' => [
					'ms' => [
						'min' => 100,
						'max' => 3000,
						'step' => 100,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 500,
				],
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_responsive_control(
			'display_mode',
			[
				'label' => esc_html__( 'Mode d\'affichage', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'slider',
				'tablet_default' => 'slider',
				'mobile_default' => 'slider',
				'options' => [
					'slider' => esc_html__( 'Slider', 'NOVA-addons' ),
					'grid' => esc_html__( 'Grid', 'NOVA-addons' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__( 'Lecture automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'label' => esc_html__( 'Vitesse de lecture automatique (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'ms' ],
				'range' => [
					'ms' => [
						'min' => 1000,
						'max' => 10000,
						'step' => 500,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 3000,
				],
				'condition' => [
					'autoplay' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label' => esc_html__( 'Pause au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'autoplay' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'infinite_loop',
			[
				'label' => esc_html__( 'Boucle infinie', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'show_arrows',
			[
				'label' => esc_html__( 'Afficher les flèches', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'arrow_prev_icon',
			[
				'label' => esc_html__( 'Icône Précédent', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'eicon-chevron-left',
					'library' => 'eicons',
				],
				'condition' => [
					'show_arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrow_next_icon',
			[
				'label' => esc_html__( 'Icône Suivant', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'eicon-chevron-right',
					'library' => 'eicons',
				],
				'condition' => [
					'show_arrows' => 'yes',
				],
			]
		);


		$this->add_control(
			'navigation_position',
			[
				'label' => esc_html__( 'Position de la navigation', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'content-2',
				'options' => [
					'content-2' => esc_html__( 'Dans Contenu 2 (avec bouton)', 'NOVA-addons' ),
					'left' => esc_html__( 'À gauche du slider', 'NOVA-addons' ),
					'right' => esc_html__( 'À droite du slider', 'NOVA-addons' ),
					'top' => esc_html__( 'Au-dessus du slider', 'NOVA-addons' ),
					'bottom' => esc_html__( 'En dessous du slider', 'NOVA-addons' ),
					'outside' => esc_html__( 'De chaque côté (Prev à gauche, Next à droite)', 'NOVA-addons' ),
				],
				'condition' => [
					'show_arrows' => 'yes',
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'show_dots',
			[
				'label' => esc_html__( 'Afficher les points de navigation', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'display_mode' => 'slider',
				],
				'frontend_available' => true,
			]
		);

		// Contrôles pour le mode Grid (defaults scalaires + {{VALUE}} pour persistance correcte)
		$this->add_responsive_control(
			'grid_columns',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'condition' => [
					'display_mode' => 'grid',
				],
				// Pas de selectors - géré via CSS custom properties + JS pour l'éditeur
			]
		);

		$this->add_responsive_control(
			'grid_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'condition' => [
					'display_mode' => 'grid',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-grid, {{WRAPPER}} .nova-carousel-widget.responsive-display-mode .nova-carousel-slider' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);


		$this->add_control(
			'space_between',
			[
				'label' => esc_html__( 'Espacement entre les slides', 'NOVA-addons' ),
				'condition' => [
					'display_mode' => 'slider',
				],
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
					'unit' => 'px',
					'size' => 20,
				],
				'frontend_available' => true,
			]
		);

		// --- Options Owl Carousel avancées ---
		$this->add_control(
			'owl_advanced_heading',
			[
				'label' => esc_html__( 'Options avancées Owl Carousel', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'display_mode' => 'slider' ],
			]
		);

		$this->add_control(
			'owl_center',
			[
				'label' => esc_html__( 'Centrer les slides', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_stage_padding',
			[
				'label' => esc_html__( 'Stage Padding (px)', 'NOVA-addons' ),
				'description' => esc_html__( 'Padding gauche/droite du stage pour voir les slides adjacentes.', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 300,
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_auto_width',
			[
				'label' => esc_html__( 'Largeur automatique', 'NOVA-addons' ),
				'description' => esc_html__( 'Chaque slide prend sa propre largeur.', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_auto_height',
			[
				'label' => esc_html__( 'Hauteur automatique', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_rtl',
			[
				'label' => esc_html__( 'Mode RTL (droite à gauche)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_mouse_drag',
			[
				'label' => esc_html__( 'Drag souris', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_touch_drag',
			[
				'label' => esc_html__( 'Drag tactile', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_pull_drag',
			[
				'label' => esc_html__( 'Pull drag (rebond)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_free_drag',
			[
				'label' => esc_html__( 'Free drag (libre)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_lazy_load',
			[
				'label' => esc_html__( 'Lazy Load images', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_animate_out',
			[
				'label' => esc_html__( 'Animation sortie (CSS class)', 'NOVA-addons' ),
				'description' => esc_html__( 'Ex: fadeOut, zoomOut (nécessite animate.css)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_animate_in',
			[
				'label' => esc_html__( 'Animation entrée (CSS class)', 'NOVA-addons' ),
				'description' => esc_html__( 'Ex: fadeIn, zoomIn (nécessite animate.css)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_start_position',
			[
				'label' => esc_html__( 'Position de départ (index)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_nav_rewind',
			[
				'label' => esc_html__( 'Nav Rewind (retour au début)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_nav_speed',
			[
				'label' => esc_html__( 'Vitesse navigation (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 3000,
				'description' => esc_html__( '0 = utilise la vitesse globale.', 'NOVA-addons' ),
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_dots_speed',
			[
				'label' => esc_html__( 'Vitesse dots (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 3000,
				'description' => esc_html__( '0 = utilise la vitesse globale.', 'NOVA-addons' ),
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'owl_fluidspeed',
			[
				'label' => esc_html__( 'Fluid Speed (ms)', 'NOVA-addons' ),
				'description' => esc_html__( 'Vitesse de transition fluide lors du redimensionnement.', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 3000,
				'condition' => [ 'display_mode' => 'slider' ],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();

		// Section Options d'affichage
		$this->start_controls_section(
			'section_display_options',
			[
				'label' => esc_html__( 'Options d\'affichage', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'image_as_background',
			[
				'label' => esc_html__( 'Image en background', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Afficher l\'image comme background au lieu d\'une balise img', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_enable',
			[
				'label' => esc_html__( 'Background créatif avec masque', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Ajouter un masque SVG aléatoire en bas de chaque item', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_color',
			[
				'label' => esc_html__( 'Couleur du masque', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'condition' => [
					'creative_background_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'creative_background_random_rotation',
			[
				'label' => esc_html__( 'Rotation aléatoire (180deg)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Applique une rotation de 180° de manière aléatoire sur chaque masque SVG', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_random_flip',
			[
				'label' => esc_html__( 'Flip horizontal aléatoire', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Applique un flip horizontal (scaleX) de manière aléatoire sur chaque masque SVG', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'image_overlay_enable',
			[
				'label' => esc_html__( 'Activer l\'overlay sur l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'image_overlay_color',
			[
				'label' => esc_html__( 'Couleur de l\'overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#00000033',
				'alpha' => true,
				'condition' => [
					'image_overlay_enable' => 'yes',
				],
				'description' => esc_html__( 'Utilisez le format rgba ou ajoutez la transparence directement (ex: #00000033)', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// Section Items Manuels
		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Items du Carousel', 'NOVA-addons' ),
				'condition' => [
					'data_source' => 'manual',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_image',
			[
				'label' => esc_html__( 'Image', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => '',
				],
			]
		);

		// ── Média initial : image ou vidéo ────────────────────────────────
		$repeater->add_control(
			'item_media_type',
			[
				'label'   => esc_html__( 'Type de média initial', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image', 'NOVA-addons' ),
					'video' => esc_html__( 'Vidéo', 'NOVA-addons' ),
				],
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_video_url',
			[
				'label'       => esc_html__( 'Vidéo (médiathèque)', 'NOVA-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => [ 'video' ],
				'default'     => [ 'url' => '' ],
				'description' => esc_html__( 'Sélectionnez une vidéo depuis la médiathèque WordPress (MP4 recommandé).', 'NOVA-addons' ),
				'condition'   => [ 'item_media_type' => 'video' ],
			]
		);

		$repeater->add_control(
			'item_video_loop',
			[
				'label'     => esc_html__( 'Boucle vidéo', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [ 'item_media_type' => 'video' ],
			]
		);

		$repeater->add_control(
			'item_video_muted',
			[
				'label'     => esc_html__( 'Vidéo muette', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [ 'item_media_type' => 'video' ],
			]
		);

		// ── Média au hover ────────────────────────────────────────────────
		$repeater->add_control(
			'item_hover_media_heading',
			[
				'label'     => esc_html__( '— Média au hover —', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_hover_media_type',
			[
				'label'   => esc_html__( 'Type de média au hover', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'  => esc_html__( 'Aucun (garder le même)', 'NOVA-addons' ),
					'image' => esc_html__( 'Image différente', 'NOVA-addons' ),
					'video' => esc_html__( 'Vidéo', 'NOVA-addons' ),
				],
			]
		);

		$repeater->add_control(
			'item_hover_image',
			[
				'label'     => esc_html__( 'Image au hover', 'NOVA-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [ 'url' => '' ],
				'condition' => [ 'item_hover_media_type' => 'image' ],
			]
		);

		$repeater->add_control(
			'item_hover_video_url',
			[
				'label'       => esc_html__( 'Vidéo au hover (médiathèque)', 'NOVA-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => [ 'video' ],
				'default'     => [ 'url' => '' ],
				'description' => esc_html__( 'Sélectionnez une vidéo depuis la médiathèque WordPress.', 'NOVA-addons' ),
				'condition'   => [ 'item_hover_media_type' => 'video' ],
			]
		);

		$repeater->add_control(
			'item_hover_video_loop',
			[
				'label'     => esc_html__( 'Boucle vidéo hover', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [ 'item_hover_media_type' => 'video' ],
			]
		);

		$repeater->add_control(
			'item_hover_video_muted',
			[
				'label'     => esc_html__( 'Vidéo hover muette', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [ 'item_hover_media_type' => 'video' ],
			]
		);

		// ── Texte au hover ────────────────────────────────────────────────
		$repeater->add_control(
			'item_hover_text_heading',
			[
				'label'     => esc_html__( '— Texte au hover —', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_hover_text_enable',
			[
				'label'     => esc_html__( 'Afficher un texte au hover', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => '',
			]
		);

		$repeater->add_control(
			'item_hover_text',
			[
				'label'       => esc_html__( 'Texte au hover', 'NOVA-addons' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => '',
				'placeholder' => esc_html__( 'Texte affiché au survol…', 'NOVA-addons' ),
				'condition'   => [ 'item_hover_text_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_hover_text_position',
			[
				'label'   => esc_html__( 'Position du texte hover', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'top'    => esc_html__( 'Haut', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'bottom' => esc_html__( 'Bas', 'NOVA-addons' ),
				],
				'condition' => [ 'item_hover_text_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_hover_text_overlay_color',
			[
				'label'       => esc_html__( 'Couleur overlay derrière le texte', 'NOVA-addons' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => 'rgba(0,0,0,0.45)',
				'description' => esc_html__( 'Fond semi-transparent affiché derrière le texte hover. Laissez vide pour aucun overlay.', 'NOVA-addons' ),
				'condition'   => [ 'item_hover_text_enable' => 'yes' ],
			]
		);

		// ── Popup au clic — contenu par item ─────────────────────────────
		$repeater->add_control(
			'item_popup_content_heading',
			[
				'label'     => esc_html__( '— Contenu Popup —', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_popup_enable',
			[
				'label'     => esc_html__( 'Activer le popup pour cet item', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => '',
			]
		);

		// Type de média popup : image ou vidéo
		$repeater->add_control(
			'item_popup_media_type',
			[
				'label'       => esc_html__( 'Type de média', 'NOVA-addons' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'none'  => [ 'title' => esc_html__( 'Aucun', 'NOVA-addons' ),  'icon' => 'eicon-ban' ],
					'image' => [ 'title' => esc_html__( 'Image', 'NOVA-addons' ),  'icon' => 'eicon-image' ],
					'video' => [ 'title' => esc_html__( 'Vidéo', 'NOVA-addons' ),  'icon' => 'eicon-video-camera' ],
				],
				'default'   => 'none',
				'condition' => [ 'item_popup_enable' => 'yes' ],
			]
		);

		// Contenu popup — image
		$repeater->add_control(
			'item_popup_image',
			[
				'label'     => esc_html__( 'Image popup', 'NOVA-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [ 'url' => '' ],
				'condition' => [ 'item_popup_media_type' => 'image', 'item_popup_enable' => 'yes' ],
			]
		);

		// Contenu popup — vidéo (URL externe : YouTube, Vimeo, ou fichier direct)
		$repeater->add_control(
			'item_popup_video_type',
			[
				'label'     => esc_html__( 'Source vidéo', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'youtube',
				'options'   => [
					'youtube'  => esc_html__( 'YouTube', 'NOVA-addons' ),
					'vimeo'    => esc_html__( 'Vimeo', 'NOVA-addons' ),
					'hosted'   => esc_html__( 'Fichier hébergé (MP4…)', 'NOVA-addons' ),
				],
				'condition' => [ 'item_popup_media_type' => 'video', 'item_popup_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_popup_video_url',
			[
				'label'       => esc_html__( 'URL de la vidéo', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'https://www.youtube.com/watch?v=…',
				'label_block' => true,
				'condition'   => [
					'item_popup_enable'      => 'yes',
					'item_popup_media_type'  => 'video',
					'item_popup_video_type!' => 'hosted',
				],
			]
		);

		$repeater->add_control(
			'item_popup_video_hosted',
			[
				'label'      => esc_html__( 'Fichier vidéo (MP4)', 'NOVA-addons' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'video',
				'default'    => [ 'url' => '' ],
				'condition'  => [
					'item_popup_enable'     => 'yes',
					'item_popup_media_type' => 'video',
					'item_popup_video_type' => 'hosted',
				],
			]
		);

		$repeater->add_control(
			'item_popup_video_autoplay',
			[
				'label'     => esc_html__( 'Lecture automatique', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => '',
				'condition' => [ 'item_popup_enable' => 'yes', 'item_popup_media_type' => 'video' ],
			]
		);

		$repeater->add_control(
			'item_popup_video_mute',
			[
				'label'     => esc_html__( 'Muet', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => '',
				'condition' => [ 'item_popup_enable' => 'yes', 'item_popup_media_type' => 'video' ],
			]
		);

		$repeater->add_control(
			'item_popup_video_loop',
			[
				'label'     => esc_html__( 'Boucle', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => '',
				'condition' => [ 'item_popup_enable' => 'yes', 'item_popup_media_type' => 'video' ],
			]
		);

		// Contenu popup — texte 1 (titre)
		$repeater->add_control(
			'item_popup_text1',
			[
				'label'       => esc_html__( 'Texte 1 (titre)', 'NOVA-addons' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => '',
				'placeholder' => esc_html__( 'Titre du popup…', 'NOVA-addons' ),
				'condition'   => [ 'item_popup_enable' => 'yes' ],
			]
		);

		// Contenu popup — texte 2 (description)
		$repeater->add_control(
			'item_popup_text2',
			[
				'label'       => esc_html__( 'Texte 2 (description)', 'NOVA-addons' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => '',
				'placeholder' => esc_html__( 'Description du popup…', 'NOVA-addons' ),
				'condition'   => [ 'item_popup_enable' => 'yes' ],
			]
		);

		// Contenu popup — bouton (style Nova Cards)
		$repeater->add_control(
			'item_popup_link',
			[
				'label'     => esc_html__( 'Lien du bouton', 'NOVA-addons' ),
				'type'      => Controls_Manager::URL,
				'default'   => [ 'url' => '' ],
				'condition' => [ 'item_popup_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_popup_link_text',
			[
				'label'       => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
				'label_block' => true,
				'condition'   => [ 'item_popup_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_popup_btn_icon',
			[
				'label'       => esc_html__( 'Icône / image du bouton (SVG ou image)', 'NOVA-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'media_type'  => 'image',
				'description' => esc_html__( 'Sélectionnez une image ou un SVG depuis la médiathèque.', 'NOVA-addons' ),
				'condition'   => [ 'item_popup_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_popup_btn_icon_position',
			[
				'label'     => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => [
					'before' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
					'after'  => esc_html__( 'Après le texte', 'NOVA-addons' ),
				],
				'condition' => [ 'item_popup_enable' => 'yes' ],
			]
		);

		$repeater->add_control(
			'item_text',
			[
				'label' => esc_html__( 'Texte', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez le texte', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'item_date',
			[
				'label' => esc_html__( 'Date', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'placeholder' => esc_html__( 'ex: 29 septembre 2025', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'item_link',
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

		// Icône de l'item (possibilité d'utiliser une icône Elementor OU une image PNG/SVG)
		$repeater->add_control(
			'item_icon_type',
			[
				'label'       => esc_html__( 'Type d\'icône', 'NOVA-addons' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'icon'  => [
						'title' => esc_html__( 'Icône', 'NOVA-addons' ),
						'icon'  => 'eicon-star',
					],
					'image' => [
						'title' => esc_html__( 'Image (PNG, SVG…)', 'NOVA-addons' ),
						'icon'  => 'eicon-image-bold',
					],
					'none'  => [
						'title' => esc_html__( 'Aucun', 'NOVA-addons' ),
						'icon'  => 'eicon-ban',
					],
				],
				'default'     => 'icon',
				'separator'   => 'before',
			]
		);

		$repeater->add_control(
			'item_icon',
			[
				'label'   => esc_html__( 'Icône', 'NOVA-addons' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'eicon-chevron-right',
					'library' => 'eicons',
				],
				'condition' => [
					'item_icon_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'item_icon_image',
			[
				'label'     => esc_html__( 'Image d\'icône (PNG, SVG…)', 'NOVA-addons' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'item_icon_type' => 'image',
				],
				'description' => esc_html__( 'Permet d\'utiliser un fichier image (PNG, SVG, JPG…) comme icône de la carte.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'item_button_text',
			[
				'label' => esc_html__( 'Bouton - Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'En savoir plus', 'NOVA-addons' ),
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'item_button_link',
			[
				'label' => esc_html__( 'Bouton - Lien', 'NOVA-addons' ),
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

		$repeater->add_control(
			'item_button_icon',
			[
				'label' => esc_html__( 'Bouton - Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'eicon-chevron-right',
					'library' => 'eicons',
				],
			]
		);

		$repeater->add_control(
			'item_button_icon_position',
			[
				'label' => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'after',
				'options' => [
					'before' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
					'after' => esc_html__( 'Après le texte', 'NOVA-addons' ),
				],
			]
		);

		$repeater->add_control(
			'item_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'global' => [
					'default' => \Elementor\Core\Kits\Documents\Tabs\Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'items_list',
			[
				'label' => esc_html__( 'Items', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ item_text }}}',
			]
		);

		$this->end_controls_section();

		// ── Section Popup (configuration globale) ────────────────────────
		$this->start_controls_section(
			'section_popup_settings',
			[
				'label'     => esc_html__( 'Popup au clic', 'NOVA-addons' ),
				'condition' => [ 'data_source' => 'manual' ],
			]
		);

		$this->add_control(
			'popup_enable',
			[
				'label'     => esc_html__( 'Activer le popup au clic', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => '',
			]
		);

		// ── Mise en page du popup (style Nova Tabs) ───────────────────────
		$this->add_control(
			'popup_layout_heading',
			[
				'label'     => esc_html__( 'Mise en page', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'popup_enable' => 'yes' ],
			]
		);

		$this->add_control(
			'popup_layout',
			[
				'label'   => esc_html__( 'Structure', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'flat',
				'options' => [
					'flat'  => esc_html__( 'Flexible (1 seule zone)', 'NOVA-addons' ),
					'2_col' => esc_html__( '2 Colonnes (Gauche / Droite)', 'NOVA-addons' ),
				],
				'condition' => [ 'popup_enable' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'popup_col_gap',
			[
				'label'      => esc_html__( 'Espacement entre colonnes', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-layout-2_col' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'popup_enable' => 'yes',
					'popup_layout' => '2_col',
				],
			]
		);

		// Assignation colonne + ordre pour chaque élément
		$popup_elements_global = [
			'image' => esc_html__( 'Image', 'NOVA-addons' ),
			'text1' => esc_html__( 'Texte 1', 'NOVA-addons' ),
			'text2' => esc_html__( 'Texte 2', 'NOVA-addons' ),
			'link'  => esc_html__( 'Lien / Bouton', 'NOVA-addons' ),
		];

		foreach ( $popup_elements_global as $el_key => $el_label ) {
			$this->add_control(
				"popup_{$el_key}_col",
				[
					'label'   => $el_label . ' → ' . esc_html__( 'Colonne', 'NOVA-addons' ),
					'type'    => Controls_Manager::CHOOSE,
					'options' => [
						'left'  => [ 'title' => esc_html__( 'Gauche', 'NOVA-addons' ), 'icon' => 'eicon-h-align-left' ],
						'right' => [ 'title' => esc_html__( 'Droite', 'NOVA-addons' ), 'icon' => 'eicon-h-align-right' ],
					],
					'default'   => 'left',
					'condition' => [
						'popup_enable' => 'yes',
						'popup_layout' => '2_col',
					],
				]
			);

			$this->add_control(
				"popup_{$el_key}_order",
				[
					'label'     => $el_label . ' : ' . esc_html__( 'Ordre', 'NOVA-addons' ),
					'type'      => Controls_Manager::NUMBER,
					'default'   => '',
					'min'       => 1,
					'max'       => 10,
					'condition' => [ 'popup_enable' => 'yes' ],
				]
			);
		}

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
			'animation_delay',
			[
				'label' => esc_html__( 'Délai (ms)', 'NOVA-addons' ),
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

		// Section Style - Widget
		$this->start_controls_section(
			'section_style_widget',
			[
				'label' => esc_html__( 'Widget', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'widget_background',
				'selector' => '{{WRAPPER}} .nova-carousel-widget',
			]
		);

		$this->add_responsive_control(
			'widget_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'widget_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'widget_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'widget_border',
				'selector' => '{{WRAPPER}} .nova-carousel-widget',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'widget_box_shadow',
				'selector' => '{{WRAPPER}} .nova-carousel-widget',
			]
		);

		$this->add_responsive_control(
			'widget_width',
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
					'{{WRAPPER}} .nova-carousel-widget' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'widget_max_width',
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
					'{{WRAPPER}} .nova-carousel-widget' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'widget_align',
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
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-widget' => 'margin-left: {{VALUE}}; margin-right: {{VALUE}};',
				],
				'selectors_dictionary' => [
					'left' => '0; auto',
					'center' => 'auto',
					'right' => 'auto; 0',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Content Container
		$this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__( 'Conteneur de contenu', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 2000 ] ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-content' => 'max-width: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'content_direction',
			[
				'label' => esc_html__( 'Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row'    => [ 'title' => esc_html__( 'Horizontal', 'NOVA-addons' ), 'icon' => 'eicon-arrow-right' ],
					'column' => [ 'title' => esc_html__( 'Vertical', 'NOVA-addons' ), 'icon' => 'eicon-arrow-down' ],
				],
				'default' => 'row',
				'selectors' => [ '{{WRAPPER}} .nova-carousel-content' => 'flex-direction: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'content_align',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'flex-start'    => esc_html__( 'Début', 'NOVA-addons' ),
					'center'        => esc_html__( 'Centre', 'NOVA-addons' ),
					'flex-end'      => esc_html__( 'Fin', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Space Between', 'NOVA-addons' ),
				],
				'default' => 'flex-start',
				'selectors' => [ '{{WRAPPER}} .nova-carousel-content' => 'justify-content: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'content_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 200 ] ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-content' => 'gap: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'selector' => '{{WRAPPER}} .nova-carousel-content',
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// Section Style - Content 1 (Title & Description)
		$this->start_controls_section(
			'section_style_content_1',
			[
				'label' => esc_html__( 'Contenu 1 (Titre & Description)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_style_controls( 'content_1', '.nova-carousel-content-1' );

		$this->end_controls_section();

		// Section Style - Content 2 (Button & Navigation)
		$this->start_controls_section(
			'section_style_content_2',
			[
				'label' => esc_html__( 'Contenu 2 (Bouton & Navigation)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_style_controls( 'content_2', '.nova-carousel-content-2' );

		$this->end_controls_section();

		// Section Style - Container
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_style_controls( 'container', '.nova-carousel-container' );

		$this->end_controls_section();

		// Section Style - Titre
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
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
					'justify' => [
						'title' => esc_html__( 'Justifié', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-title, {{WRAPPER}} .nova-carousel-title h1, {{WRAPPER}} .nova-carousel-title h2, {{WRAPPER}} .nova-carousel-title h3, {{WRAPPER}} .nova-carousel-title h4, {{WRAPPER}} .nova-carousel-title h5, {{WRAPPER}} .nova-carousel-title h6, {{WRAPPER}} .nova-carousel-title p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .nova-carousel-title, {{WRAPPER}} .nova-carousel-title h1, {{WRAPPER}} .nova-carousel-title h2, {{WRAPPER}} .nova-carousel-title h3, {{WRAPPER}} .nova-carousel-title h4, {{WRAPPER}} .nova-carousel-title h5, {{WRAPPER}} .nova-carousel-title h6, {{WRAPPER}} .nova-carousel-title p',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .nova-carousel-description',
			]
		);

		$this->add_responsive_control(
			'description_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Bouton
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab(
			'button_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'selector' => '{{WRAPPER}} .nova-carousel-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_hover',
				'selector' => '{{WRAPPER}} .nova-carousel-button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .nova-carousel-button',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .nova-carousel-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .nova-carousel-button',
			]
		);

		$this->end_controls_section();

		// Section Style - Cartes
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 2000 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [ 'creative_background_enable!' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'card_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 2000 ] ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [ 'creative_background_enable!' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'card_creative_width',
			[
				'label' => esc_html__( 'Largeur (avec masque créatif)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 2000 ] ],
				'description' => esc_html__( 'La hauteur sera calculée automatiquement selon l\'aspect-ratio du SVG masque.', 'NOVA-addons' ),
				'condition' => [ 'creative_background_enable' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'card_background',
				'selector' => '{{WRAPPER}} .nova-carousel-item',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .nova-carousel-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .nova-carousel-item',
			]
		);

		$this->add_control(
			'card_hover_effect',
			[
				'label' => esc_html__( 'Effet au survol', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'lift',
				'options' => [
					'none'   => esc_html__( 'Aucun', 'NOVA-addons' ),
					'lift'   => esc_html__( 'Soulever', 'NOVA-addons' ),
					'scale'  => esc_html__( 'Agrandir', 'NOVA-addons' ),
					'shadow' => esc_html__( 'Ombre', 'NOVA-addons' ),
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Contenu de la carte
		$this->start_controls_section(
			'section_style_card_content',
			[
				'label' => esc_html__( 'Contenu de la carte', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_content_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 2000 ] ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'card_content_flex_direction',
			[
				'label' => esc_html__( 'Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row'    => [ 'title' => esc_html__( 'Horizontal', 'NOVA-addons' ), 'icon' => 'eicon-arrow-right' ],
					'column' => [ 'title' => esc_html__( 'Vertical', 'NOVA-addons' ), 'icon' => 'eicon-arrow-down' ],
				],
				'default' => 'column',
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'flex-direction: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'card_content_flex_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'gap: {{SIZE}}{{UNIT}};' ],
			]
		);

		$this->add_control(
			'card_content_align_items',
			[
				'label' => esc_html__( 'Alignement (Align Items)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'stretch',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'center'     => esc_html__( 'Centre', 'NOVA-addons' ),
					'flex-end'   => esc_html__( 'Fin', 'NOVA-addons' ),
					'stretch'    => esc_html__( 'Étirer', 'NOVA-addons' ),
				],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'align-items: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'card_content_justify_content',
			[
				'label' => esc_html__( 'Justification (Justify Content)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start'    => esc_html__( 'Début', 'NOVA-addons' ),
					'center'        => esc_html__( 'Centre', 'NOVA-addons' ),
					'flex-end'      => esc_html__( 'Fin', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Space Between', 'NOVA-addons' ),
				],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'justify-content: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'card_content_background',
				'selector' => '{{WRAPPER}} .nova-carousel-item-content',
			]
		);

		$this->add_responsive_control(
			'card_content_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->add_responsive_control(
			'card_content_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [ '{{WRAPPER}} .nova-carousel-item-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);

		$this->end_controls_section();

		// Section Style - Texte des cartes
		$this->start_controls_section(
			'section_style_card_text',
			[
				'label' => esc_html__( 'Texte des cartes', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// Heading - Texte principal
		$this->add_control(
			'card_text_heading',
			[
				'label' => esc_html__( 'Texte principal', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'card_text_align',
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
					'justify' => [
						'title' => esc_html__( 'Justifié', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_text_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-text, {{WRAPPER}} .nova-carousel-item-text h1, {{WRAPPER}} .nova-carousel-item-text h2, {{WRAPPER}} .nova-carousel-item-text h3, {{WRAPPER}} .nova-carousel-item-text h4, {{WRAPPER}} .nova-carousel-item-text h5, {{WRAPPER}} .nova-carousel-item-text h6, {{WRAPPER}} .nova-carousel-item-text p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'card_text_typography',
				'selector' => '{{WRAPPER}} .nova-carousel-item-text, {{WRAPPER}} .nova-carousel-item-text h1, {{WRAPPER}} .nova-carousel-item-text h2, {{WRAPPER}} .nova-carousel-item-text h3, {{WRAPPER}} .nova-carousel-item-text h4, {{WRAPPER}} .nova-carousel-item-text h5, {{WRAPPER}} .nova-carousel-item-text h6, {{WRAPPER}} .nova-carousel-item-text p',
			]
		);

		$this->add_responsive_control(
			'card_text_line_height',
			[
				'label' => esc_html__( 'Hauteur de ligne', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'lh', 'rlh', 'custom' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 200,
					],
					'em' => [
						'min' => 0.1,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-text, {{WRAPPER}} .nova-carousel-item-text h1, {{WRAPPER}} .nova-carousel-item-text h2, {{WRAPPER}} .nova-carousel-item-text h3, {{WRAPPER}} .nova-carousel-item-text h4, {{WRAPPER}} .nova-carousel-item-text h5, {{WRAPPER}} .nova-carousel-item-text h6, {{WRAPPER}} .nova-carousel-item-text p' => 'line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_text_letter_spacing',
			[
				'label' => esc_html__( 'Espacement des lettres', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => -5,
						'max' => 10,
						'step' => 0.1,
					],
					'em' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-text, {{WRAPPER}} .nova-carousel-item-text h1, {{WRAPPER}} .nova-carousel-item-text h2, {{WRAPPER}} .nova-carousel-item-text h3, {{WRAPPER}} .nova-carousel-item-text h4, {{WRAPPER}} .nova-carousel-item-text h5, {{WRAPPER}} .nova-carousel-item-text h6, {{WRAPPER}} .nova-carousel-item-text p' => 'letter-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_text_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_text_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Heading - Date
		$this->add_control(
			'card_date_heading',
			[
				'label' => esc_html__( 'Date', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'card_date_align',
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
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-date' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_date_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'card_date_typography',
				'selector' => '{{WRAPPER}} .nova-carousel-item-date',
			]
		);

		$this->add_responsive_control(
			'card_date_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_date_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-date' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Image des cartes
		$this->start_controls_section(
			'section_style_card_image',
			[
				'label' => esc_html__( 'Image des cartes', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_image_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 1000 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-image' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-carousel-item-image img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_image_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [ 'px' => [ 'min' => 0, 'max' => 1000 ] ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-image' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-carousel-item-image img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'card_image_object_fit',
			[
				'label' => esc_html__( 'Object Fit', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => [
					'cover'   => esc_html__( 'Cover', 'NOVA-addons' ),
					'contain' => esc_html__( 'Contain', 'NOVA-addons' ),
					'fill'    => esc_html__( 'Fill', 'NOVA-addons' ),
					'none'    => esc_html__( 'None', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-image img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_image_object_position',
			[
				'label' => esc_html__( 'Position', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center center',
				'options' => [
					'center center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'top center'    => esc_html__( 'Haut', 'NOVA-addons' ),
					'bottom center' => esc_html__( 'Bas', 'NOVA-addons' ),
					'left center'   => esc_html__( 'Gauche', 'NOVA-addons' ),
					'right center'  => esc_html__( 'Droite', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-image img' => 'object-position: {{VALUE}};',
				],
				'condition' => [ 'card_image_object_fit' => [ 'cover', 'contain' ] ],
			]
		);

		$this->add_responsive_control(
			'card_image_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-image'     => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
					'{{WRAPPER}} .nova-carousel-item-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_image_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_image_box_shadow',
				'selector' => '{{WRAPPER}} .nova-carousel-item-image img',
			]
		);

		$this->add_control(
			'card_image_css_filters_heading',
			[
				'label' => esc_html__( 'Filtres CSS', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name' => 'card_image_css_filters',
				'selector' => '{{WRAPPER}} .nova-carousel-item-image img',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Css_Filter::get_type(),
			[
				'name' => 'card_image_css_filters_hover',
				'label' => esc_html__( 'Filtres CSS (Hover)', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-carousel-item:hover .nova-carousel-item-image img',
			]
		);

		$this->end_controls_section();

		// Section Style - Icône des cartes
		$this->start_controls_section(
			'section_style_card_icon',
			[
				'label' => esc_html__( 'Icône des cartes', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 200,
						'step' => 1,
					],
					'em' => [
						'min' => 0.5,
						'max' => 10,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-carousel-item-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-carousel-item-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_icon_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 20,
						'step' => 0.1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_icon_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
						'step' => 1,
					],
					'em' => [
						'min' => 0,
						'max' => 20,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_icon_align',
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
					'{{WRAPPER}} .nova-carousel-item-icon' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-carousel-item-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-carousel-item-icon svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_icon_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_icon_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'card_icon_background',
				'selector' => '{{WRAPPER}} .nova-carousel-item-icon',
			]
		);

		$this->add_responsive_control(
			'card_icon_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_icon_border',
				'selector' => '{{WRAPPER}} .nova-carousel-item-icon',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_icon_box_shadow',
				'selector' => '{{WRAPPER}} .nova-carousel-item-icon',
			]
		);

		$this->end_controls_section();

		// Section Style - Bouton des cartes (item button)
		$this->start_controls_section(
			'section_style_item_button',
			[
				'label' => esc_html__( 'Bouton des cartes', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'item_button_tabs' );

		$this->start_controls_tab(
			'item_button_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'item_button_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_button_background',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_button_border',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_button_box_shadow',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'item_button_tab_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'item_button_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_button_background_hover',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_button_border_hover',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button:hover',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'item_button_typography',
				'selector' => '{{WRAPPER}} .nova-carousel-item-button',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'item_button_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_button_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_button_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_button_gap',
			[
				'label' => esc_html__( 'Espacement icône/texte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 50, 'step' => 1 ],
					'em' => [ 'min' => 0, 'max' => 5, 'step' => 0.1 ],
				],
				'default' => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'item_button_align',
			[
				'label' => esc_html__( 'Alignement', 'NOVA-addons' ),
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
					'{{WRAPPER}} .nova-carousel-item-button' => 'align-self: {{VALUE}};',
				],
			]
		);

		// Heading - Icône du bouton
		$this->add_control(
			'item_button_icon_heading',
			[
				'label' => esc_html__( 'Icône du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'item_button_icon_size',
			[
				'label' => esc_html__( 'Taille de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 8, 'max' => 100, 'step' => 1 ],
					'em' => [ 'min' => 0.5, 'max' => 5, 'step' => 0.1 ],
					'rem' => [ 'min' => 0.5, 'max' => 5, 'step' => 0.1 ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-carousel-item-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-carousel-item-button-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'item_button_icon_color',
			[
				'label' => esc_html__( 'Couleur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-carousel-item-button-icon svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .nova-carousel-item-button-icon i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_button_icon_color_hover',
			[
				'label' => esc_html__( 'Couleur de l\'icône (hover)', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-carousel-item-button:hover .nova-carousel-item-button-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-carousel-item-button:hover .nova-carousel-item-button-icon svg' => 'fill: {{VALUE}};',
					'{{WRAPPER}} .nova-carousel-item-button:hover .nova-carousel-item-button-icon i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// =====================================================================
		// SECTION STYLE — Tooltip Hover Text
		// =====================================================================
		// SECTION STYLE — Tooltip Hover Text
		// Utilise .nova-tooltip-[widget_id] pour cibler le tooltip global
		// =====================================================================
		$this->start_controls_section(
			'section_hover_tooltip_style',
			[
				'label' => esc_html__( 'Tooltip Hover Text', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'hover_tooltip_color',
			[
				'label'     => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'#nova-cursor-tooltip.nova-tooltip-{{ID}}' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_tooltip_bg',
			[
				'label'     => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.75)',
				'selectors' => [
					'#nova-cursor-tooltip.nova-tooltip-{{ID}}' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'hover_tooltip_typography',
				'label'    => esc_html__( 'Typographie', 'NOVA-addons' ),
				'selector' => '#nova-cursor-tooltip.nova-tooltip-{{ID}}',
			]
		);

		$this->add_responsive_control(
			'hover_tooltip_padding',
			[
				'label'      => esc_html__( 'Padding', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [
					'top' => '8', 'right' => '14', 'bottom' => '8', 'left' => '14', 'unit' => 'px',
				],
				'selectors'  => [
					'#nova-cursor-tooltip.nova-tooltip-{{ID}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hover_tooltip_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top' => '6', 'right' => '6', 'bottom' => '6', 'left' => '6', 'unit' => 'px',
				],
				'selectors'  => [
					'#nova-cursor-tooltip.nova-tooltip-{{ID}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'hover_tooltip_border',
				'label'    => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '#nova-cursor-tooltip.nova-tooltip-{{ID}}',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'hover_tooltip_shadow',
				'label'    => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '#nova-cursor-tooltip.nova-tooltip-{{ID}}',
				'fields_options' => [
					'box_shadow_type' => [ 'default' => 'yes' ],
					'box_shadow' => [
						'default' => [
							'horizontal' => 0, 'vertical' => 4, 'blur' => 16,
							'spread' => 0, 'color' => 'rgba(0,0,0,0.25)',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'hover_tooltip_max_width',
			[
				'label'      => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range'      => [ 'px' => [ 'min' => 80, 'max' => 600, 'step' => 4 ] ],
				'default'    => [ 'size' => 220, 'unit' => 'px' ],
				'selectors'  => [
					'#nova-cursor-tooltip.nova-tooltip-{{ID}}' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'hover_tooltip_offset_y',
			[
				'label'       => esc_html__( 'Décalage vertical (px)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => -100, 'max' => 100, 'step' => 2 ] ],
				'default'     => [ 'size' => -8 ],
				'description' => esc_html__( 'Décalage vertical par rapport au centre du curseur.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'hover_tooltip_offset_x',
			[
				'label'       => esc_html__( 'Décalage horizontal (px)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => -100, 'max' => 100, 'step' => 2 ] ],
				'default'     => [ 'size' => 16 ],
				'description' => esc_html__( 'Distance entre le curseur et le bord gauche du tooltip. Positif = à droite.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'hover_tooltip_transition',
			[
				'label'     => esc_html__( 'Durée transition (ms)', 'NOVA-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 50, 'max' => 600, 'step' => 10 ] ],
				'default'   => [ 'size' => 200 ],
				'selectors' => [
					'#nova-cursor-tooltip.nova-tooltip-{{ID}}' => 'transition: opacity {{SIZE}}ms ease, transform {{SIZE}}ms ease;',
				],
			]
		);

		$this->end_controls_section();

		$this->end_controls_section();

		// =====================================================================
		// SECTION STYLE — Popup
		// =====================================================================
		$this->start_controls_section(
			'section_popup_style',
			[
				'label' => esc_html__( 'Popup', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Overlay
		$this->add_control(
			'popup_overlay_color',
			[
				'label'     => esc_html__( 'Couleur overlay (fond)', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.6)',
				'selectors' => [
					'#nova-popup-overlay-{{ID}}' => 'background-color: {{VALUE}};',
				],
			]
		);

		// Boîte popup
		$this->add_control(
			'popup_heading_box',
			[
				'label'     => esc_html__( 'Boîte popup', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'popup_bg',
				'label'    => esc_html__( 'Fond', 'NOVA-addons' ),
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-box',
			]
		);

		$this->add_responsive_control(
			'popup_width',
			[
				'label'      => esc_html__( 'Largeur max', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', '%' ],
				'range'      => [ 'px' => [ 'min' => 200, 'max' => 1400, 'step' => 10 ] ],
				'default'    => [ 'size' => 640, 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-box' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_padding',
			[
				'label'      => esc_html__( 'Padding', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [ 'top' => '32', 'right' => '32', 'bottom' => '32', 'left' => '32', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [ 'top' => '12', 'right' => '12', 'bottom' => '12', 'left' => '12', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'popup_border',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-box',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'popup_shadow',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-box',
				'fields_options' => [
					'box_shadow_type' => [ 'default' => 'yes' ],
					'box_shadow' => [
						'default' => [
							'horizontal' => 0, 'vertical' => 20, 'blur' => 60,
							'spread' => 0, 'color' => 'rgba(0,0,0,0.3)',
						],
					],
				],
			]
		);

		// Bouton fermer
		$this->add_control(
			'popup_heading_close',
			[
				'label'     => esc_html__( 'Bouton fermer', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'popup_close_color',
			[
				'label'     => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-close' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_close_bg',
			[
				'label'     => esc_html__( 'Fond', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.08)',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-close' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_close_size',
			[
				'label'     => esc_html__( 'Taille (px)', 'NOVA-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 20, 'max' => 60 ] ],
				'default'   => [ 'size' => 36 ],
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-close' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px * 0.45);',
				],
			]
		);

		// Texte 1 popup
		$this->add_control(
			'popup_heading_text1',
			[
				'label'     => esc_html__( 'Texte 1 (titre)', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'popup_text1_typography',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-text1, #nova-popup-overlay-{{ID}} .nova-popup-text1 *',
			]
		);

		$this->add_control(
			'popup_text1_color',
			[
				'label'     => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-text1, #nova-popup-overlay-{{ID}} .nova-popup-text1 *' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_text1_margin',
			[
				'label'      => esc_html__( 'Marge', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => '0', 'right' => '0', 'bottom' => '12', 'left' => '0', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-text1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Texte 2 popup
		$this->add_control(
			'popup_heading_text2',
			[
				'label'     => esc_html__( 'Texte 2 (description)', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'popup_text2_typography',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-text2, #nova-popup-overlay-{{ID}} .nova-popup-text2 *',
			]
		);

		$this->add_control(
			'popup_text2_color',
			[
				'label'     => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-text2, #nova-popup-overlay-{{ID}} .nova-popup-text2 *' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_text2_margin',
			[
				'label'      => esc_html__( 'Marge', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => '0', 'right' => '0', 'bottom' => '20', 'left' => '0', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-text2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Image popup
		$this->add_control(
			'popup_heading_image',
			[
				'label'     => esc_html__( 'Image', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'popup_image_width',
			[
				'label'      => esc_html__( 'Largeur image', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 50, 'max' => 800 ] ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-image img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
				],
			]
		);

		$this->add_control(
			'popup_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius image', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_image_margin',
			[
				'label'      => esc_html__( 'Marge image', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => '0', 'right' => '0', 'bottom' => '20', 'left' => '0', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Bouton lien popup
		$this->add_control(
			'popup_heading_btn',
			[
				'label'     => esc_html__( 'Bouton lien', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'popup_btn_typography',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-btn',
			]
		);

		$this->add_responsive_control(
			'popup_btn_alignment',
			[
				'label'   => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [ 'title' => esc_html__( 'Gauche', 'NOVA-addons' ),  'icon' => 'eicon-text-align-left' ],
					'center'     => [ 'title' => esc_html__( 'Centre', 'NOVA-addons' ),  'icon' => 'eicon-text-align-center' ],
					'flex-end'   => [ 'title' => esc_html__( 'Droite', 'NOVA-addons' ),  'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'flex-start',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn-wrap' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_btn_padding',
			[
				'label'      => esc_html__( 'Padding', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [ 'top' => '12', 'right' => '28', 'bottom' => '12', 'left' => '28', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [ 'top' => '999', 'right' => '999', 'bottom' => '999', 'left' => '999', 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'popup_btn_border',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-btn',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'popup_btn_shadow',
				'selector' => '#nova-popup-overlay-{{ID}} .nova-popup-btn',
			]
		);

		$this->add_responsive_control(
			'popup_btn_icon_size',
			[
				'label'      => esc_html__( 'Taille icône', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 80 ] ],
				'default'    => [ 'size' => 24 ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_btn_gap',
			[
				'label'      => esc_html__( 'Espacement texte / icône', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 8, 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Tabs Normal / Hover
		$this->start_controls_tabs( 'popup_btn_tabs' );

		$this->start_controls_tab( 'popup_btn_tab_normal', [ 'label' => esc_html__( 'Normal', 'NOVA-addons' ) ] );

		$this->add_control(
			'popup_btn_color',
			[
				'label'     => esc_html__( 'Couleur texte', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_btn_bg',
			[
				'label'     => esc_html__( 'Fond', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#333333',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'popup_btn_tab_hover', [ 'label' => esc_html__( 'Hover', 'NOVA-addons' ) ] );

		$this->add_control(
			'popup_btn_color_hover',
			[
				'label'     => esc_html__( 'Couleur texte', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_btn_bg_hover',
			[
				'label'     => esc_html__( 'Fond', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_btn_border_hover',
			[
				'label'     => esc_html__( 'Couleur bordure', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_btn_hover_transform',
			[
				'label'      => esc_html__( 'Translation verticale (px)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => -20, 'max' => 20 ] ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-btn:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Animation popup
		$this->add_control(
			'popup_heading_anim',
			[
				'label'     => esc_html__( 'Animation', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'popup_animation',
			[
				'label'   => esc_html__( 'Type d\'animation', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade-scale',
				'options' => [
					'fade'       => esc_html__( 'Fade', 'NOVA-addons' ),
					'fade-scale' => esc_html__( 'Fade + Scale', 'NOVA-addons' ),
					'slide-up'   => esc_html__( 'Slide Up', 'NOVA-addons' ),
					'slide-down' => esc_html__( 'Slide Down', 'NOVA-addons' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'popup_animation_duration',
			[
				'label'     => esc_html__( 'Durée animation (ms)', 'NOVA-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 100, 'max' => 800, 'step' => 50 ] ],
				'default'   => [ 'size' => 300 ],
				'selectors' => [
					'#nova-popup-overlay-{{ID}}' => 'transition: opacity {{SIZE}}ms ease;',
					'#nova-popup-overlay-{{ID}} .nova-popup-box' => 'transition: opacity {{SIZE}}ms ease, transform {{SIZE}}ms cubic-bezier(0.34, 1.56, 0.64, 1);',
				],
			]
		);

		// ── Bouton Mute (vidéo hébergée) ──────────────────────────────────
		$this->add_control(
			'popup_mute_btn_heading',
			[
				'label'     => esc_html__( 'Bouton Son (vidéo)', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'popup_mute_btn_size',
			[
				'label'     => esc_html__( 'Taille du bouton', 'NOVA-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 20, 'max' => 80 ] ],
				'default'   => [ 'size' => 40 ],
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn svg' => 'width: calc({{SIZE}}px * 0.5); height: calc({{SIZE}}px * 0.5);',
				],
			]
		);

		$this->add_control(
			'popup_mute_btn_color',
			[
				'label'     => esc_html__( 'Couleur icône', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_mute_btn_bg',
			[
				'label'     => esc_html__( 'Fond du bouton', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.35)',
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_mute_btn_radius',
			[
				'label'     => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'   => [ 'top' => '50', 'right' => '50', 'bottom' => '50', 'left' => '50', 'unit' => '%' ],
				'selectors' => [
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_mute_btn_pos_top',
			[
				'label'      => esc_html__( 'Position Haut', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
				'default'    => [ 'size' => 12, 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_mute_btn_pos_left',
			[
				'label'      => esc_html__( 'Position Gauche', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 200 ], '%' => [ 'min' => 0, 'max' => 100 ] ],
				'default'    => [ 'size' => 12, 'unit' => 'px' ],
				'selectors'  => [
					'#nova-popup-overlay-{{ID}} .nova-popup-mute-btn' => 'left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

	} // end register_controls()

	/**
	 * Récupère la liste des SVG disponibles pour le background créatif
	 *
	 * @return array
	 */
	/**
	 * Ajoute les contrôles de layout communs (width, height, display, flex, grid)
	 * 
	 * @param string $prefix Préfixe pour les noms de contrôles (ex: 'content_1', 'container')
	 * @param string $selector Sélecteur CSS (ex: '.nova-carousel-content-1')
	 * @param array $defaults Valeurs par défaut pour les contrôles
	 */
	protected function add_layout_controls( $prefix, $selector, $defaults = [] ) {
		$defaults = wp_parse_args( $defaults, [
			'width' => null,
			'height' => null,
			'display' => 'flex',
			'flex_direction' => 'row',
			'flex_wrap' => 'nowrap',
			'align_items' => 'flex-start',
			'justify_content' => 'flex-start',
			'gap' => null,
		] );

		// Width
		$this->add_responsive_control(
			$prefix . '_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 2000, 'step' => 1 ],
					'%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Height
		$this->add_responsive_control(
			$prefix . '_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh', 'custom' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 2000, 'step' => 1 ],
					'%' => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Display
		$this->add_control(
			$prefix . '_display',
			[
				'label' => esc_html__( 'Type d\'affichage', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => $defaults['display'],
				'options' => [
					'flex' => esc_html__( 'Flex', 'NOVA-addons' ),
					'grid' => esc_html__( 'Grid', 'NOVA-addons' ),
					'block' => esc_html__( 'Block', 'NOVA-addons' ),
					'inline-block' => esc_html__( 'Inline Block', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'display: {{VALUE}};',
				],
			]
		);

		// Flex Direction
		$this->add_control(
			$prefix . '_flex_direction',
			[
				'label' => esc_html__( 'Direction Flex', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => $defaults['flex_direction'],
				'options' => [
					'row' => esc_html__( 'Row', 'NOVA-addons' ),
					'column' => esc_html__( 'Column', 'NOVA-addons' ),
					'row-reverse' => esc_html__( 'Row Reverse', 'NOVA-addons' ),
					'column-reverse' => esc_html__( 'Column Reverse', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'flex-direction: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'flex',
				],
			]
		);

		// Flex Wrap
		$this->add_control(
			$prefix . '_flex_wrap',
			[
				'label' => esc_html__( 'Flex Wrap', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => $defaults['flex_wrap'],
				'options' => [
					'nowrap' => esc_html__( 'No Wrap', 'NOVA-addons' ),
					'wrap' => esc_html__( 'Wrap', 'NOVA-addons' ),
					'wrap-reverse' => esc_html__( 'Wrap Reverse', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'flex-wrap: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'flex',
				],
			]
		);

		// Align Items
		$this->add_control(
			$prefix . '_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => $defaults['align_items'],
				'options' => [
					'flex-start' => esc_html__( 'Flex Start', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Flex End', 'NOVA-addons' ),
					'center' => esc_html__( 'Center', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Stretch', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Baseline', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'align-items: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'flex',
				],
			]
		);

		// Justify Content
		$this->add_control(
			$prefix . '_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => $defaults['justify_content'],
				'options' => [
					'flex-start' => esc_html__( 'Flex Start', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Flex End', 'NOVA-addons' ),
					'center' => esc_html__( 'Center', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Space Between', 'NOVA-addons' ),
					'space-around' => esc_html__( 'Space Around', 'NOVA-addons' ),
					'space-evenly' => esc_html__( 'Space Evenly', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'justify-content: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'flex',
				],
			]
		);

		// Gap
		if ( $defaults['gap'] !== false ) {
			$this->add_responsive_control(
				$prefix . '_gap',
				[
					'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'em', '%' ],
					'range' => [
						'px' => [ 'min' => 0, 'max' => 200, 'step' => 1 ],
					],
					'selectors' => [
						'{{WRAPPER}} ' . $selector => 'gap: {{SIZE}}{{UNIT}};',
					],
					'condition' => [
						$prefix . '_display' => [ 'flex', 'grid' ],
					],
				]
			);
		}

		// Grid Template Columns
		$this->add_responsive_control(
			$prefix . '_grid_template_columns',
			[
				'label' => esc_html__( 'Grid Template Columns', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'repeat(2, 1fr)',
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'grid-template-columns: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'grid',
				],
			]
		);

		// Grid Template Rows
		$this->add_responsive_control(
			$prefix . '_grid_template_rows',
			[
				'label' => esc_html__( 'Grid Template Rows', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'repeat(2, 1fr)',
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'grid-template-rows: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'grid',
				],
			]
		);

		// Grid Column Gap
		$this->add_responsive_control(
			$prefix . '_grid_column_gap',
			[
				'label' => esc_html__( 'Grid Column Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'column-gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					$prefix . '_display' => 'grid',
				],
			]
		);

		// Grid Row Gap
		$this->add_responsive_control(
			$prefix . '_grid_row_gap',
			[
				'label' => esc_html__( 'Grid Row Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'row-gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					$prefix . '_display' => 'grid',
				],
			]
		);

		// Grid Align Items
		$this->add_control(
			$prefix . '_grid_align_items',
			[
				'label' => esc_html__( 'Grid Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'stretch',
				'options' => [
					'start' => esc_html__( 'Start', 'NOVA-addons' ),
					'end' => esc_html__( 'End', 'NOVA-addons' ),
					'center' => esc_html__( 'Center', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Stretch', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Baseline', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'align-items: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'grid',
				],
			]
		);

		// Grid Justify Items
		$this->add_control(
			$prefix . '_grid_justify_items',
			[
				'label' => esc_html__( 'Grid Justify Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'stretch',
				'options' => [
					'start' => esc_html__( 'Start', 'NOVA-addons' ),
					'end' => esc_html__( 'End', 'NOVA-addons' ),
					'center' => esc_html__( 'Center', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Stretch', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'justify-items: {{VALUE}};',
				],
				'condition' => [
					$prefix . '_display' => 'grid',
				],
			]
		);
	}

	/**
	 * Génère le HTML d'un seul bouton de navigation (prev ou next)
	 * 
	 * @param array $settings Les paramètres du widget
	 * @param string $type 'prev' ou 'next'
	 * @return string Le HTML du bouton
	 */
	protected function render_single_navigation_button( $settings, $type = 'prev' ) {
		$icon = $type === 'prev' 
			? ( isset( $settings['arrow_prev_icon'] ) ? $settings['arrow_prev_icon'] : [] )
			: ( isset( $settings['arrow_next_icon'] ) ? $settings['arrow_next_icon'] : [] );
		
		$aria_label = $type === 'prev' 
			? esc_attr__( 'Précédent', 'NOVA-addons' )
			: esc_attr__( 'Suivant', 'NOVA-addons' );
		
		$default_icon = $type === 'prev'
			? '<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>'
			: '<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
		
		ob_start();
		?>
		<button class="nova-carousel-nav nova-carousel-<?php echo esc_attr( $type ); ?>" aria-label="<?php echo $aria_label; ?>">
			<?php
			$icon_rendered = false;
			
			if ( ! empty( $icon ) ) {
				ob_start();
				Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
				$icon_output = ob_get_clean();
				
				if ( ! empty( $icon_output ) ) {
					echo $icon_output;
					$icon_rendered = true;
				} else {
					$svg_url = null;
					
					if ( isset( $icon['library'] ) && ( $icon['library'] === 'svg' || $icon['library'] === 'svg-upload' ) ) {
						if ( isset( $icon['value']['url'] ) ) {
							$svg_url = $icon['value']['url'];
						} elseif ( isset( $icon['value'] ) && is_string( $icon['value'] ) ) {
							$svg_url = $icon['value'];
						}
					}
					
					if ( ! $svg_url && isset( $icon['value'] ) ) {
						if ( is_string( $icon['value'] ) && ( strpos( $icon['value'], '.svg' ) !== false || strpos( $icon['value'], 'http' ) === 0 ) ) {
							$svg_url = $icon['value'];
						} elseif ( is_array( $icon['value'] ) && isset( $icon['value']['url'] ) ) {
							$svg_url = $icon['value']['url'];
						}
					}
					
					if ( $svg_url ) {
						$svg_path = str_replace( content_url(), WP_CONTENT_DIR, $svg_url );
						if ( file_exists( $svg_path ) ) {
							$svg_content = file_get_contents( $svg_path );
							if ( $svg_content ) {
								$svg_content = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg_content );
								echo wp_kses( $svg_content, [
									'svg' => [ 'xmlns' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'fill' => [], 'class' => [], 'style' => [], 'preserveAspectRatio' => [] ],
									'rect' => [ 'width' => [], 'height' => [], 'rx' => [], 'ry' => [], 'transform' => [], 'fill' => [], 'stroke' => [], 'stroke-width' => [] ],
									'path' => [ 'd' => [], 'stroke' => [], 'stroke-width' => [], 'fill' => [], 'fill-rule' => [] ],
									'circle' => [ 'cx' => [], 'cy' => [], 'r' => [], 'fill' => [], 'stroke' => [] ],
									'g' => [ 'fill' => [], 'transform' => [] ],
								] );
								$icon_rendered = true;
							}
						}
					}
				}
			}
			
			if ( ! $icon_rendered ) {
				?>
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<?php echo $default_icon; ?>
				</svg>
				<?php
			}
			?>
		</button>
		<?php
		return ob_get_clean();
	}

	/**
	 * Génère le HTML des boutons de navigation
	 * 
	 * @param array $settings Les paramètres du widget
	 * @return string Le HTML des boutons de navigation
	 */
	protected function render_navigation_buttons( $settings ) {
		$prev_icon = isset( $settings['arrow_prev_icon'] ) ? $settings['arrow_prev_icon'] : [];
		$next_icon = isset( $settings['arrow_next_icon'] ) ? $settings['arrow_next_icon'] : [];
		
		ob_start();
		?>
		<div class="nova-carousel-navigation">
			<button class="nova-carousel-nav nova-carousel-prev" aria-label="<?php esc_attr_e( 'Précédent', 'NOVA-addons' ); ?>">
				<?php
				$icon_rendered = false;
				
				if ( ! empty( $prev_icon ) ) {
					ob_start();
					Icons_Manager::render_icon( $prev_icon, [ 'aria-hidden' => 'true' ] );
					$icon_output = ob_get_clean();
					
					if ( ! empty( $icon_output ) ) {
						echo $icon_output;
						$icon_rendered = true;
					} else {
						$svg_url = null;
						
						if ( isset( $prev_icon['library'] ) && ( $prev_icon['library'] === 'svg' || $prev_icon['library'] === 'svg-upload' ) ) {
							if ( isset( $prev_icon['value']['url'] ) ) {
								$svg_url = $prev_icon['value']['url'];
							} elseif ( isset( $prev_icon['value'] ) && is_string( $prev_icon['value'] ) ) {
								$svg_url = $prev_icon['value'];
							}
						}
						
						if ( ! $svg_url && isset( $prev_icon['value'] ) ) {
							if ( is_string( $prev_icon['value'] ) && ( strpos( $prev_icon['value'], '.svg' ) !== false || strpos( $prev_icon['value'], 'http' ) === 0 ) ) {
								$svg_url = $prev_icon['value'];
							} elseif ( is_array( $prev_icon['value'] ) && isset( $prev_icon['value']['url'] ) ) {
								$svg_url = $prev_icon['value']['url'];
							}
						}
						
						if ( $svg_url ) {
							$svg_path = str_replace( content_url(), WP_CONTENT_DIR, $svg_url );
							if ( file_exists( $svg_path ) ) {
								$svg_content = file_get_contents( $svg_path );
								if ( $svg_content ) {
									$svg_content = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg_content );
									echo wp_kses( $svg_content, [
										'svg' => [ 'xmlns' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'fill' => [], 'class' => [], 'style' => [], 'preserveAspectRatio' => [] ],
										'rect' => [ 'width' => [], 'height' => [], 'rx' => [], 'ry' => [], 'transform' => [], 'fill' => [], 'stroke' => [], 'stroke-width' => [] ],
										'path' => [ 'd' => [], 'stroke' => [], 'stroke-width' => [], 'fill' => [], 'fill-rule' => [] ],
										'circle' => [ 'cx' => [], 'cy' => [], 'r' => [], 'fill' => [], 'stroke' => [] ],
										'g' => [ 'fill' => [], 'transform' => [] ],
									] );
									$icon_rendered = true;
								}
							}
						}
					}
				}
				
				if ( ! $icon_rendered ) {
					?>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<?php
				}
				?>
			</button>
			<button class="nova-carousel-nav nova-carousel-next" aria-label="<?php esc_attr_e( 'Suivant', 'NOVA-addons' ); ?>">
				<?php
				$icon_rendered = false;
				
				if ( ! empty( $next_icon ) ) {
					ob_start();
					Icons_Manager::render_icon( $next_icon, [ 'aria-hidden' => 'true' ] );
					$icon_output = ob_get_clean();
					
					if ( ! empty( $icon_output ) ) {
						echo $icon_output;
						$icon_rendered = true;
					} else {
						$svg_url = null;
						
						if ( isset( $next_icon['library'] ) && ( $next_icon['library'] === 'svg' || $next_icon['library'] === 'svg-upload' ) ) {
							if ( isset( $next_icon['value']['url'] ) ) {
								$svg_url = $next_icon['value']['url'];
							} elseif ( isset( $next_icon['value'] ) && is_string( $next_icon['value'] ) ) {
								$svg_url = $next_icon['value'];
							}
						}
						
						if ( ! $svg_url && isset( $next_icon['value'] ) ) {
							if ( is_string( $next_icon['value'] ) && ( strpos( $next_icon['value'], '.svg' ) !== false || strpos( $next_icon['value'], 'http' ) === 0 ) ) {
								$svg_url = $next_icon['value'];
							} elseif ( is_array( $next_icon['value'] ) && isset( $next_icon['value']['url'] ) ) {
								$svg_url = $next_icon['value']['url'];
							}
						}
						
						if ( $svg_url ) {
							$svg_path = str_replace( content_url(), WP_CONTENT_DIR, $svg_url );
							if ( file_exists( $svg_path ) ) {
								$svg_content = file_get_contents( $svg_path );
								if ( $svg_content ) {
									$svg_content = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg_content );
									echo wp_kses( $svg_content, [
										'svg' => [ 'xmlns' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'fill' => [], 'class' => [], 'style' => [], 'preserveAspectRatio' => [] ],
										'rect' => [ 'width' => [], 'height' => [], 'rx' => [], 'ry' => [], 'transform' => [], 'fill' => [], 'stroke' => [], 'stroke-width' => [] ],
										'path' => [ 'd' => [], 'stroke' => [], 'stroke-width' => [], 'fill' => [], 'fill-rule' => [] ],
										'circle' => [ 'cx' => [], 'cy' => [], 'r' => [], 'fill' => [], 'stroke' => [] ],
										'g' => [ 'fill' => [], 'transform' => [] ],
									] );
									$icon_rendered = true;
								}
							}
						}
					}
				}
				
				if ( ! $icon_rendered ) {
					?>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<?php
				}
				?>
			</button>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Ajoute les contrôles de style communs (background, padding, margin, border, shadow)
	 * 
	 * @param string $prefix Préfixe pour les noms de contrôles
	 * @param string $selector Sélecteur CSS
	 */
	protected function add_style_controls( $prefix, $selector ) {
		// Background
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => $prefix . '_background',
				'selector' => '{{WRAPPER}} ' . $selector,
			]
		);

		// Padding
		$this->add_responsive_control(
			$prefix . '_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Margin
		$this->add_responsive_control(
			$prefix . '_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Border Radius
		$this->add_responsive_control(
			$prefix . '_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} ' . $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Border
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => $prefix . '_border',
				'selector' => '{{WRAPPER}} ' . $selector,
			]
		);

		// Box Shadow
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => $prefix . '_box_shadow',
				'selector' => '{{WRAPPER}} ' . $selector,
			]
		);
	}

	protected function get_creative_background_svgs() {
		if ( defined( 'NOVA_ADDONS_PLUGIN_DIR' ) && defined( 'NOVA_ADDONS_PLUGIN_URL' ) ) {
			$plugin_dir = NOVA_ADDONS_PLUGIN_DIR;
			$plugin_url = NOVA_ADDONS_PLUGIN_URL;
		} else {
			$plugin_path = plugin_dir_path( __FILE__ );
			$plugin_dir = dirname( dirname( $plugin_path ) );
			$plugin_url = plugin_dir_url( __FILE__ );
			$plugin_url = dirname( dirname( $plugin_url ) );
		}
		
		$svg_url_base = $plugin_url . 'assets/svg/';
		
		$svg_list = [
			'back-big-1.svg',
			'back-big-2.svg',
		];
		
		$svg_files = [];
		foreach ( $svg_list as $svg_file ) {
			$svg_files[] = $svg_url_base . $svg_file;
		}
		
		return $svg_files;
	}

	/**
	 * Récupère la liste des post types disponibles
	 *
	 * @return array
	 */
	private function get_post_types() {
		return function_exists( 'nova_addons_get_elementor_post_type_options' )
			? nova_addons_get_elementor_post_type_options()
			: array();
	}

	/**
	 * Récupère les posts selon les paramètres
	 *
	 * @param array $settings Settings du widget.
	 * @return array
	 */
	private function get_posts( $settings ) {
		$args = [
			'post_type' => $settings['post_type'],
			'posts_per_page' => $settings['posts_per_page'],
			'orderby' => $settings['order_by'],
			'order' => $settings['order'],
			'post_status' => 'publish',
		];

		$query = new \WP_Query( $args );
		$posts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				// Récupérer l'image
				$image_url = '';
				if ( $settings['image_source'] === 'featured' ) {
					$image_url = get_the_post_thumbnail_url( $post_id, 'large' );
				} elseif ( $settings['image_source'] === 'acf' && ! empty( $settings['acf_image_field'] ) ) {
					$acf_image = get_field( $settings['acf_image_field'], $post_id );
					if ( $acf_image ) {
						if ( is_array( $acf_image ) && isset( $acf_image['url'] ) ) {
							$image_url = $acf_image['url'];
						} elseif ( is_numeric( $acf_image ) ) {
							$image_url = wp_get_attachment_image_url( $acf_image, 'large' );
						} elseif ( is_string( $acf_image ) ) {
							$image_url = $acf_image;
						}
					}
				}

				$posts[] = [
					'id' => $post_id,
					'title' => get_the_title(),
					'excerpt' => $settings['show_post_excerpt'] === 'yes' ? wp_trim_words( get_the_excerpt(), $settings['excerpt_length'], '...' ) : '',
					'date' => $settings['show_post_date'] === 'yes' ? get_the_date( 'j F Y' ) : '',
					'author' => $settings['show_post_author'] === 'yes' ? get_the_author() : '',
					'image' => $image_url,
					'link' => get_permalink(),
					'show_excerpt' => $settings['show_post_excerpt'] === 'yes',
					'show_author' => $settings['show_post_author'] === 'yes',
				];
			}
			wp_reset_postdata();
		}

		return $posts;
	}

	/**
	 * Affiche le widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// ── Popup — lire la config globale tôt (utilisée dans le render des items) ──
		$popup_enable_global = ! empty( $settings['popup_enable'] ) && $settings['popup_enable'] === 'yes';

		// Préparer les items
		$items = [];
		if ( $settings['data_source'] === 'post_type' ) {
			$posts = $this->get_posts( $settings );
			$show_post_item_button = ! empty( $settings['show_post_item_button'] ) && $settings['show_post_item_button'] === 'yes';
			$post_item_button_text = isset( $settings['post_item_button_text'] ) ? $settings['post_item_button_text'] : '';
			$post_item_button_icon = isset( $settings['post_item_button_icon'] ) ? $settings['post_item_button_icon'] : [];
			foreach ( $posts as $post ) {
				$item_text = '';
				if ( $settings['show_post_title'] === 'yes' ) {
					$item_text = $post['title'];
				}
				if ( $post['show_excerpt'] && ! empty( $post['excerpt'] ) ) {
					$item_text .= ( ! empty( $item_text ) ? ' ' : '' ) . $post['excerpt'];
				}
				
				$item_date = $post['date'];
				if ( $post['show_author'] && ! empty( $post['author'] ) ) {
					$item_date .= ( ! empty( $item_date ) ? ' • ' : '' ) . $post['author'];
				}
				
				$items[] = [
					'item_image' => [
						'url' => $post['image'],
					],
					'item_text' => $item_text,
					'item_date' => $item_date,
					'item_link' => [
						'url' => $post['link'],
						'is_external' => false,
						'nofollow' => false,
					],
					'item_button_text' => $show_post_item_button ? $post_item_button_text : '',
					'item_button_link' => [
						'url' => $show_post_item_button ? $post['link'] : '',
						'is_external' => false,
						'nofollow' => false,
					],
					'item_button_icon' => $show_post_item_button ? $post_item_button_icon : [],
					'item_button_icon_position' => $show_post_item_button ? ( isset( $settings['post_item_button_icon_position'] ) ? $settings['post_item_button_icon_position'] : 'after' ) : 'after',
				];
			}
		} else {
			$items = $settings['items_list'];
		}

		// Lien du bouton
		$button_link = $settings['button_link'];
		$this->add_link_attributes( 'button_link', $button_link );

		// Options d'affichage
		$image_as_background = ! empty( $settings['image_as_background'] ) && $settings['image_as_background'] === 'yes';
		$creative_background_enable = ! empty( $settings['creative_background_enable'] ) && $settings['creative_background_enable'] === 'yes';
		$creative_background_color = ! empty( $settings['creative_background_color'] ) ? $settings['creative_background_color'] : '#ffffff';
		$image_overlay_enable = ! empty( $settings['image_overlay_enable'] ) && $settings['image_overlay_enable'] === 'yes';
		$image_overlay_color = ! empty( $settings['image_overlay_color'] ) ? $settings['image_overlay_color'] : '#00000033';
		
		// Récupérer les SVG pour le background créatif
		$svg_backgrounds = [];
		if ( $creative_background_enable ) {
			$svg_backgrounds = $this->get_creative_background_svgs();
		}

		$is_swiper_widget = $this instanceof Carousel_Swiper_Widget;

		$display_mode_desktop = isset( $settings['display_mode'] ) && ! empty( $settings['display_mode'] ) ? $settings['display_mode'] : 'slider';
		$display_mode_tablet = isset( $settings['display_mode_tablet'] ) && ! empty( $settings['display_mode_tablet'] ) ? $settings['display_mode_tablet'] : $display_mode_desktop;
		$display_mode_mobile = isset( $settings['display_mode_mobile'] ) && ! empty( $settings['display_mode_mobile'] ) ? $settings['display_mode_mobile'] : $display_mode_tablet;
		$display_mode_config = [
			'desktop' => $display_mode_desktop,
			'tablet'  => $display_mode_tablet,
			'mobile'  => $display_mode_mobile,
		];

		$is_mixed_display_mode = ( $display_mode_desktop !== $display_mode_tablet ) || ( $display_mode_desktop !== $display_mode_mobile ) || ( $display_mode_tablet !== $display_mode_mobile );
		$has_any_slider_mode = in_array( 'slider', $display_mode_config, true );
		$is_grid_mode = ( $display_mode_desktop === 'grid' ) && ! ( $is_swiper_widget && $is_mixed_display_mode );
		$grid_columns_initial = isset( $settings['grid_columns'] ) ? intval( $settings['grid_columns'] ) : 3;
		if ( $grid_columns_initial < 1 ) $grid_columns_initial = 3;
		$grid_columns_tablet = isset( $settings['grid_columns_tablet'] ) ? intval( $settings['grid_columns_tablet'] ) : 0;
		if ( $grid_columns_tablet < 1 ) $grid_columns_tablet = $grid_columns_initial;
		$grid_columns_mobile = isset( $settings['grid_columns_mobile'] ) ? intval( $settings['grid_columns_mobile'] ) : 0;
		if ( $grid_columns_mobile < 1 ) $grid_columns_mobile = 1;

		// Configuration du slider (seulement si mode slider)
		$space_between = isset( $settings['space_between']['size'] ) ? (int) $settings['space_between']['size'] : 20;

		// Helper : lire un switcher Elementor ('yes' / '' / null) → bool
		$sw = function( $key, $default = false ) use ( $settings ) {
			if ( ! isset( $settings[ $key ] ) ) return $default;
			return $settings[ $key ] === 'yes';
		};

		// Helper : lire un slider Elementor (['size'=>…]) → float|int
		$sl = function( $key, $default = 0 ) use ( $settings ) {
			return isset( $settings[ $key ]['size'] ) ? (float) $settings[ $key ]['size'] : $default;
		};

		// ============================================
		// CONFIGURATION DU SLIDER - Organisée par catégories
		// ============================================
		$slider_config = [
			// --- Configuration de base ---
			'slidesToShow' => isset( $settings['slides_to_show'] ) ? (int) $settings['slides_to_show'] : 3,
			'slidesToShowTablet' => isset( $settings['slides_to_show_tablet'] ) ? (int) $settings['slides_to_show_tablet'] : 2,
			'slidesToShowMobile' => isset( $settings['slides_to_show_mobile'] ) ? (int) $settings['slides_to_show_mobile'] : 1,
			'slidesToScroll' => isset( $settings['slides_to_scroll'] ) ? (int) $settings['slides_to_scroll'] : 1,
			'slidesToScrollTablet' => isset( $settings['slides_to_scroll_tablet'] ) ? (int) $settings['slides_to_scroll_tablet'] : 1,
			'slidesToScrollMobile' => isset( $settings['slides_to_scroll_mobile'] ) ? (int) $settings['slides_to_scroll_mobile'] : 1,
			'speed' => isset( $settings['slider_speed']['size'] ) ? (int) $settings['slider_speed']['size'] : 500,
			'spaceBetween' => $space_between,

			// --- Autoplay ---
			'autoplay' => ! empty( $settings['autoplay'] ) && $settings['autoplay'] === 'yes',
			'autoplaySpeed' => isset( $settings['autoplay_speed']['size'] ) ? (int) $settings['autoplay_speed']['size'] : 3000,
			'pauseOnHover' => ! empty( $settings['pause_on_hover'] ) && $settings['pause_on_hover'] === 'yes',

			// --- Loop ---
			'loop' => ! empty( $settings['infinite_loop'] ) && $settings['infinite_loop'] === 'yes',

			// --- Navigation ---
			'showArrows' => ! empty( $settings['show_arrows'] ) && $settings['show_arrows'] === 'yes',
			'showDots' => ! empty( $settings['show_dots'] ) && $settings['show_dots'] === 'yes',

			// --- Options Owl avancées ---
			'owlCenter'        => ! empty( $settings['owl_center'] ) && $settings['owl_center'] === 'yes',
			'owlStagePadding'  => isset( $settings['owl_stage_padding'] ) ? (int) $settings['owl_stage_padding'] : 0,
			'owlAutoWidth'     => ! empty( $settings['owl_auto_width'] ) && $settings['owl_auto_width'] === 'yes',
			'owlAutoHeight'    => ! empty( $settings['owl_auto_height'] ) && $settings['owl_auto_height'] === 'yes',
			'owlRtl'           => ! empty( $settings['owl_rtl'] ) && $settings['owl_rtl'] === 'yes',
			'owlMouseDrag'     => ! isset( $settings['owl_mouse_drag'] ) || $settings['owl_mouse_drag'] === 'yes',
			'owlTouchDrag'     => ! isset( $settings['owl_touch_drag'] ) || $settings['owl_touch_drag'] === 'yes',
			'owlPullDrag'      => ! isset( $settings['owl_pull_drag'] ) || $settings['owl_pull_drag'] === 'yes',
			'owlFreeDrag'      => ! empty( $settings['owl_free_drag'] ) && $settings['owl_free_drag'] === 'yes',
			'owlLazyLoad'      => ! empty( $settings['owl_lazy_load'] ) && $settings['owl_lazy_load'] === 'yes',
			'owlAnimateOut'    => ! empty( $settings['owl_animate_out'] ) ? sanitize_text_field( $settings['owl_animate_out'] ) : false,
			'owlAnimateIn'     => ! empty( $settings['owl_animate_in'] ) ? sanitize_text_field( $settings['owl_animate_in'] ) : false,
			'owlStartPosition' => isset( $settings['owl_start_position'] ) ? (int) $settings['owl_start_position'] : 0,
			'owlNavRewind'     => ! isset( $settings['owl_nav_rewind'] ) || $settings['owl_nav_rewind'] === 'yes',
			'owlNavSpeed'      => ! empty( $settings['owl_nav_speed'] ) ? (int) $settings['owl_nav_speed'] : false,
			'owlDotsSpeed'     => ! empty( $settings['owl_dots_speed'] ) ? (int) $settings['owl_dots_speed'] : false,
			'owlFluidSpeed'    => ! empty( $settings['owl_fluidspeed'] ) ? (int) $settings['owl_fluidspeed'] : false,

			// --- Effet hover (pour CSS) ---
			'hoverEffect' => isset( $settings['card_hover_effect'] ) ? $settings['card_hover_effect'] : 'lift',
			// --- Tooltip hover text ---
			'hoverTooltipOffsetY' => $sl( 'hover_tooltip_offset_y', -8 ),
			'hoverTooltipOffsetX' => $sl( 'hover_tooltip_offset_x', 16 ),

			// =========================================================
			// --- Options Swiper avancées (Carousel Swiper widget) ---
			// Ces clés sont lues par carousel-swiper.js depuis data-slider-config
			// =========================================================
			'swiperDirection'            => isset( $settings['swiper_direction'] ) ? $settings['swiper_direction'] : 'horizontal',
			'swiperEffect'               => isset( $settings['swiper_effect'] ) ? $settings['swiper_effect'] : 'slide',
			'swiperCenteredSlides'       => $sw( 'swiper_centered_slides' ),
			'swiperSlidesPerViewMode'    => isset( $settings['swiper_slides_per_view_mode'] ) ? $settings['swiper_slides_per_view_mode'] : 'fixed',
			'swiperGrabCursor'           => $sw( 'swiper_grab_cursor', true ),
			'swiperFreeMode'             => $sw( 'swiper_free_mode' ),
			'swiperFreeModeSticky'       => $sw( 'swiper_free_mode_sticky' ),
			'swiperFreeModeMomentum'     => $sw( 'swiper_free_mode_momentum', true ),
			'swiperRewind'               => $sw( 'swiper_rewind' ),
			'swiperSlideToClickedSlide'  => $sw( 'swiper_slide_to_clicked_slide' ),
			'swiperAllowTouchMove'       => $sw( 'swiper_allow_touch_move', true ),
			'swiperSimulateTouch'        => $sw( 'swiper_simulate_touch', true ),
			'swiperWatchOverflow'        => $sw( 'swiper_watch_overflow', true ),
			'swiperAutoHeight'           => $sw( 'swiper_auto_height' ),
			'swiperKeyboardEnabled'      => $sw( 'swiper_keyboard_enabled' ),
			'swiperKeyboardOnlyInViewport' => $sw( 'swiper_keyboard_only_in_viewport', true ),
			'swiperKeyboardPageUpDown'   => $sw( 'swiper_keyboard_page_up_down', true ),
			'swiperMousewheelEnabled'    => $sw( 'swiper_mousewheel_enabled' ),
			'swiperMousewheelInvert'     => $sw( 'swiper_mousewheel_invert' ),
			'swiperMousewheelForceToAxis' => $sw( 'swiper_mousewheel_force_to_axis' ),
			'swiperMousewheelSensitivity' => $sl( 'swiper_mousewheel_sensitivity', 1 ),
			'swiperPaginationType'       => isset( $settings['swiper_pagination_type'] ) ? $settings['swiper_pagination_type'] : 'bullets',
			'swiperPaginationDynamicBullets' => $sw( 'swiper_pagination_dynamic_bullets' ),
			'swiperPaginationClickable'  => $sw( 'swiper_pagination_clickable', true ),
			'swiperScrollbarEnabled'     => $sw( 'swiper_scrollbar_enabled' ),
			'swiperScrollbarDraggable'   => $sw( 'swiper_scrollbar_draggable', true ),
			'swiperScrollbarHide'        => $sw( 'swiper_scrollbar_hide', true ),
			'swiperLazyEnabled'          => $sw( 'swiper_lazy_enabled' ),
			'swiperLazyLoadPrevNext'     => $sw( 'swiper_lazy_load_prev_next', true ),
			'swiperParallaxEnabled'      => $sw( 'swiper_parallax_enabled' ),
			'swiperGridEnabled'          => $sw( 'swiper_grid_enabled' ),
			'swiperGridRows'             => isset( $settings['swiper_grid_rows'] ) ? (int) $settings['swiper_grid_rows'] : 2,
			'swiperGridFill'             => isset( $settings['swiper_grid_fill'] ) ? $settings['swiper_grid_fill'] : 'column',
			'swiperCoverflowRotate'      => $sl( 'swiper_coverflow_rotate', 50 ),
			'swiperCoverflowStretch'     => $sl( 'swiper_coverflow_stretch', 0 ),
			'swiperCoverflowDepth'       => $sl( 'swiper_coverflow_depth', 100 ),
			'swiperCoverflowScale'       => $sl( 'swiper_coverflow_scale', 1 ),
			'swiperCoverflowSlideShadows' => $sw( 'swiper_coverflow_slide_shadows', true ),
			'swiperCardsPerSlideRotate'  => $sl( 'swiper_cards_per_slide_rotate', 2 ),
			'swiperCardsPerSlideOffset'  => $sl( 'swiper_cards_per_slide_offset', 8 ),
			'swiperCardsRotate'          => $sw( 'swiper_cards_rotate', true ),
			'swiperCardsSlideShadows'    => $sw( 'swiper_cards_slide_shadows', true ),
			'swiperFadeCrossFade'        => $sw( 'swiper_fade_cross_fade', true ),
			// --- Mode Fan (rotation aléatoire) ---
			'swiperFanEnabled'           => $sw( 'swiper_fan_enabled' ),
			'swiperFanAngleMin'          => $sl( 'swiper_fan_angle_min', -5 ),
			'swiperFanAngleMax'          => $sl( 'swiper_fan_angle_max', 5 ),
			'swiperFanHoverScale'        => $sl( 'swiper_fan_hover_scale', 1.15 ),
			'swiperFanTransitionDuration' => $sl( 'swiper_fan_transition_duration', 400 ),
			'swiperFanOverlap'           => $sl( 'swiper_fan_overlap', -20 ),
			'swiperFanOverflowVisible'   => $sw( 'swiper_fan_overflow_visible', true ),
			// --- Mode Fan Deck (rotation alternée) ---
			'swiperFanDeckEnabled'           => $sw( 'swiper_fan_deck_enabled' ),
			'swiperFanDeckAngle'             => $sl( 'swiper_fan_deck_angle', 3 ),
			'swiperFanDeckAngleStep'         => $sl( 'swiper_fan_deck_angle_step', 1 ),
			'swiperFanDeckOverlap'           => $sl( 'swiper_fan_deck_overlap', -20 ),
			'swiperFanDeckHoverScale'        => $sl( 'swiper_fan_deck_hover_scale', 1.08 ),
			'swiperFanDeckHoverRotationRange' => $sl( 'swiper_fan_deck_hover_rotation_range', 2 ),
			'swiperFanDeckHoverShadow'       => $sw( 'swiper_fan_deck_hover_shadow', true ),
			'swiperFanDeckTransitionDuration' => $sl( 'swiper_fan_deck_transition_duration', 400 ),
			'swiperFanDeckCenterUpright'     => $sw( 'swiper_fan_deck_center_upright', true ),
			'swiperFanDeckOverflowVisible'   => $sw( 'swiper_fan_deck_overflow_visible', true ),
		];

		// Préparer la configuration de largeur créative pour JavaScript
		$creative_width_config = [];
		if ( $creative_background_enable && ! empty( $settings['card_creative_width'] ) ) {
			$width_setting = $settings['card_creative_width'];
			$creative_width_config = [
				'desktop' => isset( $width_setting['sizes']['desktop'] ) ? $width_setting['sizes']['desktop'] : ( isset( $width_setting['size'] ) ? [ 'size' => $width_setting['size'], 'unit' => isset( $width_setting['unit'] ) ? $width_setting['unit'] : 'px' ] : null ),
				'tablet' => isset( $width_setting['sizes']['tablet'] ) ? $width_setting['sizes']['tablet'] : null,
				'mobile' => isset( $width_setting['sizes']['mobile'] ) ? $width_setting['sizes']['mobile'] : null,
			];
		}
		$wrapper_classes = 'nova-carousel-widget';
		$wrapper_classes .= $image_as_background ? ' image-as-background' : '';
		$wrapper_classes .= $creative_background_enable ? ' creative-background-enabled' : '';
		if ( $is_grid_mode ) {
			$wrapper_classes .= ' grid-mode';
		} else {
			$wrapper_classes .= ' slider-mode';
		}

		if ( $is_swiper_widget && $is_mixed_display_mode ) {
			$wrapper_classes .= ' responsive-display-mode';
			$wrapper_classes .= ' display-desktop-' . sanitize_html_class( $display_mode_desktop );
			$wrapper_classes .= ' display-tablet-' . sanitize_html_class( $display_mode_tablet );
			$wrapper_classes .= ' display-mobile-' . sanitize_html_class( $display_mode_mobile );
		}

		$grid_vars_style = '--grid-cols:' . esc_attr( $grid_columns_initial ) . ';'
			. '--grid-cols-tablet:' . esc_attr( $grid_columns_tablet ) . ';'
			. '--grid-cols-mobile:' . esc_attr( $grid_columns_mobile ) . ';';
		?>
		<div class="<?php echo esc_attr( $wrapper_classes ); ?>"
			<?php if ( ! $is_grid_mode && $has_any_slider_mode ) : ?>
				data-slider-config="<?php echo esc_attr( wp_json_encode( $slider_config ) ); ?>"
				data-hover-effect="<?php echo esc_attr( $slider_config['hoverEffect'] ); ?>"
				data-widget-id="<?php echo esc_attr( $this->get_id() ); ?>"
			<?php endif; ?>
			data-display-mode-config="<?php echo esc_attr( wp_json_encode( $display_mode_config ) ); ?>"
			data-grid-columns="<?php echo esc_attr( $grid_columns_initial ); ?>"
			data-grid-columns-tablet="<?php echo esc_attr( $grid_columns_tablet ); ?>"
			data-grid-columns-mobile="<?php echo esc_attr( $grid_columns_mobile ); ?>"
			style="<?php echo esc_attr( $grid_vars_style ); ?>"
			data-creative-width-config="<?php echo esc_attr( wp_json_encode( $creative_width_config ) ); ?>"
		>
			<div class="nova-carousel-container">
				<?php
				// Check if content should be displayed
				$has_title = ! empty( $settings['title_text'] );
				$has_description = ! empty( $settings['description_text'] );
				$has_button = ! empty( $settings['button_text'] );
				$show_arrows = ! empty( $settings['show_arrows'] ) && $settings['show_arrows'] === 'yes';
				$navigation_position = isset( $settings['navigation_position'] ) ? $settings['navigation_position'] : 'content-2';
				$has_content_1 = $has_title || $has_description;
				// content-2 ne contient les flèches que si position = 'content-2'
				$has_content_2 = $has_button || ( $show_arrows && $navigation_position === 'content-2' );
				$has_content = $has_content_1 || $has_content_2;
				
				// Générer les boutons de navigation si nécessaire
				$navigation_html = '';
				$prev_button_html = '';
				$next_button_html = '';
				if ( $show_arrows && ! $is_grid_mode ) {
					if ( $navigation_position === 'outside' ) {
						// Générer les boutons séparément pour position "outside"
						$prev_button_html = $this->render_single_navigation_button( $settings, 'prev' );
						$next_button_html = $this->render_single_navigation_button( $settings, 'next' );
					} else {
						// Générer les boutons ensemble pour les autres positions
						$navigation_html = $this->render_navigation_buttons( $settings );
					}
				}
				?>
				<?php if ( $show_arrows && $navigation_position === 'top' ) : ?>
					<?php echo $navigation_html; ?>
				<?php endif; ?>
				<?php if ( $has_content ) : ?>
				<div class="nova-carousel-content">
					<?php if ( $has_content_1 ) : ?>
					<div class="nova-carousel-content-1">
						<?php if ( $has_title ) : ?>
							<div class="nova-carousel-title"><?php echo wp_kses_post( $settings['title_text'] ); ?></div>
						<?php endif; ?>

						<?php if ( $has_description ) : ?>
							<div class="nova-carousel-description"><?php echo wp_kses_post( $settings['description_text'] ); ?></div>
						<?php endif; ?>
					</div>
					<?php endif; ?>

					<?php if ( $has_content_2 ) : ?>
					<div class="nova-carousel-content-2">
						<?php if ( $has_button ) : ?>
							<a class="nova-carousel-button" <?php $this->print_render_attribute_string( 'button_link' ); ?>>
								<?php echo esc_html( $settings['button_text'] ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $show_arrows && $navigation_position === 'content-2' ) : ?>
							<?php echo $navigation_html; ?>
						<?php endif; ?>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<?php if ( $show_arrows && $navigation_position === 'outside' ) : ?>
					<div class="nova-carousel-slider-wrapper nova-carousel-nav-position-outside">
						<?php echo $prev_button_html; ?>
				<?php elseif ( $show_arrows && in_array( $navigation_position, [ 'left', 'right' ] ) ) : ?>
					<div class="nova-carousel-slider-wrapper nova-carousel-nav-position-<?php echo esc_attr( $navigation_position ); ?>">
						<?php if ( $navigation_position === 'left' ) : ?>
							<?php echo $navigation_html; ?>
						<?php endif; ?>
				<?php endif; ?>
				<?php if ( $is_grid_mode ) : ?>
					<div class="nova-carousel-grid<?php echo ! $has_content ? ' full-width' : ''; ?>"
						data-grid-columns="<?php echo esc_attr( $grid_columns_initial ); ?>"
						data-grid-columns-tablet="<?php echo esc_attr( $grid_columns_tablet ); ?>"
						data-grid-columns-mobile="<?php echo esc_attr( $grid_columns_mobile ); ?>"
						style="--grid-cols:<?php echo esc_attr( $grid_columns_initial ); ?>;--grid-cols-tablet:<?php echo esc_attr( $grid_columns_tablet ); ?>;--grid-cols-mobile:<?php echo esc_attr( $grid_columns_mobile ); ?>;"
					>
				<?php else : ?>
					<?php
					// IMPORTANT :
					// - Pour le widget original "NOVA Carousel" (Owl), on garde la classe "owl-carousel"
					// - Pour le widget "NOVA Carousel (Swiper)", on la retire pour éviter toute initialisation Owl.
					$is_swiper_widget = $this instanceof Carousel_Swiper_Widget;
					?>
					<div class="nova-carousel-slider<?php echo $is_swiper_widget ? '' : ' owl-carousel'; ?><?php echo ! $has_content ? ' full-width' : ''; ?>">
				<?php endif; ?>
						<?php foreach ( $items as $index => $item ) : ?>
							<?php
							$item_link = isset( $item['item_link'] ) ? $item['item_link'] : [];
							$item_image = isset( $item['item_image'] ) ? $item['item_image'] : [];
							$item_text = isset( $item['item_text'] ) ? $item['item_text'] : '';
							$item_date = isset( $item['item_date'] ) ? $item['item_date'] : '';
							$item_icon = isset( $item['item_icon'] ) ? $item['item_icon'] : [];
							$item_button_text = isset( $item['item_button_text'] ) ? $item['item_button_text'] : '';
							$item_button_link = isset( $item['item_button_link'] ) ? $item['item_button_link'] : [];
							$item_button_icon = isset( $item['item_button_icon'] ) ? $item['item_button_icon'] : [];
							$item_button_icon_position = isset( $item['item_button_icon_position'] ) ? $item['item_button_icon_position'] : 'after';
							$item_background_color = isset( $item['item_background_color'] ) ? $item['item_background_color'] : '';
							
							// Résoudre la couleur globale si elle est définie
							// Vérifier d'abord dans les settings globaux du widget
							$global_color_resolved = false;
							
							// Vérifier si c'est une référence globale dans l'item
							if ( isset( $item['__globals__'] ) && ! empty( $item['__globals__']['item_background_color'] ) ) {
								$global_ref = $item['__globals__']['item_background_color'];
								if ( preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
									$global_id = $matches[1];
									// Utiliser la variable CSS globale d'Elementor
									$item_background_color = 'var(--e-global-color-' . esc_attr( $global_id ) . ')';
									$global_color_resolved = true;
								}
							}
							
							// Si la couleur est vide mais qu'il y a une référence globale dans les settings du repeater
							if ( empty( $item_background_color ) && ! $global_color_resolved ) {
								// Essayer de trouver dans les settings du repeater
								if ( isset( $settings['items_list'] ) && is_array( $settings['items_list'] ) ) {
									foreach ( $settings['items_list'] as $repeater_item ) {
										if ( isset( $repeater_item['_id'] ) && isset( $item['_id'] ) && $repeater_item['_id'] === $item['_id'] ) {
											if ( isset( $repeater_item['__globals__'] ) && ! empty( $repeater_item['__globals__']['item_background_color'] ) ) {
												$global_ref = $repeater_item['__globals__']['item_background_color'];
												if ( preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
													$global_id = $matches[1];
													$item_background_color = 'var(--e-global-color-' . esc_attr( $global_id ) . ')';
													$global_color_resolved = true;
												}
											}
											break;
										}
									}
								}
							}
							
							// Si toujours pas résolu, vérifier si la valeur elle-même est une variable CSS
							if ( ! empty( $item_background_color ) && ! $global_color_resolved ) {
								if ( strpos( $item_background_color, 'var(--e-global-color-' ) === 0 ) {
									$global_color_resolved = true;
								}
							}
							
							$image_url = is_array( $item_image ) && isset( $item_image['url'] ) ? $item_image['url'] : '';
							$link_url = is_array( $item_link ) && isset( $item_link['url'] ) ? $item_link['url'] : '';
							$button_url = is_array( $item_button_link ) && isset( $item_button_link['url'] ) ? $item_button_link['url'] : '';
							$button_target = ( is_array( $item_button_link ) && ! empty( $item_button_link['is_external'] ) ) ? '_blank' : '';
							$button_nofollow = ( is_array( $item_button_link ) && ! empty( $item_button_link['nofollow'] ) ) ? 'nofollow' : '';
							if ( empty( $button_url ) ) {
								$button_url = $link_url;
							}
							$has_item_button = ! empty( $button_url ) && ( ! empty( $item_button_text ) || ( ! empty( $item_button_icon ) && ! empty( $item_button_icon['value'] ) ) );
							
							// Sélectionner un SVG aléatoire pour le background créatif
							$selected_svg = '';
							$svg_transforms = [];
							if ( $creative_background_enable && ! empty( $svg_backgrounds ) ) {
								$selected_svg = $svg_backgrounds[ array_rand( $svg_backgrounds ) ];
								
								// Appliquer des transformations aléatoires si activées
								$has_rotation = ! empty( $settings['creative_background_random_rotation'] ) && $settings['creative_background_random_rotation'] === 'yes';
								$has_flip = ! empty( $settings['creative_background_random_flip'] ) && $settings['creative_background_random_flip'] === 'yes';
								
								if ( $has_rotation || $has_flip ) {
									// Utiliser des valeurs vraiment aléatoires pour chaque item
									$random_rotation = $has_rotation ? ( mt_rand( 0, 1 ) === 1 ) : false;
									$random_flip = $has_flip ? ( mt_rand( 0, 1 ) === 1 ) : false;
									
									if ( $has_rotation && $has_flip ) {
										// Les deux options activées : combinaison aléatoire
										if ( $random_flip ) {
											$svg_transforms[] = 'scaleX(-1)';
										}
										if ( $random_rotation ) {
											$svg_transforms[] = 'rotate(180deg)';
										}
									} elseif ( $has_rotation ) {
										// Seulement la rotation activée
										if ( $random_rotation ) {
											$svg_transforms[] = 'rotate(180deg)';
										}
									} elseif ( $has_flip ) {
										// Seulement le flip activé
										if ( $random_flip ) {
											$svg_transforms[] = 'scaleX(-1)';
										}
									}
								}
							}
							
							// Si le masque créatif est activé, récupérer les dimensions du SVG
							$svg_width = '';
							$svg_height = '';
							if ( $creative_background_enable && ! empty( $selected_svg ) ) {
								// Extraire le nom du fichier SVG
								$svg_filename = basename( parse_url( $selected_svg, PHP_URL_PATH ) );
								// Chemin du fichier SVG
								if ( defined( 'NOVA_ADDONS_PLUGIN_DIR' ) ) {
									$svg_path = NOVA_ADDONS_PLUGIN_DIR . 'assets/svg/' . $svg_filename;
								} else {
									$plugin_path = plugin_dir_path( __FILE__ );
									$plugin_dir = dirname( dirname( $plugin_path ) );
									$svg_path = $plugin_dir . '/assets/svg/' . $svg_filename;
								}
								
								// Lire le SVG pour obtenir ses dimensions
								if ( file_exists( $svg_path ) ) {
									$svg_content = file_get_contents( $svg_path );
									if ( $svg_content ) {
										// Extraire width et height du SVG
										if ( preg_match( '/width=["\']([^"\']+)["\']/', $svg_content, $width_matches ) ) {
											$svg_width = $width_matches[1];
										} elseif ( preg_match( '/viewBox=["\']([^"\']+)["\']/', $svg_content, $viewbox_matches ) ) {
											$viewbox = explode( ' ', trim( $viewbox_matches[1] ) );
											if ( count( $viewbox ) >= 4 ) {
												$svg_width = $viewbox[2];
												$svg_height = $viewbox[3];
											}
										}
										if ( preg_match( '/height=["\']([^"\']+)["\']/', $svg_content, $height_matches ) ) {
											$svg_height = $height_matches[1];
										}
									}
								}
							}
							
							$item_style = '';
							
							// Si le masque créatif est activé, l'appliquer en premier
							if ( $creative_background_enable && ! empty( $selected_svg ) ) {
								// Appliquer le masque SVG directement sur l'élément pour découper sa forme
								$item_style .= '-webkit-mask-image: url(' . esc_url( $selected_svg ) . '); ';
								$item_style .= 'mask-image: url(' . esc_url( $selected_svg ) . '); ';
								$item_style .= '-webkit-mask-size: contain; mask-size: contain; ';
								$item_style .= '-webkit-mask-position: center; mask-position: center; ';
								$item_style .= '-webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; ';
								
								// Appliquer les transformations aléatoires si définies
								if ( ! empty( $svg_transforms ) ) {
									$item_style .= 'transform: ' . implode( ' ', $svg_transforms ) . '; ';
								}
								
								// Vérifier si une largeur personnalisée est configurée
								$custom_width = '';
								if ( ! empty( $settings['card_creative_width'] ) ) {
									$width_setting = $settings['card_creative_width'];
									// Récupérer la valeur responsive appropriée
									if ( isset( $width_setting['sizes']['desktop']['size'] ) && $width_setting['sizes']['desktop']['size'] !== '' ) {
										$custom_width = $width_setting['sizes']['desktop']['size'] . ( isset( $width_setting['sizes']['desktop']['unit'] ) ? $width_setting['sizes']['desktop']['unit'] : 'px' );
									} elseif ( isset( $width_setting['size'] ) && $width_setting['size'] !== '' ) {
										$custom_width = $width_setting['size'] . ( isset( $width_setting['unit'] ) ? $width_setting['unit'] : 'px' );
									}
								}
								
								// Si une largeur personnalisée est définie, calculer la hauteur selon l'aspect-ratio
								if ( ! empty( $custom_width ) && ! empty( $svg_width ) && ! empty( $svg_height ) ) {
									// Nettoyer les valeurs SVG
									$svg_width_clean = preg_replace( '/[^0-9.]/', '', $svg_width );
									$svg_height_clean = preg_replace( '/[^0-9.]/', '', $svg_height );
									
									if ( ! empty( $svg_width_clean ) && ! empty( $svg_height_clean ) && is_numeric( $svg_width_clean ) && is_numeric( $svg_height_clean ) && $svg_width_clean > 0 ) {
										// Calculer l'aspect-ratio
										$aspect_ratio = floatval( $svg_height_clean ) / floatval( $svg_width_clean );
										
										// Extraire la valeur numérique de la largeur personnalisée
										$custom_width_value = preg_replace( '/[^0-9.]/', '', $custom_width );
										$custom_width_unit = preg_replace( '/[0-9.]/', '', $custom_width );
										
										if ( ! empty( $custom_width_value ) && is_numeric( $custom_width_value ) && $custom_width_value > 0 ) {
											// Calculer la hauteur proportionnelle
											$calculated_height = floatval( $custom_width_value ) * $aspect_ratio;
											
											// Appliquer la largeur et la hauteur calculée
											$item_style .= 'width: ' . esc_attr( $custom_width ) . ' !important; ';
											$item_style .= 'min-width: ' . esc_attr( $custom_width ) . ' !important; ';
											$item_style .= 'max-width: ' . esc_attr( $custom_width ) . ' !important; ';
											$item_style .= 'flex-basis: ' . esc_attr( $custom_width ) . ' !important; ';
											$item_style .= 'flex-grow: 0 !important; ';
											$item_style .= 'flex-shrink: 0 !important; ';
											$item_style .= 'height: ' . esc_attr( $calculated_height . $custom_width_unit ) . ' !important; ';
											$item_style .= 'min-height: ' . esc_attr( $calculated_height . $custom_width_unit ) . ' !important; ';
										}
									}
								} else {
									// Sinon, utiliser les dimensions originales du SVG
									if ( ! empty( $svg_width ) ) {
										// Nettoyer la valeur (enlever 'px' si présent)
										$svg_width_clean = preg_replace( '/[^0-9.]/', '', $svg_width );
										if ( ! empty( $svg_width_clean ) && is_numeric( $svg_width_clean ) ) {
											$item_style .= 'width: ' . esc_attr( $svg_width_clean ) . 'px !important; ';
											$item_style .= 'min-width: ' . esc_attr( $svg_width_clean ) . 'px !important; ';
											$item_style .= 'max-width: ' . esc_attr( $svg_width_clean ) . 'px !important; ';
											$item_style .= 'flex-basis: ' . esc_attr( $svg_width_clean ) . 'px !important; ';
											$item_style .= 'flex-grow: 0 !important; ';
											$item_style .= 'flex-shrink: 0 !important; ';
										}
									}
									// Appliquer aussi la hauteur du SVG si disponible
									if ( ! empty( $svg_height ) ) {
										// Nettoyer la valeur (enlever 'px' si présent)
										$svg_height_clean = preg_replace( '/[^0-9.]/', '', $svg_height );
										if ( ! empty( $svg_height_clean ) && is_numeric( $svg_height_clean ) ) {
											$item_style .= 'height: ' . esc_attr( $svg_height_clean ) . 'px !important; ';
											$item_style .= 'min-height: ' . esc_attr( $svg_height_clean ) . 'px !important; ';
										}
									}
								}
							}
							
							// Ajouter la couleur de fond personnalisée si définie (priorité sur les autres backgrounds)
							if ( ! empty( $item_background_color ) ) {
								$item_style .= 'background-color: ' . esc_attr( $item_background_color ) . ' !important; ';
							}
							
							// Ajouter l'image en background si activée
							if ( $image_as_background && ! empty( $image_url ) ) {
								$item_style .= 'background-image: url(' . esc_url( $image_url ) . '); ';
								$item_style .= 'background-size: cover; ';
								$item_style .= 'background-position: center; ';
								$item_style .= 'background-repeat: no-repeat; ';
							} elseif ( $creative_background_enable && ! empty( $selected_svg ) && empty( $item_background_color ) ) {
								// Si pas d'image mais masque activé, utiliser la couleur du masque (seulement si pas de couleur personnalisée)
								$item_style .= 'background-color: ' . esc_attr( $creative_background_color ) . '; ';
							}
							?>
							<?php
							// Ajouter les dimensions SVG aux attributs data si disponibles
							$item_data_attrs = '';
							if ( ! empty( $svg_width ) ) {
								$svg_width_clean = preg_replace( '/[^0-9.]/', '', $svg_width );
								if ( ! empty( $svg_width_clean ) && is_numeric( $svg_width_clean ) ) {
									$item_data_attrs .= ' data-svg-width="' . esc_attr( $svg_width_clean ) . '"';
								}
							}
							if ( ! empty( $svg_height ) ) {
								$svg_height_clean = preg_replace( '/[^0-9.]/', '', $svg_height );
								if ( ! empty( $svg_height_clean ) && is_numeric( $svg_height_clean ) ) {
									$item_data_attrs .= ' data-svg-height="' . esc_attr( $svg_height_clean ) . '"';
								}
							}
							// Ajouter data-popup-index si le popup global est activé ET que l'item a son popup activé
							if ( $popup_enable_global && ! empty( $item['item_popup_enable'] ) && $item['item_popup_enable'] === 'yes' ) {
								$item_data_attrs .= ' data-popup-index="' . esc_attr( $index ) . '"';
							}
							?>
							<div class="nova-carousel-item<?php echo $image_as_background && ! empty( $image_url ) ? ' has-background-image' : ''; ?><?php echo $creative_background_enable ? ' has-creative-background' : ''; ?><?php echo $image_overlay_enable && ! empty( $image_url ) ? ' has-image-overlay' : ''; ?>" data-index="<?php echo esc_attr( $index ); ?>" data-hover-effect="<?php echo esc_attr( $slider_config['hoverEffect'] ); ?>" data-svg-url="<?php echo $creative_background_enable && ! empty( $selected_svg ) ? esc_url( $selected_svg ) : ''; ?>"<?php echo $item_data_attrs; ?><?php echo ! empty( $item_style ) ? ' style="' . $item_style . '"' : ''; ?>>
								<?php
								// ── Médias initial + hover ────────────────────────────────
								$item_media_type       = isset( $item['item_media_type'] ) ? $item['item_media_type'] : 'image';
								$item_video_url        = isset( $item['item_video_url']['url'] ) ? esc_url( $item['item_video_url']['url'] ) : '';
								$item_video_loop       = ! isset( $item['item_video_loop'] ) || $item['item_video_loop'] === 'yes';
								$item_video_muted      = ! isset( $item['item_video_muted'] ) || $item['item_video_muted'] === 'yes';
								$hover_media_type      = isset( $item['item_hover_media_type'] ) ? $item['item_hover_media_type'] : 'none';
								$hover_image_url       = isset( $item['item_hover_image']['url'] ) ? esc_url( $item['item_hover_image']['url'] ) : '';
								$hover_video_url       = isset( $item['item_hover_video_url']['url'] ) ? esc_url( $item['item_hover_video_url']['url'] ) : '';
								$hover_video_loop      = ! isset( $item['item_hover_video_loop'] ) || $item['item_hover_video_loop'] === 'yes';
								$hover_video_muted     = ! isset( $item['item_hover_video_muted'] ) || $item['item_hover_video_muted'] === 'yes';
								$has_hover_media       = $hover_media_type !== 'none' && ( ! empty( $hover_image_url ) || ! empty( $hover_video_url ) );
								// ── Texte hover ───────────────────────────────────────────
								$hover_text_enable        = ! empty( $item['item_hover_text_enable'] ) && $item['item_hover_text_enable'] === 'yes';
								$hover_text               = $hover_text_enable && ! empty( $item['item_hover_text'] ) ? $item['item_hover_text'] : '';
								$hover_text_position      = isset( $item['item_hover_text_position'] ) ? $item['item_hover_text_position'] : 'center';
								$hover_text_overlay_color = isset( $item['item_hover_text_overlay_color'] ) ? $item['item_hover_text_overlay_color'] : 'rgba(0,0,0,0.45)';
								?>
								<?php if ( ! $image_as_background ) : ?>
									<div class="nova-carousel-item-image<?php echo $has_hover_media ? ' has-hover-media' : ''; ?><?php echo $hover_text_enable ? ' has-hover-text' : ''; ?>">

										<?php // ── Média initial ──────────────────────────────────── ?>
										<?php if ( $item_media_type === 'video' && ! empty( $item_video_url ) ) : ?>
											<video class="nova-carousel-media nova-carousel-media--initial"
												src="<?php echo $item_video_url; ?>"
												<?php echo $item_video_loop ? 'loop' : ''; ?>
												<?php echo $item_video_muted ? 'muted' : ''; ?>
												autoplay
												playsinline
												preload="auto">
											</video>
										<?php elseif ( ! empty( $image_url ) ) : ?>
											<img class="nova-carousel-media nova-carousel-media--initial"
												src="<?php echo esc_url( $image_url ); ?>"
												alt="<?php echo esc_attr( $item_text ); ?>">
										<?php endif; ?>

										<?php // ── Média hover (caché par défaut) ─────────────────── ?>
										<?php if ( $has_hover_media ) : ?>
											<?php if ( $hover_media_type === 'video' && ! empty( $hover_video_url ) ) : ?>
												<video class="nova-carousel-media nova-carousel-media--hover"
													src="<?php echo $hover_video_url; ?>"
													<?php echo $hover_video_loop ? 'loop' : ''; ?>
													<?php echo $hover_video_muted ? 'muted' : ''; ?>
													playsinline
													preload="none">
												</video>
											<?php elseif ( $hover_media_type === 'image' && ! empty( $hover_image_url ) ) : ?>
												<img class="nova-carousel-media nova-carousel-media--hover"
													src="<?php echo $hover_image_url; ?>"
													alt="<?php echo esc_attr( $item_text ); ?>">
											<?php endif; ?>
										<?php endif; ?>

										<?php // ── Texte hover ────────────────────────────────────── ?>
										<?php if ( $hover_text_enable && ! empty( $hover_text ) ) : ?>
											<div class="nova-carousel-hover-text nova-carousel-hover-text--<?php echo esc_attr( $hover_text_position ); ?>"
												<?php if ( ! empty( $hover_text_overlay_color ) ) : ?>
													style="--nova-hover-text-overlay: <?php echo esc_attr( $hover_text_overlay_color ); ?>;"
												<?php endif; ?>>
												<div class="nova-carousel-hover-text__inner">
													<?php echo wp_kses_post( $hover_text ); ?>
												</div>
											</div>
										<?php endif; ?>

										<?php if ( $image_overlay_enable ) : ?>
											<div class="nova-carousel-item-overlay" style="background-color: <?php echo esc_attr( $image_overlay_color ); ?>;"></div>
										<?php endif; ?>
									</div>
								<?php endif; ?>
								<?php if ( $image_overlay_enable && ! empty( $image_url ) && ( $image_as_background || ( $creative_background_enable && ! empty( $selected_svg ) ) ) ) : ?>
									<div class="nova-carousel-item-overlay" style="background-color: <?php echo esc_attr( $image_overlay_color ); ?>;"></div>
								<?php endif; ?>
								
								<?php
								// Si le masque créatif est activé, appliquer aussi la largeur et hauteur au contenu
								$content_style = '';
								if ( $creative_background_enable && ! empty( $selected_svg ) ) {
									// Vérifier si une largeur personnalisée est configurée
									$custom_width = '';
									if ( ! empty( $settings['card_creative_width'] ) ) {
										$width_setting = $settings['card_creative_width'];
										// Récupérer la valeur responsive appropriée
										if ( isset( $width_setting['sizes']['desktop']['size'] ) && $width_setting['sizes']['desktop']['size'] !== '' ) {
											$custom_width = $width_setting['sizes']['desktop']['size'] . ( isset( $width_setting['sizes']['desktop']['unit'] ) ? $width_setting['sizes']['desktop']['unit'] : 'px' );
										} elseif ( isset( $width_setting['size'] ) && $width_setting['size'] !== '' ) {
											$custom_width = $width_setting['size'] . ( isset( $width_setting['unit'] ) ? $width_setting['unit'] : 'px' );
										}
									}
									
									// Si une largeur personnalisée est définie, calculer la hauteur selon l'aspect-ratio
									if ( ! empty( $custom_width ) && ! empty( $svg_width ) && ! empty( $svg_height ) ) {
										// Nettoyer les valeurs SVG
										$svg_width_clean = preg_replace( '/[^0-9.]/', '', $svg_width );
										$svg_height_clean = preg_replace( '/[^0-9.]/', '', $svg_height );
										
										if ( ! empty( $svg_width_clean ) && ! empty( $svg_height_clean ) && is_numeric( $svg_width_clean ) && is_numeric( $svg_height_clean ) && $svg_width_clean > 0 ) {
											// Calculer l'aspect-ratio
											$aspect_ratio = floatval( $svg_height_clean ) / floatval( $svg_width_clean );
											
											// Extraire la valeur numérique de la largeur personnalisée
											$custom_width_value = preg_replace( '/[^0-9.]/', '', $custom_width );
											$custom_width_unit = preg_replace( '/[0-9.]/', '', $custom_width );
											
											if ( ! empty( $custom_width_value ) && is_numeric( $custom_width_value ) && $custom_width_value > 0 ) {
												// Calculer la hauteur proportionnelle
												$calculated_height = floatval( $custom_width_value ) * $aspect_ratio;
												
												// Appliquer la largeur et la hauteur calculée
												$content_style = ' style="width: ' . esc_attr( $custom_width ) . ' !important; min-width: ' . esc_attr( $custom_width ) . ' !important; max-width: ' . esc_attr( $custom_width ) . ' !important; flex-basis: ' . esc_attr( $custom_width ) . ' !important; flex-grow: 0 !important; flex-shrink: 0 !important; height: ' . esc_attr( $calculated_height . $custom_width_unit ) . ' !important; min-height: ' . esc_attr( $calculated_height . $custom_width_unit ) . ' !important;"';
											}
										}
									} else {
										// Sinon, utiliser les dimensions originales du SVG
										if ( ! empty( $svg_width ) ) {
											$svg_width_clean = preg_replace( '/[^0-9.]/', '', $svg_width );
											if ( ! empty( $svg_width_clean ) && is_numeric( $svg_width_clean ) ) {
												$content_style = ' style="width: ' . esc_attr( $svg_width_clean ) . 'px !important; min-width: ' . esc_attr( $svg_width_clean ) . 'px !important; max-width: ' . esc_attr( $svg_width_clean ) . 'px !important; flex-basis: ' . esc_attr( $svg_width_clean ) . 'px !important; flex-grow: 0 !important; flex-shrink: 0 !important;';
												// Ajouter la hauteur si disponible
												if ( ! empty( $svg_height ) ) {
													$svg_height_clean = preg_replace( '/[^0-9.]/', '', $svg_height );
													if ( ! empty( $svg_height_clean ) && is_numeric( $svg_height_clean ) ) {
														$content_style .= ' height: ' . esc_attr( $svg_height_clean ) . 'px !important; min-height: ' . esc_attr( $svg_height_clean ) . 'px !important;';
													}
												}
												$content_style .= '"';
											}
										}
									}
								}
								?>
								<div class="nova-carousel-item-content"<?php echo $content_style; ?>>
									<?php
									// Icône / image de la carte (supporte soit une icône Elementor, soit une image PNG/SVG)
									$icon_type       = isset( $item['item_icon_type'] ) ? $item['item_icon_type'] : 'icon';
									$item_icon_image = isset( $item['item_icon_image'] ) ? $item['item_icon_image'] : [];
									if ( 'icon' === $icon_type && ! empty( $item_icon ) && ! empty( $item_icon['value'] ) ) :
										?>
										<div class="nova-carousel-item-icon">
											<?php Icons_Manager::render_icon( $item_icon, [ 'aria-hidden' => 'true' ] ); ?>
										</div>
										<?php
									elseif ( 'image' === $icon_type && ! empty( $item_icon_image ) && ! empty( $item_icon_image['url'] ) ) :
										$image_url = esc_url( $item_icon_image['url'] );
										$alt_text  = ! empty( $item_text ) ? wp_strip_all_tags( $item_text ) : '';
										?>
										<div class="nova-carousel-item-icon nova-carousel-item-icon--image">
											<img src="<?php echo $image_url; ?>" alt="<?php echo esc_attr( $alt_text ); ?>" loading="lazy" />
										</div>
										<?php
									endif;
									?>
									<?php if ( ! empty( $item_text ) ) : ?>
										<?php
										// Déterminer la balise HTML pour le titre
										$title_tag = 'div';
										if ( $settings['data_source'] === 'post_type' && ! empty( $settings['show_post_title'] ) && $settings['show_post_title'] === 'yes' ) {
											$title_tag = ! empty( $settings['post_title_tag'] ) ? $settings['post_title_tag'] : 'h3';
										}
										?>
										<div class="nova-carousel-item-text"><?php echo wp_kses_post( $item_text ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $item_date ) ) : ?>
										<div class="nova-carousel-item-date"><?php echo wp_kses_post( $item_date ); ?></div>
									<?php endif; ?>

									<?php if ( $has_item_button ) : ?>
										<a
											class="nova-carousel-item-button icon-<?php echo esc_attr( $item_button_icon_position ); ?>"
											href="<?php echo esc_url( $button_url ); ?>"
											<?php echo $button_target ? ' target="' . esc_attr( $button_target ) . '"' : ''; ?>
											<?php echo $button_nofollow ? ' rel="' . esc_attr( $button_nofollow ) . '"' : ''; ?>
										>
											<?php if ( $item_button_icon_position === 'before' && ! empty( $item_button_icon ) && ! empty( $item_button_icon['value'] ) ) : ?>
												<span class="nova-carousel-item-button-icon">
													<?php Icons_Manager::render_icon( $item_button_icon, [ 'aria-hidden' => 'true' ] ); ?>
												</span>
											<?php endif; ?>
											<?php if ( ! empty( $item_button_text ) ) : ?>
												<span class="nova-carousel-item-button-text"><?php echo esc_html( $item_button_text ); ?></span>
											<?php endif; ?>
											<?php if ( $item_button_icon_position !== 'before' && ! empty( $item_button_icon ) && ! empty( $item_button_icon['value'] ) ) : ?>
												<span class="nova-carousel-item-button-icon">
													<?php Icons_Manager::render_icon( $item_button_icon, [ 'aria-hidden' => 'true' ] ); ?>
												</span>
											<?php endif; ?>
										</a>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $link_url ) && ! $has_item_button ) : ?>
									<a href="<?php echo esc_url( $link_url ); ?>" class="nova-carousel-item-link" aria-label="<?php echo esc_attr( $item_text ); ?>"></a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php if ( ! $is_grid_mode && $slider_config['showDots'] ) : ?>
						<div class="owl-dots"></div>
					<?php endif; ?>
				</div>
				<?php if ( $show_arrows && $navigation_position === 'outside' ) : ?>
						<?php echo $next_button_html; ?>
					</div>
				<?php elseif ( $show_arrows && in_array( $navigation_position, [ 'left', 'right' ] ) ) : ?>
						<?php if ( $navigation_position === 'right' ) : ?>
							<?php echo $navigation_html; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $show_arrows && $navigation_position === 'bottom' ) : ?>
					<?php echo $navigation_html; ?>
				<?php endif; ?>
			</div>
		</div>

		<?php
		// ── Popup HTML — rendu une fois par widget, hors du carousel ──────
		$widget_id_str = $this->get_id();
		$popup_animation = isset( $settings['popup_animation'] ) ? $settings['popup_animation'] : 'fade-scale';
		$has_any_popup = false;
		$popup_items_data = [];

		// ── Popup — configuration globale ────────────────────────────────
		$popup_layout_global = isset( $settings['popup_layout'] ) ? $settings['popup_layout'] : 'flat';
		$popup_image_col     = isset( $settings['popup_image_col'] ) ? $settings['popup_image_col'] : 'left';
		$popup_text1_col     = isset( $settings['popup_text1_col'] ) ? $settings['popup_text1_col'] : 'left';
		$popup_text2_col     = isset( $settings['popup_text2_col'] ) ? $settings['popup_text2_col'] : 'left';
		$popup_link_col      = isset( $settings['popup_link_col'] ) ? $settings['popup_link_col'] : 'left';
		$popup_image_order   = isset( $settings['popup_image_order'] ) && $settings['popup_image_order'] !== '' ? (int) $settings['popup_image_order'] : null;
		$popup_text1_order   = isset( $settings['popup_text1_order'] ) && $settings['popup_text1_order'] !== '' ? (int) $settings['popup_text1_order'] : null;
		$popup_text2_order   = isset( $settings['popup_text2_order'] ) && $settings['popup_text2_order'] !== '' ? (int) $settings['popup_text2_order'] : null;
		$popup_link_order    = isset( $settings['popup_link_order'] ) && $settings['popup_link_order'] !== '' ? (int) $settings['popup_link_order'] : null;

		if ( $popup_enable_global ) {
			foreach ( $items as $index => $item ) {
				// Respecter le switcher par item
				if ( empty( $item['item_popup_enable'] ) || $item['item_popup_enable'] !== 'yes' ) {
					continue;
				}

				$has_any_popup = true;

				// Média : image ou vidéo
				$media_type = isset( $item['item_popup_media_type'] ) ? $item['item_popup_media_type'] : 'none';
				$video_html = '';

				if ( $media_type === 'video' ) {
					$video_type     = isset( $item['item_popup_video_type'] ) ? $item['item_popup_video_type'] : 'youtube';
					$video_autoplay = ! empty( $item['item_popup_video_autoplay'] ) && $item['item_popup_video_autoplay'] === 'yes';
					$video_mute     = ! empty( $item['item_popup_video_mute'] )     && $item['item_popup_video_mute']     === 'yes';
					$video_loop     = ! empty( $item['item_popup_video_loop'] )     && $item['item_popup_video_loop']     === 'yes';

					if ( $video_type === 'hosted' ) {
						$hosted_url = isset( $item['item_popup_video_hosted']['url'] ) ? esc_url( $item['item_popup_video_hosted']['url'] ) : '';
						if ( $hosted_url ) {
							$attrs  = $video_autoplay ? ' autoplay' : '';
							$attrs .= $video_mute     ? ' muted'    : '';
							$attrs .= $video_loop     ? ' loop'     : '';
							// Bouton mute custom — icônes SVG inline
							// Muet : speaker barré (comme sur la capture)
							$icon_muted = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/></svg>';
							// Son actif : speaker avec ondes sonores
							$icon_sound = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>';
							$video_html = '<div class="nova-popup-video-container" data-muted="' . ( $video_mute ? '1' : '0' ) . '">'
								. '<video class="nova-popup-video" src="' . $hosted_url . '" playsinline' . $attrs . '></video>'
								. '<button class="nova-popup-mute-btn" aria-label="' . esc_attr__( 'Activer/désactiver le son', 'NOVA-addons' ) . '" type="button">'
								. '<span class="nova-popup-mute-icon nova-popup-icon-muted">' . $icon_muted . '</span>'
								. '<span class="nova-popup-mute-icon nova-popup-icon-sound">' . $icon_sound . '</span>'
								. '</button>'
								. '</div>';
						}
					} else {
						$raw_url = isset( $item['item_popup_video_url'] ) ? trim( $item['item_popup_video_url'] ) : '';
						if ( $raw_url ) {
							// Construire l'URL embed
							$embed_url = '';
							$params    = [];
							if ( $video_autoplay ) $params[] = 'autoplay=1';
							if ( $video_mute )     $params[] = 'mute=1';
							if ( $video_loop )     $params[] = 'loop=1';

							if ( $video_type === 'youtube' ) {
								// Extraire l'ID YouTube
								preg_match( '/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $raw_url, $m );
								$vid_id = isset( $m[1] ) ? $m[1] : '';
								if ( $vid_id ) {
									if ( $video_loop ) $params[] = 'playlist=' . $vid_id;
									$embed_url = 'https://www.youtube.com/embed/' . $vid_id;
								}
							} elseif ( $video_type === 'vimeo' ) {
								preg_match( '/vimeo\.com\/(\d+)/', $raw_url, $m );
								$vid_id = isset( $m[1] ) ? $m[1] : '';
								if ( $vid_id ) {
									$embed_url = 'https://player.vimeo.com/video/' . $vid_id;
								}
							}

							if ( $embed_url ) {
								if ( $params ) $embed_url .= '?' . implode( '&', $params );
								$video_html = '<div class="nova-popup-video-wrap"><iframe src="' . esc_url( $embed_url ) . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media"></iframe></div>';
							}
						}
					}
				}

				$popup_items_data[ $index ] = [
					'media_type'      => $media_type,
					'image'           => ( $media_type === 'image' && isset( $item['item_popup_image']['url'] ) ) ? esc_url( $item['item_popup_image']['url'] ) : '',
					'video_html'      => $video_html,
					'text1'           => isset( $item['item_popup_text1'] ) ? $item['item_popup_text1'] : '',
					'text2'           => isset( $item['item_popup_text2'] ) ? $item['item_popup_text2'] : '',
					'link_url'        => isset( $item['item_popup_link']['url'] ) ? esc_url( $item['item_popup_link']['url'] ) : '',
					'link_text'       => isset( $item['item_popup_link_text'] ) ? esc_html( $item['item_popup_link_text'] ) : '',
					'link_target'     => ! empty( $item['item_popup_link']['is_external'] ) ? '_blank' : '_self',
					'btn_icon_url'    => isset( $item['item_popup_btn_icon']['url'] ) ? esc_url( $item['item_popup_btn_icon']['url'] ) : '',
					'btn_icon_pos'    => isset( $item['item_popup_btn_icon_position'] ) ? $item['item_popup_btn_icon_position'] : 'after',
					// Layout global
					'layout'          => $popup_layout_global,
					'image_col'       => $popup_image_col,
					'text1_col'       => $popup_text1_col,
					'text2_col'       => $popup_text2_col,
					'link_col'        => $popup_link_col,
					'image_order'     => $popup_image_order,
					'text1_order'     => $popup_text1_order,
					'text2_order'     => $popup_text2_order,
					'link_order'      => $popup_link_order,
				];
			}
		}

		if ( $has_any_popup ) :
		?>
		<div id="nova-popup-overlay-<?php echo esc_attr( $widget_id_str ); ?>"
			class="nova-popup-overlay"
			data-animation="<?php echo esc_attr( $popup_animation ); ?>"
			aria-hidden="true"
			role="dialog">
			<div class="nova-popup-box" role="document">
				<button class="nova-popup-close" aria-label="<?php esc_attr_e( 'Fermer', 'NOVA-addons' ); ?>">&#x2715;</button>
				<?php foreach ( $popup_items_data as $idx => $pd ) :
					$is_2col = ( $pd['layout'] === '2_col' );

					// Construire les éléments HTML avec leur style d'ordre
					$el_html = [];

					// Média : image ou vidéo
					$order_style_media = $pd['image_order'] !== null ? ' style="order:' . $pd['image_order'] . ';"' : '';
					if ( $pd['media_type'] === 'image' && ! empty( $pd['image'] ) ) {
						$el_html['image'] = [
							'col'  => $pd['image_col'],
							'html' => '<div class="nova-popup-image"' . $order_style_media . '><img src="' . esc_url( $pd['image'] ) . '" alt=""></div>',
						];
					} elseif ( $pd['media_type'] === 'video' && ! empty( $pd['video_html'] ) ) {
						$el_html['image'] = [
							'col'  => $pd['image_col'],
							'html' => '<div class="nova-popup-media nova-popup-video-container"' . $order_style_media . '>' . $pd['video_html'] . '</div>',
						];
					}

					if ( ! empty( $pd['text1'] ) ) {
						$order_style = $pd['text1_order'] !== null ? ' style="order:' . $pd['text1_order'] . ';"' : '';
						$el_html['text1'] = [
							'col'  => $pd['text1_col'],
							'html' => '<div class="nova-popup-text1"' . $order_style . '>' . wp_kses_post( $pd['text1'] ) . '</div>',
						];
					}

					if ( ! empty( $pd['text2'] ) ) {
						$order_style = $pd['text2_order'] !== null ? ' style="order:' . $pd['text2_order'] . ';"' : '';
						$el_html['text2'] = [
							'col'  => $pd['text2_col'],
							'html' => '<div class="nova-popup-text2"' . $order_style . '>' . wp_kses_post( $pd['text2'] ) . '</div>',
						];
					}

					if ( ! empty( $pd['link_url'] ) && ! empty( $pd['link_text'] ) ) {
						$order_style = $pd['link_order'] !== null ? ' style="order:' . $pd['link_order'] . ';"' : '';

						// Icône du bouton (style Nova Cards)
						$icon_html = '';
						if ( ! empty( $pd['btn_icon_url'] ) ) {
							$icon_html = '<span class="nova-popup-btn-icon"><img src="' . esc_url( $pd['btn_icon_url'] ) . '" alt="" class="nova-popup-btn-arrow" aria-hidden="true" loading="lazy"></span>';
						}

						$text_html = '<span class="nova-popup-btn-text">' . esc_html( $pd['link_text'] ) . '</span>';

						// Ordre icône / texte
						$inner_html = $pd['btn_icon_pos'] === 'before'
							? $icon_html . $text_html
							: $text_html . $icon_html;

						$el_html['link'] = [
							'col'  => $pd['link_col'],
							'html' => '<div class="nova-popup-btn-wrap"' . $order_style . '>'
								. '<a href="' . esc_url( $pd['link_url'] ) . '" target="' . esc_attr( $pd['link_target'] ) . '" class="nova-popup-btn">'
								. $inner_html
								. '</a></div>',
						];
					}
				?>
				<div class="nova-popup-content nova-popup-layout-<?php echo esc_attr( $pd['layout'] ); ?>"
					data-popup-index="<?php echo esc_attr( $idx ); ?>"
					style="display:none;">
					<?php if ( $is_2col ) : ?>
						<?php
						$left_html  = '';
						$right_html = '';
						foreach ( $el_html as $el ) {
							if ( $el['col'] === 'right' ) {
								$right_html .= $el['html'];
							} else {
								$left_html .= $el['html'];
							}
						}
						?>
						<div class="nova-popup-col nova-popup-col-left"><?php echo $left_html; ?></div>
						<div class="nova-popup-col nova-popup-col-right"><?php echo $right_html; ?></div>
					<?php else : ?>
						<?php foreach ( $el_html as $el ) : ?>
							<?php echo $el['html']; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Génère le CSS personnalisé pour forcer l'application des propriétés de typographie
	 * Cette méthode est appelée par Elementor pour générer le CSS personnalisé
	 *
	 * @return string
	 */
	protected function get_custom_css() {
		$settings = $this->get_settings_for_display();
		$custom_css = '';
		$widget_id = $this->get_id();

		// CSS pour .nova-carousel-title et toutes ses balises HTML
		$title_selectors = '.nova-carousel-title, .nova-carousel-title h1, .nova-carousel-title h2, .nova-carousel-title h3, .nova-carousel-title h4, .nova-carousel-title h5, .nova-carousel-title h6, .nova-carousel-title p';
		
		$title_css = [];
		
		// Line height - vérifier toutes les variantes possibles de la structure des données
		if ( ! empty( $settings['title_typography_line_height'] ) ) {
			$line_height = $settings['title_typography_line_height'];
			$size = '';
			$unit = 'em';
			
			// Structure responsive avec sizes
			if ( isset( $line_height['sizes']['desktop']['size'] ) && $line_height['sizes']['desktop']['size'] !== '' ) {
				$size = $line_height['sizes']['desktop']['size'];
				$unit = isset( $line_height['sizes']['desktop']['unit'] ) ? $line_height['sizes']['desktop']['unit'] : 'em';
			} elseif ( isset( $line_height['size'] ) && $line_height['size'] !== '' ) {
				$size = $line_height['size'];
				$unit = isset( $line_height['unit'] ) ? $line_height['unit'] : 'em';
			}
			
			if ( $size !== '' ) {
				$title_css[] = 'line-height: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		// Letter spacing
		if ( ! empty( $settings['title_typography_letter_spacing'] ) ) {
			$letter_spacing = $settings['title_typography_letter_spacing'];
			$size = '';
			$unit = 'px';
			
			// Structure responsive avec sizes
			if ( isset( $letter_spacing['sizes']['desktop']['size'] ) && $letter_spacing['sizes']['desktop']['size'] !== '' ) {
				$size = $letter_spacing['sizes']['desktop']['size'];
				$unit = isset( $letter_spacing['sizes']['desktop']['unit'] ) ? $letter_spacing['sizes']['desktop']['unit'] : 'px';
			} elseif ( isset( $letter_spacing['size'] ) && $letter_spacing['size'] !== '' ) {
				$size = $letter_spacing['size'];
				$unit = isset( $letter_spacing['unit'] ) ? $letter_spacing['unit'] : 'px';
			}
			
			if ( $size !== '' ) {
				$title_css[] = 'letter-spacing: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		if ( ! empty( $title_css ) ) {
			$custom_css .= ".elementor-element-{$widget_id} {$title_selectors} { " . implode( ' ', $title_css ) . " }";
		}

		// CSS pour .nova-carousel-item-text et toutes ses balises HTML
		$text_selectors = '.nova-carousel-item-text, .nova-carousel-item-text h1, .nova-carousel-item-text h2, .nova-carousel-item-text h3, .nova-carousel-item-text h4, .nova-carousel-item-text h5, .nova-carousel-item-text h6, .nova-carousel-item-text p';
		
		$text_css = [];
		
		// Line height
		if ( ! empty( $settings['card_text_typography_line_height'] ) ) {
			$line_height = $settings['card_text_typography_line_height'];
			$size = '';
			$unit = 'em';
			
			// Structure responsive avec sizes
			if ( isset( $line_height['sizes']['desktop']['size'] ) && $line_height['sizes']['desktop']['size'] !== '' ) {
				$size = $line_height['sizes']['desktop']['size'];
				$unit = isset( $line_height['sizes']['desktop']['unit'] ) ? $line_height['sizes']['desktop']['unit'] : 'em';
			} elseif ( isset( $line_height['size'] ) && $line_height['size'] !== '' ) {
				$size = $line_height['size'];
				$unit = isset( $line_height['unit'] ) ? $line_height['unit'] : 'em';
			}
			
			if ( $size !== '' ) {
				$text_css[] = 'line-height: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		// Letter spacing
		if ( ! empty( $settings['card_text_typography_letter_spacing'] ) ) {
			$letter_spacing = $settings['card_text_typography_letter_spacing'];
			$size = '';
			$unit = 'px';
			
			// Structure responsive avec sizes
			if ( isset( $letter_spacing['sizes']['desktop']['size'] ) && $letter_spacing['sizes']['desktop']['size'] !== '' ) {
				$size = $letter_spacing['sizes']['desktop']['size'];
				$unit = isset( $letter_spacing['sizes']['desktop']['unit'] ) ? $letter_spacing['sizes']['desktop']['unit'] : 'px';
			} elseif ( isset( $letter_spacing['size'] ) && $letter_spacing['size'] !== '' ) {
				$size = $letter_spacing['size'];
				$unit = isset( $letter_spacing['unit'] ) ? $letter_spacing['unit'] : 'px';
			}
			
			if ( $size !== '' ) {
				$text_css[] = 'letter-spacing: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		if ( ! empty( $text_css ) ) {
			$custom_css .= ".elementor-element-{$widget_id} {$text_selectors} { " . implode( ' ', $text_css ) . " }";
		}

		return $custom_css;
	}

	/**
	 * Ajoute le CSS personnalisé pour les propriétés de typographie via le hook Elementor
	 *
	 * @param object $post_css Le fichier CSS Elementor
	 * @param object $element L'élément Elementor
	 */
	public function add_typography_css_to_element( $post_css, $element ) {
		// Vérifier si c'est le bon widget
		if ( ! $element instanceof \Elementor\Element_Base ) {
			return;
		}

		// Vérifier si c'est un widget et si c'est le bon widget
		if ( ! $element instanceof \Elementor\Widget_Base ) {
			return;
		}

		if ( $element->get_name() !== $this->get_name() ) {
			return;
		}

		$settings = $element->get_settings_for_display();
		$widget_id = $element->get_id();
		$post_id = $post_css->get_post_id();
		$selector = ".elementor-{$post_id} .elementor-element-{$widget_id}";

		// CSS pour .nova-carousel-title et toutes ses balises HTML
		$title_selectors = "{$selector} .nova-carousel-title, {$selector} .nova-carousel-title h1, {$selector} .nova-carousel-title h2, {$selector} .nova-carousel-title h3, {$selector} .nova-carousel-title h4, {$selector} .nova-carousel-title h5, {$selector} .nova-carousel-title h6, {$selector} .nova-carousel-title p";
		
		$title_css = [];
		
		// Line height
		if ( ! empty( $settings['title_typography_line_height'] ) ) {
			$line_height = $settings['title_typography_line_height'];
			$size = '';
			$unit = 'em';
			
			if ( isset( $line_height['sizes']['desktop']['size'] ) && $line_height['sizes']['desktop']['size'] !== '' ) {
				$size = $line_height['sizes']['desktop']['size'];
				$unit = isset( $line_height['sizes']['desktop']['unit'] ) ? $line_height['sizes']['desktop']['unit'] : 'em';
			} elseif ( isset( $line_height['size'] ) && $line_height['size'] !== '' ) {
				$size = $line_height['size'];
				$unit = isset( $line_height['unit'] ) ? $line_height['unit'] : 'em';
			}
			
			if ( $size !== '' ) {
				$title_css[] = 'line-height: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		// Letter spacing
		if ( ! empty( $settings['title_typography_letter_spacing'] ) ) {
			$letter_spacing = $settings['title_typography_letter_spacing'];
			$size = '';
			$unit = 'px';
			
			if ( isset( $letter_spacing['sizes']['desktop']['size'] ) && $letter_spacing['sizes']['desktop']['size'] !== '' ) {
				$size = $letter_spacing['sizes']['desktop']['size'];
				$unit = isset( $letter_spacing['sizes']['desktop']['unit'] ) ? $letter_spacing['sizes']['desktop']['unit'] : 'px';
			} elseif ( isset( $letter_spacing['size'] ) && $letter_spacing['size'] !== '' ) {
				$size = $letter_spacing['size'];
				$unit = isset( $letter_spacing['unit'] ) ? $letter_spacing['unit'] : 'px';
			}
			
			if ( $size !== '' ) {
				$title_css[] = 'letter-spacing: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		if ( ! empty( $title_css ) ) {
			$post_css->get_stylesheet()->add_rules( $title_selectors, implode( ' ', $title_css ) );
		}

		// CSS pour .nova-carousel-item-text et toutes ses balises HTML
		$text_selectors = "{$selector} .nova-carousel-item-text, {$selector} .nova-carousel-item-text h1, {$selector} .nova-carousel-item-text h2, {$selector} .nova-carousel-item-text h3, {$selector} .nova-carousel-item-text h4, {$selector} .nova-carousel-item-text h5, {$selector} .nova-carousel-item-text h6, {$selector} .nova-carousel-item-text p";
		
		$text_css = [];
		
		// Line height
		if ( ! empty( $settings['card_text_typography_line_height'] ) ) {
			$line_height = $settings['card_text_typography_line_height'];
			$size = '';
			$unit = 'em';
			
			if ( isset( $line_height['sizes']['desktop']['size'] ) && $line_height['sizes']['desktop']['size'] !== '' ) {
				$size = $line_height['sizes']['desktop']['size'];
				$unit = isset( $line_height['sizes']['desktop']['unit'] ) ? $line_height['sizes']['desktop']['unit'] : 'em';
			} elseif ( isset( $line_height['size'] ) && $line_height['size'] !== '' ) {
				$size = $line_height['size'];
				$unit = isset( $line_height['unit'] ) ? $line_height['unit'] : 'em';
			}
			
			if ( $size !== '' ) {
				$text_css[] = 'line-height: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		// Letter spacing
		if ( ! empty( $settings['card_text_typography_letter_spacing'] ) ) {
			$letter_spacing = $settings['card_text_typography_letter_spacing'];
			$size = '';
			$unit = 'px';
			
			if ( isset( $letter_spacing['sizes']['desktop']['size'] ) && $letter_spacing['sizes']['desktop']['size'] !== '' ) {
				$size = $letter_spacing['sizes']['desktop']['size'];
				$unit = isset( $letter_spacing['sizes']['desktop']['unit'] ) ? $letter_spacing['sizes']['desktop']['unit'] : 'px';
			} elseif ( isset( $letter_spacing['size'] ) && $letter_spacing['size'] !== '' ) {
				$size = $letter_spacing['size'];
				$unit = isset( $letter_spacing['unit'] ) ? $letter_spacing['unit'] : 'px';
			}
			
			if ( $size !== '' ) {
				$text_css[] = 'letter-spacing: ' . esc_attr( $size . $unit ) . ';';
			}
		}
		
		if ( ! empty( $text_css ) ) {
			$post_css->get_stylesheet()->add_rules( $text_selectors, implode( ' ', $text_css ) );
		}
	}
}
