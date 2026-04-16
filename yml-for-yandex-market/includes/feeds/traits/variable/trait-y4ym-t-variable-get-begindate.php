<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.3.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_begindate` methods.
 * 
 * This method allows you to return the `beginDate` tag.
 *
 * @since      5.3.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 */
trait Y4YM_T_Variable_Get_Begindate {

	/**
	 * Get `beginDate` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<beginDate>2018-07-19T23:02:35</beginDate>`.
	 */
	public function get_begindate( $tag_name = 'beginDate', $result_xml = '' ) {

		$begindate = Y4YM_Options::settings_get(
			'y4ym_begindate',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $begindate === 'disabled' ) {
			return $result_xml;
		}

		$format_date = Y4YM_Options::settings_get(
			'y4ym_format_date',
			'rfc_short',
			$this->get_feed_id(),
			'y4ym'
		);
		switch ( $format_date ) {
			case 'rfc_short':

				// 2022-07-17T17:47;
				$tag_value = (string) current_time( 'Y-m-d\TH:i:s' );

				break;
			case 'rfc':

				// 2022-07-17T17:47:19+03:00
				$tag_value = (string) current_time( 'c' );

				break;
			default:

				// время в unix формате 2022-03-21 17:47
				$tag_value = (string) current_time( 'Y-m-d H:i:s' );
		}

		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}