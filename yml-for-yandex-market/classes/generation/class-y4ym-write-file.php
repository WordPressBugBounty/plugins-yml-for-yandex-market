<?php
/**
 * Writes files (`tmp`, `xml` and etc)
 *
 * @package                 iCopyDoc Plugins (v1, core 13-11-2023)
 * @subpackage              YML for Yandex Market
 * @since                   4.1.0
 * 
 * @version                 4.4.1 (11-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param     string        $xml_string - Required
 * @param     string        $file_name - Required
 * @param     string        $feed_id - Optional
 * @param     string        $action - Optional
 * @param     string        $tmp_dir_name - Optional
 *
 * @depends                 classes     
 *                          traits      
 *                          methods     
 *                          functions:  
 *                          constants:  YFYM_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    
 *                          actions:    
 *                          filters:    
 */
defined( 'ABSPATH' ) || exit;

final class Y4YM_Write_File {
	/**
	 * Text to tmp
	 * @var string
	 */
	protected $xml_string;
	/**
	 * Path to the tmp file
	 * @var string
	 */
	protected $tmp_file_path; // /home/site.ru/public_html/wp-content/uploads/yfym/feed/12345.tmp

	/**
	 * Writes files (`tmp`, `xml` and etc)
	 * 
	 * @param string $xml_string - Required
	 * @param string $file_name - Required
	 * @param string $feed_id - Optional
	 * @param string $action - Optional
	 * @param string $tmp_dir_name - Optional
	 * 
	 * @return void
	 */
	public function __construct( $xml_string, $file_name, $feed_id, $action = 'create', $tmp_dir_name = YFYM_PLUGIN_UPLOADS_DIR_PATH ) {
		$this->xml_string = $xml_string;
		if ( is_dir( $tmp_dir_name ) ) {
			$this->tmp_file_path = sprintf( '%1$s/feed%2$s/%3$s.tmp', $tmp_dir_name, $feed_id, $file_name );
		} else {
			if ( mkdir( $tmp_dir_name ) ) {
				$this->tmp_file_path = sprintf( '%1$s/feed%2$s/%3$s.tmp', $tmp_dir_name, $feed_id, $file_name );
			} else {
				$this->tmp_file_path = false;
				error_log( 'ERROR: Y4YM_Write_File : No folder "' . $tmp_dir_name . '"; Line: ' . __LINE__, 0 );
			}
		}

		if ( false === $this->get_file_path() ) {
			return;
		}

		if ( $action === 'create' ) {
			$this->create_file( $xml_string );
		} else {
			$this->append_to_file( $xml_string );
		}
	}

	/**
	 * Save tmp file
	 * 
	 * @param string $xml_string
	 * 
	 * @return void
	 */
	protected function create_file( $xml_string ) {
		if ( empty( $xml_string ) ) {
			$xml_string = ' ';
		}
		$fp = fopen( $this->get_file_path(), "w" );
		if ( false === $fp ) {
			error_log(
				'ERROR: Y4YM_Write_File : File opening return (bool) false "' . $this->get_file_path() . '"; Line: ' . __LINE__, 0
			);
		} else {
			fwrite( $fp, $xml_string ); // записываем в файл текст
			fclose( $fp ); // закрываем
		}
	}

	/**
	 * Append to tmp file
	 * 
	 * @param string $xml_string
	 * 
	 * @return void
	 */
	protected function append_to_file( $xml_string ) {
		file_put_contents(
			$this->get_file_path(), $xml_string, FILE_APPEND
		);
	}

	/**
	 * Returns the path to the tmp file
	 * 
	 * @return string
	 */
	protected function get_file_path() {
		return $this->tmp_file_path;
	}
}