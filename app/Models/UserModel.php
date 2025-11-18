<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['username', 'password_hash', 'role'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $validationRules = [
        'username'      => 'required|min_length[3]|max_length[100]|alpha_numeric_punct',
        'password_hash' => 'required|min_length[20]',
        'role'          => 'required|min_length[2]|max_length[50]',
    ];
}
