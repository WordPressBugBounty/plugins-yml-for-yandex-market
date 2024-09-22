<?php
/**
 * Traits Cofinance Price for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.1.1
 * 
 * @version                 4.1.1 (27-11-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support/marketplace/assortment/fields/index.html
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Cofinance_Price {
	/**
	 * Summary of get_cofinance_price
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_cofinance_price( $tag_name = 'cofinance_price', $result_xml = '' ) {
		$cofinance_price = common_option_get( 'yfym_cofinance_price', false, $this->get_feed_id(), 'yfym' );
		if ( $cofinance_price !== 'enabled' ) {
			return $result_xml;
		}

		$tag_value = '';
		if ( get_post_meta( $this->get_product()->get_id(), '_yfym_cofinance_price', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_yfym_cofinance_price', true );
		}
		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_cofinance_price',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_cofinance_price',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_cofinance_price',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}