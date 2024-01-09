<?php

/*  *
 to use add_filter woocommerce_thankyou_order_received_text, woocomerce default theme has to be applied and apply this filter in its checkout page, but if new theme is applied and checkout page is overriden then add_action "woocommerce_thankyou_FAWRY_PAY_PAYMENT_METHOD" will be better
    *
*/
add_action('woocommerce_thankyou_'.FAWRY_PAY_PAYMENT_METHOD, 'action_woocommerce_thankyou_fawry_pay', 11, 1);
function action_woocommerce_thankyou_fawry_pay($order_id) {
    $order = wc_get_order($order_id);
    $data = $_REQUEST;
    $data["customerMail"] = $order->get_billing_email();
    $data["customerMobile"] = $order->get_billing_phone();
    $data = str_replace('\\', '', $data);
    $dummy = json_encode($data);
    $chargeResponse = json_decode($dummy);
    global $wpdb;
    $prefix = $wpdb->prefix;
    if($chargeResponse != null && (isset($chargeResponse->key))){
        $output = ['status' => 10];
        $order_key = $chargeResponse->key;
        $merchantRefNum = wc_get_order_id_by_order_key($order_key);
        if ($merchantRefNum) {
            $order = wc_get_order($merchantRefNum);
            if ($order->get_user_id() === get_current_user_id()) {
                $order->update_meta_data('_rec_faw_pay', 1);
                $order->save();//dont forget
                $output = ['status' => 20];
            }
        }
    }
    
    if( 
        ($chargeResponse != null && property_exists($chargeResponse, 'statusCode') && ($chargeResponse->statusCode !== '200') ) 
        ||
       ( property_exists($chargeResponse, 'code') && $chargeResponse->code == "9901" ) 
    ) {
        // failed
        $uri = wp_parse_url($_SERVER['REQUEST_URI']);
        if (get_option('permalink_structure') == ""){
            // plain permaling is activated
            $order_id = $_GET['order-received'];
        }
        else{
            $chunks = explode('order-received/', $uri["path"]);
            $vals = array_values($chunks);
            $order_id = $vals[1];
            $order_id = explode('/', $order_id)[0];     
        }

        $order = wc_get_order($order_id);
        if($order->get_status() != "failed"){
            // change status to failed then refresh thank_you page to print the error message
            $order->update_status('failed', $chargeResponse->statusDescription);
            header("Refresh: 0");
        }
        //$order->setStatus("cancel");
        //$obj = json_decode($order);
        //$mail = $obj->{'billing'}->{'email'};
        $str = '<Strong>Order has been cancelled!</Strong><br><br>';
        $str .= '<br><br>' . $chargeResponse->statusDescription .'<br><br>';
        //$wc_emails = WC()->mailer()->get_emails();
        //$wc_emails['WC_Email_Cancelled_Order']->recipient = $mail;
        //$wc_emails['WC_Email_Cancelled_Order']->trigger( $obj->{'id'} );
        echo $str;
        exit;
    } 
    elseif($chargeResponse != null 
    && property_exists($chargeResponse, 'statusCode')
    && ($chargeResponse->statusCode === '200')
    && property_exists($chargeResponse, 'merchantRefNumber')
    && property_exists($chargeResponse, 'referenceNumber')
    && property_exists($chargeResponse, 'paymentMethod')
    )
    {
        //$order_id = $chargeResponse->merchantRefNumber;
        //remove prefix
        $order_id = str_replace($prefix, "", $chargeResponse->merchantRefNumber);
        $order = wc_get_order($order_id);

        if(strcasecmp(strtolower($chargeResponse->paymentMethod) , 'payusingcc') == 0 || strcasecmp( strtolower($chargeResponse->paymentMethod) , 'card')==0)
        {
            $chargeResponse1 = json_decode(json_encode($chargeResponse), true);
            $result = handleCallBack($chargeResponse1, 1);
            //credit card, then check with server first
            //$result = checkPaymentStatus($chargeResponse, $order_id);
            if ($result == 0)
            {
                $order->add_order_note("Fawry Pay reference number: ".$chargeResponse->referenceNumber);
                echo draw_order_details($order, $chargeResponse);
            }
            else if ($result == 1) {
                echo '<br><br>' . '<Strong>FAILD</Strong>'.'<br><br>';
            }
            else if ($result == 2) {
                echo "in result 2 ";
                echo '<br><br>' . '<Strong>INVALID_SIGNATURE</Strong>'.'<br><br>';
            }   
        }
        if(strcasecmp ($chargeResponse->paymentMethod , 'PayAtFawry') == 0)
        {
            $order->add_order_note("Fawry Pay reference number: ".$chargeResponse->referenceNumber);
            echo draw_order_details($order, $chargeResponse);   
        }
    } elseif ($order != null && $order->get_payment_method() == FAWRY_PAY_PAYMENT_METHOD && $order->get_status() == 'pending') {
        //print_r("on-hold");
        if ($order->get_meta('_rec_faw_pay', TRUE) == 1) {

            $new_str = '
            <div id="loading" style="width:200px;margin:auto;margin-top:20px">
            <img style="width:100%" src="'. FAWRY_PAY_URL ."images/spinner.gif" . ' ">
            <center>Loading FawryPay</center>
            </div>';

            $new_str .= '<script> '
            .'setTimeout(function(){
                document.getElementById("loading").remove()
                checkout();
            }, 3000);'
            . '</script>';
            
            $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');
          

            $merchantCode = $options['merchant_identifier'];
            $merchantRefNum = $order->get_id();
            //append prefix
            $merchantRefNum = $prefix . $merchantRefNum;
            
            $price = $order->get_total();
            $merchantHashCode =  $options['hash_code'];

           

            $profile_id = $order->get_customer_id() ?? '' ;
            $checkout_url = wc_get_checkout_url();
            if (get_option('permalink_structure') == "")
                // plain permaling is activated
                $return_url = $checkout_url . '&order-received=' . $order->get_id()."&key=". $order->get_order_key();
            else
                $return_url = $checkout_url . 'order-received/' . $order->get_id() ."/?key=". $order->get_order_key()."&";
            //$return_url = $checkout_url . 'order-received/' . $order->get_id() . '/';
            $customer = getCustomer($order);

            $sorted_order_signature_string = getSortedOrderSignatureString($order,$options);
            $signature = hash('sha256' ,
                $merchantCode
                . $merchantRefNum
                . ($profile_id  ?? "")
                . $return_url
                //. ($order->get_billing_phone() ?? "")
                //. ($order->get_billing_email() ?? "")
                . $sorted_order_signature_string
                //. $paymentExpiry
                . $merchantHashCode);
            $requestPaymentMehod = get_payment_method();
            $new_str .= '<script> '
                    . 'var language= "en";'
                    . 'var merchant= "' . $merchantCode . '";'
                    . 'var signature= "' . $signature . '";'
                    . 'var merchantRefNum= "' . $merchantRefNum . '";'
                    . 'var productsJSON=' . getProductsJson($order,$options) . ';'
                    . 'var customer=' . getCustomer($order) . ';'
                    . 'var customerName= "' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '";'
                    . 'var mobile = "' . $order->get_billing_phone() . '";'
                    . 'var email = "' . $order->get_billing_email() . '";'
                    . 'var customerProfileId = "' . $order->get_customer_id() . '";'
                    . 'var paymentExpiry = "";'
                    . 'var paymentMethod = "' . $requestPaymentMehod . '";'
                    . 'var locale = "' . ( (strpos(get_locale(), 'ar') !== false) ? 'ar' : 'en') . '";'
                    . 'var callBack = "' . $return_url. '";'
                    . 'var failCallBack = "' . $return_url . '";'
                    . '</script>';
            echo $new_str;
        }
    }
}

