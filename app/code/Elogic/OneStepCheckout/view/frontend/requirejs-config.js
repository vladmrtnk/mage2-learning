/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins : {
            'Magento_Checkout/js/model/payment-service': {
                'Elogic_OneStepCheckout/js/payment-service-mixin': true
            }
        }
    }
};
