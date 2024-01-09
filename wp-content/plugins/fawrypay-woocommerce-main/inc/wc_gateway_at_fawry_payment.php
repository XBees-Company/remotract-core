<?php


class wc_gateway_at_fawry_payment extends WC_Payment_Gateway {

    private $order_status;

	public function __construct() {
        global $woocommerce;
        $this->id = FAWRY_PAY_PAYMENT_METHOD;
        //  $this->method_title =__( 'MyFawry','fawry_pay');
        $this->title = __('VISA | Mastercard | Meeza | Fawry', 'fawry_pay');
        $this->description = __('Pay by Credit/Debit Cards or through Fawry Network', 'fawry_pay');
        
        $this->method_description = __('FawryPay Payment Gateway', 'fawry_pay');

        // $this->load_plugin_textdomain();
        $this->icon = FAWRY_PAY_URL . '/images/FawryPayLogo.png';
        $this->has_fields = FALSE;
        if (is_admin()) {
            $this->has_fields = true;
            $this->init_form_fields();
        }


        $this->init_form_fields();
        $this->init_settings();
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('init', 'callback_handler');
        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'callback_handler'));
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'fawry_pay'),
                'type' => 'checkbox',
                'label' => __('Enable FawryPay gateway', 'fawry_pay'),
                'default' => 'yes'
            ),
            'description' => array(
                'title' => __('Description', 'fawry_pay'),
                'type' => 'text',
                'description' => __('أدفع عن طريق كارت الأئتمان او ماكينات الدفع الخاصة بفورى', 'fawry_pay'),
                'default' => __('Pay by Credit, Debit Card or through Fawry Network', 'fawry_pay')
            ),
            'merchant_identifier' => array(
                'title' => __('Merchant Identifier', 'fawry_pay'),
                'type' => 'text',
                'description' => __('Your Merchant Identifier', 'fawry_pay'),
                'default' => '',
                'desc_tip' => true,
                'placeholder' => ''
            ),
            'hash_code' => array(
                'title' => __('Hash Code', 'fawry_pay'),
                'type' => 'password',
                'description' => __('Your Hash Code ', 'fawry_pay') . '<br>' . __(' The Callback URL is  : ', 'fawry_pay')
                . '<strong>' . home_url() . '/?wc-api=wc_gateway_at_fawry_payment</strong>'
                ,
                'default' => '',
                'desc_tip' => FALSE,
                'placeholder' => ''
            ),
            'is_staging' => array(
                'title' => __('Is Staging Environment', 'fawry_pay'),
                'type' => 'checkbox',
                'label' => __('Enable staging (Testing) Environment'),
                'default' => 'no'
            ),
           
            'order_complete_after_payment' => array(
                'label' => __('set order status to complete instead of processing after payment', 'fawry_pay'),
                'type' => 'checkbox',
                'title' => __('Complete Order after payment', 'fawry_pay'),
                'default' => 'no'
            )
            // ,
            //     'stupid_mode' => array(
            //     'label' => __('enable order calculations based only on total price (that includes taxes and shipping)', 'fawry_pay'),
            //     'type' => 'checkbox',
            //     'title' => __('Enable Summarized Mode', 'fawry_pay'),
            //     'default' => 'no'
            // ),
        );
    }

    function process_payment($order_id) {
        global $woocommerce;
        $order = new WC_Order($order_id);

        $order->update_status('pending', __('Awaiting fawry payment Confirmation', 'fawry_pay'));

        // Reduce stock levels
        //this will enable stock timeout after the timeout the order is cancelled 
        //you can disable stock or change timeout in settings ->products->inventory
        $order->reduce_order_stock();

        // Remove cart
        $woocommerce->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }

    public function callback_handler() {
     

        $request_string = preg_replace("/[\r\n]+/", " ", file_get_contents('php://input'));
        $json_string = utf8_encode($request_string);
        $request_data = json_decode($json_string , true);
        handleCallBack($request_data);
        exit;
    }




}
