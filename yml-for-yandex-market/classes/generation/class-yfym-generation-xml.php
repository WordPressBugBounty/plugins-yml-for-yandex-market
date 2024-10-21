<?php
/**
 * Starts feed generation
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.8.1 (21-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param    string|int     $feed_id - Required
 *
 * @depends                 classes:    YFYM_Get_Unit
 *                                      Get_Paired_Tag
 *                                      WP_Query
 *                                      ZipArchive
 *                                      DOMDocument
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      yfym_optionGET
 *                                      yfym_optionUPD
 *                          constants:  YFYM_SITE_UPLOADS_DIR_PATH
 *                                      YFYM_SITE_UPLOADS_URL
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class YFYM_Generation_XML {
	/**
	 * Feed ID
	 * @var string
	 */
	protected $feed_id;
	/**
	 * XML code
	 * @var string
	 */
	protected $result_xml = '';

	/**
	 * Starts feed generation
	 * 
	 * @param string|int $feed_id - Required
	 */
	public function __construct( $feed_id ) {
		$this->feed_id = (string) $feed_id;
	}

	/**
	 * Write file feed tmp
	 * 
	 * @param string $result_xml - контент, который записываем в файл feed-yml-0-tmp.xml
	 * @param string $mode - тип доступа, который вы запрашиваете у потока (`w+` - чтение и запись, `a` - только запись)
	 * 
	 * @return bool
	 */
	public function write_file_feed_tmp( $result_xml, $mode ) {
		$filename = urldecode( common_option_get( 'yfym_file_file', false, $this->get_feed_id(), 'yfym' ) );
		if ( empty( $filename ) ) {
			$filename = sprintf( '%1$s/%2$sfeed-yml-0-tmp.xml',
				YFYM_SITE_UPLOADS_DIR_PATH,
				$this->get_prefix_feed()
			);
		}

		// если временный файл в папке загрузок есть
		if ( file_exists( $filename ) ) {
			if ( ! $handle = fopen( $filename, $mode ) ) {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; ERROR: %2$s %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Не могу открыть временный файл',
					$filename,
					'class-yfym-generation-xml.php',
					__LINE__
				) );
			}
			if ( false === fwrite( $handle, $result_xml ) ) {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; ERROR: %2$s %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Не могу произвести запись во временный файл',
					$filename,
					'class-yfym-generation-xml.php',
					__LINE__
				) );
			} else {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; SUCCESS: %2$s %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Успешно записан временный файл',
					$filename,
					'class-yfym-generation-xml.php',
					__LINE__
				) );
				return true;
			}
			fclose( $handle );
		} else {
			// файла еще нет. попытаемся создать
			if ( is_multisite() ) {
				$tmp_filename = $this->get_prefix_feed() . 'feed-yml-' . get_current_blog_id() . '-tmp.xml';
			} else {
				$tmp_filename = $this->get_prefix_feed() . 'feed-yml-0-tmp.xml';
			}
			// загружаем временный файл в папку загрузок
			$upload = wp_upload_bits( $tmp_filename, null, $result_xml );
			/**
			 *	для работы с csv или xml требуется в плагине разрешить загрузку таких файлов
			 *	$upload['file'] => '/var/www/wordpress/wp-content/uploads/2010/03/feed-xml.xml', // путь
			 *	$upload['url'] => 'http://site.ru/wp-content/uploads/2010/03/feed-xml.xml', // урл
			 *	$upload['error'] => false, // сюда записывается сообщение об ошибке в случае ошибки
			 */
			// проверим получилась ли запись
			if ( $upload['error'] ) {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; ERROR: %2$s $filename = %3$s, $tmp_filename = %4$s %5$s: %6$s; Файл: %7$s; Строка: %8$s',
					$this->get_feed_id(),
					'При создании временного файла',
					$filename,
					$tmp_filename,
					'Запись вызвала ошибку',
					$upload['error'],
					'class-yfym-generation-xml.php',
					__LINE__
				) );
			} else {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; SUCCESS: %2$s %3$s. Путь файла: %4$s, УРЛ файла: %5$s; Файл: %6$s; Строка: %7$s',
					$this->get_feed_id(),
					'Записали файл',
					$tmp_filename,
					$upload['file'],
					$upload['url'],
					'class-yfym-generation-xml.php',
					__LINE__
				) );
				yfym_optionUPD( 'yfym_file_file', urlencode( $upload['file'] ), $this->get_feed_id(), 'yes', 'set_arr' );
				return true;
			}
		}
		return false;
	}

	/**
	 * Gluing cache files into a single feed
	 * 
	 * @param array $id_arr
	 * 
	 * @return void
	 */
	public function gluing( $id_arr ) {
		/**
		 * $id_arr[$i]['ID'] - ID товара
		 * $id_arr[$i]['post_modified_gmt'] - Время обновления карточки товара
		 * global $wpdb;
		 * $res = $wpdb->get_results(
		 *	"SELECT ID, post_modified_gmt FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish'"
		 * );	
		 */
		$name_dir = YFYM_SITE_UPLOADS_DIR_PATH . '/yfym/feed' . $this->get_feed_id();
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log(
					sprintf(
						'FEED № %s; ERROR: Нет папки yfym! И создать не вышло! $name_dir = %s; Файл: %s; Строка: %s',
						$this->get_feed_id(),
						$name_dir,
						'class-yfym-generation-xml.php',
						__LINE__
						, 0 )
				);
			}
		}

		/** 
		 *	этот блок исправляет потенциальную проблему изменения относительных путей типа:
		 *	/home/c/canpro4d/canpro4d.beget.tech/public_html/wp-content/uploads/yfym/feed2/ids_in_xml.tmp 
		 *	/home/c/canpro4d/canpower.ru/public_html/wp-content/uploads/yfym/feed2/ids_in_xml.tmp
		 **/
		$yfym_file_ids_in_xml = urldecode( common_option_get( 'yfym_file_ids_in_xml', false, $this->get_feed_id(), 'yfym' ) );
		$yfym_file_ids_in_yml = urldecode( common_option_get( 'yfym_file_ids_in_yml', false, $this->get_feed_id(), 'yfym' ) );
		if ( empty( $yfym_file_ids_in_xml ) ||
			$yfym_file_ids_in_xml !== YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_xml.tmp'
		) { // если не указан адрес файла с id-шниками или они не равны
			$yfym_file_ids_in_xml = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_xml.tmp';
			yfym_optionUPD( 'yfym_file_ids_in_xml', urlencode( $yfym_file_ids_in_xml ), $this->get_feed_id(), 'yes', 'set_arr' );
		}
		if ( empty( $yfym_file_ids_in_yml ) ||
			$yfym_file_ids_in_yml !== YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_yml.tmp'
		) { // если не указан адрес файла с id-шниками или они не равны
			$yfym_file_ids_in_yml = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $this->get_feed_id() . '/ids_in_yml.tmp';
			yfym_optionUPD( 'yfym_file_ids_in_yml', urlencode( $yfym_file_ids_in_yml ), $this->get_feed_id(), 'yes', 'set_arr' );
		}

		$yfym_date_save_set = common_option_get( 'yfym_date_save_set', false, $this->get_feed_id(), 'yfym' );
		clearstatcache(); // очищаем кэш дат файлов

		foreach ( $id_arr as $product ) {
			$filename = $name_dir . '/' . $product['ID'] . '.tmp';
			$filename_in_tmp = $name_dir . '/' . $product['ID'] . '-in.tmp'; /* с версии 2.0.0 */
			if ( is_file( $filename ) && is_file( $filename_in_tmp ) ) { // if (file_exists($filename)) {
				$last_upd_tmp_file = filemtime( $filename ); // 1318189167			
				if ( ( $last_upd_tmp_file < strtotime( $product['post_modified_gmt'] ) )
					|| ( $yfym_date_save_set > $last_upd_tmp_file ) ) {
					// Файл кэша обновлен раньше чем время модификации товара
					// или файл обновлен раньше чем время обновления настроек фида
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; NOTICE: %2$s %3$s %4$s. %5$s; Файл: %6$s; Строка: %7$s',
						$this->get_feed_id(),
						'Файл кэша',
						$filename,
						'обновлен РАНЬШЕ чем время модификации товара или время сохранения настроек фида',
						'Обновляем кэш товара',
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					$result_get_unit_obj = new YFYM_Get_Unit( $product['ID'], $this->get_feed_id() );
					$result_xml = $result_get_unit_obj->get_result();
					$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();

					yfym_wf( $result_xml, $product['ID'], $this->get_feed_id(), $ids_in_xml );
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Обновили кэш товара',
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					file_put_contents( $yfym_file_ids_in_xml, $ids_in_xml, FILE_APPEND );
				} else {
					// Файл кэша обновлен позже чем время модификации товара
					// или файл обновлен позже чем время обновления настроек фида
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; NOTICE: %2$s %3$s %4$s. %5$s; Файл: %6$s; Строка: %7$s',
						$this->get_feed_id(),
						'Файл кэша',
						$filename,
						'обновлен ПОЗЖЕ чем время модификации товара или время сохранения настроек фида',
						'Пристыковываем файл кэша без изменений',
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					$result_xml = file_get_contents( $filename );
					$ids_in_xml = file_get_contents( $filename_in_tmp );
					file_put_contents( $yfym_file_ids_in_xml, $ids_in_xml, FILE_APPEND );
				}
			} else { // Файла нет
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; NOTICE: %2$s %3$s %4$s; Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Файла кэша товара',
					$filename,
					'ещё нет! Создаем...',
					'class-yfym-generation-xml.php',
					__LINE__
				) );
				$result_get_unit_obj = new YFYM_Get_Unit( $product['ID'], $this->get_feed_id() );
				$result_xml = $result_get_unit_obj->get_result();
				$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();

				yfym_wf( $result_xml, $product['ID'], $this->get_feed_id(), $ids_in_xml );
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'Создали кэш товара',
					'class-yfym-generation-xml.php',
					__LINE__
				) );
				file_put_contents( $yfym_file_ids_in_xml, $ids_in_xml, FILE_APPEND );
			}
		}
	} // end function gluing()

	/**
	 * Clears the file containing the product IDs that are in the feed
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function clear_file_ids_in_xml( $feed_id ) {
		$yfym_file_ids_in_xml = urldecode( common_option_get( 'yfym_file_ids_in_xml', false, $this->get_feed_id(), 'yfym' ) );
		if ( is_file( $yfym_file_ids_in_xml ) ) {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; NOTICE: %2$s = %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				'Обнуляем файл $yfym_file_ids_in_xml',
				$yfym_file_ids_in_xml,
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			file_put_contents( $yfym_file_ids_in_xml, '' );
		} else {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; WARNING: %2$s = %3$s. %4$s; Файл: %5$s; Строка: %6$s',
				$this->get_feed_id(),
				'Нет файла c idшниками $yfym_file_ids_in_xml',
				$yfym_file_ids_in_xml,
				'Создадим пустой',
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			$yfym_file_ids_in_xml = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id . '/ids_in_xml.tmp';
			$res = file_put_contents( $yfym_file_ids_in_xml, '' );
			if ( false !== $res ) {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; NOTICE: %2$s = %3$s. %4$s; Файл: %5$s; Строка: %6$s',
					$this->get_feed_id(),
					'Файл c idшниками $yfym_file_ids_in_xml',
					$yfym_file_ids_in_xml,
					'успешно создан',
					'class-yfym-generation-xml.php',
					__LINE__
				) );
				yfym_optionUPD( 'yfym_file_ids_in_xml', urlencode( $yfym_file_ids_in_xml ), $feed_id, 'yes', 'set_arr' );
			} else {
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; ERROR: %2$s = %3$s; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Ошибка создания файла $yfym_file_ids_in_xml',
					$yfym_file_ids_in_xml,
					'class-yfym-generation-xml.php',
					__LINE__
				) );
			}
		}
	}

	/**
	 * Run the creation of the feed
	 * 
	 * @return void
	 */
	public function run() {
		$result_xml = '';

		$step_export = (int) common_option_get( 'yfym_step_export', false, $this->get_feed_id(), 'yfym' );
		$status_sborki = (int) yfym_optionGET( 'yfym_status_sborki', $this->get_feed_id() );

		new YFYM_Error_Log( sprintf(
			'FEED № %1$s; %2$s %3$s; Файл: %4$s; Строка: %5$s',
			$this->get_feed_id(),
			'$status_sborki =',
			$status_sborki,
			'class-yfym-generation-xml.php',
			__LINE__
		) );

		switch ( $status_sborki ) {
			case -1: // сборка завершена
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'case -1',
					'class-yfym-generation-xml.php',
					__LINE__
				) );

				wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $this->get_feed_id() ] );
				break;
			case 1: // сборка начата
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'case 1',
					'class-yfym-generation-xml.php',
					__LINE__
				) );

				// создаём пустой временный файл фида т.к заголовок у нас в -1.tmp $result_xml
				$result = $this->write_file_feed_tmp( '', 'w+' );
				if ( true == $result ) {
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'$this->write_file_feed_tmp отработала успешно',
						'class-yfym-generation-xml.php',
						__LINE__
					) );
				} else {
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; ERROR: %2$s %3$s; Файл: %4$s; Строка: %5$s',
						$this->get_feed_id(),
						'$this->write_file_feed_tmp вернула ошибку при записи временного файла фида! $result =',
						$result,
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					$this->stop();
					return;
				}
				$this->clear_file_ids_in_xml( $this->get_feed_id() );
				$filename = sprintf( '%1$s/yfym/feed%2$s/-1.tmp', YFYM_SITE_UPLOADS_DIR_PATH, $this->get_feed_id() );
				file_put_contents( $filename, '' );
				$status_sborki++;
				if ( $status_sborki == 2 ) {
					if ( is_file( $filename ) && is_file( $filename ) ) {
						if ( filesize( $filename ) > 0 ) {
							$result_xml = file_get_contents( $filename );
						} else {
							$result_xml = $this->get_feed_header();
						}
					} else {
						$result_xml = $this->get_feed_header();
					}
					$result = $this->write_file_feed_tmp( $result_xml, 'a' );
				}
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; %2$s %3$s %4$s %5$s; Файл: %6$s; Строка: %7$s',
					$this->get_feed_id(),
					'status_sborki увеличен на',
					$step_export,
					'и равен',
					$status_sborki,
					'class-yfym-generation-xml.php',
					__LINE__
				) );

				yfym_optionUPD( 'yfym_status_sborki', $status_sborki, $this->get_feed_id() );
				break;
			default:
				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
					$this->get_feed_id(),
					'case default',
					'class-yfym-generation-xml.php',
					__LINE__
				) );

				$offset = ( ( $status_sborki - 1 ) * $step_export ) - $step_export; // $status_sborki - $step_export;
				$args = [ 
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => $step_export,
					'offset' => $offset,
					'relation' => 'AND',
					'orderby' => 'ID'
				];
				$whot_export = common_option_get( 'yfym_whot_export', false, $this->get_feed_id(), 'yfym' );
				switch ( $whot_export ) {
					case "vygruzhat":
						$args['meta_query'] = [ 
							[ 
								'key' => 'vygruzhat',
								'value' => 'on'
							]
						];
						break;
					case "xmlset":
						$yfym_xmlset_number = '1';
						$yfym_xmlset_number = apply_filters(
							'yfym_xmlset_number_filter',
							$yfym_xmlset_number,
							$this->get_feed_id()
						);
						$yfym_xmlset_key = '_yfym_xmlset' . $yfym_xmlset_number;
						$args['meta_query'] = [ 
							[ 
								'key' => $yfym_xmlset_key,
								'value' => 'on'
							]
						];
						break;
				}
				$args = apply_filters( 'yfym_query_arg_filter', $args, $this->get_feed_id() );

				new YFYM_Error_Log( sprintf(
					'FEED № %1$s; %2$s = %3$s. $args =>; Файл: %4$s; Строка: %5$s',
					$this->get_feed_id(),
					'Полная сборка. $whot_export',
					$whot_export,
					'class-yfym-generation-xml.php',
					__LINE__
				) );
				new YFYM_Error_Log( $args );

				$featured_query = new \WP_Query( $args );
				$prod_id_arr = [];
				if ( $featured_query->have_posts() ) {
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; %2$s = %3$s; Файл: %4$s; Строка: %5$s',
						$this->get_feed_id(),
						'Вернулось записей',
						count( $featured_query->posts ),
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					// ? если начинать с 0, то может возникнуть ситуация, когда в фиде задублится товар
					for ( $i = 0; $i < count( $featured_query->posts ); $i++ ) {
						$prod_id_arr[ $i ]['ID'] = $featured_query->posts[ $i ]->ID;
						$prod_id_arr[ $i ]['post_modified_gmt'] = $featured_query->posts[ $i ]->post_modified_gmt;
					}
					wp_reset_query(); /* Remember to reset */
					unset( $featured_query ); // чутка освободим память
					$this->gluing( $prod_id_arr );
					$status_sborki++; // = $status_sborki + $step_export;
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; %2$s %3$s %4$s %5$s; Файл: %6$s; Строка: %7$s',
						$this->get_feed_id(),
						'status_sborki увеличен на',
						$step_export,
						'и равен',
						$status_sborki,
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					yfym_optionUPD( 'yfym_status_sborki', $status_sborki, $this->get_feed_id() );
				} else { // если постов нет, пишем концовку файла
					$result_xml = $this->get_feed_footer();
					$result = $this->write_file_feed_tmp( $result_xml, 'a' );
					new YFYM_Error_Log( sprintf(
						'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$this->get_feed_id(),
						'Файл фида готов. Осталось только переименовать временный файл в основной',
						'class-yfym-generation-xml.php',
						__LINE__
					) );
					$res_rename = $this->rename_feed_file();
					$this->archiving( $res_rename );
					$this->stop();

					// ? в качестве эксперимента для борьбы с задублением товаров крутанём быструю сборку
					$del_identical_ids = common_option_get( 'yfym_del_identical_ids', false, $this->get_feed_id(), 'yfym' );
					if ( $del_identical_ids == 'enabled' ) {
						$this->clear_file_ids_in_xml( $this->get_feed_id() );
						$this->onlygluing( true );
					}
				}
			// end default
		} // end switch($status_sborki)
		return; // final return from public function phase()
	}

	/**
	 * Stops the creation of the feed
	 * 
	 * @return void
	 */
	public function stop() {
		if ( 'once' === common_option_get( 'yfym_run_cron', false, $this->get_feed_id(), 'yfym' ) ) {
			// если была одноразовая сборка - переводим переключатель в `отключено`
			common_option_upd( 'yfym_run_cron', 'disabled', 'no', $this->get_feed_id(), 'yfym' );
			common_option_upd( 'yfym_status_cron', 'off', 'no', $this->get_feed_id(), 'yfym' );
		}
		$status_sborki = -1;
		yfym_optionUPD( 'yfym_status_sborki', $status_sborki, $this->get_feed_id() );
		wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $this->get_feed_id() ] );
		do_action( 'yfym_after_construct', $this->get_feed_id(), 'full' ); // сборка закончена
	}

	/**
	 * Проверим, нужна ли пересборка фида при обновлении поста
	 * 
	 * @param mixed $post_id
	 * 
	 * @return bool
	 */
	public function check_ufup( $post_id ) {
		$yfym_ufup = common_option_get( 'yfym_ufup', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_ufup === 'on' ) {
			$status_sborki = (int) yfym_optionGET( 'yfym_status_sborki', $this->get_feed_id() );
			if ( $status_sborki > -1 ) { // если идет сборка фида - пропуск
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get feed header
	 * 
	 * @return string
	 */
	protected function get_feed_header() {
		$result_xml = '';
		// обнуляем лог ошибок
		// common_option_upd( 'yfym_critical_errors', '', 'no', $this->get_feed_id(), 'yfym' );
		$yfym_cache = common_option_get( 'yfym_cache', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_cache === 'enabled' ) {
			$unixtime = (string) current_time( 'timestamp', 1 ); // 1335808087 - временная зона GMT (Unix формат)
			common_option_upd( 'yfym_date_save_set', $unixtime, 'no', $this->get_feed_id(), 'yfym' );
		}

		$unixtime = (string) current_time( 'Y-m-d H:i' ); // время в unix формате 2022-03-21 17:47
		$rfc_3339_time = (string) current_time( 'c' ); // 2022-07-17T17:47:19+03:00
		$rfc_3339_short_time = (string) current_time( 'Y-m-d\TH:i' ); // 2022-07-17T17:47
		common_option_upd( 'yfym_date_sborki', $unixtime, 'no', $this->get_feed_id(), 'yfym' );
		$shop_name = stripslashes( common_option_get( 'yfym_shop_name', false, $this->get_feed_id(), 'yfym' ) );
		$company_name = stripslashes( common_option_get( 'yfym_company_name', false, $this->get_feed_id(), 'yfym' ) );

		new YFYM_Error_Log( sprintf(
			'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			'Пиступаем к формированию кода в шапке сайта',
			'class-yfym-generation-xml.php',
			__LINE__
		) );

		$result_xml .= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
		$yfym_format_date = common_option_get( 'yfym_format_date', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_format_date === 'unixtime' ) {
			$catalog_date = $unixtime;
		} else if ( $yfym_format_date === 'rfc_short' ) {
			$catalog_date = $rfc_3339_short_time;
		} else {
			$catalog_date = $rfc_3339_time;
		}
		$result_xml .= new Get_Open_Tag( 'yml_catalog', [ 'date' => $catalog_date ] );
		$result_xml .= new Get_Open_Tag( 'shop' );
		$result_xml .= new Get_Paired_Tag( 'name', esc_html( $shop_name ) );
		$result_xml .= new Get_Paired_Tag( 'company', esc_html( $company_name ) );
		$res_home_url = home_url( '/' );
		$res_home_url = apply_filters( 'yfym_home_url', $res_home_url, $this->get_feed_id() );
		$result_xml .= new Get_Paired_Tag( 'url', yfym_replace_domain( $res_home_url, $this->get_feed_id() ) );
		$result_xml .= new Get_Paired_Tag( 'platform', 'WordPress - YML for Yandex Market' );
		$result_xml .= new Get_Paired_Tag( 'version', get_bloginfo( 'version' ) );

		if ( class_exists( 'WOOCS' ) ) {
			$yfym_wooc_currencies = common_option_get( 'yfym_wooc_currencies', false, $this->get_feed_id(), 'yfym' );
			if ( $yfym_wooc_currencies !== '' ) {
				global $WOOCS;
				$WOOCS->set_currency( $yfym_wooc_currencies );
			}
		}

		/* общие параметры */
		$yfym_currencies = common_option_get( 'yfym_currencies', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_currencies !== 'disabled' ) {
			$res = get_woocommerce_currency(); // получаем валюта магазина
			$rate_cb = '';
			switch ( $res ) {
				case "RUB":
					$currencyId_yml = "RUR";
					break;
				case "USD":
					$currencyId_yml = "USD";
					$rate_cb = "CB";
					break;
				case "EUR":
					$currencyId_yml = "EUR";
					$rate_cb = "CB";
					break;
				case "UAH":
					$currencyId_yml = "UAH";
					break;
				case "KZT":
					$currencyId_yml = "KZT";
					break;
				case "UZS":
					$currencyId_yml = "UZS";
					break;
				case "BYN":
					$currencyId_yml = "BYN";
					break;
				case "BYR":
					$currencyId_yml = "BYN";
					break;
				case "ABC":
					$currencyId_yml = "BYN";
					break;
				default:
					$currencyId_yml = "RUB";
			}
			$rate_cb = apply_filters( 'y4ym_f_rate_cb', $rate_cb, $this->get_feed_id() );
			$currencyId_yml = apply_filters( 'yfym_currency_id', $currencyId_yml, $this->get_feed_id() );
			if ( $rate_cb == '' ) {
				$result_xml .= new Get_Open_Tag( 'currencies' );
				$result_xml .= new Get_Open_Tag( 'currency', [ 'id' => $currencyId_yml, 'rate' => '1' ], true );
				$result_xml .= new Get_Closed_Tag( 'currencies' );
			} else {
				$result_xml .= new Get_Open_Tag( 'currencies' );
				$result_xml .= new Get_Open_Tag( 'currency', [ 'id' => 'RUB', 'rate' => '1' ], true );
				$result_xml .= new Get_Open_Tag( 'currency', [ 'id' => $currencyId_yml, 'rate' => $rate_cb ], true );
				$result_xml .= new Get_Closed_Tag( 'currencies' );
			}
		}

		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_yml_rules !== 'sales_terms' && $yfym_yml_rules !== 'sets' ) {
			$result_xml .= $this->get_categories();
		}

		$yfym_pickup_options = common_option_get( 'yfym_pickup_options', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_pickup_options === 'on' ) {
			$pickup_cost = common_option_get( 'yfym_pickup_cost', false, $this->get_feed_id(), 'yfym' );
			$pickup_days = common_option_get( 'yfym_pickup_days', false, $this->get_feed_id(), 'yfym' );
			$attr_arr = [ 
				'cost' => $pickup_cost,
				'days' => $pickup_days
			];
			$pickup_order_before = common_option_get( 'yfym_pickup_order_before', false, $this->get_feed_id(), 'yfym' );
			if ( $pickup_order_before !== '' ) {
				$attr_arr['order-before'] = $pickup_order_before;
			}
			$result_xml .= new Get_Open_Tag( 'pickup-options' );
			$result_xml .= new Get_Open_Tag( 'option', $attr_arr, true );
			$result_xml .= new Get_Closed_Tag( 'pickup-options' );
		}

		if ( $yfym_yml_rules === 'sbermegamarket' ) {
			$shipment_options = common_option_get( 'yfym_shipment_options', false, $this->get_feed_id(), 'yfym' );
			if ( $shipment_options === 'enabled' ) {
				$shipment_days = common_option_get( 'yfym_shipment_days', false, $this->get_feed_id(), 'yfym' );
				$order_before = common_option_get( 'yfym_order_before', false, $this->get_feed_id(), 'yfym' );
				$attr_arr = [ 
					'days' => $shipment_days
				];
				if ( $order_before !== '' ) {
					$attr_arr['order-before'] = $order_before;
				}
				$result_xml .= new Get_Open_Tag( 'shipment-options' );
				$result_xml .= new Get_Open_Tag( 'option', $attr_arr, true );
				$result_xml .= new Get_Closed_Tag( 'shipment-options' );
			}
		} else {
			$yfym_delivery_options = common_option_get( 'yfym_delivery_options', false, $this->get_feed_id(), 'yfym' );
			if ( $yfym_delivery_options === 'on' ) {
				$delivery_cost = common_option_get( 'yfym_delivery_cost', false, $this->get_feed_id(), 'yfym' );
				$delivery_days = common_option_get( 'yfym_delivery_days', false, $this->get_feed_id(), 'yfym' );
				$order_before = common_option_get( 'yfym_order_before', false, $this->get_feed_id(), 'yfym' );
				$attr_arr = [ 
					'cost' => $delivery_cost,
					'days' => $delivery_days
				];
				if ( $order_before !== '' ) {
					$attr_arr['order-before'] = $order_before;
				}
				$result_xml .= new Get_Open_Tag( 'delivery-options' );
				$result_xml .= new Get_Open_Tag( 'option', $attr_arr, true );

				$yfym_delivery_options2 = common_option_get( 'yfym_delivery_options2', false, $this->get_feed_id(), 'yfym' );
				if ( $yfym_delivery_options2 === 'on' ) {
					$delivery_cost2 = common_option_get( 'yfym_delivery_cost2', false, $this->get_feed_id(), 'yfym' );
					$delivery_days2 = common_option_get( 'yfym_delivery_days2', false, $this->get_feed_id(), 'yfym' );
					$order_before2 = common_option_get( 'yfym_order_before2', false, $this->get_feed_id(), 'yfym' );
					$attr_arr2 = [ 
						'cost' => $delivery_cost2,
						'days' => $delivery_days2
					];
					if ( $order_before2 !== '' ) {
						$attr_arr['order-before'] = $order_before2;
					}
					$result_xml .= new Get_Open_Tag( 'option', $attr_arr2, true );
				}
				$result_xml .= new Get_Closed_Tag( 'delivery-options' );
			}
		}
		// магазин 18+
		$adult = common_option_get( 'yfym_adult', false, $this->get_feed_id(), 'yfym' );
		if ( $adult === 'yes' ) {
			$result_xml .= new Get_Paired_Tag( 'adult', 'true' );
		}

		/* end общие параметры */
		do_action( 'yfym_before_offers', $this->get_feed_id() );

		$result_xml = apply_filters(
			'y4ym_f_before_offers',
			$result_xml,
			[ 
				'rules' => $yfym_yml_rules
			],
			$this->get_feed_id()
		);

		/* индивидуальные параметры товара */
		$result_xml .= new Get_Open_Tag( 'offers' );
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$WOOCS->reset_currency();
		}
		do_action( 'yfym_before_offers', $this->get_feed_id() );

		new Y4YM_Write_File( $result_xml, '-1', $this->get_feed_id() );
		return $result_xml;
	}

	/**
	 * Get YML list of categories 
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	function get_categories( $result_xml = '' ) {
		$categories_yml = '';
		$all_parent_flag = false;
		$all_parent_flag = apply_filters( 'y4ym_f_all_parent_flag', $all_parent_flag, $this->get_feed_id() );
		$args_terms_arr = [ 
			'hide_empty' => false,
			'taxonomy' => 'product_cat'
		];
		$args_terms_arr = apply_filters( 'yfym_args_terms_arr_filter', $args_terms_arr, $this->get_feed_id() );
		$terms = get_terms( $args_terms_arr );
		$count = count( $terms );
		if ( $count > 0 ) {
			foreach ( $terms as $term ) {
				$skip_flag_category = false;
				$skip_flag_category = apply_filters(
					'y4ym_f_skip_flag_category',
					$skip_flag_category,
					[ 
						'terms' => $terms,
						'term' => $term
					],
					$this->get_feed_id()
				);
				if ( true === $skip_flag_category ) {
					continue;
				}
				if ( $term->parent == 0 || true === $all_parent_flag ) {
					// у категории НЕТ родительской категории или настройками задано делать все родительскими
					$categories_attr_arr = [ 
						'id' => $term->term_id
					];
					$categories_attr_arr = apply_filters(
						'y4ym_f_categories_attr_arr',
						$categories_attr_arr,
						[ 
							'terms' => $terms,
							'term' => $term
						],
						$this->get_feed_id()
					);
					$categories_yml .= new Get_Paired_Tag( 'category', $term->name, $categories_attr_arr );
				} else {
					// у категории ЕСТЬ родительская категория
					$categories_attr_arr = [ 
						'id' => $term->term_id,
						'parentId' => $term->parent
					];
					$categories_attr_arr = apply_filters(
						'y4ym_f_categories_attr_arr',
						$categories_attr_arr,
						[ 
							'terms' => $terms,
							'term' => $term
						],
						$this->get_feed_id()
					);
					$categories_yml .= new Get_Paired_Tag( 'category', $term->name, $categories_attr_arr );
				}
			}
		}

		$result_xml .= new Get_Open_Tag( 'categories' );
		$categories_yml = apply_filters(
			'y4ym_f_categories',
			$categories_yml,
			[],
			$this->get_feed_id()
		);
		$result_xml .= $categories_yml;
		$result_xml = apply_filters( 'yfym_append_categories_filter', $result_xml, $this->get_feed_id() );
		$result_xml .= new Get_Closed_Tag( 'categories' );

		return $result_xml;
	}

	/**
	 * Get YML list of collections
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	function get_collections( $result_xml = '' ) {
		$collections_yml = '';
		$args_terms_arr = [ 
			'hide_empty' => false,
			'taxonomy' => 'yfym_collection'
		];
		$args_terms_arr = apply_filters( 'y4ym_f_collection_args_terms_arr', $args_terms_arr, $this->get_feed_id() );
		$terms = get_terms( $args_terms_arr );
		$count = count( $terms );
		if ( $count > 0 ) {
			foreach ( $terms as $term ) {
				$skip_flag_collection = false;
				$skip_flag_collection = apply_filters(
					'y4ym_f_skip_flag_collection',
					$skip_flag_collection,
					[ 
						'terms' => $terms,
						'term' => $term
					],
					$this->get_feed_id()
				);
				if ( true === $skip_flag_collection ) {
					continue;
				}
				// у категории НЕТ родительской категории или настройками задано делать все родительскими
				$collection_attr_arr = [ 
					'id' => $term->term_id
				];
				$collection_attr_arr = apply_filters(
					'y4ym_f_collection_attr_arr',
					$collection_attr_arr,
					[ 
						'terms' => $terms,
						'term' => $term
					],
					$this->get_feed_id()
				);
				$collections_yml .= new Get_Open_Tag( 'collection', [ 'id' => $term->term_id ] );
				if ( get_term_meta( $term->term_id, 'yfym_collection_url', true ) !== '' ) {
					$yfym_collection_url = get_term_meta( $term->term_id, 'yfym_collection_url', true );
					$collections_yml .= new Get_Paired_Tag( 'url', htmlspecialchars( $yfym_collection_url ) );
				}
				if ( get_term_meta( $term->term_id, 'yfym_collection_picture', true ) !== '' ) {
					$yfym_collection_picture = get_term_meta( $term->term_id, 'yfym_collection_picture', true );
					$collections_yml .= new Get_Paired_Tag( 'picture', htmlspecialchars( $yfym_collection_picture ) );
				}
				$collections_yml .= new Get_Paired_Tag( 'name', $term->name );
				if ( ! empty( $term->description ) ) {
					$collections_yml .= new Get_Paired_Tag( 'description', $term->description );
				}
				$collections_yml .= new Get_Closed_Tag( 'collection' );
			}
		}

		$result_xml .= new Get_Open_Tag( 'collections' );
		$collections_yml = apply_filters(
			'y4ym_f_collection',
			$collections_yml,
			[],
			$this->get_feed_id()
		);
		$result_xml .= $collections_yml;
		$result_xml = apply_filters( 'yfym_append_collection_filter', $result_xml, $this->get_feed_id() );
		$result_xml .= new Get_Closed_Tag( 'collections' );

		return $result_xml;
	}

	/**
	 * Getting product IDs in an XML feed
	 * 
	 * @param string $file_content
	 * 
	 * @return array
	 */
	protected function get_ids_in_xml( $file_content ) {
		/**
		 * $file_content - содержимое файла (Обязательный параметр)
		 * Возвращает массив в котором ключи - это id товаров в БД WordPress, попавшие в фид
		 */
		$res_arr = [];
		$file_content_string_arr = explode( PHP_EOL, $file_content );
		for ( $i = 0; $i < count( $file_content_string_arr ) - 1; $i++ ) {
			$r_arr = explode( ';', $file_content_string_arr[ $i ] );
			$res_arr[ $r_arr[0] ] = '';
		}
		return $res_arr;
	}

	/**
	 * Get body of XML feed
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_feed_body( $result_xml = '' ) {
		$yfym_file_ids_in_xml = urldecode( common_option_get( 'yfym_file_ids_in_xml', false, $this->get_feed_id(), 'yfym' ) );
		$file_content = file_get_contents( $yfym_file_ids_in_xml );
		$ids_in_xml_arr = $this->get_ids_in_xml( $file_content );

		$name_dir = YFYM_SITE_UPLOADS_DIR_PATH . '/yfym/feed' . $this->get_feed_id();

		foreach ( $ids_in_xml_arr as $key => $value ) {
			$product_id = (int) $key;
			$filename = $name_dir . '/' . $product_id . '.tmp';
			$result_xml .= file_get_contents( $filename );
		}

		$offer_count = count( $ids_in_xml_arr ); // число товаров попавших в фид
		common_option_upd( 'yfym_count_products_in_feed', $offer_count, 'no', $this->get_feed_id(), 'yfym' );

		return $result_xml;
	}

	/**
	 * Get feed footer
	 * 
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_feed_footer( $result_xml = '' ) {
		$result_xml .= $this->get_feed_body( $result_xml );

		$result_xml .= new Get_Closed_Tag( 'offers' );
		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_yml_rules == 'yandex_direct' ||
			$yfym_yml_rules == 'yandex_direct_free_from' ||
			$yfym_yml_rules == 'yandex_direct_combined' ||
			$yfym_yml_rules == 'all_elements' ) {
			$yfym_collection_id = common_option_get( 'yfym_collection_id', false, $this->get_feed_id(), 'yfym' );
			if ( 'enabled' === $yfym_collection_id ) {
				$result_xml .= $this->get_collections();
			}
		}
		$result_xml = apply_filters( 'yfym_after_offers_filter', $result_xml, $this->get_feed_id() );
		$result_xml .= new Get_Closed_Tag( 'shop' );
		$result_xml .= new Get_Closed_Tag( 'yml_catalog' );

		yfym_optionUPD( 'yfym_date_sborki_end', current_time( 'Y-m-d H:i' ), $this->get_feed_id(), 'yes', 'set_arr' );

		return $result_xml;
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	protected function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get prefix of feed
	 * 
	 * @return string
	 */
	protected function get_prefix_feed() {
		if ( $this->get_feed_id() === '1' ) {
			return '';
		} else {
			return $this->get_feed_id();
		}
	}

	/**
	 * Summary of onlygluing
	 * 
	 * @param bool $without_header - Optional
	 * 
	 * @return void
	 */
	public function onlygluing( $without_header = false ) {
		if ( true === $without_header ) {
			$filename = sprintf( '%1$s/yfym/feed%2$s/-1.tmp', YFYM_SITE_UPLOADS_DIR_PATH, $this->get_feed_id() );
			if ( is_file( $filename ) && is_file( $filename ) ) {
				if ( filesize( $filename ) > 0 ) {
					$result_xml = file_get_contents( $filename );
				} else {
					$result_xml = $this->get_feed_header();
				}
			} else {
				$result_xml = $this->get_feed_header();
			}
		} else {
			$result_xml = $this->get_feed_header();
		}

		/* создаем файл или перезаписываем старый удалив содержимое */
		$result = $this->write_file_feed_tmp( $result_xml, 'w+' );
		if ( false === $result ) {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; $this->write_file вернула ошибку! $result = %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				$result,
				'class-yfym-generation-xml.php',
				__LINE__
			) );
		}

		yfym_optionUPD( 'yfym_status_sborki', '-1', $this->get_feed_id() );
		$whot_export = common_option_get( 'yfym_whot_export', false, $this->get_feed_id(), 'yfym' );

		$result_xml = '';
		$step_export = -1;
		$prod_id_arr = [];

		if ( $whot_export === 'vygruzhat' ) {
			$args = [ 
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => $step_export, // сколько выводить товаров
				// 'offset' => $offset,
				'relation' => 'AND',
				'orderby' => 'ID',
				'fields' => 'ids',
				'meta_query' => [ 
					[ 
						'key' => 'vygruzhat',
						'value' => 'on'
					]
				]
			];
		} else { //  if ($whot_export == 'all' || $whot_export == 'simple')
			$args = [ 
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => $step_export, // сколько выводить товаров
				// 'offset' => $offset,
				'relation' => 'AND',
				'orderby' => 'ID',
				'fields' => 'ids'
			];
		}

		$args = apply_filters( 'yfym_query_arg_filter', $args, $this->get_feed_id() );
		new YFYM_Error_Log( sprintf(
			'FEED № %1$s; Быстрая сборка. $whot_export = %2$s; Файл: %3$s; Строка: %4$s',
			$this->get_feed_id(),
			$whot_export,
			'class-yfym-generation-xml.php',
			__LINE__
		) );
		new YFYM_Error_Log( $args );
		$featured_query = new \WP_Query( $args );

		global $wpdb;
		if ( $featured_query->have_posts() ) {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; Вернулось записей = %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				count( $featured_query->posts ),
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			for ( $i = 0; $i < count( $featured_query->posts ); $i++ ) {
				/**
				 *	если не юзаем 'fields'  => 'ids'
				 *	$prod_id_arr[$i]['ID'] = $featured_query->posts[$i]->ID;
				 *	$prod_id_arr[$i]['post_modified_gmt'] = $featured_query->posts[$i]->post_modified_gmt;
				 */
				$cur_id = $featured_query->posts[ $i ];
				$prod_id_arr[ $i ]['ID'] = $cur_id;
				$res = $wpdb->get_results(
					$wpdb->prepare( "SELECT post_modified_gmt FROM $wpdb->posts WHERE id=%d", $cur_id ), ARRAY_A
				);
				$prod_id_arr[ $i ]['post_modified_gmt'] = $res[0]['post_modified_gmt'];
				// get_post_modified_time('Y-m-j H:i:s', true, $featured_query->posts[$i]);
			}
			wp_reset_query(); /* Remember to reset */
			unset( $featured_query ); // чутка освободим память
		}
		if ( ! empty( $prod_id_arr ) ) {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'onlygluing передала управление this->gluing',
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			$this->gluing( $prod_id_arr );
		}

		// если постов нет, пишем концовку файла
		$result_xml = $this->get_feed_footer();
		$result = $this->write_file_feed_tmp( $result_xml, 'a' );
		$res_rename = $this->rename_feed_file();
		$this->archiving( $res_rename );

		$this->stop();
	} // end function onlygluing()

	/**
	 * Перименовывает временный файл фида в основной
	 * 
	 * @return array|false
	 */
	private function rename_feed_file() {
		$feed_file_meta = new YFYM_Feed_File_Meta( $this->get_feed_id() );
		$file_feed_name = $feed_file_meta->get_feed_filename();

		// /home/site.ru/public_html/wp-content/uploads/feed-yml-0.xml
		$feed_basedir_old = urldecode( common_option_get( 'yfym_file_file', false, $this->get_feed_id(), 'yfym' ) );

		// /home/site.ru/public_html/wp-content/uploads/feed-yml-0.xml
		// ? надо придумать как поулчить урл загрузок конкретного блога, например, используя BLOGUPLOADDIR
		$feed_basedir_new = sprintf(
			'%1$s/%2$s.%3$s', YFYM_SITE_UPLOADS_DIR_PATH, $file_feed_name, $feed_file_meta->get_feed_extension()
		);

		// https://site.ru/wp-content/uploads/feed-yml-2.xml
		$feed_url_new = sprintf(
			'%1$s/%2$s.%3$s', YFYM_SITE_UPLOADS_URL, $file_feed_name, $feed_file_meta->get_feed_extension()
		);

		$file_name = $file_feed_name . "." . $feed_file_meta->get_feed_extension();
		$file_name_zip = $file_feed_name . ".zip";

		if ( false === $this->doom_check( $feed_basedir_old ) ) {
			$err_msg = sprintf( '%1$s $feed_basedir_new = %2$s. %3$s $feed_basedir_old = %4$s',
				'Нарушена структура DOOM-дерева нового фида',
				$feed_basedir_new,
				'Оставляем прошлую версию фида',
				$feed_basedir_old
			);
			common_option_upd( 'yfym_critical_errors', $err_msg, 'no', $this->get_feed_id(), 'yfym' );
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; ERROR: %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				$err_msg,
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			return false;
		} else {
			common_option_upd( 'yfym_critical_errors', '', 'no', $this->get_feed_id(), 'yfym' );
		}

		if ( false === rename( $feed_basedir_old, $feed_basedir_new ) ) {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; ERROR: Не могу переименовать файл фида из %2$s в %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				$feed_basedir_old,
				$feed_basedir_new,
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			return false;
		} else {
			yfym_optionUPD( 'yfym_file_url', urlencode( $feed_url_new ), $this->get_feed_id(), 'yes', 'set_arr' );
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; SUCCESS: Файл фида успешно переименован из %2$s в %3$s; Файл: %4$s; Строка: %5$s',
				$this->get_feed_id(),
				$feed_basedir_old,
				$feed_basedir_new,
				'class-yfym-generation-xml.php',
				__LINE__
			) );

			return [ 
				'file_name_zip' => $file_name_zip,
				'file_name' => $file_name,
				'file_url' => $feed_url_new,
				'file_basedir' => $feed_basedir_new
			];
		}
	}

	/**
	 * Проверяет целостность DOOM-дерева временного фида
	 * 
	 * @return bool - `true` - дерево цело; `false` - структура дерева битая
	 */
	private function doom_check( $file_path ) {
		$doc = new \DOMDocument();
		if ( @$doc->load( $file_path ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Archiving to ZIP
	 * 
	 * @param mixed $res_rename
	 * 
	 * @return void
	 */
	private function archiving( $res_rename ) {
		$archive_to_zip = common_option_get( 'yfym_archive_to_zip', false, $this->get_feed_id(), 'yfym' );
		if ( $archive_to_zip === 'enabled' && is_array( $res_rename ) ) {
			new YFYM_Error_Log( sprintf( 'FEED №%1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'Приступаем к архивированию файла;',
				'class-yfym-generation-xml.php',
				__LINE__
			) );
			$zip = new ZipArchive();
			$zip->open(
				YFYM_SITE_UPLOADS_DIR_PATH . '/' . $res_rename['file_name_zip'],
				ZipArchive::CREATE | ZipArchive::OVERWRITE
			);
			$zip->addFile( $res_rename['file_basedir'], $res_rename['file_name'] );
			$zip->close();
			yfym_optionUPD(
				'yfym_file_url',
				urlencode( YFYM_SITE_UPLOADS_URL . '/' . $res_rename['file_name_zip'] ),
				$this->get_feed_id(),
				'yes',
				'set_arr'
			);
			new YFYM_Error_Log( sprintf( 'FEED №%1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$this->get_feed_id(),
				'SUCCESS: Архивирование прошло успешно;',
				'class-yfym-generation-xml.php',
				__LINE__
			) );
		}
	}
}