<?php

/**
 * Version and Documentation Consistency Test.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Validates that version numbers and changelog entries are synchronised across the project.
 */
class VersionConsistencyTest extends TestCase
{

	/**
	 * Root directory of the plugin.
	 *
	 * @var string
	 */
	private string $root_dir;

	/**
	 * Sets up the base plugin root path.
	 */
	protected function setUp(): void
	{
		parent::setUp();
		$this->root_dir = dirname(__DIR__, 2);
	}

	/**
	 * Verifies that the authoritative plugin version matches all supporting files.
	 */
	public function test_version_is_consistent_across_all_files(): void
	{
		// 1. Extract authoritative version from the main plugin header.
		$main_file = $this->root_dir . '/gf-form-multicolumn.php';
		$this->assertFileExists($main_file);
		$main_content = (string)file_get_contents($main_file);

		preg_match('/\*\s*Version:\s*([0-9]+\.[0-9]+\.[0-9]+)/i', $main_content, $header_matches);
		$this->assertNotEmpty($header_matches, 'Could not find "Version: x.x.x" in main plugin header.');
		$expected_version = $header_matches[1];

		// 2. Check GF_MC_VERSION constant if defined in the main file.
		if (preg_match("/define\(\s*['\"]GF_MC_VERSION['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $main_content, $const_matches)) {
			$this->assertSame(
				$expected_version,
				$const_matches[1],
				'GF_MC_VERSION constant does not match the plugin header version.'
			);
		}

		// 3. Check readme.txt Stable tag.
		$readme_file = $this->root_dir . '/readme.txt';
		$this->assertFileExists($readme_file);
		$readme_content = (string)file_get_contents($readme_file);

		preg_match('/Stable tag:\s*([0-9]+\.[0-9]+\.[0-9]+)/i', $readme_content, $readme_matches);
		$this->assertNotEmpty($readme_matches, 'Could not find "Stable tag: x.x.x" in readme.txt.');
		$this->assertSame(
			$expected_version,
			$readme_matches[1],
			'readme.txt Stable tag does not match the plugin header version.'
		);

		// 4. Ensure readme.txt has a changelog block matching the current version.
		$this->assertMatchesRegularExpression(
			'/=\s*' . preg_quote($expected_version, '/') . '\s*=/m',
			$readme_content,
			"readme.txt is missing a changelog header for version {$expected_version} (expected '= {$expected_version} =')."
		);

		// 5. Ensure changelog.txt exists and has an entry matching the current version.
		$changelog_file = $this->root_dir . '/changelog.txt';
		$this->assertFileExists($changelog_file);
		$changelog_content = (string)file_get_contents($changelog_file);

		$this->assertMatchesRegularExpression(
			'/=\s*' . preg_quote($expected_version, '/') . '\s*=/m',
			$changelog_content,
			"changelog.txt is missing an entry header for version {$expected_version} (expected '= {$expected_version} =')."
		);

		// 6. Check package.json if present.
		$package_file = $this->root_dir . '/package.json';
		if (file_exists($package_file)) {
			$package_data = json_decode((string)file_get_contents($package_file), true);
			if (isset($package_data['version'])) {
				$this->assertSame(
					$expected_version,
					$package_data['version'],
					'package.json version does not match the plugin header version.'
				);
			}
		}

		// 7. Check composer.json if a version property is defined.
		$composer_file = $this->root_dir . '/composer.json';
		if (file_exists($composer_file)) {
			$composer_data = json_decode((string)file_get_contents($composer_file), true);
			if (isset($composer_data['version'])) {
				$this->assertSame(
					$expected_version,
					$composer_data['version'],
					'composer.json version does not match the plugin header version.'
				);
			}
		}
	}
}