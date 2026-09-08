<?php defined( 'WPINC' ) || exit;

/**
 * The class will help you connect your store to Yandex Market using Yandex Market API.
 *
 * @link       https://icopydoc.ru
 * @since      5.8.0
 * @version    5.8.1 (08-09-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/import
 */

/**
 * The class will help you connect your store to Yandex Market using Yandex Market API.
 *
 * @since      5.8.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/import
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @see                     https://yandex.ru/dev/market/partner-api/doc/ru/
 *                          https://oauth.yandex.ru/client/new
 * @depends    classes:     Y4YM_Error_Log
 *             functions:   
 */
final class Y4YM_Api {

	/**
	 * API key from Yandex Market personal account.
	 * @var string
	 */
	protected $api_key;

	/**
	 * Номер кампании (со страницы https://partner.market.yandex.ru/supplier/XXXXXXX/api/settings).
	 * @var string
	 */
	protected $campaign_id;

	/**
	 * ID кабинета (https://partner.market.yandex.ru/business/XXXXXXX/).
	 * @var string
	 */
	protected $businesses_id;

	/**
	 * Добавляет к url запроса GET-параметр для дебага.
	 * @var string
	 */
	protected $debug;

	/**
	 * Feed ID.
	 * @var string
	 */
	protected $feed_id;

	/**
	 * Constructor.
	 * 
	 * @param array $args_arr
	 */
	public function __construct( array $args_arr = [] ) {

		// Сначала достаём feed_id, если он есть — для дальнейших запросов
		$feed_id = $args_arr['feed_id'] ?? '1';
		$this->feed_id = $feed_id;

		// Устанавливаем свойства
		$this->api_key = $args_arr['api_key'] ?? Y4YM_Options::settings_get( 'y4ym_api_key', '', $feed_id, 'y4ym' );
		$this->campaign_id = $args_arr['campaign_id'] ?? Y4YM_Options::settings_get( 'y4ym_campaign_id', '', $feed_id, 'y4ym' );
		$this->businesses_id = $args_arr['businesses_id'] ?? Y4YM_Options::settings_get( 'y4ym_businesses_id', '', $feed_id, 'y4ym' );
		$this->debug = $args_arr['debug'] ?? null;

	}

	// ======================= API методы =======================

