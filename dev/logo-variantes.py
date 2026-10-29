"""Genera las variantes del logo de MMPadel a partir del original (blanco sobre azul).

Uso (requiere Pillow):
    python dev/logo-variantes.py themes/mmpadel/assets/images/logommpadel.webp themes/mmpadel/assets/images

- logo-blanco.webp: solo las letras, blancas y con fondo transparente (cabecera y pie).
- favicon.png: «MM» blanco sobre el azul de marca, 512x512.
"""
import sys

from PIL import Image

src, outdir = sys.argv[1], sys.argv[2]
BG = (0x2A, 0x27, 0x63)

im = Image.open(src).convert('RGB')
W, H = im.size
px = im.load()


def lum(c):
    return 0.299 * c[0] + 0.587 * c[1] + 0.114 * c[2]


LBG = lum(BG)


def alpha_of(c):
    """Opacidad del blanco: 0 en el fondo azul, 255 en blanco puro (bordes suavizados)."""
    a = (lum(c) - LBG) / (255 - LBG)
    return max(0, min(255, round(a * 255)))


def bbox(x0, y0, x1, y1, threshold=40):
    xs, ys = [], []
    for y in range(y0, y1):
        for x in range(x0, x1):
            if alpha_of(px[x, y]) > threshold:
                xs.append(x)
                ys.append(y)
    return min(xs), min(ys), max(xs) + 1, max(ys) + 1


def white_with_alpha(box, pad):
    x0, y0, x1, y1 = box
    x0, y0, x1, y1 = max(0, x0 - pad), max(0, y0 - pad), min(W, x1 + pad), min(H, y1 + pad)
    out = Image.new('RGBA', (x1 - x0, y1 - y0))
    o = out.load()
    for y in range(y0, y1):
        for x in range(x0, x1):
            o[x - x0, y - y0] = (255, 255, 255, alpha_of(px[x, y]))
    return out


# 1. Palabra completa («escuela MMPADEL»).
word = white_with_alpha(bbox(0, 0, W, H), pad=4)
word.save(f'{outdir}/logo-blanco.webp', 'WEBP', lossless=True, quality=100, method=6)
print('logo-blanco.webp', word.size)

# 2. Favicon con «MM»: las dos primeras letras de la línea inferior.
#    Se busca el hueco vertical entre «MM» y «P» recorriendo columnas.
_, ly0, _, ly1 = bbox(0, H // 2 - 20, W, H)
cols = [any(alpha_of(px[x, y]) > 40 for y in range(ly0, ly1)) for x in range(W)]
runs, run_start = [], None
for x, filled in enumerate(cols + [False]):
    if filled and run_start is None:
        run_start = x
    elif not filled and run_start is not None:
        runs.append((run_start, x))
        run_start = None
# La segunda M va pegada a la P: se corta con el ancho de la primera M.
first_m = runs[0][1] - runs[0][0]
start, end = runs[0][0], runs[1][0] + first_m
mm = white_with_alpha((start, ly0, end, ly1), pad=0)
size = 512
icon = Image.new('RGBA', (size, size), BG + (255,))
scale = (size * 0.72) / max(mm.size)
mm = mm.resize((round(mm.size[0] * scale), round(mm.size[1] * scale)), Image.LANCZOS)
icon.alpha_composite(mm, ((size - mm.size[0]) // 2, (size - mm.size[1]) // 2))
icon.convert('RGB').save(f'{outdir}/favicon.png', 'PNG', optimize=True)
print('favicon.png', icon.size, 'MM recortado', (start, ly0, end, ly1))
