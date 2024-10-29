<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

class Service_Structure_Helper {

	const SERVICES_OPTION = Plugin::APP_PREFIX . '_SERVICES';

	const PACKAGE_TYPE_OPTION = Plugin::APP_PREFIX . '_PACKAGE_TYPE';

	const POINTS_TYPE_OPTION = Plugin::APP_PREFIX . '_POINTS_TYPE';

	const OPTIONS_OPTION = Plugin::APP_PREFIX . '_OPTIONS';


	public function update_options_by_service_structure(
		object $service_structure
	) {
		update_option( self::SERVICES_OPTION, $service_structure->services );
		update_option( self::PACKAGE_TYPE_OPTION,
			$service_structure->package_type );
		update_option( self::POINTS_TYPE_OPTION,
			$service_structure->points_type );
		update_option( self::OPTIONS_OPTION, $service_structure->options );
	}

	/**
	 * @return false|mixed|void
	 */
	public function get_services() {
		$this->refresh();

		return get_option( self::SERVICES_OPTION );
	}

	/**
	 * @return false|mixed|void
	 */
	public function get_package_type() {
		$this->refresh();

		return get_option( self::PACKAGE_TYPE_OPTION );
	}

	/**
	 * @return false|mixed|void
	 */
	public function get_points_type() {
		$this->refresh();

		return get_option( self::POINTS_TYPE_OPTION );
	}

	/**
	 * @return false|mixed|void
	 */
	public function get_options() {
		$this->refresh();

		return get_option( self::OPTIONS_OPTION );
	}

	/**
	 * @return void
	 */
	private function refresh() {
		( new Web_Api_V2() )->service_structure();
	}

}
