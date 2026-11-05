/**
 * Rally de cinco estrellas en el panel del marcador de Google (grupo con la
 * clase mmpadel-opiniones-nota, en la página Opiniones).
 *
 * Sobre el marcador flotan cinco estrellas 3D apagadas. Una bola de pádel
 * entra por la izquierda y va botando de estrella en estrella; cada una que
 * toca gira y se enciende en el amarillo de marca. Con las cinco encendidas
 * saltan unas chispas y la bola vuelve por encima al principio, apagándolas
 * a su paso, para empezar de nuevo.
 * Con el ratón, la escena se inclina un poco hacia el puntero.
 *
 * - three.js (assets/vendor/three) solo se descarga cuando el panel se
 *   acerca a la pantalla, y la animación se para cuando sale de ella.
 * - Con «reducir movimiento» se dibujan las cinco estrellas encendidas y la
 *   bola quieta, sin animación.
 * - Sin WebGL, o si falla la carga, el panel se queda como estaba.
 *
 * Las medidas se calculan en píxeles del panel (la fila de estrellas ocupa
 * el hueco que deja el padding superior) y se pasan a unidades de la escena.
 */

const THREE_VERSION = '0.170.0';
const NUM_ESTRELLAS = 5;

const panel = document.querySelector( '.mmpadel-opiniones-nota' );

