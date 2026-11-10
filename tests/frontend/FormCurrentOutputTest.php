<?php
/**
 * Test Form_Current frontend HTML structural transformations using fixture forms.
 *
 * @package WH\GF\Multicolumn\Tests
 */

namespace WH\GF\Multicolumn\Tests;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Form_Current;

/**
 * Tests for Form_Current output rendering.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class FormCurrentOutputTest extends WP_UnitTestCase
{

	/**
	 * Helper to load and decode a Gravity Forms JSON fixture.
	 *
	 * @param string $filename
	 * @return array
	 */
	private function get_form_fixture(string $filename): array
	{
		$path = dirname(__DIR__) . '/fixtures/' . $filename;
		if (!file_exists($path)) {
			return array();
		}

		$json = file_get_contents($path);
		$data = json_decode($json, true);

		// Standard Gravity Forms exports wrap the form in an index array [0]
		$form = isset($data[0]) ? $data[0] : $data;

		// Gravity Forms field objects inside runtime hooks are stdClass instances
		if (!empty($form['fields'])) {
			foreach ($form['fields'] as $index => $field) {
				$form['fields'][$index] = (object)$field;
			}
		}

		return $form;
	}

	/**
	 * Helper to load raw HTML fixture matching the form.
	 *
	 * @param string $filename
	 * @return string
	 */
	private function get_html_fixture(string $filename): string
	{
		$path = dirname(__DIR__) . '/fixtures/' . $filename;
		return file_exists($path) ? (string)file_get_contents($path) : '';
	}

	/**
	 * Data provider that maps each fixture file to its expected transformation traits.
	 *
	 * @return array
	 */
	public function provide_form_fixtures(): array
	{
		return array(
			'Applicant Quiz' => array('applicant-quiz.json', 'applicant-quiz.html'),
			'Product Configurator' => array('product-configurator.json', 'product-configurator.html'),
			'Sod Request' => array('sod-request.json', 'sod-request.html'),
			'Webholism Contact' => array('wh-contact.json', 'wh-contact.html'),
		);
	}

	/**
	 * Tests all real form fixtures through Form_Current.
	 *
	 * @dataProvider provide_form_fixtures
	 */
	public function test_form_fixtures_render_correctly(string $json_file, string $html_file)
	{
		$fixture_path = dirname(__DIR__) . '/fixtures/' . $json_file;

		// Skip gracefully if private fixture file is not present in the current environment.
		if (!file_exists($fixture_path)) {
			$this->markTestSkipped(
				sprintf('Fixture "%s" is not present in this environment (skipped for privacy).', $json_file)
			);
			return;
		}

		$form = $this->get_form_fixture($json_file);
		$raw_html = $this->get_html_fixture($html_file);

		if (empty($raw_html)) {
			$raw_html = $this->generate_mock_markup_for_form($form);
		}

		$current = new Form_Current($raw_html, $form);
		$output = $current->get_form();

		$mode = Form_Current::resolve_mode($form);

		// 1. Verify structural wrapper elements based on layout engine.
		if ('grid' === $mode['engine']) {
			$this->assertStringContainsString('gfmc-row', $output, "{$json_file} missing gfmc-row container");
			$this->assertStringContainsString('gfmc-col', $output, "{$json_file} missing gfmc-col container");
		} else {
			$this->assertStringContainsString('gfmc-column', $output, "{$json_file} missing gfmc-column wrapper");
			$this->assertStringContainsString('flex-wrapper', $output, "{$json_file} missing flex-wrapper container");
		}

		// 2. Multi-column marker labels must always be stripped.
		$this->assertStringNotContainsString('Row Start', $output, "{$json_file} did not strip 'Row Start' label");
		$this->assertStringNotContainsString('Row End', $output, "{$json_file} did not strip 'Row End' label");

		// 3. Verify column break handling if defined on the form.
		$has_column_breaks = false;
		if (!empty($form['fields'])) {
			foreach ($form['fields'] as $field) {
				if (isset($field->type) && 'column_break' === $field->type) {
					$has_column_breaks = true;
					break;
				}
			}
		}

		if ($has_column_breaks) {
			$this->assertStringNotContainsString('Column Break', $output, "{$json_file} did not strip 'Column Break' label");
		}
	}

	/**
	 * Fallback generator to simulate GF 2.5+ raw div output if no static HTML snapshot exists yet.
	 */
	private function generate_mock_markup_for_form(array $form): string
	{
		$html = '<div class="gform_fields">';
		if (!empty($form['fields'])) {
			foreach ($form['fields'] as $field) {
				$html .= sprintf(
					'<div id="field_%d_%d" class="gfield"><label>%s</label></div>',
					$form['id'] ?? 1,
					$field->id ?? 0,
					$field->label ?? ''
				);
			}
		}
		$html .= '</div>';
		return $html;
	}
}