# MMPadel – web de captación

Web de la escuela de pádel MMPadel (Málaga) en WordPress: un tema propio ligero (`themes/mmpadel`) y un plugin propio con la lógica y los contenidos (`plugins/mmpadel-core`). Sin page builders.

La web explica la escuela y lleva al usuario a tres sitios: el formulario «Encuentra tu grupo», el canal de WhatsApp de plazas libres o el software de gestión. No tiene login, reservas, disponibilidad ni pagos; eso lo hará el software de gestión.

> Estado: **base del proyecto** con el logo y los colores de marca. Los textos, las tarifas y las fotos son provisionales.

## Demo online

**[▶ Abrir la demo en WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/IsmaGDevOps/mmpadel_web/main/blueprint.json)**

WordPress se ejecuta entero en tu navegador, sin instalar nada. La primera carga tarda unos segundos: instala el tema y el plugin desde este repositorio (rama `main`), pone el sitio en español, crea las páginas y los menús y añade el contenido de ejemplo. Lo hace [`blueprint.json`](blueprint.json).

- Ya entras con sesión iniciada. El panel está en `/wp-admin` (usuario `admin`, contraseña `password`).
- Puedes enviar el formulario y ver la solicitud en *Solicitudes*, pero los emails no se envían.
- Es una copia temporal: al recargar la página se vuelve a montar desde cero y se pierden los cambios.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) arrancado (en Windows, con WSL2).
- Node.js 18 o superior.

## Arrancar en local

```bash
npm install        # instala @wordpress/env
npm start          # levanta WordPress en Docker (la primera vez tarda unos minutos)
npm run setup      # español, enlaces permanentes, tema, zona horaria, páginas y menús
npm run demo       # opcional: tarifas, entrenadores, eventos, FAQ, opiniones y píldoras de ejemplo
```

- Web: http://localhost:8888
- Panel: http://localhost:8888/wp-admin, usuario `admin` y contraseña `password`

## Comandos

| Comando | Qué hace |
|---|---|
| `npm start` / `npm stop` | Arranca o para el entorno |
| `npm run setup` | Configuración inicial. Se puede repetir sin duplicar nada |
| `npm run demo` | Crea el contenido de ejemplo. Para borrarlo: `npm run wp -- mmpadel demo --borrar` |
| `npm run wp -- <comando>` | Ejecuta WP-CLI, p. ej. `npm run wp -- plugin list` |
| `npm run mail` | Muestra los emails enviados en local (no se envían de verdad) |
| `npm run lint:install` | Instala PHPCS y los WordPress Coding Standards dentro del contenedor (una vez) |
| `npm run lint` / `npm run lint:fix` | Revisa o corrige el PHP con los estándares de WordPress |
| `npm run destroy` | Borra el entorno y su base de datos |

## Cómo probarlo

1. **Páginas y menú.** Recorre la web en el móvil (o con el modo móvil del navegador): las 13 páginas, el menú desplegable y el pie con contacto y textos legales.
2. **Origen.** Entra con una URL de campaña, p. ej. http://localhost:8888/?utm_source=instagram&utm_medium=bio&utm_campaign=prueba, y navega hasta «Encuentra tu grupo».
3. **Formulario con errores.** Envíalo con campos vacíos o un email incorrecto: verás un resumen de errores arriba y los datos que ya escribiste se conservan.
4. **Formulario correcto.** Rellénalo y espera al menos 3 segundos antes de enviar (los envíos más rápidos se descartan como spam). Verás la confirmación.
5. **Emails.** `npm run mail` muestra el aviso a MMPadel (con `Reply-To` al interesado) y la respuesta automática.
6. **Solicitudes.** En el panel, *Solicitudes*: el contador de nuevas, la ficha con origen y consentimiento, el cambio de estado, las notas internas y *Exportar CSV* (se abre bien en Excel).
7. **dataLayer.** En la consola del navegador escribe `dataLayer` tras:
   - enviar el formulario (`form_submit`);
   - pulsar un enlace de WhatsApp (`click_whatsapp`);
   - pulsar un teléfono (`click_phone`);
   - pulsar «Inscribirme» en un pool (`click_inscripcion_pool`).
8. **Edición.** Cambia una tarifa, un entrenador o un dato de *Ajustes → MMPadel* y comprueba que la web se actualiza.
9. **Calidad.** `npm run lint:install` (solo la primera vez) y después `npm run lint`.

## Qué edita el cliente y dónde

