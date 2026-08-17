<?php defined( 'WPINC' ) || exit;

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.7.0
 * @version    5.7.0 (17-08-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_param_composition` methods.
 * 
 * This method allows you to return the `composition` tag.
 *
 * @since      5.7.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 */
trait Y4YM_T_Variable_Get_Param_Composition {

	/**
	 * Get `composition` tag.
	 * 
	 * @see https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<param name="composition">бадан, золотой корень</param>`
	 */
	public function get_param_composition( $tag_name = 'composition', $result_xml = '' ) {

		$composition = Y4YM_Options::settings_get(
			'y4ym_param_composition',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $composition === 'disabled' ) {
			return $result_xml;
		} else {
			$tag_value = $this->get_variable_global_attribute_value( $composition );
			$result_xml = apply_filters(
				'y4ym_f_variable_tag_' . strtolower( $tag_name ),
				$result_xml,
				[
					'product' => $this->get_product(),
					'offer' => $this->get_offer(),
					'feed_category_id' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			if ( ! empty( $tag_value ) ) {
				$result_xml .= new Y4YM_Get_Paired_Tag(
					'param',
					ucfirst( y4ym_replace_decode( $tag_value ) ),
					[ 'name' => htmlspecialchars( $tag_name ) ]
				);
			}
		}
		return $result_xml;

	}

}