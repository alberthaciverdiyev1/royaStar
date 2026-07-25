<?php

namespace App\Modules\City\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\City\Actions\DeleteCityAction;
use App\Modules\City\Actions\ListCitiesAction;
use App\Modules\City\Models\City;
use App\Modules\City\Actions\ShowCityAction;
use App\Modules\City\Actions\StoreCityAction;
use App\Modules\City\Actions\UpdateCityAction;
use App\Modules\City\Requests\StoreCityRequest;
use App\Modules\City\Requests\UpdateCityRequest;
use App\Modules\City\Resources\CityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CityController extends Controller
{
    public function __construct(
        private readonly ListCitiesAction $listCitiesAction,
        private readonly ShowCityAction   $showCityAction,
        private readonly StoreCityAction  $storeCityAction,
        private readonly UpdateCityAction $updateCityAction,
        private readonly DeleteCityAction $deleteCityAction,
    )
    {
    }

    #[OA\Get(
        path: '/cities',
        summary: 'List all cities',
        security: [[]],
        tags: ['Cities'],
        parameters: [
            new OA\QueryParameter(name: 'all', description: 'Set to true to get all cities without pagination', schema: new OA\Schema(type: 'boolean')),
            new OA\QueryParameter(name: 'search', description: 'Search by name', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of cities'
            )]),
    ]
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return apiResponse(data: CityResource::collection(City::all()));
        }

        $paginator = $this->listCitiesAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($city) => new CityResource($city));
    }

    #[OA\Post(path: '/admin/cities', summary: 'Create city', requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: 'name', properties: [
            new OA\Property(property: 'az', type: 'string'),
            new OA\Property(property: 'en', type: 'string'),
            new OA\Property(property: 'ru', type: 'string'),
        ], type: 'object'),
    ])),
        tags: ['Cities'],
        responses: [new OA\Response(response: 201, description: 'City created')]),
    ]
    public function store(StoreCityRequest $request): JsonResponse
    {
        return apiResponse(data: new CityResource($this->storeCityAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Get(path: '/cities/{city}', summary: 'Get city by ID', tags: ['Cities'],
        parameters: [
            new OA\PathParameter(name: 'city', description: 'City ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'City data')]),
    ]
    public function show(int $city): JsonResponse
    {
        return apiResponse(data: new CityResource($this->showCityAction->execute($city)));
    }

    #[OA\Put(path: '/admin/cities/{city}', summary: 'Update city', requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
        new OA\Property(property: 'name', properties: [
            new OA\Property(property: 'az', type: 'string'),
            new OA\Property(property: 'en', type: 'string'),
            new OA\Property(property: 'ru', type: 'string'),
        ], type: 'object'),
    ])),
        tags: ['Cities'],
        responses: [new OA\Response(response: 200, description: 'City updated')]),
    ]
    public function update(int $city, UpdateCityRequest $request): JsonResponse
    {
        return apiResponse(data: new CityResource($this->updateCityAction->execute($city, $request->validated())));
    }

    #[OA\Delete(path: '/admin/cities/{city}', summary: 'Delete city', tags: ['Cities'],
        responses: [new OA\Response(response: 200, description: 'City deleted')]),
    ]
    public function delete(int $city): JsonResponse
    {
        $this->deleteCityAction->execute($city);
        return apiResponse();
    }
}
