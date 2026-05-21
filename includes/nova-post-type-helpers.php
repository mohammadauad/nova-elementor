<?php
/**
 * Options de types de publication pour les widgets Elementor NOVA (dont CPT admin-only).
 *
 * @package Nova_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'nova_addons_get_elementor_post_type_options' ) ) {
	/**
	 * Types de contenu proposés dans les selects « Post Type » des widgets NOVA.
	 *
	 * Inclut les CPT publics et ceux visibles en admin (`show_ui`), ex. mission.
	 *
	 * @return array<string, string> slug => label
	 */
	function nova_addons_get_elementor_post_type_options(): array {
		$public = get_post_types( array( 'public' => true ), 'objects' );
		$admin  = get_post_types(
			array(
				'public'   => false,
				'show_ui'  => true,
			),
			'objects'
		);

		$skip = array(
			'attachment',
			'elementor_library',
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
			'wp_template',
			'wp_template_part',
			'wp_global_styles',
			'wp_navigation',
		);
		$skip = apply_filters( 'nova_addons_elementor_post_type_exclude', $skip );

		$merged = array_merge( $public, $admin );
		$options = array();

		foreach ( $merged as $pt ) {
			if ( ! $pt instanceof \WP_Post_Type ) {
				continue;
			}
			$name = $pt->name;
			if ( in_array( $name, $skip, true ) ) {
				continue;
			}
			$options[ $name ] = $pt->labels->name ?? $pt->label;
		}

		natcasesort( $options );

		return apply_filters( 'nova_addons_elementor_post_type_options', $options );
	}
}
