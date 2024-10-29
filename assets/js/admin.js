var apaczka_parcel_type_handle = '';
var apaczka_parcel_depth = '';
var apaczka_parcel_width = '';
var apaczka_parcel_height = '';





function validateShippingPointSelected() {

	const PocztaKurier48 = 160;
	const PocztaKurier48Punkty = 162;
	const AllegroSMARTKurier48Punkty = 164;
	const AllegroSMARTPocztaKurier48 = 165;
	const AllegroSMARTPaczkomatInPost = 40;
	const PaczkomatInPost = 41;
	const UPSAPPunktDrzwi = 13;
	const UPSAPPunktPunkt = 14;
    const DPD_kurier = 21;


	let selectedService = parseInt(jQuery('#apaczka_woocommerce_settings_general_service').val());
	let dispathPointInpostSelected = jQuery('#apaczka_woocommerce_settings_general_dispath_point_inpost').val() !== '';
	let dispathPointKurier48Selected = jQuery('#apaczka_woocommerce_settings_general_dispath_point_kurier48').val() !== '';
	let dispathPointUpsSelected = jQuery('#apaczka_woocommerce_settings_general_dispath_point_ups').val() !== '';
    let dispathPointDPDSelected = jQuery('#apaczka_woocommerce_settings_general_dispath_point_dpd').val() !== '';

	let error = null;

	if ((selectedService === PocztaKurier48
			|| selectedService === PocztaKurier48Punkty
			|| selectedService === AllegroSMARTKurier48Punkty
			|| selectedService === AllegroSMARTPocztaKurier48
		) &&
		!dispathPointKurier48Selected
	) {
		error = '#apaczka_woocommerce_settings_general_dispath_point_kurier48';
	}

	if ((selectedService === AllegroSMARTPaczkomatInPost
			|| selectedService === PaczkomatInPost
		) &&
		!dispathPointInpostSelected
	) {
		error = '#apaczka_woocommerce_settings_general_dispath_point_inpost';
	}

	if ((selectedService === UPSAPPunktPunkt
			|| selectedService === UPSAPPunktPunkt
		) &&
		!dispathPointUpsSelected
	) {
		error = '#apaczka_woocommerce_settings_general_dispath_point_ups';
	}

	if (error !== null) {
		jQuery(error).addClass('apaczka_missing_field');

		if (error !== '#apaczka_woocommerce_settings_general_dispath_point_ups') {
			jQuery('#apaczka_woocommerce_settings_general_dispath_point_ups').removeClass('apaczka_missing_field');
		}
		if (error !== '#apaczka_woocommerce_settings_general_dispath_point_inpost') {
			jQuery('#apaczka_woocommerce_settings_general_dispath_point_inpost').removeClass('apaczka_missing_field');
		}
		if (error !== '#apaczka_woocommerce_settings_general_dispath_point_kurier48') {
			jQuery('#apaczka_woocommerce_settings_general_dispath_point_kurier48').removeClass('apaczka_missing_field');
		}
        if (error !== '#apaczka_woocommerce_settings_general_dispath_point_dpd') {
            jQuery('#apaczka_woocommerce_settings_general_dispath_point_dpd').removeClass('apaczka_missing_field');
        }

		jQuery('.button-primary.woocommerce-save-button').prop('disabled', 'disabled');
	} else {
		jQuery('#apaczka_woocommerce_settings_general_dispath_point_kurier48').removeClass('apaczka_missing_field');
		jQuery('#apaczka_woocommerce_settings_general_dispath_point_ups').removeClass('apaczka_missing_field');
		jQuery('#apaczka_woocommerce_settings_general_dispath_point_inpost').removeClass('apaczka_missing_field');
        jQuery('#apaczka_woocommerce_settings_general_dispath_point_dpd').removeClass('apaczka_missing_field');
		jQuery('.button-primary.woocommerce-save-button').prop('disabled', '');
	}

}


jQuery(document).ready(function () {
	jQuery("#apaczka_woocommerce_settings_general_service").change(function (e) {

		validateShippingPointSelected()
	});
});
jQuery(document).ready(function () {
	jQuery("#apaczka_woocommerce_settings_general_dispath_point_ups").change(function (e) {

		validateShippingPointSelected();
	});
});
jQuery(document).ready(function () {
	jQuery("#apaczka_woocommerce_settings_general_dispath_point_kurier48").change(function (e) {

		validateShippingPointSelected();
	});
});
jQuery(document).ready(function () {
	jQuery("#apaczka_woocommerce_settings_general_dispath_point_inpost").change(function (e) {

		validateShippingPointSelected();
	});
});
jQuery(document).ready(function () {
    jQuery("#apaczka_woocommerce_settings_general_dispath_point_dpd").change(function (e) {

        validateShippingPointSelected();
    });
});


