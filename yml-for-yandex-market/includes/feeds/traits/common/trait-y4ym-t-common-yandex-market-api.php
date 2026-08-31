<?php defined( 'WPINC' ) || exit;

/**
 * Traits for different classes.
 *
 * @link       https://icopydoc.ru
 * @since      5.8.0
 * @version    5.8.0 (31-08-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 */

/**
 * The trait adds `get_price_for_api` and `get_stocks_for_api` methods.
 *
 * @since      5.8.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Get_Paired_Tag
 *                          Y4YM_Options
 *             traits:     
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   
 *             constants:   
 *             variable:    feed_category_id (set it)
 */
trait Y4YM_T_Common_Yandex_Market_API {

	/**
	 * Get get product price for Yandex Market API.
	 * 
	 * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/prices/updateBusinessPrices
	 * 
	 * @param float $tag_value
	 * 
	 * @return array {offerId: string, price: array{value: float, currencyId: string}}.
	 */
	public function get_price_for_api( $tag_value ) {

		$offer_id_value = $this->get_offer_id_value();
		if ( empty( $offer_id_value ) ) {
			// если данных нет, то ID-шником офера будет ID товара
			$offer_id_value = $this->get_product()->get_id();
		}
		$arr = [
			'offerId' => $offer_id_value,
			'price' => [
				'value' => (float) $tag_value,
				'currencyId' => $this->common_currency_switcher()
			]
		];
		$this->result_api_products_prices = $arr;
		return $arr;

	}

	/**
	 * Get get product stocks for Yandex Market API.
	 * 
	 * @see https://yandex.ru/dev/market/partner-api/doc/ru/reference/stocks/updateStocks
	 * 
	 * @param float $tag_value
	 * 
	 * @return array {sku: string, items: array{count: int}}.
	 */
	public function get_stocks_for_api( $tag_value ) {

		$offer_id_value = $this->get_offer_id_value();
		if ( empty( $offer_id_value ) ) {
			// если данных нет, то ID-шником офера будет ID товара
			$offer_id_value = $this->get_product()->get_id();
		}
		$arr = [
			'sku' => $offer_id_value,
			'items' => [
				'count' => (int) $tag_value,// , 
				// 'updatedAt' => '2022-12-29T18:02:01Z'
			]
		];
		$this->result_api_products_stocks = $arr;
		return $arr;

	}

}