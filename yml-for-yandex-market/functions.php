<?php defined( 'ABSPATH' ) || exit;
/**
 * Возвращает то, что может быть результатом update_blog_option, update_option
 * 
 * @since 3.0.0
 *
 * @param string $option_name - Required
 * @param string $value - Optional -
 * @param string $n - Optional -
 * @param string|bool $autoload - Optional - (yes/no)
 * @param string $type - Optional
 * @param string $source_settings_name - Optional 
 *
 * @return bool
 */
function yfym_optionUPD( $option_name, $value = '', $n = '', $autoload = 'yes', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	if ( $autoload === 'yes' ) {
		$autoload = true;
	} else {
		$autoload = false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
			$yfym_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'yfym_settings_arr', $yfym_settings_arr );
			} else {
				return update_option( 'yfym_settings_arr', $yfym_settings_arr, $autoload );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$yfym_settings_arr = yfym_optionGET( $source_settings_name );
			$yfym_settings_arr[ $n ][ $option_name ] = $value;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $yfym_settings_arr );
			} else {
				return update_option( $source_settings_name, $yfym_settings_arr, $autoload );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $option_name, $value );
			} else {
				return update_option( $option_name, $value, $autoload );
			}
	}
}

/**
 * Возвращает то, что может быть результатом get_blog_option, get_option
 * 
 * @since 2.0.0
 *
 * @param string $option_name - Required
 * @param string $n - Optional
 * @param string $type - Optional
 * @param string $source_settings_name - Optional
 *
 * @return mixed|false
 */
function yfym_optionGET( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == 'yfym_status_sborki' && $n == '1' ) {
		if ( is_multisite() ) {
			return get_blog_option( get_current_blog_id(), 'yfym_status_sborki' );
		} else {
			return get_option( 'yfym_status_sborki' );
		}
	}

	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
			if ( isset( $yfym_settings_arr[ $n ][ $option_name ] ) ) {
				return $yfym_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$yfym_settings_arr = yfym_optionGET( $source_settings_name );
			if ( isset( $yfym_settings_arr[ $n ][ $option_name ] ) ) {
				return $yfym_settings_arr[ $n ][ $option_name ];
			} else {
				return false;
			}
		case "for_update_option":
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
		default:
			/* for old premium versions */
			if ( $option_name === 'yfym_desc' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'yfym_no_default_png_products' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'yfym_whot_export' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'yfym_file_extension' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'yfym_feed_assignment' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}

			if ( $option_name === 'yfym_file_ids_in_yml' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}
			if ( $option_name === 'yfym_wooc_currencies' ) {
				return yfym_optionGET( $option_name, $n, 'set_arr' );
			}
			/* for old premium versions */
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return get_blog_option( get_current_blog_id(), $option_name );
			} else {
				return get_option( $option_name );
			}
	}
}

/**
 * Возвращает то, что может быть результатом delete_blog_option, delete_option
 * 
 * @since 3.0.0
 *
 * @param string $option_name - Required
 * @param string $n - Optional -
 * @param string $type - Optional - (@since 3.5.5)
 * @param string $source_settings_name - Optional - (@since 3.6.4)
 *
 * @return bool
 */
function yfym_optionDEL( $option_name, $n = '', $type = '', $source_settings_name = '' ) {
	if ( $option_name == '' ) {
		return false;
	}
	switch ( $type ) {
		case "set_arr":
			if ( $n === '' ) {
				$n = '1';
			}
			$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
			unset( $yfym_settings_arr[ $n ][ $option_name ] );
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), 'yfym_settings_arr', $yfym_settings_arr );
			} else {
				return update_option( 'yfym_settings_arr', $yfym_settings_arr );
			}
		case "custom_set_arr":
			if ( $source_settings_name === '' ) {
				return false;
			}
			if ( $n === '' ) {
				$n = '1';
			}
			$yfym_settings_arr = yfym_optionGET( $source_settings_name );
			unset( $yfym_settings_arr[ $n ][ $option_name ] );
			if ( is_multisite() ) {
				return update_blog_option( get_current_blog_id(), $source_settings_name, $yfym_settings_arr );
			} else {
				return update_option( $source_settings_name, $yfym_settings_arr );
			}
		default:
			if ( $n === '1' ) {
				$n = '';
			}
			$option_name = $option_name . $n;
			if ( is_multisite() ) {
				return delete_blog_option( get_current_blog_id(), $option_name );
			} else {
				return delete_option( $option_name );
			}
	}
}

/**
 * Создает tmp файл-кэш товара
 * 
 * @since 2.0.0
 * @since 3.0.0 добавлена поддержка нескольких фидов
 * @since 3.0.2 исправлена критическая ошибка
 * @since 3.1.0 добавлен параметр ids_in_yml
 * 
 * @return void
 */
