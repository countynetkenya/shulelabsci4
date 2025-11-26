<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ResetUnprefixedAuthTables extends Migration
{
    public function up()
    {
        // Drop CI4 prefixed tables if they exist
        foreach (['ci4_user_roles','ci4_roles','ci4_users'] as $t) {
            if ($this->db->tableExists($t)) {
                $this->forge->dropTable($t, true);
            }
        }

        // Create roles
        $this->forge->addField([
            'id'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'role_name'   => ['type'=>'VARCHAR','constraint'=>100],
            'role_slug'   => ['type'=>'VARCHAR','constraint'=>100,'unique'=>true],
            'ci3_usertype_id' => ['type'=>'INT','constraint'=>11,'null'=>true],
            'description' => ['type'=>'TEXT','null'=>true],
            'created_at'  => ['type'=>'DATETIME','null'=>true],
            'updated_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('roles', true);

        // Create users
        $this->forge->addField([
            'id'           => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'username'     => ['type'=>'VARCHAR','constraint'=>100],
            'email'        => ['type'=>'VARCHAR','constraint'=>255],
            'password_hash'=> ['type'=>'VARCHAR','constraint'=>255],
            'full_name'    => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'photo'        => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'schoolID'     => ['type'=>'INT','constraint'=>11,'null'=>true,'unsigned'=>true],
            'is_active'    => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'last_login'   => ['type'=>'DATETIME','null'=>true],
            'ci3_user_id'  => ['type'=>'INT','constraint'=>11,'null'=>true],
            'ci3_user_table'=>['type'=>'VARCHAR','constraint'=>100,'null'=>true],
            'created_at'   => ['type'=>'DATETIME','null'=>true],
            'updated_at'   => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);

        // Create user_roles
        $this->forge->addField([
            'id'       => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'user_id'  => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'role_id'  => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'created_at'=>['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id','role_id']);
        $this->forge->createTable('user_roles', true);

        // Seed minimal roles and admin user
        $roles = [
            ['role_name'=>'Super Admin','role_slug'=>'superadmin','ci3_usertype_id'=>0,'created_at'=>date('Y-m-d H:i:s')],
            ['role_name'=>'Admin','role_slug'=>'admin','ci3_usertype_id'=>1,'created_at'=>date('Y-m-d H:i:s')],
            ['role_name'=>'Teacher','role_slug'=>'teacher','ci3_usertype_id'=>2,'created_at'=>date('Y-m-d H:i:s')],
            ['role_name'=>'Student','role_slug'=>'student','ci3_usertype_id'=>3,'created_at'=>date('Y-m-d H:i:s')],
        ];
        $this->db->table('roles')->insertBatch($roles);

        $this->db->table('users')->insert([
            'username' => 'superadmin',
            'email' => 'admin@shulelabs.local',
            'password_hash' => password_hash('Admin@123456', PASSWORD_DEFAULT),
            'full_name' => 'System Administrator',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $adminId = $this->db->insertID();
        $superRole = $this->db->table('roles')->where('role_slug','superadmin')->get()->getRow();
        if ($superRole) {
            $this->db->table('user_roles')->insert([
                'user_id' => $adminId,
                'role_id' => $superRole->id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        foreach (['user_roles','users','roles'] as $t) {
            if ($this->db->tableExists($t)) {
                $this->forge->dropTable($t, true);
            }
        }
        // Optionally recreate ci4_* tables (skipped)
    }
}
