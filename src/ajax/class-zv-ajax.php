<?php

namespace zenVPN\Ajax;

use zenVPN\Blocker\ZV_IP_Blocker;
use zenVPN\Settings\ZV_Settings;

/**
 * Class for Ajax connected functions.
 */
class ZV_AJAX implements ZV_AJAX_Interface {


	/**
	 * Funtion, called from Ajax to save plugin's settings.
	 *
	 * @return void
	 */
	public function save_plugin_settings(): void {
		// Verify the nonce value to prevent CSRF attacks.
		check_ajax_referer( 'zv_save_nonce', 'security' );

		if ( isset( $_POST['zv_settings'] ) ) {
			// Validate and sanitize the settings data from the POST request.
			$settings = self::validate_settings( wp_unslash( $_POST['zv_settings'] ) );

			// Update the option with the settings data.
			update_option( ZV_PREFIX . 'settings', $settings );
		}
	}

	/**
	 * Function, called from Ajax to test connection to zenVPN API with given token.
	 *
	 * @return void
	 */
	public function test_connection(): void {
		// Verify the nonce value to prevent CSRF attacks.
		check_ajax_referer( 'zv_save_nonce', 'security' );

		if ( isset( $_POST['token'] ) ) {
			// Sanitize the token value from the POST request.
			$token = sanitize_text_field( wp_kses_post( wp_unslash( $_POST['token'] ) ) );

			// Create an instance of the ZV_IP_Blocker class.
			$ip_blocker = new ZV_IP_Blocker();

			// Get the allowed IP addresses from the zenVPN API using the token value.
			$result = $ip_blocker->get_allowed_ip( $token );
			if ( $result ) {
				if ( 200 === $result['code'] || 204 === $result['code'] ) {
					$zv_settings      = ZV_Settings::get_instance();
					$ip_settings_data = $zv_settings->get_ip_settings();
					if ( empty( $ip_settings_data ) ) {
						$zv_settings->save_ip_settings( $result['data'] );
						$result = $ip_blocker->get_allowed_ip( $token );
					}
				}
				// Send the result as a JSON response.
				wp_send_json_success( $result );
			} else {
				// Send an error message as a JSON response.
				wp_send_json_error( 'Connection failed' );
			}
		}
	}

	/**
	 * Loads token value from options.
	 * If no token found, then returns empty string.
	 *
	 * @return void
	 */
	public function load_token_value(): void {
		// Verify the nonce value to prevent CSRF attacks.
		check_ajax_referer( 'zv_save_nonce', 'security' );

		$option_value = get_option( ZV_PREFIX . 'settings' );

		$token = '';
		if ( ! empty( $option_value ) && isset( $option_value['token'] ) ) {
			if ( trim( $option_value['token'] ) !== '' ) {
				$token = trim( $option_value['token'] );
			}
		}
		wp_send_json_success( $token );
	}

	/**
	 * Function to validate values, received from Ajax by POST.
	 *
	 * @param array $settings array of settings.
	 * @return array<string, string | bool>
	 */
	private static function validate_settings( array $settings = array() ): array {
		// Define an array to store the validated settings.
		$validated_settings = array();
		// Loop through each setting in the input array.
		foreach ( $settings as $key => $value ) {
			// Remove the prefix and the brackets from the key using regular expressions.
			$key = preg_replace( '/^' . ZV_PREFIX . 'settings\[|\]$/', '', $key );

			// Switch on the key to apply different validation rules.
			switch ( $key ) {
				case 'token':
					$validated_settings[ $key ] = self::check_token( $value );
					break;
				case 'zv_protect_wp_admin':
					$validated_settings[ $key ] = self::check_protection_values( (int) $value );
					break;
				default:
					// Ignore any unknown setting.
					break;
			}
		}
		// Return the validated settings array.
		return $validated_settings;
	}

	/**
	 * Function, to check if UUID is valid.
	 *
	 * @param string $uuid token string.
	 * @return int|false
	 */
	private static function is_valid_uuid( string $uuid ) {
		// Use a regular expression to match the UUID format.
		return preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid );
	}

	/**
	 * Function to check token.
	 *
	 * @param string $token token string.
	 * @return string
	 */
	private static function check_token( string $token ): string {
		// The token setting should be a valid UUID.
		return self::is_valid_uuid( $token ) ? sanitize_text_field( $token ) : '';
	}

	/**
	 * Function to check and transform protection values.
	 *
	 * @param int $value protection value.
	 * @return bool
	 */
	private static function check_protection_values( int $value ): bool {
		// These settings should be either 1 or 0, representing true or false.
		return ( 1 === $value || 0 === $value ) && (bool) $value;
	}
}
