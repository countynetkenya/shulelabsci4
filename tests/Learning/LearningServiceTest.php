<?php

namespace Tests\Learning;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Services\LearningService;

/**
 * @internal
 */
final class LearningServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;
    protected LearningService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LearningService();
    }

    public function testGetSchoolCourses(): void
    {
        // Create test course
        $this->service->createCourse(6, 1, 'Mathematics Grade 1', 'MATH101', 25);

        $courses = $this->service->getSchoolCourses(6);

        $this->assertIsArray($courses);
        $this->assertGreaterThan(0, count($courses));
    }

    public function testCreateCourse(): void
    {
        $result = $this->service->createCourse(6, 1, 'English Language', 'ENG101', 26);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('course_id', $result);

        // Verify course
        $course = model('App\Models\CourseModel')->find($result['course_id']);
        $this->assertEquals('English Language', $course['course_name']);
        $this->assertEquals('ENG101', $course['course_code']);
        $this->assertEquals(6, $course['school_id']);
        $this->assertEquals(1, $course['class_id']);
    }

    public function testGetCourseAssignments(): void
    {
        // Create course and assignment
        $courseResult = $this->service->createCourse(6, 1, 'Science', 'SCI101', 27);
        $courseId = $courseResult['course_id'];

        $this->service->createAssignment($courseId, 6, 'Lab Report 1', 'Write a lab report', '2025-12-15', 50);

        $assignments = $this->service->getCourseAssignments($courseId);

        $this->assertIsArray($assignments);
        $this->assertGreaterThan(0, count($assignments));
    }

    public function testCreateAssignment(): void
    {
        // Create course first
        $courseResult = $this->service->createCourse(6, 2, 'History', 'HIST101', 28);
        $courseId = $courseResult['course_id'];

        $result = $this->service->createAssignment(
            $courseId,
            6,
            'Essay on Ancient Egypt',
            'Write 500 words about ancient Egyptian civilization',
            '2025-12-20',
            100
        );

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('assignment_id', $result);

        // Verify assignment
        $assignment = model('App\Models\AssignmentModel')->find($result['assignment_id']);
        $this->assertEquals('Essay on Ancient Egypt', $assignment['title']);
        $this->assertEquals(100, $assignment['max_points']);
    }

    public function testSubmitGrade(): void
    {
        // Create course and assignment
        $courseResult = $this->service->createCourse(6, 3, 'Physics', 'PHY101', 29);
        $courseId = $courseResult['course_id'];

        $assignmentResult = $this->service->createAssignment($courseId, 6, 'Quiz 1', 'Physics quiz', '2025-12-10', 50);
        $assignmentId = $assignmentResult['assignment_id'];

        // Submit grade
        $result = $this->service->submitGrade($assignmentId, 50, 6, 45, 'Great work!');

        $this->assertTrue($result['success']);

        // Verify grade
        $grade = model('App\Models\GradeModel')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', 50)
            ->first();

        $this->assertNotNull($grade);
        $this->assertEquals(45, $grade['points_earned']);
        $this->assertEquals('Great work!', $grade['feedback']);
    }

    public function testCannotExceedMaxPoints(): void
    {
        // Create course and assignment
        $courseResult = $this->service->createCourse(6, 4, 'Chemistry', 'CHEM101', 30);
        $courseId = $courseResult['course_id'];

        $assignmentResult = $this->service->createAssignment($courseId, 6, 'Lab Test', 'Chemistry lab', '2025-12-12', 100);
        $assignmentId = $assignmentResult['assignment_id'];

        // Try to submit grade exceeding max
        $result = $this->service->submitGrade($assignmentId, 51, 6, 105, 'Exceeded');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('exceed maximum', $result['message']);
    }

    public function testUpdateExistingGrade(): void
    {
        // Create course and assignment
        $courseResult = $this->service->createCourse(6, 5, 'Biology', 'BIO101', 31);
        $courseId = $courseResult['course_id'];

        $assignmentResult = $this->service->createAssignment($courseId, 6, 'Midterm', 'Biology midterm', '2025-12-18', 100);
        $assignmentId = $assignmentResult['assignment_id'];

        // Submit initial grade
        $this->service->submitGrade($assignmentId, 52, 6, 80, 'Good');

        // Update grade
        $result = $this->service->submitGrade($assignmentId, 52, 6, 85, 'Excellent!');

        $this->assertTrue($result['success']);

        // Verify updated grade
        $grade = model('App\Models\GradeModel')
            ->where('assignment_id', $assignmentId)
            ->where('student_id', 52)
            ->first();

        $this->assertEquals(85, $grade['points_earned']);
        $this->assertEquals('Excellent!', $grade['feedback']);
    }

    public function testGetStudentGrades(): void
    {
        // Create course and assignments
        $courseResult = $this->service->createCourse(6, 6, 'Geography', 'GEO101', 32);
        $courseId = $courseResult['course_id'];

        $assignment1 = $this->service->createAssignment($courseId, 6, 'Quiz 1', 'Geo quiz 1', '2025-12-05', 50);
        $assignment2 = $this->service->createAssignment($courseId, 6, 'Quiz 2', 'Geo quiz 2', '2025-12-12', 50);

        // Submit grades
        $this->service->submitGrade($assignment1['assignment_id'], 53, 6, 40, 'Good');
        $this->service->submitGrade($assignment2['assignment_id'], 53, 6, 45, 'Excellent');

        $grades = $this->service->getStudentGrades(53, $courseId);

        $this->assertIsArray($grades);
        $this->assertCount(2, $grades);
    }

    public function testGetCourseAverage(): void
    {
        // Create course and assignments
        $courseResult = $this->service->createCourse(6, 7, 'Art', 'ART101', 33);
        $courseId = $courseResult['course_id'];

        $assignment1 = $this->service->createAssignment($courseId, 6, 'Drawing', 'Art drawing', '2025-12-08', 100);
        $assignment2 = $this->service->createAssignment($courseId, 6, 'Painting', 'Art painting', '2025-12-15', 100);

        // Submit grades
        $this->service->submitGrade($assignment1['assignment_id'], 54, 6, 80, 'Good');
        $this->service->submitGrade($assignment2['assignment_id'], 54, 6, 90, 'Excellent');

        $average = $this->service->getCourseAverage(54, $courseId);

        $this->assertNotNull($average);
        $this->assertEquals(85.0, $average);
    }

    public function testGetAssignmentStats(): void
    {
        // Create course and assignment
        $courseResult = $this->service->createCourse(6, 8, 'Music', 'MUS101', 34);
        $courseId = $courseResult['course_id'];

        $assignmentResult = $this->service->createAssignment($courseId, 6, 'Performance', 'Music performance', '2025-12-20', 100);
        $assignmentId = $assignmentResult['assignment_id'];

        // Submit multiple grades
        $this->service->submitGrade($assignmentId, 55, 6, 85, 'Good');
        $this->service->submitGrade($assignmentId, 56, 6, 90, 'Excellent');
        $this->service->submitGrade($assignmentId, 57, 6, 78, 'Fair');

        $stats = $this->service->getAssignmentStats($assignmentId);

        $this->assertIsArray($stats);
        $this->assertEquals(3, $stats['total_submissions']);
        $this->assertEquals(90, $stats['highest_score']);
        $this->assertEquals(78, $stats['lowest_score']);
        $this->assertEqualsWithDelta(84.33, $stats['average_score'], 0.1);
    }
}
