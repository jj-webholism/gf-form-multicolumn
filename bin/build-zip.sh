#!/usr/bin/env bash

set -e

PLUGIN_SLUG="gf-form-multicolumn"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

TARGET_DIR="${1:-${PROJECT_DIR}}"
mkdir -p "${TARGET_DIR}"
TARGET_DIR="$(cd "${TARGET_DIR}" && pwd)"
DIST_ZIP="${TARGET_DIR}/${PLUGIN_SLUG}.zip"

BUILD_DIR="/tmp/wp-plugin-build-${PLUGIN_SLUG}"
STAGE_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

echo "==> Preparing build environment..."
rm -rf "${BUILD_DIR}" "${DIST_ZIP}"
mkdir -p "${STAGE_DIR}"

EXCLUDE_FILE="/tmp/distignore-clean.txt"
grep -v '^[[:space:]]*#' "${PROJECT_DIR}/.distignore" 2>/dev/null | grep -v '^[[:space:]]*$' > "${EXCLUDE_FILE}" || true

cat << 'SAFE' >> "${EXCLUDE_FILE}"
.env*
vendor/
vendor-zips/
node_modules/
.git/
.wp-tests/
.idea
.idea/
.idea/**
.vscode
.vscode/
bin/
*.zip
*.tar.gz
package.xml
package.json
package-lock.json
composer.json
composer.lock
phpstan.neon*
phpunit.xml*
phpcs.xml*
playwright.config.*
run-tests.sh
SAFE

echo "==> Staging files for ${PLUGIN_SLUG}..."
rsync -rc --exclude-from="${EXCLUDE_FILE}" "${PROJECT_DIR}/" "${STAGE_DIR}/"
rm -f "${EXCLUDE_FILE}"

echo "==> Compressing into ${DIST_ZIP}..."
cd "${BUILD_DIR}"
zip -r -q "${DIST_ZIP}" "${PLUGIN_SLUG}"

rm -rf "${BUILD_DIR}"

echo ""
echo "✓ Distribution zip created successfully: ${DIST_ZIP}"
echo "  File size: $(du -h "${DIST_ZIP}" | cut -f1)"
echo ""
echo "==> Verifying archive contents..."
LEAKS=$(zipinfo -1 "${DIST_ZIP}" | grep -E '(\.env|\.git/|tests/|vendor/|vendor-zips/|node_modules/|playwright|\.idea|package\.xml|package-lock\.json)' || true)

if [ -n "${LEAKS}" ]; then
    echo "⚠️  WARNING: Found development artifacts in zip:"
    echo "${LEAKS}"
    exit 1
else
    echo "✓ Clean build: zero test files, credentials, or dev dependencies found."
fi
