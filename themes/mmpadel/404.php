<?php
/**
 * Página no encontrada.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="contenido" class="site-main">
	<div class="container container--narrow">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Esta página no existe', 'mmpadel' ); ?></h1>
		</header>
		<p><?php esc_html_e( 'Puede que el enlace esté mal o que la página se haya movido. Prueba a buscar o vuelve al inicio.', 'mmpadel' ); ?></p>
		<?php get_search_form(); ?>
		<p class="button-row">
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ir al inicio', 'mmpadel' ); ?></a>
			<a class="button button--outline" href="<?php echo esc_url( mmpadel_theme_page_url( 'encuentra-tu-grupo' ) ); ?>"><?php esc_html_e( 'Encuentra tu grupo', 'mmpadel' ); ?></a>
		</p>
	</div>
</main>
<?php
get_footer();
