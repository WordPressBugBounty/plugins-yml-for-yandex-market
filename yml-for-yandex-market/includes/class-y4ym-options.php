<?php

/**
 * Unified options management for YML for Yandex Market.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes
 */

/**
 * Unified options management for YML for Yandex Market.
 *
 * Provides a clean, consistent API for handling WordPress options in both
 * single-site and multisite environments.
 * 
 * Two levels of access:
 * 1. Direct option access via get()/update()/add()/delete() — like univ_*
 * 2. Feed-scoped settings via settings_get()/settings_update() etc — like common_*
 * 
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Options {

	/**
	 * Adds a new option.
	 *
	 * You do not need to serialize values. If the value needs to be serialized, then
	 * it will be serialized before it is inserted into the database. Remember,
	 * resources can not be serialized or added as an option.
	 * 
	 * Fails if the option already exists.
	 *
	 * @since 0.1.0
	 *
	 * @param string $option_name Name of the option to add. Expected to not be SQL-escaped.
	 * @param mixed  $value       Optional. Option value. Must be serializable if non-scalar.
	 *                            Expected to not be SQL-escaped.
	 * @param string $autoload    Whether to load the option when WordPress starts up ('yes'|'no').
	 *
	 * @return bool `true` if the option was added, `false` - otherwise.
	 */
	public static function add( string $option_name, $value, $autoload = 'no' ) {

		if ( is_multisite() ) {
			return add_blog_option( get_current_blog_id(), $option_name, $value );
		}
		return add_option( $option_name, $value, '', $autoload );

	}

	/**
	 * Updates the value of a site option.
	 *
	 * Adds the option if it does not already exist.
	 *
	 * @since 0.1.0
	 *
	 * @param string $option_name Name of the option to update. Expected to not be SQL-escaped.
	 * @param mixed  $new_value   Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 * @param string $autoload    Whether to load the option when WordPress starts up ('yes'|'no').
	 *
	 * @return bool `true` if the value was updated, `false` - otherwise.
	 */
	public static function update( string $option_name, $new_value, $autoload = 'no' ) {

		if ( is_multisite() ) {
			return update_blog_option( get_current_blog_id(), $option_name, $new_value );
		}
		return update_option( $option_name, $new_value, $autoload );

	}

	/**
	 * Retrieves the value of a site option.
	 *
	 * Works universally in single-site and multisite environments.
	 *
	 * @since 0.1.0
	 *
	 * @param string $option_name   Name of the option to retrieve. Expected to not be SQL-escaped.
	 * @param mixed  $default_value Optional. Default value to return if the option does not exist.
	 *
	 * @return mixed Value of the option. A value of any type may be returned, including
	 *               scalar (string, boolean, float, integer), null, array, object.
	 *               Scalar and null values will be returned as strings as long as they originate
	 *               from a database stored option value. If there is no option in the database,
	 *               boolean `false` is returned.
	 */
	public static function get( string $option_name, $default_value = false ) {

		if ( is_multisite() ) {
			return get_blog_option( get_current_blog_id(), $option_name, $default_value );
		}
		return get_option( $option_name, $default_value );

	}

	/**
	 * Deletes a site option.
	 *
	 * @since 0.1.0
	 *
	 * @param string $option_name Name of the option to delete. Expected to not be SQL-escaped.
	 *
	 * @return bool `true` if the option was deleted, `false` - otherwise.
	 */
	public static function delete( string $option_name ) {

		if ( is_multisite() ) {
			return delete_blog_option( get_current_blog_id(), $option_name );
		}
		return delete_option( $option_name );

	}

	/**
	 * Adds an element to the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a add_blog_option or add_option. Also, this function can work
	 * as add_blog_option or add_option. To do this, DO NOT pass the SLUG.
	 *
	 * @since 0.1.0
	 *
	 * @param string      $option_name Name of the setting to add.
	 * @param mixed       $value       Value to set. Must be serializable.
	 * @param string|bool $autoload    Maybe: `yes`|`no` or `true`|`false`.
	 * @param string      $feed_id     Feed ID (key) in the array of common settings. If `$feed_id == 0`
	 * or empty, then `$slug` and `_settings_arr` not used.
	 * @param string      $slug        This slug will be added to `_settings_arr` option name.
	 *
	 * @return bool
	 */
	public static function settings_add( string $option_name, $value, $autoload = 'no', $feed_id = '0', $slug = '' ) {

		// Global option mode: bypass array and use direct option storage
		if ( empty( $feed_id ) || $feed_id === '0' ) {
			return self::add( $option_name, $value, $autoload );
		}

		// Feed-scoped mode
		$feed_id = (string) $feed_id; // ? replace to trim( (string) $feed_id );
		if ( $feed_id === '' ) {
			return false; // Invalid feed ID
		}

		$option_name_in_db = $slug . '_settings_arr';

		// Retrieve the current settings array
		$settings_arr = self::get( $option_name_in_db, [] );
		if ( ! is_array( $settings_arr ) ) {
			// Cannot proceed if the stored value is not an array
			return false;
		}

		// Check if the key already exists
		if ( isset( $settings_arr[ $feed_id ][ $option_name ] ) ) {
			return false; // Already exists → do not overwrite
		}

		$settings_arr[ $feed_id ][ $option_name ] = $value;

		// Save back to database (using update because the option may already exist)
		return self::update( $option_name_in_db, $settings_arr, $autoload );

	}

	/**
	 * Updates an element in the array stored in the `SLUG_settings_arr` option.
	 *
	 * This method is atomic and safe for concurrent access. It uses an optimistic locking strategy
	 * to prevent race conditions when multiple processes modify settings simultaneously.
	 *
	 * Example usage:
	 * 
	 * // Update feed-specific setting
	 * Y4YM_Options::settings_update('price', 99.99, 'yes', '2', 'y4ym');
	 * 
	 * // Update global option (bypass feed system)
	 * Y4YM_Options::settings_update('my_option', 'value');
	 *
	 * @since 0.1.0
	 *
	 * @param string      $option_name   Name of the setting to update (e.g. 'price')
	 * @param mixed       $option_value  Value to set. Must be serializable.
	 * @param string|bool $autoload      Whether to autoload: 'yes'|'no' or true|false.
	 * @param string      $feed_id       Feed ID (key) in the array of common settings. If `$feed_id == 0`
	 * or empty, then `$slug` and `_settings_arr` not used.
	 * @param string      $slug          Plugin slug. Forms option name: {$slug}_settings_arr
	 *
	 * @return bool `true` on success, `false` on failure after retries.
	 */
	public static function settings_update( string $option_name, $option_value, $autoload = 'no', $feed_id = '0', $slug = '' ) {

		// Global option mode: bypass array and use direct option storage
		if ( empty( $feed_id ) || $feed_id === '0' ) {
			return self::update( $option_name, $option_value, $autoload );
		}

		// Feed-scoped mode
		$feed_id = (string) $feed_id; // ? replace to trim( (string) $feed_id );
		if ( $feed_id === '' ) {
			return false; // Invalid feed ID
		}

		$option_name_in_db = sprintf( '%s_settings_arr', $slug );
		$max_retries = 3;
		$attempts = 0;

		do {
			$attempts++;

			// Get current settings array
			$settings_arr = self::get( $option_name_in_db, [] );
			if ( ! is_array( $settings_arr ) ) {
				$settings_arr = [];
			}

			// Apply change
			if ( ! isset( $settings_arr[ $feed_id ] ) ) {
				$settings_arr[ $feed_id ] = [];
			}
			$settings_arr[ $feed_id ][ $option_name ] = $option_value;

			// Try to save
			$result = self::update( $option_name_in_db, $settings_arr, $autoload );

			// Проверяем, действительно ли значение обновилось
			if ( $result ) {
				return true; // Success
			}

			// Если update_option вернул false — проверим, может, значение уже правильное?
			$current_settings = self::get( $option_name_in_db, [] );
			if (
				isset( $current_settings[ $feed_id ][ $option_name ] ) &&
				$current_settings[ $feed_id ][ $option_name ] === $option_value
			) {
				return true; // Значение уже обновлено — успех!
			}

			// Small delay before retry (reduce load)
			if ( $attempts < $max_retries ) {
				usleep( 50000 ); // 50ms
			}

		} while ( ! $result && $attempts < $max_retries );

		// Финальная проверка: если до сих пор не записали — тогда ошибка
		$final_settings = self::get( $option_name_in_db, [] );
		if (
			isset( $final_settings[ $feed_id ][ $option_name ] ) &&
			$final_settings[ $feed_id ][ $option_name ] === $option_value
		) {
			return true; // Всё ок, просто race condition
		}

		// Only if, after 3 attempts, the value does NOT match, we write to the log
		error_log( "Y4YM_Options::settings_update - Critical: Failed to update $option_name after $max_retries attempts" );
		return false;

	}

	/**
	 * Get the element from the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a get_blog_option or get_option. Also, this function can work
	 * as get_blog_option or get_option. To do this, DO NOT pass the SLUG. 
	 * 
	 * Note: Returns default value if stored value is `''`, `null`, or `false`.
	 *
	 * @since 0.1.0
	 *
	 * @param string $option_name
	 * @param mixed  $default_value Value to return if the option does not exist.
	 * @param string $feed_id       Feed ID (key) in the array of common settings. If `$feed_id == 0`
	 * or empty, then `$slug` and `_settings_arr` not used.
	 * @param string $slug          This slug will be added to `_settings_arr` option name.
	 *
	 * @return mixed
	 */
	public static function settings_get( string $option_name, $default_value = false, $feed_id = '0', $slug = '' ) {

		// Global option mode: bypass array and use direct option storage
		if ( empty( $feed_id ) || $feed_id === '0' ) {
			return self::get( $option_name, $default_value );
		}

		// Feed-scoped mode
		$feed_id = (string) $feed_id; // ? replace to trim( (string) $feed_id );
		if ( $feed_id === '' ) {
			return false; // Invalid feed ID
		}

		$option_name_in_db = sprintf( '%s_settings_arr', $slug );
		$settings_arr = self::get( $option_name_in_db, [] );
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
		}
		return $default_value;

	}

	/**
	 * Deletes an element from the array stored in the `SLUG`+`_settings_arr` option.
	 * 
	 * Returns what might be the result of a delete_blog_option or delete_option. Also, this function can work
	 * as delete_blog_option or delete_option. To do this, DO NOT pass the SLUG.
	 *
	 * @since 0.1.0
	 *
	 * @param string $option_name
	 * @param string $feed_id     Feed ID (key) in the array of common settings. If `$feed_id == 0`
	 * or empty, then `$slug` and `_settings_arr` not used.
	 * @param string $slug        This slug will be added to `_settings_arr` option name.
	 *
	 * @return bool `true` if the setting was deleted or didn't exist.
	 */
	public static function settings_delete( string $option_name, $feed_id = '0', $slug = '' ) {

		// Global option mode: bypass array and use direct option storage
		if ( empty( $feed_id ) || $feed_id === '0' ) {
			return self::delete( $option_name );
		}

		// Feed-scoped mode
		$feed_id = (string) $feed_id; // ? replace to trim( (string) $feed_id );
		if ( $feed_id === '' ) {
			return false; // Invalid feed ID
		}

		$option_name_in_db = sprintf( '%s_settings_arr', $slug );
		$settings_arr = self::get( $option_name_in_db, [] );
		if ( isset( $settings_arr[ $feed_id ][ $option_name ] ) ) {
			unset( $settings_arr[ $feed_id ][ $option_name ] );
			return self::update( $option_name_in_db, $settings_arr );
		}
		return true;

	}

}