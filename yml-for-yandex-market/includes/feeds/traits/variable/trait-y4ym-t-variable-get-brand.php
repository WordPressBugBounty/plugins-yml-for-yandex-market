<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.5.0
 * @version    5.5.0 (19-05-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_brand` method.
 * 
 * This method allows you to return the `brand` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 */
trait Y4YM_T_Variable_Get_Brand {

	/**
	 * Get `brand` tag.
	 * 
	 * @see https://help.aliexpress-cis.com/help/article/upload-yml-file#heading-shag-1.-podgotovte-yml-fayl
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<brand>LEVENHUK</brand>`.
	 */
	public function get_brand( $tag_name = 'brand', $result_xml = '' ) {

		$brand_name = '';
		$brand = Y4YM_Options::settings_get(
			'y4ym_brand',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $brand === 'woocommerce_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php' )
			|| is_plugin_active( 'perfect-woocommerce-brands/main.php' )
			|| class_exists( 'Perfect_Woocommerce_Brands' ) ) && $brand === 'sfpwb' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'pwb-brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'saphali-custom-brands-pro/saphali-custom-brands-pro.php' )
			|| class_exists( 'saphali_brands_pro' ) ) && $brand === 'saphali_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'brands' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'premmerce-woocommerce-brands/premmerce-brands.php' ) )
			&& ( $brand === 'premmercebrandsplugin' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'woocommerce-brands/woocommerce-brands.php' ) )
			&& ( $brand === 'plugin_woocommerce_brands' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( class_exists( 'woo_brands' ) && $brand === 'woo_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) )
			&& ( $brand === 'yith_woocommerce_brands_add_on' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'yith_product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$brand_name = $barnd_term->name;
					break;
				}
			}
		} else if ( $brand == 'post_meta' ) {
			$brand_post_meta_id = Y4YM_Options::settings_get(
				'y4ym_brand_post_meta',
				'',
				$this->get_feed_id(),
				'y4ym'
			);
			if ( get_post_meta( $this->get_product()->get_id(), $brand_post_meta_id, true ) !== '' ) {
				$brand_yml = get_post_meta( $this->get_product()->get_id(), $brand_post_meta_id, true );
				$brand_name = $brand_yml;
			}
		} else if ( $brand == 'default_value' ) {
			$brand_yml = Y4YM_Options::settings_get(
				'y4ym_brand_post_meta',
				'',
				$this->get_feed_id(),
				'y4ym'
			);
			if ( $brand_yml !== '' ) {
				$brand_name = $brand_yml;
			}
		} else {
			if ( $brand !== 'disabled' ) {
				$brand_name = y4ym_replace_decode( $this->get_variable_global_attribute_value( $brand ) );
			}
		}

		$skip_brand_reason = false;
		$skip_brand_reason = apply_filters(
			'y4ym_f_variable_skip_brand_reason',
			$skip_brand_reason,
			[
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'brand_name' => $brand_name
			],
			$this->get_feed_id()
		);
		if ( false === $skip_brand_reason ) {
			// ! в некоторых случаях, в том числе при неправильных действиях пользователя тут может быть массив
			if ( is_string( $brand_name ) ) {
				// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
				$tag_value = htmlspecialchars( $brand_name );
			}
		} else {
			$this->add_skip_reason( [
				'reason' => $skip_brand_reason,
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-y4ym-t-variable-get-brand.php',
				'line' => __LINE__
			] );
			return '';
		}

		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}