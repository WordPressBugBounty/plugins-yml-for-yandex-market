<?php
/**
 * The class return the Settings page of the plugin YML for Yandex Market
 *
 * @package                 iCopyDoc Plugins (v1.1, core 10-10-2024)
 * @subpackage              YML for Yandex Market
 * @since                   0.1.0
 * 
 * @version                 4.8.0 (10-10-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param                   
 *
 * @depends                 classes:    Y4YM_Data_Arr
 *                                      YFYM_Error_Log 
 *                                      YFYM_WP_List_Table
 *                                      YFYM_Settings_Feed_WP_List_Table
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                                      yfym_optionGET
 *                                      yfym_optionUPD
 *                                      yfym_optionDEL
 *                          constants:  YFYM_PLUGIN_UPLOADS_DIR_PATH
 *                          options:    
 *
 */
defined( 'ABSPATH' ) || exit;

class Y4YM_Settings_Page {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.8.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    4.8.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Allowed HTML tags for use in `wp_kses()`.
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	const ALLOWED_HTML_ARR = [ 
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
		'kbd' => [ 'class' => true ]
	];

	/**
	 * Feed ID
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $feed_id;

	/**
	 * The value of the current tab
	 * 
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $cur_tab = 'main_tab';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		if ( isset( $_GET['feed_id'] ) ) {
			if ( preg_match( '/^[0-9]+$/', sanitize_key( $_GET['feed_id'] ) ) ) {
				$this->feed_id = sanitize_key( $_GET['feed_id'] );
			} else {
				if ( empty( yfym_get_first_feed_id() ) ) {
					$this->feed_id = '1';
				} else {
					$this->feed_id = yfym_get_first_feed_id();
				}
			}
		} else {
			if ( empty( yfym_get_first_feed_id() ) ) {
				$this->feed_id = '1';
			} else {
				$this->feed_id = yfym_get_first_feed_id();
			}
		}

		if ( isset( $_GET['tab'] ) ) {
			$this->cur_tab = sanitize_text_field( $_GET['tab'] );
		}

		$this->listen_submit();
		$this->print_view_html_form();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    4.8.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Icd_Seo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Icd_Seo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/icd-seo-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    4.8.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Icd_Seo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Icd_Seo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/icd-seo-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * The function listens for the send buttons
	 * 
	 * @return void
	 */
	private function listen_submit() {
		// массовое удаление фидов по чекбоксу checkbox_xml_file
		if ( isset( $_GET['yfym_form_id'] ) && ( $_GET['yfym_form_id'] === 'yfym_wp_list_table' ) ) {
			if ( is_array( $_GET['checkbox_xml_file'] ) && ! empty( $_GET['checkbox_xml_file'] ) ) {
				if ( $_GET['action'] === 'delete' || $_GET['action2'] === 'delete' ) {
					$checkbox_xml_file_arr = $_GET['checkbox_xml_file'];
					$yfym_settings_arr = yfym_optionGET( 'yfym_settings_arr' );
					for ( $i = 0; $i < count( $checkbox_xml_file_arr ); $i++ ) {
						$feed_id = $checkbox_xml_file_arr[ $i ];
						unset( $yfym_settings_arr[ $feed_id ] );
						wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] ); // отключаем крон
						wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $feed_id ] ); // отключаем крон
						remove_directory( YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id );
						yfym_optionDEL( 'yfym_status_sborki', $i );

						$yfym_registered_feeds_arr = yfym_optionGET( 'yfym_registered_feeds_arr' );
						// первый элемент не проверяем, тк. там инфо по последнему id
						for ( $n = 1; $n < count( $yfym_registered_feeds_arr ); $n++ ) {
							if ( $yfym_registered_feeds_arr[ $n ]['id'] === $feed_id ) {
								unset( $yfym_registered_feeds_arr[ $n ] );
								$yfym_registered_feeds_arr = array_values( $yfym_registered_feeds_arr );
								yfym_optionUPD( 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
								break;
							}
						}
					}
					yfym_optionUPD( 'yfym_settings_arr', $yfym_settings_arr );
					$this->feed_id = yfym_get_first_feed_id();
				}
			}
		}

		if ( isset( $_REQUEST['yfym_submit_action'] ) || isset( $_REQUEST['yfym_submit_action'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'yfym_nonce_action', 'yfym_nonce_field' ) ) {
				do_action( 'yfym_prepend_submit_action', $this->get_feed_id() );
				$feed_id = sanitize_text_field( $_POST['yfym_feed_id_for_save'] );

				$unixtime = (string) current_time( 'timestamp', 1 );
				common_option_upd( 'yfym_date_save_set', $unixtime, 'no', $feed_id, 'yfym' );
				// $unixtime = current_time('timestamp', 1); // 1335808087 - временная зона GMT (Unix формат)
				// yfym_optionUPD('yfym_date_save_set', $unixtime, $feed_id, 'yes', 'set_arr');

				if ( isset( $_POST['yfym_run_cron'] ) ) {
					$run_cron = sanitize_text_field( $_POST['yfym_run_cron'] );
					common_option_upd( 'yfym_status_cron', $run_cron, 'no', $feed_id, 'yfym' );
					// yfym_optionUPD('yfym_status_cron', $run_cron, $feed_id, 'yes', 'set_arr');

					if ( $run_cron === 'disabled' ) {
						// отключаем крон
						wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] );
						common_option_upd( 'yfym_status_cron', 'disabled', 'no', $feed_id, 'yfym' );

						wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $feed_id ] );
						common_option_upd( 'yfym_cron_sborki', '-1', 'no', $feed_id, 'yfym' );
					} else if ( $run_cron === 'once' ) {
						// единоразовый импорт
						common_option_upd( 'yfym_cron_sborki', '-1', 'no', $feed_id, 'yfym' );
						// ? в теории тут можно регулировать "продолжить импорт" или "с нуля"
						wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] );
						wp_schedule_single_event( time() + 3, 'yfym_cron_period', [ $feed_id ] ); // старт через 3 сек
						new YFYM_Error_Log( sprintf( 'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
							'Единоразово yfym_cron_period внесен в список заданий',
							$this->get_feed_id(),
							'class-y4ym-settings-page.php',
							__LINE__
						) );
					} else {
						wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] );
						if ( ! wp_next_scheduled( 'yfym_cron_period', [ $feed_id ] ) ) {

							$cron_start_time = common_option_get( 'yfym_cron_start_time', false, $feed_id, 'yfym' );

							if ( empty( $cron_start_time )
								|| $run_cron == 'hourly'
								|| $run_cron == 'three_hours'
								|| $run_cron == 'six_hours'
								|| $run_cron == 'twicedaily' ) {

								$cron_start_time = 'now';
								
							}
							$gmt_offset = 3600 * (int) univ_option_get( 'gmt_offset' );
							$t = strtotime( $cron_start_time ) - (int) $gmt_offset;
							$planning_result = wp_schedule_event( $t, $run_cron, 'yfym_cron_period', [ $feed_id ] );
							if ( true === $planning_result ) {
								new YFYM_Error_Log( sprintf( 'FEED № %1$s; %2$s. Файл: %3$s; Строка: %4$s',
									'yfym_cron_period внесен в список заданий',
									$this->get_feed_id(),
									'class-y4ym-settings-page.php',
									__LINE__
								) );
							} else {
								// Ошибка планирования
							}
						}

					}
				}

				$def_plugin_date_arr = new Y4YM_Data_Arr();
				$opts_name_and_def_date_arr = $def_plugin_date_arr->get_opts_name_and_def_date( 'public' );
				foreach ( $opts_name_and_def_date_arr as $opt_name => $value ) {
					$save_if_empty = 'no';
					$save_if_empty = apply_filters( 'yfym_f_save_if_empty', $save_if_empty, [ 'opt_name' => $opt_name ] );
					$this->save_plugin_set( $opt_name, $feed_id, $save_if_empty );
				}
				do_action( 'y4ym_settings_page_listen_submit', $feed_id );
				$this->feed_id = $feed_id;
			}
		}

		if ( isset( $_REQUEST['yfym_submit_add_new_feed'] ) ) { // если создаём новый фид
			if ( ! empty( $_POST )
				&& check_admin_referer( 'yfym_nonce_action_add_new_feed', 'yfym_nonce_field_add_new_feed' ) ) {
				$yfym_settings_arr = yfym_optionGET( 'yfym_settings_arr' );

				if ( is_multisite() ) {
					$yfym_registered_feeds_arr = get_blog_option( get_current_blog_id(), 'yfym_registered_feeds_arr' );
					$feed_id = $yfym_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$yfym_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$yfym_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_blog_option( get_current_blog_id(), 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
				} else {
					$yfym_registered_feeds_arr = get_option( 'yfym_registered_feeds_arr' );
					$feed_id = $yfym_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$yfym_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$yfym_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_option( 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
				}

				$name_dir = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id;
				if ( ! is_dir( $name_dir ) ) {
					if ( ! mkdir( $name_dir ) ) {
						error_log( sprintf( 'ERROR: Ошибка создания папки %s; Файл: %s; Строка: %s',
							$name_dir,
							'class-y4ym-settings-page.php',
							__LINE__
						), 0 );
					}
				}

				$def_plugin_date_arr = new Y4YM_Data_Arr();
				$yfym_settings_arr[ $feed_id ] = $def_plugin_date_arr->get_opts_name_and_def_date( 'all' );

				yfym_optionUPD( 'yfym_settings_arr', $yfym_settings_arr );

				univ_option_add( apply_filters( 'y4ym_save_separate_opt', 'yfym_status_sborki', $feed_id ), '-1' );
				univ_option_add( apply_filters( 'y4ym_save_separate_opt', 'yfym_last_element', $feed_id ), '-1' );
				printf( '<div class="updated notice notice-success is-dismissible"><p>%s. ID = %s.</p></div>',
					__( 'Feed added', 'yml-for-yandex-market' ),
					$feed_id
				);

				$this->feed_id = $feed_id;
			}
		}

		// дублировать фид
		if ( isset( $_GET['feed_id'] )
			&& isset( $_GET['action'] )
			&& sanitize_text_field( $_GET['action'] ) === 'duplicate'
		) {
			$feed_id = (string) sanitize_text_field( $_GET['feed_id'] );
			if ( wp_verify_nonce( $_GET['_wpnonce'], 'nonce_duplicate' . $feed_id ) ) {
				$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
				$new_data_arr = $yfym_settings_arr[ $feed_id ];
				$yfym_consists_arr = yfym_optionGET( 'yfym_consists_arr', $feed_id );
				$yfym_params_arr = yfym_optionGET( 'yfym_params_arr', $feed_id );
				$yfym_no_group_id_arr = yfym_optionGET( 'yfym_no_group_id_arr', $feed_id );
				$yfym_add_in_name_arr = yfym_optionGET( 'yfym_add_in_name_arr', $feed_id );
				if ( class_exists( 'YmlforYandexMarketPro' ) ) {
					$params_arr = yfym_optionGET( 'yfymp_exclude_cat_arr', $feed_id );
					$p_arr = yfym_optionGET( 'p_arr' );
					$new_p_arr = $p_arr[ $feed_id ];
				}

				// обнулим часть значений т.к фид-клон ещё не создавался
				$new_data_arr['yfym_file_url'] = '';
				$new_data_arr['yfym_file_file'] = '';
				$new_data_arr['yfym_status_cron'] = 'off';
				$new_data_arr['yfym_date_sborki'] = '-'; // 'Y-m-d H:i
				$new_data_arr['yfym_date_sborki_end'] = '-'; // 'Y-m-d H:i
				$new_data_arr['yfym_date_save_set'] = 0000000001; // 0000000001 - timestamp format
				$new_data_arr['yfym_count_products_in_feed'] = '-1';

				// обновим список зарегистрированных фидов
				if ( is_multisite() ) {
					$yfym_registered_feeds_arr = get_blog_option( get_current_blog_id(), 'yfym_registered_feeds_arr' );
					$feed_id = $yfym_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$yfym_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$yfym_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_blog_option( get_current_blog_id(), 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
				} else {
					$yfym_registered_feeds_arr = get_option( 'yfym_registered_feeds_arr' );
					$feed_id = $yfym_registered_feeds_arr[0]['last_id'];
					$feed_id++;
					$yfym_registered_feeds_arr[0]['last_id'] = (string) $feed_id;
					$yfym_registered_feeds_arr[] = [ 'id' => (string) $feed_id ];
					update_option( 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
				}

				// запишем данные в базу
				$yfym_settings_arr[ $feed_id ] = $new_data_arr;
				yfym_optionUPD( 'yfym_settings_arr', $yfym_settings_arr );
				univ_option_add( apply_filters( 'y4ym_save_separate_opt', 'yfym_status_sborki', $feed_id ), '-1' );
				univ_option_add( apply_filters( 'y4ym_save_separate_opt', 'yfym_last_element', $feed_id ), '-1' );

				yfym_optionUPD( 'yfym_no_group_id_arr', $yfym_no_group_id_arr, $feed_id );
				yfym_optionUPD( 'yfym_add_in_name_arr', $yfym_add_in_name_arr, $feed_id );
				yfym_optionUPD( 'yfym_params_arr', $yfym_params_arr, $feed_id );
				yfym_optionUPD( 'yfym_consists_arr', $yfym_consists_arr, $feed_id );
				if ( class_exists( 'YmlforYandexMarketPro' ) ) {
					yfym_optionUPD( 'yfymp_exclude_cat_arr', $params_arr, $feed_id );
					$p_arr[ $feed_id ] = $new_p_arr;
					yfym_optionUPD( 'p_arr', $p_arr );
				}

				// создадим папку
				$name_dir = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id;
				if ( ! is_dir( $name_dir ) ) {
					if ( ! mkdir( $name_dir ) ) {
						error_log( 'ERROR: Ошибка создания папки ' . $name_dir . '; Файл: export.php; Строка: ' . __LINE__, 0 );
					}
				}

				$url = admin_url() . '?page=yfymexport&action=edit&feed_id=' . $feed_id . '&duplicate=true';
				wp_safe_redirect( $url );
			}
		}

		return;
	}

	/**
	 * Summary of print_view_html_form
	 * 
	 * @return void
	 */
	public function print_view_html_form() {
		$view_arr = [ 
			'feed_id' => $this->get_feed_id(),
			'tab_name' => $this->get_tab_name(),
			'tabs_arr' => $this->get_tabs_arr(),
			'prefix_feed' => $this->get_prefix_feed(),
			'current_blog_id' => $this->get_current_blog_id(),
			'extension' => $this->get_extension()
		];
		include_once __DIR__ . '/views/html-admin-settings-page.php';
	}

	/**
	 * Get tabs arr
	 * 
	 * @param string $current
	 * 
	 * @return array
	 */
	public function get_tabs_arr( $current = 'main_tab' ) {
		$tabs_arr = [ 
			'main_tab' => sprintf( '%s (%s: %s)',
				__( 'Main settings', 'yml-for-yandex-market' ),
				__( 'Feed', 'yml-for-yandex-market' ),
				$this->get_feed_id()
			),
			'shop_data_tab' => sprintf( '%s <shop> (%s: %s)',
				__( 'Elements inside', 'yml-for-yandex-market' ),
				__( 'Feed', 'yml-for-yandex-market' ),
				$this->get_feed_id()
			),
			'tags_settings_tab' => sprintf( '%s <offer> (%s: %s)',
				__( 'Elements inside', 'yml-for-yandex-market' ),
				__( 'Feed', 'yml-for-yandex-market' ),
				$this->get_feed_id()
			),
			'filtration_tab' => sprintf( '%s (%s: %s)',
				__( 'Filtration', 'yml-for-yandex-market' ),
				__( 'Feed', 'yml-for-yandex-market' ),
				$this->get_feed_id()
			)
		];
		$tabs_arr = apply_filters( 'y4ym_f_tabs_arr', $tabs_arr, [ 'feed_id' => $this->get_feed_id() ] );
		return $tabs_arr;
	}

	/**
	 * Print `text`, `number`, `select` or `textarea` html fields
	 * 
	 * @param string $tab
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public static function print_view_html_fields( $tab, $feed_id ) {
		$yfym_data_arr_obj = new Y4YM_Data_Arr();
		$data_for_tab_arr = $yfym_data_arr_obj->get_data_for_tabs( $tab ); // список дефолтных настроек

		for ( $i = 0; $i < count( $data_for_tab_arr ); $i++ ) {
			switch ( $data_for_tab_arr[ $i ]['type'] ) {
				case 'text':
					self::get_view_html_field_input( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'number':
					self::get_view_html_field_number( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'select':
					self::get_view_html_field_select( $data_for_tab_arr[ $i ], $feed_id );
					break;
				case 'textarea':
					self::get_view_html_field_textarea( $data_for_tab_arr[ $i ], $feed_id );
					break;
				default:
					do_action( 'yfym_f_print_view_html_fields', $data_for_tab_arr[ $i ], $feed_id );
			}
		}
	}

	/**
	 * Summary of get_view_html_field_input
	 * 
	 * @param array $data_arr
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public static function get_view_html_field_input( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							type="text" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s"
							class="y4ym_input"
							style="%6$s" /><br />
						<span class="description"><small>%7$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'yfym' ) ),
			esc_html( $data_arr['placeholder'] ),
			'width: 100%;',
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Summary of get_view_html_field_number
	 * 
	 * @param array $data_arr
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public static function get_view_html_field_number( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['min'] ) ) {
			$min = $data_arr['min'];
		} else {
			$min = '';
		}
		if ( isset( $data_arr['max'] ) ) {
			$max = $data_arr['max'];
		} else {
			$max = '';
		}
		if ( isset( $data_arr['step'] ) ) {
			$step = $data_arr['step'];
		} else {
			$step = '';
		}

		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<input 
							type="number" 
							name="%2$s" 
							id="%2$s" 
							value="%4$s"
							placeholder="%5$s" 
							min="%6$s"
							max="%7$s"
							step="%8$s"
							class="y4ym_input"
							/><br />
						<span class="description"><small>%9$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'yfym' ) ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( $min ),
			esc_attr( $max ),
			esc_attr( $step ),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Summary of get_view_html_field_select
	 * 
	 * @param array $data_arr
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public static function get_view_html_field_select( $data_arr, $feed_id ) {
		if ( isset( $data_arr['key_value_arr'] ) ) {
			$key_value_arr = $data_arr['key_value_arr'];
		} else {
			$key_value_arr = [];
		}
		if ( isset( $data_arr['categories_list'] ) ) {
			$categories_list = $data_arr['categories_list'];
		} else {
			$categories_list = false;
		}
		if ( isset( $data_arr['tags_list'] ) ) {
			$tags_list = $data_arr['tags_list'];
		} else {
			$tags_list = false;
		}
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['size'] ) ) {
			$size = $data_arr['size'];
		} else {
			$size = '1';
		}
		// массивы храним отдельно от других параметров
		if ( isset( $data_arr['multiple'] ) && true === $data_arr['multiple'] ) {
			$multiple = true;
			$multiple_val = '[]" multiple';
			$value = maybe_unserialize( yfym_optionGET( $data_arr['opt_name'], $feed_id ) );
			// TODO: $value = maybe_unserialize( univ_option_get( $data_arr['opt_name'] . $feed_id ) );
		} else {
			$multiple = false;
			$multiple_val = '"';
			$value = common_option_get(
				$data_arr['opt_name'],
				false,
				$feed_id,
				'yfym' );
		}

		printf( '<tr class="%1$s">
				<th scope="row"><label for="%2$s">%3$s</label></th>
				<td class="overalldesc">
					<select name="%2$s%5$s id="%2$s" size="%4$s"/>%6$s</select><br />
					<span class="description"><small>%7$s</small></span>
				</td>
			</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( $size ),
			$multiple_val,
			self::print_view_html_option_for_select(
				$value,
				$data_arr['opt_name'],
				[ 
					'woo_attr' => $data_arr['woo_attr'],
					'key_value_arr' => $key_value_arr,
					'categories_list' => $categories_list,
					'tags_list' => $tags_list,
					'multiple' => $multiple
				]
			),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Summary of print_view_html_option_for_select
	 * 
	 * @param mixed $opt_value
	 * @param string $opt_name
	 * @param array $params_arr
	 * @param mixed $res
	 * 
	 * @return mixed
	 */
	public static function print_view_html_option_for_select( $opt_value, string $opt_name, $params_arr = [], $res = '' ) {
		if ( true === $params_arr['multiple'] ) {
			if ( $opt_name === 'yfymp_exclude_cat_arr' ) {
				$res .= sprintf( '<optgroup label="%s">', __( 'Categories', 'yml-for-yandex-market' ) );
				foreach ( get_terms( [ 'taxonomy' => [ 'product_cat' ], 'hide_empty' => 0, 'parent' => 0 ] ) as $term ) {
					$res .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
				}
				$res .= '</optgroup>';

				$res .= sprintf( '<optgroup label="%s">', __( 'Tags', 'yml-for-yandex-market' ) );
				foreach ( get_terms( [ 'taxonomy' => [ 'product_tag' ], 'hide_empty' => 0, 'parent' => 0 ] ) as $term ) {
					$res .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
				}
				$res .= '</optgroup>';
			} else if ( $opt_name === 'yfym_no_group_id_arr' ) {
				foreach ( get_terms( [ 'taxonomy' => [ 'product_cat' ], 'hide_empty' => 0, 'parent' => 0 ] ) as $term ) {
					$res .= the_cat_tree( $term->taxonomy, $term->term_id, $opt_value );
				}
			} else {
				$woo_attributes_arr = get_woo_attributes();
				foreach ( $woo_attributes_arr as $attribute ) {
					if ( ! empty( $opt_value ) ) {
						foreach ( $opt_value as $value ) {
							if ( (string) $attribute['id'] == (string) $value ) {
								$selected = ' selected="select" ';
								break;
							} else {
								$selected = '';
							}
						}
					} else {
						$selected = '';
					}
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $attribute['id'] ),
						$selected,
						esc_attr( $attribute['name'] )
					);
				}
				unset( $woo_attributes_arr );
			}
		} else {
			if ( ! empty( $params_arr['key_value_arr'] ) ) {
				for ( $i = 0; $i < count( $params_arr['key_value_arr'] ); $i++ ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $params_arr['key_value_arr'][ $i ]['value'] ),
						esc_attr( selected( $opt_value, $params_arr['key_value_arr'][ $i ]['value'], false ) ),
						esc_attr( $params_arr['key_value_arr'][ $i ]['text'] )
					);
				}
			}

			if ( ! empty( $params_arr['woo_attr'] ) ) {
				$woo_attributes_arr = get_woo_attributes();
				for ( $i = 0; $i < count( $woo_attributes_arr ); $i++ ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $woo_attributes_arr[ $i ]['id'] ),
						esc_attr( selected( $opt_value, $woo_attributes_arr[ $i ]['id'], false ) ),
						esc_attr( $woo_attributes_arr[ $i ]['name'] )
					);
				}
				unset( $woo_attributes_arr );
			}

			// Если активен плагин WooCommerce Multi Inventory & Warehouses
			if ( $opt_name === 'yfymp_inventories' ) {
				foreach ( get_terms( [ 'taxonomy' => [ 'inventories' ], 'hide_empty' => 0, 'parent' => 0 ] ) as $term ) {
					$res .= sprintf( '<option value="%1$s" %2$s>%3$s</option>' . PHP_EOL,
						esc_attr( $term->term_id ),
						esc_attr( selected( $opt_value, $term->term_id, false ) ),
						esc_attr( $term->name )
					);
				}
			}
		}

		return $res;
	}

	/**
	 * Summary of get_view_html_field_textarea
	 * 
	 * @param array $data_arr
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public static function get_view_html_field_textarea( $data_arr, $feed_id ) {
		if ( isset( $data_arr['tr_class'] ) ) {
			$tr_class = $data_arr['tr_class'];
		} else {
			$tr_class = '';
		}
		if ( isset( $data_arr['rows'] ) ) {
			$rows = $data_arr['rows'];
		} else {
			$rows = '6';
		}
		if ( isset( $data_arr['cols'] ) ) {
			$cols = $data_arr['cols'];
		} else {
			$cols = '32';
		}
		printf( '<tr class="%1$s">
					<th scope="row"><label for="%2$s">%3$s</label></th>
					<td class="overalldesc">
						<textarea 							 
							name="%2$s" 
							id="%2$s" 
							rows="%4$s"
							cols="%5$s"
							class="y4ym_textarea"
							placeholder="%6$s">%7$s</textarea><br />
						<span class="description"><small>%8$s</small></span>
					</td>
				</tr>',
			esc_attr( $tr_class ),
			esc_attr( $data_arr['opt_name'] ),
			wp_kses( $data_arr['label'], self::ALLOWED_HTML_ARR ),
			esc_attr( $rows ),
			esc_attr( $cols ),
			esc_html( $data_arr['placeholder'] ),
			esc_attr( common_option_get( $data_arr['opt_name'], false, $feed_id, 'yfym' ) ),
			wp_kses( $data_arr['desc'], self::ALLOWED_HTML_ARR )
		);
	}

	/**
	 * Get feed ID
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}

	/**
	 * Get current tab
	 * 
	 * @return string
	 */
	private function get_tab_name() {
		return $this->cur_tab;
	}

	/**
	 * Save plugin settings
	 * 
	 * @param string $opt_name
	 * @param string $feed_id
	 * @param string $save_if_empty
	 * 
	 * @return void
	 */
	private function save_plugin_set( $opt_name, $feed_id, $save_if_empty = 'no' ) {
		if ( isset( $_POST[ $opt_name ] ) ) {
			if ( is_array( $_POST[ $opt_name ] ) ) {
				// массивы храним отдельно от других параметров
				yfym_optionUPD( $opt_name, serialize( $_POST[ $opt_name ] ), $feed_id );
				// TODO: univ_option_upd( $opt_name . $feed_id, maybe_serialize( $_POST[ $opt_name ] ) );
			} else {
				$value = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $_POST[ $opt_name ] );
				common_option_upd( $opt_name, $value, 'no', $feed_id, 'yfym' );
			}
		} else {
			if ( 'empty_str' === $save_if_empty ) {
				common_option_upd( $opt_name, '', 'no', $feed_id, 'yfym' );
			}
			if ( 'empty_arr' === $save_if_empty ) {
				// массивы храним отдельно от других параметров
				yfym_optionUPD( $opt_name, serialize( [] ), $feed_id );
				// TODO: univ_option_upd( $opt_name . $feed_id, maybe_serialize( [] ) );
			}
		}
		return;
	}

	/**
	 * Возвращает префикс фида
	 * 
	 * @return string
	 */
	private function get_prefix_feed() {
		if ( $this->get_feed_id() == '1' ) {
			$prefix_feed = '';
		} else {
			$prefix_feed = $this->get_feed_id();
		}
		return (string) $prefix_feed;
	}

	/**
	 * Возвращает id текущего блога
	 * 
	 * @return string
	 */
	private function get_current_blog_id() {
		if ( is_multisite() ) {
			$cur_blog_id = get_current_blog_id();
		} else {
			$cur_blog_id = '0';
		}
		return (string) $cur_blog_id;
	}

	/**
	 * Возвращает расширение файла фида
	 * 
	 * @return string
	 */
	private function get_extension() {
		return common_option_get( 'yfym_file_extension', false, $this->get_feed_id(), 'yfym' );
	}
}