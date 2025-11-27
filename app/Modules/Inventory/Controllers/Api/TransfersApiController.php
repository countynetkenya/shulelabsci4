<?php

declare(strict_types=1);

namespace Modules\Inventory\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Inventory\Services\InventoryService;
use CodeIgniter\HTTP\ResponseInterface;

class TransfersApiController extends ResourceController
{
    protected $format = 'json';
    protected InventoryService $inventoryService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();
    }

    /**
     * Initiate a stock transfer
     * POST /api/inventory/transfers
     */
    public function create()
    {
        $rules = [
            'item_id' => 'required|integer',
            'from_location_id' => 'required|integer',
            'to_location_id' => 'required|integer',
            'quantity' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        try {
            $data = $this->request->getJSON(true);
            // Assuming authenticated user ID is available via auth service or session
            // For now, we'll mock it or retrieve from session if available
            $userId = session()->get('user_id') ?? 1; // Fallback to 1 for dev/test if not set

            $transferId = $this->inventoryService->transferStock(
                (int)$data['item_id'],
                (int)$data['from_location_id'],
                (int)$data['to_location_id'],
                (int)$data['quantity'],
                (int)$userId
            );

            return $this->respondCreated([
                'message' => 'Transfer initiated successfully',
                'transfer_id' => $transferId
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Confirm a stock transfer
     * POST /api/inventory/transfers/(:num)/confirm
     */
    public function confirm($id = null)
    {
        if ($id === null) {
            return $this->failNotFound('Transfer ID is required');
        }

        try {
            $userId = session()->get('user_id') ?? 1; // Fallback

            $this->inventoryService->confirmTransfer((int)$id, (int)$userId);

            return $this->respond([
                'message' => 'Transfer confirmed successfully'
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function test()
    {
        return $this->respondCreated(['foo' => 'bar']);
    }
}
