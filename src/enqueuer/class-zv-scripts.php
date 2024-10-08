<?php

namespace zenVPN\Enqueuer;

/**
 * Class to enqueue scripts and styles of plugin.
 */
class ZV_Scripts implements ZV_Scripts_Interface {

	/**
	 * Enqueue scripts and styles for plugin.
	 *
	 * @param string $hook_suffix current page.
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		// Check if the current page is the plugin's options page.
		if ( 'settings_page_zv_settings' === $hook_suffix ) {
			wp_register_style( ZV_PREFIX . 'style', plugins_url( '../../assets/style.css', __FILE__ ), array(), ZV_VERSION );
			wp_enqueue_style( ZV_PREFIX . 'style' );

			// Enqueue main.js with handle 'zv-script' and dependency 'jquery'.
			wp_enqueue_script( ZV_PREFIX . 'script', plugins_url( '../../assets/main.js', __FILE__ ), array( 'jquery' ), ZV_VERSION, true );
			wp_localize_script(
				ZV_PREFIX . 'script',
				ZV_PREFIX . 'settings_data',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ), // the ajax url.
					'security' => wp_create_nonce( 'zv_save_nonce' ), // the nonce value.
				)
			);
		}
	}
}
