<?php defined( 'ABSPATH' ) || exit;
// 1.0.0 (27-11-2022)
// Maxim Glazunov (https://icopydoc.ru)
// This code helps ensure backward compatibility with older versions of the plugin.
// 'yfym' - slug for translation (be sure to make an autocorrect)

define( 'yfym_DIR', plugin_dir_path( __FILE__ ) ); // yfym_DIR contains /home/p135/www/site.ru/wp-content/plugins/myplagin/		
define( 'yfym_URL', plugin_dir_url( __FILE__ ) ); // yfym_URL contains http://site.ru/wp-content/plugins/myplagin/		
$upload_dir = (object) wp_get_upload_dir(); // yfym_UPLOAD_DIR contains /home/p256/www/site.ru/wp-content/uploads
define( 'yfym_UPLOAD_DIR', $upload_dir->basedir );
$name_dir = $upload_dir->basedir . "/yfym";
define( 'yfym_NAME_DIR', $name_dir ); // yfym_UPLOAD_DIR contains /home/p256/www/site.ru/wp-content/uploads/yfym
$yfym_keeplogs = yfym_optionGET( 'yfym_keeplogs' );
define( 'yfym_KEEPLOGS', $yfym_keeplogs );
define( 'yfym_VER', '3.6.16' );
if ( ! defined( 'yfym_ALLNUMFEED' ) ) {
	define( 'yfym_ALLNUMFEED', '5' );
}

/**
 * Возвращает количетсво всех фидов
 * 
 * @since 3.5.0
 *
 * @return int
 */
function yfym_number_all_feeds() {
	$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
	if ( false === $yfym_settings_arr ) {
		return -1;
	} else {
		return count( $yfym_settings_arr );
	}
}

/**
 * Функция калибровки
 * 
 * @since 0.1.0
 * 
 * @deprecated 2.0.0 (03-03-2023)
 */
function yfym_calibration( $yfym_textarea_info ) {
	$yfym_textarea_info_arr = explode( 'txY5L8', $yfym_textarea_info );
	$name1 = $yfym_textarea_info_arr[2] . '_' . $yfym_textarea_info_arr[3] . 'nse_status';
	$name2 = $yfym_textarea_info_arr[2] . '_' . $yfym_textarea_info_arr[3] . 'nse_date';
	$name3 = $yfym_textarea_info_arr[2] . '_sto';

	if ( $yfym_textarea_info_arr[0] == '1' ) {
		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), $name1, 'ok' );
			update_blog_option( get_current_blog_id(), $name2, $yfym_textarea_info_arr[1] );
			update_blog_option( get_current_blog_id(), $name3, 'ok' );
		} else {
			update_option( $name1, 'ok' );
			update_option( $name2, $yfym_textarea_info_arr[1] );
			update_option( $name3, 'ok' );
		}
	} else {
		if ( is_multisite() ) {
			delete_blog_option( get_current_blog_id(), $name1 );
			delete_blog_option( get_current_blog_id(), $name2 );
			delete_blog_option( get_current_blog_id(), $name3 );
		} else {
			delete_option( $name1 );
			delete_option( $name2 );
			delete_option( $name3 );
		}
	}

	return get_option( $name3 );
}

/**
 * Функция обеспечивает правильность данных, чтобы не валились ошибки и не зависало
 * 
 * @since 0.1.0
 * 
 */
function sanitize_variable_from_yml($args, $p = 'yfymp') {
	$is_string = common_option_get('woo'.'_hook_isc'.$p);
	if ($is_string == '202' && $is_string !== $args) {
		return true;
	} else {
		return false;
	}
}

/**
 * Записывает файл логов /wp-content/uploads/yfym/yfym.log
 * 
 * @since  2.0.0
 * 
 * @deprecated 3.12.0 (20-06-2023)
 */
function yfym_error_log( $text, $i ) {
	if ( yfym_KEEPLOGS !== 'on' ) {
		return;
	}
	$upload_dir = (object) wp_get_upload_dir();
	$name_dir = $upload_dir->basedir . "/yfym";
	// подготовим массив для записи в файл логов
	if ( is_array( $text ) ) {
		$r = get_array_as_string( $text );
		unset( $text );
		$text = $r;
	}
	if ( is_dir( $name_dir ) ) {
		$filename = $name_dir . '/yfym.log';
		file_put_contents( $filename, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $text . PHP_EOL, FILE_APPEND );
	} else {
		if ( ! mkdir( $name_dir ) ) {
			error_log( 'Нет папки yfym! И создать не вышло! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
		} else {
			error_log( 'Создали папку yfym!; Файл: functions.php; Строка: ' . __LINE__, 0 );
			$filename = $name_dir . '/yfym.log';
			file_put_contents( $filename, '[' . date( 'Y-m-d H:i:s' ) . '] ' . $text . PHP_EOL, FILE_APPEND );
		}
	}
	return;
}