<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.3.0
 * @version    5.3.0 (22-03-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_isvisibletostatecustomers` methods.
 * 
 * This method allows you to return the `isVisibleToStateCustomers` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Isvisibletostatecustomers {

	/**
	 * Get `isVisibleToStateCustomers` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<isVisibleToStateCustomers>true</isVisibleToStateCustomers>`.
	 */
	public function get_isvisibletostatecustomers( $tag_name = 'isVisibleToStateCustomers', $result_xml = '' ) {

		$isvisibletostatecustomers = common_option_get(
			'y4ym_isvisibletostatecustomers',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $isvisibletostatecustomers === 'disabled' ) {
			return $result_xml;
		}

		if ( $this->get_offer()->get_stock_status() !== 'instock'
			|| $isvisibletostatecustomers === 'allfalse'
		) {
			$tag_value = 'false';
		} else {
			$tag_value = 'true';
		}

		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}