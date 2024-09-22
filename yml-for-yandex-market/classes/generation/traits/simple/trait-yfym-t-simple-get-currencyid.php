<?php 
/**
 * Traits Currencyid for simple products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.1.0 (22-11-2023)
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

trait YFYM_T_Simple_Get_Currencyid {
	/**
	 * Summary of get_currencyid
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_currencyid( $tag_name = 'currencyId', $result_xml = '' ) {
		$tag_value = '';

		/* общие данные для вариативных и обычных товаров */
		$res = get_woocommerce_currency(); // получаем валюта магазина
		switch ( $res ) { /* RUR, USD, UAH, KZT, BYN */
			case "RUB":
				$currency_id_yml = "RUR";
				break;
			case "USD":
				$currency_id_yml = "USD";
				break;
			case "EUR":
				$currency_id_yml = "EUR";
				break;
			case "UAH":
				$currency_id_yml = "UAH";
				break;
			case "KZT":
				$currency_id_yml = "KZT";
				break;
			case "UZS":
				$currency_id_yml = "UZS";
				break;
			case "BYN":
				$currency_id_yml = "BYN";
				break;
			case "BYR":
				$currency_id_yml = "BYN";
				break;
			case "ABC":
				$currency_id_yml = "BYN";
				break;
			default:
				$currency_id_yml = "RUR";
		}
		$currency_id_yml = apply_filters( 'yfym_currency_id', $currency_id_yml, $this->get_feed_id() );

		$tag_value = $currency_id_yml;

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_currencyid',
			$tag_value,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_currencyid',
				$tag_name,
				[ 'product' => $this->get_product() ],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_currencyid',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}