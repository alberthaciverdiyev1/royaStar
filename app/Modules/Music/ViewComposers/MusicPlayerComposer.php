<?php

namespace App\Modules\Music\ViewComposers;

use App\Modules\Music\Models\MusicSetting;
use App\Modules\Music\Models\MusicTrack;
use App\Modules\Music\Resources\MusicTrackResource;
use Illuminate\View\View;

class MusicPlayerComposer
{
    /**
     * Shares the playlist for the current web page with the global Blade
     * layout, which renders the floating player.
     */
    public function compose(View $view): void
    {
        $page = MusicSetting::pageForRoute(request()->route()?->getName());

        $tracks = MusicSetting::current()->allowsPage($page)
            ? MusicTrack::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn (MusicTrack $track) => (new MusicTrackResource($track))->resolve())
                ->values()
                ->all()
            : [];

        $view->with('musicPlayerTracks', $tracks);
    }
}
