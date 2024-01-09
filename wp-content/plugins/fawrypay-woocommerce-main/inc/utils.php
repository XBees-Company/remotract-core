<?php

function updatePaymentStatus($responseData)
{
    //print_r($responseData);
    //returns 
    // 0 success
    // 1 fail
    if($responseData == null || !array_key_exists('merchantRefNumber', $responseData))
    {
        return 1;
    }
    $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');

    $FawryRefNo = $responseData['fawryRefNumber']; //internal to fawry
    $MerchantRefNo = $responseData['merchantRefNumber'];
    $OrderStatus = $responseData['orderStatus']; //New, PAID, CANCELED, DELIVERED, REFUNDED, EXPIRED
    $Amount = $responseData['orderAmount'];
    //$MessageSignature = $responseData['signature'];

    $order = wc_get_order($MerchantRefNo);
  

    //get order
    //check amount and  order status PAID
    if ($Amount == $order->get_total() && $OrderStatus == 'PAID') {
        $order->payment_complete();
        if (trim($options['order_complete_after_payment']) === 'yes') {
            $order->update_status('completed');
        }
        return 0;
    } else {
        return 1;
    }
}


function changePaymentStatus($callbackData)
{
    //print_r($responseData);
    //returns
    // 0 success
    // 1 fail
    if($callbackData == null || !array_key_exists('merchantRefNumber', $callbackData))
    {
        return 1;
    }
    $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');
    $MerchantRefNo = $callbackData['merchantRefNumber'];
    $OrderStatus = $callbackData['orderStatus']; //New, PAID, CANCELED,UNPAID, DELIVERED, REFUNDED, EXPIRED

    $Amount = $callbackData['orderAmount'];

    global $wpdb;
    $prefix = $wpdb->prefix;
    $order_id = str_replace($prefix, "", $MerchantRefNo);
    $order = wc_get_order($order_id);
    error_log ("order: ".$order);

    //get order
    //check amount and  order status PAID
    if ($Amount == $order->get_total() && $OrderStatus == 'PAID') {
        $order->payment_complete();
        if (trim($options['order_complete_after_payment']) === 'yes') {
            $order->update_status('completed');
        }
        return 0;
    } else if ($Amount == $order->get_total() && $OrderStatus == 'CANCELED') {
            $order->update_status('canceled');
        return 0;
    } else if ($Amount == $order->get_total() && $OrderStatus == 'New') {
            $order->update_status('pending');
        return 0;
    } else if ($Amount == $order->get_total() && $OrderStatus == 'UNPAID') {
        $order->update_status('UNPAID');
       return 0;
    }
    else {
        return 1;
    }
}


function checkPaymentStatus($chargeResponse, $orderID)
{
    $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');
    $merchantCode    = $options['merchant_identifier'];
    $merchantRefNumber  = $orderID;
    $merchant_sec_key =  $options['hash_code'];

    $formattedPayLoad = formatGetPaymentStatus($merchantCode, $merchant_sec_key, $merchantRefNumber);
    //print_r($formattedPayLoad);

    $isStaging = $options['is_staging'] == 'no' ? FALSE : TRUE;
    //print_r($isStaging);
    if($isStaging)
    {
        $response = get('https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/status/v2',$formattedPayLoad);
    }
    else{
        $response = get('https://www.atfawry.com/ECommerceWeb/Fawry/payments/status/v2',$formattedPayLoad);
    }
    
    return updatePaymentStatus($response);
}

function get($url,$data)
{
    $request = new WP_Http();
    $query_encoded = http_build_query($data , null, '&', 2);
    $query = urldecode($query_encoded);
    $query = str_replace('%20' , ' ' , $query);
    $response = $request->get($url. '?' . $query);
    if(array_key_exists('body', $response))
    {
        $response = json_decode($response['body'], true);
        return $response;
    }
    else
    {
        return null;
    }
}

function formatGetPaymentStatus($merchantCode, $merchantSecKey, $merchantRefNumber)
{
    $signature = hash('sha256' , $merchantCode . $merchantRefNumber . $merchantSecKey);

    return [
        'merchantCode' => $merchantCode,
        'merchantRefNumber' => $merchantRefNumber,
        'signature' => $signature
    ];
}


