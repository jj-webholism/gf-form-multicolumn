<?php

namespace WH\GF\Multicolumn\Classes;

use GFAPI;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since       3.0.4
 * Class        WH_GF_Multicolumn_Activator
 * @package     WH\GF\Multicolumn\Classes
 * @subpackage  gf-form-multicolumn/includes
 * @author      jj <jj@webholism.com>
 */
class WH_GF_Multicolumn_Activator {
	public function activate() {
		$forms = GFAPI::get_forms();
		foreach ( $forms as $form ) {
			if ( ! isset ( $form['gfmc_enable_css'] ) ) {
				// Add the gfmc_enable_css option and set it to 1
				$form['gfmc_enable_css'] = 1;
			}
			if ( ! isset ( $form['gfmc_enable_js'] ) ) {
				// Add the gfmc_enable_js option and set it to 1
				$form['gfmc_enable_js'] = 0;
			}
			GFAPI::update_form( $form );
		}
	}
}
