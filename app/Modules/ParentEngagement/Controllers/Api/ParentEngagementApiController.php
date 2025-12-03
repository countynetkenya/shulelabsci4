<?php

namespace Modules\ParentEngagement\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ParentEngagementApiController extends ResourceController
{
    public function messages()
    {
        return $this->respond(['messages' => [['id' => 1, 'text' => 'Hello']]]);
    }

    public function send()
    {
        return $this->respond(['status' => 'success', 'message' => 'Message sent']);
    }
}
