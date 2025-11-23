<?php

namespace App\Models;

use App\Models\TenantModel;

/**
 * LibraryBookModel - Library book catalog.
 */
class LibraryBookModel extends TenantModel
{
    protected $table = 'library_books';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'school_id',
        'title',
        'isbn',
        'author',
        'category',
        'total_copies',
        'available_copies',
    ];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
