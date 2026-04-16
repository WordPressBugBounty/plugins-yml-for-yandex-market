<?php defined( 'ABSPATH' ) || exit;
// 0.1.0 (04-02-2025)
// Maxim Glazunov (https://icopydoc.ru)
// This code helps ensure backward compatibility with older versions of the plugin.
// 'y4ym' - slug for translation (be sure to make an autocorrect)

/**
 * Функция обеспечивает правильность данных, чтобы не валились ошибки и не зависало.
 * 
 * @since 0.1.0
 * 
 */
function sanitize_variable_from_yml( $args, $p = 'yfymp' ) {

	$is_string = Y4YM_Options::settings_get( 'woo' . '_hoo' . 'k_isc' . $p );
	if ( $is_string == '202' && $is_string !== $args ) {
		return true;
	} else {
		return false;
	}

}

if ( ! function_exists( 'common_option_add' ) ) {
	/**
	 * Adds an element to the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a add_blog_option or add_option. Also, this function can work as
	 * add_blog_option or add_option. To do this, DO NOT pass the SLUG.
	 * 
	 * @since 1.0.0
	 * @version 1.1.9 (22-10-2024)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param mixed $value
	 * @param string|bool $autoload Maybe: `yes`|`no` or `true`|`false`.
	 * @param string $feed_id Feed ID (key) in the array of common settings. If `$feed_id == 0` then `$slug` and 
	 * `_settings_arr`not used and.
	 * @param string $slug This slug will be added to `_settings_arr` option name.
	 *
	 * @return bool
	 */
	function common_option_add( $option_name, $value, $autoload = 'no', $feed_id = '0', $slug = '' ) {

		if ( $feed_id === '0' ) {
			$option_name_in_db = $option_name;
			$value_in_db = $value;
		} else {
			$option_name_in_db = $slug . '_settings_arr';
			$settings_arr = univ_option_get( $option_name_in_db );
			$settings_arr[ $feed_id ][ $option_name ] = $value;
			$value_in_db = $settings_arr;
		}

		if ( is_multisite() ) {
			return add_blog_option( get_current_blog_id(), $option_name_in_db, $value_in_db );
		} else {
			return add_option( $option_name_in_db, $value_in_db, '', $autoload );
		}

	}
}

if ( ! function_exists( 'common_option_upd' ) ) {
	/**
	 * Updates an element in the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a update_blog_option or update_option. Also, this function can work as
	 * update_blog_option or update_option. To do this, DO NOT pass the SLUG.
	 * 
	 * @since 1.0.0
	 * @version 1.1.9 (22-10-2024)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param mixed $option_value
	 * @param string|bool $autoload Maybe: `yes`|`no` or `true`|`false`.
	 * @param string $feed_id Feed ID (key) in the array of common settings. If `$feed_id == 0` then `$slug` and 
	 * `_settings_arr`not used and.
	 * @param string $slug This slug will be added to `_settings_arr` option name.
	 *
	 * @return bool
	 */
	function common_option_upd( $option_name, $option_value, $autoload = 'no', $feed_id = '0', $slug = '' ) {

		if ( $feed_id === '0' ) {
			$option_name_in_db = $option_name;
			$option_value_in_db = $option_value;
		} else {
			$option_name_in_db = sprintf( '%s_settings_arr', $slug );
			$settings_arr = common_option_get( $option_name_in_db );
			if ( is_array( $settings_arr ) ) {
				$settings_arr[ $feed_id ][ $option_name ] = $option_value;
			} else {
				$settings_arr = [];
				$settings_arr[ $feed_id ][ $option_name ] = $option_value;
			}
			$option_value_in_db = $settings_arr;
		}

		if ( is_multisite() ) {
			return update_blog_option( get_current_blog_id(), $option_name_in_db, $option_value_in_db );
		} else {
			return update_option( $option_name_in_db, $option_value_in_db, $autoload );
		}

	}
}

