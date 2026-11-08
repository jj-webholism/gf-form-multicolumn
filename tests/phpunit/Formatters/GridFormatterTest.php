<?php
/**
 * Grid Formatter Test Class.
 *
 * @package WH\GF\Multicolumn\Tests\Formatters
 */

namespace WH\GF\Multicolumn\Tests\Formatters;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Formatters\Grid_Formatter;

/**
 * Tests the Grid Formatter layout transformation.
 */
class GridFormatterTest extends WP_UnitTestCase
{

	/**
	 * Tests that legacy markers are converted to a CSS Grid container.
	 */
	public function test_formats_grid_row_and_removes_markers()
	{
		$form = array(
			'id' => 4,
			'fields' => array(
				array('id' => 1, 'type' => 'column_start'),
				array('id' => 2, 'type' => 'text'),
				array('id' => 3, 'type' => 'column_break'),
				array('id' => 4, 'type' => 'text'),
				array('id' => 5, 'type' => 'column_end'),
			),
		);

		$html = '<div id="field_4_1" class="gfield">Row Start Label</div>' .
			'<div id="field_4_2" class="gfield">Field 1</div>' .
			'<div id="field_4_3" class="gfield">Column Break Label</div>' .
			'<div id="field_4_4" class="gfield">Field 2</div>' .
			'<div id="field_4_5" class="gfield">Row End Label</div>';

		$formatter = new Grid_Formatter();
		$output = $formatter->format($html, $form);

		// The raw marker labels should be completely stripped from the output.
		$this->assertStringNotContainsString('Row Start Label', $output);
		$this->assertStringNotContainsString('Column Break Label', $output);
		$this->assertStringNotContainsString('Row End Label', $output);

		// The output should contain the CSS Grid row wrapper and column elements.
		$this->assertStringContainsString('gfmc-row', $output);
		$this->assertStringContainsString('--gfmc-cols:2', $output);
		$this->assertStringContainsString('gfmc-col', $output);
		$this->assertStringContainsString('Field 1', $output);
		$this->assertStringContainsString('Field 2', $output);
	}
}