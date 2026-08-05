<?php

namespace App\Modules\Star\Services;

use App\Modules\Star\Models\Star;
use App\Modules\Star\Models\UserStar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StarService
{
    public function award(int $userId, string $type, ?string $refType = null, ?int $refId = null, array $meta = []): void
    {
        $star = Star::where('type', $type)->first();
        if (!$star) return;

        $exists = UserStar::where('user_id', $userId)
            ->where('star_id', $star->id)
            ->when($refType, fn($q) => $q->where('reference_type', $refType))
            ->when($refId !== null, fn($q) => $q->where('reference_id', $refId))
            ->exists();

        if ($exists) return;

        UserStar::create([
            'user_id' => $userId,
            'star_id' => $star->id,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'metadata' => $meta,
        ]);
    }

    public function awardDailyLogin(int $userId): void
    {
        $today = (int) now()->format('Ymd');
        $this->award($userId, 'daily_login', 'daily', $today);

        $this->checkLoginStreak($userId);
    }

    public function awardLessonCompleted(int $userId, int $lessonId): void
    {
        $this->award($userId, 'lesson_completed', 'lesson', $lessonId);
    }

    public function awardQuizCompleted(int $userId, int $quizId): void
    {
        $this->award($userId, 'quiz_completed', 'quiz', $quizId);
    }

    public function awardQuizPerfect(int $userId, int $quizId): void
    {
        $this->award($userId, 'quiz_perfect', 'quiz', $quizId);
    }

    public function awardExamPassed(int $userId, int $examId): void
    {
        $this->award($userId, 'exam_passed', 'exam', $examId);
    }

    public function awardExamExcellent(int $userId, int $examId): void
    {
        $this->award($userId, 'exam_excellent', 'exam', $examId);
    }

    private function checkLoginStreak(int $userId): void
    {
        $star = Star::where('type', 'daily_login')->first();
        if (!$star) return;

        $last7 = UserStar::where('user_id', $userId)
            ->where('star_id', $star->id)
            ->where('reference_type', 'daily')
            ->orderBy('reference_id', 'desc')
            ->limit(7)
            ->pluck('reference_id');

        if ($last7->count() < 7) return;

        $dates = $last7->sort()->values();
        $expected = collect(range(6, 0))->map(fn($i) => (int) now()->subDays($i)->format('Ymd'));

        if ($dates->toArray() !== $expected->toArray()) return;

        $this->award($userId, 'login_streak', 'streak', (int) now()->format('Ymd'));
    }

    public function getUserTotalStars(int $userId, ?string $month = null): int
    {
        $query = UserStar::where('user_id', $userId)
            ->join('stars', 'user_stars.star_id', '=', 'stars.id');

        if ($month !== null) {
            $start = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
            $end = \Carbon\Carbon::parse($month . '-01')->endOfMonth();
            $query->whereBetween('user_stars.created_at', [$start, $end]);
        }

        return (int) $query->sum('stars.point');
    }

    public function getStarHistory(int $userId): Collection
    {
        return UserStar::where('user_id', $userId)
            ->with('star')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($us) => [
                'type' => $us->star->type,
                'point' => $us->star->point,
                'reference_type' => $us->reference_type,
                'reference_id' => $us->reference_id,
                'created_at' => $us->created_at,
            ]);
    }
}
