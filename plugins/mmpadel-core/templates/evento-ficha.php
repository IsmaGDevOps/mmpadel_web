<?php
/**
 * Plantilla: ficha pública de un pool o evento (/eventos/{slug}/).
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array  $evento    Datos del evento (ver MMPadel_Shortcodes::evento_data()).
 *     @type string $contenido Contenido del editor, ya filtrado por WordPress.
 * }
 */

defined( 'ABSPATH' ) || exit;

$evento = $args['evento'];
$reglas = array_filter( array_map( 'trim', explode( "\n", $evento['reglas'] ) ) );
?>
<dl class="mmpadel-ficha">
	<?php if ( $evento['fecha_texto'] ) : ?>
		<div><dt><?php esc_html_e( 'Fecha', 'mmpadel-core' ); ?></dt><dd><?php echo esc_html( $evento['fecha_texto'] ); ?></dd></div>
	<?php endif; ?>
	<?php if ( $evento['hora'] ) : ?>
		<div><dt><?php esc_html_e( 'Hora', 'mmpadel-core' ); ?></dt><dd><?php echo esc_html( $evento['hora'] ); ?></dd></div>
	<?php endif; ?>
	<?php if ( $evento['nivel'] ) : ?>
		<div><dt><?php esc_html_e( 'Nivel', 'mmpadel-core' ); ?></dt><dd><?php echo esc_html( $evento['nivel'] ); ?></dd></div>
	<?php endif; ?>
</dl>

<?php if ( $evento['inscripcion'] && ! $evento['terminado'] ) : ?>
	<p>
		<a class="button button--cta" href="<?php echo esc_url( $evento['inscripcion'] ); ?>" target="_blank" rel="noopener" data-mmpadel-track="click_inscripcion_pool" data-mmpadel-evento="<?php echo esc_attr( $evento['titulo'] ); ?>"><?php esc_html_e( 'Inscribirme', 'mmpadel-core' ); ?></a>
	</p>
<?php endif; ?>

<?php
// Contenido del editor de bloques, ya procesado y filtrado por WordPress.
echo $args['contenido']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>

<?php if ( $reglas ) : ?>
	<h2><?php esc_html_e( 'Reglas', 'mmpadel-core' ); ?></h2>
	<ul class="mmpadel-reglas">
		<?php foreach ( $reglas as $regla ) : ?>
			<li><?php echo esc_html( $regla ); ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<?php if ( $evento['resultados'] ) : ?>
	<h2><?php esc_html_e( 'Resultados', 'mmpadel-core' ); ?></h2>
	<div class="mmpadel-resultados"><?php echo wp_kses_post( wpautop( esc_html( $evento['resultados'] ) ) ); ?></div>
<?php endif; ?>

<p class="mmpadel-volver">
	<a href="<?php echo esc_url( mmpadel_get_page_url( 'pools-y-eventos' ) ); ?>"><?php esc_html_e( '← Todos los pools y eventos', 'mmpadel-core' ); ?></a>
</p>
