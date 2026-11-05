/**
 * MMPadel – barra de progreso del formulario «Encuentra tu grupo».
 *
 * Sin este script la barra es un índice de enlaces a cada sección. Con él:
 * - una sección se marca completa (is-completa) cuando todos sus campos
 *   obligatorios tienen respuesta válida, y la barra avanza;
 * - el paso de la sección que se está viendo lleva aria-current="step".
 * No bloquea nada: la validación de verdad la hace el servidor.
 */
( function () {
	'use strict';

	var form = document.getElementById( 'mmpadel-formulario' );
	var nav = form && form.querySelector( '.mmpadel-form__progreso' );
	if ( ! nav ) {
		return;
	}

	var secciones = Array.prototype.slice.call( form.querySelectorAll( '[data-mmpadel-seccion]' ) );
	var textoCompleta = nav.getAttribute( 'data-texto-completa' ) || '(completada)';

	function paso( clave ) {
		return nav.querySelector( '[data-mmpadel-paso="' + clave + '"]' );
	}

	function campoCompleto( campo ) {
		var marcables = campo.querySelectorAll( 'input[type="radio"], input[type="checkbox"]' );
		if ( marcables.length ) {
			return Array.prototype.some.call( marcables, function ( input ) {
				return input.checked;
			} );
		}
		var control = campo.querySelector( 'input, textarea' );
		return !! control && '' !== control.value.trim() && control.checkValidity();
	}

	function actualizar() {
		var hechas = 0;
		secciones.forEach( function ( seccion ) {
			var campos = seccion.querySelectorAll( '[data-mmpadel-obligatorio]' );
			var completa = Array.prototype.every.call( campos, campoCompleto );
			var item = paso( seccion.getAttribute( 'data-mmpadel-seccion' ) );
			seccion.classList.toggle( 'is-completa', completa );
			if ( item ) {
				item.classList.toggle( 'is-completa', completa );
				item.querySelector( '.mmpadel-form__paso-estado' ).textContent = completa ? ' ' + textoCompleta : '';
			}
			hechas += completa ? 1 : 0;
		} );
		nav.style.setProperty( '--progreso', secciones.length ? hechas / secciones.length : 0 );
	}

	form.addEventListener( 'input', actualizar );
	form.addEventListener( 'change', actualizar );
	actualizar();

	// Sección en pantalla: la que cruza la franja superior de la ventana.
	if ( 'IntersectionObserver' in window ) {
		var visibles = new Map();
		var observer = new IntersectionObserver( function ( entradas ) {
			entradas.forEach( function ( entrada ) {
				visibles.set( entrada.target, entrada.isIntersecting );
			} );
			var actual = secciones.filter( function ( s ) {
				return visibles.get( s );
			} )[ 0 ];
			secciones.forEach( function ( s ) {
				var item = paso( s.getAttribute( 'data-mmpadel-seccion' ) );
				var enlace = item && item.querySelector( 'a' );
				if ( ! enlace ) {
					return;
				}
				if ( s === actual ) {
					enlace.setAttribute( 'aria-current', 'step' );
				} else {
					enlace.removeAttribute( 'aria-current' );
				}
			} );
		}, { rootMargin: '-30% 0px -60% 0px' } );
		secciones.forEach( function ( s ) {
			observer.observe( s );
		} );
	}
}() );
