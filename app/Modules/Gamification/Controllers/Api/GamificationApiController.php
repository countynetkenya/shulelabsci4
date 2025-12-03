<?php

namespace Modules\Gamification\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class GamificationApiController extends ResourceController
{
    public function leaderboard()
    {
        return $this->respond(['top_students' => ['Alice', 'Bob', 'Charlie']]);
    }

    public function award()
    {
        return $this->respond(['status' => 'success', 'message' => 'Points awarded']);
    }
}
