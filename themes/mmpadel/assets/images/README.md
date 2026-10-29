# Imágenes del tema

Imágenes de **diseño** que forman parte del tema y se versionan con el código.

| Archivo | Uso |
|---|---|
| `logommpadel.webp` | Logo original (blanco sobre azul). Pantalla de acceso al panel y origen de las variantes |
| `logo-blanco.webp` | Solo las letras, blancas y transparentes. Cabecera y pie (fondo azul) |
| `favicon.png` | «MM» sobre azul, 512×512. Icono del navegador mientras no se suba uno en *Apariencia → Personalizar → Identidad del sitio* |

`logo-blanco.webp` y `favicon.png` se generan a partir del original. Si cambia el logo, se sustituye `logommpadel.webp` y se ejecuta (requiere Pillow):

```bash
python dev/logo-variantes.py themes/mmpadel/assets/images/logommpadel.webp themes/mmpadel/assets/images
```

Si se sube un logo en *Personalizar → Identidad del sitio*, ese tiene prioridad sobre el del tema; debe verse bien sobre el azul `#2A2763`.

En las plantillas PHP las imágenes de esta carpeta se usan con `mmpadel_theme_image_url( 'logo-blanco.webp' )`.

## Qué NO va aquí

Las fotos de **contenido** se suben a la *Biblioteca de medios* del panel, no a esta carpeta:

- Fotos de entrenadores (imagen destacada de cada entrenador)
- Fotos de clases, instalaciones, pools y eventos
- Imágenes de las píldoras del blog y de las páginas

Así el cliente las cambia sin tocar código, WordPress genera los tamaños para móvil y no se suben al repositorio (`uploads/` está en `.gitignore`).

## Formato

- **Logotipo e iconos:** SVG optimizado, o WebP/PNG con transparencia si solo hay versión rasterizada.
- **Fotos:** WebP (o AVIF), como mucho 300 KB, de 2400 px de ancho para imágenes a pantalla completa y de 1200 px para el resto.
- **Nombres:** en minúsculas, sin tildes ni espacios y con guiones: `portada-pista-noche.webp`.
- Toda foto que comunique algo necesita un texto alternativo en la plantilla; las decorativas, `alt=""`.
