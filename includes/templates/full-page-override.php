<?php
/**
 * Full Page Override Template
 * Renders nova_template header, page content, and footer via Elementor.
 *
 * @package Nova Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_id          = (int) Nova_Addons_Elementor::resolve_nova_template_id( 'header', 'nova_header_template' );
$footer_id          = (int) Nova_Addons_Elementor::resolve_nova_template_id( 'footer', 'nova_footer_template' );
$single_template_id = 0;

if ( is_singular() ) {
	$post_type          = get_post_type();
	$single_template_id = (int) Nova_Addons_Elementor::resolve_nova_template_id( 'single', 'nova_single_' . $post_type . '_template', false );
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<?php if ( $header_id ) : ?>
		<header class="nova-header-template">
			<?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $header_id, true ); ?>
		</header>
	<?php endif; ?>

	<main id="content" class="site-content">
	<?php
	if ( $single_template_id ) {
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $single_template_id, true );
	} else {
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				the_content();
			}
		}
	}
	?>
	</main>

	<?php if ( $footer_id ) : ?>
		<footer class="nova-footer-template">
			<?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $footer_id, true ); ?>
		</footer>
	<?php endif; ?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
