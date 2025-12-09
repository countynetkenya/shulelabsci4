<?php

namespace Tests\Feature\Wallets;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * WalletCrudTest - Feature tests for Wallets CRUD operations
 */
class WalletCrudTest extends CIUnitTestCase
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

    public function testIndexDisplaysWallets()
    {
        // Seed a wallet
        $this->db->table('wallets')->insert([
            'school_id'   => $this->schoolId,
            'user_id'     => $this->userId,
            'wallet_type' => 'student',
            'balance'     => 1000.00,
            'currency'    => 'KES',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('wallets');

        $result->assertOK();
        $result->assertSee('Wallets');
        $result->assertSee('1000');
    }

    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('wallets');

        $result->assertOK();
        $result->assertSee('No wallets found');
    }

    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('wallets/create');

        $result->assertOK();
        $result->assertSee('Create Wallet');
        $result->assertSee('Wallet Type');
    }

    public function testStoreCreatesWallet()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('wallets/store', [
                           'user_id'     => $this->userId,
                           'wallet_type' => 'student',
                           'currency'    => 'KES',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/wallets');
        
        $this->seeInDatabase('wallets', [
            'user_id'     => $this->userId,
            'wallet_type' => 'student',
            'school_id'   => $this->schoolId,
        ]);
    }

    public function testStoreValidationFailsWithMissingFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('wallets/store', [
                           'user_id'     => '',
                           'wallet_type' => '',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirect();
    }

    public function testEditPageDisplaysWalletData()
    {
        // Seed a wallet
        $this->db->table('wallets')->insert([
            'school_id'   => $this->schoolId,
            'user_id'     => $this->userId,
            'wallet_type' => 'parent',
            'balance'     => 2500.00,
            'currency'    => 'KES',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $walletId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("wallets/edit/{$walletId}");

        $result->assertOK();
        $result->assertSee('Edit Wallet');
        $result->assertSee('2500');
    }

    public function testEditPageRedirectsForNonExistent()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('wallets/edit/99999');

        $result->assertRedirectTo('/wallets');
    }

    public function testUpdateModifiesWallet()
    {
        // Seed a wallet
        $this->db->table('wallets')->insert([
            'school_id'   => $this->schoolId,
            'user_id'     => $this->userId,
            'wallet_type' => 'student',
            'balance'     => 1500.00,
            'currency'    => 'KES',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $walletId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->post("wallets/update/{$walletId}", [
                           'user_id'     => $this->userId,
                           'wallet_type' => 'staff',
                           'status'      => 'suspended',
                           csrf_token()  => csrf_hash(),
                       ]);

        $result->assertRedirectTo('/wallets');
        
        $this->seeInDatabase('wallets', [
            'id'          => $walletId,
            'wallet_type' => 'staff',
            'status'      => 'suspended',
        ]);
    }

    public function testDeleteRemovesWallet()
    {
        // Seed a wallet
        $this->db->table('wallets')->insert([
            'school_id'   => $this->schoolId,
            'user_id'     => $this->userId,
            'wallet_type' => 'student',
            'balance'     => 500.00,
            'currency'    => 'KES',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $walletId = $this->db->insertID();

        $this->seeInDatabase('wallets', ['id' => $walletId]);

        $result = $this->withSession($this->getAdminSession())
                       ->get("wallets/delete/{$walletId}");

        $result->assertRedirectTo('/wallets');
        
        $this->dontSeeInDatabase('wallets', ['id' => $walletId]);
    }

    public function testDeleteNonExistentRedirects()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('wallets/delete/99999');

        $result->assertRedirectTo('/wallets');
    }

    public function testCannotAccessOtherSchoolWallets()
    {
        // Create wallet for different school
        $this->db->table('wallets')->insert([
            'school_id'   => 99999,
            'user_id'     => 999,
            'wallet_type' => 'student',
            'balance'     => 3000.00,
            'currency'    => 'KES',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $otherWalletId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get("wallets/edit/{$otherWalletId}");

        $result->assertRedirectTo('/wallets');
    }
}
