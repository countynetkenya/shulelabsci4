<?php

declare(strict_types=1);

namespace Modules\Inventory\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Inventory\Services\StockService;

class InventoryStockController extends ResourceController
{
    protected $format = 'json';
    protected StockService $service;

    public function __construct()
    {
        $this->service = new StockService();
    }

    public function index()
    {
        try {
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = (int) ($this->request->getGet('per_page') ?? 20);

            $result = $this->service->paginate($page, $perPage);

            return $this->response->setBody(json_encode($result))
                                  ->setHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
