<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_Controller extends MY_Controller {
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://inilabs.net
    | -----------------------------------------------------
    */

    private $_backendTheme = '';
    private $_backendThemePath = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->model("signin_m");
        $this->load->model("permission_m");
        $this->load->model("site_m");
        $this->load->model("holiday_m");
        $this->load->model("schoolyear_m");
        $this->load->model("school_m");
        $this->load->model("alert_m");
        $this->load->library("session");
        $this->load->helper('language');
        $this->load->helper('date');
        $this->load->helper('form');
        $this->load->helper('feature_flag');
        $this->load->library('form_validation');
        $this->load->model('classes_m');
        $this->load->model("menu_m");
        $this->load->model("mailandsms_m");
        $this->load->model("quickbookssettings_m");
        $this->load->library("bongasms", array('schoolID' => $this->session->userdata('schoolID')));
        $this->load->library("smsleopard", array('schoolID' => $this->session->userdata('schoolID')));
    		$this->load->library("clickatell");
    		$this->load->library("twilio");
    		$this->load->library("bulk");
    		$this->load->library("msg91");
        $this->lang->load('topbar_menu', $this->session->userdata('lang'));

        $module            = $this->uri->segment(1);
        $action            = $this->uri->segment(2);
        $schoolID = 0;
        if($this->session->userdata('schoolID'))
          $schoolID = $this->session->userdata('schoolID');
        $siteInfo          = $this->site_m->get_site($schoolID);
        $frontendManager   = $this->_frontendManager($siteInfo);
        $permissionManager = $this->_permissionManager($module, $action);
        if ( !empty($frontendManager) ) {
            redirect($frontendManager);
        } elseif ( !empty($permissionManager) ) {
            redirect($permissionManager);
        }

        $userTypeID = $this->session->userdata('usertypeID');
        $backendTheme = '';
        if ( isset($siteInfo->backend_theme) && is_string($siteInfo->backend_theme) ) {
            $backendTheme = trim($siteInfo->backend_theme);
        }
        if ( $backendTheme === '' ) {
            $backendTheme = 'default';
            $siteInfo->backend_theme = $backendTheme;
        }
        $backendTheme = strtolower($backendTheme);
        $this->_backendTheme     = $backendTheme;
        $this->_backendThemePath = 'assets/inilabs/themes/' . $backendTheme;

        $this->data["siteinfos"]            = $siteInfo;
        $this->data['backendTheme']         = $this->_backendTheme;
        $this->data['backendThemePath']     = $this->_backendThemePath;
        $this->data['allcountry']           = $this->getAllCountry();
        $this->data['allbloodgroup']        = $this->_bloodGroup();
        $this->data['myclass']              = $this->_classManager($userTypeID);
        $this->data['schoolyearobj']        = $this->schoolyear_m->get_obj_schoolyear(array('schoolyearID' => $siteInfo->school_year));
        $this->data['schoolyearsessionobj'] = $this->schoolyear_m->get_obj_schoolyear(array('schoolyearID' => $this->session->userdata('defaultschoolyearID')));
        $this->data['topbarschoolyears']    = $this->schoolyear_m->get_order_by_schoolyear([ 'schooltype' => 'classbase', 'schoolID' => $schoolID ]);
    }

    Private function _classManager( $userTypeID )
    {
        if ( $userTypeID == 3 ) {
            $this->load->model('studentrelation_m');
            $student = $this->studentrelation_m->get_single_student([
                'srstudentID'    => $this->session->userdata('loginuserID'),
                'srschoolyearID' => $this->session->userdata('defaultschoolyearID')
            ]);
            if ( customCompute($student) ) {
                return $student->srclassesID;
            }
            return 0;
        }
        return 0;
    }

    private function _frontendManager( $siteInfo )
    {
        $url = '';
        $exceptionUris = [
            'register',
            'register/index',
            'signin',
            'signin/index',
            'signin/signout',
        ];

        if ( in_array(uri_string(), $exceptionUris) == false ) {
            if ( $this->signin_m->loggedin() == false ) {
                if ( $siteInfo->frontendorbackend === 'YES' || $siteInfo->frontendorbackend == 1 ) {
                    $this->load->model('fmenu_m');
                    $this->load->model('pages_m');
                    $this->load->model('posts_m');
                    $frontendRedirectURL    = '';
                    $frontendRedirectMethod = 'home';
                    $frontendTopbar         = $this->fmenu_m->get_single_fmenu([ 'topbar' => 1 ]);
                    $homePage               = $this->pages_m->get_one($frontendTopbar);
                    if ( customCompute($homePage) ) {
                        if ( $homePage->menu_typeID == 1 ) {
                            $page = $this->pages_m->get_single_pages([ 'pagesID' => $homePage->menu_pagesID ]);
                            if ( customCompute($page) ) {
                                $frontendRedirectURL    = $page->url;
                                $frontendRedirectMethod = 'page';
                            }
                        } elseif ( $homePage->menu_typeID == 2 ) {
                            $post = $this->posts_m->get_single_posts([ 'postsID' => $homePage->menu_pagesID ]);
                            if ( customCompute($post) ) {
                                $frontendRedirectURL    = $post->url;
                                $frontendRedirectMethod = 'post';
                            }
                        }
                    }
                    $url = base_url('frontend/' . $frontendRedirectMethod . '/' . $frontendRedirectURL);
                } else {
                    $url = base_url("signin/index");
                }
            }
        }
        return $url;
    }

    private function _permissionManager( $module, $action )
    {
        if ( $action == 'index' || $action == false ) {
            $permission = $module;
        } else {
            $permission = $module . '_' . $action;
        }

        $requestedPermission = $permission;

        $url             = '';
        $permissionArray = [];
        $userdata        = $this->session->userdata;

        if ( $this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 ) {
            if ( isset($userdata['loginuserID']) && !isset($userdata['get_permission']) ) {
                $features = $this->permission_m->get_permission();
                if ( customCompute($features) ) {
                    foreach ( $features as $featureKey => $feature ) {
                        $permissionArray['master_permission_set'][ trim($feature->name) ] = $feature->active;
                    }

                    $permissionArray['master_permission_set']['take_exam'] = 'yes';
                    $this->session->set_userdata([ 'get_permission' => true ]);
                    $this->session->set_userdata($permissionArray);
                }
            }
        } else {
            if ( isset($userdata['loginuserID']) && isset($userdata['schoolID']) && !isset($userdata['get_permission']) ) {
                if ( !$this->session->userdata($permission) ) {
                    $user_permission = $this->permission_m->get_modules_with_permission(array('id' => $userdata['usertypeID'], 'schoolID' => $userdata['schoolID']));

                    foreach ( $user_permission as $value ) {
                        $permissionArray['master_permission_set'][ $value->name ] = $value->active;
                    }

                    if ( $userdata['usertypeID'] == 3 ) {
                        $permissionArray['master_permission_set']['take_exam'] = 'yes';
                    }

                    $this->session->set_userdata([ 'get_permission' => true ]);
                    $this->session->set_userdata($permissionArray);
                }
            }
        }

        $sessionPermission     = $this->session->userdata('master_permission_set');

        if($this->session->userdata('dbMenus')) {
            $this->data['dbMenus'] = $this->session->userdata('dbMenus');
        } else {
            $dbMenus               = $this->menuTree(json_decode(json_encode(pluck($this->menu_m->get_order_by_menu([ 'status' => 1 ]), 'obj', 'menuID')), true), $sessionPermission);
            $dbMenus               = $this->_copyTree($dbMenus);
            $this->data["dbMenus"] = $this->_fixMenuLinks($dbMenus);

            $this->session->set_userdata(array('dbMenus' => $this->data["dbMenus"]));
        }

        if ( ( isset($sessionPermission[ $requestedPermission ]) && $sessionPermission[ $requestedPermission ] == "no" ) ) {
            if ( $requestedPermission == 'dashboard' && $sessionPermission[ $requestedPermission ] == "no" ) {
                if ( in_array('yes', $sessionPermission) ) {
                    if ( $sessionPermission["dashboard"] == 'no' ) {
                        $url = 'exceptionpage/index';
                        foreach ( $sessionPermission as $key => $value ) {
                            if ( $value == 'yes' ) {
                                $url = $key;
                                break;
                            }
                        }
                    }
                }
            } else {
                $url = base_url('exceptionpage/error');
            }
        }
        return $url;
    }

    public function usercreatemail($email=NULL, $username=NULL, $password=NULL) {
        $this->load->model('emailsetting_m');
        $emailSetting = $this->emailsetting_m->get_emailsetting();
        $this->load->library('email');
        $this->email->set_mailtype("html");

        if(customCompute($emailSetting)) {
            if($emailSetting->email_engine == 'smtp') {
                if ($emailSetting->smtp_security){
                    $config = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $emailSetting->smtp_server,
                        'smtp_port'   => $emailSetting->smtp_port,
                        'smtp_user'   => $emailSetting->smtp_username,
                        'smtp_pass'   => $emailSetting->smtp_password,
                        'smtp_crypto' => $emailSetting->smtp_security,
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'crlf' => "\r\n",
                        'newline' => "\r\n"
                    ];
                } else{
                    $config = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $emailSetting->smtp_server,
                        'smtp_port'   => $emailSetting->smtp_port,
                        'smtp_user'   => $emailSetting->smtp_username,
                        'smtp_pass'   => $emailSetting->smtp_password,
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'crlf' => "\r\n",
                        'newline' => "\r\n"
                    ];
                }
                $this->email->initialize($config);
            }
        }

        if($email) {
            $this->email->from($this->data['siteinfos']->email, $this->data['siteinfos']->sname);
            $this->email->to($email);
            $this->email->subject($this->data['siteinfos']->sname);
            $url = base_url();
            $message = "<h2>Welcome to ".$this->data['siteinfos']->sname."</h2>
	        <p>Please log-in to this website and change the password as soon as possible </p>
	        <p>Website : ".$url."</p>
	        <p>Username: ".$username."</p>
	        <p>Password: ".$password."</p>
	        <br>
	        <p>Once again, thank you for choosing ".$this->data['siteinfos']->sname."</p>
	        <p>Best Wishes,</p>
	        <p>The ".$this->data['siteinfos']->sname." Team</p>";
            $this->email->message($message);
            $this->email->send();
        }
    }

    public function allgetway_send_message($getway, $to, $message, $mailandsmsID=NULL) {
  		$result = [];
  		if($getway == 'smsleopard') {
  			if($to) {
          $response = json_decode($this->smsleopard->send($to, $message));
  				if($response->success == TRUE)  {
            $recipients = $response->recipients;
            foreach ($recipients as $recipient) {
              $this->mailandsms_m->update_mailandsms(array("message_uuid" => $recipient->id), $mailandsmsID);
            }
  					$result['check'] = TRUE;
            return $result;
  				} else {
  					$result['check'] = FALSE;
            if($response->message == "no valid recipients") {
              $recipients = $response->recipients;
              foreach($recipients as $recipient) {
                $result['message'] = $response->message .": ". $recipient->status;
              }
            }
            else {
              $result['message'] = $response->message;
            }
  					return $result;
  				}
  			}
  		} elseif($getway == 'bongasms') {
  			if($to) {
          $response = $this->bongasms->send($to, $message);
  				if($response->status == 222)  {
            $this->mailandsms_m->update_mailandsms(array("message_uuid" => $response->unique_id), $mailandsmsID);
  					$result['check'] = TRUE;
            return $result;
  				} else {
  					$result['check'] = FALSE;
  					$result['message'] = $response->status_message;
  					return $result;
  				}
  			}
  		} elseif($getway == "clickatell") {
  			if($to) {
  				$this->clickatell->send_message($to, $message);
  				$result['check'] = TRUE;
  				return $result;
  			}
  		} elseif($getway == 'twilio') {
  			$get = $this->twilio->get_twilio();
  			$from = $get['number'];
  			if($to) {
  				$response = $this->twilio->sms($from, $to, $message);
  				if($response->IsError) {
  					$result['check'] = FALSE;
  					$result['message'] = $response->ErrorMessage;
  					return $result;
  				} else {
  					$result['check'] = TRUE;
  					return $result;
  				}

  			}
  		} elseif($getway == 'bulk') {
  			if($to) {
  				if($this->bulk->send($to, $message) == TRUE)  {
  					$result['check'] = TRUE;
  					return $result;
  				} else {
  					$result['check'] = FALSE;
  					$result['message'] = "Check your bulk account";
  					return $result;
  				}
  			}
  		} elseif($getway == 'msg91') {
  			if($to) {
  				if($this->msg91->send($to, $message) == TRUE)  {
  					$result['check'] = TRUE;
  					return $result;
  				} else {
  					$result['check'] = FALSE;
  					$result['message'] = "Check your msg91 account";
  					return $result;
  				}
  			}
  		}
  	}

    public function get_delivery_report($mailandsms) {
      if($mailandsms->delivery_report == NULL) {
        if($mailandsms->sms_gateway == "smsleopard") {
  		    $response = json_decode($this->smsleopard->delivery_report($mailandsms->message_uuid));
          $delivery_report = $response->status .";". $response->reason;
        }
        elseif($mailandsms->sms_gateway == "bongasms") {
          $response = $this->bongasms->delivery_report($mailandsms->message_uuid);
          if($response->status == "222") {
            $delivery_report = $response->delivery_status_desc .";". $response->status_message;
          }
          elseif($response->status == "666") {
            $delivery_report = "Failed;". $response->status_message;
          }
        }
        $this->mailandsms_m->update_mailandsms(array("delivery_report" => $delivery_report), $mailandsms->mailandsmsID);
      } else {
        $delivery_report = $mailandsms->delivery_report;
      }

      return $delivery_report;
  	}

    public function reportPDF($stylesheet=NULL, $data=NULL, $viewpath= NULL, $mode = 'view', $pagesize = 'a4', $pagetype='portrait', $headerHtml = '', $footerHtml = '') {
        $designType = 'LTR';
        $this->data['panel_title'] = $this->lang->line('panel_title');
        $html = $this->load->view($viewpath, $this->data, true);

        $this->load->library('mhtml2pdf');

        $this->mhtml2pdf->folder('uploads/report/');
        $this->mhtml2pdf->filename('Report');
        $this->mhtml2pdf->paper($pagesize, $pagetype);
        $this->mhtml2pdf->html($html);

        $stylesheetContent = '';
        if(!empty($stylesheet)) {
            $stylesheetContent = file_get_contents(base_url('assets/pdf/'.$designType.'/'.$stylesheet));
        }

        return $this->mhtml2pdf->create($mode, $this->data['panel_title'], $stylesheetContent, $headerHtml, $footerHtml);
    }

    public function pathPDF($stylesheet=NULL, $data=NULL, $viewpath=NULL, $pagesize = 'a4', $pagetype='portrait') {
        $designType = 'LTR';
        $this->load->library('mhtml2pdf');
        $this->mhtml2pdf->folder('uploads/report/');
        $rand    = random19() . date('y-m-d h:i:s');
        $sharand = hash('sha512', $rand);

        $this->mhtml2pdf->filename($sharand);
        $this->mhtml2pdf->paper($pagesize, $pagetype);
        $this->data['panel_title'] = $this->lang->line('panel_title');
        $html = $this->load->view($viewpath, $this->data, true);
        $this->mhtml2pdf->html($html);


        if(!empty($stylesheet)) {
            $stylesheet = file_get_contents(base_url('assets/pdf/'.$designType.'/'.$stylesheet));
        }

        return @$this->mhtml2pdf->create('save',$this->data['panel_title'], $stylesheet);
    }

    public function reportSendToMail($stylesheet=NULL, $data=NULL, $viewpath=NULL, $email=NULL, $subject=NULL, $message=NULL, $pagesize = 'a4', $pagetype='portrait') {
        $this->load->model('emailsetting_m');

        $designType = 'LTR';
        $this->load->library('email');
        $this->load->library('mhtml2pdf');
        $this->mhtml2pdf->folder('uploads/report/');
        $rand    = random19() . date('y-m-d h:i:s');
        $sharand = hash('sha512', $rand);

        $this->mhtml2pdf->filename($sharand);
        $this->mhtml2pdf->paper($pagesize, $pagetype);
        $this->data['panel_title'] = $this->lang->line('panel_title');
        $html = $this->load->view($viewpath, $this->data, true);
        $this->mhtml2pdf->html($html);


        if(!empty($stylesheet)) {
            $stylesheet = file_get_contents(base_url('assets/pdf/'.$designType.'/'.$stylesheet));
        }

        $emailsetting = $this->emailsetting_m->get_emailsetting();
        $this->email->set_mailtype("html");

        if(customCompute($emailsetting)) {
            if($path = @$this->mhtml2pdf->create('save',$this->data['panel_title'], $stylesheet)) {
                if($emailsetting->email_engine == 'smtp') {
                    if ($emailsetting->smtp_security){
                        $config = [
                            'protocol'    => 'smtp',
                            'smtp_host'   => $emailsetting->smtp_server,
                            'smtp_port'   => $emailsetting->smtp_port,
                            'smtp_user'   => $emailsetting->smtp_username,
                            'smtp_pass'   => $emailsetting->smtp_password,
                            'smtp_crypto' => $emailsetting->smtp_security,
                            'mailtype'    => 'html',
                            'charset'     => 'utf-8',
                            'crlf' => "\r\n",
                            'newline' => "\r\n"
                        ];
                    } else{
                        $config = [
                            'protocol'    => 'smtp',
                            'smtp_host'   => $emailsetting->smtp_server,
                            'smtp_port'   => $emailsetting->smtp_port,
                            'smtp_user'   => $emailsetting->smtp_username,
                            'smtp_pass'   => $emailsetting->smtp_password,
                            'mailtype'    => 'html',
                            'charset'     => 'utf-8',
                            'crlf' => "\r\n",
                            'newline' => "\r\n"
                        ];
                    }
                    $this->email->initialize($config);
                }

                $fromEmail = $emailsetting->smtp_username;
                /*if($this->session->userdata('email') != '') {
                    $fromEmail = $this->session->userdata('email');
                }*/

                $this->email->from($fromEmail, $this->data['siteinfos']->sname);
                $this->email->to($email);
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->attach($path);
                if($this->email->send()) {
                    $this->session->set_flashdata('success', $this->lang->line('mail_success'));
                    $result['check'] = TRUE;
          					return $result;
                } else {
                    $this->session->set_flashdata('error', $this->lang->line('mail_error'));
                    $result['check'] = FALSE;
          					return $result;
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('mail_error'));
        }
    }

    public function reportSendToMail2($stylesheet=NULL, $data=NULL, $viewpath=NULL, $email=NULL, $subject=NULL, $message=NULL, $pagesize = 'a4', $pagetype='portrait') {
        $this->load->model('emailsetting_m');

        $designType = 'LTR';
        $paths = [];
        $this->load->library('email');
        $this->load->library('mhtml2pdf');
        $this->mhtml2pdf->folder('uploads/report/');

        $this->mhtml2pdf->paper($pagesize, $pagetype);

        if(!empty($stylesheet)) {
            $stylesheet = file_get_contents(base_url('assets/pdf/'.$designType.'/'.$stylesheet));
        }

        foreach ($data as $item) {
          $rand    = random19() . date('y-m-d h:i:s');
          $sharand = $item['student']->srname . hash('sha512', $rand);
          $item['panel_title'] = $this->lang->line('panel_title');
          $html = $this->load->view($viewpath, $item, true);
          $this->mhtml2pdf->filename($sharand);
          $this->mhtml2pdf->html($html);
          $paths[] = $this->mhtml2pdf->create('save',$item['panel_title'], $stylesheet);
        }

        $emailsetting = $this->emailsetting_m->get_emailsetting();
        $this->email->set_mailtype("html");

        if(customCompute($emailsetting)) {
            if($emailsetting->email_engine == 'smtp') {
                if ($emailsetting->smtp_security){
                    $config = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $emailsetting->smtp_server,
                        'smtp_port'   => $emailsetting->smtp_port,
                        'smtp_user'   => $emailsetting->smtp_username,
                        'smtp_pass'   => $emailsetting->smtp_password,
                        'smtp_crypto' => $emailsetting->smtp_security,
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'crlf' => "\r\n",
                        'newline' => "\r\n"
                    ];
                } else{
                    $config = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $emailsetting->smtp_server,
                        'smtp_port'   => $emailsetting->smtp_port,
                        'smtp_user'   => $emailsetting->smtp_username,
                        'smtp_pass'   => $emailsetting->smtp_password,
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'crlf' => "\r\n",
                        'newline' => "\r\n"
                    ];
                }
                $this->email->initialize($config);
            }

            $fromEmail = $emailsetting->smtp_username;
            /*if($this->session->userdata('email') != '') {
                $fromEmail = $this->session->userdata('email');
            }*/

            $this->email->clear(TRUE);
            $this->email->from($fromEmail, $this->data['siteinfos']->sname);
            $this->email->to($email);
            $this->email->subject($subject);
            $this->email->message($message);
            foreach ($paths as $path) {
                $this->email->attach($path);
            }

            if($this->email->send()) {
                $this->session->set_flashdata('success', $this->lang->line('mail_success'));
                $result['check'] = TRUE;
      					return $result;
            } else {
                $this->session->set_flashdata('error', $this->lang->line('mail_error'));
                $result['check'] = FALSE;
      					return $result;
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('mail_error'));
        }
    }

    public function getAllCountry() {
        $country = array(
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "BQ" => "British Antarctic Territory",
            "IO" => "British Indian Ocean Territory",
            "VG" => "British Virgin Islands",
            "BN" => "Brunei",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CT" => "Canton and Enderbury Islands",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos [Keeling] Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo - Brazzaville",
            "CD" => "Congo - Kinshasa",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "CI" => "Côte d’Ivoire",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "NQ" => "Dronning Maud Land",
            "DD" => "East Germany",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "FQ" => "French Southern and Antarctic Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and McDonald Islands",
            "HN" => "Honduras",
            "HK" => "Hong Kong SAR China",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JT" => "Johnston Island",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Laos",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macau SAR China",
            "MK" => "Macedonia",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "FX" => "Metropolitan France",
            "MX" => "Mexico",
            "FM" => "Micronesia",
            "MI" => "Midway Islands",
            "MD" => "Moldova",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar [Burma]",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NT" => "Neutral Zone",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "KP" => "North Korea",
            "VD" => "North Vietnam",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PC" => "Pacific Islands Trust Territory",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territories",
            "PA" => "Panama",
            "PZ" => "Panama Canal Zone",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "YD" => "People's Democratic Republic of Yemen",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn Islands",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RO" => "Romania",
            "RU" => "Russia",
            "RW" => "Rwanda",
            "RE" => "Réunion",
            "BL" => "Saint Barthélemy",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "MF" => "Saint Martin",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "KR" => "South Korea",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syria",
            "ST" => "São Tomé and Príncipe",
            "TW" => "Taiwan",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UM" => "U.S. Minor Outlying Islands",
            "PU" => "U.S. Miscellaneous Pacific Islands",
            "VI" => "U.S. Virgin Islands",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "SU" => "Union of Soviet Socialist Republics",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "ZZ" => "Unknown or Invalid Region",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VA" => "Vatican City",
            "VE" => "Venezuela",
            "VN" => "Vietnam",
            "WK" => "Wake Island",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe",
            "AX" => "Åland Islands",
        );
        return $country;
    }

    private function _bloodGroup() {
        $bloodgroup = array(
            'A+' => 'A+',
            'A-' => 'A-',
            'B+' => 'B+',
            'B-' => 'B-',
            'O+' => 'O+',
            'O-' => 'O-',
            'AB+' => 'AB+',
            'AB-' => 'AB-'
        );
        return $bloodgroup;
    }

    private function _menuNodePermissionCandidates(array $node)
    {
        $candidates = [];

        foreach (['permission', 'permission_key'] as $permissionField) {
            if (!empty($node[$permissionField]) && is_string($node[$permissionField])) {
                $candidates[] = trim($node[$permissionField]);
            }
        }

        foreach (['menuName', 'menu_name'] as $menuField) {
            if (!empty($node[$menuField]) && is_string($node[$menuField])) {
                $candidates[] = trim($node[$menuField]);
            }
        }

        if (!empty($node['link']) && is_string($node['link'])) {
            $link = ltrim(trim($node['link']), '/');
            if ($link !== '' && $link !== '#') {
                $candidates[] = $link;
            }
        }

        $expanded = [];
        foreach ($candidates as $candidate) {
            if ($candidate === '' || $candidate === '#') {
                continue;
            }

            $expanded[] = $candidate;

            if (strpos($candidate, '.') !== false) {
                $expanded[] = str_replace('.', '_', $candidate);
            }

            if (strpos($candidate, '_') !== false) {
                $expanded[] = str_replace('_', '.', $candidate);
            }

            if (strpos($candidate, '/') !== false) {
                $expanded[] = str_replace('/', '_', $candidate);
            }
        }

        $expanded = array_filter(array_unique($expanded), function ($value) {
            return $value !== '' && $value !== '#';
        });

        return array_values($expanded);
    }

    private function _shouldIncludeMenuNode(array $node, $sessionPermission)
    {
        if (!empty($node['skip_permission'])) {
            return true;
        }

        $link = isset($node['link']) ? trim($node['link']) : '';
        if ($link === '#') {
            return true;
        }

        if (!is_array($sessionPermission)) {
            $sessionPermission = [];
        }

        foreach ($this->_menuNodePermissionCandidates($node) as $candidate) {
            if (isset($sessionPermission[$candidate]) && $sessionPermission[$candidate] !== 'no') {
                return true;
            }
        }

        return false;
    }

    public function menuTree($dataset, $sessionPermission) {
        $tree = array();
        foreach ($dataset as $id=>&$node) {
            if($this->_shouldIncludeMenuNode($node, $sessionPermission)) {
                if ($node['parentID'] == 0) {
                    $tree[$id]=&$node;
                } else {
                    if (!isset($dataset[$node['parentID']]['child']))
                        $dataset[$node['parentID']]['child'] = array();

                    $dataset[$node['parentID']]['child'][$id] = &$node;
                }
            }
        }
        return $tree;
    }

    private function _copyTree($nodes) {
        $tree = [];
        foreach ($nodes as $nodeID => &$node) {
            $newNode = [];
            foreach($node as $key => $value) {
                if ($key !== 'child') {
                    $newNode[$key] = $value;
                }
            }

            if (isset($node['child'])) {
                $newNode['child'] = $this->_copyTree($node['child']);
            }
            $tree[$nodeID] = $newNode;
        }
        return $tree;
    }

    public function getHolidays() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->data['siteinfos']->school_year;
        $holidays = $this->holiday_m->get_order_by_holiday(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        $allHolidayList = array();
        if(customCompute($holidays)) {
            foreach ($holidays as $holiday) {
                $from_date = strtotime($holiday->fdate);
                $to_date   = strtotime($holiday->tdate);
                $oneday    = 60*60*24;
                for($i= $from_date; $i<= $to_date; $i= $i+$oneday) {
                    $allHolidayList[] = date('d-m-Y', $i);
                }
            }
        }

        $uniqueHolidays =  array_unique($allHolidayList);
        if(customCompute($uniqueHolidays)) {
            $uniqueHolidays = implode('","', $uniqueHolidays);
        } else {
            $uniqueHolidays = '';
        }

        return $uniqueHolidays;
    }

    public function getHolidaysSession() {
        $schoolID = $this->session->userdata('schoolID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $holidays = $this->holiday_m->get_order_by_holiday(array('schoolyearID' => $schoolyearID, 'schoolID' => $schoolID));
        $allHolidayList = array();
        if(customCompute($holidays)) {
            foreach ($holidays as $holiday) {
                $from_date = strtotime($holiday->fdate);
                $to_date   = strtotime($holiday->tdate);
                $oneday    = 60*60*24;
                for($i= $from_date; $i<= $to_date; $i= $i+$oneday) {
                    $allHolidayList[] = date('d-m-Y', $i);
                }
            }
        }

        $uniqueHolidays =  array_unique($allHolidayList);
        if(customCompute($uniqueHolidays)) {
            $uniqueHolidays = implode('","', $uniqueHolidays);
        } else {
            $uniqueHolidays = '';
        }

        return $uniqueHolidays;
    }

    public function getWeekendDays() {
        $date_from = strtotime($this->data['schoolyearobj']->startingdate);
        $date_to = strtotime($this->data['schoolyearobj']->endingdate);
        $oneDay = 60*60*24;

        $allDays = array(
            '0' => 'Sunday',
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday'
        );

        $weekendDay    = $this->data['siteinfos']->weekends;
        $weekendArrays = explode(',', $weekendDay);
        $weekendDateArrays = array();
        for($i= $date_from; $i<= $date_to; $i= $i+$oneDay) {
            if($weekendDay != "") {
                foreach($weekendArrays as $weekendValue) {
                    if($weekendValue >= 0 && $weekendValue <= 6) {
                        if(date('l',$i) == $allDays[$weekendValue]) {
                            $weekendDateArrays[] = date('d-m-Y',
$i);
                        }
                    }
                }
            }
        }
        return $weekendDateArrays;
    }

    private function _fixMenuLinks($menus) {
        $linkCorrections = [
            'feetype' => 'feetypes',
            'credittype' => 'credittypes',
            'payment' => 'make_payment',
            'credit_memo' => 'creditmemo',
            'quickbook' => 'quickbookssettings',
            'sattendance' => 'sattendance/sattendance',
            'tattendance' => 'tattendance/tattendance',
            'eattendance' => 'eattendance/eattendance'
        ];

        $menus = $this->_applyLinkCorrections($menus, $linkCorrections);

        $overrides = $this->_loadMenuOverrides();

        if (!empty($overrides['custom_nodes'])) {
            foreach ($overrides['custom_nodes'] as $nodeConfig) {
                if ($this->_shouldSkipMenuOverride($nodeConfig)) {
                    continue;
                }

                $menus = $this->_insertMenuNode($menus, $nodeConfig);
            }
        }

        if (!empty($overrides['relocations'])) {
            foreach ($overrides['relocations'] as $relocation) {
                $menus = $this->_relocateMenuNode($menus, $relocation);
            }
        }

        return $this->_sortMenuTree($menus);
    }

    private function _applyLinkCorrections($menus, $corrections) {
        foreach ($menus as &$menu) {
            if (isset($menu['link'], $corrections[$menu['link']])) {
                $menu['link'] = $corrections[$menu['link']];
            }

            if (isset($menu['child']) && is_array($menu['child'])) {
                $menu['child'] = $this->_applyLinkCorrections($menu['child'], $corrections);
            }
        }

        return $menus;
    }

    private function _loadMenuOverrides() {
        $overrides = [
            'custom_nodes' => [],
            'relocations' => [],
        ];

        $this->load->model('menu_override_m');
        $storedOverrides = $this->menu_override_m->get_grouped_overrides();

        if (customCompute($storedOverrides['custom_nodes']) || customCompute($storedOverrides['relocations'])) {
            return $storedOverrides;
        }

        $this->config->load('menu_overrides', true);

        $customNodes = $this->config->item('menu_custom_nodes', 'menu_overrides');
        if (is_array($customNodes)) {
            $overrides['custom_nodes'] = $customNodes;
        }

        $relocations = $this->config->item('menu_relocations', 'menu_overrides');
        if (is_array($relocations)) {
            $overrides['relocations'] = $relocations;
        }

        return $overrides;
    }

    private function _relocateMenuNode($menus, $config) {
        if (!is_array($config) || (!isset($config['menuName']) && !isset($config['link'])) || !isset($config['parent'])) {
            return $menus;
        }

        $identifier = isset($config['menuName']) ? $config['menuName'] : $config['link'];
        $node = $this->_removeMenuNode($menus, $identifier);

        if ($node === null && isset($config['link'])) {
            $node = $this->_removeMenuNode($menus, $config['link']);
        }

        if ($node === null) {
            return $menus;
        }

        $allowedKeys = ['menuName', 'link', 'icon', 'priority', 'status'];
        foreach ($allowedKeys as $key) {
            if (isset($config[$key])) {
                $node[$key] = $config[$key];
            }
        }

        if (array_key_exists('skip_permission', $config)) {
            if ($config['skip_permission']) {
                $node['skip_permission'] = true;
            } else {
                unset($node['skip_permission']);
            }
        }

        $createIfMissing = isset($config['create_if_missing']) ? $config['create_if_missing'] : false;

        return $this->_placeMenuNode($menus, $node, $config['parent'], $createIfMissing);
    }

    private function _removeMenuNode(&$nodes, $identifier) {
        foreach ($nodes as $index => &$node) {
            if ($this->_menuNodeMatches($node, $identifier)) {
                $removed = $node;
                unset($nodes[$index]);
                return $removed;
            }

            if (isset($node['child']) && is_array($node['child'])) {
                $removed = $this->_removeMenuNode($node['child'], $identifier);
                if ($removed !== null) {
                    if (empty($node['child'])) {
                        unset($node['child']);
                    }
                    return $removed;
                }
            }
        }

        return null;
    }

    private function _insertMenuNode($menus, $config) {
        if (!is_array($config) || !isset($config['menuName'])) {
            return $menus;
        }

        $node = $this->_removeMenuNode($menus, $config['menuName']);

        if ($node === null && isset($config['link'])) {
            $node = $this->_removeMenuNode($menus, $config['link']);
        }

        if ($node === null) {
            $node = [];
        }

        $node['menuName'] = $config['menuName'];
        $node['link'] = isset($config['link']) ? $config['link'] : (isset($node['link']) ? $node['link'] : '#');
        $node['icon'] = isset($config['icon']) ? $config['icon'] : (isset($node['icon']) ? $node['icon'] : 'fa-circle-o');
        $node['priority'] = isset($config['priority']) ? (int) $config['priority'] : (isset($node['priority']) ? (int) $node['priority'] : 0);
        $node['status'] = isset($config['status']) ? (int) $config['status'] : (isset($node['status']) ? (int) $node['status'] : 1);

        if (array_key_exists('skip_permission', $config)) {
            if ($config['skip_permission']) {
                $node['skip_permission'] = true;
            } else {
                unset($node['skip_permission']);
            }
        }

        $parent = isset($config['parent']) ? $config['parent'] : null;
        $createIfMissing = isset($config['create_if_missing']) ? $config['create_if_missing'] : false;

        return $this->_placeMenuNode($menus, $node, $parent, $createIfMissing);
    }

    private function _placeMenuNode($menus, $node, $parentIdentifier, $createIfMissing = false) {
        $parentAttributes = [];
        if (is_array($createIfMissing)) {
            $parentAttributes = $createIfMissing;
            $createIfMissing = true;
        }

        if ($parentIdentifier !== null) {
            $parentRef = $this->_locateMenuNode($menus, $parentIdentifier);

            if ($parentRef === null && $createIfMissing) {
                $placeholder = array_merge([
                    'menuName' => $parentIdentifier,
                    'link' => '#',
                    'icon' => 'fa-circle-o',
                    'priority' => 0,
                    'status' => 1,
                ], $parentAttributes);

                $menus[] = $placeholder;
                $parentRef = $this->_locateMenuNode($menus, $parentIdentifier);
            }

            if ($parentRef !== null) {
                if (!isset($parentRef['node']['child']) || !is_array($parentRef['node']['child'])) {
                    $parentRef['node']['child'] = [];
                }

                $parentRef['node']['child'][] = $node;
                return $menus;
            }
        }

        $menus[] = $node;
        return $menus;
    }

    private function _locateMenuNode(&$nodes, $identifier) {
        foreach ($nodes as $index => &$node) {
            if ($this->_menuNodeMatches($node, $identifier)) {
                return [
                    'node' => &$nodes[$index],
                    'index' => $index,
                    'parent' => &$nodes,
                ];
            }

            if (isset($node['child']) && is_array($node['child'])) {
                $found = $this->_locateMenuNode($node['child'], $identifier);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function _menuNodeMatches($node, $identifier) {
        return (isset($node['menuName']) && $node['menuName'] === $identifier)
            || (isset($node['link']) && $node['link'] === $identifier);
    }

    private function _sortMenuTree($nodes) {
        $nodes = array_values($nodes);

        usort($nodes, function ($a, $b) {
            $priorityA = isset($a['priority']) ? (int) $a['priority'] : PHP_INT_MAX;
            $priorityB = isset($b['priority']) ? (int) $b['priority'] : PHP_INT_MAX;

            if ($priorityA === $priorityB) {
                $nameA = isset($a['menuName']) ? $a['menuName'] : '';
                $nameB = isset($b['menuName']) ? $b['menuName'] : '';
                return strcasecmp($nameA, $nameB);
            }

            return $priorityA - $priorityB;
        });

        foreach ($nodes as &$node) {
            if (isset($node['child']) && is_array($node['child'])) {
                $node['child'] = $this->_sortMenuTree($node['child']);
            }
        }

        return $nodes;
    }

    private function _shouldSkipMenuOverride($config)
    {
        if (!is_array($config)) {
            return false;
        }

        $label = isset($config['menuName']) ? $config['menuName'] : '[unnamed]';

        if (!empty($config['feature_flag']) && function_exists('feature_flag_enabled')) {
            $flag = $config['feature_flag'];
            if (!feature_flag_enabled($flag)) {
                if (function_exists('log_message')) {
                    log_message('debug', sprintf('Skipping menu override "%s" because feature flag %s is disabled.', $label, $flag));
                }

                return true;
            }
        }

        if (!empty($config['controller'])) {
            $controllerPath = $this->_normalizeControllerPath($config['controller']);
            $controllerFile = $this->_resolveControllerFile($controllerPath);

            if (!is_file($controllerFile)) {
                if (function_exists('log_message')) {
                    log_message('debug', sprintf('Skipping menu override "%s" because controller %s is missing.', $label, $controllerPath));
                }

                return true;
            }

            $method = !empty($config['method']) ? $config['method'] : 'index';
            if (!$this->_controllerMethodExists($controllerPath, $method)) {
                if (function_exists('log_message')) {
                    log_message('debug', sprintf('Skipping menu override "%s" because method %s::%s is unavailable.', $label, $controllerPath, $method));
                }

                return true;
            }
        }

        return false;
    }

    private function _normalizeControllerPath($controller)
    {
        $normalized = str_replace('\\', '/', trim($controller));
        return ltrim($normalized, '/');
    }

    private function _resolveControllerFile($controllerPath)
    {
        return APPPATH . 'controllers/' . $controllerPath . '.php';
    }

    private function _controllerMethodExists($controllerPath, $method)
    {
        $controllerFile = $this->_resolveControllerFile($controllerPath);

        if (!is_file($controllerFile)) {
            return false;
        }

        static $methodCache = [];
        $cacheKey = $controllerFile . '::' . strtolower($method);

        if (array_key_exists($cacheKey, $methodCache)) {
            return $methodCache[$cacheKey];
        }

        $contents = file_get_contents($controllerFile);
        if ($contents === false) {
            $methodCache[$cacheKey] = false;
            return false;
        }

        $pattern = '/function\s+' . preg_quote($method, '/') . '\s*\(/i';
        $methodCache[$cacheKey] = (bool) preg_match($pattern, $contents);

        return $methodCache[$cacheKey];
    }

    public function getWeekendDaysSession() {
        $date_from = strtotime($this->data['schoolyearsessionobj']->startingdate);
        $date_to   = strtotime($this->data['schoolyearsessionobj']->endingdate);
        $oneDay    = 60*60*24;

        $allDays = array(
            '0' => 'Sunday',
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday'
        );

        $weekendDay = $this->data['siteinfos']->weekends;
        $weekendArrays = explode(',', $weekendDay);

        $weekendDateArrays = array();

        for($i= $date_from; $i<= $date_to; $i= $i+$oneDay) {
            if($weekendDay != "") {
                foreach($weekendArrays as $weekendValue) {
                    if($weekendValue >= 0 && $weekendValue <= 6) {
                        if(date('l',$i) == $allDays[$weekendValue]) {
                            $weekendDateArrays[] = date('d-m-Y', $i);
                        }
                    }
                }
            }
        }
        return $weekendDateArrays;
    }

    public function quickbooksConfig()
  	{
  		$config = array();
  		$get_quickbooks = $this->quickbookssettings_m->get_quickbooksetting_values(array('schoolID' => $this->session->userdata('schoolID')));
  		foreach ($get_quickbooks as $key => $value) {
  			$config[$value->field_names] = $value->field_values;
  		}

  		return $config;
  	}
}
