<?php

namespace App\Http\Controllers;

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\LessonReview;
use App\Modules\Topic\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumController extends Controller
{
    public function topics()
    {
        $search = request('search');
        $topics = Topic::when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate(20)
            ->withQueryString();

        return view('pages.topics', compact('topics', 'search'));
    }

    public function topicDetail(Topic $topic)
    {
        $search = request('search');
        $lessons = $topic->lessons()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate(20)
            ->withQueryString();

        return view('pages.subtopics', compact('topic', 'lessons', 'search'));
    }

    public function lesson($id)
    {
        $lesson = Lesson::with(['topic', 'videos', 'quizzes.questions'])->findOrFail($id);

        // NOTE:
        // - Lesson view counting lives in UpdateLessonProgressAction (first
        //   engagement per student), so simply opening the page does not inflate it.
        // - The "lesson completed" star is awarded there too, only when progress
        //   reaches 100% — not by merely opening the lesson page.

        // Get existing review if user is authenticated
        $user = Auth::user();
        $existingReview = null;
        if ($user) {
            $existingReview = LessonReview::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();
        }

        return view('pages.lesson', compact('lesson', 'existingReview'));
    }

    public function lessonRate(Request $request, $id)
    {
        // Lesson must exist, and at least one of rating/review is required.
        Lesson::findOrFail($id);

        $request->validate([
            'rating' => 'required_without:review|integer|min:1|max:5',
            'review' => 'required_without:rating|string|max:1000',
        ]);

        $user = Auth::user();

        $exists = LessonReview::where('user_id', $user->id)
            ->where('lesson_id', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Siz artıq bu dərsə rəy vermisiniz.',
            ], 409);
        }

        LessonReview::create([
            'user_id' => $user->id,
            'lesson_id' => $id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }
}
