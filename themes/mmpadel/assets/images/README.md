# Imágenes del tema

Imágenes de **diseño** que forman parte del tema y se versionan con el código:

- Logotipo (`logo.svg`, y `logo-blanco.svg` para fondos oscuros)
- Imagen de fondo de la cabecera de portada (`portada.webp`)
- Iconos e ilustraciones de las secciones
- Favicon de origen (el icono del sitio se sube en *Apariencia → Personalizar → Identidad del sitio*)

En las plantillas PHP se usan con `mmpadel_theme_image_url( 'logo.svg' )`.

## Qué NO va aquí

Las fotos de **contenido** se suben a la *Biblioteca de medios* del panel, no a esta carpeta:

- Fotos de entrenadores (imagen destacada de cada entrenador)
- Fotos de clases, instalaciones, pools y eventos
- Imágenes de las píldoras del blog y de las páginas

Así el cliente las cambia sin tocar código, WordPress genera los tamaños para móvil y no se suben al repositorio (`uploads/` está en `.gitignore`).

## Formato

- **Logotipo e iconos:** SVG optimizado (sin metadatos del editor).
- **Fotos:** WebP (o AVIF), como mucho 300 KB, de 2400 px de ancho para imágenes a pantalla completa y de 1200 px para el resto.
- **Nombres:** en minúsculas, sin tildes ni espacios y con guiones: `portada-pista-noche.webp`.
- Toda foto que comunique algo necesita un texto alternativo en la plantilla; las decorativas, `alt=""`.
