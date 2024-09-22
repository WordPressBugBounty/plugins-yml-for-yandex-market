<?php
/**
 * Print the Save button
 * 
 * @version 4.3.1 (14-04-2024)
 * @see     
 * @package 
 * 
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

if ( $view_arr['tab_name'] === 'no_submit_tab' ) {
	return;
}
?>
<div class="postbox">
	<div class="inside">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="button-primary"></label></th>
					<td class="overalldesc">
						<?php wp_nonce_field( 'yfym_nonce_action', 'yfym_nonce_field' ); ?>
						<input id="button-primary" class="button-primary" name="yfym_submit_action" type="submit" value="<?php
						if ( $view_arr['tab_name'] === 'main_tab' ) {
							printf( '%s & %s (ID: %s)',
								esc_html__( 'Save', 'yml-for-yandex-market' ),
								esc_html__( 'Create feed', 'yml-for-yandex-market' ),
								esc_attr( $view_arr['feed_id'] )
							);
						} else {
							printf( '%s (ID: %s)',
								esc_html__( 'Save', 'yml-for-yandex-market' ),
								esc_attr( $view_arr['feed_id'] )
							);
						}
						?>" /><br />
						<span class="description">
							<small>
								<?php esc_html_e( 'Click to save the settings', 'yml-for-yandex-market' ); ?>
							</small>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>