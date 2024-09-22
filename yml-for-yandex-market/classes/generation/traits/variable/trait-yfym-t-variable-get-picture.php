<?php
/**
 * Traits Picture for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.1.1
 * 
 * @version                 4.7.0 (09-09-2024)
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
 *                                      yfym_replace_domain
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Picture {
	/**
	 * Get `picture` tag
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_picture( $tag_name = 'picture', $result_xml = '' ) {
		$yfym_picture = common_option_get( 'yfym_picture', false, $this->get_feed_id(), 'yfym' );
		if ( $yfym_picture === 'disabled' ) {
			return $result_xml;
		} else if ( empty( $yfym_picture ) ) {
			$size_pic = 'full';
		} else {
			$size_pic = $yfym_picture;
		}

		$thumb_yml = get_the_post_thumbnail_url( $this->get_offer()->get_id(), $size_pic );
		if ( empty( $thumb_yml ) ) {
			// убираем default.png из фида
			$no_default_png_products = common_option_get( 'yfym_no_default_png_products', false, $this->get_feed_id(), 'yfym' );
			if ( ( $no_default_png_products === 'on' ) && ( ! has_post_thumbnail( $this->get_product()->get_id() ) ) ) {
				$picture_yml = '';
			} else {
				$thumb_id = get_post_thumbnail_id( $this->get_product()->get_id() );
				$thumb_url = wp_get_attachment_image_src( $thumb_id, $size_pic, true );
				$tag_value = $thumb_url[0]; /* урл оригинал миниатюры товара */
				$tag_value = get_from_url( $tag_value );
				$picture_yml = $this->skip_gif( $tag_name, $tag_value );
			}
		} else {
			$tag_value = get_from_url( $thumb_yml );
			$picture_yml = $this->skip_gif( $tag_name, $tag_value );
		}
		$picture_yml = apply_filters(
			'yfym_pic_variable_offer_filter',
			$picture_yml,
			$this->get_product(),
			$this->get_feed_id(),
			$this->get_offer()
		);

		// пропускаем вариации без картинок
		$skip_products_without_pic = common_option_get( 'yfym_skip_products_without_pic', false, $this->get_feed_id(), 'yfym' );
		if ( ( $skip_products_without_pic === 'on' ) && ( $picture_yml == '' ) ) {
			$this->add_skip_reason( [ 
				'offer_id' => $this->get_offer()->get_id(),
				'reason' => __( 'Product has no images', 'yml-for-yandex-market' ),
				'post_id' => $this->get_offer()->get_id(),
				'file' => 'trait-yfym-t-variable-get-picture.php',
				'line' => __LINE__
			] );
			return '';
		}

		$result_xml = $picture_yml;

		$result_xml = yfym_replace_domain( $result_xml, $this->get_feed_id() );
		$result_xml = apply_filters(
			'y4ym_f_variable_tag_picture',
			$result_xml,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			], $this->get_feed_id()
		);
		return $result_xml;
	}

	/**
	 * Skip `gif` and `svg` files
	 * 
	 * @param string $tag_name
	 * @param string $tag_value
	 * 
	 * @return string
	 */
	public function skip_gif( $tag_name, $tag_value ) {
		// удаляем из фида gif и svg картинки
		if ( false === strpos( $tag_value, '.gif' ) && false === strpos( $tag_value, '.svg' ) ) {
			$picture_yml = new Get_Paired_Tag( $tag_name, $tag_value );
		} else {
			$picture_yml = '';
		}
		return $picture_yml;
	}
}