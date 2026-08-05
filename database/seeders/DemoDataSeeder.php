<?php

namespace Database\Seeders;

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Question\Models\Question;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->createQuestions();
        $this->createQuizzes();
    }

    private function createQuestions(): void
    {
        $topics = Topic::all();
        $index = 0;

        foreach ($topics as $topic) {
            $lesson = Lesson::create([
                'name' => "{$topic->name} Lesson",
                'description' => "Lesson for {$topic->name}",
                'topic_id' => $topic->id,
            ]);

            $this->createRegularQuestion($topic, $lesson, $index++);
            $this->createOpenQuestion($topic, $lesson, $index++);
        }
    }

    private function createRegularQuestion(Topic $topic, Lesson $lesson, int $i): void
    {
        $num = ($i % 10) + 1;

        $rightAnswerIdx = $num % 5;

        $variants = ['a', 'b', 'c', 'd', 'e'];
        $rightAnswer = $variants[$rightAnswerIdx];
        $answers = [];
        foreach ($variants as $j => $v) {
            if ($j === $rightAnswerIdx) {
                $answers["variant_{$v}"] = [
                    ['type' => 'text', 'content' => "Düzgün cavab: {$topic->name} — sual {$num}"],
                ];
            } else {
                $answers["variant_{$v}"] = [
                    ['type' => 'text', 'content' => "Seçim {$v}: Səhv cavab {$num}"],
                ];
            }
        }

        Question::create(array_merge([
            'question' => [
                ['type' => 'text', 'content' => "{$topic->name} haqqında sual {$num}?"],
            ],
            'type' => 'regular',
            'right_answer' => $rightAnswer,
            'explanation' => [
                ['type' => 'text', 'content' => "{$topic->name} mövzusu üçün izahat {$num}"],
            ],
            'difficulty_level' => $topic->difficulty_level->value,
            'lesson_id' => $lesson->id,
        ], $answers));
    }

    private function createOpenQuestion(Topic $topic, Lesson $lesson, int $i): void
    {
        $num = ($i % 10) + 1;

        Question::create([
            'question' => [
                ['type' => 'text', 'content' => "{$topic->name} mövzusunu izah edin. Nümunələr verin."],
            ],
            'type' => 'open',
            'open_answer' => [
                ['type' => 'text', 'content' => "{$topic->name} mövzusu İngilis dili sahəsində vacib bir mövzudur. Əsas prinsiplərə daxildir: tərif, xüsusiyyətlər və tətbiq sahələri."],
            ],
            'difficulty_level' => $topic->difficulty_level->value,
            'lesson_id' => $lesson->id,
        ]);
    }

    private function createQuizzes(): void
    {
        $topics = Topic::all();
        $lessons = Lesson::all()->keyBy('topic_id');

        // 50 topic-based quizzes — one per topic
        foreach ($topics as $topic) {
            $lesson = $lessons->get($topic->id);
            if (!$lesson) continue;

            $questionIds = Question::where('lesson_id', $lesson->id)->pluck('id')->toArray();

            Quiz::create([
                'name' => "{$topic->name} — Quiz",
                'type' => 'topic_based',
                'lesson_id' => $lesson->id,
            ])->questions()->attach($questionIds);
        }

        // 5 general quizzes
        $allQuestionIds = Question::pluck('id')->toArray();
        $generalQuizNames = [
            'General Knowledge Quiz 1',
            'General Knowledge Quiz 2',
            'Science & Technology',
            'Language & Literature',
            'Mathematics & Logic',
        ];

        $chunkSize = count($allQuestionIds) > 0 ? intdiv(count($allQuestionIds), count($generalQuizNames)) : 0;

        foreach ($generalQuizNames as $i => $name) {
            $offset = $i * $chunkSize;
            $ids = array_slice($allQuestionIds, $offset, $chunkSize);

            Quiz::create([
                'name' => $name,
                'type' => 'general',
                'lesson_id' => null,
            ])->questions()->attach($ids);
        }
    }
}
