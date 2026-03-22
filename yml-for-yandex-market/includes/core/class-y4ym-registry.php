<?php

/**
 * Y4YM Registry — Centralized storage for shared data.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.3.0 (22-03-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/core
 */

/**
 * Y4YM Registry — Centralized storage for shared data (regions, OKEI, etc.).
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/core
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 */
class Y4YM_Registry {

	/**
	 * Хранит данные в памяти, чтобы не читать повторно.
	 *
	 * @var array
	 */
	private static $data = [];

	// === РЕГИОНЫ ПОРТАЛА ПОСТАВЩИКОВ МОСКВЫ ===

	/**
	 * Получить список регионов для "Портал поставщиков Москвы".
	 *
	 * @return array
	 */
	public static function get_regions_list() {

		if ( ! isset( self::$data['regions'] ) ) {
			self::$data['regions'] = get_option( 'y4ym_zakupki_mos_regions', [] );
			if ( empty( self::$data['regions'] ) ) {
				// Если нет в опциях — парсим XML и сохраняем
				self::$data['regions'] = self::parse_xml_regions();
				if ( ! empty( self::$data['regions'] ) ) {
					update_option( 'y4ym_zakupki_mos_regions', self::$data['regions'], false );
				}
			}
		}
		return self::$data['regions'];

	}

	/**
	 * Парсинг XML с регионами.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 *
	 * @return array
	 */
	private static function parse_xml_regions() {

		$file = Y4YM_PLUGIN_DIR_PATH . 'assets/data/zakupki-mos-regions.xml';

		if ( ! file_exists( $file ) ) {
			return []; // Файл не найден
		}

		$xml_string = file_get_contents( $file );
		$xml_object = new SimpleXMLElement( $xml_string, LIBXML_NOERROR | LIBXML_ERR_NONE );

		// Регистрация единственного пространства имен
		$xml_object->registerXPathNamespace( 'spv', 'http://market.zakupki.mos.ru/spIntegration/1.0' );

		// Выбор всех элементов regionType с указанным пространством имен
		$regions = $xml_object->xpath( '//spv:regionType' );

		$list = [];
		foreach ( $regions as $region ) {
			$attributes = $region->attributes();

			if ( isset( $attributes['id'] ) && isset( $attributes['name'] ) ) {
				$list[] = [
					'value' => (string) $attributes['id'],
					'text' => (string) $attributes['name']
				];
			}
		}

		return $list;

	}

	// === ОКЕИ (Общероссийский классификатор единиц измерения) ===

	/**
	 * Получить список ОКЕИ.
	 *
	 * @return array
	 */
	public static function get_okei_list() {

		if ( ! isset( self::$data['okei'] ) ) {
			// Сначала пробуем кэш
			self::$data['okei'] = wp_cache_get( 'y4ym_okei_list', 'y4ym' );
			if ( false === self::$data['okei'] ) {
				self::$data['okei'] = self::parse_xml_okei();
				if ( ! empty( self::$data['okei'] ) ) {
					wp_cache_set( 'y4ym_okei_list', self::$data['okei'], 'y4ym', HOUR_IN_SECONDS * 24 );
				}
			}
		}
		return self::$data['okei'];

	}

	/**
	 * Парсинг XML с ОКЕИ.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 *
	 * @return array
	 */
	private static function parse_xml_okei() {

		$file = Y4YM_PLUGIN_DIR_PATH . 'assets/data/zakupki-mos-okei.xml';
		if ( ! file_exists( $file ) ) {
			return [];
		}

		$xml_string = file_get_contents( $file );
		if ( false === $xml_string ) {
			return [];
		}

		$xml = new SimpleXMLElement( $xml_string, LIBXML_NOERROR | LIBXML_ERR_NONE );
		$xml->registerXPathNamespace( 'spv', 'http://market.zakupki.mos.ru/spIntegration/1.0' );

		$items = $xml->xpath( '//spv:okeiType' );
		if ( false === $items || empty( $items ) ) {
			return [];
		}

		$list = [];
		foreach ( $items as $item ) {
			$attributes = $item->attributes();
			if ( isset( $attributes['id'], $attributes['name'], $attributes['code'] ) ) {
				$list[] = [
					'value' => (string) $attributes['id'],
					'text' => (string) $attributes['name'],
					'name' => (string) $attributes['name'],
					'code' => (string) $attributes['code'],
					'id' => (string) $attributes['id']
				];
			}
		}

		return $list;

	}

	// === ПАКЕТИРОВКА (packageType) ===

