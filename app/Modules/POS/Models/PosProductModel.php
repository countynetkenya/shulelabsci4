<?php

namespace App\Modules\POS\Models;

use CodeIgniter\Model;

class PosProductModel extends Model
{
    protected $table = 'pos_products';
    protected $primaryKey = 'id';
    protected $allowedFields = ['school_id', 'name', 'price', 'stock', 'description'];
    protected $useTimestamps = true;
}
