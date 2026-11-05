/**
 * Pista 3D de la sección «Qué trabajamos en cada nivel» (grupo con la clase
 * mmpadel-pista, en la página Metodología).
 *
 * Dibuja con three.js una pista de pádel y repite una jugada típica del nivel
 * elegido: la bola con su estela, los botes, los cristales y cuatro jugadores
 * que se mueven hacia ella. Las tarjetas de nivel (columnas con la clase
 * mmpadel-nivel--iniciacion, --intermedio o --avanzado; si no la tienen, por
 * orden) eligen la jugada. Mientras nadie elige, los niveles se alternan.
 *
 * - three.js (assets/vendor/three) solo se descarga cuando la sección se
 *   acerca a la pantalla, y la animación se para cuando sale de ella.
 * - Con «reducir movimiento» se dibuja la jugada quieta: la trayectoria, la
 *   bola y los jugadores, sin animación.
 * - Sin WebGL, o si falla la carga, la sección se queda como texto.
 *
 * Medidas en metros, como una pista real: 10 × 20, red de 0,88 y cristales de
 * 3 m. El eje z va a lo largo (red en z = 0; el equipo A, en z > 0, es el más
 * cercano a la cámara) y el eje x a lo ancho.
 */

const THREE_VERSION = '0.170.0';

const seccion = document.querySelector( '.mmpadel-pista' );

/*
 * Jugadas. Cada punto es un contacto de la bola, en orden y en bucle:
 *   [x, y, z, golpe, arco, rapidez]
 * - golpe: nombre del golpe si lo da un jugador; null si es un bote en el
 *   suelo (y = 0) o en un cristal (|z| = 10 fondo, |x| = 5 lateral).
 * - arco: altura extra (m) de la parábola del tramo que llega a ese punto.
 * - rapidez: multiplica la velocidad del tramo (opcional).
 */
const JUGADAS = {
	iniciacion: {
		velocidad: 7,
		puntos: [
			[ 1.5, 0.9, 8.5, 'Derecha', 0.9 ],
			[ -1.0, 0, -6.5, null, 2.2 ],
			[ -1.4, 0.9, -8.6, 'Derecha', 0.9 ],
			[ -1.8, 0, 6.6, null, 2.2 ],
			[ -2.2, 0.9, 8.6, 'Revés', 0.9 ],
			[ 1.2, 0, -6.4, null, 2.2 ],
			[ 1.6, 0.9, -8.5, 'Revés', 0.9 ],
			[ 1.3, 0, 6.5, null, 2.2 ],
		],
	},
	intermedio: {
		velocidad: 9,
		puntos: [
			[ -1.5, 1.0, -8.5, 'Derecha', 0.5 ],
			[ 2.0, 0, 7.2, null, 1.8 ],
			[ 2.6, 1.5, 10, null, 0.4 ],
			[ 2.2, 1.0, 8.0, 'Salida de pared', 0.3 ],
			[ -1.0, 0, -6.0, null, 1.6 ],
			[ -1.3, 0.9, -8.4, 'Revés', 0.8 ],
			[ 0.8, 1.3, 3.0, 'Volea', 0.9 ],
			[ -2.5, 0, -5.5, null, 0.5 ],
			[ -2.8, 0.8, -8.0, 'Globo', 0.6 ],
			[ 1.0, 2.4, 5.0, 'Bandeja', 3.2 ],
			[ -1.5, 0, -7.5, null, 0.6 ],
		],
	},
	avanzado: {
		velocidad: 10,
		puntos: [
			[ -1.5, 0.8, 8.4, 'Globo', 0.8 ],
			[ 1.0, 2.8, -4.5, 'Remate', 4.5 ],
			[ -0.5, 0, 5.5, null, 0.15, 1.9 ],
			[ -0.2, 2.4, 10, null, 0.6 ],
			[ 0.2, 1.0, 7.0, 'Defensa tras el cristal', 0.5 ],
			[ 2.0, 2.2, -5.0, 'Víbora', 2.4 ],
			[ 3.6, 0, 7.5, null, 0.3, 1.5 ],
			[ 5.0, 1.2, 8.8, null, 0.3 ],
			[ 3.8, 0.9, 8.6, 'Salida de pared', 0.2 ],
			[ -2.0, 0, -6.8, null, 1.7 ],
			[ -2.4, 0.9, -8.6, 'Revés', 0.8 ],
			[ -1.2, 0, 6.6, null, 2.0 ],
		],
	},
};

const ORDEN_NIVELES = [ 'iniciacion', 'intermedio', 'avanzado' ];
const VUELTAS_POR_NIVEL = 2; // Vueltas antes de pasar solo al siguiente nivel.

if ( seccion && soportaWebGL() ) {
	montar( seccion );
}

/**
 * Comprueba si el navegador puede dibujar WebGL, sin dejar el contexto abierto.
 *
 * @return {boolean}
 */
