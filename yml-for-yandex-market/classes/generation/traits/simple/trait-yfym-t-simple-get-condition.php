<?php
/**
 * Traits Condition for simple products
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

trait YFYM_T_Simple_Get_Condition {
	/**
	 * Get `condition` tags
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_condition( $tag_name = 'condition', $result_xml = '' ) {
		$yfym_condition = get_post_meta( $this->get_product()->get_id(), '_yfym_condition', true );
		if ( empty( $yfym_condition ) || $yfym_condition === 'default' ) {
			$yfym_condition = yfym_optionGET( 'yfym_condition', $this->get_feed_id(), 'set_arr' );
		}
		$yfym_reason = get_post_meta( $this->get_product()->get_id(), 'yfym_reason', true );
		if ( empty( $yfym_reason ) ) {
			$yfym_reason = yfym_optionGET( 'yfym_reason', $this->get_feed_id(), 'set_arr' );
		}
		$yfym_quality = get_post_meta( $this->get_product()->get_id(), '_yfym_quality', true );
		if ( empty( $yfym_quality ) || $yfym_quality === 'default' ) {
			$yfym_quality = yfym_optionGET( 'yfym_quality', $this->get_feed_id(), 'set_arr' );
		}

		if ( empty( $yfym_condition ) || empty( $yfym_reason ) || $yfym_condition === 'disabled' ) {

		} else {
			$result_xml = new Get_Open_Tag( $tag_name, [ 'type' => $yfym_condition ] );
			$result_xml .= new Get_Paired_Tag( 'reason', $yfym_reason );
			$result_xml .= new Get_Paired_Tag( 'quality', $yfym_quality );
			$result_xml .= new Get_Closed_Tag( $tag_name );
		}

		/*
		if ((get_post_meta($this->get_product()->get_id(), 'yfym_condition', true) !== '') 
			&& (get_post_meta($this->get_product()->get_id(), 'yfym_condition', true) !== 'off') 
			&& (get_post_meta($this->get_product()->get_id(), 'yfym_reason', true) !== '')) {
			$yfym_condition = get_post_meta($this->get_product()->get_id(), 'yfym_condition', true);
			$yfym_reason = get_post_meta($this->get_product()->get_id(), 'yfym_reason', true);	
			$result_xml = new Get_Open_Tag($tag_name, array('type' => $yfym_condition));
			$result_xml .= new Get_Paired_Tag('reason', $yfym_reason);
			$result_xml .= new Get_Closed_Tag($tag_name);;	 
		}
		*/

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_condition',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}