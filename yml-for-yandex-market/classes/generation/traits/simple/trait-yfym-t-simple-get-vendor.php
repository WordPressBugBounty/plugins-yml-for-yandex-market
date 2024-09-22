<?php
/**
 * Traits Vendor for simple products
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
 *                                      add_skip_reason
 *                          functions:  common_option_get
 *                          constants:
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Vendor {
	/**
	 * Get `vendor` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_vendor( $tag_name = 'vendor', $result_xml = '' ) {
		$vendor_name = '';

		$vendor = common_option_get( 'yfym_vendor', false, $this->get_feed_id(), 'yfym' );
		if ( ( is_plugin_active( 'perfect-woocommerce-brands/perfect-woocommerce-brands.php' )
			|| is_plugin_active( 'perfect-woocommerce-brands/main.php' )
			|| class_exists( 'Perfect_Woocommerce_Brands' ) ) && $vendor === 'sfpwb' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'pwb-brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'saphali-custom-brands-pro/saphali-custom-brands-pro.php' )
			|| class_exists( 'saphali_brands_pro' ) ) && $vendor === 'saphali_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'brands' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'premmerce-woocommerce-brands/premmerce-brands.php' ) )
			&& ( $vendor === 'premmercebrandsplugin' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'woocommerce-brands/woocommerce-brands.php' ) )
			&& ( $vendor === 'woocommerce_brands' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( class_exists( 'woo_brands' ) && $vendor === 'woo_brands' ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( ( is_plugin_active( 'yith-woocommerce-brands-add-on/init.php' ) )
			&& ( $vendor === 'yith_woocommerce_brands_add_on' ) ) {
			$barnd_terms = get_the_terms( $this->get_product()->get_id(), 'yith_product_brand' );
			if ( $barnd_terms !== false ) {
				foreach ( $barnd_terms as $barnd_term ) {
					$vendor_name = $barnd_term->name;
					break;
				}
			}
		} else if ( $vendor == 'post_meta' ) {
			$vendor_post_meta_id = common_option_get( 'yfym_vendor_post_meta', false, $this->get_feed_id(), 'yfym' );
			if ( get_post_meta( $this->get_product()->get_id(), $vendor_post_meta_id, true ) !== '' ) {
				$vendor_yml = get_post_meta( $this->get_product()->get_id(), $vendor_post_meta_id, true );
				$vendor_name = ucfirst( yfym_replace_decode( $vendor_yml ) );
			}
		} else if ( $vendor == 'default_value' ) {
			$vendor_yml = common_option_get( 'yfym_vendor_post_meta', false, $this->get_feed_id(), 'yfym' );
			if ( $vendor_yml !== '' ) {
				$vendor_name = ucfirst( yfym_replace_decode( $vendor_yml ) );
			}
		} else {
			if ( $vendor !== 'disabled' ) {
				$vendor = (int) $vendor;
				$vendor_yml = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $vendor ) );
				if ( ! empty( $vendor_yml ) ) {
					$vendor_name = ucfirst( yfym_replace_decode( $vendor_yml ) );
				}
			}
		}

		$skip_vendor_reason = false;
		$skip_vendor_reason = apply_filters(
			'y4ym_f_simple_skip_vendor_reason',
			$skip_vendor_reason,
			[ 
				'product' => $this->get_product(),
				'vendor_name' => $vendor_name
			],
			$this->get_feed_id()
		);
		if ( $skip_vendor_reason !== false ) {
			$this->add_skip_reason( [ 
				'reason' => $skip_vendor_reason,
				'post_id' => $this->get_product()->get_id(),
				'file' => 'trait-yfym-t-simple-get-vendor.php',
				'line' => __LINE__
			] );
			return '';
		}

		$vendor_name = apply_filters(
			'y4ym_f_simple_tag_value_vendor',
			$vendor_name,
			[ 
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $vendor_name ) ) {
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_vendor',
				$tag_name,
				[ 
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			// ! обернул $tag_value в htmlspecialchars т.к у нас могут быть амперсанды
			$result_xml = new Get_Paired_Tag( $tag_name, htmlspecialchars( $vendor_name ) );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_vendor',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'vendor_name' => $vendor_name
			],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}