if ( ! function_exists( 'common_option_get' ) ) {
	/**
	 * Get the element from the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a get_blog_option or get_option. Also, this function can work as
	 * get_blog_option or get_option. To do this, DO NOT pass the SLUG.
	 * 
	 * @since 1.0.0
	 * @version 2.0.1 (17-06-2025)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param mixed $default_value Value to return if the option does not exist.
	 * @param string $feed_id Feed ID (key) in the array of common settings. If `$feed_id == 0` then `$slug` and 
	 * `_settings_arr`not used and.
	 * @param string $slug This slug will be added to `_settings_arr` option name.
	 *
	 * @return mixed
	 */
	function common_option_get( $option_name, $default_value = false, $feed_id = '0', $slug = '' ) {

		if ( $feed_id === '0' ) {
			$option_name_in_db = $option_name;
		} else {
			$option_name_in_db = sprintf( '%s_settings_arr', $slug );
			$settings_arr = common_option_get( $option_name_in_db, [] );
			if ( isset( $settings_arr[ $feed_id ][ $option_name ] ) ) {
				if (
					'' === $settings_arr[ $feed_id ][ $option_name ]
					|| null === $settings_arr[ $feed_id ][ $option_name ]
					|| false === $settings_arr[ $feed_id ][ $option_name ]
				) {
					return $default_value;
				} else {
					return $settings_arr[ $feed_id ][ $option_name ];
				}
			} else {
				return $default_value;
			}
		}

		if ( is_multisite() ) {
			return get_blog_option( get_current_blog_id(), $option_name_in_db, $default_value );
		} else {
			return get_option( $option_name_in_db, $default_value );
		}

	}
}

if ( ! function_exists( 'common_option_del' ) ) {
	/**
	 * Deletes an element from the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a delete_blog_option or delete_option. Also, this function can work as
	 * delete_blog_option or delete_option. To do this, DO NOT pass the SLUG.
	 * 
	 * @since 1.0.0
	 * @version 1.1.9 (22-10-2024)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param string $feed_id Feed ID (key) in the array of common settings. If `$feed_id == 0` then `$slug` and 
	 * `_settings_arr`not used and.
	 * @param string $slug This slug will be added to `_settings_arr` option name.
	 *
	 * @return bool
	 */
	function common_option_del( $option_name, $feed_id = '0', $slug = '' ) {

		if ( $feed_id === '0' ) {
			$option_name_in_db = $option_name;
		} else {
			$option_name_in_db = sprintf( '%s_settings_arr', $slug );
			$settings_arr = common_option_get( $option_name_in_db, [] );
			if ( isset( $settings_arr[ $feed_id ][ $option_name ] ) ) {
				unset( $settings_arr[ $feed_id ][ $option_name ] );
				if ( is_multisite() ) {
					return update_blog_option( get_current_blog_id(), $option_name_in_db, $settings_arr );
				} else {
					return update_option( $option_name_in_db, $settings_arr );
				}
			} else {
				return false;
			}
		}

		if ( is_multisite() ) {
			return delete_blog_option( get_current_blog_id(), $option_name_in_db );
		} else {
			return delete_option( $option_name_in_db );
		}

	}
}

if ( ! function_exists( 'univ_option_add' ) ) {
	/**
	 * Returns what might be the result of a add_blog_option or add_option.
	 * 
	 * @since 1.0.0 (23-05-2023)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param mixed $value
	 * @param string|bool $autoload Maybe: `yes`|`no` or `true`|`false`.
	 *
	 * @return bool
	 */
	function univ_option_add( $option_name, $value, $autoload = 'no' ) {

		if ( is_multisite() ) {
			return add_blog_option( get_current_blog_id(), $option_name, $value );
		} else {
			return add_option( $option_name, $value, '', $autoload );
		}

	}
}

if ( ! function_exists( 'univ_option_upd' ) ) {
	/** 
	 * Returns what might be the result of a update_blog_option or update_option.
	 * 
	 * @since 1.0.0 (23-05-2023)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param mixed $new_value
	 * @param string|bool $autoload Maybe: `yes`|`no` or `true`|`false`.
	 *
	 * @return bool
	 */
	function univ_option_upd( $option_name, $new_value, $autoload = 'no' ) {

		if ( is_multisite() ) {
			return update_blog_option( get_current_blog_id(), $option_name, $new_value );
		} else {
			return update_option( $option_name, $new_value, $autoload );
		}

	}
}

if ( ! function_exists( 'univ_option_get' ) ) {
	/**
	 * Returns what might be the result of a get_blog_option or get_option.
	 * 
	 * @since 1.0.0 (23-05-2023)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 * @param mixed $default_value Value to return if the option does not exist.
	 *
	 * @return mixed
	 */
	function univ_option_get( $option_name, $default_value = false ) {

		if ( is_multisite() ) {
			return get_blog_option( get_current_blog_id(), $option_name, $default_value );
		} else {
			return get_option( $option_name, $default_value );
		}

	}
}

if ( ! function_exists( 'univ_option_del' ) ) {
	/**
	 * Returns what might be the result of a delete_blog_option or delete_option.
	 * 
	 * @since 1.0.0 (23-05-2023)
	 * @depricated 5.4.0 // TODO: remove (16-04-2026)
	 *
	 * @param string $option_name
	 *
	 * @return bool
	 */
	function univ_option_del( $option_name ) {

		if ( is_multisite() ) {
			return delete_blog_option( get_current_blog_id(), $option_name );
		} else {
			return delete_option( $option_name );
		}

	}
}
