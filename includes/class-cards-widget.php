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
 * Widget NOVA Cards - Cards avec image de fond et texte
 */
class Cards_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-cards';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Cards', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-posts-grid';
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
		return [ 'nova-cards-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-cards-script' ];
	}

	/**
	 * Get custom CSS for button typography with !important
	 * This method is called by Elementor to inject styles in the editor
	 *
	 * @return string
	 */
	protected function get_custom_css() {
		$settings = $this->get_settings_for_display();
		$custom_css = '';
		$widget_id = $this->get_id();
		
		// Force !important on button typography styles
		$has_typography = false;
		$css_rules = [];
		
		if ( ! empty( $settings['button_typography_font_family'] ) ) {
			$css_rules[] = "font-family: " . esc_attr( $settings['button_typography_font_family'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_font_size']['size'] ) ) {
			$size = $settings['button_typography_font_size']['size'];
			$unit = $settings['button_typography_font_size']['unit'] ?? 'px';
			$css_rules[] = "font-size: " . esc_attr( $size . $unit ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_font_weight'] ) ) {
			$css_rules[] = "font-weight: " . esc_attr( $settings['button_typography_font_weight'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_text_transform'] ) ) {
			$css_rules[] = "text-transform: " . esc_attr( $settings['button_typography_text_transform'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_font_style'] ) ) {
			$css_rules[] = "font-style: " . esc_attr( $settings['button_typography_font_style'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_text_decoration'] ) ) {
			$css_rules[] = "text-decoration: " . esc_attr( $settings['button_typography_text_decoration'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_line_height']['size'] ) ) {
			$size = $settings['button_typography_line_height']['size'];
			$unit = $settings['button_typography_line_height']['unit'] ?? '';
			$css_rules[] = "line-height: " . esc_attr( $size . $unit ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_letter_spacing']['size'] ) ) {
			$size = $settings['button_typography_letter_spacing']['size'];
			$unit = $settings['button_typography_letter_spacing']['unit'] ?? 'px';
			$css_rules[] = "letter-spacing: " . esc_attr( $size . $unit ) . " !important;";
			$has_typography = true;
		}
		
		if ( $has_typography ) {
			$custom_css .= ".elementor-element-{$widget_id} .nova-card-button {";
			$custom_css .= implode( ' ', $css_rules );
			$custom_css .= "}";
		}
		
		return $custom_css;
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {

		// ─── Section Source de données ────────────────────────────────────
		$this->start_controls_section(
			'section_data_source',
			[
				'label' => esc_html__( 'Source de données', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'data_source',
			[
				'label'   => esc_html__( 'Source', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => [
					'manual'    => esc_html__( 'Manuel', 'NOVA-addons' ),
					'post_type' => esc_html__( 'Post Type', 'NOVA-addons' ),
				],
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'     => esc_html__( 'Post Type', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $this->get_post_types(),
				'default'   => 'post',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'     => esc_html__( 'Nombre d\'éléments', 'NOVA-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'min'       => 1,
				'max'       => 100,
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'order_by',
			[
				'label'     => esc_html__( 'Trier par', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'date'       => esc_html__( 'Date', 'NOVA-addons' ),
					'title'      => esc_html__( 'Titre', 'NOVA-addons' ),
					'rand'       => esc_html__( 'Aléatoire', 'NOVA-addons' ),
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
				'label'     => esc_html__( 'Ordre', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => [
					'ASC'  => esc_html__( 'Croissant', 'NOVA-addons' ),
					'DESC' => esc_html__( 'Décroissant', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'image_source',
			[
				'label'     => esc_html__( 'Source de l\'image', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'featured',
				'options'   => [
					'featured' => esc_html__( 'Image à la une', 'NOVA-addons' ),
					'acf'      => esc_html__( 'Champ ACF', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'acf_image_field',
			[
				'label'       => esc_html__( 'Clé du champ ACF (Image)', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: image_field', 'NOVA-addons' ),
				'condition'   => [
					'data_source'  => 'post_type',
					'image_source' => 'acf',
				],
			]
		);

		// ─── Champs de contenu ACF ──────────────────────────────────────
		$this->add_control(
			'acf_text_field',
			[
				'label'       => esc_html__( 'Clé ACF — Texte 1 (optionnel)', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: sous_titre', 'NOVA-addons' ),
				'description' => esc_html__( 'Si renseigné, le contenu de ce champ ACF remplacera le titre du post dans la zone Texte 1. Laissez vide pour utiliser le titre.', 'NOVA-addons' ),
				'separator'   => 'before',
				'condition'   => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'post_type_text_2_source',
			[
				'label'     => esc_html__( 'Source — Texte 2', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => [
					''        => esc_html__( 'Aucun', 'NOVA-addons' ),
					'excerpt' => esc_html__( 'Extrait du post (excerpt)', 'NOVA-addons' ),
					'acf'     => esc_html__( 'Champ ACF', 'NOVA-addons' ),
				],
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'acf_text_2_field',
			[
				'label'       => esc_html__( 'Clé du champ ACF — Texte 2', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'ex: description', 'NOVA-addons' ),
				'description' => esc_html__( 'Entrez la clé du champ ACF à afficher dans la zone Texte 2.', 'NOVA-addons' ),
				'condition'   => [
					'data_source'            => 'post_type',
					'post_type_text_2_source' => 'acf',
				],
			]
		);

		$tag_options = [
			'h1'   => 'H1',
			'h2'   => 'H2',
			'h3'   => 'H3',
			'h4'   => 'H4',
			'h5'   => 'H5',
			'h6'   => 'H6',
			'p'    => 'p',
			'div'  => 'div',
			'span' => 'span',
		];

		$this->add_control(
			'post_type_text_tag',
			[
				'label'     => esc_html__( 'Balise HTML — Texte 1', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => $tag_options,
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'post_type_text_2_tag',
			[
				'label'     => esc_html__( 'Balise HTML — Texte 2', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'p',
				'options'   => $tag_options,
				'condition' => [
					'data_source' => 'post_type',
					'acf_text_2_field!' => '',
				],
			]
		);

		// ─── Bouton "Lire la suite" ─────────────────────────────────────
		$this->add_control(
			'post_type_button_enable',
			[
				'label'     => esc_html__( 'Afficher un bouton "Lire la suite"', 'NOVA-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => 'no',
				'separator' => 'before',
				'condition' => [
					'data_source' => 'post_type',
				],
			]
		);

		$this->add_control(
			'post_type_button_text',
			[
				'label'       => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Lire la suite', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Lire la suite', 'NOVA-addons' ),
				'condition'   => [
					'data_source'           => 'post_type',
					'post_type_button_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'post_type_button_arrow_media',
			[
				'label'       => esc_html__( 'Icône / flèche (SVG ou image)', 'NOVA-addons' ),
				'type'        => Controls_Manager::MEDIA,
				'media_type'  => 'image',
				'description' => esc_html__( 'Sélectionnez ou uploadez une image/SVG depuis la médiathèque.', 'NOVA-addons' ),
				'condition'   => [
					'data_source'             => 'post_type',
					'post_type_button_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'post_type_button_arrow_position',
			[
				'label'       => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'after',
				'options'     => [
					'before' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
					'after'  => esc_html__( 'Après le texte', 'NOVA-addons' ),
				],
				'condition'   => [
					'data_source'             => 'post_type',
					'post_type_button_enable' => 'yes',
					'post_type_button_arrow_media[url]!' => '', // Seulement si l'icône est choisie
				],
			]
		);

		$this->end_controls_section();

		// ─── Section Filtres ──────────────────────────────────────────────
		$this->start_controls_section(
			'section_filters',
			[
				'label' => esc_html__( 'Filtres', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'enable_filters',
			[
				'label'       => esc_html__( 'Activer les filtres', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'no',
				'description' => esc_html__( 'Mode manuel : catégories par card. Post type : taxonomie ou champs meta (clé + type).', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'filter_source',
			[
				'label'     => esc_html__( 'Type de filtres', 'NOVA-addons' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'taxonomy',
				'options'   => [
					'taxonomy' => esc_html__( 'Taxonomie (boutons)', 'NOVA-addons' ),
					'meta'     => esc_html__( 'Champs meta (formulaire)', 'NOVA-addons' ),
				],
				'condition' => [
					'enable_filters' => 'yes',
					'data_source'    => 'post_type',
				],
			]
		);

		$this->add_control(
			'filter_taxonomy',
			[
				'label'       => esc_html__( 'Taxonomie pour les filtres', 'NOVA-addons' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $this->get_taxonomies(),
				'default'     => '',
				'condition'   => [
					'enable_filters' => 'yes',
					'data_source'    => 'post_type',
					'filter_source'  => 'taxonomy',
				],
				'description' => esc_html__( 'Boutons de filtre basés sur les termes de cette taxonomie.', 'NOVA-addons' ),
			]
		);

		$meta_filter_rep = new Repeater();
		$meta_filter_rep->add_control(
			'meta_key',
			[
				'label'       => esc_html__( 'Meta key', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
				'description' => esc_html__( 'Clé post meta (ex. debloque, secteur-activite).', 'NOVA-addons' ),
			]
		);
		$meta_filter_rep->add_control(
			'field_type',
			[
				'label'   => esc_html__( 'Type de champ', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'select',
				'options' => [
					'select'   => esc_html__( 'Liste (select)', 'NOVA-addons' ),
					'text'     => esc_html__( 'Texte', 'NOVA-addons' ),
					'checkbox' => esc_html__( 'Case à cocher', 'NOVA-addons' ),
				],
			]
		);
		$meta_filter_rep->add_control(
			'label',
			[
				'label'       => esc_html__( 'Libellé', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'label_block' => true,
			]
		);
		$meta_filter_rep->add_control(
			'placeholder',
			[
				'label'     => esc_html__( 'Placeholder', 'NOVA-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => [
					'field_type' => 'text',
				],
			]
		);
		$meta_filter_rep->add_control(
			'select_options',
			[
				'label'       => esc_html__( 'Options (select)', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '',
				'description' => esc_html__( 'Valeurs séparées par des virgules. Vide = toutes les valeurs distinctes de cette meta sur les publications publiées du type choisi (texte ou select d’origine).', 'NOVA-addons' ),
				'condition'   => [
					'field_type' => 'select',
				],
			]
		);
		$meta_filter_rep->add_control(
			'filter_icon',
			[
				'label' => esc_html__( 'Icône à côté du champ', 'NOVA-addons' ),
				'type'  => Controls_Manager::MEDIA,
			]
		);

		$this->add_control(
			'meta_filters',
			[
				'label'       => esc_html__( 'Filtres meta', 'NOVA-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $meta_filter_rep->get_controls(),
				'default'     => [],
				'title_field' => '{{{ meta_key }}} ({{{ field_type }}})',
				'condition'   => [
					'enable_filters' => 'yes',
					'data_source'    => 'post_type',
					'filter_source'  => 'meta',
				],
			]
		);

		$this->add_control(
			'filter_all_text',
			[
				'label'     => esc_html__( 'Texte du bouton "Tout"', 'NOVA-addons' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Tout', 'NOVA-addons' ),
				'condition' => [
					'enable_filters' => 'yes',
				],
			]
		);

		$this->add_control(
			'hide_filter_all',
			[
				'label'       => esc_html__( 'Masquer le bouton « Tout »', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'no',
				'separator'   => 'before',
				'condition'   => [
					'enable_filters' => 'yes',
				],
			]
		);

		$this->add_control(
			'hide_filter_all_mobile',
			[
				'label'       => esc_html__( 'Masquer « Tout » sur mobile uniquement', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'no',
				'condition'   => [
					'enable_filters'  => 'yes',
					'hide_filter_all!' => 'yes',
				],
				'description' => esc_html__( 'Masque « Tout » sur mobile ; le premier filtre actif s’applique par défaut.', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// ─── Section Cards ────────────────────────────────────────────────
		$this->start_controls_section(
			'section_cards',
			[
				'label' => esc_html__( 'Cards', 'NOVA-addons' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'card_image',
			[
				'label' => esc_html__( 'Image de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'card_background_color',
			[
				'label' => esc_html__( 'Couleur de fond de la carte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'description' => esc_html__( 'Optionnel. Appliquée derrière l\'image et l\'overlay.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_text',
			[
				'label' => esc_html__( 'Texte 1', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Texte principal', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez le premier texte', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_text_2',
			[
				'label' => esc_html__( 'Texte 2', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => esc_html__( 'Texte secondaire', 'NOVA-addons' ),
				'placeholder' => esc_html__( 'Entrez le deuxième texte', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_overlay_enable',
			[
				'label' => esc_html__( 'Activer Overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_overlay_mode',
			[
				'label' => esc_html__( 'Mode de configuration', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'simple',
				'options' => [
					'simple' => esc_html__( 'Simple (CSS direct)', 'NOVA-addons' ),
					'advanced' => esc_html__( 'Avancé (Contrôles)', 'NOVA-addons' ),
				],
				'condition' => [
					'card_overlay_enable' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient',
			[
				'label' => esc_html__( 'Gradient CSS', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)',
				'placeholder' => esc_html__( 'linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)', 'NOVA-addons' ),
				'description' => esc_html__( 'Entrez directement le gradient CSS. Exemple: linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)', 'NOVA-addons' ),
				'rows' => 2,
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'simple',
				],
			]
		);

		// Global layout option - controls how image and content are arranged.
		$this->add_control(
			'card_layout_mode',
			[
				'label' => esc_html__( 'Layout des cartes', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'background_overlay',
				'options' => [
					'background_overlay' => esc_html__( 'Image de fond avec overlay (défaut)', 'NOVA-addons' ),
					'image_above_content' => esc_html__( 'Image puis contenu (sans overlay dans l\'image)', 'NOVA-addons' ),
				],
				'description' => esc_html__( '\"Image puis contenu\" affiche l\'image en haut et le texte en dessous, sans texte à l\'intérieur de l\'image.', 'NOVA-addons' ),
			]
		);

		// ─── Hover options (background_overlay only) ─────────────────────────

		$this->add_control(
			'hover_image_scale_enable',
			[
				'label'       => esc_html__( 'Hover : zoom image', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'yes',
				'description' => esc_html__( 'Active un léger zoom sur l\'image de fond au hover.', 'NOVA-addons' ),
				'condition'   => [
					'card_layout_mode' => 'background_overlay',
				],
				'separator'   => 'before',
			]
		);

		$this->add_control(
			'hover_content_gsap_enable',
			[
				'label'       => esc_html__( 'Hover : animation contenu (GSAP)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'yes',
				'description' => esc_html__( 'Active l\'animation GSAP du contenu au hover (déplacement vers le centre).', 'NOVA-addons' ),
				'condition'   => [
					'card_layout_mode' => 'background_overlay',
				],
			]
		);

		$this->add_control(
			'hover_content_initial_position',
			[
				'label'       => esc_html__( 'Position initiale du contenu', 'NOVA-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'bottom-center',
				'options'     => [
					'bottom-center' => esc_html__( 'Bas centre', 'NOVA-addons' ),
					'bottom-left'   => esc_html__( 'Bas gauche', 'NOVA-addons' ),
					'bottom-right'  => esc_html__( 'Bas droite', 'NOVA-addons' ),
					'center-center' => esc_html__( 'Centre centre', 'NOVA-addons' ),
					'center-left'   => esc_html__( 'Centre gauche', 'NOVA-addons' ),
					'center-right'  => esc_html__( 'Centre droite', 'NOVA-addons' ),
					'top-center'    => esc_html__( 'Haut centre', 'NOVA-addons' ),
					'top-left'      => esc_html__( 'Haut gauche', 'NOVA-addons' ),
					'top-right'     => esc_html__( 'Haut droite', 'NOVA-addons' ),
				],
				'description' => esc_html__( 'Position de départ du contenu avant le hover. Au hover, il se déplace vers le centre de la carte.', 'NOVA-addons' ),
				'condition'   => [
					'card_layout_mode'          => 'background_overlay',
					'hover_content_gsap_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'hover_duration_in',
			[
				'label'       => esc_html__( 'Durée animation hover-in (ms)', 'NOVA-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 600,
				'min'         => 100,
				'max'         => 3000,
				'step'        => 50,
				'description' => esc_html__( 'Vitesse de l\'animation quand la souris entre sur la card.', 'NOVA-addons' ),
				'condition'   => [
					'card_layout_mode'          => 'background_overlay',
					'hover_content_gsap_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'hover_duration_out',
			[
				'label'       => esc_html__( 'Durée animation hover-out (ms)', 'NOVA-addons' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 500,
				'min'         => 100,
				'max'         => 3000,
				'step'        => 50,
				'description' => esc_html__( 'Vitesse de l\'animation quand la souris quitte la card.', 'NOVA-addons' ),
				'condition'   => [
					'card_layout_mode'          => 'background_overlay',
					'hover_content_gsap_enable' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_background',
			[
				'label' => esc_html__( 'Type d\'overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'gradient',
				'options' => [
					'classic' => esc_html__( 'Couleur unie', 'NOVA-addons' ),
					'gradient' => esc_html__( 'Gradient', 'NOVA-addons' ),
				],
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0.5)',
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
					'card_overlay_background' => 'classic',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient_type',
			[
				'label' => esc_html__( 'Type', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'linear',
				'options' => [
					'linear' => esc_html__( 'Linéaire', 'NOVA-addons' ),
					'radial' => esc_html__( 'Radial', 'NOVA-addons' ),
				],
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
					'card_overlay_background' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient_angle',
			[
				'label' => esc_html__( 'Angle', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range' => [
					'deg' => [
						'step' => 10,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'unit' => 'deg',
					'size' => 180,
				],
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
					'card_overlay_background' => 'gradient',
					'card_overlay_gradient_type' => 'linear',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient_color_a',
			[
				'label' => esc_html__( 'Couleur 1', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0)',
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
					'card_overlay_background' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient_color_a_location',
			[
				'label' => esc_html__( 'Position couleur 1 (%)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 40.44,
				],
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
					'card_overlay_background' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient_color_b',
			[
				'label' => esc_html__( 'Couleur 2', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0.7)',
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_background' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'card_overlay_gradient_color_b_location',
			[
				'label' => esc_html__( 'Position couleur 2 (%)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'condition' => [
					'card_overlay_enable' => 'yes',
					'card_overlay_mode' => 'advanced',
					'card_overlay_background' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'card_link',
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

		$repeater->add_control(
			'card_category',
			[
				'label'       => esc_html__( 'Catégorie (pour filtres)', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'ex: Nature, Voyage', 'NOVA-addons' ),
				'description' => esc_html__( 'Utilisé pour les filtres si activés. Plusieurs catégories séparées par une virgule.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_button_text',
			[
				'label'       => esc_html__( 'Texte du bouton', 'NOVA-addons' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Découvrir', 'NOVA-addons' ),
				'description' => esc_html__( 'Laissez vide pour ne pas afficher de bouton.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_button_link',
			[
				'label' => esc_html__( 'Lien du bouton', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://votre-lien.com', 'NOVA-addons' ),
				'show_external' => true,
				'description' => esc_html__( 'Si vide, le lien principal de la card sera utilisé.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'card_svg_color',
			[
				'label' => esc_html__( 'Couleur SVG (cette carte)', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'description' => esc_html__( 'Applique cette couleur au SVG de cette carte. Laissez vide pour utiliser la couleur globale ou la couleur originale.', 'NOVA-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'creative_background_enable',
			[
				'label' => esc_html__( 'Activer creative background', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'description' => esc_html__( 'Si activé, applique des backgrounds SVG aléatoires sur les cards et désactive les styles de border et background configurés.', 'NOVA-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'creative_background_size_type',
			[
				'label' => esc_html__( 'Taille des SVG', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'large',
				'options' => [
					'large' => esc_html__( 'Large', 'NOVA-addons' ),
					'small' => esc_html__( 'Small', 'NOVA-addons' ),
				],
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Choisissez la taille des SVG à utiliser (large ou small).', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_size',
			[
				'label' => esc_html__( 'Taille du background', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'contain',
				'options' => [
					'contain' => esc_html__( 'Contain (garde aspect ratio)', 'NOVA-addons' ),
					'cover' => esc_html__( 'Cover (remplit la card)', 'NOVA-addons' ),
					'auto' => esc_html__( 'Auto (taille originale)', 'NOVA-addons' ),
				],
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Contain préserve l\'aspect ratio du SVG, Cover remplit toute la card.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_random_rotation',
			[
				'label' => esc_html__( 'Rotation aléatoire 180°', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Applique aléatoirement une rotation de 180° sur certains SVG.', 'NOVA-addons' ),
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
				'description' => esc_html__( 'Applique aléatoirement un flip horizontal sur certains SVG.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_color',
			[
				'label' => esc_html__( 'Couleur SVG (toutes les cartes)', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Applique cette couleur à tous les SVG. Laissez vide pour garder la couleur originale.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_hover_enable',
			[
				'label' => esc_html__( 'Activer effet hover', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'condition' => [
					'creative_background_enable' => 'yes',
				],
				'description' => esc_html__( 'Applique une transformation aléatoire (rotation ou flip) au hover sur les cards avec background créatif.', 'NOVA-addons' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'creative_background_hover_transformations',
			[
				'label' => esc_html__( 'Transformations hover', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => [
					'rotate' => esc_html__( 'Rotation', 'NOVA-addons' ),
					'scaleX' => esc_html__( 'Flip horizontal', 'NOVA-addons' ),
				],
				'default' => ['rotate', 'scaleX'],
				'condition' => [
					'creative_background_enable' => 'yes',
					'creative_background_hover_enable' => 'yes',
				],
				'description' => esc_html__( 'Sélectionnez les transformations à appliquer aléatoirement au hover. Maintenez Ctrl/Cmd pour sélectionner plusieurs.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'creative_background_hover_duration',
			[
				'label' => esc_html__( 'Durée animation hover (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['ms'],
				'range' => [
					'ms' => [
						'min' => 100,
						'max' => 2000,
						'step' => 50,
					],
				],
				'default' => [
					'unit' => 'ms',
					'size' => 500,
				],
				'condition' => [
					'creative_background_enable' => 'yes',
					'creative_background_hover_enable' => 'yes',
				],
				'description' => esc_html__( 'Durée de l\'animation de transition au hover.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'cards_list',
			[
				'label'     => esc_html__( 'Liste des cards', 'NOVA-addons' ),
				'type'      => Controls_Manager::REPEATER,
				'fields'    => $repeater->get_controls(),
				'default'   => [
					[
						'card_text' => esc_html__( 'Card 1', 'NOVA-addons' ),
					],
					[
						'card_text' => esc_html__( 'Card 2', 'NOVA-addons' ),
					],
					[
						'card_text' => esc_html__( 'Card 3', 'NOVA-addons' ),
					],
				],
				'title_field' => '{{{ card_text }}}',
				'condition'   => [
					'data_source' => 'manual',
				],
			]
		);

		$this->end_controls_section();

		// Section Grid
		$this->start_controls_section(
			'section_grid',
			[
				'label' => esc_html__( 'Grid', 'NOVA-addons' ),
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
			'grid_columns',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'desktop_default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'condition' => [
					'creative_background_enable!' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'grid_columns_creative',
			[
				'label' => esc_html__( 'Colonnes', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'desktop_default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'condition' => [
					'creative_background_enable' => 'yes',
					'grid_columns_mode' => 'auto',
				],
			]
		);

		$this->add_responsive_control(
			'grid_columns_px',
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
			'grid_gap_horizontal',
			[
				'label' => esc_html__( 'Espacement horizontal (Gap)', 'NOVA-addons' ),
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
					'{{WRAPPER}} .nova-cards-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'grid_gap_vertical',
			[
				'label' => esc_html__( 'Espacement vertical (Gap)', 'NOVA-addons' ),
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
					'{{WRAPPER}} .nova-cards-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-cards-grid' => 'justify-items: {{VALUE}};',
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
					'{{WRAPPER}} .nova-cards-grid' => 'align-items: {{VALUE}};',
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
					'{{WRAPPER}} .nova-cards-grid' => 'justify-content: {{VALUE}} !important;',
				],
				'description' => esc_html__( 'Aligne la grille elle-même horizontalement si elle est plus petite que le conteneur. Utilisez "Centre" pour centrer les éléments qui ne remplissent pas complètement la ligne (utile avec le mode pixels).', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'grid_equal_height',
			[
				'label' => esc_html__( 'Cartes même hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .nova-cards-grid' => 'grid-auto-rows: 1fr;',
					'{{WRAPPER}} .nova-cards-grid > .nova-card-item' => 'height: 100%;',
				],
				'description' => esc_html__( 'Force toutes les cartes d\'une même ligne à avoir la même hauteur.', 'NOVA-addons' ),
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

		// Flex configuration for each card item (like other widget configs)
		$this->add_control(
			'card_item_display',
			[
				'label' => esc_html__( 'Card Display', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => esc_html__( 'Block', 'NOVA-addons' ),
					'flex'  => esc_html__( 'Flex', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-grid > .nova-card-item' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_item_flex_direction',
			[
				'label' => esc_html__( 'Card Flex Direction', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'row' => esc_html__( 'Row', 'NOVA-addons' ),
					'column' => esc_html__( 'Column', 'NOVA-addons' ),
					'row-reverse' => esc_html__( 'Row Reverse', 'NOVA-addons' ),
					'column-reverse' => esc_html__( 'Column Reverse', 'NOVA-addons' ),
				],
				'condition' => [
					'card_item_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-grid > .nova-card-item' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_item_justify_content',
			[
				'label' => esc_html__( 'Card Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
					'space-between' => [
						'title' => esc_html__( 'Between', 'NOVA-addons' ),
						'icon' => 'eicon-justify-space-between-v',
					],
				],
				'default' => 'flex-start',
				'condition' => [
					'card_item_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-grid > .nova-card-item' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_item_align_items',
			[
				'label' => esc_html__( 'Card Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Start', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'End', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-right',
					],
					'stretch' => [
						'title' => esc_html__( 'Stretch', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-stretch',
					],
				],
				'default' => 'stretch',
				'condition' => [
					'card_item_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-grid > .nova-card-item' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_item_gap',
			[
				'label' => esc_html__( 'Card Gap', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [ 'min' => 0, 'max' => 100 ],
					'em' => [ 'min' => 0, 'max' => 8 ],
					'rem' => [ 'min' => 0, 'max' => 8 ],
				],
				'condition' => [
					'card_item_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-grid > .nova-card-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
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
				'label' => esc_html__( 'Délai entre cards (ms)', 'NOVA-addons' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 500,
				'min' => 0,
				'max' => 5000,
				'step' => 50,
				'description' => esc_html__( 'Temps entre chaque animation de card (si cascade activée)', 'NOVA-addons' ),
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

		// Section Style - Overlay
		$this->start_controls_section(
			'section_style_overlay',
			[
				'label' => esc_html__( 'Overlay', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'overlay_enable',
			[
				'label' => esc_html__( 'Activer Overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'overlay_mode',
			[
				'label' => esc_html__( 'Mode de configuration', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'simple',
				'options' => [
					'simple' => esc_html__( 'Simple (CSS direct)', 'NOVA-addons' ),
					'advanced' => esc_html__( 'Avancé (Contrôles)', 'NOVA-addons' ),
				],
				'condition' => [
					'overlay_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'overlay_gradient',
			[
				'label' => esc_html__( 'Gradient CSS', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => 'linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)',
				'placeholder' => esc_html__( 'linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)', 'NOVA-addons' ),
				'description' => esc_html__( 'Entrez directement le gradient CSS. Sera appliqué à toutes les cards. Exemple: linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)', 'NOVA-addons' ),
				'rows' => 2,
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'simple',
				],
			]
		);

		$this->add_control(
			'overlay_background',
			[
				'label' => esc_html__( 'Type d\'overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'gradient',
				'options' => [
					'classic' => esc_html__( 'Couleur unie', 'NOVA-addons' ),
					'gradient' => esc_html__( 'Gradient', 'NOVA-addons' ),
				],
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
				],
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0.5)',
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'classic',
				],
			]
		);

		$this->add_control(
			'overlay_gradient_type',
			[
				'label' => esc_html__( 'Type', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'linear',
				'options' => [
					'linear' => esc_html__( 'Linéaire', 'NOVA-addons' ),
					'radial' => esc_html__( 'Radial', 'NOVA-addons' ),
				],
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'gradient',
				],
			]
		);

		$this->add_control(
			'overlay_gradient_angle',
			[
				'label' => esc_html__( 'Angle', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range' => [
					'deg' => [
						'step' => 10,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'unit' => 'deg',
					'size' => 180,
				],
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'gradient',
					'overlay_gradient_type' => 'linear',
				],
			]
		);

		$this->add_control(
			'overlay_gradient_color_a',
			[
				'label' => esc_html__( 'Couleur 1', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0)',
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'gradient',
				],
			]
		);

		$this->add_control(
			'overlay_gradient_color_a_location',
			[
				'label' => esc_html__( 'Position couleur 1 (%)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 40.44,
				],
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'gradient',
				],
			]
		);

		$this->add_control(
			'overlay_gradient_color_b',
			[
				'label' => esc_html__( 'Couleur 2', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0.7)',
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'gradient',
				],
			]
		);

		$this->add_control(
			'overlay_gradient_color_b_location',
			[
				'label' => esc_html__( 'Position couleur 2 (%)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'condition' => [
					'overlay_enable' => 'yes',
					'overlay_mode' => 'advanced',
					'overlay_background' => 'gradient',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Card
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => esc_html__( 'Card', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding du conteneur', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_aspect_ratio',
			[
				'label' => esc_html__( 'Ratio d\'aspect', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					'' => esc_html__( 'Aucun (hauteur min)', 'NOVA-addons' ),
					'21/9' => '21:9 (Ultra-wide)',
					'16/9' => '16:9 (Widescreen)',
					'4/3' => '4:3 (Standard)',
					'3/2' => '3:2 (Photo)',
					'1/1' => '1:1 (Carré)',
					'2/3' => '2:3 (Portrait)',
					'3/4' => '3:4 (Portrait)',
					'9/16' => '9:16 (Mobile)',
					'custom' => esc_html__( 'Personnalisé', 'NOVA-addons' ),
				],
				'description' => esc_html__( 'Définit le ratio largeur/hauteur des cards. Si aucun ratio n\'est sélectionné, la hauteur min sera utilisée.', 'NOVA-addons' ),
				'selectors_dictionary' => [
					'' => '',
					'21/9' => '21/9',
					'16/9' => '16/9',
					'4/3' => '4/3',
					'3/2' => '3/2',
					'1/1' => '1/1',
					'2/3' => '2/3',
					'3/4' => '3/4',
					'9/16' => '9/16',
					'custom' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_aspect_ratio_custom',
			[
				'label' => esc_html__( 'Ratio personnalisé', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
				'placeholder' => esc_html__( 'Ex: 16/9, 1.777, 800/600, 1920/1080', 'NOVA-addons' ),
				'description' => esc_html__( 'Entrez un ratio au format fraction (16/9) ou nombre décimal (1.777). Vous pouvez aussi utiliser des pixels (800/600).', 'NOVA-addons' ),
				'condition' => [
					'card_aspect_ratio' => 'custom',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item' => 'aspect-ratio: {{VALUE}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'card_min_height',
			[
				'label' => esc_html__( 'Hauteur min', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'vh' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'em' => [
						'min' => 5,
						'max' => 50,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 300,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item' => 'min-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'card_aspect_ratio' => '',
				],
			]
		);

		$this->add_responsive_control(
			'card_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em', 'rem' ],
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
					'rem' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'description' => esc_html__( 'Définit la largeur maximale des cartes.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'card_border',
				'selector' => '{{WRAPPER}} .nova-card-item:not(.has-creative-background)',
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item:not(.has-creative-background)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					// Ensure image-top layout inherits consistent rounding
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0;',
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-content' => 'border-radius: 0 0 {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .nova-card-item:not(.has-creative-background)',
			]
		);

		$this->end_controls_section();

		// Section Style - Image (for image-above-content layout)
		$this->start_controls_section(
			'section_style_image_top',
			[
				'label' => esc_html__( 'Image (layout image puis contenu)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'card_layout_mode' => 'image_above_content',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [ 'min' => 50, 'max' => 1200 ],
					'%' => [ 'min' => 10, 'max' => 100 ],
					'vw' => [ 'min' => 10, 'max' => 100 ],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'width: 100%; max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh', 'custom' ],
				'range' => [
					'px' => [ 'min' => 50, 'max' => 800 ],
					'%' => [ 'min' => 10, 'max' => 100 ],
					'vh' => [ 'min' => 10, 'max' => 100 ],
				],
				'default' => [
					'size' => 260,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_aspect_ratio',
			[
				'label' => esc_html__( 'Ratio d\'aspect', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'Défaut', 'NOVA-addons' ),
					'1/1' => '1:1 (Carré)',
					'4/3' => '4:3',
					'3/2' => '3:2',
					'16/9' => '16:9',
					'21/9' => '21:9 (Cinéma)',
					'3/4' => '3:4 (Portrait)',
					'2/3' => '2:3 (Portrait)',
					'9/16' => '9:16 (Portrait)',
					'custom' => esc_html__( 'Personnalisé', 'NOVA-addons' ),
				],
				'default' => '',
				'selectors_dictionary' => [
					'custom' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_aspect_ratio_custom',
			[
				'label' => esc_html__( 'Ratio personnalisé', 'NOVA-addons' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => '16/9',
				'description' => esc_html__( 'Entrez un ratio comme "16/9", "4/3" ou "1.5"', 'NOVA-addons' ),
				'condition' => [
					'image_top_aspect_ratio' => 'custom',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'aspect-ratio: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_border_radius',
			[
				'label' => esc_html__( 'Arrondi', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_top_object_fit',
			[
				'label' => esc_html__( 'Ajustement de l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'cover' => esc_html__( 'Couvrir', 'NOVA-addons' ),
					'contain' => esc_html__( 'Contenir', 'NOVA-addons' ),
					'fill' => esc_html__( 'Remplir', 'NOVA-addons' ),
					'none' => esc_html__( 'Aucun', 'NOVA-addons' ),
					'scale-down' => esc_html__( 'Scale Down', 'NOVA-addons' ),
				],
				'default' => 'cover',
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'image_top_object_position',
			[
				'label' => esc_html__( 'Position de l\'image', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'center center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'top center' => esc_html__( 'Haut', 'NOVA-addons' ),
					'bottom center' => esc_html__( 'Bas', 'NOVA-addons' ),
					'left center' => esc_html__( 'Gauche', 'NOVA-addons' ),
					'right center' => esc_html__( 'Droite', 'NOVA-addons' ),
					'top left' => esc_html__( 'Haut gauche', 'NOVA-addons' ),
					'top right' => esc_html__( 'Haut droite', 'NOVA-addons' ),
					'bottom left' => esc_html__( 'Bas gauche', 'NOVA-addons' ),
					'bottom right' => esc_html__( 'Bas droite', 'NOVA-addons' ),
				],
				'default' => 'center center',
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper img' => 'object-position: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_top_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default' => [
					'bottom' => '0',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_top_border',
				'selector' => '{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'image_top_box_shadow',
				'selector' => '{{WRAPPER}} .nova-card-item--image-top .nova-card-image-wrapper',
			]
		);

		$this->end_controls_section();

		// Section Style - Text 1
		$this->start_controls_section(
			'section_style_text_1',
			[
				'label' => esc_html__( 'Texte 1', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_1_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-card-text-1 *' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_1_typography',
				'selector' => '{{WRAPPER}} .nova-card-text-1 *',
			]
		);

		$this->add_responsive_control(
			'text_1_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text-1' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Text 2
		$this->start_controls_section(
			'section_style_text_2',
			[
				'label' => esc_html__( 'Texte 2', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_2_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-card-text-2' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'text_2_typography',
				'selector' => '{{WRAPPER}} .nova-card-text-2',
			]
		);

		$this->add_responsive_control(
			'text_2_margin',
			[
				'label' => esc_html__( 'Marge', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text-2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Button
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Bouton', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_alignment',
			[
				'label' => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Gauche', 'NOVA-addons' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon'  => 'eicon-text-align-center',
					],
					'flex-end' => [
						'title' => esc_html__( 'Droite', 'NOVA-addons' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-card-button-wrap' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .nova-card-button',
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
				'default' => [
					'top' => '12',
					'right' => '28',
					'bottom' => '12',
					'left' => '28',
					'unit' => 'px',
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
					'{{WRAPPER}} .nova-card-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
				'default' => [
					'top' => '999',
					'right' => '999',
					'bottom' => '999',
					'left' => '999',
					'unit' => 'px',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .nova-card-button',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .nova-card-button',
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
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-card-button' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(255,255,255,0.18)',
				'selectors' => [
					'{{WRAPPER}} .nova-card-button' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'button_text_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-card-button:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_background_color_hover',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-card-button:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover_custom',
			[
				'label' => esc_html__( 'Couleur de bordure (hover)', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-card-button:hover' => 'border-color: {{VALUE}} !important;',
				],
				'condition' => [
					'button_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'button_hover_transform',
			[
				'label' => esc_html__( 'Translation verticale (px)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => -20,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-button:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// Section Style - Content
		$this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__( 'Contenu (nova-card-content)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'content_display',
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
					'{{WRAPPER}} .nova-card-content' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_flex_direction',
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
					'content_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Début', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
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
				],
				'default' => 'flex-end',
				'condition' => [
					'content_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Début', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Fin', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
					'stretch' => [
						'title' => esc_html__( 'Étirer', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-stretch',
					],
				],
				'default' => 'center',
				'condition' => [
					'content_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_gap',
			[
				'label' => esc_html__( 'Espacement (gap)', 'NOVA-addons' ),
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
					'size' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_align',
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
					'{{WRAPPER}} .nova-card-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'content_max_width_auto',
			[
				'label' => esc_html__( 'Largeur auto (max-content)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'max-width: max-content;',
				],
			]
		);

		$this->add_responsive_control(
			'content_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em', 'rem' ],
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
					'rem' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition' => [
					'content_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Text Wrapper
		$this->start_controls_section(
			'section_style_text_wrapper',
			[
				'label' => esc_html__( 'Wrapper Textes (nova-card-text.nova-card-text)', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'text_wrapper_display',
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
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_flex_direction',
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
					'text_wrapper_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_justify_content',
			[
				'label' => esc_html__( 'Justify Content', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Début', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-center',
					],
					'flex-end' => [
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
				],
				'default' => 'flex-start',
				'condition' => [
					'text_wrapper_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_align_items',
			[
				'label' => esc_html__( 'Align Items', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'flex-start' => [
						'title' => esc_html__( 'Début', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-top',
					],
					'center' => [
						'title' => esc_html__( 'Centre', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-middle',
					],
					'flex-end' => [
						'title' => esc_html__( 'Fin', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-bottom',
					],
					'stretch' => [
						'title' => esc_html__( 'Étirer', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-stretch',
					],
				],
				'default' => 'flex-start',
				'condition' => [
					'text_wrapper_display' => 'flex',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_gap',
			[
				'label' => esc_html__( 'Espacement (gap)', 'NOVA-addons' ),
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
					'size' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_margin',
			[
				'label' => esc_html__( 'Margin', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_align',
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
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_wrapper_max_width_auto',
			[
				'label' => esc_html__( 'Largeur auto (max-content)', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'yes',
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'max-width: max-content;',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_max_width',
			[
				'label' => esc_html__( 'Largeur maximale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'em', 'rem' ],
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
					'rem' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition' => [
					'text_wrapper_max_width_auto' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-card-text.nova-card-text' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ─── Section Style - Filtres ──────────────────────────────────────
		$this->start_controls_section(
			'section_style_filters',
			[
				'label'     => esc_html__( 'Filtres', 'NOVA-addons' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_filters' => 'yes',
				],
			]
		);

		$this->add_control(
			'filters_align',
			[
				'label'     => esc_html__( 'Alignement', 'NOVA-addons' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Gauche', 'NOVA-addons' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Centre', 'NOVA-addons' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Droite', 'NOVA-addons' ),  'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .nova-cards-filters' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'filters_gap',
			[
				'label'     => esc_html__( 'Espacement entre boutons', 'NOVA-addons' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'     => [
					'px' => [ 'min' => 0, 'max' => 50, 'step' => 1 ],
				],
				'default'   => [ 'unit' => 'px', 'size' => 10 ],
				'selectors' => [
					'{{WRAPPER}} .nova-cards-filter-item' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filters_margin',
			[
				'label'      => esc_html__( 'Marge', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-cards-filters' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'filter_tabs' );

		// --- Normal ---
		$this->start_controls_tab( 'filter_tab_normal', [ 'label' => esc_html__( 'Normal', 'NOVA-addons' ) ] );

		$this->add_control(
			'filter_color',
			[
				'label'     => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-cards-filter-item' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'filter_background',
				'selector' => '{{WRAPPER}} .nova-cards-filter-item',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'filter_typography',
				'selector' => '{{WRAPPER}} .nova-cards-filter-item',
			]
		);

		$this->add_responsive_control(
			'filter_padding',
			[
				'label'      => esc_html__( 'Padding', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-cards-filter-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'filter_border_radius',
			[
				'label'      => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .nova-cards-filter-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'filter_border',
				'selector' => '{{WRAPPER}} .nova-cards-filter-item',
			]
		);

		$this->end_controls_tab();

		// --- Actif ---
		$this->start_controls_tab( 'filter_tab_active', [ 'label' => esc_html__( 'Actif', 'NOVA-addons' ) ] );

		$this->add_control(
			'filter_active_color',
			[
				'label'     => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-cards-filter-item.active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'filter_active_background',
				'selector' => '{{WRAPPER}} .nova-cards-filter-item.active',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'filter_active_border',
				'selector' => '{{WRAPPER}} .nova-cards-filter-item.active',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Récupère la liste des post types publics.
	 *
	 * @return array
	 */
	private function get_post_types() {
		return function_exists( 'nova_addons_get_elementor_post_type_options' )
			? nova_addons_get_elementor_post_type_options()
			: array();
	}

	/**
	 * Récupère la liste des taxonomies publiques.
	 *
	 * @return array
	 */
	private function get_taxonomies() {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [ '' => esc_html__( '-- Sélectionner --', 'NOVA-addons' ) ];
		foreach ( $taxonomies as $tax ) {
			$options[ $tax->name ] = $tax->label;
		}
		return $options;
	}

	/**
	 * Récupère les posts selon les paramètres et retourne un tableau
	 * normalisé compatible avec le format interne des cards.
	 *
	 * @param array $settings Settings Elementor.
	 * @return array
	 */
	private function get_posts( $settings ) {
		$args  = [
			'post_type'      => $settings['post_type'],
			'posts_per_page' => intval( $settings['posts_per_page'] ),
			'orderby'        => $settings['order_by'],
			'order'          => $settings['order'],
			'post_status'    => 'publish',
		];
		$query = new \WP_Query( $args );
		$posts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				// Image
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

				// Termes de taxonomie pour les filtres
				$terms = [];
				if ( ! empty( $settings['filter_taxonomy'] ) ) {
					$post_terms = wp_get_post_terms( $post_id, $settings['filter_taxonomy'] );
					if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
						foreach ( $post_terms as $term ) {
							$terms[] = $term->slug;
						}
					}
				}

				$meta_for_filter = [];
				$use_meta_filter = ! empty( $settings['enable_filters'] )
					&& $settings['enable_filters'] === 'yes'
					&& ! empty( $settings['filter_source'] )
					&& $settings['filter_source'] === 'meta';
				if ( $use_meta_filter ) {
					foreach ( $this->get_meta_filter_keys_from_settings( $settings ) as $meta_key ) {
						$meta_for_filter[ $meta_key ] = $this->read_post_meta_for_filter( $post_id, $meta_key );
					}
				}

				$posts[] = [
					'id'          => $post_id,
					'title'       => get_the_title(),
					'image'       => $image_url,
					'link'        => get_permalink(),
					'terms'       => $terms,
					'meta'        => $meta_for_filter,
					'excerpt'     => get_the_excerpt(),
					// ACF Texte 1 (fallback: titre)
					// Si la valeur est un ID d'image ou une URL d'image → afficher <img>
					'text'        => ( function () use ( $settings, $post_id ) {
						if ( empty( $settings['acf_text_field'] ) ) {
							return get_the_title();
						}

						// Lire la valeur brute (meta ou ACF)
						$acf_key = $settings['acf_text_field'];
						$value = function_exists( 'get_field' ) ? get_field( $acf_key, $post_id ) : get_post_meta( $post_id, $acf_key, true );

						if ( empty( $value ) ) {
							return get_the_title();
						}

						// Cas 1 : la valeur est un tableau ACF image (retourne array avec 'url', 'id', etc.)
						if ( is_array( $value ) && ! empty( $value['url'] ) ) {
							$alt = ! empty( $value['alt'] ) ? $value['alt'] : get_the_title();
							return '<img src="' . esc_url( $value['url'] ) . '" alt="' . esc_attr( $alt ) . '" class="nova-card-text-logo" loading="lazy" />';
						}

						// Cas 2 : la valeur est un ID d'attachment WordPress
						if ( is_numeric( $value ) && wp_attachment_is_image( (int) $value ) ) {
							$img_url = wp_get_attachment_image_url( (int) $value, 'medium' );
							$alt     = get_post_meta( (int) $value, '_wp_attachment_image_alt', true ) ?: get_the_title();
							if ( $img_url ) {
								return '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $alt ) . '" class="nova-card-text-logo" loading="lazy" />';
							}
						}

						// Cas 3 : la valeur est une URL d'image
						if ( is_string( $value ) && preg_match( '/\.(jpg|jpeg|png|gif|svg|webp)(\?.*)?$/i', $value ) ) {
							return '<img src="' . esc_url( $value ) . '" alt="' . esc_attr( get_the_title() ) . '" class="nova-card-text-logo" loading="lazy" />';
						}

						// Cas 4 : texte normal
						return (string) $value;
					} )(),
					// Texte 2 — source configurable : aucun / excerpt / ACF
					'text2'       => ( function () use ( $settings, $post_id ) {
						$src = ! empty( $settings['post_type_text_2_source'] ) ? $settings['post_type_text_2_source'] : '';
						if ( 'excerpt' === $src ) {
							return get_the_excerpt();
						}
						if ( 'acf' === $src && ! empty( $settings['acf_text_2_field'] ) && function_exists( 'get_field' ) ) {
							return get_field( $settings['acf_text_2_field'], $post_id ) ?: '';
						}
						return '';
					} )(),
					// Bouton "Lire la suite"
					'button_text' => ( ! empty( $settings['post_type_button_enable'] ) && $settings['post_type_button_enable'] === 'yes' )
						? ( ! empty( $settings['post_type_button_text'] ) ? $settings['post_type_button_text'] : __( 'Lire la suite', 'NOVA-addons' ) )
						: '',
				];
			}
			wp_reset_postdata();
		}
		return $posts;
	}

	/**
	 * Récupère les termes d'une taxonomie pour les boutons de filtre.
	 *
	 * @param string $taxonomy Nom de la taxonomie.
	 * @return array
	 */
	private function get_filter_terms( $taxonomy ) {
		if ( empty( $taxonomy ) ) {
			return [];
		}
		$terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => true ] );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}
		$result = [];
		foreach ( $terms as $term ) {
			$result[] = [ 'slug' => $term->slug, 'name' => $term->name ];
		}
		return $result;
	}

	/**
	 * Normalise une valeur meta pour comparaison / data-attribute JSON.
	 *
	 * @param mixed $raw
	 */
	private function normalize_meta_value_for_card_filter( $raw ): string {
		if ( is_array( $raw ) ) {
			$parts = [];
			foreach ( $raw as $v ) {
				if ( is_scalar( $v ) || ( is_object( $v ) && method_exists( $v, '__toString' ) ) ) {
					$s = trim( (string) $v );
					if ( $s !== '' ) {
						$parts[] = $s;
					}
				}
			}

			return implode( ', ', $parts );
		}
		if ( is_bool( $raw ) ) {
			return $raw ? '1' : '0';
		}
		if ( ! is_scalar( $raw ) ) {
			return '';
		}

		return trim( (string) $raw );
	}

	/**
	 * Variantes de clé meta (tirets / underscores), ex. secteur-activite ↔ secteur_activite.
	 *
	 * @return list<string>
	 */
	private function meta_key_lookup_candidates( string $meta_key ): array {
		$key     = sanitize_key( $meta_key );
		$aliases = [ $key ];
		$under   = str_replace( '-', '_', $key );
		$hyphen  = str_replace( '_', '-', $key );
		if ( $under !== $key ) {
			$aliases[] = $under;
		}
		if ( $hyphen !== $key ) {
			$aliases[] = $hyphen;
		}

		return array_values( array_unique( $aliases ) );
	}

	/**
	 * Lit une meta sur un post (essaie les alias de clé).
	 */
	private function read_post_meta_for_filter( int $post_id, string $meta_key ): string {
		if ( $post_id <= 0 || $meta_key === '' ) {
			return '';
		}
		foreach ( $this->meta_key_lookup_candidates( $meta_key ) as $candidate ) {
			$raw = get_post_meta( $post_id, $candidate, true );
			if ( $raw === '' || $raw === null || ( is_array( $raw ) && $raw === [] ) ) {
				continue;
			}
			$normalized = $this->normalize_meta_value_for_card_filter( $raw );
			if ( $normalized !== '' ) {
				return $normalized;
			}
		}

		return '';
	}

	/**
	 * Valeurs distinctes d’une meta sur toutes les publications publiées d’un type (pas seulement la page courante du widget).
	 *
	 * @return list<string>
	 */
	private function collect_distinct_meta_values_for_post_type( string $post_type, string $meta_key, int $limit = 1000 ): array {
		$post_type = sanitize_key( $post_type );
		$meta_key  = sanitize_key( $meta_key );
		if ( $post_type === '' || $meta_key === '' ) {
			return [];
		}

		$q = new \WP_Query(
			[
				'post_type'              => $post_type,
				'post_status'            => 'publish',
				'posts_per_page'         => max( 1, min( 2000, $limit ) ),
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
				'orderby'                => 'date',
				'order'                  => 'DESC',
			]
		);

		$seen    = [];
		$choices = [];
		foreach ( $q->posts as $post_id ) {
			$val = $this->read_post_meta_for_filter( (int) $post_id, $meta_key );
			if ( $val === '' || isset( $seen[ $val ] ) ) {
				continue;
			}
			$seen[ $val ] = true;
			$choices[]    = $val;
		}
		wp_reset_postdata();

		natcasesort( $choices );

		return array_values( $choices );
	}

	/**
	 * @param array<int, array<string, mixed>> $posts Repli si le type de publication est inconnu.
	 * @return list<array<string, mixed>>
	 */
	private function get_normalized_meta_filters_config( array $settings, string $post_type, array $posts = [] ): array {
		$rows = isset( $settings['meta_filters'] ) && is_array( $settings['meta_filters'] ) ? $settings['meta_filters'] : [];
		$out  = [];
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$key = isset( $row['meta_key'] ) ? sanitize_key( (string) $row['meta_key'] ) : '';
			if ( $key === '' ) {
				continue;
			}
			$type = isset( $row['field_type'] ) ? sanitize_key( (string) $row['field_type'] ) : 'select';
			if ( ! in_array( $type, [ 'select', 'text', 'checkbox' ], true ) ) {
				$type = 'select';
			}
			$label = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
			if ( $label === '' ) {
				$label = $key;
			}
			$choices = [];
			if ( $type === 'select' ) {
				$manual = isset( $row['select_options'] ) ? trim( (string) $row['select_options'] ) : '';
				if ( $manual !== '' ) {
					foreach ( preg_split( '/\s*,\s*/', $manual ) as $part ) {
						$part = trim( (string) $part );
						if ( $part !== '' ) {
							$choices[] = $part;
						}
					}
				} elseif ( $post_type !== '' ) {
					$choices = $this->collect_distinct_meta_values_for_post_type( $post_type, $key );
				} else {
					$seen = [];
					foreach ( $posts as $post ) {
						$pid = isset( $post['id'] ) ? (int) $post['id'] : 0;
						if ( $pid <= 0 ) {
							continue;
						}
						$val = $this->read_post_meta_for_filter( $pid, $key );
						if ( $val === '' || isset( $seen[ $val ] ) ) {
							continue;
						}
						$seen[ $val ] = true;
						$choices[]    = $val;
					}
					natcasesort( $choices );
					$choices = array_values( $choices );
				}
			}
			$icon_url = '';
			if ( ! empty( $row['filter_icon']['url'] ) ) {
				$icon_url = esc_url( (string) $row['filter_icon']['url'] );
			}
			$out[] = [
				'meta_key'    => $key,
				'field_type'  => $type,
				'label'       => $label,
				'placeholder' => isset( $row['placeholder'] ) ? (string) $row['placeholder'] : '',
				'choices'     => $choices,
				'icon_url'    => $icon_url,
			];
		}

		return $out;
	}

	/**
	 * @param list<array<string, mixed>> $filters
	 */
	private function render_meta_filters_bar( array $settings, array $filters ): void {
		if ( $filters === [] ) {
			return;
		}
		$all_text       = ! empty( $settings['filter_all_text'] ) ? $settings['filter_all_text'] : __( 'Tout', 'NOVA-addons' );
		$hide_all       = ! empty( $settings['hide_filter_all'] ) && $settings['hide_filter_all'] === 'yes';
		$hide_all_mobile = ! $hide_all && ! empty( $settings['hide_filter_all_mobile'] ) && $settings['hide_filter_all_mobile'] === 'yes';
		?>
		<div class="nova-cards-filters nova-cards-filters--meta" role="search">
			<?php if ( ! $hide_all ) : ?>
			<button type="button" class="nova-cards-filter-item nova-cards-meta-reset active<?php echo $hide_all_mobile ? ' hide-on-mobile' : ''; ?>" data-filter="*">
				<?php echo esc_html( $all_text ); ?>
			</button>
			<?php endif; ?>
			<?php foreach ( $filters as $filter ) : ?>
				<?php
				$key   = (string) $filter['meta_key'];
				$type  = (string) $filter['field_type'];
				$label = (string) $filter['label'];
				$icon  = (string) ( $filter['icon_url'] ?? '' );
				$fid   = 'nova-meta-filter-' . esc_attr( $this->get_id() ) . '-' . esc_attr( $key );
				?>
				<div class="nova-cards-meta-filter" data-meta-key="<?php echo esc_attr( $key ); ?>" data-field-type="<?php echo esc_attr( $type ); ?>">
					<?php if ( $icon !== '' ) : ?>
						<span class="nova-cards-meta-filter__icon" aria-hidden="true">
							<img src="<?php echo esc_url( $icon ); ?>" alt="" loading="lazy" decoding="async" width="22" height="22">
						</span>
					<?php endif; ?>
					<div class="nova-cards-meta-filter__control">
						<?php if ( $type === 'checkbox' ) : ?>
							<label class="nova-cards-meta-filter__checkbox-label" for="<?php echo esc_attr( $fid ); ?>">
								<input type="checkbox" class="nova-cards-meta-filter-input" id="<?php echo esc_attr( $fid ); ?>" data-meta-key="<?php echo esc_attr( $key ); ?>" data-field-type="checkbox" value="1">
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php elseif ( $type === 'text' ) : ?>
							<label class="screen-reader-text" for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $label ); ?></label>
							<input type="search" class="nova-cards-meta-filter-input" id="<?php echo esc_attr( $fid ); ?>" data-meta-key="<?php echo esc_attr( $key ); ?>" data-field-type="text" placeholder="<?php echo esc_attr( $label . ( ! empty( $filter['placeholder'] ) ? ' — ' . $filter['placeholder'] : '' ) ); ?>" autocomplete="off">
						<?php else : ?>
							<label class="screen-reader-text" for="<?php echo esc_attr( $fid ); ?>"><?php echo esc_html( $label ); ?></label>
							<select class="nova-cards-meta-filter-input" id="<?php echo esc_attr( $fid ); ?>" data-meta-key="<?php echo esc_attr( $key ); ?>" data-field-type="select">
								<option value=""><?php echo esc_html( $label ); ?></option>
								<?php foreach ( (array) ( $filter['choices'] ?? [] ) as $choice ) : ?>
									<option value="<?php echo esc_attr( (string) $choice ); ?>"><?php echo esc_html( (string) $choice ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Meta keys à exposer sur chaque card (mode filtre meta).
	 *
	 * @return list<string>
	 */
	private function get_meta_filter_keys_from_settings( array $settings ): array {
		$keys = [];
		$rows = isset( $settings['meta_filters'] ) && is_array( $settings['meta_filters'] ) ? $settings['meta_filters'] : [];
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$key = isset( $row['meta_key'] ) ? sanitize_key( (string) $row['meta_key'] ) : '';
			if ( $key !== '' && ! in_array( $key, $keys, true ) ) {
				$keys[] = $key;
			}
		}

		return $keys;
	}

	/**
	 * Get available creative background SVG files.
	 *
	 * @param string $size_type Size type: 'large' or 'small'.
	 * @return array List of SVG file URLs.
	 */
	protected function get_creative_background_svgs( $size_type = 'large' ) {
		// Use plugin constants if available, otherwise fallback
		if ( defined( 'NOVA_ADDONS_PLUGIN_DIR' ) && defined( 'NOVA_ADDONS_PLUGIN_URL' ) ) {
			$plugin_dir = NOVA_ADDONS_PLUGIN_DIR;
			$plugin_url = NOVA_ADDONS_PLUGIN_URL;
		} else {
			$plugin_path = plugin_dir_path( __FILE__ );
			$plugin_dir = dirname( dirname( $plugin_path ) );
			$plugin_url = plugin_dir_url( __FILE__ );
			$plugin_url = dirname( dirname( $plugin_url ) );
		}
		
		$svg_dir = $plugin_dir . 'assets/svg/';
		$svg_url_base = $plugin_url . 'assets/svg/';
		
		$svg_files = [];
		
		// List of SVG files to use based on size type
		if ( $size_type === 'small' ) {
			$svg_list = [
				'back-small-1.svg',
				'back-small-2.svg',
				'back-small-3.svg',
			];
		} else {
			// Default to large
			$svg_list = [
				'back-large-1.svg',
				'back-large-2.svg',
				'back-large-3.svg',
			];
		}
		
		foreach ( $svg_list as $svg_file ) {
			$file_path = $svg_dir . $svg_file;
			if ( file_exists( $file_path ) ) {
				$svg_url = $svg_url_base . $svg_file;
				$svg_url = str_replace( '\\', '/', $svg_url );
				$svg_files[] = $svg_url;
			}
		}
		
		return $svg_files;
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// ─── Source de données ─────────────────────────────────────────────
		$data_source       = ! empty( $settings['data_source'] ) ? $settings['data_source'] : 'manual';
		$items             = [];
		$filter_terms      = [];
		$meta_filters_cfg  = [];
		$filter_source     = ! empty( $settings['filter_source'] ) ? $settings['filter_source'] : 'taxonomy';
		$filters_enabled   = ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes';
		$query_posts       = [];

		if ( $data_source === 'post_type' ) {
			// Mode Post Type : construire les items depuis WP_Query
			$posts       = $this->get_posts( $settings );
			$query_posts = $posts;

			// Préparer l'icône flèche pour le bouton (une seule fois)
			$button_arrow_html = '';
			if (
				! empty( $settings['post_type_button_enable'] ) &&
				$settings['post_type_button_enable'] === 'yes' &&
				! empty( $settings['post_type_button_arrow_media']['url'] )
			) {
				$arrow_url = esc_url( $settings['post_type_button_arrow_media']['url'] );
				$button_arrow_html = '<img src="' . $arrow_url . '" alt="" class="nova-card-button-arrow" aria-hidden="true" loading="lazy">';
			}

			foreach ( $posts as $post ) {
				// Construire le texte du bouton avec icône si configurée et structure HTML comme le Carousel
				$button_label = '';
				if ( ! empty( $post['button_text'] ) ) {
					$text_escaped = esc_html( $post['button_text'] );
					$position     = ! empty( $settings['post_type_button_arrow_position'] ) ? $settings['post_type_button_arrow_position'] : 'after';
					
					$text_html = '<span class="nova-card-button-text">' . $text_escaped . '</span>';
					
					if ( ! empty( $button_arrow_html ) ) {
						$icon_html = '<span class="nova-card-button-icon">' . $button_arrow_html . '</span>';
						
						if ( 'before' === $position ) {
							$button_label = $icon_html . $text_html;
						} else {
							$button_label = $text_html . $icon_html;
						}
					} else {
						$button_label = $text_html;
					}
				}

				$items[] = [
					'card_image'    => [ 'url' => $post['image'], 'id' => '' ],
					'card_text'     => wp_kses_post( $post['text'] ),
					'card_text_2'   => wp_kses_post( $post['text2'] ),
					'card_link'     => [ 'url' => $post['link'], 'is_external' => false, 'nofollow' => false ],
					'card_button_text' => $button_label,
					'card_category' => implode( ' ', $post['terms'] ),
					'card_meta'     => isset( $post['meta'] ) && is_array( $post['meta'] ) ? $post['meta'] : [],
					// Overlay en mode post type (utilisation des paramètres globaux)
					'card_overlay_enable'   => 'yes',
					'card_overlay_mode'     => 'simple',
					'card_overlay_gradient' => 'linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)',
				];
			}

			if ( $filters_enabled ) {
				if ( $filter_source === 'meta' ) {
					$pt = ! empty( $settings['post_type'] ) ? sanitize_key( (string) $settings['post_type'] ) : '';
					$meta_filters_cfg = $this->get_normalized_meta_filters_config( $settings, $pt, $query_posts );
				} elseif ( ! empty( $settings['filter_taxonomy'] ) ) {
					$filter_terms = $this->get_filter_terms( $settings['filter_taxonomy'] );
				}
			}
		} else {
			// Mode Manuel
			$items = ! empty( $settings['cards_list'] ) ? $settings['cards_list'] : [];

			// Extraire les catégories uniques des items manuels
			if ( ! empty( $settings['enable_filters'] ) && $settings['enable_filters'] === 'yes' ) {
				$categories = [];
				foreach ( $items as $item ) {
					if ( ! empty( $item['card_category'] ) ) {
						$cat_parts = preg_split( '/[,\s]+/', $item['card_category'], -1, PREG_SPLIT_NO_EMPTY );
						foreach ( array_map( 'trim', $cat_parts ) as $cat ) {
							if ( ! empty( $cat ) && ! in_array( $cat, $categories, true ) ) {
								$categories[] = $cat;
							}
						}
					}
				}
				foreach ( $categories as $cat ) {
					$filter_terms[] = [ 'slug' => sanitize_title( $cat ), 'name' => $cat ];
				}
			}
		}

		if ( empty( $items ) ) {
			return;
		}

		// Animation config
		$animation_config = [
			'enable'                       => $settings['animation_enable'] === 'yes',
			'simultaneous'                 => $settings['animation_simultaneous'] === 'yes',
			'delay'                        => intval( $settings['animation_delay'] ),
			'duration'                     => intval( $settings['animation_duration'] ),
			'stagger'                      => intval( $settings['animation_stagger'] ),
			'translateY'                   => isset( $settings['animation_translate_y'] ) ? intval( $settings['animation_translate_y'] ) : 30,
			// Hover options for background_overlay layout
			'hoverImageScale'              => ! isset( $settings['hover_image_scale_enable'] ) || $settings['hover_image_scale_enable'] === 'yes',
			'hoverContentGsap'             => ! isset( $settings['hover_content_gsap_enable'] ) || $settings['hover_content_gsap_enable'] === 'yes',
			'hoverContentInitialPosition'  => isset( $settings['hover_content_initial_position'] ) ? $settings['hover_content_initial_position'] : 'bottom-center',
			'hoverDurationIn'              => isset( $settings['hover_duration_in'] ) ? floatval( $settings['hover_duration_in'] ) / 1000 : 0.6,
			'hoverDurationOut'             => isset( $settings['hover_duration_out'] ) ? floatval( $settings['hover_duration_out'] ) / 1000 : 0.5,
		];

		// Creative background check
		$creative_background_enabled = ! empty( $settings['creative_background_enable'] ) && $settings['creative_background_enable'] === 'yes';

		// Layout mode: background overlay (default) or image above content.
		$card_layout_mode = isset( $settings['card_layout_mode'] ) ? $settings['card_layout_mode'] : 'background_overlay';
		
		// Balises HTML pour les textes en mode post type
		$allowed_tags     = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ];
		$post_type_text_tag   = ( $data_source === 'post_type' && ! empty( $settings['post_type_text_tag'] ) && in_array( $settings['post_type_text_tag'], $allowed_tags, true ) )
			? $settings['post_type_text_tag']
			: 'div';
		$post_type_text_2_tag = ( $data_source === 'post_type' && ! empty( $settings['post_type_text_2_tag'] ) && in_array( $settings['post_type_text_2_tag'], $allowed_tags, true ) )
			? $settings['post_type_text_2_tag']
			: 'div';
		$grid_columns_mode = ! empty( $settings['grid_columns_mode'] ) ? $settings['grid_columns_mode'] : 'auto';
		$use_pixels_mode = $creative_background_enabled && $grid_columns_mode === 'pixels';
		
		if ( $use_pixels_mode ) {
			// Mode pixels: utiliser les largeurs en pixels
			$grid_columns_px = isset( $settings['grid_columns_px']['size'] ) ? intval( $settings['grid_columns_px']['size'] ) : 300;
			$grid_columns_px_tablet = isset( $settings['grid_columns_px_tablet']['size'] ) && $settings['grid_columns_px_tablet']['size'] !== '' ? intval( $settings['grid_columns_px_tablet']['size'] ) : $grid_columns_px;
			$grid_columns_px_mobile = isset( $settings['grid_columns_px_mobile']['size'] ) && $settings['grid_columns_px_mobile']['size'] !== '' ? intval( $settings['grid_columns_px_mobile']['size'] ) : 200;
			
			$grid_config = [
				'mode' => 'pixels',
				'column_width' => $grid_columns_px,
				'column_width_tablet' => $grid_columns_px_tablet,
				'column_width_mobile' => $grid_columns_px_mobile,
			];
		} else {
			// Mode auto: utiliser le nombre de colonnes
			// Si creative background est activé, utiliser grid_columns_creative, sinon grid_columns
			if ( $creative_background_enabled ) {
				$grid_columns = isset( $settings['grid_columns_creative'] ) ? $settings['grid_columns_creative'] : '3';
				$grid_columns_tablet = isset( $settings['grid_columns_creative_tablet'] ) && $settings['grid_columns_creative_tablet'] !== '' ? $settings['grid_columns_creative_tablet'] : $grid_columns;
				$grid_columns_mobile = isset( $settings['grid_columns_creative_mobile'] ) && $settings['grid_columns_creative_mobile'] !== '' ? $settings['grid_columns_creative_mobile'] : '1';
			} else {
				$grid_columns = isset( $settings['grid_columns'] ) ? $settings['grid_columns'] : '3';
				$grid_columns_tablet = isset( $settings['grid_columns_tablet'] ) && $settings['grid_columns_tablet'] !== '' ? $settings['grid_columns_tablet'] : $grid_columns;
				$grid_columns_mobile = isset( $settings['grid_columns_mobile'] ) && $settings['grid_columns_mobile'] !== '' ? $settings['grid_columns_mobile'] : '1';
			}
			
			$grid_config = [
				'mode' => 'auto',
				'columns' => $grid_columns,
				'columns_tablet' => $grid_columns_tablet,
				'columns_mobile' => $grid_columns_mobile,
			];
		}
		
		// Get gap values - handle responsive controls (horizontal and vertical)
		$grid_gap_horizontal_size = isset( $settings['grid_gap_horizontal']['size'] ) ? $settings['grid_gap_horizontal']['size'] : 30;
		$grid_gap_horizontal_unit = isset( $settings['grid_gap_horizontal']['unit'] ) ? $settings['grid_gap_horizontal']['unit'] : 'px';
		$grid_gap_vertical_size = isset( $settings['grid_gap_vertical']['size'] ) ? $settings['grid_gap_vertical']['size'] : 30;
		$grid_gap_vertical_unit = isset( $settings['grid_gap_vertical']['unit'] ) ? $settings['grid_gap_vertical']['unit'] : 'px';
		
		// Store both values for JavaScript (using column-gap for horizontal calculations)
		$grid_config['gap_horizontal'] = $grid_gap_horizontal_size . $grid_gap_horizontal_unit;
		$grid_config['gap_vertical'] = $grid_gap_vertical_size . $grid_gap_vertical_unit;
		// Keep 'gap' for backward compatibility (use horizontal as default)
		$grid_config['gap'] = $grid_gap_horizontal_size . $grid_gap_horizontal_unit;
		
		// Auto-center for pixels mode
		if ( $use_pixels_mode ) {
			$auto_center = ! empty( $settings['grid_justify_content_pixels_center'] ) && $settings['grid_justify_content_pixels_center'] === 'yes';
			$grid_config['auto_center'] = $auto_center;
		}
		
		$svg_backgrounds = [];
		if ( $creative_background_enabled ) {
			$size_type = ! empty( $settings['creative_background_size_type'] ) ? $settings['creative_background_size_type'] : 'large';
			$svg_backgrounds = $this->get_creative_background_svgs( $size_type );
		}

		// Hover config for creative background
		$hover_config = [];
		if ( $creative_background_enabled ) {
			$hover_enabled = ! empty( $settings['creative_background_hover_enable'] ) && $settings['creative_background_hover_enable'] === 'yes';
			$hover_transformations = ! empty( $settings['creative_background_hover_transformations'] ) ? $settings['creative_background_hover_transformations'] : [];
			$hover_duration = ! empty( $settings['creative_background_hover_duration']['size'] ) ? intval( $settings['creative_background_hover_duration']['size'] ) : 500;
			
			$hover_config = [
				'enable' => $hover_enabled,
				'transformations' => $hover_transformations,
				'duration' => $hover_duration,
			];
		}

		$grid_data_attrs = '';
		if ( isset( $grid_config['mode'] ) && $grid_config['mode'] === 'auto' ) {
			$initial_columns = isset( $grid_config['columns'] ) ? intval( $grid_config['columns'] ) : 3;
			if ( $initial_columns < 1 ) {
				$initial_columns = 3;
			}
			$grid_data_attrs .= ' data-grid-columns="' . esc_attr( $initial_columns ) . '"';
		} elseif ( isset( $grid_config['mode'] ) && $grid_config['mode'] === 'pixels' ) {
			$grid_data_attrs .= ' data-grid-mode="pixels"';
		}

		?>
		<div class="nova-cards-widget<?php echo $creative_background_enabled ? ' creative-background-enabled' : ''; ?>" 
			 data-animation-config="<?php echo esc_attr( wp_json_encode( $animation_config ) ); ?>"
			 data-grid-config="<?php echo esc_attr( wp_json_encode( $grid_config ) ); ?>"
			 data-hover-config="<?php echo esc_attr( wp_json_encode( $hover_config ) ); ?>"
			 data-enable-filters="<?php echo $filters_enabled ? 'yes' : 'no'; ?>"
			 data-filter-mode="<?php echo esc_attr( $filters_enabled ? $filter_source : '' ); ?>"
			 data-hide-filter-all="<?php echo ( ! empty( $settings['hide_filter_all'] ) && $settings['hide_filter_all'] === 'yes' ) ? 'yes' : 'no'; ?>"
			 data-hide-filter-all-mobile="<?php echo ( ! empty( $settings['hide_filter_all_mobile'] ) && $settings['hide_filter_all_mobile'] === 'yes' ) ? 'yes' : 'no'; ?>">

			<?php
			$hide_filter_all_btn = ! empty( $settings['hide_filter_all'] ) && $settings['hide_filter_all'] === 'yes';
			$hide_filter_all_mob = ! $hide_filter_all_btn && ! empty( $settings['hide_filter_all_mobile'] ) && $settings['hide_filter_all_mobile'] === 'yes';
			if ( $filters_enabled && $filter_source === 'meta' && $meta_filters_cfg !== [] ) {
				$this->render_meta_filters_bar( $settings, $meta_filters_cfg );
			} elseif ( $filters_enabled && $filter_source === 'taxonomy' && $filter_terms !== [] ) {
				?>
				<div class="nova-cards-filters nova-cards-filters--taxonomy">
					<?php if ( ! $hide_filter_all_btn ) : ?>
					<button type="button" class="nova-cards-filter-item active<?php echo $hide_filter_all_mob ? ' hide-on-mobile' : ''; ?>" data-filter="*">
						<?php echo esc_html( ! empty( $settings['filter_all_text'] ) ? $settings['filter_all_text'] : __( 'Tout', 'NOVA-addons' ) ); ?>
					</button>
					<?php endif; ?>
					<?php foreach ( $filter_terms as $term ) : ?>
						<button type="button" class="nova-cards-filter-item" data-filter=".<?php echo esc_attr( $term['slug'] ); ?>">
							<?php echo esc_html( $term['name'] ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<?php
			}
			?>

			<div class="nova-cards-grid"<?php echo $grid_data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php foreach ( $items as $index => $item ) : ?>

					<?php
					$card_image_url = '';
					$creative_svg_url = '';
					
					$svg_transforms = [];
					if ( $creative_background_enabled && ! empty( $svg_backgrounds ) ) {
						// Use random SVG from available backgrounds (truly random each time)
						$random_index = array_rand( $svg_backgrounds );
						$creative_svg_url = $svg_backgrounds[ $random_index ];
						
						// Apply random transformations if enabled (truly random each time)
						$has_rotation = ! empty( $settings['creative_background_random_rotation'] ) && $settings['creative_background_random_rotation'] === 'yes';
						$has_flip = ! empty( $settings['creative_background_random_flip'] ) && $settings['creative_background_random_flip'] === 'yes';
						
						if ( $has_rotation || $has_flip ) {
							// Use truly random values for each card on each page load
							$random_rotation = $has_rotation ? ( mt_rand( 0, 1 ) === 1 ) : false;
							$random_flip = $has_flip ? ( mt_rand( 0, 1 ) === 1 ) : false;
							
							if ( $has_rotation && $has_flip ) {
								// Both options enabled: random combination
								if ( $random_flip ) {
									$svg_transforms[] = 'scaleX(-1)';
								}
								if ( $random_rotation ) {
									$svg_transforms[] = 'rotate(180deg)';
								}
							} elseif ( $has_rotation ) {
								// Only rotation enabled
								if ( $random_rotation ) {
									$svg_transforms[] = 'rotate(180deg)';
								}
							} elseif ( $has_flip ) {
								// Only flip enabled
								if ( $random_flip ) {
									$svg_transforms[] = 'scaleX(-1)';
								}
							}
						}
					} elseif ( ! $creative_background_enabled ) {
						// Use normal image if creative background is disabled
						if ( ! empty( $item['card_image']['id'] ) ) {
							$card_image_url = wp_get_attachment_image_url( intval( $item['card_image']['id'] ), 'full' );
						} elseif ( ! empty( $item['card_image']['url'] ) ) {
							$card_image_url = $item['card_image']['url'];
						}
					}

					$card_link = isset( $item['card_link'] ) ? $item['card_link'] : [];
					$card_url_raw = ! empty( $card_link['url'] ) ? $card_link['url'] : '';
					$card_url = ! empty( $card_url_raw ) ? esc_url( $card_url_raw ) : '';
					$card_text_plain = ! empty( $item['card_text'] ) ? wp_strip_all_tags( $item['card_text'] ) : '';

					$button_text = isset( $item['card_button_text'] ) ? trim( $item['card_button_text'] ) : '';
					$button_link = isset( $item['card_button_link'] ) ? $item['card_button_link'] : [];
					$button_link_data = [];
					if ( ! empty( $button_link['url'] ) ) {
						$button_link_data = $button_link;
					} elseif ( ! empty( $card_link['url'] ) ) {
						$button_link_data = $card_link;
					}

					$has_button = ! empty( $button_text );

					$card_href = ! empty( $button_link_data['url'] ) ? $button_link_data : $card_link;
					$wrap_with_link = ! empty( $card_href['url'] );

					if ( $has_button ) {
						$button_key = 'nova-card-button-' . $index;
						$this->add_render_attribute( $button_key, 'class', 'nova-card-button' );
					}

					$card_key = 'nova-card-item-' . $index;
					$card_classes = [ 'nova-card-item' ];
					if ( ! empty( $card_url_raw ) ) {
						$card_classes[] = 'has-link';
					}
					if ( $has_button ) {
						$card_classes[] = 'has-button';
					}
					if ( $creative_background_enabled ) {
						$card_classes[] = 'has-creative-background';
					}
					if ( ! empty( $card_image_url ) && 'background_overlay' === $card_layout_mode ) {
						$card_classes[] = 'has-background-image';
					}
						if ( 'image_above_content' === $card_layout_mode && ! $creative_background_enabled ) {
						$card_classes[] = 'nova-card-item--image-top';
					}

					// Classes de filtre basées sur card_category
					if ( ! empty( $item['card_category'] ) ) {
						$cat_parts = preg_split( '/[,\s]+/', $item['card_category'], -1, PREG_SPLIT_NO_EMPTY );
						foreach ( array_map( 'trim', $cat_parts ) as $cat ) {
							if ( ! empty( $cat ) ) {
								$card_classes[] = sanitize_title( $cat );
							}
						}
					}

					$this->add_render_attribute( $card_key, 'class', $card_classes );
					$this->add_render_attribute( $card_key, 'data-card-index', $index );

					if ( ! empty( $item['card_meta'] ) && is_array( $item['card_meta'] ) ) {
						$this->add_render_attribute(
							$card_key,
							'data-card-meta',
							wp_json_encode( $item['card_meta'], JSON_UNESCAPED_UNICODE )
						);
					}

					if ( ! empty( $item['card_background_color'] ) ) {
						$this->add_render_attribute( $card_key, 'style', 'background-color: ' . esc_attr( $item['card_background_color'] ) . ';' );
					}
					
					// Add SVG URL as data attribute for aspect ratio calculation
					if ( $creative_background_enabled && ! empty( $creative_svg_url ) ) {
						$this->add_render_attribute( $card_key, 'data-svg-url', esc_url( $creative_svg_url ) );
					}

					if ( $wrap_with_link ) {
						$this->add_link_attributes( $card_key, $card_href );
						if ( ! empty( $card_text_plain ) ) {
							$this->add_render_attribute( $card_key, 'aria-label', $card_text_plain );
						}
					}
					
					// Overlay configuration - from Style section (applied to all cards)
					// Disable overlay if creative background is enabled
					$overlay_enable = ! $creative_background_enabled && ! empty( $settings['overlay_enable'] ) && $settings['overlay_enable'] === 'yes';
					$overlay_style = '';
					
					if ( $overlay_enable ) {
						$overlay_mode = ! empty( $settings['overlay_mode'] ) ? $settings['overlay_mode'] : 'simple';
						
						if ( $overlay_mode === 'simple' ) {
							// Simple mode: use direct CSS gradient
							$overlay_gradient = ! empty( $settings['overlay_gradient'] ) ? $settings['overlay_gradient'] : 'linear-gradient(180deg, rgba(0, 0, 0, 0) 40.44%, rgba(0, 0, 0, 0.7) 100%)';
							$overlay_style = 'background: ' . esc_attr( $overlay_gradient ) . ';';
						} else {
							// Advanced mode: build from controls
							$overlay_background = ! empty( $settings['overlay_background'] ) ? $settings['overlay_background'] : 'gradient';
							
							if ( $overlay_background === 'gradient' ) {
								// Build gradient from controls
								$gradient_type = ! empty( $settings['overlay_gradient_type'] ) ? $settings['overlay_gradient_type'] : 'linear';
								$gradient_angle = ! empty( $settings['overlay_gradient_angle']['size'] ) ? $settings['overlay_gradient_angle']['size'] : 180;
								$color_a = ! empty( $settings['overlay_gradient_color_a'] ) ? $settings['overlay_gradient_color_a'] : 'rgba(0, 0, 0, 0)';
								$color_a_location = ! empty( $settings['overlay_gradient_color_a_location']['size'] ) ? $settings['overlay_gradient_color_a_location']['size'] : 40.44;
								$color_b = ! empty( $settings['overlay_gradient_color_b'] ) ? $settings['overlay_gradient_color_b'] : 'rgba(0, 0, 0, 0.7)';
								$color_b_location = ! empty( $settings['overlay_gradient_color_b_location']['size'] ) ? $settings['overlay_gradient_color_b_location']['size'] : 100;
								
								if ( $gradient_type === 'linear' ) {
									$overlay_style = sprintf(
										'background: linear-gradient(%sdeg, %s %s%%, %s %s%%);',
										esc_attr( $gradient_angle ),
										esc_attr( $color_a ),
										esc_attr( $color_a_location ),
										esc_attr( $color_b ),
										esc_attr( $color_b_location )
									);
								} else {
									$overlay_style = sprintf(
										'background: radial-gradient(circle, %s %s%%, %s %s%%);',
										esc_attr( $color_a ),
										esc_attr( $color_a_location ),
										esc_attr( $color_b ),
										esc_attr( $color_b_location )
									);
								}
							} else {
								// Solid color
								$overlay_color = ! empty( $settings['overlay_color'] ) ? $settings['overlay_color'] : 'rgba(0, 0, 0, 0.5)';
								$overlay_style = 'background: ' . esc_attr( $overlay_color ) . ';';
							}
						}
					}

					if ( $overlay_enable && ! empty( $overlay_style ) && 'background_overlay' === $card_layout_mode ) {
						$card_classes[] = 'has-overlay-enabled';
					}
					$this->add_render_attribute( $card_key, 'class', $card_classes );
					?>
					<?php if ( $wrap_with_link ) : ?>
						<a <?php echo $this->get_render_attribute_string( $card_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php else : ?>
						<div <?php echo $this->get_render_attribute_string( $card_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php endif; ?>

						<?php if ( $creative_background_enabled && ! empty( $creative_svg_url ) && 'background_overlay' === $card_layout_mode ) : ?>
							<?php
							$bg_size = ! empty( $settings['creative_background_size'] ) ? $settings['creative_background_size'] : 'contain';
							
							// Build transform string if any transformations are applied
							$transform_style = '';
							if ( ! empty( $svg_transforms ) ) {
								$transform_style = 'transform: ' . implode( ' ', $svg_transforms ) . ';';
							}
							
							// Determine SVG color: card-specific color > global color > no color (original)
							$svg_color = '';
							if ( ! empty( $item['card_svg_color'] ) ) {
								$svg_color = $item['card_svg_color'];
							} elseif ( ! empty( $settings['creative_background_color'] ) ) {
								$svg_color = $settings['creative_background_color'];
							}
							
							// Build style for SVG background
							if ( ! empty( $svg_color ) ) {
								// Use mask technique to color the SVG
								$bg_style = sprintf(
									'background-color: %s; -webkit-mask-image: url(\'%s\'); mask-image: url(\'%s\'); -webkit-mask-size: %s; mask-size: %s; -webkit-mask-position: center; mask-position: center; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; %s',
									esc_attr( $svg_color ),
									esc_url( $creative_svg_url ),
									esc_url( $creative_svg_url ),
									esc_attr( $bg_size ),
									esc_attr( $bg_size ),
									$transform_style
								);
							} else {
								// Use original SVG as background-image
								$bg_style = sprintf(
									'background-image: url(\'%s\'); background-size: %s; background-position: center; background-repeat: no-repeat; %s',
									esc_url( $creative_svg_url ),
									esc_attr( $bg_size ),
									$transform_style
								);
							}
							?>
							<div class="nova-card-background nova-card-creative-background" style="<?php echo $bg_style; ?>"></div>
						<?php elseif ( ! empty( $card_image_url ) && 'background_overlay' === $card_layout_mode ) : ?>
							<div class="nova-card-background" style="background-image: url('<?php echo esc_url( $card_image_url ); ?>');"></div>
						<?php elseif ( ! empty( $card_image_url ) && 'image_above_content' === $card_layout_mode ) : ?>
							<div class="nova-card-image-wrapper">
								<img src="<?php echo esc_url( $card_image_url ); ?>" alt="<?php echo esc_attr( $card_text_plain ); ?>" loading="lazy" />
							</div>
						<?php endif; ?>
						
						<?php if ( $overlay_enable && ! empty( $overlay_style ) && 'background_overlay' === $card_layout_mode ) : ?>
							<div class="nova-card-overlay" style="<?php echo $overlay_style; ?>"></div>
						<?php endif; ?>
						
						<div class="nova-card-content">
							<?php if ( ! empty( $item['card_text'] ) || ! empty( $item['card_text_2'] ) ) : ?>
								<div class="nova-card-text nova-card-text">
									<?php if ( ! empty( $item['card_text'] ) ) : ?>
										<div class="nova-card-text nova-card-text-1">

											<<?php echo esc_html( $post_type_text_tag ); ?>>
												<?php echo wp_kses_post( $item['card_text'] ); ?>
											</<?php echo esc_html( $post_type_text_tag ); ?>>
									</div>

									<?php endif; ?>
									
									<?php if ( ! empty( $item['card_text_2'] ) ) : ?>
										<<?php echo esc_html( $post_type_text_2_tag ); ?> class="nova-card-text nova-card-text-2">
											<?php echo wp_kses_post( $item['card_text_2'] ); ?>
										</<?php echo esc_html( $post_type_text_2_tag ); ?>>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( $has_button ) : ?>
								<div class="nova-card-button-wrap">
									<span <?php echo $this->get_render_attribute_string( $button_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
										<?php echo wp_kses_post( $button_text ); // SVG arrow may be present, already sanitized via wp_kses() ?>
									</span>
								</div>
							<?php endif; ?>
						</div>
					<?php if ( $wrap_with_link ) : ?>
						</a>
					<?php else : ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		
		// Add inline styles to force !important on button typography
		// This ensures typography styles override theme styles
		$widget_id = $this->get_id();
		
		// Elementor stores typography data with prefixed keys
		$has_typography = false;
		$inline_css = "<style id='nova-cards-button-typography-{$widget_id}'>";
		$inline_css .= ".elementor-element-{$widget_id} .nova-card-button {";
		
		// Force !important on all typography properties
		if ( ! empty( $settings['button_typography_font_family'] ) ) {
			$inline_css .= "font-family: " . esc_attr( $settings['button_typography_font_family'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_font_size']['size'] ) ) {
			$size = $settings['button_typography_font_size']['size'];
			$unit = $settings['button_typography_font_size']['unit'] ?? 'px';
			$inline_css .= "font-size: " . esc_attr( $size . $unit ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_font_weight'] ) ) {
			$inline_css .= "font-weight: " . esc_attr( $settings['button_typography_font_weight'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_text_transform'] ) ) {
			$inline_css .= "text-transform: " . esc_attr( $settings['button_typography_text_transform'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_font_style'] ) ) {
			$inline_css .= "font-style: " . esc_attr( $settings['button_typography_font_style'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_text_decoration'] ) ) {
			$inline_css .= "text-decoration: " . esc_attr( $settings['button_typography_text_decoration'] ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_line_height']['size'] ) ) {
			$size = $settings['button_typography_line_height']['size'];
			$unit = $settings['button_typography_line_height']['unit'] ?? '';
			$inline_css .= "line-height: " . esc_attr( $size . $unit ) . " !important;";
			$has_typography = true;
		}
		if ( ! empty( $settings['button_typography_letter_spacing']['size'] ) ) {
			$size = $settings['button_typography_letter_spacing']['size'];
			$unit = $settings['button_typography_letter_spacing']['unit'] ?? 'px';
			$inline_css .= "letter-spacing: " . esc_attr( $size . $unit ) . " !important;";
			$has_typography = true;
		}
		
		$inline_css .= "}";
		$inline_css .= "</style>";
		
		if ( $has_typography ) {
			echo $inline_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

