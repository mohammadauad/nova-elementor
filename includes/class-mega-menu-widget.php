<?php
namespace Nova_Addons_Elementor;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;
use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Widget Mega Menu Elementor.
 */
class Mega_Menu_Widget extends Widget_Base {

	/**
	 * Récupère le nom du widget.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'nova-mega-menu';
	}

	/**
	 * Récupère le titre du widget.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'NOVA Mega Menu', 'NOVA-addons' );
	}

	/**
	 * Récupère l'icône du widget.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-nav-menu';
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
	 * Récupère les mots-clés pour la recherche.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'menu', 'mega', 'nav' ];
	}

	/**
	 * Récupère les dépendances de style pour le widget.
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'nova-addons-style' ];
	}

	/**
	 * Récupère les dépendances de script pour le widget.
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'nova-mega-menu', 'nova-mega-menu-mobile' ];
	}

	/**
	 * Récupère la liste des menus enregistrés dans WordPress.
	 *
	 * @return array
	 */
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

		// Breadcrumb Mobile
		$this->add_control(
			'enable_mobile_breadcrumb',
			[
				'label' => esc_html__( 'Breadcrumb Mobile', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
				'description' => esc_html__( 'Afficher un breadcrumb mobile avec icône pour ouvrir le menu.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'mobile_breadcrumb_icon',
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

		$this->add_control(
			'enable_mobile_logo',
			[
				'label' => esc_html__( 'Afficher Logo', 'NOVA-addons' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Oui', 'NOVA-addons' ),
				'label_off' => esc_html__( 'Non', 'NOVA-addons' ),
				'default' => 'no',
				'separator' => 'before',
				'condition' => [
					'enable_mobile_breadcrumb' => 'yes',
				],
				'description' => esc_html__( 'Afficher un logo à côté du bouton breadcrumb.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'mobile_logo_image',
			[
				'label' => esc_html__( 'Logo', 'NOVA-addons' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'enable_mobile_breadcrumb' => 'yes',
					'enable_mobile_logo' => 'yes',
				],
				'description' => esc_html__( 'Sélectionnez l\'image du logo.', 'NOVA-addons' ),
			]
		);

		$this->add_responsive_control(
			'mobile_logo_width',
			[
				'label' => esc_html__( 'Largeur du Logo', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 300,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default' => [
					'size' => 120,
					'unit' => 'px',
				],
				'condition' => [
					'enable_mobile_breadcrumb' => 'yes',
					'enable_mobile_logo' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-logo img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'mobile_logo_height',
			[
				'label' => esc_html__( 'Hauteur du Logo', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'auto' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 200,
					],
				],
				'default' => [
					'size' => 40,
					'unit' => 'px',
				],
				'condition' => [
					'enable_mobile_breadcrumb' => 'yes',
					'enable_mobile_logo' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mobile-breadcrumb-logo img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'mobile_logo_link',
			[
				'label' => esc_html__( 'Lien du Logo', 'NOVA-addons' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://votre-site.com', 'NOVA-addons' ),
				'default' => [
					'url' => home_url(),
					'is_external' => false,
					'nofollow' => false,
				],
				'condition' => [
					'enable_mobile_breadcrumb' => 'yes',
					'enable_mobile_logo' => 'yes',
				],
				'description' => esc_html__( 'URL vers laquelle le logo redirige (par défaut: page d\'accueil).', 'NOVA-addons' ),
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
						'{{WRAPPER}} .nova-mobile-breadcrumb' => '--breadcrumb-breakpoint: {{SIZE}}{{UNIT}};',
					],
				]
			);

		$this->end_controls_section();

		// --- Section Icônes Dropdown Globales ---
		$this->start_controls_section(
			'section_dropdown_icons',
			[
				'label' => esc_html__( 'Icônes Dropdown', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'global_dropdown_icon',
			[
				'label' => esc_html__( 'Dropdown Icon (Normal)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'default' => [
					'value' => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'global_dropdown_icon_hover',
			[
				'label' => esc_html__( 'Dropdown Icon (Hover)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'description' => esc_html__( 'Laisser vide pour utiliser l\'icône normale.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'global_dropdown_icon_active',
			[
				'label' => esc_html__( 'Dropdown Icon (Active)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'description' => esc_html__( 'Laisser vide pour utiliser l\'icône normale.', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// --- Section pour les options de Mega Menu ---
		$this->start_controls_section(
			'section_mega_menu_settings',
			[
				'label' => esc_html__( 'Mega Menu Settings', 'NOVA-addons' ),
			]
		);

		// Le cœur de la fonctionnalité : un Répéteur pour chaque élément de menu.
		// Cela est complexe et nécessite une approche différente.
		// Pour l'instant, nous allons nous concentrer sur la structure.

		$repeater = new \Elementor\Repeater();

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
				'label' => esc_html__( 'Dropdown Icon (Normal)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'default' => [
					'value' => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'chevron-down',
						'angle-down',
						'caret-down',
					],
				],
			]
		);

		$repeater->add_control(
			'menu_item_icon_hover',
			[
				'label' => esc_html__( 'Dropdown Icon (Hover)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'description' => esc_html__( 'Icône affichée au survol. Laisser vide pour utiliser l\'icône normale.', 'NOVA-addons' ),
			]
		);

		$repeater->add_control(
			'menu_item_icon_active',
			[
				'label' => esc_html__( 'Dropdown Icon (Active)', 'NOVA-addons' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false,
				'description' => esc_html__( 'Icône affichée quand le mega menu est ouvert. Laisser vide pour utiliser l\'icône normale.', 'NOVA-addons' ),
			]
		);

		$mega_menu_options = $this->get_mega_menu_content_options();

		if ( ! empty( $mega_menu_options ) ) {
			// Ajouter une option vide en premier pour permettre les sous-menus natifs
			$mega_menu_options_with_empty = [ '' => esc_html__( '— Sous-menus natifs (sans template) —', 'NOVA-addons' ) ] + $mega_menu_options;
			
			$repeater->add_control(
				'content_id',
				[
					'label'   => esc_html__( 'Mega Menu Content', 'NOVA-addons' ),
					'type'    => Controls_Manager::SELECT,
					'options' => $mega_menu_options_with_empty,
					'default' => '',
					'description' => esc_html__( 'Sélectionnez un template Elementor ou laissez vide pour afficher les sous-menus WordPress natifs.', 'NOVA-addons' ),
				]
			);
		} else {
			$repeater->add_control(
				'content_notice',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw' => '<strong>' . esc_html__( 'No Mega Menu content found.', 'NOVA-addons' ) . '</strong><br>' . sprintf( esc_html__( 'Please create new content via the "NOVA Mega Menus" interface in WordPress dashboard. %s', 'NOVA-addons' ), '<a href="' . admin_url( 'post-new.php?post_type=mega_menu_content' ) . '" target="_blank">Create content</a>' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
				]
			);
		}

		// Message d'aide simple (le JavaScript est maintenant dans nova-addons-admin.js)
		$help_text = '<div style="padding: 10px; background: #e8f4f8; border-left: 3px solid #00a0d2; margin: 10px 0;">';
		$help_text .= '<strong>' . esc_html__( 'How to use:', 'NOVA-addons' ) . '</strong><br>';
		$help_text .= esc_html__( '1. Select a menu from "Select Menu" above', 'NOVA-addons' ) . '<br>';
		$help_text .= esc_html__( '2. The menu items will load automatically in the dropdown below', 'NOVA-addons' ) . '<br>';
		$help_text .= esc_html__( '3. Select a menu item and associate it with Mega Menu content', 'NOVA-addons' );
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
			'mega_menu_items',
			[
				'label'   => esc_html__( 'Menu Item Content', 'NOVA-addons' ),
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'title_field' => '{{{ menu_item_title }}}',
				'description' => esc_html__( 'Associate Elementor content to each menu item to create the Mega Menu.', 'NOVA-addons' ),
			]
		);

		$this->end_controls_section();

		// --- Section de Style du Menu ---
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
					'{{WRAPPER}} .nova-mega-menu-container' => 'text-align: {{VALUE}};',
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
					'{{WRAPPER}} .nova-mega-menu-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-mega-menu-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul' => 'display: {{VALUE}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul' => 'flex-direction: {{VALUE}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul' => 'justify-content: {{VALUE}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul' => 'align-items: {{VALUE}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul' => 'gap: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul' => 'flex-wrap: {{VALUE}};',
				],
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
					'justify' => [
						'title' => esc_html__( 'Justifié', 'NOVA-addons' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-container > ul > li > a' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .nova-menu-list > li > a' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'menu_item_typography',
				'label' => esc_html__( 'Typography', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mega-menu-container > ul > li > a',
			]
		);

		$this->add_responsive_control(
			'menu_item_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-container > ul > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .nova-mega-menu-container > ul > li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'menu_item_style_tabs' );

		$this->start_controls_tab(
			'menu_item_normal',
			[
				'label' => esc_html__( 'Normal', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_item_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-container > ul > li > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_item_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-mega-menu-container > ul > li > a',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'menu_item_hover',
			[
				'label' => esc_html__( 'Hover', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_item_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-container > ul > li > a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-mega-menu-container > ul > li.nova-mega-menu-displayed > a' => 'color: {{VALUE}};',
					'body.nova-mega-menu-displayed {{WRAPPER}} .nova-mega-menu-container > ul > li.nova-mega-menu-displayed > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_item_background_hover',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-mega-menu-container > ul > li > a:hover',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'menu_item_active',
			[
				'label' => esc_html__( 'Active', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'menu_item_color_active',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-container > ul > li.current-menu-item > a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .nova-mega-menu-container > ul > li.current-menu-ancestor > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'menu_item_background_active',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-mega-menu-container > ul > li.current-menu-item > a, {{WRAPPER}} .nova-mega-menu-container > ul > li.current-menu-ancestor > a',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'menu_item_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mega-menu-container > ul > li > a',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'menu_item_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-container > ul > li > a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'menu_item_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mega-menu-container > ul > li > a',
			]
		);

		$this->end_controls_section();

		// --- Section Style du Mega Menu Panel ---
		$this->start_controls_section(
			'section_mega_menu_panel_style',
			[
				'label' => esc_html__( 'Style du Mega Menu Panel', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'mega_menu_panel_width',
			[
				'label' => esc_html__( 'Largeur du Panel', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 2000,
						'step' => 10,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
					'vw' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 1280,
				],
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-panel' => 'min-width: {{SIZE}}{{UNIT}} !important; width: {{SIZE}}{{UNIT}} !important; max-width: {{SIZE}}{{UNIT}} !important;',
				],
				'description' => esc_html__( 'Définir la largeur du panel mega menu.', 'NOVA-addons' ),
			]
		);

		$this->add_control(
			'mega_menu_panel_background',
			[
				'label' => esc_html__( 'Couleur de fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-panel' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'mega_menu_panel_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mega-menu-panel',
			]
		);

		$this->add_responsive_control(
			'mega_menu_panel_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-panel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'mega_menu_panel_margin',
			[
				'label' => esc_html__( 'Marge externe', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-panel' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'mega_menu_panel_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .nova-mega-menu-panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'mega_menu_panel_border',
				'label' => esc_html__( 'Bordure', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-mega-menu-panel, {{WRAPPER}} .nova-mega-menu-container .nova-mega-menu-panel',
				'fields_options' => [
					'border' => [
						'selectors' => [
							'{{WRAPPER}} .nova-mega-menu-panel' => 'border-style: {{VALUE}} !important;',
							'{{WRAPPER}} .nova-mega-menu-container .nova-mega-menu-panel' => 'border-style: {{VALUE}} !important;',
						],
					],
					'width' => [
						'selectors' => [
							'{{WRAPPER}} .nova-mega-menu-panel' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
							'{{WRAPPER}} .nova-mega-menu-container .nova-mega-menu-panel' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
						],
					],
					'color' => [
						'selectors' => [
							'{{WRAPPER}} .nova-mega-menu-panel' => 'border-color: {{VALUE}} !important;',
							'{{WRAPPER}} .nova-mega-menu-container .nova-mega-menu-panel' => 'border-color: {{VALUE}} !important;',
						],
					],
				],
			]
		);

		$this->end_controls_section();

		// --- Section Style des Sous-menus Natifs ---
		$this->start_controls_section(
			'section_submenu_style',
			[
				'label' => esc_html__( 'Style des Sous-menus', 'NOVA-addons' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'submenu_background',
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .nova-menu-list .sub-menu',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'submenu_box_shadow',
				'label' => esc_html__( 'Ombre', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-menu-list .sub-menu',
			]
		);

		$this->add_responsive_control(
			'submenu_padding',
			[
				'label' => esc_html__( 'Espacement interne', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-menu-list .sub-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_border_radius',
			[
				'label' => esc_html__( 'Rayon de bordure', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .nova-menu-list .sub-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'submenu_min_width',
			[
				'label' => esc_html__( 'Largeur minimale', 'NOVA-addons' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [ 'px' => [ 'min' => 100, 'max' => 600 ] ],
				'default' => [ 'size' => 200, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .nova-menu-list .sub-menu' => 'min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'submenu_items_heading',
			[
				'label' => esc_html__( 'Items du sous-menu', 'NOVA-addons' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'submenu_typography',
				'label' => esc_html__( 'Typographie', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-menu-list .sub-menu a',
			]
		);

		$this->add_responsive_control(
			'submenu_item_padding',
			[
				'label' => esc_html__( 'Espacement interne des items', 'NOVA-addons' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .nova-menu-list .sub-menu a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'submenu_item_tabs' );

		$this->start_controls_tab( 'submenu_item_normal', [ 'label' => esc_html__( 'Normal', 'NOVA-addons' ) ] );

		$this->add_control(
			'submenu_item_color',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nova-menu-list .sub-menu a' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'submenu_item_bg',
			[
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nova-menu-list .sub-menu li' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'submenu_item_hover', [ 'label' => esc_html__( 'Hover', 'NOVA-addons' ) ] );

		$this->add_control(
			'submenu_item_color_hover',
			[
				'label' => esc_html__( 'Couleur du texte', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nova-menu-list .sub-menu a:hover' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'submenu_item_bg_hover',
			[
				'label' => esc_html__( 'Fond', 'NOVA-addons' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .nova-menu-list .sub-menu li:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'submenu_item_border',
				'label' => esc_html__( 'Séparateur entre items', 'NOVA-addons' ),
				'selector' => '{{WRAPPER}} .nova-menu-list .sub-menu li',
				'separator' => 'before',
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
					'expand' => esc_html__( 'Expand (Expansion verticale)', 'NOVA-addons' ),
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
	 * Enregistre le style CSS du widget.
	 *
	 * Note: Cette méthode est appelée par `get_style_depends()` et est la manière
	 * recommandée de charger le CSS de manière conditionnelle dans Elementor.
	 */
	public function print_styles() {
		// Style is already registered in main plugin file as 'nova-addons-style'
		// This method is kept for compatibility but doesn't need to re-register
	}

	/**
	 * Rendu du widget sur le frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['menu_slug'] ) ) {
			return;
		}

		// Afficher le breadcrumb mobile si activé
		if ( ! empty( $settings['enable_mobile_breadcrumb'] ) && 'yes' === $settings['enable_mobile_breadcrumb'] ) {
			$template_id = ! empty( $settings['mobile_breadcrumb_template'] ) ? $settings['mobile_breadcrumb_template'] : '';
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
			
			// Récupérer les paramètres du logo
			$enable_logo = ! empty( $settings['enable_mobile_logo'] ) && 'yes' === $settings['enable_mobile_logo'];
			$logo_image = ! empty( $settings['mobile_logo_image']['url'] ) ? $settings['mobile_logo_image'] : [];
			$logo_link = ! empty( $settings['mobile_logo_link']['url'] ) ? $settings['mobile_logo_link'] : [];
			
			?>
			<style>.nova-mobile-breadcrumb-wrapper { display: none; }</style>
			<div class="nova-mobile-breadcrumb-wrapper">
				<?php if ( $enable_logo && ! empty( $logo_image['url'] ) ) : ?>
					<div class="nova-mobile-breadcrumb-logo">
						<?php if ( ! empty( $logo_link['url'] ) ) : ?>
							<a href="<?php echo esc_url( $logo_link['url'] ); ?>"
								<?php if ( ! empty( $logo_link['is_external'] ) ) : ?>target="_blank"<?php endif; ?>
								<?php if ( ! empty( $logo_link['nofollow'] ) ) : ?>rel="nofollow"<?php endif; ?>
								aria-label="<?php esc_attr_e( 'Logo', 'NOVA-addons' ); ?>">
								<img src="<?php echo esc_url( $logo_image['url'] ); ?>" 
									alt="<?php echo esc_attr( ! empty( $logo_image['alt'] ) ? $logo_image['alt'] : get_bloginfo( 'name' ) ); ?>" />
							</a>
						<?php else : ?>
							<img src="<?php echo esc_url( $logo_image['url'] ); ?>" 
								alt="<?php echo esc_attr( ! empty( $logo_image['alt'] ) ? $logo_image['alt'] : get_bloginfo( 'name' ) ); ?>" />
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<div class="nova-mobile-breadcrumb" 
					data-template-id="<?php echo esc_attr( $template_id ); ?>"
					data-breakpoint-desktop="<?php echo esc_attr( $breakpoint_desktop ); ?>"
					data-breakpoint-tablet="<?php echo esc_attr( $breakpoint_tablet ); ?>"
					data-breakpoint-mobile="<?php echo esc_attr( $breakpoint_mobile ); ?>"
					style="--breadcrumb-breakpoint-desktop: <?php echo esc_attr( $breakpoint_desktop ); ?>px; --breadcrumb-breakpoint-tablet: <?php echo esc_attr( $breakpoint_tablet ); ?>px; --breadcrumb-breakpoint-mobile: <?php echo esc_attr( $breakpoint_mobile ); ?>px;">
					<button type="button" class="nova-mobile-breadcrumb-toggle" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'NOVA-addons' ); ?>">
						<?php if ( ! empty( $icon ) ) : ?>
							<span class="nova-breadcrumb-icon-open">
								<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
						<?php if ( ! empty( $close_icon ) ) : ?>
							<span class="nova-breadcrumb-icon-close">
								<?php \Elementor\Icons_Manager::render_icon( $close_icon, [ 'aria-hidden' => 'true' ] ); ?>
							</span>
						<?php endif; ?>
					</button>
					<div class="nova-mobile-breadcrumb-overlay"></div>
				</div>
			</div>
			<div class="nova-mobile-breadcrumb-content<?php echo $animation_type === 'expand' ? ' nova-expand-hidden' : ''; ?>"
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
						echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
					}
					?>
				</div>
			</div>
			<?php
		}

		// Inclure le Walker personnalisé pour pouvoir l'utiliser.
		require_once NOVA_ADDONS_PLUGIN_DIR . 'includes/class-mega-menu-walker.php';

		// Préparer les données du mega menu pour le Walker.
		$mega_menu_map = [];
		
		if ( ! empty( $settings['mega_menu_items'] ) && is_array( $settings['mega_menu_items'] ) ) {
			foreach ( $settings['mega_menu_items'] as $item ) {
				// Inclure l'item si menu_item_title est défini (content_id peut être vide = sous-menus natifs)
				if ( ! empty( $item['menu_item_title'] ) ) {
					$item_key = (string) $item['menu_item_title'];
					
					$mega_menu_map[ $item_key ] = [
						'content_id'   => ! empty( $item['content_id'] ) ? (int) $item['content_id'] : 0,
						'icon'         => isset( $item['menu_item_icon'] ) ? $item['menu_item_icon'] : [],
						'icon_hover'   => ! empty( $item['menu_item_icon_hover'] ) ? $item['menu_item_icon_hover'] : [],
						'icon_active'  => ! empty( $item['menu_item_icon_active'] ) ? $item['menu_item_icon_active'] : [],
					];
				}
			}
		}

		// Icônes dropdown globales (pour tous les items avec sous-menus).
		// Fallback sur le chevron par défaut quand le réglage n'a jamais été sauvegardé
		// (cas des widgets cr\u00e9\u00e9s avant l'ajout du contr\u00f4le `global_dropdown_icon`).
		$default_dropdown_icon = [
			'value'   => 'fas fa-chevron-down',
			'library' => 'fa-solid',
		];
		$normal_icon = ( ! empty( $settings['global_dropdown_icon'] ) && ! empty( $settings['global_dropdown_icon']['value'] ) )
			? $settings['global_dropdown_icon']
			: $default_dropdown_icon;

		$global_icons = [
			'icon'        => $normal_icon,
			'icon_hover'  => ! empty( $settings['global_dropdown_icon_hover'] ) ? $settings['global_dropdown_icon_hover'] : [],
			'icon_active' => ! empty( $settings['global_dropdown_icon_active'] ) ? $settings['global_dropdown_icon_active'] : [],
		];

		$menu_args = [
			'menu'            => ! empty( $settings['menu_slug'] ) ? $settings['menu_slug'] : '',
			'menu_class'      => 'nova-menu-list',
			'container'       => 'nav',
			'container_class' => 'nova-mega-menu-container',
			'fallback_cb'     => false,
			'walker'          => new \NOVA_Addons_Elementor\Mega_Menu_Walker( $mega_menu_map, $global_icons ),
		];

		// Afficher le menu seulement si un menu est sélectionné.
		if ( ! empty( $settings['menu_slug'] ) ) {
			wp_nav_menu( $menu_args );
		}

		// Affichage de secours pour la sandbox.
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="NOVA-editor-preview">';
			echo '<h3>' . esc_html__( 'Mega Menu Preview', 'NOVA-addons' ) . '</h3>';
			echo '<p>' . esc_html__( 'Selected menu:', 'NOVA-addons' ) . '<strong>' . $settings['menu_slug'] . '</strong></p>';
			echo '<p>' . esc_html__( 'Full rendering requires an active WordPress environment.', 'NOVA-addons' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Rendu du widget dans l'éditeur.
	 */
	protected function content_template() {
		// Le template pour l'éditeur est souvent plus simple.
		?>
		<# if ( settings.enable_mobile_breadcrumb === 'yes' ) { #>
			<div class="nova-mobile-breadcrumb-wrapper" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding: 10px; border: 1px dashed #ccc; background: #f9f9f9;">
				<# if ( settings.enable_mobile_logo === 'yes' && settings.mobile_logo_image.url ) { #>
					<div class="nova-mobile-breadcrumb-logo" style="display: flex; align-items: center;">
						<# if ( settings.mobile_logo_link.url ) { #>
							<a href="#" style="display: flex; align-items: center; text-decoration: none;">
								<img src="{{{ settings.mobile_logo_image.url }}}" alt="Logo" style="max-width: 120px; height: 40px; object-fit: contain;" />
							</a>
						<# } else { #>
							<img src="{{{ settings.mobile_logo_image.url }}}" alt="Logo" style="max-width: 120px; height: 40px; object-fit: contain;" />
						<# } #>
					</div>
				<# } #>
				<div class="nova-mobile-breadcrumb" style="display: flex; align-items: center;">
					<button type="button" class="nova-mobile-breadcrumb-toggle" style="background: none; border: 1px solid #ccc; padding: 8px 12px; cursor: pointer; display: flex; align-items: center;">
						<# if ( settings.mobile_breadcrumb_icon.value ) { #>
							<i class="{{{ settings.mobile_breadcrumb_icon.value }}}" style="font-size: 18px;"></i>
						<# } else { #>
							☰
						<# } #>
					</button>
				</div>
				<small style="color: #666; font-style: italic;">
					<?php echo esc_html__( 'Aperçu du breadcrumb mobile avec logo', 'NOVA-addons' ); ?>
					<# if ( settings.popup_animation_type === 'expand' ) { #>
						- <?php echo esc_html__( 'Mode Expand: popup s\'étend verticalement', 'NOVA-addons' ); ?>
					<# } #>
				</small>
			</div>
			
			<# if ( settings.enable_mobile_breadcrumb === 'yes' && settings.popup_animation_type === 'expand' ) { #>
				<div style="margin-top: 10px; padding: 15px; border: 1px dashed #0666DD; background: #f0f8ff; border-radius: 4px;">
					<small style="color: #0666DD; font-weight: bold;"><?php echo esc_html__( 'Aperçu Mode Expand:', 'NOVA-addons' ); ?></small>
					<div style="margin-top: 8px; padding: 10px; background: white; border-radius: 3px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
						<p style="margin: 0; font-size: 14px; color: #333;"><?php echo esc_html__( 'Le contenu du menu s\'affichera ici avec une animation d\'expansion verticale (height: 0 → auto)', 'NOVA-addons' ); ?></p>
					</div>
				</div>
			<# } #>
		<# } #>
		
		<div class="NOVA-editor-preview">
			<h3><?php echo esc_html__( 'Mega Menu Preview', 'NOVA-addons' ); ?></h3>
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

