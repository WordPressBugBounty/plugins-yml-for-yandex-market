<?php
/**
 * Traits Weight for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.6.1 (08-08-2024)
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
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Weight {
	/**
	 * Summary of get_weight
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_weight( $tag_name = 'weight', $result_xml = '' ) {
		$tag_value = '';

		$weight = common_option_get( 'yfym_weight', false, $this->get_feed_id(), 'yfym' );
		if ( empty( $weight ) || $weight === 'woo_shippings' ) {
			$weight_yml = $this->get_offer()->get_weight(); // вес
			if ( ! empty( $weight_yml ) ) {
				$tag_value = round( wc_get_weight( $weight_yml, 'kg' ), 3 );
			}	
		} else {
			$weight = (int) $weight;
			$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $weight ) );
			if ( empty( $tag_value ) ) {
				$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $weight ) );
			}
			if ( ! empty( $tag_value ) ) {
				$tag_value = round( wc_get_weight( (float) $tag_value, 'kg' ), 3 );
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_weight',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_weight',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
			if ( $yml_rules === 'flowwow' ) {
				$result_xml = new Get_Paired_Tag( 'param', $tag_value, [ 'name' => 'Вес, кг' ] );
			} else {
				$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_weight',
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