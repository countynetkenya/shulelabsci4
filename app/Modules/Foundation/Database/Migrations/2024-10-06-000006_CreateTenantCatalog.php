<?php

namespace Modules\Foundation\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantCatalog extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'VARCHAR', 'constraint' => 64],
            'tenant_type' => ['type' => 'VARCHAR', 'constraint' => 32],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 191],
            'metadata'    => ['type' => 'LONGTEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME'],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey(['id', 'tenant_type'], true);
        $this->forge->addKey('tenant_type');
        $this->forge->createTable('ci4_tenant_catalog', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci4_tenant_catalog', true);
    }
}
