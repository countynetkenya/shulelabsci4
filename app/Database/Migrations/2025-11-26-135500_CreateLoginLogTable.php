<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginLogTable extends Migration
{
    public function up()
    {
        // Create CI3-compatible loginlog table
        $this->forge->addField([
            'loginlogID' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'userID'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'usertypeID' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'ip'         => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'browser'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'login'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'logout'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('loginlogID', true);
        $this->forge->createTable('loginlog', true);
    }

    public function down()
    {
        $this->forge->dropTable('loginlog', true);
    }
}
