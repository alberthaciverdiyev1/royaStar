<?php

namespace App\Modules\Banner\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Banner\Actions\DeleteBannerAction;
use App\Modules\Banner\Actions\ListBannersAction;
use App\Modules\Banner\Actions\ShowBannerAction;
use App\Modules\Banner\Actions\StoreBannerAction;
use App\Modules\Banner\Actions\UpdateBannerAction;
use App\Modules\Banner\Models\Banner;
use App\Modules\Banner\Requests\StoreBannerRequest;
use App\Modules\Banner\Requests\UpdateBannerRequest;
use App\Modules\Banner\Resources\BannerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    public function __construct(
        private readonly ListBannersAction  $listBannersAction,
        private readonly ShowBannerAction   $showBannerAction,
        private readonly StoreBannerAction  $storeBannerAction,
        private readonly UpdateBannerAction $updateBannerAction,
        private readonly DeleteBannerAction $deleteBannerAction,
    ) {}

    #[OA\Get(
        path: '/banners',
        summary: 'List all banners',
        security: [[]],
        tags: ['Banners'],
        parameters: [
            new OA\QueryParameter(name: 'all', description: 'Set to true to get all banners without pagination', schema: new OA\Schema(type: 'boolean')),
            new OA\QueryParameter(name: 'search', description: 'Search by title or subtitle', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. title, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of banners')]),
    ]
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return apiResponse(data: BannerResource::collection(Banner::all()));
        }

        $paginator = $this->listBannersAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($banner) => new BannerResource($banner));
    }

    #[OA\Post(path: '/admin/banners', summary: 'Create banner', requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: 'image', type: 'string', nullable: true),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'subtitle', type: 'string', nullable: true),
    ])),
        tags: ['Banners'],
        responses: [new OA\Response(response: 201, description: 'Banner created')]),
    ]
    public function store(StoreBannerRequest $request): JsonResponse
    {
        return apiResponse(data: new BannerResource($this->storeBannerAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Get(path: '/banners/{banner}', summary: 'Get banner by ID', tags: ['Banners'],
        parameters: [
            new OA\PathParameter(name: 'banner', description: 'Banner ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Banner data')]),
    ]
    public function show(int $banner): JsonResponse
    {
        return apiResponse(data: new BannerResource($this->showBannerAction->execute($banner)));
    }

    #[OA\Put(path: '/admin/banners/{banner}', summary: 'Update banner', requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: 'image', type: 'string', nullable: true),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'subtitle', type: 'string', nullable: true),
    ])),
        tags: ['Banners'],
        responses: [new OA\Response(response: 200, description: 'Banner updated')]),
    ]
    public function update(int $banner, UpdateBannerRequest $request): JsonResponse
    {
        return apiResponse(data: new BannerResource($this->updateBannerAction->execute($banner, $request->validated())));
    }

    #[OA\Delete(path: '/admin/banners/{banner}', summary: 'Delete banner', tags: ['Banners'],
        responses: [new OA\Response(response: 200, description: 'Banner deleted')]),
    ]
    public function delete(int $banner): JsonResponse
    {
        $this->deleteBannerAction->execute($banner);
        return apiResponse();
    }
}
