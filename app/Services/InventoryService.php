<?php

namespace App\Services;

use App\Models\InventoryAssetModel;
use App\Models\InventoryTransactionModel;

/**
 * InventoryService - Asset and stock management.
 */
class InventoryService
{
    protected InventoryAssetModel $assetModel;

    protected InventoryTransactionModel $transactionModel;

    public function __construct()
    {
        $this->assetModel = model(InventoryAssetModel::class);
        $this->transactionModel = model(InventoryTransactionModel::class);
    }

    /**
     * Get all assets for school.
     */
    public function getSchoolAssets(int $schoolId, ?string $category = null): array
    {
        $builder = $this->assetModel->forSchool($schoolId);

        if ($category) {
            $builder->where('category', $category);
        }

        return $builder->findAll();
    }

    /**
     * Add asset to inventory.
     */
    public function addAsset(int $schoolId, string $name, string $code, string $category, int $quantity, float $unitPrice): array
    {
        $data = [
            'school_id' => $schoolId,
            'asset_name' => $name,
            'asset_code' => $code,
            'category' => $category,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_value' => $quantity * $unitPrice,
        ];

        $assetId = $this->assetModel->insert($data);

        if (!$assetId) {
            return ['success' => false, 'message' => 'Failed to add asset'];
        }

        // Record initial stock transaction
        $this->recordTransaction($assetId, $schoolId, 'in', $quantity, 'Initial stock');

        return ['success' => true, 'asset_id' => $assetId];
    }

    /**
     * Update asset quantity.
     */
    public function updateQuantity(int $assetId, int $quantityChange, string $type, ?string $notes = null): array
    {
        $asset = $this->assetModel->find($assetId);

        if (!$asset) {
            return ['success' => false, 'message' => 'Asset not found'];
        }

        if ($type === 'out' && $asset['quantity'] < abs($quantityChange)) {
            return ['success' => false, 'message' => 'Insufficient quantity'];
        }

        $newQuantity = $type === 'in' ? $asset['quantity'] + $quantityChange : $asset['quantity'] - $quantityChange;

        $this->assetModel->update($assetId, [
            'quantity' => $newQuantity,
            'total_value' => $newQuantity * $asset['unit_price'],
        ]);

        // Record transaction
        $this->recordTransaction($assetId, $asset['school_id'], $type, $quantityChange, $notes);

        return ['success' => true, 'new_quantity' => $newQuantity];
    }

    /**
     * Record inventory transaction.
     */
    protected function recordTransaction(int $assetId, int $schoolId, string $type, int $quantity, ?string $notes = null): void
    {
        $this->transactionModel->insert([
            'school_id' => $schoolId,
            'asset_id' => $assetId,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'notes' => $notes,
            'transaction_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get asset transaction history.
     */
    public function getAssetTransactions(int $assetId): array
    {
        return $this->transactionModel
            ->where('asset_id', $assetId)
            ->orderBy('transaction_date', 'DESC')
            ->findAll();
    }

    /**
     * Get low stock items.
     */
    public function getLowStockItems(int $schoolId, int $threshold = 10): array
    {
        return $this->assetModel
            ->forSchool($schoolId)
            ->where('quantity <=', $threshold)
            ->findAll();
    }

    /**
     * Get inventory statistics.
     */
    public function getInventoryStats(int $schoolId): array
    {
        $assets = $this->assetModel->forSchool($schoolId)->findAll();

        $stats = [
            'total_items' => count($assets),
            'total_value' => 0,
            'by_category' => [],
            'low_stock_count' => 0,
        ];

        foreach ($assets as $asset) {
            $stats['total_value'] += $asset['total_value'];

            $category = $asset['category'];
            if (!isset($stats['by_category'][$category])) {
                $stats['by_category'][$category] = ['count' => 0, 'value' => 0];
            }
            $stats['by_category'][$category]['count']++;
            $stats['by_category'][$category]['value'] += $asset['total_value'];

            if ($asset['quantity'] <= 10) {
                $stats['low_stock_count']++;
            }
        }

        return $stats;
    }

    /**
     * Search assets.
     */
    public function searchAssets(int $schoolId, string $query): array
    {
        return $this->assetModel
            ->forSchool($schoolId)
            ->groupStart()
                ->like('asset_name', $query)
                ->orLike('asset_code', $query)
            ->groupEnd()
            ->findAll();
    }

    /**
     * Transfer asset between schools.
     */
    public function transferAsset(int $assetId, int $fromSchoolId, int $toSchoolId, int $quantity): array
    {
        $asset = $this->assetModel->find($assetId);

        if (!$asset || $asset['school_id'] != $fromSchoolId) {
            return ['success' => false, 'message' => 'Asset not found'];
        }

        if ($asset['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient quantity'];
        }

        // Decrease from source
        $this->updateQuantity($assetId, $quantity, 'out', "Transfer to school $toSchoolId");

        // Check if asset exists in target school
        $targetAsset = $this->assetModel
            ->forSchool($toSchoolId)
            ->where('asset_code', $asset['asset_code'])
            ->first();

        if ($targetAsset) {
            // Update existing asset in target school
            $this->updateQuantity($targetAsset['id'], $quantity, 'in', "Transfer from school $fromSchoolId");
        } else {
            // Create new asset in target school
            $this->addAsset($toSchoolId, $asset['asset_name'], $asset['asset_code'], $asset['category'], $quantity, $asset['unit_price']);
        }

        return ['success' => true];
    }
}
