<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

class Bongasms
{
	protected $senderID;
  protected $clientID;
  protected $key;
  protected $secret;

  public function __construct()
  {
    $this->ci =& get_instance();
    $this->ci->load->model('smssettings_m');

    $bongasms_bind = [];
    $get_bongasms = $this->ci->smssettings_m->get_order_by_bongasms(array('types' => 'bongasms', 'schoolID' => $this->ci->session->userdata('schoolID')));
    foreach ( $get_bongasms as $key => $value ) {
      $bongasms_bind[ $value->field_names ] = $value->field_values;
    }
    $this->clientID = $bongasms_bind['bongasms_api_clientID'];
    $this->key = $bongasms_bind['bongasms_api_key'];
    $this->secret = $bongasms_bind['bongasms_api_secret'];
  }

  public function send( $to, $message )
  {

    $url = "http://167.172.14.50:4002/v1/send-sms";
    $post_data = http_build_query([
      "apiClientID" => $this->clientID,
      "key" => $this->key,
      "secret" => $this->secret,
      "txtMessage" => $message,
      "MSISDN" => $to
    ]);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded '));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);

    $result = json_decode($result);
    return $result;
  }

  public function delivery_report( $message_uuid )
  {

    $url = "https://app.bongasms.co.ke/api/fetch-delivery?apiClientID=". $this->clientID ."&key=". $this->key ."&unique_id=". $message_uuid;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded '));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);

    $result = json_decode($result);
    return $result;
  }
}
