"""Genera la foto de portada de una página a partir de cualquier foto.

Uso (requiere Pillow):
    python dev/portada.py ruta/a/la/foto.jpg metodologia

Crea en themes/mmpadel/assets/images/portadas/:
- {slug}.webp        1920x1080 (escritorio), como mucho 300 KB
- {slug}-movil.webp  1000x1000 (móvil), como mucho 150 KB

El recorte es centrado: si lo importante de la foto está en un lateral,
mejor recortarla antes. El slug es el de la página (inicio, metodologia,
clases-y-tarifas, pools-y-eventos, equipo…).
"""
import io
import os
import sys

from PIL import Image, ImageOps

DESTINO = os.path.join(os.path.dirname(__file__), '..', 'themes', 'mmpadel', 'assets', 'images', 'portadas')


def crop(im, ratio):
    w, h = im.size
    if w / h > ratio:
        nw = round(h * ratio)
        x = (w - nw) // 2
        return im.crop((x, 0, x + nw, h))
    nh = round(w / ratio)
    y = (h - nh) // 2
    return im.crop((0, y, w, y + nh))


def save_webp(im, path, limit):
    """Guarda en WebP bajando la calidad hasta quedar por debajo del límite (mínimo 40)."""
    for quality in range(62, 39, -4):
        buf = io.BytesIO()
        im.save(buf, 'WEBP', quality=quality, method=6)
        if buf.tell() <= limit:
            break
    with open(path, 'wb') as f:
        f.write(buf.getvalue())
    return buf.tell()


def main():
    if len(sys.argv) != 3:
        sys.exit(__doc__)
    origen, slug = sys.argv[1], sys.argv[2]

    # exif_transpose: respeta la orientación de las fotos hechas con el móvil.
    im = ImageOps.exif_transpose(Image.open(origen)).convert('RGB')
    if im.width < 1920:
        print(f'Aviso: la foto mide {im.width} px de ancho; en pantallas grandes se verá algo borrosa.')

    os.makedirs(DESTINO, exist_ok=True)
    grande = save_webp(crop(im, 16 / 9).resize((1920, 1080), Image.LANCZOS), os.path.join(DESTINO, f'{slug}.webp'), 300_000)
    movil = save_webp(crop(im, 1).resize((1000, 1000), Image.LANCZOS), os.path.join(DESTINO, f'{slug}-movil.webp'), 150_000)
    print(f'{slug}.webp ({grande // 1000} KB) y {slug}-movil.webp ({movil // 1000} KB) listos.')


if __name__ == '__main__':
    main()
