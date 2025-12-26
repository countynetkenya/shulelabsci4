<?php

namespace App\Modules\Gamification\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * GamificationSeeder - Seed sample badges and achievements.
 */
class GamificationSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Sample badges for school_id = 1
        $badges = [
            [
                'school_id' => 1,
                'name' => 'Perfect Attendance',
                'code' => 'PERFECT_ATTENDANCE',
                'description' => 'Awarded for 100% attendance in a term',
                'icon' => 'fa-calendar-check',
                'color' => '#28a745',
                'category' => 'attendance',
                'tier' => 'gold',
                'points_reward' => 100,
                'criteria' => json_encode(['type' => 'attendance', 'value' => 100]),
                'is_secret' => 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Academic Excellence',
                'code' => 'ACADEMIC_EXCELLENCE',
                'description' => 'Awarded for scoring above 90% average',
                'icon' => 'fa-star',
                'color' => '#ffc107',
                'category' => 'academic',
                'tier' => 'platinum',
                'points_reward' => 250,
                'criteria' => json_encode(['type' => 'grade_average', 'value' => 90]),
                'is_secret' => 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Team Player',
                'code' => 'TEAM_PLAYER',
                'description' => 'Awarded for outstanding teamwork in sports',
                'icon' => 'fa-users',
                'color' => '#17a2b8',
                'category' => 'sports',
                'tier' => 'silver',
                'points_reward' => 75,
                'criteria' => json_encode(['type' => 'sports_participation', 'value' => 5]),
                'is_secret' => 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Student Leader',
                'code' => 'STUDENT_LEADER',
                'description' => 'Awarded for demonstrating leadership qualities',
                'icon' => 'fa-crown',
                'color' => '#6610f2',
                'category' => 'leadership',
                'tier' => 'gold',
                'points_reward' => 150,
                'criteria' => json_encode(['type' => 'leadership_score', 'value' => 80]),
                'is_secret' => 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Good Behavior',
                'code' => 'GOOD_BEHAVIOR',
                'description' => 'Awarded for exemplary conduct throughout the term',
                'icon' => 'fa-heart',
                'color' => '#dc3545',
                'category' => 'behavior',
                'tier' => 'bronze',
                'points_reward' => 50,
                'criteria' => json_encode(['type' => 'behavior_score', 'value' => 90]),
                'is_secret' => 0,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Sample achievements
        $achievements = [
            [
                'school_id' => 1,
                'name' => '100 Points Club',
                'code' => 'POINTS_100',
                'description' => 'Earn 100 total points',
                'category' => 'milestones',
                'criteria_type' => 'total_points',
                'criteria_value' => 100,
                'points_reward' => 25,
                'badge_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => '500 Points Champion',
                'code' => 'POINTS_500',
                'description' => 'Earn 500 total points',
                'category' => 'milestones',
                'criteria_type' => 'total_points',
                'criteria_value' => 500,
                'points_reward' => 100,
                'badge_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Reading Master',
                'code' => 'READ_10_BOOKS',
                'description' => 'Read 10 books in the library',
                'category' => 'academic',
                'criteria_type' => 'books_read',
                'criteria_value' => 10,
                'points_reward' => 50,
                'badge_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Assignment Ace',
                'code' => 'ASSIGNMENTS_PERFECT',
                'description' => 'Submit all assignments on time for a term',
                'category' => 'academic',
                'criteria_type' => 'assignments_on_time',
                'criteria_value' => 100,
                'points_reward' => 75,
                'badge_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'school_id' => 1,
                'name' => 'Community Helper',
                'code' => 'COMMUNITY_SERVICE',
                'description' => 'Complete 20 hours of community service',
                'category' => 'special',
                'criteria_type' => 'service_hours',
                'criteria_value' => 20,
                'points_reward' => 100,
                'badge_id' => null,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert badges
        foreach ($badges as $badge) {
            $db->table('badges')->insert($badge);
        }

        // Insert achievements
        foreach ($achievements as $achievement) {
            $db->table('achievements')->insert($achievement);
        }

        echo 'Inserted ' . count($badges) . ' sample badges and ' . count($achievements) . " sample achievements.\n";
    }
}
