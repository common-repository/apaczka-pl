<?php
/**
 * @var array $calculate
 */

$skip_this_service = [27, 20, 81];

use Inspire_Labs\Apaczka_Woocommerce\Shipping_Method_Apaczka;

?>

<fieldset id="apaczka_calculate_radio">
	<?php $first = true ?>
	<?php $i = 0 ?>
	<?php foreach ( $calculate as $k => $service ): ?>
		<?php $checked = '' ?>
		<?php if ( in_array( $k, $skip_this_service ) ): continue; endif; ?>
        <?php if ( $apaczka_order['address']['sender']['is_residential'] === 1 && $k === 150 ): continue; endif; // exclude Geis for adres Prywatny ?>
        <label class="apaczka-calculate-item selected"
               onclick="addBorder()"
               data-item="<?php echo esc_attr($i) ?>"
               data-pickup_courier="<?php echo esc_attr( $service['pickup_courier'] ); ?>"
               data-service_id="<?php echo esc_attr( $k ); ?>"
               data-supplier="<?php echo esc_attr( $service['supplier'] ); ?>">
            <p class="apaczka-logo-wrapper <?php echo 'service_id_' . esc_attr( $k ); ?>">
                <?php
                $apaczka = new Shipping_Method_Apaczka();
                $logo = $apaczka->get_logo( $k );
                $logo_src = '';
                if( $logo ) {
                    $logo_src = apaczka()->get_plugin_img_url() . '/' . $logo . '.png';
                }
                ?>
                <img class="apaczka-service-logo" src="<?php echo esc_url( $logo_src ); ?>" style="">
            </p>
            <p>
				<?php echo str_replace(
					[
						'Drzwi -',
						'Punkt -',
					],
					[
						'<br>Drzwi -',
						'<br>Punkt -',
					],
					esc_attr($service['name'] )) ?>
            </p>

            <div class="apaczka-calculate-item-price-wrapper">
                <p>
					<?php echo esc_html( Shipping_Method_Apaczka::format_calculate_price( $service['price'] ) ); ?>
                    zł netto
                </p>
                <p>
					<?php echo esc_html( Shipping_Method_Apaczka::format_calculate_price( $service['price_gross'] ) ); ?>
                    zł brutto
                </p>
                <!--</div>-->
                <input
                        type="radio"
                        value="<?php echo esc_attr( $k ); ?>"
                        id="apaczka_calculate_radio_<?php echo esc_attr( $k ); ?>"
                        class="apaczka_calculate_radio"
                        name="apaczka_calculate_radio"
                >
				<?php $i ++ ?>
            </div>
        </label>
		<?php $first = false ?>
	<?php endforeach; ?>

</fieldset>
