<?php

namespace Modules\Governance\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class GovernanceApiController extends ResourceController
{
    public function policies()
    {
        return $this->respond(['policies' => ['Policy A', 'Policy B']]);
    }

    public function vote()
    {
        return $this->respond(['status' => 'success', 'message' => 'Vote recorded']);
    }
}
