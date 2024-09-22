<?php 
/**
 * Traits Manufacturer for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.1.6 (13-12-2023)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                                      add_skip_reason
 *                          functions:  common_option_get
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Manufacturer {
	/**
	 * Get product manufacturer
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_manufacturer( $tag_name = 'manufacturer', $result_xml = '' ) {
		$tag_value = '';

		$manufacturer = common_option_get( 'yfym_manufacturer', false, $this->get_feed_id(), 'yfym' );
		if ( $manufacturer == 'post_meta' ) {
			$manufacturer_post_meta_id = common_option_get( 'yfym_manufacturer_post_meta', false, $this->get_feed_id(), 'yfym' );
			if ( get_post_meta( $this->get_product()->get_id(), $manufacturer_post_meta_id, true ) !== '' ) {
				$manufacturer_yml = get_post_meta( $this->get_product()->get_id(), $manufacturer_post_meta_id, true );
				$tag_value = ucfirst( yfym_replace_decode( $manufacturer_yml ) );
			}
		} else if ( $manufacturer == 'default_value' ) {
			$manufacturer_yml = common_option_get( 'yfym_manufacturer_post_meta', false, $this->get_feed_id(), 'yfym' );
			if ( $manufacturer_yml !== '' ) {
				$tag_value = ucfirst( yfym_replace_decode( $manufacturer_yml ) );
			}
		} else {
			if ( $manufacturer !== 'disabled' ) {
				$manufacturer = (int) $manufacturer;
				$manufacturer_yml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $manufacturer ) );
				if ( ! empty( $manufacturer_yml ) ) {
					$tag_value = ucfirst( yfym_replace_decode( $manufacturer_yml ) );
				}
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_manufacturer',
			$tag_value,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_manufacturer',
				$tag_name,
				[ 'product' => $this->get_product() ],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_manufacturer',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}