| Qué | Dónde (panel) |
|---|---|
| Textos de cada página | *Páginas* (editor de bloques) |
| Tarifas y tipos de clase | *Tarifas* (el campo *Orden* decide el orden en la web) |
| Entrenadores | *Entrenadores* (la imagen destacada es la foto) |
| Pools y eventos | *Pools y eventos* (cada uno tiene ficha en `/eventos/nombre/`) |
| Preguntas frecuentes | *Preguntas frecuentes* (con categorías) |
| Opiniones (reseñas de Google que se muestran en la web) | *Opiniones*: nombre como aparece en Google, estrellas, texto y, si se quiere, un detalle |
| Teléfono, WhatsApp, dirección, horario, redes sociales, enlaces, email de avisos, datos legales | *Ajustes → MMPadel* |
| Fotos (entrenadores, clases, eventos, píldoras) | *Medios* (Biblioteca de medios) |
| Foto de portada de una página | *Imagen destacada* de la página (sustituye a la foto del tema, ver `themes/mmpadel/assets/images/README.md`) |
| Menús | *Apariencia → Menús* |
| Solicitudes recibidas | *Solicitudes* (solo administradores) |

Las fichas se muestran en las páginas mediante shortcodes. Se ven en el editor y se puede escribir texto antes y después:

| Shortcode | Muestra |
|---|---|
| `[mmpadel_tarifas]` | Tarifas agrupadas por tipo de clase (`tipo="slug"` para filtrar) |
| `[mmpadel_equipo]` | Fichas de entrenadores |
| `[mmpadel_eventos mostrar="proximos"]` | Pools y eventos (`proximos`, `pasados` o `todos`; `limite="6"`) |
| `[mmpadel_faq]` | Preguntas frecuentes desplegables (`categoria="slug"` para filtrar) |
| `[mmpadel_formulario]` | Formulario «Encuentra tu grupo» |
| `[mmpadel_contacto]` | Dirección, horario, teléfono, email y botones |
| `[mmpadel_valoracion]` | Marcador con la nota y el número de reseñas de Google (*Ajustes → MMPadel → Enlaces*) |
| `[mmpadel_opiniones]` | Muro de opiniones (`limite="12"`). Si no hay ninguna, no muestra nada |
| `[mmpadel_boton destino="plazas"]` | Botón a un enlace de Ajustes: `grupo`, `whatsapp`, `plazas`, `software`, `telefono`, `resenas`, `instagram`, `facebook`. Admite `texto="…"` y `estilo="cta"` o `estilo="borde"`. Si falta el enlace no se muestra (los editores ven un aviso). |
| `[mmpadel_dato clave="titular"]` | Un dato de Ajustes (para los textos legales). Si falta, sale marcado como pendiente. |

## Cómo está montado

