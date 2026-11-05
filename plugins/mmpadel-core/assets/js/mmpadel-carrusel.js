/**
 * MMPadel – carrusel de opiniones ([mmpadel_opiniones]).
 *
 * La fila de tarjetas ya se desliza sola con CSS (scroll-snap). Este script
 * solo añade:
 * - las flechas anterior / siguiente (avanzan una tarjeta y se desactivan
 *   en los extremos);
 * - la barra de progreso, que muestra qué parte de la fila se está viendo;
 * - las clases puede-atras / puede-adelante, que difuminan el borde por el
 *   que quedan tarjetas.
 * Si todas las tarjetas caben, los controles no salen.
 */
( function () {
	'use strict';

	var reducir = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	function iniciar( carrusel ) {
		var pista = carrusel.querySelector( '.mmpadel-opiniones' );
		var controles = carrusel.querySelector( '.mmpadel-carrusel__controles' );
		var barra = carrusel.querySelector( '.mmpadel-carrusel__progreso > span' );
		var flechas = carrusel.querySelectorAll( '[data-mmpadel-carrusel-ir]' );
		if ( ! pista || ! controles ) {
			return;
		}

		carrusel.classList.add( 'is-mejorado' );

		// Distancia de una tarjeta a la siguiente (ancho + hueco).
		function paso() {
			var tarjetas = pista.children;
			if ( tarjetas.length < 2 ) {
				return pista.clientWidth;
			}
			return tarjetas[ 1 ].offsetLeft - tarjetas[ 0 ].offsetLeft;
		}

		function actualizar() {
			var maximo = pista.scrollWidth - pista.clientWidth;
			var hayMas = maximo > 2;
			controles.hidden = ! hayMas;
			carrusel.classList.toggle( 'puede-atras', hayMas && pista.scrollLeft > 2 );
			carrusel.classList.toggle( 'puede-adelante', hayMas && pista.scrollLeft < maximo - 2 );

			flechas[ 0 ].disabled = ! carrusel.classList.contains( 'puede-atras' );
			flechas[ 1 ].disabled = ! carrusel.classList.contains( 'puede-adelante' );

			if ( barra && hayMas ) {
				var visible = pista.clientWidth / pista.scrollWidth;
				barra.style.width = ( visible * 100 ) + '%';
				barra.style.transform = 'translateX(' + ( ( pista.scrollLeft / maximo ) * ( 1 / visible - 1 ) * 100 ) + '%)';
			}
		}

		Array.prototype.forEach.call( flechas, function ( flecha ) {
			flecha.addEventListener( 'click', function () {
				pista.scrollBy( {
					left: Number( flecha.getAttribute( 'data-mmpadel-carrusel-ir' ) ) * paso(),
					behavior: reducir.matches ? 'auto' : 'smooth',
				} );
			} );
		} );

		var pendiente = false;
		pista.addEventListener( 'scroll', function () {
			if ( ! pendiente ) {
				pendiente = true;
				window.requestAnimationFrame( function () {
					pendiente = false;
					actualizar();
				} );
			}
		}, { passive: true } );

		if ( 'ResizeObserver' in window ) {
			new ResizeObserver( actualizar ).observe( pista );
		} else {
			window.addEventListener( 'resize', actualizar );
		}
		actualizar();
	}

	document.querySelectorAll( '[data-mmpadel-carrusel]' ).forEach( iniciar );
}() );
