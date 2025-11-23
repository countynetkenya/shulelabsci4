<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Backfill CI4 Users from CI3 Tables.
 *
 * This migration backfills the users and user_roles tables
 * from existing CI3 user tables (systemadmin, user, teacher, student, parents).
 *
 * It is idempotent - safe to run multiple times, as it checks for existing data.
 */
class BackfillCi4UsersFromCi3 extends Migration
{
    /**
     * CI3 table mappings with their configuration.
     */
    private array $ci3Tables = [
        'systemadmin' => [
            'id_field' => 'systemadminID',
            'default_usertype_id' => 0, // Super admin by default
        ],
        'user' => [
            'id_field' => 'userID',
            'default_usertype_id' => 1, // Admin by default
        ],
        'teacher' => [
            'id_field' => 'teacherID',
            'default_usertype_id' => 2,
        ],
        'student' => [
            'id_field' => 'studentID',
            'default_usertype_id' => 3,
        ],
        'parents' => [
            'id_field' => 'parentsID',
            'default_usertype_id' => 4,
        ],
    ];

    public function up()
    {
        // Check if users already has data
        $existingUsers = $this->db->table('users')->countAllResults();

        if ($existingUsers > 0) {
            echo "CI4 users table already has {$existingUsers} records. Skipping backfill to prevent duplicates.\n";
            echo "If you want to re-run the backfill, please truncate users and user_roles first.\n";
            return;
        }

        echo "Starting CI4 users backfill from CI3 tables...\n";

        $totalMigrated = 0;

        foreach ($this->ci3Tables as $tableName => $config) {
            $migrated = $this->backfillFromTable($tableName, $config);
            $totalMigrated += $migrated;
            echo "Migrated {$migrated} users from {$tableName}\n";
        }

        echo "Total users migrated: {$totalMigrated}\n";
    }

    public function down()
    {
        // Optionally clear CI4 users that were backfilled
        // Uncomment if you want rollback to remove data
        // $this->db->table('user_roles')->truncate();
        // $this->db->table('users')->truncate();

        echo "Backfill rollback: Data preserved. To remove, manually truncate users and user_roles.\n";
    }

    /**
     * Backfill users from a specific CI3 table.
     *
     * @param string $tableName The CI3 table name
     * @param array $config Table configuration
     * @return int Number of users migrated
     */
    private function backfillFromTable(string $tableName, array $config): int
    {
        // Check if table exists
        if (!$this->db->tableExists($tableName)) {
            echo "Table {$tableName} does not exist, skipping.\n";
            return 0;
        }

        $idField = $config['id_field'];
        $defaultUsertypeId = $config['default_usertype_id'];

        // Get all active users from the CI3 table
        $builder = $this->db->table($tableName);
        $users = $builder->get()->getResult();

        if (empty($users)) {
            return 0;
        }

        $migratedCount = 0;

        foreach ($users as $user) {
            try {
                // Prepare user data for users
                $ci4UserData = [
                    'username' => $user->username ?? '',
                    'email' => $user->email ?? null,
                    'password_hash' => $user->password ?? '',
                    'full_name' => $user->name ?? '',
                    'photo' => $user->photo ?? null,
                    'schoolID' => $this->extractSchoolID($user),
                    'ci3_user_id' => $user->{$idField},
                    'ci3_user_table' => $tableName,
                    'is_active' => isset($user->active) ? (int) $user->active : 1,
                    'created_at' => $this->extractCreatedAt($user),
                    'updated_at' => $this->extractUpdatedAt($user),
                ];

                // Skip if username is empty
                if (empty($ci4UserData['username'])) {
                    echo "Skipping user from {$tableName} with empty username (ID: {$user->{$idField}})\n";
                    continue;
                }

                // Check for duplicate username
                $existingUser = $this->db->table('users')
                    ->where('username', $ci4UserData['username'])
                    ->get()
                    ->getRow();

                if ($existingUser) {
                    echo "Skipping duplicate username: {$ci4UserData['username']} from {$tableName}\n";
                    continue;
                }

                // Insert into users
                $this->db->table('users')->insert($ci4UserData);
                $newUserId = $this->db->insertID();

                // Determine role based on usertypeID if present, otherwise use default
                $usertypeId = isset($user->usertypeID) ? (int) $user->usertypeID : $defaultUsertypeId;

                // Get the role ID for this usertype
                $role = $this->db->table('roles')
                    ->where('ci3_usertype_id', $usertypeId)
                    ->get()
                    ->getRow();

                if ($role) {
                    // Insert user-role mapping
                    $this->db->table('user_roles')->insert([
                        'user_id' => $newUserId,
                        'role_id' => $role->id,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                $migratedCount++;
            } catch (\Exception $e) {
                echo "Error migrating user from {$tableName} (ID: {$user->{$idField}}): {$e->getMessage()}\n";
                continue;
            }
        }

        return $migratedCount;
    }

    /**
     * Extract schoolID from user object.
     *
     * @param object $user
     * @return string|null
     */
    private function extractSchoolID(object $user): ?string
    {
        if (isset($user->schoolID)) {
            return (string) $user->schoolID;
        }

        // For students, might be a single school based on their class
        // For now, return null if not set
        return null;
    }

    /**
     * Extract created_at timestamp.
     *
     * @param object $user
     * @return string|null
     */
    private function extractCreatedAt(object $user): ?string
    {
        if (isset($user->create_date)) {
            return $user->create_date;
        }

        return date('Y-m-d H:i:s');
    }

    /**
     * Extract updated_at timestamp.
     *
     * @param object $user
     * @return string|null
     */
    private function extractUpdatedAt(object $user): ?string
    {
        if (isset($user->modify_date)) {
            return $user->modify_date;
        }

        if (isset($user->create_date)) {
            return $user->create_date;
        }

        return date('Y-m-d H:i:s');
    }
}
