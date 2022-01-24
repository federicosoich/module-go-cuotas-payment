define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function ($,Component,urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'FS_GoCuotas/payment/gocuotas',
                redirectAfterPlaceOrder: false
            },
            afterPlaceOrder: function (url) {
                window.location.replace(urlBuilder.build('gocuotas/payment/redirect/'));
            },
            getMessage: function(){
                return window.checkoutConfig.payment.gocuotas.message;
            }
        });
    }
);