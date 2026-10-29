<?php
/**
 * Blog (Píldoras MMPadel).
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_blog_page = (int) get_option( 'page_for_posts' );

get_header();
?>
<main id="contenido" class="site-main">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title">
				<?php echo esc_html( $mmpadel_blog_page ? get_the_title( $mmpadel_blog_page ) : __( 'Píldoras MMPadel', 'mmpadel' ) ); ?>
			</h1>
			<?php if ( $mmpadel_blog_page && has_excerpt( $mmpadel_blog_page ) ) : ?>
				<p class="page-intro"><?php echo esc_html( get_the_excerpt( $mmpadel_blog_page ) ); ?></p>
			<?php endif; ?>
		</header>

		<?php get_template_part( 'template-parts/loop' ); ?>
	</div>
</main>
<?php
get_footer();
