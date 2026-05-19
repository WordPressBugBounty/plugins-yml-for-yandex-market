<?php

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.5.0
 * @version    5.5.0 (19-05-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_shortdescription`.
 * 
 * This method allows you to return the `shortDescription` tag.
 *
 * @since      5.5.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 */
trait Y4YM_T_Simple_Get_Shortdescription {

	/**
	 * Get `shortDescription` tag.
	 * 
	 * @see https://yandex.ru/sprav/templates/price-list-template.xml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<shortDescription><![CDATA[<p>текст</p>]]></shortDescription>.
	 */
	public function get_shortdescription( $tag_name = 'shortDescription', $result_xml = '' ) {

		$tag_value = '';
		$shortdesc_source = Y4YM_Options::settings_get(
			'y4ym_shortdescription',
			'disabled',
			$this->get_feed_id(),
			'y4ym'
		);
		if ( $shortdesc_source === 'disabled' ) {
			return $result_xml;
		}
		$y4ym_the_content = Y4YM_Options::settings_get(
			'y4ym_the_content',
			'enabled',
			$this->get_feed_id(),
			'y4ym'
		);

		switch ( $shortdesc_source ) {
			case "full":
				// сейчас и далее проверка на случай, если описание вариации главнее

				$tag_value = $this->get_product()->get_description();

				break;
			case "excerpt":

				$tag_value = $this->get_product()->get_short_description();

				break;
			case "fullexcerpt":

				$tag_value = $this->get_product()->get_description();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_short_description();
				}

				break;
			case "excerptfull":

				$tag_value = $this->get_product()->get_short_description();
				if ( empty( $tag_value ) ) {
					$tag_value = $this->get_product()->get_description();
				}

				break;
			case "fullplusexcerpt":

				$tag_value = sprintf( '%1$s<br/>%2$s',
					$this->get_product()->get_description(),
					$this->get_product()->get_short_description()
				);

				break;
			case "excerptplusfull":

				$tag_value = sprintf( '%1$s<br/>%2$s',
					$this->get_product()->get_short_description(),
					$this->get_product()->get_description()
				);

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
					$tag_value = apply_filters( 'y4ym_f_simple_switchcase_default_shortdescription',
						$tag_value,
						[
							'y4ym_shortdescription' => $shortdesc_source,
							'product' => $this->get_product()
						],
						$this->get_feed_id()
					);
				}
		}

		if ( ! empty( $tag_value ) ) {
			if ( $y4ym_the_content === 'enabled' ) {
				$tag_value = html_entity_decode( apply_filters( 'the_content', $tag_value ) );
			}
			$tag_value = apply_filters(
				'y4ym_f_simple_shortdescription',
				$tag_value,
				$this->get_product()->get_id(),
				$this->get_product(),
				$this->get_feed_id()
			);
			$tag_value = trim( $tag_value );
		}

		$tag_value = apply_filters(
			'y4ym_f_simple_tag_value_shortdescription',
			$tag_value,
			[
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		if ( ! empty( $tag_value ) ) {
			$tag_value = y4ym_strip_tags( $tag_value, '' );
			$tag_value = htmlspecialchars( $tag_value );
			$tag_name = apply_filters(
				'y4ym_f_simple_tag_name_shortdescription',
				$tag_name,
				[
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			$result_xml = new Y4YM_Get_Paired_Tag( $tag_name, $tag_value );
		}

		$result_xml = apply_filters(
			'y4ym_f_simple_tag_shortdescription',
			$result_xml,
			[
				'product' => $this->get_product()
			],
			$this->get_feed_id()
		);
		return $result_xml;

	}

}