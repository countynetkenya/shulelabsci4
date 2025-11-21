<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');
require_once(APPPATH . 'libraries/Mpesa/daraja.php');

class Mpesa extends PaymentAbstract
{
    public $shortcode, $key, $secret, $passkey, $demo, $daraja;

    public function __construct()
    {
        parent::__construct();

        $this->shortcode = $this->payment_setting_option['mpesa_shortcode'];
        $this->key = $this->payment_setting_option['mpesa_key'];
        $this->secret = $this->payment_setting_option['mpesa_secret'];
        $this->passkey = $this->payment_setting_option['mpesa_passkey'];
        $this->demo = $this->payment_setting_option['mpesa_demo'];

        // Initialize Daraja with your credentials
        $this->$daraja = new Daraja([
          'consumerKey' => $this->key,
          'consumerSecret' => $this->secret,
          'shortcode' => $this->shortcode,
          'lipaNaMpesaOnlinePasskey' => $this->passkey,
          'lipaNaMpesaOnlineShortcode' => $this->shortcode,
          'demo' => $this->demo,
        ]);

    }

    public function rules()
    {
        return [
            [
                'field' => 'mpesa_shortcode',
                'label' => $this->ci->lang->line("mpesa_shortcode"),
                'rules' => 'trim|xss_clean|max_length[255]|numeric|callback_unique_field'
            ],
            [
                'field' => 'mpesa_key',
                'label' => $this->ci->lang->line("mpesa_key"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'mpesa_secret',
                'label' => $this->ci->lang->line("mpesa_secret"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'mpesa_passkey',
                'label' => $this->ci->lang->line("mpesa_passkey"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'mpesa_demo',
                'label' => $this->ci->lang->line("mpesa_demo"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ]
        ];
    }

    public function payment_rules()
    {
        return [];
    }

    public function status()
    {
        return true;
    }

    public function success()
    {

    }

    public function payment( $array, $invoice )
    {
        $amount = $array['amount'];
        $phonenumber = $array['phonenumber'];

        // Construct the payment request
        $lipaNaMpesa = $this->$daraja->lipaNaMpesaOnline([
          'BusinessShortCode' => $this->shortcode,
          'Amount' => $amount, // Specify the amount to be paid
          'PartyA' => $phonenumber, // Customer's phone number
          'PartyB' => $this->shortcode,
          'PhoneNumber' => $phonenumber, // Customer's phone number
          'CallBackURL' => site_url("safaricom/ipn"), // Replace with your callback URL
          'AccountReference' => $phonenumber,
          'TransactionDesc' => 'DEPOSIT',
          'TransactionType' => 'CustomerPayBillOnline',
        ]);

        // Send the request
        $response = $this->$daraja->execute($lipaNaMpesa);
        return $response;
    }

    public function cancel()
    {

    }

    public function fail()
    {

    }
}
