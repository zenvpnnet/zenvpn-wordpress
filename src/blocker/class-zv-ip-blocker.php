<?php

namespace zenVPN\Blocker;

use WP_Http;
use zenVPN\Settings\ZV_Settings;

/**
 * Class with functionality of scanning allowed ips' list.
 * Blocking access to requested urls.
 */
class ZV_IP_Blocker implements ZV_IP_Blocker_Interface {

	/**
	 * Block by ip, received from API call.
	 *
	 * @return void
	 */
	public function block_by_ip(): void {
		// Get the settings option from the database.
		$settings = get_option( ZV_PREFIX . 'settings' );

		// Check if the settings option exists and is not empty.
		if ( $settings && ! empty( $settings['token'] ) ) {
			// Get the allowed IP addresses from the zenVPN API using the token value.
			$zenvpn_ips = $this->get_allowed_ip( $settings['token'] );

			// Check if the API response is successful and contains IP addresses.
			if ( $zenvpn_ips && in_array( $zenvpn_ips['code'], array( 200, 204 ) ) && $zenvpn_ips['data'] ) {
				// Block the access if the client IP is not in the allowed IP addresses.
				$this->check_ip( $zenvpn_ips['data'] );
			}
		}
	}

	/**
	 * Block wp file access, by list of blocked urls.
	 *
	 * @return void
	 */
	public function block_wp_file_access(): void {
		$blocked_urls     = self::build_blocked_urls_list();
		$request_url      = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$site_url         = site_url( '', 'https' );
		$referer          = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		$cleaned_site_url = str_replace( array( 'https://', 'http://' ), '', $site_url );
		$cleaned_referer  = str_replace( array( 'https://', 'http://' ), '', $referer );
		$is_ajax          = wp_doing_ajax();

		foreach ( $blocked_urls as $blocked_url ) {
			if ( strpos( $request_url, $blocked_url ) !== false ) {
				if ( ! $is_ajax ) {
					if ( ! strpos( $cleaned_referer, $cleaned_site_url ) !== false ) {
						$this->block_by_ip();
					}
				}
			}
			if ( 'wp-admin' === $blocked_url ) {
				$custom_admin_url = get_option( 'admin_url' );
				if ( $custom_admin_url && $custom_admin_url !== $site_url . 'wp-admin' ) {
					if ( strpos( $request_url, $custom_admin_url ) !== false ) {
						if ( ! $is_ajax ) {
							if ( ! strpos( $cleaned_referer, $cleaned_site_url ) !== false ) {
								$this->block_by_ip();
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Check if client's IP is from zenVPN.
	 *
	 * @param string $ip_address_string user's up address string.
	 * @return void
	 */
	public function check_ip( string $ip_address_string ): void {
		$zv_settings = ZV_Settings::get_instance();
		$user_ip     = $zv_settings->get_user_ip();
		$ip_in_list  = self::compare_ips( $user_ip, $ip_address_string );

		// If client IP is not in zenVPN IPs, deny access with a 403 status code.
		if ( ! $ip_in_list ) {
			wp_die(
				'You are not authorized to access this page.', // Message.
				'403 Forbidden', // Title.
				array( 'response' => 403 ) // Status code.
			);
		}
	}

	/**
	 * Get list of IPs from zenVPN by token.
	 *
	 * @param string $token token string.
	 * @return array<string, string | bool | int>
	 */
	public function get_allowed_ip( string $token ): array {
		// Build request URL with domain name and access token parameters.
		$request_url = self::build_request_url( $token );

		// Create an instance of WP_Http class.
		$http = new WP_Http();

		// Make HTTP GET request to zenVPN API with request URL.
		$response = $http->get( $request_url );

		// Check if response is an array or a WP_Error object.
		if ( is_array( $response ) ) {
			// Get status code and body from HTTP response.
			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );

			switch ( $status_code ) {
				case 200:
					// Extract IP address from response body using regular expression pattern.
					preg_match( '/\[(.*?)\]/', $response_body, $parsed_ip );

					$message = __('Connection with zenVPN successfully established.<br>We can see that you are running zenVPN now on your client, so you can proceed with protecting your WP administrative resources. Please don\'t forget to start zenVPN client on your computer in order to access your /wp-admin directory!', ZV_TEXT_DOMAIN );
					$code    = 200;

					$zv_settings = ZV_Settings::get_instance();
					$user_ip     = $zv_settings->get_user_ip();

					if ( empty( $user_ip ) || ! self::compare_ips( $user_ip, $parsed_ip[1] ) ) {
						$message = __('Connection with zenVPN cloud API established, your account is set up correctly.<br><b>However we can see that you are not using zenVPN client right now. Please connect with your desktop client and refresh that page in order to enable zenVPN plugin services</b>', ZV_TEXT_DOMAIN );
						$code    = 204;
					}
					return array(
						'code'    => $code,
						'data'    => $parsed_ip[1] ?? false,
						'message' => $message,
					);
				case 403:
					return array(
						'code'    => 403,
						'data'    => false,
						'message' => __('Connection with zenVPN cloud could not be established.<br>Please make sure you <b>copy your zenVPN</b> key from your <a href="'.ZEN_VPN_APP.'/profile" target="_blank">account</a> properly and run <a href="'.ZEN_VPN_APP.'/downloads" target="_blank">zenVPN desktop/mobile client</a>', ZV_TEXT_DOMAIN ),
					);
				case 404:
					return array(
						'code'    => 404,
						'data'    => false,
						'message' => __('Connection with zenVPN cloud API established.<br>However we were not able to find a correct set up of the tunnel to your website ', ZV_TEXT_DOMAIN ),
					);
				default:
					return array(
						'code'    => 500,
						'data'    => false,
						'message' => __( 'Connection with zenVPN cloud API failed. Please try again and contact <a href="mailto:support@zenvpn.net?subject=plugin">support@zenvpn.net</a> if it fails again.', ZV_TEXT_DOMAIN ),
					);
			}
		} else {
			return array(
				'code'    => 500,
				'data'    => false,
				'message' => __( 'Connection with zenVPN cloud API failed. Please try again and contact <a href="mailto:support@zenvpn.net?subject=plugin">support@zenvpn.net</a> if it fails again.', ZV_TEXT_DOMAIN ),
			);
		}
	}
	
	/**
	 * Compare client's IP with zenVPN IPs.
	 *
	 * @param string $client_ip clien'ts ip.
	 * @param string $ip_address_string user's up address string.
	 * @return bool
	 */
	public static function compare_ips( string $client_ip, string $ip_address_string ): bool {
		// Remove the double quotes from the IP address string.
		$cleaned_string = str_replace( '"', '', $ip_address_string );

		// Split the IP address string by comma into an array.
		$ip_addresses = explode( ', ', $cleaned_string );

		//Check if the client IP is behind IPV6 proxy, convert to IPV4
		if ( substr( filter_var( $client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ), 0, 9 ) === '64:ff9b::' ) {
			$client_ip = substr( $client_ip, 7 );
			$client_ip = substr(inet_ntop( inet_pton( $client_ip ) ),2);
		}

		return in_array( $client_ip, $ip_addresses, true );
	}

	/**
	 * Get website's domain.
	 *
	 * @return string|null|false
	 */
	private static function get_domain() {
		// Get site URL with https scheme.
		$site_url = get_site_url( null, '', 'https' );

		// Get host part of the URL.
		return wp_parse_url( $site_url, PHP_URL_HOST );
	}

	/**
	 * Build list of blocked urls.
	 *
	 * @return array<int<0, max>, mixed>
	 */
	private static function build_blocked_urls_list(): array {
		// Get the settings option from the database.
		$settings = get_option( ZV_PREFIX . 'settings' );
		// Define an array to store the blocked URLs.
		$blocked_urls = array();
		// Check if the settings option exists and is not empty.
		if ( $settings ) {
			// Loop through each setting in the option.
			foreach ( $settings as $key => $setting ) {
				// Check if the setting key starts with 'protect' and the setting value is true.
				if ( strpos( $key, ZV_PREFIX . 'protect' ) === 0 && $setting ) {
					// Remove the 'protect_' prefix from the key.
					$output = str_replace( ZV_PREFIX . 'protect_', '', $key );
					// Replace the underscores with hyphens in the output.
					$output = str_replace( '_', '-', $output );
					// Append the '.php' extension to the output, except for 'wp-admin'.
					$blocked_url = 'wp-admin' === $output ? $output : $output . '.php';
					// Add the blocked URL to the array.
					$blocked_urls[] = $blocked_url;
				}
			}
		}
		// Return the blocked URLs array.
		return $blocked_urls;
	}

	/**
	 * Build request url by given token.
	 *
	 * @param string $token token string.
	 * @return string
	 */
	private static function build_request_url( string $token ): string {
		// Get domain name from site URL.
		$site_url = self::get_domain();

		// Build request data array with domain name and access token.
		$request_data = array(
			'domain_name'  => 'zenvpn.local' === $site_url ? 'example.com' : $site_url,
			'access_token' => $token,
		);

		// Build request query string from request data array.
		$request_query = http_build_query( $request_data, '', '&', PHP_QUERY_RFC3986 );

		// Build request URL from API URL and request query string.
		return rtrim( ZEN_VPN_API_URL, '/' ) . '/api/v1/get_ips/?' . $request_query;
	}

}
