<?php

namespace App\Http\Controllers;

use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessmentService,
    ) {}

    public function quiz($id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        if (Auth::user()?->student && !$quiz->isAvailableForGrade(Auth::user()->student->grade_id)) {
            abort(403, 'This quiz is not available for your grade.');
        }

        $questions = $quiz->questions->map(function ($q) {
            $data = [
                'id' => $q->id,
                'type' => $q->type,
                'answer_type' => $q->answer_type,
                'question' => $q->question ?? [],
                'difficulty_level' => $q->difficulty_level,
            ];

            if ($q->type === 'regular') {
                $data['variant_a'] = $q->variant_a ?? [];
                $data['variant_b'] = $q->variant_b ?? [];
                $data['variant_c'] = $q->variant_c ?? [];
                $data['variant_d'] = $q->variant_d ?? [];
                $data['variant_e'] = $q->variant_e ?? [];
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

        if (Auth::user()?->student && !$quiz->isAvailableForGrade(Auth::user()->student->grade_id)) {
            abort(403, 'This quiz is not available for your grade.');
        }

        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'nullable|string',
        ]);

        $answers = $request->input('answers', []);
        $user = Auth::user();
        $student = $user->student;

        abort_unless($student, 403, 'Only students can submit quizzes');

        $result = $this->assessmentService->submitQuiz($user, $student, $quiz, $answers);

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
                $result = $this->assessmentService->buildResultFromAttempts($attempts);
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

    /**
     * Per-question answer check used by the quiz solve page's Confirm step.
     *
     * The correct answer is NEVER embedded in the page HTML (that would leak it
     * via DevTools). Instead, after the student selects and confirms a single
     * answer, the browser asks the server to evaluate it and only then receives
     * right/wrong + the explanation video.
     */
    public function quizCheckAnswer(Request $request, $id)
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        abort_unless(Auth::user()?->student, 403, 'Only students can take quizzes');

        if (!$quiz->isAvailableForGrade(Auth::user()->student->grade_id)) {
            abort(403, 'This quiz is not available for your grade.');
        }

        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'required|string|max:2000',
        ]);

        $question = $quiz->questions->firstWhere('id', (int) $request->question_id);

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        return response()->json($this->buildCheckResponse($question, $request->answer));
    }

    /**
     * Evaluate one answer server-side. Regular → exact letter match. Open →
     * we report correct/wrong but never reveal the model answer text.
     */
    private function buildCheckResponse($question, string $answer): array
    {
        if ($question->type === 'regular') {
            $correctLetter = $this->assessmentService->resolveRightAnswerLetter($question);
            $isCorrect = (str_replace('variant_', '', strtolower(trim($answer))) === $correctLetter);

            return [
                'type' => 'regular',
                'correct' => $isCorrect,
                'correct_answer' => $correctLetter,
                'explanation_video_url' => $question->explanation_video_url ?? null,
            ];
        }

        return [
            'type' => 'open',
            'correct' => $this->assessmentService->evaluateOpenAnswer($question, $answer),
            'correct_answer' => null, // never reveal the model answer mid-quiz
            'explanation_video_url' => $question->explanation_video_url ?? null,
        ];
    }
}
