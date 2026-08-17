<?php defined( 'WPINC' ) || exit;

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.7.0
 * @version    5.7.0 (17-08-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_minorder` method.
 * 
 * This method allows you to return the `minorder` tag.
 *
 * @since      5.7.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 *                          get_simple_product_post_meta
 *                          get_simple_tag
 */
trait Y4YM_T_Simple_Get_Minorder {

	/**
	 * Get `minorder` tag.
	 * 
	 * @see https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<minorder>2</minorder>`.
	 */
	public function get_minorder( $tag_name = 'minorder', $result_xml = '' ) {

		$minorder = Y4YM_Options::settings_get(
			'y4ym_minorder',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $minorder === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'minorder' );
			$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}