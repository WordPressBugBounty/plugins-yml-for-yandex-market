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
 * The trait adds `get_manufacturer` method.
 * 
 * This method allows you to return the `manufacturer` tag.
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
 *                          get_variable_global_attribute_value
 *                          get_variable_tag
 */
trait Y4YM_T_Variable_Get_Manufacturer {

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
				$tag_value = $this->get_variable_product_post_meta( $manufacturer_post_meta_id );

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

				$tag_value = $this->get_variable_global_attribute_value( $manufacturer );

		}

		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}