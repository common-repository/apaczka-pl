<?php

namespace Inspire_Labs\Apaczka_Woocommerce\Plugin;

trait Tools {

	public function require_wp_core_file( string $path ) {
		require_once ABSPATH . $path;
	}
}