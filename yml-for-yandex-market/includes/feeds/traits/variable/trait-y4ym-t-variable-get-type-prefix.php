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
 * The trait adds `get_type_prefix` method.
 * 
 * This method allows you to return the `typePrefix` tag.
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
trait Y4YM_T_Variable_Get_Type_Prefix {

	/**
	 * Get `typePrefix` tag.
	 * 
	 * @see https://yandex.ru/support/merchants/ru/elements/vendor-name-model
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<typePrefix>Кроссовки</typePrefix>`.
	 */
	public function get_type_prefix( $tag_name = 'typePrefix', $result_xml = '' ) {

		$type_prefix = Y4YM_Options::settings_get(
			'y4ym_type_prefix',
			'disabled',
			$this->get_feed_id(), 'y4ym' );
		if ( $type_prefix === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_variable_global_attribute_value( $type_prefix );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}