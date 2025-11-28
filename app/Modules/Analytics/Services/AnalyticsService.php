<?php

namespace App\Modules\Analytics\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * AnalyticsService - Provides predictive analytics and dashboard capabilities.
 */
class AnalyticsService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Identify at-risk students based on multiple factors.
     */
    public function identifyAtRiskStudents(): array
    {
        $schoolId = session('school_id');
        $identified = [];

        // Academic risk - Low grades
        $academicRisk = $this->identifyAcademicRisk($schoolId);
        foreach ($academicRisk as $student) {
            $identified[] = $this->createRiskRecord($student, 'academic');
        }

        // Attendance risk - High absences
        $attendanceRisk = $this->identifyAttendanceRisk($schoolId);
        foreach ($attendanceRisk as $student) {
            $identified[] = $this->createRiskRecord($student, 'attendance');
        }

        // Financial risk - Outstanding fees
        $financialRisk = $this->identifyFinancialRisk($schoolId);
        foreach ($financialRisk as $student) {
            $identified[] = $this->createRiskRecord($student, 'financial');
        }

        return $identified;
    }

    /**
     * Identify students with academic risk (low grades).
     */
    private function identifyAcademicRisk(int $schoolId): array
    {
        // In real implementation, would query exam_results and calculate averages
        // This is a placeholder
        return [];
    }

    /**
     * Identify students with attendance risk.
     */
    private function identifyAttendanceRisk(int $schoolId): array
    {
        // In real implementation, would query attendance and calculate rates
        // This is a placeholder
        return [];
    }

    /**
     * Identify students with financial risk.
     */
    private function identifyFinancialRisk(int $schoolId): array
    {
        // In real implementation, would query outstanding fees
        // This is a placeholder
        return [];
    }

    /**
     * Create at-risk student record.
     */
    private function createRiskRecord(array $student, string $category): int
    {
        $schoolId = session('school_id');

        // Calculate risk score based on factors
        $riskScore = $this->calculateRiskScore($student, $category);
        $riskLevel = $this->determineRiskLevel($riskScore);

        $this->db->table('at_risk_students')->insert([
            'school_id' => $schoolId,
            'student_id' => $student['id'],
            'risk_category' => $category,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'risk_factors' => json_encode($student['factors'] ?? []),
            'recommended_actions' => json_encode($this->getRecommendedActions($category, $riskLevel)),
            'intervention_status' => 'pending',
            'identified_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Calculate risk score.
     */
    private function calculateRiskScore(array $student, string $category): float
    {
        // Simplified scoring - real implementation would use ML model
        return match ($category) {
            'academic' => min(100, ($student['factors']['avg_grade'] ?? 50) < 50 ? 75 : 40),
            'attendance' => min(100, ($student['factors']['absence_rate'] ?? 0) * 100),
            'financial' => min(100, ($student['factors']['outstanding_ratio'] ?? 0) * 100),
            default => 50,
        };
    }

    /**
     * Determine risk level from score.
     */
    private function determineRiskLevel(float $score): string
    {
        return match (true) {
            $score >= 80 => 'critical',
            $score >= 60 => 'high',
            $score >= 40 => 'medium',
            default => 'low',
        };
    }

    /**
     * Get recommended actions based on risk.
     */
    private function getRecommendedActions(string $category, string $level): array
    {
        $actions = [
            'academic' => [
                'critical' => ['Schedule parent meeting', 'Assign tutor', 'Create remedial plan'],
                'high' => ['Extra homework support', 'Weekly progress check'],
                'medium' => ['Monitor progress', 'Encourage participation'],
                'low' => ['Continue monitoring'],
            ],
            'attendance' => [
                'critical' => ['Contact parents immediately', 'Home visit', 'Social worker referral'],
                'high' => ['Parent meeting', 'Attendance contract'],
                'medium' => ['Counselor check-in', 'Attendance reminder'],
                'low' => ['Continue monitoring'],
            ],
            'financial' => [
                'critical' => ['Payment plan discussion', 'Scholarship review'],
                'high' => ['Payment reminder', 'Financial aid options'],
                'medium' => ['Statement of account'],
                'low' => ['Continue monitoring'],
            ],
        ];

        return $actions[$category][$level] ?? ['Continue monitoring'];
    }

    /**
     * Generate financial forecast.
     */
    public function generateFinancialForecast(string $forecastType, string $periodType): array
    {
        $schoolId = session('school_id');

        // Get historical data
        $historical = $this->getHistoricalFinancialData($schoolId, $forecastType);

        // Simple moving average forecast (real implementation would use time series models)
        $forecast = $this->calculateForecast($historical);

        // Store forecast
        $this->db->table('financial_forecasts')->insert([
            'school_id' => $schoolId,
            'forecast_type' => $forecastType,
            'period_type' => $periodType,
            'period_start' => $forecast['period_start'],
            'period_end' => $forecast['period_end'],
            'forecast_amount' => $forecast['amount'],
            'confidence_level' => $forecast['confidence'],
            'model_version' => '1.0',
            'generated_at' => date('Y-m-d H:i:s'),
        ]);

        return $forecast;
    }

    /**
     * Get historical financial data.
     */
    private function getHistoricalFinancialData(int $schoolId, string $type): array
    {
        // Placeholder - would query from finance module
        return [];
    }

    /**
     * Calculate forecast using simple moving average.
     */
    private function calculateForecast(array $historical): array
    {
        // Simple placeholder
        return [
            'period_start' => date('Y-m-01', strtotime('+1 month')),
            'period_end' => date('Y-m-t', strtotime('+1 month')),
            'amount' => 0,
            'confidence' => 0.7,
        ];
    }

    /**
     * Record trend analysis data point.
     */
    public function recordTrendDataPoint(string $metricName, string $category, float $value, string $periodType = 'daily'): void
    {
        $schoolId = session('school_id');
        $periodDate = date('Y-m-d');

        // Get previous value
        $previous = $this->db->table('trend_analyses')
            ->where('school_id', $schoolId)
            ->where('metric_name', $metricName)
            ->where('period_date <', $periodDate)
            ->orderBy('period_date', 'DESC')
            ->get()
            ->getRowArray();

        $previousValue = $previous['value'] ?? null;
        $changePercent = $previousValue > 0 ? (($value - $previousValue) / $previousValue) * 100 : null;

        $trendDirection = null;
        if ($changePercent !== null) {
            $trendDirection = match (true) {
                $changePercent > 1 => 'up',
                $changePercent < -1 => 'down',
                default => 'stable',
            };
        }

        $this->db->table('trend_analyses')->insert([
            'school_id' => $schoolId,
            'metric_name' => $metricName,
            'metric_category' => $category,
            'period_date' => $periodDate,
            'period_type' => $periodType,
            'value' => $value,
            'previous_value' => $previousValue,
            'change_percent' => $changePercent,
            'trend_direction' => $trendDirection,
        ]);
    }

    /**
     * Get dashboard widgets.
     */
    public function getDashboardWidgets(int $dashboardId): array
    {
        return $this->db->table('analytics_widgets')
            ->where('dashboard_id', $dashboardId)
            ->where('is_active', 1)
            ->orderBy('position->>"$.row"', 'ASC')
            ->orderBy('position->>"$.col"', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Create custom dashboard.
     */
    public function createDashboard(string $name, ?string $description = null, array $layout = []): int
    {
        $schoolId = session('school_id');
        $userId = session('user_id');

        $this->db->table('analytics_dashboards')->insert([
            'school_id' => $schoolId,
            'name' => $name,
            'description' => $description,
            'layout' => json_encode($layout),
            'created_by' => $userId,
        ]);

        return (int) $this->db->insertID();
    }
}
