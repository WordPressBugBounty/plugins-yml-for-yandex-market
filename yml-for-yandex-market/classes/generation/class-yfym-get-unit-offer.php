<?php
/**
 * The abstract class for getting the XML-code or skip reasons
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   0.1.0
 * 
 * @version                 4.3.4 (22-05-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @param      array        $args_arr - Required
 * 
 * @depends                 classes:    YFYM_Error_Log
 *                          traits:     YFYM_T_Get_Post_Id
 *                                      YFYM_T_Get_Feed_Id;
 *                                      YFYM_T_Get_Product
 *                                      YFYM_T_Get_Skip_Reasons_Arr
 *                                      Y4YM_Rules_List
 *                          methods:    
 *                          functions:  common_option_get
 *                          constants:  
 *                          options:    
 */
defined( 'ABSPATH' ) || exit;

abstract class YFYM_Get_Unit_Offer {
	use YFYM_T_Get_Feed_Id;
	use YFYM_T_Get_Product;
	use YFYM_T_Get_Skip_Reasons_Arr;

	/**
	 * Summary of feed_price
	 * @var 
	 */
	public $feed_price;
	/**
	 * Summary of input_data_arr
	 * @var array
	 */
	protected $input_data_arr; // массив, который пришёл в класс. Этот массив используется в фильтрах трейтов
	/**
	 * Summary of offer
	 * @var object
	 */
	protected $offer = null;
	/**
	 * Summary of variation_count
	 * @var int
	 */
	protected $variation_count = null;
	/**
	 * Summary of variations_arr
	 * @var array
	 */
	protected $variations_arr = null;

	/**
	 * Summary of result_product_xml
	 * @var string
	 */
	protected $result_product_xml;
	/**
	 * Summary of do_empty_product_xml
	 * @var bool
	 */
	protected $do_empty_product_xml = false;

	/**
	 * @param array $args_arr [
	 *	'feed_id' 			- string - Required
	 *	'product' 			- object - Required
	 *	'offer' 			- object - Optional
	 *	'variation_count' 	- int - Optional
	 * ]
	 */
	public function __construct( $args_arr ) {
		// без этого не будет работать вне адмники is_plugin_active
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$this->input_data_arr = $args_arr;
		$this->feed_id = (string) $args_arr['feed_id'];
		$this->product = $args_arr['product'];

		if ( isset( $args_arr['offer'] ) ) {
			$this->offer = $args_arr['offer'];
		}
		if ( isset( $args_arr['variation_count'] ) ) {
			$this->variation_count = $args_arr['variation_count'];
		} else {
			$this->variation_count = null;
		}

		$r = $this->generation_product_xml();

		// если нет нужды пропускать
		if ( empty( $this->get_skip_reasons_arr() ) ) {
			$this->result_product_xml = $r;
		} else {
			// !!! - тут нужно ещё раз подумать и проверить
			// с простыми товарами всё чётко
			$this->result_product_xml = '';
			if ( null == $this->get_offer() ) { // если прстой товар - всё чётко
				$this->set_do_empty_product_xml( true );
			} else {
				// если у нас вариативный товар, то как быть, если все вариации пропущены
				// мы то возвращаем false (см ниже), возможно надо ещё вести учёт вариций
				// также см функцию set_result() в классе class-yfym-get-unit.php
				$this->set_do_empty_product_xml( false );
			}
		}
	}

	/**
	 * Generation product XML
	 * 
	 * @return string
	 */
	abstract public function generation_product_xml();

	/**
	 * Get product XML
	 * 
	 * @return string
	 */
	public function get_product_xml() {
		return $this->result_product_xml;
	}

	/**
	 * Set `do_empty_product_xml` flag
	 * 
	 * @param bool $v
	 * 
	 * @return void
	 */
	public function set_do_empty_product_xml( $v ) {
		$this->do_empty_product_xml = $v;
	}

	/**
	 * Summary of get_do_empty_product_xml
	 * 
	 * @return bool|mixed
	 */
	public function get_do_empty_product_xml() {
		return $this->do_empty_product_xml;
	}

	/**
	 * Summary of get_feed_price
	 * 
	 * @return mixed
	 */
	public function get_feed_price() {
		return $this->feed_price;
	}

	/**
	 * Add skip reason
	 * 
	 * @param array $reason
	 * 
	 * @return void
	 */
	protected function add_skip_reason( $reason ) {
		if ( isset( $reason['offer_id'] ) ) {
			$reason_string = sprintf(
				'FEED № %1$s; Вариация товара (post_id = %2$s, offer_id = %3$s) пропущена. Причина: %4$s; Файл: %5$s; Строка: %6$s',
				$this->feed_id, $reason['post_id'], $reason['offer_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		} else {
			$reason_string = sprintf(
				'FEED № %1$s; Товар с postId = %2$s пропущен. Причина: %3$s; Файл: %4$s; Строка: %5$s',
				$this->feed_id, $reason['post_id'], $reason['reason'], $reason['file'], $reason['line']
			);
		}
		$this->set_skip_reasons_arr( $reason_string );
		new YFYM_Error_Log( $reason_string );
	}

	/**
	 * Get input_data_arr
	 * 
	 * @return array
	 */
	protected function get_input_data_arr() {
		return $this->input_data_arr;
	}

	/**
	 * Get offer
	 * 
	 * @return WC_Product_Variation
	 */
	protected function get_offer() {
		return $this->offer;
	}

	/**
	 * Get tags
	 * 
	 * @param string $rules
	 * @param string $result_xml
	 * 
	 * @return string
	 */
	protected function get_tags( $rules, $result_xml = '' ) {
		$rules_obj = new Y4YM_Rules_List();
		$rules_arr = $rules_obj->get_rules_arr();

		if ( isset( $rules_arr[ $rules ] ) ) {
			for ( $i = 0; $i < count( $rules_arr[ $rules ] ); $i++ ) {
				if ( $rules_arr[ $rules ][ $i ] === 'currencies' ) {
					// этот пропуск нужен потому, что get_currencies срабатывает в шапке фида
					continue;
				}
				if ( $rules_arr[ $rules ][ $i ] === 'oldprice' ) {
					// этот пропуск нужен потому, что get_oldprice срабатывает позже в этой же функции
					continue;
				}

				$func_name = 'get_' . $rules_arr[ $rules ][ $i ];
				$result_xml .= $this->$func_name();
			}
		}
		if ( class_exists( 'WOOCS' ) ) {
			$yfym_wooc_currencies = common_option_get( 'yfym_wooc_currencies', false, $this->get_feed_id(), 'yfym' );
			if ( $yfym_wooc_currencies !== '' ) {
				global $WOOCS;
				$WOOCS->set_currency( $yfym_wooc_currencies );
			}
		}

		$p = $this->get_price();
		if ( $p !== '' ) {
			$result_xml .= $p;
			$result_xml .= $this->get_currencyid();
		}
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$WOOCS->reset_currency();
		}
		return $result_xml;
	}
}