<?php

namespace Modules\Inventory\Services;

use Config\Services;
use Exception;
use Modules\Inventory\Models\InventoryItemModel;
use Modules\Inventory\Models\InventoryStockModel;
use Modules\Inventory\Models\InventoryTransferModel;
use Modules\Threads\Services\EventBus;
use Modules\Threads\Services\InMemoryThreadRepository;
use Modules\Threads\Services\ThreadService;

class InventoryService
{
    protected $itemModel;
    protected $stockModel;
    protected $transferModel;
    protected $threadService;
    protected $db;

    public function __construct()
    {
        $this->itemModel = new InventoryItemModel();
        $this->stockModel = new InventoryStockModel();
        $this->transferModel = new InventoryTransferModel();
        $this->db = \Config\Database::connect();

        // Initialize ThreadService (Temporary: using InMemory repository as DB repo is missing)
        $this->threadService = new ThreadService(
            new InMemoryThreadRepository(),
            Services::audit(),
            new EventBus()
        );
    }

    /**
     * Create a new inventory item.
     */
    public function createItem(array $data): int
    {
        $id = $this->itemModel->insert($data);
        if (!$id) {
            throw new Exception('Failed to create inventory item: ' . implode(', ', $this->itemModel->errors()));
        }
        return (int) $id;
    }

    /**
     * Get items with optional filtering.
     */
    public function getItems(array $filters = []): array
    {
        $builder = $this->itemModel->select('inventory_items.*, inventory_categories.name as category_name')
            ->join('inventory_categories', 'inventory_categories.id = inventory_items.category_id', 'left');

        if (isset($filters['category_id'])) {
            $builder->where('category_id', $filters['category_id']);
        }
        
        if (isset($filters['search'])) {
            $builder->groupStart()
                ->like('inventory_items.name', $filters['search'])
                ->orLike('sku', $filters['search'])
                ->groupEnd();
        }

        return $builder->findAll();
    }

    /**
     * Get stock level for an item at a location.
     */
    public function getStock(int $itemId, int $locationId): float
    {
        $stock = $this->stockModel->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->first();
            
        return $stock ? (float) $stock['quantity'] : 0.0;
    }

    /**
     * Adjust stock quantity (In/Out).
     */
    public function adjustStock(int $itemId, int $locationId, float $quantity, string $reason, int $userId): void
    {
        $this->db->transStart();

        $stock = $this->stockModel->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->first();

        if ($stock) {
            $newQty = $stock['quantity'] + $quantity;
            if ($newQty < 0) {
                throw new Exception('Insufficient stock for adjustment.');
            }
            $this->stockModel->update($stock['id'], ['quantity' => $newQty]);
        } else {
            if ($quantity < 0) {
                throw new Exception('Cannot reduce stock below zero (no record found).');
            }
            $this->stockModel->insert([
                'item_id' => $itemId,
                'location_id' => $locationId,
                'quantity' => $quantity,
            ]);
        }

        // TODO: Record transaction log (InventoryTransactionModel)

        $this->db->transComplete();
    }

    /**
     * Transfer stock from one location to another.
     *
     * @param int $itemId
     * @param int $fromLocationId
     * @param int $toLocationId
     * @param int $quantity
     * @param int $userId
     * @return int Transfer ID
     * @throws Exception
     */
    public function transferStock(int $itemId, int $fromLocationId, int $toLocationId, int $quantity, int $userId): int
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        $this->db->transStart();

        try {
            // 1. Atomic Decrement from Source
            // Check if record exists
            $sourceStock = $this->stockModel->where('item_id', $itemId)
                                            ->where('location_id', $fromLocationId)
                                            ->first();

            if (!$sourceStock) {
                throw new Exception('Stock record not found at source location.');
            }

            if ($sourceStock['quantity'] < $quantity) {
                throw new Exception('Insufficient stock at source location.');
            }

            // Atomic update
            $sql = 'UPDATE inventory_stock SET quantity = quantity - ? WHERE item_id = ? AND location_id = ? AND quantity >= ?';
            $this->db->query($sql, [$quantity, $itemId, $fromLocationId, $quantity]);

            if ($this->db->affectedRows() === 0) {
                throw new Exception('Failed to decrement stock. Insufficient balance or race condition.');
            }

            // 2. Create Transfer Record
            $transferId = $this->transferModel->insert([
                'item_id' => $itemId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'quantity' => $quantity,
                'status' => 'pending',
                'initiated_by' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // 3. Trigger Thread (Digital Handshake)
            $this->createTransferThread($transferId, $itemId, $quantity, $fromLocationId, $toLocationId, $userId);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Transaction failed.');
            }

            return $transferId;

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    protected function createTransferThread($transferId, $itemId, $quantity, $from, $to, $userId)
    {
        $payload = [
            'subject' => "Stock Transfer #{$transferId}",
            'context_type' => 'inventory_transfer',
            'context_id' => (string) $transferId,
            'is_cfr' => true,
            'message' => "Transfer of {$quantity} items initiated.",
        ];

        $context = [
            'actor_id' => (string) $userId,
            'module' => 'Inventory',
        ];

        $thread = $this->threadService->createThread($payload, $context);

        // Update transfer with thread_id
        $this->transferModel->update($transferId, ['thread_id' => $thread->getId()]);
    }

    /**
     * Confirm a stock transfer.
     *
     * @param int $transferId
     * @param int $userId
     * @throws Exception
     */
    public function confirmTransfer(int $transferId, int $userId)
    {
        $this->db->transStart();

        try {
            $transfer = $this->transferModel->find($transferId);
            if (!$transfer) {
                throw new Exception('Transfer not found.');
            }

            if ($transfer['status'] !== 'pending') {
                throw new Exception('Transfer is not pending.');
            }

            // Increment destination stock
            // Check if stock record exists
            $destStock = $this->stockModel->where('item_id', $transfer['item_id'])
                                          ->where('location_id', $transfer['to_location_id'])
                                          ->first();

            if ($destStock) {
                $this->db->query('UPDATE inventory_stock SET quantity = quantity + ? WHERE id = ?', [$transfer['quantity'], $destStock['id']]);
            } else {
                $this->stockModel->insert([
                    'item_id' => $transfer['item_id'],
                    'location_id' => $transfer['to_location_id'],
                    'quantity' => $transfer['quantity'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // Update transfer status
            $this->transferModel->update($transferId, [
                'status' => 'completed',
                'completed_by' => $userId,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Transaction failed.');
            }

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
