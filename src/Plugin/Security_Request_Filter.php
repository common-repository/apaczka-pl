<?php

namespace Inspire_Labs\Apaczka_Woocommerce\Plugin;

class Security_Request_Filter implements Request_Filter_Interface {

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return array|string
	 */
	public function filter( $key, $value ) {
		if ( ! is_array( $value ) ) {
			return sanitize_text_field( $value );
		}

		return $value;
	}
}
