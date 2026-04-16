<?php

/**
 * WP CLI Command for YML for Yandex Market.
 *
 * This class provides WP-CLI commands to generate, manage, and monitor YML feed creation
 * for the Y4YM plugin. Designed for automation, cron jobs, and server-side integration.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/wp-cli
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CLI command to generate and manage YML feed.
 *
 * Provides three main commands:
 *
 * - `wp y4ym generate` — Step-by-step feed generation with progress bar (recommended for large feeds).
 * - `wp y4ym quick` — Fast synchronous feed generation (suitable for small feeds).
 * - `wp y4ym status` — Check current status and progress of feed generation.
 *
 * ### Examples
 *
 * Run step-by-step generation:
 *     wp y4ym generate --feed_id=1
 *
 * Run quick generation:
 *     wp y4ym quick --feed_id=1
 *
 * Check status:
 *     wp y4ym status --feed_id=1
 *
 * Use in system cron with post-processing:
 *     0 3 * * * cd /var/www/site && wp y4ym generate --feed_id=1 --path=/var/www/site --allow-root && sed -i 's|old-domain.com|new-domain.com|g' wp-content/uploads/feed-yml-1.xml && rsync -av wp-content/uploads/feed-yml-1.xml user@remote:/feeds/
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/wp-cli
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_WP_CLI_Command extends WP_CLI_Command {

	/**
	 * List all YML feeds with their status and last generation time.
	 *
	 * Displays a table containing feed ID, URL, start/end timestamps, and current status.
	 * Useful for monitoring multiple feeds at once.
	 *
	 * ## EXAMPLES
	 *
	 *     wp y4ym feedlist
	 * 
	 * @param array $args       Positional arguments (not used).
	 * @param array $assoc_args Associative arguments from CLI (not used).
	 * 
	 * @return void
	 */
	public function feedlist( $args, $assoc_args ) {

		// Предположим, у вас есть способ получить все feed_id
		// Например, через опции или базу
		$all_feed_ids = $this->get_all_feed_ids(); // ваш метод

		$table = [];
		foreach ( $all_feed_ids as $feed_id ) {
			$feed_url = Y4YM_Options::settings_get( 'y4ym_feed_url', '', $feed_id, 'y4ym' );
			$date_sborki_start = Y4YM_Options::settings_get( 'y4ym_date_sborki_start', '', $feed_id, 'y4ym' );
			$date_sborki_end = Y4YM_Options::settings_get( 'y4ym_date_sborki_end', '', $feed_id, 'y4ym' );
			$status_sborki = (int) Y4YM_Options::settings_get( 'y4ym_status_sborki', '-1', $feed_id, 'y4ym' );
			$status_map = [
				'-1' => 'Completed',
				'1' => 'Initializing',
				'2' => 'In progress',
				'3' => 'Finalizing',
				'4' => 'Archiving'
			];
			$status_text = $status_map[ $status_sborki ] ?? 'Unknown';

			$table[] = [
				'ID' => $feed_id,
				'Feed URL' => $feed_url,
				'Last Generated Start' => $$date_sborki_start ?: '-',
				'Last Generated End' => $date_sborki_end ?: '-',
				'Status' => $status_text,
			];
		}

		WP_CLI\Utils\format_items(
			'table',
			$table,
			[ 'ID', 'Feed URL', 'Last Generated Start', 'Last Generated End', 'Status' ]
		);

	}

	/**
	 * Display current status of feed generation.
	 *
	 * Shows:
	 * - Current status code and description
	 * - Number of processed products
	 * - Start/end times
	 * - Progress percentage
	 *
	 * ## OPTIONS
	 *
	 * [--feed_id=<id>]
	 * : The feed ID to check.
	 * ---
	 * default: 1
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp y4ym status --feed_id=1
	 *
	 * @param array $args       Positional arguments (not used).
	 * @param array $assoc_args Associative arguments from CLI.
	 *
	 * @return void
	 */
	public function status( $args, $assoc_args ) {

		$feed_id = WP_CLI\Utils\get_flag_value( $assoc_args, 'feed_id', '1' );

		$status_code = (int) Y4YM_Options::settings_get( 'y4ym_status_sborki', -1, $feed_id, 'y4ym' );
		$last_element = (int) Y4YM_Options::get( 'y4ym_last_element_feed_' . $feed_id, 0 );
		$total_products = $this->get_total_products_count( $feed_id );
		$date_start = Y4YM_Options::settings_get( 'y4ym_date_sborki_start', '', $feed_id, 'y4ym' );
		$date_end = Y4YM_Options::settings_get( 'y4ym_date_sborki_end', '', $feed_id, 'y4ym' );

		$status_map = [
			-1 => 'Completed or stopped',
			1 => 'Started (initializing)',
			2 => 'In progress (processing products)',
			3 => 'Finalizing (writing footer)',
			4 => 'Archiving and finishing'
		];

		$status_text = isset( $status_map[ $status_code ] ) ? $status_map[ $status_code ] : 'Unknown';

		WP_CLI::line( "Feed ID: $feed_id" );
		WP_CLI::line( "Status: $status_code — $status_text" );
		WP_CLI::line( "Processed: $last_element / $total_products products" );
		WP_CLI::line( "Start time: " . ( $date_start ?: 'Not started' ) );
		WP_CLI::line( "End time: " . ( $date_end ?: 'Not completed' ) );

		if ( $total_products > 0 ) {
			$percent = round( ( $last_element / $total_products ) * 100, 1 );
			WP_CLI::line( "Progress: $percent%" );
		}

	}

	/**
	 * Retrieves all feed IDs configured in the plugin.
	 *
	 * Fetches the list of feed IDs from the stored settings array.
	 * Each key in 'y4ym_settings_arr' corresponds to a feed ID.
	 *
	 * @return array List of feed IDs as strings.
	 */
	private function get_all_feed_ids() {

		$feed_ids = [];
		$settings_arr = Y4YM_Options::get( 'y4ym_settings_arr' );
		$settings_arr_keys_arr = array_keys( $settings_arr );
		for ( $i = 0; $i < count( $settings_arr_keys_arr ); $i++ ) {
			$feed_ids[] = (string) $settings_arr_keys_arr[ $i ];
		}
		return $feed_ids;

	}

	/**
	 * Generates YML feed step-by-step with real-time status updates.
	 *
	 * Starts feed generation process and monitors it in a loop until completion.
	 * Uses incremental processing via cron manager to handle large product catalogs.
	 * Outputs progress information including percentage and processed product count.
	 *
	 * ## OPTIONS
	 *
	 * [--feed_id=<id>]
	 * : The ID of the feed to generate.
	 * ---
	 * default: 1
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp y4ym generate --feed_id=1
	 *
	 * @param array $args       Positional arguments (not used).
	 * @param array $assoc_args Associative arguments from CLI.
	 * 
	 * @return void
	 */
	public function generate( $args, $assoc_args ) {

		if ( ! class_exists( 'Y4YM_Generation_XML' ) ) {
			WP_CLI::error( 'Y4YM_Generation_XML class not found. Plugin may be inactive.' );
			return;
		}

		$feed_id = WP_CLI\Utils\get_flag_value( $assoc_args, 'feed_id', '1' );

		// Получаем общее количество товаров
		$total_products = $this->get_total_products_count( $feed_id );
		if ( $total_products === 0 ) {
			WP_CLI::warning( "No products found for feed ID $feed_id." );
			return;
		}

		// Проверяем статус сборки
		$status_sborki = (string) Y4YM_Options::settings_get(
			'y4ym_status_sborki',
			'-1',
			$feed_id,
			'y4ym'
		);

		if ( $status_sborki !== '-1' ) {
			// HACK: почему то через WP_CLI::confirm был баг и `y` не прокатывал. Заменил на readline
			$input = readline(
				sprintf( 'Feed #%s. %s. %s? [y/N]:',
					$feed_id,
					__( 'This feed is currently being created', 'yml-for-yandex-market' ),
					__(
						'Are you sure you want to interrupt the current process and start generating from scratch',
						'yml-for-yandex-market'
					)
				)
			);
			// Останавить сборку, если она уже запущена?
			if ( strtolower( $input ) !== 'y' ) {
				// Да. Останавливаем сборку, если она уже запущена
				WP_CLI::warning( __( 'The action was canceled by the user', 'yml-for-yandex-market' ) );
				return;
			}
		}

		try {

			// wp_clear_scheduled_hook( 'y4ym_cron_start_feed_creation', [ $feed_id ] );
			wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $feed_id ] );

			// счётчик завершенных товаров в положение 0.
			Y4YM_Options::update(
				'y4ym_last_element_feed_' . $feed_id,
				'0',
				'no'
			);
			// сборку начали
			Y4YM_Options::settings_update(
				'y4ym_status_sborki',
				'1',
				'no',
				$feed_id,
				'y4ym'
			);
			$date_sborki_start = current_time( 'Y-m-d H:i' );
			Y4YM_Options::settings_update(
				'y4ym_date_sborki_start',
				$date_sborki_start,
				'no',
				$feed_id,
				'y4ym'
			);

			$cron_manager = new Y4YM_Cron_Manager();
			// Получаем общее количество товаров
			$total_products = $this->get_total_products_count( $feed_id );

			// Читаем время выполнения шага из настроек
			$script_execution_time = (int) Y4YM_Options::settings_get(
				'y4ym_script_execution_time',
				'26',
				$feed_id,
				'y4ym'
			);
			$sleep_interval = max( $script_execution_time + 2, 5 ); // минимум 5 сек

			WP_CLI::warning( sprintf( 'Feed #%s. %s %s %s',
				$feed_id,
				__( 'The maximum step execution according to the settings is', 'yml-for-yandex-market' ),
				$sleep_interval,
				__( 'seconds', 'yml-for-yandex-market' )
			) );

			while ( true ) {

				$start_time = microtime( true );
				// Проверяем статус сборки
				$status_sborki = (string) Y4YM_Options::settings_get(
					'y4ym_status_sborki',
					'-1',
					$feed_id,
					'y4ym'
				);

				switch ( $status_sborki ) {

					case '-1':

						$feed_url = Y4YM_Options::settings_get(
							'y4ym_feed_url',
							'',
							$feed_id,
							'y4ym'
						);
						wp_clear_scheduled_hook( 'y4ym_cron_sborki', [ $feed_id ] );
						WP_CLI::success( sprintf( 'Feed #%s. %s! %s: %s.',
							$feed_id,
							__(
								'The creation of the feed has been completed successfully',
								'yml-for-yandex-market'
							),
							__(
								'Feed URL',
								'yml-for-yandex-market'
							),
							$feed_url
						) );
						return;

						break;
					case '1':

						WP_CLI::line( sprintf( 'Feed #%s. %s.',
							$feed_id,
							__( 'Creating feed headers', 'yml-for-yandex-market' )
						) );

						break;
					case '2':

						$last_element_feed = (int) Y4YM_Options::get(
							'y4ym_last_element_feed_' . $feed_id,
							0
						);

						WP_CLI::line( sprintf( 'Feed #%s. %s. %s: %s / %s (%s%%)',
							$feed_id,
							__( 'Creating temporary feed files', 'yml-for-yandex-market' ),
							__( 'The number of processed products', 'yml-for-yandex-market' ),
							$last_element_feed,
							$total_products,
							round( ( $last_element_feed / $total_products ) * 100, 1 )
						) );

						break;
					case '3':

						WP_CLI::line( sprintf( 'Feed #%s. %s.',
							$feed_id,
							__( 'Gluing the feed', 'yml-for-yandex-market' )
						) );

						break;
					case '4':

						WP_CLI::line( sprintf( 'Feed #%s. %s...',
							$feed_id,
							__( 'Completing the assembly', 'yml-for-yandex-market' )
						) );

						break;

				}

				$r = $cron_manager->do_it_every_minute( $feed_id ); // прямой вызов
				usleep( 2000000 ); // 2 сек
				$execution_time = microtime( true ) - $start_time;
				WP_CLI::debug( sprintf( '%.3f sec', $execution_time ) );

			}
		} catch (\Exception $e) {
			WP_CLI::error( "Feed generation failed: " . $e->getMessage() );
		}

	}

	/**
	 * Perform a quick synchronous feed generation.
	 *
	 * This method generates the entire feed in a single pass. Suitable for small feeds
	 * (< 1000 products). May cause timeout or memory issues with large catalogs.
	 *
	 * ## OPTIONS
	 *
	 * [--feed_id=<id>]
	 * : The feed ID to generate.
	 * ---
	 * default: 1
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp y4ym quick --feed_id=1
	 *
	 * @param array $args       Positional arguments (not used).
	 * @param array $assoc_args Associative arguments from CLI.
	 *
	 * @return void
	 */
	public function quick( $args, $assoc_args ) {

		$feed_id = WP_CLI\Utils\get_flag_value( $assoc_args, 'feed_id', '1' );

		if ( ! class_exists( 'Y4YM_Generation_XML' ) ) {
			WP_CLI::error( 'Y4YM_Generation_XML class not found. Plugin may be inactive.' );
			return;
		}

		WP_CLI::line( "Starting QUICK feed generation for feed ID: $feed_id" );

		try {
			$generation = new Y4YM_Generation_XML( $feed_id );
			$generation->quick_generation();
			$feed_url = Y4YM_Options::settings_get(
				'y4ym_feed_url',
				'',
				$feed_id,
				'y4ym'
			);
			WP_CLI::success( sprintf( 'Feed #%s. %s! %s: %s.',
				$feed_id,
				__(
					'Quick feed generation has been completed successfully',
					'yml-for-yandex-market'
				),
				__(
					'Feed URL',
					'yml-for-yandex-market'
				),
				$feed_url
			) );
		} catch (\Exception $e) {
			WP_CLI::error( "Quick feed generation failed: " . $e->getMessage() );
		}

	}

	/**
	 * Get the total number of published products for the feed.
	 *
	 * Applies filters like `y4ym_f_query_args` to respect custom query logic.
	 * Counts only products that match the feed criteria and are publicly visible.
	 *
	 * @param string $feed_id The feed ID used to apply filters.
	 *
	 * @return int Total number of products that match the feed criteria.
	 */
	private function get_total_products_count( $feed_id ) {

		$args = apply_filters(
			'y4ym_f_query_args',
			[
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			],
			$feed_id
		);

		$query = new \WP_Query( $args );
		return $query->found_posts;

	}

}

WP_CLI::add_command( 'y4ym', 'Y4YM_WP_CLI_Command' );