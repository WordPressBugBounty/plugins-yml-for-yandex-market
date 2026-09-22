<?php defined( 'WPINC' ) || exit;

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      5.9.0
 * @version    5.9.0 (22-09-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_ctru_code` method.
 * 
 * This method allows you to return the `ctru_code` tag.
 *
 * @since      5.9.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *             functions:   
 */
trait Y4YM_T_Variable_Get_Ctru_Code {

	/**
	 * Get `ctru_code` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/ru/assortment/auto/yml-file#ctru-code
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<ctru_code>20.41.32.121-00000006</ctru_code>`.
	 */
	public function get_ctru_code( $tag_name = 'ctru_code', $result_xml = '' ) {

		$ctru_code = Y4YM_Options::settings_get(
			'y4ym_ctru_code',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $ctru_code === 'enabled' ) {
			$tag_value = $this->get_variable_product_post_meta( 'ctru_code' );
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}
		return $result_xml;

	}

}
