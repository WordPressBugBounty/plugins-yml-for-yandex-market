<?php defined( 'WPINC' ) || exit;

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.9.0
 * @version    5.9.0 (22-09-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_compliance_document_type` method.
 * 
 * This method allows you to return the `compliance_document_type` tag.
 *
 * @since      5.9.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   
 */
trait Y4YM_T_Variable_Get_Compliance_Document_Type {

	/**
	 * Get `compliance_document_type` and `compliance_document_link` tags.
	 * 
	 * @see https://seller-docs.flowwow.com/2.-upravlenie-tovarami/2.11-import-yml-1/trebovaniya-k-yml-i-xml-failam
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<compliance_document_type>4</compliance_document_type>`.
	 */
	public function get_compliance_document_type( $tag_name = 'compliance_document_type', $result_xml = '' ) {

		$compliance_document_type = Y4YM_Options::settings_get(
			'y4ym_compliance_document_type',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $compliance_document_type === 'disabled' ) {
			return $result_xml;
		}

		$tag_value = $this->get_variable_product_post_meta( 'compliance_document_type' );
		if ( $tag_value === 'disabled' ) {
			// если отключено на уровне тоара
			return $result_xml;
		}

		if ( $tag_value === 'default' ) {
			$tag_value = Y4YM_Options::settings_get(
				'y4ym_compliance_document_type_default_value',
				'4',
				$this->get_feed_id(),
				'y4ym'
			);
		}
		$result_xml .= $this->get_variable_tag( $tag_name, $tag_value );
		$compliance_document_link_tag_value = $this->get_variable_product_post_meta( 'compliance_document_link' );
		$result_xml .= $this->get_variable_tag( 'compliance_document_link', $compliance_document_link_tag_value );
		return $result_xml;

	}

}
