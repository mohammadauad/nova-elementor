<?php
/**
 * Plugin Name: Nova Elementor Addons
 * Plugin URI: https://nova-addons.com
 * Description: Advanced Elementor widgets including stunning page loaders, mega menus, icon menus, and smooth scroll hero sections.
 * Version: 1.0.1
 * Author: Mohammad auad - Euroweb Digital
 * Author URI: https://euroweb-digital.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nova-addons
 * Domain Path: /languages
 * Requires PHP: 7.4
 * Requires at least: 5.0
 * Elementor tested up to: 3.20
 * Elementor Pro tested up to: 3.20
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * ============================================================================
 * PLUGIN CONSTANTS
 * ============================================================================
 */
define( 'NOVA_ADDONS_VERSION', '1.4.4' );
define( 'NOVA_ADDONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NOVA_ADDONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NOVA_ADDONS_TEXT_DOMAIN', 'nova-addons' );

/**
 * ============================================================================
 * MAIN PLUGIN CLASS
 * ============================================================================
 * 
 * This is the main class that handles all plugin initialization and functionality.
 * It follows the singleton pattern to ensure only one instance is created.
 */
final class Nova_Addons_Elementor {

	/**
	 * Singleton instance
	 *
	 * @var Nova_Addons_Elementor|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Nova_Addons_Elementor
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Si le serveur impose une limite basse, tenter de monter à 3GB pour éviter "memory exhausted" (Elementor, widgets complexes).
		$current = ini_get( 'memory_limit' );
		$current_bytes = -1;
		
		// Parse la valeur actuelle (ex: "256M", "1G", "512M")
		if ( preg_match( '/^(\d+)\s*([KMG])?$/i', trim( $current ), $m ) ) {
			$n = (int) $m[1];
			$u = isset( $m[2] ) ? strtoupper( $m[2] ) : '';
			$current_bytes = $u === 'G' ? $n * 1024 * 1024 * 1024 : ( $u === 'M' ? $n * 1024 * 1024 : ( $u === 'K' ? $n * 1024 : $n ) );
		}
		
		// Si la limite actuelle est inférieure à 3GB, essayer de la monter à 3GB (1024 * 3 = 30720M)
		$target_memory = 30720 * 1024 * 1024; // 3GB en bytes
		if ( $current_bytes >= 0 && $current_bytes < $target_memory ) {
			@ini_set( 'memory_limit', '30720M' );
			
			// Log pour debug si WP_DEBUG est activé
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				$new_limit = ini_get( 'memory_limit' );
				error_log( sprintf( 
					'[NOVA Addons] Memory limit changed from %s to %s (requested: 30720M = 3GB)', 
					$current, 
					$new_limit 
				) );
			}
		}

		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * ========================================================================
	 * PLUGIN INITIALIZATION
	 * ========================================================================
	 */

