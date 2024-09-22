<?php
/**
 * Traits Custom_Score for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.7.0
 * 
 * @version                 4.7.0 (09-09-2024)
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

trait YFYM_T_Variable_Get_Custom_Score {
	/**
	 * Get `custom_score` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_custom_score( $tag_name = 'custom_score', $result_xml = '' ) {
		$custom_score = common_option_get( 'yfym_custom_score', false, $this->get_feed_id(), 'yfym' );
		if ( $custom_score === 'enabled' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_yfym_custom_score', true );
			$tag_value = apply_filters(
				'y4ym_f_variable_tag_value_custom_score',
				$tag_value,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			if ( ! empty( $tag_value ) ) {
				$tag_name = apply_filters(
					'y4ym_f_variable_tag_name_custom_score',
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
				'y4ym_f_variable_tag_custom_score',
				$result_xml,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;
	}
}