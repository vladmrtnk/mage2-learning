define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Ui/js/model/messages',
    'mage/translate',
], function (
    $,
    wrapper,
    placeOrderAction,
    redirectOnSuccessAction,
    Messages,
) {
    'use strict';

    return function (paymentService) {
        paymentService.setPaymentMethods = wrapper.wrapSuper(paymentService.setPaymentMethods, function (methods) {

            this._super(methods);
            var config = window.checkoutConfig;
            var messageContainer = new Messages();
            var countPayments = methods.length;

            if (countPayments === 1 && config.oneStepCheckout) {
                var payment = methods[0]['method'];
                var data = {
                    'method': payment,
                    'po_number': null,
                    'additional_data': null
                };

                placeOrderAction(data, messageContainer);
                redirectOnSuccessAction.execute();
            }
        });

        return paymentService;
    };
});