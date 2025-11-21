<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

    class Signin_m extends MY_Model
    {
        public function __construct()
        {
            parent::__construct();
            $this->load->model("setting_m");
            $this->load->model('usertype_m');
            $this->load->model('loginlog_m');
            $this->load->model('user_m');
        }

        public function change_password()
        {
            $username     = $this->session->userdata("username");
            $old_password = $this->input->post('old_password');
            $new_password = $this->hash($this->input->post('new_password'));

            $user = $this->user_m->get_user_for_signin($username, $old_password);
            if (customCompute($user)) {
                $array = [ "password" => $new_password ];
                $this->db->where([ "username" => $username, "password" => $this->hash($old_password) ]);
                $this->db->update($user->user_table, $array);
                return true;
            }
            return false;
        }

        public function signout()
        {
            $this->session->sess_destroy();
        }

        public function loggedin()
        {
            return (bool) $this->session->userdata("loggedin");
        }
    }
