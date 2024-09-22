<?php
/**
 * Print tabs
 * 
 * @version 4.3.1 (14-04-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['tabs_arr']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="nav-tab-wrapper" style="margin-bottom: 10px;">
	<?php
	foreach ( $view_arr['tabs_arr'] as $tab => $name ) {
		if ( $tab === $view_arr['tab_name'] ) {
			$class = ' nav-tab-active';
		} else {
			$class = '';
		}
		if ( isset( $_REQUEST['yfym_submit_add_new_feed'] ) ) {
			// если нажата кнопка "Добавить фид"
			$nf = '&feed_id=' . yfym_get_last_feed_id();
		} else if ( isset( $_GET['feed_id'] ) ) {
			$nf = '&feed_id=' . sanitize_key( $_GET['feed_id'] );
		} else {
			$nf = '&feed_id=' . yfym_get_first_feed_id();
		}
		printf(
			'<a class="nav-tab%1$s" href="?page=yfymexport&tab=%2$s%3$s">%4$s</a>',
			esc_attr( $class ), esc_attr( $tab ), esc_attr( $nf ), esc_html( $name )
		);
	}
	?>
</div>