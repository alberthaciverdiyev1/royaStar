<?php

namespace App\Modules\Popup\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Popup\Actions\DeletePopupAction;
use App\Modules\Popup\Actions\ListPopupsAction;
use App\Modules\Popup\Actions\ShowPopupAction;
use App\Modules\Popup\Actions\StorePopupAction;
use App\Modules\Popup\Actions\UpdatePopupAction;
use App\Modules\Popup\Models\Popup;
use App\Modules\Popup\Requests\StorePopupRequest;
use App\Modules\Popup\Requests\UpdatePopupRequest;
use App\Modules\Popup\Resources\PopupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PopupController extends Controller
{
    public function __construct(
        private readonly ListPopupsAction   $listPopupsAction,
        private readonly ShowPopupAction    $showPopupAction,
        private readonly StorePopupAction   $storePopupAction,
        private readonly UpdatePopupAction  $updatePopupAction,
        private readonly DeletePopupAction  $deletePopupAction,
    ) {}

    /**
     * Admin list — includes hidden popups.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listPopupsAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($popup) => new PopupResource($popup));
    }

    /**
     * Public endpoint — a random published popup for website visitors.
     */
    public function random(): JsonResponse
    {
        $popup = Popup::query()->active()->inRandomOrder()->first();

        return apiResponse(data: $popup ? new PopupResource($popup) : null);
    }

    public function show(int $popup): JsonResponse
    {
        return apiResponse(data: new PopupResource($this->showPopupAction->execute($popup)));
    }

    public function store(StorePopupRequest $request): JsonResponse
    {
        return apiResponse(
            data: new PopupResource($this->storePopupAction->execute($request->validated())),
            statusCode: 201,
        );
    }

    public function update(int $popup, UpdatePopupRequest $request): JsonResponse
    {
        return apiResponse(data: new PopupResource($this->updatePopupAction->execute($popup, $request->validated())));
    }

    public function delete(int $popup): JsonResponse
    {
        $this->deletePopupAction->execute($popup);

        return apiResponse();
    }
}
