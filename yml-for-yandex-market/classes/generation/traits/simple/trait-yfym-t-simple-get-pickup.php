<?php
/**
 * Traits Pickup for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.4.3 (18-07-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support2/marketplace/ru/assortment/fields/#pickup
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Pickup {
	/**
	 * Get pickup tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_pickup( $tag_name = 'pickup', $result_xml = '' ) {
		$tag_value = '';

		if ( get_post_meta( $this->get_product()->get_id(), 'yfym_individual_pickup', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), 'yfym_individual_pickup', true );
			if ( empty( $tag_value ) || $tag_value === 'off' || $tag_value === 'disabled' ) {
				$tag_value = common_option_get( 'yfym_pickup', false, $this->get_feed_id(), 'yfym' );
			}
		} else {
			$tag_value = common_option_get( 'yfym_pickup', false, $this->get_feed_id(), 'yfym' );
		}

		if ( ! empty( $tag_value ) && $tag_value !== 'disabled' ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_pickup',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_pickup',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}