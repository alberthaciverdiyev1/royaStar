<?php

namespace App\Modules\Exam\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Exam\Requests\SubmitExamRequest;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class StudentExamController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessmentService,
    ) {}

    #[OA\Post(path: '/exams/{exam}/start', summary: 'Start an exam attempt (get questions)',
        tags: ['Exams'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'exam', description: 'Exam ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Exam questions returned')]),
    ]
    public function start(Exam $exam): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Only students can start exams');

        if ($exam->grade_id && $student->grade_id && $student->grade_id !== $exam->grade_id) {
            abort(403, 'This exam is not available for your grade.');
        }

        $exam->load('questions');
        $locale = app()->getLocale();

        return apiResponse(data: [
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'description' => $exam->description,
                'grade_id' => $exam->grade_id,
                'duration_minutes' => $exam->duration_minutes,
                'passing_score' => $exam->passing_score,
                'type' => $exam->type,
            ],
            'questions' => $exam->questions->map(function($q) use ($locale) {
                return [
                    'id' => $q->id,
                    'type' => $q->type,
                    'answer_type' => $q->answer_type,
                    'question' => $q->question[$locale] ?? $q->question['az'] ?? [],
                    'variant_a' => $q->variant_a[$locale] ?? $q->variant_a['az'] ?? [],
                    'variant_b' => $q->variant_b[$locale] ?? $q->variant_b['az'] ?? [],
                    'variant_c' => $q->variant_c[$locale] ?? $q->variant_c['az'] ?? [],
                    'variant_d' => $q->variant_d[$locale] ?? $q->variant_d['az'] ?? [],
                    'variant_e' => $q->variant_e[$locale] ?? $q->variant_e['az'] ?? [],
                    'difficulty_level' => $q->difficulty_level,
                ];
            }),
        ]);
    }

    #[OA\Post(path: '/exams/{exam}/submit', summary: 'Submit exam answers and get result',
        tags: ['Exams'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'exam', description: 'Exam ID', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'answers', type: 'array', description: 'Array of answers', items: new OA\Items(properties: [
                new OA\Property(property: 'question_id', type: 'integer'),
                new OA\Property(property: 'answer', type: 'string', nullable: true),
            ])),
        ])),
        responses: [new OA\Response(response: 200, description: 'Exam submitted with results')]),
    ]
    public function submit(Exam $exam, SubmitExamRequest $request): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Only students can submit exams');

        if ($exam->grade_id && $student->grade_id && $student->grade_id !== $exam->grade_id) {
            abort(403, 'This exam is not available for your grade.');
        }

        $result = $this->assessmentService->submitExam(
            auth()->user(),
            $student,
            $exam,
            $request->input('answers', []),
            app()->getLocale(),
        );

        $answerDetails = collect($result['answers'])->map(fn($a) => [
            'question_id' => $a['question_id'],
            'type' => $a['type'],
            'answer' => $a['answer'],
            'correct_answer' => $a['correct_answer'],
            'is_correct' => $a['is_correct'],
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

    #[OA\Get(path: '/exams/{exam}/result', summary: 'Get latest exam result',
        tags: ['Exams'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\PathParameter(name: 'exam', description: 'Exam ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Exam result')]),
    ]
    public function result(Exam $exam): JsonResponse
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Only students can view results');

        $answers = StudentExam::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->with('question')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($answers->isEmpty()) {
            return apiResponse(data: null);
        }

        $locale = app()->getLocale();
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
            'answers' => $answers->map(function($a) use ($locale) {
                return [
                    'question_id' => $a->question_id,
                    'question' => $a->question?->question[$locale] ?? $a->question?->question['az'] ?? [],
                    'type' => $a->type,
                    'answer' => $a->answer,
                    'correct_answer' => $a->correct_answer,
                    'is_correct' => $a->is_correct,
                ];
            }),
        ]);
    }
}
