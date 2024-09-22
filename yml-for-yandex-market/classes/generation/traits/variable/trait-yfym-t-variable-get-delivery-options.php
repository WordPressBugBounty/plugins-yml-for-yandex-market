<?php
/**
 * Traits Delivery_Options for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.1.5 (07-12-2023)
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

trait YFYM_T_Variable_Get_Delivery_Options {
	/**
	 * Summary of get_delivery_options
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_delivery_options( $tag_name = 'delivery-options', $result_xml = '', $rules = '' ) {
		$tag_value = '';

		if ( ( get_post_meta( $this->get_product()->get_id(), 'yfym_cost', true ) !== '' )
			&& ( get_post_meta( $this->get_product()->get_id(), 'yfym_days', true ) !== '' ) ) {
			$yfym_cost = get_post_meta( $this->get_product()->get_id(), 'yfym_cost', true );
			$yfym_days = get_post_meta( $this->get_product()->get_id(), 'yfym_days', true );
			if ( get_post_meta( $this->get_product()->get_id(), 'yfym_order_before', true ) !== '' ) {
				$yfym_order_before = get_post_meta( $this->get_product()->get_id(), 'yfym_order_before', true );
				$yfym_order_before_yml = ' order-before="' . $yfym_order_before . '"';
			} else {
				$yfym_order_before_yml = '';
			}

			if ( $rules === 'sbermegamarket' ) {
				$result_xml = new Get_Open_Tag( 'shipment-options' );
				$result_xml .= '<option days="' . $yfym_days . '"' . $yfym_order_before_yml . '/>' . PHP_EOL;
				$result_xml .= new Get_Closed_Tag( 'shipment-options' );
			} else {
				$result_xml = new Get_Open_Tag( $tag_name );
				$result_xml .= '<option cost="' . $yfym_cost . '" days="' . $yfym_days . '"' . $yfym_order_before_yml . '/>' . PHP_EOL;
				$result_xml .= new Get_Closed_Tag( $tag_name );
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_delivery_options',
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