function yfym_wf( $result_yml, $post_id, $feed_id = '1', $ids_in_yml = '' ) {
	if ( ! is_dir( YFYM_PLUGIN_UPLOADS_DIR_PATH ) ) {
		error_log( sprintf( 'WARNING: Папки %s нет; Файл: functions.php; Строка: %s',
			YFYM_PLUGIN_UPLOADS_DIR_PATH,
			__LINE__
		), 0 );
		if ( ! mkdir( YFYM_PLUGIN_UPLOADS_DIR_PATH ) ) {
			error_log( sprintf( 'ERROR: Создать папку %s не вышло; Файл: functions.php; Строка: %s',
				YFYM_PLUGIN_UPLOADS_DIR_PATH,
				__LINE__
			), 0 );
		} else {
			if ( yfym_optionGET( 'yzen_yandex_zen_rss' ) == 'enabled' ) {
				$result_yml = yfym_optionGET( 'yfym_feed_content' );
			}
		}
	} else {
		if ( yfym_optionGET( 'yzen_yandex_zen_rss' ) == 'enabled' ) {
			$result_yml = yfym_optionGET( 'yfym_feed_content' );
		}
	}

	$name_dir = YFYM_PLUGIN_UPLOADS_DIR_PATH . '/feed' . $feed_id;
	if ( ! is_dir( $name_dir ) ) {
		error_log( 'WARNING: Папки $name_dir =' . $name_dir . ' нет; Файл: functions.php; Строка: ' . __LINE__, 0 );
		if ( ! mkdir( $name_dir ) ) {
			error_log(
				'ERROR: Создать папку $name_dir =' . $name_dir . ' не вышло; Файл: functions.php; Строка: ' . __LINE__,
				0
			);
		}
	}
	if ( is_dir( $name_dir ) ) {
		$filename = $name_dir . '/' . $post_id . '.tmp';
		$fp = fopen( $filename, "w" );
		if ( empty( $result_yml ) ) {
			$result_yml = ' ';
		}
		fwrite( $fp, $result_yml ); // записываем в файл текст
		fclose( $fp ); // закрываем

		/* C версии 3.1.0 */
		$filename = $name_dir . '/' . $post_id . '-in.tmp';
		$fp = fopen( $filename, "w" );
		fwrite( $fp, $ids_in_yml );
		fclose( $fp );
		/* end с версии 3.1.0 */
	} else {
		error_log( 'ERROR: Нет папки yfym! $name_dir =' . $name_dir . '; Файл: functions.php; Строка: ' . __LINE__, 0 );
	}
}

/**
 * @since 3.3.16
 *
 * @return string
 */
function yfym_replace_decode( $string, $feed_id = '1' ) {
	$string = str_replace( "+", 'yfym', $string );
	$string = urldecode( $string );
	$string = str_replace( "yfym", '+', $string );
	$string = apply_filters( 'yfym_replace_decode_filter', $string, $feed_id );
	return $string;
}

/**
 * @since 3.4.0
 *
 * @param string $array - Required
 * @param string|int $key - Required
 * @param mixed|array $default_data - Optional
 *
 * @return mixed
 */
function yfym_data_from_arr( $array, $key, $default_data = null ) {
	if ( isset( $array[ $key ] ) ) {
		return $array[ $key ];
	} else {
		return $default_data;
	}
}

/**
 * Print multiple select. Function based woocommerce_wp_select
 * 
 * @since 3.4.0
 * @see https://stackoverflow.com/questions/23287358/woocommerce-multi-select-for-single-product-field
 * 
 * @param array $field - Required
 * @param bool $blog_option - Optional
 *
 * @return void
 */
function yfym_woocommerce_wp_select_multiple( $field, $blog_option = false ) {
	if ( false === $blog_option ) {
		global $thepostid, $post, $woocommerce;
		$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['value'] = isset( $field['value'] ) ? $field['value'] : ( get_post_meta( $thepostid, $field['id'], true ) ? get_post_meta( $thepostid, $field['id'], true ) : [] );
	} else { // если у нас глобальные настройки, а не метаполя, то данные тащим через yfym_optionGET
		global $woocommerce;
		$field['value'] = isset( $field['value'] ) ? $field['value'] : ( yfym_optionGET( $field['id'] ) ? yfym_optionGET( $field['id'] ) : [] );
	}

	$field['class'] = isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['name'] = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['label'] = isset( $field['label'] ) ? $field['label'] : '';

	printf(
		'<p class="form-field %1$s_field %2$s">
			<label for="%1$s">%3$s</label><select id="%1$s" name="%4$s[]" class="%5$s" multiple="multiple">',
		esc_attr( $field['id'] ),
		esc_attr( $field['wrapper_class'] ),
		wp_kses_post( $field['label'] ),
		esc_attr( $field['name'] ),
		esc_attr( $field['class'] )
	);

	foreach ( $field['options'] as $key => $value ) {
		echo '<option value="' . esc_attr( $key ) . '" ' . ( in_array( $key, $field['value'] ) ? 'selected="selected"' : '' ) . '>' . esc_html( $value ) . '</option>';
	}

	echo '</select> ';

	if ( ! empty( $field['description'] ) ) {
		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			printf(
				'<img class="help_tip" data-tip="%s" src="%s/assets/images/help.png" height="16" width="16" />',
				esc_attr( $field['description'] ),
				esc_url( WC()->plugin_url() )
			);
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}
	}

	echo '</p>';
}

