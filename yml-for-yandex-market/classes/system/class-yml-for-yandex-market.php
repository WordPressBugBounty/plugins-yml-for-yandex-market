<?php
/**
 * The main class of the plugin YML for Yandex Market
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
 * @param           
 *
 * @depends                 classes:     Y4YM_Data_Arr
 *                                       Y4YM_Settings_Page
 *                                       YFYM_Debug_Page
 *                                       YFYM_Error_Log
 *                                       YFYM_Generation_XML
 *                                       ICPD_Feedback
 *                                       ICPD_Promo
 *                          traits:     
 *                          methods:    
 *                          functions:   common_option_get
 *                                       common_option_upd
 *                                       univ_option_get
 *                          constants:	 YFYM_PLUGIN_VERSION
 *                                       YFYM_PLUGIN_BASENAME
 *                                       YFYM_PLUGIN_DIR_URL
 *                                       YFYM_PLUGIN_UPLOADS_DIR_PATH
 *                          options:     
 */
defined( 'ABSPATH' ) || exit;

final class YmlforYandexMarket {
	/**
	 * The list of allowed tags that will be passed to the function wp_kses()
	 * @var array
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
	 * The current version of the plugin
	 * @var string
	 */
	private $plugin_version = YFYM_PLUGIN_VERSION; // 1.0.0

	/**
	 * Instance
	 * @var YmlforYandexMarket
	 */
	protected static $instance;

