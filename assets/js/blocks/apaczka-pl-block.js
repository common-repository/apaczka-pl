(() => {
    "use strict";
    const e = window.wc.blocksCheckout, t = window.wp.element, a = window.wp.i18n,
        n = window.wp.data, {ExperimentalOrderMeta: l} = wc.blocksCheckout;

    function o({handleDeliveryPointChange: e, apaczkaDeliveryPoint: a}) {
        return (0, t.createElement)("div", {
            className: "apaczka-delivery-point-wrap",
            style: {display: "none"}
        }, (0, t.createElement)("input", {value: a, type: "text", id: "apaczka-point", onChange: e, required: !0}))
    }

    const i = JSON.parse('{"apiVersion":2,"name":"apaczka-pl/iapaczka-pl-block","version":"2.0.0","title":"Apaczka PL Shipping Options Block","category":"woocommerce","description":"Adds map button abd input to save delivery point data.","supports":{"html":false,"align":false,"multiple":false,"reusable":false},"parent":["woocommerce/checkout-shipping-methods-block"],"attributes":{"lock":{"type":"object","default":{"remove":true,"move":true}},"text":{"type":"string","source":"html","selector":".wp-block-apaczka-pl","default":""}},"textdomain":"apaczka-pl","editorStyle":""}');
    (0, e.registerCheckoutBlock)({
        metadata: i, component: ({checkoutExtensionData: e, extensions: i}) => {
            let c = !1, s = null;
            const [p, r] = (0, t.useState)(""), {setExtensionData: d} = e, u = "apaczka-delivery-point-error", {
                setValidationErrors: k,
                clearValidationError: m
            } = (0, n.useDispatch)("wc/store/validation");
            let w = (0, n.useSelect)((e => e("wc/store/cart").getShippingRates()));
            if (null != w) {
                let e = w[Object.keys(w)[0]];
                if (null != e && e.hasOwnProperty("shipping_rates")) {
                    const t = e.shipping_rates, a = [];
                    if (null != t) {
                        for (let e of t) "pickup_location" !== e.method_id && (!0 === e.selected && (s = e.instance_id), a.push(e));
                        if (!s && a.length > 0) {
                            const e = document.getElementsByClassName("wc-block-components-shipping-rates-control")[0];
                            if (null != e) {
                                const t = e.querySelector('input[name^="radio-control-"]:checked');
                                if (null != t) {
                                    let e = t.getAttribute("id");
                                    if (null != e) {
                                        let t = e.split(":");
                                        s = t[t.length - 1]
                                    }
                                }
                            }
                        }
                    }
                }
            }
            const h = window.apaczka_block && window.apaczka_block.map_config ? window.apaczka_block.map_config : {};
            null != h && 0 !== Object.keys(h).length && h.hasOwnProperty(s) && (c = !0);
            const g = (0, t.useCallback)((() => {
                c && !p && k({[u]: {message: (0, a.__)("Delivery point must be chosen!", "apaczka-pl"), hidden: !0}})
            }), [p, k, m, c]), _ = (0, t.useCallback)((() => {
                if (p || !c) return m(u), !0
            }), [p, k, m, c]);
            return (0, t.useEffect)((() => {
                g(), _(), d("apaczka_pl", "apaczka-point", p)
            }), [p, d, _]), (0, t.createElement)(t.Fragment, null, c && (0, t.createElement)(t.Fragment, null, (0, t.createElement)("div", {
                className: "button alt geowidget_show_map",
                id: "geowidget_show_map"
            }, (0, a.__)("Wybierz punkt", "apaczka-pl")), (0, t.createElement)("div", {
                id: "apaczka_selected_point_data_wrap",
                className: "apaczka_selected_point_data_wrap",
                style: {display: "none"}
            }), (0, t.createElement)(l, null, (0, t.createElement)(o, {
                apaczkaDeliveryPoint: p,
                handleDeliveryPointChange: e => {
                    const t = e.target.value;
                    r(t)
                }
            }))))
        }
    })
})();