function soportaWebGL() {
	try {
		const gl = document.createElement( 'canvas' ).getContext( 'webgl2' );
		gl?.getExtension( 'WEBGL_lose_context' )?.loseContext();
		return !! gl;
	} catch {
		return false;
	}
}

/**
 * Prepara el HTML (escena y botones de las tarjetas) y carga three.js cuando
 * la sección se acerca a la pantalla.
 *
 * @param {HTMLElement} contenedor Grupo con la clase mmpadel-pista.
 */
function montar( contenedor ) {
	const escena = document.createElement( 'div' );
	escena.className = 'mmpadel-pista__escena is-cargando';
	const canvas = document.createElement( 'canvas' );
	canvas.setAttribute( 'aria-hidden', 'true' );
	const golpe = document.createElement( 'p' );
	golpe.className = 'mmpadel-pista__golpe';
	golpe.setAttribute( 'aria-hidden', 'true' );
	golpe.append( Object.assign( document.createElement( 'span' ), { textContent: 'Golpe' } ), ' ', document.createElement( 'strong' ) );
	escena.append( canvas, golpe );

	const columnas = contenedor.querySelector( '.mmpadel-niveles' );
	if ( columnas ) {
		columnas.before( escena );
	} else {
		contenedor.append( escena );
	}
	contenedor.classList.add( 'tiene-escena' );

	// Tarjetas de nivel: por su clase o, si no la tienen, por orden.
	const tarjetas = new Map();
	contenedor.querySelectorAll( '.mmpadel-niveles > .wp-block-column' ).forEach( ( tarjeta, i ) => {
		const nivel = ORDEN_NIVELES.find( ( n ) => tarjeta.classList.contains( `mmpadel-nivel--${ n }` ) ) || ORDEN_NIVELES[ i ];
		if ( ! nivel || tarjetas.has( nivel ) ) {
			return;
		}
		const titulo = tarjeta.querySelector( 'h3' )?.textContent.trim() || nivel;
		const boton = document.createElement( 'button' );
		boton.type = 'button';
		boton.className = 'mmpadel-nivel__ver';
		boton.setAttribute( 'aria-pressed', 'false' );
		boton.append( 'Ver jugada', Object.assign( document.createElement( 'span' ), { className: 'screen-reader-text', textContent: ` de ${ titulo }` } ) );
		tarjeta.append( boton );
		tarjeta.classList.add( 'mmpadel-nivel' );
		tarjetas.set( nivel, { tarjeta, boton } );
	} );

	const deshacer = () => {
		escena.remove();
		contenedor.classList.remove( 'tiene-escena' );
		tarjetas.forEach( ( { tarjeta, boton } ) => {
			boton.remove();
			tarjeta.classList.remove( 'is-activa' );
		} );
	};

	const observador = new IntersectionObserver(
		( entradas ) => {
			if ( ! entradas.some( ( e ) => e.isIntersecting ) ) {
				return;
			}
			observador.disconnect();
			import( new URL( `../vendor/three/three.module.min.js?v=${ THREE_VERSION }`, import.meta.url ).href )
				.then( ( THREE ) => iniciar( THREE, { contenedor, escena, canvas, golpe, tarjetas } ) )
				.catch( ( error ) => {
					// eslint-disable-next-line no-console
					console.warn( 'MMPadel: no se pudo cargar la pista 3D.', error );
					deshacer();
				} );
		},
		{ rootMargin: '400px 0px' }
	);
	observador.observe( contenedor );
}

/**
 * Crea la escena y arranca la animación.
 *
 * @param {Object} THREE   Módulo de three.js.
 * @param {Object} partes  Elementos del HTML.
 */
