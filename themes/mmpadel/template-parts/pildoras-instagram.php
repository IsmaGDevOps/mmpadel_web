<?php
/**
 * Cierre de las píldoras: invitación a Instagram, donde están los vídeos, y
 * al formulario. Sin enlace de Instagram en Ajustes → MMPadel, solo el
 * formulario.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_instagram = mmpadel_theme_option( 'instagram_url' );
?>
<div class="container">
	<section class="mmpadel-cierre pildoras-cierre" aria-labelledby="pildoras-cierre-titulo">
		<h2 id="pildoras-cierre-titulo">
			<?php if ( $mmpadel_instagram ) : ?>
				<?php
				/* translators: %s: palabra resaltada. */
				printf( esc_html__( 'Las píldoras, también en %s', 'mmpadel' ), '<mark>' . esc_html__( 'vídeo', 'mmpadel' ) . '</mark>' );
				?>
			<?php else : ?>
				<?php
				/* translators: %s: palabra resaltada. */
				printf( esc_html__( '¿Lo trabajamos en %s?', 'mmpadel' ), '<mark>' . esc_html__( 'pista', 'mmpadel' ) . '</mark>' );
				?>
			<?php endif; ?>
		</h2>
		<p>
			<?php
			echo esc_html(
				$mmpadel_instagram
					? __( 'Síguenos en Instagram para ver los consejos explicados en pista.', 'mmpadel' )
					: __( 'En clase practicamos cada consejo con ejercicios de tu nivel.', 'mmpadel' )
			);
			?>
		</p>
		<?php if ( $mmpadel_instagram ) : ?>
			<div class="mmpadel-boton">
				<a class="button button--cta" href="<?php echo esc_url( $mmpadel_instagram ); ?>" target="_blank" rel="noopener"><?php mmpadel_theme_icon( 'instagram' ); ?><?php esc_html_e( 'Síguenos en Instagram', 'mmpadel' ); ?></a>
			</div>
		<?php endif; ?>
		<div class="mmpadel-boton">
			<a class="button <?php echo $mmpadel_instagram ? 'button--outline' : 'button--cta'; ?>" href="<?php echo esc_url( mmpadel_theme_page_url( 'encuentra-tu-grupo' ) ); ?>"><?php esc_html_e( 'Encuentra tu grupo', 'mmpadel' ); ?></a>
		</div>
	</section>
</div>
