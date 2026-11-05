<?php
/**
 * Plantilla: muro de opiniones (reseñas de Google copiadas en el panel).
 *
 * Cada opinión es un bocadillo con sus estrellas y el texto; debajo, el
 * autor con sus iniciales, el detalle y cuándo. Las tarjetas se reparten en
 * columnas de distinto alto, como un muro.
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array $opiniones Lista: autor, iniciales, nota (1–5), texto, detalle, cuando.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<ul class="mmpadel-opiniones" role="list">
	<?php foreach ( $args['opiniones'] as $opinion ) : ?>
		<li class="mmpadel-opinion">
			<figure>
				<div class="mmpadel-opinion__bocadillo">
					<p class="mmpadel-opinion__estrellas" style="--nota: <?php echo (int) $opinion['nota']; ?>">
						<span class="screen-reader-text">
							<?php
							/* translators: %d: número de estrellas. */
							echo esc_html( sprintf( _n( '%d estrella de 5', '%d estrellas de 5', $opinion['nota'], 'mmpadel-core' ), $opinion['nota'] ) );
							?>
						</span>
					</p>
					<blockquote class="mmpadel-opinion__texto">
						<p><?php echo nl2br( esc_html( $opinion['texto'] ) ); ?></p>
					</blockquote>
				</div>
				<figcaption class="mmpadel-opinion__autor">
					<span class="mmpadel-opinion__avatar" aria-hidden="true"><?php echo esc_html( $opinion['iniciales'] ); ?></span>
					<span>
						<strong><?php echo esc_html( $opinion['autor'] ); ?></strong>
						<span class="mmpadel-opinion__detalle">
							<?php
							echo esc_html(
								implode(
									' · ',
									array_filter( array( $opinion['detalle'], $opinion['cuando'], __( 'Reseña en Google', 'mmpadel-core' ) ) )
								)
							);
							?>
						</span>
					</span>
				</figcaption>
			</figure>
		</li>
	<?php endforeach; ?>
</ul>