function iniciar( THREE, { contenedor, escena, canvas, golpe, tarjetas } ) {
	const quieto = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const estilos = getComputedStyle( contenedor );
	const color = ( variable, defecto ) => new THREE.Color( estilos.getPropertyValue( variable ).trim() || defecto );

	const ACENTO = color( '--mmpadel-color-acento', '#d7f25c' );
	const PRIMARIO = color( '--mmpadel-color-primario', '#2a2763' );
	const OSCURO = color( '--mmpadel-color-primario-oscuro', '#1b1944' );
	const BLANCO = new THREE.Color( 0xffffff );

	const renderer = new THREE.WebGLRenderer( { canvas, antialias: true, alpha: true, powerPreference: 'low-power' } );
	renderer.setPixelRatio( Math.min( window.devicePixelRatio || 1, 2 ) );
	renderer.setClearColor( 0x000000, 0 );

	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera( 30, 4 / 3, 1, 120 );

	scene.add( new THREE.HemisphereLight( 0xffffff, OSCURO, 1.3 ) );
	const sol = new THREE.DirectionalLight( 0xffffff, 1.6 );
	sol.position.set( 6, 14, 9 );
	scene.add( sol );

	construirPista( THREE, scene, { ACENTO, PRIMARIO, OSCURO, BLANCO } );

	// Bola, su sombra y su estela.
	const bola = new THREE.Mesh(
		new THREE.SphereGeometry( 0.13, 24, 16 ),
		new THREE.MeshLambertMaterial( { color: ACENTO, emissive: ACENTO, emissiveIntensity: 0.35 } )
	);
	scene.add( bola );

	const sombraBola = sombra( THREE, 0.2, 0.4 );
	scene.add( sombraBola );

	const ESTELA = 16;
	const estela = new THREE.InstancedMesh(
		new THREE.SphereGeometry( 0.11, 12, 8 ),
		new THREE.MeshBasicMaterial( { color: 0xffffff, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false } ),
		ESTELA
	);
	const oculta = new THREE.Matrix4().makeScale( 0, 0, 0 );
	for ( let i = 0; i < ESTELA; i++ ) {
		estela.setColorAt( i, ACENTO.clone().multiplyScalar( 0.55 * ( 1 - i / ESTELA ) ) );
		estela.setMatrixAt( i, oculta );
	}
	estela.visible = ! quieto;
	scene.add( estela );
	const huellas = [];

	// Trayectoria completa de la jugada, discontinua.
	const trayectoria = new THREE.Line(
		new THREE.BufferGeometry(),
		new THREE.LineDashedMaterial( { color: BLANCO, transparent: true, opacity: 0.28, dashSize: 0.35, gapSize: 0.25 } )
	);
	scene.add( trayectoria );

	const jugadores = crearJugadores( THREE, scene, { ACENTO, PRIMARIO, BLANCO } );
	const efectos = crearEfectos( THREE, scene, ACENTO );

	// Jugadas listas para animar: tramos con origen, destino y duración.
	const jugadas = {};
	for ( const [ nivel, def ] of Object.entries( JUGADAS ) ) {
		jugadas[ nivel ] = prepararJugada( THREE, def );
	}

	const estado = {
		nivel: null,
		jugada: null,
		tramo: 0,
		tiempo: 0,
		vueltas: 0,
		elegido: false,
		reloj: 0,
		puntero: new THREE.Vector2(),
		punteroSuave: new THREE.Vector2(),
	};
	const posicion = new THREE.Vector3();

	/**
	 * Cambia la jugada en pista y marca su tarjeta.
	 *
	 * @param {string}  nivel   Clave de JUGADAS.
	 * @param {boolean} usuario Si lo ha elegido el usuario (para el cambio automático).
	 */
	function elegir( nivel, usuario ) {
		if ( usuario ) {
			estado.elegido = true;
		}
		if ( nivel === estado.nivel ) {
			return;
		}
		estado.nivel = nivel;
		estado.jugada = jugadas[ nivel ];
		estado.tramo = 0;
		estado.tiempo = 0;
		estado.vueltas = 0;
		huellas.length = 0;

		trayectoria.geometry.dispose();
		trayectoria.geometry = new THREE.BufferGeometry().setFromPoints( estado.jugada.muestras );
		trayectoria.computeLineDistances();

		tarjetas.forEach( ( { tarjeta, boton }, clave ) => {
			const activa = clave === nivel;
			tarjeta.classList.toggle( 'is-activa', activa );
			boton.setAttribute( 'aria-pressed', String( activa ) );
		} );

		golpe.hidden = quieto;
		golpe.querySelector( 'strong' ).textContent = '';
		jugadores.reiniciar( estado.jugada );

		if ( quieto ) {
			// Imagen fija: la bola a mitad del primer tramo y los jugadores ya colocados.
			posicionEnTramo( estado.jugada.tramos[ 0 ], 0.55, posicion );
			jugadores.actualizar( estado.jugada, 0, posicion, 1, true );
			colocarBola( posicion );
			dibujar();
		}
	}

	tarjetas.forEach( ( { tarjeta }, nivel ) => {
		tarjeta.addEventListener( 'click', ( evento ) => {
			if ( ! evento.target.closest( 'a' ) ) {
				elegir( nivel, true );
			}
		} );
	} );

	function colocarBola( p ) {
		bola.position.copy( p );
		sombraBola.position.set( p.x, 0.012, p.z );
		const altura = Math.min( p.y / 4, 1 );
		sombraBola.scale.setScalar( 1 - altura * 0.5 );
		sombraBola.material.opacity = 0.4 * ( 1 - altura * 0.7 );
	}

	/**
	 * Contacto de la bola al final de un tramo: golpe o bote.
	 *
	 * @param {Object} tramo Tramo que acaba de terminar.
	 */
	function contacto( tramo ) {
		if ( tramo.golpe ) {
			efectos.destello( tramo.destino );
			jugadores.golpear( tramo.destino );
			const nombre = golpe.querySelector( 'strong' );
			nombre.textContent = tramo.golpe;
			golpe.classList.remove( 'is-nuevo' );
			void golpe.offsetWidth; // Reinicia la animación del nombre.
			golpe.classList.add( 'is-nuevo' );
		} else {
			efectos.bote( tramo.destino, tramo.tipo );
		}
	}

	function actualizar( dt ) {
		const jugada = estado.jugada;
		estado.reloj += dt;
		estado.tiempo += dt;

		while ( estado.tiempo >= jugada.tramos[ estado.tramo ].duracion ) {
			estado.tiempo -= jugada.tramos[ estado.tramo ].duracion;
			contacto( jugada.tramos[ estado.tramo ] );
			estado.tramo = ( estado.tramo + 1 ) % jugada.tramos.length;
			if ( 0 === estado.tramo ) {
				estado.vueltas++;
				if ( ! estado.elegido && estado.vueltas >= VUELTAS_POR_NIVEL ) {
					const siguiente = ORDEN_NIVELES[ ( ORDEN_NIVELES.indexOf( estado.nivel ) + 1 ) % ORDEN_NIVELES.length ];
					elegir( siguiente, false );
					return;
				}
			}
		}

		const tramo = jugada.tramos[ estado.tramo ];
		posicionEnTramo( tramo, estado.tiempo / tramo.duracion, posicion );
		colocarBola( posicion );

		// Estela: últimas posiciones, cada vez más pequeñas.
		huellas.unshift( posicion.clone() );
		huellas.length = Math.min( huellas.length, ESTELA );
		const matriz = new THREE.Matrix4();
		for ( let i = 0; i < ESTELA; i++ ) {
			const h = huellas[ i ] || posicion;
			const escala = huellas[ i ] ? 1 - i / ESTELA : 0;
			matriz.makeScale( escala, escala, escala ).setPosition( h );
			estela.setMatrixAt( i, matriz );
		}
		estela.instanceMatrix.needsUpdate = true;

		jugadores.actualizar( jugada, estado.tramo, posicion, dt, false );
		efectos.actualizar( dt );

		// Cámara: balanceo lento y un poco de paralaje con el ratón.
		estado.punteroSuave.lerp( estado.puntero, 1 - Math.exp( -dt * 3 ) );
		orientarCamara( 0.55 + 0.16 * Math.sin( estado.reloj * 0.11 ) + estado.punteroSuave.x * 0.12, 0.74 - estado.punteroSuave.y * 0.06 );
	}

	let distancia = 40;

	function orientarCamara( azimut, elevacion ) {
		const objetivo = new THREE.Vector3( 0, 0.4, 0 );
		camera.position.set(
			Math.cos( elevacion ) * Math.sin( azimut ),
			Math.sin( elevacion ),
			Math.cos( elevacion ) * Math.cos( azimut )
		).multiplyScalar( distancia ).add( objetivo );
		camera.lookAt( objetivo );
	}

	function dibujar() {
		renderer.render( scene, camera );
	}

	function redimensionar() {
		const ancho = escena.clientWidth;
		const alto = escena.clientHeight;
		if ( ! ancho || ! alto ) {
			return;
		}
		renderer.setSize( ancho, alto, false );
		camera.aspect = ancho / alto;
		// Distancia para que quepa la pista entera (radio ~11 m) en el lado más estrecho.
		const vertical = THREE.MathUtils.degToRad( camera.fov );
		const horizontal = 2 * Math.atan( Math.tan( vertical / 2 ) * camera.aspect );
		distancia = 10.5 / Math.sin( Math.min( vertical, horizontal ) / 2 );
		camera.updateProjectionMatrix();
		if ( quieto ) {
			orientarCamara( 0.55, 0.74 );
			dibujar();
		}
	}

	// Bucle: solo mientras la sección se ve y la pestaña está activa.
	let visible = false;
	let anterior = 0;
	let pedido = 0;

	function fotograma( ahora ) {
		pedido = 0;
		const dt = Math.min( ( ahora - anterior ) / 1000, 0.05 );
		anterior = ahora;
		actualizar( dt );
		dibujar();
		programar();
	}

	function programar() {
		if ( ! quieto && visible && ! document.hidden && ! pedido ) {
			pedido = requestAnimationFrame( ( t ) => {
				if ( ! anterior ) {
					anterior = t;
				}
				fotograma( t );
			} );
		}
	}

	new IntersectionObserver( ( entradas ) => {
		visible = entradas.some( ( e ) => e.isIntersecting );
		anterior = 0;
		programar();
	} ).observe( escena );

	document.addEventListener( 'visibilitychange', () => {
		anterior = 0;
		programar();
	} );

	contenedor.addEventListener( 'pointermove', ( evento ) => {
		const caja = escena.getBoundingClientRect();
		estado.puntero.set(
			THREE.MathUtils.clamp( ( ( evento.clientX - caja.left ) / caja.width ) * 2 - 1, -1, 1 ),
			THREE.MathUtils.clamp( ( ( evento.clientY - caja.top ) / caja.height ) * 2 - 1, -1, 1 )
		);
	} );
	contenedor.addEventListener( 'pointerleave', () => estado.puntero.set( 0, 0 ) );

	new ResizeObserver( redimensionar ).observe( escena );
	redimensionar();
	orientarCamara( 0.55, 0.74 );

	elegir( ORDEN_NIVELES.find( ( n ) => tarjetas.has( n ) ) || ORDEN_NIVELES[ 0 ], false );
	dibujar();
	escena.classList.remove( 'is-cargando' );
}

