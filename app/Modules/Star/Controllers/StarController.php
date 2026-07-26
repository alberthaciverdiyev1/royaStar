<?php

namespace App\Modules\Star\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Star\Models\Star;
use App\Modules\Star\Actions\ShowStarAction;
use App\Modules\Star\Actions\UpdateStarAction;
use App\Modules\Star\Requests\UpdateStarRequest;
use App\Modules\Star\Resources\StarResource;
use App\Modules\Star\Services\StarService;
use Illuminate\Http\JsonResponse;

class StarController extends Controller
{
    public function __construct(
        private readonly ShowStarAction   $showStarAction,
        private readonly UpdateStarAction $updateStarAction,
        private readonly StarService      $starService,
    ) {}

    public function index(): JsonResponse
    {
        $stars = Star::orderBy('sort_order')->get();
        $collection = StarResource::collection($stars);
        return apiResponse(data: $collection);
    }

    public function show(int $star): JsonResponse
    {
        return apiResponse(data: new StarResource($this->showStarAction->execute($star)));
    }

    public function update(int $star, UpdateStarRequest $request): JsonResponse
    {
        return apiResponse(data: new StarResource($this->updateStarAction->execute($star, $request->validated())), message: 'crud.updated');
    }

    public function userStars(): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user, 401);

        return apiResponse(data: [
            'total' => $this->starService->getUserTotalStars($user->id),
            'history' => $this->starService->getStarHistory($user->id),
        ]);
    }
}