function getCustomer($order){
	if($order == null) return;
	$customer = new stdClass();
	$customer->customerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
	$customer->customerMobile = $order->get_billing_phone();
	$customer->customerEmail = $order->get_billing_email();
	$customer->customerId = $order->get_customer_id();
	return json_encode($customer);
}

function getSortedOrderSignatureString($order, $options)
{
    
    $signature_string = '';
    
        $items = $order->get_items();
       
        $order_items = [];
        foreach ($items as $item) {
            $data = $item->get_data();
            $order_items[] = [
                'itemId' => $data['product_id'],
                'description' => $data['name'],
                'quantity' => $data['quantity'],
                'price' => number_format((float)($data['subtotal'] / $data['quantity']), 2,'.', ''),
            ];
        }

        $shipping_price = floatval($order->get_shipping_total());
       
        if($shipping_price > 0)
        {
            $order_items[] = [
                'itemId' => 'shipping',
                'description' => 'Shipping',
                'quantity' => 1,
                'price' => number_format($shipping_price , 2, '.', ''),
            ];
        }
        $tax_price = floatval($order->get_total_tax());
        if($tax_price > 0)
        {
            $order_items[] = [
                'itemId' => 'tax',
                'description' => 'Tax',
                'quantity' => 1,
                'price' =>  number_format($tax_price , 2, '.', ''),
            ];
        }
        $discount_price = floatval($order->get_discount_total());
    
        if($discount_price > 0)
        {
            $order_items[] = [
                'itemId' => 'discount',
                'description' => 'Discount',
                'quantity' => 1,
                'price' => number_format($discount_price* -1 , 2, '.', ''),
            ];
        }
        $itemId = array_column($order_items, 'itemId');
        array_multisort($itemId, SORT_ASC,SORT_STRING, $order_items);
        foreach ($order_items as $order_item)
        {
            $signature_string .=   $order_item['itemId'] . $order_item['quantity'] . $order_item['price'] ;
        }
   
    return $signature_string;
}