/**
 * Convierte la definición de una jugada en tramos con duración, y muestrea
 * su trayectoria para dibujarla.
 *
 * @param {Object} THREE Módulo de three.js.
 * @param {Object} def   Entrada de JUGADAS.
 * @return {{tramos: Object[], muestras: Object[]}}
 */
function prepararJugada( THREE, def ) {
	const puntos = def.puntos.map( ( [ x, y, z, golpe, arco, rapidez = 1 ] ) => ( {
		p: new THREE.Vector3( x, y, z ),
		golpe,
		arco,
		rapidez,
	} ) );

	const tramos = puntos.map( ( destino, i ) => {
		const origen = puntos[ ( i - 1 + puntos.length ) % puntos.length ];
		const recorrido = Math.hypot( destino.p.x - origen.p.x, destino.p.z - origen.p.z ) + destino.arco * 1.5;
		let tipo = 'golpe';
		if ( ! destino.golpe ) {
			if ( Math.abs( destino.p.z ) >= 10 ) {
				tipo = 'fondo';
			} else if ( Math.abs( destino.p.x ) >= 5 ) {
				tipo = 'lateral';
			} else {
				tipo = 'suelo';
			}
		}
		return {
			origen: origen.p,
			destino: destino.p,
			arco: destino.arco,
			golpe: destino.golpe,
			tipo,
			duracion: Math.max( 0.3, recorrido / ( def.velocidad * destino.rapidez ) ),
		};
	} );

	const muestras = [];
	tramos.forEach( ( tramo ) => {
		for ( let i = 0; i < 24; i++ ) {
			muestras.push( posicionEnTramo( tramo, i / 24, new THREE.Vector3() ) );
		}
	} );
	muestras.push( muestras[ 0 ].clone() );

	return { tramos, muestras };
}