	/**
	 * Возвращает список магазинов (campaigns), к которым имеет доступ 
	 * пользователь — владелец авторизационного токена.
	 *
	 * Выполняет GET-запрос к эндпоинту <code>/campaigns</code> API Яндекс.Маркета.
	 * 
	 * Поддерживает новую систему пагинации через pageToken и limit.
	 *
	 * @version         5.8.1 (08-09-2026)
	 * @since           0.1.0
	 * @see             https://yandex.ru/dev/market/partner-api/doc/ru/reference/campaigns/getCampaigns
	 * @see             https://yandex.ru/dev/market/partner-api/doc/ru/concepts/pagination
	 * @use             Y4YM_Error_Log::record() — для логирования ошибок API
	 * @use             $this->response_to_yandex() — для отправки запроса
	 * @use             $this->get_headers_arr() — для формирования заголовков (Api-Key + Bearer token)
	 *
	 * @return array{
	 *   status: bool,
	 *   body_answer?: object,
	 *   errors?: array<array-key, object{code: string, message: string}>
	 * }
	 * 
	 * Где:
	 * - `status` — `true`, если запрос завершился успешно (HTTP 200 и нет ошибок в теле ответа),
	 *            — `false`, если произошла ошибка (HTTP ≠ 200, тело содержит `errors`, или cURL-ошибка).
	 * - `body_answer` — объект ответа от Yandex (присутствует, если `status === true` и ответ декодирован успешно):
	 *   ```php
	 *   (object) {
	 *     "campaigns": [
	 *       (object) {
	 *         "id": 84006121,
	 *         "domain": "iCopyDoc",
	 *         "clientId": 107702234,
	 *         "business": (object) {
	 *           "id": 71124214,
	 *           "name": "iCopyDoc"
	 *         },
	 *         "placementType": "FBS" // тип размещения: FBS / drop-off / etc.
	 *       }
	 *     ],
	 *     "pager": (object) {
	 *       "total": 1,
	 *       "from": 1,
	 *       "to": 1,
	 *       "currentPage": 1,
	 *       "pagesCount": 1,
	 *       "pageSize": 1
	 *     }
	 *   }
	 *   ```
	 * - `errors` — массив ошибок, присутствует, если `status === false` и Yandex вернул структуру ошибок:
	 *   ```php
	 *   [
	 *     (object) {
	 *       "code": "FORBIDDEN",      // код ошибки
	 *       "message": "Token is invalid" // человеческое описание
	 *     }
	 *   ]
	 *   ```
	 *   ⚠️ Обратите внимание: Yandex возвращает HTTP-коды, но `body_answer->errors` — структура, а не HTTP-статус!
	 *
	 * 📝 Примеры возвращаемых значений:
	 *
	 * ✅ Успешный запрос:
	 * ```php
	 * [
	 *   'status' => true,
	 *   'body_answer' => (object) {
	 *     'campaigns' => [
	 *       (object) [
	 *         'id' => 84006121,
	 *         'domain' => 'iCopyDoc',
	 *         'business' => (object) ['id' => 71124214, 'name' => 'iCopyDoc'],
	 *         'placementType' => 'FBS'
	 *       ]
	 *     ],
	 *     'pager' => (object) ['total' => 1, 'currentPage' => 1]
	 *   }
	 * ]
	 * ```
	 *
	 * ❌ Ошибка авторизации (HTTP 403, тело ошибки):
	 * ```php
	 * [
	 *   'status' => false,
	 *   'errors' => [
	 *     (object) [
	 *       'code' => 'FORBIDDEN',
	 *       'message' => 'Token is invalid'
	 *     ]
	 *   ]
	 * ]
	 * ```
	 *
	 * ❌ Ошибка сети (WP_Error):
	 * ```php
	 * [
	 *   'status' => false,
	 *   'errors' => 'cURL error 28: Connection timed out'
	 * ]
	 * ```
	 *
	 * ⚠️ Важные особенности:
	 * - Метод **не перехватывает фатальные ошибки PHP**, только логические ошибки API.
	 * - Если тело ответа не JSON (например, HTML-страница 404), `body_answer` будет строкой или `null`.
	 * - Для получения `campaign_id` рекомендуется использовать `body_answer->campaigns[0]->id`.
	 * - Чтобы определить тип размещения (FBS, drop-off и т.д.) — проверяйте `campaign->placementType`.
	 */
	public function get_campaigns() {

		$result = [
			'status' => false
		];

		$all_campaigns = [];
		$page_token = null;
		$has_more = true;

		// Установим лимит по умолчанию 100, как требуется в новой API
		$default_limit = 100;

		// Цикл для сбора всех страниц, если Яндекс вернул nextPageToken
		while ( $has_more ) {

			$params_arr = [];

			// Добавляем pageToken в параметры запроса, если он есть
			if ( ! empty( $page_token ) ) {
				$params_arr['pageToken'] = $page_token;
			}

			// Всегда передаем limit, так как дефолта нет
			$params_arr['limit'] = $default_limit;

			$answer_arr = $this->response_to_yandex(
				'https://api.partner.market.yandex.ru/campaigns',
				$params_arr,
				$this->get_headers_arr(),
				'GET',
				[],
				'http_build_query'
			);

			if ( isset( $answer_arr['body_answer']->errors ) ) {
				// Ошибка получения списка магазинов

				// в случае ошибки yandex возвращает:
				// [body_request] => (NULL)
				// [status] => (boolean)1
				// [http_code] => (integer)403
				// [body_answer] => (object)
				// ---[errors] => (array)---
				// ------[0] => (object)------
				// ---------[code] => (string)FORBIDDEN
				// ---------[message] => (string)Token is invalid
				// ---[status] => (string)ERROR
				Y4YM_Error_Log::record(
					sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! Файл: %5$s; Строка: %6$s',
						$this->get_feed_id(),
						__( 'Error retrieving the list of store', 'yml-for-yandex-market' ),
						$answer_arr['body_answer']->errors[0]->code ?? 'UNKNOWN',
						$answer_arr['body_answer']->errors[0]->message ?? 'Unknown error',
						'class-ip2y-api.php',
						__LINE__
					)
				);
				$result['errors'] = $answer_arr['body_answer']->errors;
				return $result;
			}
			// в случае успеха yandex возвращает:
			// [status] => (boolean)1
			// [http_code] => (integer)200
			// [body_answer] => (object)
			// ---[campaigns] => (array)---
			// ------[0] => (object)------
			// ---------[domain] => (string)iCopyDoc
			// ---------[id] => (integer)84006121
			// ---------[clientId] => (integer)107702234
			// ---------[business] => (object)---------
			// ------------[id] => (integer)71124214
			// ------------[name] => (string)iCopyDoc
			// ---------[placementType] => (string)FBS
			// ---------["apiAvailability"] => (string)AVAILABLE
			// ---[pager] => (object)---
			// ------[total] => (integer)1
			// ------[from] => (integer)1
			// ------[to] => (integer)1
			// ------[currentPage] => (integer)1
			// ------[pagesCount] => (integer)1
			// ------[pageSize] => (integer)1
			// ---[paging]=> object(stdClass)#3965 (0) 

			$body = $answer_arr['body_answer'] ?? null;

			if ( $body && isset( $body->campaigns ) && is_array( $body->campaigns ) ) {
				$all_campaigns = array_merge( $all_campaigns, $body->campaigns );
			}

			// Проверяем наличие следующей страницы
			if ( isset( $body->nextPageToken ) && ! empty( $body->nextPageToken ) ) {
				$page_token = $body->nextPageToken;
			} else {
				$has_more = false;
			}

			// Защита от бесконечного цикла
			if ( count( $all_campaigns ) > 10000 ) {
				break;
			}
		}

