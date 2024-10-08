<?php

namespace zenVPN\Settings;

interface ZV_Settings_Interface {
	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings(): void;

	/**
	 * Validate received settings.
	 *
	 * @param array $input array of settings.
	 * @return array<string, string>
	 */
	public function validate_settings( array $input ): array;

	/**
	 * Add option page.
	 *
	 * @return void
	 */
	public function add_options_page(): void;

	/**
	 * Render option's page markup.
	 *
	 * @return void
	 */
	public function render_options_page(): void;

	/**
	 * Unregister settings.
	 *
	 * @return void
	 */
	public function unregister_settings(): void;
}
