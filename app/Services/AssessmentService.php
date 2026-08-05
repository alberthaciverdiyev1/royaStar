<?php

namespace App\Services;

use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Star\Models\Star;
use App\Modules\Star\Models\UserStar;
use App\Modules\Star\Services\StarService;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

/**
 * Shared quiz/exam answer evaluation & persistence logic.
 *
 * Both quizzes and exams follow the same flow:
 * 1. Atomically replace previous attempts for the same student+quiz/exam
 * 2. Evaluate each answer (regular = letter match, open = exact match)
 * 3. Persist per-question attempt records
 * 4. Award stars only on first completion
 */
class AssessmentService
{
    public function __construct(
        private readonly StarService $starService,
    ) {}

    /**
     * Resolve which letter (a–e) holds the correct answer for a regular question.
     */
    public function resolveRightAnswerLetter($question, string $locale = 'az'): string
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

    /**
     * Evaluate submitted answers against the question set.
     *
     * @return array{score: int, total: int, correct: int, wrong: int, skipped: int, answers: array}
     */
    public function evaluateAnswers(array $answers, BaseCollection $questions, string $locale): array
    {
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
                ],
            ];
        }

        $score = $total > 0 ? round(($correctCount / $total) * 100) : 0;

        return [
            'score' => $score,
            'total' => $total,
            'correct' => $correctCount,
            'wrong' => $wrongCount,
            'skipped' => $skippedCount,
            'answers' => $answerDetails,
        ];
    }

    /**
     * Rebuild a result payload from previously persisted attempts (used as a
     * fallback when the fresh-submission session payload is unavailable).
     *
     * @param  Collection<int, StudentQuiz|StudentExam>  $attempts
     * @return array{score: int, total: int, correct: int, wrong: int, skipped: int, answers: array}
     */
    public function buildResultFromAttempts(Collection $attempts, string $locale): array
    {
        $total = $attempts->count();
        $correct = $attempts->where('is_correct', true)->count();
        $wrong = $attempts->where('is_correct', false)->whereNotNull('answer')->where('answer', '!=', '')->count();
        $skipped = $attempts->filter(fn ($a) => empty($a->answer))->count();

        return [
            'score' => $total > 0 ? round(($correct / $total) * 100) : 0,
            'total' => $total,
            'correct' => $correct,
            'wrong' => $wrong,
            'skipped' => $skipped,
            'answers' => $attempts->map(function ($a) use ($locale) {
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
                    ],
                ];
            })->toArray(),
        ];
    }

    /**
     * Evaluate + persist a quiz submission, awarding stars on first completion.
     */
    public function submitQuiz(User $user, Student $student, Quiz $quiz, array $answers, string $locale): array
    {
        $questions = $quiz->questions->keyBy('id');

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

        return DB::transaction(function () use ($student, $quiz, $questions, $answers, $locale, $user, $alreadyAwardedCompleted, $alreadyAwardedPerfect) {
            // Delete old attempts atomically with new insert
            StudentQuiz::where('student_id', $student->id)
                ->where('quiz_id', $quiz->id)
                ->delete();

            $result = $this->evaluateAnswers($answers, $questions, $locale);

            foreach ($result['answers'] as $detail) {
                StudentQuiz::create([
                    'student_id' => $student->id,
                    'quiz_id' => $quiz->id,
                    'question_id' => $detail['question_id'],
                    'answer' => $detail['answer'],
                    'correct_answer' => $detail['correct_answer'],
                    'is_correct' => $detail['is_correct'],
                    'type' => $detail['type'],
                ]);
            }

            // Award stars only on first completion
            if (!$alreadyAwardedCompleted) {
                $this->starService->awardQuizCompleted($user->id, $quiz->id);
            }
            if ($result['score'] === 100 && !$alreadyAwardedPerfect) {
                $this->starService->awardQuizPerfect($user->id, $quiz->id);
            }

            return $result;
        });
    }

    /**
     * Evaluate + persist an exam submission, awarding stars on first completion.
     */
    public function submitExam(User $user, Student $student, Exam $exam, array $answers, string $locale): array
    {
        $questions = $exam->questions->keyBy('id');

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

            $result = $this->evaluateAnswers($answers, $questions, $locale);

            foreach ($result['answers'] as $detail) {
                StudentExam::create([
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'question_id' => $detail['question_id'],
                    'answer' => $detail['answer'],
                    'correct_answer' => $detail['correct_answer'],
                    'is_correct' => $detail['is_correct'],
                    'type' => $detail['type'],
                ]);
            }

            // Award stars only on first completion
            $passingScore = $exam->passing_score ?? 60;
            if ($result['score'] >= $passingScore && !$alreadyAwardedPassed) {
                $this->starService->awardExamPassed($user->id, $exam->id);
            }
            if ($result['score'] >= 90 && !$alreadyAwardedExcellent) {
                $this->starService->awardExamExcellent($user->id, $exam->id);
            }

            return $result;
        });
    }
}
