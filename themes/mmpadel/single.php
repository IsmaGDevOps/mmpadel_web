<?php
/**
 * Entrada individual (píldoras) y fichas públicas de tipos de contenido, como los eventos.
 *
 * Los datos propios de cada tipo (fecha del evento, nivel…) los añade el
 * plugin mmpadel-core al contenido.
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
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
			<header class="entry-header container">
				<?php if ( 'post' === get_post_type() ) : ?>
					<p class="entry-meta"><?php mmpadel_theme_posted_on(); ?></p>
				<?php endif; ?>
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

			<?php if ( 'post' === get_post_type() ) : ?>
				<footer class="entry-footer container">
					<?php
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-label">' . esc_html__( 'Anterior', 'mmpadel' ) . '</span> %title',
							'next_text' => '<span class="nav-label">' . esc_html__( 'Siguiente', 'mmpadel' ) . '</span> %title',
						)
					);
					?>
				</footer>
			<?php endif; ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
