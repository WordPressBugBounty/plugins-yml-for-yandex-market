<?php
/**
 * Traits Delivery for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.3.3 (14-05-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support2/marketplace/ru/assortment/fields/#delivery
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

trait YFYM_T_Variable_Get_Delivery {
	/**
	 * Summary of get_delivery
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_delivery( $tag_name = 'delivery', $result_xml = '' ) {
		$tag_value = '';

		if ( get_post_meta( $this->get_product()->get_id(), 'yfym_individual_delivery', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), 'yfym_individual_delivery', true );
			if ( empty( $tag_value ) || $tag_value === 'off' || $tag_value === 'disabled' ) {
				$tag_value = common_option_get( 'yfym_delivery', false, $this->get_feed_id(), 'yfym' );
			}
		} else {
			$tag_value = common_option_get( 'yfym_delivery', false, $this->get_feed_id(), 'yfym' );
		}

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_delivery',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_delivery',
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