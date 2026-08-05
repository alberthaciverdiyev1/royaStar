<?php

namespace App\Modules\Quiz\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Quiz\Requests\SubmitQuizRequest;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class StudentQuizController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessmentService,
    ) {}
    #[OA\Post(path: '/quizzes/{quiz}/start', summary: 'Start a quiz attempt (get questions)',
        tags: ['Quizzes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'quiz', description: 'Quiz ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Quiz questions returned')]),
    ]
    public function start(Quiz $quiz): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Only students can start quizzes');

        if (!$quiz->isAvailableForGrade($student->grade_id)) {
            abort(403, 'This quiz is not available for your grade.');
        }

        $quiz->load('questions');

        return apiResponse(data: [
            'quiz' => [
                'id' => $quiz->id,
                'name' => $quiz->name,
                'type' => $quiz->type,
            ],
            'questions' => $quiz->questions->map(function($q) {
                return [
                    'id' => $q->id,
                    'type' => $q->type,
                    'answer_type' => $q->answer_type,
                    'question' => $q->question ?? [],
                    'variant_a' => $q->variant_a ?? [],
                    'variant_b' => $q->variant_b ?? [],
                    'variant_c' => $q->variant_c ?? [],
                    'variant_d' => $q->variant_d ?? [],
                    'variant_e' => $q->variant_e ?? [],
                    'difficulty_level' => $q->difficulty_level,
                ];
            }),
        ]);
    }

    #[OA\Post(path: '/quizzes/{quiz}/submit', summary: 'Submit quiz answers and get result',
        tags: ['Quizzes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'quiz', description: 'Quiz ID', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'answers', type: 'array', description: 'Array of answers', items: new OA\Items(properties: [
                new OA\Property(property: 'question_id', type: 'integer'),
                new OA\Property(property: 'answer', type: 'string', nullable: true),
            ])),
        ])),
        responses: [new OA\Response(response: 200, description: 'Quiz submitted with results')]),
    ]
    public function submit(Quiz $quiz, SubmitQuizRequest $request): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Only students can submit quizzes');

        if (!$quiz->isAvailableForGrade($student->grade_id)) {
            abort(403, 'This quiz is not available for your grade.');
        }

        $result = $this->assessmentService->submitQuiz(
            auth()->user(),
            $student,
            $quiz,
            $request->input('answers', []),
        );

        $answerDetails = collect($result['answers'])->map(fn($a) => [
            'question_id' => $a['question_id'],
            'type' => $a['type'],
            'answer' => $a['answer'],
            'correct_answer' => $a['correct_answer'],
            'is_correct' => $a['is_correct'],
            'explanation_video_url' => $a['explanation_video_url'] ?? null,
        ])->values()->all();

        return apiResponse(data: [
            'score' => $result['score'],
            'total_questions' => $result['total'],
            'correct_count' => $result['correct'],
            'wrong_count' => $result['wrong'],
            'skipped_count' => $result['skipped'],
            'answers' => $answerDetails,
        ]);
    }

    #[OA\Get(path: '/quizzes/{quiz}/result', summary: 'Get latest quiz result',
        tags: ['Quizzes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'quiz', description: 'Quiz ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Quiz result')]),
    ]
    public function result(Quiz $quiz): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Only students can view results');

        $answers = StudentQuiz::where('student_id', $student->id)
            ->where('quiz_id', $quiz->id)
            ->with('question')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($answers->isEmpty()) {
            return apiResponse(data: null);
        }

        $total = $answers->count();
        $correctCount = $answers->where('is_correct', true)->count();
        $wrongCount = $answers->where('is_correct', false)->whereNotNull('answer')->where('answer', '!=', '')->count();
        $skippedCount = $answers->filter(fn($a) => empty($a->answer))->count();
        $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

        return apiResponse(data: [
            'score' => $score,
            'total_questions' => $total,
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'skipped_count' => $skippedCount,
            'answers' => $answers->map(function($a) {
                return [
                    'question_id' => $a->question_id,
                    'question' => $a->question?->question ?? [],
                    'type' => $a->type,
                    'answer' => $a->answer,
                    'correct_answer' => $a->correct_answer,
                    'is_correct' => $a->is_correct,
                    'explanation_video_url' => $a->question?->explanation_video_url,
                ];
            }),
        ]);
    }
}
