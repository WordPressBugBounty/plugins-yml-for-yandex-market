<?php defined( 'WPINC' ) || exit;

/**
 * Writes files (`tmp`, `xml` and etc).
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.11.0 (29-10-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 */

/**
 * Writes files (`tmp`, `xml` and etc).
 *
 * Usage example: `new Y4YM_Write_File( $result_xml, '-1.tmp', '2' );`
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
final class Y4YM_Write_File {

	/**
	 * Text to tmp file.
	 * @var string
	 */
	protected $xml_string;

	/**
	 * Path to the tmp file.
	 * Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/feed1/12345.tmp`.
	 * @var string|false
	 */
	protected $tmp_file_path;

	/**
	 * The result of writing to a file.
	 * @var bool
	 */
	protected $result = false;

	/**
	 * Writes files (`tmp`, `xml` and etc).
	 *
	 * @param string $xml_string The data to be written to the file.
	 * @param string $file_name Full file name. Example: `12345.tmp`.
	 * @param string $feed_id Feed ID.
	 * @param string $action Maybe: `create`, `append`.
	 * @param string $tmp_dir_name The location of the file on the server.
	 *                             Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/feed1/12345.tmp`.
	 * @param string $trim Maybe: `yes`, `no_trim`.
	 *
	 * @return void
	 */
	public function __construct( $xml_string, $file_name, $feed_id, $action = 'create', $tmp_dir_name = Y4YM_PLUGIN_UPLOADS_DIR_PATH, $trim = 'yes' ) {

		$this->xml_string = $xml_string;
		$tmp_file_path = sprintf( '%1$s/feed%2$s/%3$s', $tmp_dir_name, $feed_id, $file_name );

		$feed_folder = sprintf( '%1$s/feed%2$s', $tmp_dir_name, $feed_id );
		if ( ! wp_mkdir_p( $feed_folder ) ) {
			error_log(
				sprintf( 'ERROR: Y4YM_Write_File : I can\'t create a folder "%s"; Line: %s',
					$feed_folder,
					__LINE__
				),
				0
			);
			$this->tmp_file_path = false;
			return;
		}
		$this->tmp_file_path = $tmp_file_path;

		if ( $action === 'create' ) {
			$this->create_file( $xml_string, $trim );
		} else {
			$this->append_to_file( $xml_string );
		}

	}

	/**
	 * Save tmp file.
	 *
	 * @param string $xml_string
	 * @param string $trim Maybe: `yes`, `no_trim`.
	 *
	 * @return void
	 */
	protected function create_file( $xml_string, $trim ) {

		if ( empty( $xml_string ) ) {
			$xml_string = ' ';
		} else {
			if ( $trim === 'yes' ) {
				$xml_string = trim( $xml_string );
			}
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- A raw file handle is required for flock(LOCK_EX) below; WP_Filesystem has no descriptor-based API, so an OS-level atomic lock is impossible through it. Feed files are large and written concurrently by cron processes, so locking is mandatory.
		$fp = fopen( $this->get_file_path(), "wb" );
		if ( false === $fp ) {
			// Получаем конкретную причину ошибки открытия файла
			$error = error_get_last();
			$error_msg = $error ? $error['message'] : 'Unknown error';
			$msg_for_log = sprintf(
				'ERROR: Y4YM_Write_File : File opening failed (return (bool) false) for "%s". Reason: %s; Line: %s',
				$this->get_file_path(),
				$error_msg,
				__LINE__
			);
			error_log( $msg_for_log, 0 );
			Y4YM_Error_Log::record( $msg_for_log );
			return;
		}

		// Применяем эксклюзивную блокировку
		$locked = flock( $fp, LOCK_EX );
		if ( ! $locked ) {
			// Если блокировка не удалась, получаем причину
			$error = error_get_last();
			$error_msg = $error ? $error['message'] : 'Unknown locking error';
			$msg_for_log = sprintf(
				'ERROR: Y4YM_Write_File : Failed to acquire lock on file "%s". Reason: %s; Line: %s',
				$this->get_file_path(),
				$error_msg,
				__LINE__
			);
			error_log( $msg_for_log, 0 );
			Y4YM_Error_Log::record( $msg_for_log );
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the handle opened by fopen() above to release the OS lock; no WP_Filesystem equivalent exists.
			fclose( $fp );
			$this->result = false;
			return;
		}

		// Записываем данные в файл
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- Writing through the handle locked by flock(LOCK_EX) above preserves the atomic lock-and-write pattern; see the fopen() rationale.
		$written = fwrite( $fp, $xml_string );

		// Проверка на ошибку записи (fwrite возвращает false при ошибке)
		if ( false === $written ) {
			$error = error_get_last();
			$error_msg = $error ? $error['message'] : 'Unknown write error';
			$msg_for_log = sprintf(
				'ERROR: Y4YM_Write_File : Failed to write to file "%s". Reason: %s; Line: %s',
				$this->get_file_path(),
				$error_msg,
				__LINE__
			);
			error_log( $msg_for_log, 0 );
			flock( $fp, LOCK_UN ); // Освобождаем блокировку
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the handle opened by fopen() above to release the OS lock; no WP_Filesystem equivalent exists.
			fclose( $fp );
			$this->result = false;
			return;
		}

		// Освобождаем блокировку
		flock( $fp, LOCK_UN );

		// Закрываем файл
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing the handle opened by fopen() above to release the OS lock; no WP_Filesystem equivalent exists.
		fclose( $fp );

		$this->result = true;

	}

	/**
	 * Append to tmp file.
	 *
	 * @param string $xml_string
	 *
	 * @return void
	 */
	protected function append_to_file( $xml_string ) {

		$fa = file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Append to large feed files requires native streaming: WP_Filesystem has no append method, and reading the whole file into memory risks hitting memory_limit.
			$this->get_file_path(), $xml_string, FILE_APPEND | LOCK_EX
		);

		if ( false == $fa ) { // ! важно именно двойное равенство из за особенностей file_put_contents
			// Получаем последнюю ошибку
			$error = error_get_last();

			// Формируем понятное сообщение об ошибке
			$error_message = sprintf(
				'ERROR: Y4YM_Write_File : Failed to append to file "%s". Reason: %s; Line: %s',
				$this->get_file_path(),
				$error ? $error['message'] : 'Unknown error',
				__LINE__
			);

			// Логируем ошибку в системный лог WordPress или PHP
			error_log( $error_message );

			$this->result = false;
		} else {
			$this->result = true;
		}

	}

	/**
	 * Returns the path to the tmp file.
	 * Example: `/home/site.ru/public_html/wp-content/uploads/y4ym/feed1/12345.tmp`.
	 *
	 * @return string|false
	 */
	protected function get_file_path() {
		return $this->tmp_file_path;
	}

	/**
	 * Returns the result of writing to a file.
	 *
	 * @return bool
	 */
	public function get_result() {
		return $this->result;
	}

}
