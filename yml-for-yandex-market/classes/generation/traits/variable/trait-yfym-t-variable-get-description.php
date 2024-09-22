<?php
/**
 * Traits Description for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.1.1
 * 
 * @version                 4.4.1 (11-06-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support/marketplace/assortment/fields/index.html
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

trait YFYM_T_Variable_Get_Description {
	/**
	 * Get product description
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_description( $tag_name = 'description', $result_xml = '' ) {
		$tag_value = '';

		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		$yfym_desc = common_option_get( 'yfym_desc', false, $this->get_feed_id(), 'yfym' );
		$yfym_the_content = common_option_get( 'yfym_the_content', false, $this->get_feed_id(), 'yfym' );
		$yfym_enable_tags_behavior = common_option_get( 'yfym_enable_tags_behavior', false, $this->get_feed_id(), 'yfym' );
		$var_desc_priority = common_option_get( 'yfym_var_desc_priority', false, $this->get_feed_id(), 'yfym' );

		if ( $var_desc_priority === 'enabled' || $var_desc_priority === 'on' ) {
			// если описание вариации в приоритете
			$tag_value = $this->get_offer()->get_description();
		}

		switch ( $yfym_desc ) {
			case "full":
				// сейчас и далее проверка на случай, если описание вариации главнее
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
				}
				break;
			case "excerpt":
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_short_description();
				}
				break;
			case "fullexcerpt":
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
					if ( empty( $tag_value ) ) {
						$tag_value = $this->get_product()->get_short_description();
					}
				}
				break;
			case "excerptfull":
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_short_description();
					if ( empty( $tag_value ) ) {
						$tag_value = $this->get_product()->get_description();
					}
				}
				break;
			case "fullplusexcerpt":
				if ( $var_desc_priority === 'enabled' || $var_desc_priority === 'on' ) {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_offer()->get_description(),
						$this->get_product()->get_short_description()
					);
				} else {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_description(),
						$this->get_product()->get_short_description()
					);
				}
				break;
			case "excerptplusfull":
				if ( $var_desc_priority === 'enabled' || $var_desc_priority === 'on' ) {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_offer()->get_description()
					);
				} else {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_product()->get_description()
					);
				}
				break;
			default:
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
					$tag_value = apply_filters( 'y4ym_f_variable_switchcase_default_description',
						$tag_value,
						[ 
							'yfym_desc' => $yfym_desc,
							'product' => $this->get_product(),
							'offer' => $this->get_offer()
						],
						$this->get_feed_id()
					);
				}
		}

		if ( empty( $tag_value ) ) {
			// схожее со строкой 43, на случай, если описание вариации имеет низкий приоритет, а другие описания пусты
			$tag_value = $this->get_offer()->get_description();
		}

		if ( ! empty( $tag_value ) ) {
			if ( $yfym_the_content === 'enabled' ) {
				$tag_value = html_entity_decode( apply_filters( 'the_content', $tag_value ) );
			}
			$tag_value = apply_filters(
				'yfym_description_filter',
				$tag_value,
				$this->get_product()->get_id(),
				$this->get_product(),
				$this->get_feed_id()
			);
			$tag_value = trim( $tag_value );
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_description',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			if ( $yfym_yml_rules === 'vk' ) {
				$tag_value = y4ym_strip_tags( $tag_value, '' );
				$tag_value = htmlspecialchars( $tag_value );
				// $tag_value = mb_strimwidth($tag_value, 0, 256);
			} else {
				$tag_value = $this->replace_tags( $tag_value, $yfym_enable_tags_behavior );
				$tag_value = '<![CDATA[' . $tag_value . ']]>';
			}
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_description',
				$tag_name,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_description',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( empty( $result_xml ) ) {
			// пропускаем вариации без описания
			$skip_products_without_desc = common_option_get( 'yfym_skip_products_without_desc', false, $this->get_feed_id(), 'yfym' );
			if ( ( $skip_products_without_desc === 'enabled' ) && ( $tag_value == '' ) ) {
				$this->add_skip_reason( [ 
					'offer_id' => $this->get_offer()->get_id(),
					'reason' => __( 'Variation product has no description', 'yml-for-yandex-market' ),
					'post_id' => $this->get_offer()->get_id(),
					'file' => 'trait-yfym-t-variable-get-description.php',
					'line' => __LINE__
				] );
				return '';
			}
		}
		return $result_xml;
	}

	/**
	 * Summary of replace_tags
	 * 
	 * @param string $description_yml - Required
	 * @param string $yfym_enable_tags_behavior - Required
	 * 
	 * @return string
	 */
	private function replace_tags( $tag_value, $yfym_enable_tags_behavior ) {
		if ( $yfym_enable_tags_behavior == 'default' ) {
			$tag_value = str_replace( '<ul>', '', $tag_value );
			$tag_value = str_replace( '<li>', '', $tag_value );
			$tag_value = str_replace( '</li>', '<br/>', $tag_value );
		}

		$yfym_enable_tags_custom = common_option_get( 'yfym_enable_tags_custom', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_enable_tags_behavior == 'default' ) {
			$enable_tags = '<p>,<br/>,<br>';
			$enable_tags = apply_filters( 'yfym_enable_tags_filter', $enable_tags, $this->get_feed_id() );
		} else {
			$enable_tags = trim( $yfym_enable_tags_custom );
			if ( $enable_tags !== '' ) {
				$enable_tags = '<' . str_replace( ',', '>,<', $enable_tags ) . '>';
			}
		}
		$tag_value = y4ym_strip_tags( $tag_value, $enable_tags );
		$tag_value = str_replace( '<br>', '<br/>', $tag_value );
		$tag_value = strip_shortcodes( $tag_value );
		return $tag_value;
	}
}