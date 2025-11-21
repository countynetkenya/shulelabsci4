<?php defined('BASEPATH') OR exit('No direct script access allowed');

class AuditEventsSeeder
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

        if (!$CI->db->table_exists('audit_events')) {
            log_message('debug', 'AuditEventsSeeder skipped because the audit_events table does not exist.');
            return true;
        }

        $eventKey = 'system.bootstrap';
        $existing = $CI->db->get_where('audit_events', ['event_key' => $eventKey])->row();
        if ($existing) {
            log_message('debug', 'AuditEventsSeeder found an existing bootstrap audit event.');
            return true;
        }

        $now = date('Y-m-d H:i:s');
        $payload = [
            'event_key' => $eventKey,
            'actor_type' => 'system',
            'actor_id' => 'bootstrap',
            'context_type' => null,
            'context_id' => null,
            'description' => 'Initial bootstrap audit event to verify logging pipeline.',
            'metadata' => json_encode(['source' => 'migrate seed']),
            'ip_address' => null,
            'user_agent' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $CI->db->insert('audit_events', $payload);

        log_message('info', 'AuditEventsSeeder inserted the bootstrap audit event.');
        return true;
    }
}
