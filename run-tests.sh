#!/bin/bash
set -e

echo "=========================================="
echo "  1. Running PHPUnit Regression Suite    "
echo "=========================================="
./vendor/bin/phpunit

echo ""
echo "=========================================="
echo "  2. Running Playwright E2E Suite        "
echo "=========================================="
npx playwright test --workers=1

echo ""
echo "=========================================="
echo "  ✓ All test suites passed cleanly!       "
echo "=========================================="