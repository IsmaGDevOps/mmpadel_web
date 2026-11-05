<?php
/**
 * Páginas. El contenido se edita con el editor de bloques.
 *
 * Si la página tiene foto de portada (ver mmpadel_theme_cover()), el título
 * va sobre ella en una franja a todo el ancho, con las migas de pan y la
 * entradilla (el primer párrafo de la página si lleva la clase
 * «mmpadel-entradilla», ver mmpadel_theme_split_intro()).
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
		$mmpadel_intro = $mmpadel_cover ? mmpadel_theme_split_intro() : array(
			'entradilla' => '',
			'contenido'  => null,
		);
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
			<?php if ( $mmpadel_cover ) : ?>
				<div class="page-hero"<?php echo mmpadel_theme_cover_style( $mmpadel_cover ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función. ?>>
					<header class="entry-header container">
						<nav class="page-hero__migas" aria-label="<?php esc_attr_e( 'Migas de pan', 'mmpadel' ); ?>">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'mmpadel' ); ?></a>
							<span aria-hidden="true">/</span>
							<span aria-current="page"><?php the_title(); ?></span>
						</nav>
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
						<?php if ( $mmpadel_intro['entradilla'] ) : ?>
							<div class="page-hero__entradilla">
								<?php echo $mmpadel_intro['entradilla']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bloque del contenido de la página, como the_content(). ?>
							</div>
						<?php endif; ?>
					</header>
				</div>
			<?php else : ?>
				<header class="entry-header container">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header>
			<?php endif; ?>
			<div class="entry-content is-layout-constrained has-global-padding">
				<?php
				if ( null !== $mmpadel_intro['contenido'] ) {
					echo $mmpadel_intro['contenido']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenido filtrado con the_content.
				} else {
					the_content();
				}
				?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
