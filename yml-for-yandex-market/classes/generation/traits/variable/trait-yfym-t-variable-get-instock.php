<?php
/**
 * Traits Instock Count for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.0.10 (01-11-2023)
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

trait YFYM_T_Variable_Get_Instock {
	/**
	 * Summary of get_instock
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_instock( $tag_name = 'instock', $result_xml = '' ) {
		$tag_value = '';

		$yfym_instock = common_option_get( 'yfym_instock', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_instock === 'enabled' ) {
			if ( $this->get_offer()->get_manage_stock() == true ) { // включено управление запасом на уровне вариации
				$stock_quantity = $this->get_offer()->get_stock_quantity();
				if ( $stock_quantity > -1 ) {
					$tag_value = $stock_quantity;
				} else {
					$tag_value = (int) 0;
				}
			} else {
				if ( $this->get_product()->get_manage_stock() == true ) { // включено управление запасом
					$stock_quantity = $this->get_product()->get_stock_quantity();
					if ( $stock_quantity > -1 ) {
						$tag_value = $stock_quantity;
					} else {
						$tag_value = (int) 0;
					}
				}
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_instock',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_instock',
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
			'y4ym_f_variable_tag_instock',
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