<?php
/**
 * Traits Shop_Sku for variable products
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
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Shop_Sku {
	/**
	 * Get `shop-sku` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_shop_sku( $tag_name = 'shop-sku', $result_xml = '' ) {
		$product = $this->get_product();
		$offer = $this->get_offer();
		$tag_value = '';

		$yfym_shop_sku = yfym_optionGET( 'yfym_shop_sku', $this->get_feed_id(), 'set_arr' );
		switch ( $yfym_shop_sku ) {
			case "disabled": // выгружать штрихкод нет нужды		
				break;
			case "sku": // выгружать из артикула
				$tag_value = $offer->get_sku();
				if ( empty( $tag_value ) ) {
					$tag_value = $product->get_sku();
				}
				break;
			case "products_id": // выгружать из id вариации
				$tag_value = $offer->get_id();
				break;
			default:
				$tag_value = apply_filters( 'y4ym_f_variable_tag_value_switch_shop_sku', $tag_value, array( 'product' => $product, 'offer' => $offer, 'switch_value' => $yfym_shop_sku ), $this->get_feed_id() );
				if ( $tag_value == '' ) {
					$yfym_shop_sku = (int) $yfym_shop_sku;
					$tag_value = $offer->get_attribute( wc_attribute_taxonomy_name_by_id( $yfym_shop_sku ) );
					if ( empty( $tag_value ) ) {
						$tag_value = $product->get_attribute( wc_attribute_taxonomy_name_by_id( $yfym_shop_sku ) );
					}
				}
		}

		$tag_value = apply_filters( 'y4ym_f_variable_tag_value_shop_sku', $tag_value, array( 'product' => $product, 'offer' => $offer ), $this->get_feed_id() );
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters( 'y4ym_f_variable_tag_name_shop_sku', $tag_name, array( 'product' => $product, 'offer' => $offer ), $this->get_feed_id() );
			// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
			$result_xml = new Get_Paired_Tag( $tag_name, htmlspecialchars( $tag_value ) );
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_shop_sku',
			$result_xml,
			array(
				'product' => $product,
				'offer' => $offer ),
			$this->get_feed_id()
		);
		return $result_xml;
	}
}