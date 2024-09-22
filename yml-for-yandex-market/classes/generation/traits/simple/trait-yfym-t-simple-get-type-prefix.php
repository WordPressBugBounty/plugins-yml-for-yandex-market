<?php
/**
 * Traits Type Prefix for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.1.4
 * 
 * @version                 4.1.4 (04-12-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Type_Prefix {
	/**
	 * Summary of get_type_prefix
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_type_prefix( $tag_name = 'typePrefix', $result_xml = '' ) {
		$tag_value = '';

		$type_prefix = common_option_get( 'yfym_type_prefix', false, $this->get_feed_id(), 'yfym' );
		if ( empty( $type_prefix ) || $type_prefix === 'disabled' ) {

		} else {
			$type_prefix = (int) $type_prefix;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $type_prefix ) );
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_type_prefix',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		if ( ! empty( $tag_value ) ) {

			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_type_prefix',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_type_prefix',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		return $result_xml;
	}
}