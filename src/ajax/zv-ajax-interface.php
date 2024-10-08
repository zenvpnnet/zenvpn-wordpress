<?php

namespace zenVPN\Ajax;

interface ZV_AJAX_Interface {


	/**
	 * Save plugin settings action.
	 *
	 * @return void
	 */
	public function save_plugin_settings(): void;

	/**
	 * Test connection action.
	 *
	 * @return void
	 */
	public function test_connection(): void;
}
