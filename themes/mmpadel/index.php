<?php
/**
 * Plantilla de reserva: listados que no tienen plantilla propia (búsqueda…).
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="contenido" class="site-main">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title">
				<?php
				if ( is_search() ) {
					/* translators: %s: término buscado. */
					printf( esc_html__( 'Resultados para «%s»', 'mmpadel' ), esc_html( get_search_query() ) );
				} else {
					echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
				}
				?>
			</h1>
		</header>

		<?php get_template_part( 'template-parts/loop' ); ?>
	</div>
</main>
<?php
get_footer();
