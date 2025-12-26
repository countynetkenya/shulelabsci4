<?php

namespace Tests\Feature\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * DatabaseCrudTest - Feature tests for Database Backup CRUD operations
 */
class DatabaseCrudTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    use TenantTestTrait;

    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTenantContext();
    }

    /**
     * Test: Index page displays backups
     */
    public function testIndexDisplaysBackups()
    {
        // Seed a test backup
        $this->db->table('db_backups')->insert([
            'school_id' => $this->schoolId,
            'backup_id' => 'backup_test_001',
            'name' => 'Test Backup',
            'path' => '/backups/test/backup.sql',
            'size' => 1024000,
            'status' => 'completed',
            'type' => 'full',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('database');

        $result->assertOK();
        $result->assertSee('Test Backup');
        $result->assertSee('backup_test_001');
    }

    /**
     * Test: Index page shows empty state when no backups
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('database');

        $result->assertOK();
        $result->assertSee('No backups found');
    }

    /**
     * Test: Create page displays form
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('database/create');

        $result->assertOK();
        $result->assertSee('Backup Name');
        $result->assertSee('Backup Type');
    }

    /**
     * Test: Store creates a new backup
     */
    public function testStoreCreatesBackup()
    {
        $data = [
            'name' => 'New Test Backup',
            'type' => 'full',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('database/store', $data);

        $result->assertRedirectTo('/database');
        
        $backup = $this->db->table('db_backups')
                          ->where('school_id', $this->schoolId)
                          ->where('name', 'New Test Backup')
                          ->get()
                          ->getRowArray();
        
        $this->assertNotNull($backup);
        $this->assertEquals('full', $backup['type']);
        $this->assertEquals('pending', $backup['status']);
    }

    /**
     * Test: Store validation fails with missing data
     */
    public function testStoreValidationFails()
    {
        $data = [
            'name' => '', // Empty name should fail
            'type' => 'full',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('database/store', $data);

        $result->assertRedirect();
        $this->assertTrue(session()->has('errors'));
    }

    /**
     * Test: Edit page displays backup
     */
    public function testEditPageDisplaysBackup()
    {
        $backupId = $this->db->table('db_backups')->insert([
            'school_id' => $this->schoolId,
            'backup_id' => 'backup_edit_001',
            'name' => 'Edit Test Backup',
            'path' => '/backups/test/edit.sql',
            'size' => 2048000,
            'status' => 'completed',
            'type' => 'incremental',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('database/edit/' . $backupId);

        $result->assertOK();
        $result->assertSee('Edit Test Backup');
        $result->assertSee('backup_edit_001');
    }

    /**
     * Test: Update modifies existing backup
     */
    public function testUpdateModifiesBackup()
    {
        $backupId = $this->db->table('db_backups')->insert([
            'school_id' => $this->schoolId,
            'backup_id' => 'backup_update_001',
            'name' => 'Original Name',
            'path' => '/backups/test/update.sql',
            'size' => 3072000,
            'status' => 'pending',
            'type' => 'full',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $data = [
            'name' => 'Updated Name',
            'status' => 'completed',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('database/update/' . $backupId, $data);

        $result->assertRedirectTo('/database');
        
        $backup = $this->db->table('db_backups')
                          ->where('id', $backupId)
                          ->get()
                          ->getRowArray();
        
        $this->assertEquals('Updated Name', $backup['name']);
        $this->assertEquals('completed', $backup['status']);
    }

    /**
     * Test: Delete removes backup
     */
    public function testDeleteRemovesBackup()
    {
        $backupId = $this->db->table('db_backups')->insert([
            'school_id' => $this->schoolId,
            'backup_id' => 'backup_delete_001',
            'name' => 'Delete Test Backup',
            'path' => '/backups/test/delete.sql',
            'size' => 4096000,
            'status' => 'failed',
            'type' => 'differential',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('database/delete/' . $backupId);

        $result->assertRedirectTo('/database');
        
        $backup = $this->db->table('db_backups')
                          ->where('id', $backupId)
                          ->get()
                          ->getRowArray();
        
        $this->assertNull($backup);
    }

    /**
     * Test: Tenant isolation - cannot see other school's backups
     */
    public function testTenantIsolation()
    {
        // Create backup for another school
        $this->db->table('db_backups')->insert([
            'school_id' => 999,
            'backup_id' => 'backup_other_001',
            'name' => 'Other School Backup',
            'path' => '/backups/999/backup.sql',
            'size' => 5120000,
            'status' => 'completed',
            'type' => 'full',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('database');

        $result->assertOK();
        $result->assertDontSee('Other School Backup');
    }
}
