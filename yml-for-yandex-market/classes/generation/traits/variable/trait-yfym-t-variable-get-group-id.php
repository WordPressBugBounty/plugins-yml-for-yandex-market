<?php
/**
 * Traits Group_Id for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.2.6 (26-03-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends					classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Group_Id {
	/**
	 * Summary of get_additional_expenses
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_group_id( $tag_name = 'group_id', $result_xml = '' ) {
		$tag_value = '';

		$yfym_group_id = common_option_get( 'yfym_group_id', false, $this->get_feed_id(), 'yfym' );
		if ( empty ( $yfym_group_id ) || $yfym_group_id === 'disabled' ) {

		} else {
			$tag_value = $this->get_product()->get_id();
		}
		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_group_id',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		if ( ! empty ( $tag_value ) ) {

			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_group_id',
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
			'y4ym_f_variable_tag_group_id',
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