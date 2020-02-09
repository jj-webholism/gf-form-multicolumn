<?php

namespace WH\GF\Multicolumn\Classes;

class WH_GF_Multicolumn_i18n {
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'gf-form-multicolumn',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
