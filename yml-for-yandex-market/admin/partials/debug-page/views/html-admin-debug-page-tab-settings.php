<?php defined( 'WPINC' ) || exit;

/**
 * Display the Debug settings tab.
 * 
 * @version    5.12.0 (31-10-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 * 
 * @param $view_arr['keeplogs']
 * @param $view_arr['plugin_notifications']
 */
?>
<div class="y4ym-postbox postbox">

	<table class="form-table">
		<tbody>
			<tr>
				<th class="y4ym_th" scope="row"><?php esc_html_e( 'Keep logs', 'yml-for-yandex-market' ); ?></th>
				<td class="y4ym_td overalldesc">
					<select id="y4ym_keeplogs" class="y4ym_select" name="y4ym_keeplogs">
						<option value="disabled" <?php selected( $view_arr['keeplogs'], 'disabled' ); ?>>
							<?php esc_html_e( 'Disabled', 'yml-for-yandex-market' ); ?>
						</option>
						<option value="enabled" <?php selected( $view_arr['keeplogs'], 'enabled' ); ?>>
							<?php esc_html_e( 'Enabled', 'yml-for-yandex-market' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<?php if ( $view_arr['keeplogs'] === 'enabled' ) : ?>
				<tr>
					<th class="y4ym_th" scope="row">
						<?php esc_html_e( 'Plugin logs', 'yml-for-yandex-market' ); ?>
					</th>
					<td class="y4ym_td overalldesc">
						<?php
						$log_file_path = Y4YM_PLUGIN_UPLOADS_DIR_PATH . '/' . Y4YM_Error_Log::get_log_file_name();
						if ( ! file_exists( $log_file_path ) ) {
							printf( '<p><em>%s</em></p>',
								esc_html__( 'The log file does not exist', 'yml-for-yandex-market' )
							);
						} else {
							$log_content = Y4YM_Error_Log::read_tail( $log_file_path );
							if ( '' === $log_content || false === $log_content ) {
								printf( '<p><em>%s</em>.</p>',
									esc_html__( 'The log file is empty', 'yml-for-yandex-market' )
								);
							} else {
								printf( '<textarea readonly style="width: 100%%; height: 300px;" id="y4ym_log_content">%s</textarea>',
									esc_textarea( $log_content )
								);
								printf( '<p><em>%s: %s</em></p>',
									esc_html__( 'Location on your server', 'yml-for-yandex-market' ),
									esc_html( $log_file_path )
								);
							}
						}

						?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th class="y4ym_th" scope="row"><?php esc_html_e( 'Plugin notifications', 'yml-for-yandex-market' ); ?>
				</th>
				<td class="y4ym_td overalldesc">
					<select id="y4ym_plugin_notifications" class="y4ym_select" name="y4ym_plugin_notifications">
						<option value="enabled" <?php selected( $view_arr['plugin_notifications'], 'enabled' ); ?>>
							<?php esc_html_e( 'Enabled', 'yml-for-yandex-market' ); ?>
						</option>
						<option value="disabled" <?php selected( $view_arr['plugin_notifications'], 'disabled' ); ?>>
							<?php esc_html_e( 'Disabled', 'yml-for-yandex-market' ); ?>
						</option>
					</select>
					<p><?php esc_html_e( 'Disables most of the plugin notifications', 'yml-for-yandex-market' ); ?>.</p>
				</td>
			</tr>
		</tbody>
	</table>

</div>