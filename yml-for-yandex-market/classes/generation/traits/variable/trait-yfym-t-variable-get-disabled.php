<?php
/**
 * Traits Disabled for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.2.1 (24-01-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     https://yandex.ru/support2/marketplace/ru/assortment/fields/index#disabled
 *
 * @depends                 classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_Disabled {
	/**
	 * Summary of get_disabled
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_disabled($tag_name = 'disabled', $result_xml = '') {
		$tag_value = '';

		$disabled = common_option_get('yfym_auto_disabled', false, $this->get_feed_id(), 'yfym');
		if ($disabled === 'enabled' || $disabled === 'yes') {
			// если товар не доступен к покупке
			if (false === $this->get_offer()->is_in_stock()) {
				$tag_value = 'true';
			} else {
				$tag_value = 'false';
			}
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_disabled',
			$tag_value,
			[ 
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			], 
			$this->get_feed_id()
		);

		if (!empty($tag_value)) {
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_disabled',
				$tag_name,
				[
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Get_Paired_Tag($tag_name, $tag_value);
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_disabled',
			$result_xml,
			[
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);

		return $result_xml;
	}
}