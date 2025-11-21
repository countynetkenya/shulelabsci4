<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');

class Mailandsms extends Admin_Controller {
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
	function __construct () {
		parent::__construct();
		$this->load->model('usertype_m');
		$this->load->model('systemadmin_m');
		$this->load->model('teacher_m');
		$this->load->model('student_m');
		$this->load->model('parents_m');
		$this->load->model('user_m');
		$this->load->model('classes_m');
		$this->load->model('section_m');
		$this->load->model("mark_m");
		$this->load->model("grade_m");
		$this->load->model("exam_m");
		$this->load->model('mailandsms_m');
		$this->load->model('mailandsmstemplate_m');
		$this->load->model('mailandsmstemplatetag_m');
		$this->load->model('studentgroup_m');
		$this->load->model('studentrelation_m');
		$this->load->model('payment_m');
		$this->load->model('invoice_m');
		$this->load->model('creditmemo_m');
		$this->load->model('emailsetting_m');
		$this->load->model('subject_m');
		$this->load->model('payment_gateway_option_m');
		$this->load->model('fees_balance_tier_m');
		$this->load->model('schoolterm_m');
		$this->load->library("email");
		$this->load->library("inilabs",$this->data);

		$language = $this->session->userdata('lang');
		$this->lang->load('global_payment', $language);
		$this->lang->load('mailandsms', $language);
	}

