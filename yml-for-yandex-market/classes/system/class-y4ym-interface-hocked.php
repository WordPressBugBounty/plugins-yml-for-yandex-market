<?php
/**
 * Interface Hoocked
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.7.2 (16-09-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param         
 *
 * @depends                 classes:    YFYM_Error_Log
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                                      common_option_upd
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

final class Y4YM_Interface_Hoocked {
	/**
	 * Interface Hoocked
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_classes();
	}

	/**
	 * Initialization hooks
	 * 
	 * @return void
	 */
	public function init_hooks() {
		// https://opttour.ru/web/wordpress/byistroe-redaktirovanie-zapisi/
		add_action( 'init', [ $this, 'add_new_taxonomies' ], 10 );
		add_action( 'yfym_collection_edit_form_fields', [ $this, 'add_meta_product_cat' ], 10, 1 );
		add_action( 'edited_yfym_collection', [ $this, 'save_meta_product_cat' ], 10, 1 );
		add_action( 'create_yfym_collection', [ $this, 'save_meta_product_cat' ], 10, 1 );

		add_action( 'woocommerce_product_data_panels', [ $this, 'yfym_art_added_tabs_panel' ], 10, 1 );
		add_action( 'woocommerce_product_options_general_product_data',
			[ $this, 'yfym_woocommerce_product_options_general_product_data' ],
			10,
			1
		);
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'add_variable_custom_fields' ], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save_product_variation' ], 10, 2 );
		// https://wpruse.ru/woocommerce/custom-fields-in-products/
		// https://wpruse.ru/woocommerce/custom-fields-in-variations/
		add_filter( 'woocommerce_product_data_tabs', [ $this, 'yfym_added_wc_tabs' ], 10, 1 );
		add_action( 'admin_footer', [ $this, 'yfym_art_added_tabs_icon' ], 10, 1 );
		// индивидуальные опции доставки товара
		add_action( 'save_post', [ $this, 'save_post_product' ], 50, 3 );
		// пришлось юзать save_post вместо save_post_product ибо wc блочит обновы

		add_filter( 'yfym_f_save_if_empty', [ $this, 'flag_save_if_empty' ], 10, 2 );
		add_filter( 'y4ym_save_separate_opt', [ $this, 'save_separate_opt' ], 10, 2 );
	}

	/**
	 * Initialization classes
	 * 
	 * @return void
	 */
	public function init_classes() {
		return;
	}

	/**
	 * Add new taxonomy
	 * 
	 * @return void
	 */
	public function add_new_taxonomies() {
		$labels_arr = [ 
			'name' => __( 'Сollections for YML feed', 'yml-for-yandex-market' ),
			'singular_name' => 'Сollection',
			'search_items' => __( 'Search collection', 'yml-for-yandex-market' ),
			'popular_items' => null, // __('Популярные категории', 'yml-for-yandex-market'),
			'all_items' => __( 'All collections', 'yml-for-yandex-market' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit collection', 'yml-for-yandex-market' ),
			'update_item' => __( 'Update collection', 'yml-for-yandex-market' ),
			'add_new_item' => __( 'Add new collection', 'yml-for-yandex-market' ),
			'new_item_name' => __( 'New collection', 'yml-for-yandex-market' ),
			'menu_name' => __( 'Сollections for YML', 'yml-for-yandex-market' )
		];
		$args_arr = [ 
			'hierarchical' => true, // true - по типу рубрик, false - по типу меток (по умолчанию)
			'labels' => $labels_arr,
			'public' => true, // каждый может использовать таксономию, либо только администраторы, по умолчанию - true
			'show_ui' => true, // добавить интерфейс создания и редактирования
			'publicly_queryable' => false, // сделать элементы таксономии доступными для добавления в меню сайта. По умолчанию: значение аргумента public.
			'show_in_nav_menus' => false, // добавить на страницу создания меню
			'show_tagcloud' => false, // нужно ли разрешить облако тегов для этой таксономии
			'update_count_callback' => '_update_post_term_count', // callback-функция для обновления счетчика $object_type
			'query_var' => true, // разрешено ли использование query_var, также можно указать строку, которая будет использоваться в качестве него, по умолчанию - имя таксономии
			'rewrite' => [ // настройки URL пермалинков
				'slug' => 'yfym_collection', // ярлык
				'hierarchical' => false // разрешить вложенность
			]
		];
		register_taxonomy( 'yfym_collection', [ 'product' ], $args_arr );
	}

	/**
	 * Позволяет добавить дополнительные поля на страницу редактирования элементов таксономии (термина).
	 * Function for `(taxonomy)_edit_form_fields` action-hook.
	 * 
	 * @param WP_Term $tag Current taxonomy term object.
	 * @param string $taxonomy Current taxonomy slug.
	 *
	 * @return void
	 */
	public function add_meta_product_cat( $term ) {
		global $post; ?>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'URL', 'yml-for-yandex-market' ); ?>
				</label>
			</th>
			<td>
				<input id="yfym_collection_url" type="text" name="yfym_cat_meta[yfym_collection_url]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'yfym_collection_url', 1 ) ) ?>" /><br />
				<p class="description">
					<?php esc_html_e( 'URL of the catalog page', 'yml-for-yandex-market' ); ?>
				</p>
			</td>
		</tr>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'Picture URL', 'yml-for-yandex-market' ); ?>
				</label>
			</th>
			<td>
				<input id="yfym_collection_picture" type="text" name="yfym_cat_meta[yfym_collection_picture]"
					value="<?php echo esc_attr( get_term_meta( $term->term_id, 'yfym_collection_picture', 1 ) ) ?>" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Сохранение данных в БД. Function for `create_(taxonomy)` and `edited_(taxonomy)` action-hooks.
	 * 
	 * @param int $term_id
	 * 
	 * @return void
	 */
	function save_meta_product_cat( $term_id ) {
		if ( ! isset( $_POST['yfym_cat_meta'] ) ) {
			return;
		}
		$yfym_cat_meta = array_map( 'sanitize_text_field', $_POST['yfym_cat_meta'] );
		foreach ( $yfym_cat_meta as $key => $value ) {
			if ( empty( $value ) ) {
				delete_term_meta( $term_id, $key );
				continue;
			}
			update_term_meta( $term_id, $key, $value );
		}
		return;
	}

	/**
	 * Сохраняем данные блока, когда пост сохраняется. Function for `save_post` action-hook
	 * 
	 * @param int $post_id
	 * @param WP_Post $post Post object
	 * @param bool $update (`true` — это обновление записи; `false` — это добавление новой записи)
	 * 
	 * @return void
	 */
	public function save_post_product( $post_id, $post, $update ) {
		new YFYM_Error_Log( 'Стартовала функция save_post_product. Файл: class-y4ym-interface-hocked.php; Строка: ' . __LINE__ );

		if ( $post->post_type !== 'product' ) {
			return; // если это не товар вукомерц
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // проверяем права юзера
		}

		$post_meta_arr = [ 
			'_yfym_market_category_id',
			'_yfym_market_sku',
			'_yfym_tn_ved_code',
			'_yfym_cargo_types',
			'_yfym_video_url',
			'yfym_individual_delivery',
			'yfym_cost',
			'yfym_days',
			'yfym_order_before',
			'yfym_individual_pickup',
			'yfym_pickup_cost',
			'yfym_pickup_days',
			'yfym_pickup_order_before',
			'yfym_bid',
			'yfym_individual_vat',
			// 'yfym_condition',
			'_yfym_condition',
			'yfym_reason',
			'_yfym_market_category',
			'_yfym_custom_score',
			'_yfym_custom_label_0',
			'_yfym_custom_label_1',
			'_yfym_custom_label_2',
			'_yfym_custom_label_3',
			'_yfym_custom_label_4',
			'_yfym_quality',
			'_yfym_warranty_days',
			'yfym_credit_template',
			'_yfym_supplier',
			'_yfym_min_quantity',
			'_yfym_step_quantity',
			'_yfym_barcode',
			'_yfym_premium_price',
			'_yfym_price_rrp',
			'_yfym_min_price',
			'_yfym_additional_expenses',
			'_yfym_cofinance_price',
			'_yfym_purchase_price'
		];
		$this->save_post_meta( $post_meta_arr, $post_id );

		// Убедимся что поле установлено.
		if ( isset( $_POST['yfym_cost'] ) ) {
			$yfym_recommend_stock_data_arr = [];
			$yfym_recommend_stock_data_arr['availability'] = sanitize_text_field( $_POST['_yfym_availability'] );
			$yfym_recommend_stock_data_arr['transport_unit'] = sanitize_text_field( $_POST['_yfym_transport_unit'] );
			$yfym_recommend_stock_data_arr['min_delivery_pieces'] = sanitize_text_field( $_POST['_yfym_min_delivery_pieces'] );
			$yfym_recommend_stock_data_arr['quantum'] = sanitize_text_field( $_POST['_yfym_quantum'] );
			$yfym_recommend_stock_data_arr['leadtime'] = sanitize_text_field( $_POST['_yfym_leadtime'] );
			$yfym_recommend_stock_data_arr['box_count'] = sanitize_text_field( $_POST['_yfym_box_count'] );
			if ( isset( $_POST['_delivery_weekday_arr'] ) && ! empty( $_POST['_delivery_weekday_arr'] ) ) {
				$yfym_recommend_stock_data_arr['delivery_weekday_arr'] = $_POST['_delivery_weekday_arr'];
			} else {
				$yfym_recommend_stock_data_arr['delivery_weekday_arr'] = [];
			}
			// Обновляем данные в базе данных
			update_post_meta( $post_id, '_yfym_recommend_stock_data_arr', $yfym_recommend_stock_data_arr );
		}

		$this->run_feeds_upd( $post_id );
		return;
	}

	/**
	 * Проверяет, нужно ли запускать обновление фида при обновлении товара и при необходимости запускает процесс
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function run_feeds_upd( $post_id ) {
		// нужно ли запускать обновление фида при перезаписи файла
		$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
		$yfym_settings_arr_keys_arr = array_keys( $yfym_settings_arr );
		for ( $i = 0; $i < count( $yfym_settings_arr_keys_arr ); $i++ ) {
			$feed_id = (string) $yfym_settings_arr_keys_arr[ $i ]; // ! для правильности работы важен тип string

			$yfym_run_cron = common_option_get( 'yfym_run_cron', false, $feed_id, 'yfym' );
			if ( $yfym_run_cron == 'disabled' ) {
				new YFYM_Error_Log(
					sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
						$feed_id,
						'Фид отключён. Создание кэш-файла для данного фида не требуется',
						'class-y4ym-interface-hocked.php',
						__LINE__
					)
				);
				continue;
			}

			new YFYM_Error_Log(
				sprintf( 'FEED № %1$s; Шаг $i = %2$s цикла по формированию кэша файлов; Файл: %3$s; Строка: %4$s',
					$feed_id,
					$i,
					'class-y4ym-interface-hocked.php',
					__LINE__
				)
			);

			// если в настройках включено создание кэш-файлов в момент сохранения товара
			$do_cash_file = common_option_get( 'yfym_do_cash_file', false, $feed_id, 'yfym' );
			if ( $do_cash_file !== 'enabled' ) {
				$result_get_unit_obj = new YFYM_Get_Unit( $post_id, $feed_id );
				$result_xml = $result_get_unit_obj->get_result();
				$ids_in_xml = $result_get_unit_obj->get_ids_in_xml();
				yfym_wf( $result_xml, $post_id, $feed_id, $ids_in_xml );
			}

			// нужно ли запускать обновление фида при перезаписи файла
			$yfym_ufup = common_option_get( 'yfym_ufup', false, $feed_id, 'yfym' );
			if ( $yfym_ufup == 'on' ) {
				new YFYM_Error_Log(
					sprintf( 'FEED № %1$s; Шаг $yfym_ufup = %2$s Пересборка фида требуется; Файл: %3$s; Строка: %4$s',
						$feed_id,
						$yfym_ufup,
						'class-y4ym-interface-hocked.php',
						__LINE__
					)
				);
			} else {
				new YFYM_Error_Log(
					sprintf( 'FEED № %1$s; Шаг $yfym_ufup = %2$s Пересборка фида не требуется; Файл: %3$s; Строка: %4$s',
						$feed_id,
						$yfym_ufup,
						'class-y4ym-interface-hocked.php',
						__LINE__
					)
				);
				continue;
			}
			$status_sborki = (int) yfym_optionGET( 'yfym_status_sborki', $feed_id );
			if ( $status_sborki > -1 ) {
				continue; // если идет сборка фида - пропуск
			}

			new YFYM_Error_Log(
				sprintf( 'FEED № %1$s; Пересборка запускается; Файл: %2$s; Строка: %3$s',
					$feed_id,
					'class-y4ym-interface-hocked.php',
					__LINE__
				)
			);

			$yfym_date_save_set = common_option_get( 'yfym_date_save_set', false, $feed_id, 'yfym' );

			$feed_file_meta = new YFYM_Feed_File_Meta( $feed_id );
			$filenamefeed = sprintf( '%1$s/%2$s.%3$s',
				YFYM_SITE_UPLOADS_DIR_PATH,
				$feed_file_meta->get_feed_filename(),
				$feed_file_meta->get_feed_extension()
			);
			if ( ! file_exists( $filenamefeed ) ) { // файла с фидом нет
				new YFYM_Error_Log(
					sprintf( 'FEED № %1$s; WARNING: Файла %2$s не существует! Пропускаем быструю сборку; Файл: %3$s; Строка: %4$s',
						$feed_id,
						$filenamefeed,
						'class-y4ym-interface-hocked.php',
						__LINE__
					)
				);
				continue;
			}

			clearstatcache(); // очищаем кэш дат файлов
			$last_upd_file = filemtime( $filenamefeed );
			new YFYM_Error_Log(
				sprintf( 'FEED № %1$s; %2$s: $yfym_date_save_set = %3$s; $filenamefeed = %4$s; Файл: %5$s; Строка: %6$s',
					$feed_id,
					'Начинаем сравнивать даты',
					$yfym_date_save_set,
					$filenamefeed,
					'class-y4ym-interface-hocked.php',
					__LINE__
				)
			);
			if ( $yfym_date_save_set > $last_upd_file ) {
				// настройки фида сохранялись позже, чем создан фид. Нужно полностью пересобрать фид
				new YFYM_Error_Log(
					sprintf( 'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
						$feed_id,
						'Настройки фида сохранялись позже, чем создан фид',
						'class-y4ym-interface-hocked.php',
						__LINE__
					)
				);
				// ! для правильности работы важен тип string
				$yfym_run_cron = common_option_get( 'yfym_status_cron', false, $feed_id, 'yfym' );
				if ( $yfym_run_cron === 'disabled' || $yfym_run_cron === 'once' || $yfym_run_cron === 'off' ) {
					// фид отключён или разово собирается
				} else {
					wp_clear_scheduled_hook( 'yfym_cron_period', [ $feed_id ] );
					if ( ! wp_next_scheduled( 'yfym_cron_period', [ $feed_id ] ) ) {
						wp_schedule_event( time() + 3, $yfym_run_cron, 'yfym_cron_period', [ $feed_id ] );
					}
					new YFYM_Error_Log(
						sprintf( 'FEED № %1$s; %2$s; Файл: %3$s; Строка: %4$s',
							$feed_id,
							'Для полной пересборки после быстрого сохранения yfym_cron_period внесен в список заданий',
							'class-y4ym-interface-hocked.php',
							__LINE__
						)
					);
				}
			} else { // нужно лишь обновить цены
				new YFYM_Error_Log(
					sprintf( 'FEED № %1$s; NOTICE: %2$s; Файл: %3$s; Строка: %4$s',
						$feed_id,
						'Настройки фида сохранялись раньше, чем создан фид. Нужно лишь обновить цены',
						'class-y4ym-interface-hocked.php',
						__LINE__
					)
				);
				$generation = new YFYM_Generation_XML( $feed_id );
				$generation->clear_file_ids_in_xml( $feed_id );
				$generation->onlygluing();
			}
		}
		return;
	}

	/**
	 * Save post_meta
	 * 
	 * @param array $post_meta_arr
	 * @param int $post_id
	 * 
	 * @return void
	 */
	private function save_post_meta( $post_meta_arr, $post_id ) {
		for ( $i = 0; $i < count( $post_meta_arr ); $i++ ) {
			$meta_name = $post_meta_arr[ $i ];
			if ( isset( $_POST[ $meta_name ] ) ) {
				if ( empty( $_POST[ $meta_name ] ) ) {
					delete_post_meta( $post_id, $meta_name );
				} else {
					update_post_meta( $post_id, $meta_name, sanitize_text_field( $_POST[ $meta_name ] ) );
				}
			}
		}
	}

	/**
	 * Function for `woocommerce_product_options_general_product_data` action-hook.
	 * 
	 * @return void
	 */
	public static function yfym_woocommerce_product_options_general_product_data() {
		global $product, $post;
		printf( '<div class="options_group"><h2><strong>%s</strong></h2>',
			__( 'Individual product settings for YML-feed', 'yml-for-yandex-market' )
		);
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_barcode',
			'label' => __( 'Barcode', 'yml-for-yandex-market' ),
			'placeholder' => sprintf( '%s: 978020137962', __( 'For example', 'yml-for-yandex-market' ) ),
			'description' => sprintf( '%s "_yfym_barcode" %s. %s get_post_meta',
				__( 'The data of this field is stored in the', 'yml-for-yandex-market' ),
				__( 'meta field', 'yml-for-yandex-market' ),
				__( 'You can always display them in your website template using', 'yml-for-yandex-market' )
			),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '1',
				'min' => '0'
			]
		] );
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_premium_price',
			'label' => 'premium_price',
			'placeholder' => '0',
			'description' => __(
				'Price for Ozon Premium customers. Used only in the OZONE feed',
				'yml-for-yandex-market'
			),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '0.01',
				'min' => '0'
			]
		] );
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_price_rrp',
			'label' => 'price_rrp',
			'placeholder' => '0',
			'description' => __( 'Recommended retail price, type of price for suppliers', 'yml-for-yandex-market' ),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '0.01',
				'min' => '0'
			]
		] );
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_min_price',
			'label' => 'min_price',
			'placeholder' => '0',
			'description' => __( 'Minimum price', 'yml-for-yandex-market' ),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '0.01',
				'min' => '0'
			]
		] );
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_additional_expenses',
			'label' => 'additional_expenses',
			'placeholder' => '0',
			'description' => __( 'Additional costs for the product', 'yml-for-yandex-market' ),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '1',
				'min' => '0'
			]
		] );
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_cofinance_price',
			'label' => 'cofinance_price',
			'placeholder' => '0',
			'description' => __( 'Threshold for receiving discounts in Yandex Market', 'yml-for-yandex-market' ),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '1',
				'min' => '0'
			]
		] );
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_purchase_price',
			'label' => 'cofinance_price',
			'placeholder' => '0',
			'description' => __( 'Purchase price', 'yml-for-yandex-market' ),
			'type' => 'number',
			'desc_tip' => true,
			'custom_attributes' => [ 
				'step' => '1',
				'min' => '0'
			]
		] );
		print ( '</div>' );
	}

	/**
	 * Function for `woocommerce_product_after_variable_attributes` action-hook
	 * 
	 * @param int     $loop           Position in the loop.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Post data. 
	 * 
	 * @return void
	 */
	public function add_variable_custom_fields( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( [ 
			'id' => '_yfym_barcode[' . $variation->ID . ']',
			'label' => __( 'Barcode', 'yml-for-yandex-market' ),
			'placeholder' => sprintf( '%s: 978020137962', __( 'For example', 'yml-for-yandex-market' ) ),
			'description' => sprintf( '%s "_yfym_barcode" %s. %s get_post_meta',
				__( 'The data of this field is stored in the', 'yml-for-yandex-market' ),
				__( 'meta field', 'yml-for-yandex-market' ),
				__( 'You can always display them in your website template using', 'yml-for-yandex-market' )
			),
			'type' => 'number',
			'desc_tip' => 'true', // Всплывающая подсказка
			'custom_attributes' => [ 
				'step' => '1',
				'min' => '0'
			],
			'value' => get_post_meta( $variation->ID, '_yfym_barcode', true )
		] );
	}

	/**
	 * Сохраняем данные блока, когда пост сохраняется. Function for `woocommerce_save_product_variation` action-hook
	 * 
	 * @param int $post_id
	 * 
	 * @return void
	 */
	public function save_product_variation( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return; // если это ревизия
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return; // если это автосохранение ничего не делаем
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return; // проверяем права юзера
		}

		// обращаем внимание на двойное подчёркивание в $woocommerce__yfym_barcode
		$woocommerce__yfym_barcode = $_POST['_yfym_barcode'][ $post_id ];
		if ( isset( $woocommerce__yfym_barcode ) && ! empty( $woocommerce__yfym_barcode ) ) {
			update_post_meta( $post_id, '_yfym_barcode', esc_attr( $woocommerce__yfym_barcode ) );
		} else {
			update_post_meta( $post_id, '_yfym_barcode', '' );
		}
	}

	/**
	 * Function for `woocommerce_product_data_tabs` filter-hook.
	 * 
	 * @param array $tabs
	 *
	 * @return array
	 */
	public static function yfym_added_wc_tabs( $tabs ) {
		$tabs['yfym_special_panel'] = [ 
			'label' => __( 'YML for Yandex Market', 'yml-for-yandex-market' ), // название вкладки
			'target' => 'yfym_added_wc_tabs', // идентификатор вкладки
			'class' => [ 'hide_if_grouped' ], // классы управления видимостью вкладки в зависимости от типа товара
			'priority' => 70 // приоритет вывода
		];
		return $tabs;
	}

	/**
	 * Function for `admin_footer` action-hook.
	 * 
	 * @see https://rawgit.com/woothemes/woocommerce-icons/master/demo.html
	 * 
	 * @param string $data The data to print.
	 *
	 * @return void
	 */
	public static function yfym_art_added_tabs_icon( $data ) {
		print ( '<style>#woocommerce-coupon-data ul.wc-tabs li.yfym_special_panel_options a::before,
			#woocommerce-product-data ul.wc-tabs li.yfym_special_panel_options a::before,
			.woocommerce ul.wc-tabs li.yfym_special_panel_options a::before {
				content: "\f172";
			}</style>' );
	}

	/**
	 * Function for `woocommerce_product_data_panels` filter-hook.
	 * 
	 * @return void
	 */
	public static function yfym_art_added_tabs_panel() {
		global $post; ?>
		<div id="yfym_added_wc_tabs" class="panel woocommerce_options_panel">
			<div class="options_group">
				<h2>
					<?php esc_html_e( 'Individual product settings for YML-feed', 'yml-for-yandex-market' ); ?>
				</h2>
				<?php
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_market_category_id',
					'label' => sprintf(
						'%s <i>[market_category_id]</i>',
						__( 'Market category ID', 'yml-for-yandex-market' )
					),
					'description' => __(
						'Do not confuse it with the "market_category" parameter',
						'yml-for-yandex-market'
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_market_sku',
					'label' => sprintf(
						'%s <i>[market-sku]</i>',
						__( 'Product ID on Yandex', 'yml-for-yandex-market' )
					),
					'description' => __( 'Product ID on Yandex or other marketplace', 'yml-for-yandex-market' ),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_tn_ved_code',
					'label' => sprintf(
						'%s <i>[tn-ved-codes]</i>',
						__( 'Code ТН ВЭД', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <code>|</code>. <a target="_blank" href="%s">%s</a>',
						__( 'If you need to specify multiple values, separate them with a', 'yml-for-yandex-market' ),
						'//yandex.ru/support2/marketplace/ru/assortment/fields/#tn-ved-code',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_select( [ 
					'id' => '_yfym_cargo_types',
					'label' => sprintf(
						'%s <i>[cargo-types]</i>',
						__( "I'll product marking", "yml-for-yandex-market" )
					),
					'description' => sprintf( '%s. <a target="_blank" href="%s">%s</a>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						'//yandex.ru/support2/marketplace/ru/assortment/fields/#cz',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'options' => [ 
						'default' => __( 'Default', 'yml-for-yandex-market' ),
						'disabled' => __( 'Disabled', 'yml-for-yandex-market' ),
						'yes' => 'CIS_REQUIRED'
					],
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_video_url',
					'label' => sprintf( '%s <i>[video]</i>', __( 'Video', 'yml-for-yandex-market' ) ),
					'description' => sprintf( '%s <strong>video</strong></strong>',
						__( 'Video URL', 'yml-for-yandex-market' )
					),
					'type' => 'text',
					'desc_tip' => 'true'
				] );
				?>
			</div>
			<div class="options_group">
				<p>
					<?php esc_html_e( 'Here you can set up individual options terms for this product', 'yml-for-yandex-market' ); ?>.
					<a target="_blank" href="//yandex.ru/support2/marketplace/ru/assortment/fields/#option-days">
						<?php esc_html_e( 'Read more on Yandex', 'yml-for-yandex-market' ); ?>
					</a>
				</p>
				<?php do_action( 'yfym_prepend_options_group_1', $post ); ?>
				<?php
				woocommerce_wp_select( [ 
					'id' => 'yfym_individual_delivery',
					'label' => sprintf( '%s <i>[delivery]</i>', __( 'Delivery', 'yml-for-yandex-market' ) ),
					'options' => [ 
						'' => __( 'Disabled', 'yml-for-yandex-market' ),
						'false' => 'False',
						'true' => 'True'
					],
					'description' => sprintf( '%s <strong>delivery</strong>',
						__( 'Optional element', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_days',
					'label' => sprintf(
						'%s <i>[delivery-option days]</i>',
						__( 'Delivery days', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>days</strong> %s <strong>delivery-option</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_cost',
					'label' => sprintf(
						'%s <i>[delivery-option cost]</i>',
						__( 'Delivery cost', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>cost</strong> %s <strong>delivery-option</strong>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '0.01',
						'min' => '0'
					]
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_order_before',
					'label' => sprintf(
						'%s <i>[delivery-option order-before]</i>',
						__( 'The time', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>order-before</strong> %s <strong>delivery-option</strong>. %s',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' ),
						__( 'The time in which you need to place an order to get it at this time', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				?>
			</div>
			<div class="options_group">
				<p>
					<?php esc_html_e(
						'Here you can configure the pickup conditions for this product',
						'yml-for-yandex-market' );
					?>
				</p>
				<?php
				woocommerce_wp_select( [ 
					'id' => 'yfym_individual_pickup',
					'label' => sprintf( '%s <i>[pickup]</i>', __( 'Delivery', 'yml-for-yandex-market' ) ),
					'options' => [ 
						'' => __( 'Disabled', 'yml-for-yandex-market' ),
						'false' => 'False',
						'true' => 'True'
					],
					'description' => sprintf( '%s <strong>pickup</strong>',
						__( 'Optional element', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true'
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_pickup_days',
					'label' => sprintf(
						'%s <i>[pickup-option days]</i>',
						__( 'Delivery days', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>days</strong> %s <strong>pickup-option</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_pickup_cost',
					'label' => sprintf(
						'%s <i>[pickup-option cost]</i>',
						__( 'Delivery cost', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>cost</strong> %s <strong>pickup-option</strong>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '0.01',
						'min' => '0'
					]
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_pickup_order_before',
					'label' => sprintf(
						'%s <i>[pickup-option order-before]</i>',
						__( 'The time', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>order-before</strong> %s <strong>pickup-option</strong>. %s',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' ),
						__( 'The time in which you need to place an order to get it at this time', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				?>
			</div>
			<div class="options_group">
				<p>
					<?php esc_html_e( 'Bid values', 'yml-for-yandex-market' ); ?> &
					<?php esc_html_e( 'Сondition', 'yml-for-yandex-market' ); ?>
				</p>
				<?php
				woocommerce_wp_select( [ 
					'id' => 'yfym_individual_vat',
					'label' => sprintf( '%s <i>[vat]</i>', __( 'VAT rate', 'yml-for-yandex-market' ) ),
					'options' => [ 
						'global' => __( 'Use global settings', 'yml-for-yandex-market' ),
						'NO_VAT' => __( 'No VAT', 'yml-for-yandex-market' ) . ' (NO_VAT)',
						'VAT_0' => '0% (VAT_0)',
						'VAT_10' => '10% (VAT_10)',
						'VAT_10_110' => '10/110 (VAT_10_110)',
						'VAT_18' => '18% (VAT_18)',
						'VAT_18_118' => '18/118 (VAT_18_118)',
						'VAT_20' => '20% (VAT_20)',
						'VAT_20_120' => '20/120 VAT_20_120)'
					],
					'description' => sprintf( '%s <strong>vat</strong>. <a target="_blank" href="%s">%s</a>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						'//yandex.ru/support2/marketplace/ru/assortment/fields/#vat',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true'
				] );
				// ? насколько актуален этот тег //yandex.ru/support/partnermarket/elements/bid-cbid.html
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_bid',
					'label' => sprintf(
						'%s <i>[bid]</i>',
						__( 'Bid values', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>bid</strong>. %s <a target="_blank" href="%s">%s</a>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' ),
						__(
							'Bid values in your price list. Specify the bid amount in Yandex cents: for example, the value 80 corresponds to the bid of 0.8 Yandex units. The values must be positive integers',
							'yml-for-yandex-market'
						),
						'//yandex.ru/support/partnermarket/elements/bid-cbid.html',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_select( [ 
					'id' => '_yfym_condition',
					'label' => sprintf(
						'%s <i>[condition]</i>',
						__( 'Сondition', 'yml-for-yandex-market' )
					),
					'options' => [ 
						'default' => __( 'Default', 'yml-for-yandex-market' ),
						'disabled' => __( 'Disabled', 'yml-for-yandex-market' ),
						'showcasesample' => __( 'Showcase sample', 'yml-for-yandex-market' ) . ' (showcasesample)',
						'reduction' => __( 'Reduction', 'yml-for-yandex-market' ) . ' (reduction)',
						'fashionpreowned' => __( 'Fashionpreowned', 'yml-for-yandex-market' ) . ' (fashionpreowned)',
						'preowned' => __( 'Fashionpreowned', 'yml-for-yandex-market' ) . ' (preowned)',
						'likenew' => __( 'Like New', 'yml-for-yandex-market' ) . ' (likenew)'
					],
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>condition</strong>',
					'desc_tip' => 'true'
				] );
				woocommerce_wp_select( [ 
					'id' => '_yfym_quality',
					'label' => sprintf(
						'%s <i>[condition quality]</i>',
						__( 'Quality', 'yml-for-yandex-market' )
					),
					'options' => [ 
						'default' => __( 'Default', 'yml-for-yandex-market' ),
						'perfect' => __( 'Perfect', 'yml-for-yandex-market' ),
						'excellent' => __( 'Excellent', 'yml-for-yandex-market' ),
						'good' => __( 'Good', 'yml-for-yandex-market' ),
					],
					'description' => sprintf( '%s <strong>quality</strong> %s <strong>condition</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => 'yfym_reason',
					'label' => sprintf(
						'%s <i>[condition reason]</i>',
						__( 'Reason', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>reason</strong> %s <strong>condition</strong>',
						__( 'Required element', 'yml-for-yandex-market' ),
						__( 'of attribute', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				?>
			</div>
			<div class="options_group">
				<h2>
					<?php esc_html_e( 'Individual product settings for Yandex Direct', 'yml-for-yandex-market' ); ?>
				</h2>
				<p>
					<?php esc_html_e( 'Here you can set up individual settings for Yandex Direct', 'yml-for-yandex-market' ); ?>.
					<a target="_blank" href="//yandex.ru/support/direct/feeds/requirements-yml.html">
						<?php esc_html_e( 'Read more on Yandex', 'yml-for-yandex-market' ); ?>
					</a>
				</p>
				<?php
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_market_category',
					'label' => sprintf(
						'%s <i>[market_category]</i>',
						__( 'Market category', 'yml-for-yandex-market' )
					),
					'description' => __(
						'The product category in which it should be placed on Yandex Market',
						'yml-for-yandex-market'
					),
					'desc_tip' => 'true',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_custom_score',
					'label' => sprintf( '%s <i>[custom_score]</i>', __( 'Custom elements', 'yml-for-yandex-market' ) ),
					'description' => __( 'The value is zero or any positive integer', 'yml-for-yandex-market' ),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );
				for ( $i = 0; $i < 5; $i++ ) {
					$post_meta_name = '_yfym_custom_label_' . (string) $i;
					woocommerce_wp_text_input( [ 
						'id' => $post_meta_name,
						'label' => sprintf(
							'%s <i>[custom_label_%s]</i>',
							__( 'Custom elements', 'yml-for-yandex-market' ),
							(string) $i
						),
						'description' => sprintf( '%s. %s. %s',
							__( 'Custom elements', 'yml-for-yandex-market' ),
							__( 'An arbitrary description', 'yml-for-yandex-market' ),
							__(
								'Latin and Cyrillic letters, numbers. The length of one element is up to 175 characters',
								'yml-for-yandex-market'
							)
						),
						'desc_tip' => 'true',
						'type' => 'text'
					] );
				}
				?>
			</div>
			<div class="options_group">
				<h2>Маркетплейс Яндекс.Маркета</h2>
				<p>
					<?php esc_html_e( 'This data is used only when creating a feed for', 'yml-for-yandex-market' ); ?>
					Маркетплейс Яндекс.Маркета
				</p>
				<?php


				if ( get_post_meta( $post->ID, '_yfym_recommend_stock_data_arr', true ) == '' ) {
					$yfym_recommend_stock_data_arr = [];
				} else {
					$yfym_recommend_stock_data_arr = get_post_meta( $post->ID, '_yfym_recommend_stock_data_arr', true );
				}
				$availability = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'availability', 'disabled' );
				$transport_unit = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'transport_unit' );
				$min_delivery_pieces = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'min_delivery_pieces' );
				$quantum = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'quantum' );
				$leadtime = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'leadtime' );
				$box_count = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'box_count' );
				$delivery_weekday_arr = yfym_data_from_arr( $yfym_recommend_stock_data_arr, 'delivery_weekday_arr', [] );

				woocommerce_wp_select( [ 
					'id' => '_yfym_availability',
					'label' => __( 'Supply plans', 'yml-for-yandex-market' ),
					'value' => $availability,
					'options' => [ 
						'disabled' => __( 'Disabled', 'yml-for-yandex-market' ),
						'ACTIVE' => __( 'Supplies will', 'yml-for-yandex-market' ),
						'INACTIVE' => __( 'There will be no supplies', 'yml-for-yandex-market' ),
						'DELISTED' => __( 'Product in the archive', 'yml-for-yandex-market' )
					],
					'description' => sprintf(
						'%s <strong>availability</strong> (%s) <a target="_blank" href="%s">%s</a>',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'Forbidden in Yandex Market', 'yml-for-yandex-market' ),
						'//yandex.ru/support/marketplace/catalog/yml-simple.html',
						__( 'Read more on Yandex', 'yml-for-yandex-market' )
					)
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_transport_unit',
					'label' => __( 'The number of products in the package (multiplicity of the box)', 'yml-for-yandex-market' ),
					'value' => $transport_unit,
					'placeholder' => '',
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>transport-unit</strong> (' . __( 'Forbidden in Yandex Market', 'yml-for-yandex-market' ) . ') <a target="_blank" href="//yandex.ru/support/marketplace/catalog/yml-simple.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_min_delivery_pieces',
					'label' => __( 'Minimum delivery pieces', 'yml-for-yandex-market' ),
					'value' => $min_delivery_pieces,
					'placeholder' => '',
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>min-delivery-pieces</strong> (' . __( 'Forbidden in Yandex Market', 'yml-for-yandex-market' ) . ') <a target="_blank" href="//yandex.ru/support/marketplace/catalog/yml-simple.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_quantum',
					'label' => __( 'Additional batch (quantum of delivery)', 'yml-for-yandex-market' ),
					'value' => $quantum,
					'placeholder' => '',
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>quantum</strong> (' . __( 'Forbidden in Yandex Market', 'yml-for-yandex-market' ) . ') <a target="_blank" href="//yandex.ru/support/marketplace/catalog/yml-simple.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_leadtime',
					'label' => __( 'Lead time', 'yml-for-yandex-market' ),
					'value' => $leadtime,
					'placeholder' => '',
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>leadtime</strong> (' . __( 'Forbidden in Yandex Market', 'yml-for-yandex-market' ) . ') <a target="_blank" href="//yandex.ru/support/marketplace/catalog/yml-simple.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_box_count',
					'label' => __( 'Box count', 'yml-for-yandex-market' ),
					'value' => $box_count,
					'placeholder' => '',
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>box-count</strong> (' . __( 'Forbidden in Yandex Market', 'yml-for-yandex-market' ) . ') <a target="_blank" href="//yandex.ru/support/marketplace/catalog/yml-simple.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				yfym_woocommerce_wp_select_multiple( [ 
					'id' => '_delivery_weekday_arr',
					//	'wrapper_class' => 'show_if_simple', 
					'label' => __( 'Days of the week when you are ready to deliver the goods to the warehouse of the marketplace', 'yml-for-yandex-market' ),
					'value' => $delivery_weekday_arr,
					'options' => [ 
						'MONDAY' => __( 'Monday', 'yml-for-yandex-market' ),
						'TUESDAY' => __( 'Tuesday', 'yml-for-yandex-market' ),
						'WEDNESDAY' => __( 'Wednesday', 'yml-for-yandex-market' ),
						'THURSDAYy' => __( 'Thursday', 'yml-for-yandex-market' ),
						'FRIDAY' => __( 'Friday', 'yml-for-yandex-market' ),
						'SATURDAY' => __( 'Saturday', 'yml-for-yandex-market' ),
						'SUNDAY' => __( 'Sunday', 'yml-for-yandex-market' )
					]
				] );
				?>
			</div>
			<div class="options_group">
				<h2>
					<?php esc_html_e( 'Other', 'yml-for-yandex-market' ); ?>
				</h2>
				<?php
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_warranty_days',
					'label' => sprintf(
						'%s <i>[warranty-days]</i>',
						__( 'The warranty period', 'yml-for-yandex-market' )
					),
					'description' => sprintf( '%s <strong>warranty-days</strong>. %s',
						__( 'Optional element', 'yml-for-yandex-market' ),
						__( 'The number of days of the warranty period', 'yml-for-yandex-market' )
					),
					'desc_tip' => 'true',
					'type' => 'number',
					'custom_attributes' => [ 
						'step' => '1',
						'min' => '0'
					]
				] );

				woocommerce_wp_text_input( [ 
					'id' => 'yfym_credit_template',
					'label' => __( 'Credit program identifier', 'yml-for-yandex-market' ),
					'placeholder' => '',
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>credit-template</strong> <a target="_blank" href="//yandex.ru/support/partnermarket/efficiency/credit.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );

				woocommerce_wp_text_input( [ 
					'id' => '_yfym_supplier',
					'label' => 'ОГРН/ОГРНИП ' . __( 'of a third-party seller', 'yml-for-yandex-market' ),
					'description' => __( 'Optional element', 'yml-for-yandex-market' ) . ' <strong>supplier</strong>. <a target="_blank" href="//yandex.ru/support/partnermarket/registration/marketplace.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_min_quantity',
					'label' => __( 'Minimum number of products per order', 'yml-for-yandex-market' ),
					'description' => __( 'For these categories only', 'yml-for-yandex-market' ) . ': "Автошины", "Грузовые шины", "Мотошины", "Диски" <strong>min-quantity</strong>. <a target="_blank" href="//yandex.ru/support/partnermarket/elements/min-quantity.html">' . __( 'Read more on Yandex', 'yml-for-yandex-market' ) . '</a>',
					'type' => 'text'
				] );
				woocommerce_wp_text_input( [ 
					'id' => '_yfym_step_quantity',
					'label' => 'step-quantity',
					'description' => __( 'For these categories only', 'yml-for-yandex-market' ) . ': "Автошины", "Грузовые шины", "Мотошины", "Диски" <strong>step-quantity</strong>',
					'type' => 'text'
				] );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Флаг для того, чтобы работало сохранение настроек если мультиселект пуст
	 * 
	 * @param string $save_if_empty
	 * @param array $args_arr
	 * 
	 * @return string
	 */
	public function flag_save_if_empty( $save_if_empty, $args_arr ) {
		if ( ! empty( $_GET ) && isset( $_GET['tab'] ) && $_GET['tab'] === 'tags_settings_tab' ) {
			if ( $args_arr['opt_name'] === 'yfym_params_arr'
				|| $args_arr['opt_name'] === 'yfym_consists_arr' ) {
				$save_if_empty = 'empty_arr';
			}
		}
		if ( ! empty( $_GET ) && isset( $_GET['tab'] ) && $_GET['tab'] === 'filtration_tab' ) {
			if ( $args_arr['opt_name'] === 'yfym_no_group_id_arr'
				|| $args_arr['opt_name'] === 'yfym_add_in_name_arr'
			) {
				$save_if_empty = 'empty_arr';
			}
		}
		return $save_if_empty;
	}

	/**
	 * Добавляет к переменной опции номер фида
	 * 
	 * @param string $opt_name
	 * @param string $feed_id
	 * 
	 * @return string
	 */
	public function save_separate_opt( $opt_name, $feed_id ) {
		if ( $feed_id === '1' ) {
			$n = '';
		} else {
			$n = $feed_id;
		}
		$opt_name = $opt_name . $n;
		return $opt_name;
	}
} // end class Y4YM_Interface_Hoocked