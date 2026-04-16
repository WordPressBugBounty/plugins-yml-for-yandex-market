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
 * The trait adds `get_packagetype` methods.
 * 
 * This method allows you to return the `packageType` tag.
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
trait Y4YM_T_Simple_Get_Packagetype {

	/**
	 * Get `packageType` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<packageType id="4" />`
	 */
	public function get_packagetype( $tag_name = 'packageType', $result_xml = '' ) {

		$packagetype = Y4YM_Options::settings_get(
			'y4ym_packagetype',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $packagetype === 'enabled' ) {
			$tag_value = $this->get_simple_product_post_meta( 'packagetype' );
			if ( ! empty( $tag_value ) ) {
				$result_xml = new Y4YM_Get_Open_Tag(
					$tag_name,
					[ 'id' => $tag_value ],
					true
				);
			}

			$result_xml = apply_filters(
				'y4ym_f_simple_tag_packagetype', $result_xml,
				[
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;

	}

}