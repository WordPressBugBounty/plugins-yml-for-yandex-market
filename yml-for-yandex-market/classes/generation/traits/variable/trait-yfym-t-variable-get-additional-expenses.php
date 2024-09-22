<?php
/**
 * Traits Additional Expenses for variable products
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
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Additional_Expenses {
	/**
	 * Summary of get_additional_expenses
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_additional_expenses( $tag_name = 'additional_expenses', $result_xml = '' ) {
		$additional_expenses = common_option_get( 'yfym_additional_expenses', false, $this->get_feed_id(), 'yfym' );
		if ( $additional_expenses !== 'enabled' ) {
			return $result_xml;
		}

		$tag_value = '';
		if ( get_post_meta( $this->get_product()->get_id(), '_yfym_additional_expenses', true ) !== '' ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_yfym_additional_expenses', true );
		}
		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_additional_expenses',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_additional_expenses',
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
			'y4ym_f_variable_tag_additional_expenses',
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