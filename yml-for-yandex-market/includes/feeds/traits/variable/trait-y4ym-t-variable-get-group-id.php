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
 * The trait adds `get_group_id` methods.
 * 
 * This method allows you to return the `group_id` tag.
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
 */
trait Y4YM_T_Variable_Get_Group_Id {

	/**
	 * Get `group_id` tag.
	 * 
	 * @see 
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<group_id>153</group_id>`
	 */
	public function get_group_id( $tag_name = 'group_id', $result_xml = '' ) {

		$group_id = Y4YM_Options::settings_get(
			'y4ym_group_id',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);

		if ( $group_id === 'enabled' ) {
			$tag_value = $this->get_group_id_value();
			$result_xml = $this->get_variable_tag( $tag_name, $tag_value );
		}

		return $result_xml;

	}

	/**
	 * Get `group_id` value.
	 * 
	 * @return string Example: `153`.
	 */
	public function get_group_id_value() {

		$tag_value = $this->get_product()->get_id();
		$tag_value = apply_filters(
			'y4ym_f_group_id_value',
			$tag_value,
			[
				'product' => $this->get_product(),
				'offer' => $this->get_offer(),
				'feed_category_id' => $this->get_feed_category_id()
			],
			$this->get_feed_id()
		);
		return (string) $tag_value;

	}


}
