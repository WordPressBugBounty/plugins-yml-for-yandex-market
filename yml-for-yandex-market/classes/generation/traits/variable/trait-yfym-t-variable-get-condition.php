<?php
/**
 * Traits Condition for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.4.1 (11-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Open_Tag
 *                                      Get_Paired_Tag
 *                                      Get_Closed_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Condition {
	/**
	 * Summary of get_condition
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_condition( $tag_name = 'condition', $result_xml = '' ) {
		$yfym_condition = get_post_meta( $this->get_product()->get_id(), '_yfym_condition', true );
		if ( empty( $yfym_condition ) || $yfym_condition === 'default' ) {
			$yfym_condition = common_option_get( 'yfym_condition', false, $this->get_feed_id(), 'yfym' );
		}
		$yfym_reason = get_post_meta( $this->get_product()->get_id(), 'yfym_reason', true );
		if ( empty( $yfym_reason ) ) {
			$yfym_reason = common_option_get( 'yfym_reason', false, $this->get_feed_id(), 'yfym' );
		}
		$yfym_quality = get_post_meta( $this->get_product()->get_id(), '_yfym_quality', true );
		if ( empty( $yfym_quality ) || $yfym_quality === 'default' ) {
			$yfym_quality = common_option_get( 'yfym_quality', false, $this->get_feed_id(), 'yfym' );
		}

		if ( empty( $yfym_condition ) || empty( $yfym_reason ) || $yfym_condition === 'disabled' ) {

		} else {
			$result_xml = new Get_Open_Tag( $tag_name, [ 'type' => $yfym_condition ] );
			$result_xml .= new Get_Paired_Tag( 'reason', $yfym_reason );
			$result_xml .= new Get_Paired_Tag( 'quality', $yfym_quality );
			$result_xml .= new Get_Closed_Tag( $tag_name );
		}

		// if ((get_post_meta($this->get_product()->get_id(), _, true) !== '') 
		// && (get_post_meta($this->get_product()->get_id(), _, true) !== 'off') 
		// && (get_post_meta($this->get_product()->get_id(), 'yfym_reason', true) !== '')) {
		// 	$yfym_condition = get_post_meta($this->get_product()->get_id(), _, true);
		// 	$yfym_reason = get_post_meta($this->get_product()->get_id(), 'yfym_reason', true);	
		// 	$result_xml = new Get_Open_Tag($tag_name, [ 'type' => $yfym_condition ] );
		// 	$result_xml .= new Get_Paired_Tag('reason', $yfym_reason);
		// 	$result_xml .= new Get_Closed_Tag($tag_name);;	 
		// }

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_condition',
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