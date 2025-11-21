<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

class Smsleopard
{
	protected $senderID;
  protected $accountID;
  protected $secret;

  public function __construct($params=[])
  {
    $this->ci =& get_instance();
    $this->ci->load->model('smssettings_m');

    $smsleopard_bind = [];
    $get_smsleopards = $this->ci->smssettings_m->get_order_by_smsleopard(array('types' => 'smsleopard', 'schoolID' => $params['schoolID']));
    foreach ( $get_smsleopards as $key => $get_smsleopard ) {
      $smsleopard_bind[ $get_smsleopard->field_names ] = $get_smsleopard->field_values;
    }
    $this->senderID  = $smsleopard_bind['smsleopard_senderID'];
    $this->accountID  = $smsleopard_bind['smsleopard_accountID'];
    $this->secret = $smsleopard_bind['smsleopard_secret'];
  }

  public function send( $to, $message )
  {

    $auth = base64_encode($this->accountID . ":" . $this->secret);
		$params = array(
			"message" => $message,
			"destination" => $to,
			"source" => $this->senderID,
		);
		$url = "https://api.smsleopard.com/v1/sms/send?".http_build_query($params);
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic ' . $auth,
			),
		));

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			echo "<br>errno==>" . curl_errno($ch);
			echo "<br>error==>" . curl_error($ch);
		}
		curl_close($ch);

		return $response;
  }

  public function delivery_report( $message_uuid )
  {

    $auth = base64_encode($this->accountID . ":" . $this->secret);
    $url = "https://api.smsleopard.com/v1/delivery_reports/".$message_uuid;
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Basic ' . $auth,
			),
		));

		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			echo "<br>errno==>" . curl_errno($ch);
			echo "<br>error==>" . curl_error($ch);
		}
		curl_close($ch);

		return $response;
  }
}
