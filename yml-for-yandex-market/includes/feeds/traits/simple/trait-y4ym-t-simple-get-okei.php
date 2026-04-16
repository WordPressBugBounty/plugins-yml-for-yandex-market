<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.3.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_okei` methods.
 * 
 * This method allows you to return the `okei` tag.
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 */
trait Y4YM_T_Simple_Get_Okei {

	/**
	 * Get `okei` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<okei id="113">Упаковка</okei>`
	 */
	public function get_okei( $tag_name = 'okei', $result_xml = '' ) {

		$okei = Y4YM_Options::settings_get(
			'y4ym_okei',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $okei === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'okei' );
			if ( empty( $tag_value ) || $tag_value === 'default' ) {
				$okei_default_value = Y4YM_Options::settings_get(
					'y4ym_okei_default_value',
					'disabled',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( $okei_default_value === 'disabled' ) {
					$tag_value = '';
				} else {
					$tag_value = $okei_default_value;
				}
			}

			$okeis_arr = Y4YM_Registry::to_key_value_pairs( Y4YM_Registry::get_okei_list() );
			if ( ! empty( $tag_value ) ) {

				$result_xml = new Y4YM_Get_Paired_Tag(
					$tag_name,
					$okeis_arr[ $tag_value ],
					[ 'id' => $tag_value ]
				);

			}

			$result_xml = apply_filters(
				'y4ym_f_simple_tag_okei', $result_xml,
				[
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;

	}

}