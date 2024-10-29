<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

use WC_Admin_Settings;
use WC_Settings_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Global_Settings_Integration extends WC_Settings_Page {

	static $prevent_duplicate = [];
	static $created = false;

	/**
	 * @var Global_Settings
	 */
	private $config;

	public function __construct() {
		parent::__construct();
		$this->id     = Plugin::APP_PREFIX . '_settings_general';
		$this->label  = __( 'Apaczka', 'apaczka-pl' );
		$this->config = new Global_Settings();

		$this->remove_sender_template();
		$this->remove_parcel_template();


		if ( ! self::$created ) {
			add_filter( 'woocommerce_settings_tabs_array',
				[ $this, 'add_settings_page' ], 20 );
			add_action( 'woocommerce_settings_' . $this->id,
				[ $this, 'output' ] );
			add_action( 'woocommerce_settings_save_' . $this->id,
				[ $this, 'save' ] );
		}
	}


	public function save() {

		$this->change_current_sender_template_name();
		$this->change_current_parcel_template_name();

		$settings = $this->get_settings();

		$should_create_sender_template = apaczka()
			                                 ->get_request()
			                                 ->get_by_key( $this->config->get_setting_id( 'create_sender_template' ) ) &&
		                                 'yes' === apaczka()
			                                 ->get_request()
			                                 ->get_by_key( $this->config->get_setting_id( ( 'create_sender_template' ) ) );

		$should_create_package_template = apaczka()
			                                  ->get_request()
			                                  ->get_by_key( $this->config->get_setting_id( 'create_package_template' ) ) &&
		                                  'yes' === apaczka()
			                                  ->get_request()
			                                  ->get_by_key( $this->config->get_setting_id( 'create_package_template' ) );


		if ( $should_create_sender_template || $should_create_package_template ) {
			$this->create_templates( $settings );

			return;
		}


		WC_Admin_Settings::save_fields( $settings );

		( new Service_Structure_Helper() )->get_services();

	}

	/**
	 * @param $settings
	 *
	 * @return void
	 */
	private function create_templates( $settings ) {
		$detect_sender_section    = Plugin::APP_PREFIX . '_settings_general_sender_';
		$submitted_sender_values  = [];
		$submitted_package_values = [];

		$new_sender_template_name  = null;
		$new_package_template_name = null;

		$package_ids = [
			$this->config->get_setting_id( 'dispath_point_ups' ),
			$this->config->get_setting_id( 'dispath_point_kurier48' ),
			$this->config->get_setting_id( 'dispath_point_inpost' ),
            $this->config->get_setting_id( 'dispath_point_dpd' ),
			$this->config->get_setting_id( 'pickup_hour_to' ),
			$this->config->get_setting_id( 'pickup_hour_from' ),
			$this->config->get_setting_id( 'shipping_method' ),
			$this->config->get_setting_id( 'declared_content' ),
            $this->config->get_setting_id( 'declared_content_auto' ),
            $this->config->get_setting_id( 'set_order_status_completed' ),
			$this->config->get_setting_id( 'package_contents' ),
			$this->config->get_setting_id( 'package_weight' ),
			$this->config->get_setting_id( 'package_height' ),
			$this->config->get_setting_id( 'package_depth' ),
			$this->config->get_setting_id( 'package_width' ),
			$this->config->get_setting_id( 'parcel_type' ),
			$this->config->get_setting_id( 'service' ),
			$this->config->get_setting_id( 'cod' ),
			$this->config->get_setting_id( 'apaczka_debug_mode' ),

		];


		foreach ( $settings as $k => $option ) {
			if ( isset( $option['id'] )
			     && $option['id'] === $this->config->get_setting_id( 'create_sender_template' ) ) {
				unset( $settings[ $k ] );
			}

			if ( isset( $option['id'] )
			     && $option['id'] === $this->config->get_setting_id( 'create_package_template' ) ) {
				unset( $settings[ $k ] );
			}
		}

		add_filter( 'woocommerce_admin_settings_sanitize_option',
			function ( $value, $option, $raw_value ) use (
				&$submitted_sender_values,
				&$submitted_package_values,
				$detect_sender_section,
				&$new_sender_template_name,
				&$new_package_template_name,
				$package_ids
			) {
				if ( strpos( $option['id'],
						$detect_sender_section ) === 0 ) {
					$submitted_sender_values[ $option['id'] ] = $value;
				}

				if ( ! $new_sender_template_name
				     && $option['id'] === $this->config->get_setting_id( 'new_sender_template_name' ) ) {
					$new_sender_template_name = $value;
				}

				if ( in_array( $option['id'], $package_ids ) ) {
					$submitted_package_values[ $option['id'] ] = $value;
				}

				if ( ! $new_package_template_name
				     && $option['id'] === $this->config->get_setting_id( 'new_package_template_name' ) ) {
					$new_package_template_name = $value;
				}

				return $value;
			}, 3, 100 );

		WC_Admin_Settings::save_fields( $settings );

		//var_dump($new_package_template_name);die;

		if ( $new_sender_template_name ) {
			( new Sender_Settings_Templates_Helper() )->create( $submitted_sender_values,
				$new_sender_template_name );
		}

		if ( $new_package_template_name ) {
			( new Gateway_Settings_Templates_Helper() )->create( $submitted_package_values,
				$new_package_template_name );
		}
	}

	public function output() {

		$settings = $this->get_settings();
		WC_Admin_Settings::output_fields( $settings );

	}


	protected function change_current_sender_template_name(): bool {
		if (
			apaczka()
				->get_request()
				->get_by_key( $this->config->get_setting_id( 'change_name_sender_template' )
				) ) {
			$new_name = apaczka()
				->get_request()
				->get_by_key( $this->config->get_setting_id( 'change_name_sender_template' )
				);

			$current_slug = $this->config->get_current_sender_template_name();
			$new_slug     = ( new Sender_Settings_Templates_Helper() )->rename_template( $current_slug,
				$new_name );
			if ( null !== $new_slug ) {
				update_option(
					$this->config->get_setting_id( 'select_sender_template' ),
					$new_slug );
				apaczka()
					->get_request()
					->overwrite( $this->config->get_setting_id( 'select_sender_template' ),
						$new_slug
					);

				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function change_current_parcel_template_name(): bool {
		if ( apaczka()
			->get_request()
			->get_by_key( $this->config->get_setting_id( 'change_name_package_template' )
			) ) {
			$new_name = apaczka()
				->get_request()
				->get_by_key( $this->config->get_setting_id( 'change_name_package_template' )
				);

			$current_slug = $this->config->get_current_parcel_template_name();
			$new_slug     = ( new Gateway_Settings_Templates_Helper() )->rename_template( $current_slug,
				$new_name );
			if ( null !== $new_slug ) {
				update_option(
					$this->config->get_setting_id( 'select_package_template' ),
					$new_slug );
				apaczka()
					->get_request()
					->overwrite( $this->config->get_setting_id( 'select_package_template' ),
						$new_slug
					);

				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $key
	 *
	 * @return false|mixed|void
	 */
	public function get_option( string $key ) {
		return get_option( $this->config->get_setting_id( $key ) );
	}

	public function get_settings(): array {
		$return = $this->config->get_api_settings();

		if ( $this->api_keys_exists() ) {
			$services = Shipping_Method_Apaczka::get_services();
			if ( ! empty( $services ) ) {
				$return   = array_merge( $return,
					$this->config->get_sender_settings() );
				$return[] = $this->sender_template_support();
				$return   = array_merge( $return,
					$this->config->get_parcel_settings() );
				$return[] = $this->package_template_support();
			}
		} else {
			update_option( Web_Api_V2::SERVICE_STRUCTURE_CACHE_TIMESTAMP_OPTION,
				0 );
			update_option( Service_Structure_Helper::SERVICES_OPTION,
				null );
		}

		self::$created = true;

		return $return;
	}


	/**
	 * @return bool
	 */
	private function api_keys_exists(): bool {

		/*update_option($this->config->get_setting_id( 'app_id' ), null);
		update_option($this->config->get_setting_id( 'app_secret' ), null);*/

		$app_id     = get_option( $this->config->get_setting_id( 'app_id' ) );
		$app_secret = get_option( $this->config->get_setting_id( 'app_secret' ) );

		return ( ! empty( $app_id ) && ! empty( $app_secret ) );
	}

	/**
	 *
	 * @return array[]
	 */
	private function sender_template_support(): array {
		$custom_button_id = $this->config->get_setting_id( 'load_from_sender_template_btn_fieldset' );


		add_action( 'woocommerce_admin_field_' . $custom_button_id,
			function ( array $data ) use ( $custom_button_id ) {

				$type = $data['type'];
				if ( isset( self::$prevent_duplicate[ $type ] )
				     && self::$prevent_duplicate[ $type ]
				) {
					return;
				}
				if ( $custom_button_id === $type ) {
					?>
                    <button id="apaczka_load_sender_settings_btn"
                            class='apaczka_load_sender_settings_btn'>
						<?php _e( 'Get sender data from selected template',
							'apaczka-pl' ) ?></button>

                    <button
                            id="apaczka_remove_sender_template_btn"
                            class="button-primary woocommerce-save-button"><?php _e( 'Remove selected sender template',
							'apaczka-pl' ) ?>
                    </button>

                    <input name="apaczka_remove_sender_template_slug"
                           type="hidden"
                           id="apaczka_remove_sender_template_slug">

                    <input name="apaczka_remove_sender_template"
                           type="hidden"
                           id="apaczka_remove_sender_template">

                    <script>
                        //get_all_templates_json - encoded with wp_json_encode
                        const apaczka_sender_templates = JSON.parse('<?php echo ( new Sender_Settings_Templates_Helper() )->get_all_templates_json()?>');

                        jQuery(document).ready(function () {
                            jQuery("#apaczka_load_sender_settings_btn").click(function (e) {
                                e.preventDefault();

                                let selectedTemplateSlug = jQuery('#apaczka_woocommerce_settings_general_select_sender_template').val();
                                apaczkaFillFormByTemplate(selectedTemplateSlug);
                            });


                            jQuery('#geowidget_show_map').click(function (e) {
                                e.preventDefault();

                                var apaczkaMap = new ApaczkaMap({
                                    app_id: 'demo',
                                    onChange: function (record) {
                                        if (record) {
                                            jQuery('#parcel_machine_id').val(record.foreign_access_point_id);
                                            jQuery('#selected-parcel-machine').removeClass('hidden');
                                            jQuery('#selected-parcel-machine-id').html(record.foreign_access_point_id);
                                        }
                                    }
                                });
                                apaczkaMap.setFilterSupplierAllowed(
                                    ['INPOST'],
                                    ['INPOST']
                                );

                                apaczkaMap.show();

                                initiated = true;
                            });
                        });

                        function apaczkaFillFormByTemplate(templateSlug) {


                            let valueToSet;
                            for (const [key, value] of Object.entries(apaczka_sender_templates)) {
                                if (key === templateSlug) {

                                    //console.log(`${key}: ${value}`);
                                    for (const [key2, value2] of Object.entries(value.options)) {
                                        //console.log(`${key2}: ${value2}`)
                                        if (key2 === 'order_status_completed_auto') {
                                            if (value2 === 'yes') {
                                                jQuery('#woocommerce_apaczka_' + key2).prop('checked', true);
                                            } else {
                                                jQuery('#woocommerce_apaczka_' + key2).prop('checked', false);
                                            }
                                        } else {
                                            console.log(value2);
                                            jQuery('#' + key2).val(value2)
                                        }

                                    }
                                }
                            }
                        }


                    </script>


					<?php
				}
				self::$prevent_duplicate[ $type ] = true;
			},
			10 );


		return
			[

				'title'       => __( 'Load from sender template',
					'apaczka-pl' ),
				'type'        => $custom_button_id,
				'id'          => $this->config->get_setting_id( 'load_from_sender_template' ),
				'description' => __( '', 'apaczka-pl' ),
				'default'     => '',


			];
	}

	/**
	 *
	 * @return array[]
	 */
	private function package_template_support(): array {
		$custom_button_id = $this->config->get_setting_id( 'load_from_package_template_btn_fieldset' );

		//die($custom_button_id);
		add_action( 'woocommerce_admin_field_' . $custom_button_id,
			function ( array $data ) use ( $custom_button_id ) {
				$type = $data['type'];
				if ( isset( self::$prevent_duplicate[ $type ] )
				     && self::$prevent_duplicate[ $type ]
				) {
					return;
				}
				if ( $custom_button_id === $type ) {
					?>
                    <div class="tamplate_buttons-wrapper">
                        <label class='apaczka_load_package_settings_label'
                               for="apaczka_load_package_settings_btn"></label>
                        <button id="apaczka_load_package_settings_btn"
                                class='apaczka_load_package_settings_btn'>
							<?php _e( 'Get package data from selected template',
								'apaczka-pl' ) ?></button>
                        <input name="apaczka_remove_parcel_template"
                               type="submit"
                               id="apaczka_remove_parcel_template_btn"
                               class='apaczka_remove_parcel_template_btn'
                               value="<?php _e( 'Remove selected parcel template',
							       'apaczka-pl' ) ?>">
                        <input name="apaczka_remove_parcel_template_slug"
                               type="hidden"
                               id="apaczka_remove_parcel_template_slug">
                        <input name="apaczka_remove_parcel_template"
                               type="hidden"
                               id="apaczka_remove_parcel_template">
                    </div>

                    <script>
                        //get_all_templates_json - encoded with wp_json_encode
                        const apaczka_package_templates = JSON.parse('<?php echo ( new Gateway_Settings_Templates_Helper() )->get_all_templates_json()?>');
                        console.log('apaczka_package_templates');
                        console.log(apaczka_package_templates);

                        jQuery(document).ready(function () {
                            jQuery("#apaczka_load_package_settings_btn").click(function (e) {

                                e.preventDefault();
                                let selectedTemplateSlug = jQuery('#apaczka_woocommerce_settings_general_select_package_template').val();
                                apaczkaFillPackageFormByTemplate(selectedTemplateSlug);
                            });

                            jQuery("#apaczka_woocommerce_settings_general_select_sender_template").change(function (e) {
                                e.preventDefault();
                                let selectedTemplateSlug = jQuery(this).val();
                                const apaczkaRemoveSenderTemplateSlug = jQuery("#apaczka_remove_sender_template_slug");
                                apaczkaRemoveSenderTemplateSlug.val(selectedTemplateSlug);
                                apaczkaRemoveSenderTemplateSlug.attr('disabled', false);
                                jQuery("#apaczka_remove_parcel_template_slug").attr('disabled', true);

                            });

                            jQuery("#apaczka_woocommerce_settings_general_select_package_template").change(function (e) {
                                e.preventDefault();
                                const apaczkaRemoveParcelTemplateSlug = jQuery("#apaczka_remove_parcel_template_slug");
                                apaczkaRemoveParcelTemplateSlug.attr('disabled', false);
                                let selectedTemplateSlug = jQuery(this).val();
                                apaczkaRemoveParcelTemplateSlug.val(selectedTemplateSlug);
                                jQuery("#apaczka_remove_sender_template_slug").attr('disabled', true)
                            });

                            jQuery("#apaczka_remove_sender_template_btn").click(function (e) {
                                e.preventDefault();
                                jQuery("#apaczka_remove_sender_template").val(1);
                                jQuery('[name="save"]').click();

                                return false
                            });


                            jQuery("#apaczka_remove_parcel_template_btn").click(function (e) {
                                e.preventDefault();
                                jQuery("#apaczka_remove_parcel_template").val(1);
                                jQuery('[name="save"]').click();

                                return false
                            });

                            jQuery('#apaczka_remove_sender_template_slug').val(jQuery('#apaczka_woocommerce_settings_general_select_sender_template').val())

                            /*jQuery('#apaczka_remove_sender_template_slug').val(
								jQuery('#apaczka_woocommerce_settings_general_select_sender_template').val();
							)*/

                        });


                        function apaczkaFillPackageFormByTemplate(templateSlug) {


                            let valueToSet;
                            for (const [key, value] of Object.entries(apaczka_package_templates)) {
                                if (key === templateSlug) {

                                    //console.log(`${key}: ${value}`);
                                    for (const [key2, value2] of Object.entries(value.options)) {
                                        console.log(`${key2}: ${value2}`);
                                        //console.log(value2);
                                        jQuery('#' + key2).val(value2);

                                    }
                                }
                            }
                        }


                    </script>


					<?php
				}
				self::$prevent_duplicate[ $type ] = true;
			},
			10 );


		return

			[

				'title'       => __( 'Load from package template',
					'apaczka-pl' ),
				'type'        => $custom_button_id,
				'id'          => $this->config->get_setting_id( 'load_from_package_template' ),
				'description' => __( '', 'apaczka-pl' ),
				'default'     => '',


			];
	}

	private function remove_parcel_template() {
		if ( apaczka()
			     ->get_request()
			     ->get_by_key( 'apaczka_remove_parcel_template' )

		     && apaczka()
			        ->get_request()
			        ->get_by_key( 'apaczka_remove_parcel_template' )
		        === '1' ) {

			( new Gateway_Settings_Templates_Helper() )
				->remove_by_slug( apaczka()
					->get_request()
					->get_by_key( 'apaczka_remove_parcel_template_slug' )
				);
			( new Alerts() )->add_notice( __( 'Apaczka: Template was deleted' ) );

		}
	}

	private function remove_sender_template() {
		if ( apaczka()
			     ->get_request()
			     ->get_by_key( 'apaczka_remove_sender_template' )
		     && apaczka()
			        ->get_request()
			        ->get_by_key( 'apaczka_remove_sender_template' ) === '1' ) {


			( new Sender_Settings_Templates_Helper() )
				->remove_by_slug( apaczka()
					->get_request()
					->get_by_key( 'apaczka_remove_sender_template_slug' ) );
			( new Alerts() )->add_notice( __( 'Template was deleted',
				'apaczka-pl' ) );
		}
	}

}
