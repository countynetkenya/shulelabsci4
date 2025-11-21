<?php

namespace App\Services;

use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
use Modules\Foundation\Services\AuditService;

/**
 * User Migration Service
 *
 * Handles automatic migration of users from legacy CI3 tables to ci4_users
 * during the authentication process.
 */
class UserMigrationService
{
    /**
     * CI3 table mappings with their configuration
     */
    private array $ci3Tables = [
        'systemadmin' => [
            'id_field' => 'systemadminID',
            'default_usertype_id' => 0, // Super admin
            'name_field' => 'name',
        ],
        'user' => [
            'id_field' => 'userID',
            'default_usertype_id' => 1, // Admin
            'name_field' => 'name',
        ],
        'teacher' => [
            'id_field' => 'teacherID',
            'default_usertype_id' => 2,
            'name_field' => 'name',
        ],
        'student' => [
            'id_field' => 'studentID',
            'default_usertype_id' => 3,
            'name_field' => 'name',
        ],
        'parents' => [
            'id_field' => 'parentsID',
            'default_usertype_id' => 4,
            'name_field' => 'name',
        ],
    ];

    protected BaseConnection $db;
    protected UserModel $userModel;
    protected AuditService $auditService;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->userModel = new UserModel();
        $this->auditService = new AuditService($this->db);
    }

    /**
     * Find user in CI3 tables and backfill to ci4_users if found
     *
     * @param string $username
     * @return object|null User object if found and migrated, null otherwise
     */
    public function findAndMigrateUser(string $username): ?object
    {
        log_message('debug', "UserMigrationService::findAndMigrateUser() - Searching for user: {$username}");

        // Search each CI3 table
        foreach ($this->ci3Tables as $tableName => $config) {
            $ci3User = $this->findUserInCi3Table($tableName, $username);

            if ($ci3User) {
                log_message('info', "UserMigrationService::findAndMigrateUser() - Found user in {$tableName}, attempting migration");
                return $this->migrateUser($ci3User, $tableName, $config);
            }
        }

        log_message('debug', "UserMigrationService::findAndMigrateUser() - User not found in any CI3 table: {$username}");
        return null;
    }

    /**
     * Find user in a specific CI3 table
     *
     * @param string $tableName
     * @param string $username
     * @return object|null
     */
    protected function findUserInCi3Table(string $tableName, string $username): ?object
    {
        // Check if table exists
        if (!$this->db->tableExists($tableName)) {
            log_message('debug', "UserMigrationService::findUserInCi3Table() - Table {$tableName} does not exist");
            return null;
        }

        try {
            $user = $this->db->table($tableName)
                ->where('username', $username)
                ->get()
                ->getRow();

            return $user ?: null;
        } catch (\Exception $e) {
            log_message('error', "UserMigrationService::findUserInCi3Table() - Error querying {$tableName}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Migrate a CI3 user to ci4_users
     *
     * @param object $ci3User
     * @param string $tableName
     * @param array $config
     * @return object|null Migrated user object from ci4_users, null on failure
     */
    protected function migrateUser(object $ci3User, string $tableName, array $config): ?object
    {
        $idField = $config['id_field'];
        $nameField = $config['name_field'];
        $defaultUsertypeId = $config['default_usertype_id'];

        try {
            $this->db->transStart();

            // Prepare user data for ci4_users
            $ci4UserData = [
                'username' => $ci3User->username ?? '',
                'email' => $ci3User->email ?? null,
                'password_hash' => $ci3User->password ?? '',
                'full_name' => $ci3User->{$nameField} ?? '',
                'photo' => $ci3User->photo ?? null,
                'schoolID' => $this->extractSchoolID($ci3User),
                'ci3_user_id' => $ci3User->{$idField},
                'ci3_user_table' => $tableName,
                'is_active' => isset($ci3User->active) ? (int)$ci3User->active : 1,
                'created_at' => $this->extractCreatedAt($ci3User),
                'updated_at' => $this->extractUpdatedAt($ci3User),
            ];

            // Insert into ci4_users
            $this->db->table('ci4_users')->insert($ci4UserData);
            $newUserId = $this->db->insertID();

            // Determine role based on usertypeID if present, otherwise use default
            $usertypeId = isset($ci3User->usertypeID) ? (int)$ci3User->usertypeID : $defaultUsertypeId;

            // Get the role ID for this usertype
            $role = $this->db->table('ci4_roles')
                ->where('ci3_usertype_id', $usertypeId)
                ->get()
                ->getRow();

            if ($role) {
                // Insert user-role mapping
                $this->db->table('ci4_user_roles')->insert([
                    'user_id' => $newUserId,
                    'role_id' => $role->id,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $this->db->transComplete();

            if (!$this->db->transStatus()) {
                log_message('error', "UserMigrationService::migrateUser() - Transaction failed for user: {$ci3User->username}");
                return null;
            }

            // Log the migration in audit trail
            $this->logMigration($newUserId, $ci3User, $tableName);

            log_message('info', "UserMigrationService::migrateUser() - Successfully migrated user {$ci3User->username} from {$tableName} to ci4_users (new ID: {$newUserId})");

            // Fetch and return the newly created user
            return $this->userModel->find($newUserId);

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', "UserMigrationService::migrateUser() - Error migrating user from {$tableName}: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Log user migration in audit trail
     *
     * @param int $newUserId
     * @param object $ci3User
     * @param string $tableName
     */
    protected function logMigration(int $newUserId, object $ci3User, string $tableName): void
    {
        try {
            $this->auditService->recordEvent(
                eventKey: "user.migrated.{$newUserId}",
                eventType: 'user_migrated_from_ci3',
                context: [
                    'tenant_id' => session()->get('schoolID') ?? null,
                    'actor_id' => 'system',
                ],
                before: null,
                after: [
                    'ci4_user_id' => $newUserId,
                    'ci3_user_table' => $tableName,
                    'ci3_user_id' => property_exists($ci3User, 'id') ? $ci3User->id : null,
                    'username' => $ci3User->username ?? null,
                ],
                metadata: [
                    'migration_timestamp' => date('Y-m-d H:i:s'),
                    'migration_source' => 'automatic_signin',
                ]
            );
        } catch (\Exception $e) {
            log_message('error', "UserMigrationService::logMigration() - Failed to log migration: {$e->getMessage()}");
        }
    }

    /**
     * Extract schoolID from CI3 user object
     *
     * @param object $user
     * @return string|null
     */
    protected function extractSchoolID(object $user): ?string
    {
        if (isset($user->schoolID)) {
            return (string)$user->schoolID;
        }

        // For students, might be derived from their class
        // For now, return null if not set
        return null;
    }

    /**
     * Extract created_at timestamp
     *
     * @param object $user
     * @return string
     */
    protected function extractCreatedAt(object $user): string
    {
        if (isset($user->create_date)) {
            return $user->create_date;
        }

        if (isset($user->created_at)) {
            return $user->created_at;
        }

        return date('Y-m-d H:i:s');
    }

    /**
     * Extract updated_at timestamp
     *
     * @param object $user
     * @return string
     */
    protected function extractUpdatedAt(object $user): string
    {
        if (isset($user->modify_date)) {
            return $user->modify_date;
        }

        if (isset($user->updated_at)) {
            return $user->updated_at;
        }

        if (isset($user->create_date)) {
            return $user->create_date;
        }

        return date('Y-m-d H:i:s');
    }
}