/**
 * Posición de la bola en un tramo: avance recto en planta y parábola en altura.
 *
 * @param {Object} tramo Tramo de la jugada.
 * @param {number} t     Progreso, de 0 a 1.
 * @param {Object} out   Vector3 donde escribir.
 * @return {Object} out
 */
function posicionEnTramo( tramo, t, out ) {
	out.lerpVectors( tramo.origen, tramo.destino, t );
	out.y = tramo.origen.y + ( tramo.destino.y - tramo.origen.y ) * t + 4 * tramo.arco * t * ( 1 - t );
	return out;
}

/**
 * Mancha oscura en el suelo (sombra falsa, más barata que las sombras reales).
 *
 * @param {Object} THREE    Módulo de three.js.
 * @param {number} radio    Radio en metros.
 * @param {number} opacidad Opacidad.
 * @return {Object} Mesh
 */
function sombra( THREE, radio, opacidad ) {
	const mesh = new THREE.Mesh(
		new THREE.CircleGeometry( radio, 24 ),
		new THREE.MeshBasicMaterial( { color: 0x000000, transparent: true, opacity: opacidad, depthWrite: false } )
	);
	mesh.rotation.x = -Math.PI / 2;
	return mesh;
}

/**
 * Suelo, líneas, red, cristales y malla.
 *
 * @param {Object} THREE   Módulo de three.js.
 * @param {Object} scene   Escena.
 * @param {Object} colores Colores de marca.
 */
