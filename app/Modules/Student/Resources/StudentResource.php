<?php

namespace App\Modules\Student\Resources;

use App\Modules\Grade\Resources\GradeResource;
use App\Modules\City\Resources\CityResource;
use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class StudentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'grade_id' => $this->grade_id,
            'city_id' => $this->city_id,
            'school_name' => $this->school_name,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'level' => $this->level,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
            $this->mergeWhen($this->relationLoaded('grade'), [
                'grade' => new GradeResource($this->grade),
            ]),
            $this->mergeWhen($this->relationLoaded('city'), [
                'city' => new CityResource($this->city),
            ]),
        ];
    }
}
