<?php
/**
 * @var WC_ORDER $order
 * @var array $apaczka_wc_order_data
 * @var array $sender_templates
 * @var array $sender_templates_json
 * @var array $package_properties_templates
 * @var array $package_properties_templates_json
 * @var array $package_properties_services
 * @var array $package_properties_parcel_types
 * @var array $package_properties_shipping_methods
 * @var array $package_properties_hours
 * @var bool $package_send
 * @var bool $apaczka_delivery_point
 */

use Inspire_Labs\Apaczka_Woocommerce\Global_Settings;
use Inspire_Labs\Apaczka_Woocommerce\Plugin;
use Inspire_Labs\Apaczka_Woocommerce\Service_Structure_Helper; ?>

<?php
$custom_attributes = array();
if ( $package_send ) {
	$custom_attributes['disabled'] = 'disabled';
}

$id = 'gateway';
?>

<?php if ( ! $package_send ) : ?>

	<div id="apaczka_panel_sender"
		class="panel woocommerce_options_panel apaczka_panel">

		<h4>
			<?php _e( 'Sender', 'apaczka-pl' ); ?>
		</h4>

		<div class="options_group">
			<div class="apaczka-sender-template-wrapper">
				<?php
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}
				if ( ! $package_send ) :
					woocommerce_wp_select(
						array(
							'id'                => '_apaczka[sender_template]',
							'label'             => __(
								'Sender template',
								'apaczka-pl'
							),
							'desc_tip'          => false,
							'type'              => 'number',
							'options'           => $sender_templates,
							'value'             => '',
							'custom_attributes' => array( 'data-raw-id' => 'test' ),
						)
					);
					?>


					<button id="apaczka_load_sender_settings_btn"
							class='apaczka_load_sender_settings_btn'>
						<?php
						_e(
							'Get sender data from selected template',
							'apaczka-pl'
						)
						?>
							</button>
				<?php endif; ?>
			</div>
			<?php
			$key                           = 'is_residential';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Address type',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'options'           => array(
						'0' => __( 'Company', 'apaczka-pl' ),
						'1' => __( 'Private', 'apaczka-pl' ),
					),
					'value'             => isset( $apaczka_wc_order_data['sender'][ $key ] )
							? $apaczka_wc_order_data['sender'][ $key ]
							: '0',
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'company_name';
			$custom_attributes             = array( 'maxlength' => '50' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Company name',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'first_name';
			$custom_attributes             = array( 'maxlength' => '50' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'First name',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'last_name';
			$custom_attributes             = array( 'maxlength' => '50' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Last name',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'street';
			$custom_attributes             = array( 'maxlength' => '50' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __( 'Street', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'building_number';
			$custom_attributes             = array( 'maxlength' => '10' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Building number',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'apartment_number';
			$custom_attributes             = array( 'maxlength' => '10' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Apartment number',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'postal_code';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Postal code',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'city';
			$custom_attributes             = array( 'maxlength' => '50' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __( 'City', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'contact_person';
			$custom_attributes             = array( 'maxlength' => '50' );
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Contact person',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'phone';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __( 'Phone', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'email';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __( 'E-mail', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'email',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'bank_account_number';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[sender][%s]',
						$key
					),
					'label'             => __(
						'Bank account number',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['sender'][ $key ],
				)
			);
			?>

			<input type="hidden"
					data-key="foreign_address_id"
					id="_apaczka[sender][apm_foreign_access_point_id]"
					name="_apaczka[sender][apm_foreign_access_point_id]"/>

		</div>


	</div>


	<div id="apaczka_panel_receiver"
		class="panel woocommerce_options_panel apaczka_panel">
		<h4>
			<?php _e( 'Receiver', 'apaczka-pl' ); ?>
		</h4>

		<div class="options_group">

			<?php
			$key                           = 'is_residential';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __(
						'Address type',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'options'           => array(
						'0' => __( 'Company', 'apaczka-pl' ),
						'1' => __( 'Private', 'apaczka-pl' ),
					),
					'value'             => '0',
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'country_code';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __( 'Country', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'Country', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			/*
			$key                           = 'company_name';
			$custom_attributes             = ['maxlength' => '50'];
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input( [
				'id'                => sprintf( '_apaczka[receiver][%s]',
					$key ),
				'label'             => __( 'Company name', 'apaczka-pl' ),
				'desc_tip'          => false,
				'type'              => 'text',
				'custom_attributes' => $custom_attributes,
				'data_type'         => 'text',
				'value'             => isset($apaczka_wc_order_data['receiver'][ $key ])
					? $apaczka_wc_order_data['receiver'][ $key ]
					: $order->get_billing_company(),
				'placeholder'       => __( 'Company name', 'apaczka-pl' ),
			] );
			*/
			?>

			<?php
			$key                           = 'name';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => ! empty( $order->get_billing_company() ) || ! empty( $order->get_shipping_company() )
							? __( 'Company name', 'apaczka-pl' )
							: __( 'First name', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => ! empty( $order->get_shipping_company() ) ? $order->get_shipping_company() : $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => ! empty( $order->get_billing_company() ) || ! empty( $order->get_shipping_company() )
							? __( 'Company name', 'apaczka-pl' )
							: __( 'First name', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'line1';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __(
						'Address line 1',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'Address line 1', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'line2';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __(
						'Address line 2',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'Address line 2', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'postal_code';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __( 'Postcode', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'Postcode', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'city';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __( 'City', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'City', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'contact_person';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			// $contact_person = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __(
						'Contact person',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'Contact person', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'phone';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __( 'Phone', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'Phone', 'apaczka-pl' ),
				)
			);
			?>

			<?php
			$key                           = 'email';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[receiver][%s]',
						$key
					),
					'label'             => __( 'E-mail', 'apaczka-pl' ),
					'desc_tip'          => false,
					'type'              => 'email',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['receiver'][ $key ],
					'placeholder'       => __( 'E-mail', 'apaczka-pl' ),
				)
			);
			?>

		</div>
	</div>

	<div id="apaczka_panel_properties"
		class="panel woocommerce_options_panel apaczka_panel">

		<h4>
			<?php _e( 'Package properties', 'apaczka-pl' ); ?>
		</h4>

		<div class="options_group">

			<div class="apaczka-sender-template-wrapper">
				<?php
				$selected_template_in_settings = get_option( ( new Global_Settings() )->get_setting_id( 'select_package_template' ) );

				$key                           = 'selected_template';
				$custom_attributes             = array();
				$custom_attributes['data-key'] = $key;
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}
				woocommerce_wp_select(
					array(
						'id'                => sprintf(
							'_apaczka[package_properties][%s]',
							$key
						),
						'label'             => __(
							'Package properties template',
							'apaczka-pl'
						),
						'desc_tip'          => false,
						'options'           => $package_properties_templates,
						'value'             => $selected_template_in_settings,
						'custom_attributes' => $custom_attributes,
					)
				);
				?>

				<?php if ( ! $package_send ) : ?>

					<button id="apaczka_load_package_properties_settings_btn"
							class='apaczka_load_package_properties_settings_btn'>
						<?php
						_e(
							'Get data from selected template',
							'apaczka-pl'
						)
						?>
						</button>
				<?php endif; ?>

			</div>

			<?php
			$key                           = 'parcel_type';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Parcel type',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'options'           => $package_properties_parcel_types,
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'is_nstd';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Non standard package',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'options'           => array(
						'yes' => __( 'Tak', 'apaczka-pl' ),
						'no'  => __( 'Nie', 'apaczka-pl' ),
					),
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'package_width';
			$custom_attributes['min']      = 0;
			$custom_attributes['max']      = 10000;
			$custom_attributes['step']     = 1;
			$custom_attributes['required'] = 0;
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Package width',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'package_depth';
			$custom_attributes             = array();
			$custom_attributes['min']      = 0;
			$custom_attributes['max']      = 10000;
			$custom_attributes['step']     = 1;
			$custom_attributes['required'] = 0;
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Package depth',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'package_height';
			$custom_attributes             = array();
			$custom_attributes['min']      = 0;
			$custom_attributes['max']      = 10000;
			$custom_attributes['step']     = 1;
			$custom_attributes['required'] = 0;
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Package height',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'package_weight';
			$custom_attributes             = array();
			$custom_attributes['min']      = 0;
			$custom_attributes['max']      = 10000;
			$custom_attributes['step']     = 1;
			$custom_attributes['required'] = 0;
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Package weight',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'package_contents';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Package contents',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'text',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
				)
			);
			?>

			<?php
			$key                           = 'cod_amount';
			$custom_attributes             = array();
			$custom_attributes['min']      = 0;
			$custom_attributes['max']      = 10000;
			$custom_attributes['step']     = 'any';
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[additional_options][%s]',
						$key
					),
					'label'             => __(
						'COD amount',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'custom_attributes' => $custom_attributes,
					'data_type'         => 'text',
					'value'             => $apaczka_wc_order_data['additional_options'][ $key ],
				)
			);
			?>


			<?php
			$key                           = 'declared_content';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'label'             => __(
						'Declared value',
						'apaczka-pl'
					),
					'custom_attributes' => $custom_attributes,
					'class'             => '',
				)
			);
			?>

			<?php
			$key                           = 'comment';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[additional_options][%s]',
						$key
					),
					'value'             => $apaczka_wc_order_data['additional_options'][ $key ] ?? '',
					'label'             => __(
						'Comment',
						'apaczka-pl'
					),
					'custom_attributes' => $custom_attributes,
					'class'             => '',
				)
			);
			?>

			<?php
			$key                           = 'shipping_method';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}

			$shipping_method_from_settings = get_option( ( new Global_Settings() )->get_setting_id( 'shipping_method' ) );
			$selected_value                = '';
			if ( isset( $apaczka_wc_order_data['package_properties'][ $key ] )
				&& ! empty( $apaczka_wc_order_data['package_properties'][ $key ] ) ) {
				$selected_value = $apaczka_wc_order_data['package_properties'][ $key ];
			} else {
				$selected_value = $shipping_method_from_settings;
			}

			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Shipping method',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'options'           => $package_properties_shipping_methods,
					'value'             => $selected_value,
					'custom_attributes' => $custom_attributes,
				)
			);
			?>


			<?php
			$key                           = 'pickup_hour_from';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Pickup hour from',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'options'           => $package_properties_hours,
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'pickup_hour_to';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_select(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Pickup hour to',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'number',
					'options'           => $package_properties_hours,
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

			<?php
			$key                           = 'pickup_date';
			$custom_attributes             = array();
			$custom_attributes['data-key'] = $key;
			if ( $package_send ) {
				$custom_attributes['disabled'] = 'disabled';
			}
			woocommerce_wp_text_input(
				array(
					'id'                => sprintf(
						'_apaczka[package_properties][%s]',
						$key
					),
					'label'             => __(
						'Pickup date',
						'apaczka-pl'
					),
					'desc_tip'          => false,
					'type'              => 'date',
					'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
					'custom_attributes' => $custom_attributes,
				)
			);
			?>

		</div>


	</div>

<?php endif ?>

<div class="panel woocommerce_options_panel apaczka_panel">
	<?php if ( $package_send == false ) : ?>
		<div class="apaczka-actions-wrapper">
			<div class="options_panel-button-wrapper">
				<button class="button-primary apaczka_calculate_price"
						id="apaczka_calculate_price_btn">
					<?php
					_e(
						'Calculate price',
						'apaczka-pl'
					);
					?>
					</button>
			</div>
			<span style="float:none;"
					class="spinner spinner_calculate"></span>
			<div style="padding-top:5px"></div>

			<div id="apaczka-calculate-wrapper"></div>

			<div id="deliver_to_any_shipping_point" style="display:none">
				<p>
					<?php echo __( 'Deliver the shipment to any shipping point of the selected carrier', 'apaczka-pl' ); ?>
				</p>
			</div>

			<div class="apaczka-hidden" id="dispath_point_inpost_wrapper">
				<?php
				$key                           = 'dispath_point_inpost';
				$custom_attributes             = array();
				$custom_attributes['data-key'] = $key;
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}

				woocommerce_wp_text_input(
					array(
						'id'                => sprintf(
							'_apaczka[package_properties][%s]',
							$key
						),
						'label'             => __(
							'Dispath point',
							'apaczka-pl'
						),
						'desc_tip'          => false,
						'type'              => 'text',
						'custom_attributes' => $custom_attributes,
						'data_type'         => 'text',
						// 'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
						'value'             => get_option( 'apaczka_woocommerce_settings_general_dispath_point_inpost' ),
						'placeholder'       => __( 'Dispath point', 'apaczka-pl' ),
					)
				);
				?>
			</div>

			<div class="apaczka-hidden" id="dispath_point_kurier48_wrapper">
				<?php
				$key                           = 'dispath_point_kurier48';
				$custom_attributes             = array();
				$custom_attributes['data-key'] = $key;
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}

				woocommerce_wp_text_input(
					array(
						'id'                => sprintf(
							'_apaczka[package_properties][%s]',
							$key
						),
						'label'             => __(
							'Dispath point',
							'apaczka-pl'
						),
						'desc_tip'          => false,
						'type'              => 'text',
						'custom_attributes' => $custom_attributes,
						'data_type'         => 'text',
						// 'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
						'value'             => get_option( 'apaczka_woocommerce_settings_general_dispath_point_kurier48' ),
						'placeholder'       => __( 'Dispath point', 'apaczka-pl' ),
					)
				);
				?>
			</div>
			<div class="apaczka-hidden" id="dispath_point_ups_wrapper">
				<?php
				$key                           = 'dispath_point_ups';
				$custom_attributes             = array();
				$custom_attributes['data-key'] = $key;
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}

				woocommerce_wp_text_input(
					array(
						'id'                => sprintf(
							'_apaczka[package_properties][%s]',
							$key
						),
						'label'             => __(
							'Dispath point',
							'apaczka-pl'
						),
						'desc_tip'          => false,
						'type'              => 'text',
						'custom_attributes' => $custom_attributes,
						'data_type'         => 'text',
						// 'value'             => $apaczka_wc_order_data['package_properties'][ $key ],
						'value'             => get_option( 'apaczka_woocommerce_settings_general_dispath_point_ups' ),
						'placeholder'       => __( 'Dispath point', 'apaczka-pl' ),
					)
				);

				?>
			</div>

			<div class="apaczka-hidden" id="dispath_point_dpd_wrapper">
				<?php
				$key                           = 'dispath_point_dpd';
				$custom_attributes             = array();
				$custom_attributes['data-key'] = $key;
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}

				woocommerce_wp_text_input(
					array(
						'id'                => '_apaczka_dispath_point_dpd',
						'label'             => __(
							'Dispath point',
							'apaczka-pl'
						),
						'desc_tip'          => false,
						'type'              => 'text',
						'custom_attributes' => $custom_attributes,
						'data_type'         => 'text',
						'value'             => get_option( 'apaczka_woocommerce_settings_general_dispath_point_dpd' ),
						'placeholder'       => __( 'Dispath point', 'apaczka-pl' ),
					)
				);
				?>
			</div>


			<div class="apaczka-hidden" id="apaczka_delivery_point_id_wrapper">
				<?php
				$key                           = 'delivery_point_id';
				$custom_attributes             = array();
				$custom_attributes['data-key'] = $key;
				if ( $package_send ) {
					$custom_attributes['disabled'] = 'disabled';
				}

				woocommerce_wp_text_input(
					array(
						'id'                => sprintf(
							'_apaczka[delivery_point_id]',
							$key
						),
						'label'             => __(
							'Delivery point id',
							'apaczka-pl'
						),
						'desc_tip'          => false,
						'type'              => 'text',
						'custom_attributes' => $custom_attributes,
						'data_type'         => 'text',
						'value'             => isset( $apaczka_delivery_point['apm_foreign_access_point_id'] ) ? $apaczka_delivery_point['apm_foreign_access_point_id'] : '',
						'placeholder'       => __( 'Delivery point id', 'apaczka-pl' ),
					)
				);

				?>


				<input data-key="apm_access_point_id" type="hidden"
						id="_apaczka_apm_access_point_id"
						name="_apaczka[delivery_point][apm_access_point_id]"/>
				<input data-key="apm_supplier" type="hidden"
						id="_apaczka_apm_supplier"
						name="_apaczka[delivery_point][apm_supplier"/>
				<input data-key="apm_name" type="hidden" id="_apaczka_apm_name"
						name="_apaczka[delivery_point][apm_name]"/>
				<input data-key="apm_foreign_access_point_id" type="hidden"
						id="_apaczka_apm_foreign_access_point_id"
						name="_apaczka[delivery_point][apm_foreign_access_point_id]"/>
				<input data-key="apm_street" type="hidden"
						id="_apaczka_apm_street"
						name="_apaczka[delivery_point][apm_street]"/>
				<input data-key="apm_city" type="hidden" id="_apaczka_apm_city"
						name="_apaczka[delivery_point][apm_city]"/>
				<input data-key="apm_postal_code" type="hidden"
						id="_apaczka_apm_postal_code"
						name="_apaczka[delivery_point][apm_postal_code]"/>
				<input data-key="apm_country_code" type="hidden"
						id="_apaczka_apm_country_code"
						name="_apaczka[delivery_point][apm_country_code]"/>

			</div>

			<div class="options_panel-button-wrapper">
				<!--<div class="apaczka_set_order_completed-wrapper apaczka-hidden">
					<p class="apaczka_set_order_completed">
						<input name="apaczka_set_order_completed" id="apaczka_set_order_completed" type="checkbox" value="1">
						<label for="apaczka_set_order_completed">
							<?php /*_e( 'Change order status to Completed?', 'apaczka-pl' ); */ ?>
						</label>
					</p>
				</div>-->
				<button disabled class="button-primary apaczka_send"
						id="apaczka_send"
						data-apaczka-id="<?php echo esc_attr( $id ); ?>">
					<?php _e( 'Send', 'apaczka-pl' ); ?></button>
			</div>

			<br>
			<span style="float:none;"
					class="spinner spinner_courier"></span>
			<div style="padding-top:5px"></div>
			<span style="float:none;" class="spinner spinner_self"></span>
		</div>
	<?php endif; ?>
	<div class="apaczka_error" id="apaczka_error">
		<?php
		if ( isset( $apaczka_wc_order_data['error_messages'] )
			&& $apaczka_wc_order_data['error_messages'] != ''
		) :
			?>
			<hr/>

			<?php echo esc_html( $apaczka_wc_order_data['error_messages'] ); ?>

		<?php endif; ?>
	</div>

	<div class="apaczka_success" style="color: lawngreen"
		id="apaczka_success">
	</div>

	<?php if ( $package_send == true ) : ?>
		<?php
		$srv_list        = ( new Service_Structure_Helper() )->get_services();
		$choosed_carrier = '';
		$apaczka_order   = get_post_meta( $post->ID, '_apaczka_last_order_object', true );
		if ( $apaczka_order ) {
			foreach ( $srv_list as $service ) {
				if ( $service->service_id == $apaczka_order['service_id'] ) {
					$choosed_carrier = $service->name;
				}
			}
		}
		?>
		<h3>
		<?php
		_e(
			'Your order has been placed on apaczka.pl',
			'apaczka-pl'
		);
		?>
			</h3>
		<h4><?php _e( 'The waybill number is:', 'apaczka-pl' ); ?>
			<strong><a target="_blank"
						href="<?php echo esc_url( $apaczka_wc_order_data['apaczka_order']->tracking_url ); ?>">
					<?php echo esc_attr( $apaczka_wc_order_data['apaczka_order']->waybill_number ); ?>
				</a>
			</strong>
		</h4>
		<?php if ( $choosed_carrier ) : ?>
		<h4><?php _e( 'Carrier:', 'apaczka-pl' ); ?>
			<strong id="apaczka_choosed_carrier_id" data-id="<?php echo esc_attr( $apaczka_order['service_id'] ); ?>" style="color:#36aa27">
				<?php echo esc_attr( $choosed_carrier ); ?>
			</strong>
		</h4>
	<?php endif; ?>
		<script>
			document.addEventListener( 'click', function (e) {
				e = e || window.event;
				var target = e.target || e.srcElement;
				if (target.hasAttribute('id') && target.getAttribute('id') == 'apaczka_alert_modal_close_button') {
					// hide cancel package alert modal
					jQuery("#apaczka_alert_modal").removeClass("active");
				}
			}, false );
		</script>
	<?php endif; ?>

	<?php if ( $package_send ) : ?>
		<br/>

		<button
				class="button-primary"
				id="apaczka_get_waybill">
				<?php
				_e(
					'Get waybill',
					'apaczka-pl'
				);
				?>
											</button>
		</a>
		<br/>
		<br/>
		<button class="button-primary apaczka_cancel" id="apaczka_cancel"
				data-apaczka-id="<?php echo esc_attr( $id ); ?>">
											<?php
											_e(
												'Cancel parcel',
												'apaczka-pl'
											);
											?>
									</button>


		<?php if ( $apaczka_wc_order_data['package_properties']['shipping_method'] === 'COURIER' ) : ?>
			<button class="button-primary apaczka_download_turn_in"
					id="apaczka_download_turn_in"
					data-apaczka-id="<?php echo esc_attr( $id ); ?>">
												<?php
												_e(
													'Download turn in protocol',
													'apaczka-pl'
												);
												?>
										</button>
		<?php endif; ?>
	<?php endif; ?>

