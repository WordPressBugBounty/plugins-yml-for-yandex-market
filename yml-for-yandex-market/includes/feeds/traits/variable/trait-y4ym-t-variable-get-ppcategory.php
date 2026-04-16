<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.3.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_ppcategory` methods.
 * 
 * This method allows you to return the `ppCategory` tag.
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_product_post_meta
 *                          get_variable_tag
 */
trait Y4YM_T_Variable_Get_Ppcategory {

	/**
	 * Get `ppCategory` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<ppCategory>1</ppCategory> `
	 */
	public function get_ppcategory( $tag_name = 'ppCategory', $result_xml = '' ) {

		$ppcategory = Y4YM_Options::settings_get(
			'y4ym_ppcategory',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $ppcategory === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'ppcategory' );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}