<?php
/**
 * Traits Params for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.7.3 (01-10-2024)
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

trait YFYM_T_Simple_Get_Params {
	/**
	 * Get params tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_params( $tag_name = 'params', $result_xml = '' ) {
		$params_arr = maybe_unserialize( yfym_optionGET( 'yfym_params_arr', $this->get_feed_id() ) );
		if ( is_array( $params_arr ) && ! empty( $params_arr ) ) {
			$behavior_of_params = common_option_get( 'yfym_behavior_of_params', false, $this->get_feed_id(), 'yfym' );

			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {
				// проверка на вариативность атрибута не нужна
				$param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				// если этот параметр не нужно выгружать - пропускаем
				$variation_id_string = (string) $param->get_id(); // важно, т.к. в настройках id как строки
				if ( ! in_array( $variation_id_string, $params_arr, true ) ) {
					continue;
				}
				$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				// если пустое имя атрибута или значение - пропускаем
				if ( empty( $param_name ) || empty( $param_val ) ) {
					continue;
				}

				if ( $behavior_of_params == 'split' ) {
					$val = ucfirst( yfym_replace_decode( $param_val ) );
					$val_arr = explode( ", ", $val );
					foreach ( $val_arr as $value ) {
						$result_xml .= new Get_Paired_Tag( 'param', $value, [ 'name' => htmlspecialchars( $param_name ) ] );
					}
				} else {
					$result_xml .= new Get_Paired_Tag(
						'param',
						ucfirst( yfym_replace_decode( $param_val ) ),
						[ 'name' => htmlspecialchars( $param_name ) ]
					);
				}
			}
		}

		$yfym_ebay_stock = yfym_optionGET( 'yfym_ebay_stock', $this->get_feed_id(), 'set_arr' );
		if ( $yfym_ebay_stock === 'on' ) {
			if ( true == $this->get_product()->get_manage_stock() ) { // включено управление запасом
				$stock_quantity = $this->get_product()->get_stock_quantity();
				$result_xml .= new Get_Paired_Tag( 'param', $stock_quantity, [ 'name' => 'stock' ] );
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_params',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}