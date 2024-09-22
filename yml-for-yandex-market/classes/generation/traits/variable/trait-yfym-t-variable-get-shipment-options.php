<?php
/**
 * Traits Shipment_Options for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.7.1
 * 
 * @version                 4.7.1 (11-09-2024)
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

trait YFYM_T_Variable_Get_Shipment_Options {
	/**
	 * Get `shipment-options` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_shipment_options( $tag_name = 'shipment-options', $result_xml = '' ) {
		if ( ( get_post_meta( $this->get_product()->get_id(), 'yfym_days', true ) !== '' ) ) {
			$yfym_days = get_post_meta( $this->get_product()->get_id(), 'yfym_days', true );
			if ( get_post_meta( $this->get_product()->get_id(), 'yfym_order_before', true ) !== '' ) {
				$yfym_order_before = get_post_meta( $this->get_product()->get_id(), 'yfym_order_before', true );
				$yfym_order_before_yml = ' order-before="' . $yfym_order_before . '"';
			} else {
				$yfym_order_before_yml = '';
			}

			$result_xml = new Get_Open_Tag( $tag_name );
			$result_xml .= '<option days="' . $yfym_days . '"' . $yfym_order_before_yml . '/>' . PHP_EOL;
			$result_xml .= new Get_Closed_Tag( $tag_name );
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_shipment_options',
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