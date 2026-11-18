#!/usr/bin/env python3
"""Extrai a paleta dominante de simbolo.png (ignora preto, branco e transparente)."""
import sys
from collections import Counter
from PIL import Image

path = sys.argv[1] if len(sys.argv) > 1 else "assets/logo/simbolo.png"
im = Image.open(path).convert("RGBA")
cnt = Counter()
for r, g, b, a in im.getdata():
    if a < 200 or max(r, g, b) < 60 or min(r, g, b) > 235:
        continue
    cnt[(r // 16 * 16 + 8, g // 16 * 16 + 8, b // 16 * 16 + 8)] += 1
for (r, g, b), n in cnt.most_common(14):
    print(f"#{r:02X}{g:02X}{b:02X}  {n}")
