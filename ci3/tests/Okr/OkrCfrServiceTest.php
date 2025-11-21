<?php

declare(strict_types=1);

use App\Services\Okr\OkrCfrService;
use PHPUnit\Framework\TestCase;

final class OkrCfrServiceTest extends TestCase
{
    public function testCalculateScoreBlendsProgressAndEngagement(): void
    {
        $service = new OkrCfrService();

        $objectives = [
            ['progress' => 0.8],
            ['progress' => 0.6],
            ['progress' => 1.0],
        ];

        $cfr = [
            ['type' => 'conversation', 'sentiment' => 'positive'],
            ['type' => 'feedback', 'sentiment' => 'positive'],
            ['type' => 'feedback', 'sentiment' => 'negative'],
            ['type' => 'recognition', 'sentiment' => 'positive'],
        ];

        $score = $service->calculateScore($objectives, $cfr);

        $this->assertSame(80.0, $score['okr_progress']);
        $this->assertEquals(71.43, $score['cfr_engagement'], '', 0.01);
        $this->assertEquals(77.43, $score['alignment_index'], '', 0.01);
        $this->assertSame(2, $score['cfr_breakdown']['feedback']);
        $this->assertSame(1, $score['cfr_breakdown']['conversation']);
        $this->assertSame(1, $score['cfr_breakdown']['recognition']);
    }

    public function testEmptyInputsReturnNeutralScores(): void
    {
        $service = new OkrCfrService();
        $score = $service->calculateScore([], []);

        $this->assertSame(0.0, $score['okr_progress']);
        $this->assertSame(50.0, $score['cfr_engagement']);
        $this->assertSame(15.0, $score['alignment_index']);
    }
}
