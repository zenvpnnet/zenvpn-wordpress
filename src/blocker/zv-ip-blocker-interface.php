<?php

namespace zenVPN\Blocker;

interface ZV_IP_Blocker_Interface {


	/**
	 * Block by ip.
	 *
	 * @return void
	 */
	public function block_by_ip(): void;

	/**
	 * Get allowed ips list.
	 *
	 * @param string $token token string.
	 * @return array<string, string | bool | int>
	 */
	public function get_allowed_ip( string $token ): array;

	/**
	 * Check if ip is allowed.
	 *
	 * @param string $ip_address_string user's up address string.
	 * @return void
	 */
	public function check_ip( string $ip_address_string ): void;

	/**
	 * Block access to given list of files.
	 *
	 * @return void
	 */
	public function block_wp_file_access(): void;

		/**
	 * Compare client's IP with zenVPN IPs.
	 *
	 * @param string $client_ip clien'ts ip.
	 * @param string $ip_address_string user's up address string.
	 * @return bool
	 */
	public static function compare_ips( string $client_ip, string $ip_address_string ): bool;

}
