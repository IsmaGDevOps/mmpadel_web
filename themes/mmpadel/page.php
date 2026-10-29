<?php
/**
 * Páginas. El contenido se edita con el editor de bloques.
 *
 * Si la página tiene foto de portada (ver mmpadel_theme_cover()), el título
 * va sobre ella en una franja a todo el ancho.
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
		$mmpadel_cover = mmpadel_theme_cover();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
			<?php if ( $mmpadel_cover ) : ?>
				<div class="page-hero"<?php echo mmpadel_theme_cover_style( $mmpadel_cover ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función. ?>>
					<header class="entry-header container">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>
				</div>
			<?php else : ?>
				<header class="entry-header container">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>
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
