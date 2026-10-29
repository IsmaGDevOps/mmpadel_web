<?php
/**
 * Portada. Sin título de página: el H1 va en el bloque de cabecera del contenido.
 * La foto de portada (ver mmpadel_theme_cover()) se pasa como variable CSS y
 * la usa de fondo el grupo con la clase mmpadel-hero.
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
		<div class="entry-content is-layout-constrained has-global-padding"<?php echo mmpadel_theme_cover_style( mmpadel_theme_cover() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función. ?>>
			<?php the_content(); ?>
		</div>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
