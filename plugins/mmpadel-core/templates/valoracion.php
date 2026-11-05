<?php
/**
 * Plantilla: nota de Google.
 *   formato «marcador»: dos casillas como un marcador de pista (la nota con
 *   sus estrellas y el número de reseñas).
 *   formato «linea»: estrellas, nota y reseñas en una línea discreta.
 *
 * @package MMPadel_Core
 *
 * @var array $args Claves: nota, resenas, formato.
 */

defined( 'ABSPATH' ) || exit;

if ( 'linea' === $args['formato'] ) : ?>
<p class="mmpadel-valoracion-linea">
	<span class="mmpadel-valoracion-linea__estrellas" aria-hidden="true"></span>
	<span><strong><?php echo esc_html( $args['nota'] ); ?></strong><span class="screen-reader-text"> <?php esc_html_e( 'de 5', 'mmpadel-core' ); ?></span>
	<?php
	if ( $args['resenas'] ) {
		/* translators: %s: número de reseñas. */
		echo esc_html( sprintf( __( '· %s reseñas en Google', 'mmpadel-core' ), $args['resenas'] ) );
	} else {
		esc_html_e( 'en Google', 'mmpadel-core' );
	}
	?>
	</span>
</p>
<?php else : ?>
<div class="mmpadel-marcador">
	<div class="mmpadel-marcador__casilla">
		<span class="mmpadel-marcador__valor mmpadel-marcador__valor--nota"><?php echo esc_html( $args['nota'] ); ?><span class="screen-reader-text"> <?php esc_html_e( 'de 5', 'mmpadel-core' ); ?></span></span>
		<span class="mmpadel-marcador__estrellas" aria-hidden="true"></span>
	</div>
	<?php if ( $args['resenas'] ) : ?>
		<div class="mmpadel-marcador__casilla">
			<span class="mmpadel-marcador__valor"><?php echo esc_html( $args['resenas'] ); ?></span>
			<span class="mmpadel-marcador__texto"><?php esc_html_e( 'reseñas en Google', 'mmpadel-core' ); ?></span>
		</div>
	<?php else : ?>
		<div class="mmpadel-marcador__casilla">
			<span class="mmpadel-marcador__texto"><?php esc_html_e( 'en Google', 'mmpadel-core' ); ?></span>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>
