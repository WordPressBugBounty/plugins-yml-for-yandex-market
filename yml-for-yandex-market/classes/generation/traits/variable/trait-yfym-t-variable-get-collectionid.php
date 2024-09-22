<?php
/**
 * Traits CollectionId for variable products
 *
 * @package                 YML for Yandex Market
 * @subpackage              
 * @since                   4.3.3
 * 
 * @version                 4.3.3 (14-05-2024)
 * @author                  Maxim Glazunov
 * @link                    https://icopydoc.ru/
 * @see                     
 *
 * @depends					classes:    Get_Paired_Tag
 *                          traits:     
 *                          methods:    get_product
 *                                      get_offer
 *                                      get_feed_id
 *                          functions:  common_option_get
 *                          constants:  
 */
defined( 'ABSPATH' ) || exit;

trait YFYM_T_Variable_Get_CollectionId {
	/**
	 * Summary of get_collectionid
	 * 
	 * @param string $tag_name - Optional
	 * @param string $result_xml - Optional
	 * 
	 * @return string
	 */
	public function get_collection_id( $tag_name = 'collectionId', $result_xml = '' ) {
		$yfym_collection_id = common_option_get( 'yfym_collection_id', false, $this->get_feed_id(), 'yfym' );
		if ( 'enabled' === $yfym_collection_id ) {
			$collections_arr = get_the_terms( $this->get_product()->get_id(), 'yfym_collection' );
			if ( is_array( $collections_arr ) ) {
				foreach ( $collections_arr as $cur_collection ) {
					$result_xml .= new Get_Paired_Tag( $tag_name, $cur_collection->term_id );
				}
			}

			$result_xml = apply_filters(
				'y4ym_f_variable_tag_collectionid',
				$result_xml,
				[ 
					'product' => $this->get_product(),
					'offer' => $this->get_offer()
				],
				$this->get_feed_id()
			);
		}
		return $result_xml;
	}
}