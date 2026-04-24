#!/usr/bin/env bash

set -euo pipefail

VERSION="${1:-}"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_SLUG="braudypedrosa/vmb-starter-theme-public"
TAG_NAME=""

if [[ -z "${VERSION}" ]]; then
  echo "Usage: scripts/publish-release.sh <version>" >&2
  exit 1
fi

if [[ ! "${VERSION}" =~ ^[0-9]+(\.[0-9]+)+$ ]]; then
  echo "Version must look like 1.1.8" >&2
  exit 1
fi

TAG_NAME="v${VERSION}"

cd "${ROOT_DIR}"

gh auth status >/dev/null

if [[ "$(git rev-parse --abbrev-ref HEAD)" != "main" ]]; then
  echo "Publish from the main branch only." >&2
  exit 1
fi

if ! git diff --quiet || ! git diff --cached --quiet || [[ -n "$(git ls-files --others --exclude-standard)" ]]; then
  echo "Working tree must be clean before publishing." >&2
  exit 1
fi

if git rev-parse "${TAG_NAME}" >/dev/null 2>&1; then
  echo "Tag ${TAG_NAME} already exists locally." >&2
  exit 1
fi

if gh release view "${TAG_NAME}" --repo "${REPO_SLUG}" >/dev/null 2>&1; then
  echo "Release ${TAG_NAME} already exists on GitHub." >&2
  exit 1
fi

npm ci
npm version --no-git-tag-version "${VERSION}"
perl -0pi -e "s/Version:\\s*[0-9]+(?:\\.[0-9]+)+/Version: ${VERSION}/" style.css

PACKAGE_PATH="$("${ROOT_DIR}/scripts/build-release-zip.sh" | tail -n 1)"

git add style.css package.json package-lock.json dist
git commit -m "Release ${TAG_NAME}"
git push origin main
git tag "${TAG_NAME}"
git push origin "${TAG_NAME}"

gh release create "${TAG_NAME}" "${PACKAGE_PATH}" \
  --repo "${REPO_SLUG}" \
  --title "${TAG_NAME}" \
  --generate-notes

echo "Published ${TAG_NAME}"
