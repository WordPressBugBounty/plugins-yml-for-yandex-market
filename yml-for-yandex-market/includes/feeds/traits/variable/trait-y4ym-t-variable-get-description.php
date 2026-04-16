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
 * The trait adds `get_description` and `replace_tags` methods.
 * 
 * This method allows you to return the `description` tag.
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
trait Y4YM_T_Variable_Get_Description {

	/**
	 * Get `description` tag.
	 * 
	 * @see https://yandex.ru/support/marketplace/assortment/fields/index.html
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<description><![CDATA[<p>текст</p>]]></description>`.
	 */
	public function get_description( $tag_name = 'description', $result_xml = '' ) {

		$tag_value = '';

		$yml_rules = Y4YM_Options::settings_get(
			'y4ym_yml_rules',
			'yandex_market_assortment',
			$this->get_feed_id(),
			'y4ym'
		);
		$desc_source = Y4YM_Options::settings_get(
			'y4ym_desc',
			'fullexcerpt',
			$this->get_feed_id(),
			'y4ym'
		);
		$y4ym_the_content = Y4YM_Options::settings_get(
			'y4ym_the_content',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);
		$enable_tags_behavior = Y4YM_Options::settings_get(
			'y4ym_enable_tags_behavior',
			'default',
			$this->get_feed_id(),
			'y4ym'
		);
		$var_desc_priority = Y4YM_Options::settings_get(
			'y4ym_var_desc_priority',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);

		if ( $var_desc_priority === 'enabled' || $var_desc_priority === 'on' ) {
			// если описание вариации в приоритете
			$tag_value = $this->get_offer()->get_description();
		}

		switch ( $desc_source ) {
			case "full":

				// сейчас и далее проверка на случай, если описание вариации главнее
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
				}

				break;
			case "excerpt":

				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_short_description();
				}

