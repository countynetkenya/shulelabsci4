<?php

namespace App\Modules\Admissions\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * AdmissionsService - Handles student application workflow.
 */
class AdmissionsService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Submit a new application.
     */
    public function submitApplication(array $data): int
    {
        $schoolId = session('school_id');
        $applicationNumber = $this->generateApplicationNumber($schoolId);

        $data['school_id'] = $schoolId;
        $data['application_number'] = $applicationNumber;
        $data['status'] = 'submitted';

        $this->db->table('applications')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Advance application to next stage.
     */
    public function advanceStage(int $applicationId, int $stageId, ?int $reviewerId = null): bool
    {
        return $this->db->table('applications')
            ->where('id', $applicationId)
            ->update([
                'stage_id' => $stageId,
                'reviewed_by' => $reviewerId ?? session('user_id'),
                'reviewed_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Accept an application.
     */
    public function accept(int $applicationId, ?string $notes = null): bool
    {
        return $this->updateStatus($applicationId, 'accepted', $notes);
    }

    /**
     * Reject an application.
     */
    public function reject(int $applicationId, string $reason): bool
    {
        return $this->updateStatus($applicationId, 'rejected', $reason);
    }

    /**
     * Add to waitlist.
     */
    public function addToWaitlist(int $applicationId, int $classId): int
    {
        $schoolId = session('school_id');

        // Get next position
        $lastPosition = $this->db->table('waitlists')
            ->selectMax('position')
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->get()
            ->getRowArray();

        $position = ($lastPosition['position'] ?? 0) + 1;

        $this->db->transStart();

        $this->db->table('waitlists')->insert([
            'school_id' => $schoolId,
            'application_id' => $applicationId,
            'class_id' => $classId,
            'position' => $position,
        ]);
        $waitlistId = (int) $this->db->insertID();

        $this->updateStatus($applicationId, 'waitlisted');

        $this->db->transComplete();

        return $waitlistId;
    }

    /**
     * Send offer from waitlist.
     */
    public function sendWaitlistOffer(int $waitlistId, int $expiryDays = 7): bool
    {
        return $this->db->table('waitlists')
            ->where('id', $waitlistId)
            ->update([
                'offer_sent' => 1,
                'offer_sent_at' => date('Y-m-d H:i:s'),
                'offer_expires_at' => date('Y-m-d H:i:s', strtotime("+{$expiryDays} days")),
            ]);
    }

    /**
     * Schedule entrance test for application.
     */
    public function scheduleTest(int $applicationId, int $testId): int
    {
        // Check capacity
        $test = $this->db->table('entrance_tests')
            ->where('id', $testId)
            ->get()
            ->getRowArray();

        if (!$test || $test['registered_count'] >= $test['max_candidates']) {
            throw new \RuntimeException('Test is full');
        }

        $this->db->transStart();

        $this->db->table('test_registrations')->insert([
            'test_id' => $testId,
            'application_id' => $applicationId,
        ]);
        $regId = (int) $this->db->insertID();

        $this->db->table('entrance_tests')
            ->where('id', $testId)
            ->set('registered_count', 'registered_count + 1', false)
            ->update();

        $this->updateStatus($applicationId, 'test_scheduled');

        $this->db->transComplete();

        return $regId;
    }

    /**
     * Schedule interview.
     */
    public function scheduleInterview(int $applicationId, string $date, string $time, ?int $interviewerId = null, ?string $venue = null): int
    {
        $this->db->table('interviews')->insert([
            'application_id' => $applicationId,
            'interview_date' => $date,
            'interview_time' => $time,
            'interviewer_id' => $interviewerId,
            'venue' => $venue,
        ]);

        $this->updateStatus($applicationId, 'interview_scheduled');

        return (int) $this->db->insertID();
    }

    /**
     * Get application statistics.
     */
    public function getStatistics(?int $schoolId = null, ?string $academicYear = null): array
    {
        $schoolId = $schoolId ?? session('school_id');
        $academicYear = $academicYear ?? date('Y');

        $builder = $this->db->table('applications')
            ->select('status, COUNT(*) as count')
            ->where('school_id', $schoolId)
            ->where('academic_year', $academicYear)
            ->groupBy('status');

        $results = $builder->get()->getResultArray();

        $stats = [
            'total' => 0,
            'submitted' => 0,
            'under_review' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'waitlisted' => 0,
            'enrolled' => 0,
        ];

        foreach ($results as $row) {
            $stats[$row['status']] = (int) $row['count'];
            $stats['total'] += (int) $row['count'];
        }

        return $stats;
    }

    /**
     * Update application status.
     */
    private function updateStatus(int $applicationId, string $status, ?string $notes = null): bool
    {
        $data = [
            'status' => $status,
            'reviewed_by' => session('user_id'),
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        if ($notes) {
            $data['decision_notes'] = $notes;
        }

        return $this->db->table('applications')
            ->where('id', $applicationId)
            ->update($data);
    }

    /**
     * Generate unique application number.
     */
    private function generateApplicationNumber(int $schoolId): string
    {
        $year = date('Y');
        $prefix = "APP-{$year}-";

        $lastApp = $this->db->table('applications')
            ->select('application_number')
            ->where('school_id', $schoolId)
            ->like('application_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($lastApp) {
            $lastNum = (int) substr($lastApp['application_number'], -4);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
    }
}
