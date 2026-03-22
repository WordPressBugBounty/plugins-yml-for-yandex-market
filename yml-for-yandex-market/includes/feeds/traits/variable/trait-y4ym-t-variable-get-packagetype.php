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
 * The trait adds `get_packagetype` methods.
 * 
 * This method allows you to return the `packageType` tag.
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
 *             functions:   common_option_get
 */
trait Y4YM_T_Variable_Get_Packagetype {

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

		$packagetype = common_option_get(
			'y4ym_packagetype',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $packagetype === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'packagetype' );
			if ( ! empty( $tag_value ) ) {
				$result_xml = new Y4YM_Get_Open_Tag(
					$tag_name,
					[ 'id' => $tag_value ],
					true
				);
			}

			$result_xml = apply_filters(
				'y4ym_f_variable_tag_packagetype', $result_xml,
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