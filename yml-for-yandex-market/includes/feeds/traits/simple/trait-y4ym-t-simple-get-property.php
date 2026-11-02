<?php defined( 'WPINC' ) || exit;

/**
 * Trait for simple products.
 *
 * @link       https://icopydoc.ru
 * @since      5.10.0
 * @version    5.10.0 (08-10-2026)
 *
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 */

/**
 * The trait adds `get_property` method.
 * 
 * This method allows you to return the `property` tags.
 *
 * @since      5.10.0
 * @package    Y4YM
 * @subpackage Y4YM/includes/feeds/traits/simple
 * @author     Maxim Glazunov <icopydoc@gmail.com>
 * @depends    classes:     Y4YM_Get_Paired_Tag
 *                          Y4YM_Options
 *             methods:     get_product
 *                          get_feed_id
 *                          get_feed_rules
 */
trait Y4YM_T_Simple_Get_Property {

	/**
	 * Get `property` tags.
	 * 
	 * @see https://yandex.ru/support/direct/feeds/requirements-yml
	 * 
	 * @param string $tag_name
	 * @param string $result_xml
	 * 
	 * @return string Example: `<property name="Операционная система">Для Windows</property>`.
	 */
	public function get_property( $tag_name = 'property', $result_xml = '' ) {

		// массивы хранятся в отдельных опциях и выводятся тоже иначе
		$property_arr = maybe_unserialize( Y4YM_Options::get(
			'y4ym_property_arr' . $this->get_feed_id(),
			[]
		) );
		if ( empty( $property_arr ) ) {

			return $result_xml;

		} else {

			$behavior_of_property = Y4YM_Options::settings_get(
				'y4ym_behavior_of_property',
				'default',
				$this->get_feed_id(),
				'y4ym'
			);
			$attributes = $this->get_product()->get_attributes();
			foreach ( $attributes as $param ) {

				// проверка на вариативность атрибута не нужна
				$param_val = $this->get_product()->get_attribute( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );

				$param_id_string = (string) $param->get_id(); // ! важно, т.к. в настройках id как строки
				if ( ! in_array( $param_id_string, $property_arr, true ) ) {
					continue; // если этот параметр не нужно выгружать - пропускаем
				}

				$param_name = wc_attribute_label( wc_attribute_taxonomy_name_by_id( $param->get_id() ) );
				if ( empty( $param_name ) || empty( $param_val ) ) {
					continue; // если пустое имя атрибута или значение - пропускаем
				}

				if ( $behavior_of_property === 'split' ) {
					$val = ucfirst( y4ym_replace_decode( $param_val ) );
					$val_arr = explode( ", ", $val );
					foreach ( $val_arr as $value ) {
						$result_xml .= new Y4YM_Get_Paired_Tag(
							$tag_name,
							$value,
							[ 'name' => htmlspecialchars( $param_name ) ]
						);
					}
				} else {
					$result_xml .= new Y4YM_Get_Paired_Tag(
						$tag_name,
						ucfirst( y4ym_replace_decode( $param_val ) ),
						[ 'name' => htmlspecialchars( $param_name ) ]
					);
				}

			} // end foreach

			$result_xml = apply_filters(
				'y4ym_f_simple_tag_property',
				$result_xml,
				[
					'product' => $this->get_product()
				],
				$this->get_feed_id()
			);
			return $result_xml;

		}

	}

}
