<?php
/**
 * Entrada individual y fichas públicas de tipos de contenido, como los eventos.
 *
 * Las píldoras (entradas del blog) usan template-parts/pildora.php. Los datos
 * propios de cada tipo (fecha del evento, nivel…) los añade el plugin
 * mmpadel-core al contenido.
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

		if ( 'post' === get_post_type() ) {
			get_template_part( 'template-parts/pildora' );
			continue;
		}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
			<header class="entry-header container">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-thumbnail container">
					<?php the_post_thumbnail( 'large' ); ?>
				</figure>
			<?php endif; ?>

			<div class="entry-content is-layout-constrained has-global-padding">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