function construirPista( THREE, scene, { ACENTO, PRIMARIO, OSCURO, BLANCO } ) {
	const plano = ( ancho, fondo, material, x = 0, y = 0, z = 0 ) => {
		const mesh = new THREE.Mesh( new THREE.PlaneGeometry( ancho, fondo ), material );
		mesh.rotation.x = -Math.PI / 2;
		mesh.position.set( x, y, z );
		scene.add( mesh );
		return mesh;
	};

	// Suelo: un borde algo más oscuro alrededor y la pista en el azul de marca, más luminoso.
	plano( 12.5, 23, new THREE.MeshLambertMaterial( { color: OSCURO.clone().lerp( PRIMARIO, 0.55 ) } ), 0, -0.02, 0 );
	plano( 10, 20, new THREE.MeshLambertMaterial( { color: PRIMARIO.clone().lerp( new THREE.Color( 0x4f4ad8 ), 0.6 ) } ) );

	// Líneas de saque (a 6,95 m de la red) y central.
	const blanco = new THREE.MeshBasicMaterial( { color: BLANCO, transparent: true, opacity: 0.85 } );
	plano( 10, 0.05, blanco, 0, 0.004, 6.95 );
	plano( 10, 0.05, blanco, 0, 0.004, -6.95 );
	plano( 0.05, 13.9, blanco, 0, 0.004, 0 );

	// Red con su cinta y postes.
	const red = new THREE.Mesh(
		new THREE.PlaneGeometry( 10, 0.88 ),
		new THREE.MeshBasicMaterial( { color: BLANCO, transparent: true, opacity: 0.16, side: THREE.DoubleSide, depthWrite: false } )
	);
	red.position.y = 0.44;
	scene.add( red );
	const redMalla = cuadricula( THREE, 10, 0.88, 0.12, BLANCO, 0.14 );
	redMalla.position.set( -5, 0, 0 );
	scene.add( redMalla );
	const cinta = new THREE.Mesh( new THREE.BoxGeometry( 10, 0.06, 0.03 ), new THREE.MeshLambertMaterial( { color: BLANCO } ) );
	cinta.position.y = 0.88;
	scene.add( cinta );
	for ( const x of [ -5.05, 5.05 ] ) {
		const poste = new THREE.Mesh( new THREE.CylinderGeometry( 0.05, 0.05, 0.92, 12 ), new THREE.MeshLambertMaterial( { color: ACENTO } ) );
		poste.position.set( x, 0.46, 0 );
		scene.add( poste );
	}

	// Cristales: fondo entero (10 × 3) y 4 m de cada lateral, en paños de 2 m.
	const cristal = new THREE.MeshBasicMaterial( { color: 0xc9ccff, transparent: true, opacity: 0.07, side: THREE.DoubleSide, depthWrite: false } );
	const marco = new THREE.LineBasicMaterial( { color: BLANCO, transparent: true, opacity: 0.4 } );
	const panel = ( ancho, alto, x, y, z, giro ) => {
		const geometria = new THREE.PlaneGeometry( ancho, alto );
		const mesh = new THREE.Mesh( geometria, cristal );
		const borde = new THREE.LineSegments( new THREE.EdgesGeometry( geometria ), marco );
		for ( const objeto of [ mesh, borde ] ) {
			objeto.position.set( x, y, z );
			objeto.rotation.y = giro;
			scene.add( objeto );
		}
	};
	for ( const lado of [ -1, 1 ] ) {
		for ( let i = 0; i < 5; i++ ) {
			panel( 2, 3, -4 + i * 2, 1.5, lado * 10, 0 );
		}
		for ( const x of [ -5, 5 ] ) {
			panel( 2, 3, x, 1.5, lado * 9, Math.PI / 2 );
			panel( 2, 3, x, 1.5, lado * 7, Math.PI / 2 );
		}
	}

	// Malla metálica: sobre el fondo (de 3 a 4 m) y en el centro de los laterales.
	for ( const lado of [ -1, 1 ] ) {
		const fondo = cuadricula( THREE, 10, 1, 0.25, BLANCO, 0.12 );
		fondo.position.set( -5, 3, lado * 10 );
		scene.add( fondo );

		const lateral = cuadricula( THREE, 12, 3, 0.25, BLANCO, 0.08 );
		lateral.rotation.y = Math.PI / 2;
		lateral.position.set( lado * 5, 0, 6 );
		scene.add( lateral );
	}
}

/**
 * Cuadrícula de líneas en el plano XY, desde el origen hacia +x y +y.
 *
 * @param {Object} THREE    Módulo de three.js.
 * @param {number} ancho    Ancho en metros.
 * @param {number} alto     Alto en metros.
 * @param {number} paso     Separación entre líneas.
 * @param {Object} color    Color.
 * @param {number} opacidad Opacidad.
 * @return {Object} LineSegments
 */
function cuadricula( THREE, ancho, alto, paso, color, opacidad ) {
	const vertices = [];
	for ( let x = 0; x <= ancho + 1e-6; x += paso ) {
		vertices.push( x, 0, 0, x, alto, 0 );
	}
	for ( let y = 0; y <= alto + 1e-6; y += paso ) {
		vertices.push( 0, y, 0, ancho, y, 0 );
	}
	const geometria = new THREE.BufferGeometry();
	geometria.setAttribute( 'position', new THREE.Float32BufferAttribute( vertices, 3 ) );
	return new THREE.LineSegments( geometria, new THREE.LineBasicMaterial( { color, transparent: true, opacity: opacidad } ) );
}

/**
 * Cuatro jugadores sencillos (cuerpo, cabeza y pala) que van hacia la bola.
 *
 * Quien va a dar el siguiente golpe corre a por ella; su compañero se mueve en
 * paralelo, a la misma altura de pista; la otra pareja espera a la altura de
 * su último golpe, siguiendo la bola de lado.
 *
 * @param {Object} THREE   Módulo de three.js.
 * @param {Object} scene   Escena.
 * @param {Object} colores Colores de marca.
 * @return {Object} API: reiniciar, actualizar, golpear.
 */
