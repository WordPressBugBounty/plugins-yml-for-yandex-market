<?php
/**
 * Traits Consists for simple products
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
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                                      yfym_optionGET
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Consists {
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
		if ( ! empty( $consists_arr ) ) {
			$behavior_of_consists = common_option_get( 'yfym_behavior_of_consists', false, $this->get_feed_id(), 'yfym' );

			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {
				// проверка на вариативность атрибута не нужна
				$param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				// если этот параметр не нужно выгружать - пропускаем
				$variation_id_string = (string) $param->get_id(); // важно, т.к. в настройках id как строки
				if ( ! in_array( $variation_id_string, $consists_arr, true ) ) {
					continue;
				}
				$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				// если пустое имя атрибута или значение - пропускаем
				if ( empty( $param_name ) || empty( $param_val ) ) {
					continue;
				}

				if ( $behavior_of_consists == 'split' ) {
					$val = ucfirst( yfym_replace_decode( $param_val ) );
					$val_arr = explode( ", ", $val );
					foreach ( $val_arr as $value ) {
						$result_xml .= new Get_Paired_Tag( $tag_name, $value, [ 'name' => htmlspecialchars( $param_name ), 'unit' => 'шт' ] );
					}
				} else {
					$result_xml .= new Get_Paired_Tag(
						$tag_name,
						ucfirst( yfym_replace_decode( $param_val ) ),
						[ 'name' => htmlspecialchars( $param_name ), 'unit' => 'шт' ]
					);
				}
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_consists',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}