</div>


<script type="text/javascript">
	const apaczka_sender_templates =
		JSON.parse('<?php echo $sender_templates_json; ?>');////$sender_templates_json - encoded with wp_json_encode
	const apaczka_package_properties_templates =
		JSON.parse('<?php echo $package_properties_templates_json; ?>');////$package_properties_templates_json - encoded with wp_json_encode
	// var apaczka_calculate_selected_service = 0
	var apaczka_calculate_selected_service = jQuery('input[name="apaczka_calculate_radio"]:checked').val();
	var apaczka_dispath_point_handler_to_validate = false;


	jQuery("#apaczka_load_sender_settings_btn").click(function (e) {
		e.preventDefault();
		let selectedTemplateSlug = jQuery('#_apaczka\\[sender_template\\]').val();
		fillSenderFormByTemplate(selectedTemplateSlug)
	});


	jQuery("#apaczka_load_package_properties_settings_btn").click(function (e) {
		e.preventDefault();
		let selectedTemplateSlug = jQuery('#_apaczka\\[package_properties\\]\\[selected_template\\]').val();

		fillPackagePropertiesFormByTemplate(selectedTemplateSlug)
	});

	function changeShippingMethodTriggers(method) {
		if ('SELF' === method) {
			jQuery("label[data-pickup_courier='2']").remove();
			jQuery("label[data-pickup_courier='0']").show();
			jQuery("label[data-pickup_courier='1']").show();
			jQuery("label[data-service_id='41']").remove();
			jQuery("label[data-service_id='14']").remove();
			jQuery("label[data-service_id='13']").remove();
			jQuery("label[data-service_id='50']").remove();
			jQuery("label[data-service_id='26']").remove();
			jQuery("label[data-service_id='43']").remove();
			jQuery("label[data-service_id='162']").remove();
			jQuery("label[data-service_id='160']").remove();
			jQuery("label[data-service_id='165']").remove();
			jQuery("label[data-service_id='40']").remove();
			jQuery("label[data-service_id='164']").remove();
			jQuery("label[data-service_id='66']").remove();
			jQuery("label[data-service_id='65']").remove();

		} else {
			jQuery("label[data-service_id='65']").show();
			jQuery("label[data-service_id='66']").show();
			jQuery("label[data-service_id='164']").show();
			jQuery("label[data-service_id='40']").show();
			jQuery("label[data-service_id='165']").show();
			jQuery("label[data-service_id='160']").show();
			jQuery("label[data-service_id='162']").show();
			jQuery("label[data-service_id='43']").show();
			jQuery("label[data-service_id='26']").show();
			jQuery("label[data-service_id='50']").show();
			jQuery("label[data-service_id='41']").show();
			jQuery("label[data-service_id='14']").show();
			jQuery("label[data-service_id='13']").show();
			jQuery("label[data-pickup_courier='2']").show();
			jQuery("label[data-pickup_courier='0']").show();
			jQuery("label[data-pickup_courier='1']").show();
		}
	}

	jQuery('#_apaczka\\[package_properties\\]\\[shipping_method\\]').change(function (e) {
		if (jQuery('#apaczka-calculate-wrapper').html() !== '') {
			jQuery('#apaczka_calculate_price_btn').click();
			return false
		}
	});

	function fillSenderFormByTemplate(templateSlug) {
		//console.log(Object.entries(apaczka_sender_templates));
		for (const [key, value] of Object.entries(apaczka_sender_templates)) {
			if (key === templateSlug) {

				//console.log(`${key}: ${value}`);
				for (const [key2, value2] of Object.entries(value.options)) {
					key2_new = key2.replace("apaczka_woocommerce_settings_general_sender_", "");
					jQuery('#' + '_apaczka\\[sender\\]\\[' + key2_new + '\\]').val(value2)
				}
			}
		}
	}

	function fillPackagePropertiesFormByTemplate(templateSlug) {
		for (const [key, value] of Object.entries(apaczka_package_properties_templates)) {
			if (key === templateSlug) {
				for (const [key2, value2] of Object.entries(value.options)) {
					key2_new = key2.replace("apaczka_woocommerce_settings_general_", "");
					//console.log(key2_new + ' ' + value2);
					jQuery('#' + '_apaczka\\[package_properties\\]\\[' + key2_new + '\\]').val(value2)
				}
			}
		}

		jQuery('#_apaczka\\[package_properties\\]\\[parcel_type\\]').trigger('change');

	}

	const PocztaKurier48 = 160;
	const PocztaKurier48Punkty = 162;
	const PocztexPunktDrzwi = 65;
	const PocztexPunktPunkt = 66;
	const AllegroSMARTKurier48Punkty = 164;
	const AllegroSMARTPocztaKurier48 = 165;
	const AllegroSMARTPaczkomatInPost = 40;
	const PaczkomatInPost = 41;
	const PaczkomatInPostInternational = 43;
	const PaczkomatInPostInternationalFR = 46;
	const UPSAPPunktDrzwi = 13;
	const UPSAPPunktDrzwiFR = 16;
	const UPSAPPunktPunkt = 14;
	const DPD_kurier = 21;
	const AllegroSMARTPocztex = 67;
	const AllegroSMARTPocztexPunkty = 68;
	const InPostPaczkomatDrzwi  = 43;
	const PocztexDrzwiPunkt = 64;
	const AllegroSMARTDPDPickup = 20;
	const DPDCourierEurope = 22;
	const DPDPickupEurope = 29;
	const FedexInternationalEconomy = 153;
	const UPSstandard = 5;
	const UPSexpressSaver = 6;


	/**
	 * /**
	 *  * UPS Access Point (service id: 14,15, 16)
	 * DPD Pickup (service id: 23, 26)
	 * ORLEN Paczka  (service id: 50)
	 * Paczkomaty InPost  (service id: 41)
	 * Poczta Polska Ekspres24 Punkty  (service id: 163)
	 * DHL POP  (service id: 86)
	 * Poczta Polska Kurier48 Punkty  (service id: 162)
	 * DHL_PARCEL
	 * DPD
	 * INPOST
	 * POCZTA
	 * UPS
	 */


	function serviceIdToApmSupplierId(serviceId) {
		switch (serviceId) {
			case 14:
			case 15:
			case 16:
				return 'UPS';
			case 20:
			case 23:
			case 26:
			case 29:
				return 'DPD';
			case 50:
				return 'PWR';
			case 40:
			case 41:
			case 46:
				return 'INPOST';
			case 163:
				return 'POCZTA';
			case 64:
				return 'POCZTA';
			case 66:
				return 'POCZTA';
			case 162:
				return 'POCZTA';
			case 86:
				return 'DHL_PARCEL';
			case 68:
				return 'POCZTA';
			default:
				return null
		}
	}

	function is_cod_amount_defined() {
		return parseInt(jQuery('#_apaczka\\[additional_options\\]\\[cod_amount\\]').val()) > 0
	}


	/**
	 *DHL_PARCEL DHL
	 * DPD DPD
	 * INPOST INPOST
	 * PWR Orlen Paczka
	 * POCZTA Poczta Polska
	 * UPS UPS
	 */

	function handleCalculateDynamicFields(selectedService) {
		let shipping_method = jQuery('#_apaczka\\[package_properties\\]\\[shipping_method\\]').val();

		let debug = serviceIdToApmSupplierId(selectedService);
		console.log('serviceIdToApmSupplierId');
		console.log(debug);

		if (serviceIdToApmSupplierId(selectedService) !== null) {
			jQuery('#apaczka_delivery_point_id_wrapper').removeClass('apaczka-hidden');
		} else {
			jQuery('#apaczka_delivery_point_id_wrapper').addClass('apaczka-hidden');
		}

		method = null;

		if ((selectedService === PocztaKurier48
			|| selectedService === PocztaKurier48Punkty
			|| selectedService === AllegroSMARTKurier48Punkty
			|| selectedService === AllegroSMARTPocztaKurier48
			|| selectedService === PocztexPunktDrzwi
			|| selectedService === PocztexPunktPunkt
			|| selectedService === AllegroSMARTPocztex
			|| selectedService === AllegroSMARTPocztexPunkty
		)
		) {
			method = 'kurier48';

		}

		if ((selectedService === AllegroSMARTPaczkomatInPost
			|| selectedService === PaczkomatInPost
			|| selectedService === InPostPaczkomatDrzwi
			|| selectedService === PaczkomatInPostInternational
			|| selectedService === PaczkomatInPostInternationalFR
		)
		) {
			method = 'inpost';

		}

		if (selectedService === UPSAPPunktPunkt
			|| selectedService === UPSAPPunktDrzwi
			//|| selectedService === UPSAPPunktDrzwiFR
			//|| selectedService === UPSexpressSaver
			//|| selectedService === UPSstandard
		) {
			method = 'ups';

		}

		if ( selectedService === AllegroSMARTDPDPickup
			//|| selectedService === DPDCourierEurope
			//|| selectedService === DPDPickupEurope
		) {
			method = 'dpd';

		}

		/*if ( selectedService === FedexInternationalEconomy
		) {
			method = 'fedex';

		}*/

		if ('POINT' === shipping_method) {
			if (selectedService === DPD_kurier ) {
				method = 'dpd';

			}
		}


		console.log('selectedService ' + selectedService);
		console.log('method ' + method);
		console.log('shipping_method ' + shipping_method);

		if ('kurier48' === method) {
			jQuery('#dispath_point_kurier48_wrapper').removeClass('apaczka-hidden');
			jQuery('#dispath_point_ups_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_dpd_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_inpost_wrapper').addClass('apaczka-hidden');

			jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_kurier48\\]').val());
			apaczka_dispath_point_handler_to_validate = jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_kurier48\\]');


		}

		if ('inpost' === method) {
			jQuery('#dispath_point_kurier48_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_ups_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_dpd_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_inpost_wrapper').removeClass('apaczka-hidden');

			jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_inpost\\]').val());
			apaczka_dispath_point_handler_to_validate = jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_inpost\\]');

			if ('COURIER' === shipping_method) {
				jQuery('#dispath_point_inpost_wrapper').addClass('apaczka-hidden');
				apaczka_dispath_point_handler_to_validate = false;
			} else {
				jQuery('#dispath_point_inpost_wrapper').removeClass('apaczka-hidden');
				apaczka_dispath_point_handler_to_validate = jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_inpost\\]');
			}
		}

		if ('ups' === method) {
			jQuery('#dispath_point_kurier48_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_ups_wrapper').removeClass('apaczka-hidden');
			jQuery('#dispath_point_inpost_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_dpd_wrapper').addClass('apaczka-hidden');

			jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_ups\\]').val())
			apaczka_dispath_point_handler_to_validate = jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_ups\\]');

		}

		if ('dpd' === method) {
			jQuery('#dispath_point_kurier48_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_ups_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_inpost_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_dpd_wrapper').removeClass('apaczka-hidden');

			jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(jQuery('#_apaczka_dispath_point_dpd').val());
			apaczka_dispath_point_handler_to_validate = jQuery('#_apaczka_dispath_point_dpd');

		} else {
			jQuery('#dispath_point_dpd_wrapper').addClass('apaczka-hidden');
		}

		if (null === method) {
			jQuery('#dispath_point_kurier48_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_ups_wrapper').addClass('apaczka-hidden');
			jQuery('#dispath_point_inpost_wrapper').addClass('apaczka-hidden');
			apaczka_dispath_point_handler_to_validate = false;
		}
	}


	jQuery(".apaczka_calculate_price").click(function () {
		if (!jQuery(this).closest("form")[0].checkValidity()) {
			jQuery(this).closest("form")[0].reportValidity();
			return false;
		}
		jQuery(this).attr('disabled', true);
		jQuery(this).parent().find(".spinner_courier").hide();
		jQuery(this).parent().find(".spinner").hide();
		jQuery(this).parent().find(".spinner_calculate").addClass('is-active');

		var data = {
			action: 'apaczka',
			order_id: <?php echo esc_attr( $order_id ); ?>,
			apaczka_action: 'calculate',
			security: apaczka_ajax_nonce,
			apaczka: {
				sender: {},
				receiver: {},
				package_properties: {},
				additional_options: {},
			}
		};

		var package_properties = jQuery('input[id^="_apaczka[package_properties]"], select[id^="_apaczka[package_properties]"]');
		var sender = jQuery('input[id^="_apaczka[sender]"]');
		var receiver = jQuery('input[id^="_apaczka[receiver]"]');
		var additional_options = jQuery('input[id^="_apaczka[additional_options]"]');
		var is_residential_sender = jQuery('select[id="_apaczka[sender][is_residential]"]').val();
		var is_residential_receiver = jQuery('select[id="_apaczka[receiver][is_residential]"]').val();

		sender.each(function () {
			data.apaczka.sender[jQuery(this).data('key')] = jQuery(this).val()
		});
		data.apaczka.sender['is_residential'] = is_residential_sender;

		receiver.each(function () {
			data.apaczka.receiver[jQuery(this).data('key')] = jQuery(this).val()
		});
		data.apaczka.receiver['is_residential'] = is_residential_receiver;

		package_properties.each(function () {
			data.apaczka.package_properties[jQuery(this).data('key')] = jQuery(this).val()
		});

		additional_options.each(function () {
			data.apaczka.additional_options[jQuery(this).data('key')] = jQuery(this).val()
		});

		jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_inpost\\]').click(function (e) {
			const field = jQuery(this);
			e.preventDefault();

			var apaczkaMap = new ApaczkaMap({
				app_id: Math.random() * 9999999,
				onChange: function (record) {
					if (record) {
						field.val(record.foreign_access_point_id);
						jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(record.foreign_access_point_id);
					}
				}
			});
			apaczkaMap.setFilterSupplierAllowed(
				['INPOST']
			);
			apaczkaMap.show();
		});

		jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_kurier48\\]').click(function (e) {
			const field = jQuery(this);
			e.preventDefault();

			var apaczkaMap = new ApaczkaMap({
				app_id: Math.random() * 9999999,
				onChange: function (record) {
					if (record) {
						field.val(record.foreign_access_point_id);
						jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(record.foreign_access_point_id);
					}
				}
			});
			apaczkaMap.setFilterSupplierAllowed(
				['POCZTA']
			);
			apaczkaMap.show();
		});

		jQuery('#_apaczka\\[package_properties\\]\\[dispath_point_ups\\]').click(function (e) {
			const field = jQuery(this);
			e.preventDefault();

			var apaczkaMap = new ApaczkaMap({
				app_id: Math.random() * 9999999,
				onChange: function (record) {
					if (record) {
						field.val(record.foreign_access_point_id);
						jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(record.foreign_access_point_id);
					}
				}
			});
			apaczkaMap.setFilterSupplierAllowed(
				['UPS']
			);
			apaczkaMap.show();
		});

		jQuery('#_apaczka_dispath_point_dpd').click(function (e) {
			const field = jQuery(this);
			e.preventDefault();

			var apaczkaMap = new ApaczkaMap({
				app_id: Math.random() * 9999999,
				onChange: function (record) {
					if (record) {
						field.val(record.foreign_access_point_id);
						jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val(record.foreign_access_point_id);
					}
				}
			});
			apaczkaMap.setFilterSupplierAllowed(
				['DPD']
			);
			apaczkaMap.show();
		});

		jQuery('#_apaczka\\[delivery_point_id\\]').click(function (e) {
			let field = jQuery(this);
			e.preventDefault();
			let cod_amount_defined = is_cod_amount_defined();
			var apaczkaMap = new ApaczkaMap({
				app_id: Math.random() * 9999999,
				criteria: [
					cod_amount_defined ?
						{
							field: 'services_cod',
							operator: 'eq',
							value: true
						} : null,
					{field: 'services_receiver', operator: 'eq', value: true}
				],
				hideServicesCod: cod_amount_defined,
				onChange: function (record) {
					if (record) {
						console.log(record.foreign_access_point_id);
						field.val(record.foreign_access_point_id);
						//jQuery('#_apaczka_apm_access_point_id').val(record.foreign_access_point_id);
						jQuery('input[id=_apaczka_apm_access_point_id]').each(function(ind, elem) {
							jQuery(elem).val(record.foreign_access_point_id);
						});
						
						//jQuery('#_apaczka_apm_supplier').val(record.supplier);
						jQuery('input[id=_apaczka_apm_supplier]').each(function(ind, elem) {
							jQuery(elem).val(record.supplier);
						});
						
						//jQuery('#_apaczka_apm_name').val(record.name);
						jQuery('input[id=_apaczka_apm_name]').each(function(ind, elem) {
							jQuery(elem).val(record.name);
						});
						
						//jQuery('#_apaczka_apm_foreign_access_point_id').val(record.foreign_access_point_id);
						jQuery('input[id=_apaczka_apm_foreign_access_point_id]').each(function(ind, elem) {
							jQuery(elem).val(record.foreign_access_point_id);
						});
						
						//jQuery('#_apaczka_apm_street').val(record.street);
						jQuery('input[id=_apaczka_apm_street]').each(function(ind, elem) {
							jQuery(elem).val(record.street);
						});
						
						//jQuery('#_apaczka_apm_city').val(record.city);
						jQuery('input[id=_apaczka_apm_city]').each(function(ind, elem) {
							jQuery(elem).val(record.city);
						});
						
						//jQuery('#_apaczka_apm_postal_code').val(record.postal_code);
						jQuery('input[id=_apaczka_apm_postal_code]').each(function(ind, elem) {
							jQuery(elem).val(record.postal_code);
						});
						
						//jQuery('#_apaczka_apm_country_code').val(record.country_code);
						jQuery('input[id=_apaczka_apm_country_code]').each(function(ind, elem) {
							jQuery(elem).val(record.country_code);
						});
					}
				}
			});
			apaczkaMap.setFilterSupplierAllowed(
				[serviceIdToApmSupplierId(parseInt(apaczka_calculate_selected_service))]
			);
			apaczkaMap.show();
		});

		jQuery.post(ajaxurl, data, function (response) {
			if (response != 0) {
				response = JSON.parse(response);
				console.log(response.status);
				console.log(response);
				if (response.status === 'ok') {
					jQuery('#apaczka_error').html('');
					jQuery("#apaczka-calculate-wrapper").html(jQuery.parseHTML(response.calculate_html));
					jQuery("#apaczka_send").prop("disabled", false);
					//jQuery('.apaczka_set_order_completed-wrapper').removeClass('apaczka-hidden');
					changeShippingMethodTriggers(jQuery('#_apaczka\\[package_properties\\]\\[shipping_method\\]').val())

					// jQuery('.apaczka-calculate-item[data-item="0"]').click();
				} else {
					console.log(response.status);
					console.log(response.error_messages);

					jQuery("#apaczka-calculate-wrapper").html('');
					jQuery('#apaczka_error').html(response.error_messages);
					jQuery("#apaczka_send").prop("disabled", true);
					//jQuery('.apaczka_set_order_completed-wrapper').addClass('apaczka-hidden');

				}
				jQuery(this).parent().find(".spinner").removeClass('is-active');
				jQuery(this).parent().find(".spinner_calculate").show();
				jQuery('.apaczka_calculate_price').attr('disabled', false);

				return false;
			} else {
				//console.log('Invalid response.');
				jQuery('#apaczka_error').html('Invalid response.');
			}


		});

		return false;
	});


	jQuery(document).on("click", '.apaczka_calculate_radio', function (event) {
		apaczka_calculate_selected_service = jQuery('input[name="apaczka_calculate_radio"]:checked').val();
		handleCalculateDynamicFields(parseInt(apaczka_calculate_selected_service))
	});

	<?php if ( true === $package_send ) : ?>

	jQuery(document).on("click", '#apaczka_get_waybill', function (event) {
        event.preventDefault();
        let apaczka_waybill_id = "<?php echo esc_attr( $apaczka_wc_order_data['apaczka_order']->id ); ?>";
        let apaczka_get_waybill_link = document.location.toString() + '&apaczka_get_waybill=' + apaczka_waybill_id;
        window.location.href = apaczka_get_waybill_link;
    });


	jQuery("#apaczka_cancel").click(function () {

		const cancelled_only_on_apaczka_pl = new Array('260', '220', '250', '230', '240', '150');

		let service_id = jQuery('#apaczka_choosed_carrier_id').attr('data-id');
		let service_name = jQuery('#apaczka_choosed_carrier_id').text();

		if(service_id) {
			if (jQuery.inArray(service_id, cancelled_only_on_apaczka_pl) !== -1) {
				jQuery('#apaczka_alert_modal').addClass('active');
				return false;
			}
		}

		jQuery(this).attr('disabled', true);
		jQuery(this).parent().find(".spinner_courier").hide();
		jQuery(this).parent().find(".spinner").hide();
		jQuery(this).parent().find(".spinner_calculate").addClass('is-active');

		var data = {
			action: 'apaczka',
			order_id: <?php echo esc_attr( $order_id ); ?>,
			apaczka_action: 'cancel_package',
			security: apaczka_ajax_nonce,
		};

		jQuery.post(ajaxurl, data, function (response) {
			if (response != 0) {
				response = JSON.parse(response);
				console.log(response.status);
				if (response.status === 'ok') {
					window.location.reload();
				} else {
					console.log(response.status);
					console.log(response.error_messages);
					jQuery('#apaczka_error').html(response.error_messages);
					jQuery('#apaczka_cancel').attr('disabled', false);

				}
				jQuery(this).parent().find(".spinner").removeClass('is-active');
				jQuery(this).parent().find(".spinner_calculate").show();


				return false;
			} else {
				jQuery('#apaczka_cancel').attr('disabled', false);
				jQuery('#apaczka_error').html('Invalid response.');
			}


		});

	});

	jQuery(document).on("click", '#apaczka_download_turn_in', function (event) {
		var data = {
			action: 'apaczka',
			order_id: <?php echo esc_attr( $order_id ); ?>,
			apaczka_action: 'download_turn_in',
			security: apaczka_ajax_nonce,
		};

		jQuery.post(ajaxurl, data, function (response) {
			if (response != 0) {
				response = JSON.parse(response);
				console.log(response.status);
				if (response.status === 'ok') {
					jQuery('#apaczka_error').html('');
					var win = window.open();
					win.document.write('<iframe src="data:application/pdf;base64,' + response.base64 + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen></iframe>');
				} else {
					console.log(response.status);
					console.log(response.error_messages);


					jQuery('#apaczka_error').html(response.error_messages);
				}
				jQuery(this).parent().find(".spinner").removeClass('is-active');
				jQuery(this).parent().find(".spinner_calculate").show();
				jQuery('.apaczka_calculate_price').attr('disabled', false);

				return false;
			} else {
				jQuery('#apaczka_error').html('Invalid response.');
			}


		});


		return false;
	});

	<?php endif; ?>


	jQuery("#apaczka_send").click(function () {

		if (false !== apaczka_dispath_point_handler_to_validate) {
			if ('' === apaczka_dispath_point_handler_to_validate.val()) {
				alert("Punkt nadania nie może być pusty.");
				return false;
			}
		}

		let $delivery_point_id;
		if (jQuery('#apaczka_delivery_point_id_wrapper').hasClass('apaczka-hidden')) {
			$delivery_point_id = ''
		} else {
			$delivery_point_id = jQuery('#_apaczka\\[delivery_point_id\\]').val();
			if ('' === $delivery_point_id) {
				alert("Punkt odbioru nie może być pusty.");
				return false;
			}
		}


		if (!jQuery(this).closest("form")[0].checkValidity()) {
			jQuery(this).closest("form")[0].reportValidity();
			return false;
		}

		jQuery(this).attr('disabled', true);
		jQuery(this).parent().find(".spinner_courier").hide();
		jQuery(this).parent().find(".spinner").hide();
		jQuery(this).parent().find(".spinner_calculate").addClass('is-active');



		var data = {
			action: 'apaczka',
			order_id: <?php echo esc_attr( $order_id ); ?>,
			apaczka_action: 'create_package',
			security: apaczka_ajax_nonce,
			//apaczka_order_status_completed: false,
			apaczka: {
				sender: {},
				receiver: {},
				package_properties: {},
				additional_options: {},
				delivery_point: {},
				delivery_point_id: $delivery_point_id,
				selected_service: apaczka_calculate_selected_service,
			}
		};

		var package_properties = jQuery('input[id^="_apaczka[package_properties]"], select[id^="_apaczka[package_properties]"]');
		var sender = jQuery('input[id^="_apaczka[sender]"]');


		var additional_options = jQuery('input[id^="_apaczka[additional_options]"]');
		var receiver = jQuery('input[id^="_apaczka[receiver]"]');
		var delivery_point = jQuery('input[name^="_apaczka[delivery_point]"]');
		var is_residential_sender = jQuery('select[id="_apaczka[sender][is_residential]"]').val();
		var is_residential_receiver = jQuery('select[id="_apaczka[receiver][is_residential]"]').val();

		receiver.each(function () {
			data.apaczka.receiver[jQuery(this).data('key')] = jQuery(this).val()
		});
		data.apaczka.receiver['is_residential'] = is_residential_receiver;

		sender.each(function () {
			data.apaczka.sender[jQuery(this).data('key')] = jQuery(this).val()
		});
		data.apaczka.sender['is_residential'] = is_residential_sender;

		data.apaczka.sender['apm_foreign_access_point_id'] = jQuery('#_apaczka\\[sender\\]\\[apm_foreign_access_point_id\\]').val();
		// fix #25653
		if(apaczka_calculate_selected_service === '42') {
			data.apaczka.sender['apm_foreign_access_point_id'] = '';
		}

		// fix #25629
		let shipping_method = jQuery('#_apaczka\\[package_properties\\]\\[shipping_method\\]').val();
		if(apaczka_calculate_selected_service === '41' && 'COURIER' === shipping_method) {
			data.apaczka.sender['apm_foreign_access_point_id'] = '';
		}

		package_properties.each(function () {
			data.apaczka.package_properties[jQuery(this).data('key')] = jQuery(this).val();
		});

		additional_options.each(function () {
			data.apaczka.additional_options[jQuery(this).data('key')] = jQuery(this).val();
		});

		delivery_point.each(function () {
			data.apaczka.delivery_point[jQuery(this).data('key')] = jQuery(this).val();
		});

		console.log(delivery_point);

		//data.apaczka_order_status_completed = $('input[name="apaczka_set_order_completed"]').is(':checked');

		jQuery.post(ajaxurl, data, function (response) {
			if (response != 0) {
				response = JSON.parse(response);
				if (response.status === 'ok') {
					window.location.reload();
				} else {
					console.log(response.status);
					console.log(response.error_messages);
					jQuery('#apaczka_error').html(response.error_messages);
					jQuery('.apaczka_send').attr('disabled', false);

				}
				jQuery(this).parent().find(".spinner").removeClass('is-active');
				jQuery(this).parent().find(".spinner_calculate").show();


				return false;
			} else {
				//console.log('Invalid response.');
				jQuery('#apaczka_error').html('Invalid response.');
				jQuery('.apaczka_send').attr('disabled', false);
			}


		});


		return false;
	});


</script>
