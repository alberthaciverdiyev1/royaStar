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
        $subjectName = $topic->subject->name;

        $questions = [
            "What is the basic concept of {$topic->name} in {$subjectName}?",
            "Which of the following best describes {$topic->name}?",
            "What is the main principle of {$topic->name}?",
            "How does {$topic->name} apply to real-world problems?",
            "Which formula is used in {$topic->name}?",
            "What is the primary focus of {$topic->name}?",
            "Which theory is most relevant to {$topic->name}?",
            "What is the correct definition of {$topic->name}?",
            "Which method is commonly used in {$topic->name}?",
            "What is the key characteristic of {$topic->name}?",
        ];

        $rightAnswerIdx = $num % 5;

        $variants = ['a', 'b', 'c', 'd', 'e'];
        $rightAnswer = $variants[$rightAnswerIdx];
        $answers = [];
        foreach ($variants as $j => $v) {
            if ($j === $rightAnswerIdx) {
                $answers["variant_{$v}"] = [
                    'az' => [['type' => 'text', 'content' => "Düzgün cavab: {$topic->name} — sual {$num}"]],
                    'en' => [['type' => 'text', 'content' => "Correct answer for {$topic->name} — question {$num}"]],
                    'ru' => [['type' => 'text', 'content' => "Правильный ответ: {$topic->name} — вопрос {$num}"]],
                ];
            } else {
                $answers["variant_{$v}"] = [
                    'az' => [['type' => 'text', 'content' => "Seçim {$v}: Səhv cavab {$num}"]],
                    'en' => [['type' => 'text', 'content' => "Option {$v}: Wrong answer {$num}"]],
                    'ru' => [['type' => 'text', 'content' => "Вариант {$v}: Неправильный ответ {$num}"]],
                ];
            }
        }

        Question::create(array_merge([
            'question' => [
                'az' => [['type' => 'text', 'content' => "{$topic->name} haqqında sual {$num}?"]],
                'en' => [['type' => 'text', 'content' => $questions[$i % count($questions)]]],
                'ru' => [['type' => 'text', 'content' => "Вопрос о {$topic->name} {$num}?"]],
            ],
            'type' => 'regular',
            'right_answer' => $rightAnswer,
            'explanation' => [
                'az' => [['type' => 'text', 'content' => "{$topic->name} mövzusu üçün izahat {$num}"]],
                'en' => [['type' => 'text', 'content' => "Explanation for {$topic->name} question {$num}"]],
                'ru' => [['type' => 'text', 'content' => "Объяснение для вопроса {$num} по теме {$topic->name}"]],
            ],
            'difficulty_level' => $topic->difficulty_level->value,
            'lesson_id' => $lesson->id,
        ], $answers));
    }

    private function createOpenQuestion(Topic $topic, Lesson $lesson, int $i): void
    {
        $num = ($i % 10) + 1;
        $subjectName = $topic->subject->name;

        Question::create([
            'question' => [
                'az' => [['type' => 'text', 'content' => "{$topic->name} mövzusunu izah edin. Nümunələr verin."]],
                'en' => [['type' => 'text', 'content' => "Explain {$topic->name}. Provide examples."]],
                'ru' => [['type' => 'text', 'content' => "Объясните тему «{$topic->name}». Приведите примеры."]],
            ],
            'type' => 'open',
            'open_answer' => [
                'az' => [['type' => 'text', 'content' => "{$topic->name} mövzusu {$subjectName} sahəsində vacib bir mövzudur. Əsas prinsiplərə daxildir: tərif, xüsusiyyətlər və tətbiq sahələri."]],
                'en' => [['type' => 'text', 'content' => "{$topic->name} is an important topic in {$subjectName}. Key principles include: definition, characteristics, and application areas."]],
                'ru' => [['type' => 'text', 'content' => "Тема «{$topic->name}» является важной в области {$subjectName}. Основные принципы включают: определение, характеристики и области применения."]],
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
