<?php
/**
 * Traits Qty for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.6.0
 * 
 * @version                 4.6.0 (06-08-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends	                classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Qty {
	/**
	 * Summary of get_qty
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_qty( $tag_name = 'qty', $result_xml = '' ) {
		$tag_value = '';

		$yfym_qty = common_option_get( 'yfym_qty', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_qty === 'enabled' ) {
			if ( true == $this->get_offer()->get_manage_stock() ) { // включено управление запасом на уровне вариации
				$stock_quantity = $this->get_offer()->get_stock_quantity();
				if ( $stock_quantity > -1 ) {
					$tag_value = $stock_quantity;
				}
			} else {
				if ( true == $this->get_product()->get_manage_stock() ) { // включено управление запасом
					$stock_quantity = $this->get_product()->get_stock_quantity();
					if ( $stock_quantity > -1 ) {
						$tag_value = $stock_quantity;
					}
				}
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_qty',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( $tag_value !== '' ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_qty',
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
			'y4ym_f_variable_tag_qty',
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