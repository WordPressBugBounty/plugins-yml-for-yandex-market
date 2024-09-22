<?php
/**
 * Traits Consists for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.6.1
 * 
 * @version                 4.6.1 (13-08-2024)
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
 *                                      yfym_optionGET
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Consists {
	/**
	 * Get consists tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_consists( $tag_name = 'consist', $result_xml = '' ) {
		$consists_arr = maybe_unserialize( yfym_optionGET( 'yfym_consists_arr', $this->get_feed_id() ) );

		// Consist в вариациях
		if ( ! empty( $consists_arr ) ) {
			$behavior_of_consists = common_option_get( 'yfym_behavior_of_consists', false, $this->get_feed_id(), 'yfym' );

			$attributes = $this->get_product()->get_attributes(); // получили все атрибуты товара		 
			foreach ( $attributes as $consist ) {
				if ( false == $consist->get_variation() ) {
					// это обычный атрибут
					$consist_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $consist->get_id() ) );
				} else {
					$consist_val = $this->get_offer()->get_attribute( wc_attribute_taxonomy_name_by_id( $consist->get_id() ) );
				}
				// если этот параметр не нужно выгружать - пропускаем
				$variation_id_string = (string) $consist->get_id(); // важно, т.к. в настройках id как строки
				if ( ! in_array( $variation_id_string, $consists_arr, true ) ) {
					continue;
				}
				$consist_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $consist->get_id() ) );
				// если пустое имя атрибута или значение - пропускаем
				if ( empty( $consist_name ) || empty( $consist_val ) ) {
					continue;
				}

				if ( $behavior_of_consists == 'split' ) {
					$val = ucfirst( yfym_replace_decode( $consist_val ) );
					$val_arr = explode( ", ", $val );
					foreach ( $val_arr as $value ) {
						$result_xml .= new Get_Paired_Tag( $tag_name, $value, [ 'name' => htmlspecialchars( $consist_name ), 'unit' => 'шт' ] );
					}
				} else {
					$result_xml .= new Get_Paired_Tag(
						$tag_name,
						ucfirst( yfym_replace_decode( $consist_val ) ),
						[ 'name' => htmlspecialchars( $consist_name ), 'unit' => 'шт' ]
					);
				}
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_consists',
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