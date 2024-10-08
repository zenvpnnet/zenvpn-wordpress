<?php

namespace zenVPN\Settings;

use zenVPN\Blocker\ZV_IP_Blocker;

/**
 * Class for register and render plugin's settings and settings' page.
 */
class ZV_Settings implements ZV_Settings_Interface {


	private array $settings;
	private static ZV_Settings $instance;

	/**
	 * Construct function.
	 *
	 * @param array $settings settings array.
	 */
	private function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get instance of current class.
	 */
	public static function get_instance(): ZV_Settings {
		if ( ! isset( self::$instance ) ) {
			self::init( array() );
		}
		return self::$instance;
	}

	/**
	 * Init function.
	 */
	public static function init( array $settings ): ZV_Settings {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $settings );
		}
		return self::$instance;
	}

	/**
	 * Register plugin setting's option.
	 *
	 * @return void
	 */
	public function register_settings_option(): void {
		register_setting(
			ZV_PREFIX . 'settings',
			ZV_PREFIX . 'settings',
			array( $this, 'validate_settings' )
		);

		register_setting(
			ZV_PREFIX . 'ip_settings',
			ZV_PREFIX . 'ip_settings'
		);
	}

	/**
	 * Register plugin settings' sections.
	 *
	 * @return void
	 */
	public function register_settings_section(): void {
		add_settings_section(
			ZV_PREFIX . 'main_section',
			esc_html__( '', ZV_TEXT_DOMAIN ),
			array( $this, 'main_section_callback' ),
			ZV_PREFIX . 'settings'
		);
	}

	/**
	 * Register settings fields.
	 *
	 * @return void
	 */
	private function register_settings_fields(): void {
		foreach ( $this->settings as $settings_field ) {
			add_settings_field(
				$settings_field['name'],
				'',
				array( $this, $settings_field['callback'] ),
				ZV_PREFIX . 'settings',
				$settings_field['section'],
				$settings_field['args']
			);
		}
	}

	/**
	 * Register token field setting.
	 *
	 * @return void
	 */
	public function register_token_field(): void {
		add_settings_field(
			ZV_PREFIX . 'token',
			esc_html__( 'Token', ZV_TEXT_DOMAIN ),
			array( $this, 'token_field_callback' ),
			ZV_PREFIX . 'settings',
			ZV_PREFIX . 'main_section',
			array(
				'label_for'   => ZV_PREFIX . 'token',
				'class'       => ZV_PREFIX . 'row',
				'description' => esc_html__( 'Enter your zenVPN API key', ZV_TEXT_DOMAIN ),
			)
		);
	}

	/**
	 * Register protect wp-admin field setting.
	 *
	 * @return void
	 */
	public function register_protect_field(): void {
		add_settings_field(
			ZV_PREFIX . 'protect_wp_admin',
			esc_html__( 'Protect files', ZV_TEXT_DOMAIN ),
			array( $this, 'protect_field_callback' ),
			ZV_PREFIX . 'settings',
			ZV_PREFIX . 'main_section',
			array(
				'label_for'   => ZV_PREFIX . 'protect_wp_admin',
				'class'       => ZV_PREFIX . 'row',
				'description' => esc_html__( 'Protect your /wp-admin now', ZV_TEXT_DOMAIN ),
			)
		);
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		$this->register_settings_option();
		$this->register_settings_section();
		$this->register_settings_fields();
	}

	/**
	 * Add plugin's option page.
	 *
	 * @return void
	 */
	public function add_options_page(): void {
		add_options_page(
			esc_html__( 'zenVPN Settings', ZV_TEXT_DOMAIN ),
			esc_html__( 'zenVPN Settings', ZV_TEXT_DOMAIN ),
			'manage_options',
			ZV_PREFIX . 'settings',
			array( $this, 'render_options_page' )
		);
	}

	/**
	 * Render plugin's option page.
	 *
	 * @return void
	 */
	public function render_options_page(): void {
		?>
		<p id="statusMessage" class="notice hidden"></p>
		<h1>
			<?php esc_html_e( 'zenVPN Settings', ZV_TEXT_DOMAIN ); ?>
		</h1>
		<p>
			<?php echo esc_html__( 'In order to use zenVPN service, you need an API key from app.zenvpn.net', ZV_TEXT_DOMAIN ); ?>
		</p>
		<div class="wrap">
			<div class="postbox-container-outer">
				<div class="postbox">
					<div class="inside">
						<form id="settingsForm" method="post" action="options.php">
							<?php
							settings_fields( ZV_PREFIX . 'settings' );
							$this->main_section_callback();
							do_settings_fields( ZV_PREFIX . 'settings', ZV_PREFIX . 'main_section' );
							wp_nonce_field( 'zv_save_nonce' );
							?>
					</div><!-- end inside -->
					</form>
				</div><!-- end postbox -->
				<div class="postbox-container">
					<div class="postbox">
						<div class="inside">
							<h2>
								<?php esc_html_e( 'I\'m new to zenVPN', ZV_TEXT_DOMAIN ); ?>
							</h2>
							<p class="txt-big">
								<?php echo esc_html__( 'Register at zenvpn.net service for free and get your API key for free.', ZV_TEXT_DOMAIN ); ?>
							</p>
							<a href="<?php echo ZEN_VPN_APP; ?>/signup?domain_name=<?php echo str_replace( array( 'https://', 'http://' ), '', get_site_url( null, '', 'https' ) ); ?>"
								class="button button-primary" target="_blank">
								<?php echo esc_html__( 'Register and get API key', ZV_TEXT_DOMAIN ); ?>
							</a>
						</div><!-- end inside -->
					</div><!-- end postbox -->
				</div><!-- end postbox-container -->
			</div><!-- end postbox-container-outer -->
			<div class="postbox">
				<div class="inside">
					<h2>
						<?php esc_html_e( 'zenVPN', ZV_TEXT_DOMAIN ); ?>
					</h2>
					<p>
						<?php echo esc_html__( 'Secure access to your company resources.', ZV_TEXT_DOMAIN ); ?>
					</p>
					<a href="<?php echo ZEN_VPN_WEBSITE; ?>" class="button button-primary" target="_blank">
						<?php echo esc_html__( 'Discover all features', ZV_TEXT_DOMAIN ); ?>
					</a>
					<div class="icon-list-outer">
						<ul class="icon-list">
							<li class="icon-list__item"><span class="dashicons dashicons-shield"></span>
								<?php echo esc_html__( 'Additional layer of protection to your company resources', ZV_TEXT_DOMAIN ); ?>
							</li>
							<li class="icon-list__item"><span class="dashicons dashicons-groups"></span>
								<?php echo esc_html__( 'Hassle-free, zero-config for your team members', ZV_TEXT_DOMAIN ); ?>
							</li>
						</ul>
						<ul class="icon-list">
							<li class="icon-list__item"><span class="dashicons dashicons-lock"></span>
								<?php echo esc_html__( 'Access to classic VPN', ZV_TEXT_DOMAIN ); ?>
							</li>
							<li class="icon-list__item"><span class="dashicons dashicons-randomize"></span>
								<?php echo esc_html__( 'Split tunneling', ZV_TEXT_DOMAIN ); ?>
							</li>
						</ul>
					</div>
				</div><!-- end inside -->
			</div><!-- end postbox -->
		</div><!-- end wrap -->
		<?php
	}

	/**
	 * Unregister plugin's settings. Called on hook deactivation.
	 *
	 * @return void
	 */
	public function unregister_settings(): void {
		unregister_setting(
			ZV_PREFIX . 'settings',
			ZV_PREFIX . 'settings'
		);

		unregister_setting(
			ZV_PREFIX . 'ip_settings',
			ZV_PREFIX . 'ip_settings'
		);

		delete_option( ZV_PREFIX . 'settings' );
		delete_option( ZV_PREFIX . 'ip_settings' );
	}

	/**
	 * Validate settings.
	 *
	 * @param array $input array of settings.
	 * @return array<string, string>
	 */
	public function validate_settings( array $input ): array {
		$output = array();

		if ( isset( $input['token'] ) ) {
			$output['token'] = sanitize_text_field( $input['token'] );
		}

		return $output;
	}

	/**
	 * Callback function for main section.
	 *
	 * @return void
	 */
	public function main_section_callback(): void {
		?>
		<h2>
			<?php echo esc_html__( 'I already have an API key', ZV_TEXT_DOMAIN ); ?>
		</h2>
		<p class="txt-big">
			<?php printf( esc_html__( 'Enter your API key from your %s to connect zenVPN service.', ZV_TEXT_DOMAIN ), '<a href="https://app.zenvpn.net/profile" target="_blank">account page</a>' ); ?>
		</p>
		<?php
	}

	/**
	 * Callback function for token field.
	 *
	 * @param array $args args for token field callback.
	 * @return void
	 */
	public function token_field_callback( array $args ): void {
		$settings    = get_option( ZV_PREFIX . 'settings' );
		$token       = ! empty( $settings['token'] ) ? $settings['token'] : '';
		$field_id    = $args['label_for'];
		$description = $args['description'];
		?>
		<div class="button-container">
			<input type="text" name="<?php echo esc_attr( ZV_PREFIX . 'settings[token]' ); ?>"
				id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( $token ); ?>"
				placeholder="<?php echo esc_attr__( $description, ZV_TEXT_DOMAIN ); ?>" disabled />

			<button id=editButton type=button class="button button-primary">
				<?php echo esc_html__( 'Edit', ZV_TEXT_DOMAIN ); ?>
			</button>

			<button id=testButton type=button class="button button-primary hidden">
				<?php echo esc_html__( 'Test connection', ZV_TEXT_DOMAIN ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Function to render a checkbox field for wp-admin protection settings.
	 *
	 * @param array $args array of arguments for the field.
	 * @return void
	 */
	public function protect_admin_field_callback( array $args ): void {
		// Get the settings option from the database.
		$settings = get_option( ZV_PREFIX . 'settings' );

		// Get the field id and description from the arguments.
		$field_id = $args['label_for'];

		$description = $args['description'] ?? '';

		// Remove the prefix and the brackets from the field id using regular expressions.
		$field_key = preg_replace( '/^' . ZV_PREFIX . 'settings\[|\]$/', '', $field_id );

		// Get the protection value from the settings option or an empty string.
		$protect = ! empty( $settings[ $field_key ] ) ? $settings[ $field_key ] : '';

		// Render the checkbox input and the description paragraph.
		?>
		<div id="optionsBlock" class="hidden">
			<?php if ( ! empty( $description ) ) : ?>
				<h2>
					<?php echo esc_html( $description ); ?>
				</h2>
			<?php endif; ?>
			<label class="switch">
				<input type="checkbox" name="<?php echo esc_attr( ZV_PREFIX . 'settings[' . $field_key . ']' ); ?>"
					id="<?php echo esc_attr( $field_id ); ?>" value="1" <?php checked( $protect, 1 ); ?> />
				<span class="slider round"></span>
			</label>
		</div>
		<div id="buttonsBlock" class="button-container hidden">
			<button id=cancelButton type=button class="button button-primary">
				<?php echo esc_html__( 'Cancel', ZV_TEXT_DOMAIN ); ?>
			</button>

			<button id=saveButton type=button class="button button-primary" disabled>
				<?php echo esc_html__( 'Save', ZV_TEXT_DOMAIN ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Function to save position and source of needed ip.
	 *
	 * @param string $ip_string string with ip address.
	 */
	public function save_ip_settings( string $ip_string ) {
		$result     = array();
		$places     = array(
			'REMOTE_ADDR',
			'X-REAL-IP',
			'True-Client-IP',
			'FORWARDED',
			'HTTP_X_FORWARDED',
			'X-FORWARDED',
			'HTTP_X_FORWARDED_FOR',
			'X-FORWARDED-FOR',
			'HTTP_CLIENT_IP',
			'X-Client-IP',
			'HTTP_CF_CONNECTING_IP',
		);
		$ip_blocker = new ZV_IP_Blocker();
		$is_found   = false;
		foreach ( $places as $place ) {
			if ( ! $is_found && isset( $_SERVER[ $place ] ) ) {
				$result = self::build_result( $place, 'server', $ip_string, $ip_blocker );
				if ( ! empty( $result ) ) {
					$is_found = true;
				}
			}

			if ( ! $is_found && isset( $_SERVER[ 'HTTP_' . $place ] ) ) {
				$result = self::build_result( $place, 'header', $ip_string, $ip_blocker );
				if ( ! empty( $result ) ) {
					$is_found = true;
				}
			}
		}

		update_option( ZV_PREFIX . 'ip_settings', $result );
	}

	/**
	 * Function to build result array.
	 *
	 * @param string        $place name of place, where ip is searched.
	 * @param string        $type name of type.
	 * @param string        $ip_string ip string.
	 * @param ZV_IP_Blocker $ip_blocker IP_Blokcer object.
	 *
	 * @return array empty if not found. With data if found.
	 */
	private static function build_result( string $place, string $type, string $ip_string, ZV_IP_Blocker $ip_blocker ) {
		$value = $_SERVER[ $place ];
		$value = str_replace( '"', '', $value );
		$ips   = explode( ',', $value );
		foreach ( $ips as $index => $ip ) {
			$ip          = trim( $ip );
			$is_valid_ip = $ip_blocker::compare_ips( $ip, $ip_string );
			if ( $is_valid_ip ) {
				$result = array(
					'type'     => $type,
					'name'     => $place,
					'position' => $index,
				);

				return $result;
			}
		}

		return array();
	}

	/**
	 * Get IP settings.
	 */
	public function get_ip_settings() {
		return get_option( ZV_PREFIX . 'ip_settings' );
	}

	/**
	 * Function to get user's ip address.
	 *
	 * @return string
	 */
	public function get_user_ip() {
		$ip_settings = $this->get_ip_settings();
		if ( ! empty( $ip_settings ) ) {
			extract( $ip_settings );
			$key   = 'server' === $type ? $name : 'HTTP_' . $name;
			$value = $_SERVER[ $key ] ?? '';
			$ips   = explode( ',', $value );
			$ip    = $ips[ $position ] ?? '';
			$ip    = trim( $ip );
			return $ip;
		}

		return '';
	}
}
