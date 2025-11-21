<?php

declare(strict_types=1);

namespace Modules\Foundation\Controllers;

use CodeIgniter\HTTP\IncomingRequest;
use RuntimeException;

trait InteractsWithIncomingRequest
{
    protected function incomingRequest(): IncomingRequest
    {
        $request = $this->request;
        if ($request instanceof IncomingRequest) {
            return $request;
        }

        throw new RuntimeException(sprintf(
            'Expected IncomingRequest, got %s',
            get_debug_type($request)
        ));
    }
}
