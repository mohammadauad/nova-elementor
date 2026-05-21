<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Gallery Multi Filters - Galerie avec filtres multiples par taxonomie
 */
class Gallery_Multi_Filters_Widget extends Widget_Base {

	/**
	 * Variable statique pour stocker le HTML du popup
	 *
	 * @var string
	 */
	private static $popup_html = '';

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-gallery-multi-filters';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Gallery Multi Filters', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
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
		return [ 'nova-gallery-multi-filters-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-gallery-multi-filters-script' ];
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

		// Post Type
		$this->add_control(
			'post_type',
			[
				'label' => esc_html__( 'Post Type', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_post_types(),
				'default' => 'post',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => esc_html__( 'Nombre d\'éléments', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 100,
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
					'rand' => esc_html__( 'Aléatoire', 'NOVA-addons' ),
					'menu_order' => esc_html__( 'Ordre du menu', 'NOVA-addons' ),
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

		// Image Source
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

		// Section Mise en page
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Mise en page', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'card_template_path',
			[
				'label' => esc_html__( 'Chemin du Template PHP personnalisé', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__( 'Ex: template-parts/card-car.php. Si rempli, ce fichier de votre thème sera utilisé pour afficher le contenu de chaque carte. La variable $item (ou $args dans WP 5.5+) sera disponible.', 'NOVA-addons' ),
				'default' => '',
			]
		);

		$this->end_controls_section();

		// Section Filtres
		$this->start_controls_section(
			'section_filters',
			[
				'label' => esc_html__( 'Filtres', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'enable_filters',
			[
				'label' => esc_html__( 'Activer les filtres', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'En mode manuel, les filtres utilisent les catégories définies pour chaque item. En mode post type, ils utilisent les taxonomies sélectionnées.', 'NOVA-addons' ),
			]
		);

		$repeater_filters = new Repeater();

		$repeater_filters->add_control(
			'taxonomy',
			[
				'label' => esc_html__( 'Taxonomie', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_taxonomies(),
				'default' => '',
			]
		);

		$repeater_filters->add_control(
			'filter_label',
			[
				'label' => esc_html__( 'Label du filtre (optionnel)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$repeater_filters->add_control(
			'use_image',
			[
				'label' => esc_html__( 'Afficher image au lieu du texte ?', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$repeater_filters->add_control(
			'acf_image_key',
			[
				'label' => esc_html__( 'Clé ACF de l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'condition' => [
					'use_image' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'enable_slider',
			[
				'label' => esc_html__( 'Activer slider (Owl Carousel)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$repeater_filters->add_control(
			'slider_breakpoint',
			[
				'label' => esc_html__( 'Activer slider sur', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'all'    => esc_html__( 'Tous les écrans', 'NOVA-addons' ),
					'tablet' => esc_html__( 'Tablet et Mobile (≤ 1024px)', 'NOVA-addons' ),
					'mobile' => esc_html__( 'Mobile seulement (≤ 767px)', 'NOVA-addons' ),
				],
				'default' => 'all',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'show_slider_arrows',
			[
				'label' => esc_html__( 'Afficher flèches (Slider)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_prev_image',
			[
				'label' => esc_html__( 'Flèche précédent (image/SVG)', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_next_image',
			[
				'label' => esc_html__( 'Flèche suivant (image/SVG)', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_size',
			[
				'label' => esc_html__( 'Taille flèche (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 32,
				'min' => 10,
				'max' => 120,
				'step' => 1,
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_icon_size',
			[
				'label' => esc_html__( 'Taille icône/image intérieure (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 16,
				'min' => 6,
				'max' => 80,
				'step' => 1,
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_color',
			[
				'label' => esc_html__( 'Couleur icône flèche', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_bg',
			[
				'label' => esc_html__( 'Background flèche', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_border_color',
			[
				'label' => esc_html__( 'Couleur bordure flèche', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_border_width',
			[
				'label' => esc_html__( 'Épaisseur bordure (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 0,
				'max' => 10,
				'step' => 1,
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'arrow_border_radius',
			[
				'label' => esc_html__( 'Border radius flèche (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 50,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'condition' => [
					'enable_slider' => 'yes',
					'show_slider_arrows' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_align',
			[
				'label' => esc_html__( 'Alignement du slider', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [ 'title' => esc_html__( 'Gauche', 'NOVA-addons' ),  'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Centre', 'NOVA-addons' ),  'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Droite', 'NOVA-addons' ),  'icon' => 'eicon-text-align-right' ],
				],
				'default' => 'left',
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'pin_filter_all',
			[
				'label' => esc_html__( 'Épingler bouton "Tout" (hors slider)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Le bouton "Tout" reste visible et fixé à gauche, en dehors du carousel.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_item_min_width',
			[
				'label' => esc_html__( 'Largeur min item (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 600,
				'step' => 1,
				'description' => esc_html__( '0 = auto. Permet de forcer une largeur minimale pour chaque filtre.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);
		$repeater_filters->add_control(
			'slider_item_min_width_tablet',
			[
				'label' => esc_html__( 'Largeur min item Tablet (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 600,
				'step' => 1,
				'description' => esc_html__( '0 = hérite de Desktop', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);
		$repeater_filters->add_control(
			'slider_item_min_width_mobile',
			[
				'label' => esc_html__( 'Largeur min item Mobile (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 600,
				'step' => 1,
				'description' => esc_html__( '0 = hérite de Tablet/Desktop', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_margin',
			[
				'label' => esc_html__( 'Marge entre items (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 10,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'description' => esc_html__( 'Équivalent du "margin" Owl Carousel entre chaque item.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);
		$repeater_filters->add_control(
			'slider_margin_tablet',
			[
				'label' => esc_html__( 'Marge Tablet (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'description' => esc_html__( '0 = hérite de Desktop', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);
		$repeater_filters->add_control(
			'slider_margin_mobile',
			[
				'label' => esc_html__( 'Marge Mobile (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 100,
				'step' => 1,
				'description' => esc_html__( '0 = hérite de Tablet/Desktop', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_items',
			[
				'label' => esc_html__( 'Items visibles (Desktop)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 20,
				'step' => 1,
				'description' => esc_html__( '0 = auto (largeur naturelle). Nombre d\'items visibles simultanément.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);
		$repeater_filters->add_control(
			'slider_items_tablet',
			[
				'label' => esc_html__( 'Items visibles (Tablet)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 20,
				'step' => 1,
				'description' => esc_html__( '0 = hérite de Desktop', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);
		$repeater_filters->add_control(
			'slider_items_mobile',
			[
				'label' => esc_html__( 'Items visibles (Mobile)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 20,
				'step' => 1,
				'description' => esc_html__( '0 = hérite de Tablet/Desktop', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_loop',
			[
				'label'   => esc_html__( 'Loop (infini) — Desktop', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [ 'no' => esc_html__( 'Non', 'NOVA-addons' ), 'yes' => esc_html__( 'Oui', 'NOVA-addons' ) ],
				'default' => 'no',
				'description' => esc_html__( 'Fait défiler les filtres en boucle continue. Note: nécessite au moins 2x le nombre d\'items visibles et est incompatible avec autoWidth.', 'NOVA-addons' ),
				'condition' => [ 'enable_slider' => 'yes' ],
			]
		);

		$repeater_filters->add_control(
			'slider_loop_tablet',
			[
				'label'   => esc_html__( 'Loop (infini) — Tablet', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [ 'inherit' => esc_html__( 'Hériter Desktop', 'NOVA-addons' ), 'no' => esc_html__( 'Non', 'NOVA-addons' ), 'yes' => esc_html__( 'Oui', 'NOVA-addons' ) ],
				'default' => 'inherit',
				'condition' => [ 'enable_slider' => 'yes' ],
			]
		);

		$repeater_filters->add_control(
			'slider_loop_mobile',
			[
				'label'   => esc_html__( 'Loop (infini) — Mobile', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [ 'inherit' => esc_html__( 'Hériter Tablet', 'NOVA-addons' ), 'no' => esc_html__( 'Non', 'NOVA-addons' ), 'yes' => esc_html__( 'Oui', 'NOVA-addons' ) ],
				'default' => 'inherit',
				'condition' => [ 'enable_slider' => 'yes' ],
			]
		);

		$repeater_filters->add_control(
			'slider_autowidth',
			[
				'label' => esc_html__( 'Largeur automatique (autoWidth)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'description' => esc_html__( 'Chaque item prend sa largeur naturelle (recommandé pour les filtres texte/image). Note: incompatible avec le mode loop.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_free_drag',
			[
				'label' => esc_html__( 'Drag libre (freeDrag)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Permet un glissement libre sans snap sur les slides.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'slider_disable_drag',
			[
				'label' => esc_html__( 'Désactiver drag & drop', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none'    => esc_html__( 'Jamais (drag toujours actif)', 'NOVA-addons' ),
					'all'     => esc_html__( 'Tous les écrans', 'NOVA-addons' ),
					'desktop' => esc_html__( 'Desktop seulement (> 1024px)', 'NOVA-addons' ),
					'tablet'  => esc_html__( 'Tablet seulement (768px – 1024px)', 'NOVA-addons' ),
					'mobile'  => esc_html__( 'Mobile seulement (≤ 767px)', 'NOVA-addons' ),
					'tablet_desktop' => esc_html__( 'Tablet et Desktop (> 767px)', 'NOVA-addons' ),
					'mobile_tablet'  => esc_html__( 'Mobile et Tablet (≤ 1024px)', 'NOVA-addons' ),
				],
				'default' => 'none',
				'description' => esc_html__( 'Désactive le glissement souris/tactile selon le breakpoint.', 'NOVA-addons' ),
				'condition' => [
					'enable_slider' => 'yes',
				],
			]
		);

		$repeater_filters->add_control(
			'hide_filter_all',
			[
				'label' => esc_html__( 'Masquer bouton "Tout"', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$repeater_filters->add_control(
			'hide_term_title',
			[
				'label' => esc_html__( 'Masquer le titre du terme', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
			]
		);

		$this->add_control(
			'filters_list',
			[
				'label' => esc_html__( 'Groupes de Filtres', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater_filters->get_controls(),
				'default' => [],
				'title_field' => '{{{ filter_label || taxonomy }}}',
				'condition' => [
					'enable_filters' => 'yes',
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'filter_all_text',
			[
				'label' => esc_html__( 'Texte du bouton "Tout"', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Tout', 'NOVA-addons' ),
				'condition' => [
					'enable_filters' => 'yes',
				],
			]
		);

		$this->add_control(
			'hide_filter_all_mobile',
			[
				'label' => esc_html__( 'Masquer "Tout" sur mobile', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
				'condition' => [
					'enable_filters' => 'yes',
				],
				'description' => esc_html__( 'Si activé, le bouton "Tout" sera masqué sur mobile et le premier filtre sera activé automatiquement.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'filters_sticky',
			[
				'label'     => esc_html__( 'Filtres sticky', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'yes' => esc_html__( 'Oui', 'NOVA-addons' ),
					'no'  => esc_html__( 'Non', 'NOVA-addons' ),
				],
				'default'        => 'no',
				'tablet_default' => 'no',
				'mobile_default' => 'no',
				'separator' => 'before',
				'condition' => [ 'enable_filters' => 'yes' ],
				'description' => esc_html__( 'Le bloc de filtres devient sticky quand l\'utilisateur arrive dessus lors du scroll. Configurable par breakpoint.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'filters_sticky_offset',
			[
				'label'      => esc_html__( 'Offset sticky (top)', 'NOVA-addons' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 300 ] ],
				'default'        => [ 'size' => 0, 'unit' => 'px' ],
				'tablet_default' => [ 'size' => 0, 'unit' => 'px' ],
				'mobile_default' => [ 'size' => 0, 'unit' => 'px' ],
				'condition'  => [ 'enable_filters' => 'yes', 'filters_sticky' => 'yes' ],
				'description' => esc_html__( 'Distance depuis le haut de la fenêtre quand le bloc est sticky. Configurable par breakpoint (ex: hauteur du header).', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// Section Items Manuels
		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Items de la galerie', 'NOVA-addons' ),
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
			'item_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'Entrez le titre', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'item_category',
			[
				'label' => esc_html__( 'Catégorie (pour filtres)', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'ex: Oiseaux, Papillons', 'NOVA-addons' ),
				'description' => esc_html__( 'Utilisé pour les filtres si activés', 'NOVA-addons' ),
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

		$this->add_control(
			'items_list',
			[
				'label' => esc_html__( 'Items', 'NOVA-addons' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ item_title }}}',
			]
		);

		$this->end_controls_section();

		// Section Configuration de la grille
		$this->start_controls_section(
			'section_grid_settings',
			[
				'label' => esc_html__( 'Configuration de la grille', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'grid_columns_mode',
			[
				'label' => esc_html__( 'Mode de colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => [
					'auto' => esc_html__( 'Automatique (1fr)', 'NOVA-addons' ),
					'pixels' => esc_html__( 'Pixels (px)', 'NOVA-addons' ),
				],
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Mode automatique : colonnes égales. Mode pixels : définissez la largeur de chaque colonne en pixels.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'frontend_available' => true,
				'condition' => [
					'creative_background_enable!' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'columns_creative',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 4,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'min' => 1,
				'max' => 12,
				'step' => 1,
				'frontend_available' => true,
				'condition' => [
					'creative_background_enable' => 'yes',
					'grid_columns_mode' => 'auto',
				],
			]
		);

		$this->add_responsive_control(
			'columns_px',
			[
				'label' => esc_html__( 'Largeur des colonnes (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 2000,
						'step' => 10,
					],
				],
				'default' => [
					'size' => 300,
					'unit' => 'px',
				],
				'desktop_default' => [
					'size' => 300,
					'unit' => 'px',
				],
				'tablet_default' => [
					'size' => 250,
					'unit' => 'px',
				],
				'mobile_default' => [
					'size' => 200,
					'unit' => 'px',
				],
				'condition' => [
					'creative_background_enable' => 'yes',
					'grid_columns_mode' => 'pixels',
				],
				'description' => esc_html__( 'Définit la largeur de chaque colonne en pixels. Les colonnes se répètent automatiquement.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'gap_horizontal',
			[
				'label' => esc_html__( 'Espacement horizontal', 'NOVA-addons' ),
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
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'gap_vertical',
			[
				'label' => esc_html__( 'Espacement vertical', 'NOVA-addons' ),
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
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'grid_justify_items',
			[
				'label' => esc_html__( 'Alignement horizontal', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-right',
					],
					'stretch' => [
						'title' => esc_html__( 'Étirer', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-stretch',
					],
				],
				'default' => 'stretch',
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-grid' => 'justify-items: {{VALUE}};',
				],
				'description' => esc_html__( 'Aligne horizontalement les éléments dans leurs cellules de grille.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'grid_align_items',
			[
				'label' => esc_html__( 'Alignement vertical', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Haut', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'end' => [
						'title' => esc_html__( 'Bas', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
					'stretch' => [
						'title' => esc_html__( 'Étirer', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-stretch',
					],
				],
				'default' => 'stretch',
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-grid' => 'align-items: {{VALUE}};',
				],
				'description' => esc_html__( 'Aligne verticalement les éléments dans leurs cellules de grille.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'grid_justify_content',
			[
				'label' => esc_html__( 'Justification de la grille', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'start' => [
						'title' => esc_html__( 'Début', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-center',
					],
					'end' => [
						'title' => esc_html__( 'Fin', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-right',
					],
					'space-between' => [
						'title' => esc_html__( 'Espace entre', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-stretch',
					],
					'space-around' => [
						'title' => esc_html__( 'Espace autour', 'NOVA-addons' ),
						'icon' => 'eicon-flex',
					],
					'space-evenly' => [
						'title' => esc_html__( 'Espace égal', 'NOVA-addons' ),
						'icon' => 'eicon-flex',
					],
				],
				'default' => 'start',
				'desktop_default' => 'start',
				'tablet_default' => 'start',
				'mobile_default' => 'start',
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-grid' => 'justify-content: {{VALUE}} !important;',
				],
				'description' => esc_html__( 'Aligne la grille elle-même horizontalement si elle est plus petite que le conteneur. Utilisez "Centre" pour centrer les éléments qui ne remplissent pas complètement la ligne (utile avec le mode pixels).', 'NOVA-addons' ),
			]
		);

		// Override default to 'center' when pixels mode is enabled
		$this->add_control(
			'grid_justify_content_pixels_center',
			[
				'label' => esc_html__( 'Centrer automatiquement en mode pixels', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'condition' => [
					'creative_background_enable' => 'yes',
					'grid_columns_mode' => 'pixels',
				],
				'description' => esc_html__( 'Si activé, centre automatiquement les éléments de la grille quand ils ne remplissent pas complètement la ligne. Utile pour centrer la dernière ligne d\'éléments.', 'NOVA-addons' ),
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
				'separator' => 'before',
			]
		);

		$this->add_control(
			'enable_image_popup',
			[
				'label' => esc_html__( 'Activer le popup d\'image au clic', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
				'description' => esc_html__( 'Si activé, un popup avec slider s\'affichera au clic sur une image, sans rediriger vers le lien.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'enable_pagination',
			[
				'label' => esc_html__( 'Activer la pagination', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label' => esc_html__( 'Type de pagination', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'load_more',
				'options' => [
					'load_more' => esc_html__( 'Bouton "Voir plus"', 'NOVA-addons' ),
					'numbered'  => esc_html__( 'Pages numérotées', 'NOVA-addons' ),
				],
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_per_page',
			[
				'label' => esc_html__( 'Éléments par page', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'condition' => [ 'enable_pagination' => 'yes' ],
			]
		);

		$this->add_control(
			'pagination_load_more_text',
			[
				'label' => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Voir plus', 'NOVA-addons' ),
				'condition' => [
					'enable_pagination' => 'yes',
					'pagination_type'   => 'load_more',
				],
			]
		);

		// Largeur personnalisée pour le masque créatif
		$this->add_responsive_control(
			'item_creative_width',
			[
				'label' => esc_html__( 'Largeur personnalisée (masque créatif)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
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
				],
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'La hauteur sera calculée automatiquement selon l\'aspect-ratio du SVG masque.', 'NOVA-addons' ),
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
				'selector' => '{{WRAPPER}} .nova-gallery-widget',
			]
		);

		$this->add_responsive_control(
			'widget_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-gallery-widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-gallery-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'widget_border',
				'selector' => '{{WRAPPER}} .nova-gallery-widget',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'widget_box_shadow',
				'selector' => '{{WRAPPER}} .nova-gallery-widget',
			]
		);

		$this->end_controls_section();

		// Section Style - Filtres
		$this->start_controls_section(
			'section_style_filters',
			[
				'label' => esc_html__( 'Filtres', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_filters' => 'yes',
				],
			]
		);

		$this->add_control(
			'filters_align',
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
					'{{WRAPPER}} .nova-gallery-filters' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .nova-gallery-filters .nova-gallery-filters-wrapper' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'filters_gap',
			[
				'label' => esc_html__( 'Espacement entre boutons', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-filter-item' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filters_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-filters' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'filter_tabs' );

		$this->start_controls_tab(
			'filter_tab_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'filter_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-filter-item' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'filter_background',
				'selector' => '{{WRAPPER}} .nova-gallery-filter-item',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'filter_typography',
				'selector' => '{{WRAPPER}} .nova-gallery-filter-item',
			]
		);

		$this->add_responsive_control(
			'filter_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-filter-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filter_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-filter-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'filter_border',
				'selector' => '{{WRAPPER}} .nova-gallery-filter-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'filter_tab_active',
			[
				'label' => esc_html__( 'Actif', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'filter_active_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-filter-item.active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'filter_active_background',
				'selector' => '{{WRAPPER}} .nova-gallery-filter-item.active',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'filter_active_border',
				'selector' => '{{WRAPPER}} .nova-gallery-filter-item.active',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Section Style - Item
		$this->start_controls_section(
			'section_style_item',
			[
				'label' => esc_html__( 'Item', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// Width and Height
		$this->add_responsive_control(
			'item_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
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
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh', 'custom' ],
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
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Display
		$this->add_control(
			'item_display',
			[
				'label' => esc_html__( 'Display', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'flex' => esc_html__( 'Flex', 'NOVA-addons' ),
					'grid' => esc_html__( 'Grid', 'NOVA-addons' ),
					'block' => esc_html__( 'Block', 'NOVA-addons' ),
					'inline-block' => esc_html__( 'Inline Block', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'display: {{VALUE}};',
				],
			]
		);

		// Flex Direction
		$this->add_control(
			'item_flex_direction',
			[
				'label' => esc_html__( 'Flex Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'row' => esc_html__( 'Row', 'NOVA-addons' ),
					'column' => esc_html__( 'Column', 'NOVA-addons' ),
					'row-reverse' => esc_html__( 'Row Reverse', 'NOVA-addons' ),
					'column-reverse' => esc_html__( 'Column Reverse', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'flex-direction: {{VALUE}};',
				],
				'condition' => [
					'item_display' => 'flex',
				],
			]
		);

		// Flex Wrap
		$this->add_control(
			'item_flex_wrap',
			[
				'label' => esc_html__( 'Flex Wrap', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'nowrap',
				'options' => [
					'nowrap' => esc_html__( 'No Wrap', 'NOVA-addons' ),
					'wrap' => esc_html__( 'Wrap', 'NOVA-addons' ),
					'wrap-reverse' => esc_html__( 'Wrap Reverse', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'flex-wrap: {{VALUE}};',
				],
				'condition' => [
					'item_display' => 'flex',
				],
			]
		);

		// Align Items
		$this->add_control(
			'item_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'stretch',
				'options' => [
					'flex-start' => esc_html__( 'Flex Start', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Flex End', 'NOVA-addons' ),
					'center' => esc_html__( 'Center', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Stretch', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Baseline', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'align-items: {{VALUE}};',
				],
				'condition' => [
					'item_display' => 'flex',
				],
			]
		);

		// Justify Content
		$this->add_control(
			'item_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => esc_html__( 'Flex Start', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Flex End', 'NOVA-addons' ),
					'center' => esc_html__( 'Center', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Space Between', 'NOVA-addons' ),
					'space-around' => esc_html__( 'Space Around', 'NOVA-addons' ),
					'space-evenly' => esc_html__( 'Space Evenly', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'justify-content: {{VALUE}};',
				],
				'condition' => [
					'item_display' => 'flex',
				],
			]
		);

		// Gap
		$this->add_responsive_control(
			'item_gap',
			[
				'label' => esc_html__( 'Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'item_display' => [ 'flex', 'grid' ],
				],
			]
		);

		// Grid Template Columns
		$this->add_control(
			'item_grid_template_columns',
			[
				'label' => esc_html__( 'Grid Template Columns', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'repeat(2, 1fr)',
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'grid-template-columns: {{VALUE}};',
				],
				'condition' => [
					'item_display' => 'grid',
				],
			]
		);

		// Grid Template Rows
		$this->add_control(
			'item_grid_template_rows',
			[
				'label' => esc_html__( 'Grid Template Rows', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => 'auto',
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'grid-template-rows: {{VALUE}};',
				],
				'condition' => [
					'item_display' => 'grid',
				],
			]
		);

		// Grid Column Gap
		$this->add_responsive_control(
			'item_grid_column_gap',
			[
				'label' => esc_html__( 'Column Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'item_display' => 'grid',
				],
			]
		);

		// Grid Row Gap
		$this->add_responsive_control(
			'item_grid_row_gap',
			[
				'label' => esc_html__( 'Row Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'row-gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'item_display' => 'grid',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background',
				'selector' => '{{WRAPPER}} .nova-gallery-item',
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-gallery-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'item_border',
				'selector' => '{{WRAPPER}} .nova-gallery-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .nova-gallery-item',
			]
		);

		$this->end_controls_section();

		// Section Style - Image
		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Image', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
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
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item-image img' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-gallery-item.has-background-image' => 'background-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh', 'custom' ],
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
				],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item-image img' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-gallery-item.has-background-image' => 'min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .nova-gallery-item.has-background-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'selector' => '{{WRAPPER}} .nova-gallery-item-image img, {{WRAPPER}} .nova-gallery-item.has-background-image',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .nova-gallery-item-image img, {{WRAPPER}} .nova-gallery-item.has-background-image',
			]
		);

		$this->end_controls_section();

		// Section Style - Titre
		$this->start_controls_section(
			'section_style_title',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .nova-gallery-item-title',
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-gallery-item-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$options = [];

		foreach ( $post_types as $post_type ) {
			$options[ $post_type->name ] = $post_type->label;
		}

		return $options;
	}

	/**
	 * Récupère la liste des taxonomies disponibles
	 *
	 * @return array
	 */
	private function get_taxonomies() {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options = [
			'' => esc_html__( '-- Sélectionner --', 'NOVA-addons' ),
		];

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
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
			'back-superbig-1.svg',
			'back-superbig-2.svg',
		];
		
		$svg_files = [];
		foreach ( $svg_list as $svg_file ) {
			$file_path = $plugin_dir . 'assets/svg/' . $svg_file;
			if ( file_exists( $file_path ) ) {
				$svg_url = $svg_url_base . $svg_file;
				$svg_url = str_replace( '\\', '/', $svg_url );
				$svg_files[] = $svg_url;
			}
		}
		
		return $svg_files;
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
					$image_url = get_the_post_thumbnail_url( $post_id, 'full' );
				} elseif ( $settings['image_source'] === 'acf' && ! empty( $settings['acf_image_field'] ) ) {
					$acf_image = get_field( $settings['acf_image_field'], $post_id );
					if ( $acf_image ) {
						if ( is_array( $acf_image ) && isset( $acf_image['url'] ) ) {
							$image_url = $acf_image['url'];
						} elseif ( is_numeric( $acf_image ) ) {
							$image_url = wp_get_attachment_image_url( $acf_image, 'full' );
						} elseif ( is_string( $acf_image ) ) {
							$image_url = $acf_image;
						}
					}
				}

				// Récupérer les termes de la taxonomie pour les filtres multiples
				$terms = [];
				if ( ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' && ! empty( $settings['filters_list'] ) ) {
					foreach ( $settings['filters_list'] as $filter_item ) {
						if ( ! empty( $filter_item['taxonomy'] ) ) {
							$post_terms = wp_get_post_terms( $post_id, $filter_item['taxonomy'] );
							if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
								foreach ( $post_terms as $term ) {
									$terms[] = $term->slug;
								}
							}
						}
					}
				}

				$posts[] = [
					'id' => $post_id,
					'title' => get_the_title(),
					'image' => $image_url,
					'link' => get_permalink(),
					'terms' => $terms,
				];
			}
			wp_reset_postdata();
		}

		return $posts;
	}

	/**
	 * Récupère les termes de la taxonomie pour les filtres
	 *
	 * @param string $taxonomy Nom de la taxonomie.
	 * @return array
	 */
	private function get_filter_terms( $taxonomy ) {
		if ( empty( $taxonomy ) ) {
			return [];
		}

		$terms = get_terms( [
			'taxonomy' => $taxonomy,
			'hide_empty' => true,
		] );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		$terms_array = [];
		foreach ( $terms as $term ) {
			$terms_array[] = [
				'term_id' => $term->term_id,
				'slug' => $term->slug,
				'name' => $term->name,
			];
		}

		return $terms_array;
	}

	/**
	 * Affiche le widget.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Enqueue Owl Carousel if any filter group has slider enabled
		$needs_owl = false;
		if ( ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' && ! empty( $settings['filters_list'] ) ) {
			foreach ( $settings['filters_list'] as $filter_item ) {
				if ( ! empty( $filter_item['enable_slider'] ) && $filter_item['enable_slider'] === 'yes' ) {
					$needs_owl = true;
					break;
				}
			}
		}
		if ( $needs_owl ) {
			wp_enqueue_script( 'owl-carousel' );
			wp_enqueue_style( 'owl-carousel' );
			wp_enqueue_style( 'owl-carousel-theme' );
		}

		// Préparer les items
		$items = [];
		$filter_terms = [];
		
		if ( $settings['data_source'] === 'post_type' ) {
			$posts = $this->get_posts( $settings );
			foreach ( $posts as $post ) {
				$items[] = [
					'id' => isset($post['id']) ? $post['id'] : 0,
					'post' => $post, // pass the post info
					'item_image' => [
						'url' => $post['image'],
					],
					'item_title' => $post['title'],
					'item_link' => [
						'url' => $post['link'],
						'is_external' => false,
						'nofollow' => false,
					],
					'item_category' => implode( ' ', $post['terms'] ),
				];
			}

			// Option conservée pour rétro-compatibilité avec le code de l'ancien widget
			if ( ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' && ! empty( $settings['filter_taxonomy'] ) ) {
				$filter_terms = $this->get_filter_terms( $settings['filter_taxonomy'] );
			}
		} else {
			$items = $settings['items_list'];
			
			// Pour les items manuels, extraire les catégories uniques
			if ( ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' ) {
				$categories = [];
				foreach ( $items as $item ) {
					if ( ! empty( $item['item_category'] ) ) {
						// Support both comma and space separated categories (same as in render)
						$cat_parts = preg_split( '/[,\s]+/', $item['item_category'], -1, PREG_SPLIT_NO_EMPTY );
						$cat_parts = array_map( 'trim', $cat_parts );
						foreach ( $cat_parts as $cat ) {
							if ( ! empty( $cat ) && ! in_array( $cat, $categories ) ) {
								$categories[] = $cat;
							}
						}
					}
				}
				
				foreach ( $categories as $cat ) {
					$filter_terms[] = [
						'slug' => sanitize_title( $cat ),
						'name' => $cat,
					];
				}
			}
		}

		// Préparer les groupes de filtres
		$filter_groups = [];
		if ( ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' ) {
			if ( $settings['data_source'] === 'post_type' && ! empty( $settings['filters_list'] ) ) {
				foreach ( $settings['filters_list'] as $filter_item ) {
					if ( ! empty( $filter_item['taxonomy'] ) ) {
						$terms_for_tax = $this->get_filter_terms( $filter_item['taxonomy'] );
						if ( ! empty( $terms_for_tax ) ) {
							$filter_groups[] = [
								'taxonomy' => $filter_item['taxonomy'],
								'label' => isset($filter_item['filter_label']) ? $filter_item['filter_label'] : '',
								'use_image' => isset($filter_item['use_image']) && $filter_item['use_image'] === 'yes',
								'acf_image_key' => isset($filter_item['acf_image_key']) ? $filter_item['acf_image_key'] : '',
						'enable_slider' => isset($filter_item['enable_slider']) && $filter_item['enable_slider'] === 'yes',
						'slider_breakpoint' => isset($filter_item['slider_breakpoint']) ? $filter_item['slider_breakpoint'] : 'all',
						'show_slider_arrows' => isset($filter_item['show_slider_arrows']) && $filter_item['show_slider_arrows'] === 'yes',
						'arrow_prev_image'   => isset($filter_item['arrow_prev_image']['url']) ? $filter_item['arrow_prev_image']['url'] : '',
						'arrow_next_image'   => isset($filter_item['arrow_next_image']['url']) ? $filter_item['arrow_next_image']['url'] : '',
						'arrow_size'         => isset($filter_item['arrow_size']) ? absint($filter_item['arrow_size']) : 32,
						'arrow_icon_size'    => isset($filter_item['arrow_icon_size']) ? absint($filter_item['arrow_icon_size']) : 16,
						'arrow_color'        => isset($filter_item['arrow_color']) ? $filter_item['arrow_color'] : '',
						'arrow_bg'           => isset($filter_item['arrow_bg']) ? $filter_item['arrow_bg'] : '',
						'arrow_border_color' => isset($filter_item['arrow_border_color']) ? $filter_item['arrow_border_color'] : '',
						'arrow_border_width' => isset($filter_item['arrow_border_width']) ? absint($filter_item['arrow_border_width']) : 1,
						'arrow_border_radius'=> isset($filter_item['arrow_border_radius']) ? absint($filter_item['arrow_border_radius']) : 50,
						'slider_align' => isset($filter_item['slider_align']) ? $filter_item['slider_align'] : 'left',
						'slider_item_min_width' => isset($filter_item['slider_item_min_width']) ? absint($filter_item['slider_item_min_width']) : 0,
						'slider_item_min_width_tablet' => isset($filter_item['slider_item_min_width_tablet']) ? absint($filter_item['slider_item_min_width_tablet']) : 0,
						'slider_item_min_width_mobile' => isset($filter_item['slider_item_min_width_mobile']) ? absint($filter_item['slider_item_min_width_mobile']) : 0,
						'slider_margin' => isset($filter_item['slider_margin']) ? absint($filter_item['slider_margin']) : 10,
						'slider_margin_tablet' => isset($filter_item['slider_margin_tablet']) ? absint($filter_item['slider_margin_tablet']) : 0,
						'slider_margin_mobile' => isset($filter_item['slider_margin_mobile']) ? absint($filter_item['slider_margin_mobile']) : 0,
						'slider_items' => isset($filter_item['slider_items']) ? absint($filter_item['slider_items']) : 0,
						'slider_items_tablet' => isset($filter_item['slider_items_tablet']) ? absint($filter_item['slider_items_tablet']) : 0,
						'slider_items_mobile' => isset($filter_item['slider_items_mobile']) ? absint($filter_item['slider_items_mobile']) : 0,
						'slider_loop' => isset($filter_item['slider_loop']) && $filter_item['slider_loop'] === 'yes',
						'slider_loop_tablet' => isset($filter_item['slider_loop_tablet']) && $filter_item['slider_loop_tablet'] !== 'inherit'
							? $filter_item['slider_loop_tablet'] === 'yes'
							: ( isset($filter_item['slider_loop']) && $filter_item['slider_loop'] === 'yes' ),
						'slider_loop_mobile' => isset($filter_item['slider_loop_mobile']) && $filter_item['slider_loop_mobile'] !== 'inherit'
							? $filter_item['slider_loop_mobile'] === 'yes'
							: ( isset($filter_item['slider_loop_tablet']) && $filter_item['slider_loop_tablet'] !== 'inherit'
								? $filter_item['slider_loop_tablet'] === 'yes'
								: ( isset($filter_item['slider_loop']) && $filter_item['slider_loop'] === 'yes' ) ),
						'slider_autowidth' => ! isset($filter_item['slider_autowidth']) || $filter_item['slider_autowidth'] === 'yes',
						'slider_free_drag' => isset($filter_item['slider_free_drag']) && $filter_item['slider_free_drag'] === 'yes',
						'slider_disable_drag' => isset($filter_item['slider_disable_drag']) ? $filter_item['slider_disable_drag'] : 'none',
						'pin_filter_all' => isset($filter_item['pin_filter_all']) && $filter_item['pin_filter_all'] === 'yes',
						'hide_filter_all' => isset($filter_item['hide_filter_all']) && $filter_item['hide_filter_all'] === 'yes',
						'hide_term_title' => isset($filter_item['hide_term_title']) && $filter_item['hide_term_title'] === 'yes',
						'terms' => $terms_for_tax
							];
						}
					}
				}
			} else if ( $settings['data_source'] === 'manual' && ! empty( $filter_terms ) ) {
				// Fallback pour le mode manuel ou si filters_list est vide
				$filter_groups[] = [
					'taxonomy' => 'category',
					'label' => '',
					'use_image' => false,
					'acf_image_key' => '',
					'enable_slider' => false,
					'slider_breakpoint' => 'all',
					'show_slider_arrows' => false,
					'arrow_prev_image' => '', 'arrow_next_image' => '',
					'arrow_size' => 32, 'arrow_icon_size' => 16,
					'arrow_color' => '', 'arrow_bg' => '',
					'arrow_border_color' => '', 'arrow_border_width' => 1, 'arrow_border_radius' => 50,
					'slider_align' => 'left',
					'hide_filter_all' => false,
					'hide_term_title' => false,
					'slider_item_min_width' => 0,
					'slider_item_min_width_tablet' => 0,
					'slider_item_min_width_mobile' => 0,
					'slider_margin' => 10,
					'slider_margin_tablet' => 0,
					'slider_margin_mobile' => 0,
					'slider_items' => 0,
					'slider_items_tablet' => 0,
					'slider_items_mobile' => 0,
					'slider_loop' => false,
					'slider_autowidth' => true,
					'slider_free_drag' => false,
					'slider_disable_drag' => 'none',
					'pin_filter_all' => false,
					'terms' => $filter_terms
				];
			}
		}

		// Options d'affichage
		$image_as_background = ! empty( $settings['image_as_background'] ) && $settings['image_as_background'] === 'yes';
		$creative_background_enable = ! empty( $settings['creative_background_enable'] ) && $settings['creative_background_enable'] === 'yes';
		$creative_background_color = ! empty( $settings['creative_background_color'] ) ? $settings['creative_background_color'] : '#ffffff';
		$image_overlay_enable = ! empty( $settings['image_overlay_enable'] ) && $settings['image_overlay_enable'] === 'yes';
		$image_overlay_color = ! empty( $settings['image_overlay_color'] ) ? $settings['image_overlay_color'] : '#00000033';
		$show_title = ! empty( $settings['show_title'] ) && $settings['show_title'] === 'yes';
		$enable_image_popup = ! empty( $settings['enable_image_popup'] ) && $settings['enable_image_popup'] === 'yes';
		$enable_pagination  = ! empty( $settings['enable_pagination'] ) && $settings['enable_pagination'] === 'yes';
		$pagination_type    = ! empty( $settings['pagination_type'] ) ? $settings['pagination_type'] : 'load_more';
		$pagination_per_page = ! empty( $settings['pagination_per_page'] ) ? absint( $settings['pagination_per_page'] ) : 6;
		$load_more_text     = ! empty( $settings['pagination_load_more_text'] ) ? $settings['pagination_load_more_text'] : __( 'Voir plus', 'NOVA-addons' );

		// Récupérer les SVG pour le background créatif
		$svg_backgrounds = [];
		if ( $creative_background_enable ) {
			$svg_backgrounds = $this->get_creative_background_svgs();
		}

		// Configuration de la grille
		// Récupérer les valeurs responsive (Elementor stocke les valeurs responsive dans un format spécial)
		$creative_background_enable = ! empty( $settings['creative_background_enable'] ) && $settings['creative_background_enable'] === 'yes';
		$grid_columns_mode = ! empty( $settings['grid_columns_mode'] ) ? $settings['grid_columns_mode'] : 'auto';
		$use_pixels_mode = $creative_background_enable && $grid_columns_mode === 'pixels';
		
		$get_responsive_number = function( $base_key, $desktop_default = 4, $tablet_default = 3, $mobile_default = 2 ) use ( $settings ) {
			$raw_desktop = isset( $settings[ $base_key ] ) ? $settings[ $base_key ] : null;
			$raw_tablet = isset( $settings[ $base_key . '_tablet' ] ) ? $settings[ $base_key . '_tablet' ] : null;
			$raw_mobile = isset( $settings[ $base_key . '_mobile' ] ) ? $settings[ $base_key . '_mobile' ] : null;

			$desktop = is_array( $raw_desktop ) && isset( $raw_desktop['size'] ) ? (int) $raw_desktop['size'] : (int) $raw_desktop;
			$tablet  = is_array( $raw_tablet ) && isset( $raw_tablet['size'] ) ? (int) $raw_tablet['size'] : (int) $raw_tablet;
			$mobile  = is_array( $raw_mobile ) && isset( $raw_mobile['size'] ) ? (int) $raw_mobile['size'] : (int) $raw_mobile;

			if ( $desktop <= 0 ) {
				$desktop = (int) $desktop_default;
			}
			if ( $tablet <= 0 ) {
				$tablet = (int) $tablet_default;
			}
			if ( $mobile <= 0 ) {
				$mobile = (int) $mobile_default;
			}

			return [ $desktop, $tablet, $mobile ];
		};

		if ( $use_pixels_mode ) {
			// Mode pixels: utiliser les largeurs en pixels
			$grid_columns_px = isset( $settings['columns_px'] ) && is_array( $settings['columns_px'] ) && isset( $settings['columns_px']['size'] ) ? intval( $settings['columns_px']['size'] ) : 300;
			$grid_columns_px_tablet = isset( $settings['columns_px_tablet'] ) && is_array( $settings['columns_px_tablet'] ) && isset( $settings['columns_px_tablet']['size'] ) && $settings['columns_px_tablet']['size'] !== '' ? intval( $settings['columns_px_tablet']['size'] ) : $grid_columns_px;
			$grid_columns_px_mobile = isset( $settings['columns_px_mobile'] ) && is_array( $settings['columns_px_mobile'] ) && isset( $settings['columns_px_mobile']['size'] ) && $settings['columns_px_mobile']['size'] !== '' ? intval( $settings['columns_px_mobile']['size'] ) : 200;
			
			$grid_config = [
				'mode' => 'pixels',
				'column_width' => $grid_columns_px,
				'column_width_tablet' => $grid_columns_px_tablet,
				'column_width_mobile' => $grid_columns_px_mobile,
			];
			
			// Auto-center for pixels mode
			$auto_center = ! empty( $settings['grid_justify_content_pixels_center'] ) && $settings['grid_justify_content_pixels_center'] === 'yes';
			$grid_config['auto_center'] = $auto_center;
			
			// Pour compatibilité avec l'ancien code JavaScript
			$columns = $grid_columns_px;
			$columns_tablet = $grid_columns_px_tablet;
			$columns_mobile = $grid_columns_px_mobile;
		} else {
			// Mode auto: utiliser le nombre de colonnes
			// Si creative background est activé, utiliser columns_creative, sinon columns
			if ( $creative_background_enable ) {
				list( $columns, $columns_tablet, $columns_mobile ) = $get_responsive_number( 'columns_creative', 4, 3, 2 );
			} else {
				list( $columns, $columns_tablet, $columns_mobile ) = $get_responsive_number( 'columns', 4, 3, 2 );
			}
			
			$grid_config = [
				'mode' => 'auto',
				'columns' => $columns,
				'columns_tablet' => $columns_tablet,
				'columns_mobile' => $columns_mobile,
			];
		}

		?>
		<?php
		$hide_filter_all_mobile = ! empty( $settings['hide_filter_all_mobile'] ) && $settings['hide_filter_all_mobile'] === 'yes';
		$filters_sticky        = ! empty( $settings['filters_sticky'] ) && $settings['filters_sticky'] === 'yes';
		$filters_sticky_tablet = isset( $settings['filters_sticky_tablet'] ) ? $settings['filters_sticky_tablet'] === 'yes' : $filters_sticky;
		$filters_sticky_mobile = isset( $settings['filters_sticky_mobile'] ) ? $settings['filters_sticky_mobile'] === 'yes' : $filters_sticky_tablet;
		$filters_sticky_offset        = isset( $settings['filters_sticky_offset']['size'] ) ? (int) $settings['filters_sticky_offset']['size'] : 0;
		$filters_sticky_offset_tablet = isset( $settings['filters_sticky_offset_tablet']['size'] ) ? (int) $settings['filters_sticky_offset_tablet']['size'] : $filters_sticky_offset;
		$filters_sticky_offset_mobile = isset( $settings['filters_sticky_offset_mobile']['size'] ) ? (int) $settings['filters_sticky_offset_mobile']['size'] : $filters_sticky_offset_tablet;
		$has_sticky = $filters_sticky || $filters_sticky_tablet || $filters_sticky_mobile;
		?>
		<div class="nova-gallery-widget<?php echo $creative_background_enable ? ' creative-background-enabled' : ''; ?>" 
			data-columns="<?php echo esc_attr( $columns ); ?>"
			data-columns-tablet="<?php echo esc_attr( $columns_tablet ); ?>"
			data-columns-mobile="<?php echo esc_attr( $columns_mobile ); ?>"
			data-grid-config="<?php echo esc_attr( wp_json_encode( $grid_config ) ); ?>"
			data-enable-filters="<?php echo ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' ? 'yes' : 'no'; ?>"
			data-hide-filter-all-mobile="<?php echo $hide_filter_all_mobile ? 'yes' : 'no'; ?>"
			data-enable-popup="<?php echo $enable_image_popup ? 'yes' : 'no'; ?>"
			data-pagination="<?php echo $enable_pagination ? esc_attr( $pagination_type ) : 'none'; ?>"
			data-per-page="<?php echo esc_attr( $pagination_per_page ); ?>">
			
			<?php 
			if ( ! empty( $filter_groups ) ) : ?>
				<div class="nova-gallery-multi-filters-container<?php echo $has_sticky ? ' nova-filters-sticky' : ''; ?>"<?php if ( $has_sticky ) : ?> data-sticky="<?php echo $filters_sticky ? 'yes' : 'no'; ?>" data-sticky-tablet="<?php echo $filters_sticky_tablet ? 'yes' : 'no'; ?>" data-sticky-mobile="<?php echo $filters_sticky_mobile ? 'yes' : 'no'; ?>" data-sticky-offset="<?php echo esc_attr( $filters_sticky_offset ); ?>" data-sticky-offset-tablet="<?php echo esc_attr( $filters_sticky_offset_tablet ); ?>" data-sticky-offset-mobile="<?php echo esc_attr( $filters_sticky_offset_mobile ); ?>"<?php endif; ?>>
					<?php foreach ( $filter_groups as $group_index => $group ) : 
						$is_slider = $group['enable_slider'];
						$pin_all   = $is_slider && ! empty( $group['pin_filter_all'] ) && empty( $group['hide_filter_all'] );
						$align     = $is_slider && ! empty( $group['slider_align'] ) ? $group['slider_align'] : 'left';
					?>
						<div class="nova-gallery-filter-group<?php echo $pin_all ? ' has-pinned-all' : ''; ?>" data-filter-group="<?php echo esc_attr( $group['taxonomy'] ); ?>"<?php if ( $is_slider ) : ?> data-owl-align="<?php echo esc_attr( $align ); ?>"<?php endif; ?>>
							<?php if ( ! empty( $group['label'] ) ) : ?>
								<div class="nova-gallery-filter-label"><?php echo esc_html( $group['label'] ); ?></div>
							<?php endif; ?>

							<?php if ( $pin_all ) : ?>
							<div class="nova-gallery-filter-all-pinned">
								<button class="nova-gallery-filter-item active<?php echo $hide_filter_all_mobile ? ' hide-on-mobile' : ''; ?>" data-filter="*">
									<span class="nova-gallery-filter-name"><?php echo esc_html( ! empty( $settings['filter_all_text'] ) ? $settings['filter_all_text'] : esc_html__( 'Tout', 'NOVA-addons' ) ); ?></span>
								</button>
							</div>
							<?php endif; ?>
							
						<div class="nova-gallery-filters <?php echo $is_slider ? 'owl-carousel nova-gallery-filters-slider' : ''; ?>"<?php if ( $is_slider ) : ?>
							data-owl-margin="<?php echo esc_attr( isset( $group['slider_margin'] ) ? $group['slider_margin'] : 10 ); ?>"
							data-owl-margin-tablet="<?php echo esc_attr( isset( $group['slider_margin_tablet'] ) ? $group['slider_margin_tablet'] : 0 ); ?>"
							data-owl-margin-mobile="<?php echo esc_attr( isset( $group['slider_margin_mobile'] ) ? $group['slider_margin_mobile'] : 0 ); ?>"
							data-owl-items="<?php echo esc_attr( isset( $group['slider_items'] ) ? $group['slider_items'] : 0 ); ?>"
							data-owl-items-tablet="<?php echo esc_attr( isset( $group['slider_items_tablet'] ) ? $group['slider_items_tablet'] : 0 ); ?>"
							data-owl-items-mobile="<?php echo esc_attr( isset( $group['slider_items_mobile'] ) ? $group['slider_items_mobile'] : 0 ); ?>"
							data-owl-loop="<?php echo ! empty( $group['slider_loop'] ) ? 'yes' : 'no'; ?>"
						data-owl-loop-tablet="<?php echo ! empty( $group['slider_loop_tablet'] ) ? 'yes' : 'no'; ?>"
						data-owl-loop-mobile="<?php echo ! empty( $group['slider_loop_mobile'] ) ? 'yes' : 'no'; ?>"
							data-owl-autowidth="<?php echo ! empty( $group['slider_autowidth'] ) ? 'yes' : 'no'; ?>"
							data-owl-freedrag="<?php echo ! empty( $group['slider_free_drag'] ) ? 'yes' : 'no'; ?>"
							data-owl-disabledrag="<?php echo esc_attr( isset( $group['slider_disable_drag'] ) ? $group['slider_disable_drag'] : 'none' ); ?>"
							data-owl-breakpoint="<?php echo esc_attr( isset( $group['slider_breakpoint'] ) ? $group['slider_breakpoint'] : 'all' ); ?>"
							data-filter-item-min-width="<?php echo esc_attr( isset( $group['slider_item_min_width'] ) ? $group['slider_item_min_width'] : 0 ); ?>"
							data-filter-item-min-width-tablet="<?php echo esc_attr( isset( $group['slider_item_min_width_tablet'] ) ? $group['slider_item_min_width_tablet'] : 0 ); ?>"
							data-filter-item-min-width-mobile="<?php echo esc_attr( isset( $group['slider_item_min_width_mobile'] ) ? $group['slider_item_min_width_mobile'] : 0 ); ?>"
					<?php endif; ?>>
						<?php if ( ! $is_slider ) : ?>
						<div class="nova-gallery-filters-wrapper">
						<?php endif; ?>

						<?php if ( ! $group['hide_filter_all'] && ! $pin_all ) : ?>
						<div class="nova-gallery-filter-wrap">
							<button class="nova-gallery-filter-item active<?php echo $hide_filter_all_mobile ? ' hide-on-mobile' : ''; ?>" data-filter="*">
								<span class="nova-gallery-filter-name"><?php echo esc_html( ! empty( $settings['filter_all_text'] ) ? $settings['filter_all_text'] : esc_html__( 'Tout', 'NOVA-addons' ) ); ?></span>
							</button>
						</div>
						<?php endif; ?>

						<?php foreach ( $group['terms'] as $index => $term ) :
							$active_class = ( $group['hide_filter_all'] && $index === 0 ) ? ' active' : '';
							$has_image = false;
							$term_image_url = '';
							if ( $group['use_image'] && ! empty( $group['acf_image_key'] ) && isset( $term['term_id'] ) ) {
								if ( function_exists('get_field') ) {
									$acf_img = get_field( $group['acf_image_key'], $group['taxonomy'] . '_' . $term['term_id'] );
									if ( $acf_img ) {
										$has_image = true;
										if ( is_array( $acf_img ) && isset( $acf_img['url'] ) ) {
											$term_image_url = $acf_img['url'];
										} elseif ( is_numeric( $acf_img ) ) {
											$term_image_url = wp_get_attachment_image_url( $acf_img, 'thumbnail' );
										} elseif ( is_string( $acf_img ) ) {
											$term_image_url = $acf_img;
										}
									}
								} else {
									$image_id = get_term_meta( $term['term_id'], $group['acf_image_key'], true );
									if ( $image_id ) {
										if ( is_numeric( $image_id ) ) {
											$term_image_url = wp_get_attachment_image_url( $image_id, 'full' );
											if ( $term_image_url ) { $has_image = true; }
										} elseif ( is_string( $image_id ) && filter_var( $image_id, FILTER_VALIDATE_URL ) ) {
											$term_image_url = $image_id;
											$has_image = true;
										}
									}
								}
							}
						?>
						<div class="nova-gallery-filter-wrap">
							<button class="nova-gallery-filter-item <?php echo $has_image ? 'has-image' : ''; ?><?php echo $active_class; ?>" data-filter=".<?php echo esc_attr( $term['slug'] ); ?>">
								<?php if ( $has_image && ! empty( $term_image_url ) ) : ?>
								<img src="<?php echo esc_url( $term_image_url ); ?>" alt="<?php echo esc_attr( $term['name'] ); ?>" class="nova-gallery-filter-image">
								<?php endif; ?>
								<?php if ( ! $group['hide_term_title'] ) : ?>
								<span class="nova-gallery-filter-name"><?php echo esc_html( $term['name'] ); ?></span>
								<?php endif; ?>
							</button>
						</div>
						<?php endforeach; ?>

						<?php if ( ! $is_slider ) : ?></div><?php endif; ?>

					</div><!-- /.nova-gallery-filters -->

					<?php if ( $is_slider && ! empty( $group['show_slider_arrows'] ) ) : 
						$arrow_style = '';
						if ( ! empty( $group['arrow_size'] ) )          $arrow_style .= 'width:' . $group['arrow_size'] . 'px;height:' . $group['arrow_size'] . 'px;';
						if ( ! empty( $group['arrow_bg'] ) )            $arrow_style .= 'background:' . esc_attr( $group['arrow_bg'] ) . ';';
						if ( ! empty( $group['arrow_border_color'] ) )  $arrow_style .= 'border-color:' . esc_attr( $group['arrow_border_color'] ) . ';';
						if ( isset( $group['arrow_border_width'] ) )    $arrow_style .= 'border-width:' . $group['arrow_border_width'] . 'px;border-style:solid;';
						if ( isset( $group['arrow_border_radius'] ) )   $arrow_style .= 'border-radius:' . $group['arrow_border_radius'] . 'px;';
						if ( ! empty( $group['arrow_color'] ) )         $arrow_style .= 'color:' . esc_attr( $group['arrow_color'] ) . ';';

						$icon_style = ! empty( $group['arrow_icon_size'] ) ? 'width:' . $group['arrow_icon_size'] . 'px;height:' . $group['arrow_icon_size'] . 'px;font-size:' . $group['arrow_icon_size'] . 'px;' : '';

						$prev_content = ! empty( $group['arrow_prev_image'] )
							? '<img src="' . esc_url( $group['arrow_prev_image'] ) . '" alt="" style="' . esc_attr( $icon_style ) . 'object-fit:contain;">'
							: '<span style="' . esc_attr( $icon_style ) . 'line-height:1;">&#8249;</span>';

						$next_content = ! empty( $group['arrow_next_image'] )
							? '<img src="' . esc_url( $group['arrow_next_image'] ) . '" alt="" style="' . esc_attr( $icon_style ) . 'object-fit:contain;">'
							: '<span style="' . esc_attr( $icon_style ) . 'line-height:1;">&#8250;</span>';

						$style_attr = $arrow_style ? ' style="' . esc_attr( $arrow_style ) . '"' : '';
					?>
					<button class="nova-gallery-owl-prev" aria-label="Précédent"<?php echo $style_attr; ?>><?php echo $prev_content; ?></button>
					<button class="nova-gallery-owl-next" aria-label="Suivant"<?php echo $style_attr; ?>><?php echo $next_content; ?></button>
					<?php endif; ?>
</div><!-- /.nova-gallery-filter-group -->
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="nova-gallery-grid">
				<?php foreach ( $items as $index => $item ) : ?>
					<?php
					$item_image = isset( $item['item_image'] ) ? $item['item_image'] : [];
					$item_title = isset( $item['item_title'] ) ? $item['item_title'] : '';
					$item_link = isset( $item['item_link'] ) ? $item['item_link'] : [];
					$item_category = isset( $item['item_category'] ) ? $item['item_category'] : '';
					
					$image_url = is_array( $item_image ) && isset( $item_image['url'] ) ? $item_image['url'] : '';
					$link_url = is_array( $item_link ) && isset( $item_link['url'] ) ? $item_link['url'] : '';
					
					// Préparer les classes de filtre
					$filter_classes = '';
					if ( ! empty( $item_category ) ) {
						// Support both comma and space separated categories
						$categories = preg_split( '/[,\s]+/', $item_category, -1, PREG_SPLIT_NO_EMPTY );
						$categories = array_map( 'trim', $categories );
						foreach ( $categories as $cat ) {
							if ( ! empty( $cat ) ) {
								$filter_classes .= ' ' . sanitize_title( $cat );
							}
						}
					}

					// Sélectionner un SVG aléatoire pour le background créatif
					$selected_svg = '';
					$svg_transforms = [];
					$svg_width = '';
					$svg_height = '';
					
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

						// Extraire les dimensions du SVG
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
					$item_transform_style = '';

					if ( ! empty( $svg_transforms ) ) {
						$item_transform_style = 'transform: ' . implode( ' ', $svg_transforms ) . ';';
					}

					// Si le masque créatif est activé, l'appliquer
					if ( $creative_background_enable && ! empty( $selected_svg ) ) {
						$item_style .= '-webkit-mask-image: url(' . esc_url( $selected_svg ) . '); ';
						$item_style .= 'mask-image: url(' . esc_url( $selected_svg ) . '); ';
						$item_style .= '-webkit-mask-size: contain; mask-size: contain; ';
						$item_style .= '-webkit-mask-position: center; mask-position: center; ';
						$item_style .= '-webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; ';
						$item_style .= $item_transform_style;

						// Récupérer la largeur personnalisée
						$custom_width = '';
						if ( ! empty( $settings['item_creative_width'] ) ) {
							if ( is_array( $settings['item_creative_width'] ) && isset( $settings['item_creative_width']['size'] ) ) {
								$custom_width = $settings['item_creative_width']['size'] . ( isset( $settings['item_creative_width']['unit'] ) ? $settings['item_creative_width']['unit'] : 'px' );
							} else {
								$custom_width = $settings['item_creative_width'];
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
							// Sinon, utiliser les dimensions originales du SVG si disponibles
							if ( ! empty( $svg_width ) ) {
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
								$svg_height_clean = preg_replace( '/[^0-9.]/', '', $svg_height );
								if ( ! empty( $svg_height_clean ) && is_numeric( $svg_height_clean ) ) {
									$item_style .= 'height: ' . esc_attr( $svg_height_clean ) . 'px !important; ';
									$item_style .= 'min-height: ' . esc_attr( $svg_height_clean ) . 'px !important; ';
								}
							}
						}
					}

					// Ajouter l'image en background si activée
					if ( $image_as_background && ! empty( $image_url ) ) {
						$item_style .= 'background-image: url(' . esc_url( $image_url ) . '); ';
						$item_style .= 'background-size: cover; ';
						$item_style .= 'background-position: center; ';
						$item_style .= 'background-repeat: no-repeat; ';
					} elseif ( $creative_background_enable && ! empty( $selected_svg ) ) {
						$item_style .= 'background-color: ' . esc_attr( $creative_background_color ) . '; ';
					}
					?>
					<div class="nova-gallery-item<?php echo $image_as_background && ! empty( $image_url ) && empty($settings['card_template_path']) ? ' has-background-image' : ''; ?><?php echo $creative_background_enable ? ' has-creative-background' : ''; ?><?php echo $image_overlay_enable && ! empty( $image_url ) && empty($settings['card_template_path']) ? ' has-image-overlay' : ''; ?><?php echo esc_attr( $filter_classes ); ?>"<?php echo ! empty( $item_style ) && empty($settings['card_template_path']) ? ' style="' . $item_style . '"' : ''; ?> data-item-index="<?php echo esc_attr( $index ); ?>"<?php if ( $enable_image_popup && ! empty( $image_url ) && empty($settings['card_template_path']) ) : ?> data-popup-image="<?php echo esc_url( $image_url ); ?>" data-popup-title="<?php echo esc_attr( $item_title ); ?>"<?php endif; ?>>
						<?php 
						$custom_template = ! empty( $settings['card_template_path'] ) ? locate_template( $settings['card_template_path'] ) : false;
						
						if ( $custom_template ) {
							// Utiliser le template PHP personnalisé
							// En WP 5.5+, get_template_part supporte le 3ème argument $args
							$template_name = str_replace('.php', '', $settings['card_template_path']);
							$template_parts = explode('/', $template_name);
							$slug = array_shift($template_parts);
							$name = implode('/', $template_parts);
							
							if ( function_exists('get_template_part') && version_compare( get_bloginfo('version'), '5.5', '>=' ) ) {
								get_template_part( $template_name, null, [ 'item' => $item, 'settings' => $settings, 'post_id' => isset($item['id']) ? $item['id'] : 0 ] );
							} else {
								// Fallback old WP version
								set_query_var( 'nova_card_data', [ 'item' => $item, 'settings' => $settings ] );
								load_template( $custom_template, false );
							}
						} else {
							// Rendu natif par défaut
						?>
							<?php if ( ! empty( $image_url ) && ! $image_as_background ) : ?>
								<div class="nova-gallery-item-image">
									<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $item_title ); ?>">
									<?php if ( $image_overlay_enable ) : ?>
										<div class="nova-gallery-item-overlay" style="background-color: <?php echo esc_attr( $image_overlay_color ); ?>;"></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<?php if ( $image_overlay_enable && $image_as_background && ! empty( $image_url ) ) : ?>
								<div class="nova-gallery-item-overlay" style="background-color: <?php echo esc_attr( $image_overlay_color ); ?>;"></div>
							<?php endif; ?>
							<?php if ( $show_title && ! empty( $item_title ) ) : ?>
								<div class="nova-gallery-item-title"><?php echo esc_html( $item_title ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $link_url ) && ! $enable_image_popup ) : ?>
								<a href="<?php echo esc_url( $link_url ); ?>" class="nova-gallery-item-link" aria-label="<?php echo esc_attr( $item_title ); ?>"></a>
							<?php endif; ?>
						<?php } // Fin de la condition custom_template ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( $enable_pagination ) : ?>
			<div class="nova-gallery-pagination" data-pagination-type="<?php echo esc_attr( $pagination_type ); ?>">
				<?php if ( $pagination_type === 'load_more' ) : ?>
					<button class="nova-gallery-load-more"><?php echo esc_html( $load_more_text ); ?></button>
				<?php else : ?>
					<div class="nova-gallery-pages"></div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

		</div>
		<?php
		
		// Stocker le HTML du popup pour l'injecter avant </body> (une seule fois même si plusieurs widgets)
		if ( $enable_image_popup && empty( self::$popup_html ) ) {
			ob_start();
			?>
			<div class="nova-gallery-popup" style="display: none;">
				<div class="nova-gallery-popup-overlay"></div>
				<div class="nova-gallery-popup-content">
					<button class="nova-gallery-popup-close" aria-label="<?php echo esc_attr__( 'Fermer', 'NOVA-addons' ); ?>">&times;</button>
					<button class="nova-gallery-popup-prev" aria-label="<?php echo esc_attr__( 'Image précédente', 'NOVA-addons' ); ?>">&#8249;</button>
					<button class="nova-gallery-popup-next" aria-label="<?php echo esc_attr__( 'Image suivante', 'NOVA-addons' ); ?>">&#8250;</button>
					<div class="nova-gallery-popup-image-wrapper">
						<img class="nova-gallery-popup-image" src="" alt="">
						<div class="nova-gallery-popup-title"></div>
						<div class="nova-gallery-popup-counter"></div>
					</div>
				</div>
			</div>
			<?php
			self::$popup_html = ob_get_clean();
			
			// Ajouter le hook wp_footer si ce n'est pas déjà fait
			if ( ! has_action( 'wp_footer', [ __CLASS__, 'inject_popup_html' ] ) ) {
				add_action( 'wp_footer', [ __CLASS__, 'inject_popup_html' ], 999 );
			}
		}
	}

	/**
	 * Injecte le HTML du popup avant </body>
	 */
	public static function inject_popup_html() {
		if ( ! empty( self::$popup_html ) ) {
			echo self::$popup_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

