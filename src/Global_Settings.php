<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

class Global_Settings {


	public function get_api_settings(): array {
		return [
			[
				'title'       => __( 'API settings', 'apaczka-pl' ),
				'type'        => 'title',
				'description' => '',
				'id'          => 'api_settings',
			],

			[
				'title'    => __( 'App ID',
					'apaczka-pl' ),
				'id'       => $this->get_setting_id( 'app_id' ),
				'css'      => '',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => false,
				'class'    => '',
				//'custom_attributes' => [ 'required' => 'required' ],
			],


			[

				'title'       => __( 'App Secret', 'apaczka-pl' ),
				'id'          => $this->get_setting_id( 'app_secret' ),
				'type'        => 'password',
				'description' => __( 'App Secret', 'apaczka-pl' ),
				'default'     => '',
				'desc_tip'    => true,
				//'custom_attributes' => [
				//'required_' => '',
				//],

			],


			[
				'id'   => 'api_settings',
				'type' => 'sectionend',
			],
		];
	}

	public function get_sender_settings(): array {
		return [
			[
				'title'       => __( 'Sender details', 'apaczka-pl' ),
				'id'          => 'sender_details',
				'type'        => 'title',
				'description' => '',
			],
            [
                'title'       => __( 'Address type',
                    'apaczka-pl' ),
                'id'          => $this->get_setting_id( 'sender_is_residential' ),
                'type'        => 'select',
                'description' => __( '', 'apaczka-pl' ),
                'default'     => 'company',
                'desc_tip'    => true,
                'options'     => [
                    '0' => __( 'Company', 'apaczka-pl' ),
                    '1' => __( 'Private', 'apaczka-pl' ),
                ],
            ],
			[

				'title'             => __( 'Company name', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_company_name' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '50'
				],
			],
			[

				'title'             => __( 'First Name', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_first_name' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '50'
				],
			],
			[

				'title'             => __( 'Last name', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_last_name' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '50'
				],
			],
			[
				'title'             => __( 'Street', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_street' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '50'
				],

			],
			[
				'title'             => __( 'Building number',
					'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_building_number' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '10'
				],

			],
			[
				'title'             => __( 'Apartment number',
					'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_apartment_number' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '10'
				],

			],

			[
				'title'             => __( 'Postal code', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_postal_code' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
				],

			],
			[
				'title'             => __( 'City', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_city' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '50'
				],

			],
			[
				'title'             => __( 'Contact person',
					'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_contact_person' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
                    'maxlength' => '50'
				],

			],
			[
				'title'             => __( 'Phone', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_phone' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
				],
			],
			[
				'title'             => __( 'E-mail', 'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_email' ),
				'type'              => 'email',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required_' => '',
				],
			],
			[
				'title'             => __( 'Bank account number',
					'apaczka-pl' ),
				'id'                => $this->get_setting_id( 'sender_bank_account_number' ),
				'type'              => 'text',
				'description'       => __( '', 'apaczka-pl' ),
				'default'           => '',
				'desc_tip'          => true,
				'custom_attributes' => [
					'required' => 'true',
				],
				'ok'                => 'ok',
			],


			[
				'title'       => __( 'Create new sender template?',
					'apaczka-pl' ),
				'id'          => $this->get_setting_id( 'create_sender_template' ),
				'type'        => 'select',
				'description' => __( '', 'apaczka-pl' ),
				'default'     => 'no',
				'desc_tip'    => true,
				'options'     => [
					'no'  => __( 'No', 'apaczka-pl' ),
					'yes' => __( 'Yes', 'apaczka-pl' ),
				],
			],

			[
				'title'       => __( 'New sender template name',
					'apaczka-pl' ),
				'id'          => $this->get_setting_id( 'new_sender_template_name' ),
				'type'        => 'text',
				'description' => __( '', 'apaczka-pl' ),
				'default'     => '',
				'value' => '',
				'desc_tip'    => true,
			],

			[
				'title'       => __( 'Choose sender template to load',
					'apaczka-pl' ),
				'id'          => $this->get_setting_id( 'select_sender_template' ),
				'type'        => 'select',
				'description' => __( '', 'apaczka-pl' ),
				'default'     => '',
				'desc_tip'    => true,
				'options'     => ( new Sender_Settings_Templates_Helper() )->get_all_templates_list(),

			],

			/*[
				'title'       => __( 'Change current sender template name',
					'apaczka-pl' ),
				'id'          => $this->get_setting_id( 'change_name_sender_template' ),
				'type'        => 'text',
				'description' => __( '', 'apaczka-pl' ),
				'default'     => '',
				'desc_tip'    => true,
				'value'       => ( new Sender_Settings_Templates_Helper() )
					->get_template_name_by_template_slug( $this->get_current_sender_template_name() ),
			],*/

			[
				'id'   => 'sender_details',
				'type' => 'sectionend',
			],


		];

	}

	/**
	 * @return array
	 */
	public function get_parcel_settings(): array {
		$options_hours = [];
		for ( $h = 9; $h < 20; $h ++ ) {
			$options_hours[ $h . ':00' ] = $h . ':00';
			if ( $h < 19 ) {
				$options_hours[ $h . ':30' ] = $h . ':30';
			}
		}


		return [

			[
				'title' => __( 'Default shipping settings',
					'apaczka-pl' ),
				'type'  => 'title',
				'id'    => 'default_shipping_settings',

				'description' => '',
			],

			[
				'id'                       => $this->get_setting_id( 'service' ),
				'title'                    => __( 'Default service',
					'apaczka-pl' ),
				'type'                     => 'select',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => self::get_services(),
				'visible_on_order_details' => true,
			],
			
			[
                'id'                       => $this->get_setting_id( 'shipping_method' ),
                'title'                    => __( 'Default way to send a parcel',
                    'apaczka-pl' ),
                'type'                     => 'select',
                'description'              => __( '',
                    'apaczka-pl' ),
                'default'                  => '',
                'desc_tip'                 => true,
                'options'                  => [
                    'POINT'   => __( 'Shipment directly at the point',
                        'apaczka-pl' ),
                    'COURIER' => __( 'Courier pickup request',
                        'apaczka-pl' ),
                    'SELF'    => __( 'Pickup self',
                        'apaczka-pl' ),
                ],
                'visible_on_order_details' => true,
            ],

			[
				'id'                       => $this->get_setting_id( 'parcel_type' ),
				'title'                    => __( 'Parcel type',
					'apaczka-pl' ),
				'type'                     => 'select',
				'desc_tip'                 => __( '' ),
				'options'                  => [
					'box'             => __( 'Box',
						'apaczka-pl' ),
					'europalette'     => __( 'Europalette',
						'apaczka-pl' ),
					'palette_60x80'   => __( 'Palette 60x80',
						'apaczka-pl' ),
                    'palette_120x100' => __( 'Palette 120x100',
                        'apaczka-pl' ),
					'palette_120x120' => __( 'Palette 120x120',
						'apaczka-pl' ),
				],
				'visible_on_order_details' => true,
			],

			[
				'id'                       => $this->get_setting_id( 'is_nstd' ),
				'title'                    => __( 'Non standard package',
					'apaczka-pl' ),
				'type'                     => 'select',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => 'no',
				'desc_tip'                 => true,
				'options'                  => [
					'yes' => __( 'Yes', 'apaczka-pl' ),
					'no'  => __( 'No', 'apaczka-pl' ),
				],
				'visible_on_order_details' => true,
			],

			[
				'id'                       => $this->get_setting_id( 'package_width' ),
				'title'                    => __( 'Package length [cm]',
					'apaczka-pl' ),
				'type'                     => 'number',
				'description'              => __( 'Package length [cm].',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => [
					'min'      => 0,
					'max'      => 10000,
					'step'     => 1,
					'required' => 'required',
				],
				'visible_on_order_details' => true,
			],
			[
				'id'                       => $this->get_setting_id( 'package_depth' ),
				'title'                    => __( 'Package width [cm]',
					'apaczka-pl' ),
				'type'                     => 'number',
				'description'              => __( 'Package width [cm].',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => [
					'min'      => 0,
					'max'      => 10000,
					'step'     => 1,
					'required' => 'required',
				],
				'visible_on_order_details' => true,
			],
			[
				'id'                       => $this->get_setting_id( 'package_height' ),
				'title'                    => __( 'Package height [cm]',
					'apaczka-pl' ),
				'type'                     => 'number',
				'description'              => __( 'Package height [cm].',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => [
					'min'      => 0,
					'max'      => 10000,
					'step'     => 1,
					'required' => 'required',
				],
				'visible_on_order_details' => true,
			],
			[
				'id'                       => $this->get_setting_id( 'package_weight' ),
				'title'                    => __( 'Package weight [kg]',
					'apaczka-pl' ),
				'type'                     => 'number',
				'description'              => __( 'Package weight [kg].',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'custom_attributes'        => [
					'min'      => 0,
					'max'      => 10000,
					'step'     => 'any',
					'required' => 'required',
				],
				'visible_on_order_details' => true,
			],

			[
				'id'                       => $this->get_setting_id( 'package_contents' ),
				'title'                    => __( 'Default package contents',
					'apaczka-pl' ),
				'type'                     => 'text',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			],

			[
				'id'                       => $this->get_setting_id( 'declared_content' ),
				'title'                    => __( 'Declared value',
					'apaczka-pl' ),
				'type'                     => 'text',
				'label'                    => __( '',
					'apaczka-pl' ),
				'visible_on_order_details' => true,
			],

            [
                'id'                       => $this->get_setting_id( 'declared_content_auto' ),
                'title'                    => __( 'Automatically complete the "Declaration of value" with the value of the order',
                    'apaczka-pl' ),
                'type'                     => 'checkbox',
                'label'                    => __( '',
                    'apaczka-pl' ),
                'default'                  => 'yes',
                'visible_on_order_details' => true,
            ],

            [
                'id'                       => $this->get_setting_id( 'set_order_status_completed' ),
                'title'                    => __( 'Change order status to Completed after shipment created?',
                    'apaczka-pl' ),
                'type'                     => 'checkbox',
                'label'                    => __( '',
                    'apaczka-pl' ),
                'default'                  => 'no',
                'visible_on_order_details' => true,
            ],

			[
				'id'                       => $this->get_setting_id( 'pickup_hour_from' ),
				'title'                    => __( 'Pickup hour from',
					'apaczka-pl' ),
				'type'                     => 'select',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => $options_hours,
				'visible_on_order_details' => true,
			],
			[
				'id'                       => $this->get_setting_id( 'pickup_hour_to' ),
				'title'                    => __( 'Pickup hour to',
					'apaczka-pl' ),
				'type'                     => 'select',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => $options_hours,
				'visible_on_order_details' => true,
			],

			[
				'id'                       => $this->get_setting_id( 'dispath_point_inpost' ),
				'title'                    => __( 'Default dispatch point (InPost)',
					'apaczka-pl' ),
				'type'                     => 'text',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			],

			[
				'id'                       => $this->get_setting_id( 'dispath_point_kurier48' ),
				'title'                    => __( 'Default dispatch point (Kurier48)',
					'apaczka-pl' ),
				'type'                     => 'text',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			],
			[
				'id'                       => $this->get_setting_id( 'dispath_point_ups' ),
				'title'                    => __( 'Default dispatch point (UPS)',
					'apaczka-pl' ),
				'type'                     => 'text',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => true,
			],
            [
                'id'                       => $this->get_setting_id( 'dispath_point_dpd' ),
                'title'                    => __( 'Default dispatch point DPD',
                    'apaczka-pl' ),
                'type'                     => 'text',
                'description'              => __( '',
                    'apaczka-pl' ),
                'default'                  => '',
                'desc_tip'                 => true,
                'visible_on_order_details' => true,
            ],

			[
				'id'                       => $this->get_setting_id( 'create_package_template' ),
				'title'                    => __( 'Create new template from this settings?',
					'apaczka-pl' ),
				'type'                     => 'select',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => 'no',
				'desc_tip'                 => true,
				'options'                  => [
					'no'  => __( 'No', 'apaczka-pl' ),
					'yes' => __( 'Yes', 'apaczka-pl' ),
				],
				'visible_on_order_details' => false,
			],

			[
				'id'                       => $this->get_setting_id( 'new_package_template_name' ),
				'title'                    => __( 'New template name',
					'apaczka-pl' ),
				'type'                     => 'text',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'visible_on_order_details' => false,
			],

			[
				'id'                       => $this->get_setting_id( 'select_package_template' ),
				'title'                    => __( 'Choose template to load',
					'apaczka-pl' ),
				'type'                     => 'select',
				'description'              => __( '',
					'apaczka-pl' ),
				'default'                  => '',
				'desc_tip'                 => true,
				'options'                  => ( new Gateway_Settings_Templates_Helper() )->get_all_templates_list(),
				'visible_on_order_details' => true,
			],
			[
				'id'                       => 'load_from_template',
				'name'                     => __( '', '' ),
				'title'                    => __( '',
					'apaczka-pl' ),
				'type'                     => 'load_from_template',
				'visible_on_order_details' => true,
			],


			[
				'id'   => 'default_shipping_settings',
				'type' => 'sectionend',
			],

            [
                'title' => __( 'Debug mode', 'apaczka-pl' ),
                'type'  => 'title',
                'id'    => 'debug_settings',

                'description' => '',
            ],

            [
                'id'                       => $this->get_setting_id( 'apaczka_debug_mode' ),
                'title'                    => __( 'Log API requests to a log file', 'apaczka-pl' ),
                'type'                     => 'checkbox',
                'label'                    => __( '', 'apaczka-pl' ),
                'default'                  => 'no',
                'visible_on_order_details' => true,
            ],

            [
                'id'   => 'debug_settings',
                'type' => 'sectionend',
            ],

			/*[
				'title'       => __( 'Change current package template name',
					'apaczka-pl' ),
				'id'          => $this->get_setting_id( 'change_name_package_template' ),
				'type'        => 'text',
				'description' => __( '', 'apaczka-pl' ),
				'default'     => '',
				'desc_tip'    => true,
				'value'       => ( new Gateway_Settings_Templates_Helper() )
					->get_template_name_by_template_slug( $this->get_current_parcel_template_name() ),
			],*/

		];

	}


	/**
	 * @return array
	 */
	private static function get_services(): array {
		$return   = [];
		$services = ( new Service_Structure_Helper() )->get_services();
		if ( ! is_array( $services ) ) {
			return [];
		}
		foreach ( ( new Service_Structure_Helper() )->get_services() as $service ) {
			$return [ $service->service_id ] = $service->name;
		}

		return $return;
	}

	/**
	 * @param $key
	 *
	 * @return string
	 */
	public function get_setting_id( $key ): string {
		return Plugin::APP_PREFIX . '_settings_general_' . $key;
	}


	/**
	 * @return array
	 */
	public function get_current_sender_config(): array {
		$sender_config = [];

		$settings       = $this->get_sender_settings();
		$detect_section = Plugin::APP_PREFIX . '_settings_general_sender_';

		foreach ( $settings as $setting ) {
			if ( ! isset( $setting['id'] ) ) {
				continue;
			}
			if ( strpos( $setting['id'], $detect_section ) === 0 ) {
				$sender_config[ str_replace( $this->get_setting_id( 'sender_' ),
					'',
					$setting['id'] ) ] = get_option( $setting['id'] );
			}
		}

		return $sender_config;
	}

	/**
	 * @return string
	 */
	public function get_current_sender_template_name(): string {
		$current_sender_template_name = get_option(
			$this->get_setting_id( 'select_sender_template' ) );

		return ! empty( $current_sender_template_name )
			? $current_sender_template_name : '';
	}

	/**
	 * @return string
	 */
	public function get_current_parcel_template_name(): string {
		$current_parcel_template_name = get_option(
			$this->get_setting_id( 'select_package_template' ) );

		return ! empty( $current_parcel_template_name )
			? $current_parcel_template_name : '';
	}

}
