<?php

namespace App\Modules\Exam\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Exam\Requests\SubmitExamRequest;
use App\Modules\Star\Models\Star;
use App\Modules\Star\Models\UserStar;
use App\Modules\Star\Services\StarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class StudentExamController extends Controller
{
    public function __construct(
        private readonly StarService $starService,
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

        $exam->load('questions');
        $questions = $exam->questions->keyBy('id');
        $answers = $request->input('answers', []);
        $locale = app()->getLocale();
        $user = auth()->user();

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

        return DB::transaction(function () use ($student, $exam, $questions, $answers, $locale, $user, $alreadyAwardedPassed, $alreadyAwardedExcellent) {
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
                $questionId = $item['question_id'];
                $answer = $item['answer'] ?? null;

                $question = $questions->get($questionId);
                if (!$question) continue;

                $isCorrect = false;
                $correctAnswer = null;

                if ($answer === null || trim($answer) === '') {
                    $skippedCount++;
                    $answer = null;
                    if ($question->type === 'regular') {
                        $rawRight = $question->right_answer ?? '';
                        $correctAnswer = str_replace('variant_', '', strtolower(trim($rawRight)));
                    } else {
                        $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                        $correctAnswer = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;
                    }
                } elseif ($question->type === 'regular') {
                    $rawRight = $question->right_answer ?? '';
                    $correctAnswer = str_replace('variant_', '', strtolower(trim($rawRight)));
                    $userAnswerNorm = str_replace('variant_', '', strtolower(trim($answer)));

                    $isCorrect = ($userAnswerNorm === $correctAnswer);
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    // Open question
                    $openAnswerBlocks = $question->open_answer[$locale] ?? $question->open_answer['az'] ?? [];
                    $correctAnswer = is_array($openAnswerBlocks) ? ($openAnswerBlocks[0]['content'] ?? '') : $openAnswerBlocks;

                    if ($question->answer_type === 'exact') {
                        $isCorrect = (mb_strtolower(trim($answer)) === mb_strtolower(trim($correctAnswer)));
                        $isCorrect ? $correctCount++ : $wrongCount++;
                    } else {
                        // similar — set is_correct = false for now, awaiting admin review
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

            // Award stars only on first completion
            if ($score >= $exam->passing_score && !$alreadyAwardedPassed) {
                $this->starService->awardExamPassed($user->id, $exam->id);
            }
            if ($score >= 90 && !$alreadyAwardedExcellent) {
                $this->starService->awardExamExcellent($user->id, $exam->id);
            }

            return apiResponse(data: [
                'score' => $score,
                'total_questions' => $total,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'skipped_count' => $skippedCount,
                'answers' => $answerDetails,
            ]);
        });
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
