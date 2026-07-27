<?php

namespace App\Http\Controllers;

use App\Modules\User\Models\User;
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

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

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
        ]);
    }

    public function signupPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'type' => 'student',
            ]);

            $user->assignRole('student');

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('welcome');
    }

    public function index()
    {
        return view('index', [
            'isIndex' => true,
            'hideNavbar' => true,
            'initials' => '',
        ]);
    }

    public function topics()
    {
        return view('pages.topics');
    }

    public function subtopics()
    {
        return view('pages.subtopics');
    }

    public function lesson($id)
    {
        return view('pages.lesson', [
            'lesson' => [
                'id' => $id,
                'name' => 'Countable & Uncountable Nouns',
                'topic_name' => 'Nouns & Objects',
                'description' => 'Learn how to distinguish between countable and uncountable nouns in English grammar.',
                'star' => 3,
            ],
            'videos' => [
                ['youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'title' => 'Lesson Video'],
            ],
            'quizzes' => [
                ['id' => 1, 'name' => 'Countable Nouns Quiz', 'star' => 0, 'questions_count' => 10],
                ['id' => 2, 'name' => 'Uncountable Nouns Quiz', 'star' => 0, 'questions_count' => 8],
            ],
        ]);
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

        return view('pages.profile', [
            'profile' => [
                'name' => $user?->name ?? 'Star Student',
                'email' => $user?->email ?? 'student@example.com',
                'phone' => $user?->phone ?? '+994 XX XXX XX XX',
            ],
            'totalStars' => 42,
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        if ($user) {
            $user->update($request->only(['name', 'email', 'phone']));
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
