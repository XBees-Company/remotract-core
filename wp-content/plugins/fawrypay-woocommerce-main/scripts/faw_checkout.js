function getLastPart(url) {
    var parts = url.split("/");
    return (url.lastIndexOf('/') !== url.length - 1
            ? parts[parts.length - 1]
            : parts[parts.length - 2]);
}

    function checkout() {
        let lang = 'en';
        const configuration = {
            locale : locale,  //default en
            mode: DISPLAY_MODE.SEPARATED,  //required, allowed values [POPUP, INSIDE_PAGE, SIDE_PAGE, SEPARATED]
        };
        FawryPay.checkout(buildChargeRequest(), configuration);
    }

    function buildChargeRequest() {
        
         //'CashOnDelivery', 'PayAtFawry', 'MWALLET', 'CARD' or 'VALU'.
        const chargeRequest = {
            merchantCode: merchant, // the merchant account number in Fawry
            merchantRefNum: merchantRefNum, // order refrence number from merchant side
            customerProfileId: customerProfileId,
            customerMobile: customer.customerMobile,
            customerEmail: customer.customerEmail,
            customerName: customer.customerName,
            chargeItems: productsJSON.orderItems,
            paymentExpiry: paymentExpiry ,
            returnUrl: callBack,
            signature: signature,
        };
        if(paymentMethod != "default"){
            chargeRequest.paymentMethod = paymentMethod;
        }
        console.log(chargeRequest);
        return chargeRequest;
    }

(function ($) {
    'use strict';
    $(function () {
        

        var mode = null
        var orderDesc = null;

    }); 
})(jQuery);
