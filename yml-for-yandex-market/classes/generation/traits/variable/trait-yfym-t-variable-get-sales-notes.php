<?php
/**
 * Traits Sales_Notes for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.2.4 (22-02-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                                      yfym_replace_decode
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Sales_Notes {
	/**
	 * Get sales_notes
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_sales_notes( $tag_name = 'sales_notes', $result_xml = '' ) {
		$tag_value = '';

		$sales_notes_cat = common_option_get( 'yfym_sales_notes_cat', false, $this->get_feed_id(), 'yfym' );
		if ( $sales_notes_cat === 'default_value' ) {
			$sales_notes = common_option_get( 'yfym_sales_notes', false, $this->get_feed_id(), 'yfym' );
			if ( ! empty( $sales_notes ) ) {
				$tag_value = $sales_notes;
			}
		} else if ( ! empty( $sales_notes_cat ) && $sales_notes_cat !== 'disabled' ) {
			$sales_notes_cat = (int) $sales_notes_cat;
			$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $sales_notes_cat ) );
			if ( empty( $tag_value ) ) {
				$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $sales_notes_cat ) );
			}
		}

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_sales_notes',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, yfym_replace_decode( $tag_value ) );
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_sales_notes',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}