<?php
/**
 * Traits Supplier for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.3.4 (22-05-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Supplier {
	/**
	 * Summary of get_supplier
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_supplier( $tag_name = 'supplier', $result_xml = '' ) {
		$tag_value = '';

		if ( ( get_post_meta( $this->get_product()->get_id(), '_yfym_supplier', true ) !== '' ) ) {
			$tag_value = get_post_meta( $this->get_product()->get_id(), '_yfym_supplier', true );
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_supplier',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		if ( ! empty( $tag_value ) ) {

			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_supplier',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Open_Tag( $tag_name, [ 'ogrn' => $tag_value ], true );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_supplier',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}