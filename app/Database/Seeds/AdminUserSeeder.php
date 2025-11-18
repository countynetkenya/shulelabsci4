<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $model = new UserModel();

        if ($model->where('username', 'admin')->first()) {
            return;
        }

        $model->insert([
            'username'      => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role'          => 'superadmin',
        ]);
    }
}
