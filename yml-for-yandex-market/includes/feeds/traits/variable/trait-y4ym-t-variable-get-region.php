<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.3.0
 * @version    5.3.0 (22-03-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_region` methods.
 * 
 * This method allows you to return the `region` tag.
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_product_post_meta
 *                          get_variable_tag
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Region {

	/**
	 * Get `region` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<region id="504">Москва</region>`
	 */
	public function get_region( $tag_name = 'region', $result_xml = '' ) {

		$region = common_option_get(
			'y4ym_region',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $region === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'region' );
			if ( empty( $tag_value ) || $tag_value === 'default' ) {
				$region_default_value = common_option_get(
					'y4ym_region_default_value',
					'disabled',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( $region_default_value === 'disabled' ) {
					$tag_value = '';
				} else {
					$tag_value = $region_default_value;
				}
			}
 
			$regions_arr = Y4YM_Registry::to_key_value_pairs( Y4YM_Registry::get_regions_list() );
			if ( ! empty( $tag_value ) ) {
				if ( isset( $regions_arr[ $tag_value ] ) ) {
					$city = $regions_arr[ $tag_value ];
				} else {
					$city = '';
				}
				$result_xml = new Y4YM_Get_Open_Tag( 'regions' );
				$result_xml .= new Y4YM_Get_Paired_Tag(
					$tag_name,
					$city,
					[ 'id' => $tag_value ]
				);
				$result_xml .= new Y4YM_Get_Closed_Tag( 'regions' );
			}

			$result_xml = apply_filters(
				'y4ym_f_variable_tag_okei', $result_xml,
				[
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;

	}

}