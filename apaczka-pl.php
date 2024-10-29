<?php
/**
 * Plugin Name: Apaczka.pl
 * Plugin URI: https://www.apaczka.com/zostan-sprzedawca
 * Description: Nadawaj przesyłki za pośrednictwem Apaczka.pl bezpośrednio z panelu swojego sklepu
 * Product: Apaczka Woocommerce
 * Version: 1.2.6
 * Tested up to: 6.6
 * Requires at least: 5.3
 * Requires PHP: 7.2
 * Author: iLabs LTD
 * Author URI: https://iLabs.dev
 * Text Domain: apaczka-pl
 * Domain Path: /languages/
 *
 * Copyright 2022 iLabs LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

function apaczka(): \Inspire_Labs\Apaczka_Woocommerce\Plugin {
	return new \Inspire_Labs\Apaczka_Woocommerce\Plugin();
}

$config = array(
	'__FILE__'    => __FILE__,
	'slug'        => 'apaczka_woocommerce',
	'lang_dir'    => 'lang',
	'text_domain' => 'apaczka-pl',
);

// Check if WooCommerce is active.
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	apaczka()->execute( $config );
	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	);

} else {
	add_action( 'admin_notices', 'apaczka_woo_needed_notice' );
	return;
}

function apaczka_woo_needed_notice() {
	$message = sprintf(
	/* translators: Placeholders: %1$s and %2$s are <strong> tags. %3$s and %4$s are <a> tags */
		esc_html__( '%1$sApaczka WooCommerce 2.0 %2$s requires WooCommerce to function.', 'apaczka-pl' ),
		'<strong>',
		'</strong>'
	);
	printf( '<div class="error"><p>%s</p></div>', esc_html( $message ) );
}

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		$plugin_links = array(
			'<a href="/wp-admin/admin.php?page=wc-settings&tab=apaczka_woocommerce_settings_general">' . __( 'Settings', 'apaczka-pl' ) . '</a>',
		);

		return array_merge( $links, $plugin_links );
	}
);
