<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu extends Admin_Controller {
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
	private $permissionRelationshipHasSchool = NULL;

	function __construct() {
		parent::__construct();
		$this->load->model("menu_m");
		if(config_item('demo') == FALSE || ENVIRONMENT == 'production') {
			redirect('dashboard/index');
		}
	}

	public function index() {
		$this->data['menus'] = $this->menu_m->get_order_by_menu();
		$this->data["subview"] = "menu/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'menuName',
				'label' => 'Menu Name',
				'rules' => 'trim|required|xss_clean|max_length[120]'
			),
			array(
				'field' => 'parentID',
				'label' => 'Parent',
				'rules' => 'trim|numeric|max_length[11]|xss_clean'
			),
			array(
				'field' => 'link',
				'label' => 'Link',
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'icon',
				'label' => 'Icon',
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'status',
				'label' => 'Status',
				'rules' => 'trim|numeric|xss_clean'
			),
			array(
				'field' => 'priority',
				'label' => 'Priority',
				'rules' => 'trim|numeric|max_length[200]|xss_clean'
			),
            array(
				'field' => 'pullRight',
				'label' => 'Pull Right',
				'rules' => 'trim|max_length[200]|xss_clean'
			)
		);
		return $rules;
	}

	public function add() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);

        $this->data['menus'] = $this->menu_m->get_order_by_menu();

		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "menu/add";
				$this->load->view('_layout_main', $this->data);
			} else {
				$postData = array_filter($this->input->post());
				$this->menu_m->insert_menu($postData);
				$this->resetMenuCache();
				$this->registerMenuPermissions($postData);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("menu/index"));
			}
		} else {
			$this->data["subview"] = "menu/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->data['menu'] = $this->menu_m->get_menu($id);
            // dd($this->data['menu']);
            $this->data['menus'] = $this->menu_m->get_order_by_menu();
			if($this->data['menu']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "menu/edit";
						$this->load->view('_layout_main', $this->data);
					} else {
                        $postData = $this->input->post();
                        $postData['status'] = (int) $postData['status'];
						$this->menu_m->update_menu($postData, $id);
						$this->resetMenuCache();
						$this->registerMenuPermissions($postData);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("menu/index"));
					}
				} else {
					$this->data["subview"] = "menu/edit";
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

	public function delete() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->menu_m->delete_menu($id);
			$this->resetMenuCache();
			$this->session->set_flashdata('success', $this->lang->line('menu_success'));
			redirect(base_url("menu/index"));
		} else {
			redirect(base_url("menu/index"));
		}
	}

    public function menuList()
    {
        $menus = json_decode(json_encode(pluck($this->menu_m->get_order_by_menu(), 'obj', 'menuID')), true);
        dd($this->menuTrees($menus));
    }


    public function menuTrees($dataset) {
    	$tree = array();
    	foreach ($dataset as $id=>&$node) {
    		if ($node['parentID'] == 0) {
    			$tree[$id]=&$node;
    		} else {
    			if (!isset($dataset[$node['parentID']]['child']))
    				$dataset[$node['parentID']]['child'] = array();
    			$dataset[$node['parentID']]['child'][$id] = &$node;
    		}
    	}
    	return $tree;
    }
	private function resetMenuCache() {
		$this->session->unset_userdata('dbMenus');
	}

	private function registerMenuPermissions($menuData) {
		if(!is_array($menuData)) {
			return;
		}

		if(!isset($menuData['link'])) {
			return;
		}

		$link = trim($menuData['link']);
		if($link === '' || $link === '#') {
			return;
		}

		$module = $this->extractModuleFromLink($link);
		if($module === '') {
			return;
		}

		$this->load->model('permission_m');

		if(isset($menuData['menuName']) && $menuData['menuName']) {
			$baseDescription = $menuData['menuName'];
		} else {
			$baseDescription = ucwords(str_replace('_', ' ', $module));
		}

		$expectedPermissions = array(
			$module => $baseDescription,
			$module . '_add' => $baseDescription . ' Add',
			$module . '_edit' => $baseDescription . ' Edit',
			$module . '_delete' => $baseDescription . ' Delete',
			$module . '_view' => $baseDescription . ' View',
		);

		$newPermissionIDs = array();
		foreach ($expectedPermissions as $permissionName => $description) {
			$existing = $this->permission_m->get_order_by_permission(array('name' => $permissionName));
			if(!customCompute($existing)) {
				$newPermissionIDs[] = $this->permission_m->insert_permission(array(
					'name' => $permissionName,
					'description' => $description,
					'active' => 'yes'
				));
			}
		}

		if(customCompute($newPermissionIDs)) {
			$this->seedSuperAdminPermissions($newPermissionIDs);
		}
	}

	private function seedSuperAdminPermissions($permissionIDs) {
		if(!customCompute($permissionIDs)) {
			return;
		}

		$this->load->model('permission_m');

		$relationships = array();
		$hasSchoolColumn = $this->permissionRelationshipSupportsSchool();
		$schoolID = $this->session->userdata('schoolID');

		foreach ($permissionIDs as $permissionID) {
			if(!$permissionID) {
				continue;
			}

			$row = array(
				'permission_id' => $permissionID,
				'usertype_id' => 1,
			);

			if($hasSchoolColumn && $schoolID) {
				$row['schoolID'] = $schoolID;
			}

			$relationships[] = $row;
		}

		if(customCompute($relationships)) {
			$this->permission_m->insert_batch_permission_relationships($relationships);
		}
	}

	private function permissionRelationshipSupportsSchool() {
		if($this->permissionRelationshipHasSchool === NULL) {
			$this->permissionRelationshipHasSchool = $this->db->field_exists('schoolID', 'permission_relationships');
		}

		return $this->permissionRelationshipHasSchool;
	}

	private function extractModuleFromLink($link) {
		if(!$link || $link === '#') {
			return '';
		}

		$cleanLink = trim($link);
		if(strpos($cleanLink, '://') !== FALSE) {
			return '';
		}

		$cleanLink = trim($cleanLink, '/');
		if($cleanLink === '') {
			return '';
		}

		if(stripos($cleanLink, 'javascript:') === 0) {
			return '';
		}

		$segments = explode('/', $cleanLink);
		$module = strtolower(str_replace('-', '_', reset($segments)));
		return $module;
	}
}
