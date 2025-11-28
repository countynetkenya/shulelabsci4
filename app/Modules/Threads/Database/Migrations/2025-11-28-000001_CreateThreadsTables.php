<?php

namespace App\Modules\Threads\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates enhanced Threads module tables for messaging and notifications.
 */
class CreateThreadsTables extends Migration
{
    public function up(): void
    {
        // threads - Conversation threads
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 255],
            'thread_type' => ['type' => 'ENUM', 'constraint' => ['direct', 'group', 'announcement', 'support']],
            'context_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'context_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'is_archived' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'last_message_at' => ['type' => 'DATETIME', 'null' => true],
            'message_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'thread_type'], false, false, 'idx_school_type');
        $this->forge->addKey(['context_type', 'context_id'], false, false, 'idx_context');
        $this->forge->addKey('last_message_at', false, false, 'idx_last_message');
        $this->forge->createTable('threads', true);

        // thread_participants - Users in a thread
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'thread_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'role' => ['type' => 'ENUM', 'constraint' => ['owner', 'admin', 'member', 'readonly'], 'default' => 'member'],
            'last_read_at' => ['type' => 'DATETIME', 'null' => true],
            'unread_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_muted' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'joined_at' => ['type' => 'DATETIME', 'null' => true],
            'left_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['thread_id', 'user_id'], 'uk_thread_user');
        $this->forge->addKey('user_id', false, false, 'idx_user');
        $this->forge->createTable('thread_participants', true);

        // thread_messages - Individual messages
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'thread_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'sender_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'content' => ['type' => 'TEXT'],
            'content_type' => ['type' => 'ENUM', 'constraint' => ['text', 'html', 'markdown'], 'default' => 'text'],
            'reply_to_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'attachments' => ['type' => 'JSON', 'null' => true],
            'metadata' => ['type' => 'JSON', 'null' => true],
            'is_system_message' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_edited' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'edited_at' => ['type' => 'DATETIME', 'null' => true],
            'is_deleted' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['thread_id', 'created_at'], false, false, 'idx_thread_date');
        $this->forge->addKey('sender_id', false, false, 'idx_sender');
        $this->forge->createTable('thread_messages', true);

        // message_read_receipts - Read status tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'message_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'read_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['message_id', 'user_id'], 'uk_message_user');
        $this->forge->createTable('message_read_receipts', true);

        // announcements - School/class announcements
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'content' => ['type' => 'TEXT'],
            'content_type' => ['type' => 'ENUM', 'constraint' => ['text', 'html', 'markdown'], 'default' => 'text'],
            'scope' => ['type' => 'ENUM', 'constraint' => ['school', 'class', 'department', 'role', 'custom']],
            'scope_ids' => ['type' => 'JSON', 'null' => true],
            'priority' => ['type' => 'ENUM', 'constraint' => ['low', 'normal', 'high', 'urgent'], 'default' => 'normal'],
            'attachments' => ['type' => 'JSON', 'null' => true],
            'publish_at' => ['type' => 'DATETIME', 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'is_pinned' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'requires_acknowledgment' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'scheduled', 'published', 'archived'], 'default' => 'draft'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status', 'publish_at'], false, false, 'idx_school_status');
        $this->forge->addKey(['scope', 'status'], false, false, 'idx_scope_status');
        $this->forge->createTable('announcements', true);

        // announcement_acknowledgments - Acknowledgment tracking
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'announcement_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'acknowledged_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['announcement_id', 'user_id'], 'uk_announcement_user');
        $this->forge->createTable('announcement_acknowledgments', true);

        // notification_preferences - User notification settings
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'channel' => ['type' => 'ENUM', 'constraint' => ['email', 'sms', 'push', 'in_app']],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'is_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'quiet_hours_start' => ['type' => 'TIME', 'null' => true],
            'quiet_hours_end' => ['type' => 'TIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'channel', 'category'], 'uk_user_channel_category');
        $this->forge->createTable('notification_preferences', true);

        // notification_queue - Pending notifications
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'channel' => ['type' => 'ENUM', 'constraint' => ['email', 'sms', 'push', 'in_app']],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'body' => ['type' => 'TEXT'],
            'data' => ['type' => 'JSON', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'sent', 'failed', 'cancelled'], 'default' => 'pending'],
            'scheduled_at' => ['type' => 'DATETIME', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'error_message' => ['type' => 'TEXT', 'null' => true],
            'retry_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'scheduled_at'], false, false, 'idx_status_scheduled');
        $this->forge->addKey(['user_id', 'status'], false, false, 'idx_user_status');
        $this->forge->createTable('notification_queue', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('notification_queue', true);
        $this->forge->dropTable('notification_preferences', true);
        $this->forge->dropTable('announcement_acknowledgments', true);
        $this->forge->dropTable('announcements', true);
        $this->forge->dropTable('message_read_receipts', true);
        $this->forge->dropTable('thread_messages', true);
        $this->forge->dropTable('thread_participants', true);
        $this->forge->dropTable('threads', true);
    }
}