/**
 * Получает ID первого фида. Используется на случай если get-параметр numFeed не указан
 * 
 * @since 3.7.0
 *
 * @return string feed ID or (string)''
 */
function yfym_get_first_feed_id() {
	$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
	if ( ! empty( $yfym_settings_arr ) ) {
		return (string) array_key_first( $yfym_settings_arr );
	} else {
		return '';
	}
}

/**
 * Получает ID последнего фида
 * 
 * @since 4.1.12
 *
 * @return string feed ID or (string)''
 */
function yfym_get_last_feed_id() {
	$yfym_settings_arr = univ_option_get( 'yfym_settings_arr' );
	if ( ! empty( $yfym_settings_arr ) ) {
		return (string) array_key_last( $yfym_settings_arr );
	} else {
		return yfym_get_first_feed_id();
	}
}

/**
 * The function replaces the domain in the URL
 * 
 * @since 3.7.5
 *
 * @param string $url - Required
 * @param string $feed_id - Required
 *
 * @return string
 */
function yfym_replace_domain( $url, $feed_id ) {
	$new_url = common_option_get( 'yfym_replace_domain', false, $feed_id, 'yfym' );
	if ( ! empty( $new_url ) ) {
		$domain = home_url(); // parse_url($url, PHP_URL_HOST);
		$new_url = (string) $new_url;
		$url = str_replace( $domain, $new_url, $url );
	}
	return $url;
}

/**
 * Wrapper for the `strip_tags` function
 * 
 * @since 4.4.1
 *
 * @param mixed $tag_value - Required
 * @param string $enable_tags - Optional
 *
 * @return string
 */
function y4ym_strip_tags( $tag_value, $enable_tags = '' ) {
	if ( null === $tag_value ) {
		return (string) $tag_value;
	}
	$tag_value = strip_tags( $tag_value, $enable_tags );
	return $tag_value;
}

if ( ! function_exists( 'get_nested_tag' ) ) {
	/**
	 * 
	 * Splits the tag value into nested parts using the ` | ` separator
	 * 
	 * @since 4.7.2
	 * 
	 * @param string $name_wrapper_tag - Required
	 * @param string $name_nested_tag - Required
	 * @param string $tag_value - Required
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	function get_nested_tag( $name_wrapper_tag, $name_nested_tag, $tag_value, $result_xml = '' ) {
		$elements = explode( "|", $tag_value );
		if ( count( $elements ) > 1 ) {
			$result_xml .= new Get_Open_Tag( $name_wrapper_tag );
			foreach ( $elements as $element ) {
				$result_xml .= new Get_Paired_Tag( $name_nested_tag, $element );
			}
			$result_xml .= new Get_Closed_Tag( $name_wrapper_tag );
		} else if ( count( $elements ) === (int) 1 ) {
			$result_xml .= new Get_Paired_Tag( $name_wrapper_tag, $elements[0] );
		}
		return $result_xml;
	}
}

if ( ! function_exists( 'forced_cron' ) ) {
	/**
	 * Forced to start wp-cron.php if CRON tasks are overdue by more than `85` seconds
	 * 
	 * @since 4.8.0
	 * 
	 * @param int $sec
	 * 
	 * @return void
	 */
	function forced_cron( $sec = -85 ) {
		if ( defined( 'Y4YM_CHECK_CRON' ) && false === Y4YM_CHECK_CRON ) {
			$cron_arr = _get_cron_array();
			// array(25) {
			// 	[1729495534] => array(1) {
			//		["yfym_cron_period"] => array(1) {
			//			["099b16b94d8670ab53a3e075f2e61215"] => array(3) {
			//					["schedule"] => string(6) "hourly"
			//					["args"] => array(1) {
			//					[0] => string(1) "1"
			//				}
			//				["interval"] => int(3600)
			//			}
			//		}
			// 	}
			// }
			if ( ! empty( $cron_arr ) ) {
				$first_key = array_key_first( $cron_arr );
				if ( $sec > $first_key - current_time( 'timestamp', 1 ) ) {
					wp_remote_get( home_url() . '/wp-cron.php?doing_wp_cron' );
					error_log( sprintf( 'WARNING: %s. %s `define( "Y4YM_CHECK_CRON", false );` %s wp-config.php',
						'The CRON force start function has been activated',
						'To disable it, use a constant',
						'in your'
					), 0 );
				}
			}
		}
	}
}