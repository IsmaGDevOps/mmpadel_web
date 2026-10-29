<?php
/**
 * Portada. Sin título de página: el H1 va en el bloque de cabecera del contenido.
 *
 * Si la portada muestra las últimas entradas en vez de una página, se usa el listado.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

if ( 'page' !== get_option( 'show_on_front' ) ) {
	get_template_part( 'home' );
	return;
}

get_header();
?>
<main id="contenido" class="site-main site-main--front">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="entry-content is-layout-constrained has-global-padding">
			<?php the_content(); ?>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
