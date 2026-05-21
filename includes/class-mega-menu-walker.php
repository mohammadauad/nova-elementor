<?php
namespace Nova_Addons_Elementor;

use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Walker de menu personnalisé pour le Mega Menu Elementor.
 *
 * Cette classe étend Walker_Nav_Menu pour injecter le contenu Elementor
 * dans les éléments de menu de premier niveau qui ont un contenu associé.
 */
class Mega_Menu_Walker extends \Walker_Nav_Menu {

	private $mega_menu_map = [];
	private $global_icons  = [];
	
	/**
	 * Variable statique pour éviter les boucles infinies de rendu.
	 */
	private static $rendering_content_ids = [];

	public function __construct( $mega_menu_map = [], $global_icons = [] ) {
		$this->mega_menu_map = $mega_menu_map;
		$this->global_icons  = $global_icons;
	}

	/**
	 * Démarre l'élément de liste <li>.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param WP_Post $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param stdClass $args An object of wp_nav_menu() arguments.
	 * @param int $id ID of the current menu item.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		// Vérifier si cet élément de menu a un contenu de mega menu associé.
		$is_mega_menu_item = false;
		$mega_content_id   = 0;
		$mega_icon         = [];
		$mega_icon_hover   = [];
		$mega_icon_active  = [];
		
		// Utiliser l'ID du menu item pour le mapping (le SELECT2 stocke l'ID, pas le titre)
		$item_key = (string) $item->ID;

		// Initialiser avec les icônes globales par défaut pour tous les items de niveau 0
		if ( 0 === $depth ) {
			$mega_icon        = ! empty( $this->global_icons['icon']['value'] ) ? $this->global_icons['icon'] : [];
			$mega_icon_hover  = ! empty( $this->global_icons['icon_hover']['value'] ) ? $this->global_icons['icon_hover'] : [];
			$mega_icon_active = ! empty( $this->global_icons['icon_active']['value'] ) ? $this->global_icons['icon_active'] : [];
		}

		if ( 0 === $depth && isset( $this->mega_menu_map[ $item_key ] ) ) {
			$mega_data = $this->mega_menu_map[ $item_key ];

			$mega_content_id  = isset( $mega_data['content_id'] ) ? (int) $mega_data['content_id'] : 0;
			// Les icônes du repeater ont priorité sur les globales
			if ( ! empty( $mega_data['icon']['value'] ) ) $mega_icon = $mega_data['icon'];
			if ( ! empty( $mega_data['icon_hover']['value'] ) ) $mega_icon_hover = $mega_data['icon_hover'];
			if ( ! empty( $mega_data['icon_active']['value'] ) ) $mega_icon_active = $mega_data['icon_active'];

			if ( $mega_content_id > 0 ) {
				$is_mega_menu_item = true;
				$classes[]         = 'nova-has-mega-menu';
			}
		}

		/**
		 * Filtre les classes CSS appliquées à chaque élément de liste de menu.
		 *
		 * @param array $classes The CSS classes that are applied to the menu item's `<li>`.
		 * @param WP_Post $item The current menu item object.
		 * @param stdClass $args An object of wp_nav_menu() arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filtre les attributs ID appliqués à chaque élément de liste de menu.
		 *
		 * @param string $menu_id The ID that is applied to the menu item's `<li>`.
		 * @param WP_Post $item The current menu item object.
		 * @param stdClass $args An object of wp_nav_menu() arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . '>';

		$atts           = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		/**
		 * Filtre les attributs HTML pour l'élément de lien de menu.
		 *
		 * @param array $atts The HTML attributes applied to the menu item's `<a>` element.
		 * @param WP_Post $item The current menu item object.
		 * @param stdClass $args An object of wp_nav_menu() arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		/**
		 * Filtre le titre de l'élément de menu.
		 *
		 * @param string $title The menu item's title.
		 * @param WP_Post $item The current menu item object.
		 * @param stdClass $args An object of wp_nav_menu() arguments.
		 * @param int $depth Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filtre le contenu HTML de l'élément de lien de menu.
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param WP_Post $item The current menu item object.
		 * @param int $depth Depth of menu item. Used for padding.
		 * @param stdClass $args An object of wp_nav_menu() arguments.
		 */
		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $args->link_before . '<span class="nova-mega-menu-title">' . $title . '</span>' . $args->link_after;

