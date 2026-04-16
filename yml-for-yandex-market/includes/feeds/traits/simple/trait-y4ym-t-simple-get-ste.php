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
 * The trait adds `get_ste` methods.
 * 
 * This method allows you to return the `ste` tag.
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
trait Y4YM_T_Simple_Get_Ste {

	/**
	 * Get `ste` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<ste>77778</ste>`
	 */
	public function get_ste( $tag_name = 'ste', $result_xml = '' ) {

		$ste = Y4YM_Options::settings_get(
			'y4ym_ste',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $ste === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'ste' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}