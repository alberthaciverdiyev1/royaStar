<?php

namespace App\Http\Controllers;

use App\Modules\AcceptedStudent\Models\AcceptedStudent;
use App\Modules\Popup\Models\Popup;

class HomeController extends Controller
{
    public function index()
    {
        // Top 6 by custom (admin-entered) university exam points.
        $topAccepted = AcceptedStudent::query()->top(6)->get();

        // Active popups for the home page. The frontend picks one at random per
        // visit and honours the visitor's "don't show again" preference.
        $popups = Popup::query()->active()->get()->map(fn (Popup $popup) => [
            'id' => $popup->id,
            'title' => $popup->title,
            'content' => $popup->content,
            'image' => $popup->image,
            // Include updated_at so an edited popup is shown to visitors again.
            'version' => $popup->updated_at?->getTimestamp() ?? 0,
        ])->values();

        return view('index', [
            'isIndex' => true,
            'topAccepted' => $topAccepted,
            'popups' => $popups,
        ]);
    }

    public function acceptedStudents()
    {
        // All published records, highest points first.
        $accepted = AcceptedStudent::query()->published()->orderByDesc('exam_points')->orderByDesc('id')->get();

        return view('pages.accepted-students', [
            'accepted' => $accepted,
        ]);
    }
}
