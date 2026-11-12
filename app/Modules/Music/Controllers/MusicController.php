<?php

namespace App\Modules\Music\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Music\Actions\DeleteMusicTrackAction;
use App\Modules\Music\Actions\ListMusicTracksAction;
use App\Modules\Music\Actions\ShowMusicTrackAction;
use App\Modules\Music\Actions\StoreMusicTrackAction;
use App\Modules\Music\Actions\UpdateMusicTrackAction;
use App\Modules\Music\Models\MusicSetting;
use App\Modules\Music\Models\MusicTrack;
use App\Modules\Music\Requests\StoreMusicTrackRequest;
use App\Modules\Music\Requests\UpdateMusicSettingsRequest;
use App\Modules\Music\Requests\UpdateMusicTrackRequest;
use App\Modules\Music\Resources\MusicTrackResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MusicController extends Controller
{
    public function __construct(
        private readonly ListMusicTracksAction  $listMusicTracksAction,
        private readonly ShowMusicTrackAction   $showMusicTrackAction,
        private readonly StoreMusicTrackAction  $storeMusicTrackAction,
        private readonly UpdateMusicTrackAction $updateMusicTrackAction,
        private readonly DeleteMusicTrackAction $deleteMusicTrackAction,
    ) {}

    /**
     * Public playlist for the website player. Respects the general page
     * settings, so a page that is not allowed returns an empty playlist.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $page = $request->query('page');
        $page = is_string($page) && $page !== '' ? $page : null;

        if (!MusicSetting::current()->allowsPage($page)) {
            return apiResponse(data: []);
        }

        $tracks = MusicTrack::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn (MusicTrack $track) => new MusicTrackResource($track))
            ->values();

        return apiResponse(data: $tracks);
    }

    /**
     * Admin list — includes hidden tracks.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listMusicTracksAction->execute($request->all());

        return apiPaginated($paginator, transform: fn ($track) => new MusicTrackResource($track));
    }

    public function show(int $music): JsonResponse
    {
        return apiResponse(data: new MusicTrackResource($this->showMusicTrackAction->execute($music)));
    }

    public function store(StoreMusicTrackRequest $request): JsonResponse
    {
        return apiResponse(
            data: new MusicTrackResource($this->storeMusicTrackAction->execute($request->validated())),
            statusCode: 201,
        );
    }

    public function update(int $music, UpdateMusicTrackRequest $request): JsonResponse
    {
        return apiResponse(data: new MusicTrackResource($this->updateMusicTrackAction->execute($music, $request->validated())));
    }

    public function delete(int $music): JsonResponse
    {
        $this->deleteMusicTrackAction->execute($music);

        return apiResponse();
    }

    /**
     * General settings: which pages may play music.
     */
    public function settings(): JsonResponse
    {
        return apiResponse(data: MusicSetting::current()->toSettingsArray());
    }

    public function updateSettings(UpdateMusicSettingsRequest $request): JsonResponse
    {
        $setting = MusicSetting::query()->firstOrCreate([]);

        $setting->update([
            'play_on_all' => $request->boolean('play_on_all'),
            'pages' => $request->boolean('play_on_all') ? [] : ($request->validated()['pages'] ?? []),
        ]);

        return apiResponse(
            data: $setting->fresh()->toSettingsArray(),
            message: 'crud.updated',
        );
    }

    /**
     * Resolves a track's title/artist/thumbnail from its URL so the admin
     * panel can prefill the form. Supports YouTube (oEmbed) and direct audio
     * links (title derived from the file name).
     */
    public function metadata(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|string|max:2048|url:http,https',
        ]);

        $url = $validated['url'];
        $youtubeId = MusicTrack::extractYoutubeId($url);

        $title = null;
        $artist = null;
        $thumbnail = null;

        if ($youtubeId) {
            try {
                $response = Http::acceptJson()
                    ->connectTimeout(3)
                    ->timeout(6)
                    ->get('https://www.youtube.com/oembed', [
                        'url' => "https://www.youtube.com/watch?v={$youtubeId}",
                        'format' => 'json',
                    ]);

                if ($response->successful()) {
                    $payload = $response->json();
                    $title = $payload['title'] ?? null;
                    $artist = $payload['author_name'] ?? null;
                    $thumbnail = $payload['thumbnail_url'] ?? null;
                }
            } catch (\Throwable $e) {
                // Network/oEmbed failures are non-fatal — fall back below.
            }

            $thumbnail ??= "https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg";
            $title ??= 'YouTube videosu';
        } else {
            $path = parse_url($url, PHP_URL_PATH) ?: '';
            $name = urldecode(basename($path));
            $name = preg_replace('/\.[a-z0-9]{2,5}$/i', '', $name) ?? $name;
            $name = trim(preg_replace('/[\s\-_]+/', ' ', $name) ?? $name);

            if ($name !== '') {
                $title = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
            }
        }

        return apiResponse(data: [
            'title' => $title,
            'artist' => $artist,
            'thumbnail' => $thumbnail,
            'youtube_id' => $youtubeId,
            'source_type' => $youtubeId ? 'youtube' : 'audio',
        ]);
    }
}
