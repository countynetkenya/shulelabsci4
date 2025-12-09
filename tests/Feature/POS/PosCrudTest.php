<?php

namespace Tests\Feature\POS;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\TenantTestTrait;

/**
 * PosCrudTest - Feature tests for POS Product CRUD operations.
 *
 * Tests all CRUD endpoints for the POS module:
 * - GET /pos (index)
 * - GET /pos/create (create form)
 * - POST /pos/store (create action)
 * - GET /pos/edit/{id} (edit form)
 * - POST /pos/update/{id} (update action)
 * - GET /pos/delete/{id} (delete action)
 */
class PosCrudTest extends CIUnitTestCase
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
     * Test: Index page displays products.
     */
    public function testIndexDisplaysProducts()
    {
        // Seed a test product
        $this->db->table('pos_products')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Test Product',
            'description' => 'Test Description',
            'price'       => 500.00,
            'stock'       => 10,
            'sku'         => 'TEST-001',
            'category'    => 'Test Category',
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('pos');

        $result->assertOK();
        $result->assertSee('Test Product');
        $result->assertSee('500');
    }

    /**
     * Test: Index page shows empty state when no products.
     */
    public function testIndexShowsEmptyState()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('pos');

        $result->assertOK();
        $result->assertSee('No products found');
    }

    /**
     * Test: Create page displays form.
     */
    public function testCreatePageDisplaysForm()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->get('pos/create');

        $result->assertOK();
        $result->assertSee('Add POS Product');
        $result->assertSee('Product Name');
        $result->assertSee('Price');
    }

    /**
     * Test: Store creates a new product.
     */
    public function testStoreCreatesProduct()
    {
        $data = [
            'name'        => 'New Product',
            'description' => 'Product Description',
            'price'       => 750.50,
            'stock'       => 20,
            'sku'         => 'NEW-001',
            'category'    => 'New Category',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('pos/store', $data);

        $result->assertRedirectTo('/pos');
        $result->assertSessionHas('message');

        // Verify database
        $this->seeInDatabase('pos_products', [
            'school_id' => $this->schoolId,
            'name'      => 'New Product',
            'price'     => 750.50,
        ]);
    }

    /**
     * Test: Store validates required fields.
     */
    public function testStoreValidatesRequiredFields()
    {
        $result = $this->withSession($this->getAdminSession())
                       ->post('pos/store', []);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
    }

    /**
     * Test: Edit page displays form with product data.
     */
    public function testEditPageDisplaysProduct()
    {
        // Seed a test product
        $this->db->table('pos_products')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Edit Test Product',
            'price'       => 300.00,
            'stock'       => 5,
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $productId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get('pos/edit/' . $productId);

        $result->assertOK();
        $result->assertSee('Edit POS Product');
        $result->assertSee('Edit Test Product');
        $result->assertSee('300');
    }

    /**
     * Test: Update modifies existing product.
     */
    public function testUpdateModifiesProduct()
    {
        // Seed a test product
        $this->db->table('pos_products')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Old Name',
            'price'       => 100.00,
            'stock'       => 1,
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $productId = $this->db->insertID();

        $data = [
            'name'        => 'Updated Name',
            'price'       => 200.00,
            'stock'       => 2,
            'description' => 'Updated Description',
        ];

        $result = $this->withSession($this->getAdminSession())
                       ->post('pos/update/' . $productId, $data);

        $result->assertRedirectTo('/pos');
        $result->assertSessionHas('message');

        // Verify database
        $this->seeInDatabase('pos_products', [
            'id'    => $productId,
            'name'  => 'Updated Name',
            'price' => 200.00,
        ]);
    }

    /**
     * Test: Delete removes product.
     */
    public function testDeleteRemovesProduct()
    {
        // Seed a test product
        $this->db->table('pos_products')->insert([
            'school_id'   => $this->schoolId,
            'name'        => 'Delete Me',
            'price'       => 50.00,
            'stock'       => 1,
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $productId = $this->db->insertID();

        $result = $this->withSession($this->getAdminSession())
                       ->get('pos/delete/' . $productId);

        $result->assertRedirectTo('/pos');
        $result->assertSessionHas('message');

        // Verify product is deleted
        $this->dontSeeInDatabase('pos_products', [
            'id' => $productId,
        ]);
    }

    /**
     * Test: Tenant scoping - cannot access other school's products.
     */
    public function testTenantScopingPreventsAccessToOtherSchools()
    {
        // Create product for a different school
        $this->db->table('pos_products')->insert([
            'school_id'   => 999,
            'name'        => 'Other School Product',
            'price'       => 100.00,
            'stock'       => 1,
            'is_active'   => 1,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $result = $this->withSession($this->getAdminSession())
                       ->get('pos');

        $result->assertOK();
        $result->assertDontSee('Other School Product');
    }

    /**
     * Test: Access control - redirects when not logged in.
     */
    public function testAccessControlRedirectsWhenNotLoggedIn()
    {
        $result = $this->get('pos');
        $result->assertRedirectTo('/login');
    }
}
