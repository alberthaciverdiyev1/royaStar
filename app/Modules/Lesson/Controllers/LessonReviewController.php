<?php

namespace App\Modules\Lesson\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lesson\Models\LessonReview;
use App\Modules\Lesson\Resources\LessonReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LessonReview::with(['user', 'lesson'])
            ->orderBy('created_at', 'desc');

        $perPage = min((int) ($request->input('per_page', 20)), 100);

        $paginator = $query->paginate($perPage);

        return apiPaginated($paginator, transform: fn($review) => new LessonReviewResource($review));
    }
}
