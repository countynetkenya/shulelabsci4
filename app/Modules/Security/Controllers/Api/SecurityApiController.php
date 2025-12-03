<?php

namespace Modules\Security\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class SecurityApiController extends ResourceController
{
    public function roles()
    {
        return $this->respond(['roles' => ['Admin', 'Teacher', 'Student']]);
    }

    public function permissions()
    {
        return $this->respond(['permissions' => ['read', 'write']]);
    }
}
