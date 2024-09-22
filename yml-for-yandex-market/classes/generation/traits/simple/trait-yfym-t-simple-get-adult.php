<?php
/**
 * Traits Adult for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.3.6
 * 
 * @version                 4.3.6 (29-05-2024)
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

trait YFYM_T_Simple_Get_Adult {
	/**
	 * Summary of get_adult
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_adult( $tag_name = 'adult', $result_xml = '' ) {
		$tag_value = '';

		$adult = common_option_get( 'yfym_adult', false, $this->get_feed_id(), 'yfym' );
		if ( $adult === 'yes' || $adult === 'enabled' ) {
			$tag_value = 'true';
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_adult',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		if ( ! empty( $tag_value ) ) {

			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_adult',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_adult',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		return $result_xml;
	}
}