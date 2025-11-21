<?php

class Menuoverrides extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('menu_override_m');
        $this->load->model('menu_m');
        $this->lang->load('menuoverrides', $this->session->userdata('lang'));
    }

    public function index()
    {
        $this->data['headerassets'] = $this->build_header_assets(false);

        $this->data['overrides'] = $this->menu_override_m->get_menu_overrides();
        $this->data['grouped_overrides'] = $this->menu_override_m->get_grouped_overrides();
        $this->data['subview'] = 'menuoverrides/index';
        $this->load->view('_layout_main', $this->data);
    }

    public function add()
    {
        $this->data['headerassets'] = $this->build_header_assets(true);

        $this->data['override'] = $this->default_form_values();
        $this->data['form_mode'] = 'add';
        $this->data['override_id'] = null;
        $this->prepare_form_dependencies();

        if ($_POST) {
            $overrideType = $this->input->post('override_type');
            $rules = $this->rules($overrideType);
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data['subview'] = 'menuoverrides/form';
                $this->load->view('_layout_main', $this->data);
                return;
            }

            $payload = $this->collect_payload_from_post();
            $this->menu_override_m->insert_override($payload);
            $this->flush_menu_cache();

            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
            redirect(base_url('menuoverrides/index'));
        }

        $this->data['subview'] = 'menuoverrides/form';
        $this->load->view('_layout_main', $this->data);
    }

    public function edit()
    {
        $this->data['headerassets'] = $this->build_header_assets(true);

        $id = htmlentities(escapeString($this->uri->segment(3)));
        if ((int) $id === 0) {
            $this->session->set_flashdata('error', $this->lang->line('menuoverrides_invalid_identifier'));
            redirect(base_url('menuoverrides/index'));
        }

        $override = $this->menu_override_m->get_single_override($id);
        if (!$override) {
            $this->session->set_flashdata('error', $this->lang->line('menuoverrides_not_found'));
            redirect(base_url('menuoverrides/index'));
        }

        $this->data['override'] = $this->map_override_to_form($override);
        $this->data['form_mode'] = 'edit';
        $this->data['override_id'] = $id;
        $this->prepare_form_dependencies();

        if ($_POST) {
            $overrideType = $this->input->post('override_type');
            $rules = $this->rules($overrideType);
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data['subview'] = 'menuoverrides/form';
                $this->load->view('_layout_main', $this->data);
                return;
            }

            $payload = $this->collect_payload_from_post();
            $this->menu_override_m->update_override($payload, $id);
            $this->flush_menu_cache();

            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
            redirect(base_url('menuoverrides/index'));
        }

        $this->data['subview'] = 'menuoverrides/form';
        $this->load->view('_layout_main', $this->data);
    }

    public function delete()
    {
        $id = htmlentities(escapeString($this->uri->segment(3)));
        if ((int) $id === 0) {
            $this->session->set_flashdata('error', $this->lang->line('menuoverrides_invalid_identifier'));
            redirect(base_url('menuoverrides/index'));
        }

        $override = $this->menu_override_m->get_single_override($id);
        if (!$override) {
            $this->session->set_flashdata('error', $this->lang->line('menuoverrides_not_found'));
            redirect(base_url('menuoverrides/index'));
        }

        $this->menu_override_m->delete_override($id);
        $this->flush_menu_cache();

        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
        redirect(base_url('menuoverrides/index'));
    }

    protected function rules($type = 'custom')
    {
        $rules = [
            [
                'field' => 'override_type',
                'label' => $this->lang->line('menuoverrides_override_type'),
                'rules' => 'trim|required|xss_clean|in_list[custom,relocation]'
            ],
            [
                'field' => 'menuName',
                'label' => $this->lang->line('menuoverrides_menu_name'),
                'rules' => 'trim|required|xss_clean|max_length[100]'
            ],
            [
                'field' => 'parent',
                'label' => $this->lang->line('menuoverrides_parent'),
                'rules' => 'trim|xss_clean|max_length[100]'
            ],
            [
                'field' => 'priority',
                'label' => $this->lang->line('menuoverrides_priority'),
                'rules' => 'trim|required|numeric'
            ],
            [
                'field' => 'status',
                'label' => $this->lang->line('menuoverrides_status'),
                'rules' => 'trim|required|numeric'
            ],
            [
                'field' => 'skip_permission',
                'label' => $this->lang->line('menuoverrides_skip_permission'),
                'rules' => 'trim|numeric'
            ],
            [
                'field' => 'notes',
                'label' => $this->lang->line('menuoverrides_notes'),
                'rules' => 'trim|xss_clean'
            ],
            [
                'field' => 'create_if_missing_icon',
                'label' => $this->lang->line('menuoverrides_create_if_missing_icon'),
                'rules' => 'trim|xss_clean|max_length[100]'
            ],
            [
                'field' => 'create_if_missing_priority',
                'label' => $this->lang->line('menuoverrides_create_if_missing_priority'),
                'rules' => 'trim|xss_clean|callback__validate_optional_numeric'
            ],
            [
                'field' => 'create_if_missing_status',
                'label' => $this->lang->line('menuoverrides_create_if_missing_status'),
                'rules' => 'trim|xss_clean|callback__validate_optional_status'
            ],
        ];

        if ($type === 'custom') {
            $rules[] = [
                'field' => 'link',
                'label' => $this->lang->line('menuoverrides_link'),
                'rules' => 'trim|required|xss_clean|max_length[255]'
            ];
            $rules[] = [
                'field' => 'icon',
                'label' => $this->lang->line('menuoverrides_icon'),
                'rules' => 'trim|xss_clean|max_length[100]'
            ];
        } else {
            $rules[] = [
                'field' => 'link',
                'label' => $this->lang->line('menuoverrides_link'),
                'rules' => 'trim|xss_clean|max_length[255]'
            ];
            $rules[] = [
                'field' => 'icon',
                'label' => $this->lang->line('menuoverrides_icon'),
                'rules' => 'trim|xss_clean|max_length[100]'
            ];
        }

        return $rules;
    }

    private function default_form_values()
    {
        return [
            'override_type' => set_value('override_type', 'custom'),
            'menuName' => set_value('menuName', ''),
            'parent' => set_value('parent', ''),
            'link' => set_value('link', ''),
            'icon' => set_value('icon', ''),
            'priority' => set_value('priority', 0),
            'status' => set_value('status', 1),
            'skip_permission' => set_value('skip_permission', 0),
            'create_if_missing_enabled' => set_value('create_if_missing_enabled', 0),
            'create_if_missing_icon' => set_value('create_if_missing_icon', ''),
            'create_if_missing_priority' => set_value('create_if_missing_priority', ''),
            'create_if_missing_status' => set_value('create_if_missing_status', 1),
            'notes' => set_value('notes', ''),
        ];
    }

    private function map_override_to_form($override)
    {
        $createIfMissing = $this->menu_override_m->decode_create_if_missing($override->create_if_missing);
        $enabled = false;
        $icon = '';
        $priority = '';
        $status = 1;

        if ($createIfMissing === true) {
            $enabled = true;
        } elseif (is_array($createIfMissing) && !empty($createIfMissing)) {
            $enabled = true;
            $icon = isset($createIfMissing['icon']) ? $createIfMissing['icon'] : '';
            $priority = isset($createIfMissing['priority']) ? $createIfMissing['priority'] : '';
            if (isset($createIfMissing['status'])) {
                $status = (int) $createIfMissing['status'] === 1 ? 1 : 0;
            }
        }

        return [
            'override_type' => set_value('override_type', $override->override_type),
            'menuName' => set_value('menuName', $override->menuName),
            'parent' => set_value('parent', $override->parent),
            'link' => set_value('link', $override->link),
            'icon' => set_value('icon', $override->icon),
            'priority' => set_value('priority', $override->priority),
            'status' => set_value('status', $override->status),
            'skip_permission' => set_value('skip_permission', $override->skip_permission),
            'create_if_missing_enabled' => set_value('create_if_missing_enabled', $enabled ? 1 : 0),
            'create_if_missing_icon' => set_value('create_if_missing_icon', $icon),
            'create_if_missing_priority' => set_value('create_if_missing_priority', $priority),
            'create_if_missing_status' => set_value('create_if_missing_status', $status),
            'notes' => set_value('notes', $override->notes),
        ];
    }

    private function collect_payload_from_post()
    {
        $createIfMissingEnabled = (bool) $this->input->post('create_if_missing_enabled');
        $createIfMissing = null;

        if ($createIfMissingEnabled) {
            $details = [];
            $icon = trim((string) $this->input->post('create_if_missing_icon'));
            $priority = $this->input->post('create_if_missing_priority');
            $status = $this->input->post('create_if_missing_status');

            if ($icon !== '') {
                $details['icon'] = $icon;
            }

            if ($priority !== '' && $priority !== null && is_numeric($priority)) {
                $details['priority'] = (int) $priority;
            }

            if ($status !== '' && $status !== null && in_array((string) $status, ['0', '1'], true)) {
                $details['status'] = (int) $status;
            }

            $createIfMissing = !empty($details) ? $details : true;
        }

        return [
            'override_type' => $this->input->post('override_type'),
            'menuName' => $this->input->post('menuName'),
            'parent' => $this->input->post('parent'),
            'link' => $this->input->post('link'),
            'icon' => $this->input->post('icon'),
            'priority' => $this->input->post('priority'),
            'status' => $this->input->post('status'),
            'skip_permission' => $this->input->post('skip_permission'),
            'create_if_missing' => $createIfMissing,
            'notes' => $this->input->post('notes'),
        ];
    }

    public function _validate_optional_numeric($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return true;
        }

        if (!is_numeric($value)) {
            $this->form_validation->set_message('_validate_optional_numeric', $this->lang->line('menuoverrides_error_numeric'));
            return false;
        }

        return true;
    }

    public function _validate_optional_status($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return true;
        }

        if (!in_array($value, ['0', '1', 0, 1], true)) {
            $this->form_validation->set_message('_validate_optional_status', $this->lang->line('menuoverrides_error_status'));
            return false;
        }

        return true;
    }

    private function flush_menu_cache()
    {
        $this->session->unset_userdata('dbMenus');
    }

    private function build_header_assets($withFormAssets = false)
    {
        $assets = [
            'css' => [
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
                'assets/iconpicker/css/iconpicker.css',
            ],
            'js' => [
                'assets/select2/select2.js',
                'assets/iconpicker/js/iconpicker.js',
            ],
        ];

        if ($withFormAssets) {
            $assets['js'][] = 'assets/menuoverrides/form.js';
        }

        return $assets;
    }

    private function prepare_form_dependencies()
    {
        $choices = $this->build_menu_choices();
        $this->data['menu_name_options'] = $choices['menu_names'];
        $this->data['menu_name_translations'] = $choices['menu_translations'];
        $this->data['parent_options'] = $choices['parent_options'];
    }

    private function build_menu_choices()
    {
        $menuNameOptions = [];
        $menuTranslations = [];
        $parentOptions = [];

        $menus = $this->menu_m->get_order_by_menu();
        if (customCompute($menus)) {
            foreach ($menus as $menu) {
                $menuName = $menu->menuName;
                $link = isset($menu->link) ? $menu->link : '';
                $translation = $this->lang->line('menu_' . $menuName);
                if ($translation === false) {
                    $translation = '';
                }

                $menuNameOptions[$menuName] = $this->format_menu_choice_label($menuName, $translation, $link, 'core');
                if ($translation !== '') {
                    $menuTranslations[$menuName] = $translation;
                }

                $parentOptions[$menuName] = $this->format_menu_choice_label($menuName, $translation, $link, 'core');
                if (!empty($link)) {
                    $parentOptions[$link] = $this->format_menu_choice_label($link, $translation, $link, 'link');
                }
            }
        }

        $overrides = $this->menu_override_m->get_menu_overrides();
        if (customCompute($overrides)) {
            foreach ($overrides as $override) {
                $menuName = $override->menuName;
                $link = isset($override->link) ? $override->link : '';
                $translation = $this->lang->line('menu_' . $menuName);
                if ($translation === false) {
                    $translation = '';
                }

                $label = $this->format_menu_choice_label($menuName, $translation, $link, 'override');
                if (!isset($menuNameOptions[$menuName])) {
                    $menuNameOptions[$menuName] = $label;
                }

                if ($translation !== '') {
                    $menuTranslations[$menuName] = $translation;
                }

                if (!isset($parentOptions[$menuName])) {
                    $parentOptions[$menuName] = $label;
                }

                if (!empty($link) && !isset($parentOptions[$link])) {
                    $parentOptions[$link] = $this->format_menu_choice_label($link, $translation, $link, 'override');
                }
            }
        }

        if (!empty($menuNameOptions)) {
            uasort($menuNameOptions, 'strnatcasecmp');
        }

        if (!empty($parentOptions)) {
            uasort($parentOptions, 'strnatcasecmp');
        }

        return [
            'menu_names' => $menuNameOptions,
            'menu_translations' => $menuTranslations,
            'parent_options' => $parentOptions,
        ];
    }

    private function format_menu_choice_label($identifier, $translation = '', $link = '', $origin = 'core')
    {
        $label = $identifier;
        if ($translation !== '') {
            $label .= ' – ' . $translation;
        }
        if ($link !== '' && $link !== $identifier) {
            $label .= ' (' . $link . ')';
        }

        $originLabel = '';
        if ($origin === 'override') {
            $originLabel = $this->lang->line('menuoverrides_option_label_override');
        } elseif ($origin === 'link') {
            $originLabel = $this->lang->line('menuoverrides_option_label_link');
        } else {
            $originLabel = $this->lang->line('menuoverrides_option_label_core');
        }

        if ($originLabel !== '') {
            $label .= ' [' . $originLabel . ']';
        }

        return $label;
    }
}
