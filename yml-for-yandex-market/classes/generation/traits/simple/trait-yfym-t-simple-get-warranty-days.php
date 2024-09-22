<?php
/**
 * Traits Warranty Days for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.3.3
 * 
 * @version                 4.4.1 (11-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support2/marketplace/ru/assortment/fields/#warranty-days
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Warranty_Days {
	/**
	 * Summary of get_warranty_days
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_warranty_days( $tag_name = 'warranty-days', $result_xml = '' ) {
		$tag_value = '';

		$yfym_warranty_days = common_option_get( 'yfym_warranty_days', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_warranty_days === 'enabled' ) {
			if ( get_post_meta( $this->get_product()->get_id(), '_yfym_warranty_days', true ) !== '' ) {
				$warranty_days_value = get_post_meta( $this->get_product()->get_id(), '_yfym_warranty_days', true );
			} else {
				$warranty_days_value = common_option_get( 'yfym_warranty_days_default_value', false, $this->get_feed_id(), 'yfym' );
			}
		} else {
			$warranty_days_value = 0;
		}

		$warranty_days_value = (int) $warranty_days_value;
		if ( $warranty_days_value > 0 ) {
			$y = floor( $warranty_days_value / 365 );
			$m = floor( ( $warranty_days_value - 365 * $y ) / 30 );
			$d = floor( $warranty_days_value - 365 * $y - 30 * $m );

			$tag_value = 'P';
			if ( $y > 0 ) {
				$tag_value = sprintf( '%s%dY', $tag_value, $y );
			}
			if ( $m > 0 ) {
				$tag_value = sprintf( '%s%dM', $tag_value, $m );
			}
			if ( $d > 0 ) {
				$tag_value = sprintf( '%s%dD', $tag_value, $d );
			}
		}

		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_warranty_days',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);

			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_warranty_days',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}