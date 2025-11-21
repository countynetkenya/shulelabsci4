<?php if ( !defined('BASEPATH') ) {
    exit('No direct script access allowed');
}

    class User_m extends MY_Model
    {

        protected $_table_name = 'user';
        protected $_primary_key = 'userID';
        protected $_primary_filter = 'intval';
        protected $_order_by = "usertypeID";

        public function __construct()
        {
            parent::__construct();
        }

      	private function prefixLoad($array) {
      		if(is_array($array)) {
      			if(customCompute($array)) {
      				foreach ($array as $arkey =>  $ar) {
      					if($arkey == "userID" || $arkey == "schoolID") {
      						unset($array[$arkey]);
      						$array['user.'.$arkey] = $ar;
      					}
      				}
      			}
      		}

      		return $array;
      	}

        public function get_username( $table, $data = null )
        {
            $query = $this->db->get_where($table, $data);
            return $query->result();
        }

        public function get_user_by_usertype( $array = null )
        {
            $array = $this->prefixLoad($array);
            $this->db->select('user.*, usertype.usertype');
            $this->db->from('user');
            $this->db->join('usertype', 'usertype.usertypeID = user.usertypeID', 'LEFT');
            $this->db->where("FIND_IN_SET(".$array['user.schoolID'].",user.schoolID) >", 0);
            unset($array['user.schoolID']);
            $this->db->where($array);
            $query = $this->db->get();
            if ( $array['user.userID'] ) {
                return $query->row();
            } else {
                return $query->result();
            }
        }

        public function get_user( $array = null, $signal = false )
        {
            $query = parent::get($array, $signal);
            return $query;
        }

        public function get_order_by_user( $array = null )
        {
            $query = parent::get_order_by($array);
            return $query;
        }

        public function get_single_user( $array )
        {
            return parent::get_single($array);
        }

        public function get_select_user( $select = null, $array = [] )
        {
            if ( $select == null ) {
                $select = 'userID, usertypeID, name, photo';
            }

            $this->db->select($select);
            $this->db->from($this->_table_name);

            if ( customCompute($array) ) {
                $this->db->where($array);
            }

            $query = $this->db->get();
            return $query->result();
        }

        public function insert_user( $array )
        {
            return parent::insert($array);
        }

        public function update_user( $data, $id = null )
        {
            parent::update($data, $id);
            return $id;
        }

        public function delete_user( $id )
        {
            parent::delete($id);
        }

        public function hash( $string )
        {
            return parent::hash($string);
        }

        public function get_user_info($usertypeID, $userID)
        {
            if ( $usertypeID == 1 ) {
                $table = "systemadmin";
            } elseif ( $usertypeID == 2 ) {
                $table = "teacher";
            } elseif ( $usertypeID == 3 ) {
                $table = 'student';
            } elseif ( $usertypeID == 4 ) {
                $table = 'parents';
            } else {
                $table = 'user';
            }

            $query = $this->db->get_where($table, [ $table . 'ID' => $userID ]);
            return $query->row();
        }

        public function get_user_table($table, $username, $password)
        {
            $query = $this->db->get_where($table, [ 'username' => $username, 'password' => $this->hash($password) ]);
            return $query->row();
        }

        public function get_user_for_signin($username, $password) {
            $password = $this->hash($password);
            $sql = "
                SELECT studentID as `userID`, `name`, `email`, `usertypeID`, `username`, `photo`, `schoolID`, `active`, 'student' as `user_table` FROM `student` WHERE `username` = ? AND `password` = ?
                UNION ALL
                SELECT parentsID as `userID`, `name`, `email`, `usertypeID`, `username`, `photo`, `schoolID`, `active`, 'parents' as `user_table` FROM `parents` WHERE `username` = ? AND `password` = ?
                UNION ALL
                SELECT teacherID as `userID`, `name`, `email`, `usertypeID`, `username`, `photo`, `schoolID`, `active`, 'teacher' as `user_table` FROM `teacher` WHERE `username` = ? AND `password` = ?
                UNION ALL
                SELECT userID as `userID`, `name`, `email`, `usertypeID`, `username`, `photo`, `schoolID`, `active`, 'user' as `user_table` FROM `user` WHERE `username` = ? AND `password` = ?
                UNION ALL
                SELECT systemadminID as `userID`, `name`, `email`, `usertypeID`, `username`, `photo`, `schoolID`, `active`, 'systemadmin' as `user_table` FROM `systemadmin` WHERE `username` = ? AND `password` = ?
            ";
            $query = $this->db->query($sql, array($username, $password, $username, $password, $username, $password, $username, $password,  $username, $password));
            return $query->row();
        }


        public function get_all_user($table, $data=NULL)
        {
            $query = $this->db->get_where($table, $data);
            return $query->result();
        }
    }
