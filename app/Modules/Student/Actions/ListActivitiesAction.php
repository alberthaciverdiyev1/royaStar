<?php

namespace App\Modules\Student\Actions;

use App\Modules\Student\Models\StudentActivity;

class ListActivitiesAction
{
    public function execute(int $studentId, int $limit = 20): array
    {
        return StudentActivity::where('student_id', $studentId)
            ->latest('id')
            ->take($limit)
            ->get()
            ->map(fn(StudentActivity $a) => [
                'type' => $a->type,
                'date' => $this->humanDate($a->created_at),
                'created_at' => $a->created_at->toDateTimeString(),
                ...($a->metadata ?? []),
            ])
            ->all();
    }

    private function humanDate(\Carbon\Carbon $date): string
    {
        $now = now();
        $diffInDays = (int) $date->diffInDays($now);
        $diffInWeeks = (int) $date->diffInWeeks($now);

        if ($diffInDays === 0) {
            $diffInHours = (int) $date->diffInHours($now);
            if ($diffInHours === 0) {
                $diffInMinutes = (int) $date->diffInMinutes($now);
                return $diffInMinutes <= 1 ? __('crud.just_now') : $diffInMinutes . ' ' . __('crud.minutes_ago');
            }
            return $diffInHours . ' ' . __('crud.hours_ago');
        }

        if ($diffInDays === 1) return __('crud.yesterday');
        if ($diffInDays === 2) return __('crud.two_days_ago');
        if ($diffInWeeks === 1) return __('crud.one_week_ago');

        return $date->format('d.m.Y');
    }
}
