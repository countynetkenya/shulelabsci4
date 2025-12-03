<?php

namespace Modules\Admissions\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AdmissionsApiController extends ResourceController
{
    public function apply()
    {
        return $this->respond(['status' => 'success', 'message' => 'Application received']);
    }

    public function status()
    {
        return $this->respond(['status' => 'pending', 'message' => 'Application under review']);
    }
}
