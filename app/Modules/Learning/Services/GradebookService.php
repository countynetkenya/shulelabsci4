<?php

namespace App\Modules\Learning\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * GradebookService - Handles exam results and report card generation.
 */
class GradebookService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Enter exam result for a student.
     */
    public function enterResult(
        int $examId,
        int $studentId,
        int $subjectId,
        int $classId,
        float $score,
        ?int $enteredBy = null
    ): int {
        // Get grade from score
        $gradeInfo = $this->getGradeFromScore($score);

        $data = [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'class_id' => $classId,
            'score' => $score,
            'grade' => $gradeInfo['grade'],
            'points' => $gradeInfo['points'],
            'remarks' => $gradeInfo['remarks'],
            'entered_by' => $enteredBy ?? session('user_id'),
        ];

        // Check for existing
        $existing = $this->db->table('exam_results')
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('exam_results')
                ->where('id', $existing['id'])
                ->update($data);
            return (int) $existing['id'];
        }

        $this->db->table('exam_results')->insert($data);
        return (int) $this->db->insertID();
    }

    /**
     * Get grade information from score.
     */
    public function getGradeFromScore(float $score, ?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? session('school_id');

        $grade = $this->db->table('grading_scales')
            ->where('school_id', $schoolId)
            ->where('is_active', 1)
            ->where('min_score <=', $score)
            ->where('max_score >=', $score)
            ->get()
            ->getRowArray();

        if ($grade) {
            return [
                'grade' => $grade['grade'],
                'points' => (float) $grade['points'],
                'remarks' => $grade['remarks'],
            ];
        }

        return ['grade' => '-', 'points' => 0, 'remarks' => null];
    }

    /**
     * Get student results for an exam.
     */
    public function getStudentExamResults(int $studentId, int $examId): array
    {
        return $this->db->table('exam_results er')
            ->select('er.*, s.name as subject_name, s.code as subject_code')
            ->join('subjects s', 's.id = er.subject_id')
            ->where('er.student_id', $studentId)
            ->where('er.exam_id', $examId)
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Generate report card for a student.
     */
    public function generateReportCard(int $studentId, string $academicYear, string $term): array
    {
        $schoolId = session('school_id');

        // Get student info
        $student = $this->db->table('users')
            ->select('users.*, se.class_id')
            ->join('student_enrollments se', 'se.student_id = users.id')
            ->where('users.id', $studentId)
            ->get()
            ->getRowArray();

        if (!$student) {
            throw new \RuntimeException('Student not found');
        }

        // Get exams for the term
        $exams = $this->db->table('exams')
            ->where('school_id', $schoolId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('status', 'completed')
            ->get()
            ->getResultArray();

        $examIds = array_column($exams, 'id');
        if (empty($examIds)) {
            throw new \RuntimeException('No completed exams found for this term');
        }

        // Get all results for the student
        $results = $this->db->table('exam_results er')
            ->select('er.*, s.name as subject_name, s.code as subject_code, e.name as exam_name, e.weight_percentage')
            ->join('subjects s', 's.id = er.subject_id')
            ->join('exams e', 'e.id = er.exam_id')
            ->where('er.student_id', $studentId)
            ->whereIn('er.exam_id', $examIds)
            ->get()
            ->getResultArray();

        // Calculate aggregates
        $subjectScores = [];
        foreach ($results as $result) {
            $subjectId = $result['subject_id'];
            if (!isset($subjectScores[$subjectId])) {
                $subjectScores[$subjectId] = [
                    'subject_name' => $result['subject_name'],
                    'subject_code' => $result['subject_code'],
                    'weighted_score' => 0,
                    'total_weight' => 0,
                    'exams' => [],
                ];
            }
            $weight = $result['weight_percentage'] / 100;
            $subjectScores[$subjectId]['weighted_score'] += $result['score'] * $weight;
            $subjectScores[$subjectId]['total_weight'] += $weight;
            $subjectScores[$subjectId]['exams'][] = [
                'exam_name' => $result['exam_name'],
                'score' => $result['score'],
                'grade' => $result['grade'],
            ];
        }

        // Calculate final grades
        $totalPoints = 0;
        $subjectCount = 0;
        foreach ($subjectScores as &$subject) {
            if ($subject['total_weight'] > 0) {
                $finalScore = $subject['weighted_score'] / $subject['total_weight'];
                $gradeInfo = $this->getGradeFromScore($finalScore, $schoolId);
                $subject['final_score'] = round($finalScore, 2);
                $subject['final_grade'] = $gradeInfo['grade'];
                $subject['points'] = $gradeInfo['points'];
                $totalPoints += $gradeInfo['points'];
                $subjectCount++;
            }
        }

        $meanPoints = $subjectCount > 0 ? $totalPoints / $subjectCount : 0;
        $meanGrade = $this->getGradeFromPoints($meanPoints, $schoolId);

        // Calculate positions
        $positions = $this->calculatePositions($student['class_id'], $academicYear, $term);

        // Save report card
        $reportCard = [
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'class_id' => $student['class_id'],
            'academic_year' => $academicYear,
            'term' => $term,
            'total_points' => round($totalPoints, 2),
            'average_score' => $subjectCount > 0 ? round(array_sum(array_column($subjectScores, 'final_score')) / $subjectCount, 2) : 0,
            'mean_grade' => $meanGrade,
            'class_position' => $positions[$studentId]['class_position'] ?? null,
            'overall_position' => $positions[$studentId]['overall_position'] ?? null,
            'status' => 'generated',
            'generated_at' => date('Y-m-d H:i:s'),
        ];

        // Insert or update report card
        $existing = $this->db->table('report_cards')
            ->where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('report_cards')
                ->where('id', $existing['id'])
                ->update($reportCard);
            $reportCardId = (int) $existing['id'];
        } else {
            $this->db->table('report_cards')->insert($reportCard);
            $reportCardId = (int) $this->db->insertID();
        }

        return [
            'report_card_id' => $reportCardId,
            'student' => $student,
            'subjects' => array_values($subjectScores),
            'summary' => $reportCard,
        ];
    }

    /**
     * Get grade from points.
     */
    private function getGradeFromPoints(float $points, int $schoolId): string
    {
        $grade = $this->db->table('grading_scales')
            ->where('school_id', $schoolId)
            ->where('is_active', 1)
            ->where('points >=', $points)
            ->orderBy('points', 'ASC')
            ->get()
            ->getRowArray();

        return $grade['grade'] ?? '-';
    }

    /**
     * Calculate class positions.
     */
    private function calculatePositions(int $classId, string $academicYear, string $term): array
    {
        $results = $this->db->table('report_cards')
            ->select('student_id, total_points')
            ->where('class_id', $classId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->orderBy('total_points', 'DESC')
            ->get()
            ->getResultArray();

        $positions = [];
        $rank = 1;
        $previousPoints = null;
        $sameRankCount = 0;

        foreach ($results as $result) {
            if ($previousPoints !== null && $result['total_points'] < $previousPoints) {
                $rank += $sameRankCount;
                $sameRankCount = 1;
            } else {
                $sameRankCount++;
            }

            $positions[$result['student_id']] = [
                'class_position' => $rank,
                'overall_position' => $rank,
            ];
            $previousPoints = $result['total_points'];
        }

        return $positions;
    }
}
