<?php

namespace Database\Seeders;

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
            // 2 questions per topic
            $this->createRegularQuestion($topic, $index++);
            $this->createOpenQuestion($topic, $index++);
        }
    }

    private function createRegularQuestion(Topic $topic, int $i): void
    {
        $num = ($i % 10) + 1;
        $subjectName = $topic->subject->name['en'] ?? 'General';

        $questions = [
            "What is the basic concept of {$topic->name['en']} in {$subjectName}?",
            "Which of the following best describes {$topic->name['en']}?",
            "What is the main principle of {$topic->name['en']}?",
            "How does {$topic->name['en']} apply to real-world problems?",
            "Which formula is used in {$topic->name['en']}?",
            "What is the primary focus of {$topic->name['en']}?",
            "Which theory is most relevant to {$topic->name['en']}?",
            "What is the correct definition of {$topic->name['en']}?",
            "Which method is commonly used in {$topic->name['en']}?",
            "What is the key characteristic of {$topic->name['en']}?",
        ];

        $rightAnswerIdx = $num % 5;

        $variants = ['a', 'b', 'c', 'd', 'e'];
        $rightAnswer = $variants[$rightAnswerIdx];
        $answers = [];
        foreach ($variants as $j => $v) {
            if ($j === $rightAnswerIdx) {
                $answers["variant_{$v}"] = [
                    'az' => "Düzgün cavab: {$topic->name['az']} — sual {$num}",
                    'en' => "Correct answer for {$topic->name['en']} — question {$num}",
                    'ru' => "Правильный ответ: {$topic->name['en']} — вопрос {$num}",
                ];
            } else {
                $answers["variant_{$v}"] = [
                    'az' => "Seçim {$v}: Səhv cavab {$num}",
                    'en' => "Option {$v}: Wrong answer {$num}",
                    'ru' => "Вариант {$v}: Неправильный ответ {$num}",
                ];
            }
        }

        Question::create(array_merge([
            'question' => [
                'az' => "{$topic->name['az']} haqqında sual {$num}?",
                'en' => $questions[$i % count($questions)],
                'ru' => "Вопрос о {$topic->name['en']} {$num}?",
            ],
            'type' => 'regular',
            'right_answer' => $rightAnswer,
            'explanation' => [
                'az' => "{$topic->name['az']} mövzusu üçün izahat {$num}",
                'en' => "Explanation for {$topic->name['en']} question {$num}",
                'ru' => "Объяснение для вопроса {$num} по теме {$topic->name['en']}",
            ],
            'difficulty_level' => $topic->difficulty_level->value,
            'topic_id' => $topic->id,
        ], $answers));
    }

    private function createOpenQuestion(Topic $topic, int $i): void
    {
        $num = ($i % 10) + 1;
        $subjectName = $topic->subject->name['en'] ?? 'General';

        Question::create([
            'question' => [
                'az' => "{$topic->name['az']} mövzusunu izah edin. Nümunələr verin.",
                'en' => "Explain {$topic->name['en']}. Provide examples.",
                'ru' => "Объясните тему «{$topic->name['en']}». Приведите примеры.",
            ],
            'type' => 'open',
            'open_answer' => [
                'az' => "{$topic->name['az']} mövzusu {$subjectName} sahəsində vacib bir mövzudur. Əsas prinsiplərə daxildir: tərif, xüsusiyyətlər və tətbiq sahələri.",
                'en' => "{$topic->name['en']} is an important topic in {$subjectName}. Key principles include: definition, characteristics, and application areas.",
                'ru' => "Тема «{$topic->name['en']}» является важной в области {$subjectName}. Основные принципы включают: определение, характеристики и области применения.",
            ],
            'difficulty_level' => $topic->difficulty_level->value,
            'topic_id' => $topic->id,
        ]);
    }

    private function createQuizzes(): void
    {
        $topics = Topic::all();
        $allQuestionIds = Question::pluck('id')->toArray();

        // 50 topic-based quizzes — one per topic, 2 questions each
        foreach ($topics as $topic) {
            $questionIds = Question::where('topic_id', $topic->id)->pluck('id')->toArray();

            Quiz::create([
                'name' => [
                    'az' => "{$topic->name['az']} — Test",
                    'en' => "{$topic->name['en']} — Quiz",
                    'ru' => "{$topic->name['en']} — Тест",
                ],
                'type' => 'topic_based',
                'topic_id' => $topic->id,
                'lesson_id' => null,
            ])->questions()->attach($questionIds);
        }

        // 5 general quizzes — mix questions from multiple topics
        $generalQuizNames = [
            ['az' => 'Ümumi bilik testi 1', 'en' => 'General Knowledge Quiz 1', 'ru' => 'Общий тест знаний 1'],
            ['az' => 'Ümumi bilik testi 2', 'en' => 'General Knowledge Quiz 2', 'ru' => 'Общий тест знаний 2'],
            ['az' => 'Elm və texnologiya', 'en' => 'Science & Technology', 'ru' => 'Наука и технологии'],
            ['az' => 'Dil və ədəbiyyat', 'en' => 'Language & Literature', 'ru' => 'Язык и литература'],
            ['az' => 'Riyaziyyat və məntiq', 'en' => 'Mathematics & Logic', 'ru' => 'Математика и логика'],
        ];

        $chunkSize = intdiv(count($allQuestionIds), count($generalQuizNames));

        foreach ($generalQuizNames as $i => $name) {
            $offset = $i * $chunkSize;
            $ids = array_slice($allQuestionIds, $offset, $chunkSize);

            Quiz::create([
                'name' => $name,
                'type' => 'general',
                'topic_id' => null,
                'lesson_id' => null,
            ])->questions()->attach($ids);
        }
    }
}