function crearJugadores( THREE, scene, { ACENTO, PRIMARIO, BLANCO } ) {
	const cuerpoGeo = new THREE.CapsuleGeometry( 0.26, 0.85, 4, 12 );
	const cabezaGeo = new THREE.SphereGeometry( 0.17, 16, 12 );
	const palaGeo = new THREE.CylinderGeometry( 0.2, 0.2, 0.035, 20 );
	const mangoGeo = new THREE.CylinderGeometry( 0.025, 0.025, 0.22, 8 );
	const materiales = {
		A: new THREE.MeshLambertMaterial( { color: BLANCO } ),
		B: new THREE.MeshLambertMaterial( { color: ACENTO.clone().lerp( BLANCO, 0.15 ) } ),
	};
	const materialPala = new THREE.MeshLambertMaterial( { color: ACENTO } );

	const lista = [];
	for ( const equipo of [ 'A', 'B' ] ) {
		for ( const lado of [ -1, 1 ] ) {
			const grupo = new THREE.Group();
			const cuerpo = new THREE.Mesh( cuerpoGeo, materiales[ equipo ] );
			cuerpo.position.y = 0.72;
			const cabeza = new THREE.Mesh( cabezaGeo, materiales[ equipo ] );
			cabeza.position.y = 1.5;

			// La pala gira desde el hombro derecho (+x mirando hacia -z).
			const hombro = new THREE.Group();
			hombro.position.set( 0.3, 1.12, 0 );
			const pala = new THREE.Group();
			// Pala amarilla para el equipo blanco y blanca para el amarillo.
			const colorPala = 'A' === equipo ? materialPala : materiales.A;
			const cara = new THREE.Mesh( palaGeo, colorPala );
			cara.rotation.x = Math.PI / 2;
			cara.position.y = 0.36;
			const mango = new THREE.Mesh( mangoGeo, colorPala );
			mango.position.y = 0.11;
			pala.add( cara, mango );
			pala.position.set( 0.12, -0.15, -0.12 );
			pala.rotation.z = -0.5;
			hombro.add( pala );

			const sombraJugador = sombra( THREE, 0.38, 0.28 );
			sombraJugador.position.y = 0.01;

			grupo.add( cuerpo, cabeza, hombro );
			// El equipo B mira hacia +z: girado media vuelta, su derecha queda en -x.
			grupo.rotation.y = 'B' === equipo ? Math.PI : 0;
			scene.add( grupo, sombraJugador );

			lista.push( {
				equipo,
				lado,
				grupo,
				hombro,
				sombra: sombraJugador,
				objetivo: new THREE.Vector3(),
				swing: 0,
			} );
		}
	}

	const profundidad = { A: 8.3, B: -8.3 };
	const signo = { A: 1, B: -1 };

	const ladoDe = ( x ) => ( x >= 0 ? 1 : -1 );
	const equipoDe = ( z ) => ( z > 0 ? 'A' : 'B' );

	/**
	 * Siguiente golpe a partir de un tramo (incluido).
	 *
	 * @param {Object} jugada Jugada preparada.
	 * @param {number} desde  Índice de tramo.
	 * @return {Object} Tramo con golpe.
	 */
	function siguienteGolpe( jugada, desde ) {
		for ( let i = 0; i < jugada.tramos.length; i++ ) {
			const tramo = jugada.tramos[ ( desde + i ) % jugada.tramos.length ];
			if ( tramo.golpe ) {
				return tramo;
			}
		}
		return jugada.tramos[ 0 ];
	}

	/**
	 * Posición donde se coloca quien golpea: la bola a su derecha y un poco por delante.
	 *
	 * @param {Object} punto  Punto de golpe.
	 * @param {string} equipo A o B.
	 * @param {Object} out    Vector3 donde escribir.
	 * @return {Object} out
	 */
	function sitioDeGolpe( punto, equipo, out ) {
		return out.set( punto.x - 0.45 * signo[ equipo ], 0, punto.z + 0.35 * signo[ equipo ] );
	}

	return {
		reiniciar( jugada ) {
			profundidad.A = 8.3;
			profundidad.B = -8.3;
			for ( const j of lista ) {
				j.grupo.position.set( j.lado * 2.6 * signo[ j.equipo ], 0, profundidad[ j.equipo ] );
				j.swing = 0;
			}
			// Cada pareja empieza a la altura de su primer golpe de la jugada.
			for ( const tramo of jugada.tramos ) {
				if ( tramo.golpe ) {
					const equipo = equipoDe( tramo.destino.z );
					if ( Math.abs( profundidad[ equipo ] ) === 8.3 ) {
						profundidad[ equipo ] = tramo.destino.z + 0.35 * signo[ equipo ];
					}
				}
			}
		},

		actualizar( jugada, indiceTramo, bola, dt, inmediato ) {
			const proximo = siguienteGolpe( jugada, indiceTramo );
			const equipoGolpe = equipoDe( proximo.destino.z );
			const ladoGolpe = ladoDe( proximo.destino.x );
			const sitio = sitioDeGolpe( proximo.destino, equipoGolpe, new THREE.Vector3() );

			for ( const j of lista ) {
				const ladoReal = j.lado * signo[ j.equipo ]; // lado en x del mundo
				if ( j.equipo === equipoGolpe ) {
					if ( ladoReal === ladoGolpe ) {
						j.objetivo.copy( sitio );
					} else {
						j.objetivo.set( -ladoGolpe * 2.6 + sitio.x * 0.25, 0, sitio.z );
					}
				} else {
					j.objetivo.set( ladoReal * 2.6 + bola.x * 0.3, 0, profundidad[ j.equipo ] );
				}

				const rapidez = j.equipo === equipoGolpe && ladoReal === ladoGolpe ? 5 : 3;
				if ( inmediato ) {
					j.grupo.position.copy( j.objetivo );
				} else {
					j.grupo.position.lerp( j.objetivo, 1 - Math.exp( -dt * rapidez ) );
				}
				j.sombra.position.set( j.grupo.position.x, 0.01, j.grupo.position.z );

				// Golpe: la pala barre hacia delante y vuelve.
				j.swing *= Math.exp( -dt * 5 );
				j.hombro.rotation.x = -j.swing * 1.6;
				j.hombro.rotation.y = j.swing * 0.6;
			}
		},

		golpear( punto ) {
			const equipo = equipoDe( punto.z );
			const lado = ladoDe( punto.x );
			for ( const j of lista ) {
				if ( j.equipo === equipo && j.lado * signo[ j.equipo ] === lado ) {
					j.swing = 1;
				}
			}
			profundidad[ equipo ] = punto.z + 0.35 * signo[ equipo ];
		},
	};
}

