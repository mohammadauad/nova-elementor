<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Repeater;
use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget Menu avec Icônes et Description Elementor.
 */
class Icon_Menu_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-icon-menu';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Icon Menu', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
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
		return [ 'nova-icon-menu-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-addons-icon-menu-script', 'nova-mega-menu-mobile' ];
	}

	/**
	 * Enregistre le style CSS du widget.
	 */
	public function print_styles() {
		wp_register_style(
			'nova-icon-menu-style',
			NOVA_ADDONS_PLUGIN_URL . 'assets/css/icon-menu-style.css',
			[],
			NOVA_ADDONS_VERSION
		);
	}

	/**
	 * Enregistre le script JS du widget.
	 */
	public function print_scripts() {
		wp_register_script(
			'nova-addons-icon-menu-script',
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/icon-menu.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);
	}

	/**
	 * Récupère la liste des menus enregistrés dans WordPress.
	 *
	 * @return array
	 */
	protected function get_available_menus() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return [];
		}

		$menus = wp_get_nav_menus();

		$options = [];

		foreach ( $menus as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}

		return $options;
	}

	/**
	 * Récupère la liste des éléments de menu pour un menu donné.
	 *
	 * @param string $menu_slug Le slug du menu.
	 * @return array
	 */
	protected function get_menu_items( $menu_slug ) {
		if ( empty( $menu_slug ) ) {
			return [];
		}

		$menu_items = wp_get_nav_menu_items( $menu_slug );
		$options = [];

		if ( ! empty( $menu_items ) ) {
			foreach ( $menu_items as $item ) {
				// Ne prendre que les éléments de premier niveau
				if ( $item->menu_item_parent == 0 ) {
					$options[ $item->title ] = $item->title;
				}
			}
		}

		return $options;
	}

	/**
	 * Récupère la liste des contenus de Mega Menu (CPT 'mega_menu_content').
	 *
	 * @return array
	 */
	protected function get_mega_menu_content_options() {
		$args = [
			'post_type'      => 'mega_menu_content',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		$posts = get_posts( $args );
		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
		}

		return $options;
	}

	/**
	 * Enregistre les scripts pour le widget.
	 */
	protected function enqueue_widget_scripts() {
		// Enregistrer le script JavaScript
		wp_enqueue_script(
			'nova-addons-widget-' . $this->get_name(),
			NOVA_ADDONS_PLUGIN_URL . 'assets/js/nova-addons-admin.js',
			[ 'jquery' ],
			NOVA_ADDONS_VERSION,
			true
		);

		wp_localize_script( 'nova-addons-widget-' . $this->get_name(), 'NOVA_addons', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'NOVA_addons_nonce' ),
		] );
	}

	/**
	 * Enregistre les contrôles du widget.
	 */
	protected function register_controls() {
		// Charger le script JavaScript directement dans le widget
		$this->enqueue_widget_scripts();

		$this->start_controls_section(
			'section_menu_content',
			[
				'label' => esc_html__( 'Menu Content', 'NOVA-addons' ),
			]
		);

		$menus = $this->get_available_menus();

		if ( ! empty( $menus ) ) {
			// Ajouter une option vide en premier
			$menu_options = [ '' => esc_html__( '-- Select a menu --', 'NOVA-addons' ) ] + $menus;
			
			$this->add_control(
				'menu_slug',
				[
					'label'   => esc_html__( 'Select Menu', 'NOVA-addons' ),
					'type'    => Controls_Manager::SELECT,
					'options' => $menu_options,
					'default' => '',
					'save_default' => false,
					'separator' => 'after',
					'description' => esc_html__( 'Choose the WordPress menu to display.', 'NOVA-addons' ),
				]
			);

			$this->add_control(
				'show_menu_title',
				[
					'label' => esc_html__( 'Afficher le titre du menu', 'NOVA-addons' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
					'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
					'default' => 'no',
					'separator' => 'before',
				]
			);

			$this->add_control(
				'menu_title_text',
				[
					'label' => esc_html__( 'Titre du menu', 'NOVA-addons' ),
					'type' => Controls_Manager::TEXT,
					'default' => '',
					'placeholder' => esc_html__( 'Entrez le titre du menu', 'NOVA-addons' ),
					'description' => esc_html__( 'Laissez vide pour utiliser le nom du menu WordPress.', 'NOVA-addons' ),
					'condition' => [
						'show_menu_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'menu_title_tag',
				[
					'label' => esc_html__( 'Niveau de titre (HTML)', 'NOVA-addons' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'h2',
					'options' => [
						'h1' => 'H1',
						'h2' => 'H2',
						'h3' => 'H3',
						'h4' => 'H4',
						'h5' => 'H5',
						'h6' => 'H6',
						'p' => 'P',
					],
					'condition' => [
						'show_menu_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'menu_title_link',
				[
					'label' => esc_html__( 'Lien du titre', 'NOVA-addons' ),
					'type' => Controls_Manager::URL,
					'placeholder' => esc_html__( 'https://votre-lien.com', 'NOVA-addons' ),
					'condition' => [
						'show_menu_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'menu_title_icon',
				[
					'label' => esc_html__( 'Icône du titre', 'NOVA-addons' ),
					'type' => Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'condition' => [
						'show_menu_title' => 'yes',
					],
				]
			);

			$this->add_control(
				'menu_title_icon_position',
				[
					'label' => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
					'type' => Controls_Manager::CHOOSE,
					'options' => [
						'before' => [
							'title' => esc_html__( 'Avant le texte', 'NOVA-addons' ),
							'icon' => 'eicon-h-align-left',
						],
						'after' => [
							'title' => esc_html__( 'Après le texte', 'NOVA-addons' ),
							'icon' => 'eicon-h-align-right',
						],
					],
					'default' => 'before',
					'condition' => [
						'show_menu_title' => 'yes',
						'menu_title_icon[value]!' => '',
					],
				]
			);

			// Accordion
			$this->add_control(
				'accordion_enable',
				[
					'label' => esc_html__( 'Activer accordion', 'NOVA-addons' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
					'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
					'default' => '',
					'separator' => 'before',
					'condition' => [
						'show_menu_title' => 'yes',
					],
					'description' => esc_html__( 'Le clic sur le titre ouvre/ferme la liste du menu.', 'NOVA-addons' ),
				]
			);

			$this->add_control(
				'accordion_icon_open',
				[
					'label' => esc_html__( 'Icône ouvert (moins)', 'NOVA-addons' ),
					'type' => Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [
						'value' => 'fas fa-minus',
						'library' => 'fa-solid',
					],
					'condition' => [
						'show_menu_title' => 'yes',
						'accordion_enable' => 'yes',
					],
				]
			);

			$this->add_control(
				'accordion_icon_closed',
				[
					'label' => esc_html__( 'Icône fermé (plus)', 'NOVA-addons' ),
					'type' => Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [
						'value' => 'fas fa-plus',
						'library' => 'fa-solid',
					],
					'condition' => [
						'show_menu_title' => 'yes',
						'accordion_enable' => 'yes',
					],
				]
			);

			$this->add_control(
				'accordion_default_open',
				[
					'label' => esc_html__( 'Ouvert par défaut', 'NOVA-addons' ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
					'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
					'default' => 'yes',
					'condition' => [
						'show_menu_title' => 'yes',
						'accordion_enable' => 'yes',
					],
				]
			);

			// Breadcrumb Mobile
			$this->add_control(
				'enable_mobile_breadcrumb',
				[
					'label' => esc_html__( 'Icône Breadcrumb', 'NOVA-addons' ),
					'type' => Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [
						'value' => 'fas fa-bars',
						'library' => 'fa-solid',
					],
					'condition' => [
						'enable_mobile_breadcrumb' => 'yes',
					],
				]
			);

			$this->add_control(
				'mobile_breadcrumb_close_icon',
				[
					'label' => esc_html__( 'Icône Fermer', 'NOVA-addons' ),
					'type' => Controls_Manager::ICONS,
					'skin' => 'inline',
					'label_block' => false,
					'default' => [
						'value' => 'fas fa-times',
						'library' => 'fa-solid',
					],
					'condition' => [
						'enable_mobile_breadcrumb' => 'yes',
					],
					'description' => esc_html__( 'Icône pour le bouton de fermeture du popup.', 'NOVA-addons' ),
				]
			);

			$mega_menu_template_options = $this->get_mega_menu_content_options();
			if ( ! empty( $mega_menu_template_options ) ) {
				$this->add_control(
					'mobile_breadcrumb_template',
					[
						'label' => esc_html__( 'Template NOVA Mega Menu', 'NOVA-addons' ),
						'type' => Controls_Manager::SELECT,
						'options' => $mega_menu_template_options,
						'default' => ! empty( $mega_menu_template_options ) ? array_keys( $mega_menu_template_options )[0] : '',
						'description' => esc_html__( 'Sélectionnez le template à afficher lors du clic sur le breadcrumb.', 'NOVA-addons' ),
						'condition' => [
							'enable_mobile_breadcrumb' => 'yes',
						],
					]
				);
			} else {
				$this->add_control(
					'mobile_breadcrumb_template_notice',
					[
						'type' => Controls_Manager::RAW_HTML,
						'raw' => '<strong>' . esc_html__( 'Aucun template trouvé.', 'NOVA-addons' ) . '</strong><br>' . sprintf( esc_html__( 'Créez un nouveau template via "NOVA Mega Menus" dans le tableau de bord WordPress. %s', 'NOVA-addons' ), '<a href="' . admin_url( 'post-new.php?post_type=mega_menu_content' ) . '" target="_blank">Créer un template</a>' ),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
						'condition' => [
							'enable_mobile_breadcrumb' => 'yes',
						],
					]
				);
			}

			// Configuration responsive pour l'affichage du breadcrumb
			$this->add_responsive_control(
				'mobile_breadcrumb_breakpoint',
				[
					'label' => esc_html__( 'Largeur maximale d\'affichage', 'NOVA-addons' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 320,
							'max' => 1920,
							'step' => 10,
						],
					],
					'default' => [
						'size' => 1024,
						'unit' => 'px',
					],
					'tablet_default' => [
						'size' => 768,
						'unit' => 'px',
					],
					'mobile_default' => [
						'size' => 480,
						'unit' => 'px',
					],
					'description' => esc_html__( 'Le breadcrumb s\'affichera lorsque la largeur de l\'écran est inférieure ou égale à cette valeur.', 'NOVA-addons' ),
					'condition' => [
						'enable_mobile_breadcrumb' => 'yes',
					],
					'selectors' => [
						'{{WRAPPER}} .nova-mobile-breadcrumb' => '--breadcrumb-breakpoint-desktop: {{SIZE}}{{UNIT}};',
					],
				]
			);
		} else {
			$this->add_control(
				'menu_slug_notice',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw' => '<strong>' . esc_html__( 'No menu found.', 'NOVA-addons' ) . '</strong><br>' . esc_html__( 'Please create a menu in WordPress admin.', 'NOVA-addons' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				]
			);
		}

		$repeater = new Repeater();

		$repeater->add_control(
			'menu_item_title',
			[
				'label'       => esc_html__( 'Menu Item', 'NOVA-addons' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => [],
				'description' => esc_html__( 'Select the menu item to customize.', 'NOVA-addons' ),
				'label_block' => true,
				'multiple'    => false,
				'select2options' => [
					'placeholder' => esc_html__( 'Select a menu item...', 'NOVA-addons' ),
					'allowClear' => true,
				],
			]
		);

		$repeater->add_control(
			'menu_item_icon',
			[
				'label' => esc_html__( 'Icône (bibliothèque)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'condition' => [
					'menu_item_icon_type' => 'icon',
				],
				'skin' => 'inline',
				'label_block' => false,
				'description' => esc_html__( 'Icône issue de la bibliothèque Elementor / Font Awesome.', 'NOVA-addons' ),
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],
			]
		);

		$repeater->add_control(
			'menu_item_icon_image',
			[
				'label' => esc_html__( 'Image', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'media_types' => [ 'image', 'svg' ],
				'button_text' => esc_html__( 'Choisir une image', 'NOVA-addons' ),
				'description' => esc_html__( 'Formats acceptés : JPG, PNG, WEBP, AVIF, ICO, SVG…', 'NOVA-addons' ),
				'condition' => [
					'menu_item_icon_type' => 'image',
				],
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'menu_item_description',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'type' => Controls_Manager::WYSIWYG,
				'default' => '',
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'menu_item_icon_heading',
			[
				'label' => esc_html__( 'Icône', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'menu_item_icon_type',
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
						'title' => esc_html__( 'Aucune', 'NOVA-addons' ),
						'icon'  => 'eicon-ban',
					],
				],
				'default' => 'icon',
			]
		);

		// Message d'aide simple (le JavaScript est maintenant dans nova-addons-admin.js)
		$help_text = '<div style="padding: 10px; background: #e8f4f8; border-left: 3px solid #00a0d2; margin: 10px 0;">';
		$help_text .= '<strong>' . esc_html__( 'How to use:', 'NOVA-addons' ) . '</strong><br>';
		$help_text .= esc_html__( '1. Select a menu from "Select Menu" above', 'NOVA-addons' ) . '<br>';
		$help_text .= esc_html__( '2. The menu items will load automatically in the dropdown below', 'NOVA-addons' ) . '<br>';
		$help_text .= esc_html__( '3. Select a menu item, add an icon and description', 'NOVA-addons' );
		$help_text .= '</div>';
		
		$this->add_control(
			'menu_items_help',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => $help_text,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'icon_menu_items',
			[
				'label'   => esc_html__( 'Icon & Description Items', 'NOVA-addons' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'title_field' => '{{{ menu_item_title }}}',
				'description' => esc_html__( 'Associate an icon and description to each menu item.', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// Section de Style - Menu Principal
		$this->start_controls_section(
			'section_menu_style',
			[
				'label' => esc_html__( 'Style du Menu', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// --- Container du Menu ---
		$this->add_control(
			'menu_container_heading',
			[
				'label' => esc_html__( 'Container du Menu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'menu_container_align',
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
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-container' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_container_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_container_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// --- Liste du Menu ---
		$this->add_control(
			'menu_list_heading',
			[
				'label' => esc_html__( 'Liste du Menu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		// Orientation du menu
		$this->add_control(
			'menu_orientation',
			[
				'label' => esc_html__( 'Orientation', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => esc_html__( 'Horizontal', 'NOVA-addons' ),
					'vertical' => esc_html__( 'Vertical', 'NOVA-addons' ),
				],
				'prefix_class' => 'nova-icon-menu-',
			]
		);

		// Mode sous-menus en dessous (pour mode vertical)
		$this->add_control(
			'submenu_below_enable',
			[
				'label' => esc_html__( 'Sous-menus en dessous', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'return_value' => 'yes',
				'default' => 'no',
				'condition' => [
					'menu_orientation' => 'vertical',
				],
				'description' => esc_html__( 'Active le mode où les sous-menus s\'affichent en dessous des items au lieu de sur le côté (mode vertical uniquement).', 'NOVA-addons' ),
			]
		);

		// Déterminer l'événement d'ouverture/fermeture des sous-menus
		$this->add_control(
			'submenu_trigger_event',
			[
				'label'       => esc_html__( 'Événement sous-menu', 'NOVA-addons' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'hover',
				'options'     => [
					'hover' => esc_html__( 'Hover', 'NOVA-addons' ),
					'click' => esc_html__( 'Click', 'NOVA-addons' ),
					'both'  => esc_html__( 'Click + Hover', 'NOVA-addons' ),
				],
				'prefix_class' => 'nova-submenu-trigger-',
				'description' => esc_html__( 'Choisissez comment afficher/masquer les sous-menus sur desktop. Sur mobile, le comportement reste au clic.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'menu_list_display',
			[
				'label' => esc_html__( 'Type d\'affichage', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex',
				'options' => [
					'block' => esc_html__( 'Block', 'NOVA-addons' ),
					'flex' => esc_html__( 'Flex', 'NOVA-addons' ),
					'inline-flex' => esc_html__( 'Inline Flex', 'NOVA-addons' ),
					'grid' => esc_html__( 'Grid', 'NOVA-addons' ),
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list' => 'display: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_list_flex_direction',
			[
				'label' => esc_html__( 'Direction Flex', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'row',
				'options' => [
					'row' => esc_html__( 'Row (Horizontal)', 'NOVA-addons' ),
					'column' => esc_html__( 'Column (Vertical)', 'NOVA-addons' ),
					'row-reverse' => esc_html__( 'Row Reverse', 'NOVA-addons' ),
					'column-reverse' => esc_html__( 'Column Reverse', 'NOVA-addons' ),
				],
				'condition' => [
					'menu_list_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_list_justify_content',
			[
				'label' => esc_html__( 'Justification (Horizontal)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'space-between' => esc_html__( 'Espace entre', 'NOVA-addons' ),
					'space-around' => esc_html__( 'Espace autour', 'NOVA-addons' ),
					'space-evenly' => esc_html__( 'Espace égal', 'NOVA-addons' ),
				],
				'condition' => [
					'menu_list_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_list_align_items',
			[
				'label' => esc_html__( 'Alignement (Vertical)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'center',
				'options' => [
					'flex-start' => esc_html__( 'Début', 'NOVA-addons' ),
					'flex-end' => esc_html__( 'Fin', 'NOVA-addons' ),
					'center' => esc_html__( 'Centre', 'NOVA-addons' ),
					'stretch' => esc_html__( 'Étirer', 'NOVA-addons' ),
					'baseline' => esc_html__( 'Baseline', 'NOVA-addons' ),
				],
				'condition' => [
					'menu_list_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_list_gap',
			[
				'label' => esc_html__( 'Espacement (Gap)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_list_wrap',
			[
				'label' => esc_html__( 'Retour à la ligne', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'nowrap',
				'options' => [
					'nowrap' => esc_html__( 'Pas de retour', 'NOVA-addons' ),
					'wrap' => esc_html__( 'Retour', 'NOVA-addons' ),
					'wrap-reverse' => esc_html__( 'Retour inversé', 'NOVA-addons' ),
				],
				'condition' => [
					'menu_list_display' => [ 'flex', 'inline-flex' ],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list' => 'flex-wrap: {{VALUE}};',
				],
			]
		);

		// Ancien contrôle gap (conservé pour compatibilité)
		$this->add_responsive_control(
			'menu_items_gap',
			[
				'label' => esc_html__( 'Espacement entre items (Legacy)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}.nova-icon-menu-horizontal .nova-icon-menu-list > li' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.nova-icon-menu-horizontal .nova-icon-menu-list > li:last-child' => 'margin-right: 0;',
					'{{WRAPPER}}.nova-icon-menu-vertical .nova-icon-menu-list > li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.nova-icon-menu-vertical .nova-icon-menu-list > li:last-child' => 'margin-bottom: 0;',
				],
				'separator' => 'before',
			]
		);

		// --- Items du Menu ---
		$this->add_control(
			'menu_items_heading',
			[
				'label' => esc_html__( 'Items du Menu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'menu_item_text_align',
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
				'selectors_dictionary' => [
					'left' => 'flex-start',
					'center' => 'center',
					'right' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li > a' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_item_inner_text_align',
			[
				'label' => esc_html__( 'Alignement du texte interne', 'NOVA-addons' ),
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
				'default' => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li > a .nova-icon-menu-text-wrap' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'menu_item_typography',
				'label' => esc_html__( 'Typography', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list > li > a',
			]
		);

		$this->add_responsive_control(
			'menu_item_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_item_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Onglets Normal/Hover/Active
		$this->start_controls_tabs( 'menu_item_style_tabs' );

		// Onglet Normal
		$this->start_controls_tab(
			'menu_item_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_item_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_item_bg',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-list > li > a',
			]
		);

		$this->end_controls_tab();

		// Onglet Hover
		$this->start_controls_tab(
			'menu_item_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_item_text_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li > a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_item_bg_hover',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-list > li > a:hover',
			]
		);

		$this->end_controls_tab();

		// Onglet Active
		$this->start_controls_tab(
			'menu_item_active',
			[
				'label' => esc_html__( 'Active', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_item_text_color_active',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li.current-menu-item > a, {{WRAPPER}} .nova-icon-menu-list > li.current-menu-ancestor > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_item_bg_active',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-list > li.current-menu-item > a, {{WRAPPER}} .nova-icon-menu-list > li.current-menu-ancestor > a',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Border
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'menu_item_border',
				'label' => esc_html__( 'Border', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list > li > a',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'menu_item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list > li > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Box Shadow
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'menu_item_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list > li > a',
			]
		);

		$this->end_controls_section();

		// Section Style - Layout du Wrapper Texte
		$this->start_controls_section(
			'section_text_wrapper_style',
			[
				'label' => esc_html__( 'Layout Texte', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_wrapper_heading',
			[
				'label' => esc_html__( 'Wrapper Texte (Titre + Description)', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'text_wrapper_flex_direction',
			[
				'label' => esc_html__( 'Direction Flex', 'NOVA-addons' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row' => [
						'title' => esc_html__( 'Horizontal', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-right',
					],
					'column' => [
						'title' => esc_html__( 'Vertical', 'NOVA-addons' ),
						'icon' => 'eicon-arrow-down',
					],
				],
				'default' => 'column',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'display: flex; flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_gap',
			[
				'label' => esc_html__( 'Gap (Espacement)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 4,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_align_items',
			[
				'label' => esc_html__( 'Alignement Vertical', 'NOVA-addons' ),
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
						'title' => esc_html__( 'Stretch', 'NOVA-addons' ),
						'icon' => 'eicon-v-align-stretch',
					],
				],
				'default' => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'align-items: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_justify_content',
			[
				'label' => esc_html__( 'Alignement Horizontal', 'NOVA-addons' ),
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
						'title' => esc_html__( 'Space Between', 'NOVA-addons' ),
						'icon' => 'eicon-h-align-stretch',
					],
				],
				'default' => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'auto' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_wrapper_margin',
			[
				'label' => esc_html__( 'Margin', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-text-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Titre et Description
		$this->start_controls_section(
			'section_text_style',
			[
				'label' => esc_html__( 'Titre et Description', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Typography pour le titre
		$this->add_control(
			'menu_title_heading',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'menu_title_typography',
				'label' => esc_html__( 'Typography', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-title',
			]
		);

		// Typography pour la description
		$this->add_control(
			'menu_description_heading',
			[
				'label' => esc_html__( 'Description', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'menu_description_typography',
				'label' => esc_html__( 'Typography', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-description',
			]
		);

		$this->add_control(
			'menu_description_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_description_spacing',
			[
				'label' => esc_html__( 'Espacement au-dessus', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-description' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Section Style - Icônes
		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => esc_html__( 'Icônes', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'menu_icon_size',
			[
				'label' => esc_html__( 'Largeur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
					'em' => [
						'min' => 0.5,
						'max' => 5,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-icon' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-icon-menu-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-icon-menu-icon img' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_icon_height',
			[
				'label' => esc_html__( 'Hauteur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', 'auto' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
					'em' => [
						'min' => 0.5,
						'max' => 5,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit' => 'auto',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-icon' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-icon-menu-icon img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'menu_icon_color',
			[
				'label' => esc_html__( 'Couleur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-icon-menu-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_icon_spacing',
			[
				'label' => esc_html__( 'Espacement à droite', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Icône Dropdown
		$this->add_control(
			'dropdown_icon_heading',
			[
				'label' => esc_html__( 'Icône Dropdown', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'dropdown_icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-dropdown-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .NOVA-dropdown-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'dropdown_icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .NOVA-dropdown-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .NOVA-dropdown-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_icon_spacing',
			[
				'label' => esc_html__( 'Espacement avec le texte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 8,
					'unit' => 'px',
				],
				'selectors' => [
					// Position droite : margin-left
					'{{WRAPPER}}.nova-dropdown-position-right .NOVA-dropdown-icon' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: 0;',
					'{{WRAPPER}}:not(.nova-dropdown-position-left):not(.nova-dropdown-position-right) .NOVA-dropdown-icon' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: 0;',
					// Position gauche : margin-right
					'{{WRAPPER}}.nova-dropdown-position-left .NOVA-dropdown-icon' => 'margin-right: {{SIZE}}{{UNIT}}; margin-left: 0;',
				],
				'description' => esc_html__( 'L\'espacement s\'adapte automatiquement selon la position de l\'icône (gauche ou droite)', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// Section pour les sous-menus
		$this->start_controls_section(
			'section_submenu_style',
			[
				'label' => esc_html__( 'Sous-Menus', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Icône dropdown pour les sous-menus
		$this->add_control(
			'submenu_dropdown_icon',
			[
				'label' => esc_html__( 'Icône Dropdown', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'description' => esc_html__( 'Icône affichée pour les éléments avec sous-menus', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'dropdown_icon_position',
			[
				'label' => esc_html__( 'Position de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => [
					'left' => esc_html__( 'Gauche', 'NOVA-addons' ),
					'right' => esc_html__( 'Droite', 'NOVA-addons' ),
				],
				'prefix_class' => 'nova-dropdown-position-',
			]
		);

		$this->add_responsive_control(
			'dropdown_icon_width',
			[
				'label' => esc_html__( 'Largeur de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'vw' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .NOVA-dropdown-icon' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}}; justify-content: center;',
				],
			]
		);

		// Titre: Conteneur du sous-menu
		$this->add_control(
			'submenu_container_heading',
			[
				'label' => esc_html__( 'Conteneur du Sous-Menu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'submenu_background_color',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_min_width',
			[
				'label' => esc_html__( 'Largeur minimale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu' => 'min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'submenu_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu',
			]
		);

		// Titre: Mode Vertical - Sous-menus en dessous
		$this->add_control(
			'submenu_below_heading',
			[
				'label' => esc_html__( 'Mode Vertical - Sous-menus en dessous', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'description' => esc_html__( 'Ces styles s\'appliquent uniquement lorsque la classe "nova-submenu-below" est ajoutée au conteneur du menu en mode vertical.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'submenu_below_margin_left',
			[
				'label' => esc_html__( 'Marge à gauche', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
					'%' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}.nova-icon-menu-vertical.nova-submenu-below .nova-icon-menu-list > li > .sub-menu' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'submenu_below_border',
				'label' => esc_html__( 'Bordure gauche', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}}.nova-icon-menu-vertical.nova-submenu-below .nova-icon-menu-list > li > .sub-menu',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width' => [
						'default' => [
							'top' => '0',
							'right' => '0',
							'bottom' => '0',
							'left' => '2',
							'unit' => 'px',
							'isLinked' => false,
						],
					],
					'color' => [
						'default' => 'rgba(0, 0, 0, 0.1)',
					],
				],
			]
		);

		$this->add_responsive_control(
			'submenu_below_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'%' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}}.nova-icon-menu-vertical.nova-submenu-below .nova-icon-menu-list > li > .sub-menu' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'submenu_below_box_shadow',
				'label' => esc_html__( 'Ombre portée', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}}.nova-icon-menu-vertical.nova-submenu-below .nova-icon-menu-list > li > .sub-menu',
			]
		);

		// Titre: Items du sous-menu
		$this->add_control(
			'submenu_items_heading',
			[
				'label' => esc_html__( 'Items du Sous-Menu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'submenu_item_text_align',
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
				'selectors_dictionary' => [
					'left' => 'flex-start',
					'center' => 'center',
					'right' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu > li > a' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_item_inner_text_align',
			[
				'label' => esc_html__( 'Alignement du texte interne', 'NOVA-addons' ),
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
				'default' => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu > li > a .nova-icon-menu-text-wrap' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'submenu_item_typography',
				'label' => esc_html__( 'Typography', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu a',
			]
		);

		$this->add_responsive_control(
			'submenu_item_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_item_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_items_gap',
			[
				'label' => esc_html__( 'Espacement entre items (Legacy)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu li:last-child' => 'margin-bottom: 0;',
				],
				'separator' => 'before',
			]
		);

		// Onglets pour les items du sous-menu
		$this->start_controls_tabs( 'submenu_item_style_tabs' );

		// Normal
		$this->start_controls_tab(
			'submenu_item_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'submenu_item_text_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'submenu_item_bg',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu a',
			]
		);

		$this->end_controls_tab();

		// Hover
		$this->start_controls_tab(
			'submenu_item_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'submenu_item_text_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'submenu_item_bg_hover',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu a:hover',
			]
		);

		$this->end_controls_tab();

		// Active
		$this->start_controls_tab(
			'submenu_item_active',
			[
				'label' => esc_html__( 'Active', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'submenu_item_text_color_active',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu .current-menu-item > a, {{WRAPPER}} .nova-icon-menu-list .sub-menu .current-menu-ancestor > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'submenu_item_bg_active',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu .current-menu-item > a, {{WRAPPER}} .nova-icon-menu-list .sub-menu .current-menu-ancestor > a',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Border
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'submenu_item_border',
				'label' => esc_html__( 'Border', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu a',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'submenu_item_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-list .sub-menu a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Box Shadow
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'submenu_item_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-list .sub-menu a',
			]
		);

		$this->end_controls_section();

		// Section Style - Menu Title
		$this->start_controls_section(
			'section_style_menu_title',
			[
				'label' => esc_html__( 'Titre du menu', 'NOVA-addons' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_menu_title' => 'yes',
				],
			]
		);

		// Heading pour le titre
		$this->add_control(
			'menu_header_title_heading',
			[
				'label' => esc_html__( 'Titre', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'menu_title_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-icon-menu-header-title-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'menu_header_title_typography_',
				'label' => esc_html__( 'Typography', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-header-title-text',
			]
		);

		$this->add_responsive_control(
			'menu_title_margin',
			[
				'label' => esc_html__( 'Margin', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_title_padding',
			[
				'label' => esc_html__( 'Padding', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_title_align',
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
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_title_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-header-title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'menu_title_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-header-title',
			]
		);

		$this->add_responsive_control(
			'menu_title_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'menu_title_box_shadow',
				'label' => esc_html__( 'Ombre portée', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-icon-menu-header-title',
			]
		);

		// --- Icône du titre ---
		$this->add_control(
			'menu_header_title_icon_heading',
			[
				'label' => esc_html__( 'Icône du titre', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'menu_title_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'menu_title_icon_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 100,
					],
					'em' => [
						'min' => 0.5,
						'max' => 5,
						'step' => 0.1,
					],
					'rem' => [
						'min' => 0.5,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-icon-menu-header-title-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-icon-menu-header-title-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'menu_title_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'menu_title_icon_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-icon-menu-header-title-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-icon-menu-header-title-icon svg, {{WRAPPER}} .nova-icon-menu-header-title-icon svg *' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
				'condition' => [
					'menu_title_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'menu_title_icon_gap',
			[
				'label' => esc_html__( 'Espacement avec le texte', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
					'em' => [
						'min' => 0,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 6,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title--has-icon .nova-icon-menu-header-title-inner' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'menu_title_icon[value]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'menu_title_icon_vertical_align',
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
					'{{WRAPPER}} .nova-icon-menu-header-title--has-icon .nova-icon-menu-header-title-inner' => 'align-items: {{VALUE}};',
				],
				'condition' => [
					'menu_title_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'menu_title_heading_hover',
			[
				'label' => esc_html__( 'Au survol (Hover)', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'menu_title_color_hover',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-icon-menu-header-title-link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_title_background_hover',
				'label' => esc_html__( 'Fond (hover)', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-icon-menu-header-title:hover, {{WRAPPER}} .nova-icon-menu-header-title-link:hover',
			]
		);

		$this->add_control(
			'menu_title_heading_active',
			[
				'label' => esc_html__( 'Page active', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'description' => esc_html__( 'Quand le lien du titre pointe vers la page affichée.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_title_color_active',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-icon-menu-header-title-link.nova-icon-menu-header-title-link--current' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_section();

		// --- Section Style et Animation du Popup Mobile ---
		$this->start_controls_section(
			'section_mobile_popup_style',
			[
				'label' => esc_html__( 'Popup Mobile', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_mobile_breadcrumb' => 'yes',
				],
			]
		);

		// --- Sous-section : Bouton Toggle ---
		$this->add_control(
			'popup_toggle_heading',
			[
				'label' => esc_html__( 'Bouton Toggle', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_responsive_control(
			'popup_toggle_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle' => 'width: {{SIZE}}px; height: {{SIZE}}px; min-width: {{SIZE}}px; min-height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'popup_toggle_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => '10',
					'right' => '10',
					'bottom' => '10',
					'left' => '10',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_toggle_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_toggle_icon_size',
			[
				'label' => esc_html__( 'Taille de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle i' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'popup_toggle_style_tabs' );

		$this->start_controls_tab(
			'popup_toggle_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'popup_toggle_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'popup_toggle_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-toggle',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'popup_toggle_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'popup_toggle_color_hover',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle:hover svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'popup_toggle_background_hover',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-toggle:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'popup_toggle_border_heading',
			[
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'popup_toggle_border',
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-toggle',
			]
		);

		$this->add_responsive_control(
			'popup_toggle_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'popup_toggle_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-toggle',
			]
		);

		$this->add_control(
			'popup_toggle_transition',
			[
				'label' => esc_html__( 'Transition', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.3,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-toggle' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		// --- Sous-section : Overlay ---
		$this->add_control(
			'popup_overlay_heading',
			[
				'label' => esc_html__( 'Overlay', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'popup_overlay_background',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => 'rgba(0, 0, 0, 0.5)',
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_overlay_opacity',
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
					'size' => 0.5,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-overlay' => 'opacity: {{SIZE}};',
				],
			]
		);

		// --- Sous-section : Popup Content ---
		$this->add_control(
			'popup_content_heading',
			[
				'label' => esc_html__( 'Contenu du Popup', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'popup_width',
			[
				'label' => esc_html__( 'Largeur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1920,
						'step' => 10,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 90,
					'unit' => '%',
				],
				'tablet_default' => [
					'size' => 85,
					'unit' => '%',
				],
				'mobile_default' => [
					'size' => 100,
					'unit' => '%',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-content' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_height',
			[
				'label' => esc_html__( 'Hauteur', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vh' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 2000,
						'step' => 10,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 100,
					'unit' => 'vh',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-content' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'popup_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-content',
				'default' => '#ffffff',
			]
		);

		$this->add_responsive_control(
			'popup_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_border_heading',
			[
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'popup_border',
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-content',
			]
		);

		$this->add_responsive_control(
			'popup_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'popup_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-content',
			]
		);

		// --- Sous-section : Animation ---
		$this->add_control(
			'popup_animation_heading',
			[
				'label' => esc_html__( 'Animation', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'popup_animation_type',
			[
				'label' => esc_html__( 'Type d\'animation', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => [
					'slide' => esc_html__( 'Slide (Glissement)', 'NOVA-addons' ),
					'fade' => esc_html__( 'Fade (Fondu)', 'NOVA-addons' ),
					'scale' => esc_html__( 'Scale (Zoom)', 'NOVA-addons' ),
					'slide-up' => esc_html__( 'Slide Up (Glissement vers le haut)', 'NOVA-addons' ),
					'slide-down' => esc_html__( 'Slide Down (Glissement vers le bas)', 'NOVA-addons' ),
					'slide-right' => esc_html__( 'Slide Right (Glissement vers la droite)', 'NOVA-addons' ),
					'rotate' => esc_html__( 'Rotate (Rotation)', 'NOVA-addons' ),
					'flip' => esc_html__( 'Flip (Retournement)', 'NOVA-addons' ),
				],
			]
		);

		$this->add_control(
			'popup_animation_duration',
			[
				'label' => esc_html__( 'Durée (secondes)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0.1,
						'max' => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.3,
				],
			]
		);

		$this->add_control(
			'popup_animation_easing',
			[
				'label' => esc_html__( 'Easing (Courbe)', 'NOVA-addons' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'power2.out',
				'options' => [
					'none' => esc_html__( 'Aucun', 'NOVA-addons' ),
					'power1.in' => esc_html__( 'Power1 In', 'NOVA-addons' ),
					'power1.out' => esc_html__( 'Power1 Out', 'NOVA-addons' ),
					'power1.inOut' => esc_html__( 'Power1 InOut', 'NOVA-addons' ),
					'power2.in' => esc_html__( 'Power2 In', 'NOVA-addons' ),
					'power2.out' => esc_html__( 'Power2 Out', 'NOVA-addons' ),
					'power2.inOut' => esc_html__( 'Power2 InOut', 'NOVA-addons' ),
					'power3.in' => esc_html__( 'Power3 In', 'NOVA-addons' ),
					'power3.out' => esc_html__( 'Power3 Out', 'NOVA-addons' ),
					'power3.inOut' => esc_html__( 'Power3 InOut', 'NOVA-addons' ),
					'power4.in' => esc_html__( 'Power4 In', 'NOVA-addons' ),
					'power4.out' => esc_html__( 'Power4 Out', 'NOVA-addons' ),
					'power4.inOut' => esc_html__( 'Power4 InOut', 'NOVA-addons' ),
					'back.in' => esc_html__( 'Back In', 'NOVA-addons' ),
					'back.out' => esc_html__( 'Back Out', 'NOVA-addons' ),
					'back.inOut' => esc_html__( 'Back InOut', 'NOVA-addons' ),
					'elastic.in' => esc_html__( 'Elastic In', 'NOVA-addons' ),
					'elastic.out' => esc_html__( 'Elastic Out', 'NOVA-addons' ),
					'elastic.inOut' => esc_html__( 'Elastic InOut', 'NOVA-addons' ),
					'bounce.in' => esc_html__( 'Bounce In', 'NOVA-addons' ),
					'bounce.out' => esc_html__( 'Bounce Out', 'NOVA-addons' ),
					'bounce.inOut' => esc_html__( 'Bounce InOut', 'NOVA-addons' ),
				],
			]
		);

		$this->add_control(
			'popup_animation_delay',
			[
				'label' => esc_html__( 'Délai (secondes)', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0,
				],
			]
		);

		// --- Sous-section : Bouton Close ---
		$this->add_control(
			'popup_close_heading',
			[
				'label' => esc_html__( 'Bouton Fermer', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'popup_close_position_top',
			[
				'label' => esc_html__( 'Position Top', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 15,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_close_position_right',
			[
				'label' => esc_html__( 'Position Right', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 15,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_close_size',
			[
				'label' => esc_html__( 'Taille', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'width: {{SIZE}}px; height: {{SIZE}}px; min-width: {{SIZE}}px; min-height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'popup_close_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => '5',
					'right' => '10',
					'bottom' => '5',
					'left' => '10',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'popup_close_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'popup_close_icon_size',
			[
				'label' => esc_html__( 'Taille de l\'icône', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-mobile-breadcrumb-close-icon i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .nova-mobile-breadcrumb-close-icon' => 'font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'popup_close_style_tabs' );

		$this->start_controls_tab(
			'popup_close_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'popup_close_color',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#333333',
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_close_background',
			[
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'popup_close_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'popup_close_color_hover',
			[
				'label' => esc_html__( 'Couleur', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'popup_close_background_hover',
			[
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'popup_close_border_heading',
			[
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'popup_close_border',
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-close',
			]
		);

		$this->add_responsive_control(
			'popup_close_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'popup_close_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mobile-breadcrumb-close',
			]
		);

		$this->add_control(
			'popup_close_transition',
			[
				'label' => esc_html__( 'Transition', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0.3,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-close' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		// --- Sous-section : Template Wrapper ---
		$this->add_control(
			'popup_template_heading',
			[
				'label' => esc_html__( 'Contenu Template', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'popup_template_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => '60',
					'right' => '20',
					'bottom' => '20',
					'left' => '20',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-template-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Rendu du widget sur le frontend.
	 */
	protected function render() {
		// ✅ IMPORTANT: Forcer l'enqueue des styles dans tous les cas
		// Cela garantit que les styles sont chargés même en mode déconnecté
		wp_enqueue_style( 'nova-icon-menu-style' );
		wp_enqueue_script( 'nova-addons-icon-menu-script' );
		
		// ✅ Forcer la génération et le chargement du CSS inline pour le post actuel
		// Cela garantit que les styles Elementor inline sont chargés même en mode déconnecté
		$current_post_id = get_the_ID();
		if ( $current_post_id && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			$current_css = \Elementor\Core\Files\CSS\Post::create( $current_post_id );
			// Mettre à jour le CSS pour s'assurer qu'il est généré
			$current_css->update();
			// Enqueuer le CSS
			$current_css->enqueue();
		}
		
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['menu_slug'] ) ) {
			return;
		}

		// Préparer le menu HTML pour le popup si nécessaire
		$menu_html_for_popup = '';
		$is_mobile_breadcrumb_enabled = ! empty( $settings['enable_mobile_breadcrumb'] ) && 'yes' === $settings['enable_mobile_breadcrumb'];
		$template_id = $is_mobile_breadcrumb_enabled && ! empty( $settings['mobile_breadcrumb_template'] ) ? $settings['mobile_breadcrumb_template'] : '';
		
		// ✅ FALLBACK: Si aucun template n'est défini, essayer de trouver le premier template disponible
		if ( $is_mobile_breadcrumb_enabled && empty( $template_id ) ) {
			$templates = get_posts( [
				'post_type'      => 'mega_menu_content',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			] );
			
			if ( ! empty( $templates ) ) {
				$template_id = $templates[0]->ID;
			}
		}
		
		// Si le breadcrumb mobile est activé mais qu'aucun template n'est défini, préparer le menu pour le popup
		if ( $is_mobile_breadcrumb_enabled && empty( $template_id ) ) {
			// Inclure le Walker personnalisé pour ce widget.
			require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-icon-menu-walker.php';

			// Préparer les données pour le Walker.
			$icon_menu_map = [];
			if ( ! empty( $settings['icon_menu_items'] ) ) {
				foreach ( $settings['icon_menu_items'] as $item ) {
					if ( ! empty( $item['menu_item_title'] ) ) {
						// Utiliser l'ID du menu item directement (pas le titre)
						$item_id = absint( $item['menu_item_title'] );
						$icon_type = isset( $item['menu_item_icon_type'] ) ? $item['menu_item_icon_type'] : 'icon';
						$icon_menu_map[ $item_id ] = [
							'type'        => $icon_type,
							'icon'        => isset( $item['menu_item_icon'] ) ? $item['menu_item_icon'] : [],
							'image'       => isset( $item['menu_item_icon_image'] ) ? $item['menu_item_icon_image'] : [],
							'description' => isset( $item['menu_item_description'] ) ? $item['menu_item_description'] : '',
						];
					}
				}
			}

			// Préparer les paramètres du widget pour le Walker
			$walker_settings = [
				'icon_menu_map' => $icon_menu_map,
				'dropdown_icon' => $settings['submenu_dropdown_icon'],
				'dropdown_icon_position' => $settings['dropdown_icon_position'],
			];

			// Construire la classe du conteneur
			$container_class = 'nova-icon-menu-container nova-mobile-mode';
			// Ajouter la classe submenu-below si activé et en mode vertical
			if ( ! empty( $settings['submenu_below_enable'] ) && 'yes' === $settings['submenu_below_enable'] && 
			     ! empty( $settings['menu_orientation'] ) && 'vertical' === $settings['menu_orientation'] ) {
				$container_class .= ' nova-submenu-below';
			}

			$menu_args = [
				'menu'            => $settings['menu_slug'],
				'menu_class'      => 'nova-icon-menu-list',
				'container'       => 'nav',
				'container_class' => $container_class,
				'fallback_cb'     => false,
				'walker'          => new \NOVA_Addons_Elementor\Icon_Menu_Walker( $walker_settings ),
				'echo'            => false, // Ne pas afficher, retourner le HTML
			];

			// Capturer le HTML du menu (wp_nav_menu retourne le HTML quand echo = false)
			// Note: wp_nav_menu peut retourner false, une chaîne vide, ou null si le menu n'existe pas
			$menu_html_for_popup = wp_nav_menu( $menu_args );
			
			// Debug: vérifier si le menu est bien généré
			// wp_nav_menu peut retourner false, null, ou une chaîne vide
			if ( empty( $menu_html_for_popup ) || false === $menu_html_for_popup ) {
				// Si le menu est vide, essayer avec ob_start/ob_get_clean comme fallback
				ob_start();
				$menu_result = wp_nav_menu( array_merge( $menu_args, [ 'echo' => true ] ) );
				$menu_html_for_popup = ob_get_clean();
				
				// Si toujours vide, essayer sans le walker pour voir si c'est le walker qui pose problème
				if ( empty( $menu_html_for_popup ) ) {
					ob_start();
					$menu_args_no_walker = $menu_args;
					unset( $menu_args_no_walker['walker'] );
					wp_nav_menu( array_merge( $menu_args_no_walker, [ 'echo' => true ] ) );
					$menu_html_for_popup = ob_get_clean();
				}
			}
		}

		// Afficher le breadcrumb mobile si activé
		if ( $is_mobile_breadcrumb_enabled ) {
			$icon = ! empty( $settings['mobile_breadcrumb_icon'] ) ? $settings['mobile_breadcrumb_icon'] : [];
			$close_icon = ! empty( $settings['mobile_breadcrumb_close_icon'] ) ? $settings['mobile_breadcrumb_close_icon'] : [];
			
			// Récupérer les valeurs responsive du breakpoint
			$breakpoint_desktop = ! empty( $settings['mobile_breadcrumb_breakpoint']['size'] ) ? $settings['mobile_breadcrumb_breakpoint']['size'] : 1024;
			$breakpoint_tablet = ! empty( $settings['mobile_breadcrumb_breakpoint_tablet']['size'] ) ? $settings['mobile_breadcrumb_breakpoint_tablet']['size'] : 768;
			$breakpoint_mobile = ! empty( $settings['mobile_breadcrumb_breakpoint_mobile']['size'] ) ? $settings['mobile_breadcrumb_breakpoint_mobile']['size'] : 480;
			
			// Récupérer les paramètres d'animation
			$animation_type = ! empty( $settings['popup_animation_type'] ) ? $settings['popup_animation_type'] : 'slide';
			$animation_duration = ! empty( $settings['popup_animation_duration']['size'] ) ? $settings['popup_animation_duration']['size'] : 0.3;
			$animation_easing = ! empty( $settings['popup_animation_easing'] ) ? $settings['popup_animation_easing'] : 'power2.out';
			$animation_delay = ! empty( $settings['popup_animation_delay']['size'] ) ? $settings['popup_animation_delay']['size'] : 0;
			
			?>
			<div class="nova-mobile-breadcrumb" 
				data-template-id="<?php echo esc_attr( $template_id ); ?>"
				data-breakpoint-desktop="<?php echo esc_attr( $breakpoint_desktop ); ?>"
				data-breakpoint-tablet="<?php echo esc_attr( $breakpoint_tablet ); ?>"
				data-breakpoint-mobile="<?php echo esc_attr( $breakpoint_mobile ); ?>"
				style="--breadcrumb-breakpoint-desktop: <?php echo esc_attr( $breakpoint_desktop ); ?>px; --breadcrumb-breakpoint-tablet: <?php echo esc_attr( $breakpoint_tablet ); ?>px; --breadcrumb-breakpoint-mobile: <?php echo esc_attr( $breakpoint_mobile ); ?>px;">
				<button type="button" class="nova-mobile-breadcrumb-toggle" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'NOVA-addons' ); ?>">
					<?php
					if ( ! empty( $icon ) ) {
						\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
					} else {
						// Fallback: icône hamburger SVG par défaut si aucune icône n'est configurée
						?>
						<svg aria-hidden="true" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php
					}
					?>
				</button>
				<div class="nova-mobile-breadcrumb-overlay"></div>
			</div>
			<div class="nova-mobile-breadcrumb-content"
				data-animation-type="<?php echo esc_attr( $animation_type ); ?>"
				data-animation-duration="<?php echo esc_attr( $animation_duration ); ?>"
				data-animation-easing="<?php echo esc_attr( $animation_easing ); ?>"
				data-animation-delay="<?php echo esc_attr( $animation_delay ); ?>">
				<button type="button" class="nova-mobile-breadcrumb-close" aria-label="<?php esc_attr_e( 'Fermer le menu', 'NOVA-addons' ); ?>">
					<?php
					if ( ! empty( $close_icon ) ) {
						\Elementor\Icons_Manager::render_icon( $close_icon, [ 'aria-hidden' => 'true', 'class' => 'nova-mobile-breadcrumb-close-icon' ] );
					} else {
						// Fallback vers le "×" si aucune icône n'est configurée
						?>
						<span class="nova-mobile-breadcrumb-close-icon">×</span>
						<?php
					}
					?>
				</button>
				<div class="nova-mobile-breadcrumb-template-wrapper">
					<?php
					if ( ! empty( $template_id ) ) {
						// ✅ IMPORTANT: Charger le CSS du template inline dans le popup
						// Cela garantit que les styles sont toujours présents même en mode déconnecté
						if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
							$css_file = \Elementor\Core\Files\CSS\Post::create( $template_id );
							$css_file->update();
							$meta = $css_file->get_meta();
							
							// Injecter le CSS inline directement dans le popup
							if ( ! empty( $meta['css'] ) ) {
								echo '<style id="elementor-post-' . esc_attr( $template_id ) . '-inline">';
								echo $meta['css']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo '</style>';
							}
						}
						
						// Afficher le template Elementor
						echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
					} elseif ( ! empty( $menu_html_for_popup ) ) {
						// Si aucun template n'est défini, afficher le menu directement dans le popup
						// ✅ Copier les styles du widget principal pour les appliquer au menu popup
						
						$menu_orientation = ! empty( $settings['menu_orientation'] ) ? $settings['menu_orientation'] : 'vertical';
						$orientation_class = 'nova-icon-menu-' . $menu_orientation;
						$dropdown_position_class = ! empty( $settings['dropdown_icon_position'] ) ? 'nova-dropdown-position-' . $settings['dropdown_icon_position'] : 'nova-dropdown-position-right';
						
						// Ajouter la classe submenu-below si activé
						$submenu_below_class = '';
						if ( ! empty( $settings['submenu_below_enable'] ) && 'yes' === $settings['submenu_below_enable'] && 'vertical' === $menu_orientation ) {
							$submenu_below_class = 'nova-submenu-below';
						}
						
						$wrapper_classes = trim( $orientation_class . ' ' . $dropdown_position_class . ' ' . $submenu_below_class );
						
						// ✅ Copier les styles CSS inline du widget principal vers le popup
						// Cela garantit que les styles Elementor sont appliqués même sans template
						$widget_id = $this->get_id();
						
						// Récupérer le CSS du post actuel pour ce widget
						if ( $current_post_id && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
							$post_css = \Elementor\Core\Files\CSS\Post::create( $current_post_id );
							$meta = $post_css->get_meta();
							
							if ( ! empty( $meta['css'] ) ) {
								// Injecter le CSS inline dans le popup
								echo '<style id="NOVA-menu-popup-post-' . esc_attr( $current_post_id ) . '-inline">';
								echo $meta['css']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo '</style>';
							}
						}
						?>
						<div class="<?php echo esc_attr( $wrapper_classes ); ?> elementor-element elementor-element-<?php echo esc_attr( $widget_id ); ?> elementor-widget elementor-widget-nova-icon-menu">
							<div class="elementor-widget-container">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - Menu HTML généré par WordPress
								echo $menu_html_for_popup;
								?>
							</div>
						</div>
						<?php
					} else {
						// Fallback: si le menu n'a pas été généré, le générer maintenant
						// Inclure le Walker personnalisé pour ce widget.
						require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-icon-menu-walker.php';

						// Préparer les données pour le Walker.
						$icon_menu_map = [];
						if ( ! empty( $settings['icon_menu_items'] ) ) {
							foreach ( $settings['icon_menu_items'] as $item ) {
								if ( ! empty( $item['menu_item_title'] ) ) {
									// Utiliser l'ID du menu item directement (pas le titre)
									$item_id = absint( $item['menu_item_title'] );
									$icon_type = isset( $item['menu_item_icon_type'] ) ? $item['menu_item_icon_type'] : 'icon';
									$icon_menu_map[ $item_id ] = [
										'type'        => $icon_type,
										'icon'        => isset( $item['menu_item_icon'] ) ? $item['menu_item_icon'] : [],
										'image'       => isset( $item['menu_item_icon_image'] ) ? $item['menu_item_icon_image'] : [],
										'description' => isset( $item['menu_item_description'] ) ? $item['menu_item_description'] : '',
									];
								}
							}
						}

						// Préparer les paramètres du widget pour le Walker
						$walker_settings = [
							'icon_menu_map' => $icon_menu_map,
							'dropdown_icon' => $settings['submenu_dropdown_icon'],
							'dropdown_icon_position' => $settings['dropdown_icon_position'],
						];

						// Construire la classe du conteneur
						$container_class = 'nova-icon-menu-container nova-mobile-mode';
						// Ajouter la classe submenu-below si activé et en mode vertical
						if ( ! empty( $settings['submenu_below_enable'] ) && 'yes' === $settings['submenu_below_enable'] && 
						     ! empty( $settings['menu_orientation'] ) && 'vertical' === $settings['menu_orientation'] ) {
							$container_class .= ' nova-submenu-below';
						}

						$menu_args = [
							'menu'            => $settings['menu_slug'],
							'menu_class'      => 'nova-icon-menu-list',
							'container'       => 'nav',
							'container_class' => $container_class,
							'fallback_cb'     => false,
							'walker'          => new \NOVA_Addons_Elementor\Icon_Menu_Walker( $walker_settings ),
						];

						// Essayer d'afficher le menu avec le walker
						ob_start();
						wp_nav_menu( $menu_args );
						$fallback_menu_html = ob_get_clean();
						
						// Si toujours vide, essayer sans walker
						if ( empty( $fallback_menu_html ) ) {
							$menu_args_no_walker = $menu_args;
							unset( $menu_args_no_walker['walker'] );
							ob_start();
							wp_nav_menu( $menu_args_no_walker );
							$fallback_menu_html = ob_get_clean();
						}
						
						// Afficher le menu généré ou un message d'erreur
						if ( ! empty( $fallback_menu_html ) ) {
							// ✅ Envelopper le menu dans une structure similaire au template pour que les styles s'appliquent correctement
							$menu_orientation = ! empty( $settings['menu_orientation'] ) ? $settings['menu_orientation'] : 'vertical';
							$orientation_class = 'nova-icon-menu-' . $menu_orientation;
							$dropdown_position_class = ! empty( $settings['dropdown_icon_position'] ) ? 'nova-dropdown-position-' . $settings['dropdown_icon_position'] : 'nova-dropdown-position-right';
							
							// Ajouter la classe submenu-below si activé
							$submenu_below_class = '';
							if ( ! empty( $settings['submenu_below_enable'] ) && 'yes' === $settings['submenu_below_enable'] && 'vertical' === $menu_orientation ) {
								$submenu_below_class = 'nova-submenu-below';
							}
							
							$wrapper_classes = trim( $orientation_class . ' ' . $dropdown_position_class . ' ' . $submenu_below_class );
							
							// ✅ Copier les styles CSS inline du widget principal vers le popup (fallback)
							$widget_id = $this->get_id();
							
							// Récupérer le CSS du post actuel pour ce widget
							if ( $current_post_id && class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
								$post_css = \Elementor\Core\Files\CSS\Post::create( $current_post_id );
								$meta = $post_css->get_meta();
								
								if ( ! empty( $meta['css'] ) ) {
									// Injecter le CSS inline dans le popup
									echo '<style id="NOVA-menu-fallback-post-' . esc_attr( $current_post_id ) . '-inline">';
									echo $meta['css']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo '</style>';
								}
							}
							?>
							<div class="<?php echo esc_attr( $wrapper_classes ); ?> elementor-element elementor-element-<?php echo esc_attr( $widget_id ); ?> elementor-widget elementor-widget-nova-icon-menu">
								<div class="elementor-widget-container">
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - Menu HTML généré par WordPress
									echo $fallback_menu_html;
									?>
								</div>
							</div>
							<?php
						} else {
							// Dernier recours : afficher un message de débogage (seulement si on est en mode debug)
							if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
								echo '<!-- Menu non généré. Menu slug: ' . esc_html( $settings['menu_slug'] ) . ' -->';
							}
						}
					}
					?>
				</div>
			</div>
			<?php
		}

		// Inclure le Walker personnalisé pour ce widget.
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-icon-menu-walker.php';

		// Préparer les données pour le Walker.
		$icon_menu_map = [];
		if ( ! empty( $settings['icon_menu_items'] ) ) {
			foreach ( $settings['icon_menu_items'] as $item ) {
				if ( ! empty( $item['menu_item_title'] ) ) {
					// Utiliser l'ID du menu item directement (pas le titre)
					$item_id = absint( $item['menu_item_title'] );
					$icon_type = isset( $item['menu_item_icon_type'] ) ? $item['menu_item_icon_type'] : 'icon';
					$icon_menu_map[ $item_id ] = [
						'type'        => $icon_type,
						'icon'        => isset( $item['menu_item_icon'] ) ? $item['menu_item_icon'] : [],
						'image'       => isset( $item['menu_item_icon_image'] ) ? $item['menu_item_icon_image'] : [],
						'description' => isset( $item['menu_item_description'] ) ? $item['menu_item_description'] : '',
					];
				}
			}
		}

		// Préparer les paramètres du widget pour le Walker
		$walker_settings = [
			'icon_menu_map' => $icon_menu_map,
			'dropdown_icon' => $settings['submenu_dropdown_icon'],
			'dropdown_icon_position' => $settings['dropdown_icon_position'],
		];

		// Afficher le titre du menu si activé
		if ( ! empty( $settings['show_menu_title'] ) && 'yes' === $settings['show_menu_title'] ) {
			$menu_title = ! empty( $settings['menu_title_text'] ) ? $settings['menu_title_text'] : '';
			
			// Si le titre personnalisé est vide, récupérer le nom du menu WordPress
			if ( empty( $menu_title ) ) {
				$menu = wp_get_nav_menu_object( $settings['menu_slug'] );
				if ( $menu && isset( $menu->name ) ) {
					$menu_title = $menu->name;
				}
			}
			
			$title_tag = ! empty( $settings['menu_title_tag'] ) ? $settings['menu_title_tag'] : 'h2';
			$title_link = isset( $settings['menu_title_link']['url'] ) && $settings['menu_title_link']['url'] !== '' ? $settings['menu_title_link'] : null;

			// Accordion
			$accordion_enable  = ! empty( $settings['accordion_enable'] ) && 'yes' === $settings['accordion_enable'];
			$accordion_open    = ! empty( $settings['accordion_default_open'] ) && 'yes' === $settings['accordion_default_open'];

			// Détecter si le lien du titre pointe vers la page actuelle (pour la classe "page active").
			$is_current_page = false;
			if ( $title_link && ! empty( $title_link['url'] ) ) {
				$link_url = $title_link['url'];
				$current_url = '';
				if ( is_front_page() && is_home() ) {
					$current_url = home_url( '/' );
				} elseif ( is_front_page() ) {
					$current_url = home_url( '/' );
				} elseif ( is_singular() ) {
					$current_url = get_permalink();
				} else {
					global $wp;
					$current_url = home_url( $wp->request );
				}
				$link_path   = trailingslashit( strtolower( set_url_scheme( strtok( $link_url, '?' ), 'https' ) ) );
				$current_path = trailingslashit( strtolower( set_url_scheme( strtok( $current_url, '?' ), 'https' ) ) );
				$is_current_page = ( $link_path === $current_path );
			}

			if ( ! empty( $menu_title ) ) {
				// Préparer l'icône du titre
				$title_icon = ! empty( $settings['menu_title_icon'] ) ? $settings['menu_title_icon'] : [];
				$title_icon_position = ! empty( $settings['menu_title_icon_position'] ) ? $settings['menu_title_icon_position'] : 'before';
				$has_icon = ! empty( $title_icon['value'] );

				// Construire le HTML de l'icône
				$icon_html = '';
				if ( $has_icon ) {
					ob_start();
					\Elementor\Icons_Manager::render_icon( $title_icon, [ 'aria-hidden' => 'true' ] );
					$icon_html = '<span class="nova-icon-menu-header-title-icon" aria-hidden="true">' . ob_get_clean() . '</span>';
				}

				// Construire le contenu interne (icône + texte)
				$inner_content_html = '';
				if ( $has_icon && 'before' === $title_icon_position ) {
					$inner_content_html = $icon_html . '<span class="nova-icon-menu-header-title-text">' . esc_html( $menu_title ) . '</span>';
				} elseif ( $has_icon && 'after' === $title_icon_position ) {
					$inner_content_html = '<span class="nova-icon-menu-header-title-text">' . esc_html( $menu_title ) . '</span>' . $icon_html;
				} else {
					$inner_content_html = '<span class="nova-icon-menu-header-title-text">' . esc_html( $menu_title ) . '</span>';
				}

				// Icônes accordion (plus/moins)
				$accordion_icon_html = '';
				if ( $accordion_enable ) {
					$icon_open   = ! empty( $settings['accordion_icon_open'] ) ? $settings['accordion_icon_open'] : [ 'value' => 'fas fa-minus', 'library' => 'fa-solid' ];
					$icon_closed = ! empty( $settings['accordion_icon_closed'] ) ? $settings['accordion_icon_closed'] : [ 'value' => 'fas fa-plus', 'library' => 'fa-solid' ];

					ob_start();
					\Elementor\Icons_Manager::render_icon( $icon_open, [ 'aria-hidden' => 'true' ] );
					$icon_open_html = ob_get_clean();

					ob_start();
					\Elementor\Icons_Manager::render_icon( $icon_closed, [ 'aria-hidden' => 'true' ] );
					$icon_closed_html = ob_get_clean();

					$accordion_icon_html = '<span class="nova-accordion-toggle-icon" aria-hidden="true">'
						. '<span class="nova-accordion-icon-open">' . $icon_open_html . '</span>'
						. '<span class="nova-accordion-icon-closed">' . $icon_closed_html . '</span>'
						. '</span>';
				}

				$inner_html = '<span class="nova-icon-menu-header-title-inner">' . $inner_content_html . '</span>' . $accordion_icon_html;

				// Ajouter la classe has-icon si une icône est présente
				$title_classes = 'nova-icon-menu-header-title';
				if ( $has_icon ) {
					$title_classes .= ' nova-icon-menu-header-title--has-icon nova-icon-menu-header-title--icon-' . esc_attr( $title_icon_position );
				}
				if ( $accordion_enable ) {
					$title_classes .= ' nova-accordion-trigger';
					if ( $accordion_open ) {
						$title_classes .= ' nova-accordion-open';
					}
				}

				if ( $title_link && ! $accordion_enable ) {
					$this->add_link_attributes( 'menu_title_link', $title_link );
					$link_class = 'nova-icon-menu-header-title-link';
					if ( $is_current_page ) {
						$link_class .= ' nova-icon-menu-header-title-link--current';
					}
					printf(
						'<%1$s class="%2$s"><a class="%3$s" %4$s>%5$s</a></%1$s>',
						esc_attr( $title_tag ),
						esc_attr( $title_classes ),
						esc_attr( $link_class ),
						$this->get_render_attribute_string( 'menu_title_link' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
				} else {
					printf(
						'<%1$s class="%2$s">%3$s</%1$s>',
						esc_attr( $title_tag ),
						esc_attr( $title_classes ),
						$inner_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
				}
			}
		}

		// ✅ IMPORTANT: Ajouter les classes d'orientation au wrapper Elementor
		// Cela garantit que les sélecteurs {{WRAPPER}}.nova-icon-menu-horizontal/vertical fonctionnent
		$menu_orientation = ! empty( $settings['menu_orientation'] ) ? $settings['menu_orientation'] : 'horizontal';
		$orientation_class = 'nova-icon-menu-' . $menu_orientation;
		$this->add_render_attribute( '_wrapper', 'class', $orientation_class );
		
		// Construire la classe du conteneur
		$container_class = 'nova-icon-menu-container';
		// Ajouter la classe submenu-below si activé et en mode vertical
		if ( ! empty( $settings['submenu_below_enable'] ) && 'yes' === $settings['submenu_below_enable'] && 
		     ! empty( $settings['menu_orientation'] ) && 'vertical' === $settings['menu_orientation'] ) {
			$container_class .= ' nova-submenu-below';
			// Ajouter aussi la classe sur le wrapper Elementor pour que le CSS statique fonctionne
			$this->add_render_attribute( '_wrapper', 'class', 'nova-submenu-below' );
		}
		
		// ✅ Ajouter aussi la classe de position du dropdown si configurée
		if ( ! empty( $settings['dropdown_icon_position'] ) ) {
			$dropdown_position_class = 'nova-dropdown-position-' . $settings['dropdown_icon_position'];
			$this->add_render_attribute( '_wrapper', 'class', $dropdown_position_class );
		}

		$menu_args = [
			'menu'            => $settings['menu_slug'],
			'menu_class'      => 'nova-icon-menu-list',
			'container'       => 'nav',
			'container_class' => $container_class,
			'fallback_cb'     => false,
			'walker'          => new \NOVA_Addons_Elementor\Icon_Menu_Walker( $walker_settings ),
		];

		// Accordion: ajouter un ID unique pour forcer le style inline si ouvert par défaut
		if ( $accordion_enable && $accordion_open ) {
			$unique_nav_id = 'nova-accordion-nav-' . uniqid();
			$menu_args['container_id'] = $unique_nav_id;
			// Ajouter un style inline pour forcer l'affichage
			echo '<style>#' . esc_attr( $unique_nav_id ) . ' { display: block !important; visibility: visible !important; opacity: 1 !important; }</style>';
		}

		// Accordion: envelopper le nav dans un div avec data-attribute
		$accordion_enable = ! empty( $settings['accordion_enable'] ) && 'yes' === $settings['accordion_enable'];
		$accordion_open   = ! empty( $settings['accordion_default_open'] ) && 'yes' === $settings['accordion_default_open'];
		if ( $accordion_enable ) {
			$accordion_wrapper_class = 'nova-accordion-menu-body';
			$accordion_wrapper_style = '';
			if ( ! $accordion_open ) {
				$accordion_wrapper_class .= ' nova-accordion-closed';
			} else {
				// Force display block inline pour garantir l'affichage sur mobile réel
				$accordion_wrapper_style = ' style="display: block !important; visibility: visible !important;"';
			}
			echo '<div class="' . esc_attr( $accordion_wrapper_class ) . '" data-accordion-default="' . ( $accordion_open ? 'open' : 'closed' ) . '"' . $accordion_wrapper_style . '>';
		}

		// Afficher le menu.
		wp_nav_menu( $menu_args );

		if ( $accordion_enable ) {
			echo '</div>';
		}

		// Affichage de secours pour la sandbox.
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="nova-editor-preview">';
			echo '<h3>' . esc_html__( 'Icon Menu Preview', 'NOVA-addons' ) . '</h3>';
			echo '<p>' . esc_html__( 'Selected menu:', 'NOVA-addons' ) . '<strong>' . $settings['menu_slug'] . '</strong></p>';
			echo '<p>' . esc_html__( 'Full rendering requires an active WordPress environment.', 'NOVA-addons' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Rendu du widget dans l'éditeur.
	 */
	protected function content_template() {
		// Template de l'éditeur.
		?>
		<div class="nova-editor-preview">
			<h3><?php echo esc_html__( 'Icon Menu Preview', 'NOVA-addons' ); ?></h3>
			<# if ( settings.menu_slug ) { #>
				<p><?php echo esc_html__( 'Selected menu:', 'NOVA-addons' ); ?> <strong>{{{ settings.menu_slug }}}</strong></p>
				<p><?php echo esc_html__( 'Full rendering requires an active WordPress environment.', 'NOVA-addons' ); ?></p>
			<# } else { #>
				<p><?php echo esc_html__( 'Please select a menu.', 'NOVA-addons' ); ?></p>
			<# } #>
		</div>
		<?php
	}
}
