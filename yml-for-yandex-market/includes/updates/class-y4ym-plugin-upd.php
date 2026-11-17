<?php defined( 'WPINC' ) || exit;

/**
 * Plugin Updates.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.13.0 (17-11-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/updates
 */

/**
 * Plugin Updates.
 *
 * Depends on the class `ICPD_Set_Admin_Notices` and the constant `Y4YM_PLUGIN_VERSION`.
 *
 * @see        [ 202, 402, 412, 418, 520 ]
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/updates
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Plugin_Upd {

	/**
	 * Base API URL (v1).
	 *
	 * @var string
	 */
	public const API_URL = 'https://icopydoc.ru/api/v1';

	/**
	 * How long a successful API response is cached, sec.
	 * 
	 * @var int
	 */
	public const RESPONSE_TTL = 2 * HOUR_IN_SECONDS;

	/**
	 * How long a failed API request is "cached", sec.
	 * Prevents hammering the API when it is temporarily down.
	 * 
	 * @var int
	 */
	public const FAILURE_TTL = 5 * MINUTE_IN_SECONDS;

	/**
	 * Mapping of plugin prefixes to their promo/license renewal codes.
	 *
	 * Each entry maps a plugin prefix (e.g., `y4ymp`) to an array with:
	 * - `name` – human-readable plugin name
	 * - `code` – promo/renewal code used for licensing
	 *
	 * Used to determine the correct renewal code based on the plugin prefix.
	 *
	 * @var array<string, array{name: string, code: string}>
	 */
	private $list_plugin_names = [
		'y4ymp' => [ 'name' => 'PRO', 'code' => 'renewlicense20yp' ],
		'y4ymae' => [ 'name' => 'Aliexpress Export', 'code' => 'renewlicense20ali' ],
		'y4yms' => [ 'name' => 'SETS', 'code' => 'renewlicense23sets' ]
	];

	/**
	 * Префикс плагина.
	 * 
	 * @var string
	 */
	private $pref;

	/**
	 * Псевдоним плагина (например: oop-wp).
	 * 
	 * @var string
	 */
	private $slug;

	/**
	 * Полный псевдоним плагина (папка плагина + имя главного файла, например: oop-wp/oop-wp.php).
	 * 
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Номер версии плагина.
	 * 
	 * @var string
	 */
	private $premium_version;

	/**
	 * Лицензионный ключ плагина.
	 * 
	 * @var string
	 */
	private $license_key;

	/**
	 * Номер заказа.
	 * 
	 * @var string
	 */
	private $order_id;

	/**
	 * Почта заказа.
	 * 
	 * @var string
	 */
	private $order_email;

	/**
	 * Plugin Updates.
	 * 
	 * @param array $args
	 */
	public function __construct( $args = [] ) {

		$this->pref = $args['pref'];
		$this->slug = $args['slug'];
		$this->plugin_slug = $args['plugin_slug'];
		$this->premium_version = $args['premium_version'];

		$this->list_plugin_names = apply_filters( 'y4ym_f_list_plugin_names', $this->list_plugin_names, $args );
		do_action_ref_array( 'y4ym_a_plugin_upd', $args );
		$this->init_hooks(); // подключим хуки

	}

	/**
	 * Initialization hooks.
	 * 
	 * @uses     add_filter()
	 *
	 * @return   void
	 */
	private function init_hooks() {

		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 4 );

		add_action( 'admin_init', [ $this, 'maybe_check_api' ], 10, 0 );
		add_action( 'y4ym_a_after_activation_form_saved', [ $this, 'refresh_update_info' ], 5, 1 );
		add_action( 'y4ym_a_after_activation_form_saved', [ $this, 'process_api_response' ], 10, 1 );

	}

	/**
	 * Function for `plugin_action_links` filter-hook.
	 * 
	 * @param string[] $actions     An array of plugin action links. By default this can include
	 *                              'activate', 'deactivate', and 'delete'. With Multisite active this
	 *                              can also include 'network_active' and 'network_only' items.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data. See get_plugin_data() and the {@see
	 *                              'plugin_row_meta'} filter for the list of possible values.
	 * @param string   $context     The plugin context. By default this can include 'all', 'active',
	 *                              'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins',
	 *                              and 'search'.
	 *
	 * @return string[]
	 */
	public function add_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {

		// проверка, что у нас текущий плагин
		if ( false === strpos( $plugin_file, $this->get_plugin_slug() ) ) {
			return $actions;
		}
		$i = $this->get_index_status();

		$link = $this->get_upd_notice_html( 'plugins_page' );
		if ( ! empty( $link ) ) {
			array_unshift( $actions, $link );
		}
		switch ( $i ) {
			case "202":

				$message = __( 'License is active', 'yml-for-yandex-market' );
				$color = 'green';

				break;
			case "402":

				$message = __( 'License expired', 'yml-for-yandex-market' );
				$color = '#dc3232';

				break;
			case "412":

				$message = __( 'License data is invalid', 'yml-for-yandex-market' );
				$color = '#dc3232';

				break;
			case "418":

				$message = __(
					'This license cannot be used on this site. The package limit has been exceeded',
					'yml-for-yandex-market'
				);
				$color = '#dc3232';

				break;
			default: // или ошибка 520

				$message = __( 'License data is invalid', 'yml-for-yandex-market' );
				$color = '#dc3232';

				break;
		}
		$settings_link = sprintf( '<span style="color: %s; font-weight: 700;">%s</span>',
			$color,
			$message
		);
		array_unshift( $actions, $settings_link );
		return $actions;

	}

	/**
	 * Check if we're on the plugin admin page and fetch fresh API data.
	 *
	 * Hooked to `admin_init`. When the current admin screen belongs to the
	 * plugin (URL parameter `page` contains `yml-for-yandex-market`), it
	 * retrieves an updated response from the update server and refreshes
	 * the stored plugin info before the admin page renders.
	 *
	 * @uses     $this->process_api_response()
	 * @uses     $this->get_info()
	 *
	 * @return   void
	 */
	public function maybe_check_api() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( false !== strpos( $page, 'yml-for-yandex-market' ) ) {
			$this->process_api_response(); // сначала получаем свежие данные
			$this->get_info();             // потом показываем
		}

	}

	/**
	 * Process API response for update check.
	 *
	 * Sends a request to the update server, decodes the response,
	 * and saves the status code and date. On failure,
	 * logs the error (except for throttled responses).
	 *
	 * @uses     Y4YM_Error_Log::record()
	 * @uses     $this->response_to_api()
	 * @uses     $this->save_resp()
	 *
	 * @return   void
	 */
	public function process_api_response() {

		$response = $this->response_to_api();
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( ( $response_code == 200 ) && $response_message == 'OK' ) {
			$resp = json_decode( $response['body'] );
			if ( null === $resp || ! isset( $resp->status_code, $resp->status_date ) ) {
				Y4YM_Error_Log::record( sprintf( 'ERROR: PREFIX `%1$s` - %2$s.',
					$this->get_pref(),
					__( 'Malformed API response', 'yml-for-yandex-market' )
				) );
				return;
			}

			$this->save_resp( $resp->status_code, $resp->status_date );

			if ( isset( $resp->upd ) && 'true' == $resp->upd ) {// ! важно у нас `true` тип string 
				$this->save_upd_info( $resp );
			} else {
				// No update available — nothing to change. Exit early.
				Y4YM_Options::delete( 'y4ym_upd_info_' . $this->get_pref() );
				return;
			}

		} else {
			if ( is_wp_error( $response ) ) {
				if ( 'y4ym_api_throttled' === $response->get_error_code() ) {
					return; // throttle active — normal state, do not log
				}
				Y4YM_Error_Log::record( sprintf( 'ERROR: PREFIX `%1$s` - %2$s (%3$s). %4$s: %5$s; %6$s: %7$s',
					$this->get_pref(),
					__( 'Error checking for updates', 'yml-for-yandex-market' ),
					$response->get_error_message(),
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-plugin-upd.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
			} else {
				Y4YM_Error_Log::record( sprintf( 'ERROR: PREFIX `%1$s` - #%2$s: %3$s. %4$s; %5$s: %6$s; %7$s: %8$s',
					$this->get_pref(),
					$response_code,
					__( 'Error checking for updates', 'yml-for-yandex-market' ),
					$response_message,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-plugin-upd.php',
					__( 'Line', 'yml-for-yandex-market' ),
					__LINE__
				) );
			}
		}

	}

	/**
	 * Get info.
	 * 
	 * @return void
	 */
	public function get_info() {

		$c = $this->get_index_status();

		$notice = $this->get_upd_notice_html( 'admin_notice' );
		if ( ! empty( $notice ) ) {
			new Y4YM_Set_Admin_Notices( $notice, 'info' );
		}

		$message = '';
		switch ( $c ) {
			case "202":
				break;
			case "402":

				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! %3$s, <a href="https://icopydoc.ru/product/%4$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%4$s&utm_content=notice&utm_term=license-expired" target="_blank">%5$s</a> (%6$s: <span style="font-weight: 700;">%7$s</span>). %8$s <a href="%11$sadmin.php?page=%9$s">%10$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License expired', 'yml-for-yandex-market' ),
					__( 'Please', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new license', 'yml-for-yandex-market' ),
					__( 'to get a discount, use this promo code', 'yml-for-yandex-market' ),
					$this->list_plugin_names[ $this->get_pref()]['code'],
					__( 'If you have already done this', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium',
					__( 'enter the new license information here', 'yml-for-yandex-market' ),
					admin_url()
				);

				break;
			case "412":

				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! %1$s %3$s. <a href="%9$sadmin.php?page=%8$s">%4$s</a> %5$s <a href="https://icopydoc.ru/product/%6$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%6$s&utm_content=notice&utm_term=license-invalid" target="_blank">%7$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License data is invalid', 'yml-for-yandex-market' ),
					__( 'version features do not work and you can not install updates', 'yml-for-yandex-market' ),
					__( 'Enter your license information', 'yml-for-yandex-market' ),
					__( 'or', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new one', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium',
					admin_url()
				);

				break;
			case "418":

				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! <a href="%8$sadmin.php?page=%7$s">%3$s</a> %4$s <a href="https://icopydoc.ru/product/%5$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%5$s&utm_content=notice&utm_term=license-limit" target="_blank">%6$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__(
						'This license cannot be used on this site. The package limit has been exceeded',
						'yml-for-yandex-market'
					),
					__( 'Enter your license information', 'yml-for-yandex-market' ),
					__( 'or', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new one', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium',
					admin_url()
				);

				break;
			default: // или ошибка 520

				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s! %1$s %3$s. <a href="%9$sadmin.php?page=%8$s">%4$s</a> %5$s <a href="https://icopydoc.ru/product/%6$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%6$s&utm_content=notice&utm_term=license-err" target="_blank">%7$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License data is invalid', 'yml-for-yandex-market' ),
					__( 'version features do not work and you can not install updates', 'yml-for-yandex-market' ),
					__( 'Enter your license information', 'yml-for-yandex-market' ),
					__( 'or', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new one', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium',
					admin_url()
				);

				break;
		}

		if ( ! empty( $message ) ) {
			$class = 'error';
			new Y4YM_Set_Admin_Notices( $message, $class );
		}

		if ( $c !== '0' ) {
			$remaining_seconds = $c - (int) current_time( 'timestamp' );
			$remaining_days = ceil( ( $remaining_seconds / ( 24 * 60 * 60 ) ) );
			if ( $remaining_days > 0 && $remaining_days < 8 ) {
				$message = sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s <span style="font-weight: 700; color: red;">%3$s</span>. %4$s, <a href="https://icopydoc.ru/product/%5$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%5$s&utm_content=notice&utm_term=license-remaining" target="_blank">%6$s</a> (%7$s: <span style="font-weight: 700;">%8$s</span>). %9$s <a href="%12$sadmin.php?page=%10$s">%11$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'License expires in', 'yml-for-yandex-market' ),
					sprintf(
						_n( '%s day', '%s days', $remaining_days, 'yml-for-yandex-market' ),
						$remaining_days
					),
					__( 'Please', 'yml-for-yandex-market' ),
					$this->get_slug(),
					__( 'purchase a new license', 'yml-for-yandex-market' ),
					__( 'to get a discount, use this promo code', 'yml-for-yandex-market' ),
					$this->list_plugin_names[ $this->get_pref()]['code'],
					__( 'If you have already done this', 'yml-for-yandex-market' ),
					__( 'enter the new license information here', 'yml-for-yandex-market' ),
					'yml-for-yandex-market-debug&action=edit&current_display=debug_page&tab=premium',
					admin_url()
				);
				if ( ! empty( $message ) ) {
					$class = 'error';
					new Y4YM_Set_Admin_Notices( $message, $class );
				}
			}
		}

	}

	/**
	 * Get the installed premium version, e.g. from get_plugin_data().
	 * 
	 * @return string|null
	 */
	private function get_installed_pro_version() {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$data = get_plugins()[ $this->plugin_slug ] ?? null;
		return $data['Version'] ?? null;

	}

	/**
	 * Build a manual update URL, validated by the API server at request time.
	 *
	 * @return string
	 */
	private function get_manual_update_url() {

		$api_url = apply_filters( 'y4ym_f_api_url', self::API_URL );
		return sprintf(
			'%1$s/update/?order_id=%2$s&order_email=%3$s&order_home_url=%4$s&slug=%5$s&premium_version=%6$s&basic_version=%7$s',
			esc_url_raw( $api_url ),
			rawurlencode( $this->get_order_id() ),
			rawurlencode( $this->get_order_email() ),
			rawurlencode( home_url( '/' ) ),
			rawurlencode( $this->get_slug() ),
			rawurlencode( $this->get_premium_version() ),
			rawurlencode( Y4YM_PLUGIN_VERSION )
		);

	}

	/**
	 * Get HTML notice about the new premium version.
	 *
	 * @param string $context Where the notice is shown: 'plugins_page' or 'admin_notice'.
	 *
	 * @return string Empty string if there is nothing to show.
	 */
	private function get_upd_notice_html( $context = 'admin_notice' ) {

		$info = Y4YM_Options::get( 'y4ym_upd_info_' . $this->get_pref(), [] );
		if ( empty( $info['version'] ) ) {
			return '';
		}

		$installed = $this->get_installed_pro_version();
		if ( null === $installed
			|| ! version_compare( $info['version'], (string) $installed, '>' ) ) {
			return '';
		}

		$i = $this->get_index_status();

		switch ( $i ) {
			case '202':
				if ( 'plugins_page' === $context ) {
					// бейдж на странице плагинов
					return sprintf(
						'<a href="%1$s" target="_blank" style="font-weight: 700;">%2$s v%3$s</a>',
						esc_url( $this->get_manual_update_url() ),
						esc_html__( 'New version available', 'yml-for-yandex-market' ),
						esc_html( $info['version'] )
					);
				}
				// нотис
				return sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> <a href="%2$s" target="_blank" style="font-weight: 700;">%3$s v%4$s</a>',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					esc_url( $this->get_manual_update_url() ),
					esc_html__( 'New version available', 'yml-for-yandex-market' ),
					esc_html( $info['version'] )
				);
			case '402':
			case '412':
				if ( 'plugins_page' === $context ) {
					return sprintf(
						'%1$s v%2$s. <a href="https://icopydoc.ru/product/%3$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%3$s&utm_content=plugins-page&utm_term=new-version-available" target="_blank" style="font-weight: 700;">%4$s</a>',
						esc_html__( 'New version available', 'yml-for-yandex-market' ),
						esc_html( $info['version'] ),
						esc_attr( $this->get_slug() ),
						esc_html__( 'Renew your license to get it', 'yml-for-yandex-market' )
					);
				}
				return sprintf(
					'<span style="font-weight: 700;">YML for Yandex Market %1$s:</span> %2$s v%3$s, <a href="https://icopydoc.ru/product/%4$s/?utm_source=yml-for-yandex-market&utm_medium=renewal&utm_campaign=%4$s&utm_content=notice&utm_term=renew-your-license-to-get-it" target="_blank">%5$s</a>.',
					$this->list_plugin_names[ $this->get_pref()]['name'],
					__( 'New version available', 'yml-for-yandex-market' ),
					esc_html( $info['version'] ),
					$this->get_slug(),
					__( 'Renew your license to get it', 'yml-for-yandex-market' )
				);
			default:
				return '';
		}

	}

	/**
	 * Save info about the new premium version.
	 * 
	 * @param object $upd
	 * 
	 * @return void
	 */
	private function save_upd_info( $upd ) {

		$info = [
			'version' => sanitize_text_field( (string) $upd->version ),
			'date' => sanitize_text_field( (string) $upd->last_updated ),
			'check_time' => time()
		];
		Y4YM_Options::update( 'y4ym_upd_info_' . $this->get_pref(), $info, false );

	}

	/**
	 * Get body request.
	 * 
	 * @return array
	 */
	private function get_body_request() {

		$body_request = [
			'action' => 'basic_check',
			'slug' => $this->get_slug(),
			'plugin_slug' => $this->get_plugin_slug(),
			'premium_version' => $this->get_premium_version(),
			'basic_version' => Y4YM_PLUGIN_VERSION,
			'license_key' => $this->get_license_key(),
			'order_id' => $this->get_order_id(),
			'order_email' => $this->get_order_email(),
			'order_home_url' => home_url( '/' )
		];
		return $body_request;

	}

	/**
	 * Get prefix.
	 * 
	 * @return string
	 */
	private function get_pref() {
		return $this->pref;
	}

	/**
	 * Get slug.
	 * 
	 * @return string
	 */
	private function get_slug() {
		return $this->slug;
	}

	/**
	 * Get plugin slug.
	 * 
	 * @return string
	 */
	private function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Get premium version.
	 * 
	 * @return string
	 */
	private function get_premium_version() {
		return $this->premium_version;
	}

	/**
	 * Get license key.
	 * 
	 * @return string
	 */
	private function get_license_key() {

		$order_email = $this->get_pref() . '_license_key';
		return Y4YM_Options::settings_get( $order_email );

	}

	/**
	 * Get order ID.
	 * 
	 * @return string
	 */
	private function get_order_id() {

		$order_email = $this->get_pref() . '_order_id';
		return Y4YM_Options::settings_get( $order_email );

	}

	/**
	 * Get order email.
	 * 
	 * @return string
	 */
	private function get_order_email() {

		$order_email = $this->get_pref() . '_order_email';
		return Y4YM_Options::settings_get( $order_email, '' );

	}

	/**
	 * Get response to an API request.
	 *
	 * The request is sent no more than once per RESPONSE_TTL (2 hours).
	 * A successful response is stored in a transient and reused until it expires.
	 * A failed request sets a short-lived transient (FAILURE_TTL) so that a
	 * temporarily unavailable API is not hammered on every update check.
	 *
	 * @return WP_Error|array
	 */
	private function response_to_api() {

		global $wp_version;

		$transient_key = $this->get_cache_transient_key();

		// Затвор: если ответ уже в кэше — ничего не отправляем.
		$cached = get_transient( $transient_key );
		if ( false !== $cached ) {
			// короткий затвор после неудачи
			if ( is_array( $cached ) && isset( $cached['y4ym_api_fail'] ) ) {
				return new WP_Error( 'y4ym_api_throttled', 'Previous API request failed; throttled' );
			}
			return $cached; // успешный кэш
		}

		$request_arr = [
			'timeout' => 5,
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
			'body' => [ 'request' => $this->get_body_request() ]
		];
		$api_url = apply_filters( 'y4ym_f_api_url', self::API_URL );
		$response = wp_remote_post( esc_url_raw( $api_url ), $request_arr );

		if ( is_wp_error( $response ) ) {
			$response = $this->response_to_reserved_servers( $request_arr );
		}

		// Кэшируем только успешные ответы — на полную длительность.
		if ( ! is_wp_error( $response )
			&& wp_remote_retrieve_response_code( $response ) === 200 ) {
			set_transient( $transient_key, $response, self::RESPONSE_TTL );
		} else {
			// НЕ храним false — иначе затвор не отличит его от отсутствия транзиента
			set_transient( $transient_key, [ 'y4ym_api_fail' => time() ], self::FAILURE_TTL );
		}

		return $response;

	}

	/**
	 * Returns the transient key used to throttle API requests.
	 *
	 * @return string
	 */
	private function get_cache_transient_key() {
		return 'y4ym_upd_api_resp_' . $this->get_pref();
	}

	/**
	 * Forces the next update check to hit the API immediately,
	 * bypassing the 2-hour throttle.
	 *
	 * Call it after the user changes license settings so that
	 * the new data reaches the API right away.
	 *
	 * @return   void
	 */
	public function refresh_update_info() {
		delete_transient( $this->get_cache_transient_key() );
	}

	/**
	 * Reserved response to an API request.
	 * 
	 * @param    array             $request_arr
	 * 
	 * @return   WP_Error|array
	 */
	private function response_to_reserved_servers( $request_arr ) {

		$backup_servers_arr = [
			'https://icopydoc.com/api/v1',
			'https://icopydoc.com/api/v2'
		];

		$first_api_error = null;
		foreach ( $backup_servers_arr as $url ) {
			$response = wp_remote_post( esc_url_raw( $url ), $request_arr );
			if ( ! is_wp_error( $response ) ) {
				return $response;
			}
			// запоминаем первую ошибку
			if ( ! $first_api_error ) {
				$first_api_error = $response;
			}
		}

		return $first_api_error ?? new WP_Error( 'y4ym_api_all_servers_failed', 'All backup servers failed' );

	}

	/**
	 * Save resp.
	 * 
	 * @param    string|int    $v
	 * @param    string|int    $d
	 * 
	 * @return   void
	 */
	private function save_resp( $v, $d ) {

		$v = sanitize_text_field( (string) $v );
		$d = sanitize_text_field( (string) $d );
		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), $this->get_status_option_name(), $v );
			update_blog_option( get_current_blog_id(), $this->get_date_option_name(), $d );
		} else {
			update_option( $this->get_status_option_name(), $v );
			update_option( $this->get_date_option_name(), $d );
		}

	}

	/**
	 * Get the status option name (lightly obfuscated on purpose).
	 *
	 * @return string
	 */
	private function get_status_option_name() {
		return 'woo_ho' . 'ok_isc' . $this->get_pref();
	}

	/**
	 * Get the date option name (lightly obfuscated on purpose).
	 *
	 * @return string
	 */
	private function get_date_option_name() {
		return 'woo_ho' . 'ok_isd' . $this->get_pref();
	}

	/**
	 * Get the status code.
	 *
	 * @return string
	 */
	private function get_index_status() {
		return (string) Y4YM_Options::settings_get( $this->get_status_option_name(), '0' );
	}

}
