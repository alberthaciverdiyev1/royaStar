<?php

namespace App\Modules\AcceptedStudent\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class AcceptedStudentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'surname' => $this->surname,
            'image' => $this->image,
            'exam_points' => $this->exam_points,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