/**
 * Destellos de golpe y anillos de bote, reutilizados de una pequeña reserva.
 *
 * @param {Object} THREE  Módulo de three.js.
 * @param {Object} scene  Escena.
 * @param {Object} acento Color de acento.
 * @return {Object} API: destello, bote, actualizar.
 */
function crearEfectos( THREE, scene, acento ) {
	const anilloGeo = new THREE.RingGeometry( 0.16, 0.24, 32 );
	const destelloGeo = new THREE.SphereGeometry( 0.14, 16, 12 );
	const reserva = [];

	for ( let i = 0; i < 8; i++ ) {
		const material = new THREE.MeshBasicMaterial( { color: acento, transparent: true, opacity: 0, side: THREE.DoubleSide, depthWrite: false } );
		const anillo = new THREE.Mesh( anilloGeo, material );
		const destello = new THREE.Mesh( destelloGeo, material );
		anillo.visible = destello.visible = false;
		scene.add( anillo, destello );
		reserva.push( { anillo, destello, material, vida: 0, duracion: 1, actual: null, crecer: 1 } );
	}
	let siguiente = 0;

	function lanzar( tipo, punto, duracion, crecer ) {
		const efecto = reserva[ siguiente ];
		siguiente = ( siguiente + 1 ) % reserva.length;
		efecto.anillo.visible = efecto.destello.visible = false;
		efecto.actual = 'golpe' === tipo ? efecto.destello : efecto.anillo;
		efecto.actual.visible = true;
		efecto.actual.position.copy( punto );
		efecto.actual.rotation.set( 0, 0, 0 );
		// Los anillos de cristal, un par de centímetros por dentro para no pisar el cristal.
		if ( 'suelo' === tipo ) {
			efecto.actual.rotation.x = -Math.PI / 2;
			efecto.actual.position.y = 0.015;
		} else if ( 'fondo' === tipo ) {
			efecto.actual.position.z -= Math.sign( punto.z ) * 0.03;
		} else if ( 'lateral' === tipo ) {
			efecto.actual.rotation.y = Math.PI / 2;
			efecto.actual.position.x -= Math.sign( punto.x ) * 0.03;
		}
		efecto.vida = 0;
		efecto.duracion = duracion;
		efecto.crecer = crecer;
	}

	return {
		destello( punto ) {
			lanzar( 'golpe', punto, 0.35, 3 );
		},
		bote( punto, tipo ) {
			lanzar( tipo, punto, 0.7, 4 );
		},
		actualizar( dt ) {
			for ( const efecto of reserva ) {
				if ( ! efecto.actual ) {
					continue;
				}
				efecto.vida += dt;
				const t = Math.min( efecto.vida / efecto.duracion, 1 );
				efecto.actual.scale.setScalar( 1 + ( efecto.crecer - 1 ) * ( 1 - ( 1 - t ) ** 3 ) );
				efecto.material.opacity = 0.75 * ( 1 - t );
				if ( t >= 1 ) {
					efecto.actual.visible = false;
					efecto.actual = null;
				}
			}
		},
	};
}
