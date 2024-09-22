<?php
/**
 * Traits Downloadable for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.1.1
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
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Downloadable {
	/**
	 * Get product downloadable
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_downloadable( $tag_name = 'downloadable', $result_xml = '' ) {
		$tag_value = '';

		$downloadable = common_option_get( 'yfym_downloadable', false, $this->get_feed_id(), 'yfym' );
		if ( ! empty( $downloadable ) && $downloadable !== 'off' ) {
			if ( $this->get_product()->is_downloadable( 'yes' ) ) {
				$tag_value = 'true';
			} else {
				$tag_value = 'false';
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_downloadable',
			$tag_value,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_downloadable',
				$tag_name,
				[ 'product' => $this->get_product() ],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_downloadable',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}