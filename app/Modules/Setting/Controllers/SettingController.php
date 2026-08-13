<?php

namespace App\Modules\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Setting\Actions\ShowSettingAction;
use App\Modules\Setting\Actions\UpdateSettingAction;
use App\Modules\Setting\Models\Setting;
use App\Modules\Setting\Requests\UpdateSettingRequest;
use App\Modules\Setting\Resources\SettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class SettingController extends Controller
{
    public function __construct(
        private readonly ShowSettingAction    $showSettingAction,
        private readonly UpdateSettingAction  $updateSettingAction,
    ) {}

    #[OA\Get(path: '/settings', summary: 'Get settings', tags: ['Settings'],
        security: [[]],
        responses: [new OA\Response(response: 200, description: 'Settings data')]),
    ]
    public function show(): JsonResponse
    {
        return apiResponse(data: new SettingResource($this->showSettingAction->execute()));
    }

    #[OA\Put(path: '/admin/settings', summary: 'Update settings',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'app_name', type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
            new OA\Property(property: 'keywords', type: 'string'),
            new OA\Property(property: 'logo', type: 'string'),
            new OA\Property(property: 'favicon', type: 'string'),
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'facebook', type: 'string'),
            new OA\Property(property: 'instagram', type: 'string'),
            new OA\Property(property: 'maintenance_mode', type: 'boolean'),
            new OA\Property(property: 'registration_open', type: 'boolean'),
        ])),
        tags: ['Settings'],
        responses: [new OA\Response(response: 200, description: 'Settings updated')]),
    ]
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        return apiResponse(
            data: new SettingResource($this->updateSettingAction->execute($request->validated())),
            message: 'crud.updated'
        );
    }

    #[OA\Get(path: '/admin/website-texts', summary: 'List editable website texts',
        tags: ['Settings'],
        responses: [new OA\Response(response: 200, description: 'Grouped website texts')]),
    ]
    public function texts(): JsonResponse
    {
        $stored = website_text_stored();
        $groups = [];

        foreach (config('website_texts', []) as $groupKey => $group) {
            $items = [];
            foreach ($group['keys'] ?? [] as $key => $fallback) {
                $items[] = [
                    'key'      => $key,
                    'fallback' => $fallback,
                    'value'    => $stored[$key] ?? null,
                ];
            }

            $groups[] = [
                'key'   => $groupKey,
                'label' => $group['label'] ?? $groupKey,
                'icon'  => $group['icon'] ?? 'text_fields',
                'items' => $items,
            ];
        }

        return apiResponse(data: $groups);
    }

    #[OA\Put(path: '/admin/website-texts', summary: 'Update website texts',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'texts', type: 'object'),
        ])),
        tags: ['Settings'],
        responses: [new OA\Response(response: 200, description: 'Website texts updated')]),
    ]
    public function updateTexts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'texts'      => 'present|array',
            'texts.*'    => 'nullable|string',
        ]);

        $allowedKeys = array_keys(website_text_fallbacks());

        // Full-replace semantics: the payload is the entire desired override map.
        // Non-empty values become overrides; empty/missing keys fall back to defaults.
        $clean = [];
        foreach ($validated['texts'] as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }
            $value = trim((string) $value);
            if ($value !== '') {
                $clean[$key] = $value;
            }
        }

        $setting = Setting::firstOrCreate([], ['app_name' => 'RoyaStar']);
        $setting->texts = $clean;
        $setting->save();

        Cache::forget('website_texts.stored');

        return apiResponse(
            data: $setting->texts,
            message: 'crud.updated'
        );
    }
}
