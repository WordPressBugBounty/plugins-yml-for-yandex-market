<?php
/**
 * Traits Custom_Labels for simple products
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

trait YFYM_T_Simple_Get_Custom_Labels {
	/**
	 * Get `custom_label_0`-`custom_label_4` tags
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_custom_labels( $tag_name = 'custom_label', $result_xml = '' ) {
		$custom_labels = common_option_get( 'yfym_custom_labels', false, $this->get_feed_id(), 'yfym' );
		if ( $custom_labels === 'enabled' ) {
			for ( $i = 0; $i < 5; $i++ ) {
				$post_meta_name = '_yfym_custom_label_' . (string) $i;
				
				$tag_value = get_post_meta( $this->get_product()->get_id(), $post_meta_name, true );
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_custom_label',
					$tag_value,
					[ 
						'product' => $this->get_product(),
						'i' => $i
					],
					$this->get_feed_id()
				);
				if ( ! empty( $tag_value ) ) {
					$tag_name = sprintf( '%s_%s', 'yfym_custom_label_', (string) $i );
					$tag_name = apply_filters(
						'y4ym_f_simple_tag_name_custom_label',
						$tag_name,
						[ 
							'product' => $this->get_product(),
							'i' => $i
						],
						$this->get_feed_id()
					);
					$result_xml .= new Get_Paired_Tag( $tag_name, $tag_value );
				}
				unset( $tag_value ); // ? возможно, это не имеет смысла
			}

			$result_xml = apply_filters(
				'y4ym_f_simple_tag_custom_labels',
				$result_xml,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;
	}
}