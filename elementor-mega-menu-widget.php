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
define( 'NOVA_ADDONS_VERSION', '1.2.4' );
define( 'NOVA_ADDONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NOVA_ADDONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NOVA_ADDONS_TEXT_DOMAIN', 'nova-addons' );

/**
 * ============================================================================
 * MAIN PLUGIN CLASS
 * ============================================================================
 */
final class Nova_Addons_Elementor {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	public function init() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'elementor_missing_notice' ] );
			return;
		}

		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/settings.php';
		if ( class_exists( 'Nova_Addons_Settings' ) ) {
			\Nova_Addons_Settings::init();
		}

		$this->register_hooks();
	}

	private function register_hooks() {
		if ( get_option( 'NOVA_loader_enabled', 'yes' ) === 'yes' ) {
			add_action( 'wp_head', [ $this, 'critical_loader_css' ], -999999 );
			add_action( 'wp_head', [ $this, 'inline_page_loader' ], -99999 );
			add_action( 'wp_body_open', [ $this, 'output_page_loader_html' ], 1 );
		}

		add_action( 'wp_head', [ $this, 'critical_menu_css' ], -999998 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_page_transitions_fix' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'elementor/editor/before_enqueue_styles', [ $this, 'enqueue_editor_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets_early' ], 5 );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_assets_early' ], 5 );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_preview_scripts' ] );
		add_action( 'wp_head', [ $this, 'add_js_detection_script' ], 0 );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'force_cards_styles_in_editor' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'force_cards_styles_in_editor' ] );

		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );
		add_action( 'elementor/element/after_section_end', [ $this, 'register_container_nova_settings' ], 10, 3 );
		add_action( 'elementor/frontend/container/before_render', [ $this, 'apply_container_nova_settings' ] );

		add_action( 'init', [ $this, 'register_nova_templates_cpt' ], 20 );
		add_filter( 'elementor/post_types/editable', [ $this, 'add_elementor_support_to_cpt' ] );
		register_activation_hook( __FILE__, [ $this, 'flush_nova_rewrites' ] );
		add_filter( 'template_include', [ $this, 'override_template' ], 99 );

		add_action( 'wp_ajax_NOVA_get_menu_items', [ $this, 'ajax_get_menu_items' ] );
		add_action( 'wp_ajax_nopriv_NOVA_get_menu_items', [ $this, 'ajax_get_menu_items' ] );
	}

	public function add_js_detection_script() {
		echo "<script>document.documentElement.classList.add('nova-js');</script>\n";
	}

	public function critical_menu_css() {
		if ( is_admin() ) {
			return;
		}

		echo '<style id="nova-critical-menu-css">';
		echo '.nova-mega-menu-panel{visibility:hidden;opacity:0;pointer-events:none}';
		echo '.nova-menu-list>li.nova-mega-menu-displayed>.nova-mega-menu-panel{visibility:visible;opacity:1;pointer-events:auto}';
		echo '.nova-menu-list>li>ul.sub-menu{display:none;margin:0;padding:0;list-style:none}';
		echo '.nova-menu-list>li.nova-submenu-open>ul.sub-menu,.nova-menu-list>li:hover>ul.sub-menu{display:block}';
		echo '.nova-menu-list>li:not(.menu-item-has-children):not(.nova-has-mega-menu) .nova-mega-menu-icon-wrapper{display:none!important}';
		echo '.nova-icon-menu-list>li>ul.sub-menu{display:none;margin:0;padding:0;list-style:none}';
		echo '.nova-icon-menu-list>li.nova-submenu-open>ul.sub-menu,.nova-icon-menu-list>li:hover>ul.sub-menu{display:block}';
		echo '.nova-icon-menu-list>li:not(.menu-item-has-children):not(.nova-has-submenu) .NOVA-dropdown-icon{display:none!important}';
		echo '</style>';
	}

	public function elementor_missing_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'NOVA Addons requires Elementor to be installed and activated.', NOVA_ADDONS_TEXT_DOMAIN ); ?></p>
		</div>
		<?php
	}

	public function register_widget_category( $elements_manager ) {
		$elements_manager->add_category(
			'nova-addons',
			[
				'title' => esc_html__( 'NOVA Addons', NOVA_ADDONS_TEXT_DOMAIN ),
				'icon'  => 'eicon-star',
			]
		);
	}

	public function register_widgets( $widgets_manager ) {
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/nova-post-type-helpers.php';

		$files = [
			'class-mega-menu-widget.php' => 'Mega_Menu_Widget',
			'class-icon-menu-widget.php' => 'Icon_Menu_Widget',
			'class-scroll-hero-widget.php' => 'Scroll_Hero_Widget',
			'class-brands-widget.php' => 'Brands_Widget',
			'class-title-widget.php' => 'Title_Widget',
			'class-cards-widget.php' => 'Cards_Widget',
			'class-stacking-cards-widget.php' => 'Stacking_Cards_Widget',
			'class-stacking-cards-2-widget.php' => 'Stacking_Cards_2_Widget',
			'class-features-widget.php' => 'Features_Widget',
			'class-carousel-widget.php' => 'Carousel_Widget',
			'class-carousel-swiper-widget.php' => 'Carousel_Swiper_Widget',
			'class-split-sticky-containers-widget.php' => 'Split_Sticky_Containers_Widget',
			'class-shuffle-card-widget.php' => 'Shuffle_Card_Widget',
			'class-gallery-widget.php' => 'Gallery_Widget',
			'class-gallery-multi-filters-widget.php' => 'Gallery_Multi_Filters_Widget',
			'class-faq-widget.php' => 'FAQ_Widget',
			'class-slider-news-widget.php' => 'Slider_News_Widget',
			'class-tabs-widget.php' => 'Tabs_Widget',
		];

		foreach ( $files as $file => $class_name ) {
			$path = NOVA_ADDONS_PLUGIN_DIR . 'includes/' . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
				$full_class = '\Nova_Addons_Elementor\\' . $class_name;
				if ( class_exists( $full_class ) ) {
					$widgets_manager->register( new $full_class() );
				}
			}
		}
	}

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

		$element->add_control(
			'nova_sticky_stop_selector',
			[
				'label'       => esc_html__( 'Stop selector', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '.footer',
				'description' => esc_html__( 'Sélecteur CSS de l\'élément qui doit arrêter le sticky.', NOVA_ADDONS_TEXT_DOMAIN ),
				'condition'   => [
					'nova_sticky_role' => 'wrapper',
				],
			]
		);

		$element->add_control(
			'nova_fixed_class',
			[
				'label'       => esc_html__( 'Classe CSS quand fixé', NOVA_ADDONS_TEXT_DOMAIN ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'is-sticky',
				'description' => esc_html__( 'Classe ajoutée à la colonne quand elle devient sticky.', NOVA_ADDONS_TEXT_DOMAIN ),
				'condition'   => [
					'nova_sticky_role' => 'col',
				],
			]
		);

		$element->end_controls_section();
	}

	public function apply_container_nova_settings( $element ) {
		$settings = $element->get_settings_for_display();
		$role     = isset( $settings['nova_sticky_role'] ) ? $settings['nova_sticky_role'] : '';

		if ( $role === 'wrapper' ) {
			$element->add_render_attribute( '_wrapper', 'class', 'ewd-sticky-wrapper' );
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
			$element->add_render_attribute( '_wrapper', 'style', '--ewd-sticky-top: ' . $offset_top['desktop'] . 'px;' );

			if ( ! empty( $settings['nova_sticky_add_header'] ) && $settings['nova_sticky_add_header'] === 'yes' ) {
				$selector = ! empty( $settings['nova_sticky_header_selector'] ) ? $settings['nova_sticky_header_selector'] : '#masthead, .site-header, header';
				$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-header-selector', $selector );
			}
			if ( ! empty( $settings['nova_sticky_pinned_class'] ) ) {
				$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-pinned-class', trim( $settings['nova_sticky_pinned_class'] ) );
			}
			if ( ! empty( $settings['nova_sticky_stop_selector'] ) ) {
				$element->add_render_attribute( '_wrapper', 'data-nova-sticky-stop', $settings['nova_sticky_stop_selector'] );
			}
		}

		if ( $role === 'col' ) {
			$element->add_render_attribute( '_wrapper', 'class', 'ewd-sticky-col' );
			if ( ! empty( $settings['nova_sticky_pinned_class'] ) ) {
				$element->add_render_attribute( '_wrapper', 'data-ewd-sticky-pinned-class', trim( $settings['nova_sticky_pinned_class'] ) );
			}
			if ( ! empty( $settings['nova_fixed_class'] ) ) {
				$element->add_render_attribute( '_wrapper', 'data-nova-fixed-class', $settings['nova_fixed_class'] );
			}
		}
	}

	public function flush_nova_rewrites() {
		$this->register_nova_templates_cpt();
		flush_rewrite_rules();
	}

	public function register_nova_templates_cpt() {
		$labels = [
			'name'                  => esc_html__( 'NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'singular_name'         => esc_html__( 'NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'menu_name'             => esc_html__( 'NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'name_admin_bar'        => esc_html__( 'NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'add_new'               => esc_html__( 'Add New', NOVA_ADDONS_TEXT_DOMAIN ),
			'add_new_item'          => esc_html__( 'Add New NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'new_item'              => esc_html__( 'New NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'edit_item'             => esc_html__( 'Edit NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'view_item'             => esc_html__( 'View NOVA Template', NOVA_ADDONS_TEXT_DOMAIN ),
			'all_items'             => esc_html__( 'All NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'search_items'          => esc_html__( 'Search NOVA Templates', NOVA_ADDONS_TEXT_DOMAIN ),
			'parent_item_colon'     => esc_html__( 'Parent NOVA Template:', NOVA_ADDONS_TEXT_DOMAIN ),
			'not_found'             => esc_html__( 'No NOVA Templates found.', NOVA_ADDONS_TEXT_DOMAIN ),
			'not_found_in_trash'    => esc_html__( 'No NOVA Templates found in Trash.', NOVA_ADDONS_TEXT_DOMAIN ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 90,
			'supports'           => [ 'title', 'editor', 'elementor' ],
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-layout',
			'exclude_from_search' => true,
		];

		register_post_type( 'nova_template', $args );

		register_taxonomy( 'nova_template_type', 'nova_template', [
			'label'             => esc_html__( 'Template Type', NOVA_ADDONS_TEXT_DOMAIN ),
			'rewrite'           => [ 'slug' => 'nova-template-type' ],
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		] );

		$this->pre_populate_template_types();

		$old_labels = $labels;
		$old_labels['name'] = esc_html__( 'NOVA Legacy Content', NOVA_ADDONS_TEXT_DOMAIN );
		$old_labels['menu_name'] = esc_html__( 'NOVA Legacy Content', NOVA_ADDONS_TEXT_DOMAIN );
		$old_labels['all_items'] = esc_html__( 'Legacy Mega Menus', NOVA_ADDONS_TEXT_DOMAIN );

		$old_args = $args;
		$old_args['labels'] = $old_labels;
		$old_args['menu_icon'] = 'dashicons-admin-links';

		register_post_type( 'mega_menu_content', $old_args );
	}

	public function pre_populate_template_types() {
		$terms = [
			'Header'      => 'header',
			'Footer'      => 'footer',
			'Single CPT'  => 'single',
			'Mega Menu'   => 'mega-menu',
			'Archive'     => 'archive',
			'Section'     => 'section',
		];

		foreach ( $terms as $term_name => $term_slug ) {
			if ( ! term_exists( $term_slug, 'nova_template_type' ) ) {
				wp_insert_term( $term_name, 'nova_template_type', [ 'slug' => $term_slug ] );
			}
		}
	}

	public function add_elementor_support_to_cpt( $post_types ) {
		$post_types[] = 'nova_template';
		$post_types[] = 'mega_menu_content';
		return $post_types;
	}

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

		$query = new \WP_Query( [
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
		] );

		$found = ! empty( $query->posts ) ? (int) $query->posts[0] : 0;
		return $cache[ $cache_key ] = $found;
	}

	// Stubs for other methods to ensure no fatal errors
	public function critical_loader_css() {}
	public function inline_page_loader() {}
	public function output_page_loader_html() {}
	public function enqueue_assets() {
		if ( is_admin() ) {
			return;
		}

		$this->register_assets_early();

		if ( wp_style_is( 'nova-addons-style', 'registered' ) ) {
			wp_enqueue_style( 'nova-addons-style' );
		}
		if ( wp_style_is( 'nova-icon-menu-style', 'registered' ) ) {
			wp_enqueue_style( 'nova-icon-menu-style' );
		}
		if ( wp_style_is( 'nova-fouc-fix', 'registered' ) ) {
			wp_enqueue_style( 'nova-fouc-fix' );
		}

		if ( wp_script_is( 'nova-lenis-init', 'registered' ) ) {
			wp_enqueue_script( 'nova-lenis-init' );
		}

		// Force legacy sticky-columns runtime from the known-good old plugin version.
		$old_sticky_url = plugins_url( '../elementor-mega-menu-widget-old/assets/js/sticky-columns.js', __FILE__ );
		wp_deregister_script( 'ewd-sticky-columns' );
		wp_register_script(
			'ewd-sticky-columns',
			$old_sticky_url,
			[ 'jquery', 'gsap', 'gsap-scrolltrigger', 'nova-lenis-init' ],
			NOVA_ADDONS_VERSION,
			true
		);
		wp_enqueue_script( 'ewd-sticky-columns' );
	}
	public function enqueue_page_transitions_fix() {}
	public function enqueue_editor_scripts() {}
	public function enqueue_editor_styles() {}
	public function enqueue_preview_scripts() {}
	public function force_cards_styles_in_editor() {}
	public function ajax_get_menu_items() { wp_die(); }

	/**
	 * Register all plugin scripts and styles early so Elementor
	 * can resolve widget dependencies (get_script_depends / get_style_depends).
	 */
	public function register_assets_early() {
		$url = NOVA_ADDONS_PLUGIN_URL . 'assets/';
		$v   = NOVA_ADDONS_VERSION;
		$old_base_url = plugins_url( '../elementor-mega-menu-widget-old/assets/', __FILE__ );

		// ── External libraries ──────────────────────────────────────────────
		if ( ! wp_script_is( 'gsap', 'registered' ) ) {
			wp_register_script( 'gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', [], '3.12.5', true );
		}
		if ( ! wp_script_is( 'gsap-scrolltrigger', 'registered' ) ) {
			wp_register_script( 'gsap-scrolltrigger', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js', [ 'gsap' ], '3.12.5', true );
		}

		if ( ! wp_script_is( 'swiper', 'registered' ) ) {
			wp_register_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true );
		}
		if ( ! wp_style_is( 'swiper', 'registered' ) ) {
			wp_register_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0' );
		}
		if ( ! wp_script_is( 'owl-carousel', 'registered' ) ) {
			wp_register_script( 'owl-carousel', 'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/owl.carousel.min.js', [ 'jquery' ], '2.3.4', true );
		}
		if ( ! wp_style_is( 'owl-carousel', 'registered' ) ) {
			wp_register_style( 'owl-carousel', 'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.carousel.min.css', [], '2.3.4' );
		}
		if ( ! wp_style_is( 'owl-carousel-theme', 'registered' ) ) {
			wp_register_style( 'owl-carousel-theme', 'https://cdn.jsdelivr.net/npm/owl.carousel@2.3.4/dist/assets/owl.theme.default.min.css', [], '2.3.4' );
		}

		// ── Lenis smooth scroll ─────────────────────────────────────────────
		if ( ! wp_script_is( 'lenis', 'registered' ) ) {
			// Use legacy Lenis runtime compatible with plugin-old lenis-init.js behavior.
			wp_register_script( 'lenis', 'https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js', [], '1.0.42', true );
		}
		$lenis_init_path = NOVA_ADDONS_PLUGIN_DIR . 'assets/js/lenis-init.js';
		$lenis_init_ver  = file_exists( $lenis_init_path ) ? (string) filemtime( $lenis_init_path ) : $v;
		if ( ! wp_script_is( 'nova-lenis-init', 'registered' ) ) {
			wp_register_script( 'nova-lenis-init', $url . 'js/lenis-init.js', [ 'jquery', 'lenis', 'gsap', 'gsap-scrolltrigger' ], $lenis_init_ver, true );
		}

		// ── Plugin scripts ──────────────────────────────────────────────────
		$scripts = [
			'nova-brands-script'               => [ 'brands.js',                [ 'jquery', 'swiper' ] ],
			'nova-cards-script'                => [ 'cards.js',                 [ 'jquery' ] ],
			'nova-carousel-script'             => [ 'carousel.js',              [ 'jquery', 'owl-carousel' ] ],
			'nova-carousel-swiper-script'      => [ 'carousel-swiper.js',       [ 'jquery', 'swiper' ] ],
			'nova-faq-script'                  => [ 'faq.js',                   [ 'jquery' ] ],
			'nova-features-script'             => [ 'features.js',              [ 'jquery' ] ],
			'nova-gallery-script'              => [ 'gallery.js',               [ 'jquery' ] ],
			'nova-gallery-multi-filters-script'=> [ 'gallery-multi-filters.js', [ 'jquery' ] ],
			'nova-addons-icon-menu-script'     => [ 'icon-menu.js',             [ 'jquery' ] ],
			'nova-mega-menu'                   => [ 'mega-menu.js',             [ 'jquery' ] ],
			'nova-mega-menu-mobile'            => [ 'mega-menu-mobile.js',      [ 'jquery' ] ],
			'nova-navbar-script'               => [ 'navbar.js',                [ 'jquery', 'gsap' ] ],
			'nova-scroll-hero-script'          => [ 'scroll-hero.js',           [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'nova-shuffle-card-script'         => [ 'shuffle-card.js',          [ 'jquery' ] ],
			'nova-slider-news-script'          => [ 'slider-news.js',           [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'NOVA-stacking-cards-script'       => [ 'stacking-cards.js',        [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'nova-stacking-cards-2-script'     => [ 'stacking-cards-2.js',      [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'nova-tabs-script'                 => [ 'tabs.js',                  [ 'jquery' ] ],
			'nova-title-script'                => [ 'title.js',                 [ 'jquery', 'gsap' ] ],
		];

		foreach ( $scripts as $handle => [ $file, $deps ] ) {
			$path = NOVA_ADDONS_PLUGIN_DIR . 'assets/js/' . $file;
			$ver  = file_exists( $path ) ? (string) filemtime( $path ) : $v;
			wp_deregister_script( $handle );
			wp_register_script( $handle, $url . 'js/' . $file, $deps, $ver, true );
		}

		// Force known-good legacy scroll stack from plugin-old.
		$legacy_scroll_scripts = [
			'nova-lenis-init'                => [ 'js/lenis-init.js',               [ 'jquery', 'lenis', 'gsap', 'gsap-scrolltrigger' ] ],
			'nova-scroll-hero-script'        => [ 'js/scroll-hero.js',              [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'NOVA-stacking-cards-script'     => [ 'js/stacking-cards.js',           [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'nova-stacking-cards-2-script'   => [ 'js/stacking-cards-2.js',         [ 'jquery', 'gsap', 'gsap-scrolltrigger' ] ],
			'nova-gallery-script'            => [ 'js/gallery.js',                  [ 'jquery' ] ],
			'nova-gallery-multi-filters-script' => [ 'js/gallery-multi-filters.js', [ 'jquery', 'owl-carousel' ] ],
		];

		foreach ( $legacy_scroll_scripts as $handle => [ $file, $deps ] ) {
			$old_path = WP_PLUGIN_DIR . '/elementor-mega-menu-widget-old/assets/' . $file;
			$old_ver  = file_exists( $old_path ) ? (string) filemtime( $old_path ) : $v;
			wp_deregister_script( $handle );
			wp_register_script( $handle, $old_base_url . $file, $deps, $old_ver, true );
		}

		// ── Plugin styles ───────────────────────────────────────────────────
		$styles = [
			'nova-addons-style'                  => 'style.css',
			'nova-fouc-fix'                      => 'nova-fouc-fix.css',
			'nova-brands-style'                  => 'brands-style.css',
			'nova-cards-style'                   => 'cards-style.css',
			'nova-carousel-style'                => 'carousel-style.css',
			'nova-faq-style'                     => 'faq-style.css',
			'nova-features-style'                => 'features-style.css',
			'nova-gallery-style'                 => 'gallery-style.css',
			'nova-gallery-multi-filters-style'   => 'gallery-multi-filters-style.css',
			'nova-icon-menu-style'               => 'icon-menu-style.css',
			'nova-scroll-hero-style'             => 'scroll-hero-style.css',
			'nova-shuffle-card-style'            => 'shuffle-card-style.css',
			'nova-slider-news-style'             => 'slider-news-style.css',
			'nova-split-sticky-containers-style' => 'split-sticky-containers-style.css',
			'NOVA-stacking-cards-style'          => 'stacking-cards-style.css',
			'nova-stacking-cards-2-style'        => 'stacking-cards-2-style.css',
			'nova-tabs-style'                    => 'tabs-style.css',
			'nova-title-style'                   => 'title-style.css',
		];

		foreach ( $styles as $handle => $file ) {
			$path = NOVA_ADDONS_PLUGIN_DIR . 'assets/css/' . $file;
			$ver  = file_exists( $path ) ? (string) filemtime( $path ) : $v;
			wp_deregister_style( $handle );
			wp_register_style( $handle, $url . 'css/' . $file, [], $ver );
		}
	}
}

Nova_Addons_Elementor::instance();
