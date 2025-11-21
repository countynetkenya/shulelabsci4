<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tutorial extends Admin_Controller
{
    /*
    | -----------------------------------------------------
    | PRODUCT NAME:     INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:            INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:            info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:        RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:            http://inilabs.net
    | -----------------------------------------------------
     */
    protected $_memoryLimit = '1024M';
    protected $_post_max_size = '20480M';
    protected $_upload_max_filesize = '20480M';
    public function __construct()
    {
        parent::__construct();
        $this->load->model("section_m");
        $this->load->model("subject_m");
        $this->load->model("student_m");
        $this->load->model("tutorial_m");
        $this->load->model("lesson_m");

        $language = $this->session->userdata('lang');
        $this->lang->load('tutorial', $language);
    }

    public function index()
    {
        $this->data['tutorials'] = $this->tutorial_m->get_tutorial_with_is_public();

        $this->data['classes']  = pluck($this->classes_m->general_get_classes(), 'classes', 'classesID');
        $this->data['sections'] = pluck($this->section_m->general_get_section(), 'section', 'sectionID');
        $this->data['subjects'] = pluck($this->subject_m->general_get_subject(), 'subject', 'subjectID');

        $this->data["subview"] = "tutorial/index";
        $this->load->view('_layout_main', $this->data);
    }

    public function add()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/select2/select2.js',
            ),
        );

        $this->data['classes']  = $this->classes_m->get_classes();
        $this->data['sections'] = [];
        $this->data['subjects'] = [];

        if ($_POST) {
            $classesID = $this->input->post("classesID");
            if ((int) $classesID) {
                $this->data['subjects'] = $this->subject_m->get_order_by_subject(array('classesID' => $classesID));
                $this->data['sections'] = $this->section_m->get_order_by_section(array("classesID" => $classesID));
            }

            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data["subview"] = "tutorial/add";
                $this->load->view('_layout_main', $this->data);
            } else {
                $array = array(
                    "title"             => $this->input->post("title"),
                    "classesID"         => $this->input->post("classesID"),
                    "sectionID"         => $this->input->post("sectionID"),
                    "subjectID"         => $this->input->post("subjectID"),
                    "is_public"         => $this->input->post("is_public") ? 0 : 1,
                    "created_at"        => date('Y-m-d H:i:s'),
                    "updated_at"        => date('Y-m-d H:i:s'),
                    'create_userID'     => $this->session->userdata('loginuserID'),
                    'create_usertypeID' => $this->session->userdata('usertypeID'),
                );
                $array['cover_photo'] = $this->upload_data['cover_photo']['file_name'];

                $this->tutorial_m->insert_tutorial($array);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("tutorial/index"));
            }
        } else {
            $this->data["subview"] = "tutorial/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/select2/select2.js',
            ),
        );

        $tutorial_id = htmlentities(escapeString($this->uri->segment(3)));
        if ((int) $tutorial_id) {
            $this->data['tutorial'] = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);

            if (customCompute($this->data['tutorial'])) {
                $this->data['classes']  = $this->classes_m->get_classes();
                $this->data['sections'] = [];
                $this->data['subjects'] = [];

                $classesID = $this->input->post("classesID");
                if (!(int) $classesID) {
                    $classesID = (int) $this->data['tutorial']->classesID;
                }

                $this->data['subjects'] = $this->subject_m->get_order_by_subject(array('classesID' => $classesID));
                $this->data['sections'] = $this->section_m->get_order_by_section(array("classesID" => $classesID));

                if ($_POST) {
                    $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == false) {
                        $this->data["subview"] = "tutorial/edit";
                        $this->load->view('_layout_main', $this->data);
                    } else {
                        $array = array(
                            "title"             => $this->input->post("title"),
                            "classesID"         => $this->input->post("classesID"),
                            "sectionID"         => $this->input->post("sectionID"),
                            "subjectID"         => $this->input->post("subjectID"),
                            "is_public"         => $this->input->post("is_public") ? 0 : 1,
                            "updated_at"        => date('Y-m-d H:i:s'),
                            'create_userID'     => $this->session->userdata('loginuserID'),
                            'create_usertypeID' => $this->session->userdata('usertypeID'),
                        );
                        $array['cover_photo'] = $this->upload_data['cover_photo']['file_name'];

                        $this->tutorial_m->update_tutorial($array, $tutorial_id);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("tutorial/index"));
                    }
                } else {
                    $this->data["subview"] = "tutorial/edit";
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

    public function view()
    {
        $tutorial_id = htmlentities(escapeString($this->uri->segment(3)));
        if ((int) $tutorial_id) {
            $tutorial = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);
            if (customCompute($tutorial)) {
                $this->data['headerassets'] = [
                    'css' => [
                        'assets/tutorial/css/view.css',
                    ],
                    'js'  => [
                        'assets/tutorial/js/view.js',
                    ]
                ];

                $this->data['lessons'] = $this->lesson_m->get_order_by_lesson(['tutorial_id' => $tutorial_id]);
                $this->data["subview"] = "tutorial/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function delete()
    {
        $tutorial_id = htmlentities(escapeString($this->uri->segment(3)));
        if ((int) $tutorial_id) {
            $tutorial = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);

            if (customCompute($tutorial)) {
                if (config_item('demo') == false && $tutorial->file != '') {
                    if (file_exists(FCPATH . 'uploads/images/' . $tutorial->cover_photo)) {
                        unlink(FCPATH . 'uploads/images/' . $tutorial->cover_photo);
                    }
                }
                $this->tutorial_m->delete_tutorial($tutorial_id);

                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("tutorial/index"));
            } else {
                redirect(base_url("tutorial/index"));
            }
        } else {
            redirect(base_url("tutorial/index"));
        }
    }

    public function rules()
    {
        $rules = array(
            array(
                'field' => 'title',
                'label' => $this->lang->line("tutorial_title"),
                'rules' => 'trim|required|xss_clean|max_length[255]',
            ),
            array(
                'field' => 'classesID',
                'label' => $this->lang->line("tutorial_class"),
                'rules' => 'trim|required|numeric|xss_clean',
            ),
            array(
                'field' => 'sectionID',
                'label' => $this->lang->line("tutorial_section"),
                'rules' => 'trim|required|numeric|xss_clean',
            ),
            array(
                'field' => 'subjectID',
                'label' => $this->lang->line("tutorial_subject"),
                'rules' => 'trim|required|numeric|xss_clean',
            ),
            array(
                'field' => 'is_public',
                'label' => $this->lang->line("tutorial_is_public"),
                'rules' => 'trim|xss_clean',
            ),
            array(
                'field' => 'cover_photo',
                'label' => $this->lang->line("tutorial_cover_photo"),
                'rules' => 'trim|xss_clean|callback_fileupload',
            ),
        );
        return $rules;
    }

    public function fileupload()
    {
        $tutorial_id = htmlentities(escapeString($this->uri->segment(3)));
        $tutorial    = [];
        if ((int) $tutorial_id) {
            $tutorial = $this->tutorial_m->get_single_tutorial(array('tutorial_id' => $tutorial_id));
        }

        if ($_FILES["cover_photo"]['name'] != "") {
            $file_name        = $_FILES["cover_photo"]['name'];
            $random           = random19();
            $makeRandom       = hash('sha512', $random . $this->input->post('title') . config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode          = explode('.', $file_name);
            if (customCompute($explode) >= 2) {
                $new_file                = $file_name_rename . '.' . end($explode);
                $config['upload_path']   = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png|jpeg";
                $config['file_name']     = $new_file;
                $config['max_size']      = '4096';
                $config['max_width']     = '3000';
                $config['max_height']    = '3000';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload("cover_photo")) {
                    $this->form_validation->set_message("fileupload", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['cover_photo'] = $this->upload->data();
                    return true;
                }
            } else {
                $this->form_validation->set_message("fileupload", "Invalid file");
                return false;
            }
        } else {
            if (customCompute($tutorial)) {
                $this->upload_data['cover_photo'] = array('file_name' => $tutorial->cover_photo);
                return true;
            } else {
                $this->upload_data['cover_photo'] = array('file_name' => null);
                return true;
            }
        }
    }

    public function subjectcall()
    {
        $classID = $this->input->post('id');
        if ((int) $classID) {
            $allclasses = $this->subject_m->get_order_by_subject(array('classesID' => $classID));
            echo "<option value='0'>", $this->lang->line("tutorial_select_subject"), "</option>";
            foreach ($allclasses as $value) {
                echo "<option value=\"$value->subjectID\">", $value->subject, "</option>";
            }
        }
    }

    public function sectioncall()
    {
        $classID = $this->input->post('id');
        if ((int) $classID) {
            $allsection = $this->section_m->get_order_by_section(array("classesID" => $classID));
            echo "<option value='0'>", $this->lang->line("tutorial_select_section"), "</option>";
            foreach ($allsection as $value) {
                echo "<option value=\"$value->sectionID\">", $value->section, "</option>";
            }
        }
    }

    public function getlesson()
    {
        if ($_POST) {
            $this->data['lesson'] = $this->lesson_m->get_single_lesson(['lesson_id' => $this->input->post('lessonid')]);
            if (customCompute($this->data['lesson'])) {
                $this->load->view('tutorial/getlesson', $this->data);
            }
        }
    }

    public function addlesson()
    {
        if (permissionChecker('tutorial_add')) {
            $tutorial_id = htmlentities(escapeString($this->uri->segment(3)));
            if ((int) $tutorial_id) {
                $this->data['tutorial'] = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);
                if (customCompute($this->data['tutorial'])) {

                    $this->data['lessons'] = $this->lesson_m->get_order_by_lesson(['tutorial_id' => $tutorial_id]);

                    if ($_POST) {
                        $rules = $this->lesson_rules();

                        $this->form_validation->set_rules($rules);
                        if ($this->form_validation->run() == false) {
                            $this->data["subview"] = "tutorial/addlesson";
                            $this->load->view('_layout_main', $this->data);
                        } else {
                            $array = array(
                                "title"              => $this->input->post("title"),
                                "lesson_provider"    => $this->input->post("lesson_provider"),
                                "video_url"          => $this->input->post("video_url"),
                                "duration"           => $this->input->post("duration"),
                                "file"               => $this->upload_data['file']['file_name'],
                                "file_original_name" => $this->upload_data['file']['file_original_name'],
                                "video_file"         => $this->upload_data['video_file']['file_name'],
                                "video_file_original_name" => $this->upload_data['video_file']['file_original_name'],
                                "description"        => $this->input->post("description"),
                                "tutorial_id"        => $tutorial_id,
                                "created_at"         => date('Y-m-d H:i:s'),
                                "updated_at"         => date('Y-m-d H:i:s'),
                                'create_userID'      => $this->session->userdata('loginuserID'),
                                'create_usertypeID'  => $this->session->userdata('usertypeID'),
                            );

                            $this->lesson_m->insert_lesson($array);

                            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                            redirect(base_url("tutorial/addlesson/" . $tutorial_id));
                        }
                    } else {
                        $this->data["subview"] = "tutorial/addlesson";
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function editlesson()
    {
        if (permissionChecker('tutorial_edit')) {
            $lesson_id = htmlentities(escapeString($this->uri->segment(4)));
            if ((int) $lesson_id) {
                $this->data['lesson'] = $this->lesson_m->get_single_lesson(['lesson_id' => $lesson_id]);
                if (customCompute($this->data['lesson'])) {
                    $tutorial_id = $this->data['lesson']->tutorial_id;
                    $tutorial    = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);
                    if (customCompute($tutorial)) {

                        $this->data['lessons'] = $this->lesson_m->get_order_by_lesson(['tutorial_id' => $tutorial_id]);

                        if ($_POST) {
                            $rules = $this->lesson_rules();

                            $this->form_validation->set_rules($rules);
                            if ($this->form_validation->run() == false) {
                                $this->data["subview"] = "tutorial/editlesson";
                                $this->load->view('_layout_main', $this->data);
                            } else {
                                $array = array(
                                    "title"              => $this->input->post("title"),
                                    "lesson_provider"    => $this->input->post("lesson_provider"),
                                    "video_url"          => $this->input->post("video_url"),
                                    "duration"           => $this->input->post("duration"),
                                    "file"               => $this->upload_data['file']['file_name'],
                                    "file_original_name" => $this->upload_data['file']['file_original_name'],
                                    "video_file"         => $this->upload_data['video_file']['file_name'],
                                    "video_file_original_name" => $this->upload_data['video_file']['file_original_name'],
                                    "description"        => $this->input->post("description"),
                                    "tutorial_id"        => $tutorial_id,
                                    "updated_at"         => date('Y-m-d H:i:s'),
                                    'create_userID'      => $this->session->userdata('loginuserID'),
                                    'create_usertypeID'  => $this->session->userdata('usertypeID'),
                                );

                                $this->lesson_m->update_lesson($array, $lesson_id);

                                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                                redirect(base_url("tutorial/addlesson/" . $tutorial_id));
                            }
                        } else {
                            $this->data["subview"] = "tutorial/editlesson";
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
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function viewlesson()
    {
        if (permissionChecker('tutorial_view')) {
            $lesson_id = htmlentities(escapeString($this->uri->segment(3)));
            if ((int) $lesson_id) {
                $this->data['lesson'] = $this->lesson_m->get_single_lesson(['lesson_id' => $lesson_id]);
                if (customCompute($this->data['lesson'])) {
                    $tutorial_id = $this->data['lesson']->tutorial_id;
                    $tutorial    = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);
                    if (customCompute($tutorial)) {
                        $this->data['headerassets'] = [
                            'css' => [
                                'assets/tutorial/css/view.css',
                            ]
                        ];
                        $this->data["subview"] = "tutorial/viewlesson";
                        $this->load->view('_layout_main', $this->data);
                    } else {
                        $this->data["subview"] = "error";
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function deletelesson()
    {
        if (permissionChecker('tutorial_delete')) {
            $lesson_id = htmlentities(escapeString($this->uri->segment(3)));
            if ((int) $lesson_id) {
                $lesson = $this->lesson_m->get_single_lesson(['lesson_id' => $lesson_id]);

                if (customCompute($lesson)) {
                    $tutorial_id = $lesson->tutorial_id;
                    $tutorial    = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);
                    if (customCompute($tutorial)) {
                        $this->lesson_m->delete_lesson($lesson_id);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("tutorial/addlesson/" . $lesson->tutorial_id));
                    } else {
                        redirect(base_url("tutorial/addlesson/" . $lesson->tutorial_id));
                    }
                } else {
                    redirect(base_url("tutorial/addlesson/" . $lesson->tutorial_id));
                }
            } else {
                redirect(base_url("tutorial/addlesson"));
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function lessondownload()
    {
        if (permissionChecker('tutorial_view')) {
            $lesson_id = htmlentities(escapeString($this->uri->segment(3)));
            if ((int) $lesson_id) {
                $lesson = $this->lesson_m->get_single_lesson(['lesson_id' => $lesson_id]);
                if (customCompute($lesson)) {
                    $tutorial_id = $lesson->tutorial_id;
                    $tutorial    = $this->tutorial_m->get_single_tutorial_with_is_public(['tutorial_id' => $tutorial_id]);
                    if (customCompute($tutorial)) {

                        $file = realpath('uploads/images/' . $lesson->file);
                        if (file_exists($file)) {
                            $expFileName  = explode('.', $file);
                            $originalname = ($lesson->file_original_name) . '.' . end($expFileName);
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename="' . basename($lesson->file_original_name) . '"');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($file));
                            readfile($file);
                            exit;
                        } else {
                            redirect(base_url("tutorial/addlesson/".$tutorial_id));
                        }
                    } else {
                        redirect(base_url("tutorial/addlesson/".$tutorial_id));
                    }
                } else {
                    $this->data["subview"] = "error";
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

    public function lesson_rules()
    {
        $rules = array(
            array(
                'field' => 'title',
                'label' => $this->lang->line("tutorial_title"),
                'rules' => 'trim|required|xss_clean|max_length[255]',
            ),
            array(
                'field' => 'lesson_provider',
                'label' => $this->lang->line("tutorial_lesson_provider"),
                'rules' => 'trim|numeric|xss_clean',
            ),
            array(
                'field' => 'video_url',
                'label' => $this->lang->line("tutorial_video_url"),
                'rules' => 'trim|xss_clean|callback_check_lesson_provider',
            ),
            array(
                'field' => 'duration',
                'label' => $this->lang->line("tutorial_duration"),
                'rules' => 'trim|xss_clean|callback_check_lesson_provider',
            ),
            array(
                'field' => 'video_file',
                'label' => $this->lang->line("tutorial_file"),
                'rules' => 'trim|xss_clean|callback_lesson_fileupload',
            ),
            array(
                'field' => 'file',
                'label' => $this->lang->line("tutorial_file"),
                'rules' => 'trim|xss_clean|callback_lesson_VideoFileUpload',
            ),
            array(
                'field' => 'description',
                'label' => $this->lang->line("tutorial_description"),
                'rules' => 'trim|xss_clean',
            ),
        );
        return $rules;
    }

    public function lesson_VideoFileUpload()
    {
        ini_set('memory_limit', $this->_memoryLimit);
        ini_set('post_max_size', $this->_post_max_size);
        ini_set('upload_max_filesize', $this->_upload_max_filesize);

        $lesson_id = htmlentities(escapeString($this->uri->segment(4)));
        $lesson    = [];
        if ((int) $lesson_id) {
            $lesson = $this->lesson_m->get_single_lesson(['lesson_id' => $lesson_id]);
        }

        $new_file           = '';
        $file_original_name = '';
        if ($_FILES["video_file"]['name'] != "") {
            $file_name          = $_FILES["video_file"]['name'];
            $file_original_name = $file_name;
            $random             = random19();
            $makeRandom         = hash('sha512', $random . $this->input->post('title') . config_item("encryption_key"));
            $file_name_rename   = $makeRandom;
            $explode            = explode('.', $file_name);
            if (customCompute($explode) >= 2) {
                $new_file                = $file_name_rename . '.' . end($explode);
                $config['upload_path']   = "./uploads/videos";
                $config['allowed_types'] = "mp4|avi|3gp|mov|mpeg";
                $config['file_name']     = $new_file;
                $config['max_size']      = '20480000';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload("video_file")) {
                    $this->form_validation->set_message("lesson_fileupload", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['video_file']                       = $this->upload->data();
                    $this->upload_data['video_file']['file_original_name'] = $file_original_name;
                    return true;
                }
            } else {
                $this->form_validation->set_message("lesson_fileupload", "Invalid file");
                return false;
            }
        } else {
            if (customCompute($lesson)) {
                $this->upload_data['video_file']                       = array('file_name' => $lesson->video_file);
                $this->upload_data['video_file']['file_original_name'] = $lesson->video_file_original_name;
                return true;
            } else {
                $this->upload_data['video_file']                       = array('file_name' => $new_file);
                $this->upload_data['video_file']['file_original_name'] = $file_original_name;
                return true;
            }
        }
    }

    public function lesson_fileupload()
    {
        $lesson_id = htmlentities(escapeString($this->uri->segment(4)));
        $lesson    = [];
        if ((int) $lesson_id) {
            $lesson = $this->lesson_m->get_single_lesson(['lesson_id' => $lesson_id]);
        }

        $new_file           = '';
        $file_original_name = '';
        if ($_FILES["file"]['name'] != "") {
            $file_name          = $_FILES["file"]['name'];
            $file_original_name = $file_name;
            $random             = random19();
            $makeRandom         = hash('sha512', $random . $this->input->post('title') . config_item("encryption_key"));
            $file_name_rename   = $makeRandom;
            $explode            = explode('.', $file_name);
            if (customCompute($explode) >= 2) {
                $new_file                = $file_name_rename . '.' . end($explode);
                $config['upload_path']   = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv|zip";
                $config['file_name']     = $new_file;
                $config['max_size']      = '200024';
                $config['max_width']     = '3000';
                $config['max_height']    = '3000';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload("file")) {
                    $this->form_validation->set_message("lesson_fileupload", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['file']                       = $this->upload->data();
                    $this->upload_data['file']['file_original_name'] = $file_original_name;
                    return true;
                }
            } else {
                $this->form_validation->set_message("lesson_fileupload", "Invalid file");
                return false;
            }
        } else {
            if (customCompute($lesson)) {
                $this->upload_data['file']                       = array('file_name' => $lesson->file);
                $this->upload_data['file']['file_original_name'] = $lesson->file_original_name;
                return true;
            } else {
                $this->upload_data['file']                       = array('file_name' => $new_file);
                $this->upload_data['file']['file_original_name'] = $file_original_name;
                return true;
            }
        }
    }

    public function unique_data($data)
    {
        if ($data == 0) {
            $this->form_validation->set_message("unique_data", "The %s field is required");
            return false;
        }
        return true;
    }

    public function check_lesson_provider($data)
    {
        $lesson = $this->input->post('lesson_provider');
        if ((int) $lesson!=15 && (int) $lesson && $data == '') {
            if ($this->input->post('video_url') == '') {
                $this->form_validation->set_message("check_lesson_provider", "The %s field is required");
                return false;
            }
            if ($this->input->post('duration') == '') {
                $this->form_validation->set_message("check_lesson_provider", "The %s field is required");
                return false;
            }
        }

        if ($lesson == 0) {
            if ($this->input->post('video_url') != '') {
                $this->form_validation->set_message("check_lesson_provider", "The lesson provider field is required");
                return false;
            }
            if ($this->input->post('duration') != '') {
                $this->form_validation->set_message("check_lesson_provider", "The lesson provider field is required");
                return false;
            }
        }

        return true;
    }

}
