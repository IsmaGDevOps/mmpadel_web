<?php
/**
 * Tarjeta de entrada en listados.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'mmpadel-tarjeta', array( 'loading' => 'lazy' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="card__body">
		<?php if ( 'post' === get_post_type() ) : ?>
			<p class="card__meta"><?php mmpadel_theme_posted_on(); ?></p>
		<?php endif; ?>
		<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="card__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
