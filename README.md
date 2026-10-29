# MMPadel – web de captación

Web de la escuela de pádel MMPadel (Málaga), hecha en WordPress con un tema propio ligero (`themes/mmpadel`) y un plugin propio con la lógica y los contenidos (`plugins/mmpadel-core`). Sin page builders.

La web explica la escuela y lleva al usuario al formulario «Encuentra tu grupo», al canal de WhatsApp de plazas libres o al software de gestión. No tiene login, reservas ni pagos.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) arrancado (en Windows, con WSL2).
- Node.js 18 o superior.

## Arrancar en local

```bash
npm install        # instala @wordpress/env
npm start          # levanta WordPress en Docker
npm run setup      # español, enlaces permanentes, tema activo y zona horaria (solo la primera vez)
```

- Web: http://localhost:8888
- Panel: http://localhost:8888/wp-admin, usuario `admin` y contraseña `password`

## Comandos

| Comando | Qué hace |
|---|---|
| `npm start` / `npm stop` | Arranca o para el entorno |
| `npm run wp -- <comando>` | Ejecuta WP-CLI, p. ej. `npm run wp -- plugin list` |
| `npm run mail` | Muestra los emails enviados en local |
| `npm run lint:install` | Instala PHPCS y los WordPress Coding Standards dentro del contenedor (una vez) |
| `npm run lint` / `npm run lint:fix` | Revisa o corrige el código PHP con los estándares de WordPress |
| `npm run destroy` | Borra el entorno y su base de datos |

## Cómo está montado

- **Entorno:** [@wordpress/env](https://www.npmjs.com/package/@wordpress/env) (`.wp-env.json`). Monta el tema, el plugin y `dev/mmpadel-dev-mail.php` directamente desde el repositorio, así que los cambios se ven al recargar.
- **Correo en local:** el mu-plugin `dev/mmpadel-dev-mail.php` intercepta `wp_mail` y guarda cada email en `wp-content/mmpadel-mail.log` en lugar de enviarlo. Solo actúa cuando `WP_ENVIRONMENT_TYPE` es `local`.
- **`dev/wp-env.js`:** los scripts de npm llaman a wp-env a través de este archivo. Normaliza el nombre de usuario del equipo, porque wp-env falla al construir los contenedores si el nombre de usuario de Windows tiene espacios (por ejemplo, «Despacho 1»). Si ejecutas wp-env directamente (`npx wp-env`), te saltas esta corrección.
- **`docs/`** no se versiona: contiene documentación interna del cliente.

## Estructura

```
.wp-env.json        Configuración del entorno local
dev/                Utilidades solo para desarrollo (no van a producción)
themes/mmpadel/     Tema: plantillas, estilos y presentación
plugins/mmpadel-core/  Plugin: tipos de contenido, campos, formulario, solicitudes
```
