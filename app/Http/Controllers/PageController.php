<?php

namespace App\Http\Controllers;

use App\Modules\User\Models\User;
use App\Modules\Student\Models\Student;
use App\Modules\Grade\Models\Grade;
use App\Modules\City\Models\City;
use App\Modules\Topic\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    public function welcome()
    {
        return view('welcome');
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('topics');
        }

        return view('auth.login', [
            'isIndex' => true,
            'hideHeader' => true,
            'hideNavbar' => true,
        ]);
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (!$user->is_approved) {
            return redirect()->route('pending');
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('topics'));
    }

    public function signup()
    {
        if (Auth::check()) {
            return redirect()->route('topics');
        }

        return view('auth.signup', [
            'isIndex' => true,
            'hideHeader' => true,
            'hideNavbar' => true,
            'cities' => City::all(),
            'grades' => Grade::all(),
        ]);
    }

    public function signupPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'city_id' => 'nullable|exists:cities,id',
            'grade_id' => 'nullable|exists:grades,id',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'student',
            ]);

            $user->assignRole('student');

            Student::create([
                'user_id' => $user->id,
                'city_id' => $request->city_id,
                'grade_id' => $request->grade_id,
            ]);
        });

        return redirect()->route('pending')
            ->with('success', __('auth.registration_pending'));
    }

    public function pending()
    {
        if (Auth::check()) {
            return redirect()->route('topics');
        }

        return view('auth.pending', [
            'isIndex' => true,
            'hideHeader' => true,
            'hideNavbar' => true,
        ]);
    }

    public function index()
    {
        return view('index', [
            'isIndex' => true,
            'hideNavbar' => true,
        ]);
    }

    public function topics()
    {
        $search = request('search');
        $topics = Topic::with('subject')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('subject', fn($sq) => $sq->where('name', 'like', "%{$search}%")))
            ->paginate(20)
            ->withQueryString();

        return view('pages.topics', compact('topics', 'search'));
    }

    public function topicDetail(Topic $topic)
    {
        $topic->load('subject');

        $search = request('search');
        $lessons = $topic->lessons()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->paginate(20)
            ->withQueryString();

        return view('pages.subtopics', compact('topic', 'lessons', 'search'));
    }

    public function lesson($id)
    {
        $lesson = \App\Modules\Lesson\Models\Lesson::with(['topic', 'videos', 'quiz.questions'])->findOrFail($id);

        return view('pages.lesson', compact('lesson'));
    }

    public function lessonRate(Request $request, $id)
    {
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        return redirect()->route('lesson', $id)
            ->with('success', 'Thank you for your feedback!');
    }

    public function quiz($id)
    {
        return view('pages.quiz', [
            'quiz' => [
                'id' => $id,
                'name' => 'Countable & Uncountable Nouns',
            ],
            'currentStep' => 1,
            'totalSteps' => 5,
            'question' => [
                'text' => 'Which of these is <span class="underline decoration-4 underline-offset-8 text-[rgb(var(--secondary))]">uncountable</span>?',
            ],
            'options' => [
                'A' => 'Apple',
                'B' => 'Water',
                'C' => 'Book',
                'D' => 'Chair',
            ],
        ]);
    }

    public function quizSubmit(Request $request, $id)
    {
        return redirect()->route('quiz', $id)
            ->with('success', 'Quiz submitted successfully!');
    }

    public function exam()
    {
        return view('pages.exam');
    }

    public function grade9()
    {
        return view('pages.grade-9');
    }

    public function finalExam()
    {
        return view('pages.final-exam');
    }

    public function examSubmit(Request $request)
    {
        return redirect()->route('exam-result')
            ->with('success', 'Exam submitted successfully!');
    }

    public function examResult()
    {
        return view('pages.exam-result');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function profile()
    {
        $user = Auth::user();
        $student = $user?->student;

        return view('pages.profile', [
            'profile' => [
                'name' => $user?->name ?? 'Star Student',
                'email' => $user?->email ?? 'student@example.com',
                'phone' => $user?->phone ?? '+994 XX XXX XX XX',
            ],
            'student' => $student,
            'totalStars' => 42,
            'cities' => City::all(),
            'grades' => Grade::all(),
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'city_id' => 'nullable|exists:cities,id',
            'grade_id' => 'nullable|exists:grades,id',
            'school_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        if ($user) {
            $user->update($request->only(['name', 'email', 'phone']));

            $student = $user->student;
            if ($student) {
                $student->update($request->only(['city_id', 'grade_id', 'school_name', 'birth_date']));
            }
        }

        return redirect()->route('profile')
            ->with('success', 'Profile updated successfully!');
    }

    public function profilePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return redirect()->route('profile')
                ->with('error', 'Current password is incorrect.');
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('profile')
            ->with('success', 'Password updated successfully!');
    }
}
