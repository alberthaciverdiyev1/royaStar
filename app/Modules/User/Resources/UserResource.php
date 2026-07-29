<?php

namespace App\Modules\User\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $totalStars = $this->relationLoaded('userStars') 
            ? (int) $this->userStars->sum(fn($us) => $us->star?->point ?? 0)
            : (int) app(\App\Modules\Star\Services\StarService::class)->getUserTotalStars($this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'type' => $this->type,
            'is_approved' => $this->is_approved,
            'total_stars' => $totalStars,
            'student' => $this->relationLoaded('student') && $this->student ? [
                'id' => $this->student->id,
                'grade' => $this->student->grade?->number,
                'school' => $this->student->school_name,
                'city' => $this->student->city?->name,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
