<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_purchase_price` method.
 * 
 * This method allows you to return the `purchase_price` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_product_post_meta
 */
trait Y4YM_T_Variable_Get_Purchase_Price {

	/**
	 * Get `purchase_price` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/fields/#purchase-price
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<purchase_price>200</purchase_price>`.
	 */
	public function get_purchase_price( $tag_name = 'purchase_price', $result_xml = '' ) {

		$purchase_price = Y4YM_Options::settings_get(
			'y4ym_purchase_price',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $purchase_price === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'purchase_price' );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}