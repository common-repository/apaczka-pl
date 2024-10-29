<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

use Exception;
use WC_Order;
use WC_Shipping_Method;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


class Shipping_Method_Apaczka extends WC_Shipping_Method {

	const APACZKA_PICKUP_COURIER = 1;

	const APACZKA_PICKUP_SELF = 2;

	const APACZKA_PICKUP_PARCEL_MACHINE = 3;

	const PARCEL_LOCKER_GEOWIDGET_IDS = array(
		14  => 'UPS',
		15  => 'UPS',
		16  => 'UPS',
		23  => 'DPD',
		26  => 'DPD',
		50  => 'PWR',
		41  => 'INPOST',
		163 => 'EKSPRES24',
		86  => 'DHL',
		162 => 'KURIER48',
	);

	public $api = false;

	static $services = array();

	static $order_status_completed_auto;
	static $review_order_after_shipping_once = false;
	static $instance_options                 = array();

	private $fields = array();
	/**
	 * @var mixed
	 */
	private $geowidget_supplier;
	/**
	 * @var mixed
	 */
	private $geowidget_only_cod;

	/**
	 * @var string
	 */
	private $login;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $api_key;

	/**
	 * @var mixed
	 */
	private $cost;

	/**
	 * @var mixed
	 */
	private $cost_cod;

	/**
	 * @var mixed
	 */
	private $cost_per_order;

	/**
	 * @var mixed
	 */
	private $flat_rate;

	/**
	 * @var mixed
	 */
	private $free_shipping_cost;


	/**
	 * Constructor for your shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {

		$this->instance_id = absint( $instance_id );
		$this->id          = 'apaczka';
		$this->enabled     = 'yes';
		self::$services    = array();

		$this->supports = array(
			'instance-settings',
		);

		$this->method_title       = 'Apaczka';
		$this->method_description
			= __(
				' Register on <a href="https://www.apaczka.pl/?register=1&register_promo_code=WooCommerce" target="_blank">www.apaczka.pl &rarr;</a>',
				'apaczka-pl'
			);

		$this->title = $this->get_option( 'title' );

		self::$order_status_completed_auto
			= $this->get_option( 'order_status_completed_auto' );
		$this->init();
		$this->get_waybill();
		add_action(
			'woocommerce_update_options_shipping_' . $this->id,
			array( $this, 'process_admin_options' )
		);

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

		add_action(
			'woocommerce_checkout_update_order_meta',
			array( $this, 'woocommerce_checkout_update_order_meta' ),
			100,
			2
		);

		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_post' ) );

		add_action(
			'woocommerce_after_checkout_validation',
			array( $this, 'woocommerce_checkout_process' ),
			10,
			2
		);

		add_action( 'admin_footer', array( $this, 'cancel_package_popup' ) );

		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'save_shipping_point_in_order_meta' ), 10, 2 );

		if ( ! is_plugin_active( 'apaczka-pl-mapa-punktow/apaczka-points-map.php' ) ) {
			add_action(
				'woocommerce_review_order_after_shipping',
				array( $this, 'woocommerce_review_order_after_shipping' )
			);

			// integration with Woocommerce blocks start.
			add_action(
				'woocommerce_blocks_checkout_block_registration',
				function ( $integration_registry ) {
					if ( ! $integration_registry->is_registered( 'apaczka-woo-blocks' ) ) {
						$integration_registry->register( new Woo_Blocks() );
					}
				}
			);

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_blocks_scripts' ) );			
			// integration with Woocommerce blocks end.
		}
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );

		$this->login              = $this->get_option( 'login' );
		$this->password           = $this->get_option( 'password' );
		$this->api_key            = $this->get_option( 'api_key' );
		$this->cost               = $this->get_option( 'cost' );
		$this->cost_cod           = $this->get_option( 'cost_cod' );
		$this->free_shipping_cost
			= 1.9;
		$this->flat_rate          = 1.9;
		$this->cost_per_order     = 1.9;
	}

	/**
	 * @param int $service_id service_id.
	 *
	 * @return bool
	 */
	private function is_parcel_locker_service( int $service_id ): bool {
		return key_exists( $service_id, self::PARCEL_LOCKER_GEOWIDGET_IDS );
	}


	public function woocommerce_checkout_process() {

		$cart_contents = array();
		if ( is_object( WC()->session ) ) {
			$cart_contents = WC()->session->get( 'cart' );
		}

		$physical_product_in_cart = false;

		if ( ! empty( $cart_contents ) ) {
			foreach ( $cart_contents as $cart_key => $value ) {
				if ( isset( $value['product_id'] ) && ! empty( $value['product_id'] ) ) {
					$product = wc_get_product( $value['product_id'] );

					if ( $product && ! $product->is_virtual() ) {
						$physical_product_in_cart = true;
					}
				}
			}
		}

		if ( $this->is_delivery_map_button_display() && $physical_product_in_cart ) {
			if ( empty( apaczka()->get_request()->get_by_key( 'apm_access_point_id' ) ) ) {
				wc_add_notice( __( 'Parcel locker must be choosen.', 'apaczka-pl' ), 'error' );
			}
		}
	}

	/**
	 * @return void
	 */
	public function woocommerce_review_order_after_shipping() {
		if ( $this->is_delivery_map_button_display() && ! self::$review_order_after_shipping_once ) {
			$point_id = $this->geowidget_supplier;
			$only_cod = 'yes' === $this->geowidget_only_cod ? true : false;
			wc_get_template(
				'checkout/apaczka-review-order-after-shipping.php',
				array(
					'point_id' => $point_id,
					'only_cod' => $only_cod,
				),
				'',
				apaczka()->get_plugin_templates_dir( true )
			);
			self::$review_order_after_shipping_once = true;
		}
	}

	public function add_meta_boxes( $post_type, $post ) {

		$apaczka_wc_order_data  = array();
		$apaczka_delivery_point = array();

		$show_apaczka_metabox = false;

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
			if ( 'woocommerce_page_wc-orders' === $post_type ) {

				$order_id = $post->get_id();
				$order    = wc_get_order( $order_id );

				$apaczka_wc_order_data_raw = isset( get_post_meta( $order_id )['_apaczka'][0] )
					? get_post_meta( $order_id )['_apaczka'][0]
					: '';

				if ( ! empty( $apaczka_wc_order_data_raw ) ) {
					$apaczka_wc_order_data = unserialize( $apaczka_wc_order_data_raw );
				}

				if ( empty( $apaczka_wc_order_data ) ) {
					return;
				}

				$apaczka_delivery_point_raw = isset( get_post_meta( $order_id )['apaczka_delivery_point'][0] )
					? get_post_meta( $order_id )['apaczka_delivery_point'][0]
					: '';

				if ( ! empty( $apaczka_delivery_point_raw ) ) {
					$apaczka_delivery_point = unserialize( $apaczka_delivery_point_raw );
				}

				$show_apaczka_metabox = true;

			}
		} else {

			global $post;

			if ( is_object( $post ) ) {

				$order_id  = $post->ID;
				$post_type = get_post_type( $order_id );

				if ( $post_type == 'shop_order' ) {

					$order                 = wc_get_order( $order_id );
					$apaczka_wc_order_data = get_post_meta( $order_id, '_apaczka', true );

					if ( empty( $apaczka_wc_order_data ) ) {
						return;
					}

					$apaczka_delivery_point = get_post_meta( $order_id, 'apaczka_delivery_point', true );
					$show_apaczka_metabox   = true;

				}
			}
		}