		// Si c'est un élément de méga menu de premier niveau, ajouter les icônes.
		if ( $is_mega_menu_item || ( 0 === $depth && ! empty( $mega_icon ) ) ) {
			$icon_wrapper = '<span class="nova-mega-menu-icon-wrapper">';
			
			// Icône normale (toujours affichée par défaut)
			if ( ! empty( $mega_icon ) ) {
				$icon_value = isset( $mega_icon['value'] ) ? $mega_icon['value'] : ( isset( $mega_icon['library'] ) ? $mega_icon : null );
				
				if ( ! empty( $icon_value ) ) {
					ob_start();
					\Elementor\Icons_Manager::render_icon( $mega_icon, [ 'aria-hidden' => 'true' ] );
					$icon_html = ob_get_clean();

					if ( ! empty( $icon_html ) ) {
						$icon_wrapper .= '<span class="nova-mega-menu-icon nova-mega-menu-icon-normal">' . $icon_html . '</span>';
					}
				}
			}
			
			// Icône hover (affichée au survol)
			if ( ! empty( $mega_icon_hover ) && isset( $mega_icon_hover['value'] ) && ! empty( $mega_icon_hover['value'] ) ) {
				ob_start();
				\Elementor\Icons_Manager::render_icon( $mega_icon_hover, [ 'aria-hidden' => 'true' ] );
				$icon_hover_html = ob_get_clean();

				if ( ! empty( $icon_hover_html ) ) {
					$icon_wrapper .= '<span class="nova-mega-menu-icon nova-mega-menu-icon-hover">' . $icon_hover_html . '</span>';
				}
			}
			
			// Icône active (affichée quand le mega menu est ouvert)
			if ( ! empty( $mega_icon_active ) && isset( $mega_icon_active['value'] ) && ! empty( $mega_icon_active['value'] ) ) {
				ob_start();
				\Elementor\Icons_Manager::render_icon( $mega_icon_active, [ 'aria-hidden' => 'true' ] );
				$icon_active_html = ob_get_clean();

				if ( ! empty( $icon_active_html ) ) {
					$icon_wrapper .= '<span class="nova-mega-menu-icon nova-mega-menu-icon-active">' . $icon_active_html . '</span>';
				}
			}
			
			$icon_wrapper .= '</span>';
			
			// Ajouter le wrapper d'icônes s'il contient au moins une icône
			if ( strpos( $icon_wrapper, 'nova-mega-menu-icon' ) !== false ) {
				$item_output .= $icon_wrapper;
			}
		}
		
		// Ajouter la description de l'item de menu si elle existe
		if ( ! empty( $item->description ) ) {
			$item_output .= '<span class="nova-mega-menu-description">' . esc_html( $item->description ) . '</span>';
		}



		$item_output .= '</a>';
		$item_output .= $args->after;

		// Si c'est un élément de méga menu de premier niveau, injecter le contenu.
		if ( $is_mega_menu_item ) {
			$item_output .= '<div class="nova-mega-menu-panel">';
			$item_output .= $this->get_elementor_content( $mega_content_id );
			$item_output .= '</div>';
		}

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Récupère le contenu Elementor pour un ID de publication donné.
	 *
	 * @param int $post_id L'ID du CPT 'mega_menu_content'.
	 * @return string Le contenu rendu.
	 */
	private function get_elementor_content( $post_id ) {
		if ( ! $post_id || ! is_numeric( $post_id ) ) {
			return '';
		}

		// Protection contre les boucles infinies : si ce contenu est déjà en cours de rendu, on arrête.
		if ( in_array( $post_id, self::$rendering_content_ids, true ) ) {
			return '<!-- Rendu récursif évité pour le contenu ID: ' . esc_html( $post_id ) . ' -->';
		}

		// Limite de profondeur : éviter trop de niveaux de rendu
		if ( count( self::$rendering_content_ids ) > 5 ) {
			return '<!-- Profondeur de rendu maximale atteinte -->';
		}

		// Vérifier si Elementor est chargé.
		if ( ! did_action( 'elementor/loaded' ) ) {
			return '<!-- Elementor non chargé -->';
		}

		// Vérifier que le post existe, est du bon type ET est publié (important pour les déconnectés)
		$post = get_post( $post_id );
		if ( ! $post || 'mega_menu_content' !== $post->post_type || 'publish' !== $post->post_status ) {
			return '';
		}

		// Ajouter cet ID à la liste des contenus en cours de rendu.
		self::$rendering_content_ids[] = $post_id;

		try {
			// ✅ FIX FINAL: Utiliser le shortcode Elementor pour un rendu universel (connecté/déconnecté)
			// Le shortcode contourne les restrictions de droits et gère mieux les styles.
			$content = do_shortcode( '[elementor-template id="' . $post_id . '"]' );
			
			// Si le shortcode ne renvoie rien, tenter les méthodes directes d'Elementor
			if ( empty( $content ) ) {
				$frontend = \Elementor\Plugin::instance()->frontend;
				
				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					$css_file = new \Elementor\Core\Files\CSS\Post( $post_id );
					$css_file->enqueue();
				}

				$content = $frontend->get_builder_content( $post_id, true );
				
				if ( empty( $content ) ) {
					$content = $frontend->get_builder_content_for_display( $post_id );
				}
			}
			
			// Limiter la taille du contenu
			if ( strlen( $content ) > 1000000 ) {
				$content = substr( $content, 0, 1000000 ) . '<!-- Contenu tronqué -->';
			}
			
		} catch ( \Exception $e ) {
			// En cas d'erreur, retourner un message vide plutôt que de planter.
			$content = '<!-- Erreur lors du rendu du contenu ID: ' . esc_html( $post_id ) . ' - ' . esc_html( $e->getMessage() ) . ' -->';
		} catch ( \Error $e ) {
			// Capturer aussi les erreurs fatales PHP 7+
			$content = '<!-- Erreur fatale lors du rendu du contenu ID: ' . esc_html( $post_id ) . ' -->';
		} finally {
			// Retirer cet ID de la liste après le rendu.
			$key = array_search( $post_id, self::$rendering_content_ids, true );
			if ( false !== $key ) {
				unset( self::$rendering_content_ids[ $key ] );
				self::$rendering_content_ids = array_values( self::$rendering_content_ids );
			}
		}

		return $content;
	}
}
