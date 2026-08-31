<?php defined( 'WPINC' ) || exit;

/**
 * The Instruction tab.
 * 
 * @version    5.8.0 (31-08-2026)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/views
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */

$plugin_date = new Y4YM_Data();
$attr_arr = $plugin_date->get_data_for_tabs( $view_arr['tab_name'] );

$html_header = $view_arr['tab_name'];
$html_body = '';
$html_th = '';
$html_td = '';
?>
<div class="postbox">
	<div class="inside">
		<h1><?php esc_html_e( 'Important information about integration with Yandex Market', 'yml-for-yandex-market' ); ?>
		</h1>
		<p><?php esc_html_e(
			'Since July 2026, Yandex Market has stopped supporting the updating of prices and product stocks via YML feeds. If you continue to use this method, the data will stop synchronizingt',
			'yml-for-yandex-market'
		); ?>.</p>
		<p><?php printf( '%s: <a target="_blank"
			href="%s/?%s">%s</a>',
			esc_html__(
				'To keep the information up to date, set up integration via the API — a detailed instruction is available via the link',
				'yml-for-yandex-market'
			),
			'//icopydoc.ru/kak-peredavat-ostatki-i-tseny-v-yandeks-market-posle-iyulya-2026-goda-instruktsiya-po-nastrojke-api-v-plagine-y4ym',
			'utm_source=yml-for-yandex-market&utm_medium=documentation&utm_campaign=basic-version&utm_content=settings-page&utm_term=api-tab',
			__( 'Learn more about how it works', 'yml-for-yandex-market' )
		); ?>.</p>
		<p><?php esc_html_e(
			'An alternative option is to use the Import Products to Yandex plugin: it allows you to integrate with the Market entirely via API, without working with feeds',
			'yml-for-yandex-market'
		); ?>.</p>
		<p><strong><?php esc_html_e( 'Important', 'yml-for-yandex-market' ); ?>:</strong> <?php esc_html_e(
			 	'for the data about products and prices to be transmitted correctly, the SKUs in the feed and in your account on Yandex Market must match',
			 	'yml-for-yandex-market'
			 ); ?>.</p>
		<p><strong><?php esc_html_e( 'Please note', 'yml-for-yandex-market' ); ?>:</strong> <?php esc_html_e(
			 	'feeds for Yandex Direct, AliExpress, and other platforms continue to operate as usual',
			 	'yml-for-yandex-market'
			 ); ?>.</p>
	</div>
</div>
<?php
for ( $i = 0; $i < count( $attr_arr ); $i++ ) {

	include __DIR__ . '/html-admin-settings-feed-tab-item-loop-body.php';

}

$api_key = Y4YM_Options::settings_get( 'y4ym_api_key', '', $view_arr['feed_id'], 'y4ym' );
if ( empty( $api_key ) ) {
	$html_th .= sprintf( '<h2 class="hndle"><span style="color: red;">%1$s!</span></h2>',
		esc_html__( 'You will need to enter the Api-Key', 'yml-for-yandex-market' )
	);
}

if ( ! empty( $api_key ) ) {
	$html_check_api_bth = sprintf(
		'<tr class="y4ym_tr">
			<th scope="row">
				<label for="redirect_uri">%1$s</label>
			</th>
			<td class="overalldesc">
				<input id="y4ym-button-check-api" class="button" value="%1$s" type="submit" name="y4ym_check_action" />
				<p>%2$s</p>
			</td>
		</tr>',
		esc_html__( 'Check API', 'yml-for-yandex-market' ),
		sprintf( '%s. %s.',
			esc_html__( 'The data for connecting to the API has been entered', 'yml-for-yandex-market' ),
			esc_html__(
				'Now you can check its operation by clicking on this button',
				'yml-for-yandex-market'
			)
		)
	);
}

if ( ! empty( $html_body ) ) {
	printf(
		'<div class="y4ym-postbox postbox"><table class="form-table" role="presentation">%1$s<tbody>%2$s%3$s</tbody></table></div>',
		wp_kses( $html_th, Y4YM_ALLOWED_HTML_ARR ),
		wp_kses( $html_body, Y4YM_ALLOWED_HTML_ARR ),
		wp_kses( $html_check_api_bth, Y4YM_ALLOWED_HTML_ARR )
	);
	$html_body = '';
}
