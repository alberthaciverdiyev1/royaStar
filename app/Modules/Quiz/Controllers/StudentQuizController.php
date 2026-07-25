<?php

namespace App\Modules\Quiz\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Quiz\Requests\SubmitQuizRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class StudentQuizController extends Controller
{
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

        $quiz->load('questions');

        return apiResponse(data: [
            'quiz' => [
                'id' => $quiz->id,
                'name' => $quiz->translate('name'),
                'type' => $quiz->type,
            ],
            'questions' => $quiz->questions->map(fn($q) => [
                'id' => $q->id,
                'type' => $q->type,
                'answer_type' => $q->answer_type,
                'question' => $q->translate('question'),
                'variant_a' => $q->translate('variant_a'),
                'variant_b' => $q->translate('variant_b'),
                'variant_c' => $q->translate('variant_c'),
                'variant_d' => $q->translate('variant_d'),
                'variant_e' => $q->translate('variant_e'),
                'difficulty_level' => $q->difficulty_level,
            ]),
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

        $quiz->load('questions');
        $questions = $quiz->questions->keyBy('id');
        $answers = $request->input('answers', []);

        return DB::transaction(function () use ($student, $quiz, $questions, $answers) {
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

                $isCorrect = null;
                $correctAnswer = null;

                if ($answer === null || trim($answer) === '') {
                    $skippedCount++;
                } elseif ($question->type === 'regular') {
                    $correctAnswer = $question->right_answer;
                    $isCorrect = strtolower(trim($answer)) === strtolower(trim($correctAnswer));
                    $isCorrect ? $correctCount++ : $wrongCount++;
                } else {
                    // Open question
                    $openAnswer = $question->translate('open_answer');
                    $correctAnswer = is_array($openAnswer)
                        ? ($openAnswer['content'] ?? '')
                        : $openAnswer;

                    if ($question->answer_type === 'exact') {
                        $isCorrect = mb_strtolower(trim($answer)) === mb_strtolower(trim($correctAnswer));
                        $isCorrect ? $correctCount++ : $wrongCount++;
                    } else {
                        // similar — set is_correct = false for now, awaiting admin review
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
            'answers' => $answers->map(fn($a) => [
                'question_id' => $a->question_id,
                'question' => $a->question?->translate('question'),
                'type' => $a->type,
                'answer' => $a->answer,
                'correct_answer' => $a->correct_answer,
                'is_correct' => $a->is_correct,
            ]),
        ]);
    }
}
