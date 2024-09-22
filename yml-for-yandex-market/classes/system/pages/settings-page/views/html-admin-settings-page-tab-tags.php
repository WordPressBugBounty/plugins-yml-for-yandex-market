<?php
/**
 * The Tags tab
 * 
 * @version 4.3.1 (14-04-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['tabs_arr']
 */
defined( 'ABSPATH' ) || exit;

printf( '<div class="inside"><p>%s</p></div>',
	esc_html_e(
		'The table below shows the settings corresponding to your choice in the "Follow the rules" field on the "Basic Settings" tab',
		'yml-for-yandex-market'
	)
);

$settings_feed_table = new Y4YM_Settings_Page_Tags_WP_List_Table( $view_arr['feed_id'] );
$settings_feed_table->prepare_items();
$settings_feed_table->display();