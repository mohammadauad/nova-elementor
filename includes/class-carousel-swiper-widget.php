<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget NOVA Carousel (Swiper)
 *
 * Clone du widget NOVA Carousel existant mais utilisant Swiper.js
 * pour le slider au lieu de Owl Carousel.
 *
 * Tous les contrôles Elementor (contenu, requête, réglages du slider, style)
 * sont hérités de Carousel_Widget. Seule la configuration JS change.
 */
class Carousel_Swiper_Widget extends Carousel_Widget {

	/**
	 * Nom unique du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-carousel-swiper';
	}

	/**
	 * Titre affiché dans le panneau Elementor.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Carousel (Swiper)', 'NOVA-addons' );
	}

	/**
	 * Icône du widget (même que le carousel classique).
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-slider';
	}

	/**
	 * Catégorie du widget.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'NOVA-addons' ];
	}

	/**
	 * Scripts nécessaires (version Swiper).
	 *
	 * On dépend de Swiper (fourni par Elementor / CDN) et de notre script
	 * spécifique qui mappe les réglages Elementor vers Swiper.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-carousel-swiper-script' ];
	}

	/**
	 * Styles nécessaires.
	 *
	 * On réutilise le même fichier CSS que le carousel existant, qui
	 * est neutre par rapport à la librairie JS utilisée.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-carousel-style' ];
	}

	/**
	 * Ajoute des contrôles spécifiques à Swiper en plus de ceux du carousel classique.
	 */
	protected function register_controls() {
		// Garder tous les contrôles du widget Carousel original
		parent::register_controls();

		// Section dédiée aux options Swiper avancées
		$this->start_controls_section(
			'section_swiper_settings',
			[
				'label' => esc_html__( 'Options Swiper avancées', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		// --- Direction ---
		$this->add_control(
			'swiper_direction',
			[
				'label'   => esc_html__( 'Direction', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => esc_html__( 'Horizontal', 'NOVA-addons' ),
					'vertical'   => esc_html__( 'Vertical', 'NOVA-addons' ),
				],
				'frontend_available' => true,
			]
		);

		// --- Effet de transition ---
		$this->add_control(
			'swiper_effect',
			[
				'label'   => esc_html__( 'Effet de transition', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide'     => esc_html__( 'Slide (par défaut)', 'NOVA-addons' ),
					'fade'      => esc_html__( 'Fade', 'NOVA-addons' ),
					'cube'      => esc_html__( 'Cube 3D', 'NOVA-addons' ),
					'coverflow' => esc_html__( 'Coverflow 3D', 'NOVA-addons' ),
					'flip'      => esc_html__( 'Flip', 'NOVA-addons' ),
					'creative'  => esc_html__( 'Creative', 'NOVA-addons' ),
					'cards'     => esc_html__( 'Cards', 'NOVA-addons' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_centered_slides',
			[
				'label'        => esc_html__( 'Slides centrées', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_slides_per_view_mode',
			[
				'label'   => esc_html__( 'Mode largeur des slides', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fixed',
				'options' => [
					'fixed' => esc_html__( 'Fixe (utilise « Slides à afficher »)', 'NOVA-addons' ),
					'auto'  => esc_html__( 'Auto (slidesPerView: auto)', 'NOVA-addons' ),
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_grab_cursor',
			[
				'label'        => esc_html__( 'Curseur main (grab)', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'frontend_available' => true,
			]
		);

		// --- Free Mode ---
		$this->add_control(
			'swiper_free_mode',
			[
				'label'        => esc_html__( 'Free Mode', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'description' => esc_html__( 'Permet de faire défiler librement sans s\'arrêter sur les slides', 'NOVA-addons' ),
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_free_mode_sticky',
			[
				'label'        => esc_html__( 'Free Mode - Snap aux slides', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'condition'    => [
					'swiper_free_mode' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_free_mode_momentum',
			[
				'label'        => esc_html__( 'Free Mode - Momentum', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_free_mode' => 'yes',
				],
				'description' => esc_html__( 'Continue le mouvement après le relâchement', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		// --- Rewind ---
		$this->add_control(
			'swiper_rewind',
			[
				'label'        => esc_html__( 'Rewind', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'description' => esc_html__( 'Retourne au début à la fin (incompatible avec loop)', 'NOVA-addons' ),
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		// --- Slide to clicked slide ---
		$this->add_control(
			'swiper_slide_to_clicked_slide',
			[
				'label'        => esc_html__( 'Slide au clic', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'description' => esc_html__( 'Cliquer sur une slide la rend active', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		// --- Allow touch move ---
		$this->add_control(
			'swiper_allow_touch_move',
			[
				'label'        => esc_html__( 'Autoriser le drag/swipe', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'frontend_available' => true,
			]
		);

		// --- Simulate touch (desktop) ---
		$this->add_control(
			'swiper_simulate_touch',
			[
				'label'        => esc_html__( 'Simuler le touch (desktop)', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'description' => esc_html__( 'Permet de glisser avec la souris sur desktop', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		// --- Watch overflow ---
		$this->add_control(
			'swiper_watch_overflow',
			[
				'label'        => esc_html__( 'Désactiver si pas assez de slides', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'description' => esc_html__( 'Désactive Swiper si moins de slides que slidesPerView', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		// --- Auto height ---
		$this->add_control(
			'swiper_auto_height',
			[
				'label'        => esc_html__( 'Hauteur automatique', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'description' => esc_html__( 'Adapte la hauteur à la slide active', 'NOVA-addons' ),
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		// --- Keyboard control ---
		$this->add_control(
			'swiper_keyboard_enabled',
			[
				'label'        => esc_html__( 'Navigation clavier', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_keyboard_only_in_viewport',
			[
				'label'        => esc_html__( 'Clavier uniquement dans le viewport', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_keyboard_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_keyboard_page_up_down',
			[
				'label'        => esc_html__( 'Clavier Page Up/Down', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_keyboard_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		// --- Mousewheel control ---
		$this->add_control(
			'swiper_mousewheel_enabled',
			[
				'label'        => esc_html__( 'Navigation molette souris', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_mousewheel_invert',
			[
				'label'        => esc_html__( 'Molette inversée', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'condition'    => [
					'swiper_mousewheel_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_mousewheel_force_to_axis',
			[
				'label'        => esc_html__( 'Forcer sur l\'axe', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'condition'    => [
					'swiper_mousewheel_enabled' => 'yes',
				],
				'description' => esc_html__( 'Molette horizontale uniquement en mode horizontal', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_mousewheel_sensitivity',
			[
				'label'   => esc_html__( 'Sensibilité molette', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0.1,
						'max'  => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1,
				],
				'condition' => [
					'swiper_mousewheel_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		// --- Pagination type ---
		$this->add_control(
			'swiper_pagination_type',
			[
				'label'   => esc_html__( 'Type de pagination', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'bullets',
				'options' => [
					'bullets'     => esc_html__( 'Bullets (points)', 'NOVA-addons' ),
					'fraction'    => esc_html__( 'Fraction (1/5)', 'NOVA-addons' ),
					'progressbar' => esc_html__( 'Barre de progression', 'NOVA-addons' ),
					'custom'      => esc_html__( 'Personnalisée', 'NOVA-addons' ),
				],
				'separator' => 'before',
				'condition' => [
					'show_dots' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_pagination_dynamic_bullets',
			[
				'label'        => esc_html__( 'Bullets dynamiques', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'condition'    => [
					'show_dots' => 'yes',
					'swiper_pagination_type' => 'bullets',
				],
				'description' => esc_html__( 'Affiche seulement quelques bullets à la fois', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_pagination_clickable',
			[
				'label'        => esc_html__( 'Bullets cliquables', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'show_dots' => 'yes',
					'swiper_pagination_type' => 'bullets',
				],
				'frontend_available' => true,
			]
		);

		// --- Scrollbar ---
		$this->add_control(
			'swiper_scrollbar_enabled',
			[
				'label'        => esc_html__( 'Scrollbar', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_scrollbar_draggable',
			[
				'label'        => esc_html__( 'Scrollbar draggable', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_scrollbar_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_scrollbar_hide',
			[
				'label'        => esc_html__( 'Masquer après interaction', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_scrollbar_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		// --- Lazy loading ---
		$this->add_control(
			'swiper_lazy_enabled',
			[
				'label'        => esc_html__( 'Lazy loading images', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'separator'    => 'before',
				'description' => esc_html__( 'Charge les images uniquement quand elles sont visibles', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_lazy_load_prev_next',
			[
				'label'        => esc_html__( 'Précharger slides précédent/suivant', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_lazy_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		// --- Parallax ---
		$this->add_control(
			'swiper_parallax_enabled',
			[
				'label'        => esc_html__( 'Effet Parallax', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'separator'    => 'before',
				'description' => esc_html__( 'Active les effets parallax sur les éléments avec data-swiper-parallax', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		// --- Grid (multi-row) ---
		$this->add_control(
			'swiper_grid_enabled',
			[
				'label'        => esc_html__( 'Mode Grid (multi-lignes)', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'no',
				'separator'    => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_grid_rows',
			[
				'label'   => esc_html__( 'Nombre de lignes', 'NOVA-addons' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 2,
				'min'     => 1,
				'max'     => 4,
				'step'    => 1,
				'condition' => [
					'swiper_grid_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_grid_fill',
			[
				'label'   => esc_html__( 'Remplissage', 'NOVA-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'column',
				'options' => [
					'column' => esc_html__( 'Par colonne', 'NOVA-addons' ),
					'row'    => esc_html__( 'Par ligne', 'NOVA-addons' ),
				],
				'condition' => [
					'swiper_grid_enabled' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		// --- Options Coverflow Effect ---
		$this->add_control(
			'swiper_coverflow_heading',
			[
				'label'     => esc_html__( 'Paramètres Coverflow', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'swiper_effect' => 'coverflow',
				],
			]
		);

		$this->add_control(
			'swiper_coverflow_rotate',
			[
				'label'   => esc_html__( 'Rotation (degrés)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 90,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 50,
				],
				'condition' => [
					'swiper_effect' => 'coverflow',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_coverflow_stretch',
			[
				'label'   => esc_html__( 'Stretch (px)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 0,
				],
				'condition' => [
					'swiper_effect' => 'coverflow',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_coverflow_depth',
			[
				'label'   => esc_html__( 'Depth (px)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 200,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 100,
				],
				'condition' => [
					'swiper_effect' => 'coverflow',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_coverflow_scale',
			[
				'label'   => esc_html__( 'Scale', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 2,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1,
				],
				'condition' => [
					'swiper_effect' => 'coverflow',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_coverflow_slide_shadows',
			[
				'label'        => esc_html__( 'Ombres sur les slides', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_effect' => 'coverflow',
				],
				'frontend_available' => true,
			]
		);

		// --- Options Cards Effect ---
		$this->add_control(
			'swiper_cards_heading',
			[
				'label'     => esc_html__( 'Paramètres Cards', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'swiper_effect' => 'cards',
				],
			]
		);

		$this->add_control(
			'swiper_cards_per_slide_rotate',
			[
				'label'   => esc_html__( 'Rotation par slide (degrés)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 15,
						'step' => 0.5,
					],
				],
				'default' => [
					'size' => 2,
				],
				'condition' => [
					'swiper_effect' => 'cards',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_cards_per_slide_offset',
			[
				'label'   => esc_html__( 'Offset par slide (px)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 8,
				],
				'condition' => [
					'swiper_effect' => 'cards',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_cards_rotate',
			[
				'label'        => esc_html__( 'Activer la rotation', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_effect' => 'cards',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_cards_slide_shadows',
			[
				'label'        => esc_html__( 'Ombres sur les slides', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'condition'    => [
					'swiper_effect' => 'cards',
				],
				'frontend_available' => true,
			]
		);

		// ═══════════════════════════════════════════════════════════════
		// --- Mode Fan (rotation aléatoire) ---
		// ═══════════════════════════════════════════════════════════════
		$this->add_control(
			'swiper_fan_heading',
			[
				'label'     => esc_html__( '✦ Mode Fan (rotation aléatoire)', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'swiper_fan_enabled',
			[
				'label'       => esc_html__( 'Activer le mode Fan', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'no',
				'description' => esc_html__( 'Applique une rotation aléatoire sur chaque carte. Au hover, la carte se redresse et s\'agrandit.', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_angle_min',
			[
				'label'   => esc_html__( 'Angle min (degrés)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [ 'min' => -30, 'max' => 0, 'step' => 1 ],
				],
				'default' => [ 'size' => -5 ],
				'condition' => [ 'swiper_fan_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_angle_max',
			[
				'label'   => esc_html__( 'Angle max (degrés)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [ 'min' => 0, 'max' => 30, 'step' => 1 ],
				],
				'default' => [ 'size' => 5 ],
				'condition' => [ 'swiper_fan_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_hover_scale',
			[
				'label'   => esc_html__( 'Scale au hover', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [ 'min' => 1, 'max' => 1.5, 'step' => 0.05 ],
				],
				'default' => [ 'size' => 1.15 ],
				'condition' => [ 'swiper_fan_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_transition_duration',
			[
				'label'   => esc_html__( 'Durée transition hover (ms)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [ 'min' => 100, 'max' => 1000, 'step' => 50 ],
				],
				'default' => [ 'size' => 400 ],
				'condition' => [ 'swiper_fan_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_overlap',
			[
				'label'       => esc_html__( 'Chevauchement (spaceBetween négatif, px)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [ 'min' => -100, 'max' => 0, 'step' => 5 ],
				],
				'default'     => [ 'size' => -20 ],
				'description' => esc_html__( 'Valeur négative = les cartes se chevauchent. Remplace spaceBetween quand le mode Fan est actif.', 'NOVA-addons' ),
				'condition'   => [ 'swiper_fan_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_overflow_visible',
			[
				'label'       => esc_html__( 'Overflow visible (ne pas couper les cartes)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'yes',
				'condition'   => [ 'swiper_fan_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		// ═══════════════════════════════════════════════════════════════
		// --- Mode Fan Deck (rotation alternée, style photo de groupe) ---
		// ═══════════════════════════════════════════════════════════════
		$this->add_control(
			'swiper_fan_deck_heading',
			[
				'label'     => esc_html__( '✦ Mode Fan Deck (rotation alternée)', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'swiper_fan_deck_enabled',
			[
				'label'       => esc_html__( 'Activer le mode Fan Deck', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'no',
				'description' => esc_html__( 'Rotation alternée pair/impair selon la position. Idéal pour un effet "photo de groupe" ou "deck de cartes".', 'NOVA-addons' ),
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_angle',
			[
				'label'       => esc_html__( 'Angle de base (degrés)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => 0, 'max' => 20, 'step' => 0.5 ] ],
				'default'     => [ 'size' => 3 ],
				'description' => esc_html__( 'Angle appliqué à chaque carte. Les cartes paires penchent à droite, les impaires à gauche.', 'NOVA-addons' ),
				'condition'   => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_angle_step',
			[
				'label'       => esc_html__( 'Incrément d\'angle par position', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => 0, 'max' => 5, 'step' => 0.5 ] ],
				'default'     => [ 'size' => 1 ],
				'description' => esc_html__( 'Augmente l\'angle selon la distance au centre. 0 = angle identique pour toutes les cartes.', 'NOVA-addons' ),
				'condition'   => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_overlap',
			[
				'label'       => esc_html__( 'Chevauchement (px)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => -150, 'max' => 50, 'step' => 5 ] ],
				'default'     => [ 'size' => -20 ],
				'description' => esc_html__( 'Valeur négative = les cartes se chevauchent (spaceBetween négatif).', 'NOVA-addons' ),
				'condition'   => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_hover_scale',
			[
				'label'   => esc_html__( 'Scale au hover', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [ 'px' => [ 'min' => 1, 'max' => 1.5, 'step' => 0.05 ] ],
				'default' => [ 'size' => 1.08 ],
				'condition' => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_hover_rotation_range',
			[
				'label'       => esc_html__( 'Amplitude rotation hover (±degrés)', 'NOVA-addons' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [ 'px' => [ 'min' => 0, 'max' => 15, 'step' => 0.5 ] ],
				'default'     => [ 'size' => 2 ],
				'description' => esc_html__( 'Au hover, la carte tourne d\'un angle aléatoire dans cette plage. Ex: 2 = entre -2° et +2°.', 'NOVA-addons' ),
				'condition'   => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_hover_shadow',
			[
				'label'       => esc_html__( 'Ombre au hover', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'yes',
				'condition'   => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_transition_duration',
			[
				'label'   => esc_html__( 'Durée transition (ms)', 'NOVA-addons' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [ 'px' => [ 'min' => 100, 'max' => 800, 'step' => 50 ] ],
				'default' => [ 'size' => 400 ],
				'condition' => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_center_upright',
			[
				'label'       => esc_html__( 'Carte centrale droite', 'NOVA-addons' ),
				'type'        => Controls_Manager::SWITCHER,
				'label_on'    => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'   => esc_html__( 'Non', 'NOVA-addons' ),
				'default'     => 'yes',
				'description' => esc_html__( 'La carte au centre reste à 0° (droite). Nécessite centeredSlides activé.', 'NOVA-addons' ),
				'condition'   => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'swiper_fan_deck_overflow_visible',
			[
				'label'   => esc_html__( 'Overflow visible', 'NOVA-addons' ),
				'type'    => Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default'   => 'yes',
				'condition' => [ 'swiper_fan_deck_enabled' => 'yes' ],
				'frontend_available' => true,
			]
		);

		// --- Options Fade Effect ---
		$this->add_control(
			'swiper_fade_heading',
			[
				'label'     => esc_html__( 'Paramètres Fade', 'NOVA-addons' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'swiper_effect' => 'fade',
				],
			]
		);

		$this->add_control(
			'swiper_fade_cross_fade',
			[
				'label'        => esc_html__( 'Cross fade', 'NOVA-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off'    => esc_html__( 'Non', 'NOVA-addons' ),
				'default'      => 'yes',
				'description' => esc_html__( 'Évite de voir le contenu derrière pendant la transition', 'NOVA-addons' ),
				'condition'    => [
					'swiper_effect' => 'fade',
				],
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}
}

