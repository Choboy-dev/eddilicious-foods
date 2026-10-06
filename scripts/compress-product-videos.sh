#!/usr/bin/env bash
# Compress product MP4s for faster web delivery.
# Requires: ffmpeg (brew install ffmpeg)
#
# Usage: ./scripts/compress-product-videos.sh
# Output: assets/images/products/optimized/*.mp4

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
INPUT_DIR="$ROOT/assets/images/products"
OUTPUT_DIR="$INPUT_DIR/optimized"

if ! command -v ffmpeg >/dev/null 2>&1; then
  echo "ffmpeg is not installed. Install it with: brew install ffmpeg" >&2
  exit 1
fi

mkdir -p "$OUTPUT_DIR"

for input in "$INPUT_DIR"/*.mp4; do
  [ -f "$input" ] || continue

  base="$(basename "$input" .mp4)"
  output="$OUTPUT_DIR/${base}.mp4"

  echo "Compressing: $base.mp4"
  ffmpeg -y -i "$input" \
    -c:v libx264 \
    -crf 28 \
    -preset medium \
    -movflags +faststart \
    -an \
    "$output"
done

echo "Done. Optimized files are in assets/images/products/optimized/"
