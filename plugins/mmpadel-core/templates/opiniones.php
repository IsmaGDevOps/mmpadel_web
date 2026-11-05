<?php
/**
 * Plantilla: carrusel de opiniones (reseñas de Google copiadas en el panel).
 *
 * Tarjetas iguales en una fila que se desliza (scroll-snap): estrellas y
 * comilla arriba, el texto y, al pie, el autor con sus iniciales, el detalle
 * y cuándo. Sin JavaScript se desliza con el dedo, el trackpad o el teclado;
 * mmpadel-carrusel.js añade las flechas y la barra de progreso (que aquí
 * van con «hidden» hasta que el script las activa).
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array $opiniones Lista: autor, iniciales, nota (1–5), texto, detalle, cuando.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mmpadel-carrusel" data-mmpadel-carrusel>
	<ul class="mmpadel-opiniones" role="list" tabindex="0" aria-label="<?php esc_attr_e( 'Reseñas de Google', 'mmpadel-core' ); ?>">
		<?php foreach ( $args['opiniones'] as $opinion ) : ?>
			<li class="mmpadel-opinion">
				<figure>
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

	<div class="mmpadel-carrusel__controles" hidden>
		<span class="mmpadel-carrusel__progreso" aria-hidden="true"><span></span></span>
		<button type="button" class="mmpadel-carrusel__flecha" data-mmpadel-carrusel-ir="-1" aria-label="<?php esc_attr_e( 'Reseñas anteriores', 'mmpadel-core' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7"/></svg>
		</button>
		<button type="button" class="mmpadel-carrusel__flecha" data-mmpadel-carrusel-ir="1" aria-label="<?php esc_attr_e( 'Reseñas siguientes', 'mmpadel-core' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7"/></svg>
		</button>
	</div>
</div>
