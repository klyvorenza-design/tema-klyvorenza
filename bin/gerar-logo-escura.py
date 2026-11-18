#!/usr/bin/env python3
"""Gera assets/logo/logo-site-dark.png a partir de logo-site.png.

Só a palavra "Klyvorenza" (região à direita do emblema) tem os pixels
pretos trocados por branco. O emblema não é alterado em nenhum pixel.
A divisão é geométrica: o emblema é um círculo; tudo fora dele é palavra.
"""
import sys
from PIL import Image

src = sys.argv[1] if len(sys.argv) > 1 else "assets/logo/logo-site.png"
dst = sys.argv[2] if len(sys.argv) > 2 else "assets/logo/logo-site-dark.png"

im = Image.open(src).convert("RGBA")
w, h = im.size
px = im.load()
ALPHA_MIN = 24

# 1) o emblema é um círculo: estima centro e raio pelo trecho à esquerda,
#    onde só existe o emblema (a palavra começa bem depois).
ys = [y for y in range(h) for x in range(int(w * 0.18)) if px[x, y][3] > ALPHA_MIN]
xs = [x for y in range(h) for x in range(int(w * 0.18)) if px[x, y][3] > ALPHA_MIN]
y0, y1 = min(ys), max(ys)
x0 = min(xs)
r = (y1 - y0) / 2.0
cx, cy = x0 + r, (y0 + y1) / 2.0
print(f"emblema: centro=({cx:.1f},{cy:.1f}) raio={r:.1f}")

# 2) fora do círculo (com 1 px de margem), preto vira branco.
#    Dentro do círculo nada é alterado (inclui o interior preto do emblema).
out = im.copy()
op = out.load()
changed = 0
for y in range(h):
    for x in range(w):
        R, G, B, a = px[x, y]
        if a == 0:
            continue
        if (x + 0.5 - cx) ** 2 + (y + 0.5 - cy) ** 2 <= (r + 1.0) ** 2:
            continue
        lum = (R + G + B) / 3
        if lum < 110 and abs(R - G) < 40 and abs(G - B) < 40:
            nv = int(round(R + (255 - R) * (1 - lum / 110)))
            op[x, y] = (nv, nv, nv, a)
            changed += 1
out.save(dst, optimize=True)
print(f"{changed} pixels alterados na palavra -> {dst}")
