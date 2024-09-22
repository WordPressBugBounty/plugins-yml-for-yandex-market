<?php
/**
 * Traits Cargo_Types for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.2.5 (05-03-2024)
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

trait YFYM_T_Simple_Get_Cargo_Types {
	/**
	 * Summary of cargo_types
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_cargo_types( $tag_name = 'cargo-types', $result_xml = '' ) {
		$tag_value = '';

		$cargo_types = common_option_get( 'yfym_cargo_types', false, $this->get_feed_id(), 'yfym' );
		if ( $cargo_types === 'enabled' ) {
			if ( get_post_meta( $this->get_product()->get_id(), '_yfym_cargo_types', true ) !== '' ) {
				$yfym_cargo_types = get_post_meta( $this->get_product()->get_id(), '_yfym_cargo_types', true );
				if ( $yfym_cargo_types === 'yes' ) {
					$tag_value = 'CIS_REQUIRED';
				}
			}
		}

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_cargo_types',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_cargo_types',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}