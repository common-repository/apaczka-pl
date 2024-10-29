<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

use Exception;
use Inspire_Labs\Apaczka_Woocommerce\Global_Settings_Integration;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Web_Api_V2 {

	/**
	 * @var self
	 */
	protected static $instance;

	const API_URL = 'https://www.apaczka.pl/api/v2/';

	const SIGN_ALGORITHM = 'sha256';

	const EXPIRES = '+20min';

	const SECONDS_24H = 86400;

	const SERVICE_STRUCTURE_CACHE_OPTION = Plugin::APP_PREFIX . '_SC_CACHE';

	const SERVICE_STRUCTURE_CACHE_TIMESTAMP_OPTION
		= Plugin::APP_PREFIX . '_SC_CACHE_TIMESTAMP';


	public $app_id;
	public $app_secret;

	protected $cache_period = DAY_IN_SECONDS;


	public function __construct() {
		$this->app_id     = Plugin::get_option( 'app_id' );
		$this->app_secret = Plugin::get_option( 'app_secret' );

		/*var_dump( get_option('app_id'));die;
		var_dump( $this->app_secret );
		die;*/


		//$this->setupEnvironment();
	}

	/**
	 * @throws Exception
	 */
	private function wp_remote_post( $route, $data = null ) {

		$result = wp_remote_post(
			self::API_URL . $route,
			[
				'body'    => http_build_query( $this->buildRequest( $route, $data ) ) ,
				'method' => 'POST',
				'sslverify' => false,
                'timeout' => 30
			]
		);

		if ( !$result || $result instanceof WP_Error ) {
			$error_msg = $result->get_error_message();
			throw new Exception( $error_msg );
		}

		return wp_remote_retrieve_body( $result );
	}

	/**
	 * @param $route
	 * @param $data
	 *
	 * @return mixed
	 */
	private function request( $route, $data = null ) {
		try {
			ob_start();
			var_dump( $data );
			$debug_dump = ob_get_clean();

			$response = $this->wp_remote_post( $route . '/', $data );
		} catch ( Exception $e ) {

			if ( defined( 'WOOCOMMERCE_APACZKA_DEBUG' ) ) {
				$debug_extra_info = '<br>Submitted data:<br><pre>' . $debug_dump . '</pre>';
			} else {
				$debug_extra_info = '';
			}

			( new Alerts() )->add_error( $this->prepare_error_message(
				sanitize_text_field( $route ),
				$e->getMessage() . $debug_extra_info
			), $route );

			return false;
		}

		$response_decoded = json_decode( $response );
		ob_start();
		var_dump( $response );
		$raw_response = ob_get_clean();

		ob_start();
		var_dump( $this->buildRequest( $route, $data ) );
		$raw_request_data = ob_get_clean();

		if ( ! is_object( $response_decoded )
		     || ! property_exists( $response_decoded, 'status' )
		     || ! property_exists( $response_decoded, 'message' )
		     || ! property_exists( $response_decoded, 'response' ) ) {

			if ( defined( 'WOOCOMMERCE_APACZKA_DEBUG' ) ) {
				$debug_extra_info = '<br>Raw response: <br>' . $raw_response . '<br>Submitted data:<br><pre>' . $debug_dump . '</pre>';
			} else {
				$debug_extra_info = '';
			}


			( new Alerts() )->add_error(
				$this->prepare_error_message(
					$route,
					'Response cannot be decoded' . $debug_extra_info
				), $route
			);

			return false;
		}

		$status = (int) $response_decoded->status;

		if ( 200 !== $status ) {

			if ( defined( 'WOOCOMMERCE_APACZKA_DEBUG' ) ) {
				$debug_extra_info = '<br>Submitted data:<br><pre>'
				                    . $debug_dump
				                    . '</pre><br>Raw request:<br>'
				                    . $raw_request_data;

				( new Alerts() )->add_error(
					$this->prepare_error_message(
						$route,
						sprintf( 'Response status %s. Message: %s'
							, $status
							,
							$response_decoded->message . $debug_extra_info
						) )
					, $route );
			} else {
				( new Alerts() )->add_error( $response_decoded->message, $route );
			}
			
			
			if ( function_exists( 'wc_get_logger' ) ) {
				$logger = wc_get_logger();

				$logger->log(
					'debug',
					'REQUEST',
					array(
						'source'             => 'apaczka-api-status-log',
						'additional_context' => array(
							'expires_value_min'       => self::EXPIRES,
							'expires_value_timestamp' => strtotime( self::EXPIRES ),
							'current_timestamp'       => time(),
							'timestamp_diff'          => ( strtotime( self::EXPIRES ) - time() ),
						),
						'route'              => $route,
						'error'              => $response_decoded->message,
						'request_data'       => $data,
					)
				);
			}
			

			return false;
		}

		return $response_decoded->response;
	}

	/**
	 * @param $route
	 * @param $message
	 *
	 * @return string
	 */
	public function prepare_error_message( $route, $message ): string {
		return sprintf(
			'[%s] [route: %s] %s'
			, date( "Y-m-d H:i:s" ) . ' UTC'
			, $route,
			$message
		);
	}


	private function buildRequest( $route, $data = [] ) {
		$data    = json_encode( $data );
		$expires = strtotime( self::EXPIRES );

		$system = 'WooCommerce APIv2 iLabs';

		return [
			'app_id'    => $this->app_id,
			'request'   => $data,
			'expires'   => $expires,
			'signature' => $this->getSignature(
				$this->stringToSign( $this->app_id,
					$route, $data, $expires ), $this->app_secret
			),
		];
	}

	public function order( $id ) {
		return $this->request( __FUNCTION__ . '/' . $id );
	}

	public function orders( $page = 1, $limit = 10 ) {
		return $this->request( __FUNCTION__ . '/', [
			'page'  => $page,
			'limit' => $limit,
		] );
	}

	public function waybill( $id ) {
		//return $this->request( __FUNCTION__ . '/' . $id . '/' );
		return $this->request( __FUNCTION__ . '/' . $id );
	}

	public function pickup_hours( $postal_code, $service_id = false ) {
		return $this->request( __FUNCTION__ . '/', [
			'postal_code' => $postal_code,
			'service_id'  => $service_id,
		] );
	}

	public function order_valuation( $order ) {
		return $this->request( __FUNCTION__, [
			'order' => $order,
		] );
	}

	public function order_send( $order ) {
		return $this->request( __FUNCTION__, [
			'order'  => $order,
			'system' => 'WooCommerce APIv2 iLabs',
		] );
	}

	public function cancel_order( $id ) {
		return $this->request( __FUNCTION__ . '/' . $id );
	}

	public function turn_in( $order_ids = [] ) {
		return $this->request( __FUNCTION__, [
			'order_ids' => $order_ids,
		] );
	}

	public function service_structure() {
		if ( defined( 'APACZKA_DISABLE_CACHE' ) || time()
		                                           - (int) get_option( self::SERVICE_STRUCTURE_CACHE_TIMESTAMP_OPTION )
		                                           > self::SECONDS_24H
		) {

			$service_structure = $this->request( __FUNCTION__ );


			if ( ! is_object( $service_structure )
			     || ! property_exists( $service_structure, 'services' )
			     || ! property_exists( $service_structure, 'options' )
			     || ! property_exists( $service_structure, 'package_type' )
			     || ! property_exists( $service_structure, 'points_type' ) ) {

				if ( defined( 'WOOCOMMERCE_APACZKA_DEBUG' ) ) {
					( new Alerts() )->add_error(
						$this->prepare_error_message(
							__FUNCTION__,
							'service_structure object cannot be parsed!'
						)
					);
				}

				( new Alerts() )->add_error( __( 'Unable to get the site structure from API. Make sure credentials are correct.',
					'apaczka-pl' ) );

				return false;
			}

			update_option( self::SERVICE_STRUCTURE_CACHE_OPTION,
				$service_structure );
			update_option( self::SERVICE_STRUCTURE_CACHE_TIMESTAMP_OPTION,
				time() );

			( new Service_Structure_Helper )
				->update_options_by_service_structure( $service_structure );
			( new Alerts() )->clean_errors();


		} else {
			$service_structure = get_option( self::SERVICE_STRUCTURE_CACHE_OPTION );
		}

		return $service_structure;
	}

	public function points( $type = null ) {
		return $this->request( __FUNCTION__ . '/' . $type . '/' );
	}

	public function customer_register( $customer ) {
		return $this->request( __FUNCTION__ . '/', [
			'customer' => $customer,
		] );
	}


	/**
	 * @param $string
	 * @param $key
	 *
	 * @return string
	 */
	public function getSignature( $string, $key ) {
		return hash_hmac( self::SIGN_ALGORITHM, $string, $key );
	}


	/**
	 * @param $appId
	 * @param $route
	 * @param $data
	 * @param $system
	 * @param $expires
	 *
	 * @return string
	 */
	public function stringToSign( $appId, $route, $data, $expires ) {
		return sprintf( "%s:%s:%s:%s", $appId, $route, $data, $expires );
	}


	function translate_error( $error ) {
		$errors = [
			'receiver_email' => __( 'Recipient e-mail',
				'apaczka-pl' ),
		];


		if ( isset( $errors[ $error ] ) ) {
			return $errors[ $error ];
		}

		return $error;
	}

	private function authorizationError( $message, $status ) {
		$errors = $this->translate_error( $message );

		$alerts = new Alerts();
		$alerts->add_error( 'Woocommerce Inpost: '
		                    . ( is_string( $errors ) ? $errors
				: serialize( $errors ) . $message . ' ( ' . $status . ' )' ) );


	}


	public function validate_phone( $phone ) {

		if ( $this->getCountry() == EasyPack_API::COUNTRY_UK ) {
			if ( preg_match( "/\A\d{10}\z/", $phone ) ) {
				return true;
			} else {
				return __(
					'Invalid phone number. Valid phone number must contains 10 digits.',
					'apaczka-pl' );
			}
		}
		if ( $this->getCountry() == EasyPack_API::COUNTRY_PL ) {
			if ( preg_match( "/\A[1-9]\d{8}\z/", $phone ) ) {
				return true;
			} else {
				return __(
					'Invalid phone number. Valid phone number must contains 9 digits and must not begins with 0.',
					'apaczka-pl' );
			}
		}

		return __( 'Invalid phone number.', 'apaczka-pl' );

	}


	/**
	 * @param string $method
	 * @param string $url
	 * @param array $request
	 * @param array $response
	 */
	public function addToLog( $method, $url, $request, $response ) {
		$file = WOO_INPOST_PLUGIN_DIR
		        . DIRECTORY_SEPARATOR
		        . 'log-inpost.txt';

		$line
			= sprintf(
			"******************\n\n%s\n%s\nURL:%s\nREQUEST:\n%s\nRESPONSE:\n%s\n",
			$method,
			date( "Y-m-d H:i:s", time() ),
			$url,
			preg_replace( '/[\x00-\x1F\x7F]/u', '', serialize( $request ) ),
			//remove non printable characters
			preg_replace( '/[\x00-\x1F\x7F]/u', '', serialize( $response ) )
		);

		file_put_contents( $file, $line, FILE_APPEND );
	}

	public function parse_return( $return ) {
		$ret = json_decode( json_encode( $return ), true );

		return $ret;
	}

}