if ( panel && soportaWebGL() ) {
	montar( panel );
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
 * Añade el lienzo al panel y carga three.js cuando se acerca a la pantalla.
 *
 * @param {HTMLElement} contenedor Grupo con la clase mmpadel-opiniones-nota.
 */
function montar( contenedor ) {
	const escena = document.createElement( 'div' );
	escena.className = 'mmpadel-estrellas is-cargando';
	escena.setAttribute( 'aria-hidden', 'true' );
	const canvas = document.createElement( 'canvas' );
	escena.append( canvas );
	contenedor.prepend( escena );
	contenedor.classList.add( 'tiene-estrellas' );

	const observador = new IntersectionObserver(
		( entradas ) => {
			if ( ! entradas.some( ( e ) => e.isIntersecting ) ) {
				return;
			}
			observador.disconnect();
			import( new URL( `../vendor/three/three.module.min.js?v=${ THREE_VERSION }`, import.meta.url ).href )
				.then( ( THREE ) => iniciar( THREE, { contenedor, escena, canvas } ) )
				.catch( ( error ) => {
					// eslint-disable-next-line no-console
					console.warn( 'MMPadel: no se pudo cargar la animación de estrellas.', error );
					escena.remove();
					contenedor.classList.remove( 'tiene-estrellas' );
				} );
		},
		{ rootMargin: '300px 0px' }
	);
	observador.observe( contenedor );
}

/**
 * Suavizados.
 *
 * @param {number} t De 0 a 1.
 * @return {number}
 */
const suave = ( t ) => t * t * ( 3 - 2 * t );
const rebote = ( t ) => {
	const c = 1.70158 * 1.4;
	return 1 + ( c + 1 ) * Math.pow( t - 1, 3 ) + c * Math.pow( t - 1, 2 );
};

/**
 * Crea la escena y arranca la animación.
 *
 * @param {Object} THREE  Módulo de three.js.
 * @param {Object} partes Elementos del HTML.
 */
function iniciar( THREE, { contenedor, escena, canvas } ) {
	const quieto = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	const estilos = getComputedStyle( contenedor );
	const color = ( variable, defecto ) => new THREE.Color( estilos.getPropertyValue( variable ).trim() || defecto );

	const ACENTO = color( '--mmpadel-color-acento', '#d7f25c' );
	const OSCURO = color( '--mmpadel-color-primario-oscuro', '#1b1944' );
	const APAGADA = new THREE.Color( '#5a56c4' );

	const renderer = new THREE.WebGLRenderer( { canvas, antialias: true, alpha: true, powerPreference: 'low-power' } );
	renderer.setPixelRatio( Math.min( window.devicePixelRatio || 1, 2 ) );
	renderer.setClearColor( 0x000000, 0 );

	const scene = new THREE.Scene();
	const camera = new THREE.PerspectiveCamera( 30, 1, 1, 60 );
	camera.position.set( 0, 0, 14 );
	camera.lookAt( 0, 0, 0 );

	scene.add( new THREE.HemisphereLight( 0xffffff, OSCURO, 1.5 ) );
	const sol = new THREE.DirectionalLight( 0xffffff, 2 );
	sol.position.set( 3, 4, 6 );
	scene.add( sol );

	// Todo cuelga de un grupo que se inclina hacia el puntero.
	const mundo = new THREE.Group();
	scene.add( mundo );

	/* Estrellas ------------------------------------------------------ */

	const forma = new THREE.Shape();
	for ( let i = 0; i < 10; i++ ) {
		const radio = i % 2 ? 0.45 : 1;
		const angulo = Math.PI / 2 + ( i * Math.PI ) / 5;
		const x = Math.cos( angulo ) * radio;
		const y = Math.sin( angulo ) * radio;
		if ( i ) {
			forma.lineTo( x, y );
		} else {
			forma.moveTo( x, y );
		}
	}
	forma.closePath();
	const geoEstrella = new THREE.ExtrudeGeometry( forma, {
		depth: 0.26,
		bevelEnabled: true,
		bevelThickness: 0.1,
		bevelSize: 0.08,
		bevelSegments: 3,
	} );
	geoEstrella.center();

	const estrellas = Array.from( { length: NUM_ESTRELLAS }, ( _, i ) => {
		const material = new THREE.MeshStandardMaterial( { color: APAGADA.clone(), roughness: 0.4, metalness: 0.15, emissive: ACENTO.clone(), emissiveIntensity: 0 } );
		const malla = new THREE.Mesh( geoEstrella, material );
		mundo.add( malla );
		return {
			malla,
			material,
			base: new THREE.Vector3(),
			luz: 0, // 0 apagada, 1 encendida.
			objetivo: 0,
			giro: -1, // Segundos desde el último toque (-1: sin giro).
			fase: i * 0.8,
		};
	} );

	/* Bola ----------------------------------------------------------- */

	const bola = new THREE.Group();
	bola.add( new THREE.Mesh( new THREE.SphereGeometry( 1, 32, 20 ), new THREE.MeshStandardMaterial( { color: ACENTO, roughness: 0.75 } ) ) );

	// Costura de la bola: una curva cerrada sobre la esfera.
	const costura = new THREE.Curve();
	costura.getPoint = ( t, destino = new THREE.Vector3() ) => {
		const a = 0.75;
		const b = 0.25;
		const u = t * Math.PI * 2;
		return destino.set( a * Math.cos( u ) + b * Math.cos( 3 * u ), a * Math.sin( u ) - b * Math.sin( 3 * u ), 2 * Math.sqrt( a * b ) * Math.sin( 2 * u ) ).multiplyScalar( 1.005 );
	};
	bola.add( new THREE.Mesh( new THREE.TubeGeometry( costura, 120, 0.05, 6, true ), new THREE.MeshStandardMaterial( { color: 0xffffff, roughness: 0.6 } ) ) );
	mundo.add( bola );

	/* Chispas -------------------------------------------------------- */

	const NUM_CHISPAS = 90;
	const posiciones = new Float32Array( NUM_CHISPAS * 3 );
	const velocidades = Array.from( { length: NUM_CHISPAS }, () => new THREE.Vector3() );
	const geoChispas = new THREE.BufferGeometry();
	geoChispas.setAttribute( 'position', new THREE.BufferAttribute( posiciones, 3 ) );
	// Chispas redondas: un círculo difuminado dibujado en un lienzo pequeño.
	const lienzo = Object.assign( document.createElement( 'canvas' ), { width: 32, height: 32 } );
	const ctx = lienzo.getContext( '2d' );
	const degradado = ctx.createRadialGradient( 16, 16, 0, 16, 16, 16 );
	degradado.addColorStop( 0, 'rgba(255,255,255,1)' );
	degradado.addColorStop( 0.45, 'rgba(255,255,255,0.9)' );
	degradado.addColorStop( 1, 'rgba(255,255,255,0)' );
	ctx.fillStyle = degradado;
	ctx.fillRect( 0, 0, 32, 32 );
	const matChispas = new THREE.PointsMaterial( {
		color: ACENTO,
		size: 7 * Math.min( window.devicePixelRatio || 1, 2 ),
		sizeAttenuation: false,
		map: new THREE.CanvasTexture( lienzo ),
		transparent: true,
		opacity: 0,
		depthWrite: false,
	} );
	const chispas = new THREE.Points( geoChispas, matChispas );
	mundo.add( chispas );
	let vidaChispas = 0;

	function lanzarChispas() {
		for ( let i = 0; i < NUM_CHISPAS; i++ ) {
			const origen = estrellas[ i % NUM_ESTRELLAS ].malla.position;
			posiciones.set( [ origen.x, origen.y, origen.z ], i * 3 );
			const angulo = Math.random() * Math.PI * 2;
			const fuerza = medidas.paso * ( 1.2 + Math.random() * 1.6 );
			velocidades[ i ].set( Math.cos( angulo ) * fuerza, Math.sin( angulo ) * fuerza + medidas.paso, ( Math.random() - 0.5 ) * fuerza );
		}
		geoChispas.attributes.position.needsUpdate = true;
		vidaChispas = 1;
	}

	/* Medidas (en píxeles del panel, pasadas a la escena) ------------- */

	const medidas = { paso: 1, radioEstrella: 0.5, radioBola: 0.15, y: 2, alto: 1, extremo: 3 };

	function colocar() {
		const ancho = contenedor.clientWidth;
		const alto = contenedor.clientHeight;
		const visible = 2 * camera.position.z * Math.tan( THREE.MathUtils.degToRad( camera.fov / 2 ) );
		const porPx = visible / alto;
		const hueco = parseFloat( getComputedStyle( contenedor ).paddingTop ) || 120;

		const tamano = THREE.MathUtils.clamp( ancho * 0.11, 36, 62 ); // Diámetro de cada estrella, en px.
		medidas.paso = tamano * 1.5 * porPx;
		medidas.radioEstrella = ( tamano / 2 ) * porPx;
		medidas.radioBola = tamano * 0.24 * porPx;
		medidas.y = ( alto / 2 - hueco * 0.58 ) * porPx;
		medidas.alto = tamano * porPx;
		// Donde empieza y acaba el rally: fuera de la fila, pero dentro del panel.
		medidas.extremo = Math.min( medidas.paso * 2.75, ( ancho / 2 ) * porPx - medidas.radioBola * 2.5 );

		estrellas.forEach( ( estrella, i ) => {
			const k = i - ( NUM_ESTRELLAS - 1 ) / 2;
			// Un arco suave: la del centro, un poco más alta.
			estrella.base.set( k * medidas.paso, medidas.y + medidas.alto * 0.28 * ( 1 - ( k * k ) / 4 ), 0 );
			estrella.malla.scale.setScalar( medidas.radioEstrella );
		} );
		bola.scale.setScalar( medidas.radioBola );
	}

	/* Recorrido de la bola ------------------------------------------- */

	// Punto de contacto: la punta de arriba de la estrella i (o los extremos).
	const contacto = ( i, destino ) => {
		if ( i < 0 || i >= NUM_ESTRELLAS ) {
			const lado = i < 0 ? -1 : 1;
			return destino.set( lado * medidas.extremo, medidas.y - medidas.alto * 0.55, 0.4 );
		}
		const estrella = estrellas[ i ];
		return destino.copy( estrella.malla.position ).add( new THREE.Vector3( 0, medidas.radioEstrella * 0.95 + medidas.radioBola, 0.3 ) );
	};

	/*
	 * Fases del bucle:
	 * - rally: tramos de -1 → 0 → 1 … → 4 → 5 (fuera, a la derecha); al llegar
	 *   a cada estrella, se enciende.
	 * - fiesta: chispas y una ola por las cinco.
	 * - vuelta: la bola vuelve por arriba al principio mientras se apagan.
	 */
	const estado = { fase: 'rally', tramo: -1, t: 0, reloj: 0, puntero: new THREE.Vector2(), inclinacion: new THREE.Vector2() };
	const desde = new THREE.Vector3();
	const hasta = new THREE.Vector3();

	function tocar( i ) {
		const estrella = estrellas[ i ];
		if ( estrella ) {
			estrella.objetivo = 1;
			estrella.giro = 0;
		}
	}

	function posicionBola( dt ) {
		if ( 'fiesta' === estado.fase ) {
			contacto( NUM_ESTRELLAS, hasta );
			bola.position.copy( hasta );
			bola.position.y += Math.abs( Math.sin( estado.t * Math.PI * 3 ) ) * medidas.alto * 0.25 * ( 1 - estado.t );
			return;
		}

		const vuelta = 'vuelta' === estado.fase;
		contacto( vuelta ? NUM_ESTRELLAS : estado.tramo, desde );
		contacto( vuelta ? -1 : estado.tramo + 1, hasta );
		const duracion = vuelta ? 1.6 : 0.62;
		const altura = vuelta ? medidas.alto * 1.5 : medidas.alto * 0.95;

		estado.t = Math.min( estado.t + dt / duracion, 1 );
		const t = vuelta ? suave( estado.t ) : estado.t;
		bola.position.lerpVectors( desde, hasta, t );
		bola.position.y += 4 * altura * t * ( 1 - t );
		if ( vuelta ) {
			bola.position.z += Math.sin( t * Math.PI ) * medidas.paso * 0.8;
		}

		// Gira en el sentido en que avanza.
		bola.rotation.z -= ( ( hasta.x - desde.x ) / duracion ) * dt / medidas.radioBola * 0.5;
		bola.rotation.x += dt * 1.2;
	}

	function avanzar( dt ) {
		estado.reloj += dt;

		if ( 'rally' === estado.fase && estado.t >= 1 ) {
			estado.tramo += 1;
			estado.t = 0;
			tocar( estado.tramo );
			if ( estado.tramo >= NUM_ESTRELLAS ) {
				estado.fase = 'fiesta';
				lanzarChispas();
			}
		} else if ( 'fiesta' === estado.fase ) {
			estado.t += dt / 2.2;
			if ( estado.t >= 1 ) {
				estado.fase = 'vuelta';
				estado.t = 0;
			}
		} else if ( 'vuelta' === estado.fase && estado.t >= 1 ) {
			estado.fase = 'rally';
			estado.tramo = -1;
			estado.t = 0;
		}

		posicionBola( dt );

		// En la vuelta, cada estrella se apaga cuando la bola pasa por encima.
		if ( 'vuelta' === estado.fase ) {
			estrellas.forEach( ( e ) => {
				if ( bola.position.x < e.base.x ) {
					e.objetivo = 0;
				}
			} );
		}

		estrellas.forEach( ( estrella, i ) => {
			const { malla, material } = estrella;
			const velocidad = estrella.objetivo > estrella.luz ? 6 : 4.5;
			estrella.luz += ( estrella.objetivo - estrella.luz ) * Math.min( dt * velocidad, 1 );

			// Flotan un poco; con las cinco encendidas, una ola.
			const ola = 'fiesta' === estado.fase ? Math.sin( estado.t * Math.PI * 4 - i * 0.9 ) * medidas.alto * 0.18 * ( 1 - estado.t ) : 0;
			malla.position.copy( estrella.base );
			malla.position.y += Math.sin( estado.reloj * 1.6 + estrella.fase ) * medidas.alto * 0.06 + ola;

			// Giro completo al recibir la bola, con un pequeño salto de tamaño.
			let giro = 0;
			let escala = 1;
			if ( estrella.giro >= 0 ) {
				estrella.giro += dt;
				const t = Math.min( estrella.giro / 0.8, 1 );
				giro = rebote( t ) * Math.PI * 2;
				escala = 1 + Math.sin( t * Math.PI ) * 0.25;
				if ( t >= 1 ) {
					estrella.giro = -1;
				}
			}
			malla.rotation.set( Math.sin( estado.reloj * 0.9 + estrella.fase ) * 0.12, giro + Math.sin( estado.reloj * 0.7 + estrella.fase ) * 0.25, 0 );
			malla.scale.setScalar( medidas.radioEstrella * escala );

			material.color.copy( APAGADA ).lerp( ACENTO, estrella.luz );
			material.emissiveIntensity = estrella.luz * 0.35;
		} );

		if ( vidaChispas > 0 ) {
			vidaChispas = Math.max( vidaChispas - dt / 1.4, 0 );
			for ( let i = 0; i < NUM_CHISPAS; i++ ) {
				const v = velocidades[ i ];
				v.y -= medidas.paso * 4 * dt;
				posiciones[ i * 3 ] += v.x * dt;
				posiciones[ i * 3 + 1 ] += v.y * dt;
				posiciones[ i * 3 + 2 ] += v.z * dt;
			}
			geoChispas.attributes.position.needsUpdate = true;
			matChispas.opacity = vidaChispas;
		}

		// Inclinación hacia el puntero, suavizada.
		estado.inclinacion.lerp( estado.puntero, Math.min( dt * 3, 1 ) );
		mundo.rotation.set( estado.inclinacion.y * 0.18, estado.inclinacion.x * 0.32, 0 );
	}

	/* Posición fija (reducir movimiento) ----------------------------- */

	function pose() {
		estrellas.forEach( ( estrella ) => {
			estrella.malla.position.copy( estrella.base );
			estrella.malla.rotation.set( 0, 0.35, 0 );
			estrella.material.color.copy( ACENTO );
			estrella.material.emissiveIntensity = 0.35;
		} );
		contacto( NUM_ESTRELLAS - 1, hasta );
		bola.position.copy( hasta );
		bola.rotation.set( 0.4, 0.6, 0 );
	}

	/* Tamaño y bucle ------------------------------------------------- */

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
		camera.updateProjectionMatrix();
		colocar();
		if ( quieto ) {
			pose();
			dibujar();
		}
	}

	let visible = false;
	let anterior = 0;
	let pedido = 0;

	function programar() {
		if ( quieto || ! visible || document.hidden || pedido ) {
			return;
		}
		pedido = requestAnimationFrame( ( ahora ) => {
			pedido = 0;
			const dt = anterior ? Math.min( ( ahora - anterior ) / 1000, 0.05 ) : 0;
			anterior = ahora;
			avanzar( dt );
			dibujar();
			programar();
		} );
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
		const caja = contenedor.getBoundingClientRect();
		estado.puntero.set(
			THREE.MathUtils.clamp( ( ( evento.clientX - caja.left ) / caja.width ) * 2 - 1, -1, 1 ),
			THREE.MathUtils.clamp( ( ( evento.clientY - caja.top ) / caja.height ) * 2 - 1, -1, 1 )
		);
	} );
	contenedor.addEventListener( 'pointerleave', () => estado.puntero.set( 0, 0 ) );

	new ResizeObserver( redimensionar ).observe( escena );
	redimensionar();
	if ( ! quieto ) {
		avanzar( 0 );
		dibujar();
	}
	escena.classList.remove( 'is-cargando' );
}
