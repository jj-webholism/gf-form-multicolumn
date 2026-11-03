#!/usr/bin/env bash

# Exit immediately if a command exits with a non-zero status
set -e

# ANSI Color Codes for clean output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}==========================================${NC}"
echo -e "${BLUE}  1. Running PHPUnit Formatter Tests      ${NC}"
echo -e "${BLUE}==========================================${NC}"

./vendor/bin/phpunit tests/frontend/FormCurrentOutputTest.php

echo ""
echo -e "${BLUE}==========================================${NC}"
echo -e "${BLUE}  2. Running Playwright E2E Suite         ${NC}"
echo -e "${BLUE}==========================================${NC}"

# Check if --headed flag was passed to the script
if [[ "$*" == *"--headed"* ]]; then
    npx playwright test tests/e2e/form-visual.spec.js --headed
else
    npx playwright test tests/e2e/form-visual.spec.js
fi

echo ""
echo -e "${GREEN}==========================================${NC}"
echo -e "${GREEN}  ✓ All test suites passed cleanly!       ${NC}"
echo -e "${GREEN}==========================================${NC}"