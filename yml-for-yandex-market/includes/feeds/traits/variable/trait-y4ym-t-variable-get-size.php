<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.0.2
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_size` methods.
 * 
 * This method allows you to return the `size` tag.
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
 */
trait Y4YM_T_Variable_Get_Size {

	/**
	 * Get `size` tag.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-trebovaniya-k-faylu
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<size>XL</size>`
	 */
	public function get_size( $tag_name = 'size', $result_xml = '' ) {

		$size = Y4YM_Options::settings_get(
			'y4ym_size',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $size === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_variable_global_attribute_value( $size );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}