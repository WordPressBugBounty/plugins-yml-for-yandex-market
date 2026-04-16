<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.3.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_max_quantity` method.
 * 
 * This method allows you to return the `max-quantity` tag.
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *                          get_simple_tag
 */
trait Y4YM_T_Simple_Get_Max_Quantity {

	/**
	 * Get `max-quantity` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<max-quantity>2</max-quantity>`.
	 */
	public function get_max_quantity( $tag_name = 'max-quantity', $result_xml = '' ) {

		$max_quantity = Y4YM_Options::settings_get(
			'y4ym_max_quantity',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $max_quantity === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'max_quantity' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}