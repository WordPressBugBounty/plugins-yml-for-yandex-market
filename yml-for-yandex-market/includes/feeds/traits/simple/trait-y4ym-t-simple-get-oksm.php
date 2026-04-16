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
 * The trait adds `get_oksm` methods.
 * 
 * This method allows you to return the `oksm` tag.
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 */
trait Y4YM_T_Simple_Get_Oksm {

	/**
	 * Get `oksm` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<oksm>Россия</oksm>`
	 */
	public function get_oksm( $tag_name = 'oksm', $result_xml = '' ) {

		$oksm = Y4YM_Options::settings_get(
			'y4ym_oksm',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $oksm === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_simple_global_attribute_value( $oksm );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}