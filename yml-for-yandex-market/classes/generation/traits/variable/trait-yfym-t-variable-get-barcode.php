<?php
/**
 * Traits Barcode for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.4.2 (27-06-2024)
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

trait YFYM_T_Variable_Get_Barcode {
	/**
	 * Get the product barcode
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_barcode( $tag_name = 'barcode', $result_xml = '' ) {
		$tag_value = '';

		$yfym_barcode = common_option_get( 'yfym_barcode', false, $this->get_feed_id(), 'yfym' );
		switch ( $yfym_barcode ) {
			// disabled, sku, post_meta, germanized, upc-ean-generator, ean-for-woocommerce, id
			case "disabled": // выгружать штрихкод нет нужды		
				break;
			case "sku": // выгружать из артикула
				$tag_value = $this->get_offer()->get_sku();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_sku();
				}
				break;
			case "post_meta":
				$barcode_post_meta_id = common_option_get( 'yfym_barcode_post_meta', false, $this->get_feed_id(), 'yfym' );
				$barcode_post_meta_id = trim( $barcode_post_meta_id );
				if ( get_post_meta( $this->get_offer()->get_id(), $barcode_post_meta_id, true ) == '' ) {
					if ( get_post_meta( $this->get_product()->get_id(), $barcode_post_meta_id, true ) !== '' ) {
						$tag_value = get_post_meta( $this->get_product()->get_id(), $barcode_post_meta_id, true );
					} else {
						$tag_value = '';
					}
				} else {
					$tag_value = get_post_meta( $this->get_offer()->get_id(), $barcode_post_meta_id, true );
				}
				break;
			case "germanized":
				if ( class_exists( 'WooCommerce_Germanized' ) ) {
					$var_id = $this->get_offer()->get_id();
					if ( get_post_meta( $var_id, '_ts_gtin', true ) !== '' ) {
						$tag_value = get_post_meta( $var_id, '_ts_gtin', true );
					} else {
						if ( get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true ) !== '' ) {
							$tag_value = get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true );
						}
					}
				}
				break;
			case "upc-ean-generator":
				$var_id = $this->get_offer()->get_id();
				if ( get_post_meta( $var_id, 'usbs_barcode_field', true ) !== '' ) {
					$tag_value = get_post_meta( $var_id, 'usbs_barcode_field', true );
				} else {
					if ( get_post_meta( $this->get_product()->get_id(), 'usbs_barcode_field', true ) !== '' ) {
						$tag_value = get_post_meta( $this->get_product()->get_id(), 'usbs_barcode_field', true );
					}
				}
				break;
			case "ean-for-woocommerce":
				if ( class_exists( 'Alg_WC_EAN' ) ) {
					$var_id = $this->get_offer()->get_id();
					if ( get_post_meta( $var_id, '_alg_ean', true ) !== '' ) {
						$tag_value = get_post_meta( $var_id, '_alg_ean', true );
					} else {
						if ( get_post_meta( $this->get_product()->get_id(), '_alg_ean', true ) !== '' ) {
							$tag_value = get_post_meta( $this->get_product()->get_id(), '_alg_ean', true );
						}
					}
				}
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_variable_tag_value_switch_barcode',
					$tag_value,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'switch_value' => $yfym_barcode
					],
					$this->get_feed_id()
				);
				if ( $tag_value == '' ) {
					$yfym_barcode = (int) $yfym_barcode;
					$tag_value = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $yfym_barcode ) );
					if ( empty( $tag_value ) ) {
						$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $yfym_barcode ) );
					}
				}
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_barcode',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_barcode',
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
			'y4ym_f_variable_tag_barcode',
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