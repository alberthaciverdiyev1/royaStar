<?php

namespace App\Http\Controllers;

use App\Modules\AcceptedStudent\Models\AcceptedStudent;

class HomeController extends Controller
{
    public function index()
    {
        // Top 6 by custom (admin-entered) university exam points.
        $topAccepted = AcceptedStudent::query()->top(6)->get();

        return view('index', [
            'isIndex' => true,
            'topAccepted' => $topAccepted,
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
