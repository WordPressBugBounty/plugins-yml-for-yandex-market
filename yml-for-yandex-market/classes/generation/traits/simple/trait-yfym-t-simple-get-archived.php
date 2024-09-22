<?php
/**
 * Traits Archived for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.2.1
 * 
 * @version                 4.2.1 (24-01-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support2/marketplace/ru/assortment/fields/index#archived
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Archived {
	/**
	 * Summary of get_archived
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_archived( $tag_name = 'archived', $result_xml = '' ) {
		$tag_value = '';

		$archived = common_option_get( 'yfym_auto_archived', false, $this->get_feed_id(), 'yfym' );
		if ( $archived === 'enabled' || $archived === 'yes' ) {
			// если товар не доступен к покупке
			if ( false === $this->get_product()->is_in_stock() ) {
				$tag_value = 'true';
			} else {
				$tag_value = 'false';
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_archived',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_archived',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_archived',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}