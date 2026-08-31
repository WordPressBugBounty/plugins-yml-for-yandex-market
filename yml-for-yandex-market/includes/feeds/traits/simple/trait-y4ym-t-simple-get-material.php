<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.8.0
 * @version    5.8.0 (31-08-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_material` methods.
 * 
 * This method allows you to return the `material` tag.
 *
 * @since      5.8.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 */
trait Y4YM_T_Simple_Get_Material {

	/**
	 * Get `material` tag.
	 * 
	 * @see https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/edit#heading=h.gjdgxs
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<material>Полиэстер</material>`.
	 */
	public function get_material( $tag_name = 'material', $result_xml = '' ) {

		$material = Y4YM_Options::settings_get(
			'y4ym_material',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $material === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_simple_global_attribute_value( $material );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}