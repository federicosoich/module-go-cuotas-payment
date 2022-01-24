define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'gocuotas',
                component: 'FS_GoCuotas/js/view/payment/method-renderer/gocuotas'
            }
        );
        return Component.extend({});
    }
);
