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
 * The trait adds `get_manufacturer` method.
 * 
 * This method allows you to return the `manufacturer` tag.
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
 *                          get_simple_global_attribute_value
 *                          get_simple_tag
 */
trait Y4YM_T_Simple_Get_Manufacturer {

	/**
	 * Get `manufacturer` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	public function get_manufacturer( $tag_name = 'manufacturer', $result_xml = '' ) {

		$manufacturer = Y4YM_Options::settings_get(
			'y4ym_manufacturer',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);

		if ( $manufacturer === 'disabled' ) {
			return $result_xml;
		}
		switch ( $manufacturer ) {
			case 'post_meta':

				$manufacturer_post_meta_id = Y4YM_Options::settings_get(
					'y4ym_manufacturer_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);
				$tag_value = $this->get_simple_product_post_meta( $manufacturer_post_meta_id );

				break;
			case 'default_value':

				$tag_value = Y4YM_Options::settings_get(
					'y4ym_manufacturer_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);

				break;
			default:

				$tag_value = $this->get_simple_global_attribute_value( $manufacturer );

		}

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}