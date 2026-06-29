<?php defined( 'WPINC' ) || exit;

/**
 * Returns the name of the rule that generates the feed.
 *
 * @link       https://icopydoc.ru
 * @since      5.6.0
 * @version    5.6.0 (29-06-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 */

/**
 * Returns the name of the rule that generates the feed.
 *
 * @since      5.6.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/common
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Options
 *             traits:     
 *             methods:     get_feed_id
 *             functions:   
 *             constants:   
 *             variable:    
 */
trait Y4YM_T_Common_Feed_Rules {

	/**
	 * Returns the name of the rule that generates the feed.
	 * 
	 * @param string $default_value
	 *
	 * @return string
	 */
	public function get_feed_rules( string $default_value = 'yandex_market_assortment' ) {

		return Y4YM_Options::settings_get(
			'y4ym_yml_rules',
			$default_value,
			$this->get_feed_id(),
			'y4ym'
		);

	}

}