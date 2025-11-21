<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sidebar extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->input->is_cli_request()) {
            show_error('The sidebar tools controller is CLI-only.', 403);
        }

        $this->load->database();
        $this->load->model('menu_override_m');

        require_once APPPATH . 'libraries/SidebarRegistry.php';
    }

    public function sync()
    {
        $items = SidebarRegistry::syncableItems();
        if (empty($items)) {
            echo "No sidebar items flagged for sync.\n";
            return;
        }

        $this->db->trans_start();

        $created = 0;
        $updated = 0;
        $retired = 0;
        $managedLinks = [];

        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            $built = SidebarRegistry::buildOverridePayload($key, $item);
            if ($built === null) {
                continue;
            }
            $link = $built['link'];
            $payload = $built['payload'];

            $managedLinks[] = $link;
            $existing = $this->db->get_where('menu_overrides', ['link' => $link])->row();
            if ($existing) {
                $this->menu_override_m->update_override($payload, $existing->menuOverrideID);
                $updated++;
                echo sprintf("updated %s\n", $link);
                continue;
            }

            $legacyLinks = [];
            if (strpos($link, 'admin/') !== 0) {
                $legacyLinks[] = 'admin/' . $link;
            }
            if (isset($item['legacy_links']) && is_array($item['legacy_links'])) {
                foreach ($item['legacy_links'] as $legacyLink) {
                    $legacyLink = ltrim($legacyLink, '/');
                    if ($legacyLink !== '' && !in_array($legacyLink, $legacyLinks, true)) {
                        $legacyLinks[] = $legacyLink;
                    }
                }
            }

            $updatedLegacy = false;
            foreach ($legacyLinks as $legacyLink) {
                $legacyRow = $this->db->get_where('menu_overrides', ['link' => $legacyLink])->row();
                if ($legacyRow) {
                    $this->menu_override_m->update_override($payload, $legacyRow->menuOverrideID);
                    $managedLinks[] = $legacyLink;
                    $updatedLegacy = true;
                    $updated++;
                    echo sprintf("repointed %s -> %s\n", $legacyLink, $link);
                    break;
                }
            }

            if ($updatedLegacy) {
                continue;
            }

            $this->menu_override_m->insert_override($payload);
            $created++;
            echo sprintf("created %s\n", $link);
        }

        if (!empty($managedLinks)) {
            $managedLinks = array_unique($managedLinks);
            $query = $this->db->select('menuOverrideID, link, notes')->from('menu_overrides')->get();
            foreach ($query->result() as $row) {
                if (in_array($row->link, $managedLinks, true)) {
                    continue;
                }

                $notes = [];
                if (!empty($row->notes)) {
                    $decoded = json_decode($row->notes, true);
                    if (is_array($decoded)) {
                        $notes = $decoded;
                    }
                }

                if (!isset($notes['managed_by']) || $notes['managed_by'] !== 'sidebar_config') {
                    continue;
                }

                $notes['retired_at'] = date('c');
                $payload = [
                    'status' => 0,
                    'notes' => json_encode($notes),
                ];
                $this->menu_override_m->update_override($payload, $row->menuOverrideID);
                $retired++;
                echo sprintf("retired %s\n", $row->link);
            }
        }

        $this->db->trans_complete();

        echo sprintf("Summary: %d created, %d updated, %d retired\n", $created, $updated, $retired);
    }

    public function audit()
    {
        $items = SidebarRegistry::items();
        $issues = 0;

        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                continue;
            }

            $permission = isset($item['permission']) ? $item['permission'] : (isset($item['permission_key']) ? $item['permission_key'] : null);
            if ($permission && !$this->permissionExists($permission)) {
                $issues++;
                echo sprintf("missing permission mapping: %s (%s)\n", $key, $permission);
            }

            $controller = isset($item['controller']) ? $item['controller'] : null;
            if ($controller) {
                $normalized = ltrim(str_replace('\\', '/', $controller), '/');
                $controllerPath = APPPATH . 'controllers/' . $normalized . '.php';
                if (!is_file($controllerPath)) {
                    $issues++;
                    echo sprintf("controller missing: %s (%s)\n", $key, $controller);
                }
            }
        }

        if ($issues === 0) {
            echo "Audit complete: no issues found.\n";
        } else {
            echo sprintf("Audit complete: %d issue(s) found.\n", $issues);
        }
    }

    public function checkviews()
    {
        $viewsDir = APPPATH . 'views';
        if (!is_dir($viewsDir)) {
            echo "views directory missing\n";
            return;
        }

        $items = SidebarRegistry::items();
        $knownLinks = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $link = isset($item['link']) ? ltrim($item['link'], '/') : null;
            if ($link) {
                $knownLinks[] = $link;
            }
        }
        $knownLinks = array_unique($knownLinks);

        $ignoredDirectories = ['layouts', 'partials', 'report', 'errors', 'email', 'components'];
        $missing = [];

        $iterator = new DirectoryIterator($viewsDir);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }

            $dirname = $fileinfo->getFilename();
            if (in_array($dirname, $ignoredDirectories, true)) {
                continue;
            }

            if ($dirname[0] === '_') {
                continue;
            }

            $expected = $dirname;
            if (!in_array($expected, $knownLinks, true)) {
                $missing[] = $dirname;
            }
        }

        if (empty($missing)) {
            echo "All top-level view directories have matching sidebar entries.\n";
            return;
        }

        echo "Directories without sidebar/menu entries:\n";
        foreach ($missing as $dirname) {
            echo " - {$dirname}\n";
        }
    }

    protected function permissionExists($permission)
    {
        if (!$this->db->table_exists('permissions')) {
            return true;
        }

        $candidates = [$permission];
        if (strpos($permission, '.') !== false) {
            $candidates[] = str_replace('.', '_', $permission);
        }

        $columns = $this->db->list_fields('permissions');
        $matchColumns = [];
        if (in_array('name', $columns, true)) {
            $matchColumns[] = 'name';
        }
        if (in_array('permission', $columns, true)) {
            $matchColumns[] = 'permission';
        }

        if (empty($matchColumns)) {
            return true;
        }

        foreach ($candidates as $candidate) {
            foreach ($matchColumns as $column) {
                $row = $this->db->limit(1)->get_where('permissions', [$column => $candidate])->row();
                if ($row) {
                    return true;
                }
            }
        }

        return false;
    }
}
