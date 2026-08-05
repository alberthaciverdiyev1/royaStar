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
use App\Modules\Star\Models\Star;
use App\Modules\Star\Models\UserStar;
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
        ]);
    }

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

    // ═══════════════════════════════════════════
    // QUIZ — Dynamic
    // ═══════════════════════════════════════════

    private function resolveRightAnswerLetter($question, string $locale = 'az'): string
    {
        if (!$question || $question->type !== 'regular') {
            return '';
        }

        $rawRight = trim($question->right_answer ?? '');
        if ($rawRight === '') {
            return '';
        }

        $normRaw = str_replace('variant_', '', strtolower($rawRight));
        if (in_array($normRaw, ['a', 'b', 'c', 'd', 'e'], true)) {
            return $normRaw;
        }

        foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
            $varKey = 'variant_' . $letter;
            $varData = $question->$varKey ?? null;
            if (!$varData) continue;

            $varText = is_array($varData)
                ? collect($varData[$locale] ?? $varData['az'] ?? $varData)->map(function ($block) {
                    if (is_array($block)) {
                        return $block['content'] ?? '';
                    }
                    return (string) $block;
                })->join(' ')
                : (string) $varData;

            if (mb_strtolower(trim($varText)) === mb_strtolower($rawRight)) {
                return $letter;
            }
        }

        return $normRaw;
    }

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

        // Pre-check star awards BEFORE transaction (existing UserStar records are visible here)
        $alreadyAwardedCompleted = UserStar::withTrashed()
            ->where('user_id', $user->id)
            ->whereIn('star_id', Star::where('type', 'quiz_completed')->pluck('id'))
            ->where('reference_type', 'quiz')
            ->where('reference_id', $quiz->id)
            ->exists();
        $alreadyAwardedPerfect = UserStar::withTrashed()
            ->where('user_id', $user->id)
            ->whereIn('star_id', Star::where('type', 'quiz_perfect')->pluck('id'))
            ->where('reference_type', 'quiz')
            ->where('reference_id', $quiz->id)
            ->exists();

        $result = DB::transaction(function () use ($student, $quiz, $questions, $answers, $locale, $user, $alreadyAwardedCompleted, $alreadyAwardedPerfect) {
            // Delete old attempts atomically with new insert
            StudentQuiz::where('student_id', $student->id)
                ->where('quiz_id', $quiz->id)
                ->delete();
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

                $isCorrect = false;
                $correctAnswer = null;

                if ($answer === null || trim($answer) === '') {
                    $skippedCount++;
                    $answer = null;
                    if ($question->type === 'regular') {
                        $correctAnswer = $this->resolveRightAnswerLetter($question, $locale);
                    } else {
                        $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                        $correctAnswer = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;
                    }
                } elseif ($question->type === 'regular') {
                    $correctAnswer = $this->resolveRightAnswerLetter($question, $locale);
                    $userAnswerNorm = str_replace('variant_', '', strtolower(trim($answer)));

                    $isCorrect = ($userAnswerNorm === $correctAnswer);
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                    $correctAnswer = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;

                    if ($question->answer_type === 'exact') {
                        $isCorrect = (mb_strtolower(trim($answer)) === mb_strtolower(trim($correctAnswer)));
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
                    'question_text' => $question->question[$locale] ?? $question->question['az'] ?? [],
                    'variants' => [
                        'a' => $question->variant_a[$locale] ?? $question->variant_a['az'] ?? [],
                        'b' => $question->variant_b[$locale] ?? $question->variant_b['az'] ?? [],
                        'c' => $question->variant_c[$locale] ?? $question->variant_c['az'] ?? [],
                        'd' => $question->variant_d[$locale] ?? $question->variant_d['az'] ?? [],
                        'e' => $question->variant_e[$locale] ?? $question->variant_e['az'] ?? [],
                    ]
                ];
            }

            $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

            // Award stars only on first completion
            if (!$alreadyAwardedCompleted) {
                $this->starService->awardQuizCompleted($user->id, $quiz->id);
            }
            if ($score === 100 && !$alreadyAwardedPerfect) {
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
                    'answers' => $attempts->map(function($a) use ($locale) {
                        $q = $a->question;
                        return [
                            'question_id' => $a->question_id,
                            'type' => $a->type,
                            'answer' => $a->answer,
                            'correct_answer' => $a->correct_answer,
                            'is_correct' => $a->is_correct,
                            'question_text' => $q?->question[$locale] ?? $q?->question['az'] ?? [],
                            'variants' => [
                                'a' => $q?->variant_a[$locale] ?? $q?->variant_a['az'] ?? [],
                                'b' => $q?->variant_b[$locale] ?? $q?->variant_b['az'] ?? [],
                                'c' => $q?->variant_c[$locale] ?? $q?->variant_c['az'] ?? [],
                                'd' => $q?->variant_d[$locale] ?? $q?->variant_d['az'] ?? [],
                                'e' => $q?->variant_e[$locale] ?? $q?->variant_e['az'] ?? [],
                            ]
                        ];
                    })->toArray(),
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
        $grades = Grade::has('exams')->withCount('exams')->orderBy('id')->get();

        return view('pages.exam', compact('grades'));
    }

    public function examGrade(Grade $grade)
    {
        $exams = $grade->exams()->with('grade')->withCount('questions')->get();

        $user = Auth::user();
        $student = $user?->student;

        // Get past attempt scores for each exam
        $examScores = [];
        if ($student && $exams->isNotEmpty()) {
            $attempts = StudentExam::where('student_id', $student->id)
                ->whereIn('exam_id', $exams->pluck('id'))
                ->get()
                ->groupBy('exam_id');

            foreach ($attempts as $examId => $records) {
                $total = $records->count();
                $correct = $records->where('is_correct', true)->count();
                $examScores[$examId] = $total > 0 ? round(($correct / $total) * 100) : null;
            }
        }

        return view('pages.exam', compact('grade', 'exams', 'examScores'));
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
                $data['right_answer'] = $this->resolveRightAnswerLetter($q, $locale);
                $data['variant_a'] = $q->variant_a[$locale] ?? $q->variant_a['az'] ?? [];
                $data['variant_b'] = $q->variant_b[$locale] ?? $q->variant_b['az'] ?? [];
                $data['variant_c'] = $q->variant_c[$locale] ?? $q->variant_c['az'] ?? [];
                $data['variant_d'] = $q->variant_d[$locale] ?? $q->variant_d['az'] ?? [];
                $data['variant_e'] = $q->variant_e[$locale] ?? $q->variant_e['az'] ?? [];
            } else {
                $openAnswerBlocks = $q->open_answer[$locale] ?? $q->open_answer['az'] ?? [];
                $data['correct_answer'] = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;
            }

            return $data;
        });

        return view('pages.exam-solve', [
            'exam' => $exam,
            'questions' => $questions,
            'totalSteps' => $questions->count(),
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

        // Pre-check star awards BEFORE transaction (existing UserStar records are visible here)
        $alreadyAwardedPassed = UserStar::withTrashed()
            ->where('user_id', $user->id)
            ->whereIn('star_id', Star::where('type', 'exam_passed')->pluck('id'))
            ->where('reference_type', 'exam')
            ->where('reference_id', $exam->id)
            ->exists();
        $alreadyAwardedExcellent = UserStar::withTrashed()
            ->where('user_id', $user->id)
            ->whereIn('star_id', Star::where('type', 'exam_excellent')->pluck('id'))
            ->where('reference_type', 'exam')
            ->where('reference_id', $exam->id)
            ->exists();

        $result = DB::transaction(function () use ($student, $exam, $questions, $answers, $locale, $user, $alreadyAwardedPassed, $alreadyAwardedExcellent) {
            // Delete old attempts atomically with new insert
            StudentExam::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->delete();
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

                $isCorrect = false;
                $correctAnswer = null;

                if ($answer === null || trim($answer) === '') {
                    $skippedCount++;
                    $answer = null;
                    if ($question->type === 'regular') {
                        $correctAnswer = $this->resolveRightAnswerLetter($question, $locale);
                    } else {
                        $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                        $correctAnswer = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;
                    }
                } elseif ($question->type === 'regular') {
                    $correctAnswer = $this->resolveRightAnswerLetter($question, $locale);
                    $userAnswerNorm = str_replace('variant_', '', strtolower(trim($answer)));

                    $isCorrect = ($userAnswerNorm === $correctAnswer);
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                    $correctAnswer = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;

                    if ($question->answer_type === 'exact') {
                        $isCorrect = (mb_strtolower(trim($answer)) === mb_strtolower(trim($correctAnswer)));
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
                    'question_text' => $question->question[$locale] ?? $question->question['az'] ?? [],
                    'variants' => [
                        'a' => $question->variant_a[$locale] ?? $question->variant_a['az'] ?? [],
                        'b' => $question->variant_b[$locale] ?? $question->variant_b['az'] ?? [],
                        'c' => $question->variant_c[$locale] ?? $question->variant_c['az'] ?? [],
                        'd' => $question->variant_d[$locale] ?? $question->variant_d['az'] ?? [],
                        'e' => $question->variant_e[$locale] ?? $question->variant_e['az'] ?? [],
                    ]
                ];
            }

            $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

            // Award stars only on first completion
            $passingScore = $exam->passing_score ?? 60;
            if ($score >= $passingScore && !$alreadyAwardedPassed) {
                $this->starService->awardExamPassed($user->id, $exam->id);
            }
            if ($score >= 90 && !$alreadyAwardedExcellent) {
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
                    'answers' => $attempts->map(function($a) use ($locale) {
                        $q = $a->question;
                        return [
                            'question_id' => $a->question_id,
                            'type' => $a->type,
                            'answer' => $a->answer,
                            'correct_answer' => $a->correct_answer,
                            'is_correct' => $a->is_correct,
                            'question_text' => $q?->question[$locale] ?? $q?->question['az'] ?? [],
                            'variants' => [
                                'a' => $q?->variant_a[$locale] ?? $q?->variant_a['az'] ?? [],
                                'b' => $q?->variant_b[$locale] ?? $q?->variant_b['az'] ?? [],
                                'c' => $q?->variant_c[$locale] ?? $q?->variant_c['az'] ?? [],
                                'd' => $q?->variant_d[$locale] ?? $q?->variant_d['az'] ?? [],
                                'e' => $q?->variant_e[$locale] ?? $q?->variant_e['az'] ?? [],
                            ]
                        ];
                    })->toArray(),
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
    // ACHIEVEMENTS & PROFILE — Dynamic
    // ═══════════════════════════════════════════

    public function achievements(Request $request)
    {
        $user = Auth::user();
        $student = $user?->student;
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // Generate past 6 months list + All Time option
        $availableMonths = [
            'all' => 'All Time (All Stars)',
        ];
        for ($i = 0; $i < 6; $i++) {
            $d = now()->subMonths($i);
            $key = $d->format('Y-m');
            $label = $d->format('F Y') . ($i === 0 ? ' (Current Month)' : '');
            $availableMonths[$key] = $label;
        }

        // Total stars (filtered by month if specified)
        $totalStars = $user ? $this->starService->getUserTotalStars($user->id, $selectedMonth === 'all' ? null : $selectedMonth) : 0;
        $allTimeStars = $user ? $this->starService->getUserTotalStars($user->id, null) : 0;

        // User stars query
        $userStarsQuery = $user ? UserStar::where('user_id', $user->id)->with('star') : null;
        if ($userStarsQuery && $selectedMonth !== 'all') {
            $s = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $e = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $userStarsQuery->whereBetween('created_at', [$s, $e]);
        }
        $earnedUserStars = $userStarsQuery ? $userStarsQuery->latest()->get() : collect();
        $earnedStarIds = $user ? UserStar::where('user_id', $user->id)->pluck('star_id')->unique()->toArray() : [];

        $allStars = Star::all();

        $quizCount = $student ? StudentQuiz::where('student_id', $student->id)->select('quiz_id')->distinct()->count() : 0;
        $examCount = $student ? StudentExam::where('student_id', $student->id)->select('exam_id')->distinct()->count() : 0;
        $correctAnswersCount = $student ? StudentQuiz::where('student_id', $student->id)->where('is_correct', true)->count() : 0;

        // Leaderboard query filtered by selected month
        $leaderboardQuery = User::select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COALESCE(SUM(stars.point), 0) as total_stars')
            )
            ->leftJoin('user_stars', 'users.id', '=', 'user_stars.user_id')
            ->leftJoin('stars', 'user_stars.star_id', '=', 'stars.id');

        if ($selectedMonth !== 'all') {
            $s = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $e = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $leaderboardQuery->whereBetween('user_stars.created_at', [$s, $e]);
        }

        $leaderboard = $leaderboardQuery->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_stars')
            ->limit(50)
            ->get();

        return view('pages.achievements', [
            'totalStars' => $totalStars,
            'allTimeStars' => $allTimeStars,
            'allStars' => $allStars,
            'earnedUserStars' => $earnedUserStars,
            'earnedStarIds' => $earnedStarIds,
            'quizCount' => $quizCount,
            'examCount' => $examCount,
            'correctAnswersCount' => $correctAnswersCount,
            'leaderboard' => $leaderboard,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
        ]);
    }

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
                'avatar' => $user?->avatar,
            ],
            'user' => $user,
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
            'avatar' => 'nullable|string|max:500',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'city_id' => 'nullable|exists:cities,id',
            'grade_id' => 'nullable|exists:grades,id',
            'school_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        if ($user) {
            $userData = $request->only(['name', 'email', 'phone']);

            if ($request->hasFile('avatar_file')) {
                $userData['avatar'] = $this->compressAndStoreAvatar($request->file('avatar_file'));
            } elseif ($request->filled('avatar')) {
                $userData['avatar'] = $request->input('avatar');
            }

            $user->update($userData);

            $student = $user->student;
            if ($student) {
                $student->update($request->only(['city_id', 'grade_id', 'school_name', 'birth_date']));
            }
        }

        return redirect()->route('profile')
            ->with('success', 'Profiliniz və şəkiliniz uğurla yeniləndi!');
    }

    public function avatarUpload(Request $request)
    {
        $request->validate([
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'avatar' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if ($user) {
            if ($request->hasFile('avatar_file')) {
                $user->avatar = $this->compressAndStoreAvatar($request->file('avatar_file'));
            } elseif ($request->filled('avatar')) {
                $user->avatar = $request->input('avatar');
            }
            $user->save();
        }

        return redirect()->route('profile')
            ->with('success', 'Profil şəkliniz uğurla yeniləndi!');
    }

    /**
     * Compress photo with 256x256 center-crop & WebP 75% compression (~15KB)
     */
    private function compressAndStoreAvatar($file): string
    {
        $filename = 'avatar_' . Auth::id() . '_' . time() . '.webp';
        $storageDir = storage_path('app/public/avatars');
        $publicDir = public_path('storage/avatars');

        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }

        $destinationPath = $storageDir . '/' . $filename;
        $publicPath = $publicDir . '/' . $filename;

        if (extension_loaded('gd')) {
            $imageContent = file_get_contents($file->getRealPath());
            $srcImage = @imagecreatefromstring($imageContent);

            if ($srcImage !== false) {
                $srcWidth = imagesx($srcImage);
                $srcHeight = imagesy($srcImage);
                $targetSize = 256;

                // Center crop calculations
                if ($srcWidth > $srcHeight) {
                    $cropX = (int) round(($srcWidth - $srcHeight) / 2);
                    $cropY = 0;
                    $cropSize = $srcHeight;
                } else {
                    $cropX = 0;
                    $cropY = (int) round(($srcHeight - $srcWidth) / 2);
                    $cropSize = $srcWidth;
                }

                $dstImage = imagecreatetruecolor($targetSize, $targetSize);

                // Preserve alpha transparency
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);

                imagecopyresampled(
                    $dstImage,
                    $srcImage,
                    0, 0,
                    $cropX, $cropY,
                    $targetSize, $targetSize,
                    $cropSize, $cropSize
                );

                if (function_exists('imagewebp')) {
                    imagewebp($dstImage, $destinationPath, 75);
                    @imagewebp($dstImage, $publicPath, 75);
                } else {
                    $filename = 'avatar_' . Auth::id() . '_' . time() . '.jpg';
                    $destinationPath = $storageDir . '/' . $filename;
                    $publicPath = $publicDir . '/' . $filename;
                    imagejpeg($dstImage, $destinationPath, 75);
                    @imagejpeg($dstImage, $publicPath, 75);
                }

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                return '/storage/avatars/' . $filename;
            }
        }

        // Fallback store
        $path = $file->store('avatars', 'public');
        @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
        return '/storage/' . $path;
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
