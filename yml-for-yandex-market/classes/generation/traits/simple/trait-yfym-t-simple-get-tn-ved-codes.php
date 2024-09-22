<?php
/**
 * Traits Tn Ved Codes for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.7.2 (16-09-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  get_nested_tag
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Tn_Ved_Codes {
	/**
	 * Get `tn-ved-code` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_tn_ved_codes( $wrapper_tag_name = 'tn-ved-codes', $result_xml = '' ) {
		if ( get_post_meta( $this->get_product()->get_id(), '_yfym_tn_ved_code', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_yfym_tn_ved_code', true );
			$result_xml = get_nested_tag( $wrapper_tag_name, 'tn-ved-code', $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_tn_ved_code',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}