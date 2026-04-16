<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_qty` method.
 * 
 * This method allows you to return the `qty` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 */
trait Y4YM_T_Simple_Get_Qty {

	/**
	 * Get `qty` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<qty>51</qty>`.
	 */
	public function get_qty( $tag_name = 'qty', $result_xml = '' ) {

		$qty = Y4YM_Options::settings_get(
			'y4ym_qty',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $qty === 'enabled' ) {
			$tag_value = '';
			if ( true === $this->get_product()->get_manage_stock() ) {
				// включено управление запасом на уровне товара
				$stock_quantity = $this->get_product()->get_stock_quantity();
				if ( $stock_quantity > -1 ) {
					$tag_value = $stock_quantity;
				} else {
					$tag_value = (int) 0;
				}
			}
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}