<?php
/**
 * Layout Calculator Test Class.
 *
 * @package WH\GF\Multicolumn\Tests\Formatters
 */

namespace WH\GF\Multicolumn\Tests\Formatters;

use WP_UnitTestCase;
use WH\GF\Multicolumn\Frontend\Formatters\Layout_Calculator;

/**
 * Tests the shared pre-calculation logic for layout rows and columns.
 */
class LayoutCalculatorTest extends WP_UnitTestCase
{

	/**
	 * Tests that a basic 3-column row is calculated correctly.
	 */
	public function test_calculates_three_column_row()
	{
		$form = array(
			'fields' => array(
				array('id' => 1, 'type' => 'text'), // Outside row
				array('id' => 2, 'type' => 'column_start'),
				array('id' => 3, 'type' => 'text'), // Col 1
				array('id' => 4, 'type' => 'column_break'),
				array('id' => 5, 'type' => 'text'), // Col 2
				array('id' => 6, 'type' => 'column_break'),
				array('id' => 7, 'type' => 'text'), // Col 3
				array('id' => 8, 'type' => 'column_end'),
			),
		);

		$calculator = new Layout_Calculator();
		$layout_map = $calculator->calculate($form);

		// Expect Row 1 to have a divisor of 3 (3 columns).
		$this->assertArrayHasKey(1, $layout_map['rows']);
		$this->assertEquals(3, $layout_map['rows'][1]['columns']);
	}

	/**
	 * Tests calculation across multiple rows with different column counts.
	 */
	public function test_calculates_mixed_multi_row_layout()
	{
		$form = array(
			'fields' => array(
				// Row 1: 2 Columns
				array('id' => 1, 'type' => 'column_start'),
				array('id' => 2, 'type' => 'column_break'),
				array('id' => 3, 'type' => 'column_end'),
				// Row 2: 4 Columns
				array('id' => 4, 'type' => 'column_start'),
				array('id' => 5, 'type' => 'column_break'),
				array('id' => 6, 'type' => 'column_break'),
				array('id' => 7, 'type' => 'column_break'),
				array('id' => 8, 'type' => 'column_end'),
			),
		);

		$calculator = new Layout_Calculator();
		$layout_map = $calculator->calculate($form);

		$this->assertEquals(2, $layout_map['rows'][1]['columns']);
		$this->assertEquals(4, $layout_map['rows'][2]['columns']);
	}

	/**
	 * Tests that polymorphic field objects (\GF_Field) are handled correctly.
	 */
	public function test_handles_hydrated_field_objects()
	{
		$form = array(
			'fields' => array(
				(object)array('id' => 1, 'type' => 'column_start'),
				(object)array('id' => 2, 'type' => 'column_break'),
				(object)array('id' => 3, 'type' => 'column_end'),
			),
		);

		// Mock the get_input_type method that Gravity Forms fields use.
		$form['fields'][0] = new class {
			public $id = 1;

			public function get_input_type()
			{
				return 'column_start';
			}
		};

		$calculator = new Layout_Calculator();
		$layout_map = $calculator->calculate($form);

		$this->assertEquals(2, $layout_map['rows'][1]['columns']);
	}
}