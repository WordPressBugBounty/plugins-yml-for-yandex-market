<?php

/**
 * Trait for variable products.
 *
 * @link       https://icopydoc.ru
 * @since      0.1.0
 * @version    5.4.0 (16-04-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 */

/**
 * The trait adds `get_model` method.
 * 
 * This method allows you to return the `model` tag.
 *
 * @since      0.1.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/variable
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_offer
 *                          get_feed_id
 *                          get_variable_product_post_meta
 */
trait Y4YM_T_Variable_Get_Model {

	/**
	 * Get `model` tag.
	 * 
	 * @see https://yandex.ru/support/direct/ru/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<model>Galaxy S22 Ultra 8/128 ГБ, синий</model>`.
	 */
	public function get_model( $tag_name = 'model', $result_xml = '' ) {

		$model = Y4YM_Options::settings_get(
			'y4ym_model',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $model === 'disabled' ) {
			return $result_xml;
		}
		switch ( $model ) {
			case "sku": // выгружать из артикула
				$tag_value = $this->get_offer()->get_sku();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_sku();
				}
				break;
			default:
				$tag_value = apply_filters(
					'y4ym_f_variable_tag_value_switch_model',
					'',
					[
						'product' => $this->get_product(),
						'offer' => $this->get_offer(),
						'switch_value' => $model
					],
					$this->get_feed_id()
				);
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_variable_global_attribute_value( $model );
				}
		}
		$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		return $result_xml;

	}

}