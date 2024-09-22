<?php
/**
 * Traits Shop_Sku for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.7.2 (16-09-2023)
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

trait YFYM_T_Simple_Get_Shop_Sku {
	/**
	 * Get `shop-sku` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_shop_sku( $tag_name = 'shop-sku', $result_xml = '' ) {
		$tag_value = '';

		$yfym_shop_sku = common_option_get( 'yfym_shop_sku', false, $this->get_feed_id(), 'yfym' );
		switch ( $yfym_shop_sku ) {
			case "disabled": // выгружать штрихкод нет нужды		
				break;
			case "sku": // выгружать из артикула
				$tag_value = $this->get_product()->get_sku();
				break;
			case "products_id": // выгружать из id вариации
				$tag_value = $this->get_product()->get_id();
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_simple_tag_value_switch_shop_sku',
					$tag_value,
					[ 
						'product' => $this->get_product(),
						'switch_value' => $yfym_shop_sku
					],
					$this->get_feed_id()
				);
				if ( $tag_value == '' ) {
					$yfym_shop_sku = (int) $yfym_shop_sku;
					$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $yfym_shop_sku ) );
				}
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_shop_sku',
			$tag_value,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id() );
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(

				'y4ym_f_simple_tag_name_shop_sku',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
			$result_xml = new Get_Paired_Tag( $tag_name, htmlspecialchars( $tag_value ) );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_shop_sku',
			$result_xml,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}