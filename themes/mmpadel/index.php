<?php
/**
 * Plantilla de reserva.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="contenido" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_title( '<h1>', '</h1>' );
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