	/**
	 * Initialization YmlforYandexMarket class.
	 * @return YmlforYandexMarket
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Срабатывает при активации плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_activation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! is_dir( YFYM_PLUGIN_UPLOADS_DIR_PATH ) ) {
			if ( ! mkdir( YFYM_PLUGIN_UPLOADS_DIR_PATH ) ) {
				error_log(
					sprintf( '%s %s; Файл: yml-for-yandex-market.php; Строка: ',
						'ERROR: Ошибка создания папки',
						YFYM_PLUGIN_UPLOADS_DIR_PATH,
						__LINE__
					), 0 );
			}
		}

		$name_dir = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed1';
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log(
					sprintf( '%s %s; Файл: yml-for-yandex-market.php; Строка: ',
						'ERROR: Ошибка создания папки',
						$name_dir,
						__LINE__
					), 0 );
			}
		}

		$yfym_registered_feeds_arr = [ 
			0 => [ 'last_id' => '1' ],
			1 => [ 'id' => '1' ]
		];

		$def_plugin_date_arr = new Y4YM_Data_Arr();
		$yfym_settings_arr = [];
		$yfym_settings_arr['1'] = $def_plugin_date_arr->get_opts_name_and_def_date( 'all' );

		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'yfym_version', YFYM_PLUGIN_VERSION );
			add_blog_option( get_current_blog_id(), 'yfym_keeplogs', '' );
			add_blog_option( get_current_blog_id(), 'yfym_disable_notices', '' );
			add_blog_option( get_current_blog_id(), 'yfym_feed_content', '' );

			add_blog_option( get_current_blog_id(), 'yfym_settings_arr', $yfym_settings_arr );
			add_blog_option( get_current_blog_id(), 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
		} else {
			add_option( 'yfym_version', YFYM_PLUGIN_VERSION, '', true ); // без автозагрузки
			add_option( 'yfym_keeplogs', '' );
			add_option( 'yfym_disable_notices', '' );
			add_option( 'yfym_feed_content', '' );

			add_option( 'yfym_settings_arr', $yfym_settings_arr );
			add_option( 'yfym_registered_feeds_arr', $yfym_registered_feeds_arr );
		}
	}

	/**
	 * Срабатывает при отключении плагина (вызывается единожды)
	 * 
	 * @return void
	 */
	public static function on_deactivation() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$yfym_registered_feeds_arr = univ_option_get( 'yfym_registered_feeds_arr', [] );
		if ( ! empty( $yfym_registered_feeds_arr ) ) {
			for ( $i = 1; $i < count( $yfym_registered_feeds_arr ); $i++ ) {
				// с единицы, т.к инфа по конкретным фидам там
				$feed_id = $yfym_registered_feeds_arr[ $i ]['id'];
				wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] ); // отключаем крон
				wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $feed_id ] ); // отключаем крон

				common_option_upd( 'yfym_run_cron', 'disabled', 'no', $feed_id, 'yfym' );
				common_option_upd( 'yfym_status_cron', 'off', 'no', $feed_id, 'yfym' );

				// * c id == '1' у нас, будет загвоздка при таком подходе, но в целом пойдёт и так
				univ_option_upd( 'yfym_status_sborki' . $feed_id, '-1' );
			}
		}
	}

	/**
	 * The main class of the plugin YML for Yandex Market
	 */
	public function __construct() {
		$this->check_and_fix(); // если вдруг нет настроек плагина
		$this->check_options_upd(); // проверим, нужны ли обновления опций плагина
		$this->init_classes();
		$this->init_hooks(); // подключим хуки
	}

	/**
	 * Если по непонятной причине нет настроек плагина - создаём
	 * 
	 * @return void
	 */
	public function check_and_fix() {
		$settings_arr = univ_option_get( 'yfym_settings_arr' );
		if ( ! is_array( $settings_arr ) ) {
			self::on_activation();
		}
	}

	/**
	 * Checking whether the plugin options need to be updated
	 * 
	 * @return void
	 */
	public function check_options_upd() {
		if ( false == common_option_get( 'yfym_version' ) ) { // это первая установка
			if ( is_multisite() ) {
				update_blog_option( get_current_blog_id(), 'yfym_version', $this->plugin_version );
			} else {
				update_option( 'yfym_version', $this->plugin_version );
			}
		} else {
			$this->set_new_options();
		}
	}

	/**
	 * Set new plugin options
	 * 
	 * @return void
	 */
	public function set_new_options() {
		// Если предыдущая версия плагина меньше текущей
		if ( version_compare( $this->get_plugin_version(), $this->plugin_version, '<' ) ) {
			new YFYM_Error_Log( sprintf( '%1$s (%2$s < %3$s). %4$s; Файл: %5$s; Строка: %6$s',
				'Предыдущая версия плагина меньше текущей',
				(string) $this->get_plugin_version(),
				(string) $this->plugin_version,
				'Обновляем опции плагина',
				'yml-for-yandex-market.php',
				__LINE__
			) );
		} else { // обновления не требуются
			return;
		}

		$yfym_data_arr_obj = new Y4YM_Data_Arr();
		$opts_arr = $yfym_data_arr_obj->get_opts_name_and_def_date_obj( 'all' ); // список дефолтных настроек
		// проверим, заданы ли дефолтные настройки
		$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
		$yfym_settings_arr_keys_arr = array_keys( $yfym_settings_arr );
		for ( $i = 0; $i < count( $yfym_settings_arr_keys_arr ); $i++ ) {
			// ! т.к у нас работа с array_keys, то в $feed_id может быть int. Для гарантии сделаем string
			$feed_id = (string) $yfym_settings_arr_keys_arr[ $i ];
			for ( $n = 0; $n < count( $opts_arr ); $n++ ) {
				$name = $opts_arr[ $n ]->name; // get_name();
				$value = $opts_arr[ $n ]->opt_def_value; // get_value();
				if ( ! isset( $yfym_settings_arr[ $feed_id ][ $name ] ) ) {
					// если какой-то опции нет - добавим в БД
					common_option_upd( $name, $value, 'no', $feed_id, 'yfym' );
				}
			}
		}

		if ( is_multisite() ) {
			update_blog_option( get_current_blog_id(), 'yfym_version', $this->plugin_version );
		} else {
			update_option( 'yfym_version', $this->plugin_version );
		}
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		new Y4YM_Interface_Hoocked();
		new ICPD_Feedback( [ 
			'plugin_name' => 'YML for Yandex Market',
			'plugin_version' => $this->get_plugin_version(),
			'logs_url' => YFYM_PLUGIN_UPLOADS_DIR_URL . '/plugin.log',
			'pref' => 'y4ym'
		] );
		new ICPD_Promo( 'yfym' );
		return;
	}

	/**
	 * Get the plugin version from the site database 
	 * 
	 * @return string
	 */
	public function get_plugin_version() {
		if ( is_multisite() ) {
			$v = get_blog_option( get_current_blog_id(), 'yfym_version' );
		} else {
			$v = get_option( 'yfym_version' );
		}
		return (string) $v;
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'wp_loaded', [ $this, 'check_forced_cron' ], 10 );
		add_action( 'admin_init', [ $this, 'listen_submits' ], 10 ); // ещё можно слушать чуть раньше на wp_loaded
		add_action( 'admin_init', function () {
			wp_register_style( 'yfym-admin-css', YFYM_PLUGIN_DIR_URL . 'assets/css/y4ym_style.css' );
		}, 9999 ); // Регаем стили только для страницы настроек плагина
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 10, 1 );
		add_filter( 'plugin_action_links', [ $this, 'add_plugin_action_links' ], 10, 2 );

		add_filter( 'upload_mimes', [ $this, 'add_mime_types' ], 99, 1 ); // чутка позже остальных
		add_filter( 'cron_schedules', [ $this, 'add_cron_intervals' ], 10, 1 );

		add_action( 'yfym_cron_sborki', [ $this, 'yfym_do_this_seventy_sec' ], 10, 1 );
		add_action( 'yfym_cron_period', [ $this, 'yfym_do_this_event' ], 10, 1 );
		add_action( 'admin_notices', [ $this, 'notices_prepare' ], 10, 1 );

		add_filter( 'yfymp_request_string_filter', [ $this, 'add_сompatibility_with_yandex_zen_plug' ], 10, 3 );

		// дополнительные данные для фидбэка
		add_filter( 'y4ym_f_feedback_additional_info', [ $this, 'feedback_additional_info' ], 10, 1 );
	}

	/**
	 * Forced to start wp-cron.php if CRON tasks are overdue by more than `85` seconds
	 * 
	 * @return void
	 */
	public function check_forced_cron() {
		$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
		$yfym_settings_arr_keys_arr = array_keys( $yfym_settings_arr );
		for ( $i = 0; $i < count( $yfym_settings_arr_keys_arr ); $i++ ) {
			$feed_id = $yfym_settings_arr_keys_arr[ $i ];
			$run_cron = common_option_get( 'yfym_run_cron', false, $feed_id, 'yfym' );
			if ( $run_cron == 'disabled' ) {
				continue;
			} else {
				forced_cron(); // принудительно дёрним крон при больших просрочках
				break;
			}
		}
	}

	/**
	 * Listen submits
	 * 
	 * @return void
	 */
	public function listen_submits() {
		do_action( 'yfym_listen_submits' );

		if ( isset( $_REQUEST['yfym_submit_action'] ) || isset( $_REQUEST['y4ym_submit_action'] ) ) {
			$message = __( 'Updated', 'yml-for-yandex-market' );
			$class = 'notice-success';
			if ( isset( $_POST['yfym_run_cron'] ) && sanitize_text_field( $_POST['yfym_run_cron'] ) !== 'off' ) {
				$message .= '. ' . __(
					'Creating the feed is running. You can continue working with the website',
					'yml-for-yandex-market'
				);
			}

			add_action( 'admin_notices', function () use ($message, $class) {
				$this->print_admin_notice( $message, $class );
			}, 10, 2 );
		}
	}

	/**
	 * Register the style sheet on separate pages of our plugin.
	 * Function for `admin_print_styles-[page_suffix]` action-hook.
	 * 
	 * @return void
	 */
	public function yfym_admin_css_func() {
		wp_enqueue_style( 'yfym-admin-css' ); // Ставим css-файл в очередь на вывод
	}

	/**
	 * Add items to admin menu. Function for `admin_menu` action-hook.
	 * 
	 * @return void
	 */
	public function add_admin_menu() {
		$page_suffix = add_menu_page(
			null,
			__( 'Export Yandex Market', 'yml-for-yandex-market' ),
			'manage_woocommerce',
			'yfymexport',
			[ $this, 'get_plugin_settings_page' ],
			'dashicons-redo',
			51
		);

		// создаём хук, чтобы стили выводились только на странице настроек
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'yfym_admin_css_func' ] );

		$page_suffix = add_submenu_page(
			'yfymexport',
			__( 'Debug', 'yml-for-yandex-market' ),
			__( 'Debug page', 'yml-for-yandex-market' ),
			'manage_woocommerce', 'yfymdebug',
			[ $this, 'get_debug_page_func' ]
		);
		add_action( 'admin_print_styles-' . $page_suffix, [ $this, 'yfym_admin_css_func' ] );

		$page_subsuffix = add_submenu_page(
			'yfymexport',
			__( 'Add Extensions', 'yml-for-yandex-market' ),
			__( 'Extensions', 'yml-for-yandex-market' ),
			'manage_woocommerce',
			'yfymextensions',
			[ $this, 'get_extensions_page_func' ]
		);

		add_action( 'admin_print_styles-' . $page_subsuffix, [ $this, 'yfym_admin_css_func' ] );
	}

	/**
	 * Вывод страницы настроек плагина
	 * 
	 * @return void
	 */
	public function get_plugin_settings_page() {
		new Y4YM_Settings_Page( 'yml-for-yandex-market', $this->get_plugin_version() );
		return;
	}

	/**
	 * Вывод страницы отладки плагина
	 * 
	 * @return void
	 */
	public function get_debug_page_func() {
		new Y4YM_Debug_Page();
		return;
	}

	/**
	 * Вывод страницы расширений плагина
	 * 
	 * @return void
	 */
	public function get_extensions_page_func() {
		new Y4YM_Extensions_Page();
		return;
	}

	/**
	 * Function for `plugin_action_links` action-hook.
	 * 
	 * @param string[] $actions An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory
	 * 
	 * @return string[]
	 */
	public function add_plugin_action_links( $actions, $plugin_file ) {
		if ( false === strpos( $plugin_file, YFYM_PLUGIN_BASENAME ) ) { // проверка, что у нас текущий плагин
			return $actions;
		}

		$settings_link = sprintf( '<a style="%s" href="/wp-admin/admin.php?page=%s">%s</a>',
			'color: green; font-weight: 700;',
			'yfymextensions',
			__( 'More features', 'yml-for-yandex-market' )
		);
		array_unshift( $actions, $settings_link );

		$settings_link = sprintf( '<a href="/wp-admin/admin.php?page=%s">%s</a>',
			'yfymexport',
			__( 'Settings', 'yml-for-yandex-market' )
		);
		array_unshift( $actions, $settings_link );

		return $actions;
	}

	/**
	 * Разрешим загрузку xml и csv файлов. Function for `upload_mimes` action-hook.
	 * 
	 * @param array $mimes
	 * 
	 * @return array
	 */
	public function add_mime_types( $mimes ) {
		$mimes['csv'] = 'text/csv';
		$mimes['xml'] = 'text/xml';
		$mimes['yml'] = 'text/xml';
		return $mimes;
	}

	/**
	 * Add cron intervals to WordPress. Function for `cron_schedules` action-hook.
	 * 
	 * @param array $schedules
	 * 
	 * @return array
	 */
	public function add_cron_intervals( $schedules ) {
		$schedules['seventy_sec'] = [ 
			'interval' => 70,
			'display' => __( '70 seconds', 'yml-for-yandex-market' )
		];
		$schedules['three_hours'] = [ 
			'interval' => 10800,
			'display' => __( '3 hours', 'yml-for-yandex-market' )
		];
		$schedules['six_hours'] = [ 
			'interval' => 21600,
			'display' => __( '6 hours', 'yml-for-yandex-market' )
		];
		$schedules['every_two_days'] = [ 
			'interval' => 172800,
			'display' => __( 'Every two days', 'yml-for-yandex-market' )
		];
		$schedules['week'] = [ 
			'interval' => 604800,
			'display' => __( '1 week', 'yml-for-yandex-market' )
		];
		return $schedules;
	}

	/* ----------------- функции крона ----------------- */
	/**
	 * Summary of yfym_do_this_seventy_sec
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function yfym_do_this_seventy_sec( $feed_id ) {
		// условие исправляет возможные ошибки и повторное создание удаленного фида
		if ( $feed_id == '' || $feed_id === 1 ) {
			yfym_optionUPD( 'yfym_status_sborki', '-1', $feed_id );
			wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $feed_id ] );
			wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] );
			return;
		}

		new YFYM_Error_Log( 'Cтартовала крон-задача do_this_seventy_sec' );
		$generation = new YFYM_Generation_XML( $feed_id ); // делаем что-либо каждые 70 сек
		$generation->run();
	}

	/**
	 * Summary of yfym_do_this_event
	 * 
	 * @param string $feed_id
	 * 
	 * @return void
	 */
	public function yfym_do_this_event( $feed_id ) {
		// условие исправляет возможные ошибки и повторное создание удаленного фида
		if ( $feed_id == '' || $feed_id === 1 ) {
			yfym_optionUPD( 'yfym_status_sborki', '-1', $feed_id );
			wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $feed_id ] );
			wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] );
			return;
		}

		new YFYM_Error_Log( sprintf(
			'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
			$feed_id,
			'Крон yfym_do_this_event включен. Делаем что-то каждый час',
			'class-yml-for-yandex-market.php',
			__LINE__
		) );
		$step_export = (int) yfym_optionGET( 'yfym_step_export', $feed_id, 'set_arr' );
		if ( $step_export === 0 ) {
			$step_export = 500;
		}
		yfym_optionUPD( 'yfym_status_sborki', '1', $feed_id );

		wp_clear_scheduled_hook( 'yfym_cron_sborki', [ $feed_id ] );

		if ( ! wp_next_scheduled( 'yfym_cron_sborki', [ $feed_id ] ) ) {
			// Возвращает nul|false. null когда планирование завершено. false в случае неудачи
			$res = wp_schedule_event( time(), 'seventy_sec', 'yfym_cron_sborki', [ $feed_id ] );
		} else {
			$res = false;
		}
		if ( false === $res ) {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; ERROR: %2$s; Файл: %3$s; Строка: %4$s',
				$feed_id,
				'Не удалось запланировань CRON seventy_sec',
				'class-yml-for-yandex-market.php',
				__LINE__
			) );
		} else {
			new YFYM_Error_Log( sprintf(
				'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
				$feed_id,
				'CRON seventy_sec успешно запланирован',
				'class-yml-for-yandex-market.php',
				__LINE__
			) );
		}
	}
	/* ----------------- end функции крона ----------------- */

	/**
	 * Вывод различных notices
	 * 
	 * @see https://wpincode.com/kak-dobavit-sobstvennye-uvedomleniya-v-adminke-wordpress/
	 * 
	 * @return void
	 */
	public function notices_prepare() {
		$disabled_notices_flag = false;
		$disabled_notices_flag = apply_filters( 'y4ym_f_disabled_notices_problem_list', $disabled_notices_flag );
		if ( false === $disabled_notices_flag ) {
			if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) && isset( $_GET['page'] ) ) {
				printf( '<div class="notice notice-warning"><p>
					<span class="y4ym_bold">W3 Total Cache</span> %1$s. %2$s <a href="%3$s/?%4$s" target="_blank">%5$s</a>
					</p></div>',
					esc_html__( 'plugin is active', 'yml-for-yandex-market' ),
					esc_html__( 'If no YML feed is being generated, please', 'yml-for-yandex-market' ),
					'https://icopydoc.ru/w3tc-page-cache-meshaet-sozdaniyu-fida-reshenie',
					'utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=notice&utm_term=w3-total-cache',
					esc_html__( 'read this guide', 'yml-for-yandex-market' )
				);
			}
		}

		$yfym_disable_notices = univ_option_get( 'yfym_disable_notices' );
		if ( $yfym_disable_notices !== 'on' ) {
			$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
			$yfym_settings_arr_keys_arr = array_keys( $yfym_settings_arr );
			for ( $i = 0; $i < count( $yfym_settings_arr_keys_arr ); $i++ ) {
				$feed_id = $yfym_settings_arr_keys_arr[ $i ];
				$status_sborki = yfym_optionGET( 'yfym_status_sborki', $feed_id );
				if ( $status_sborki == false ) {
					continue;
				} else {
					$status_sborki = (int) $status_sborki;
				}
				if ( $status_sborki !== -1 ) {
					$count_posts = wp_count_posts( 'product' );
					$vsegotovarov = $count_posts->publish;
					$step_export = (int) yfym_optionGET( 'yfym_step_export', $feed_id, 'set_arr' );
					if ( $step_export === 0 ) {
						$step_export = 500;
					}
					// $vobrabotke = $status_sborki-$step_export;

					$vobrabotke = ( ( $status_sborki - 1 ) * $step_export ) - $step_export;

					if ( $vsegotovarov > $vobrabotke ) {
						if ( $status_sborki == 1 ) {
							$vyvod = sprintf(
								'<br />FEED № %1$s %2$s.<br />%3$s. %4$s (<a href="%5$s/?%6$s" target="_blank">%7$s</a>)',
								$feed_id,
								__( 'Category list import', 'yml-for-yandex-market' ),
								__(
									'If the progress indicators have not changed within 20 minutes, try reducing the "Step of export" in the plugin settings',
									'yml-for-yandex-market'
								),
								__( 'Also make sure that there are no problems with the CRON on your site', 'yml-for-yandex-market' ),
								'https://icopydoc.ru/minimalnye-trebovaniya-dlya-raboty-yml-for-yandex-market',
								'utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=notice&utm_term=check_problems_cron',
								__( 'read this guide', 'yml-for-yandex-market' )
							);
						}
						if ( $status_sborki == 2 ) {
							$vyvod = sprintf( '<br />FEED № %1$s %2$s',
								(string) $feed_id,
								__( 'Counting the number of products', 'yml-for-yandex-market' )
							);
						}
						if ( $status_sborki > 2 ) {
							$vyvod = sprintf( '<br />FEED № %1$s %2$s %3$s %4$s %5$s. %6$s %7$s %8$s (<a href="%9$s/?%10$s" target="_blank">%11$s</a>)',
								(string) $feed_id,
								__( 'Progress', 'yml-for-yandex-market' ),
								$vobrabotke,
								__( 'from', 'yml-for-yandex-market' ),
								$vsegotovarov,
								__( 'products', 'yml-for-yandex-market' ),
								__(
									'If the progress indicators have not changed within 20 minutes, try reducing the "Step of export" in the plugin settings',
									'yml-for-yandex-market'
								),
								__(
									'Also make sure that there are no problems with the CRON on your site',
									'yml-for-yandex-market'
								),
								'https://icopydoc.ru/minimalnye-trebovaniya-dlya-raboty-yml-for-yandex-market',
								'utm_source=yml-for-yandex-market&utm_medium=organic&utm_campaign=in-plugin-yml-for-yandex-market&utm_content=notice&utm_term=check_problems_cron',
								__( 'read this guide', 'yml-for-yandex-market' )
							);
						}
					} else {
						$vyvod = sprintf( '<br />FEED № %1$s %2$s',
							(string) $feed_id,
							__( 'Prior to the completion of less than 70 seconds', 'yml-for-yandex-market' )
						);
					}

					$class = '1';
					add_action( 'admin_notices', function () use ($vyvod, $class) {
						$this->print_admin_notice( $vyvod, $class );
					}, 10, 2 );

					printf( '<div class="updated notice notice-success is-dismissible">
						<p><span class="y4ym_bold">Y4YM:</span>  %1$s %2$s</p>
						</div>',
						esc_html__(
							'We are working on automatic file creation. YML will be developed soon',
							'yml-for-yandex-market'
						),
						wp_kses( $vyvod, self::ALLOWED_HTML_ARR )
					);
				}
			}
		}
	}

	/**
	 * Cовместимость с палгином RSS for Yandex Zen
	 * 
	 * @param mixed $dwl_link
	 * @param mixed $oid
	 * @param mixed $oem
	 * 
	 * @return string
	 */
	public function add_сompatibility_with_yandex_zen_plug( $dwl_link, $oid, $oem ) {
		if ( yfymp_license_status() == 'ok' ) {
			if ( empty( $oid ) || empty( $oem ) ) {
				univ_option_upd( 'yzen_yandex_zen_rss', 'enabled' );
			} else {
				univ_option_upd( 'yzen_yandex_zen_rss', 'disabled' );
			}
		}
		return $dwl_link;
	}

	/**
	 * Add additional information to the feedback email. 
	 * Function for `y4ym_f_feedback_additional_info` action-hook.
	 * 
	 * @param string $additional_info
	 * 
	 * @return string
	 */
	public function feedback_additional_info( $additional_info ) {
		$possible_problems_arr = Y4YM_Debug_Page::get_possible_problems_list();
		$additional_info .= 'Самодиагностика: ';
		if ( $possible_problems_arr[1] > 0 ) {
			$additional_info .= sprintf( '<ol>%s</ol>', $possible_problems_arr[0] );
		} else {
			$additional_info .= sprintf( '<p>%s</p>', 'Функции самодиагностики не выявили потенциальных проблем' );
		}
		if ( ! class_exists( 'YmlforYandexMarketAliexpress' ) ) {
			$additional_info .= "Aliexpress Export: не активна" . "<br />";
		} else {
			if ( defined( 'YFYMAE_PLUGIN_VERSION' ) ) {
				$v = YFYMAE_PLUGIN_VERSION;
			} else {
				$v = 'н/д';
			}
			$order_id = univ_option_get( 'yfymae_order_id' );
			$order_email = univ_option_get( 'yfymae_order_email' );
			$additional_info .= sprintf( 'Aliexpress Export: активна (v %s (#%s / %s)<br />',
				$v,
				$order_id,
				$order_email
			);
		}
		if ( ! class_exists( 'YmlforYandexMarketBookExport' ) ) {
			$additional_info .= "Book Export: не активна" . "<br />";
		} else {
			if ( defined( 'YFYMBE_PLUGIN_VERSION' ) ) {
				$v = YFYMBE_PLUGIN_VERSION;
			} else {
				$v = 'н/д';
			}
			if ( ! defined( '' ) ) {
				define( 'yfymbe_VER', 'н/д' );
			}
			$order_id = univ_option_get( 'yfymbe_order_id' );
			$order_email = univ_option_get( 'yfymbe_order_email' );
			$additional_info .= sprintf( 'Book Export: активна (v %s (#%s / %s)<br />',
				$v,
				$order_id,
				$order_email
			);
		}
		if ( ! class_exists( 'YmlforYandexMarketPro' ) ) {
			$additional_info .= "Pro: не активна" . "<br />";
		} else {
			if ( defined( 'YFYMP_PLUGIN_VERSION' ) ) {
				$v = YFYMP_PLUGIN_VERSION;
			} else {
				$v = 'н/д';
			}
			$order_id = univ_option_get( 'yfymp_order_id' );
			$order_email = univ_option_get( 'yfymp_order_email' );
			$additional_info .= sprintf( 'PRO: активна (v %s (#%s / %s)<br />',
				$v,
				$order_id,
				$order_email
			);
		}
		if ( ! class_exists( 'Y4YMS' ) ) {
			$additional_info .= "SETS: не активна" . "<br />";
		} else {
			if ( defined( 'Y4YMS_PLUGIN_VERSION' ) ) {
				$v = Y4YMS_PLUGIN_VERSION;
			} else {
				$v = 'н/д';
			}
			$order_id = univ_option_get( 'y4yms_order_id' );
			$order_email = univ_option_get( 'y4yms_order_email' );
			$additional_info .= sprintf( 'SETS: активна (v %s (#%s / %s)<br />',
				$v,
				$order_id,
				$order_email
			);
		}
		$yandex_zen_rss = univ_option_get( 'yzen_yandex_zen_rss' );
		$additional_info .= "RSS for Yandex Zen: " . $yandex_zen_rss . "<br />";
		$settings_arr = univ_option_get( 'yfym_settings_arr', [] );
		$settings_arr_keys_arr = array_keys( $settings_arr );
		for ( $i = 0; $i < count( $settings_arr_keys_arr ); $i++ ) {
			$feed_id = (string) $settings_arr_keys_arr[ $i ];
			$additional_info .= sprintf(
				'<h2>ФИД №%1$s</h2>
				<p>status_sborki: %2$s<br />
				УРЛ: %3$s<br />
				УРЛ XML-фида: %4$s<br />
				Временный файл: %5$s<br />
				Придерживаться правил: %6$s<br />
				Что экспортировать: %7$s<br />
				Автоматическое создание файла: %8$s<br />
				Обновить фид при обновлении карточки товара: %9$s<br />
				Дата начала последней сборки XML: %10$s<br />
				Дата завершения последней сборки XML: %11$s',
				(string) $feed_id,
				common_option_get( 'yfym_status_sborki', false, $feed_id, 'yfym' ),
				get_site_url(),
				urldecode( common_option_get( 'yfym_file_url', false, $feed_id, 'yfym' ) ),
				urldecode( common_option_get( 'yfym_file_file', false, $feed_id, 'yfym' ) ),
				common_option_get( 'yfym_yml_rules', false, $feed_id, 'yfym' ),
				common_option_get( 'yfym_whot_export', false, $feed_id, 'yfym' ),
				common_option_get( 'yfym_status_cron', false, $feed_id, 'yfym' ),
				common_option_get( 'yfym_ufup', false, $feed_id, 'yfym' ),
				common_option_get( 'yfym_date_sborki', false, $feed_id, 'yfym' ),
				common_option_get( 'yfym_date_sborki_end', false, $feed_id, 'yfym' ),
			);
		}
		return $additional_info;
	}

	/**
	 * Print admin notice
	 * 
	 * @param string $message
	 * @param string $class
	 * 
	 * @return void
	 */
	private function print_admin_notice( $message, $class ) {
		$yfym_disable_notices = univ_option_get( 'yfym_disable_notices' );
		if ( $yfym_disable_notices !== 'on' ) {
			printf( '<div class="notice %1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}
} /* end class YmlforYandexMarket */