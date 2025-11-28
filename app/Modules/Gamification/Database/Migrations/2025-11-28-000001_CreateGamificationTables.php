<?php

namespace App\Modules\Gamification\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates Gamification module tables for points, badges, and leaderboards.
 */
class CreateGamificationTables extends Migration
{
    public function up(): void
    {
        // points - Point transactions
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'points' => ['type' => 'INT', 'constraint' => 11],
            'type' => ['type' => 'ENUM', 'constraint' => ['earned', 'spent', 'bonus', 'penalty', 'expired']],
            'source' => ['type' => 'VARCHAR', 'constraint' => 50],
            'source_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'metadata' => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'user_id'], false, false, 'idx_school_user');
        $this->forge->addKey(['user_id', 'created_at'], false, false, 'idx_user_date');
        $this->forge->createTable('points', true);

        // point_balances - Current point balances (materialized for performance)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'total_earned' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_spent' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'current_balance' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'level' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'user_id'], 'uk_school_user');
        $this->forge->createTable('point_balances', true);

        // badges - Badge definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT', 'null' => true],
            'icon' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'color' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'category' => ['type' => 'ENUM', 'constraint' => ['academic', 'attendance', 'behavior', 'sports', 'leadership', 'special']],
            'tier' => ['type' => 'ENUM', 'constraint' => ['bronze', 'silver', 'gold', 'platinum', 'diamond'], 'default' => 'bronze'],
            'points_reward' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'criteria' => ['type' => 'JSON', 'null' => true],
            'is_secret' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->createTable('badges', true);

        // user_badges - Earned badges
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'badge_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'earned_at' => ['type' => 'DATETIME'],
            'awarded_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_featured' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'badge_id'], 'uk_user_badge');
        $this->forge->createTable('user_badges', true);

        // achievements - Achievement definitions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'description' => ['type' => 'TEXT', 'null' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'criteria_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'criteria_value' => ['type' => 'INT', 'constraint' => 11],
            'points_reward' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'badge_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['school_id', 'code'], 'uk_school_code');
        $this->forge->createTable('achievements', true);

        // user_achievements - User achievement progress
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'achievement_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'progress' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_completed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'achievement_id'], 'uk_user_achievement');
        $this->forge->createTable('user_achievements', true);

        // challenges - Time-limited challenges
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'challenge_type' => ['type' => 'ENUM', 'constraint' => ['individual', 'class', 'school']],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'criteria' => ['type' => 'JSON'],
            'start_date' => ['type' => 'DATE'],
            'end_date' => ['type' => 'DATE'],
            'points_reward' => ['type' => 'INT', 'constraint' => 11],
            'max_participants' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['upcoming', 'active', 'completed', 'cancelled'], 'default' => 'upcoming'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'status', 'end_date'], false, false, 'idx_school_status');
        $this->forge->createTable('challenges', true);

        // challenge_participants - Challenge enrollment and progress
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'challenge_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'progress' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'is_completed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'completed_at' => ['type' => 'DATETIME', 'null' => true],
            'rank' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'joined_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['challenge_id', 'user_id'], 'uk_challenge_user');
        $this->forge->createTable('challenge_participants', true);

        // leaderboards - Leaderboard snapshots
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'period_type' => ['type' => 'ENUM', 'constraint' => ['daily', 'weekly', 'monthly', 'term', 'yearly', 'all_time']],
            'period_start' => ['type' => 'DATE'],
            'period_end' => ['type' => 'DATE'],
            'scope' => ['type' => 'ENUM', 'constraint' => ['school', 'class', 'global']],
            'scope_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'rankings' => ['type' => 'JSON'],
            'generated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'period_type', 'scope'], false, false, 'idx_school_period_scope');
        $this->forge->createTable('leaderboards', true);

        // rewards - Redeemable rewards catalog
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'school_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'description' => ['type' => 'TEXT', 'null' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 50],
            'points_cost' => ['type' => 'INT', 'constraint' => 11],
            'quantity_available' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'quantity_redeemed' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'image_url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['school_id', 'is_active'], false, false, 'idx_school_active');
        $this->forge->createTable('rewards', true);

        // reward_redemptions - Redeemed rewards
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'reward_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'points_spent' => ['type' => 'INT', 'constraint' => 11],
            'status' => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'fulfilled', 'cancelled'], 'default' => 'pending'],
            'redeemed_at' => ['type' => 'DATETIME'],
            'fulfilled_at' => ['type' => 'DATETIME', 'null' => true],
            'fulfilled_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status'], false, false, 'idx_user_status');
        $this->forge->createTable('reward_redemptions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('reward_redemptions', true);
        $this->forge->dropTable('rewards', true);
        $this->forge->dropTable('leaderboards', true);
        $this->forge->dropTable('challenge_participants', true);
        $this->forge->dropTable('challenges', true);
        $this->forge->dropTable('user_achievements', true);
        $this->forge->dropTable('achievements', true);
        $this->forge->dropTable('user_badges', true);
        $this->forge->dropTable('badges', true);
        $this->forge->dropTable('point_balances', true);
        $this->forge->dropTable('points', true);
    }
}
