<?php // TODO: актуален ли

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
 * The trait adds `get_store` method.
 * 
 * This method allows you to return the `store` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 */
trait Y4YM_T_Simple_Get_Store {

	/**
	 * Get `store` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<store>true</store>`.
	 */
	public function get_store( $tag_name = 'store', $result_xml = '' ) {

		$tag_value = Y4YM_Options::settings_get(
			'y4ym_store',
			'true',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $tag_value === 'disabled' ) {
			return $result_xml;
		} else {
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
			return $result_xml;
		}

	}

}