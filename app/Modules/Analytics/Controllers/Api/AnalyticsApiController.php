<?php

namespace Modules\Analytics\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AnalyticsApiController extends ResourceController
{
    public function summary()
    {
        return $this->respond(['students' => 1200, 'teachers' => 85, 'revenue' => 50000]);
    }

    public function performance()
    {
        return $this->respond(['average_grade' => 'B+', 'attendance' => '95%']);
    }
}
