<?php

/**
 * Set and Get the Plugin Data.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.5.0 (19-05-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/core
 */

/**
 * Set and Get the Plugin Data.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/core
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Data {

	/**
	 * Plugin options array.
	 *
	 * @access private
	 * @var array
	 */
	private $data_arr = [];

	/**
	 * Cached default settings array.
	 *
	 * Contains the full structure of plugin parameters (tab options, field types, default values).
	 * Populated on the first call to `get_default_settings()` and refreshed no more than once every N seconds.
	 *
	 * @var array|null
	 */
	private static $default_cache = null;

	/**
	 * Timestamp of the last cache update.
	 *
	 * Used to control the freshness of data in `self::$default_cache`.
	 * Helps avoid frequent calls to get_users() and rebuilding the large configuration array.
	 *
	 * @var int|null
	 */
	private static $default_cache_time = null;

	/**
	 * Set and Get the Plugin Data.
	 * 
	 * @param array $data_arr
	 */
	public function __construct( $data_arr = [] ) {

		if ( empty( $data_arr ) ) {
			$this->data_arr = self::get_default_settings();
		} else {
			$this->data_arr = $data_arr;
		}

		// Fallback на случай ошибки
		if ( empty( $this->data_arr ) ) {
			$this->data_arr = [];
		}

		$this->data_arr = apply_filters( 'y4ym_f_set_default_feed_settings_result_arr', $this->get_data_arr() );

	}

	/**
	 * Retrieves default settings with caching.
	 *
	 * Cache is refreshed no more than once every 120 seconds.
	 *
	 * @return array The default plugin settings.
	 */
	private static function get_default_settings() {

		$now_time = time();

		// Refresh cache every 2 minutes
		if ( null === self::$default_cache || $now_time - self::$default_cache_time > 120 ) {
			self::$default_cache_time = $now_time;
			self::$default_cache = self::get_default_data();
		}

		return self::$default_cache;

	}

	/**
	 * Get the plugin default data.
	 * 
	 * @return array
	 */
	public static function get_default_data() {

		// Get registered image sizes 
		$registered_image_sizes = self::get_registered_image_sizes();

		include_once __DIR__ . '/default-data.php';
		return $data_arr;

	}

	/**
	 * Get the plugin data array.
	 * 
	 * @return array
	 */
	public function get_data_arr() {
		return $this->data_arr;
	}

	/**
	 * Get options by name.
	 * 
	 * @param array $options_name_arr
	 * 
	 * @return array Example: `array([0] => opt_key1, [1] => opt_key2, ...)`.
	 */
	public function get_options( $options_name_arr = [] ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) && ! empty( $options_name_arr ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				if ( in_array( $this->get_data_arr()[ $i ]['opt_name'], $options_name_arr ) ) {
					$arr = $this->get_data_arr()[ $i ];
					$res_arr[] = $arr;
				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get data for tabs.
	 * 
	 * @param string $tab_name Maybe: `main_tab`, `offer_data_tab`, `filtration_tab`, `offer_data_tab`,
	 * `shop_data_tab` and so on.
	 * 
	 * @return array Example: `array([0] => opt_key1, [1] => opt_key2, ...)`.
	 */
	public function get_data_for_tabs( $tab_name = '' ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $tab_name ) {
					case "main_tab":
					case "shop_data_tab":
					case "offer_data_tab":
					case "filtration_tab":

						if ( $this->get_data_arr()[ $i ]['tab'] === $tab_name ) {
							$arr = $this->get_data_arr()[ $i ];
							$res_arr[] = $arr;
						}

						break;
					default:

						$res_arr = apply_filters(
							'y4ym_f_data_for_tabs_before',
							$res_arr,
							$tab_name, $this->get_data_arr()[ $i ]
						);

						if ( $this->get_data_arr()[ $i ]['tab'] === $tab_name ) {
							$arr = $this->get_data_arr()[ $i ];
							$res_arr[] = $arr;
						}

						$res_arr = apply_filters(
							'y4ym_f_data_for_tabs_after',
							$res_arr,
							$tab_name, $this->get_data_arr()[ $i ]
						);

				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get plugin options name.
	 * 
	 * @param string $whot Maybe: `all`, `public` or `private`.
	 * 
	 * @return array Example: `array([0] => opt_key1, [1] => opt_key2, ...)`.
	 */
	public function get_opts_name( $whot = '' ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
						}
						break;
					default:
						$res_arr[] = $this->get_data_arr()[ $i ]['opt_name'];
				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get plugin options name and default date (array).
	 * 
	 * @param string $whot Maybe: `all`, `public` or `private`.
	 * 
	 * @return array Example: `array(opt_name1 => opt_val1, opt_name2 => opt_val2, ...)`.
	 */
	public function get_opts_name_and_def_date( $whot = 'all' ) {

		$res_arr = [];
		if ( ! empty( $this->get_data_arr() ) ) {
			for ( $i = 0; $i < count( $this->get_data_arr() ); $i++ ) {
				switch ( $whot ) {
					case "public":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'public' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					case "private":
						if ( $this->get_data_arr()[ $i ]['mark'] === 'private' ) {
							$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
						}
						break;
					default:
						$res_arr[ $this->get_data_arr()[ $i ]['opt_name'] ] = $this->get_data_arr()[ $i ]['def_val'];
				}
			}
		}
		return $res_arr;

	}

	/**
	 * Get plugin options name and default date (stdClass object).
	 * 
	 * @param string $whot
	 * 
	 * @return array<stdClass>
	 */
	public function get_opts_name_and_def_date_obj( $whot = 'all' ) {

		$source_arr = $this->get_opts_name_and_def_date( $whot );

		$res_arr = [];
		foreach ( $source_arr as $key => $value ) {
			$obj = new stdClass();
			$obj->name = $key;
			$obj->opt_def_value = $value;
			$res_arr[] = $obj; // unit obj
			unset( $obj );
		}
		return $res_arr;

	}

	/**
	 * Get array for the `y4ym_picture` plugin option.
	 * 
	 * @return array
	 */
	public static function get_registered_image_sizes() {

		$res_arr = [
			[ 'value' => 'disabled', 'text' => __( 'Disabled', 'yml-for-yandex-market' ) ],
			[ 'value' => 'full', 'text' => __( 'Full size (default)', 'yml-for-yandex-market' ) ]
		];
		$sizes = wp_get_registered_image_subsizes();
		foreach ( $sizes as $key => $val ) {
			if ( is_array( $val['crop'] ) ) {
				$crop = '';
			} else {
				$crop = sprintf( ' - %s',
					__( 'сrop thumbnail to exact dimensions', 'yml-for-yandex-market' )
				);
			}
			$cur_size_arr = [
				'value' => $key,
				'text' => sprintf( '%sx%s%s (%s)', $val['width'], $val['height'], $crop, $key )
			];
			array_push( $res_arr, $cur_size_arr );
			unset( $cur_size_arr );
		}
		return $res_arr;

	}

}