#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_SLUG="vmb-starter-theme"
BUILD_DIR="${ROOT_DIR}/.release-build"
PACKAGE_ROOT="${BUILD_DIR}/${THEME_SLUG}"
PACKAGE_PATH="${ROOT_DIR}/bundled/${THEME_SLUG}.zip"

cd "${ROOT_DIR}"

npx gulp build --prod

rm -rf "${BUILD_DIR}" "${PACKAGE_PATH}"
mkdir -p "${PACKAGE_ROOT}" "$(dirname "${PACKAGE_PATH}")"

rsync -a \
  --exclude '.git' \
  --exclude '.github' \
  --exclude '.gitignore' \
  --exclude 'node_modules' \
  --exclude 'bundled' \
  --exclude '.release-build' \
  --exclude 'scripts' \
  --exclude 'src' \
  --exclude 'gulpfile.js' \
  --exclude 'package.json' \
  --exclude 'package-lock.json' \
  ./ "${PACKAGE_ROOT}/"

(
  cd "${BUILD_DIR}"
  zip -qr "${PACKAGE_PATH}" "${THEME_SLUG}"
)

echo "${PACKAGE_PATH}"
