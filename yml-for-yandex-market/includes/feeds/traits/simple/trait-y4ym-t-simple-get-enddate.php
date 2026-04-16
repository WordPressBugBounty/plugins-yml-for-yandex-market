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
 * The trait adds `get_enddate` methods.
 * 
 * This method allows you to return the `endDate` tag.
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
trait Y4YM_T_Simple_Get_Enddate {

	/**
	 * Get `endDate` tag.
	 * 
	 * @see https://zakupki.mos.ru/cms/Media/docs/Инструкция%20по%20формированию%20YML.pdf
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<endDate>2018-09-19T19:02:35</endDate>`.
	 */
	public function get_enddate( $tag_name = 'endDate', $result_xml = '' ) {

		$enddate = Y4YM_Options::settings_get(
			'y4ym_enddate',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $enddate === 'disabled' ) {
			return $result_xml;
		}

		$timestamp = current_time( 'timestamp' );
		$end_timestamp = strtotime( $enddate, $timestamp );
		if ( false === $end_timestamp ) {
			$end_timestamp = $timestamp; // fallback
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
				$tag_value = (string) gmdate( 'Y-m-d\TH:i:s', $end_timestamp );

				break;
			case 'rfc':

				// 2022-07-17T17:47:19+03:00
				$tag_value = (string) gmdate( 'c', $end_timestamp );

				break;
			default:

				// время в unix формате 2022-03-21 17:47
				$tag_value = (string) gmdate( 'Y-m-d H:i:s', $end_timestamp );
		}

		$result_xml = $this->get_simple_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}