	protected function rules_mail() {
		$rules = array(
			array(
				'field' => 'email_usertypeID',
				'label' => $this->lang->line("mailandsms_usertype"),
				'rules' => 'trim|required|xss_clean|max_length[15]|callback_check_email_usertypeID'
			),
			array(
				'field' => 'email_schoolyear',
				'label' => $this->lang->line("mailandsms_schoolyear"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_class',
				'label' => $this->lang->line("mailandsms_class"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_users',
				'label' => $this->lang->line("mailandsms_users"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_template',
				'label' => $this->lang->line("mailandsms_template"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_subject',
				'label' => $this->lang->line("mailandsms_subject"),
				'rules' => 'trim|required|xss_clean|max_length[255]'
			),
			array(
				'field' => 'email_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
		);
		return $rules;
	}

	protected function rules_sms() {
		$rules = array(
			array(
				'field' => 'sms_usertypeID',
				'label' => $this->lang->line("mailandsms_usertype"),
				'rules' => 'trim|required|xss_clean|max_length[15]|callback_check_sms_usertypeID'
			),
			array(
				'field' => 'sms_schoolyear',
				'label' => $this->lang->line("mailandsms_schoolyear"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_class',
				'label' => $this->lang->line("mailandsms_select_class"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_active',
				'label' => $this->lang->line("mailandsms_status"),
				'rules' => 'trim|xss_clean|callback_unique_smsactive'
			),
			array(
				'field' => 'sms_users',
				'label' => $this->lang->line("mailandsms_users"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_template',
				'label' => $this->lang->line("mailandsms_template"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_getway',
				'label' => $this->lang->line("mailandsms_getway"),
				'rules' => 'trim|required|xss_clean|max_length[15]|callback_check_getway'
			),
			array(
				'field' => 'sms_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
		);
		return $rules;
	}

	protected function rules_otheremail() {
		$rules = array(
			array(
				'field' => 'otheremail_email',
				'label' => $this->lang->line("mailandsms_email"),
				'rules' => 'trim|required|xss_clean|valid_email'
			),
			array(
				'field' => 'otheremail_subject',
				'label' => $this->lang->line("mailandsms_subject"),
				'rules' => 'trim|required|xss_clean|max_length[255]'
			),
			array(
				'field' => 'otheremail_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			)
		);
		return $rules;
	}

	protected function rules_othersms() {
		$rules = array(
			array(
				'field' => 'othersms_phone',
				'label' => $this->lang->line("mailandsms_phone"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'sms_getway',
				'label' => $this->lang->line("mailandsms_getway"),
				'rules' => 'trim|required|xss_clean|callback_unique_data|max_length[15]|callback_check_getway'
			),
			array(
				'field' => 'othersms_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
		);
		return $rules;
	}

	public function index() {
		$this->data['headerassets'] = array(
				'css' => array(
						'assets/datepicker/datepicker.css',
				),
				'js' => array(
						'assets/datepicker/datepicker.js',
				)
		);

		$schoolID = $this->session->userdata('schoolID');
		$usertypeID = $this->session->userdata("usertypeID");
		$userID = $this->session->userdata("loginuserID");
		$array = ["schoolID" => $schoolID, "reviewed" => 1];
		if ($usertypeID == 4)
			$array["userID"] = $userID;
		$this->data['mailandsmss'] = $this->mailandsms_m->get_mailandsms_with_usertypeID($array);
		$this->data["subview"] = "mailandsms/index";
		$this->load->view('_layout_main', $this->data);
	}

	public function review() {
		$schoolID = $this->session->userdata('schoolID');
		$this->data['mailandsmss'] = $this->mailandsms_m->get_mailandsms_with_usertypeID(array("reviewed" => 0, "schoolID" => $schoolID));
		$this->data["subview"] = "mailandsms/review";
		$this->load->view('_layout_main', $this->data);
	}

	public function add() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css',
				'assets/editor/jquery-te-1.4.0.css'
			),
			'js' => array(
				'assets/select2/select2.js',
				'assets/editor/jquery-te-1.4.0.min.js'
			)
		);
		$schoolID = $this->session->userdata('schoolID');
		$this->data['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID));
		$this->data['schoolyears'] = $this->schoolyear_m->get_order_by_schoolyear(array('schoolID' => $schoolID));
		$this->data['allClasses'] = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
    $this->data['sections'] = [];
    $classesID = $this->input->post("classesID");

    if($classesID > 0) {
        $this->data['sections'] = $this->section_m->get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
    } else {
        $this->data['sections'] = [];
    }

    /* Start For Email */
		$email_usertypeID = $this->input->post("email_usertypeID");
		if($email_usertypeID && $email_usertypeID != 'select') {
			$this->data['email_usertypeID'] = $email_usertypeID;
		} else {
			$this->data['email_usertypeID'] = 'select';
		}
		/* End For Email */

		/* Start For SMS */
		$sms_usertypeID = $this->input->post("sms_usertypeID");
		if($sms_usertypeID && $sms_usertypeID != 'select') {
			$this->data['sms_usertypeID'] = $sms_usertypeID;
		} else {
			$this->data['sms_usertypeID'] = 'select';
		}
		/* End For SMS */

		if($_POST) {
			$this->data['submittype'] = $this->input->post('type');
			if($this->input->post('type') == "email") {
				$rules = $this->rules_mail();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data['emailUserID'] = $this->input->post('email_users');
					$this->data['emailTemplateID'] = $this->input->post('email_template');

					$this->data['allStudents'] = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID, 'srschoolyearID' => $this->input->post('email_schoolyear'), 'srclassesID' => $this->input->post('email_class')), TRUE);

					$this->data['smsUserID'] = 0;
					$this->data['smsTemplateID'] = 0;

					$this->data["email"] = 1;
					$this->data["sms"] = 0;
					$this->data["otheremail"] = 0;
					$this->data["othersms"] = 0;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$usertypeID = $this->input->post('email_usertypeID');
					$schoolyearID = $this->input->post('email_schoolyear');
					$attachment = $this->input->post('email_attachment');
					$use_parent_contact = $this->input->post('email_use_parent_contact');

					if($usertypeID == 1) { /* FOR ADMIN */
						$systemadminID = $this->input->post('email_users');
						if($systemadminID == 'select') {
							$message = $this->input->post('email_message');
							$multisystemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
							if(customCompute($multisystemadmins)) {
								foreach ($multisystemadmins as $key => $multisystemadmin) {
									$configuredMessage = $this->userConfigEmail($message, $multisystemadmin, $usertypeID, $schoolID, $schoolyearID);
									$array = array(
										'userID' => $multisystemadmin->systemadminID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multisystemadmin->name,
										'recipient' => $multisystemadmin->email,
										'type' => ucfirst($this->input->post('type')),
										'message' => $configuredMessage,
										'subject' => $this->input->post('email_subject'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singlesystemadmin = $this->systemadmin_m->get_order_by_systemadmin(array('systemadminID' => $systemadminID, 'schoolID' => $schoolID));
							if(customCompute($singlesystemadmin)) {
								$configuredMessage = $this->userConfigEmail($message, $singlesystemadmin, $usertypeID, $schoolID);
								$array = array(
									'userID' => $systemadminID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singlesystemadmin->name,
									'recipient' => $singlesystemadmin->email,
									'type' => ucfirst($this->input->post('type')),
									'message' => $configuredMessage,
									'subject' => $this->input->post('email_subject'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 2) { /* FOR TEACHER */
						$teacherID = $this->input->post('email_users');
						if($teacherID == 'select') {
							$message = $this->input->post('email_message');
							$multiteachers = $this->teacher_m->general_get_order_by_teacher(array('schoolID' => $schoolID));
							if(customCompute($multiteachers)) {
								foreach ($multiteachers as $key => $multiteacher) {
									$configuredMessage = $this->userConfigEmail($message, $multiteacher, $usertypeID, $schoolID);
									$array = array(
										'userID' => $multiteacher->teacherID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multiteacher->name,
										'recipient' => $multiteacher->email,
										'type' => ucfirst($this->input->post('type')),
										'message' => $configuredMessage,
										'subject' => $this->input->post('email_subject'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singleteacher = $this->teacher_m->general_get_single_teacher(array("teacherID" => $teacherID, "schoolID" => $schoolID));
							if(customCompute($singleteacher)) {
								$configuredMessage = $this->userConfigEmail($message, $singleteacher, $usertypeID, $schoolID);
								$array = array(
									'userID' => $teacherID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singleteacher->name,
									'recipient' => $singleteacher->email,
									'type' => ucfirst($this->input->post('type')),
									'message' => $configuredMessage,
									'subject' => $this->input->post('email_subject'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));

							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 3) { /* FOR STUDENT */
						$studentID = $this->input->post('email_users');
						if($studentID == 'select') {
							$class = $this->input->post('email_class');
							if($class == 'select') {
								/* Multi School Year */
								$schoolyear = $this->input->post('email_schoolyear');
								if($schoolyear == 'select') {
									$message = $this->input->post('email_message');
									$multiSchoolYearStudents = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID), TRUE);
									if(customCompute($multiSchoolYearStudents)) {
										//$countusers = '';
										foreach ($multiSchoolYearStudents as $key => $multiSchoolYearStudent) {
											$configuredMessage = $this->userConfigEmail($message, $multiSchoolYearStudent, $usertypeID, $schoolID, $multiSchoolYearStudent->srschoolyearID);
											if ($use_parent_contact) {
												$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $multiSchoolYearStudent->parentID, 'schoolID' => $schoolID));
												$recipient = $singleparent->email;
											}
											else {
												$recipient = $multiSchoolYearStudent->email;
											}
											$array = array(
												'userID' => $multiSchoolYearStudent->srstudentID,
												'usertypeID' => $usertypeID,
												'schoolID' => $schoolID,
												'users' => $multiSchoolYearStudent->srname,
												'recipient' => $recipient,
												'type' => ucfirst($this->input->post('type')),
												'message' => $configuredMessage,
												'subject' => $this->input->post('email_subject'),
												'attachment' => $this->input->post('email_attachment'),
												'year' => date('Y'),
												'senderusertypeID' => $this->session->userdata('usertypeID'),
												'senderID' => $this->session->userdata('loginuserID'),
												'sendername' => $this->session->userdata('username')
											);
											$this->mailandsms_m->insert_mailandsms($array);
										}
										redirect(base_url('mailandsms/review'));
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								} else {
									/* Single school Year Student */
									$message = $this->input->post('email_message');
									$singleSchoolYear = $this->input->post('email_schoolyear');
									$singleSchoolYearStudents = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID, 'srschoolyearID' => $singleSchoolYear, 'active' => 1), TRUE);
									if(customCompute($singleSchoolYearStudents)) {
										foreach ($singleSchoolYearStudents as $key => $singleSchoolYearStudent) {
											$configuredMessage = $this->userConfigEmail($message, $singleSchoolYearStudent, $usertypeID, $schoolID, $schoolyearID);
											if ($use_parent_contact) {
												$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $singleSchoolYearStudent->parentID, 'schoolID' => $schoolID));
												$recipient = $singleparent->email;
											}
											else {
												$recipient = $singleSchoolYearStudent->email;
											}
											$array = array(
												'userID' => $singleSchoolYearStudent->srstudentID,
												'usertypeID' => $usertypeID,
												'schoolID' => $schoolID,
												'users' => $singleSchoolYearStudent->srname,
												'recipient' => $recipient,
												'type' => ucfirst($this->input->post('type')),
												'message' => $configuredMessage,
												'subject' => $this->input->post('email_subject'),
												'attachment' => $this->input->post('email_attachment'),
												'year' => date('Y'),
												'senderusertypeID' => $this->session->userdata('usertypeID'),
												'senderID' => $this->session->userdata('loginuserID'),
												'sendername' => $this->session->userdata('username')
											);
											$this->mailandsms_m->insert_mailandsms($array);
										}
										redirect(base_url('mailandsms/review'));
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								}
							} else {
								/* Single Class Student */
								$message = $this->input->post('email_message');
								$singleClass = $this->input->post('email_class');
								$singleSection = $this->input->post('email_section');
								if((int)$singleSection){
                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID, 'srclassesID' => $singleClass,'srsectionID' => $singleSection, 'srschoolyearID' => $schoolyearID, 'active' => 1), TRUE);
                }else {
                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student(array('srschoolID' => $schoolID, 'srclassesID' => $singleClass, 'srschoolyearID' => $schoolyearID, 'active' => 1), TRUE);
                }

								if(customCompute($singleClassStudents)) {
									foreach ($singleClassStudents as $key => $singleClassStudent) {
										$configuredMessage = $this->userConfigEmail($message, $singleClassStudent, $usertypeID, $schoolID, $schoolyearID);
										if ($use_parent_contact) {
											$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $singleClassStudent->parentID, 'schoolID' => $schoolID));
											$recipient = $singleparent->email;
										}
										else {
											$recipient = $singleClassStudent->email;
										}
										$array = array(
											'userID' => $singleClassStudent->srstudentID,
											'usertypeID' => $usertypeID,
											'schoolID' => $schoolID,
											'users' => $singleClassStudent->srname,
											'recipient' => $recipient,
											'type' => ucfirst($this->input->post('type')),
											'message' => $configuredMessage,
											'subject' => $this->input->post('email_subject'),
											'attachment' => $this->input->post('email_attachment'),
											'year' => date('Y'),
											'senderusertypeID' => $this->session->userdata('usertypeID'),
											'senderID' => $this->session->userdata('loginuserID'),
											'sendername' => $this->session->userdata('username')
										);
										$this->mailandsms_m->insert_mailandsms($array);
									}
									redirect(base_url('mailandsms/review'));
								} else {
									$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
									redirect(base_url('mailandsms/add'));
								}
							}
						} else {
							/* Single Student */
							$message = $this->input->post('email_message');
							$singlestudent = $this->studentrelation_m->general_get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), TRUE);
							if(customCompute($singlestudent)) {
								$configuredMessage = $this->userConfigEmail($message, $singlestudent, $usertypeID, $schoolID, $schoolyearID);
								if ($use_parent_contact) {
									$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $singlestudent->parentID, 'schoolID' => $schoolID));
									$recipient = $singleparent->email;
								}
								else {
									$recipient = $singlestudent->email;
								}
								$array = array(
									'userID' =>$studentID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singlestudent->srname,
									'recipient' => $recipient,
									'type' => ucfirst($this->input->post('type')),
									'message' => $configuredMessage,
									'subject' => $this->input->post('email_subject'),
									'attachment' => $this->input->post('email_attachment'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);

								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 4) { /* FOR PARENTS */
						$parentsID = $this->input->post('email_users');
						$schoolyearID = $this->session->userdata("defaultschoolyearID");
						if($parentsID == 'select') {
							$message = $this->input->post('email_message');
							$multiparents = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
							if(customCompute($multiparents)) {
								foreach ($multiparents as $key => $multiparent) {
									$configuredMessage = $this->userConfigEmail($message, $multiparent, $usertypeID, $schoolID, $schoolyearID, $attachment);
									$array = array(
										'userID' => $multiparent->parentsID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multiparent->name,
										'recipient' => $multiparent->email,
										'type' => ucfirst($this->input->post('type')),
										'message' => $configuredMessage,
										'subject' => $this->input->post('email_subject'),
										'attachment' => $this->input->post('email_attachment'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $parentsID, 'schoolID' => $schoolID));
							if(customCompute($singleparent)) {
								$configuredMessage = $this->userConfigEmail($message, $singleparent, $usertypeID, $schoolID, $schoolyearID, $attachment);
								$array = array(
									'userID' => $parentsID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singleparent->name,
									'recipient' => $singleparent->email,
									'type' => ucfirst($this->input->post('type')),
									'message' => $configuredMessage,
									'subject' => $this->input->post('email_subject'),
									'attachment' => $this->input->post('email_attachment'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} else { /* FOR ALL USERS */
						$userID = $this->input->post('email_users');
						if($userID == 'select') {
							$message = $this->input->post('email_message');
							$multiusers = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID, 'schoolID' => $schoolID));
							if(customCompute($multiusers)) {
								foreach ($multiusers as $key => $multiuser) {
									$configuredMessage = $this->userConfigEmail($message, $multiuser, $usertypeID, $schoolID);
									$array = array(
										'userID' => $multiuser->userID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multiuser->name,
										'recipient' => $multiuser->email,
										'type' => ucfirst($this->input->post('type')),
										'message' => $configuredMessage,
										'subject' => $this->input->post('email_subject'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singleuser = $this->user_m->get_single_user(array('userID' => $userID, 'schoolID' => $schoolID));
							if(customCompute($singleuser)) {
								$configuredMessage = $this->userConfigEmail($message, $singleuser, $usertypeID, $schoolID);
								$array = array(
									'userID' => $userID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singleuser->name,
									'recipient' => $singleuser->email,
									'type' => ucfirst($this->input->post('type')),
									'message' => $configuredMessage,
									'subject' => $this->input->post('email_subject'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					}
				}
			} elseif($this->input->post('type') == "sms") {
				$rules = $this->rules_sms();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data['smsUserID'] = $this->input->post('sms_users');
					$this->data['smsTemplateID'] = $this->input->post('sms_template');

					$this->data['allStudents'] = $this->studentrelation_m->get_order_by_student(array('srschoolID' => $schoolID, 'srschoolyearID' => $this->input->post('sms_schoolyear'), 'srclassesID' => $this->input->post('sms_class')));

					$this->data['emailUserID'] = 0;
					$this->data['emailTemplateID'] = 0;

					$this->data["email"] = 0;
					$this->data["sms"] = 1;
					$this->data["otheremail"] = 0;
					$this->data["othersms"] = 0;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$getway = $this->input->post('sms_getway');
					$usertypeID = $this->input->post('sms_usertypeID');
					$schoolyearID = $this->input->post('sms_schoolyear');
					$use_parent_contact = $this->input->post('sms_use_parent_contact');

					if($usertypeID == 1) { /* FOR ADMIN */
						$systemadminID = $this->input->post('sms_users');
						if($systemadminID == 'select') {
							$countusers = '';
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$active = $this->input->post('sms_active');
							$query = array('schoolID' => $schoolID);
							if((int)$active) {
								$query['active'] = $active;
							}
							$multisystemadmins = $this->systemadmin_m->get_order_by_systemadmin($query);
							if(customCompute($multisystemadmins)) {
								foreach ($multisystemadmins as $key => $multisystemadmin) {
									$configuredMessage = $this->userConfigSMS($message, $multisystemadmin, $usertypeID, $getway, $schoolID);
									$array = array(
										'userID' => $multisystemadmin->systemadminID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multisystemadmin->name,
										'recipient' => $multisystemadmin->phone,
										'type' => ucfirst($this->input->post('type')),
										'sms_gateway' => $getway,
										'message' => $configuredMessage,
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									if(strlen($configuredMessage) > 0)
										$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$singlesystemadmin = $this->systemadmin_m->get_single_systemadmin(array('systemadminID' => $systemadminID, 'schoolID' => $schoolID));
							if(customCompute($singlesystemadmin)) {
								$configuredMessage = $this->userConfigSMS($message, $singlesystemadmin, $usertypeID, $getway, $schoolID);
								$array = array(
									'userID' => $systemadminID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singlesystemadmin->name,
									'recipient' => $singlesystemadmin->phone,
									'type' => ucfirst($this->input->post('type')),
									'sms_gateway' => $getway,
									'message' => $configuredMessage,
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								if(strlen($configuredMessage) > 0)
									$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 2) { /* FOR TEACHER */
						$teacherID = $this->input->post('sms_users');
						$query = array('schoolID' => $schoolID);
						if($teacherID == 'select') {
							$message = $this->input->post('sms_message');
							$active = $this->input->post('sms_active');
							if((int)$active) {
								$query['active'] = $active;
							}
							$multiteachers = $this->teacher_m->general_get_order_by_teacher($query);
							if(customCompute($multiteachers)) {
								/*$countusers = '';
								$retval = 1;
								$retmess = '';*/
								foreach ($multiteachers as $key => $multiteacher) {
									$configuredMessage = $this->userConfigSMS($message, $multiteacher, $usertypeID, $getway, $schoolID);
									$array = array(
										'userID' => $multiteacher->teacherID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multiteacher->name,
										'recipient' => $multiteacher->phone,
										'type' => ucfirst($this->input->post('type')),
										'sms_gateway' => $getway,
										'message' => $configuredMessage,
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									if(strlen($configuredMessage) > 0)
										$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$singleteacher = $this->teacher_m->general_get_single_teacher(array('teacherID' => $teacherID, 'schoolID' => $schoolID));
							if(customCompute($singleteacher)) {
								$configuredMessage = $this->userConfigSMS($message, $singleteacher, $usertypeID, $getway, $schoolID);
								$array = array(
									'userID' => $teacherID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singleteacher->name,
									'recipient' => $singleteacher->phone,
									'type' => ucfirst($this->input->post('type')),
									'sms_gateway' => $getway,
									'message' => $configuredMessage,
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								if(strlen($configuredMessage) > 0)
									$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 3) { /* FOR STUDENT */
						$studentID = $this->input->post('sms_users');
						$query = array('srschoolID' => $schoolID);
						if($studentID == 'select') {
							$class = $this->input->post('sms_class');
							if($class == 'select') {
								/* Multi School Year */
								$countusers = '';
								$retval = 1;
								$retmess = '';

								$schoolyear = $this->input->post('sms_schoolyear');
								if($schoolyear == 'select') {
									$message = $this->input->post('sms_message');
									$active = $this->input->post('sms_active');
									if((int)$active) {
										$query['active'] = $active;
									}
									$multiSchoolYearStudents = $this->studentrelation_m->general_get_order_by_student($query, TRUE);
									if(customCompute($multiSchoolYearStudents)) {
										foreach ($multiSchoolYearStudents as $key => $multiSchoolYearStudent) {
											$configuredMessage = $this->userConfigSMS($message, $multiSchoolYearStudent, $usertypeID, $getway, $schoolID, $multiSchoolYearStudent->srschoolyearID);
											if ($use_parent_contact) {
												$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $multiSchoolYearStudent->parentID, 'schoolID' => $schoolID));
												$recipient = $singleparent->phone;
											}
											else {
												$recipient = $multiSchoolYearStudent->phone;
											}
											$array = array(
												'userID' => $multiSchoolYearStudent->srstudentID,
												'usertypeID' => $usertypeID,
												'schoolID' => $schoolID,
												'users' => $multiSchoolYearStudent->srname,
												'recipient' => $recipient,
												'type' => ucfirst($this->input->post('type')),
												'sms_gateway' => $getway,
												'message' => $configuredMessage,
												'year' => date('Y'),
												'senderusertypeID' => $this->session->userdata('usertypeID'),
												'senderID' => $this->session->userdata('loginuserID'),
												'sendername' => $this->session->userdata('username')
											);
											if(strlen($configuredMessage) > 0)
												$this->mailandsms_m->insert_mailandsms($array);
										}
										redirect(base_url('mailandsms/review'));
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								} else {
									/* Single school Year Student */
									$countusers = '';
									$retval = 1;
									$retmess = '';
									$message = $this->input->post('sms_message');
									$singleSchoolYear = $this->input->post('sms_schoolyear');
									$active = $this->input->post('sms_active');
									$query = ['srschoolyearID' => $singleSchoolYear, 'srschoolID' => $schoolID];
									if((int)$active) {
										$query['active'] = $active;
									}
									$singleSchoolYearStudents = $this->studentrelation_m->general_get_order_by_student($query, TRUE);
									if(customCompute($singleSchoolYearStudents)) {
										foreach ($singleSchoolYearStudents as $key => $singleSchoolYearStudent) {
											$configuredMessage = $this->userConfigSMS($message, $singleSchoolYearStudent, $usertypeID, $getway, $schoolID, $schoolyearID);
											if ($use_parent_contact) {
												$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $singleSchoolYearStudent->parentID, 'schoolID' => $schoolID));
												$recipient = $singleparent->phone;
											}
											else {
												$recipient = $singleSchoolYearStudent->phone;
											}
											$array = array(
												'userID' => $singleSchoolYearStudent->srstudentID,
												'usertypeID' => $usertypeID,
												'schoolID' => $schoolID,
												'users' => $singleSchoolYearStudent->srname,
												'recipient' => $recipient,
												'type' => ucfirst($this->input->post('type')),
												'sms_gateway' => $getway,
												'message' => $configuredMessage,
												'year' => date('Y'),
												'senderusertypeID' => $this->session->userdata('usertypeID'),
												'senderID' => $this->session->userdata('loginuserID'),
												'sendername' => $this->session->userdata('username')
											);
											if(strlen($configuredMessage) > 0)
												$this->mailandsms_m->insert_mailandsms($array);
										}
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								}
							} else {
								/* Single Class Student */
								$countusers = '';
								$retval = 1;
								$retmess = '';

								$message = $this->input->post('sms_message');
								$singleClass = $this->input->post('sms_class');
                $singleSection = $this->input->post('sms_section');
								$query = array('srclassesID' => $singleClass, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID);
								$active = $this->input->post('sms_active');
								if((int)$active) {
									$query['active'] = $active;
								}
                if((int)$singleSection){
										$query['srsectionID'] = $singleSection;
                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student($query, TRUE);
                }else {
                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student($query, TRUE);
                }
								if(customCompute($singleClassStudents)) {
									$countusers = '';
									foreach ($singleClassStudents as $key => $singleClassStudent) {
										$configuredMessage = $this->userConfigSMS($message, $singleClassStudent, $usertypeID, $getway, $schoolID, $schoolyearID);
										if ($use_parent_contact) {
											$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $singleClassStudent->parentID, 'schoolID' => $schoolID));
											$recipient = $singleparent->phone;
										}
										else {
											$recipient = $singleClassStudent->phone;
										}
										$array = array(
											'userID' => $singleClassStudent->srstudentID,
											'usertypeID' => $usertypeID,
											'schoolID' => $schoolID,
											'users' => $singleClassStudent->srname,
											'recipient' => $recipient,
											'type' => ucfirst($this->input->post('type')),
											'sms_gateway' => $getway,
											'message' => $configuredMessage,
											'year' => date('Y'),
											'senderusertypeID' => $this->session->userdata('usertypeID'),
											'senderID' => $this->session->userdata('loginuserID'),
											'sendername' => $this->session->userdata('username')
										);
										if(strlen($configuredMessage) > 0)
											$this->mailandsms_m->insert_mailandsms($array);
									}
									redirect(base_url('mailandsms/review'));
								} else {
									$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
									redirect(base_url('mailandsms/add'));
								}
							}
						} else {
							/* Single Student */
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$singlestudent = $this->studentrelation_m->general_get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID), TRUE);
							if(customCompute($singlestudent)) {
								$configuredMessage = $this->userConfigSMS($message, $singlestudent, $usertypeID, $getway, $schoolID, $schoolyearID);
								if ($use_parent_contact) {
									$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $singlestudent->parentID, 'schoolID' => $schoolID));
									$recipient = $singleparent->phone;
								}
								else {
									$recipient = $singlestudent->phone;
								}
								$array = array(
									'userID' => $studentID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singlestudent->srname,
									'recipient' => $recipient,
									'type' => ucfirst($this->input->post('type')),
									'sms_gateway' => $getway,
									'message' => $configuredMessage,
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								if(strlen($configuredMessage) > 0)
									$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 4) { /* FOR PARENTS */
						$parentsID = $this->input->post('sms_users');
						$schoolyearID = $this->session->userdata("defaultschoolyearID");
						if($parentsID == 'select') {
							$countusers = '';
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$active = $this->input->post('sms_active');
							$query = array('schoolID' => $schoolID);
							if((int)$active) {
								$query['active'] = $active;
							}
							$multiparents = $this->parents_m->get_order_by_parents($query);
							if(customCompute($multiparents)) {
								foreach ($multiparents as $key => $multiparent) {
									$configuredMessage = $this->userConfigSMS($message, $multiparent, $usertypeID, $getway, $schoolID, $schoolyearID);
									$phones = explode("/", str_replace(' ', '', $multiparent->phone));
									foreach($phones as $phone) {
										$array = array(
											'userID' => $multiparent->parentsID,
											'usertypeID' => $usertypeID,
											'schoolID' => $schoolID,
											'users' => $multiparent->name,
											'recipient' => $phone,
											'type' => ucfirst($this->input->post('type')),
											'sms_gateway' => $getway,
											'message' => $configuredMessage,
											'year' => date('Y'),
											'senderusertypeID' => $this->session->userdata('usertypeID'),
											'senderID' => $this->session->userdata('loginuserID'),
											'sendername' => $this->session->userdata('username')
										);

										if(strlen($configuredMessage) > 0)
											$this->mailandsms_m->insert_mailandsms($array);
									}
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$singleparent = $this->parents_m->get_single_parents(array('parentsID' => $parentsID, 'schoolID' => $schoolID));
							if(customCompute($singleparent)) {
								$configuredMessage = $this->userConfigSMS($message, $singleparent, $usertypeID, $getway, $schoolID, $schoolyearID);
								$array = array(
									'userID' => $parentsID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singleparent->name,
									'recipient' => $singleparent->phone,
									'sms_gateway' => $getway,
									'type' => ucfirst($this->input->post('type')),
									'message' => $configuredMessage,
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								if(strlen($configuredMessage) > 0)
									$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} else { /* FOR ALL USERS */
						$userID = $this->input->post('sms_users');
						if($userID == 'select') {
							$countusers = '';
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$active = $this->input->post('sms_active');
							$query = array('usertypeID' => $usertypeID, 'schoolID' => $schoolID);
							if((int)$active) {
								$query['active'] = $active;
							}
							$multiusers = $this->user_m->get_order_by_user($query);
							if(customCompute($multiusers)) {
								foreach ($multiusers as $key => $multiuser) {
									$configuredMessage = $this->userConfigSMS($message, $multiuser, $usertypeID, $getway, $schoolID);
									$array = array(
										'userID' => $multiuser->userID,
										'usertypeID' => $usertypeID,
										'schoolID' => $schoolID,
										'users' => $multiuser->name,
										'recipient' => $multiuser->phone,
										'type' => ucfirst($this->input->post('type')),
										'sms_gateway' => $getway,
										'message' => $configuredMessage,
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID'),
										'sendername' => $this->session->userdata('username')
									);
									if(strlen($configuredMessage) > 0)
										$this->mailandsms_m->insert_mailandsms($array);
								}
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$singleuser = $this->user_m->get_single_user(array('userID' => $userID, 'schoolID' => $schoolID));
							if(customCompute($singleuser)) {
								$configuredMessage = $this->userConfigSMS($message, $singleuser, $usertypeID, $getway, $schoolID);
								$array = array(
									'userID' => $userID,
									'usertypeID' => $usertypeID,
									'schoolID' => $schoolID,
									'users' => $singleuser->name,
									'recipient' => $singleuser->phone,
									'type' => ucfirst($this->input->post('type')),
									'sms_gateway' => $getway,
									'message' => $configuredMessage,
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID'),
									'sendername' => $this->session->userdata('username')
								);
								if(strlen($configuredMessage) > 0)
									$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/review'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					}
				}
			} elseif($this->input->post('type') == "otheremail") {
				$rules = $this->rules_otheremail();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {

					$this->data['emailUserID'] = 0;
					$this->data['emailTemplateID'] = 0;
					$this->data['allStudents'] = [];
					$this->data['smsUserID'] = 0;
					$this->data['smsTemplateID'] = 0;

					$this->data["email"] = 0;
					$this->data["sms"] = 0;
					$this->data["otheremail"] = 1;
					$this->data["othersms"] = 0;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$email   = $this->input->post('otheremail_email');
					$subject = $this->input->post('otheremail_subject');
					$message = $this->input->post('otheremail_message');
					//$result  = $this->inilabs->sendMailSystem($email, $subject, $message);
					//if($result) {
						$array = array(
							'usertypeID' => '0',
							'schoolID' => $schoolID,
							'users' => $email,
							'type' => ucfirst($this->lang->line('mailandsms_otheremail')),
							'message' => $this->input->post('otheremail_message'),
							'year' => date('Y'),
							'senderusertypeID' => $this->session->userdata('usertypeID'),
							'senderID' => $this->session->userdata('loginuserID'),
							'sendername' => $this->session->userdata('username')
						);
						$this->mailandsms_m->insert_mailandsms($array);
						$this->session->set_flashdata('success', $this->lang->line('mail_success'));
						redirect(base_url('mailandsms/review'));
					/*} else {
						$this->session->set_flashdata('error', $this->lang->line('mail_error'));
						redirect(base_url("mailandsms/add"));
					}*/
				}
			} elseif($this->input->post('type') == "othersms") {
				$rules = $this->rules_othersms();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {

					$this->data['emailUserID'] = 0;
					$this->data['emailTemplateID'] = 0;
					$this->data['allStudents'] = [];
					$this->data['smsUserID'] = 0;
					$this->data['smsTemplateID'] = 0;

					$this->data["email"] = 0;
					$this->data["sms"] = 0;
					$this->data["otheremail"] = 0;
					$this->data["othersms"] = 1;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$to = $this->input->post('othersms_phone');
					$getway = $this->input->post('sms_getway');
					$message = $this->input->post('othersms_message');

					//$result = $this->allgetway_send_message($getway, $to, $message);
					//if($result['check']) {
						$array = array(
							'usertypeID' => '0',
							'schoolID' => $schoolID,
							'users' => $this->input->post('othersms_phone'),
							'type' => ucfirst($this->lang->line('mailandsms_othersms')),
							'sms_gateway' => $getway,
							'message' => $this->input->post('othersms_message'),
							'year' => date('Y'),
							'senderusertypeID' => $this->session->userdata('usertypeID'),
							'senderID' => $this->session->userdata('loginuserID'),
							'sendername' => $this->session->userdata('username')
						);
						$this->mailandsms_m->insert_mailandsms($array);
						redirect(base_url('mailandsms/review'));
					/*} else {
						$retmess = isset($result['message']) ? $result['message'] : $this->lang->line('mailandsms_error');
						$this->session->set_flashdata('error', $retmess);
						redirect(base_url("mailandsms/add"));
					}*/
				}
			} else {
				redirect('mainandsms/add');
			}
		} else {
			$this->data['emailUserID'] = 0;
			$this->data['emailTemplateID'] = 0;

			$this->data['smsUserID'] = 0;
			$this->data['smsTemplateID'] = 0;

			$this->data["email"] = 1;
			$this->data["sms"] = 0;
			$this->data["otheremail"] = 0;
			$this->data["othersms"] = 0;
			$this->data['submittype'] = 'none';

			$this->data['allStudents'] = array();
			$this->data["subview"] = "mailandsms/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function test() {
		if($_POST) {
			$ids = json_decode($this->input->post('ids'));
			if (customCompute($ids)) {
				$result = array();
				$schoolID = $this->session->userdata("schoolID");
				foreach ($ids as $id) {
					$mailandsms = $this->mailandsms_m->get_single_mailandsms(array("mailandsmsID" => $id, 'schoolID' => $schoolID));
					$usertypeID = $mailandsms->usertypeID;
					$userID     = $mailandsms->userID;
					$message    = $mailandsms->message;
					$user       = new stdClass();

					if (customCompute($mailandsms)) {
						if ($usertypeID == 1) { /* FOR ADMIN */
							$user = $this->systemadmin_m->get_single_systemadmin(array("systemadminID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID == 2) { /* FOR TEACHER */
							$user = $this->teacher_m->general_get_single_teacher(array("teacherID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID == 3) { /* FOR STUDENT */
							$user = $this->student_m->get_single_student(array("studentID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID == 4) { /* FOR PARENTS */
							$user = $this->parents_m->get_single_parents(array("parentsID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID != 0) { /* ALL USERS */
							$user = $this->user_m->get_single_user(array("userID" => $userID, 'schoolID' => $schoolID));
						}

						if ($mailandsms->type == "Email") {
							if($user->email) {
								$subject = $mailandsms->subject;
								$email = $this->data['siteinfos']->email;
								if($mailandsms->attachment) {
									$data = [];
									$schoolyearID = $this->session->userdata("defaultschoolyearID");
									$students = $this->studentrelation_m->general_get_order_by_student(array('parentID' => $user->parentsID, 'srschoolyearID' => $schoolyearID, 'active' => 1, 'srschoolID' => $schoolID));
									foreach($students as $student) {
										if($mailandsms->attachment == 'student_statement') {
											$data[] = $this->studentStatement($student->srstudentID);
											$viewpath = 'student_statement/print_preview';
										} elseif($attachment == 'exam_results') {
											$data[] = $this->examResults($student->srstudentID);
											$viewpath = 'report/terminal/SingleTerminalReportPDF';
										}
									}

									if(customCompute($data))
										$status = $this->reportSendToMail2('terminalreport.css', $data, $viewpath, $email, $subject,$message);

									if ($status['check']) {
										$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
										$retArray['status']  = TRUE;
										$result[] = $retArray;
									} else {
										$retArray['status']  = FALSE;
										$retArray['error'] = $this->lang->line('mail_error');
										$result[] = $retArray;
									}
								} else {
									$emailsetting = $this->emailsetting_m->get_emailsetting();
									$this->email->set_mailtype("html");
									if(customCompute($emailsetting)) {
										if($emailsetting->email_engine == 'smtp') {
											$config = array(
											    'protocol'  => 'smtp',
											    'smtp_host' => $emailsetting->smtp_server,
											    'smtp_port' => $emailsetting->smtp_port,
											    'smtp_user' => $emailsetting->smtp_username,
											    'smtp_pass' => $emailsetting->smtp_password,
													'smtp_crypto' => $emailsetting->smtp_security,
											    'mailtype'  => 'html',
											    'charset'   => 'utf-8'
											);
											$this->email->initialize($config);
											$this->email->set_newline("\r\n");
										}

										$this->email->to($email);
										$this->email->from($emailsetting->smtp_username, $this->data['siteinfos']->sname);
										$this->email->subject($subject);
										$this->email->message($message);
										if($this->email->send()) {
											$this->session->set_flashdata('success', $this->lang->line('mail_success'));
											$retArray['status']  = TRUE;
											$result[] = $retArray;
										} else {
											$retArray['status']  = FALSE;
											$retArray['error'] = $this->lang->line('mail_error');
											$result[] = $retArray;
										}
									}
								}
							}
						} elseif ($mailandsms->type == "Other Email") {
							$subject = $mailandsms->subject;
							$email = $this->data['siteinfos']->email;
							$emailsetting = $this->emailsetting_m->get_emailsetting();
							$this->email->set_mailtype("html");
							if(customCompute($emailsetting)) {
								if($emailsetting->email_engine == 'smtp') {
									$config = array(
											'protocol'  => 'smtp',
											'smtp_host' => $emailsetting->smtp_server,
											'smtp_port' => $emailsetting->smtp_port,
											'smtp_user' => $emailsetting->smtp_username,
											'smtp_pass' => $emailsetting->smtp_password,
											'smtp_crypto' => $emailsetting->smtp_security,
											'mailtype'  => 'html',
											'charset'   => 'utf-8'
									);
									$this->email->initialize($config);
									$this->email->set_newline("\r\n");
								}

								$this->email->to($email);
								$this->email->from($emailsetting->smtp_username, $this->data['siteinfos']->sname);
								$this->email->subject($subject);
								$this->email->message($message);
								if($this->email->send()) {
									$this->session->set_flashdata('success', $this->lang->line('mail_success'));
									$retArray['status']  = TRUE;
									$result[] = $retArray;
								} else {
									$retArray['status']  = FALSE;
									$retArray['error'] = $this->lang->line('mail_error');
									$result[] = $retArray;
								}
							}
						} elseif ($mailandsms->type == "Sms") {
							if($mailandsms->recipient) {
								$phone = $this->data['siteinfos']->phone;
								$send = $this->allgetway_send_message($mailandsms->sms_gateway, $phone, $message, $mailandsms->mailandsmsID);
								if ($send['check']) {
									$this->session->set_flashdata('success', $this->lang->line('sms_success'));
									$retArray['status']  = TRUE;
									$result[] = $retArray;
								} else {
									$retArray['status']  = FALSE;
									$retArray['error']   = $send['message'];
									$result[] = $retArray;
								}
							} else {
								$retArray['status']  = FALSE;
								$retArray['error']   = "No phone number";
								$result[] = $retArray;
							}
						} elseif ($mailandsms->type == "Other SMS") {
							$phone = $this->data['siteinfos']->phone;
							$send = $this->allgetway_send_message($mailandsms->sms_gateway, $phone, $message, $mailandsms->mailandsmsID);
							if ($send['check']) {
								$this->session->set_flashdata('success', $this->lang->line('sms_success'));
								$retArray['status']  = TRUE;
								$result[] = $retArray;
							} else {
								$retArray['status']  = FALSE;
								$retArray['error'] = $send['message'];
								$result[] = $retArray;
							}
						}
					}
				}
				echo json_encode($result);
				exit;
			}
		}
	}

	public function send() {
		if($_POST) {
			$ids = json_decode($this->input->post('ids'));
			if (customCompute($ids)) {
				$result = array();
				$schoolID = $this->session->userdata("schoolID");
				foreach ($ids as $id) {
					$mailandsms = $this->mailandsms_m->get_single_mailandsms(array("mailandsmsID" => $id, 'schoolID' => $schoolID));
					$usertypeID = $mailandsms->usertypeID;
					$userID     = $mailandsms->userID;
					$message    = $mailandsms->message;
					$user       = new stdClass();

					if (customCompute($mailandsms)) {
						if ($usertypeID == 1) { /* FOR ADMIN */
							$user = $this->systemadmin_m->get_single_systemadmin(array("systemadminID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID == 2) { /* FOR TEACHER */
							$user = $this->teacher_m->general_get_single_teacher(array("teacherID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID == 3) { /* FOR STUDENT */
							$user = $this->student_m->get_single_student(array("studentID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID == 4) { /* FOR PARENTS */
							$user = $this->parents_m->get_single_parents(array("parentsID" => $userID, 'schoolID' => $schoolID));
						} elseif($usertypeID != 0) { /* ALL USERS */
							$user = $this->user_m->get_single_user(array("userID" => $userID, 'schoolID' => $schoolID));
						}

						if ($mailandsms->type == "Email") {
							if (filter_var($mailandsms->recipient, FILTER_VALIDATE_EMAIL)) {
								$subject = $mailandsms->subject;
								$email = $mailandsms->recipient;
								if($mailandsms->attachment) {
									$data = [];
									$schoolyearID = $this->session->userdata("defaultschoolyearID");
									if($usertypeID == 3)
										$students = $this->studentrelation_m->general_get_order_by_student(array('studentID' => $user->studentID, 'srschoolyearID' => $schoolyearID, 'active' => 1, 'srschoolID' => $schoolID));
									elseif($usertypeID == 4)
										$students = $this->studentrelation_m->general_get_order_by_student(array('parentID' => $user->parentsID, 'srschoolyearID' => $schoolyearID, 'active' => 1, 'srschoolID' => $schoolID));
									foreach($students as $student) {
										if($mailandsms->attachment == 'student_statement') {
											$data[] = $this->studentStatement($student->srstudentID);
											$viewpath = 'student_statement/print_preview';
										} elseif($attachment == 'exam_results') {
											$data[] = $this->examResults($student->srstudentID);
											$viewpath = 'report/terminal/SingleTerminalReportPDF';
										}
									}

									if(customCompute($data))
										$status = $this->reportSendToMail2('terminalreport.css', $data, $viewpath, $email, $subject,$message);

									if ($status['check']) {
										$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
										$retArray['status']  = TRUE;
										$retArray['id'] = $mailandsms->mailandsmsID;
										$result[] = $retArray;
									} else {
										$retArray['status']  = FALSE;
										$retArray['error'] = $this->lang->line('mail_error');
										$result[] = $retArray;
									}
								} else {
									$emailsetting = $this->emailsetting_m->get_emailsetting();
									$this->email->set_mailtype("html");
									if(customCompute($emailsetting)) {
										if($emailsetting->email_engine == 'smtp') {
											$config = array(
											    'protocol'  => 'smtp',
											    'smtp_host' => $emailsetting->smtp_server,
											    'smtp_port' => $emailsetting->smtp_port,
											    'smtp_user' => $emailsetting->smtp_username,
											    'smtp_pass' => $emailsetting->smtp_password,
													'smtp_crypto' => $emailsetting->smtp_security,
											    'mailtype'  => 'html',
											    'charset'   => 'utf-8'
											);
											$this->email->initialize($config);
											$this->email->set_newline("\r\n");
										}

										$this->email->to($email);
										$this->email->from($emailsetting->smtp_username, $this->data['siteinfos']->sname);
										$this->email->subject($subject);
										$this->email->message($message);
										if($this->email->send()) {
											$this->session->set_flashdata('success', $this->lang->line('mail_success'));
											$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
											$retArray['status']  = TRUE;
											$retArray['id'] = $mailandsms->mailandsmsID;
											$result[] = $retArray;
										} else {
											$retArray['status']  = FALSE;
											$retArray['error'] = $this->lang->line('mail_error');
											$result[] = $retArray;
										}
									}
								}
							}
						} elseif ($mailandsms->type == "Other Email") {
							$subject = $mailandsms->subject;
							$email = $mailandsms->users;
							$emailsetting = $this->emailsetting_m->get_emailsetting();
							$this->email->set_mailtype("html");
							if(customCompute($emailsetting)) {
								if($emailsetting->email_engine == 'smtp') {
									$config = array(
											'protocol'  => 'smtp',
											'smtp_host' => $emailsetting->smtp_server,
											'smtp_port' => $emailsetting->smtp_port,
											'smtp_user' => $emailsetting->smtp_username,
											'smtp_pass' => $emailsetting->smtp_password,
											'smtp_crypto' => $emailsetting->smtp_security,
											'mailtype'  => 'html',
											'charset'   => 'utf-8'
									);
									$this->email->initialize($config);
									$this->email->set_newline("\r\n");
								}

								$this->email->to($email);
								$this->email->from($emailsetting->smtp_username, $this->data['siteinfos']->sname);
								$this->email->subject($subject);
								$this->email->message($message);
								if($this->email->send()) {
									$this->session->set_flashdata('success', $this->lang->line('mail_success'));
									$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
									$retArray['status']  = TRUE;
									$retArray['id'] = $mailandsms->mailandsmsID;
									$result[] = $retArray;
								} else {
									$retArray['status']  = FALSE;
									$retArray['error'] = $this->lang->line('mail_error');
									$result[] = $retArray;
								}
							}
						} elseif ($mailandsms->type == "Sms") {
							if($mailandsms->recipient) {
								$send = $this->allgetway_send_message($mailandsms->sms_gateway, $mailandsms->recipient, $message, $mailandsms->mailandsmsID);
								if ($send['check']) {
									$this->session->set_flashdata('success', $this->lang->line('sms_success'));
									$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
									$retArray['status']  = TRUE;
									$retArray['id'] = $mailandsms->mailandsmsID;
									$result[] = $retArray;
								} else {
									$retArray['status']  = FALSE;
									$retArray['error']   = $send['message'];
									$result[] = $retArray;
								}
							} else {
								$retArray['status']  = FALSE;
								$retArray['error']   = "No phone number";
								$result[] = $retArray;
							}
						} elseif ($mailandsms->type == "Other SMS") {
							$phone = $mailandsms->users;
							$send = $this->allgetway_send_message($mailandsms->sms_gateway, $phone, $message, $mailandsms->mailandsmsID);
							if ($send['check']) {
								$this->session->set_flashdata('success', $this->lang->line('sms_success'));
								$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
								$retArray['status']  = TRUE;
								$retArray['id'] = $mailandsms->mailandsmsID;
								$result[] = $retArray;
							} else {
								$retArray['status']  = FALSE;
								$retArray['error'] = $send['message'];
								$result[] = $retArray;
							}
						}
					}
				}
				echo json_encode($result);
				exit;
			}
		}
	}

	public function resend() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$schoolID   = $this->session->userdata("schoolID");
			$mailandsms = $this->mailandsms_m->get_single_mailandsms(array("mailandsmsID" => $id, 'schoolID' => $schoolID));
			if (customCompute($mailandsms)) {
				$message = $mailandsms->message;
				$usertypeID = $mailandsms->usertypeID;
				$userID = $mailandsms->userID;

				if ($usertypeID == 1) { /* FOR ADMIN */
					$user = $this->systemadmin_m->get_single_systemadmin(array("systemadminID" => $userID, 'schoolID' => $schoolID));
				} elseif($usertypeID == 2) { /* FOR TEACHER */
					$user = $this->teacher_m->general_get_single_teacher(array("teacherID" => $userID, 'schoolID' => $schoolID));
				} elseif($usertypeID == 3) { /* FOR STUDENT */
					$user = $this->student_m->get_single_student(array("studentID" => $userI, 'schoolID' => $schoolIDD));
				} elseif($usertypeID == 4) { /* FOR PARENTS */
					$user = $this->parents_m->get_single_parents(array("parentsID" => $userID, 'schoolID' => $schoolID));
				} else { /* ALL USERS */
					$user = $this->user_m->get_single_user(array("userID" => $userID, 'schoolID' => $schoolID));
				}

				if ($mailandsms->type == "Email") {
					if($user->email) {
						$subject = $mailandsms->subject;
						$email = $user->email;
						if($mailandsms->attachment) {
							$students = $this->studentrelation_m->general_get_order_by_student(array('parentID' => $user->parentsID, 'active' => 1, 'srschoolID' => $schoolID));
							foreach($students as $student) {
								if($mailandsms->attachment == 'student_statement') {
									$data = $this->studentStatement($student->srstudentID);
									$status = $this->reportSendToMail('invoicemodule.css', $data, 'student_statement/print_preview', $email, $subject, $message);
									if ($status['check']) {
										$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
									}
									redirect(base_url('mailandsms/index'));
								} elseif($attachment == 'exam_results') {
									$data = $this->examResults($student->srstudentID);
									$status = $this->reportSendToMail('terminalreport.css', $this->data, 'report/terminal/SingleTerminalReportPDF',$email, $subject,$message);
									if ($status['check']) {
										$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
									}
									redirect(base_url('mailandsms/index'));
								}
							}
						} else {
							$emailsetting = $this->emailsetting_m->get_emailsetting();
							$this->email->set_mailtype("html");
							if(customCompute($emailsetting)) {
								if($emailsetting->email_engine == 'smtp') {
									$config = array(
											'protocol'  => 'smtp',
											'smtp_host' => $emailsetting->smtp_server,
											'smtp_port' => $emailsetting->smtp_port,
											'smtp_user' => $emailsetting->smtp_username,
											'smtp_pass' => $emailsetting->smtp_password,
											'smtp_crypto' => $emailsetting->smtp_security,
											'mailtype'  => 'html',
											'charset'   => 'utf-8'
									);
									$this->email->initialize($config);
									$this->email->set_newline("\r\n");
								}

								$this->email->to($email);
								$this->email->from($emailsetting->smtp_username, $this->data['siteinfos']->sname);
								$this->email->subject($subject);
								$this->email->message($message);
								if($this->email->send()) {
									$this->session->set_flashdata('success', $this->lang->line('mail_success'));
									$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
								} else {
									$this->session->set_flashdata('error', $this->lang->line('mail_error'));
								}
								redirect(base_url('mailandsms/index'));
							}
						}
					}
				} elseif ($mailandsms->type == "Other Email") {
					$subject = $mailandsms->subject;
					$email = $mailandsms->users;
					$emailsetting = $this->emailsetting_m->get_emailsetting();
					$this->email->set_mailtype("html");
					if(customCompute($emailsetting)) {
						if($emailsetting->email_engine == 'smtp') {
							$config = array(
									'protocol'  => 'smtp',
									'smtp_host' => $emailsetting->smtp_server,
									'smtp_port' => $emailsetting->smtp_port,
									'smtp_user' => $emailsetting->smtp_username,
									'smtp_pass' => $emailsetting->smtp_password,
									'smtp_crypto' => $emailsetting->smtp_security,
									'mailtype'  => 'html',
									'charset'   => 'utf-8'
							);
							$this->email->initialize($config);
							$this->email->set_newline("\r\n");
						}

						$this->email->to($email);
						$this->email->from($emailsetting->smtp_username, $this->data['siteinfos']->sname);
						$this->email->subject($subject);
						$this->email->message($message);
						if($this->email->send()) {
							$this->session->set_flashdata('success', $this->lang->line('mail_success'));
							$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
						} else {
							$this->session->set_flashdata('error', $this->lang->line('mail_error'));
						}
						redirect(base_url('mailandsms/index'));
					}
				} elseif ($mailandsms->type == "Sms") {
					if($user->phone) {
						$send = $this->allgetway_send_message($mailandsms->sms_gateway, $user->phone, $message, $mailandsms->mailandsmsID);
						if ($send['check']) {
							$this->session->set_flashdata('success', $this->lang->line('sms_success'));
							$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
						} else {
							$this->session->set_flashdata('error', $send['message']);
						}
					} else {
						$this->session->set_flashdata('error', "No phone number");
					}
					redirect(base_url('mailandsms/index'));
				} elseif ($mailandsms->type == "Other SMS") {
					$phone = $mailandsms->users;
					$send = $this->allgetway_send_message($mailandsms->sms_gateway, $phone, $message, $mailandsms->mailandsmsID);
					if ($send['check']) {
						$this->session->set_flashdata('success', $this->lang->line('sms_success'));
						$this->mailandsms_m->update_mailandsms(array("reviewed" => 1, "sent_date" => date("Y-m-d H:i:s")), $mailandsms->mailandsmsID);
					} else {
						$this->session->set_flashdata('error', $send['message']);
					}
					redirect(base_url('mailandsms/index'));
				}
			}
		}
	}

	public function discard() {
		if($_POST) {
			$ids = json_decode($this->input->post('ids'));
			if (customCompute($ids)) {
				$schoolID = $this->session->userdata("schoolID");
				foreach ($ids as $id) {
					$mailandsms = $this->mailandsms_m->get_single_mailandsms(array('mailandsmsID' => $id, 'schoolID' => $schoolID));
					if(customCompute($mailandsms))
						$this->mailandsms_m->delete_mailandsms($id);
				}
			}
			$this->session->set_flashdata('success', $this->lang->line('discard_success'));
			$retArray['status']  = TRUE;
			echo json_encode($retArray);
			exit;
		}
	}

	private function delivery_report($mailandsms) {
		$response = $this->get_delivery_report($mailandsms);
		return $response;
	}

	private function userConfigEmail($message, $user, $usertypeID, $schoolID, $schoolyearID = 1, $attachment = NULL) {
		if($user && $usertypeID) {
			$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => $usertypeID));

			if($usertypeID == 2) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 2));
			} elseif($usertypeID == 3) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 3));
			} elseif($usertypeID == 4) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 4));
			} else {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 1));
			}

			$message = $this->tagConvertor($userTags, $user, $message, 'email', $schoolyearID, $schoolID);

			return $message;
		}
	}

	private function userConfigSMS($message, $user, $usertypeID, $getway, $schoolID, $schoolyearID = 1) {
		if($user && $usertypeID) {
			$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => $usertypeID));

			if($usertypeID == 2) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 2));
			} elseif($usertypeID == 3) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 3));
			} elseif($usertypeID == 4) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 4));
			} else {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 1));
			}

			$message = $this->tagConvertor($userTags, $user, $message, 'SMS', $schoolyearID, $schoolID);
			return $message;
		}
	}

	private function tagConvertor($userTags, $user, $message, $sendType, $schoolyearID, $schoolID) {
		if(customCompute($userTags)) {
			foreach ($userTags as $key => $userTag) {
				if($userTag->tagname == '[name]' && strpos($message, '[name]') !== false) {
					if($user->name) {
						$message = str_replace('[name]', $user->name, $message);
					} else {
						$message = str_replace('[name]', ' ', $message);
					}
				} elseif($userTag->tagname == '[designation]' && strpos($message, '[designation]') !== false) {
					if($user->designation) {
						$message = str_replace('[designation]', $user->designation, $message);
					} else {
						$message = str_replace('[designation]', ' ', $message);
					}
				} elseif($userTag->tagname == '[dob]' && strpos($message, '[dob]') !== false) {
					if($user->dob) {
						$dob =  date("d M Y", strtotime($user->dob));
						$message = str_replace('[dob]', $dob, $message);
					} else {
						$message = str_replace('[dob]', ' ', $message);
					}
				} elseif($userTag->tagname == '[gender]' && strpos($message, '[gender]') !== false) {
					if($user->sex) {
						$message = str_replace('[gender]', $user->sex, $message);
					} else {
						$message = str_replace('[gender]', ' ', $message);
					}
				} elseif($userTag->tagname == '[religion]' && strpos($message, '[religion]') !== false) {
					if($user->religion) {
						$message = str_replace('[religion]', $user->religion, $message);
					} else {
						$message = str_replace('[religion]', ' ', $message);
					}
				} elseif($userTag->tagname == '[email]' && strpos($message, '[email]') !== false) {
					if($user->email) {
						$message = str_replace('[email]', $user->email, $message);
					} else {
						$message = str_replace('[email]', ' ', $message);
					}
				} elseif($userTag->tagname == '[phone]' && strpos($message, '[phone]') !== false) {
					if($user->phone) {
						$message = str_replace('[phone]', $user->phone, $message);
					} else {
						$message = str_replace('[phone]', ' ', $message);
					}
				} elseif($userTag->tagname == '[address]' && strpos($message, '[address]') !== false) {
					if($user->address) {
						$message = str_replace('[address]', $user->address, $message);
					} else {
						$message = str_replace('[address]', ' ', $message);
					}
				} elseif($userTag->tagname == '[jod]' && strpos($message, '[jod]') !== false) {
					if($user->jod) {
						$jod =  date("d M Y", strtotime($user->jod));
						$message = str_replace('[jod]', $jod, $message);
					} else {
						$message = str_replace('[jod]', ' ', $message);
					}
				} elseif($userTag->tagname == '[username]' && strpos($message, '[username]') !== false) {
					if($user->username) {
						$message = str_replace('[username]', $user->username, $message);
					} else {
						$message = str_replace('[username]', ' ', $message);
					}
				} elseif($userTag->tagname == "[father's_name]" && strpos($message, "[father's_name]") !== false) {
					if($user->father_name) {
						$message = str_replace("[father's_name]", $user->father_name, $message);
					} else {
						$message = str_replace("[father's_name]", ' ', $message);
					}
				} elseif($userTag->tagname == "[mother's_name]" && strpos($message, "[mother's_name]") !== false) {
					if($user->mother_name) {
						$message = str_replace("[mother's_name]", $user->mother_name, $message);
					} else {
						$message = str_replace("[mother's_name]", ' ', $message);
					}
				} elseif($userTag->tagname == "[father's_profession]" && strpos($message, "[father's_profession]") !== false) {
					if($user->father_profession) {
						$message = str_replace("[father's_profession]", $user->father_profession, $message);
					} else {
						$message = str_replace("[father's_profession]", ' ', $message);
					}
				} elseif($userTag->tagname == "[mother's_profession]" && strpos($message, "[mother's_profession]") !== false) {
					if($user->mother_profession) {
						$message = str_replace("[mother's_profession]", $user->mother_profession, $message);
					} else {
						$message = str_replace("[mother's_profession]", ' ', $message);
					}
				} elseif($userTag->tagname == '[class]' && strpos($message, '[class]') !== false) {
					$classes = $this->classes_m->general_get_classes($user->srclassesID);
					if(customCompute($classes)) {
						$message = str_replace('[class]', $classes->classes, $message);
					} else {
						$message = str_replace('[class]', ' ', $message);
					}
				} elseif($userTag->tagname == '[roll]' && strpos($message, '[roll]') !== false) {
					if($user->srroll) {
						$message = str_replace("[roll]", $user->srroll, $message);
					} else {
						$message = str_replace("[roll]", ' ', $message);
					}
				} elseif($userTag->tagname == '[country]' && strpos($message, '[country]') !== false) {
					if($user->country) {
						if(isset($this->data['allcountry'][$user->country])) {
							$message = str_replace("[country]", $this->data['allcountry'][$user->country], $message);
						} else {
							$message = str_replace("[country]", ' ', $message);
						}
					} else {
						$message = str_replace("[country]", ' ', $message);
					}
				} elseif($userTag->tagname == '[state]' && strpos($message, '[state]') !== false) {
					if($user->state) {
						$message = str_replace("[state]", $user->state, $message);
					} else {
						$message = str_replace("[state]", ' ', $message);
					}
				} elseif($userTag->tagname == '[register_no]' && strpos($message, '[register_no]') !== false) {
					if($user->srregisterNO) {
						$message = str_replace("[register_no]", $user->srregisterNO, $message);
					} else {
						$message = str_replace("[register_no]", ' ', $message);
					}
				} elseif($userTag->tagname == '[section]' && strpos($message, '[section]') !== false) {
					if($user->srsectionID) {
						$section = $this->section_m->general_get_section($user->srsectionID);
						if(customCompute($section)) {
							$message = str_replace('[section]', $section->section, $message);
						} else {
							$message = str_replace('[section]',' ', $message);
						}
					} else {
						$message = str_replace("[section]", ' ', $message);
					}
				} elseif($userTag->tagname == '[blood_group]' && strpos($message, '[blood_group]') !== false) {
					if($user->bloodgroup && $user->bloodgroup != '0') {
						$message = str_replace("[blood_group]", $user->bloodgroup, $message);
					} else {
						$message = str_replace("[blood_group]", ' ', $message);
					}
				} elseif($userTag->tagname == '[group]' && strpos($message, '[group]') !== false) {
					if($user->srstudentgroupID && $user->srstudentgroupID != 0) {
						$group = $this->studentgroup_m->get_studentgroup($user->srstudentgroupID);
						if(customCompute($group)) {
							$message = str_replace('[group]', $group->group, $message);
						} else {
							$message = str_replace('[group]',' ', $message);
						}
					} else {
						$message = str_replace('[group]',' ', $message);
					}
				} elseif($userTag->tagname == '[optional_subject]' && strpos($message, '[optional_subject]') !== false) {
					if($user->sroptionalsubjectID && $user->sroptionalsubjectID != 0) {
						$subject = $this->subject_m->general_get_single_subject(array('subjectID' => $user->sroptionalsubjectID));
						if(customCompute($subject)) {
							$message = str_replace('[optional_subject]', $subject->subject, $message);
						} else {
							$message = str_replace('[optional_subject]',' ', $message);
						}
					} else {
						$message = str_replace('[optional_subject]',' ', $message);
					}
				} elseif($userTag->tagname == '[extra_curricular_activities]' && strpos($message, '[extra_curricular_activities]') !== false) {
					if($user->extracurricularactivities) {
						$message = str_replace("[extra_curricular_activities]", $user->extracurricularactivities, $message);
					} else {
						$message = str_replace("[extra_curricular_activities]", ' ', $message);
					}
				} elseif($userTag->tagname == '[remarks]' && strpos($message, '[remarks]') !== false) {
					if($user->remarks) {
						$message = str_replace("[remarks]", $user->remarks, $message);
					} else {
						$message = str_replace("[remarks]", ' ', $message);
					}
				} elseif($userTag->tagname == '[date]' && strpos($message, '[date]') !== false) {
					$message = str_replace("[date]", (date("d M Y")), $message);
				} elseif($userTag->tagname == '[payd]' && strpos($message, '[payd]') !== false) {
					$gatewayOptions = $this->payment_gateway_option_m->get_single_payment_gateway_option_values(array('payment_option' => 'mpesa_shortcode', 'schoolID' => $schoolID));
					$mpesaDetails = "M-PESA paybill ". $gatewayOptions->payment_value;
					$students = $this->studentrelation_m->general_get_order_by_student(array('parentID' => $user->parentsID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID, 'active' => 1));
					foreach($students as $student) {
						$mpesaDetails .= " Account number ". $student->srstudentID ." for ". $student->srname;
						if($sendType == 'SMS')
							$mpesaDetails .= "\r\n";
						elseif($sendType == 'email')
							$mpesaDetails .= "<br>";
					}
					$message = str_replace("[payd]", $mpesaDetails, $message);
				} elseif($userTag->tagname == '[bal]' && strpos($message, '[bal]') !== false) {
					$studentBalance = "";
					$gatewayOptions = $this->payment_gateway_option_m->get_single_payment_gateway_option_values(array('payment_option' => 'mpesa_shortcode', 'schoolID' => $schoolID));
					if($user->usertypeID == 4) {
						$students = $this->studentrelation_m->general_get_order_by_student(array('parentID' => $user->parentsID, 'srschoolyearID' => $schoolyearID, 'srschoolID' => $schoolID, 'active' => 1));
						foreach ($students as $student) {
							$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $student->srstudentID]);
							$totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
							$invoices = $this->invoice_m->get_order_by_invoice(array('studentID' => $student->srstudentID, 'deleted_at' => 1));
							$totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
							$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $student->srstudentID, 'deleted_at' => 1));
							$totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
							$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
							if($balance > 0) {
								$studentBalance .= $student->srname ." has a balance of Ksh ". number_format($balance). ". To pay via M-PESA please enter Paybill number ". $gatewayOptions->payment_value ." and Account number ". $student->srstudentID;
								if($sendType == 'SMS')
									$studentBalance .= "\r\n";
								elseif($sendType == 'email')
									$studentBalance .= "<br>";
							} else {
								$studentBalance = "";
							}
						}
					} elseif($user->usertypeID == 3) {
						$allPaymentList = $this->payment_m->get_order_by_payment(['studentID' => $user->srstudentID]);
						$totalPaymentAmount = $this->generateAllPaymentAmount($allPaymentList);
						$invoices = $this->invoice_m->get_order_by_invoice(array('studentID' => $user->srstudentID, 'deleted_at' => 1));
						$totalInvoiceAmount = $this->generateAllInvoiceAmount($invoices);
						$creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $user->srstudentID, 'deleted_at' => 1));
						$totalCreditmemoAmount = $this->generateAllCreditmemoAmount($creditmemos);
						$balance = $totalInvoiceAmount - ($totalPaymentAmount + $totalCreditmemoAmount);
						if($balance > 0)
							$studentBalance = $user->srname ." has a balance of Ksh ". number_format($balance). ". To pay via M-PESA please enter Paybill number ". $gatewayOptions->payment_value ." and Account number ". $user->srstudentID;
						else
							$studentBalance = "";
					}
					if(strlen($studentBalance) > 0)
						$message = str_replace("[bal]", $studentBalance, $message);
					else
						$message = "";
				} elseif($userTag->tagname == '[pass]' && strpos($message, '[pass]') !== false) {
					$factory = new RandomLib\Factory;
					$generator = $factory->getGenerator(new SecurityLib\Strength(SecurityLib\Strength::MEDIUM));
					$passwordLength = 8; // Or more
					$randomPassword = $generator->generateString($passwordLength);
					// update password
					$password = $this->parents_m->hash($randomPassword);
					$this->parents_m->update_parents(array('password' => $password), $user->parentsID);
					$message = str_replace("[pass]", $randomPassword, $message);
				} elseif($userTag->tagname == '[mob]' && strpos($message, '[mob]') !== false) {
					$message = str_replace("[mob]", site_url() ."/mobileapp/download", $message);
				} elseif($userTag->tagname == '[web]' && strpos($message, '[web]') !== false) {
					$message = str_replace("[web]", site_url(), $message);
				} elseif($userTag->tagname == '[result_table]' && strpos($message, '[result_table]') !== false) {
					if($sendType == 'email') {
						if($user->usertypeID == 3) {
							$this->load->library('mark', ['studentID'=> $user->srstudentID, 'classesID'=> $user->srclassesID, 'schoolyearID'=> $schoolyearID, 'data'=> $this->data['siteinfos']]);
							$result = $this->mark->mail();
						} else {
							$result = '';
						}
						$message = str_replace("[result_table]", $result, $message);
					} elseif($sendType == 'SMS') {
						if($user->usertypeID == 3) {
							$this->load->library('mark', ['studentID'=> $user->srstudentID, 'classesID'=> $user->srclassesID, 'schoolyearID'=> $schoolyearID, 'data'=> $this->data['siteinfos']]);
							$result = $this->mark->sms();
						} else {
							$result = '';
						}
						$message = str_replace("[result_table]", $result, $message);
					}
				}
			}
		}
		return $message;
	}

	public function alltemplate() {
		if($this->input->post('usertypeID') == 'select') {
			echo '<option value="select">'.$this->lang->line('mailandsms_select_template').'</option>';
		} else {
			$usertypeID = $this->input->post('usertypeID');
			$type = $this->input->post('type');
			$schoolID = $this->session->userdata("schoolID");
			$templates = $this->mailandsmstemplate_m->get_order_by_mailandsmstemplate(array('usertypeID' => $usertypeID, 'type' => $type, 'schoolID' => $schoolID));
			echo '<option value="select">'.$this->lang->line('mailandsms_select_template').'</option>';
			if(customCompute($templates)) {
				foreach ($templates as $key => $template) {
					echo '<option value="'.$template->mailandsmstemplateID.'">'. $template->name  .'</option>';
				}
			}
		}
	}

	public function allusers() {
		if($this->input->post('usertypeID') == 'select') {
			echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
		} else {
			$usertypeID = $this->input->post('usertypeID');
			$userID = $this->input->post('userID');
			$schoolID = $this->session->userdata("schoolID");

			if($usertypeID == 1) {
				$systemadmins = $this->systemadmin_m->get_order_by_systemadmin(array('schoolID' => $schoolID));
				if(customCompute($systemadmins)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($systemadmins as $key => $systemadmin) {
						echo "<option value='".$systemadmin->systemadminID."'>".$systemadmin->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			} elseif($usertypeID == 2) {
				$teachers = $this->teacher_m->general_get_order_by_teacher(array('schoolID' => $schoolID));
				if(customCompute($teachers)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($teachers as $key => $teacher) {
						echo "<option value='".$teacher->teacherID."'>".$teacher->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			} elseif($usertypeID == 3) {
				$classes = $this->classes_m->general_get_order_by_classes(array('schoolID' => $schoolID));
				if(customCompute($classes)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_class')."</option>";
					foreach ($classes as $key => $classm) {
						echo "<option value='".$classm->classesID."'>".$classm->classes.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_class').'</option>';
				}
			} elseif($usertypeID == 4) {
				$parents = $this->parents_m->get_order_by_parents(array('schoolID' => $schoolID));
				if(customCompute($parents)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($parents as $key => $parent) {
						echo "<option value='".$parent->parentsID."'>".$parent->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			} else {
				$users = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID, 'schoolID' => $schoolID));
				if(customCompute($users)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($users as $key => $user) {
						echo "<option value='".$user->userID."'>".$user->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			}
		}
	}

	public function allstudent() {
		$schoolID = $this->session->userdata("schoolID");
		$schoolyearID = $this->input->post('schoolyear');
		$classesID = $this->input->post('classes');
		$sectionID = $this->input->post('section');
		if((int)$schoolyearID && (int)$classesID) {
	    if ((int)$sectionID){
          $students = $this->studentrelation_m->get_order_by_student(array('srschoolID' => $schoolID, 'srschoolyearID' => $schoolyearID,'srsectionID' => $sectionID, 'srclassesID' => $classesID));
      }else {
          $students = $this->studentrelation_m->get_order_by_student(array('srschoolID' => $schoolID, 'srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID));
      }
			if(customCompute($students)) {
				echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				foreach ($students as $key => $student) {
					echo '<option value="'.$student->srstudentID.'">'.$student->srname.'</option>';
				}
			} else {
				echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
			}
		} else {
			echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
		}
	}

  public function allsection() {
      $classesID = $this->input->post('classes');
      if((int)$classesID) {
				$schoolID = $this->session->userdata("schoolID");
        $allsection = $this->section_m->general_get_order_by_section(array('classesID' => $classesID, 'schoolID' => $schoolID));
        echo "<option value='select'>", $this->lang->line("mailandsms_all_section"),"</option>";
        foreach ($allsection as $value) {
            echo "<option value=\"$value->sectionID\">",$value->section,"</option>";
        }
      }
  }

	public function check_email_usertypeID() {
		if($this->input->post('email_usertypeID') == 'select') {
			$this->form_validation->set_message("check_email_usertypeID", "The %s field is required");
	     	return FALSE;
		} else {
			return TRUE;
		}
	}

	public function alltemplatedesign() {
		if((int)$this->input->post('templateID')) {
			$templateID = $this->input->post('templateID');
			$schoolID = $this->session->userdata("schoolID");
			$templates = $this->mailandsmstemplate_m->get_single_mailandsmstemplate(array('mailandsmstemplateID' => $templateID, 'schoolID' => $schoolID));
			if(customCompute($templates)) {
				echo $templates->template;
			}
		} else {
			echo '';
		}
	}

	public function check_sms_usertypeID() {
		if($this->input->post('sms_usertypeID') == 'select') {
			$this->form_validation->set_message("check_sms_usertypeID", "The %s field is required");
	     	return FALSE;
		} else {
			return TRUE;
		}
	}

	public function check_getway() {
		if($this->input->post('sms_getway') == 'select') {
			$this->form_validation->set_message("check_getway", "The %s field is required");
	    return FALSE;
		} else {
			$getway = $this->input->post('sms_getway');
			$arrgetway = array('bongasms', 'smsleopard', 'clickatell', 'twilio', 'bulk', 'msg91');
			if(in_array($getway, $arrgetway)) {
				if($getway == 'smsleopard') {
          return true;
				} elseif($getway == 'bongasms') {
          return true;
				} elseif($getway == "clickatell") {
					if($this->clickatell->ping() == TRUE) {
						return TRUE;
					} else {
						$this->form_validation->set_message("check_getway", 'Setup Your clickatell Account');
	     			return FALSE;
					}
					return TRUE;
				} elseif($getway == 'twilio') {
					$get = $this->twilio->get_twilio();
					$ApiVersion = $get['version'];
					$AccountSid = $get['accountSID'];
					$check = $this->twilio->request("/$ApiVersion/Accounts/$AccountSid/Calls");

					if($check->IsError) {
						$this->form_validation->set_message("check_getway", $check->ErrorMessage);
	     				return FALSE;
					}
					return TRUE;
				} elseif($getway == 'bulk') {
					if($this->bulk->ping() == TRUE) {
						return TRUE;
					} else {
						$this->form_validation->set_message("check_getway", 'Invalid Username or Password');
	     				return FALSE;
					}
				} elseif($getway == 'msg91') {
          return true;
				}
			} else {
				$this->form_validation->set_message("check_getway", "The %s field is required");
	     		return FALSE;
			}
		}
	}

	public function view() {
		if($_POST) {
			$id = $this->input->post("id");
		} else {
			$id = htmlentities(escapeString($this->uri->segment(3)));
		}

		if((int)$id) {
			$schoolID = $this->session->userdata("schoolID");
			$this->data['mailandsms'] = $this->mailandsms_m->get_single_mailandsms(array('mailandsmsID' => $id, 'schoolID' => $schoolID));
			$this->data['delivery_report'] = $this->delivery_report($this->data['mailandsms']);
			if($this->data['mailandsms']) {
				if($_POST)
					redirect(base_url("mailandsms/view/".$id));
				else {
					$this->data["subview"] = "mailandsms/view";
					$this->load->view('_layout_main', $this->data);
				}
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function unique_data($data) {
		if($data != "") {
			if($data == "select") {
				$this->form_validation->set_message('unique_data', 'The %s field is required.');
				return FALSE;
			}
		}
		return TRUE;
	}

	public function unique_smsactive()
	{
			$array = ['', 1, 2];

			if(!in_array($this->input->post('sms_active'), $array)) {
					$this->form_validation->set_message("unique_smsactive", "The %s field is required.");
					return FALSE;
			}
			return TRUE;
	}

	private function generateAllInvoiceAmount($invoices) {
		$total = 0;
    if(customCompute($invoices)) {
        foreach ($invoices as $invoice) {
            $total += $invoice->amount;
        }
    }

    return $total;
	}

	private function generateAllCreditmemoAmount($creditmemos) {
		$total = 0;
    if(customCompute($creditmemos)) {
        foreach ($creditmemos as $creditmemo) {
            $total += $creditmemo->amount;
        }
    }

    return $total;
  }

	private function generateAllPaymentAmount($payments) {
		$total = 0;
    if(customCompute($payments)) {
        foreach ($payments as $payment) {
            $total += $payment->paymentamount;
        }
    }

    return $total;
  }

	public function studentStatement($id)
	{
			if((int)$id) {
				$schoolID = $this->session->userdata("schoolID");
				$student = $this->studentrelation_m->general_get_single_student(array('srstudentID' => $id, 'srschoolID' => $schoolID));
				if (customCompute($student)) {
					// payment
					$allPaymentList = $this->payment_m->get_order_by_payment(array('studentID' => $student->srstudentID));
					$bbfPaymentList = $this->payment_m->get_order_by_payment(array('schoolYearID' => 0, 'schoolID' => $schoolID));

					$this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('studentID' => $student->srstudentID, 'deleted_at' => 1));
					$this->data['creditmemos'] = $this->creditmemo_m->get_order_by_creditmemo(array('studentID' => $student->srstudentID, 'deleted_at' => 1));
					$bbf_invoices = $this->invoice_m->get_order_by_invoice(array('schoolYearID' => 0, 'schoolID' => $schoolID));
					$bbf_creditmemos = $this->creditmemo_m->get_order_by_creditmemo(array('schoolYearID' => 0, 'schoolID' => $schoolID));

					// balance brought forward
					$balance = 0;
					$statement = array();

					foreach ($bbf_invoices as $invoice) {
						$statement[] = ['fee_type' => $invoice->feetype, 'amount' => $invoice->amount, 'date' => $invoice->create_date, 'column' => 'debit'];
					}
					foreach ($bbf_creditmemos as $creditmemo) {
						$statement[] = ['fee_type' => $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->create_date, 'column' => 'credit'];
					}
					foreach ($bbfPaymentList as $payment) {
						$statement[] = ['fee_type' => 'Paid', 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
					}

					usort($statement, function($a, $b) {
						return $a['date'] <=> $b['date'];
					});

					foreach ($statement as $key => $value) {
						if ($statement[$key]['column'] == "debit") {
							$balance += $statement[$key]['amount'];
						} else {
							$balance -= $statement[$key]['amount'];
						}
					}

					// statement
					$statement = array();
					$statement[] = ['fee_type' => 'Balance brought forward', 'amount' => '', 'date' => '', 'column' => '', 'balance' => $balance];
					foreach ($this->data['invoices'] as $invoice) {
						$statement[] = ['fee_type' => "Invoice #". $invoice->invoiceID ." - ". $invoice->feetype, 'amount' => $invoice->amount, 'date' => $invoice->create_date, 'column' => 'debit'];
					}
					foreach ($this->data['creditmemos'] as $creditmemo) {
						$statement[] = ['fee_type' => "Credit Memo #". $creditmemo->creditmemoID ." - ". $creditmemo->credittype, 'amount' => $creditmemo->amount, 'date' => $creditmemo->create_date, 'column' => 'credit'];
					}
					foreach ($allPaymentList as $payment) {
						$description = 'Payment Ref No. '. $payment->globalpaymentID .'; '. $payment->paymenttype .'; '. $payment->transactionID;
						$statement[] = ['fee_type' => $description, 'amount' => $payment->paymentamount, 'date' => $payment->paymentdate, 'column' => 'credit'];
					}

					usort($statement, function($a, $b) {
						return $a['date'] <=> $b['date'];
					});

					foreach($statement as $key => $value) {
						if ($statement[$key]['column'] == "debit") {
							$balance += $statement[$key]['amount'];
						} else {
						if ((int)$statement[$key]['amount'])
								$balance -= $statement[$key]['amount'];
						}
						$statement[$key]['balance'] = $balance;
					}

					$gatewayOptions = $this->payment_gateway_option_m->get_single_payment_gateway_option_values(array('payment_option' => 'mpesa_shortcode', 'schoolID' => $schoolID));
					$this->data['paybill'] = $gatewayOptions->payment_value;

					$this->data['student'] = $student;
					$this->data['statement'] = $statement;

					return $this->data;
				}
			}
	}

	public function examResults($studentID) {
		if((int)$studentID || $studentID >= 0) {
			$schoolID = $this->session->userdata("schoolID");
			$this->data['studentIDD'] = $studentID;

			$queryArray['schoolID']          = $schoolID;
			$studentQueryArray['srschoolID'] = $schoolID;

			$exam   = $this->exam_m->get_latest_exam(array('studentID' => $studentID, 'schoolID' => $schoolID));
			$examID = $exam->examID;

			if(customCompute($exam)) {
				$queryArray['examID'] = $examID;
			}
			if((int)$studentID > 0) {
				$studentQueryArray['srstudentID'] = $studentID;
				$queryArray['studentID'] = $studentID;
			}

			$this->data['examName']     = $exam->exam;
			$this->data['grades']       = $this->grade_m->get_order_by_grade(array('schoolID' => $schoolID));
			$student = $this->studentrelation_m->get_single_studentrelation($studentQueryArray);
			$classesID = $student->srclassesID;

			$marks                  = $this->mark_m->student_all_mark_array($queryArray);
			$mandatorySubjects      = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1, 'schoolID' => $schoolID));

			$this->subject_m->order('type DESC');
			$this->data['subjects'] = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'schoolID' => $schoolID));

			$settingmarktypeID      = $this->data['siteinfos']->marktypeID;
			$markpercentagesmainArr = $this->marksetting_m->get_marksetting_markpercentages();
			$markpercentagesArr     = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
			$this->data['markpercentagesArr']  = $markpercentagesArr;
			$this->data['settingmarktypeID']   = $settingmarktypeID;

			$retMark = [];
			if(customCompute($marks)) {
				foreach ($marks as $mark) {
					$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
				}
			}

			$studentPosition             = [];
			$studentChecker              = [];
			$studentClassPositionArray   = [];
			$studentSubjectPositionArray = [];
			$markpercentagesCount        = 0;

			$opuniquepercentageArr = [];
			if($student->sroptionalsubjectID > 0) {
				$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
			}

			$studentPosition[$student->srstudentID]['totalSubjectMark'] = 0;
			if(customCompute($mandatorySubjects)) {
				foreach ($mandatorySubjects as $mandatorySubject) {
					$uniquepercentageArr = isset($markpercentagesArr[$mandatorySubject->subjectID]) ? $markpercentagesArr[$mandatorySubject->subjectID] : [];

					$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
					$markpercentagesCount = customCompute($markpercentages);
					if(customCompute($markpercentages)) {
						foreach ($markpercentages as $markpercentageID) {
							$f = false;
							if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
									$f = true;
							}

							if(isset($studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID])) {
								if(isset($retMark[$studentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
									$studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID] += $retMark[$studentID][$mandatorySubject->subjectID][$markpercentageID];
								} else {
									$studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
								}
							} else {
								if(isset($retMark[$studentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
									$studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID] = $retMark[$studentID][$mandatorySubject->subjectID][$markpercentageID];
								} else {
									$studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
								}
							}

							if(isset($retMark[$studentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
								$studentPosition[$studentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID] = $retMark[$studentID][$mandatorySubject->subjectID][$markpercentageID];

								if(isset($studentPosition[$studentID]['markpercentagetotalmark'][$markpercentageID])) {
									$studentPosition[$studentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$studentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID];
								} else {
									$studentPosition[$studentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$studentID]['markpercentageMark'][$mandatorySubject->subjectID][$markpercentageID];

								}
							}

							$f = false;
							if(customCompute($opuniquepercentageArr)) {
								if(isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own'])) {
										$f = true;
								}
							}

							if(!isset($studentChecker['subject'][$studentID][$markpercentageID]) && $f) {
								if($student->sroptionalsubjectID != 0) {
									if(isset($studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID])) {
										if(isset($retMark[$studentID][$student->sroptionalsubjectID][$markpercentageID])) {
											$studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID] += $retMark[$studentID][$student->sroptionalsubjectID][$markpercentageID];
										} else {
											$studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
										}
									} else {
										if(isset($retMark[$studentID][$student->sroptionalsubjectID][$markpercentageID])) {
											$studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID] = $retMark[$studentID][$student->sroptionalsubjectID][$markpercentageID];
										} else {
											$studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
										}
									}

									if(isset($retMark[$studentID][$student->sroptionalsubjectID][$markpercentageID])) {
										$studentPosition[$studentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID] = $retMark[$studentID][$student->sroptionalsubjectID][$markpercentageID];

										if(isset($studentPosition[$studentID]['markpercentagetotalmark'][$markpercentageID])) {
											$studentPosition[$studentID]['markpercentagetotalmark'][$markpercentageID] += $studentPosition[$studentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID];
										} else {
											if($f) {
												$studentPosition[$studentID]['markpercentagetotalmark'][$markpercentageID] = $studentPosition[$studentID]['markpercentageMark'][$student->sroptionalsubjectID][$markpercentageID];
											}
										}

									}
								}
								$studentChecker['subject'][$studentID][$markpercentageID] = TRUE;
							}
						}
					}

					$studentPosition[$studentID]['totalSubjectMark'] += $studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID];

					if(!isset($studentChecker['totalSubjectMark'][$studentID])) {
						if($student->sroptionalsubjectID != 0) {
							$studentPosition[$studentID]['totalSubjectMark'] += $studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID];
						}
						$studentChecker['totalSubjectMark'][$studentID] = TRUE;
					}

					$studentSubjectPositionArray[$mandatorySubject->subjectID][$studentID] = $studentPosition[$studentID]['subjectMark'][$mandatorySubject->subjectID];
					if(!isset($studentChecker['studentSubjectPositionArray'][$student->srstudentID])) {
						if($student->sroptionalsubjectID != 0) {
							$studentSubjectPositionArray[$student->sroptionalsubjectID][$studentID] = $studentPosition[$studentID]['subjectMark'][$student->sroptionalsubjectID];
						}
					}
				}
			}


			$studentPosition[$studentID]['classPositionMark'] = ($studentPosition[$studentID]['totalSubjectMark'] / customCompute($studentPosition[$studentID]['subjectMark']));
			$studentClassPositionArray[$studentID]             = $studentPosition[$studentID]['classPositionMark'];

			if(isset($studentPosition['totalStudentMarkAverage'])) {
				$studentPosition['totalStudentMarkAverage'] += $studentPosition[$studentID]['classPositionMark'];
			} else {
				$studentPosition['totalStudentMarkAverage']  = $studentPosition[$studentID]['classPositionMark'];
			}

			arsort($studentClassPositionArray);
			$studentPosition['studentClassPositionArray'] = $studentClassPositionArray;
			if(customCompute($studentSubjectPositionArray)) {
				foreach($studentSubjectPositionArray as $subjectID => $studentSubjectPositionMark) {
					arsort($studentSubjectPositionMark);
					$studentPosition['studentSubjectPositionMark'][$subjectID] = $studentSubjectPositionMark;
				}
			}

			$this->data['student']				 = $student;
			$this->data['col']             = 5 + $markpercentagesCount;
			$this->data['attendance']      = $this->get_student_attendance($queryArray, $this->data['subjects'], $this->data['studentLists']);
			$this->data['studentPosition'] = $studentPosition;
			$this->data['percentageArr']   = pluck($this->markpercentage_m->get_order_by_markpercentage(array('schoolID' => $schoolID)), 'obj', 'markpercentageID');

			return $this->data;
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}
}
