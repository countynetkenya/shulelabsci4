<?php

namespace App\Models;

/**
 * LibraryBorrowingModel - Book borrowing records.
 */
class LibraryBorrowingModel extends TenantModel
{
    protected $table = 'library_borrowings';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'school_id',
        'book_id',
        'student_id',
        'borrowed_date',
        'due_date',
        'returned_date',
        'status',
    ];

    protected bool $allowEmptyInserts = false;

    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}