	/**
	 * Initialize plugin
	 * Checks if Elementor is active and loads all necessary features
	 *
	 * @return void
	 */
	public function init() {
		// Check if Elementor is active
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'elementor_missing_notice' ] );
			return;
		}

		// Load settings
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/settings.php';

		// Register hooks
		$this->register_hooks();
	}

	/**
	 * Register all WordPress hooks
	 *
	 * @return void
	 */
	private function register_hooks() {
		// Page Loader - Only if enabled
		if ( get_option( 'NOVA_loader_enabled', 'yes' ) === 'yes' ) {
			add_action( 'wp_head', [ $this, 'critical_loader_css' ], -999999 );
			add_action( 'wp_head', [ $this, 'inline_page_loader' ], -99999 );
			add_action( 'wp_body_open', [ $this, 'output_page_loader_html' ], 1 );
		}

		// Assets & Scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_page_transitions_fix' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'elementor/editor/before_enqueue_styles', [ $this, 'enqueue_editor_styles' ] );
		// Enregistrer les assets tôt (front + éditeur)
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets_early' ], 5 );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_assets_early' ], 5 );
		// Charger le carousel swiper dans l'iframe preview de l'éditeur
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_preview_scripts' ] );

		// FOUC Prevention Script
		add_action( 'wp_head', [ $this, 'add_js_detection_script' ], 0 );
		
		// Force styles in Elementor editor
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'force_cards_styles_in_editor' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'force_cards_styles_in_editor' ] );

		// Widgets & Categories
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );

		// NOVA Settings sur les conteneurs Elementor (sticky, offsets, classe quand fixe)
		add_action( 'elementor/element/after_section_end', [ $this, 'register_container_nova_settings' ], 10, 3 );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'apply_container_nova_settings' ] );

		// Custom Post Types
		add_action( 'init', [ $this, 'register_mega_menu_cpt' ] );
		add_action( 'init', [ $this, 'register_nova_templates_cpt' ], 20 );
		add_filter( 'elementor/post_types/editable', [ $this, 'add_elementor_support_to_cpt' ] );

		// Template override: substitute the active theme template by `full-page-override.php`
		// when a nova_template (tagged Header / Footer / Single CPT) has been configured.
		add_filter( 'template_include', [ $this, 'override_template' ], 99 );

		// AJAX
		add_action( 'wp_ajax_NOVA_get_menu_items', [ $this, 'ajax_get_menu_items' ] );
		add_action( 'wp_ajax_nopriv_NOVA_get_menu_items', [ $this, 'ajax_get_menu_items' ] );
	}

	/**
	 * Adds an inline script to <html> as early as possible.
	 * This prevents Flash of Unstyled Content (FOUC).
	 */
	public function add_js_detection_script() {
		echo "<script>document.documentElement.classList.add('nova-js');</script>\n";
	}

	/**
	 * Show admin notice if Elementor is missing
	 *
	 * @return void
	 */
	public function elementor_missing_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'NOVA Addons requires Elementor to be installed and activated.', NOVA_ADDONS_TEXT_DOMAIN ); ?></p>
		</div>
		<?php
	}

	/**
	 * ========================================================================
	 * WIDGET REGISTRATION
	 * ========================================================================
	 */

	/**
	 * Register custom widget category
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 * @return void
	 */
	public function register_widget_category( $elements_manager ) {
		$elements_manager->add_category(
			'nova-addons',
			[
				'title' => esc_html__( 'NOVA Addons', NOVA_ADDONS_TEXT_DOMAIN ),
				'icon'  => 'fa fa-star',
			]
		);
	}

	/**
	 * Register all widgets
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-mega-menu-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-icon-menu-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-scroll-hero-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-brands-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-title-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-cards-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-stacking-cards-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-stacking-cards-2-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-features-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-carousel-widget.php';
		// Nouveau widget : NOVA Carousel (Swiper)
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-carousel-swiper-widget.php';
		// Nouveau widget : Split Sticky Containers (2 colonnes)
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-split-sticky-containers-widget.php';
		// Shuffle Card widget désactivé/supprimé :
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-gallery-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-gallery-multi-filters-widget.php';
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-faq-widget.php';
		// Nouveau widget : Slider News
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-slider-news-widget.php';
		// Nouveau widget : NOVA Tabs
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-tabs-widget.php';
		// Nouveau widget : NOVA Filters (formulaire GET configurable)
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-filters-widget.php';

		$widgets_manager->register( new \NOVA_Addons_Elementor\Mega_Menu_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Icon_Menu_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Scroll_Hero_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Brands_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Title_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Cards_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Stacking_Cards_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Stacking_Cards_2_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Features_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Carousel_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Carousel_Swiper_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Split_Sticky_Containers_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Gallery_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Gallery_Multi_Filters_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\FAQ_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Slider_News_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Tabs_Widget() );
		$widgets_manager->register( new \NOVA_Addons_Elementor\Filters_Widget() );
	}

	/**
	 * NOVA Settings : section de réglages sur les conteneurs (sticky, offset, sélecteur CSS, classe).
	 *
	 * @param \Elementor\Controls_Stack $element
	 * @param string                    $section_id
	 * @param array                     $args
	 */
	public function register_container_nova_settings( $element, $section_id, $args ) {
		if ( $element->get_name() !== 'container' || $section_id !== 'section_layout' ) {
			return;
		}

		$element->start_controls_section(
			'nova_settings_section',
			[
				'label' => esc_html__( 'NOVA Settings', NOVA_ADDONS_TEXT_DOMAIN ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		// Rôle sticky : aucun / wrapper / colonne sticky
		$element->add_control(
			'nova_sticky_role',
			[
				'label'   => esc_html__( 'Sticky scroll (rôle)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''        => esc_html__( 'Aucun', NOVA_ADDONS_TEXT_DOMAIN ),
					'wrapper' => esc_html__( 'Wrapper (conteneur de la colonne sticky)', NOVA_ADDONS_TEXT_DOMAIN ),
					'col'     => esc_html__( 'Colonne sticky (élément fixé)', NOVA_ADDONS_TEXT_DOMAIN ),
				],
			]
		);

		// --- Options réservées au WRAPPER (responsives : desktop, tablette, mobile) ---
		// Offset de base en haut (px)
		$element->add_responsive_control(
			'nova_sticky_offset',
			[
				'label'     => esc_html__( 'Offset sticky (px)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 80,
				'tablet_default' => 80,
				'mobile_default'  => 0,
				'min'       => 0,
				'max'       => 500,
				'condition' => [
					'nova_sticky_role' => 'wrapper',
				],
			]
		);

		// Offset en bas (px) : espace sous la colonne sticky avant de la « dépinner »
		$element->add_responsive_control(
			'nova_sticky_offset_bottom',
			[
				'label'     => esc_html__( 'Offset sticky bas (px)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0,
				'tablet_default' => 0,
				'mobile_default'  => 0,
				'min'       => 0,
				'max'       => 500,
				'condition' => [
					'nova_sticky_role' => 'wrapper',
				],
				'description' => esc_html__( 'Espace en bas de la zone de scroll avant que la colonne ne se libère.', NOVA_ADDONS_TEXT_DOMAIN ),
			]
		);

		// Ajouter la hauteur d'un élément (sélecteur CSS) à l'offset
		$element->add_control(
			'nova_sticky_add_header',
			[
				'label'        => esc_html__( 'Ajouter la hauteur d\'un élément à l\'offset', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Oui', NOVA_ADDONS_TEXT_DOMAIN ),
				'label_off'    => esc_html__( 'Non', NOVA_ADDONS_TEXT_DOMAIN ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'nova_sticky_role' => 'wrapper',
				],
			]
		);

		// Sélecteur CSS de l'élément dont la hauteur est ajoutée
		$element->add_control(
			'nova_sticky_header_selector',
			[
				'label'       => esc_html__( 'Sélecteur CSS de l\'élément', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '#masthead, .site-header, header',
				'placeholder' => '#masthead, .navbar, .fixed-bar',
				'condition'   => [
					'nova_sticky_role'      => 'wrapper',
					'nova_sticky_add_header' => 'yes',
				],
			]
		);

		// --- Option pour WRAPPER et COLONNE STICKY ---
		// Classe CSS ajoutée quand le mode fixe (pin) est actif
		$element->add_control(
			'nova_sticky_pinned_class',
			[
				'label'       => esc_html__( 'Classe quand sticky actif (pin)', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'ewd-sticky-is-pinned',
				'placeholder' => 'ewd-sticky-is-pinned',
				'condition'   => [
					'nova_sticky_role!' => '',
				],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Appliquer les réglages NOVA au rendu du conteneur (classes, data-*, variables CSS).
	 *
	 * @param \Elementor\Element_Base $element
	 */
	public function apply_container_nova_settings( $element ) {
		$settings = $element->get_settings_for_display();
		$role     = isset( $settings['nova_sticky_role'] ) ? $settings['nova_sticky_role'] : '';

		if ( $role === 'wrapper' ) {
			$element->add_render_attribute( '_wrapper', 'class', 'ewd-sticky-wrapper' );
			// Offsets responsives : desktop, tablet, mobile (pour le JS)
			$offset_top = [
				'desktop' => isset( $settings['nova_sticky_offset'] ) ? (int) $settings['nova_sticky_offset'] : 80,
				'tablet'  => isset( $settings['nova_sticky_offset_tablet'] ) && $settings['nova_sticky_offset_tablet'] !== '' ? (int) $settings['nova_sticky_offset_tablet'] : null,
				'mobile'  => isset( $settings['nova_sticky_offset_mobile'] ) && $settings['nova_sticky_offset_mobile'] !== '' ? (int) $settings['nova_sticky_offset_mobile'] : null,
			];
			$offset_bottom = [
				'desktop' => isset( $settings['nova_sticky_offset_bottom'] ) ? (int) $settings['nova_sticky_offset_bottom'] : 0,
				'tablet'  => isset( $settings['nova_sticky_offset_bottom_tablet'] ) && $settings['nova_sticky_offset_bottom_tablet'] !== '' ? (int) $settings['nova_sticky_offset_bottom_tablet'] : null,
				'mobile'  => isset( $settings['nova_sticky_offset_bottom_mobile'] ) && $settings['nova_sticky_offset_bottom_mobile'] !== '' ? (int) $settings['nova_sticky_offset_bottom_mobile'] : null,
			];
			$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-offset-top', wp_json_encode( $offset_top ) );
			$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-offset-bottom', wp_json_encode( $offset_bottom ) );
			// Fallback CSS var pour le desktop (rétrocompat)
			$element->add_render_attribute( '_wrapper', 'style', '--ewd-sticky-top: ' . $offset_top['desktop'] . 'px;' );
			if ( ! empty( $settings['nova_sticky_add_header'] ) && $settings['nova_sticky_add_header'] === 'yes' ) {
				$selector = ! empty( $settings['nova_sticky_header_selector'] ) ? $settings['nova_sticky_header_selector'] : '#masthead, .site-header, header';
				$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-header-selector', $selector );
			}
			if ( ! empty( $settings['nova_sticky_pinned_class'] ) ) {
				$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-pinned-class', trim( $settings['nova_sticky_pinned_class'] ) );
			}
		}

		if ( $role === 'col' ) {
			$element->add_render_attribute( '_wrapper', 'class', 'ewd-sticky-col' );
			if ( ! empty( $settings['nova_sticky_pinned_class'] ) ) {
				$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-pinned-class', trim( $settings['nova_sticky_pinned_class'] ) );
			}
		}
	}

	/**
	 * ========================================================================
	 * CUSTOM POST TYPE
	 * ========================================================================
	 */

	/**
	 * Register custom post type for Mega Menu content
	 *
	 * @return void
	 */
	public function register_mega_menu_cpt() {
		$labels = [
			'name'                  => esc_html__( 'NOVA Mega Menus', NOVA_ADDONS_TEXT_DOMAIN ),
			'singular_name'         => esc_html__( 'NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'menu_name'             => esc_html__( 'NOVA Mega Menus', NOVA_ADDONS_TEXT_DOMAIN ),
			'name_admin_bar'        => esc_html__( 'NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'add_new'               => esc_html__( 'Add New', NOVA_ADDONS_TEXT_DOMAIN ),
			'add_new_item'          => esc_html__( 'Add New NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'new_item'              => esc_html__( 'New NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'edit_item'             => esc_html__( 'Edit NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'view_item'             => esc_html__( 'View NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'all_items'             => esc_html__( 'All NOVA Mega Menus', NOVA_ADDONS_TEXT_DOMAIN ),
			'search_items'          => esc_html__( 'Search NOVA Mega Menus', NOVA_ADDONS_TEXT_DOMAIN ),
			'parent_item_colon'     => esc_html__( 'Parent NOVA Mega Menu:', NOVA_ADDONS_TEXT_DOMAIN ),
			'not_found'             => esc_html__( 'No NOVA Mega Menus found.', NOVA_ADDONS_TEXT_DOMAIN ),
			'not_found_in_trash'    => esc_html__( 'No NOVA Mega Menus found in Trash.', NOVA_ADDONS_TEXT_DOMAIN ),
			'featured_image'        => esc_html__( 'Featured Image', NOVA_ADDONS_TEXT_DOMAIN ),
			'set_featured_image'    => esc_html__( 'Set featured image', NOVA_ADDONS_TEXT_DOMAIN ),
			'remove_featured_image' => esc_html__( 'Remove featured image', NOVA_ADDONS_TEXT_DOMAIN ),
			'use_featured_image'    => esc_html__( 'Use as featured image', NOVA_ADDONS_TEXT_DOMAIN ),
			'archives'              => esc_html__( 'NOVA Mega Menu Archives', NOVA_ADDONS_TEXT_DOMAIN ),
			'insert_into_item'      => esc_html__( 'Insert into NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this NOVA Mega Menu', NOVA_ADDONS_TEXT_DOMAIN ),
			'filter_items_list'     => esc_html__( 'Filter NOVA Mega Menus list', NOVA_ADDONS_TEXT_DOMAIN ),
			'items_list_navigation' => esc_html__( 'NOVA Mega Menus list navigation', NOVA_ADDONS_TEXT_DOMAIN ),
			'items_list'            => esc_html__( 'NOVA Mega Menus list', NOVA_ADDONS_TEXT_DOMAIN ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'mega-menu-content' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 90,
			'supports'           => [ 'title', 'editor', 'elementor' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'mega_menu_content', $args );
	}

	/**
	 * Add Elementor support to Mega Menu CPT
	 *
	 * @param array $post_types Post types.
	 * @return array
	 */
	public function add_elementor_support_to_cpt( $post_types ) {
		$post_types[] = 'mega_menu_content';
		$post_types[] = 'nova_template';
		return $post_types;
	}

	/**
	 * Register the `nova_template` CPT (Header / Footer / Single CPT layouts)
	 * and its associated `nova_template_type` taxonomy.
	 */
	public function register_nova_templates_cpt() {
		$labels = [
			'name'               => esc_html__( 'NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'singular_name'      => esc_html__( 'NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'menu_name'          => esc_html__( 'NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'name_admin_bar'     => esc_html__( 'NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'add_new'            => esc_html__( 'Add New', NOVA_ADDONS_TEXT_DOMAIN ),
			'add_new_item'       => esc_html__( 'Add New NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'new_item'           => esc_html__( 'New NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'edit_item'          => esc_html__( 'Edit NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'view_item'          => esc_html__( 'View NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'all_items'          => esc_html__( 'All NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'search_items'       => esc_html__( 'Search NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'not_found'          => esc_html__( 'No NOVA Templates found.', NOVA_ADDONS_TEXT_DOMAIN ),
			'not_found_in_trash' => esc_html__( 'No NOVA Templates found in Trash.', NOVA_ADDONS_TEXT_DOMAIN ),
		];

		register_post_type(
			'nova_template',
			[
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'query_var'           => true,
				'rewrite'             => false,
				'capability_type'     => 'post',
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => 90,
				'supports'            => [ 'title', 'editor', 'elementor' ],
				'show_in_rest'        => true,
				'menu_icon'           => 'dashicons-layout',
				'exclude_from_search' => true,
			]
		);

		register_taxonomy(
			'nova_template_type',
			'nova_template',
			[
				'label'             => esc_html__( 'Template Type', NOVA_ADDONS_TEXT_DOMAIN ),
				'rewrite'           => [ 'slug' => 'nova-template-type' ],
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			]
		);

		$this->pre_populate_template_types();
	}

	/**
	 * Create the default Template Type terms the first time the taxonomy exists.
	 */
	public function pre_populate_template_types() {
		$terms = [
			'Header'     => 'header',
			'Footer'     => 'footer',
			'Single CPT' => 'single',
			'Mega Menu'  => 'mega-menu',
			'Archive'    => 'archive',
			'Section'    => 'section',
		];

		foreach ( $terms as $term_name => $term_slug ) {
			if ( ! term_exists( $term_slug, 'nova_template_type' ) ) {
				wp_insert_term( $term_name, 'nova_template_type', [ 'slug' => $term_slug ] );
			}
		}
	}

	/**
	 * Replace the active theme template by Nova's full-page override when at least
	 * one nova_template (header, footer or single CPT layout) has been configured.
	 */
	public function override_template( $template ) {
		if ( is_admin() ) {
			return $template;
		}

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return $template;
		}

		$header_id = self::resolve_nova_template_id( 'header', 'nova_header_template' );
		$footer_id = self::resolve_nova_template_id( 'footer', 'nova_footer_template' );

		$single_template_id = 0;
		if ( is_singular() ) {
			$post_type          = get_post_type();
			$single_template_id = self::resolve_nova_template_id( 'single', 'nova_single_' . $post_type . '_template', false );
		}

		if ( $header_id || $footer_id || $single_template_id ) {
			return NOVA_ADDONS_PLUGIN_DIR . 'includes/templates/full-page-override.php';
		}

		return $template;
	}

	/**
	 * Resolve the ID of a nova_template post for a given template type.
	 *
	 * Resolution order:
	 *   1. Explicit override stored in $option_key (set via update_option).
	 *   2. Latest published `nova_template` post associated with the
	 *      `nova_template_type` taxonomy term matching $type_slug
	 *      (only when $taxonomy_fallback is true).
	 *
	 * Results are cached per request to avoid repeated queries when the
	 * template filter and the override template both call this method.
	 *
	 * @param string $type_slug          Taxonomy term slug (e.g. 'header', 'footer', 'single').
	 * @param string $option_key         WP option holding an explicit override ID.
	 * @param bool   $taxonomy_fallback  Whether to query by taxonomy when the option is missing.
	 * @return int Template post ID or 0 when nothing matches.
	 */
	public static function resolve_nova_template_id( $type_slug, $option_key, $taxonomy_fallback = true ) {
		static $cache = [];

		$cache_key = $type_slug . '|' . $option_key;
		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$id = (int) get_option( $option_key );
		if ( $id > 0 && get_post_status( $id ) === 'publish' ) {
			return $cache[ $cache_key ] = $id;
		}

		if ( ! $taxonomy_fallback || ! taxonomy_exists( 'nova_template_type' ) ) {
			return $cache[ $cache_key ] = 0;
		}

		$query = new \WP_Query(
			[
				'post_type'        => 'nova_template',
				'post_status'      => 'publish',
				'posts_per_page'   => 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'fields'           => 'ids',
				'no_found_rows'    => true,
				'suppress_filters' => true,
				'tax_query'        => [
					[
						'taxonomy' => 'nova_template_type',
						'field'    => 'slug',
						'terms'    => $type_slug,
					],
				],
			]
		);

		$found = ! empty( $query->posts ) ? (int) $query->posts[0] : 0;
		return $cache[ $cache_key ] = $found;
	}

	/**
	 * ========================================================================
	 * ASSETS ENQUEUE
	 * ========================================================================
	 */

	/**
	 * Enregistre les assets critiques tôt — disponibles sur le front ET dans l'éditeur.
	 *
	 * @return void
	 */
	public function register_assets_early() {
		// Swiper COMPLET (swiper-bundle) : Elementor charge souvent un Swiper modulaire SANS module Touch
		// → typeof Swiper !== 'undefined' mais drag impossible. On impose le bundle pour ce widget.
		if ( ! wp_script_is( 'nova-swiper-bundle', 'registered' ) ) {
			wp_register_style(
				'nova-swiper-bundle',
				'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
				[],
				'11.1.14'
			);
			wp_register_script(
				'nova-swiper-bundle',
				'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
				[],
				'11.1.14',
				true
			);
			wp_add_inline_script(
				'nova-swiper-bundle',
				'window.NovaSwiperBundle=window.Swiper;',
				'after'
			);
		}

		if ( ! wp_script_is( 'nova-carousel-swiper-script', 'registered' ) ) {
			wp_register_script(
				'nova-carousel-swiper-script',
				NOVA_ADDONS_PLUGIN_URL . 'assets/js/carousel-swiper.js',
				[ 'jquery', 'nova-swiper-bundle' ],
				NOVA_ADDONS_VERSION,
				true
			);
		}

		if ( ! wp_style_is( 'nova-carousel-style', 'registered' ) ) {
			wp_register_style(
				'nova-carousel-style',
				NOVA_ADDONS_PLUGIN_URL . 'assets/css/carousel-style.css',
				[],
				NOVA_ADDONS_VERSION
			);
		}
	}

	/**
	 * Charge le carousel swiper dans l'iframe preview de l'éditeur Elementor.
	 * C'est ici que elementorFrontend et les widgets sont disponibles.
	 *
	 * @return void
	 */
	public function enqueue_preview_scripts() {
		wp_enqueue_style( 'nova-swiper-bundle' );
		wp_enqueue_script(
			'nova-carousel-swiper-preview',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/carousel-swiper.js',
			[ 'jquery', 'nova-swiper-bundle' ],
			NOVA_ADDONS_VERSION . '-' . time(),
			true
		);

		wp_enqueue_style(
			'nova-carousel-style-preview',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/carousel-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		// Debug : confirmer que ce hook se déclenche
		add_action( 'wp_footer', function() {
			echo '<script>console.log("[NOVA DEBUG] enqueue_preview_scripts hook fired — carousel-swiper should be loaded");</script>';
		} );
	}

	/**
	 * Enqueue frontend scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		// Détecter le mode preview Elementor (iframe preview)
		$is_elementor_preview = isset( $_GET['elementor-preview'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			( class_exists( '\Elementor\Plugin' ) &&
			  isset( \Elementor\Plugin::$instance->preview ) &&
			  method_exists( \Elementor\Plugin::$instance->preview, 'is_preview_mode' ) &&
			  \Elementor\Plugin::$instance->preview->is_preview_mode() );

		if ( $is_elementor_preview ) {
			wp_enqueue_style( 'nova-swiper-bundle' );
			wp_enqueue_script(
				'nova-carousel-swiper-preview',
				NOVA_ADDONS_PLUGIN_URL . 'assets/js/carousel-swiper.js',
				[ 'jquery', 'nova-swiper-bundle' ],
				NOVA_ADDONS_VERSION . '-preview',
				true
			);
			wp_enqueue_style(
				'nova-carousel-style',
				NOVA_ADDONS_PLUGIN_URL . 'assets/css/carousel-style.css',
				[],
				NOVA_ADDONS_VERSION
			);
			// Debug
			wp_add_inline_script( 'nova-carousel-swiper-preview', 'console.log("[NOVA DEBUG] carousel-swiper chargé via enqueue_assets preview mode");', 'before' );
		}

		// Main plugin style (includes mega menu styles)
		if ( file_exists( NOVA_ADDONS_PLUGIN_DIR . 'assets/css/style.css' ) ) {
			wp_enqueue_style(
				'nova-addons-style',
				NOVA_ADDONS_PLUGIN_URL . 'assets/css/style.css',
				[],
				NOVA_ADDONS_VERSION
			);
		}

		// FOUC Prevention Fix
		wp_register_style(
			'nova-fouc-fix',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/nova-fouc-fix.css',
			[],
			NOVA_ADDONS_VERSION
		);
		// Enqueue the FOUC fix globally so it's always available
		wp_enqueue_style( 'nova-fouc-fix' );

		// Mega Menu Script (hover/click functionality)
		wp_register_script(
			'nova-mega-menu',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/mega-menu.js',
			[ 'jquery', 'gsap' ],
			NOVA_ADDONS_VERSION,
			true
		);
		wp_enqueue_script( 'nova-mega-menu' );

		// Mega Menu Mobile Breadcrumb Script
		wp_register_script(
			'nova-mega-menu-mobile',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/mega-menu-mobile.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);
		wp_enqueue_script( 'nova-mega-menu-mobile' );

		// ✅ GSAP Libraries - CHARGER EN PREMIER
		wp_enqueue_script(
			'gsap',
			'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
			[],
			'3.12.5',
			false  // ← DANS LE HEAD pour être disponible tôt
		);

		wp_enqueue_script(
			'gsap-scrolltrigger',
			'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js',
			[ 'gsap' ],
			'3.12.5',
			false  // ← DANS LE HEAD
		);


		// ✅ Page Loader Script - APRÈS GSAP (Only if enabled)
		if ( get_option( 'NOVA_loader_enabled', 'yes' ) === 'yes' ) {
			wp_enqueue_script(
				'nova-page-loader',
				NOVA_ADDONS_PLUGIN_URL . 'assets/js/page-loader.js',
				[ 'gsap' ],  // ← DÉPEND DE GSAP
				NOVA_ADDONS_VERSION,
				false  // ← DANS LE HEAD
			);

			wp_localize_script( 'nova-page-loader', 'nova_loader_config', [
				'color1'          => get_option( 'NOVA_loader_color_1', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ),
				'color2'          => get_option( 'NOVA_loader_color_2', '#DB002B' ),
				'phase2Delay'     => floatval( get_option( 'NOVA_loader_phase2_delay', 0.1 ) ),
				'phase2Duration'  => floatval( get_option( 'NOVA_loader_phase2_duration', 0.3 ) ),
				'phase3Duration'  => floatval( get_option( 'NOVA_loader_phase3_duration', 1.0 ) ),
				'animDelay'       => intval( get_option( 'NOVA_loader_anim_delay', 0 ) ),
			] );
		}

		// Icon Menu
		wp_enqueue_style(
			'nova-icon-menu-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/icon-menu-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		// Icon Menu Script - Enregistrer avec le bon nom pour correspondre à get_script_depends()
		wp_register_script(
			'nova-addons-icon-menu-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/icon-menu.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);
		// Enqueuer le script pour qu'il soit toujours disponible
		wp_enqueue_script( 'nova-addons-icon-menu-script' );

		// Lenis - Smooth Scroll
		wp_register_script(
			'lenis',
			'https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js',
			[],
			'1.0.42',
			true
		);

		wp_enqueue_script(
			'nova-smooth-scroll',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/lenis-init.js',
			[ 'jquery', 'lenis', 'gsap', 'gsap-scrolltrigger' ],
			NOVA_ADDONS_VERSION,
			true
		);


		// Scroll Hero
		$this->register_scroll_hero_assets();
		$this->register_stacking_cards_assets();

		// Brands Widget
		wp_register_style(
			'nova-brands-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/brands-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-brands-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/brands.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Title Widget
		wp_register_style(
			'nova-title-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/title-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-title-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/title.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Cards Widget
		wp_register_style(
			'nova-cards-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/cards-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-cards-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/cards.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Features Widget
		wp_register_style(
			'nova-features-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/features-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-features-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/features.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Carousel Widget - Version dynamique pour forcer le rechargement à chaque chargement
		$carousel_version = time(); // Version qui change à chaque chargement
		wp_register_style(
			'nova-carousel-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/carousel-style.css',
			[],
			$carousel_version
		);

		// Register Owl Carousel
		if ( ! wp_script_is( 'owl-carousel', 'registered' ) ) {
			wp_register_script(
				'owl-carousel',
				'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js',
				[ 'jquery' ],
				'2.3.4',
				true
			);
		}
		if ( ! wp_style_is( 'owl-carousel', 'registered' ) ) {
			wp_register_style(
				'owl-carousel',
				'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css',
				[],
				'2.3.4'
			);
		}
		if ( ! wp_style_is( 'owl-carousel-theme', 'registered' ) ) {
			wp_register_style(
				'owl-carousel-theme',
				'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.theme.default.min.css',
				[ 'owl-carousel' ],
				'2.3.4'
			);
		}

		wp_register_script(
			'nova-carousel-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/carousel.js',
			[ 'jquery', 'owl-carousel' ],
			$carousel_version,
			true
		);

		// NOVA Carousel Swiper : dépend de nova-swiper-bundle (touch/drag inclus), pas du Swiper modulaire Elementor.
		if ( ! wp_script_is( 'nova-swiper-bundle', 'registered' ) ) {
			wp_register_style(
				'nova-swiper-bundle',
				'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
				[],
				'11.1.14'
			);
			wp_register_script(
				'nova-swiper-bundle',
				'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
				[],
				'11.1.14',
				true
			);
			wp_add_inline_script(
				'nova-swiper-bundle',
				'window.NovaSwiperBundle=window.Swiper;',
				'after'
			);
		}
		wp_register_script(
			'nova-carousel-swiper-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/carousel-swiper.js',
			[ 'jquery', 'nova-swiper-bundle' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Gallery Widget
		wp_register_style(
			'nova-gallery-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/gallery-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-gallery-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/gallery.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Gallery Multi Filters Widget
		wp_register_style(
			'nova-gallery-multi-filters-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/gallery-multi-filters-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-gallery-multi-filters-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/gallery-multi-filters.js',
			[ 'jquery', 'owl-carousel' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// FAQ Widget
		wp_register_style(
			'nova-faq-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/faq-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-faq-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/faq.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Tabs Widget
		wp_register_style(
			'nova-tabs-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/tabs-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-tabs-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/tabs.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Filters Widget
		wp_register_style(
			'nova-filters-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/filters-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-filters-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/filters.js',
			[],
			NOVA_ADDONS_VERSION,
			true
		);

		// Slider News Widget
		wp_register_style(
			'nova-slider-news-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/slider-news-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-slider-news-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/slider-news.js',
			[ 'jquery', 'gsap', 'gsap-scrolltrigger' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Split Sticky Containers Widget (2 colonnes)
		wp_register_style(
			'nova-split-sticky-containers-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/split-sticky-containers-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		// EWD Sticky Columns – ScrollTrigger pin (DOIT s'exécuter APRÈS Lenis = pas de conflit scroll)
		wp_register_script(
			'ewd-sticky-columns',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/sticky-columns.js',
			[ 'jquery', 'gsap', 'gsap-scrolltrigger', 'nova-smooth-scroll' ],
			NOVA_ADDONS_VERSION,
			true
		);
		wp_enqueue_script( 'ewd-sticky-columns' );

		// Shuffle Card Widget
		wp_register_style(
			'nova-shuffle-card-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/shuffle-card-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_register_script(
			'nova-shuffle-card-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/shuffle-card.js',
			[ 'jquery', 'swiper' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Ensure cards assets are enqueued on the frontend when needed.
		if ( ! wp_style_is( 'nova-cards-style', 'enqueued' ) ) {
			wp_enqueue_style( 'nova-cards-style' );
		}
		if ( ! wp_script_is( 'nova-cards-script', 'enqueued' ) ) {
			wp_enqueue_script( 'nova-cards-script' );
		}

		// Navbar
		wp_register_script(
			'nova-navbar-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/navbar.js',
			[ 'jquery', 'gsap', 'gsap-scrolltrigger' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// ✅ ENQUEUE and LOCALIZE navbar script
		wp_enqueue_script( 'nova-navbar-script' );
		wp_localize_script( 'nova-navbar-script', 'NovaAddonsSettings', [
			'hoverAnimationEnabled' => get_option( 'NOVA_icon_menu_hover_enabled', 'yes' ),
			'hoverDuration'         => (float) get_option( 'NOVA_icon_menu_hover_duration', 1.0 ),
			'hoverEasing'           => sanitize_text_field( get_option( 'NOVA_icon_menu_hover_easing', 'ease' ) ),
			'submenuDuration'       => (float) get_option( 'NOVA_icon_menu_submenu_duration', 0.3 ),
		] );

		$this->register_scroll_hero_assets();
		$this->register_stacking_cards_assets();
	}

	/**
	 * Enqueue Elementor Pro Page Transitions Fix
	 * 
	 * This script ensures page transitions remain visible until page is fully loaded
	 *
	 * @return void
	 */
	public function enqueue_page_transitions_fix() {
		// Only enqueue if Elementor Pro is active
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return;
		}

		wp_enqueue_script(
			'nova-elementor-page-transitions-fix',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/elementor-page-transitions-fix.js',
			[],
			NOVA_ADDONS_VERSION,
			true
		);
	}

	/**
	 * Register scroll hero assets and their dependencies.
	 *
	 * Keeps registration centralized for both frontend and editor contexts.
	 *
	 * @return void
	 */
	private function register_scroll_hero_assets() {
		if ( ! wp_style_is( 'nova-scroll-hero-style', 'registered' ) ) {
			wp_register_style(
				'nova-scroll-hero-style',
				NOVA_ADDONS_PLUGIN_URL . 'assets/css/scroll-hero-style.css',
				[],
				NOVA_ADDONS_VERSION
			);
		}

		if ( ! wp_script_is( 'nova-scroll-hero-script', 'registered' ) ) {
			wp_register_script(
				'nova-scroll-hero-script',
				NOVA_ADDONS_PLUGIN_URL . 'assets/js/scroll-hero.js',
				[ 'jquery', 'gsap', 'gsap-scrolltrigger' ],
				NOVA_ADDONS_VERSION,
				true
			);
		}
	}

	/**
	 * Register stacking cards assets.
	 *
	 * @return void
	 */
	private function register_stacking_cards_assets() {
		if ( ! wp_style_is( 'nova-stacking-cards-style', 'registered' ) ) {
			wp_register_style(
				'nova-stacking-cards-style',
				NOVA_ADDONS_PLUGIN_URL . 'assets/css/stacking-cards-style.css',
				[],
				NOVA_ADDONS_VERSION
			);
		}

		if ( ! wp_script_is( 'nova-stacking-cards-script', 'registered' ) ) {
			wp_register_script(
				'nova-stacking-cards-script',
				NOVA_ADDONS_PLUGIN_URL . 'assets/js/stacking-cards.js',
				[ 'jquery', 'gsap', 'gsap-scrolltrigger' ],
				NOVA_ADDONS_VERSION,
				true
			);
		}

		// Stacking Cards 2
		if ( ! wp_style_is( 'nova-stacking-cards-2-style', 'registered' ) ) {
			wp_register_style(
				'nova-stacking-cards-2-style',
				NOVA_ADDONS_PLUGIN_URL . 'assets/css/stacking-cards-2-style.css',
				[],
				NOVA_ADDONS_VERSION
			);
		}

		if ( ! wp_script_is( 'nova-stacking-cards-2-script', 'registered' ) ) {
			wp_register_script(
				'nova-stacking-cards-2-script',
				NOVA_ADDONS_PLUGIN_URL . 'assets/js/stacking-cards-2.js',
				[ 'jquery', 'gsap', 'gsap-scrolltrigger', 'owl-carousel' ],
				NOVA_ADDONS_VERSION,
				true
			);
		}

	}

	/**
	 * Enqueue editor scripts and styles
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'nova-addons-editor',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/nova-addons-admin.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Copy / Paste settings tool – only in Elementor editor
		wp_enqueue_script(
			'nova-copy-paste-settings',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/nova-copy-paste-settings.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		wp_localize_script( 'nova-addons-editor', 'NOVA_addons', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'NOVA_addons_nonce' ),
		] );

		// Enqueue scripts for editor preview
		wp_enqueue_script(
			'nova-brands-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/brands.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// NOVA Carousel Swiper — nécessaire pour le preview dans l'éditeur
		wp_enqueue_style( 'nova-carousel-style' );
		wp_enqueue_script( 'nova-carousel-swiper-script' );

		wp_enqueue_script(
			'nova-title-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/title.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		wp_enqueue_script(
			'nova-cards-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/cards.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		wp_enqueue_script(
			'nova-features-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/features.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Ensure GSAP core libraries are available inside Elementor editor.
		wp_enqueue_script(
			'gsap',
			'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js',
			[],
			'3.12.5',
			false
		);

		wp_enqueue_script(
			'gsap-scrolltrigger',
			'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js',
			[ 'gsap' ],
			'3.12.5',
			false
		);

		$this->register_scroll_hero_assets();
		wp_enqueue_style( 'nova-scroll-hero-style' );
		wp_enqueue_script( 'nova-scroll-hero-script' );
		$this->register_stacking_cards_assets();
		wp_enqueue_style( 'nova-stacking-cards-style' );
		wp_enqueue_script( 'nova-stacking-cards-script' );
		wp_enqueue_style( 'nova-stacking-cards-2-style' );
		wp_enqueue_script( 'nova-stacking-cards-2-script' );

		// Script : cache/montre les champs popup du repeater selon le toggle global
		wp_enqueue_script(
			'nova-carousel-popup-editor',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/nova-carousel-popup-editor.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);
	}

	/**
	 * Enqueue editor styles
	 *
	 * @return void
	 */
	public function enqueue_editor_styles() {
		// Enqueue styles for editor preview
		wp_enqueue_style(
			'nova-brands-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/brands-style.css',
			[ 'swiper', 'e-swiper' ],
			NOVA_ADDONS_VERSION
		);

		wp_enqueue_style(
			'nova-title-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/title-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_enqueue_style(
			'nova-cards-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/cards-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_enqueue_style(
			'nova-features-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/features-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_enqueue_script(
			'nova-features-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/features.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Carousel Widget - Version dynamique pour forcer le rechargement à chaque chargement
		$carousel_version = time(); // Version qui change à chaque chargement
		
		// Register Owl Carousel for editor
		if ( ! wp_script_is( 'owl-carousel', 'registered' ) ) {
			wp_register_script(
				'owl-carousel',
				'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js',
				[ 'jquery' ],
				'2.3.4',
				true
			);
		}
		if ( ! wp_style_is( 'owl-carousel', 'registered' ) ) {
			wp_register_style(
				'owl-carousel',
				'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css',
				[],
				'2.3.4'
			);
		}
		if ( ! wp_style_is( 'owl-carousel-theme', 'registered' ) ) {
			wp_register_style(
				'owl-carousel-theme',
				'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.theme.default.min.css',
				[ 'owl-carousel' ],
				'2.3.4'
			);
		}
		
		wp_enqueue_style(
			'nova-carousel-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/carousel-style.css',
			[ 'owl-carousel', 'owl-carousel-theme' ],
			$carousel_version
		);

		wp_enqueue_script(
			'nova-carousel-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/carousel.js',
			[ 'jquery', 'owl-carousel' ],
			$carousel_version,
			true
		);

		// Gallery Widget
		wp_enqueue_style(
			'nova-gallery-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/gallery-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_enqueue_script(
			'nova-gallery-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/gallery.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		// Shuffle Card Widget
		wp_enqueue_style(
			'nova-shuffle-card-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/shuffle-card-style.css',
			[],
			NOVA_ADDONS_VERSION
		);

		wp_enqueue_script(
			'nova-shuffle-card-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/shuffle-card.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		$this->register_scroll_hero_assets();
		wp_enqueue_style( 'nova-scroll-hero-style' );
		$this->register_stacking_cards_assets();
		wp_enqueue_style( 'nova-stacking-cards-style' );
		wp_enqueue_style( 'nova-stacking-cards-2-style' );
	}

	/**
	 * Force cards styles in Elementor editor
	 * Ensures styles are loaded even if get_style_depends() doesn't work properly
	 *
	 * @return void
	 */
	public function force_cards_styles_in_editor() {
		// Force enqueue cards styles in editor
		if ( ! wp_style_is( 'nova-cards-style', 'enqueued' ) ) {
			wp_enqueue_style( 'nova-cards-style' );
		}
	}

	/**
	 * ========================================================================
	 * PAGE LOADER - CRITICAL CSS
	 * ========================================================================
	 */

	/**
	 * Critical loader CSS - loads immediately
	 */
	public function critical_loader_css() {
		$color1 = get_option( 'NOVA_loader_color_1', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' );
		$color2 = get_option( 'NOVA_loader_color_2', '#DB002B' );
		?>
		<style id="nova-loader-critical-css">
			/* Phase 1: Loader with COLOR1 (visible immediately, full height) */
			#nova-page-loader {
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100vh;
				background: <?php echo esc_attr( $color1 ); ?>;
				z-index: 999997;
				overflow: hidden;
				margin: 0;
				padding: 0;
				border: 0;
			}
			
			/* Phase 2: Overlay with COLOR2 (starts at height: 0px, will animate) - Higher z-index to be on top */
			#nova-page-loader-overlay {
				position: fixed;
				bottom: 0;
				left: 0;
				width: 100%;
				height: 0px;
				background: <?php echo esc_attr( $color2 ); ?>;
				z-index: 999998;
				overflow: hidden;
				margin: 0;
				padding: 0;
				border: 0;
				will-change: height;
			}
		</style>
		<?php
	}

	/**
	 * Output page loader HTML directly in body (no JavaScript needed)
	 * This ensures elements are present immediately
	 *
	 * @return void
	 */
	public function output_page_loader_html() {
		?>
		<!-- Phase 1: Loader with COLOR1 (visible immediately, full height) -->
		<div id="nova-page-loader" class="nova-page-loader loading"></div>
		
		<!-- Phase 2: Overlay with COLOR2 (starts at height: 0px, will animate) -->
		<div id="nova-page-loader-overlay"></div>
		<?php
	}

	/**
	 * ========================================================================
	 * PAGE LOADER - ANIMATION SCRIPT
	 * ========================================================================
	 */

	/**
	 * Output animation script for page loader
	 *
	 * @return void
	 */
	public function inline_page_loader() {
		$loader_config = [
			'animDuration' => (float) get_option( 'NOVA_loader_anim_duration', 1.2 ),
			'animDelay'    => (int) get_option( 'NOVA_loader_anim_delay', 0 ),
			'backgroundColor' => get_option( 'NOVA_loader_bg_color', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ),
		];

		?>
		<script>
			// ✅ Pass config to page-loader.js
			window.nova_loader_config = {
				backgroundColor: <?php echo wp_json_encode( $loader_config['backgroundColor'] ); ?>,
				animDuration: <?php echo (float) $loader_config['animDuration']; ?>,
				animDelay: <?php echo (int) $loader_config['animDelay']; ?>
			};
		</script>
		<?php
	}

	/**
	 * ========================================================================
	 * AJAX HANDLERS
	 * ========================================================================
	 */

	/**
	 * AJAX handler to get menu items
	 *
	 * @return void
	 */
	public function ajax_get_menu_items() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'NOVA_addons_nonce' ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$menu_slug = sanitize_text_field( $_POST['menu_slug'] ?? '' );
		
		if ( empty( $menu_slug ) ) {
			wp_send_json_error( 'Menu slug is required' );
		}

		$menu_items = wp_get_nav_menu_items( $menu_slug );
		$options = [];

		if ( ! empty( $menu_items ) ) {
			foreach ( $menu_items as $item ) {
				if ( $item->menu_item_parent == 0 ) {
					// Utiliser l'ID du menu item (pas le titre) pour la cohérence avec le walker
					$options[] = [
						'id'   => (string) $item->ID, // ID comme string pour compatibilité avec select2
						'text' => $item->title,
					];
				}
			}
		}

		wp_send_json_success( $options );
	}
}

// Initialize plugin
NOVA_Addons_Elementor::instance();

/**
 * Allow additional image mime types for icon uploads.
 *
 * Extends the list of authorised file types so icon image controls (NOVA Stacking Cards,
 * NOVA Icon Menu, etc.) can accept common formats such as SVG, WEBP, AVIF and ICO.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function NOVA_addons_allow_additional_mimes( $mimes ) {
	if ( ! is_array( $mimes ) ) {
		$mimes = [];
	}

	// Allow for users who can upload files (Editors, Admins...). SVG uploads remain restricted to privileged roles.
	if ( current_user_can( 'upload_files' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['webp'] = 'image/webp';
		$mimes['avif'] = 'image/avif';
		$mimes['ico']  = 'image/vnd.microsoft.icon';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'NOVA_addons_allow_additional_mimes' );