jQuery(document).ready(function () {
	if (jQuery('#apaczka_woocommerce_settings_general_parcel_type').length) {
		apaczka_parcel_type_handle = jQuery('#apaczka_woocommerce_settings_general_parcel_type');
		apaczka_parcel_depth = jQuery('#apaczka_woocommerce_settings_general_package_depth');
		apaczka_parcel_width = jQuery('#apaczka_woocommerce_settings_general_package_width');
		apaczka_parcel_height = jQuery('#apaczka_woocommerce_settings_general_package_height');
	} else {
		apaczka_parcel_type_handle = jQuery('#_apaczka\\[package_properties\\]\\[parcel_type\\]');
		apaczka_parcel_depth = jQuery('#_apaczka\\[package_properties\\]\\[package_depth\\]');
		apaczka_parcel_width = jQuery('#_apaczka\\[package_properties\\]\\[package_width\\]');
		apaczka_parcel_height = jQuery('#_apaczka\\[package_properties\\]\\[package_height\\]');
	}

	changeParcelTypeActions(apaczka_parcel_type_handle.val());

	jQuery('#apaczka_woocommerce_settings_general_dispath_point_inpost').click(function (e) {
		const field = jQuery(this);
		e.preventDefault();

		var apaczkaMap = new ApaczkaMap({
			app_id: Math.random() * 9999999,
			onChange: function (record) {
                if (record) {
                    field.val(record.foreign_access_point_id);
                    jQuery('#apaczka_woocommerce_settings_general_dispath_point_inpost').removeClass('apaczka_missing_field');
                    jQuery('.button-primary.woocommerce-save-button').prop('disabled', '');
                }
			}
		});
		apaczkaMap.setFilterSupplierAllowed(
			['INPOST']
		);
		apaczkaMap.show();
	});

	jQuery('#apaczka_woocommerce_settings_general_dispath_point_kurier48').click(function (e) {
		const field = jQuery(this);
		e.preventDefault();

		var apaczkaMap = new ApaczkaMap({
			app_id: Math.random() * 9999999,
			onChange: function (record) {
				if (record) {
					field.val(record.foreign_access_point_id);
				}
			}
		});
		apaczkaMap.setFilterSupplierAllowed(
			['POCZTA']
		);
		apaczkaMap.show();
	});

	jQuery('#apaczka_woocommerce_settings_general_dispath_point_ups').click(function (e) {
		const field = jQuery(this);
		e.preventDefault();

		var apaczkaMap = new ApaczkaMap({
			app_id: Math.random() * 9999999,
			onChange: function (record) {
				if (record) {
					field.val(record.foreign_access_point_id);
				}
			}
		});
		apaczkaMap.setFilterSupplierAllowed(
			['UPS']
		);
		apaczkaMap.show();
	});

    jQuery('#apaczka_woocommerce_settings_general_dispath_point_dpd').click(function (e) {
        const field = jQuery(this);
        e.preventDefault();

        var apaczkaMap = new ApaczkaMap({
            app_id: Math.random() * 9999999,
            onChange: function (record) {
                if (record) {
                    field.val(record.foreign_access_point_id);
                }
            }
        });
        apaczkaMap.setFilterSupplierAllowed(
            ['DPD']
        );
        apaczkaMap.show();
    });

	apaczka_parcel_type_handle.change(function (e) {
		changeParcelTypeActions(jQuery(this).val())
	});

	function changeParcelTypeActions(type) {
		switch (type) {
			case 'europalette':
				apaczka_parcel_width.val(120);
				apaczka_parcel_depth.val(80);
				apaczka_parcel_depth.prop('readonly', true);
				apaczka_parcel_width.prop('readonly', true);

				if (apaczka_parcel_height.val() > 220) {
					apaczka_parcel_height.val(220);
				}

				apaczka_parcel_height.prop('max', 220);
				break;
			case 'palette_60x80':
				apaczka_parcel_width.val(60);
				apaczka_parcel_depth.val(80);
				apaczka_parcel_depth.prop('readonly', true);
				apaczka_parcel_width.prop('readonly', true);

				if (apaczka_parcel_height.val() > 220) {
					apaczka_parcel_height.val(220)
				}

				apaczka_parcel_height.prop('max', 220);
				break;
            case 'palette_120x100':
                apaczka_parcel_depth.val(100);
                apaczka_parcel_width.val(120);
                apaczka_parcel_depth.prop('readonly', true);
                apaczka_parcel_width.prop('readonly', true);

                if (apaczka_parcel_height.val() > 220) {
                    apaczka_parcel_height.val(220)
                }


                apaczka_parcel_height.prop('max', 220);
                break;
			case 'palette_120x120':
				apaczka_parcel_depth.val(120);
				apaczka_parcel_width.val(120);
				apaczka_parcel_depth.prop('readonly', true);
				apaczka_parcel_width.prop('readonly', true);

				if (apaczka_parcel_height.val() > 220) {
					apaczka_parcel_height.val(220)
				}


				apaczka_parcel_height.prop('max', 220);
				break;
			default:
				apaczka_parcel_depth.prop('readonly', false);
				apaczka_parcel_width.prop('readonly', false);
				apaczka_parcel_height.removeAttr('max')
		}
	}
});

