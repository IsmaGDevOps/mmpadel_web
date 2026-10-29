<?php
/**
 * Archivos: categorías, etiquetas, fechas y tipos de contenido con archivo.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="contenido" class="site-main">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
			<?php the_archive_description( '<div class="page-intro">', '</div>' ); ?>
		</header>

		<?php get_template_part( 'template-parts/loop' ); ?>
	</div>
</main>
<?php
get_footer();
