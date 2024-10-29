<?php
/**
 * Plugin main class.
 *
 * @package Inspire_Labs\Apaczka_Woocommerce
 */

namespace Inspire_Labs\Apaczka_Woocommerce;

use Exception;
use Inspire_Labs\Apaczka_Woocommerce\Plugin\Abstract_Ilabs_Plugin;
use WC_Order;

class Plugin extends Abstract_Ilabs_Plugin {

	const TEXTDOMAIN = 'apaczka-pl';

	const APP_PREFIX = 'apaczka_woocommerce';

	public static $plugin_dir;

	public $shipping_methods = array();


	public function init() {
		$this->require_wp_core_file( 'wp-admin/includes/class-wp-filesystem-base.php' );
		$this->require_wp_core_file( 'wp-admin/includes/class-wp-filesystem-direct.php' );

		if ( is_admin() ) {
			$this->init_admin_features();
		}
	}

	protected function register_request_filters(): array {
		return array();
	}

	protected function before_init() {
	}

	protected function plugins_loaded_hooks() {
		$this->shipping_methods['apaczka'] = new Shipping_Method_Apaczka();

		if ( is_admin() ) {
			( new Apaczka_Shipping_Rates() )->init();
			add_filter(
				'woocommerce_shipping_methods',
				array( $this, 'woocommerce_shipping_methods' ),
				20,
				1
			);
		}

		if ( ! is_plugin_active( 'apaczka-pl-mapa-punktow/apaczka-points-map.php' ) ) {
			add_action(
				'woocommerce_init',
				function () {
					$this->shipping_methods['apaczka']->filtering_shipping_fields();
				}
			);
		}
	}


	/**
	 * @param $key
	 *
	 * @return false|mixed|void
	 */
	public static function get_option( $key ) {
		return get_option(
			self::APP_PREFIX
			. '_settings_general' . '_' . $key
		);
	}

	public function woocommerce_shipping_methods( $methods ) {
		$methods[ $this->shipping_methods['apaczka']->id ]
			= get_class( $this->shipping_methods['apaczka'] );

		return $methods;
	}

	private function init_admin_features() {
		add_action( 'woocommerce_settings_saved', array( $this, 'save_post' ) );
		add_filter(
			'woocommerce_get_settings_pages',
			function ( $woocommerce_settings ) {
				new Global_Settings_Integration();

				return $woocommerce_settings;
			}
		);

		( new Ajax() )->init();

		if ( ! class_exists( 'Apaczka_Points_Map\Points_Map_Plugin' ) ) {
			add_action(
				'woocommerce_admin_order_data_after_shipping_address',
				function ( WC_Order $order ) {
					$apaczka_delivery_point = get_post_meta(
						$order->get_id(),
						'apaczka_delivery_point',
						true
					);

					if ( ! empty( $apaczka_delivery_point ) ) {
						echo '<div class="order_data_column"><h4>' . __(
							'Others:',
							'apaczka-pl'
						) . '</h4><p><strong>'
							. __( 'Delivery point', 'apaczka-pl' )
							. ': </strong>'
							. esc_attr( $apaczka_delivery_point['apm_access_point_id'] )
							. ' (' . esc_attr( $apaczka_delivery_point['apm_supplier'] ) . '. ' . esc_attr( $apaczka_delivery_point['apm_name'] ) . ')'
							. '</p></div>';
					}
				},
				100
			);
		}
	}

	public function save_post() {
		update_option( 'apaczka_countries_cache', '' );
	}


	/**
	 * @return string
	 */
	private function get_admin_script_id(): string {
		return self::APP_PREFIX . '_admin-js';
	}

	private function get_admin_css_id(): string {
		return self::APP_PREFIX . '_admin-css';
	}

	public function get_front_blocks_script_id(): string {
		return self::APP_PREFIX . '_front_blocks-js';
	}

	public function enqueue_frontend_scripts() {
		if ( ! class_exists( 'Apaczka_Points_Map\Points_Map_Plugin' ) ) {
			if ( is_checkout() ) {
				wp_enqueue_script(
					self::APP_PREFIX . '_apaczka.map.js',
					'https://mapa.apaczka.pl/client/apaczka.map.js'
				);

				wp_enqueue_style(
					$this->get_admin_css_id(),
					$this->get_plugin_css_url() . '/front.css'
				);
			}
		}
	}

	public function enqueue_dashboard_scripts() {

		if ( $this->is_required_pages() ) {

			wp_enqueue_style(
				$this->get_admin_css_id(),
				$this->get_plugin_css_url() . '/admin.css'
			);

			wp_enqueue_script(
				'jquery_maskedinput',
				$this->get_plugin_js_url() . '/jquery.maskedinput.js'
			);

			$current_screen = get_current_screen();
			$admin_js_path  = $this->get_plugin_dir() . 'assets/js/admin.js';

			if ( is_a( $current_screen, 'WP_Screen' ) && 'woocommerce_page_wc-settings' === $current_screen->id ) {
				if ( isset( $_GET['tab'] ) && 'apaczka_woocommerce_settings_general' === $_GET['tab'] ) {

					wp_enqueue_script(
						$this->get_admin_script_id(),
						$this->get_plugin_js_url() . '/admin.js',
						array( 'jquery' ),
						file_exists( $admin_js_path ) ? filemtime( $admin_js_path ) : '1.2.4'
					);
				}
			} else {
				// avoid JS errors in console on Order edit page.
				wp_enqueue_script(
					$this->get_admin_script_id(),
					$this->get_plugin_js_url() . '/admin.js',
					array( 'jquery', 'mediaelement', 'wc-admin-order-meta-boxes' ),
					file_exists( $admin_js_path ) ? filemtime( $admin_js_path ) : '1.2.4'
				);
			}

			wp_enqueue_script(
				self::APP_PREFIX . '_apaczka.map.js',
				'https://mapa.apaczka.pl/client/apaczka.map.js'
			);

		}
	}

	public function is_required_pages() {
		global $pagenow;

		if ( isset( $_GET['post'] ) && ! empty( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
			$post_type = get_post_type( $_GET['post'] );
			if ( 'product' === $post_type ) {
				return false;
			}
		}

		$current_screen = get_current_screen();

		if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
			return true;
		}

		if ( is_a( $current_screen, 'WP_Screen' ) && 'woocommerce_page_wc-settings' === $current_screen->id ) {
			if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'apaczka_woocommerce_settings_general' ) {
				return true;
			}
		}

		if ( is_a( $current_screen, 'WP_Screen' ) && 'woocommerce_page_wc-orders' === $current_screen->id ) {
			if ( isset( $_GET['id'] ) ) {
				return true;
			}
		}

		return false;
	}
}
