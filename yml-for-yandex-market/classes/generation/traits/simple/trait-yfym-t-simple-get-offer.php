<?php
/**
 * Traits Offer for simple products
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
 * @depends                 classes:    Get_Open_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                                      get_feed_category_id
 *                          functions:  common_option_get
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Offer_Tag {
	/**
	 * Get open `offer` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_offer_tag( $tag_name = 'offer', $result_xml = '' ) {
		$offer_tag_attrs_arr = []; // массив с атрибутами тега offer

		// type="xx"
		$offer_type = '';
		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_yml_rules === 'yandex_direct_free_from' ) {
			$offer_type = 'vendor.model';
		}
		$yfym_on_demand = common_option_get( 'yfym_on_demand', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_on_demand === 'enabled' && $this->get_product()->get_stock_status() === 'onbackorder' ) {
			$offer_type = 'on.demand';
		}
		$offer_type = apply_filters(
			'y4ym_f_simple_offer_type',
			$offer_type,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $offer_type ) ) {
			$offer_tag_attrs_arr['type'] = $offer_type;
		}

		// bid="xx"
		$no_bid_rules_arr = [ 'yandex_direct', 'yandex_direct_free_from', 'vk', 'ozon', 'sbermegamarket' ];
		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		if ( in_array( $yfym_yml_rules, $no_bid_rules_arr ) ) {
			// bid запрещён в этих правилах
		} else {
			if ( get_post_meta( $this->get_product()->get_id(), 'yfym_bid', true ) !== '' ) {
				$yfym_bid = get_post_meta( $this->get_product()->get_id(), 'yfym_bid', true );
				$offer_tag_attrs_arr['bid'] = $yfym_bid;
			}
		}

		// id="xx"
		$offer_id_value = '';
		$yfym_source_id = common_option_get( 'yfym_source_id', false, $this->get_feed_id(), 'yfym' );
		switch ( $yfym_source_id ) {
			case "sku":
				$offer_id_value = $this->get_product()->get_sku();
				break;
			case "post_meta":
				$yfym_source_id_post_meta = common_option_get( 'yfym_source_id_post_meta', false, $this->get_feed_id(), 'yfym' );
				$yfym_source_id_post_meta = trim( $yfym_source_id_post_meta );
				if ( get_post_meta( $this->get_product()->get_id(), $yfym_source_id_post_meta, true ) !== '' ) {
					$offer_id_value = get_post_meta( $this->get_product()->get_id(), $yfym_source_id_post_meta, true );
				}
				break;
			case "germanized":
				if ( class_exists( 'WooCommerce_Germanized' ) ) {
					if ( get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true ) !== '' ) {
						$offer_id_value = get_post_meta( $this->get_product()->get_id(), '_ts_gtin', true );
					}
				}
				break;
			default:
				$offer_id_value = $this->get_product()->get_id();
		}
		$offer_id_value = apply_filters(
			'y4ym_f_simple_offer_id_value',
			$offer_id_value,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		if ( empty( $offer_id_value ) ) {
			// если данных нет, то ID-шником офера будет ID товара
			$offer_tag_attrs_arr['id'] = $this->get_product()->get_id();
		} else {
			$offer_tag_attrs_arr['id'] = $offer_id_value;
		}

		// available="xx"
		if ( true == $this->get_product()->get_manage_stock() ) { // включено управление запасом
			if ( $this->get_product()->get_stock_quantity() > 0 ) {
				$available = 'true';
			} else {
				if ( $this->get_product()->get_backorders() === 'no' ) { // предзаказ запрещен
					$available = 'false';
				} else {
					$yfym_behavior_onbackorder = common_option_get( 'yfym_behavior_onbackorder', false, $this->get_feed_id(), 'yfym' );
					if ( $yfym_behavior_onbackorder === 'false' ) {
						$available = 'false';
					} else {
						$available = 'true';
					}
				}
			}
		} else { // отключено управление запасом
			if ( $this->get_product()->get_stock_status() === 'instock' ) {
				$available = 'true';
			} else if ( $this->get_product()->get_stock_status() === 'outofstock' ) {
				$available = 'false';
			} else {
				$yfym_behavior_onbackorder = common_option_get( 'yfym_behavior_onbackorder', false, $this->get_feed_id(), 'yfym' );
				if ( $yfym_behavior_onbackorder === 'false' ) {
					$available = 'false';
				} else {
					$available = 'true';
				}
			}
		}
		$offer_tag_attrs_arr['available'] = $available;

		$offer_tag_attrs_arr = apply_filters(
			'y4ym_f_simple_offer_tag_attrs_arr',
			$offer_tag_attrs_arr,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);

		$tag_name = apply_filters(
			'y4ym_f_simple_tag_name_offer',
			$tag_name,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		$result_xml .= new Get_Open_Tag( $tag_name, $offer_tag_attrs_arr, false );

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_offer',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}