		if ( $show_apaczka_metabox ) {

			add_meta_box(
				$this->id,
				__( 'Apaczka.pl', 'apaczka-pl' ),
				array( $this, 'order_metabox' ),
				null,
				'normal',
				'default',
				array(
					'wc_order_apaczka_meta_data'          => $apaczka_wc_order_data,
					'apaczka_delivery_point'              => ! empty( $apaczka_delivery_point ) ? $apaczka_delivery_point : null,
					'sender_templates'                    => ( new Sender_Settings_Templates_Helper() )
						->get_all_templates_list(),
					'sender_templates_json'               => ( new Sender_Settings_Templates_Helper() )
						->get_all_templates_json(),
					'package_properties_templates'        => ( new Gateway_Settings_Templates_Helper() )
						->get_all_templates_list(),
					'package_properties_templates_json'   => ( new Gateway_Settings_Templates_Helper() )
						->get_all_templates_json(),
					'package_properties_services'         => self::get_services(),
					'package_properties_parcel_types'     =>
						$this->fields['parcel_type']['options'],
					'package_properties_shipping_methods' =>
						$this->fields['shipping_method']['options'],
					'package_properties_hours'            =>
						$this->fields['pickup_hour_from']['options'],
				)
			);
		}
	}

	/**
	 * Initialise Settings Form Fields
	 */
	public function init_form_fields() {

		$options_hours = array();
		for ( $h = 9; $h < 20; $h++ ) {
			$options_hours[ $h . ':00' ] = $h . ':00';
			if ( $h < 19 ) {
				$options_hours[ $h . ':30' ] = $h . ':30';
			}
		}

		$this->fields = array(
			array(
				'title'                    => __(
					'Method settings',
					'apaczka-pl'
				),
				'type'                     => 'title',
				'description'              => '',
				'id'                       => 'section_general_settings',
				'visible_on_order_details' => false,
			),
			'title'                  => array(
				'title'                    => __(
					'Method title',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'default'                  => __(
					'Method title',
					'apaczka-pl'
				),
				'desc_tip'                 => false,
				'visible_on_order_details' => false,
			),

			'cod'                    => array(
				'title'                    => __(
					'COD method',
					'apaczka-pl'
				),
				'type'                     => 'checkbox',
				'label'                    => __(
					'',
					'apaczka-pl'
				),
				'default'                  => 'no',
				'visible_on_order_details' => false,
			),
			'declared_content'       => array(
				'title'                    => __(
					'Declared value',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'label'                    => __(
					'',
					'apaczka-pl'
				),
				'visible_on_order_details' => true,
			),
			'insurance'              => array(
				'title'                    => __(
					'Insurance',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => array(
					'yes' => __( 'Tak', 'apaczka-pl' ),
					'no'  => __( 'Nie', 'apaczka-pl' ),
				),
				'visible_on_order_details' => true,
			),
			array(
				'title'                    => __(
					'Default shipping settings',
					'apaczka-pl'
				),
				'type'                     => 'title',
				'description'              => '',
				'visible_on_order_details' => false,
			),
			'service'                => array(
				'title'                    => __(
					'Service',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => self::get_services(),
				'visible_on_order_details' => true,
			),

			'parcel_type'            => array(
				'title'                    => __(
					'Parcel type',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'desc_tip'                 => __( '' ),
				'options'                  => array(
					'box'             => __(
						'Box',
						'apaczka-pl'
					),
					'europalette'     => __(
						'Europalette',
						'apaczka-pl'
					),
					'palette_60x80'   => __(
						'Palette 60x80',
						'apaczka-pl'
					),
					'palette_120x100' => __(
						'Palette 120x100',
						'apaczka-pl'
					),
					'palette_120x120' => __(
						'Palette 120x120',
						'apaczka-pl'
					),
				),
				'visible_on_order_details' => true,
			),

			'is_nstd'                => array(
				'title'                    => __(
					'Non standard package',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => 'no',
				'desc_tip'                 => true,
				'options'                  => array(
					'yes' => __( 'Yes', 'apaczka-pl' ),
					'no'  => __( 'No', 'apaczka-pl' ),
				),
				'visible_on_order_details' => true,
			),

			'shipping_method'        => array(
				'title'                    => __(
					'Shipping method',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'desc_tip'                 => true,
				'options'                  => array(
					'POINT'   => __(
						'Shipment directly at the point',
						'apaczka-pl'
					),
					'COURIER' => __(
						'Courier pickup request',
						'apaczka-pl'
					),
					'SELF'    => __(
						'Pickup self',
						'apaczka-pl'
					),
				),
				'visible_on_order_details' => true,
			),

			'pickup_hour_from'       => array(
				'title'                    => __(
					'Pickup hour from',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => $options_hours,
				'visible_on_order_details' => true,
			),
			'pickup_hour_to'         => array(
				'title'                    => __(
					'Pickup hour to',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => $options_hours,
				'visible_on_order_details' => true,
			),

			'dispath_point_inpost'   => array(
				'title'                    => __(
					'Default dispatch point (InPost)',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			),

			'dispath_point_kurier48' => array(
				'title'                    => __(
					'Default dispatch point (Kurier48)',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			),
			'dispath_point_ups'      => array(
				'title'                    => __(
					'Default dispatch point (UPS)',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			),
			'dispath_point_dpd'      => array(
				'title'                    => __(
					'Default dispatch point (DPD)',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			),

			'package_width'          => array(
				'title'                    => __(
					'Package length [cm]',
					'apaczka-pl'
				),
				'type'                     => 'number',
				'description'              => __(
					'Package length [cm].',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => array(
					'min'      => 0,
					'max'      => 10000,
					'step'     => 1,
					'required' => 'required',
				),
				'visible_on_order_details' => true,
			),
			'package_depth'          => array(
				'title'                    => __(
					'Package width [cm]',
					'apaczka-pl'
				),
				'type'                     => 'number',
				'description'              => __(
					'Package width [cm].',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => array(
					'min'      => 0,
					'max'      => 10000,
					'step'     => 1,
					'required' => 'required',
				),
				'visible_on_order_details' => true,
			),
			'package_height'         => array(
				'title'                    => __(
					'Package height [cm]',
					'apaczka-pl'
				),
				'type'                     => 'number',
				'description'              => __(
					'Package height [cm].',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => array(
					'min'      => 0,
					'max'      => 10000,
					'step'     => 1,
					'required' => 'required',
				),
				'visible_on_order_details' => true,
			),
			'package_weight'         => array(
				'title'                    => __(
					'Package weight [kg]',
					'apaczka-pl'
				),
				'type'                     => 'number',
				'description'              => __(
					'Package weight [kg].',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => array(
					'min'      => 0,
					'max'      => 10000,
					'step'     => 'any',
					'required' => 'required',
				),
				'visible_on_order_details' => true,
			),
			'package_contents'       => array(
				'title'                    => __(
					'Package contents',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			),

			'create_template'        => array(
				'title'                    => __(
					'Create new template from this settings?',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => 'no',
				'desc_tip'                 => true,
				'options'                  => array(
					'no'  => __( 'Nie', 'apaczka-pl' ),
					'yes' => __( 'Tak', 'apaczka-pl' ),
				),
				'visible_on_order_details' => false,
			),

			'new_template_name'      => array(
				'title'                    => __(
					'New template name',
					'apaczka-pl'
				),
				'type'                     => 'text',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => false,
			),

			'select_template'        => array(
				'title'                    => __(
					'Choose template to load',
					'apaczka-pl'
				),
				'type'                     => 'select',
				'description'              => __(
					'',
					'apaczka-pl'
				),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => ( new Gateway_Settings_Templates_Helper() )->get_all_templates_list(),
				'visible_on_order_details' => true,
			),
			'load_from_template'     => array(
				'name'                     => __( '', '' ),
				'title'                    => __(
					'',
					'apaczka-pl'
				),
				'type'                     => 'load_from_template',
				'id'                       => 'load_from_template',
				'visible_on_order_details' => true,
			),

		);

		$form_settings = array(
			'cod' => array(
				'title'                    => __(
					'COD method',
					'apaczka-pl'
				),
				'type'                     => 'checkbox',
				'label'                    => __(
					'',
					'apaczka-pl'
				),
				'default'                  => 'no',
				'visible_on_order_details' => false,
			),
		);

		$this->form_fields = $form_settings;
	}

	public function order_metabox( $post, $metabox_data ) {
		self::order_metabox_content( $post, $metabox_data );
	}

	/**
	 * @return array
	 */
	public static function get_services(): array {
		$app_id     = Plugin::get_option( 'app_id' );
		$app_secret = Plugin::get_option( 'app_secret' );
		if ( empty( $app_id ) || empty( $app_secret ) ) {
			return array();
		}

		$return   = array();
		$services = ( new Service_Structure_Helper() )->get_services();

		if ( ! is_array( $services ) ) {
			return array();
		}
		foreach ( ( new Service_Structure_Helper() )->get_services() as $service ) {
			$return [ $service->service_id ] = $service->name;
		}

		return $return;
	}


	private static function set_defaults_to_wc_order_data(
		array $apaczka_wc_order_data,
		WC_Order $order
	): array {

		// var_dump($apaczka_wc_order_data['package_properties']);die;
		// sender
		// Package properties
		// Additional options

		$payment_method = $order->get_payment_method();

		if ( empty( $apaczka_wc_order_data['additional_options']['point'] ) ) {
			$apaczka_wc_order_data['additional_options']['point'] = $point = self::get_point_from_order( $order );
		}

		if ( empty( $apaczka_wc_order_data['additional_options']['cod_amount'] ) ) {

			if ( 'cod' === $payment_method ) {
				$apaczka_wc_order_data['additional_options']['cod_amount'] = $order->get_total();
			} else {
				$apaczka_wc_order_data['additional_options']['cod_amount'] = 0;
			}
		}

		$sender = ( new Global_Settings() )->get_current_sender_config();
		if ( ! isset( $apaczka_wc_order_data['sender'] ) ) {
			$apaczka_wc_order_data['sender'] = $sender;
		}

		if ( empty( $apaczka_wc_order_data['package_properties']['declared_content'] ) ) {

			if ( 'cod' === $payment_method ) {
				$apaczka_wc_order_data['package_properties']['declared_content'] = $order->get_total();
			} elseif ( 'yes' === Plugin::get_option( 'declared_content_auto' ) ) {
					$apaczka_wc_order_data['package_properties']['declared_content'] = $order->get_total();
			} else {
				$apaczka_wc_order_data['package_properties']['declared_content'] = 0;
			}
		}

		if ( empty( $apaczka_wc_order_data['package_properties']['pickup_date'] ) ) {
			$apaczka_wc_order_data['package_properties']['pickup_date'] = date( 'Y-m-d' );
		}

		if ( empty( $apaczka_wc_order_data['receiver']['phone'] ) ) {
			$apaczka_wc_order_data['receiver']['phone'] = empty( $order->get_shipping_phone() )
				? $order->get_billing_phone()
				: $order->get_shipping_phone();
		}

		return $apaczka_wc_order_data;
	}

	/**
	 * @param int $apaczka_order_id apaczka_order_id.
	 *
	 * @return string
	 */
	public static function get_apaczka_order_status( int $apaczka_order_id ): ?string {
		$api_order = ( new Web_Api_V2() )->order( $apaczka_order_id );
		if ( isset( $api_order->order->status ) ) {

			return $api_order->order->status;
		}

		return null;
	}

	public static function order_metabox_content(
		$post,
		$metabox_data,
		$output = true
	) {

		if ( ! $output ) {
			ob_start();
		}

		if ( is_a( $post, 'WC_Order' ) ) {
			$order_id = $post->get_id();
		} else {
			$order_id = $post->ID;
		}

		$order = wc_get_order( $order_id );

		// $service = $order->get_meta( 'service' );
		// $has_order_parcel_machine = $order->get_meta( '_apaczka_parcel_machine_id' );

		$apaczka_wc_order_data               = $metabox_data['args']['wc_order_apaczka_meta_data'];
		$sender_templates                    = $metabox_data['args']['sender_templates'];
		$sender_templates_json               = $metabox_data['args']['sender_templates_json'];
		$package_properties_templates        = $metabox_data['args']['package_properties_templates'];
		$package_properties_templates_json   = $metabox_data['args']['package_properties_templates_json'];
		$package_properties_services         = $metabox_data['args']['package_properties_services'];
		$package_properties_parcel_types     = $metabox_data['args']['package_properties_parcel_types'];
		$package_properties_shipping_methods = $metabox_data['args']['package_properties_shipping_methods'];
		$package_properties_hours            = $metabox_data['args']['package_properties_hours'];
		$apaczka_delivery_point              = $metabox_data['args']['apaczka_delivery_point'];

		$services     = self::get_services();
		$package_send = false;

		$apaczka_wc_order_data = self::set_defaults_to_wc_order_data(
			$apaczka_wc_order_data,
			$order
		);

		if ( isset( $apaczka_wc_order_data['package_send'] )
			&& $apaczka_wc_order_data['package_send'] === 1 ) {
			if ( isset( $apaczka_wc_order_data['apaczka_response']->order->id ) ) {
				$apaczka_order_id = $apaczka_wc_order_data['apaczka_response']->order->id;
				$status           = self::get_apaczka_order_status( $apaczka_order_id );
				if ( 'CANCELLED' === $status ) {
					$apaczka_wc_order_data['package_send'] = 0;
					$meta_data                             = get_post_meta( $order_id, '_apaczka', true );

					$meta_data['package_send'] = 0;
					update_post_meta( $order_id, '_apaczka', $meta_data );

					if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
						$order = wc_get_order( $order_id );
						if ( $order && ! is_wp_error( $order ) ) {
							$order->update_meta_data( '_apaczka', $meta_data );
							$order->save();
						}
					}
				}
			}
		} elseif ( isset( $apaczka_wc_order_data['apaczka_response']->order->id ) ) {
				$apaczka_order_id = $apaczka_wc_order_data['apaczka_response']->order->id;
				$status           = self::get_apaczka_order_status( $apaczka_order_id );

			if ( 'CANCELLED' === $status ) {
				// change order status.
				$current_order_status = $order->get_status();
				if ( $order->get_date_paid() ) {
					if ( 'processing' !== $current_order_status ) {
						$order->update_status( 'wc-processing' );
					}
				} elseif ( 'on-hold' !== $current_order_status ) {
						$order->update_status( 'wc-on-hold' );
				}
			}
		}

		if ( isset( $apaczka_wc_order_data['package_send'] )
			&& $apaczka_wc_order_data['package_send'] === 1 ) {
			$package_send = true;

			$url_waybill
				= admin_url(
					'admin-ajax.php?action=apaczka&apaczka_action=get_waybill&security='
					. wp_create_nonce( 'apaczka_ajax_nonce' )
					. '&apaczka_order_id='
					. $apaczka_wc_order_data['apaczka_order']->id
				);
		}

		$options_hours = array();
		for ( $h = 9; $h < 20; $h++ ) {
			if ( $h < 10 ) {
				$h = '0' . $h;
			}
			$options_hours[ $h . ':00' ] = $h . ':00';
			if ( $h < 19 ) {
				$options_hours[ $h . ':30' ] = $h . ':30';
			}
		}

		$gateway_opts_templates = ( new Gateway_Settings_Templates_Helper() )->get_all_templates_list();
		wp_nonce_field( apaczka()->get_plugin_basename(), 'apaczka_nonce' );
		include apaczka()->get_plugin_templates_dir() . '/html-order-metabox.php';

		if ( ! $output ) {
			$out = ob_get_clean();

			return $out;
		}
	}

	public function save_post( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {

			$post_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : null;

			if ( $post_id ) {

				$post_type = get_post_type( $post_id );

				if ( 'shop_order_placehold' === $post_type ) {
					if ( isset( $_POST['_apaczka'] ) && ! empty( $_POST['_apaczka'] ) ) {

						$apaczka_wc_order_data_raw = isset( get_post_meta( $post_id )['_apaczka'][0] )
							? get_post_meta( $post_id )['_apaczka'][0]
							: '';

						if ( ! empty( $apaczka_wc_order_data_raw ) ) {
							$_apaczka = unserialize( $apaczka_wc_order_data_raw );

							$title        = $_apaczka['package_properties']['title'];
							$cod          = $_apaczka['package_properties']['cod'];
							$apaczka_post = apaczka()->get_request()->get_by_key( '_apaczka' );

							$apaczka_post['package_properties']['title'] = $title;
							$apaczka_post['package_properties']['cod']   = $cod;
							$apaczka_post['error_messages']              = isset( $_apaczka['error_messages'] ) ? $_apaczka['error_messages'] : '';

							update_post_meta( $post_id, '_apaczka', $apaczka_post );

							if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
								$order = wc_get_order( $post_id );
								if ( $order && ! is_wp_error( $order ) ) {
									$order->update_meta_data( '_apaczka', $apaczka_post );
									$order->save();
								}
							}
						}
					}
				}
			}
		}

		if ( 'shop_order' !== apaczka()
				->get_request()
				->get_by_key( 'post_type' ) ) {
			return;
		}

		if ( apaczka()->get_request()->get_by_key( '_apaczka' ) ) {
			$_apaczka = get_post_meta( $post_id, '_apaczka', true );

			if ( ! $_apaczka || ! is_array( $_apaczka ) ) {
				if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {

					$apaczka_wc_order_data_raw = isset( get_post_meta( $post_id )['_apaczka'][0] )
						? get_post_meta( $post_id )['_apaczka'][0]
						: '';

					if ( ! empty( $apaczka_wc_order_data_raw ) ) {
						if ( is_array( $apaczka_wc_order_data_raw ) ) {
							$_apaczka = $apaczka_wc_order_data_raw;
						} else {
							$_apaczka = unserialize( $apaczka_wc_order_data_raw );
						}
					}
				}
			}

			$title        = isset( $_apaczka['package_properties']['title'] ) ? $_apaczka['package_properties']['title'] : '';
			$cod          = isset( $_apaczka['package_properties']['cod'] ) ? $_apaczka['package_properties']['cod'] : '';
			$apaczka_post = apaczka()->get_request()->get_by_key( '_apaczka' );

			$apaczka_post['package_properties']['title'] = $title;
			$apaczka_post['package_properties']['cod']   = $cod;
			$apaczka_post['error_messages']              = isset( $_apaczka['error_messages'] ) ? $_apaczka['error_messages'] : '';

			update_post_meta( $post_id, '_apaczka', $apaczka_post );

			if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
				$order = wc_get_order( $post_id );
				if ( $order && ! is_wp_error( $order ) ) {
					$order->update_meta_data( '_apaczka', $apaczka_post );
					$order->save();
				}
			}
		}
	}


	/**
	 * @param $order_id
	 * @param $posted
	 *
	 * @return void
	 */
	public function woocommerce_checkout_update_order_meta(
		$order_id,
		$posted
	) {

		$order = wc_get_order( $order_id );

		$package_properties = $this->get_package_properties( $order );

		if ( apaczka()
			->get_request()
			->get_by_key( 'apm_supplier' ) ) {
			$apaczka_delivery_point =
				array(
					'apm_access_point_id'         => apaczka()
						->get_request()
						->get_by_key( 'apm_access_point_id' ),
					'apm_supplier'                => apaczka()
						->get_request()
						->get_by_key( 'apm_supplier' ),
					'apm_name'                    => apaczka()
						->get_request()
						->get_by_key( 'apm_name' ),
					'apm_foreign_access_point_id' => apaczka()
						->get_request()
						->get_by_key( 'apm_foreign_access_point_id' ),
					'apm_street'                  => apaczka()
						->get_request()
						->get_by_key( 'apm_street' ),
					'apm_city'                    => apaczka()
						->get_request()
						->get_by_key( 'apm_city' ),
					'apm_postal_code'             => apaczka()
						->get_request()
						->get_by_key( 'apm_postal_code' ),
					'apm_country_code'            => apaczka()
						->get_request()
						->get_by_key( 'apm_country_code' ),
				);
		} else {
			$apaczka_delivery_point = null;
		}

		if ( ! $order || is_wp_error( $order ) ) {
			return;
		}
		
		$receiver_company = '';
        if( ! empty( $order->get_shipping_company() ) ) {
            $receiver_company = $order->get_shipping_company();
        } else if ( ! empty( $order->get_billing_company() ) ) {
            $receiver_company = $order->get_billing_company();
        }

		$receiver = array(
			'country_code'       => $order->get_shipping_country(),
			// Kod ISO 3166.
			'name'               => ! empty( $receiver_company )
				? $receiver_company 
				: $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
			'line1'              => $order->get_shipping_address_1(),
			'line2'              => $order->get_shipping_address_2(),
			'postal_code'        => $order->get_shipping_postcode(),
			'city'               => $order->get_shipping_city(),
			'is_residential'     => 0,
			'contact_person'     => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
			'email'              => $order->get_billing_email(),
			'phone'              => $order->get_shipping_phone(),
			'foreign_address_id' => ! empty( $apaczka_delivery_point )
				? $apaczka_delivery_point['apm_foreign_access_point_id'] : '',
		);

		$apaczka                       = array();
		$apaczka['package_properties'] = $package_properties;
		$apaczka['receiver']           = $receiver;

		update_post_meta( $order_id, 'apaczka_delivery_point', $apaczka_delivery_point );
		update_post_meta( $order_id, '_apaczka', $apaczka );

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order && ! is_wp_error( $order ) ) {
				$order->update_meta_data( 'apaczka_delivery_point', $apaczka_delivery_point );
				$order->update_meta_data( '_apaczka', $apaczka );
				$order->save();
			}
		}
	}


	private function flexible_shipping_method_selected(
		$order,
		$shipping_method_integration
	) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}
		$shippings            = $order->get_shipping_methods();
		$all_shipping_methods
			= flexible_shipping_get_all_shipping_methods();
		if ( isset( $all_shipping_methods['flexible_shipping'] ) ) {
			$flexible_shipping_rates
				= $all_shipping_methods['flexible_shipping']->get_all_rates();
			foreach ( $shippings as $id => $shipping ) {
				if ( isset( $flexible_shipping_rates[ $shipping['method_id'] ] ) ) {
					$shipping_method
						= $flexible_shipping_rates[ $shipping['method_id'] ];
					if ( $shipping_method['method_integration']
						== $shipping_method_integration
					) {
						return $shipping_method;
					}
				}
			}
		}

		return false;
	}

	private static function toBool( $value ) {
		return 'true' === (string) $value;
	}


	public static function ajax_calculate() {
	}

	private static function get_point_from_order( WC_Order $order ) {
		$data = get_post_meta( $order->get_id(), 'apaczka_delivery_point', true );

		if ( is_array( $data ) ) {
			return $data;
		}

		return null;

		/**
		 * array(8) {
		 * ["apm_access_point_id"]=>
		 * string(5) "20424"
		 * ["apm_supplier"]=>
		 * string(6) "POCZTA"
		 * ["apm_name"]=>
		 * string(12) "Sklep Żabka"
		 * ["apm_foreign_access_point_id"]=>
		 * string(6) "932337"
		 * ["apm_street"]=>
		 * string(40) "ul. Bitwy Warszawskiej 1920 R. 23 lok. 1"
		 * ["apm_city"]=>
		 * string(8) "Warszawa"
		 * ["apm_postal_code"]=>
		 * string(6) "02-366"
		 * ["apm_country_code"]=>
		 * string(2) "PL"
		 * }
		 */
	}

	private static function create_api_order(
		WC_Order $order,
		array $data,
		WC_Shipping_Method $shipping_method,
		$point,
		$service_id = null
	): array {
		$pickup_method = $data['package_properties']['shipping_method'];

		$shipping_name    = $order->get_shipping_company();
		$shipping_contact = '';
		if ( $shipping_name == '' ) {
			$shipping_name    = $order->get_shipping_first_name() . ' '
				. $order->get_shipping_last_name();
			$shipping_contact = $order->get_shipping_first_name() . ' '
				. $order->get_shipping_last_name();

		} else {
			$shipping_contact = $order->get_shipping_first_name() . ' '
				. $order->get_shipping_last_name();
		}

		$data['receiver']['foreign_address_id'] = $point;

		$shipment_type_code = 'PACZKA';

		if ( 'palette_60x80' === $data['package_properties']['parcel_type'] ) {
			$shipment_type_code = 'POLPALETA';
		}

		if ( 'europalette' === $data['package_properties']['parcel_type'] ) {
			$shipment_type_code = 'PALETA';
		}

		if ( 'palette_120x100' === $data['package_properties']['parcel_type'] ) {
			$shipment_type_code = 'PALETA_PRZEMYSLOWA';
		}

		if ( 'palette_120x120' === $data['package_properties']['parcel_type'] ) {
			$shipment_type_code = 'PALETA_PRZEMYSLOWA_B';
		}

		if ( ! empty( $data['sender']['apm_foreign_access_point_id'] ) ) {
			$sender_foreign_address_id = $data['sender']['apm_foreign_access_point_id'];
		} elseif ( ! empty( $data['sender']['apm_foreign_access_point_id'] ) ) {
			$sender_foreign_address_id = $data['sender']['foreign_address_id'];
		} else {
			$sender_foreign_address_id = '';
		}

		// use Company if exists as name in request
		$sender_name = $data['sender']['first_name'] . ' ' . $data['sender']['last_name'];
		if ( isset( $data['sender']['company_name'] ) && ! empty( $data['sender']['company_name'] ) ) {
			$sender_name = $data['sender']['company_name'];
		}

		$order = array(
			// 'service_id'     => $data['package_properties']['service'],
			'service_id'     => $service_id,
			// endpoint: service_structure
			'address'        => array(
				'sender'   => array(
					'country_code'       => get_option( 'woocommerce_default_country' ),
					// Kod ISO 3166
					'name'               => $sender_name,
					'line1'              => $data['sender']['street']
						. ' ' . $data['sender']['building_number']
						. ( ! empty( $data['sender']['apartment_number'] ) ? '/'
							. $data['sender']['apartment_number'] : '' ),
					'line2'              => '',
					'postal_code'        => $data['sender']['postal_code'],
					'city'               => $data['sender']['city'],
					'is_residential'     => (int) $data['sender']['is_residential'],
					// 0 / 1
					'contact_person'     => $data['sender']['contact_person'],
					'email'              => $data['sender']['email'],
					'phone'              => $data['sender']['phone'],
					'foreign_address_id' => $sender_foreign_address_id,
					// paczkomat??
					// enpoint: points
				),
				'receiver' => $data['receiver'],
			),
			'option'         => array(
				/*
				'31'  => 0, // powiadomienie sms,
				'11 ' => 0, // rod
				'19'  => 0, // dostawa w sobotę,
				'25'  => 0, // dostawa w godzinach,
				'58'  => 0,*/  // ostrożnie
			),
			'notification'   => array(
				'new'       => array(  // Powiadomienia o utworzeniu przesyłki>
					'isReceiverEmail' => null, // 0 / 1
					'isReceiverSms'   => null, // 0 / 1
					'isSenderEmail'   => null, // 0 / 1
				),
				'sent'      => array(  // Powiadomienia o wysłaniu przesyłki
					'isReceiverEmail' => null, // 0 / 1
					'isReceiverSms'   => null, // 0 / 1
					'isSenderEmail'   => null, // 0 / 1
				),
				'exception' => array( // Powiadomienia o wyjątku
					'isReceiverEmail' => null, // 0 / 1
					'isReceiverSms'   => null, // 0 / 1
					'isSenderEmail'   => null, // 0 / 1
				),
				'delivered' => array(  // Powiadomienia o doręczeniu
					'isReceiverEmail' => null, // 0 / 1
					'isReceiverSms'   => null, // 0 / 1
					'isSenderEmail'   => null, // 0 / 1
				),
			),
			'shipment_value' => (int) ( ( (float) $data['package_properties']['declared_content'] ) * 100 ),

			'cod'            => array(
				'amount'      => ! empty( $data['additional_options']['cod_amount'] )
					? $data['additional_options']['cod_amount'] * 100  // // wartość w groszach
					: 0,  // // wartość w groszach
				'bankaccount' => $data['sender']['bank_account_number'],
			),

			'pickup'         => array(
				'type'       => $pickup_method,
				// endpoint: service_structure
				'date'       => $data['package_properties']['pickup_date'],
				// Y-m-d
				'hours_from' => date( 'H:i', strtotime( $data['package_properties']['pickup_hour_from'] ) ),
				// H:i - pickup_hours
				'hours_to'   => date( 'H:i', strtotime( $data['package_properties']['pickup_hour_to'] ) ),
				// H:i - pickup_hours
			),
			'shipment'       => array(
				array(
					'dimension1'         => (int) $data['package_properties']['package_width'],
					// cm
					'dimension2'         => (int) $data['package_properties']['package_depth'],
					// cm
					'dimension3'         => (int) $data['package_properties']['package_height'],
					// cm
					'weight'             => (int) $data['package_properties']['package_weight'],
					// kg
					'is_nstd'            => $data['package_properties']['is_nstd'] === 'yes' ? 1 : 0,
					// todo fix
					// 0 / 1
					'shipment_type_code' => $shipment_type_code,
					// todo fix
					/**
					 * PALETA
					 */
					// endpoint: service_structure
				),
			),
			'comment'        => $data['additional_options']['comment'],
			'content'        => $data['package_properties']['package_contents'],
			'is_zebra'       => null,
			// 0 / 1 (wartość opcjonalna, w przypadku nie podania etykieta będzie zgodna z ustawieniami konta)
		);

		return $order;
	}


	/**
	 * @param float  $declared_value
	 * @param float  $cod_amount
	 * @param string $api_context
	 *
	 * @return bool
	 */
	private static function declared_value_validate(
		float $declared_value,
		float $cod_amount,
		string $api_context
	): bool {

		if ( $cod_amount > 0 ) {
			if ( $declared_value === 0.0 ) {
				( new Alerts() )->add_error(
					__(
						'The Declared value field cannot be empty',
						'apaczka-pl'
					),
					$api_context
				);

				return false;
			}
			if ( $declared_value < $cod_amount ) {
				( new Alerts() )->add_error(
					__(
						'The Declared Value field must be equal to or higher than the COD amount',
						'apaczka-pl'
					),
					$api_context
				);

				return false;
			}
		}

		return true;
	}

	/**
	 * @return void
	 */
	public static function ajax_cancel_package() {
		$order_id   = apaczka()->get_request()->get_by_key( 'order_id' );
		$meta_data  = get_post_meta( $order_id, '_apaczka', true );
		$apaczka_id = $meta_data['apaczka_response']->order->id;

		try {
			$apaczka_response  = ( new Web_Api_V2() )->cancel_order( $apaczka_id );
			$response_messages = ( new Alerts() )->get_alerts_unformatted_by_context( 'cancel_order' );

			$services           = self::get_services();
			$return_price_table = array();
			if ( ! empty( $response_messages['error'] ) ) {
				$ret['error_messages'] = implode( ',', $response_messages['error'] );
				$ret['status']         = 'error';
			} else {
				$ret['status']             = 'ok';
				$meta_data['package_send'] = 0;
			}
		} catch ( Exception $e ) {
			$ret['error_messages'] = $e->getMessage();
		}

		if ( $ret['status'] === 'ok' ) {
			$ret['error_messages'] = '';
			update_post_meta( $order_id, '_apaczka', $meta_data );

			if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
				$order = wc_get_order( $order_id );
				if ( $order && ! is_wp_error( $order ) ) {
					$order->update_meta_data( '_apaczka', $meta_data );
					$order->save();
				}
			}
		}

		echo wp_json_encode( $ret );
		wp_die();
	}

	/**
	 * @return void
	 */
	public static function download_turn_in() {
		$order_id = apaczka()->get_request()->get_by_key( 'order_id' );

		$meta_data  = get_post_meta( $order_id, '_apaczka', true );
		$apaczka_id = $meta_data['apaczka_response']->order->id;

		try {
			$apaczka_response  = ( new Web_Api_V2() )->turn_in( array( $apaczka_id ) );
			$response_messages = ( new Alerts() )->get_alerts_unformatted_by_context( 'cancel_order' );

			$services           = self::get_services();
			$return_price_table = array();
			if ( ! empty( $response_messages['error'] ) ) {
				$ret['error_messages'] = implode( ',', $response_messages['error'] );
				$ret['status']         = 'error';
			} else {
				$ret['status'] = 'ok';
				$ret['base64'] = $apaczka_response->turn_in;
			}
		} catch ( Exception $e ) {
			$ret['error_messages'] = $e->getMessage();
		}

		if ( $ret['status'] === 'ok' ) {
			$ret['error_messages'] = '';
		}

		echo wp_json_encode( $ret );
		wp_die();
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	public static function ajax_calculate_package() {
		$shipping_methods = WC()->shipping()->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			$shipping_methods = WC()->shipping()->load_shipping_methods();
		}

		$shipping_method       = $shipping_methods['apaczka'];
		$ret                   = array();
		$ret['status']         = 'error';
		$ret['error_messages'] = '';
		$order_id              = apaczka()
			->get_request()
			->get_by_key( 'order_id' );

		$order = wc_get_order( $order_id );

		$post = get_post( $order_id );

		$data = apaczka()
			->get_request()
			->get_by_key( 'apaczka' );

		$meta_data = get_post_meta( $order_id, '_apaczka', true );

		$declared_value_valid = self::declared_value_validate(
			(float) $data['package_properties']['declared_content'],
			(float) $data['additional_options']['cod_amount'],
			'order_valuation'
		);

		if ( ! $declared_value_valid ) {
			$response_messages     = ( new Alerts() )->get_alerts_unformatted_by_context( 'order_valuation' );
			$ret['error_messages'] = implode( ',', $response_messages['error'][0] );
			$ret['content']        = '';

			echo wp_json_encode( $ret );
			wp_die();

		}

		$apaczka_order = self::create_api_order(
			$order,
			$data,
			$shipping_method,
			null,
			null
		);

		try {

			if ( 'yes' === Plugin::get_option( 'apaczka_debug_mode' ) ) {
				\wc_get_logger()->debug( 'Żądanie API (wycena) dla numeru zamówienia: ' . $order_id, array( 'source' => 'apaczka-log' ) );
				\wc_get_logger()->debug( print_r( $apaczka_order, true ), array( 'source' => 'apaczka-log' ) );
			}

			$apaczka_response  = ( new Web_Api_V2() )->order_valuation( $apaczka_order );
			$response_messages = ( new Alerts() )->get_alerts_unformatted_by_context( 'order_valuation' );

			if ( 'yes' === Plugin::get_option( 'apaczka_debug_mode' ) ) {
				\wc_get_logger()->debug( 'API ODPOWIEDŹ (wycena) dla numeru zamówienia: ' . $order_id, array( 'source' => 'apaczka-log' ) );
				\wc_get_logger()->debug( print_r( $apaczka_response, true ), array( 'source' => 'apaczka-log' ) );
			}

			$services           = self::get_services();
			$return_price_table = array();

			if ( ! empty( $response_messages['error'] ) ) {
				if ( stripos( $response_messages['error'][0][0], '<br>Submitted data:<br>' ) ) {
					$ret['error_messages'] = explode( '<br>Submitted data:<br>', $response_messages['error'][0][0] )[0];
				} else {
					$ret['error_messages'] = implode( ',', $response_messages['error'][0] );
				}
			} else {
				$services = ( new Service_Structure_Helper() )->get_services();

				$services_cache = array();
				foreach ( $services as $service ) {
					$services_cache[ $service->service_id ] = array(
						'pickup_courier' => $service->pickup_courier,
						'supplier'       => $service->supplier,
						'name'           => $service->name,

					);
				}

				// var_dump($services_cache);die;

				$price_table   = $apaczka_response->price_table;
				$ret['status'] = 'ok';

				foreach ( $price_table as $k => $price_item ) {
					// var_dump($price_table);die;
					$return_price_table[ $k ] =
						array(
							'name'           => $services_cache[ $k ]['name'],
							'price'          => array( $price_item->price )[0],
							'price_gross'    => array( $price_item->price_gross )[0],
							'pickup_courier' => $services_cache[ $k ]['pickup_courier'],
							'supplier'       => $services_cache[ $k ]['supplier'],
						);
				}
			}

			$data['apaczka_response'] = $apaczka_response;
		} catch ( Exception $e ) {
			$ret['error_messages'] = $e->getMessage();
		}
		$_apaczka = $data;

		$ret['apaczka_response'] = $apaczka_response;

		if ( empty( $_apaczka['package_properties']['title'] ) ) {
			$_apaczka['package_properties']['title'] = isset( $meta_data['package_properties']['title'] )
				? $meta_data['package_properties']['title']
				: null;
		}

		if ( empty( $_apaczka['package_properties']['cod'] ) ) {
			$_apaczka['package_properties']['cod'] = $meta_data['package_properties']['cod'];
		}

		if ( empty( $_apaczka['receiver'] ) ) {
			$_apaczka['receiver'] = $meta_data['receiver'];
		}

		update_post_meta( $order_id, '_apaczka', $_apaczka );
		update_post_meta( $order_id, '_apaczka_last_order_object_calc', $apaczka_order );

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order && ! is_wp_error( $order ) ) {
				$order->update_meta_data( '_apaczka', $_apaczka );
				$order->update_meta_data( '_apaczka_last_order_object_calc', $apaczka_order );
				$order->save();
			}
		}

		$default_service = (int) get_option( ( new Global_Settings() )->get_setting_id( 'service' ) );

		if ( key_exists( $default_service, $return_price_table ) ) {
			$copy = array( $default_service => $return_price_table[ $default_service ] );
			unset( $return_price_table[ $default_service ] );
			$return_price_table = $copy + $return_price_table;
		}

		if ( $ret['status'] === 'ok' ) {
			$calculate = $return_price_table;
			ob_start();
			include apaczka()->get_plugin_templates_dir()
				. DIRECTORY_SEPARATOR
				. 'html-order-metabox-calculate-result.php';
			$ret['calculate_html'] = ob_get_clean();
			$ret['error_messages'] = '';
		}

		$ret['content'] = '';

		echo wp_json_encode( $ret );
		wp_die();
	}

	/**
	 * @param string $price
	 *
	 * @return string
	 */
	public static function format_calculate_price( string $price ): string {
		return str_replace( '.', ',', (string) ( number_format( (int) $price / 100, 2, '.', '' ) ) );
	}


	public static function ajax_create_package() {
		$shipping_methods = WC()->shipping()->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			$shipping_methods = WC()->shipping()->load_shipping_methods();
		}
		$shipping_method       = $shipping_methods['apaczka'];
		$ret                   = array();
		$ret['status']         = 'error';
		$ret['error_messages'] = '';
		$order_id              = apaczka()
			->get_request()
			->get_by_key( 'order_id' );
		$order                 = wc_get_order( $order_id );
		$post                  = get_post( $order_id );
		$data                  = apaczka()
			->get_request()
			->get_by_key( 'apaczka' );
		$meta_data             = get_post_meta( $order_id, '_apaczka', true );

		$declared_value_valid = self::declared_value_validate(
			(float) $data['package_properties']['declared_content'],
			(float) $data['additional_options']['cod_amount'],
			'order_valuation'
		);

		if ( ! $declared_value_valid ) {
			$response_messages     = ( new Alerts() )->get_alerts_unformatted_by_context( 'order_send' );
			$ret['error_messages'] = implode( ',', $response_messages['error'][0] );
			$ret['content']        = '';

			echo wp_json_encode( $ret );
			wp_die();
		}

		$apaczka_delivery_point = apaczka()
				->get_request()
				->get_by_key( 'apaczka' )['delivery_point_id'] ?? '';

		$apaczka_order = self::create_api_order(
			$order,
			$data,
			$shipping_method,
			$apaczka_delivery_point,
			$data['selected_service']
		);

		try {

			if ( 'yes' === Plugin::get_option( 'apaczka_debug_mode' ) ) {
				\wc_get_logger()->debug( 'Żądanie API dla numeru zamówienia: ' . $order_id, array( 'source' => 'apaczka-log' ) );
				\wc_get_logger()->debug( print_r( $apaczka_order, true ), array( 'source' => 'apaczka-log' ) );
			}

			$apaczka_response  = ( new Web_Api_V2() )->order_send( $apaczka_order );
			$response_messages = ( new Alerts() )->get_alerts_unformatted_by_context( 'order_send' );

			if ( 'yes' === Plugin::get_option( 'apaczka_debug_mode' ) ) {
				\wc_get_logger()->debug( 'API ODPOWIEDŹ dla numeru zamówienia: ' . $order_id, array( 'source' => 'apaczka-log' ) );
				\wc_get_logger()->debug( print_r( $apaczka_response, true ), array( 'source' => 'apaczka-log' ) );
			}

			$services           = self::get_services();
			$return_price_table = array();
			if ( ! empty( $response_messages['error'] ) ) {
				$ret['error_messages'] = implode( ',', $response_messages['error'][0] );

			}

			$data['apaczka_response'] = $apaczka_response;
		} catch ( Exception $e ) {
			$ret['error_messages'] = $e->getMessage();
		}
		$_apaczka = $data;

		$ret['apaczka_response'] = $apaczka_response;

		if ( empty( $_apaczka['package_properties']['title'] ) ) {
			$_apaczka['package_properties']['title'] = $meta_data['package_properties']['title'];
		}

		if ( empty( $_apaczka['package_properties']['cod'] ) ) {
			$_apaczka['package_properties']['cod'] = $meta_data['package_properties']['cod'];
		}

		if ( empty( $_apaczka['receiver'] ) ) {
			$_apaczka['receiver'] = $meta_data['receiver'];
		}

		if ( is_object( $apaczka_response )
			&& property_exists( $apaczka_response, 'order' ) ) {
			$ret['error_messages']     = '';
			$ret['message']            = __(
				'Package created',
				'apaczka-pl'
			);
			$ret['status']             = 'ok';
			$_apaczka['package_send']  = 1;
			$_apaczka['apaczka_order'] = $apaczka_response->order;

			// set order to status 'wc-completed' if checkbox is set during parcel create
			// if( isset($_POST['apaczka_order_status_completed']) && 'true' === $_POST['apaczka_order_status_completed'] ) {
			if ( 'yes' === Plugin::get_option( 'set_order_status_completed' ) ) {
				if ( $order && ! is_wp_error( $order ) ) {
					$current_order_status = $order->get_status();
					if ( 'completed' !== $current_order_status ) {
						$order->update_status( 'wc-completed' );
					}
				}
			}
		} else {
			$_apaczka['package_send'] = 0;
		}

		update_post_meta( $order_id, '_apaczka', $_apaczka );
		update_post_meta( $order_id, '_apaczka_last_order_object', $apaczka_order );

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order && ! is_wp_error( $order ) ) {
				$order->update_meta_data( '_apaczka', $_apaczka );
				$order->update_meta_data( '_apaczka_last_order_object', $apaczka_order );
				$order->save();
			}
		}

		$ret['content'] = '';

		echo wp_json_encode( $ret );
		wp_die();
	}


	/**
	 * @return void
	 */
	public function get_waybill() {
		if ( ! is_admin() || ! isset( $_REQUEST['apaczka_get_waybill'] ) ) {
			return;
		}

		$apaczka_order_id = sanitize_text_field( $_REQUEST['apaczka_get_waybill'] );
		$waybill          = ( new Web_Api_V2() )->waybill( (int) $apaczka_order_id );

		if ( is_object( $waybill ) && isset( $waybill->waybill ) ) {

			header( 'Content-type: application/pdf' );
			header(
				'Content-Disposition: attachment; filename="apaczka_'
				. $apaczka_order_id . '.pdf"'
			);
			header( 'Content-Transfer-Encoding: binary' );
			echo base64_decode( $waybill->waybill );
		}

		die();
	}

	/**
	 * @return bool
	 */
	private function isParcelLockerChosen() {
		if ( 'PACZKOMAT' === $this->get_option( 'service' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $selected_service
	 *
	 * @return bool
	 */
	private function check_apaczka_maps_plugin( int $selected_service ): bool {
		if ( $this->is_parcel_locker_service( $selected_service ) ) {
			if ( class_exists( 'Apaczka_Points_Map\Points_Map_Plugin' ) ) {
				( new Alerts() )
					->add_error(
						'Parcel locker has been selected, but "Apaczka.pl Map of Points" plugin was not found or it is inactive.'
					);
			}
		}

		return class_exists( 'Apaczka_Points_Map\Points_Map_Plugin' );
	}

	public function process_admin_options() {
		if ( ! $this->instance_id ) {
			return parent::process_admin_options();
		}

		// Check we are processing the correct form for this instance.
		if ( ! isset( $_REQUEST['instance_id'] ) || absint( $_REQUEST['instance_id'] ) !== $this->instance_id ) { // WPCS: input var ok, CSRF ok.
			return false;
		}

		$this->init_instance_settings();
		$post_data            = $this->get_post_data();
		$instance_form_fields = $this->get_instance_form_fields();

		$this->check_apaczka_maps_plugin(
			(int) $this->get_field_value(
				'service',
				$instance_form_fields['service'],
				$post_data
			)
		);

		$create_template = $this->get_field_value(
			'create_template',
			$instance_form_fields['create_template'],
			$post_data
		) === 'yes';

		$new_template_name = $this->get_field_value(
			'new_template_name',
			$instance_form_fields['new_template_name'],
			$post_data
		);

		$created_template = array();
		foreach ( $instance_form_fields as $key => $field ) {
			if ( 'new_template_name' === $key
				|| 'create_template' === $key
				|| 'load_from_template' === $key
				|| 'select_template' === $key ) {
				continue;
			}

			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					$value                           = $this->get_field_value(
						$key,
						$field,
						$post_data
					);
					$this->instance_settings[ $key ] = $value;

					if ( $create_template ) {
						$created_template[ $key ] = $value;
					}
				} catch ( Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		if ( $create_template ) {
			( new Gateway_Settings_Templates_Helper() )->create(
				$created_template,
				$new_template_name
			);
		}

		return update_option(
			$this->get_instance_option_key(),
			apply_filters(
				'woocommerce_shipping_' . $this->id . '_instance_settings_values',
				$this->instance_settings,
				$this
			),
			'yes'
		);
	}


	/**
	 * Adds Apaczka map fields to shipping method settings.
	 *
	 * @param array $settings .
	 *
	 * @return array
	 */
	public static function settings_field( $settings ) {
		$settings['display_apaczka_map'] = array(
			'title'       => esc_html__(
				'Apaczka.pl Delivery Points Map',
				'apaczka-pl'
			),
			'type'        => 'checkbox',
			'description' => __(
				'Displays a map of delivery points on the checkout form for this shipping method.',
				'apaczka-pl'
			),
			'desc_tip'    => true,
			'default'     => '',
		);

		$settings['supplier_apaczka_map'] = array(
			'title'   => esc_html__(
				'Apaczka.pl Supplier',
				'apaczka-pl'
			),
			'type'    => 'select',
			'default' => 'all',
			'options' => array(
				'ALL'        => __( 'All', 'apaczka-pl' ),
				'DHL_PARCEL' => __( 'DHL', 'apaczka-pl-mapa-punktow' ),
				'DPD'        => __( 'DPD', 'apaczka-pl-mapa-punktow' ),
				'INPOST'     => __(
					'Inpost',
					'apaczka-pl-mapa-punktow'
				),
				'PWR'        => __(
					'Orlen Paczka',
					'apaczka-pl-mapa-punktow'
				),
				'POCZTA'     => __(
					'Poczta Polska',
					'apaczka-pl-mapa-punktow'
				),
				'UPS'        => __( 'UPS', 'apaczka-pl-mapa-punktow' ),
			),
		);

		$settings['only_cod_apaczka_map'] = array(
			'title'       => esc_html__(
				'Apaczka.pl only C.O.D points',
				'apaczka-pl'
			),
			'type'        => 'checkbox',
			'description' => __(
				'Displays only points with Collect on Delivery.',
				'apaczka-pl'
			),
			'desc_tip'    => true,
			'default'     => '',
		);

		return $settings;
	}

	public function add_map_field( $settings ) {
		$settings = self::settings_field( $settings );

		return $settings;
	}

	/**
	 * Adds custom field to each shipping method.
	 */
	public function filtering_shipping_fields() {
		$shipping_methods = WC()->shipping->get_shipping_methods();

		foreach ( $shipping_methods as $shipping_method ) {
			add_filter(
				'woocommerce_shipping_instance_form_fields_' . $shipping_method->id,
				array( $this, 'add_map_field' )
			);
		}
	}

	private function is_delivery_map_button_display() {
		// Get all your existing shipping zones IDS.
		$zone_ids                = array_keys( array( '' ) + \WC_Shipping_Zones::get_zones() );
		$chosen_shipping_methods = WC()->session->chosen_shipping_methods;

		// Loop through shipping Zones IDs.
		foreach ( $zone_ids as $zone_id ) {
			// Get the shipping Zone object.
			$shipping_zone = new \WC_Shipping_Zone( $zone_id );

			// Get all shipping method values for the shipping zone.
			$shipping_methods = $shipping_zone->get_shipping_methods(
				true,
				'values'
			);

			// Loop through each shipping methods set for the current shipping zone.
			foreach ( $shipping_methods as $instance_id => $shipping_method ) {
				if ( isset( $chosen_shipping_methods[0] ) && $shipping_method->id . ':' . $instance_id === $chosen_shipping_methods[0] ) {
					if ( isset( $shipping_method->instance_settings['display_apaczka_map'] ) && 'yes' === $shipping_method->instance_settings['display_apaczka_map'] ) {
						$this->geowidget_supplier = $shipping_method->instance_settings['supplier_apaczka_map'];

						if ( 'ALL' == $this->geowidget_supplier ) {
							$this->geowidget_supplier = "'DHL_PARCEL', 'DPD', 'INPOST', 'POCZTA', 'UPS', 'PWR'";
						} else {
							$single_carrier           = $shipping_method->instance_settings['supplier_apaczka_map'];
							$single_carrier           = "'" . $single_carrier . "'";
							$this->geowidget_supplier = $single_carrier;
						}

						$this->geowidget_only_cod = $shipping_method->instance_settings['only_cod_apaczka_map'];

						return true;
					}
				}

				/*
				if ( defined( 'FLEXIBLE_SHIPPING_VERSION' ) && 'flexible_shipping' === $shipping_method->id ) {
					$flexible_shipping      = new Flexible_Shipping_Integration();
					$flexible_shipping_data = $flexible_shipping->get_chosen_shipping_data( $chosen_shipping_methods[0], $instance_id );

					if ( isset( $flexible_shipping_data['display_apaczka_map_fxsp'] ) && 'yes' === $flexible_shipping_data['display_apaczka_map_fxsp'] ) {
						$this->supplier = $flexible_shipping_data['supplier_apaczka_map_fxsp'];
						$this->only_cod = $flexible_shipping_data['only_cod_apaczka_map_fxsp'];

						return true;
					}
				}*/
			}
		}

		return false;
	}

	public function cancel_package_popup() {

		global $pagenow;
		global $post;

		$need_popup = false;
		$post_id    = null;

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {

			$post_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : null;

			if ( $post_id ) {

				$post_type = get_post_type( $post_id );

				if ( 'shop_order_placehold' === $post_type ) {
					$need_popup = true;
				}
			}
		} elseif ( is_object( $post ) ) {

				$post_id   = $post->ID;
				$post_type = get_post_type( $post_id );
			if ( 'shop_order' === $post_type ) {
				if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
					$need_popup = true;
				}
			}
		}

		if ( $need_popup ) { ?>
			<div id="apaczka_alert_modal" class="apaczka_alert_modal">
				<div class="modalCover"></div>
				<div class="modalContext">
					<div class="apaczka_alert_modal_close"></div>
					<div class="apaczka_alert_modal_text_wrapper">
						<img id="apaczka_decor_close"
							src="<?php echo apaczka()->get_plugin_img_url() . '/decor-close.jpg'; ?>">
					</div>
					<div class="apaczka_alert_modal_title">
						<h2><?php echo __( 'Problem is occured', 'apaczka-pl' ); ?></h2>
					</div>
					<div class="apaczka_alert_modal_text">
						<p><?php echo __( 'The shipment can only be canceled on Apaczka.pl via contact form:', 'apaczka-pl' ); ?></p>
						<p><?php echo __( 'Shipping and Delivery -> Cancellation of a pallet and GLS shipment order.', 'apaczka-pl' ); ?></p>
						<p><?php echo __( 'The cancellation service may incur an additional charge.', 'apaczka-pl' ); ?></p>
					</div>
					<div class="apaczka_alert_modal_action">
						<button id="apaczka_alert_modal_close_button">Ok</button>
					</div>
				</div>
			</div>
			<?php
		}
	}


	public function get_logo( $serviceId ) {
		switch ( (int) $serviceId ) {
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 13:
			case 14:
			case 15:
			case 16:
				return 'ups';
				break;
			case 20:
			case 21:
			case 22:
			case 23:
			case 24:
			case 25:
			case 26:
			case 28:
			case 29:
			case 30:
				return 'dpd';
				break;
			case 41:
			case 40:
			case 42:
			case 43:
			case 46:
				return 'inpost';
				break;
			case 162:
			case 163:
			case 64:
			case 66:
			case 68:
				return 'pocztex';
				break;
			case 86:
			case 81:
			case 82:
			case 83:
			case 84:
				return 'dhl';
				break;
			case 50:
				return 'orlen';
				break;
			case 60:
			case 65:
			case 67:
				return 'pocztex';
				break;
			case 110:
				return 'ipaczka';
				break;
			case 150:
				return 'geis';
				break;
			case 53:
			case 151:
			case 153:
				return 'fedex';
				break;
			case 191:
				return 'apaczka-niemcy';
				break;
			case 200:
				return 'gls';
				break;
			case 211:
				return 'wawa';
				break;
			case 220:
				return 'pallex';
				break;
			case 230:
				return 'hellmann';
				break;
			case 240:
				return 'rhenus';
				break;
			case 260:
			case 261:
				return 'ambro';
				break;
			case 250:
				return 'geodis';
				break;
			default:
				return null;

		}
	}


	public function save_shipping_point_in_order_meta( $order, $request ) {

		if ( ! $order ) {
			return;
		}

		$request_body = json_decode( $request->get_body(), true );

		if ( isset( $request_body['extensions']['apaczka_pl']['apaczka-point'] )
			&& ! empty( $request_body['extensions']['apaczka_pl']['apaczka-point'] ) ) {

			$apaczka_delivery_point = json_decode( $request_body['extensions']['apaczka_pl']['apaczka-point'], true );

			$apaczka_delivery_point = array_map( 'sanitize_text_field', $apaczka_delivery_point );

			update_post_meta( $order->get_ID(), 'apaczka_delivery_point', $apaczka_delivery_point );

			if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
				$order->update_meta_data( 'apaczka_delivery_point', $apaczka_delivery_point );
				$order->save();
			}
		}
		
		$receiver_company = '';
        if( ! empty( $order->get_shipping_company() ) ) {
            $receiver_company = $order->get_shipping_company();
        } else if ( ! empty( $order->get_billing_company() ) ) {
            $receiver_company = $order->get_billing_company();
        }

		$receiver = array(
			'country_code'       => $order->get_shipping_country(),
			// Kod ISO 3166.
			'name'               => ! empty( $receiver_company )
				? $receiver_company
				: $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
			'line1'              => $order->get_shipping_address_1(),
			'line2'              => $order->get_shipping_address_2(),
			'postal_code'        => $order->get_shipping_postcode(),
			'city'               => $order->get_shipping_city(),
			'is_residential'     => 0,
			'contact_person'     => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
			'email'              => $order->get_billing_email(),
			'phone'              => $order->get_shipping_phone(),
			'foreign_address_id' => ! empty( $apaczka_delivery_point )
				? $apaczka_delivery_point['apm_foreign_access_point_id'] : '',
		);

		$package_properties = $this->get_package_properties( $order );

		$apaczka                       = array();
		$apaczka['package_properties'] = $package_properties;
		$apaczka['receiver']           = $receiver;

		update_post_meta( $order->get_ID(), '_apaczka', $apaczka );

		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
			$order->update_meta_data( '_apaczka', $apaczka );
			$order->save();
		}
	}


	public function enqueue_frontend_blocks_scripts() {
		if ( ! class_exists( 'Apaczka_Points_Map\Points_Map_Plugin' ) ) {
			if ( is_checkout() ) {
				if ( has_block( 'woocommerce/checkout' ) ) {
					$map_config = $this->get_map_config();
					$plugin     = new Plugin();
					wp_enqueue_script(
						$plugin->get_front_blocks_script_id(),
						$plugin->get_plugin_js_url() . '/blocks/front-blocks.js'
					);
					wp_localize_script(
						$plugin->get_front_blocks_script_id(),
						'apaczka_block',
						array(
							'button_text1'  => __( 'Select point', 'apaczka-pl' ),
							'button_text2'  => __( 'Change point', 'apaczka-pl' ),
							'selected_text' => __( 'Selected Parcel Locker:', 'apaczka-pl' ),
							'alert_text'    => __( 'Delivery point must be chosen!', 'apaczka-pl' ),
							'map_config'    => $map_config,
						)
					);
				}
			}
		}
	}


	private function get_map_config() {
		$config = array();
		// Get all your existing shipping zones IDS.
		$zone_ids = array_keys( array( '' ) + \WC_Shipping_Zones::get_zones() );

		foreach ( $zone_ids as $zone_id ) {

			$shipping_zone = new \WC_Shipping_Zone( $zone_id );

			$shipping_methods = $shipping_zone->get_shipping_methods( true, 'values' );

			foreach ( $shipping_methods as $instance_id => $shipping_method ) {
				if ( isset( $shipping_method->instance_settings['display_apaczka_map'] ) && 'yes' === $shipping_method->instance_settings['display_apaczka_map'] ) {
					$this->geowidget_supplier = $shipping_method->instance_settings['supplier_apaczka_map'];

					if ( 'ALL' == $this->geowidget_supplier ) {
						$config[ $instance_id ]['geowidget_supplier'] = array( 'DHL_PARCEL', 'DPD', 'INPOST', 'POCZTA', 'UPS', 'PWR' );
					} else {
						$single_carrier                               = $shipping_method->instance_settings['supplier_apaczka_map'];
						$config[ $instance_id ]['geowidget_supplier'] = array( $single_carrier );
					}

					$config[ $instance_id ]['geowidget_only_cod'] = $shipping_method->instance_settings['only_cod_apaczka_map'];
				}
			}
		}

		return $config;
	}


	private function get_package_properties( $order = null ) {

		$settings = new Global_Settings();

		$cod_amount      = 0;
		$declared_amount = 0;

		if ( $order && ! is_wp_error( $order ) ) {
			$payment_method = $order->get_payment_method();

			if ( 'cod' === $payment_method ) {
				$cod_amount      = $order->get_total();
				$declared_amount = $order->get_total();
			} else {

				$cod_amount = get_option( $settings->get_setting_id( 'cod' ), '' );
				if ( 'yes' === Plugin::get_option( 'declared_content_auto' ) ) {
					$declared_amount = $order->get_total();
				} else {
					$declared_amount = Plugin::get_option( 'declared_content' );
				}
			}
		}

		$package_properties = array(
			'dispath_point_ups'      => get_option(
				$settings->get_setting_id( 'dispath_point_ups' ),
				''
			),
			'dispath_point_kurier48' => get_option(
				$settings->get_setting_id( 'dispath_point_kurier48' ),
				''
			),
			'dispath_point_inpost'   => get_option(
				$settings->get_setting_id( 'dispath_point_inpost' ),
				''
			),
			'dispath_point_dpd'      => get_option(
				$settings->get_setting_id( 'dispath_point_dpd' ),
				''
			),
			'shipping_method'        => get_option(
				$settings->get_setting_id( 'shipping_method' ),
				''
			),
			'is_nstd'                => get_option(
				$settings->get_setting_id( 'is_nstd' ),
				''
			),
			'parcel_type'            => get_option(
				$settings->get_setting_id( 'parcel_type' ),
				''
			),
			'declared_content'       => $declared_amount,
			'cod'                    => $cod_amount,
			'service'                => get_option(
				$settings->get_setting_id( 'service' ),
				''
			),
			'package_width'          => get_option(
				$settings->get_setting_id( 'package_width' ),
				''
			),
			'package_depth'          => get_option(
				$settings->get_setting_id( 'package_depth' ),
				''
			),
			'package_height'         => get_option(
				$settings->get_setting_id( 'package_height' ),
				''
			),
			'package_weight'         => get_option(
				$settings->get_setting_id( 'package_weight' ),
				''
			),
			'package_contents'       => get_option(
				$settings->get_setting_id( 'package_contents' ),
				''
			),
			'cod_amount'             => '',
			'pickup_date'            => '',
			'pickup_hour_from'       => get_option(
				$settings->get_setting_id( 'pickup_hour_from' ),
				''
			),
			'pickup_hour_to'         => get_option(
				$settings->get_setting_id( 'pickup_hour_to' ),
				''
			),
		);

		return $package_properties;
	}

	
}
