<?php
/**
 * Una píldora (entrada del blog): cabecera con tema, fecha y lectura, la
 * imagen, el contenido, la llamada «Llévalo a la pista» y «Más píldoras»
 * (del mismo tema primero y, si faltan, las más recientes).
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_categoria = mmpadel_theme_post_category();
$mmpadel_video     = mmpadel_theme_post_has_video();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry pildora' ); ?>>
	<header class="entry-header container pildora__cabecera">
		<p class="pildora__volver"><a href="<?php echo esc_url( mmpadel_theme_blog_url() ); ?>"><?php esc_html_e( '← Todas las píldoras', 'mmpadel' ); ?></a></p>
		<?php if ( $mmpadel_categoria || $mmpadel_video ) : ?>
			<p class="pildora__etiquetas">
				<?php if ( $mmpadel_categoria ) : ?>
					<a class="card__categoria-enlace" href="<?php echo esc_url( get_category_link( $mmpadel_categoria ) ); ?>"><?php echo esc_html( $mmpadel_categoria->name ); ?></a>
				<?php endif; ?>
				<?php if ( $mmpadel_video ) : ?>
					<span class="pildora__video"><?php esc_html_e( 'Con vídeo', 'mmpadel' ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<p class="entry-meta">
			<?php mmpadel_theme_posted_on(); ?>
			<span aria-hidden="true">·</span>
			<?php echo esc_html( mmpadel_theme_reading_time() ); ?>
		</p>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry-thumbnail container">
			<?php the_post_thumbnail( 'large' ); ?>
		</figure>
	<?php endif; ?>

	<div class="entry-content is-layout-constrained has-global-padding">
		<?php the_content(); ?>
	</div>

	<div class="container pildora__pie">
		<aside class="pildora__pista" aria-labelledby="pildora-pista-titulo">
			<h2 id="pildora-pista-titulo"><?php esc_html_e( 'Llévalo a la pista', 'mmpadel' ); ?></h2>
			<p><?php esc_html_e( 'En clase practicamos cada consejo con ejercicios de tu nivel y un entrenador que te corrige.', 'mmpadel' ); ?></p>
			<a class="button button--cta" href="<?php echo esc_url( mmpadel_theme_page_url( 'encuentra-tu-grupo' ) ); ?>"><?php esc_html_e( 'Encuentra tu grupo', 'mmpadel' ); ?></a>
		</aside>
	</div>
</article>

<?php
// Más píldoras: del mismo tema y, si no llegan a tres, las más recientes.
$mmpadel_excluir = array( get_the_ID() );
$mmpadel_mas     = array();
if ( $mmpadel_categoria ) {
	$mmpadel_mas = get_posts(
		array(
			'posts_per_page'      => 3,
			'cat'                 => $mmpadel_categoria->term_id,
			'post__not_in'        => $mmpadel_excluir, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- una sola entrada.
			'ignore_sticky_posts' => true,
		)
	);
}
if ( count( $mmpadel_mas ) < 3 ) {
	$mmpadel_mas = array_merge(
		$mmpadel_mas,
		get_posts(
			array(
				'posts_per_page'      => 3 - count( $mmpadel_mas ),
				'post__not_in'        => array_merge( $mmpadel_excluir, wp_list_pluck( $mmpadel_mas, 'ID' ) ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- pocas entradas.
				'ignore_sticky_posts' => true,
			)
		)
	);
}

if ( $mmpadel_mas ) :
	?>
	<section class="container pildora__mas" aria-labelledby="pildora-mas-titulo">
		<h2 id="pildora-mas-titulo"><?php esc_html_e( 'Más píldoras', 'mmpadel' ); ?></h2>
		<div class="card-grid">
			<?php
			global $post;
			foreach ( $mmpadel_mas as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- se restaura con wp_reset_postdata().
				setup_postdata( $post );
				get_template_part( 'template-parts/content', 'card' );
			endforeach;
			wp_reset_postdata();
			?>
		</div>
	</section>
	<?php
endif;