/**
 * return the products as JSON array
 * 
 * @param WC_Order $order
 */
function getProductsJson($order,$options) {
   
        $items = $order->get_items();
      
        $order_items = [];
        foreach ($items as $item) {
            $data = $item->get_data();
            $order_items[] = [
                'itemId' => $data['product_id'],
                'description' => $data['name'],
                'quantity' => $data['quantity'],
                'price' => number_format((float)($data['subtotal'] / $data['quantity']), 2,'.', ''),
            ];
        }

        $shipping_price = floatval($order->get_shipping_total());
       
        if($shipping_price > 0)
        {
            $order_items[] = [
                'itemId' => 'shipping',
                'description' => 'Shipping',
                'quantity' => 1,
                'price' => number_format($shipping_price , 2, '.', ''),
            ];
        }
        $tax_price = floatval($order->get_total_tax());
        if($tax_price > 0)
        {
            $order_items[] = [
                'itemId' => 'tax',
                'description' => 'Tax',
                'quantity' => 1,
                'price' =>  number_format($tax_price , 2, '.', ''),
            ];
        }
        $discount_price = floatval($order->get_discount_total());
    
        if($discount_price > 0)
        {
            $order_items[] = [
                'itemId' => 'discount',
                'description' => 'Discount',
                'quantity' => 1,
                'price' => number_format($discount_price* -1 , 2, '.', ''),
            ];
        }

   
    $itemId = array_column($order_items, 'itemId');
    array_multisort($itemId, SORT_ASC, SORT_STRING, $order_items);
	$orderItems = array("orderItems" => $order_items);
    return json_encode($orderItems);
}

//add fawry js
function fawry_pay_scripts() {
    $options = get_option('woocommerce_' . FAWRY_PAY_PAYMENT_METHOD . '_settings');
    $isStaging = $options['is_staging'] == 'no' ? FALSE : TRUE;
    $php_vars = array(
        'siteurl' => get_option('siteurl'),
        'ajaxurl' => admin_url('admin-ajax.php'),
    );
    //if (is_page('checkout')) {
        if ($isStaging) {
			wp_enqueue_script('fawry_js', 'https://atfawry.fawrystaging.com/atfawry/plugin/assets/payments/js/fawrypay-payments.js');
        } else {
            wp_enqueue_script('fawry_js', 'https://www.atfawry.com/atfawry/plugin/assets/payments/js/fawrypay-payments.js');
        }

        wp_enqueue_script('faw_checkout', plugin_dir_url(__DIR__) . 'scripts/faw_checkout.js', array('jquery', 'fawry_js'));
        wp_localize_script('faw_checkout', 'FAW_PHPVAR', $php_vars); //FAW_PHPVAR name must be unqiue
    //}
}
function draw_order_details($order, $chargeResponse){
    // table headers
    $str = "<caption><strong>Thank you, Your Order Number is ".$order->get_id()."</strong></caption>
        <table><tr><th>Product</th><th>Total</th></tr>";
    foreach ($order->get_items() as $product) {
        $data = $product->get_data();
        $str .= "<tr>";
        $str .= "<td>".$data['name']." * ".$data['quantity']."</td>";
        $str .= "<td>".number_format((float)($data['total'] / $data['quantity']), 2,'.', '')."</td>";
        $str .= "</tr>";
    }
    $str .= "<tr><td>"."Total Amount</td><td>".$order->get_total(). ' '. $order->get_currency() ."</td></tr>";
    $str .= "<tr><td>"."Payment Method</td><td>".$chargeResponse->paymentMethod."</td></tr>";
    $str .= "<tr><td>"."Fawry Refrence Number</td><td>".$chargeResponse->referenceNumber."</td></tr>";
    $str .= "</table>";
    return $str;
}

function get_payment_method(){
  
    if(file_exists(__DIR__ . '/../payment_method.json')){
        $strJsonFileContents = json_decode(file_get_contents(__DIR__ . '/../payment_method.json'));
        if(property_exists( $strJsonFileContents,"method"))
            return $strJsonFileContents->method;

    }
    return "default";
}

add_action('wp_enqueue_scripts', 'fawry_pay_scripts');



?>