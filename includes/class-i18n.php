<?php

namespace WH\GF\Multicolumn;

class I18n {
	public function __construct() {
		$this->load_plugin_textdomain();
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'gf-form-multicolumn',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
