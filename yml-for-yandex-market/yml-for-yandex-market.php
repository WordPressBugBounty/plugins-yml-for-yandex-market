<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                    https://icopydoc.ru
 * @since                   1.0.0
 * @package                 Y4YM
 *
 * @wordpress-plugin
 * Plugin Name:             YML for Yandx Market
 * Requires Plugins:        woocommerce
 * Plugin URI:              https://wordpress.org/plugins/yml-for-yandex-market/
 * Description:             Creates a YML-feed to upload to Yandex Market and not only
 * Version:                 4.9.0
 * Requires at least:       4.5
 * Requires PHP:            7.4.0
 * Author:                  Maxim Glazunov
 * Author URI:              https://icopydoc.ru/
 * License:                 GPL v2 or later
 * License URI:             https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:             yml-for-yandex-market
 * Domain Path:             /languages
 * Tags:                    yml, yandex, market, export, woocommerce
 * WC requires at least:    3.0.0
 * WC tested up to:         9.4.3
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * Copyright 2018-2024 (Author emails: djdiplomat@yandex.ru, support@icopydoc.ru)
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$not_run = false;

// Check php version
if ( version_compare( phpversion(), '7.4.0', '<' ) ) { // не совпали версии
	add_action( 'admin_notices', function () {
		warning_notice( 'notice notice-error',
			sprintf(
				'<strong style="font-weight: 700;">%1$s</strong> %2$s 7.4.0 %3$s %4$s',
				'YML for Yandex Market',
				__( 'plugin requires a php version of at least', 'yml-for-yandex-market' ),
				__( 'You have the version installed', 'yml-for-yandex-market' ),
				phpversion()
			)
		);
	} );
	$not_run = true;
}

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if ( ! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) ) )
	&& ! ( is_multisite()
		&& array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', [] ) ) )
) {
	add_action( 'admin_notices', function () {
		warning_notice(
			'notice notice-error',
			sprintf(
				'<strong style="font-weight: 700;">YML for Yandex Market</strong> %1$s',
				__( 'requires WooCommerce installed and activated', 'yml-for-yandex-market' )
			)
		);
	} );
	$not_run = true;
} else {
	// add support for HPOS
	add_action( 'before_woocommerce_init', function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );
}

if ( ! function_exists( 'warning_notice' ) ) {
	/**
	 * Display a notice in the admin plugins page. Usually used in a @hook `admin_notices`.
	 * 
	 * @since 0.1.0
	 * 
	 * @param string $class
	 * @param string $message
	 * 
	 * @return void
	 */
	function warning_notice( $class = 'notice', $message = '' ) {
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}

// Define constants
define( 'YFYM_PLUGIN_VERSION', '4.9.0' );

$upload_dir = wp_get_upload_dir();
// http://site.ru/wp-content/uploads
define( 'YFYM_SITE_UPLOADS_URL', $upload_dir['baseurl'] );

// /home/site.ru/public_html/wp-content/uploads
define( 'YFYM_SITE_UPLOADS_DIR_PATH', $upload_dir['basedir'] );

// http://site.ru/wp-content/uploads/yfym
define( 'YFYM_PLUGIN_UPLOADS_DIR_URL', $upload_dir['baseurl'] . '/yfym' );

// /home/site.ru/public_html/wp-content/uploads/yfym
define( 'YFYM_PLUGIN_UPLOADS_DIR_PATH', $upload_dir['basedir'] . '/yfym' );
unset( $upload_dir );

// http://site.ru/wp-content/plugins/yml-for-yandex-market/
define( 'YFYM_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

// /home/p135/www/site.ru/wp-content/plugins/yml-for-yandex-market/
define( 'YFYM_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

// /home/p135/www/site.ru/wp-content/plugins/yml-for-yandex-market/yml-for-yandex-market.php
define( 'YFYM_PLUGIN_MAIN_FILE_PATH', __FILE__ );

// yml-for-yandex-market - псевдоним плагина
define( 'YFYM_PLUGIN_SLUG', wp_basename( dirname( __FILE__ ) ) );

// yml-for-yandex-market/yml-for-yandex-market.php - полный псевдоним плагина (папка плагина + имя главного файла)
define( 'YFYM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// $not_run = apply_filters('y4ym_f_nr', $not_run);

// load translation
add_action( 'init', function () {
	load_plugin_textdomain( 'yml-for-yandex-market', false, dirname( YFYM_PLUGIN_BASENAME ) . '/languages/' );
} );

if ( false === $not_run ) {
	unset( $not_run );

	// for wp_kses
	define( 'Y4YM_ALLOWED_HTML_ARR', [ 
		'a' => [ 
			'href' => true,
			'title' => true,
			'target' => true,
			'class' => true,
			'style' => true
		],
		'br' => [ 'class' => true ],
		'i' => [ 'class' => true ],
		'small' => [ 'class' => true ],
		'strong' => [ 'class' => true, 'style' => true ],
		'p' => [ 'class' => true, 'style' => true ],
		'kbd' => [ 'class' => true ],
		'input' => [ 
			'id' => true,
			'name' => true,
			'class' => true,
			'placeholder' => true,
			'style' => true,
			'type' => true,
			'value' => true,
			'step' => true,
			'min' => true,
			'max' => true
		],
		'textarea' => [ 
			'id' => true,
			'name' => true,
			'class' => true,
			'placeholder' => true,
			'style' => true,
			'col' => true,
			'row' => true
		],
		'select' => [ 'id' => true, 'class' => true, 'name' => true, 'style' => true, 'size' => true, 'multiple' => true ],
		'option' => [ 'id' => true, 'class' => true, 'style' => true, 'value' => true, 'selected' => true ],
		'optgroup' => [ 'label' => true ],
		'label' => [ 'id' => true, 'class' => true ],
		'tr' => [ 'id' => true, 'class' => true ],
		'th' => [ 'id' => true, 'class' => true ],
		'td' => [ 'id' => true, 'class' => true ]
	] );

	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define( 'Y4YM_PLUGIN_VERSION', '4.9.0' );

	$upload_dir = wp_get_upload_dir();
	// http://site.ru/wp-content/uploads
	define( 'Y4YM_SITE_UPLOADS_URL', $upload_dir['baseurl'] );

	// /home/site.ru/public_html/wp-content/uploads
	define( 'Y4YM_SITE_UPLOADS_DIR_PATH', $upload_dir['basedir'] );

	// http://site.ru/wp-content/uploads/y4ym
	define( 'Y4YM_PLUGIN_UPLOADS_DIR_URL', $upload_dir['baseurl'] . '/y4ym' );

	// /home/site.ru/public_html/wp-content/uploads/y4ym
	define( 'Y4YM_PLUGIN_UPLOADS_DIR_PATH', $upload_dir['basedir'] . '/y4ym' );
	unset( $upload_dir );

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-y4ym-activator.php.
	 * 
	 * @return void
	 */

	require_once YFYM_PLUGIN_DIR_PATH . '/packages.php';
	register_activation_hook( __FILE__, [ 'YmlforYandexMarket', 'on_activation' ] );
	register_deactivation_hook( __FILE__, [ 'YmlforYandexMarket', 'on_deactivation' ] );
	add_action( 'plugins_loaded', [ 'YmlforYandexMarket', 'init' ], 10 ); // активируем плагин
	define( 'YFYM_ACTIVE', true );
}