//Add custom class to checked label
function addBorder() {
	const elements = document.querySelectorAll("#apaczka_calculate_radio > .apaczka-calculate-item > .apaczka-calculate-item-price-wrapper > input[type='radio']");
	if (elements) {
		elements.forEach((element) => {
			if (element.checked) {
				element.closest(".apaczka-calculate-item").classList.toggle("selected");
				element.setAttribute("checked", "checked");
			}
			document.querySelectorAll("#apaczka_calculate_radio > .apaczka-calculate-item > .apaczka-calculate-item-price-wrapper > input[type='radio']").forEach((element) => {
				if (element.checked === false) {
					element.closest(".apaczka-calculate-item").classList.remove("selected");
					element.removeAttribute("checked");
				}
			})
		});
	}
}

jQuery(document).ready(function () {
	jQuery('.apaczka_calculate_price').click(function (e) {
		addBorder();
	});
});

//Not working here but working if run this code in console browser
jQuery(document).ready(function () {
	jQuery("#apaczka_calculate_radio > .apaczka-calculate-item > input[type='radio']").change(function (e) {
		console.log("change", e);
	});
});

jQuery(document).ready(function(){
    jQuery('body #woocommerce-order-downloads .buttons .select2-container').css("width", "100% !important");
});


jQuery(document).ready(function(){
    jQuery('#apaczka_calculate_price_btn').click(function (e) {
            var observer = new MutationObserver(function(mutations) {
            if (jQuery("#apaczka_calculate_radio").length) {
                jQuery("#apaczka_calculate_radio > .apaczka-calculate-item:first-child").click();
                jQuery("#apaczka_calculate_radio > .apaczka-calculate-item:first-child").click();
                const valOfFirstItem = jQuery("#apaczka_calculate_radio > .apaczka-calculate-item > .apaczka-calculate-item-price-wrapper > input[type='radio']:checked").val();
                console.log("valOfFirstItem", valOfFirstItem);
                apaczka_calculate_selected_service = valOfFirstItem;
                handleCalculateDynamicFields(parseInt(valOfFirstItem));
                observer.disconnect(); 
                //We can disconnect observer once the element exist if we dont want observe more changes in the DOM
            }
        });

        // Start observing
        observer.observe(document.body, { //document.body is node target to observe
            childList: true, //This is a must have for the observer with subtree
            subtree: true //Set to true if changes must also be observed in descendants.
        });
	});
});

document.addEventListener( 'click', function (e) {
    e = e || window.event;
    var target = e.target || e.srcElement;

    if ( target.classList.contains( 'apaczka_calculate_radio' ) ) {
        if( typeof( target.value ) !== 'undefined' ) {
            let id = +target.value;
            if (id === 50 || id === 86) {
                jQuery('#deliver_to_any_shipping_point').show();
            } else {
                jQuery('#deliver_to_any_shipping_point').hide();
            }
        }
    }
}, false );


jQuery(document).ready(function () {
    //validation postal code
    let sender_zip_code = jQuery('input[name="apaczka_woocommerce_settings_general_sender_postal_code"]');
    let sender_zip_code_order = jQuery('input[name="_apaczka[sender][postal_code]"]');
    if(typeof sender_zip_code != 'undefined' ) {
        jQuery(sender_zip_code).mask("99-999",{placeholder:"XX-XXX"});
    }
    if(typeof sender_zip_code_order != 'undefined' ) {
        jQuery(sender_zip_code_order).mask("99-999",{placeholder:"XX-XXX"});
    }

    let iban = jQuery('input[name="apaczka_woocommerce_settings_general_sender_bank_account_number"]');
    let iban_order = jQuery('input[name="_apaczka[sender][bank_account_number]"]');
    if(typeof iban != 'undefined' ) {
        jQuery(iban).mask('99 9999 9999 9999 9999 9999 9999', {
            placeholder: '__ ____ ____ ____ ____ ____ ____'
        });
    }
    if(typeof iban_order != 'undefined' ) {
        jQuery(iban_order).mask('99 9999 9999 9999 9999 9999 9999', {
            placeholder: '__ ____ ____ ____ ____ ____ ____'
        });
    }

    let sender_phone_number = jQuery('input[name="apaczka_woocommerce_settings_general_sender_phone"]');
    let sender_phone_number_order = jQuery('input[name="_apaczka[sender][phone]"]');
    /*
	if(typeof sender_phone_number != 'undefined' ) {
        jQuery(sender_phone_number).mask("999999999",{placeholder:" "});
    }
    if(typeof sender_phone_number_order != 'undefined' ) {
        jQuery(sender_phone_number_order).mask("999999999",{placeholder:" "});
    }
	*/
});
