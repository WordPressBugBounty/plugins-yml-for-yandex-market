<?php
/**
 * Set and Get the Plugin Data
 *
 * @package                 iCopyDoc Plugins (v1, core 16-08-2023)
 * @subpackage              YML for Yandex Market
 * @since                   4.1.12
 * 
 * @version                 4.7.2 (16-09-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       array       $data_arr - Optional
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    
 *                          functions:  
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class Y4YM_Rules_List {
	/**
	 * The rules of feeds array
	 *
	 * @var array
	 */
	private $rules_arr = [];

	/**
	 * Set and Get the Plugin Data
	 * 
	 * @param array $rules_arr - Optional
	 */
	public function __construct( $rules_arr = [] ) {
		if ( empty( $rules_arr ) ) {
			$this->rules_arr = [ 
				'yandex_market_assortment' => [ 'currencies',
					// яндекс маркет для управления товарами
					// https://yandex.ru/support2/marketplace/ru/assortment/auto/yml
					// https://yastatic.net/s3/doc-binary/src/support/market/ru/YML_sample_catalog.zip
					// https://yandex.ru/support2/marketplace/ru/assortment/fields/
					'offer_tag', 'market_category_id', 'disabled', 'archived', 'params', 'name', 'enable_auto_discounts', 'description',
					'picture', 'url', 'count', 'amount', 'barcode', 'weight', 'dimensions', 'expiry', 'age', 'video',
					'downloadable', 'sales_notes', 'country_of_origin', 'manufacturer_warranty', 'warranty_days',
					'vendor', 'vendorcode', 'store', 'pickup', 'delivery', 'categoryid', 'vat', 'delivery_options',
					'pickup_options', 'condition', 'additional_expenses', 'cofinance_price', 'purchase_price', 'oldprice'
				],
				'sales_terms' => [ 'currencies',
					// яндекс маркет для управления размещением
					// https://yastatic.net/s3/doc-binary/src/support/market/ru/YML_sample_sales_terms.zip
					'offer_tag', 'market_category_id', 'url', 'disabled', 'enable_auto_discounts', 'vat', 'delivery', 'pickup', 'delivery_options',
					'pickup_options', 'count', 'oldprice' // ? есть ли поддержка 'store', 
				],
				'yandex_direct' => [ 'currencies', // https://yandex.ru/support/direct/feeds/requirements-yml.html
					'offer_tag', 'url', 'categoryid', 'picture', 'store', 'pickup', 'delivery', 'name', 'vendor',
					'vendorcode', 'description', 'video', 'sales_notes', 'manufacturer_warranty', 'country_of_origin',
					'age', 'downloadable', 'params', 'collection_id',
					'oldprice', 'adult', 'market_category', 'custom_labels', 'custom_score'
				],
				'yandex_direct_free_from' => [ 'currencies', // https://yandex.ru/support/direct/feeds/requirements-yml.html
					'offer_tag', 'url', 'categoryid', 'picture', 'store', 'pickup', 'delivery', 'type_prefix', 'vendor',
					'model', 'vendorcode', 'description', 'video', 'sales_notes', 'manufacturer_warranty', 'country_of_origin',
					'age', 'downloadable', 'params', 'collection_id', 'oldprice',
					'adult', 'market_category', 'custom_labels', 'custom_score'
				],
				'yandex_direct_combined' => [ 'currencies', // https://yandex.ru/support/direct/feeds/requirements-yml.html
					'offer_tag', 'url', 'categoryid', 'picture', 'store', 'pickup', 'delivery', 'type_prefix', 'name',
					'vendor', 'model', 'vendorcode', 'description', 'video', 'sales_notes', 'manufacturer_warranty',
					'country_of_origin', 'age', 'downloadable', 'params',
					'collection_id', 'oldprice', 'adult', 'market_category', 'custom_labels', 'custom_score'
				],
				'single_catalog' => [ 'currencies',  // ! shop_sku устарел
					'offer_tag', 'disabled', 'archived', 'params', 'name', 'enable_auto_discounts', 'description',
					'picture', 'url', 'count', 'barcode', 'weight', 'dimensions', 'expiry', 'period_of_validity_days',
					'age', 'downloadable', 'country_of_origin', 'manufacturer', 'market_sku', 'tn_ved_codes',
					'recommend_stock_data', 'manufacturer_warranty', 'warranty_days', 'vendor', 'shop_sku',
					'vendorcode', 'store', 'pickup', 'delivery', 'categoryid', 'vat', 'delivery_options',
					'pickup_options', 'condition', 'credit_template', 'supplier', 'min_quantity', 'step_quantity',
					'additional_expenses', 'cofinance_price', 'purchase_price', 'oldprice'
				],
				'yandex_webmaster' => [ 'currencies',
					// Яндекс Вебмастер, Товарный фид, Товары и предложения
					// https://yandex.ru/support/webmaster/feed/upload.html
					// https://yandex.ru/support/products/features.html
					// https://yandex.ru/support/products/connect/form-feed.html#form-feed__step1
					'offer_tag', 'disabled', 'archived', 'barcode', 'categoryid', 'condition', 'credit_template', 'delivery_options',
					'delivery', 'pickup_options', 'description', 'dimensions', 'instock', 'keywords', 'manufacturer',
					'market_sku', 'min_quantity', 'model', 'name', 'params', 'period_of_validity_days',
					'picture', 'recommend_stock_data', 'sales_notes', 'shop_sku', 'step_quantity', 'tn_ved_codes',
					'url', 'cargo_types', 'vendor', 'vendorcode', 'weight', 'oldprice'
				],
				'vk' => [ 
					/**
					 * - Размер YML-файла — до 8 Мбайт.
					 * - До 15 000 товаров в файле. Каждый вариант товара считается за отдельный товар.
					 * - До 2 свойств у товара, до 50 значений у каждого свойства. Товар, превышающий эти лимиты,
					 * может быть некорректно обработан.
					 */
					'currencies',
					'offer_tag', 'categoryid', 'name', 'description', 'params', 'picture', // ! categoryid можно все указывать
					'url', 'shop_sku', 'disabled', 'count', 'barcode', 'dimensions', 'weight', 'oldprice' // ? есть ли поддержка
				],
				'sbermegamarket' => [ // https://s3.megamarket.tech/mms/documents/assortment/Инструкция%20к%20фиду%20xml.pdf
					'currencies', // https://partner-wiki.megamarket.ru/merchant-api/1-vvedenie/1-1-tovarnyj-fid
					'offer_tag', 'url', 'name', 'categoryid', 'picture', 'vat', 'shipment_options',
					'vendor', 'vendorcode', 'model', 'description', 'barcode', 'outlets', 'params',
					'disabled', 'dimensions', 'weight', 'oldprice'
				],
				'ozon' => [ 
					'currencies', // https://seller-edu.ozon.ru/work-with-goods/zagruzka-tovarov/created-goods/fidi
					'offer_tag', 'min_price', 'outlets', 'disabled', 'name', 'url', // 'premium_price',
					'categoryid', 'market_sku', 'oldprice' // 'count', 'amount', 
				],
				'flowwow' => [ 'currencies', // https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/edit#heading=h.gjdgxs
					'offer_tag', 'url', 'categoryid', 'picture', 'store', 'pickup', 'delivery', 'name', 'vendor', 'vendorcode',
					'description', 'sales_notes', 'delivery_options', 'pickup_options', 'oldprice', 'qty',
					'params', 'weight', 'dimensions', 'consists'
				],
				'all_elements' => [ 'currencies',
					'offer_tag', 'disabled', 'archived', 'age', 'amount', 'barcode', 'categoryid', 'condition', 'count',
					'country_of_origin', 'credit_template', 'delivery_options', 'delivery', 'description', 'dimensions',
					'downloadable', 'enable_auto_discounts', 'expiry', 'instock', 'keywords', 'manufacturer_warranty',
					'warranty_days', 'manufacturer', 'market_sku', 'min_quantity', 'model', 'name', 'outlets', 'params',
					'period_of_validity_days', 'pickup_options', 'pickup', 'picture', 'premium_price',
					'recommend_stock_data', 'sales_notes', 'shop_sku', 'step_quantity', 'store', 'supplier',
					'tn_ved_codes', 'url', 'vat', 'cargo_types', 'vendor', 'vendorcode', 'video', 'weight', 'price_rrp',
					'additional_expenses', 'cofinance_price', 'purchase_price', 'type_prefix', 'oldprice', 'adult',
					'market_category', 'market_category_id', 'custom_labels', 'custom_score'
				]
			];
		} else {
			$this->rules_arr = $rules_arr;
		}

		$this->rules_arr = apply_filters( 'y4ym_f_set_rules_arr', $this->get_rules_arr() );
	}

	/**
	 * Get the rules of feeds array
	 * 
	 * @return array
	 */
	public function get_rules_arr() {
		return $this->rules_arr;
	}
}