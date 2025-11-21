<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permission extends Admin_Controller {
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
	private $ignoredModules = array('sociallink', 'posts_categories', 'posts', 'pages', 'frontendmenu', 'admin', 'backup', 'frontend_setting', 'emailsetting', 'school');

	function __construct() {
		parent::__construct();
		$this->load->model("permission_m");
		$this->load->model("usertype_m");
		$this->load->model("menu_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('permission', $language);
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);

		$schoolID = $this->session->userdata('schoolID');
 		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$usertype = $this->usertype_m->get_usertype($id);
			if(customCompute($usertype)) {
				$this->data['set'] = $id;
				$this->data['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID));
				$permissions = $this->permission_m->get_modules_with_permission(array('id' => $id, 'schoolID' => $schoolID));
				if(!customCompute($permissions)) {
					$this->data['permissions'] = NULL;
				} else {
					$this->data['permissions'] = $permissions;
				}
				$this->preparePermissionViewData();
				$this->data["subview"] = "permission/index";
				$this->load->view('_layout_main', $this->data);
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data['usertypes'] = $this->usertype_m->get_order_by_usertype_with_or(array('schoolID' => $schoolID));
			$this->preparePermissionViewData();
			$this->data["subview"] = "permission/index";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function permission_list() {
		$usertypeID = $this->input->post('usertypeID');
		if((int)$usertypeID) {
			$string = base_url("permission/index/$usertypeID");
			echo $string;
		} else {
			redirect(base_url("permission/index"));
		}
	}

	public function save() {
		$this->session->userdata('usertype');
		$usertypeID = $this->uri->segment(3);
		if ((int)$usertypeID) {
			$usertype = $this->usertype_m->get_usertype($usertypeID);
			if(customCompute($usertype)) {
				$schoolID = $this->session->userdata('schoolID');
				if ($this->permission_m->delete_all_permission(array('usertype_id' => $usertypeID, 'schoolID' => $schoolID))) {
					foreach ($_POST as $key => $value) {
						$array = array();
						$array['permission_id'] = $value;
						$array['usertype_id'] = $usertypeID;
						$array['schoolID'] = $schoolID;
						$this->permission_m->insert_relation($array);
					}
					redirect(base_url('permission/index/'.$usertypeID),'refresh');
				} else {
					redirect(base_url('permission/index/'.$usertypeID),'refresh');
				}
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			redirect(base_url('permission/index/'.$usertypeID),'refresh');
		}
	}

	private function preparePermissionViewData() {
		$permissions = isset($this->data['permissions']) ? $this->data['permissions'] : array();
		if(!customCompute($permissions)) {
			$this->data['groupedPermissions'] = array();
			$this->data['permissionActions'] = array();
			return;
		}

		$menuItems = $this->menu_m->get_order_by_menu();
		$menuLookup = array();
		foreach($menuItems as $menuItem) {
			$menuLookup[$menuItem->menuID] = $menuItem;
		}

		$ungroupedLabel = $this->lang->line('permission_ungrouped');
		if(!$ungroupedLabel || $ungroupedLabel === 'permission_ungrouped') {
			$ungroupedLabel = 'Ungrouped';
		}

		$moduleMeta = array();
		foreach($menuItems as $menuItem) {
			$moduleKey = $this->extractModuleKey($menuItem->link);
			if(!$moduleKey) {
				continue;
			}

			$topMenu = $menuItem;
			while($topMenu->parentID && isset($menuLookup[$topMenu->parentID])) {
				$topMenu = $menuLookup[$topMenu->parentID];
			}

			$sectionName = trim($topMenu->menuName) !== '' ? $topMenu->menuName : $ungroupedLabel;
			$moduleMeta[$moduleKey] = array(
				'section' => $sectionName,
				'sectionPriority' => isset($topMenu->priority) ? (int)$topMenu->priority : PHP_INT_MAX,
				'modulePriority' => isset($menuItem->priority) ? (int)$menuItem->priority : PHP_INT_MAX,
			);
		}

		$permissionIndex = array();
		foreach($permissions as $permission) {
			$permissionIndex[$permission->name] = $permission;
		}

		$modules = array();
		$permissionActions = array();
		foreach($permissions as $permission) {
			$name = $permission->name;
			$baseName = $name;
			$actionName = NULL;
			$underscorePos = strrpos($name, '_');
			if($underscorePos !== FALSE) {
				$baseCandidate = substr($name, 0, $underscorePos);
				if(isset($permissionIndex[$baseCandidate])) {
					$baseName = $baseCandidate;
					$actionName = substr($name, $underscorePos + 1);
				}
			}

			if(in_array($baseName, $this->ignoredModules)) {
				continue;
			}

			if(!isset($modules[$baseName])) {
				$modules[$baseName] = array(
					'module' => NULL,
					'actions' => array(),
					'meta' => isset($moduleMeta[$baseName]) ? $moduleMeta[$baseName] : array(
						'section' => $ungroupedLabel,
						'sectionPriority' => PHP_INT_MAX,
						'modulePriority' => PHP_INT_MAX,
					),
				);
			}

			if($actionName === NULL) {
				$modules[$baseName]['module'] = array(
					'permissionID' => $permission->permissionID,
					'name' => $name,
					'description' => $permission->description,
					'active' => $permission->active,
				);
			} else {
				$modules[$baseName]['actions'][$actionName] = array(
					'permissionID' => $permission->permissionID,
					'name' => $name,
					'description' => $permission->description,
					'active' => $permission->active,
				);
				if(!in_array($actionName, $permissionActions, TRUE)) {
					$permissionActions[] = $actionName;
				}
			}
		}

		$defaultActionOrder = array('add', 'edit', 'delete', 'view');
		usort($permissionActions, function($a, $b) use ($defaultActionOrder) {
			$aIndex = array_search($a, $defaultActionOrder, TRUE);
			$bIndex = array_search($b, $defaultActionOrder, TRUE);
			if($aIndex !== FALSE && $bIndex !== FALSE) {
				return $aIndex - $bIndex;
			}
			if($aIndex !== FALSE) {
				return -1;
			}
			if($bIndex !== FALSE) {
				return 1;
			}
			return strcasecmp($a, $b);
		});

		$groupedPermissions = array();
		foreach($modules as $moduleKey => $moduleData) {
			if(!$moduleData['module'] && !count($moduleData['actions'])) {
				continue;
			}

			$meta = $moduleData['meta'];
			$sectionName = isset($meta['section']) ? $meta['section'] : $ungroupedLabel;
			if(!isset($groupedPermissions[$sectionName])) {
				$groupedPermissions[$sectionName] = array(
					'priority' => isset($meta['sectionPriority']) ? $meta['sectionPriority'] : PHP_INT_MAX,
					'modules' => array(),
				);
			} else {
				$groupedPermissions[$sectionName]['priority'] = min($groupedPermissions[$sectionName]['priority'], isset($meta['sectionPriority']) ? $meta['sectionPriority'] : PHP_INT_MAX);
			}

			$groupedPermissions[$sectionName]['modules'][$moduleKey] = $moduleData;
		}

		uasort($groupedPermissions, function($a, $b) {
			$aPriority = isset($a['priority']) ? $a['priority'] : PHP_INT_MAX;
			$bPriority = isset($b['priority']) ? $b['priority'] : PHP_INT_MAX;
			if($aPriority == $bPriority) {
				return 0;
			}
			return ($aPriority < $bPriority) ? -1 : 1;
		});

		foreach($groupedPermissions as &$section) {
			uasort($section['modules'], function($a, $b) {
				$aPriority = isset($a['meta']['modulePriority']) ? $a['meta']['modulePriority'] : PHP_INT_MAX;
				$bPriority = isset($b['meta']['modulePriority']) ? $b['meta']['modulePriority'] : PHP_INT_MAX;
				if($aPriority == $bPriority) {
					$aLabel = isset($a['module']['description']) && $a['module']['description'] ? $a['module']['description'] : (isset($a['module']['name']) ? $a['module']['name'] : '');
					$bLabel = isset($b['module']['description']) && $b['module']['description'] ? $b['module']['description'] : (isset($b['module']['name']) ? $b['module']['name'] : '');
					return strcasecmp($aLabel, $bLabel);
				}
				return ($aPriority < $bPriority) ? -1 : 1;
			});
		}
		unset($section);

		$this->data['groupedPermissions'] = $groupedPermissions;
		$this->data['permissionActions'] = $permissionActions;
	}

	private function extractModuleKey($link) {
		if(!$link || $link === '#') {
			return '';
		}

		$cleanLink = trim($link, '/');
		if($cleanLink === '') {
			return '';
		}

		$segments = explode('/', $cleanLink);
		$module = array_shift($segments);
		$module = strtolower(str_replace('-', '_', $module));
		return $module;
	}
}