		$result = [
			'status' => true,
			'body_answer' => (object) [
				'campaigns' => $all_campaigns,
				'pager' => (object) [
					'total' => count( $all_campaigns ),
					'from' => 1,
					'to' => count( $all_campaigns ),
					'currentPage' => 1,
					'pagesCount' => 1,
					'pageSize' => count( $all_campaigns )
				]
			]
		];

		return $result;

	}

	/**
	 * Обновление цен на товары.
	 * 
	 * @version			5.8.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/prices/updateBusinessPrices
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			или:
	 * 					['errors'] - array 
	 * 						- [0]["code"] => int(101)
	 *						- [0]["message"] => string(37)
	 */
	public function update_products_prices( $prices_arr = [] ) {

		$result = [
			'status' => false
		];

		$data = [
			'offers' => $prices_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/businesses/%s/offer-prices/updates',
				$this->get_businesses_id()
			),
			$data,
			$this->get_headers_arr(),
			'POST',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// Ошибка установки цен на товары

			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			Y4YM_Error_Log::record(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! %5$s: %6$s; %7$s: %8$s',
					$this->get_feed_id(),
					__( 'Error in setting product prices', 'yml-for-yandex-market' ),
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-api.php',
					__( 'File', 'yml-for-yandex-market' ),
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [
			'status' => true
		];

		return $result;

	}

	/**
	 * Обновление отстатков.
	 * 
	 * @version			5.8.0
	 * @see				https://yandex.ru/dev/market/partner-api/doc/ru/reference/stocks/updateStocks
	 * 
	 * @return	array:
	 *					['status'] - true / false (всегда)
	 *			или:
	 * 					['errors'] - array 
	 * 						- [0]["code"] => int(101)
	 *						- [0]["message"] => string(37)
	 */
	public function update_products_stocks( $skus_arr = [] ) {

		$result = [
			'status' => false
		];

		// распаковка вложенных массивов, если есть
		//	if ( ! empty( $skus_arr ) && is_array( $skus_arr[0] ?? null ) && isset( $skus_arr[0][0] ) ) {
		//		$skus_arr = array_merge( ...$skus_arr ); // flatten: [[a,b],[c]] → [a,b,c]
		//	}

		$data = [
			'skus' => $skus_arr
		];

		$answer_arr = $this->response_to_yandex(
			sprintf(
				'https://api.partner.market.yandex.ru/campaigns/%s/offers/stocks',
				$this->get_campaign_id()
			),
			$data,
			$this->get_headers_arr(),
			'PUT',
			[],
			'json_encode'
		);

		if ( isset( $answer_arr['body_answer']->errors ) ) {
			// Ошибка обновления остатков товара

			// в случае ошибки yandex возвращает:
			// [body_request] => (NULL)
			// [status] => (boolean)1
			// [http_code] => (integer)403
			// [body_answer] => (object)
			// ---[errors] => (array)---
			// ------[0] => (object)------
			// ---------[code] => (string)FORBIDDEN
			// ---------[message] => (string)Token is invalid
			// ---[status] => (string)ERROR

			Y4YM_Error_Log::record(
				sprintf( 'FEED № %1$s; ERROR: %2$s %3$s. body_answer = %4$s! %5$s: %6$s; %7$s: %8$s',
					$this->get_feed_id(),
					__( 'Error updating product stocks', 'yml-for-yandex-market' ),
					$answer_arr['body_answer']->errors[0]->code,
					$answer_arr['body_answer']->errors[0]->message,
					__( 'File', 'yml-for-yandex-market' ),
					'class-y4ym-api.php',
					__( 'File', 'yml-for-yandex-market' ),
					__LINE__
				)
			);
			$result['errors'] = $answer_arr['body_answer']->errors;
			return $result;
		}

		$result = [
			'status' => true
		];

		return $result;

	}

	/**
	 * Отправка запросов курлом.
	 * 
	 * @version			5.8.0
	 * @see				https://snipp.ru/php/curl
	 * 
	 * @param	string	$request_url - Required
	 * @param	array	$postfields_arr - Optional
	 * @param	array	$headers_arr - Optional
	 * @param	string	$request_type - Optional
	 * @param	array	$pwd_arr - Optional
	 * @param	string	$encode_type - Optional
	 * @param	int		$timeout - Optional
	 * @param	string	$proxy - Optional // example: '165.22.115.179:8080
	 * @param	bool	$debug - Optional
	 * @param	string	$sep - Optional
	 * @param	string	$useragent - Optional
	 * 
	 * @return 	array	keys: errors, status, http_code, body, header_request, header_answer
	 * 
	 */
	private function response_to_yandex(
		$request_url,
		$postfields_arr = [],
		$headers_arr = [],
		$request_type = 'POST',
		$pwd_arr = [],
		$encode_type = 'json_encode',
		$timeout = 40,
		$proxy = '',
		$debug = false,
		$sep = PHP_EOL,
		$useragent = 'PHP Bot'
	) {
		if ( ! empty( $this->get_debug() ) ) {
			$request_url = $request_url . '?dbg=' . $this->get_debug();
		}

		Y4YM_Error_Log::record( 'count body_request = ' . count( $postfields_arr ) );
		/** 
		 * if (!empty($pwd_arr)) {
		 *	if (isset($pwd_arr['login']) && isset($pwd_arr['pwd'])) {
		 *		$userpwd = $pwd_arr['login'].':'.$pwd_arr['pwd']; // 'логин:пароль'
		 *		curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
		 *	}
		 * }
		 **/

		$answer_arr = [];
		$answer_arr['request_url'] = $request_url;
		$answer_arr['body_request'] = null;
		if ( $request_type !== 'GET' ) {
			switch ( $encode_type ) {
				case 'json_encode':

					// используем json_encode с JSON_UNESCAPED_UNICODE вместо wp_json_encode, чтобы снизить размер пакета
					$body = json_encode( $postfields_arr, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE );
					if ( false === $body ) {
						$body = wp_json_encode( $postfields_arr );
					}
					$answer_arr['body_request'] = $body;

					break;
				case 'http_build_query':

					$answer_arr['body_request'] = http_build_query( $postfields_arr );

					break;
				case 'dont_encode':

					$answer_arr['body_request'] = $postfields_arr;

					break;
				default:

					$body = json_encode( $postfields_arr, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE );
					if ( $body === false ) {
						$body = wp_json_encode( $postfields_arr );
					}
					$answer_arr['body_request'] = $body;
			}
		}

		Y4YM_Error_Log::record(
			sprintf(
				'FEED № %1$s; Sending request to %2$s; %3$s: %4$s; %5$s: %6$s',
				$this->get_feed_id(),
				$request_url,
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-api.php',
				__( 'File', 'yml-for-yandex-market' ),
				__LINE__
			)
		);

		$size_request = strlen( $answer_arr['body_request'] );
		$max_request_size = 500 * 1024; // ! 500 KB - максимальный размер запроска к Маркету 

		if ( $size_request > $max_request_size ) {
			$size_mb = $size_request / 1024 / 1024;
			$error_msg = sprintf(
				'FEED № %s; ERROR: REQUEST SIZE EXCEEDS 500 KB LIMIT — %d bytes (%.2f MB), encode type — %s',
				$this->get_feed_id(),
				$size_request,
				$size_mb,
				$encode_type
			);

			Y4YM_Error_Log::record( $error_msg );
			Y4YM_Error_Log::record( 'First 3000 chars of body: ' . $answer_arr['body_request'] );

			// 🔁 ВАЖНО: вместо просто возврата — разбиваем на батчи, если это возможно (для stocks/prices/upd)
			$answer_arr['errors'] = $error_msg;
			$answer_arr['body_answer'] = null;

			// 💡 Для остатков и цен — отдаём "пустой" результат, но не обломаем весь цикл
			// (остальные товары обработаются)
			return $answer_arr;
		}

		Y4YM_Error_Log::record( $headers_arr );
		Y4YM_Error_Log::record( 'Body request size: ' . $size_request . ' bytes' );

		$args = [
			'body' => $answer_arr['body_request'],
			'method' => $request_type,
			'timeout' => $timeout,
			// 'redirection' => '5',
			'user-agent' => $useragent,
			// 'httpversion' => '1.0',
			// 'blocking'    => true,
			'headers' => $headers_arr,
			'cookies' => []
		];
		usleep( 300000 ); // притормозим на 0,3 секунды

		//	return $answer_arr; // !

		$result = wp_remote_request( $request_url, $args );

		Y4YM_Error_Log::record(
			sprintf( 'FEED № %1$s; %2$s; %3$s: %4$s; %5$s: %6$s',
				$this->get_feed_id(),
				'Server response received',
				__( 'File', 'yml-for-yandex-market' ),
				'class-y4ym-api.php',
				__( 'File', 'yml-for-yandex-market' ),
				__LINE__
			)
		);
		Y4YM_Error_Log::record( wp_json_encode( $result ) );

		if ( is_wp_error( $result ) ) {
			$answer_arr['errors'] = $result->get_error_message(); // $result->get_error_code();
			$answer_arr['body_answer'] = null;
		} else {
			$answer_arr['status'] = true; // true - получили ответ
			// Разделение полученных HTTP-заголовков и тела ответа
			$response_body = $result['body'];
			$http_code = $result['response']['code'];
			$answer_arr['http_code'] = $http_code;

			if ( $http_code == 200 ) {
				// Если HTTP-код ответа равен 200, то возвращаем отформатированное тело ответа в формате JSON
				$decoded_body = json_decode( $response_body );
				$answer_arr['body_answer'] = $decoded_body;
			} else {
				// Если тело ответа не пустое, то производится попытка декодирования JSON-кода
				if ( ! empty( $response_body ) ) {
					$decoded_body = json_decode( $response_body );
					if ( $decoded_body != null ) {
						// Если ответ содержит тело в формате JSON, 
						// то возвращаем отформатированное тело в формате JSON
						$answer_arr['body_answer'] = $decoded_body;
					} else {
						// Если не удалось декодировать JSON либо тело имеет другой формат, 
						// то возвращаем преобразованное тело ответа
						$answer_arr['body_answer'] = htmlspecialchars( $response_body );
					}
				} else {
					$answer_arr['body_answer'] = null;
				}
			}
			// Вывод необработанных HTTP-заголовков запроса и ответа
			// $answer_arr['header_request'] = curl_getinfo($curl, CURLINFO_HEADER_OUT); // Заголовки запроса
			$answer_arr['header_answer'] = $result['headers']; // Заголовки ответа
		}

		// var_dump($answer_arr['body_answer']);
		return $answer_arr;

	}

	/* Getters */

	/**
	 * Get headers for request to API.
	 * 
	 * @return array
	 */
	private function get_headers_arr() {

		return [
			'Content-Type' => 'application/json',
			'Cache-Control' => 'no-cache',
			'Api-Key' => $this->get_api_key()
			// 'X-Market-Integration' => 'Import Products to Yandex/' . Y4YM_PLUGIN_VERSION
		];

	}

	/**
	 * Get API key from Yandex Market personal account.
	 * 
	 * @return string
	 */
	private function get_api_key() {
		return $this->api_key;
	}

	/**
	 * Get campaign_id.
	 * 
	 * @return string
	 */
	private function get_campaign_id() {
		return $this->campaign_id;
	}

	/**
	 * Возвращает ID кабинета.
	 * 
	 * @return string
	 */
	private function get_businesses_id() {
		return (string) $this->businesses_id;
	}

	/**
	 * Get the debug string.
	 * 
	 * @return string
	 */
	private function get_debug() {
		return $this->debug;
	}

	/**
	 * Get feed ID.
	 * 
	 * @return string
	 */
	private function get_feed_id() {
		return $this->feed_id;
	}

}