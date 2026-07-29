<?php

namespace App\Http\Controllers;

use App\Modules\User\Models\User;
use App\Modules\Student\Models\Student;
use App\Modules\Grade\Models\Grade;
use App\Modules\City\Models\City;
use App\Modules\Topic\Models\Topic;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Lesson\Models\LessonReview;
use App\Modules\Star\Services\StarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    public function __construct(
        private readonly StarService $starService,
    ) {}

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

        // Track lesson view
        \App\Modules\Lesson\Models\LessonView::updateOrCreate(['lesson_id' => $lesson->id])->increment('count');

        // Award lesson completed star
        $user = Auth::user();
        if ($user) {
            $this->starService->awardLessonCompleted($user->id, $lesson->id);
        }

        // Get existing review if user is authenticated
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
        $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        LessonReview::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $id],
            ['rating' => $request->rating, 'review' => $request->review],
        );

        return redirect()->route('lesson', $id)
            ->with('success', 'Thank you for your feedback!');
    }

    // ═══════════════════════════════════════════
    // QUIZ — Dynamic
    // ═══════════════════════════════════════════

    public function quiz($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $locale = app()->getLocale();

        $questions = $quiz->questions->map(function ($q) use ($locale) {
            $data = [
                'id' => $q->id,
                'type' => $q->type,
                'answer_type' => $q->answer_type,
                'question' => $q->question[$locale] ?? $q->question['az'] ?? [],
                'difficulty_level' => $q->difficulty_level,
            ];

            if ($q->type === 'regular') {
                $data['variant_a'] = $q->variant_a[$locale] ?? $q->variant_a['az'] ?? [];
                $data['variant_b'] = $q->variant_b[$locale] ?? $q->variant_b['az'] ?? [];
                $data['variant_c'] = $q->variant_c[$locale] ?? $q->variant_c['az'] ?? [];
                $data['variant_d'] = $q->variant_d[$locale] ?? $q->variant_d['az'] ?? [];
                $data['variant_e'] = $q->variant_e[$locale] ?? $q->variant_e['az'] ?? [];
            }

            return $data;
        });

        return view('pages.quiz', [
            'quiz' => $quiz,
            'questions' => $questions,
            'totalSteps' => $questions->count(),
        ]);
    }

    public function quizSubmit(Request $request, $id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $questions = $quiz->questions->keyBy('id');
        $answers = $request->input('answers', []);
        $locale = app()->getLocale();
        $user = Auth::user();
        $student = $user->student;

        abort_unless($student, 403, 'Only students can submit quizzes');

        // Delete old attempts for this quiz
        StudentQuiz::where('student_id', $student->id)
            ->where('quiz_id', $quiz->id)
            ->delete();

        $result = DB::transaction(function () use ($student, $quiz, $questions, $answers, $locale, $user) {
            $correctCount = 0;
            $wrongCount = 0;
            $skippedCount = 0;
            $total = $questions->count();
            $answerDetails = [];

            foreach ($answers as $item) {
                $questionId = $item['question_id'] ?? null;
                $answer = $item['answer'] ?? null;

                $question = $questions->get($questionId);
                if (!$question) continue;

                $isCorrect = null;
                $correctAnswer = null;

                if ($answer === null || trim($answer) === '') {
                    $skippedCount++;
                } elseif ($question->type === 'regular') {
                    $correctAnswer = $question->right_answer;
                    $isCorrect = strtolower(trim($answer)) === strtolower(trim($correctAnswer));
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                    $correctAnswer = $openAnswerBlocks[0]['content'] ?? '';

                    if ($question->answer_type === 'exact') {
                        $isCorrect = mb_strtolower(trim($answer)) === mb_strtolower(trim($correctAnswer));
                        $isCorrect ? $correctCount++ : $wrongCount++;
                    } else {
                        $isCorrect = false;
                        $wrongCount++;
                    }
                }

                StudentQuiz::create([
                    'student_id' => $student->id,
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                    'answer' => $answer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                    'type' => $question->type,
                ]);

                $answerDetails[] = [
                    'question_id' => $questionId,
                    'type' => $question->type,
                    'answer' => $answer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                ];
            }

            $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

            // Award stars
            $this->starService->awardQuizCompleted($user->id, $quiz->id);
            if ($score === 100) {
                $this->starService->awardQuizPerfect($user->id, $quiz->id);
            }

            return [
                'score' => $score,
                'total' => $total,
                'correct' => $correctCount,
                'wrong' => $wrongCount,
                'skipped' => $skippedCount,
                'answers' => $answerDetails,
            ];
        });

        return redirect()->route('quiz.result', $id)
            ->with('quiz_result', $result);
    }

    public function quizResult($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);
        $user = Auth::user();
        $student = $user->student;

        // Try session first (fresh submission), fallback to DB
        $result = session('quiz_result');

        if (!$result && $student) {
            $attempts = StudentQuiz::where('student_id', $student->id)
                ->where('quiz_id', $quiz->id)
                ->with('question')
                ->get();

            if ($attempts->isNotEmpty()) {
                $total = $attempts->count();
                $correct = $attempts->where('is_correct', true)->count();
                $wrong = $attempts->where('is_correct', false)->whereNotNull('answer')->where('answer', '!=', '')->count();
                $skipped = $attempts->filter(fn($a) => empty($a->answer))->count();

                $locale = app()->getLocale();
                $result = [
                    'score' => $total > 0 ? round(($correct / $total) * 100) : 0,
                    'total' => $total,
                    'correct' => $correct,
                    'wrong' => $wrong,
                    'skipped' => $skipped,
                    'answers' => $attempts->map(fn($a) => [
                        'question_id' => $a->question_id,
                        'type' => $a->type,
                        'answer' => $a->answer,
                        'correct_answer' => $a->correct_answer,
                        'is_correct' => $a->is_correct,
                        'question_text' => $a->question?->question[$locale] ?? $a->question?->question['az'] ?? [],
                    ])->toArray(),
                ];
            }
        }

        if (!$result) {
            return redirect()->route('quiz', $id);
        }

        return view('pages.quiz-result', [
            'quiz' => $quiz,
            'result' => $result,
        ]);
    }

    // ═══════════════════════════════════════════
    // EXAM — Dynamic
    // ═══════════════════════════════════════════

    public function exam()
    {
        $exams = Exam::with('grade')
            ->withCount('questions')
            ->get();

        $user = Auth::user();
        $student = $user?->student;

        // Get past attempt scores for each exam
        $examScores = [];
        if ($student) {
            $examIds = $exams->pluck('id');
            $attempts = StudentExam::where('student_id', $student->id)
                ->whereIn('exam_id', $examIds)
                ->get()
                ->groupBy('exam_id');

            foreach ($attempts as $examId => $records) {
                $total = $records->count();
                $correct = $records->where('is_correct', true)->count();
                $examScores[$examId] = $total > 0 ? round(($correct / $total) * 100) : null;
            }
        }

        return view('pages.exam', compact('exams', 'examScores'));
    }

    public function examDetail(Exam $exam)
    {
        $exam->load('grade');
        $exam->loadCount('questions');

        $user = Auth::user();
        $student = $user?->student;
        $pastScore = null;

        if ($student) {
            $attempts = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->get();

            if ($attempts->isNotEmpty()) {
                $total = $attempts->count();
                $correct = $attempts->where('is_correct', true)->count();
                $pastScore = $total > 0 ? round(($correct / $total) * 100) : null;
            }
        }

        return view('pages.exam-detail', compact('exam', 'pastScore'));
    }

    public function examStart(Exam $exam)
    {
        $exam->load('questions', 'grade');
        $locale = app()->getLocale();

        $questions = $exam->questions->map(function ($q) use ($locale) {
            $data = [
                'id' => $q->id,
                'type' => $q->type,
                'answer_type' => $q->answer_type,
                'question' => $q->question[$locale] ?? $q->question['az'] ?? [],
                'difficulty_level' => $q->difficulty_level,
            ];

            if ($q->type === 'regular') {
                $data['variant_a'] = $q->variant_a[$locale] ?? $q->variant_a['az'] ?? [];
                $data['variant_b'] = $q->variant_b[$locale] ?? $q->variant_b['az'] ?? [];
                $data['variant_c'] = $q->variant_c[$locale] ?? $q->variant_c['az'] ?? [];
                $data['variant_d'] = $q->variant_d[$locale] ?? $q->variant_d['az'] ?? [];
                $data['variant_e'] = $q->variant_e[$locale] ?? $q->variant_e['az'] ?? [];
            }

            return $data;
        });

        return view('pages.exam-solve', [
            'exam' => $exam,
            'questions' => $questions,
        ]);
    }

    public function examSubmit(Request $request, Exam $exam)
    {
        $exam->load('questions');
        $questions = $exam->questions->keyBy('id');
        $answers = $request->input('answers', []);
        $locale = app()->getLocale();
        $user = Auth::user();
        $student = $user->student;

        abort_unless($student, 403, 'Only students can submit exams');

        // Delete old attempts
        StudentExam::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->delete();

        $result = DB::transaction(function () use ($student, $exam, $questions, $answers, $locale, $user) {
            $correctCount = 0;
            $wrongCount = 0;
            $skippedCount = 0;
            $total = $questions->count();
            $answerDetails = [];

            foreach ($answers as $item) {
                $questionId = $item['question_id'] ?? null;
                $answer = $item['answer'] ?? null;

                $question = $questions->get($questionId);
                if (!$question) continue;

                $isCorrect = null;
                $correctAnswer = null;

                if ($answer === null || trim($answer) === '') {
                    $skippedCount++;
                } elseif ($question->type === 'regular') {
                    $correctAnswer = $question->right_answer;
                    $isCorrect = strtolower(trim($answer)) === strtolower(trim($correctAnswer));
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                    $correctAnswer = $openAnswerBlocks[0]['content'] ?? '';

                    if ($question->answer_type === 'exact') {
                        $isCorrect = mb_strtolower(trim($answer)) === mb_strtolower(trim($correctAnswer));
                        $isCorrect ? $correctCount++ : $wrongCount++;
                    } else {
                        $isCorrect = false;
                        $wrongCount++;
                    }
                }

                StudentExam::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'question_id' => $questionId,
                    'answer' => $answer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                    'type' => $question->type,
                ]);

                $answerDetails[] = [
                    'question_id' => $questionId,
                    'type' => $question->type,
                    'answer' => $answer,
                    'correct_answer' => $correctAnswer,
                    'is_correct' => $isCorrect,
                ];
            }

            $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

            // Award stars
            $passingScore = $exam->passing_score ?? 60;
            if ($score >= $passingScore) {
                $this->starService->awardExamPassed($user->id, $exam->id);
            }
            if ($score >= 90) {
                $this->starService->awardExamExcellent($user->id, $exam->id);
            }

            return [
                'score' => $score,
                'total' => $total,
                'correct' => $correctCount,
                'wrong' => $wrongCount,
                'skipped' => $skippedCount,
                'answers' => $answerDetails,
            ];
        });

        return redirect()->route('exam.result', $exam)
            ->with('exam_result', $result);
    }

    public function examResult(Exam $exam)
    {
        $exam->load('grade', 'questions');
        $user = Auth::user();
        $student = $user->student;

        $result = session('exam_result');

        if (!$result && $student) {
            $attempts = StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->with('question')
                ->get();

            if ($attempts->isNotEmpty()) {
                $total = $attempts->count();
                $correct = $attempts->where('is_correct', true)->count();
                $wrong = $attempts->where('is_correct', false)->whereNotNull('answer')->where('answer', '!=', '')->count();
                $skipped = $attempts->filter(fn($a) => empty($a->answer))->count();

                $locale = app()->getLocale();
                $result = [
                    'score' => $total > 0 ? round(($correct / $total) * 100) : 0,
                    'total' => $total,
                    'correct' => $correct,
                    'wrong' => $wrong,
                    'skipped' => $skipped,
                    'answers' => $attempts->map(fn($a) => [
                        'question_id' => $a->question_id,
                        'type' => $a->type,
                        'answer' => $a->answer,
                        'correct_answer' => $a->correct_answer,
                        'is_correct' => $a->is_correct,
                        'question_text' => $a->question?->question[$locale] ?? $a->question?->question['az'] ?? [],
                    ])->toArray(),
                ];
            }
        }

        if (!$result) {
            return redirect()->route('exam.detail', $exam);
        }

        return view('pages.exam-result', [
            'exam' => $exam,
            'result' => $result,
        ]);
    }

    // ═══════════════════════════════════════════
    // PROFILE — Dynamic
    // ═══════════════════════════════════════════

    public function profile()
    {
        $user = Auth::user();
        $student = $user?->student;

        $totalStars = $user ? $this->starService->getUserTotalStars($user->id) : 0;
        $starHistory = $user ? $this->starService->getStarHistory($user->id) : collect();

        return view('pages.profile', [
            'profile' => [
                'name' => $user?->name ?? 'Star Student',
                'email' => $user?->email ?? 'student@example.com',
                'phone' => $user?->phone ?? '+994 XX XXX XX XX',
            ],
            'student' => $student,
            'totalStars' => $totalStars,
            'starHistory' => $starHistory,
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

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
