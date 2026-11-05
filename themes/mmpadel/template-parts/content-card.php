<?php
/**
 * Tarjeta de entrada en listados (píldoras, temas y búsqueda).
 *
 * Toda la tarjeta lleva a la entrada (el enlace del título se estira). Sin
 * imagen destacada, la tarjeta pinta una cápsula sobre las líneas de una
 * pista. Las píldoras con vídeo llevan la etiqueta «Vídeo».
 *
 * @package MMPadel
 *
 * @var array $args {
 *     @type bool $destacada Tarjeta grande, a todo el ancho (la primera del listado).
 * }
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_destacada = ! empty( $args['destacada'] );
$mmpadel_es_post   = 'post' === get_post_type();
$mmpadel_categoria = $mmpadel_es_post ? mmpadel_theme_post_category() : null;
$mmpadel_video     = $mmpadel_es_post && mmpadel_theme_post_has_video();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( $mmpadel_destacada ? 'card card--destacada' : 'card' ); ?>>
	<div class="card__media">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( $mmpadel_destacada ? 'large' : 'mmpadel-tarjeta', $mmpadel_destacada ? array() : array( 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="card__arte" aria-hidden="true"><span class="capsula"></span></span>
		<?php endif; ?>
		<?php if ( $mmpadel_video ) : ?>
			<span class="card__video" aria-hidden="true"><?php esc_html_e( 'Vídeo', 'mmpadel' ); ?></span>
		<?php endif; ?>
	</div>
	<div class="card__body">
		<?php if ( $mmpadel_categoria ) : ?>
			<p class="card__categoria"><a href="<?php echo esc_url( get_category_link( $mmpadel_categoria ) ); ?>"><?php echo esc_html( $mmpadel_categoria->name ); ?></a></p>
		<?php endif; ?>
		<h2 class="card__title">
			<a href="<?php the_permalink(); ?>">
				<?php the_title(); ?>
				<?php if ( $mmpadel_video ) : ?>
					<span class="screen-reader-text"><?php esc_html_e( '(con vídeo)', 'mmpadel' ); ?></span>
				<?php endif; ?>
			</a>
		</h2>
		<div class="card__excerpt"><?php the_excerpt(); ?></div>
		<?php if ( $mmpadel_es_post ) : ?>
			<p class="card__meta">
				<?php mmpadel_theme_posted_on(); ?>
				<span aria-hidden="true">·</span>
				<?php echo esc_html( mmpadel_theme_reading_time() ); ?>
			</p>
		<?php endif; ?>
		<?php if ( $mmpadel_destacada ) : ?>
			<p class="card__leer" aria-hidden="true"><?php esc_html_e( 'Leer la píldora', 'mmpadel' ); ?></p>
		<?php endif; ?>
	</div>
</article>
