<?php

namespace App\Services\Okr;

class OkrCfrService
{
    /**
     * @param iterable<array{progress?: float|int|string|null}> $objectives
     * @param iterable<array{type?: string|null, sentiment?: string|null}> $cfrRecords
     * @return array{
     *     okr_progress: float,
     *     cfr_engagement: float,
     *     alignment_index: float,
     *     cfr_breakdown: array<string, int>
     * }
     */
    public function calculateScore(iterable $objectives, iterable $cfrRecords): array
    {
        $objectiveScore = $this->calculateObjectiveScore($objectives);
        $engagement     = $this->calculateEngagementScore($cfrRecords);

        $alignment = round((($objectiveScore / 100) * 0.7 + ($engagement / 100) * 0.3) * 100, 2);

        return [
            'okr_progress' => round($objectiveScore, 2),
            'cfr_engagement' => round($engagement, 2),
            'alignment_index' => $alignment,
            'cfr_breakdown' => $this->summarizeCfr($cfrRecords),
        ];
    }

    /**
     * @param iterable<array{progress?: float|int|string|null}> $objectives
     */
    protected function calculateObjectiveScore(iterable $objectives): float
    {
        $objectives = is_array($objectives) ? $objectives : iterator_to_array($objectives, false);

        if ($objectives === []) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($objectives as $objective) {
            $progress = isset($objective['progress']) ? (float) $objective['progress'] : 0.0;
            $total += max(0.0, min($progress, 1.0));
        }

        return ($total / count($objectives)) * 100;
    }

    /**
     * @param iterable<array{type?: string|null, sentiment?: string|null}> $records
     */
    protected function calculateEngagementScore(iterable $records): float
    {
        $records = is_array($records) ? $records : iterator_to_array($records, false);

        if ($records === []) {
            return 50.0;
        }

        $typeWeights = [
            'conversation' => 0.3,
            'feedback' => 0.4,
            'recognition' => 0.3,
        ];

        $sentimentMap = [
            'positive' => 1,
            'neutral' => 0,
            'negative' => -1,
        ];

        $score = 0.0;
        $weightTotal = 0.0;

        foreach ($records as $record) {
            $type = strtolower($record['type'] ?? 'conversation');
            $sentiment = strtolower($record['sentiment'] ?? 'neutral');

            $weight = $typeWeights[$type] ?? 0.2;
            $score += $weight * ($sentimentMap[$sentiment] ?? 0);
            $weightTotal += $weight;
        }

        if ($weightTotal === 0.0) {
            return 50.0;
        }

        $normalized = max(-1.0, min(1.0, $score / $weightTotal));

        return ($normalized + 1) * 50;
    }

    /**
     * @param iterable<array{type?: string|null, sentiment?: string|null}> $records
     * @return array<string, int>
     */
    protected function summarizeCfr(iterable $records): array
    {
        $records = is_array($records) ? $records : iterator_to_array($records, false);

        $summary = [
            'conversation' => 0,
            'feedback' => 0,
            'recognition' => 0,
            'positive' => 0,
            'neutral' => 0,
            'negative' => 0,
        ];

        foreach ($records as $record) {
            $type = strtolower($record['type'] ?? 'conversation');
            $sentiment = strtolower($record['sentiment'] ?? 'neutral');

            if (isset($summary[$type])) {
                $summary[$type]++;
            }

            if (isset($summary[$sentiment])) {
                $summary[$sentiment]++;
            }
        }

        return $summary;
    }
}
