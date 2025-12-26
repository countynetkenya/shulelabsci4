<?php

namespace App\Modules\Gamification\Services;

use App\Modules\Gamification\Models\AchievementModel;
use App\Modules\Gamification\Models\BadgeModel;
use App\Modules\Gamification\Models\PointModel;

/**
 * GamificationCrudService - Handles CRUD operations for gamification.
 */
class GamificationCrudService
{
    protected BadgeModel $badgeModel;

    protected AchievementModel $achievementModel;

    protected PointModel $pointModel;

    public function __construct()
    {
        $this->badgeModel = new BadgeModel();
        $this->achievementModel = new AchievementModel();
        $this->pointModel = new PointModel();
    }

    /**
     * Get all badges for a school.
     */
    public function getAllBadges(int $schoolId): array
    {
        return $this->badgeModel
            ->where('school_id', $schoolId)
            ->orWhere('school_id IS NULL')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a single badge by ID.
     */
    public function getBadgeById(int $id, int $schoolId): ?array
    {
        $badge = $this->badgeModel->find($id);

        if (!$badge) {
            return null;
        }

        // Allow access if badge is global or belongs to school
        if ($badge['school_id'] === null || (int) $badge['school_id'] === $schoolId) {
            return $badge;
        }

        return null;
    }

    /**
     * Create a new badge.
     */
    public function createBadge(array $data): int|false
    {
        return $this->badgeModel->insert($data);
    }

    /**
     * Update an existing badge.
     */
    public function updateBadge(int $id, array $data, int $schoolId): bool
    {
        $badge = $this->getBadgeById($id, $schoolId);

        if (!$badge) {
            return false;
        }

        return $this->badgeModel->update($id, $data);
    }

    /**
     * Delete a badge.
     */
    public function deleteBadge(int $id, int $schoolId): bool
    {
        $badge = $this->getBadgeById($id, $schoolId);

        if (!$badge || $badge['school_id'] === null) {
            // Don't delete global badges
            return false;
        }

        return $this->badgeModel->delete($id);
    }

    /**
     * Get all achievements for a school.
     */
    public function getAllAchievements(int $schoolId): array
    {
        return $this->achievementModel
            ->where('school_id', $schoolId)
            ->orWhere('school_id IS NULL')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get a single achievement by ID.
     */
    public function getAchievementById(int $id, int $schoolId): ?array
    {
        $achievement = $this->achievementModel->find($id);

        if (!$achievement) {
            return null;
        }

        // Allow access if achievement is global or belongs to school
        if ($achievement['school_id'] === null || (int) $achievement['school_id'] === $schoolId) {
            return $achievement;
        }

        return null;
    }

    /**
     * Create a new achievement.
     */
    public function createAchievement(array $data): int|false
    {
        return $this->achievementModel->insert($data);
    }

    /**
     * Update an existing achievement.
     */
    public function updateAchievement(int $id, array $data, int $schoolId): bool
    {
        $achievement = $this->getAchievementById($id, $schoolId);

        if (!$achievement) {
            return false;
        }

        return $this->achievementModel->update($id, $data);
    }

    /**
     * Delete an achievement.
     */
    public function deleteAchievement(int $id, int $schoolId): bool
    {
        $achievement = $this->getAchievementById($id, $schoolId);

        if (!$achievement || $achievement['school_id'] === null) {
            // Don't delete global achievements
            return false;
        }

        return $this->achievementModel->delete($id);
    }

    /**
     * Get recent point transactions.
     */
    public function getRecentPoints(int $schoolId, int $limit = 50): array
    {
        return $this->pointModel
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
