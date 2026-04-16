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
 * The trait adds `get_supplier` method.
 * 
 * This method allows you to return the `supplier` tag.
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
trait Y4YM_T_Variable_Get_Supplier {

	/**
	 * Get `supplier` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<supplier>200</supplier>`.
	 */
	public function get_supplier( $tag_name = 'supplier', $result_xml = '' ) {

		$supplier = Y4YM_Options::settings_get(
			'y4ym_supplier',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $supplier === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'supplier' );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}