<?php
/**
 * Plantilla: formulario «Encuentra tu grupo».
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array    $schema      Esquema de campos (MMPadel_Form::schema()).
 *     @type array    $sections    Secciones: clave => título.
 *     @type array    $form_values      Valores anteriores (si hubo errores).
 *     @type array    $form_errors      Errores: campo => mensaje; '_form' = error general.
 *     @type string   $action      URL de admin-post.php.
 *     @type string   $volver      URL de esta página.
 *     @type string   $timestamp   Marca de tiempo firmada.
 *     @type string   $privacidad  URL de la política de privacidad.
 *     @type string[] $origin_keys Campos de origen ocultos.
 * }
 */

defined( 'ABSPATH' ) || exit;

$form_errors = $args['errors'];
$form_values = $args['values'];
$schema      = $args['schema'];

/**
 * Atributos de ayuda y error de un campo: aria-describedby y, en controles
 * (no en fieldset, donde no es válido), aria-invalid.
 */
$error_attrs   = static function ( $key, $help_id = '', $invalid = true ) use ( $form_errors ) {
	$ids = array_filter( array( $help_id, isset( $form_errors[ $key ] ) ? 'mmpadel-error-' . $key : '' ) );
	$out = $ids ? ' aria-describedby="' . esc_attr( implode( ' ', $ids ) ) . '"' : '';
	return $out . ( $invalid && isset( $form_errors[ $key ] ) ? ' aria-invalid="true"' : '' );
};
$required_mark = ' <span class="mmpadel-req" aria-hidden="true">*</span>';
?>
<form id="mmpadel-formulario" class="mmpadel-form" method="post" action="<?php echo esc_url( $args['action'] ); ?>">

	<?php if ( $form_errors ) : ?>
		<div class="mmpadel-form__resumen" role="alert" tabindex="-1">
			<p><strong><?php esc_html_e( 'Revisa estos datos para poder enviar la solicitud:', 'mmpadel-core' ); ?></strong></p>
			<ul>
				<?php foreach ( $form_errors as $key => $message ) : ?>
					<?php if ( '_form' === $key ) : ?>
						<li><?php echo esc_html( $message ); ?></li>
					<?php else : ?>
						<li><a href="#mmpadel-campo-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $message ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php foreach ( $args['sections'] as $section_key => $section_title ) : ?>
		<div class="mmpadel-form__seccion">
			<h2 class="mmpadel-form__titulo"><?php echo esc_html( $section_title ); ?></h2>

			<?php
			foreach ( $schema as $key => $field ) :
				if ( $field['section'] !== $section_key ) {
					continue;
				}
				$help_id = $field['help'] ? 'mmpadel-ayuda-' . $key : '';
				$value   = $form_values[ $key ] ?? ( 'checkbox' === $field['type'] ? array() : '' );
				$classes = 'mmpadel-campo mmpadel-campo--' . $field['type'] . ( isset( $form_errors[ $key ] ) ? ' mmpadel-campo--error' : '' );
				?>

				<?php if ( in_array( $field['type'], array( 'radio', 'checkbox' ), true ) ) : ?>
					<fieldset id="mmpadel-campo-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>"<?php echo $error_attrs( $key, $help_id, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en $error_attrs. ?>>
						<legend><?php echo esc_html( $field['label'] ); ?><?php echo $field['required'] ? $required_mark : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML fijo. ?></legend>
						<?php if ( $field['help'] ) : ?>
							<p class="mmpadel-ayuda" id="<?php echo esc_attr( $help_id ); ?>"><?php echo esc_html( $field['help'] ); ?></p>
						<?php endif; ?>
						<?php if ( isset( $form_errors[ $key ] ) ) : ?>
							<p class="mmpadel-error" id="mmpadel-error-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $form_errors[ $key ] ); ?></p>
						<?php endif; ?>
						<div class="mmpadel-opciones">
							<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
								<?php
								$input_id = 'mmpadel-' . $key . '-' . $option_value;
								$checked  = 'checkbox' === $field['type'] ? in_array( (string) $option_value, (array) $value, true ) : (string) $option_value === (string) $value;
								?>
								<label class="mmpadel-opcion" for="<?php echo esc_attr( $input_id ); ?>">
									<input
										type="<?php echo esc_attr( $field['type'] ); ?>"
										id="<?php echo esc_attr( $input_id ); ?>"
										name="mmpadel[<?php echo esc_attr( $key ); ?>]<?php echo 'checkbox' === $field['type'] ? '[]' : ''; ?>"
										value="<?php echo esc_attr( $option_value ); ?>"
										<?php checked( $checked ); ?>
										<?php echo 'radio' === $field['type'] && $field['required'] ? 'required' : ''; ?>
									>
									<span><?php echo esc_html( $option_label ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</fieldset>

				<?php else : ?>
					<div id="mmpadel-campo-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>">
						<label for="mmpadel-<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $field['label'] ); ?><?php echo $field['required'] ? $required_mark : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML fijo. ?>
						</label>
						<?php if ( $field['help'] ) : ?>
							<p class="mmpadel-ayuda" id="<?php echo esc_attr( $help_id ); ?>"><?php echo esc_html( $field['help'] ); ?></p>
						<?php endif; ?>
						<?php if ( isset( $form_errors[ $key ] ) ) : ?>
							<p class="mmpadel-error" id="mmpadel-error-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $form_errors[ $key ] ); ?></p>
						<?php endif; ?>
						<?php if ( 'textarea' === $field['type'] ) : ?>
							<textarea id="mmpadel-<?php echo esc_attr( $key ); ?>" name="mmpadel[<?php echo esc_attr( $key ); ?>]" rows="4" maxlength="2000"<?php echo $error_attrs( $key, $help_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en $error_attrs. ?>><?php echo esc_textarea( $value ); ?></textarea>
						<?php else : ?>
							<input
								type="<?php echo esc_attr( $field['type'] ); ?>"
								id="mmpadel-<?php echo esc_attr( $key ); ?>"
								name="mmpadel[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( $value ); ?>"
								maxlength="120"
								<?php echo $field['autocomplete'] ? 'autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : ''; ?>
								<?php echo 'tel' === $field['type'] ? 'inputmode="tel"' : ''; ?>
								<?php echo $field['required'] ? 'required' : ''; ?>
								<?php echo $error_attrs( $key, $help_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en $error_attrs. ?>
							>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>

	<div id="mmpadel-campo-privacidad" class="mmpadel-campo mmpadel-campo--consentimiento<?php echo isset( $form_errors['privacidad'] ) ? ' mmpadel-campo--error' : ''; ?>">
		<?php if ( isset( $form_errors['privacidad'] ) ) : ?>
			<p class="mmpadel-error" id="mmpadel-error-privacidad"><?php echo esc_html( $form_errors['privacidad'] ); ?></p>
		<?php endif; ?>
		<label class="mmpadel-opcion mmpadel-opcion--consentimiento" for="mmpadel-privacidad">
			<input type="checkbox" id="mmpadel-privacidad" name="mmpadel[privacidad]" value="1" required <?php checked( ! empty( $form_values['privacidad'] ) ); ?><?php echo $error_attrs( 'privacidad', 'mmpadel-info-privacidad' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en $error_attrs. ?>>
			<span>
				<?php
				printf(
					/* translators: %s: enlace a la política de privacidad. */
					esc_html__( 'He leído y acepto la %s.', 'mmpadel-core' ),
					'<a href="' . esc_url( $args['privacidad'] ) . '" target="_blank" rel="noopener">' . esc_html__( 'política de privacidad', 'mmpadel-core' ) . '</a>'
				);
				?>
				<span class="mmpadel-req" aria-hidden="true">*</span>
			</span>
		</label>
		<p class="mmpadel-ayuda mmpadel-info-privacidad" id="mmpadel-info-privacidad">
			<?php
			$titular = mmpadel_get_option( 'titular' );
			printf(
				/* translators: %s: titular de la web. */
				esc_html__( 'Responsable: %s. Finalidad: estudiar tu solicitud y proponerte un grupo. Puedes acceder, rectificar y suprimir tus datos, entre otros derechos, como se explica en la política de privacidad.', 'mmpadel-core' ),
				esc_html( $titular ? $titular : get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>

	<?php // Antispam: campo trampa que las personas no ven y los bots rellenan. ?>
	<div class="mmpadel-hp" aria-hidden="true">
		<label for="mmpadel-web"><?php esc_html_e( 'No rellenes este campo', 'mmpadel-core' ); ?></label>
		<input type="text" id="mmpadel-web" name="mmpadel_web" value="" tabindex="-1" autocomplete="off">
	</div>

	<?php foreach ( $args['origin_keys'] as $origin_key ) : ?>
		<input type="hidden" name="origen[<?php echo esc_attr( $origin_key ); ?>]" value="" data-mmpadel-origen="<?php echo esc_attr( $origin_key ); ?>">
	<?php endforeach; ?>

	<input type="hidden" name="action" value="<?php echo esc_attr( MMPadel_Form::ACTION ); ?>">
	<input type="hidden" name="mmpadel_volver" value="<?php echo esc_url( $args['volver'] ); ?>">
	<input type="hidden" name="mmpadel_t" value="<?php echo esc_attr( $args['timestamp'] ); ?>">
	<?php wp_nonce_field( MMPadel_Form::NONCE, '_mmpadel_nonce', false ); ?>

	<p class="mmpadel-form__enviar">
		<button type="submit" class="button button--cta"><?php esc_html_e( 'Enviar solicitud', 'mmpadel-core' ); ?></button>
	</p>
	<p class="mmpadel-ayuda"><?php esc_html_e( 'Los campos con * son obligatorios.', 'mmpadel-core' ); ?></p>
</form>