	/**
	 * Получить типы упаковки.
	 *
	 * @return array
	 */
	public static function get_packagetypes_list() {

		if ( ! isset( self::$data['packagetypes'] ) ) {
			self::$data['packagetypes'] = get_option( 'y4ym_zakupki_mos_packagetypes', [] );
			if ( empty( self::$data['packagetypes'] ) ) {
				self::$data['packagetypes'] = self::parse_xml_packagetypes();
				if ( ! empty( self::$data['packagetypes'] ) ) {
					update_option( 'y4ym_zakupki_mos_packagetypes', self::$data['packagetypes'], false );
				}
			}
		}
		return self::$data['packagetypes'];

	}

	/**
	 * Парсинг XML с типами упаковки.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 *
	 * @return array
	 */
	private static function parse_xml_packagetypes() {

		$file = Y4YM_PLUGIN_DIR_PATH . 'assets/data/zakupki-mos-packagetypes.xml';
		if ( ! file_exists( $file ) ) {
			return [];
		}

		$xml_string = file_get_contents( $file );
		if ( false === $xml_string ) {
			return [];
		}

		$xml = new SimpleXMLElement( $xml_string, LIBXML_NOERROR | LIBXML_ERR_NONE );
		$xml->registerXPathNamespace( 'spv', 'http://market.zakupki.mos.ru/spIntegration/1.0' );

		$items = $xml->xpath( '//spv:packageType' );
		if ( false === $items || empty( $items ) ) {
			return [];
		}

		$list = [];
		foreach ( $items as $item ) {
			$attributes = $item->attributes();
			if ( isset( $attributes['id'], $attributes['name'] ) ) {
				$list[] = [
					'value' => (string) $attributes['id'],
					'text' => (string) $attributes['name']
				];
			}
		}

		return $list;

	}

	// === Категории продукции (ppCategory) ===

	/**
	 * Получить категории продукции.
	 *
	 * @return array
	 */
	public static function get_ppcategories_list() {

		if ( ! isset( self::$data['ppcategories'] ) ) {
			self::$data['ppcategories'] = get_option( 'y4ym_zakupki_mos_ppcategories', [] );
			if ( empty( self::$data['ppcategories'] ) ) {
				self::$data['ppcategories'] = self::parse_xml_ppcategories();
				if ( ! empty( self::$data['ppcategories'] ) ) {
					update_option( 'y4ym_zakupki_mos_ppcategories', self::$data['ppcategories'], false );
				}
			}
		}
		return self::$data['ppcategories'];

	}

	/**
	 * Парсинг XML с категориями продукции.
	 *
	 * @return array
	 */
	/**
	 * Парсинг XML с категориями продукции.
	 *
	 * @return array
	 */
	private static function parse_xml_ppcategories() {

		$file = Y4YM_PLUGIN_DIR_PATH . 'assets/data/zakupki-mos-ppcategories.xml';
		if ( ! file_exists( $file ) ) {
			return [];
		}

		$xml_string = file_get_contents( $file );
		if ( false === $xml_string ) {
			return [];
		}

		$xml = new SimpleXMLElement( $xml_string, LIBXML_NOERROR | LIBXML_ERR_NONE );
		$xml->registerXPathNamespace( 'spv', 'http://market.zakupki.mos.ru/spIntegration/1.0' );

		$items = $xml->xpath( '//spv:productCategoryType' );
		if ( false === $items || empty( $items ) ) {
			return [];
		}

		$list = [];
		foreach ( $items as $item ) {
			$attributes = $item->attributes();
			if ( isset( $attributes['id'], $attributes['name'] ) ) {
				$list[] = [
					'value' => (string) $attributes['id'],
					'text' => (string) $attributes['name']
				];
			}
		}

		return $list;

	}

	/**
	 * Преобразует список элементов из формата ['value'=>'...', 'text'=>'...'] 
	 * в ассоциативный массив ['id' => 'name'].
	 * 
	 * Пример: `$regions = Y4YM_Registry::to_key_value_pairs( Y4YM_Registry::get_regions_list() );`.
	 *
	 * @param array $items Список в формате [ ['value' => '101', 'text' => 'Байконур'], ... ].
	 * 
	 * @return array Возвращает [ '101' => 'Байконур', ... ]
	 */
	public static function to_key_value_pairs( $items ) {

		static $cache = [];

		// Создаём уникальный ключ на основе хеша массива (или просто используем сериализацию)
		$hash = md5( serialize( $items ) );

		if ( ! isset( $cache[ $hash ] ) ) {
			$result = [];
			foreach ( $items as $item ) {
				if ( isset( $item['value'], $item['text'] ) ) {
					$result[ $item['value'] ] = $item['text'];
				}
			}
			$cache[ $hash ] = $result;
		}

		return $cache[ $hash ];

	}

}