<?php

namespace Inspire_Labs\Apaczka_Woocommerce;

use WC_Shipping_Method;
use WC_Shipping_Rate;

class Apaczka_Shipping_Rates
{

    public function init()
    {
        add_action('woocommerce_after_shipping_rate', function ($method, $index) {
            /**
             * @var WC_Shipping_Rate $method
             */
            if (strpos($method->get_method_id(), 'apaczka') === false) {
                return;
            }

        }, 10, 2);
    }
}