- **Entorno: [@wordpress/env](https://www.npmjs.com/package/@wordpress/env)** (`.wp-env.json`, PHP 8.2). Se eligió en lugar de Docker Compose porque solo necesita un archivo de configuración, trae WP-CLI y es el estándar de WordPress. Monta el tema, el plugin y las utilidades de `dev/` directamente desde el repositorio.
- **`dev/wp-env.js`:** los scripts de npm llaman a wp-env a través de este archivo. Normaliza el nombre de usuario del equipo, porque wp-env falla al construir los contenedores si el nombre de usuario de Windows tiene espacios (como «Despacho 1»). Si ejecutas `npx wp-env` directamente, te saltas la corrección.
- **`dev/mmpadel-dev-mail.php`:** mu-plugin solo para local. Guarda los emails en `wp-content/mmpadel-mail.log` en lugar de enviarlos.
- **Tema clásico + `theme.json`:**
  - El cliente edita el contenido con bloques, pero no puede romper la cabecera ni el pie.
  - `theme.json` limita colores y tipografías a los de la marca y genera las variables CSS. Encima, `main.css` define alias `--mmpadel-*`.
  - Sin proceso de compilación y sin fuentes remotas.
- **Plugin `mmpadel-core`** (la web no pierde nada si se cambia de tema):
  - `includes/class-mmpadel-fields.php`: mini-framework de campos (sustituye a ACF). Cada tipo de contenido declara sus campos en un array.
  - `includes/class-mmpadel-form.php`: el formulario.
    - Funciona sin JavaScript.
    - Validación en servidor, nonce, honeypot, tiempo mínimo firmado y límite de 5 envíos cada 10 minutos por IP.
    - El esquema de campos es único y lo usan el formulario, los emails, la ficha y el CSV.
  - `includes/class-mmpadel-requests-admin.php`: listado, ficha, estados y CSV de las solicitudes.
  - `includes/class-mmpadel-privacy.php`: conecta las solicitudes con *Herramientas → Exportar/Borrar datos personales* (RGPD).
  - `includes/class-mmpadel-cli.php`: comandos `wp mmpadel paginas` y `wp mmpadel demo`.
  - `templates/`: HTML de los listados y del formulario. El tema puede sustituir cualquier plantilla copiándola a `themes/mmpadel/mmpadel/`.
  - `content/paginas/`: contenido inicial de las páginas, en bloques.
  - `assets/js/mmpadel-tracking.js`:
    - Guarda el origen (UTM, referrer externo y página de entrada) en `sessionStorage`.
    - Envía los eventos a `dataLayer`.
- **Solicitudes:** son privadas. No tienen URL pública ni salen en la API REST, tienen permisos propios que solo tiene el administrador y no se pueden crear ni editar a mano (solo estado y notas). Al desinstalar el plugin no se borran.
- **Puntos de extensión:**
  - filtro `mmpadel_form_options`: cambiar las opciones del formulario;
  - acción `mmpadel_solicitud_creada`: enviar la solicitud al software de gestión en la fase 2.

## Estructura

```
.wp-env.json            Entorno local
package.json            Scripts y @wordpress/env
composer.json           PHPCS + WordPress Coding Standards (solo desarrollo)
phpcs.xml.dist          Reglas de PHPCS
dev/                    Utilidades solo para desarrollo (no van a producción)
themes/mmpadel/         Tema: plantillas, theme.json, estilos y menú
  assets/images/        Imágenes de diseño: logotipo, portada, iconos (ver su README)
plugins/mmpadel-core/   Plugin: contenidos, campos, formulario, solicitudes, CLI
docs/                   Documentación del cliente (no se versiona)
```

## Pasar a producción (resumen)

1. Subir `themes/mmpadel` y `plugins/mmpadel-core`. No subir `dev/`.
2. Activar el tema y el plugin y ejecutar `wp mmpadel paginas`, o crear las páginas a mano con los mismos slugs.
3. Rellenar *Ajustes → MMPadel*, sobre todo el email que recibe las solicitudes y los datos legales.
4. Configurar el correo saliente (SMTP autenticado) y enviar una solicitud de prueba.

## Pendiente

**Contenido y diseño**
- Diseño final: tipografía de marca (alojada en el propio servidor, en `theme.json`), fotos reales (las portadas actuales son genéricas y provisionales, ver `themes/mmpadel/assets/images/portadas/CREDITOS.md`) y revisión visual completa. El logo y los colores (azul `#2A2763` del logo y amarillo pelota `#D7F25C` como acento) ya están aplicados.
- Textos definitivos, tarifas reales, equipo y FAQ. Borrar el contenido de ejemplo con `wp mmpadel demo --borrar`.
- Widget oficial de reseñas de Google en *Opiniones*.

**Legal**
- Revisión de aviso legal, privacidad y cookies por una asesoría; ahora son plantillas con marcadores.
- Datos del titular en *Ajustes → MMPadel*.
- Definir cuánto tiempo se conservan las solicitudes y, si se quiere, borrarlas automáticamente.
- Banner de consentimiento de cookies. Debe cubrir también el dato `mmpadel_origen` de `sessionStorage`.

**Medición**
- Instalar GA4 (o Google Tag Manager) con Consent Mode y Search Console. Los eventos ya llegan a `dataLayer`; falta crearlos como conversiones.

**Formulario y software de gestión**
- Cuadrar las opciones del formulario (nivel, franjas, tipos…) con los campos de alta del software cuando se elija, mediante el filtro `mmpadel_form_options`.
- Fase 2: integración con el software usando la acción `mmpadel_solicitud_creada` y el enlace «Acceso alumnos» de *Ajustes → MMPadel*.
- La propuesta menciona una copia de las solicitudes en una hoja de cálculo: hoy hay exportación CSV manual; la copia automática a Google Sheets está pendiente.

**Producción**
- Correo con SMTP autenticado (SPF/DKIM) para que los avisos no lleguen a spam.
- Caché de página: no cachear «Encuentra tu grupo», o como mucho 12 h, porque el formulario lleva un nonce que caduca. Si caduca, el usuario ve un aviso y no pierde los datos.
- Si la web va detrás de un proxy o CDN (p. ej. Cloudflare), adaptar la detección de IP del límite de envíos, que hoy usa `REMOTE_ADDR`. Si no, todos los visitantes compartirían el límite.
- SEO: títulos y descripciones, datos estructurados de negocio local y conexión con el Perfil de Empresa de Google.
- Revisión de accesibilidad con lector de pantalla y de contraste con los colores definitivos.
- Alojamiento, SSL y copias de seguridad.
