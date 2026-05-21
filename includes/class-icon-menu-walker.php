<?php
namespace Nova_Addons_Elementor;

use \Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Walker de menu personnalisé pour le Menu avec Icônes et Description Elementor.
 */
class Icon_Menu_Walker extends \Walker_Nav_Menu {

	private $icon_menu_map = [];
	private $dropdown_icon = [];
	private $dropdown_icon_position = 'right';

	/**
	 * Constructeur.
	 *
	 * @param array $settings Le tableau de paramètres du widget.
	 */
	public function __construct( $settings = [] ) {
		if ( isset( $settings['icon_menu_map'] ) ) {
			$this->icon_menu_map = $settings['icon_menu_map'];
		}
		if ( isset( $settings['dropdown_icon'] ) ) {
			$this->dropdown_icon = $settings['dropdown_icon'];
		}
		if ( isset( $settings['dropdown_icon_position'] ) ) {
			$this->dropdown_icon_position = $settings['dropdown_icon_position'];
		}
	}

	/**
	 * Démarre l'élément de liste <li>.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		// Utiliser l'ID du menu item directement (pas le titre)
		$item_id = absint( $item->ID );
		$item_data = isset( $this->icon_menu_map[ $item_id ] ) ? $this->icon_menu_map[ $item_id ] : [];
		$has_custom_data = ! empty( $item_data );

		if ( $has_custom_data ) {
			$classes[] = 'emmw-has-icon-desc';
		}

		// Vérifier si l'élément a des enfants (sous-menu)
		$has_children = in_array( 'menu-item-has-children', $classes );
		if ( $has_children ) {
			$classes[] = 'nova-has-submenu';
		}

		/**
		 * Filtre les classes CSS appliquées à chaque élément de liste de menu.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filtre les attributs ID appliqués à chaque élément de liste de menu.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . '>';

		$atts           = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		// Si l'élément a des enfants, ajouter une classe au lien
		if ( $has_children ) {
			$atts['class'] = 'nova-submenu-toggle';
		}

		/**
		 * Filtre les attributs HTML pour l'élément de lien de menu.
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
		 */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filtre le contenu HTML de l'élément de lien de menu.
		 */
		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';

		// Contenu du lien
		$item_content = '';

		// 1. Icône principale (uniquement pour les éléments de premier niveau avec données personnalisées)
		if ( $depth === 0 && $has_custom_data ) {
			$icon_type = isset( $item_data['type'] ) ? $item_data['type'] : 'icon';

			if ( 'icon' === $icon_type && ! empty( $item_data['icon']['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $item_data['icon'], [ 'aria-hidden' => 'true' ] );
				$icon_html = ob_get_clean();
				if ( ! empty( $icon_html ) ) {
					$item_content .= '<span class="nova-icon-menu-icon">' . $icon_html . '</span>';
				}
			} elseif ( 'image' === $icon_type && ! empty( $item_data['image']['url'] ) ) {
				$image_url = esc_url( $item_data['image']['url'] );
				$alt       = isset( $item_data['image']['alt'] ) ? esc_attr( $item_data['image']['alt'] ) : '';
				$item_content .= sprintf(
					'<span class="nova-icon-menu-icon"><img src="%1$s" alt="%2$s" loading="lazy" decoding="async" /></span>',
					$image_url,
					$alt
				);
			}
		}

		// 2. Wrapper du texte (titre + description + icône dropdown)
		$item_content .= '<div class="nova-icon-menu-text-wrap">';

		// Préparer l'icône dropdown si nécessaire (seulement pour le premier niveau)
		$dropdown_icon_html = '';
		if ( $depth === 0 && $has_children && ! empty( $this->dropdown_icon['value'] ) ) {
			ob_start(); // Démarre la capture
			Icons_Manager::render_icon( $this->dropdown_icon, [ 'aria-hidden' => 'true' ] );
			$dropdown_icon_html = '<span class="NOVA-dropdown-icon">' . ob_get_clean() . '</span>';
		}

		// 3. Icône dropdown à gauche (si position = left)
		if ( $this->dropdown_icon_position === 'left' && ! empty( $dropdown_icon_html ) ) {
			$item_content .= $dropdown_icon_html;
		}

		// 4. Titre
		$item_content .= '<span class="nova-icon-menu-title">' . $args->link_before . $title . $args->link_after . '</span>';

		// 5. Description
		if ( $depth === 0 && $has_custom_data && ! empty( $item_data['description'] ) ) {
			$item_content .= '<div class="nova-icon-menu-description">' . ( $item_data['description'] ) . '</div>';
		}

		// 6. Icône dropdown à droite (si position = right)
		if ( $this->dropdown_icon_position === 'right' && ! empty( $dropdown_icon_html ) ) {
			$item_content .= $dropdown_icon_html;
		}

		$item_content .= '</div>'; // Fermeture du wrapper de texte


		$item_output .= $item_content;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
