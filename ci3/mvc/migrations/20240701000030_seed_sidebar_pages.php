<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Seed_sidebar_pages extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('menu_overrides')) {
            if (function_exists('log_message')) {
                log_message('debug', 'Skipping admin sidebar seed because menu_overrides table is missing.');
            }
            return;
        }

        require_once APPPATH . 'libraries/SidebarRegistry.php';

        $pages = SidebarRegistry::syncableItems();
        if (!is_array($pages) || empty($pages)) {
            return;
        }

        $this->load->model('menu_override_m');
        $managedLinks = [];

        foreach ($pages as $key => $page) {
            if (!is_array($page)) {
                continue;
            }

            $built = SidebarRegistry::buildOverridePayload($key, $page);
            if ($built === null) {
                continue;
            }
            $link = $built['link'];
            $payload = $built['payload'];

            if (isset($page['controller']) && $page['controller'] !== '') {
                $normalizedController = ltrim(str_replace('\\', '/', trim($page['controller'])), '/');
                $controllerFile = APPPATH . 'controllers/' . $normalizedController . '.php';
                if (!is_file($controllerFile) && function_exists('log_message')) {
                    log_message('debug', sprintf('Seeding menu override for %s while controller %s is missing.', $link, $normalizedController));
                }
            }
            $existing = $this->db->get_where('menu_overrides', ['link' => $link])->row();
            if ($existing) {
                $this->menu_override_m->update_override($payload, $existing->menuOverrideID);
                $managedLinks[] = $link;
                continue;
            }

            $legacyLinks = [];
            if (strpos($link, 'admin/') !== 0) {
                $legacyLinks[] = 'admin/' . $link;
            }
            if (isset($page['legacy_links']) && is_array($page['legacy_links'])) {
                foreach ($page['legacy_links'] as $legacyLink) {
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
                    $updatedLegacy = true;
                    $managedLinks[] = $legacyLink;
                    break;
                }
            }

            if ($updatedLegacy) {
                continue;
            }

            $this->menu_override_m->insert_override($payload);
            $managedLinks[] = $link;
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
            }
        }
    }

    public function down()
    {
        if (!$this->db->table_exists('menu_overrides')) {
            return;
        }

        require_once APPPATH . 'libraries/SidebarRegistry.php';

        $pages = SidebarRegistry::syncableItems();
        if (!is_array($pages) || empty($pages)) {
            return;
        }

        $links = [];
        foreach ($pages as $page) {
            if (is_array($page) && isset($page['link'])) {
                $links[] = ltrim($page['link'], '/');
            }
        }

        if (empty($links)) {
            return;
        }

        $this->db->where_in('link', $links)->delete('menu_overrides');
    }
}
