<?php
/**
 * Traits Price for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.4.1 (11-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends					classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                                      get_feed_category_id
 *                                      add_skip_reason
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

// более осторожное удаление - yfym_variable_price_filter
trait YFYM_T_Variable_Get_Price {
	/**
	 * Summary of get_price
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_price( $tag_name = 'price', $result_xml = '' ) {
		/**
		 * $offer->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		 * $offer->get_regular_price() - обычная цена
		 * $offer->get_sale_price() - цена скидки
		 */
		$price_yml = $this->get_offer()->get_price(); // цена вариации
		$price_yml = apply_filters(
			'yfym_variable_price_filter',
			$price_yml,
			$this->get_product(),
			$this->get_offer(),
			$this->get_offer()->get_id(),
			$this->get_feed_id()
		);

		$price_yml = apply_filters(
			'y4ym_f_variable_price',
			$price_yml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'product_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);

		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_yml_rules !== 'all_elements' ) { 
			// если цены нет - пропускаем вариацию. Работает для всех правил кроме "Без правил"
			if ( $price_yml == 0 || empty( $price_yml ) ) {
				$this->add_skip_reason( [ 
					'offer_id' => $this->get_offer()->get_id(),
					'reason' => __( 'The product has no price', 'yfym' ),
					'post_id' => $this->get_offer()->get_id(),
					'file' => 'trait-yfym-t-variable-get-price.php',
					'line' => __LINE__
				] );
				return '';
			}
		}

		if ( class_exists( 'YmlforYandexMarketPro' ) ) {
			if ( ( false !== common_option_get( 'yfymp_compare_value', false, $this->get_feed_id(), 'yfym' ) )
				&& ( common_option_get( 'yfymp_compare', false, $this->get_feed_id(), 'yfym' ) !== '' ) ) {
				$yfymp_compare_value = common_option_get( 'yfymp_compare_value', false, $this->get_feed_id(), 'yfym' );
				$yfymp_compare = common_option_get( 'yfymp_compare', false, $this->get_feed_id(), 'yfym' );
				if ( $yfymp_compare == '>=' ) {
					if ( $price_yml < $yfymp_compare_value ) {
						$this->add_skip_reason( [ 
							'offer_id' => $this->get_offer()->get_id(),
							'reason' => sprintf( '%s: %s < %s',
								__( 'The product price', 'yfym' ),
								$this->get_offer()->get_price(),
								$yfymp_compare_value
							),
							'post_id' => $this->get_offer()->get_id(),
							'file' => 'trait-yfym-t-variable-get-price.php',
							'line' => __LINE__
						] );
						return '';
					}
				} else {
					if ( $price_yml >= $yfymp_compare_value ) {
						$this->add_skip_reason( [ 
							'offer_id' => $this->get_offer()->get_id(),
							'reason' => sprintf( '%s: %s >= %s',
								__( 'The product price', 'yfym' ),
								$this->get_offer()->get_price(),
								$yfymp_compare_value
							),
							'post_id' => $this->get_offer()->get_id(),
							'file' => 'trait-yfym-t-variable-get-price.php',
							'line' => __LINE__
						] );
						return '';
					}
				}
			}
		}

		$skip_price_reason = false;
		$skip_price_reason = apply_filters(
			'y4ym_f_variable_skip_price_reason',
			$skip_price_reason,
			[ 
				'price_yml' => $price_yml,
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( $skip_price_reason !== false ) {
			$this->add_skip_reason( [ 
				'offer_id' => $this->get_offer()->get_id(),
				'reason' => $skip_price_reason,
				'post_id' => $this->get_offer()->get_id(),
				'file' => 'trait-yfym-t-variable-get-price.php',
				'line' => __LINE__
			] );
			return '';
		}

		// старая цена
		$yfym_oldprice = common_option_get( 'yfym_oldprice', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_oldprice === 'yes' || $yfym_oldprice === 'enabled' ) {
			$price_yml = (float) $price_yml;
			$sale_price_value = (float) $this->get_offer()->get_sale_price();
			$sale_price_value = apply_filters(
				'y4ym_f_variable_sale_price_value',
				$sale_price_value,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer(),
					'product_category_id' => $this->get_feed_category_id()
				],
				$this->get_feed_id()
			);
			if ( $sale_price_value > 0 ) {
				$old_price_value = $this->get_offer()->get_regular_price();
				$old_price_value = apply_filters(
					'y4ym_f_variable_old_price_value',
					$old_price_value,
					[ 
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'product_category_id' => $this->get_feed_category_id()
					],
					$this->get_feed_id()
				);
				$oldprice_name_tag = apply_filters( 'yfym_oldprice_name_tag_filter', 'oldprice', $this->get_feed_id() );
				if ( $old_price_value !== '' ) {
					$result_xml .= new Get_Paired_Tag( $oldprice_name_tag, $old_price_value );
				}
			}
		}
		$price_yml = apply_filters(
			'yfym_variable_price_yml_filter',
			$price_yml,
			$this->get_product(),
			$this->get_offer(),
			$this->get_feed_id()
		);
		$yfym_price_from = common_option_get( 'yfym_price_from', false, $this->get_feed_id(), 'yfym' );
		if ( $price_yml !== '' ) {
			if ( $yfym_price_from === 'enabled' ) {
				$result_xml .= new Get_Paired_Tag( $tag_name, $price_yml, [ 'from' => 'true' ] );
			} else {
				$result_xml .= new Get_Paired_Tag( $tag_name, $price_yml );
			}
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_price',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'product_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}