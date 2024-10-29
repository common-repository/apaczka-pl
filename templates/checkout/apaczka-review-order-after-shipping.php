<?php

use Inspire_Labs\Apaczka_Woocommerce\Plugin;

$parcel_machine_selected = false;
$selected                = '';


?>
<tr class="apaczka-parcel-machine">
	<th class="apaczka-parcel-machine-label">
		<?php __( 'Select point', 'apaczka-pl' ); ?>
	</th>
	<td class="apaczka-parcel-machine-select">


		<?php if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) : ?>

			<?php $randomId = 'id' . rand( 1, 9999 ); ?>
			<a id="popup-btn"></a>


			<input type="button"
					class="button alt"
					name="geowidget_show_map"
					id="geowidget_show_map"
					value="<?php echo esc_html__( 'Select point', 'apaczka-pl' ); ?>"
					data-value="<?php echo esc_html__( 'Select point', 'apaczka-pl' ); ?>"
			>

			<script type="text/javascript">
				var initiated = false;

				jQuery(document).ready(function () {
						if (false === initiated) {
							jQuery('#geowidget_show_map').click(function (e) {
								e.preventDefault();

								var apaczkaMap = new ApaczkaMap({
									app_id: 'demo',
									onChange: function (record) {
										if (record) {
											console.log(record);
											jQuery('#apm_access_point_id').val(record.foreign_access_point_id);
											jQuery('#apm_supplier').val(record.supplier);
											jQuery('#apm_name').val(record.name);
											jQuery('#apm_foreign_access_point_id').val(record.foreign_access_point_id);
											jQuery('#apm_street').val(record.street);
											jQuery('#apm_city').val(record.city);
											jQuery('#apm_postal_code').val(record.postal_code);
											jQuery('#apm_country_code').val(record.country_code);

											jQuery('#selected-parcel-machine').removeClass('apaczka-hidden');
											jQuery('#selected-parcel-machine-id').html(record.foreign_access_point_id);
											jQuery('#selected-parcel-machine-desc').html(record.name);
										}
									}
								});
								apaczkaMap.setFilterSupplierAllowed(
									[<?php echo sanitize_text_field( $point_id ); ?>]
								);

								let country_code = 'PL';
								let shipping_country = jQuery('#shipping_country');
								if (typeof shipping_country != 'undefined' && shipping_country !== null) {
									country_code = jQuery(shipping_country).val();
									if (typeof country_code != 'undefined' && country_code !== null) {
										apaczkaMap.setCountryCode(country_code);
									} else {
										let billing_country = jQuery('#billing_country');
										if (typeof billing_country != 'undefined' && billing_country !== null) {
											country_code = jQuery(billing_country).val();
											if (typeof country_code != 'undefined' && country_code !== null) {
												apaczkaMap.setCountryCode(country_code);
											}
										}
									}
								}

								<?php if ( $only_cod ) { ?>
									apaczkaMap.show();
									console.log('Apaczka: tylko COD punkty');
									apaczkaMap.filter_services_cod = true;
									jQuery('.apaczkaMapFilterCod').addClass('selected');
								<?php } else { ?>
									apaczkaMap.show();
								<?php } ?>

								initiated = true;
							});

						}
					}
				);
			</script>

			<div id="selected-parcel-machine" class="apaczka-hidden">
				<div><span class="font-height-600">
				<?php echo esc_html__( 'Selected Parcel Locker:', 'apaczka-pl' ); ?>
				</span></div>
				<span class="italic" id="selected-parcel-machine-id"></span>
				<span class="italic" id="selected-parcel-machine-desc"></span>
			</div>

			<input type="hidden" id="apm_access_point_id" name="apm_access_point_id"/>
			<input type="hidden" id="apm_supplier" name="apm_supplier"/>
			<input type="hidden" id="apm_name" name="apm_name"/>
			<input type="hidden" id="apm_foreign_access_point_id" name="apm_foreign_access_point_id"/>
			<input type="hidden" id="apm_street" name="apm_street"/>
			<input type="hidden" id="apm_city" name="apm_city"/>
			<input type="hidden" id="apm_postal_code" name="apm_postal_code"/>
			<input type="hidden" id="apm_country_code" name="apm_country_code"/>


		<?php else : ?>

		<?php endif ?>


	</td>
</tr>
