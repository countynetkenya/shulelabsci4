<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MenuOverridesSeeder
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI = get_instance();
    }

    /**
     * @param array $options
     * @return bool
     */
    public function run(array $options = [])
    {
        $CI = $this->CI;

        if (!$CI->db->table_exists('menu_overrides')) {
            log_message('debug', 'MenuOverridesSeeder skipped because the menu_overrides table does not exist.');
            return true;
        }

        require_once APPPATH . 'libraries/SidebarRegistry.php';

        $pages = SidebarRegistry::syncableItems();
        if (!is_array($pages) || empty($pages)) {
            log_message('debug', 'MenuOverridesSeeder found no sidebar items flagged for sync.');
            return true;
        }

        $CI->load->model('menu_override_m');

        $created = 0;
        $updated = 0;
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

            $existing = $CI->db->get_where('menu_overrides', ['link' => $link])->row();
            if ($existing) {
                $CI->menu_override_m->update_override($payload, $existing->menuOverrideID);
                $updated++;
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
                $legacyRow = $CI->db->get_where('menu_overrides', ['link' => $legacyLink])->row();
                if ($legacyRow) {
                    $CI->menu_override_m->update_override($payload, $legacyRow->menuOverrideID);
                    $updatedLegacy = true;
                    $updated++;
                    break;
                }
            }

            if ($updatedLegacy) {
                continue;
            }

            $CI->menu_override_m->insert_override($payload);
            $created++;
        }

        if ($created === 0) {
            log_message('debug', 'MenuOverridesSeeder did not create any new menu_overrides rows.');
        } else {
            log_message('info', sprintf('MenuOverridesSeeder created %d menu_overrides rows.', $created));
        }

        if ($updated > 0) {
            log_message('info', sprintf('MenuOverridesSeeder updated %d existing menu_overrides rows.', $updated));
        }

        return true;
    }
}
