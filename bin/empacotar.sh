#!/usr/bin/env bash
# Gera klyvorenza.zip pronto para instalar pelo painel do WordPress.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TMP="$(mktemp -d)"
mkdir "$TMP/klyvorenza"
tar -C "$ROOT" --exclude=.git --exclude=dist -cf - . | tar -C "$TMP/klyvorenza" -xf -
rm -f "$ROOT/dist/klyvorenza.zip"
(cd "$TMP" && zip -rq "$ROOT/dist/klyvorenza.zip" klyvorenza)
echo "dist/klyvorenza.zip criado"
