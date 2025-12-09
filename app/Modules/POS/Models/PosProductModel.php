<?php

namespace App\Modules\POS\Models;

use CodeIgniter\Model;

class PosProductModel extends Model
{
    protected $table = 'pos_products';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'school_id',
        'name',
        'description',
        'price',
        'stock',
        'sku',
        'barcode',
        'category',
        'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'school_id' => 'required|integer',
        'name'      => 'required|min_length[2]|max_length[255]',
        'price'     => 'required|decimal',
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Product name is required',
            'min_length' => 'Product name must be at least 2 characters',
        ],
        'price' => [
            'required' => 'Price is required',
            'decimal'  => 'Price must be a valid number',
        ],
    ];
}

