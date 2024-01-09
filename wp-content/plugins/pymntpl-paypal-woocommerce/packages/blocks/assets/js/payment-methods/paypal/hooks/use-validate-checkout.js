import {useEffect} from '@wordpress/element';
import {__} from '@wordpress/i18n';

export const useValidateCheckout = (
    {
        isExpress,
        paymentData,
        onCheckoutValidation
    }) => {
    useEffect(() => {
        if (!isExpress) {
            const unsubscribe = onCheckoutValidation(() => {
                // validate that the order has been created.
                if (!paymentData?.orderId) {
                    return {
                        errorMessage: __('Please click the PayPal button before placing your order.', 'pymntpl-paypal-woocommerce')
                    }
                }
                return true;
            });
            return unsubscribe;
        }
    }, [isExpress, paymentData]);
}