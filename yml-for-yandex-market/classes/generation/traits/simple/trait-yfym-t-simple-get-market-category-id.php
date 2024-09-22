<?php
/**
 * Traits Market_Category_Id for simple products
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
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Market_Category_Id {
	/**
	 * Get `market_category_id` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_market_category_id( $tag_name = 'market_category_id', $result_xml = '' ) {
		$market_category_id = common_option_get( 'yfym_market_category_id', false, $this->get_feed_id(), 'yfym' );
		if ( $market_category_id === 'enabled' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_yfym_market_category_id', true );
			$tag_value = apply_filters(
				'y4ym_f_simple_tag_value_market_category_id',
				$tag_value,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			if ( ! empty( $tag_value ) ) {
				$tag_name = apply_filters(
					'y4ym_f_simple_tag_name_market_category_id',
					$tag_name,
					[ 
						'product' => $this->get_product()
					],
					$this->get_feed_id()
				);
				$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
			}

			$result_xml = apply_filters(
				'y4ym_f_simple_tag_market_category_id',
				$result_xml,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;
	}
}