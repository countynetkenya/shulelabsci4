<?php

namespace App\Services;

use App\Models\SchoolModel;
use App\Models\SchoolUserModel;
use CodeIgniter\HTTP\RequestInterface;

/**
 * TenantService - Multi-tenant school context management.
 *
 * Handles automatic tenant resolution from session, subdomain, or user's primary school.
 * Provides methods for switching schools and managing multi-school access.
 */
class TenantService
{
    protected ?int $currentSchoolId = null;
    protected ?array $currentSchool = null;
    protected RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Set current school context from session, subdomain, or user's primary school.
     *
     * Priority:
     * 1. Session (user selected school)
     * 2. Subdomain routing (e.g., nairobi-primary.shulelabs.com)
     * 3. User's primary school
     */
    public function setCurrentSchool(): void
    {
        $session = session();

        // Priority 1: Session (user selected school)
        if ($session->has('current_school_id')) {
            $this->currentSchoolId = (int) $session->get('current_school_id');
            $this->loadSchool($this->currentSchoolId);

            return;
        }

        // Priority 2: Subdomain routing (e.g., nairobi-primary.shulelabs.com)
        $host = $this->request->getServer('HTTP_HOST') ?? '';
        if (preg_match('/^([a-z0-9-]+)\.shulelabs\./', $host, $matches)) {
            $schoolCode = $matches[1];
            $this->loadSchoolByCode($schoolCode);

            return;
        }

        // Priority 3: User's primary school
        if ($session->has('user_id')) {
            $this->loadUserPrimarySchool((int) $session->get('user_id'));
        }
    }

    /**
     * Get current school ID.
     */
    public function getCurrentSchoolId(): ?int
    {
        return $this->currentSchoolId;
    }

    /**
     * Get current school data.
     */
    public function getCurrentSchool(): ?array
    {
        return $this->currentSchool;
    }

    /**
     * Switch user to different school.
     *
     * @param int $schoolId School to switch to
     * @param int $userId   User making the switch
     *
     * @return bool True if switch successful
     */
    public function switchSchool(int $schoolId, int $userId): bool
    {
        // Verify user has access to this school
        $schoolUserModel = model(SchoolUserModel::class);
        $access           = $schoolUserModel->where([
            'school_id' => $schoolId,
            'user_id'   => $userId,
        ])->first();

        if (! $access) {
            return false;
        }

        session()->set('current_school_id', $schoolId);
        $this->loadSchool($schoolId);

        return true;
    }

    /**
     * Get all schools for a user.
     *
     * @param int $userId User ID
     *
     * @return array Array of schools with user's role in each
     */
    public function getUserSchools(int $userId): array
    {
        $schoolUserModel = model(SchoolUserModel::class);

        return $schoolUserModel
            ->select('schools.*, school_users.role_id, school_users.is_primary_school, roles.role_name')
            ->join('schools', 'schools.id = school_users.school_id')
            ->join('roles', 'roles.id = school_users.role_id')
            ->where('school_users.user_id', $userId)
            ->where('schools.is_active', true)
            ->findAll();
    }

    /**
     * Check if user has access to a specific school.
     *
     * @param int $userId   User ID
     * @param int $schoolId School ID
     */
    public function hasAccessToSchool(int $userId, int $schoolId): bool
    {
        $schoolUserModel = model(SchoolUserModel::class);
        $access           = $schoolUserModel->where([
            'school_id' => $schoolId,
            'user_id'   => $userId,
        ])->first();

        return $access !== null;
    }

    /**
     * Load school data by ID.
     *
     * @param int $schoolId School ID
     */
    private function loadSchool(int $schoolId): void
    {
        $schoolModel = model(SchoolModel::class);
        $school      = $schoolModel->find($schoolId);

        if ($school && $school['is_active']) {
            $this->currentSchool   = $school;
            $this->currentSchoolId = $schoolId;
        }
    }

    /**
     * Load school data by school code.
     *
     * @param string $code School code
     */
    private function loadSchoolByCode(string $code): void
    {
        $schoolModel = model(SchoolModel::class);
        $school      = $schoolModel->where('school_code', $code)->first();

        if ($school && $school['is_active']) {
            $this->currentSchool   = $school;
            $this->currentSchoolId = $school['id'];
            session()->set('current_school_id', $school['id']);
        }
    }

    /**
     * Load user's primary school.
     *
     * @param int $userId User ID
     */
    private function loadUserPrimarySchool(int $userId): void
    {
        $schoolUserModel = model(SchoolUserModel::class);
        $primary          = $schoolUserModel->where([
            'user_id'           => $userId,
            'is_primary_school' => true,
        ])->first();

        if ($primary) {
            $this->loadSchool($primary['school_id']);
        } else {
            // If no primary school, load first available school
            $firstSchool = $schoolUserModel
                ->select('school_id')
                ->where('user_id', $userId)
                ->first();

            if ($firstSchool) {
                $this->loadSchool($firstSchool['school_id']);
            }
        }
    }

    /**
     * Set user's primary school.
     *
     * @param int $userId   User ID
     * @param int $schoolId School ID to set as primary
     */
    public function setPrimarySchool(int $userId, int $schoolId): bool
    {
        $schoolUserModel = model(SchoolUserModel::class);

        // Remove primary flag from all user's schools
        $schoolUserModel->where('user_id', $userId)->set(['is_primary_school' => false])->update();

        // Set new primary school
        return $schoolUserModel
            ->where(['user_id' => $userId, 'school_id' => $schoolId])
            ->set(['is_primary_school' => true])
            ->update();
    }

    /**
     * Clear current school context.
     */
    public function clearCurrentSchool(): void
    {
        session()->remove('current_school_id');
        $this->currentSchoolId = null;
        $this->currentSchool   = null;
    }
}