				break;
			case "fullexcerpt":

				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
					if ( empty( $tag_value ) ) {
						$tag_value = $this->get_product()->get_short_description();
					}
				}

				break;
			case "excerptfull":

				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_short_description();
					if ( empty( $tag_value ) ) {
						$tag_value = $this->get_product()->get_description();
					}
				}

				break;
			case "fullplusexcerpt":

				if ( $var_desc_priority === 'enabled' || $var_desc_priority === 'on' ) {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_offer()->get_description(),
						$this->get_product()->get_short_description()
					);
				} else {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_description(),
						$this->get_product()->get_short_description()
					);
				}

				break;
			case "excerptplusfull":

				if ( $var_desc_priority === 'enabled' || $var_desc_priority === 'on' ) {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_offer()->get_description()
					);
				} else {
					$tag_value = sprintf( '%1$s<br/>%2$s',
						$this->get_product()->get_short_description(),
						$this->get_product()->get_description()
					);
				}

				break;
			case 'post_meta':

				$post_meta = Y4YM_Options::settings_get(
					'y4ym_source_description_post_meta',
					'',
					$this->get_feed_id(),
					'y4ym'
				);
				if ( empty( $post_meta ) || get_post_meta( $this->get_product()->get_id(), $post_meta, true ) == '' ) {
					$tag_value = '';
				} else {
					$tag_value = get_post_meta( $this->get_product()->get_id(), $post_meta, true );
				}

				break;
			default:

				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
					$tag_value = apply_filters( 'y4ym_f_variable_switchcase_default_description',
						$tag_value,
						[
							'y4ym_desc' => $desc_source,
							'product' => $this->get_product(),
							'offer' => $this->get_offer()
						],
						$this->get_feed_id()
					);
				}
		}

		if ( empty( $tag_value ) ) {
			// схожее со строкой 43, на случай, если описание вариации имеет низкий приоритет, а другие описания пусты
			$tag_value = $this->get_offer()->get_description();
		}

		if ( ! empty( $tag_value ) ) {
			if ( $y4ym_the_content === 'enabled' ) {
				$tag_value = html_entity_decode( apply_filters( 'the_content', $tag_value ) );
			}
			$tag_value = apply_filters(
				'y4ym_description_filter',
				$tag_value,
				$this->get_product()->get_id(),
				$this->get_product(),
				$this->get_feed_id()
			);
			$tag_value = trim( $tag_value );
		}

		$tag_value = apply_filters(
			'y4ym_f_variable_tag_value_description',
			$tag_value,
			[
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			if ( $yml_rules === 'vk'
				|| $yml_rules === 'yandex_direct'
				|| $yml_rules === 'yandex_direct_free_from'
				|| $yml_rules === 'yandex_direct_combined' ) {

				$tag_value = y4ym_strip_tags( $tag_value, '' );
				$tag_value = htmlspecialchars( $tag_value );
				// $tag_value = mb_strimwidth($tag_value, 0, 256);

			} else {

				$tag_value = $this->replace_tags( $tag_value, $enable_tags_behavior );
				$tag_value = '<![CDATA[' . $tag_value . ']]>';

			}
			$tag_name = apply_filters(
				'y4ym_f_variable_tag_name_description',
				$tag_name,
				[
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
			$result_xml = new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_variable_tag_description',
			$result_xml,
			[
				'product' => $this->get_product(),
				'offer' => $this->get_offer()
			],
			$this->get_feed_id()
		);
		if ( empty( $result_xml ) ) {
			// пропускаем вариации без описания
			$skip_products_without_desc = Y4YM_Options::settings_get(
				'y4ym_skip_products_without_desc',
				'disabled',
				$this->get_feed_id(),
				'y4ym'
			);
			if ( ( $skip_products_without_desc === 'enabled' ) && ( $tag_value == '' ) ) {
				$this->add_skip_reason( [
					'offer_id' => $this->get_offer()->get_id(),
					'reason' => __( 'Variation product has no description', 'yml-for-yandex-market' ),
					'post_id' => $this->get_offer()->get_id(),
					'file' => 'trait-y4ym-t-variable-get-description.php',
					'line' => __LINE__
				] );
				return '';
			}
		}
		return $result_xml;

	}

	/**
	 * Processes and sanitizes a string by replacing or removing specific HTML tags and shortcodes.
	 *
	 * Depending on the $enable_tags_behavior value, the function either applies a default set of allowed tags
	 * or uses a custom list of allowed tags retrieved from settings. It also handles specific tag replacements,
	 * such as converting list items to line breaks, and removes all shortcodes from the string.
	 *
	 * @param string $tag_value The input string containing HTML tags and/or shortcodes to be processed.
	 * @param string $enable_tags_behavior The behavior mode for allowed tags. Use `default` for standard processing,
	 *                                     or another value to use custom allowed tags from settings.
	 *
	 * @return string The sanitized string with processed tags and removed shortcodes.
	 */
	private function replace_tags( $tag_value, $enable_tags_behavior ) {

		if ( $enable_tags_behavior === 'default' ) {
			$tag_value = str_replace( '<ul>', '', $tag_value );
			$tag_value = str_replace( '<li>', '', $tag_value );
			$tag_value = str_replace( '</li>', '<br/>', $tag_value );
		}

		$y4ym_enable_tags_custom = Y4YM_Options::settings_get(
			'y4ym_enable_tags_custom',
			'',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $enable_tags_behavior === 'default' ) {
			$enable_tags = '<p>,<br/>,<br>';
			$enable_tags = apply_filters(
				'y4ym_enable_tags_filter',
				$enable_tags,
				$this->get_feed_id()
			);
		} else {
			$enable_tags = trim( $y4ym_enable_tags_custom );
			if ( $enable_tags !== '' ) {
				$enable_tags = '<' . str_replace( ',', '>,<', $enable_tags ) . '>';
			}
		}
		$tag_value = y4ym_strip_tags( $tag_value, $enable_tags );
		$tag_value = str_replace( '<br>', '<br/>', $tag_value );
		$tag_value = strip_shortcodes( $tag_value );
		return $tag_value;

	}

}