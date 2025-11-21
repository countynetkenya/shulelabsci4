<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
use OpenApi\Annotations as OA;

class School extends Api_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_m');
        $this->load->model('school_m');
    }

    /**
       * @OA\Get(
       *     path="/api/v10/school",
       *     @OA\Response(
       *         response="200",
       *         description="Success"
       *     )
       * )
       */
    public function index_get()
    {
        $setting            = $this->setting_m->get_setting(0);
        $array = [];
        if ( customCompute($setting) ) {
            $array['sitename']  = $setting->sname;
            $array['logo']      = $setting->photo;
            $array['phone']     = $setting->phone;
            $array['email']     = $setting->email;
            $array['address']   = $setting->address;
            $array['copyright'] = $setting->footer;
        }

        $user = $this->user_m->get_user_info($this->session->userdata('usertypeID'), $this->session->userdata('loginuserID'));
        $this->retdata['schools'] = $this->school_m->get_school_wherein(explode(",", $user->schoolID));
        $this->retdata['siteinfo'] = $array;

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    /**
 * @OA\Post(
 *   path="/api/v10/school/select",
 *   summary="Select School",
 *   description="Select a school to fetch data",
 *   @OA\RequestBody(
 *       required=true,
 *       @OA\MediaType(
 *           mediaType="application/json",
 *           @OA\Schema(
 *               type="object",
 *               @OA\Property(
 *                   property="school",
 *                   description="School ID",
 *                   type="string",
 *                   example="1"
 *               ),
 *           )
 *       )
 *   ),
 *  @OA\Response(
 *         response="200",
 *         description="Success"
 *     ),
 * )
 */
    public function select_post()
    {
        $schoolID = $this->post('school');
        // updated selected school
        $tables   = [
            'user'        => 'user',
            'systemadmin' => 'systemadmin',
        ];

        foreach($tables as $table) {
            $tableID       = $table . 'ID';
            $this->db->where($tableID, $this->session->userdata('loginuserID'));
            $this->db->update($table, array('selected_schoolID' => $schoolID));
        }

        if($schoolID != null) {
            $setting  = $this->setting_m->get_setting($schoolID);
            $sessionArray = [
                  'loginuserID'         	=> $this->session->userdata('loginuserID'),
                  'name'                	=> $this->session->userdata('name'),
                  'email'               	=> $this->session->userdata('email'),
                  'usertypeID'          	=> $this->session->userdata('usertypeID'),
                  'usertype'            	=> $this->session->userdata('usertype'),
                  'username'              => $this->session->userdata('username'),
                  'password'           	  => $this->session->userdata('password'),
                  'photo'               	=> $this->session->userdata('photo'),
                  'lang'               	  => $setting->language,
                  'defaultschoolyearID' 	=> $setting->school_year,
                  "loggedin"            	=> true,
                  "varifyvaliduser"       => true,
                  "schoolID"              => $schoolID,
              ];

            $this->session->set_userdata($sessionArray);

            if($this->session->userdata('loginuserID')) {
                $features   = $this->permission_m->get_modules_with_permission(array('id' => $this->session->userdata('usertypeID'), 'schoolID' => $schoolID));
                foreach ($features as $feature) {
                    $permissionSet['master_permission_set'][$feature->name] = $feature->active;
                }

                if($this->session->userdata('usertypeID') == 3) {
                    $permissionSet['master_permission_set']['take_exam'] = 'yes';
                }
                $this->session->unset_userdata('master_permission_set');
                $this->session->set_userdata($permissionSet);
            }

            $this->retdata['profile'] = $sessionArray;

            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => 'logged out'
        ], REST_Controller::HTTP_OK);
    }

}
