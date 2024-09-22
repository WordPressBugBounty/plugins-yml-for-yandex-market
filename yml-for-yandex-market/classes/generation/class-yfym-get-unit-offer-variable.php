<?php
/**
 * Get unit for Simple Products 
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.7.1 (11-09-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 * 
 * @param       string      $result_xml
 *
 * @depends                 classes:    YFYM_Get_Unit_Offer
 *                                      Get_Closed_Tag
 *                          traits:     
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

class YFYM_Get_Unit_Offer_Variable extends YFYM_Get_Unit_Offer {
	use YFYM_T_Common_Get_CatId;
	use YFYM_T_Common_Skips;

	use YFYM_T_Variable_Get_Additional_Expenses;
	use YFYM_T_Variable_Get_Adult;
	use YFYM_T_Variable_Get_Age;
	use YFYM_T_Variable_Get_Amount;
	use YFYM_T_Variable_Get_Archived;
	use YFYM_T_Variable_Get_Barcode;
	use YFYM_T_Variable_Get_Cargo_Types;
	use YFYM_T_Variable_Get_CategoryId;
	use YFYM_T_Variable_Get_Cofinance_Price;
	use YFYM_T_Variable_Get_CollectionId;
	use YFYM_T_Variable_Get_Condition;
	use YFYM_T_Variable_Get_Consists;
	use YFYM_T_Variable_Get_Count;
	use YFYM_T_Variable_Get_Country_Of_Orgin;
	use YFYM_T_Variable_Get_Credit_Template;
	use YFYM_T_Variable_Get_Currencyid;
	use YFYM_T_Variable_Get_Custom_Labels;
	use YFYM_T_Variable_Get_Custom_Score;
	use YFYM_T_Variable_Get_Delivery_Options;
	use YFYM_T_Variable_Get_Delivery;
	use YFYM_T_Variable_Get_Description;
	use YFYM_T_Variable_Get_Dimensions;
	use YFYM_T_Variable_Get_Disabled;
	use YFYM_T_Variable_Get_Downloadable;
	use YFYM_T_Variable_Get_Enable_Auto_Discounts;
	use YFYM_T_Variable_Get_Expiry;
	use YFYM_T_Variable_Get_Group_Id;
	use YFYM_T_Variable_Get_Id;
	use YFYM_T_Variable_Get_Instock;
	use YFYM_T_Variable_Get_Keywords;
	use YFYM_T_Variable_Get_Manufacturer_Warranty;
	use YFYM_T_Variable_Get_Manufacturer;
	use YFYM_T_Variable_Get_Market_Category;
	use YFYM_T_Variable_Get_Market_Category_Id;
	use YFYM_T_Variable_Get_Market_Sku;
	use YFYM_T_Variable_Get_Min_Price;
	use YFYM_T_Variable_Get_Min_Quantity;
	use YFYM_T_Variable_Get_Model;
	use YFYM_T_Variable_Get_Name;
	use YFYM_T_Variable_Get_Offer_Tag;
	use YFYM_T_Variable_Get_Outlets;
	use YFYM_T_Variable_Get_Params;
	use YFYM_T_Variable_Get_Period_Of_Validity_Days;
	use YFYM_T_Variable_Get_Pickup_Options;
	use YFYM_T_Variable_Get_Pickup;
	use YFYM_T_Variable_Get_Picture;
	use YFYM_T_Variable_Get_Premium_Price;
	use YFYM_T_Variable_Get_Price;
	use YFYM_T_Variable_Get_Price_Rrp;
	use YFYM_T_Variable_Get_Purchase_Price;
	use YFYM_T_Variable_Get_Qty;
	use YFYM_T_Variable_Get_Recommend_Stock_Data;
	use YFYM_T_Variable_Get_Sales_Notes;
	use YFYM_T_Variable_Get_Shipment_Options;
	use YFYM_T_Variable_Get_Shop_Sku;
	use YFYM_T_Variable_Get_Step_Quantity;
	use YFYM_T_Variable_Get_Store;
	use YFYM_T_Variable_Get_Supplier;
	use YFYM_T_Variable_Get_Tn_Ved_Codes;
	use YFYM_T_Variable_Get_Type_Prefix;
	use YFYM_T_Variable_Get_Url;
	use YFYM_T_Variable_Get_Vat;
	use YFYM_T_Variable_Get_Vendor;
	use YFYM_T_Variable_Get_Vendorcode;
	use YFYM_T_Variable_Get_Video;
	use YFYM_T_Variable_Warranty_Days;
	use YFYM_T_Variable_Get_Weight;

	/**
	 * Generating the `offer` tag with all the contents
	 * 
	 * @param string $result_xml
	 * 
	 * @return string `<offer...>...tags...</offer>` or empty string
	 */
	public function generation_product_xml( $result_xml = '' ) {
		$this->set_category_id();
		$this->get_skips();

		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		switch ( $yfym_yml_rules ) {
			case "yandex_market": // Яндекс Маркет (Для управления товарами, Упрощённый тип FBS/DBS)
				$result_xml = $this->yandex_market_assortment();
				break;
			case "sales_terms": // Яндекс Маркет (Для управления размещения, FBS/DBS)
				$result_xml = $this->sales_terms();
				break;
			case "yandex_direct": // Яндекс Директ (Упрощённый тип)
				$result_xml = $this->yandex_direct();
				break;
			case "yandex_direct_free_from": // Яндекс Директ (Произвольный тип)
				$result_xml = $this->yandex_direct_free_from();
				break;
			case "yandex_direct_combined": // Яндекс Директ (Комбинированный тип)
				$result_xml = $this->yandex_direct_combined();
				break;
			case "yandex_webmaster": // Яндекс Вебмастер (Товарный фид, Товары и предложения)
				$result_xml = $this->yandex_webmaster();
				break;
			case "vk": // ВКонтакте (vk.com) 
				$result_xml = $this->vk();
				break;
			case "sbermegamarket": // МегаМаркет
				$result_xml = $this->sbermegamarket();
				break;
			case "ozon": // OZON (только обновление цен и остатков на складе)
				$result_xml = $this->ozon();
				break;
			case "all_elements": // Нет правил (Для опытных пользователей)
				$result_xml = $this->all_elements();
				break;
			case "single_catalog":
				$result_xml = $this->single_catalog();
				break;
			case "flowwow":
				$result_xml = $this->flowwow();
				break;
			default: // Нет правил (Для опытных пользователей)
				$result_xml = $this->get_tags( $yfym_yml_rules, $result_xml );
		}

		$result_xml = apply_filters(
			'y4ym_f_append_variable_offer',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $result_xml ) ) {
			$result_xml .= new Get_Closed_Tag( 'offer' );
		}
		return $result_xml;
	}

	/**
	 * Яндекс Маркет (Для управления товарами, Упрощённый тип FBS/DBS)
	 * 
	 * @see https://yandex.ru/support2/marketplace/ru/assortment/auto/yml
	 * @see https://yandex.ru/support2/marketplace/ru/assortment/fields/
	 * @see https://yastatic.net/s3/doc-binary/src/support/market/ru/YML_sample_catalog.zip
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function yandex_market_assortment( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'yandex_market_assortment', $result_xml );
		return $result_xml;
	}

	/**
	 * Яндекс Директ (Упрощённый тип)
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements.html#requirements__market-feed
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function yandex_direct( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'yandex_direct', $result_xml );
		return $result_xml;
	}

	/**
	 * Яндекс Директ (Произвольный тип)
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements.html?lang=ru
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function yandex_direct_free_from( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'yandex_direct_free_from', $result_xml );
		return $result_xml;
	}

	/**
	 * Summary of direct
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements.html?lang=ru
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function yandex_direct_combined( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'yandex_direct_combined', $result_xml );
		return $result_xml;
	}

	/**
	 * Summary of single_catalog
	 * 
	 * @see 
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function single_catalog( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'single_catalog', $result_xml );
		return $result_xml;
	}

	/**
	 * DBS rules
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/files/index.html
	 *      https://yandex.ru/support/marketplace/tools/elements/offer-general.html
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function dbs( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'dbs', $result_xml );
		return $result_xml;
	}

	/**
	 * Яндекс Маркет (Для управления размещения, FBS/DBS)
	 * 
	 * @see 
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function sales_terms( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'sales_terms', $result_xml );
		return $result_xml;
	}

	/**
	 * МегаМаркет
	 * 
	 * @see https://s3.megamarket.tech/mms/documents/assortment/Инструкция%20к%20фиду%20xml.pdf
	 * @see https://partner-wiki.megamarket.ru/merchant-api/1-vvedenie/1-1-tovarnyj-fid
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function sbermegamarket( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'sbermegamarket', $result_xml );
		$result_xml .= $this->get_group_id();
		$result_xml .= $this->get_delivery_options( 'shipment-options', '', 'sbermegamarket' );
		$result_xml .= $this->get_outlets( 'outlets', '', 'sbermegamarket' );
		return $result_xml;
	}

	/**
	 * ВКонтакте (vk.com)
	 * 
	 * @see 
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function vk( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'vk', $result_xml );
		return $result_xml;
	}

	/**
	 * Flowwow.com
	 * 
	 * @see https://flowwow.com/blog/kak-zagruzit-tovary-na-flowwow-s-pomoshchyu-xml-ili-yml-faylov/
	 *      https://docs.google.com/document/d/1sF7CN8yPIleQ6T-AFSfV8Kyn3sTbXcJM/edit
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function flowwow( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'flowwow', $result_xml );
		return $result_xml;
	}

	/**
	 * Нет правил (Для опытных пользователей)
	 * 
	 * @see 
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function all_elements( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'all_elements', $result_xml );
		$result_xml .= $this->get_group_id(); // ! различие с простыми товарами
		return $result_xml;
	}

	/**
	 * OZON (только обновление цен и остатков на складе)
	 * 
	 * @see 
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function ozon( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'ozon', $result_xml );
		return $result_xml;
	}

	/**
	 * Яндекс Вебмастер (Товарный фид, Товары и предложения)
	 * 
	 * @see https://yandex.ru/support/products/features.html - Поиск по товарам
	 * 
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	private function yandex_webmaster( $result_xml = '' ) {
		$result_xml .= $this->get_tags( 'yandex_webmaster', $result_xml );
		return $result_xml;
	}
}