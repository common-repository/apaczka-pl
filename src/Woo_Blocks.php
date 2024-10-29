<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Woo_Blocks implements IntegrationInterface {


	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'apaczka-woo-blocks';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		$plugin     = new Plugin();
		$script_url = $plugin->get_plugin_js_url() . '/blocks/apaczka-pl-block.js';

		$dep = array(
			'dependencies' => array( 'wc-settings', 'wp-data', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-primitives' ),
			'version'      => '1.2.2',
		);

		$script_asset = $dep;

		wp_register_script(
			'apaczka-pl-wc-blocks-integration',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'apaczka-pl-wc-blocks-integration' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'apaczka-pl-wc-blocks-integration' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return array(
			'apaczka' => 'wc-data-apaczka',
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return '1.2.2';
	}
}
