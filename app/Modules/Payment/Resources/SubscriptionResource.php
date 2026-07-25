<?php

namespace App\Modules\Payment\Resources;

use App\Modules\School\Resources\SchoolResource;
use App\Modules\Teacher\Resources\TeacherResource;
use App\Modules\Parent\Resources\FamilyResource;
use App\Modules\Student\Resources\StudentResource;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class SubscriptionResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'expires_at' => $this->expires_at?->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('plan'), [
                'plan' => new SubscriptionPlanResource($this->plan),
            ]),
            $this->mergeWhen($this->relationLoaded('school'), [
                'school' => new SchoolResource($this->school),
            ]),
            $this->mergeWhen($this->relationLoaded('teacher'), [
                'teacher' => new TeacherResource($this->teacher),
            ]),
            $this->mergeWhen($this->relationLoaded('family'), [
                'family' => new FamilyResource($this->family),
            ]),
            $this->mergeWhen($this->relationLoaded('student'), [
                'student' => new StudentResource($this->student),
            ]),
        ];
    }
}
