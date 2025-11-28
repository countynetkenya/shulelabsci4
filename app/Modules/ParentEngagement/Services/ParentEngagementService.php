<?php

namespace App\Modules\ParentEngagement\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * ParentEngagementService - Handles surveys, events, conferences, and fundraising.
 */
class ParentEngagementService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    // ============= SURVEYS =============

    /**
     * Create a survey.
     */
    public function createSurvey(array $data): int
    {
        $data['school_id'] = session('school_id');
        $data['created_by'] = session('user_id');
        $data['questions'] = json_encode($data['questions']);
        $data['status'] = 'draft';

        if (!empty($data['target_ids'])) {
            $data['target_ids'] = json_encode($data['target_ids']);
        }

        $this->db->table('surveys')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Submit survey response.
     */
    public function submitSurveyResponse(int $surveyId, array $responses): int
    {
        $userId = session('user_id');

        $this->db->transStart();

        $this->db->table('survey_responses')->insert([
            'survey_id' => $surveyId,
            'user_id' => $userId,
            'responses' => json_encode($responses),
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
        $responseId = (int) $this->db->insertID();

        $this->db->table('surveys')
            ->where('id', $surveyId)
            ->set('response_count', 'response_count + 1', false)
            ->update();

        $this->db->transComplete();

        return $responseId;
    }

    /**
     * Get survey results.
     */
    public function getSurveyResults(int $surveyId): array
    {
        $survey = $this->db->table('surveys')
            ->where('id', $surveyId)
            ->get()
            ->getRowArray();

        if (!$survey) {
            return [];
        }

        $responses = $this->db->table('survey_responses')
            ->where('survey_id', $surveyId)
            ->get()
            ->getResultArray();

        $questions = json_decode($survey['questions'], true);
        $aggregated = [];

        foreach ($questions as $index => $question) {
            $aggregated[$index] = [
                'question' => $question['text'],
                'type' => $question['type'],
                'answers' => [],
            ];
        }

        foreach ($responses as $response) {
            $answers = json_decode($response['responses'], true);
            foreach ($answers as $index => $answer) {
                if (isset($aggregated[$index])) {
                    $aggregated[$index]['answers'][] = $answer;
                }
            }
        }

        return [
            'survey' => $survey,
            'total_responses' => count($responses),
            'results' => $aggregated,
        ];
    }

    // ============= EVENTS =============

    /**
     * Create an event.
     */
    public function createEvent(array $data): int
    {
        $data['school_id'] = session('school_id');
        $data['created_by'] = session('user_id');
        $data['status'] = 'draft';

        $this->db->table('events')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Register for an event.
     */
    public function registerForEvent(int $eventId, int $attendees = 1): int
    {
        $userId = session('user_id');

        $event = $this->db->table('events')
            ->where('id', $eventId)
            ->get()
            ->getRowArray();

        if (!$event) {
            throw new \RuntimeException('Event not found');
        }

        // Check capacity
        if ($event['max_attendees']) {
            $currentCount = $this->db->table('event_registrations')
                ->selectSum('attendees', 'total')
                ->where('event_id', $eventId)
                ->whereIn('status', ['registered', 'attended'])
                ->get()
                ->getRowArray();

            if (($currentCount['total'] ?? 0) + $attendees > $event['max_attendees']) {
                throw new \RuntimeException('Event is at capacity');
            }
        }

        $this->db->table('event_registrations')->insert([
            'event_id' => $eventId,
            'user_id' => $userId,
            'attendees' => $attendees,
            'status' => 'registered',
            'payment_status' => $event['fee'] > 0 ? 'pending' : 'paid',
            'registered_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Get upcoming events.
     */
    public function getUpcomingEvents(?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? session('school_id');

        return $this->db->table('events')
            ->where('school_id', $schoolId)
            ->where('status', 'published')
            ->where('start_datetime >', date('Y-m-d H:i:s'))
            ->orderBy('start_datetime', 'ASC')
            ->get()
            ->getResultArray();
    }

    // ============= CONFERENCES =============

    /**
     * Create parent-teacher conference.
     */
    public function createConference(array $data): int
    {
        $data['school_id'] = session('school_id');
        $data['created_by'] = session('user_id');
        $data['status'] = 'draft';

        $this->db->table('conferences')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Generate conference slots for a teacher.
     */
    public function generateSlots(int $conferenceId, int $teacherId): int
    {
        $conference = $this->db->table('conferences')
            ->where('id', $conferenceId)
            ->get()
            ->getRowArray();

        if (!$conference) {
            return 0;
        }

        $startTime = strtotime($conference['start_time']);
        $endTime = strtotime($conference['end_time']);
        $slotDuration = $conference['slot_duration_minutes'] * 60;

        $slots = [];
        $currentTime = $startTime;

        while ($currentTime + $slotDuration <= $endTime) {
            $slots[] = [
                'conference_id' => $conferenceId,
                'teacher_id' => $teacherId,
                'start_time' => date('H:i:s', $currentTime),
                'end_time' => date('H:i:s', $currentTime + $slotDuration),
                'status' => 'available',
            ];
            $currentTime += $slotDuration;
        }

        if (empty($slots)) {
            return 0;
        }

        $this->db->table('conference_slots')->insertBatch($slots);

        return count($slots);
    }

    /**
     * Book a conference slot.
     */
    public function bookSlot(int $slotId, int $studentId): bool
    {
        $slot = $this->db->table('conference_slots')
            ->where('id', $slotId)
            ->where('status', 'available')
            ->get()
            ->getRowArray();

        if (!$slot) {
            return false;
        }

        return $this->db->table('conference_slots')
            ->where('id', $slotId)
            ->update([
                'parent_id' => session('user_id'),
                'student_id' => $studentId,
                'status' => 'booked',
                'booked_at' => date('Y-m-d H:i:s'),
            ]);
    }

    /**
     * Get available slots for a conference/teacher.
     */
    public function getAvailableSlots(int $conferenceId, int $teacherId): array
    {
        return $this->db->table('conference_slots')
            ->where('conference_id', $conferenceId)
            ->where('teacher_id', $teacherId)
            ->where('status', 'available')
            ->orderBy('start_time', 'ASC')
            ->get()
            ->getResultArray();
    }

    // ============= FUNDRAISING =============

    /**
     * Create fundraising campaign.
     */
    public function createCampaign(array $data): int
    {
        $data['school_id'] = session('school_id');
        $data['created_by'] = session('user_id');
        $data['status'] = 'draft';

        $this->db->table('fundraising_campaigns')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Record a donation.
     */
    public function recordDonation(int $campaignId, float $amount, ?string $donorName = null, bool $isAnonymous = false, ?string $message = null): int
    {
        $this->db->transStart();

        $this->db->table('donations')->insert([
            'campaign_id' => $campaignId,
            'user_id' => session('user_id'),
            'donor_name' => $donorName,
            'amount' => $amount,
            'is_anonymous' => $isAnonymous ? 1 : 0,
            'message' => $message,
            'donated_at' => date('Y-m-d H:i:s'),
        ]);
        $donationId = (int) $this->db->insertID();

        $this->db->table('fundraising_campaigns')
            ->where('id', $campaignId)
            ->set('raised_amount', 'raised_amount + ' . $amount, false)
            ->set('donor_count', 'donor_count + 1', false)
            ->update();

        $this->db->transComplete();

        return $donationId;
    }

    /**
     * Get active campaigns.
     */
    public function getActiveCampaigns(?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? session('school_id');

        return $this->db->table('fundraising_campaigns')
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->orderBy('end_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get campaign progress.
     */
    public function getCampaignProgress(int $campaignId): array
    {
        $campaign = $this->db->table('fundraising_campaigns')
            ->where('id', $campaignId)
            ->get()
            ->getRowArray();

        if (!$campaign) {
            return [];
        }

        $percentage = $campaign['target_amount'] > 0
            ? min(100, round(($campaign['raised_amount'] / $campaign['target_amount']) * 100, 2))
            : 0;

        return [
            'campaign' => $campaign,
            'progress_percentage' => $percentage,
            'remaining_amount' => max(0, $campaign['target_amount'] - $campaign['raised_amount']),
            'days_remaining' => max(0, (strtotime($campaign['end_date']) - time()) / 86400),
        ];
    }
}
