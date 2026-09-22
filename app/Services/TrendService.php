<?php

namespace App\Services;

use App\Models\WeeklyDiseaseTrend;
use App\Services\AI\AiGateway;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrendService
{
    public function __construct(private readonly AiGateway $ai) {}

    public function generate(?Carbon $weekStart = null): int
    {
        $start = ($weekStart ?? now()->startOfWeek())->copy()->startOfDay();
        $end = $start->copy()->addWeek();

        $rows = DB::table('consultation_conditions as cc')
            ->join('consultations as c', 'c.consultation_id', '=', 'cc.consultation_id')
            ->join('appointments as a', 'a.appointment_id', '=', 'c.appointment_id')
            ->join('diagnosis_conditions as dc', 'dc.condition_id', '=', 'cc.condition_id')
            ->where('cc.finalized', true)
            ->where('a.status', 'COMPLETED')
            ->where('c.completed_at','>=',$start)->where('c.completed_at','<',$end)
            ->groupBy('cc.condition_id', 'dc.condition_name')
            ->selectRaw('cc.condition_id, dc.condition_name, COUNT(*) case_count')
            ->get();

        $total = max(1, (int) $rows->sum('case_count'));
        $commentary = $rows->isNotEmpty() ? $this->ai->diseaseTrendCommentary($rows->toArray()) : [];
        return DB::transaction(function () use ($rows, $start, $total, $commentary) {
            // Remove only conditions absent from this week's recalculated results.
            // An empty result clears this week while preserving every other week.
            WeeklyDiseaseTrend::where('week_start', $start->toDateString())
                ->whereNotIn('condition_id', $rows->pluck('condition_id')->all())
                ->delete();
            $saved = 0;

            foreach ($rows as $row) {
                $previous = (int) WeeklyDiseaseTrend::where('condition_id', $row->condition_id)
                    ->where('week_start', $start->copy()->subWeek()->toDateString())
                    ->value('case_count');

                $score = $previous > 0 ? ($row->case_count - $previous) / $previous : ($row->case_count >= 5 ? 1 : 0);
                $isSpike = $row->case_count >= 5 && $score >= 0.5;

                WeeklyDiseaseTrend::updateOrCreate(
                    ['week_start' => $start->toDateString(), 'condition_id' => $row->condition_id],
                    [
                        'case_count' => $row->case_count,
                        'total_cases' => $total,
                        'percentage' => round(($row->case_count / $total) * 100, 2),
                        'previous_case_count' => $previous,
                        'spike_score' => round($score, 3),
                        'is_spike' => $isSpike,
                        'generated_at' => now(),
                    ]
                );
                $saved++;
            }

            if ($rows->isNotEmpty()) {
                WeeklyDiseaseTrend::where('week_start', $start->toDateString())
                    ->update(['ai_commentary' => mb_substr((string) ($commentary['summary'] ?? ''), 0, 1000)]);
            }

            return $saved;
        });
    }
}
