<?php
/**
 * Traits Dimensions for simple products
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
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Simple_Get_Dimensions {
	/**
	 * Get `dimensions` tag or `<param name="Длина, см">XX</param>`, `<param name="Ширина, см">XX</param>`,
	 * `<param name="Высота, см">XX</param>`
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_dimensions( $tag_name = 'dimensions', $result_xml = '' ) {
		// * к сожалению wc_get_dimension не всегда возвращает float и юзер может передать в размер что-то типа '13-18'
		// * потому юзаем gettype() === 'double'
		$length_yml = 0;
		$width_yml = 0;
		$height_yml = 0;
		$length = common_option_get( 'yfym_length', false, $this->get_feed_id(), 'yfym' );
		if ( empty( $length ) || $length === 'woo_shippings' ) {
			if ( $this->get_product()->has_dimensions() ) {
				$length_yml = $this->get_product()->get_length();
				if ( ! empty( $length_yml ) && gettype( $length_yml ) === 'double' ) {
					$length_yml = round( wc_get_dimension( $length_yml, 'cm' ), 3 );
				}
			}
		} else {
			$length = (int) $length;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $length ) );
			$length_yml = round( wc_get_dimension( (float) $tag_value, 'cm' ), 3 );
		}

		$width = common_option_get( 'yfym_width', false, $this->get_feed_id(), 'yfym' );
		if ( empty( $width ) || $width === 'woo_shippings' ) {
			if ( $this->get_product()->has_dimensions() ) {
				$width_yml = $this->get_product()->get_width();
				if ( ! empty( $width_yml ) && gettype( $width_yml ) === 'double' ) {
					$width_yml = round( wc_get_dimension( $width_yml, 'cm' ), 3 );
				}
			}
		} else {
			$width = (int) $width;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $width ) );
			$width_yml = round( wc_get_dimension( (float) $tag_value, 'cm' ), 3 );
		}

		$height = common_option_get( 'yfym_height', false, $this->get_feed_id(), 'yfym' );
		if ( empty( $height ) || $height === 'woo_shippings' ) {
			if ( $this->get_product()->has_dimensions() ) {
				$height_yml = $this->get_product()->get_height();
				if ( ! empty( $height_yml ) && gettype( $height_yml ) === 'double' ) {
					$height_yml = round( wc_get_dimension( $height_yml, 'cm' ), 3 );
				}
			}
		} else {
			$height = (int) $height;
			$tag_value = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $height ) );
			$height_yml = round( wc_get_dimension( (float) $tag_value, 'cm' ), 3 );
		}

		$yfym_yml_rules = common_option_get( 'yfym_yml_rules', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_yml_rules === 'flowwow' ) {
			if ( $length_yml > 0 ) {
				$result_xml .= new Get_Paired_Tag( 'param', $length_yml, [ 'name' => 'Длина, см' ] );
			}
			if ( $width_yml > 0 ) {
				$result_xml .= new Get_Paired_Tag( 'param', $width_yml, [ 'name' => 'Ширина, см' ] );
			}
			if ( $height_yml > 0 ) {
				$result_xml .= new Get_Paired_Tag( 'param', $height_yml, [ 'name' => 'Высота, см' ] );
			}
		} else if ( ( $length_yml > 0 ) && ( $width_yml > 0 ) && ( $height_yml > 0 ) ) {
			$result_xml = '<dimensions>' . $length_yml . '/' . $width_yml . '/' . $height_yml . '</dimensions>' . PHP_EOL;
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_dimensions',
			$result_xml,
			[ 'product' => $this->get_product() ],
			$this->get_feed_id()
		);
		return $result_xml;
	}
}