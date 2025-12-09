<?php

namespace Modules\ParentEngagement\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ParentEngagementSeeder extends Seeder
{
    public function run()
    {
        $schoolId = 1; // Default school for testing

        // Seed Surveys
        $surveys = [
            [
                'school_id' => $schoolId,
                'title' => 'Parent Satisfaction Survey 2024',
                'description' => 'Help us improve by sharing your feedback',
                'survey_type' => 'feedback',
                'target_audience' => 'all_parents',
                'target_ids' => null,
                'questions' => json_encode([
                    ['text' => 'How satisfied are you with the school?', 'type' => 'rating'],
                    ['text' => 'What can we improve?', 'type' => 'text'],
                ]),
                'is_anonymous' => 1,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'active',
                'response_count' => 12,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'title' => 'Transportation Needs Poll',
                'description' => 'Help us plan transport routes',
                'survey_type' => 'poll',
                'target_audience' => 'all_parents',
                'target_ids' => null,
                'questions' => json_encode([
                    ['text' => 'Do you need school transport?', 'type' => 'yes_no'],
                ]),
                'is_anonymous' => 0,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+14 days')),
                'status' => 'active',
                'response_count' => 45,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('surveys')->insertBatch($surveys);

        // Seed Events
        $events = [
            [
                'school_id' => $schoolId,
                'title' => 'Annual Sports Day',
                'description' => 'Join us for a day of sports and fun!',
                'event_type' => 'sports',
                'venue' => 'School Grounds',
                'start_datetime' => date('Y-m-d H:i:s', strtotime('+7 days 09:00')),
                'end_datetime' => date('Y-m-d H:i:s', strtotime('+7 days 17:00')),
                'max_attendees' => 500,
                'registration_required' => 0,
                'registration_deadline' => null,
                'fee' => 0,
                'status' => 'published',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'title' => 'Parent-Teacher Meeting',
                'description' => 'Discuss student progress with teachers',
                'event_type' => 'meeting',
                'venue' => 'Main Hall',
                'start_datetime' => date('Y-m-d H:i:s', strtotime('+14 days 14:00')),
                'end_datetime' => date('Y-m-d H:i:s', strtotime('+14 days 17:00')),
                'max_attendees' => 200,
                'registration_required' => 1,
                'registration_deadline' => date('Y-m-d H:i:s', strtotime('+12 days')),
                'fee' => 0,
                'status' => 'published',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'title' => 'Science Fair Exhibition',
                'description' => 'Student science projects showcase',
                'event_type' => 'academic',
                'venue' => 'Science Lab',
                'start_datetime' => date('Y-m-d H:i:s', strtotime('+21 days 10:00')),
                'end_datetime' => date('Y-m-d H:i:s', strtotime('+21 days 15:00')),
                'max_attendees' => null,
                'registration_required' => 0,
                'registration_deadline' => null,
                'fee' => 0,
                'status' => 'published',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('events')->insertBatch($events);

        // Seed Conferences
        $conferences = [
            [
                'school_id' => $schoolId,
                'name' => 'End of Term 1 Conferences',
                'description' => 'Schedule time with your child\'s teachers',
                'conference_date' => date('Y-m-d', strtotime('+30 days')),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'slot_duration_minutes' => 15,
                'venue' => 'Various Classrooms',
                'is_virtual' => 0,
                'meeting_link' => null,
                'status' => 'open',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('conferences')->insertBatch($conferences);

        // Seed Fundraising Campaigns
        $campaigns = [
            [
                'school_id' => $schoolId,
                'name' => 'New Computer Lab Fund',
                'description' => 'Help us build a modern computer lab for our students',
                'target_amount' => 500000.00,
                'raised_amount' => 125000.00,
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+90 days')),
                'status' => 'active',
                'donor_count' => 45,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => $schoolId,
                'name' => 'Library Book Drive',
                'description' => 'Expand our library collection',
                'target_amount' => 150000.00,
                'raised_amount' => 87500.00,
                'start_date' => date('Y-m-d', strtotime('-15 days')),
                'end_date' => date('Y-m-d', strtotime('+45 days')),
                'status' => 'active',
                'donor_count' => 32,
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->table('fundraising_campaigns')->insertBatch($campaigns);
    }
}
