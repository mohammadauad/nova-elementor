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
 * Widget NOVA Shuffle Card - Cartes empilées avec animation shuffle
 */
class Shuffle_Card_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-shuffle-card';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Shuffle Card', 'NOVA-addons' );
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
		return [ 'nova-shuffle-card-script' ];
	}

	/**
	 * Récupère les dépendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-shuffle-card-style' ];
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

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

		// Section Configuration Shuffle Card
		$this->start_controls_section(
			'section_shuffle_card_settings',
			[
				'label' => esc_html__( 'Configuration Shuffle Card', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'shuffle_card_rotation',
			[
				'label' => esc_html__( 'Angle de rotation par carte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range' => [
					'deg' => [
						'min' => 0,
						'max' => 15,
						'step' => 0.5,
					],
				],
				'default' => [
					'unit' => 'deg',
					'size' => 2,
				],
				'description' => esc_html__( 'Angle de rotation alterné pour les cartes (gauche/droite). Correspond à perSlideRotate dans Swiper Cards Effect.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'shuffle_card_gap',
			[
				'label' => esc_html__( 'Décalage vertical par carte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 8,
				],
				'description' => esc_html__( 'Décalage vertical entre chaque carte (en px). Correspond à perSlideOffset dans Swiper Cards Effect.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'shuffle_card_navigation_type',
			[
				'label' => esc_html__( 'Type de navigation', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'arrows',
				'options' => [
					'arrows' => esc_html__( 'Boutons de navigation', 'NOVA-addons' ),
					'none' => esc_html__( 'Aucune navigation', 'NOVA-addons' ),
				],
			]
		);

		// Icône bouton précédent (même configuration que le widget Carousel)
		$this->add_control(
			'navigation_prev_icon',
			[
				'label' => esc_html__( 'Icône bouton précédent', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-left',
					'library' => 'fa-solid',
				],
				'condition' => [
					'shuffle_card_navigation_type' => 'arrows',
				],
			]
		);

		// Icône bouton suivant (même configuration que le widget Carousel)
		$this->add_control(
			'navigation_next_icon',
			[
				'label' => esc_html__( 'Icône bouton suivant', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				],
				'condition' => [
					'shuffle_card_navigation_type' => 'arrows',
				],
			]
		);

		$this->add_control(
			'shuffle_card_animation_speed',
			[
				'label' => esc_html__( 'Vitesse d\'animation (sortie)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'ms' ],
				'range' => [
					'ms' => [
						'min' => 200,
						'max' => 1000,
						'step' => 50,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 400,
				],
				'description' => esc_html__( 'Durée de l\'animation de sortie de la carte (recommandé: 400-600ms)', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'exit_distance',
			[
				'label' => esc_html__( 'Distance de sortie', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'x' ],
				'range' => [
					'x' => [
						'min' => 1,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'x',
					'size' => 2,
				],
				'description' => esc_html__( 'Multiplicateur de la largeur de la carte pour la distance de sortie (1-3x)', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label' => esc_html__( 'Afficher la pagination', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->add_control(
			'cursor_enable',
			[
				'label' => esc_html__( 'Activer le curseur personnalisé', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'description' => esc_html__( 'Curseur personnalisé qui suit la souris (desktop uniquement, >810px)', 'NOVA-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'cursor_text',
			[
				'label' => esc_html__( 'Texte du curseur', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'NEXT',
				'placeholder' => esc_html__( 'ex: NEXT, SHUFFLE, MORE', 'NOVA-addons' ),
				'condition' => [
					'cursor_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'cursor_bg_color',
			[
				'label' => esc_html__( 'Couleur de fond du curseur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'condition' => [
					'cursor_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'cursor_text_color',
			[
				'label' => esc_html__( 'Couleur du texte du curseur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'condition' => [
					'cursor_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'cursor_font_size',
			[
				'label' => esc_html__( 'Taille de police du curseur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 32,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 14,
				],
				'condition' => [
					'cursor_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'cursor_padding',
			[
				'label' => esc_html__( 'Padding du curseur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 30,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 8,
				],
				'condition' => [
					'cursor_enable' => 'yes',
				],
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
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
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

		$repeater->add_control(
			'item_icon',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'default' => [
					'value' => '',
					'library' => '',
				],
				'separator' => 'before',
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
				'label' => esc_html__( 'Cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ item_text }}}',
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
				'selector' => '{{WRAPPER}} .nova-shuffle-card-widget',
			]
		);

		$this->add_responsive_control(
			'widget_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-shuffle-card-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-shuffle-card-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'widget_border',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-widget',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'widget_box_shadow',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-widget',
			]
		);

		$this->end_controls_section();

		// Section Style - Title
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-title',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-title' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .nova-shuffle-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-description',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'description_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Button
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
				'selector' => '{{WRAPPER}} .nova-shuffle-card-button',
			]
		);

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-button',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-shuffle-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-button',
			]
		);

		$this->end_controls_section();

		// Section Style - Cards
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
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 800,
						'step' => 10,
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
				'default' => [
					'unit' => 'px',
					'size' => 500,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item' => 'width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'card_background',
				'label' => esc_html__( 'Arrière-plan', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-shuffle-card-item',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-item',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-shuffle-card-item',
				'separator' => 'before',
			]
		);

		// Card hover shadow
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow_hover',
				'label' => esc_html__( 'Ombre au survol', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-shuffle-card-item:hover',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// Section Style - Card Text
		$this->start_controls_section(
			'section_style_card_text',
			[
				'label' => esc_html__( 'Texte de la carte', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'card_text_typography',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-item-text',
			]
		);

		$this->add_control(
			'card_text_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_text_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Card Date
		$this->start_controls_section(
			'section_style_card_date',
			[
				'label' => esc_html__( 'Date de la carte', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'card_date_typography',
				'selector' => '{{WRAPPER}} .nova-shuffle-card-item-date',
			]
		);

		$this->add_control(
			'card_date_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item-date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_date_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-item-date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Navigation
		$this->start_controls_section(
			'section_style_navigation',
			[
				'label' => esc_html__( 'Navigation', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'nav_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 44,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-nav-prev, {{WRAPPER}} .nova-shuffle-card-nav-next, {{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important; min-width: {{SIZE}}{{UNIT}} !important; min-height: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_control(
			'nav_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-nav-prev, {{WRAPPER}} .nova-shuffle-card-nav-next, {{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .nova-shuffle-card-nav-prev svg, {{WRAPPER}} .nova-shuffle-card-nav-next svg, {{WRAPPER}} .swiper-button-prev svg, {{WRAPPER}} .swiper-button-next svg' => 'color: {{VALUE}} !important; stroke: {{VALUE}} !important;',
					'{{WRAPPER}} .nova-shuffle-card-nav-prev svg path, {{WRAPPER}} .nova-shuffle-card-nav-next svg path, {{WRAPPER}} .swiper-button-prev svg path, {{WRAPPER}} .swiper-button-next svg path' => 'stroke: {{VALUE}} !important;',
					'{{WRAPPER}} .nova-shuffle-card-nav-prev i, {{WRAPPER}} .nova-shuffle-card-nav-next i, {{WRAPPER}} .swiper-button-prev i, {{WRAPPER}} .swiper-button-next i' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .nova-shuffle-card-nav-prev .elementor-icon, {{WRAPPER}} .nova-shuffle-card-nav-next .elementor-icon, {{WRAPPER}} .swiper-button-prev .elementor-icon, {{WRAPPER}} .swiper-button-next .elementor-icon' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .nova-shuffle-card-nav-prev .elementor-icon svg, {{WRAPPER}} .nova-shuffle-card-nav-next .elementor-icon svg' => 'fill: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'nav_background',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-nav-prev, {{WRAPPER}} .nova-shuffle-card-nav-next, {{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'nav_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-shuffle-card-nav-prev, {{WRAPPER}} .nova-shuffle-card-nav-next, {{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->end_controls_section();
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
	 * Récupère les SVG pour le background créatif
	 *
	 * @return array
	 */
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
		<button class="nova-shuffle-card-nav nova-shuffle-card-<?php echo esc_attr( $type ); ?>" aria-label="<?php echo $aria_label; ?>">
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
	 * Affiche le widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Préparer les items
		$items = [];
		if ( $settings['data_source'] === 'post_type' ) {
			$posts = $this->get_posts( $settings );
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

		// Configuration pour Swiper Cards Effect
		$navigation_type = isset( $settings['shuffle_card_navigation_type'] ) ? $settings['shuffle_card_navigation_type'] : 'arrows';
		$rotation_raw = isset( $settings['shuffle_card_rotation']['size'] ) ? $settings['shuffle_card_rotation']['size'] : 2;
		$gap_raw = isset( $settings['shuffle_card_gap']['size'] ) ? $settings['shuffle_card_gap']['size'] : 8;
		$animation_speed_raw = isset( $settings['shuffle_card_animation_speed']['size'] ) ? $settings['shuffle_card_animation_speed']['size'] : 400;
		$exit_distance_raw = isset( $settings['exit_distance']['size'] ) ? $settings['exit_distance']['size'] : 2;
		
		// Configuration du curseur personnalisé
		$cursor_enable = ! empty( $settings['cursor_enable'] ) && $settings['cursor_enable'] === 'yes';
		$cursor_text = isset( $settings['cursor_text'] ) ? $settings['cursor_text'] : 'NEXT';
		$cursor_bg_color = isset( $settings['cursor_bg_color'] ) ? $settings['cursor_bg_color'] : '#000000';
		$cursor_text_color = isset( $settings['cursor_text_color'] ) ? $settings['cursor_text_color'] : '#ffffff';
		$cursor_font_size = isset( $settings['cursor_font_size']['size'] ) ? $settings['cursor_font_size']['size'] : 14;
		$cursor_padding = isset( $settings['cursor_padding']['size'] ) ? $settings['cursor_padding']['size'] : 8;
		
		$swiper_config = [
			'effect' => 'cards',
			'cardsEffect' => [
				'slideShadows' => false, // Désactiver les ombres des slides
				'rotate' => true,
				'perSlideOffset' => (int) $gap_raw,
				'perSlideRotate' => (float) $rotation_raw,
			],
			'grabCursor' => true,
			'speed' => (int) $animation_speed_raw,
			'exitDistance' => (float) $exit_distance_raw,
			'cursorEnable' => $cursor_enable,
			'cursorText' => $cursor_text,
			'cursorBgColor' => $cursor_bg_color,
			'cursorTextColor' => $cursor_text_color,
			'cursorFontSize' => (int) $cursor_font_size,
			'cursorPadding' => (int) $cursor_padding,
		];
		
		if ( $navigation_type === 'arrows' ) {
			$swiper_config['navigation'] = [
				'nextEl' => '.swiper-button-next',
				'prevEl' => '.swiper-button-prev',
			];
		}
		
		if ( ! empty( $settings['show_pagination'] ) && $settings['show_pagination'] === 'yes' ) {
			$swiper_config['pagination'] = [
				'el' => '.swiper-pagination',
				'clickable' => true,
			];
		}

		// Vérifier si le contenu principal existe
		$has_content = ! empty( $settings['title_text'] ) || ! empty( $settings['description_text'] ) || ! empty( $settings['button_text'] );
		$widget_id = 'nova-shuffle-card-' . $this->get_id();
		?>
		<div class="nova-shuffle-card-widget" data-swiper-config="<?php echo esc_attr( wp_json_encode( $swiper_config ) ); ?>">
			<div class="nova-shuffle-card-container">
				<?php if ( $has_content ) : ?>
					<div class="nova-shuffle-card-content">
						<?php if ( ! empty( $settings['title_text'] ) ) : ?>
							<div class="nova-shuffle-card-title"><?php echo wp_kses_post( $settings['title_text'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $settings['description_text'] ) ) : ?>
							<div class="nova-shuffle-card-description"><?php echo wp_kses_post( $settings['description_text'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $settings['button_text'] ) ) : ?>
							<a href="<?php echo esc_url( $button_link['url'] ); ?>" <?php echo $this->get_render_attribute_string( 'button_link' ); ?> class="nova-shuffle-card-button">
								<?php echo esc_html( $settings['button_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<div class="nova-shuffle-card-wrapper<?php echo $navigation_type === 'arrows' ? ' has-arrows' : ''; ?>">
					<!-- Nova Shuffle Card Slider Container -->
					<div class="nova-shuffle-card-slider <?php echo esc_attr( $widget_id ); ?><?php echo ! $has_content ? ' full-width' : ''; ?>">
						<?php foreach ( $items as $index => $item ) : ?>
							<?php
							$item_link = isset( $item['item_link'] ) ? $item['item_link'] : [];
							$item_image = isset( $item['item_image'] ) ? $item['item_image'] : [];
							$item_text = isset( $item['item_text'] ) ? $item['item_text'] : '';
							$item_date = isset( $item['item_date'] ) ? $item['item_date'] : '';
							$item_icon = isset( $item['item_icon'] ) ? $item['item_icon'] : [];
							$item_background_color = isset( $item['item_background_color'] ) ? $item['item_background_color'] : '';
							
							// Résoudre la couleur globale si elle est définie
							$global_color_resolved = false;
							if ( isset( $item['__globals__'] ) && ! empty( $item['__globals__']['item_background_color'] ) ) {
								$global_ref = $item['__globals__']['item_background_color'];
								if ( preg_match( '/id=([a-z0-9]+)/i', $global_ref, $matches ) ) {
									$global_id = $matches[1];
									$item_background_color = 'var(--e-global-color-' . esc_attr( $global_id ) . ')';
									$global_color_resolved = true;
								}
							}
							
							if ( empty( $item_background_color ) && ! $global_color_resolved ) {
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
							
							if ( ! empty( $item_background_color ) && ! $global_color_resolved ) {
								if ( strpos( $item_background_color, 'var(--e-global-color-' ) === 0 ) {
									$global_color_resolved = true;
								}
							}
							
							$image_url = is_array( $item_image ) && isset( $item_image['url'] ) ? $item_image['url'] : '';
							$link_url = is_array( $item_link ) && isset( $item_link['url'] ) ? $item_link['url'] : '';
							
							// Sélectionner un SVG aléatoire pour le background créatif
							$selected_svg = '';
							$svg_transforms = [];
							if ( $creative_background_enable && ! empty( $svg_backgrounds ) ) {
								$selected_svg = $svg_backgrounds[ array_rand( $svg_backgrounds ) ];
								
								$has_rotation = ! empty( $settings['creative_background_random_rotation'] ) && $settings['creative_background_random_rotation'] === 'yes';
								$has_flip = ! empty( $settings['creative_background_random_flip'] ) && $settings['creative_background_random_flip'] === 'yes';
								
								if ( $has_rotation || $has_flip ) {
									$random_rotation = $has_rotation ? ( mt_rand( 0, 1 ) === 1 ) : false;
									$random_flip = $has_flip ? ( mt_rand( 0, 1 ) === 1 ) : false;
									
									if ( $has_rotation && $has_flip ) {
										if ( $random_flip ) {
											$svg_transforms[] = 'scaleX(-1)';
										}
										if ( $random_rotation ) {
											$svg_transforms[] = 'rotate(180deg)';
										}
									} elseif ( $has_rotation ) {
										if ( $random_rotation ) {
											$svg_transforms[] = 'rotate(180deg)';
										}
									} elseif ( $has_flip ) {
										if ( $random_flip ) {
											$svg_transforms[] = 'scaleX(-1)';
										}
									}
								}
							}
							
							// Récupérer les dimensions du SVG si nécessaire
							$svg_width = '';
							$svg_height = '';
							if ( $creative_background_enable && ! empty( $selected_svg ) ) {
								$svg_filename = basename( parse_url( $selected_svg, PHP_URL_PATH ) );
								if ( defined( 'NOVA_ADDONS_PLUGIN_DIR' ) ) {
									$svg_path = NOVA_ADDONS_PLUGIN_DIR . 'assets/svg/' . $svg_filename;
								} else {
									$plugin_path = plugin_dir_path( __FILE__ );
									$plugin_dir = dirname( dirname( $plugin_path ) );
									$svg_path = $plugin_dir . '/assets/svg/' . $svg_filename;
								}
								
								if ( file_exists( $svg_path ) ) {
									$svg_content = file_get_contents( $svg_path );
									if ( $svg_content ) {
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
							
							// Appliquer le masque SVG si activé
							if ( $creative_background_enable && ! empty( $selected_svg ) ) {
								$item_style .= '-webkit-mask-image: url(' . esc_url( $selected_svg ) . '); ';
								$item_style .= 'mask-image: url(' . esc_url( $selected_svg ) . '); ';
								$item_style .= '-webkit-mask-size: contain; mask-size: contain; ';
								$item_style .= '-webkit-mask-position: center; mask-position: center; ';
								$item_style .= '-webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; ';
								
								if ( ! empty( $svg_transforms ) ) {
									$item_style .= 'transform: ' . implode( ' ', $svg_transforms ) . '; ';
								}
							}
							
							// Ajouter la couleur de fond personnalisée si définie
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
								$item_style .= 'background-color: ' . esc_attr( $creative_background_color ) . '; ';
							}
							
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
							?>
							<div class="nova-shuffle-card-slide swiper-slide elementor-repeater-item-<?php echo esc_attr( isset( $item['_id'] ) ? $item['_id'] : $index ); ?><?php echo $image_as_background && ! empty( $image_url ) ? ' has-background-image' : ''; ?><?php echo $creative_background_enable ? ' has-creative-background' : ''; ?><?php echo $image_overlay_enable && ! empty( $image_url ) ? ' has-image-overlay' : ''; ?>" data-svg-url="<?php echo $creative_background_enable && ! empty( $selected_svg ) ? esc_url( $selected_svg ) : ''; ?>"<?php echo $item_data_attrs; ?>>
								<div class="nova-shuffle-card-item"<?php echo ! empty( $item_style ) ? ' style="' . $item_style . '"' : ''; ?>>
									<?php if ( ! empty( $image_url ) && ! $image_as_background ) : ?>
										<div class="nova-shuffle-card-item-image">
											<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $item_text ); ?>">
											<?php if ( $image_overlay_enable ) : ?>
												<div class="nova-shuffle-card-item-overlay" style="background-color: <?php echo esc_attr( $image_overlay_color ); ?>;"></div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<?php if ( $image_overlay_enable && ! empty( $image_url ) && ( $image_as_background || ( $creative_background_enable && ! empty( $selected_svg ) ) ) ) : ?>
										<div class="nova-shuffle-card-item-overlay" style="background-color: <?php echo esc_attr( $image_overlay_color ); ?>;"></div>
									<?php endif; ?>
									
									<div class="nova-shuffle-card-item-content">
										<?php if ( ! empty( $item_icon ) && ! empty( $item_icon['value'] ) ) : ?>
											<div class="nova-shuffle-card-item-icon">
												<?php Icons_Manager::render_icon( $item_icon, [ 'aria-hidden' => 'true' ] ); ?>
											</div>
										<?php endif; ?>
										<?php if ( ! empty( $item_text ) ) : ?>
											<div class="nova-shuffle-card-item-text"><?php echo wp_kses_post( $item_text ); ?></div>
										<?php endif; ?>
										<?php if ( ! empty( $item_date ) ) : ?>
											<div class="nova-shuffle-card-item-date"><?php echo wp_kses_post( $item_date ); ?></div>
										<?php endif; ?>
									</div>
									<?php if ( ! empty( $link_url ) ) : ?>
										<a href="<?php echo esc_url( $link_url ); ?>" class="nova-shuffle-card-item-link" aria-label="<?php echo esc_attr( $item_text ); ?>"></a>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
						<?php if ( ! empty( $settings['show_pagination'] ) && $settings['show_pagination'] === 'yes' ) : ?>
							<div class="swiper-pagination nova-shuffle-card-pagination">
								<?php foreach ( $items as $index => $item ) : ?>
									<span class="swiper-pagination-bullet pagination-bullet<?php echo $index === 0 ? ' active' : ''; ?>" data-index="<?php echo esc_attr( $index ); ?>"></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<?php if ( $navigation_type === 'arrows' ) : ?>
						<?php
						// Récupérer les icônes configurées (même logique que le widget Carousel)
						$prev_icon = isset( $settings['navigation_prev_icon'] ) ? $settings['navigation_prev_icon'] : [];
						$next_icon = isset( $settings['navigation_next_icon'] ) ? $settings['navigation_next_icon'] : [];
						
						// Fonction helper pour rendre une icône (basée sur le widget Carousel)
						$render_nav_icon = function( $icon, $default_path, $icon_type = 'prev' ) {
							$icon_rendered = false;
							
							if ( ! empty( $icon ) ) {
								// Essayer d'abord avec Icons_Manager
								ob_start();
								\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
								$icon_output = ob_get_clean();
								
								if ( ! empty( $icon_output ) ) {
									echo $icon_output;
									$icon_rendered = true;
								} else {
									// Si Icons_Manager ne fonctionne pas, essayer de charger le SVG directement
									$svg_url = null;
									
									// Vérifier si c'est un SVG uploadé
									if ( isset( $icon['library'] ) && ( $icon['library'] === 'svg' || $icon['library'] === 'svg-upload' ) ) {
										if ( isset( $icon['value']['url'] ) ) {
											$svg_url = $icon['value']['url'];
										} elseif ( isset( $icon['value'] ) && is_string( $icon['value'] ) ) {
											$svg_url = $icon['value'];
										}
									}
									
									// Autres formats possibles
									if ( ! $svg_url && isset( $icon['value'] ) ) {
										if ( is_string( $icon['value'] ) && ( strpos( $icon['value'], '.svg' ) !== false || strpos( $icon['value'], 'http' ) === 0 ) ) {
											$svg_url = $icon['value'];
										} elseif ( is_array( $icon['value'] ) && isset( $icon['value']['url'] ) ) {
											$svg_url = $icon['value']['url'];
										}
									}
									
									// Si on a une URL SVG, charger le contenu du fichier
									if ( $svg_url ) {
										$svg_path = str_replace( content_url(), WP_CONTENT_DIR, $svg_url );
										
										if ( file_exists( $svg_path ) ) {
											$svg_content = file_get_contents( $svg_path );
											if ( $svg_content ) {
												// Nettoyer le contenu SVG
												$svg_content = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg_content );
												
												echo wp_kses( $svg_content, [
													'svg' => [ 'xmlns' => [], 'width' => [], 'height' => [], 'viewBox' => [], 'fill' => [], 'class' => [], 'style' => [], 'preserveAspectRatio' => [] ],
													'rect' => [ 'width' => [], 'height' => [], 'rx' => [], 'ry' => [], 'transform' => [], 'fill' => [], 'stroke' => [], 'stroke-width' => [] ],
													'path' => [ 'd' => [], 'stroke' => [], 'stroke-width' => [], 'fill' => [], 'fill-rule' => [], 'stroke-linecap' => [], 'stroke-linejoin' => [] ],
													'circle' => [ 'cx' => [], 'cy' => [], 'r' => [], 'fill' => [], 'stroke' => [] ],
													'g' => [ 'fill' => [], 'transform' => [] ],
												] );
												$icon_rendered = true;
											}
										}
									}
								}
							}
							
							// Si l'icône n'a pas été rendue, utiliser le fallback
							if ( ! $icon_rendered ) {
								?>
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<?php echo $default_path; ?>
								</svg>
								<?php
							}
						};
						?>
						<button class="nova-shuffle-card-nav-prev swiper-button-prev" aria-label="<?php echo esc_attr__( 'Précédent', 'NOVA-addons' ); ?>">
							<?php
							$render_nav_icon( $prev_icon, '<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>', 'prev' );
							?>
						</button>
						<button class="nova-shuffle-card-nav-next swiper-button-next" aria-label="<?php echo esc_attr__( 'Suivant', 'NOVA-addons' ); ?>">
							<?php
							$render_nav_icon( $next_icon, '<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>', 'next' );
							?>
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