function verifyURlSignature($request): int
{
    $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');
    $merchant_sec_key =  $options['hash_code'] ?? "";
    if(array_key_exists('referenceNumber', $request))
        $refNumber = $request['referenceNumber'];
    else
        $refNumber = $request['fawryRefNumber']  ?? "";

    if(array_key_exists('signature', $request))
        $messageSignature = $request['signature'];
    else
        $messageSignature = $request['messageSignature']  ?? "";

    $signature_string =   $refNumber
        . ($request['merchantRefNumber']  ?? "")
        . (number_format($request['paymentAmount']  , '2' , '.' , '') ?? "")
        . (number_format($request['orderAmount']   , '2' , '.' , '') ?? "")
        . ($request['orderStatus']  ?? "")
        . ($request['paymentMethod']  ?? "")
		. ((array_key_exists('fawryFees', $request))? (number_format($request['fawryFees']  , '2' , '.' , '')) : "")
		. ((array_key_exists('shippingFees', $request))? (number_format($request['shippingFees']  , '2' , '.' , '')) : "")
		. ($request['authNumber']  ?? "")
        . ($request['paymentReferenceNumber'] ?? "")
        . $merchant_sec_key;
     
    $generated_signature = hash('sha256' , $signature_string);
    if($generated_signature === $messageSignature)
        return 1;
    else
        return 0;
}

function verifyCallBackSignature($request): int
{
    $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');
    $merchant_sec_key =  $options['hash_code'] ?? "";
    if(array_key_exists('referenceNumber', $request))
        $refNumber = $request['referenceNumber'];
    else
        $refNumber = $request['fawryRefNumber']  ?? "";

    if(array_key_exists('signature', $request))
        $messageSignature = $request['signature'];
    else
        $messageSignature = $request['messageSignature']  ?? "";

    $signature_string =   $refNumber
        . ($request['merchantRefNumber']  ?? "")
        . (number_format($request['paymentAmount']  , '2' , '.' , '') ?? "")
        . (number_format($request['orderAmount']   , '2' , '.' , '') ?? "")
        . ($request['orderStatus']  ?? "")
        . ($request['paymentMethod']  ?? "")
        . ($request['paymentRefrenceNumber'] ?? "")
        . $merchant_sec_key;
    $generated_signature = hash('sha256' , $signature_string);
    
    if($generated_signature === $messageSignature)
        return 1;
    else
        return 0;
}

function handleCallBack($request_data, $url = 0)
{
    $return_flag = 0;

    if($request_data == null || !array_key_exists('merchantRefNumber', $request_data))
    {
        $message['status'] = '202';
        $message['message'] = 'FAILED';
        status_header(202, 'FAILED');
        echo $url == 0? json_encode($message) : "";
        $return_flag = 1;
    }
    else
    {
        $signature_validation = $url == 0? verifyCallBackSignature($request_data) : verifyURlSignature($request_data);
        if($signature_validation == 0)
        {
            $message['status'] = '300';
            $message['message'] = 'INVALID_SIGNATURE';
            status_header(300, 'INVALID_SIGNATURE');
            echo $url == 0? json_encode($message) : "";
            $return_flag = 2;
        } else {
            $result = changePaymentStatus($request_data);
            if ($result == 0)
            {
                $message['status'] = '200';
                $message['message'] = 'SUCCESS';
                echo $url == 0? json_encode($message) : "";
                $return_flag = 0;
            }
            else if ($result == 1) {
                $message['status'] = '202';
                $message['message'] = 'FAILED';
                status_header(202, 'FAILED');
                echo $url == 0? json_encode($message) : "";
                $return_flag = 1;
            }
            else if ($result == 2) {
                $message['status'] = '300';
                $message['message'] = 'ORDER_NOT_FOUND';
                status_header(300, 'ORDER_NOT_FOUND');
                echo $url == 0? json_encode($message) : "";
                $return_flag = 2;
            }
        }

    }
    return $return_flag;
}
