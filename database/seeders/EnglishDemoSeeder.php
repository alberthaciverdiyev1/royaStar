<?php

namespace Database\Seeders;

use App\Modules\Exam\Models\Exam;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use App\Modules\Question\Models\Question;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use App\Modules\Grade\Models\Grade;
use Illuminate\Database\Seeder;

class EnglishDemoSeeder extends Seeder
{
    public function run(): void
    {
        $topicData = [
            ['name' => 'Qrammatika', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [2, 3, 4, 5]],
            ['name' => 'Lüğət ehtiyatı', 'difficulty_level' => DifficultyLevel::Beginner, 'grades' => [1, 2, 3, 4]],
            ['name' => 'Oxu', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [4, 5, 6, 7]],
            ['name' => 'Danışıq və dinləmə', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [3, 4, 5, 6, 7]],
            ['name' => 'Yazı', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [5, 6, 7, 8]],
        ];

        $allGrades = Grade::all();

        $index = 0;
        foreach ($topicData as $td) {
            $gradeNums = $td['grades'] ?? [];
            unset($td['grades']);

            $topic = Topic::create($td);

            if (!empty($gradeNums)) {
                $gradeIds = $allGrades->filter(fn($g) => in_array((int) preg_replace('/\D/', '', $g->name), $gradeNums))->pluck('id');
                $topic->grades()->sync($gradeIds);
            }

            $lesson = Lesson::create([
                'name' => "{$topic->name} — Giriş",
                'description' => "{$topic->name} mövzusuna aid ilk dərs",
                'topic_id' => $topic->id,
            ]);

            Video::create([
                'lesson_id' => $lesson->id,
                'name' => "{$topic->name} Video Dərs",
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]);

            $this->createRegularQuestions($topic, $lesson, $index);
            $this->createQuiz($lesson, $topic);
            $index++;
        }

        $this->createExams($allGrades);
    }

    private function createExams($allGrades): void
    {
        $regularIds = Question::where('type', 'regular')->pluck('id')->toArray();
        $openIds = Question::where('type', 'open')->pluck('id')->toArray();

        $definitions = [
            [
                'name' => 'İngilis dili — Ümumi İmtahan',
                'description' => 'Bütün mövzulardan ümumi bilik imtahanı',
                'type' => 'general',
                'duration_minutes' => 20,
                'passing_score' => 50,
                'question_count' => 10,
            ],
            [
                'name' => 'İngilis dili — Final İmtahan',
                'description' => 'Semestr sonu yekun imtahan',
                'type' => 'final',
                'duration_minutes' => 30,
                'passing_score' => 60,
                'question_count' => 20,
            ],
        ];

        $grade = $allGrades->first();

        foreach ($definitions as $def) {
            $questionIds = array_merge(
                array_slice($regularIds, 0, max(0, $def['question_count'] - 1)),
                array_slice($openIds, 0, 1),
            );

            $exam = Exam::create([
                'name' => $def['name'],
                'description' => $def['description'],
                'grade_id' => $grade?->id,
                'duration_minutes' => $def['duration_minutes'],
                'passing_score' => $def['passing_score'],
                'total_questions' => count($questionIds),
                'type' => $def['type'],
            ]);

            $exam->questions()->sync(
                collect($questionIds)->mapWithKeys(fn($id, $i) => [$id => ['order' => $i + 1]])->all()
            );
        }
    }

    private function createRegularQuestions(Topic $topic, Lesson $lesson, int $tIdx): void
    {
        $englishSets = [
            // Qrammatika (Grammar)
            [
                ['q' => 'Which of these is a noun?', 'a' => 'Run', 'b' => 'Beautiful', 'c' => 'Cat', 'd' => 'Quickly'],
                ['q' => 'What is the past tense of "go"?', 'a' => 'Goed', 'b' => 'Went', 'c' => 'Gone', 'd' => 'Going'],
                ['q' => 'Which sentence is correct?', 'a' => 'He go to school', 'b' => 'He goes to school', 'c' => 'He going school', 'd' => 'He to go school'],
                ['q' => 'Which word is an adjective?', 'a' => 'Quickly', 'b' => 'Run', 'c' => 'Happy', 'd' => 'They'],
                ['q' => 'What is the plural of "child"?', 'a' => 'Childs', 'b' => 'Childes', 'c' => 'Children', 'd' => 'Child'],
                ['q' => 'Which is a preposition?', 'a' => 'And', 'b' => 'Under', 'c' => 'Because', 'd' => 'Quickly'],
                ['q' => 'Choose the correct article: ___ apple', 'a' => 'A', 'b' => 'An', 'c' => 'The', 'd' => 'None'],
                ['q' => 'Which is a complete sentence?', 'a' => 'Running fast', 'b' => 'The dog runs fast', 'c' => 'Fast dog', 'd' => 'Running dog fast'],
                ['q' => 'What is the comparative of "good"?', 'a' => 'Gooder', 'b' => 'Better', 'c' => 'Best', 'd' => 'More good'],
                ['q' => 'Which is a pronoun?', 'a' => 'Table', 'b' => 'They', 'c' => 'Run', 'd' => 'Blue'],
            ],
            // Lüğət ehtiyatı (Vocabulary)
            [
                ['q' => 'What does "happy" mean?', 'a' => 'Sad', 'b' => 'Angry', 'c' => 'Joyful', 'd' => 'Tired'],
                ['q' => 'Which word means the opposite of "big"?', 'a' => 'Large', 'b' => 'Huge', 'c' => 'Small', 'd' => 'Tall'],
                ['q' => 'What is a synonym for "quick"?', 'a' => 'Slow', 'b' => 'Fast', 'c' => 'Heavy', 'd' => 'Light'],
                ['q' => 'Which word describes a person who teaches?', 'a' => 'Doctor', 'b' => 'Teacher', 'c' => 'Driver', 'd' => 'Farmer'],
                ['q' => 'What does "beautiful" mean?', 'a' => 'Ugly', 'b' => 'Pretty', 'c' => 'Small', 'd' => 'Old'],
                ['q' => 'Which is a fruit?', 'a' => 'Carrot', 'b' => 'Apple', 'c' => 'Bread', 'd' => 'Rice'],
                ['q' => 'What is the opposite of "hot"?', 'a' => 'Warm', 'b' => 'Cool', 'c' => 'Cold', 'd' => 'Mild'],
                ['q' => 'Which word means "to look at"?', 'a' => 'Hear', 'b' => 'Touch', 'c' => 'See', 'd' => 'Smell'],
                ['q' => 'What is a synonym for "intelligent"?', 'a' => 'Silly', 'b' => 'Smart', 'c' => 'Lazy', 'd' => 'Rude'],
                ['q' => 'Which word is a color?', 'a' => 'Monday', 'b' => 'Summer', 'c' => 'Purple', 'd' => 'House'],
            ],
            // Oxu (Reading)
            [
                ['q' => 'What is the main idea of a story?', 'a' => 'The title', 'b' => 'The central message', 'c' => 'The first sentence', 'd' => 'The last word'],
                ['q' => 'What is a character in a story?', 'a' => 'A chapter', 'b' => 'A person in the story', 'c' => 'The cover', 'd' => 'The ending'],
                ['q' => 'What does "setting" mean?', 'a' => 'The characters', 'b' => 'The time and place', 'c' => 'The problem', 'd' => 'The solution'],
                ['q' => 'What is a paragraph?', 'a' => 'A single word', 'b' => 'A group of sentences', 'c' => 'A chapter', 'd' => 'A book'],
                ['q' => 'What does "predict" mean?', 'a' => 'To read again', 'b' => 'To guess what happens next', 'c' => 'To write', 'd' => 'To finish'],
                ['q' => 'What is a synonym for "story"?', 'a' => 'Poem', 'b' => 'Tale', 'c' => 'Letter', 'd' => 'List'],
                ['q' => 'What is a fact?', 'a' => 'An opinion', 'b' => 'Something true', 'c' => 'A question', 'd' => 'A character'],
                ['q' => 'What does "author" mean?', 'a' => 'A reader', 'b' => 'The person who writes', 'c' => 'A book', 'd' => 'A librarian'],
                ['q' => 'What is the purpose of a title?', 'a' => 'To end the story', 'b' => 'To name the story', 'c' => 'To describe characters', 'd' => 'To ask a question'],
                ['q' => 'What does "conclusion" mean?', 'a' => 'The beginning', 'b' => 'The middle', 'c' => 'The end', 'd' => 'The title'],
            ],
            // Danışıq və dinləmə (Speaking & Listening)
            [
                ['q' => 'How do you greet someone in the morning?', 'a' => 'Good night', 'b' => 'Good morning', 'c' => 'Goodbye', 'd' => 'See you'],
                ['q' => 'What do you say when you meet someone?', 'a' => 'Sorry', 'b' => 'Thank you', 'c' => 'Hello', 'd' => 'Goodbye'],
                ['q' => 'How do you ask for help?', 'a' => 'I am fine', 'b' => 'Can you help me?', 'c' => 'I know', 'd' => 'Look there'],
                ['q' => 'What does "Please" express?', 'a' => 'Anger', 'b' => 'Politeness', 'c' => 'Surprise', 'd' => 'Sadness'],
                ['q' => 'How do you introduce yourself?', 'a' => 'How are you?', 'b' => 'My name is...', 'c' => 'Where is...', 'd' => 'I like...'],
                ['q' => 'What do you say when someone thanks you?', 'a' => 'Please', 'b' => 'Sorry', 'c' => 'You\'re welcome', 'd' => 'Fine'],
                ['q' => 'How do you ask for directions?', 'a' => 'How much?', 'b' => 'Where is the...?', 'c' => 'What time?', 'd' => 'Who is?'],
                ['q' => 'What does "Excuse me" mean?', 'a' => 'I am sorry', 'b' => 'Attention please', 'c' => 'Goodbye', 'd' => 'Thank you'],
                ['q' => 'How do you end a conversation?', 'a' => 'Hello', 'b' => 'Nice to meet you', 'c' => 'Goodbye', 'd' => 'How are you?'],
                ['q' => 'What is a polite request?', 'a' => 'Give me!', 'b' => 'I want!', 'c' => 'Could you...?', 'd' => 'Now!'],
            ],
            // Yazı (Writing)
            [
                ['q' => 'What is a capital letter used for?', 'a' => 'Every word', 'b' => 'The start of a sentence', 'c' => 'The end of a word', 'd' => 'Middle of a word'],
                ['q' => 'What is a period (.) used for?', 'a' => 'To ask a question', 'b' => 'To end a sentence', 'c' => 'To show excitement', 'd' => 'To pause'],
                ['q' => 'What is a subject in writing?', 'a' => 'The main topic', 'b' => 'The verb', 'c' => 'The punctuation', 'd' => 'The conclusion'],
                ['q' => 'What is a verb?', 'a' => 'A describing word', 'b' => 'An action word', 'c' => 'A naming word', 'd' => 'A connecting word'],
                ['q' => 'What does punctuation help with?', 'a' => 'Making words longer', 'b' => 'Making writing clear', 'c' => 'Adding letters', 'd' => 'Deleting words'],
                ['q' => 'What is an essay?', 'a' => 'A single sentence', 'b' => 'A short piece of writing', 'c' => 'A list of words', 'd' => 'A picture'],
                ['q' => 'What is brainstorming?', 'a' => 'Writing the final copy', 'b' => 'Thinking of ideas', 'c' => 'Drawing pictures', 'd' => 'Reading aloud'],
                ['q' => 'What is a topic sentence?', 'a' => 'The last sentence', 'b' => 'The main idea sentence', 'c' => 'A question', 'd' => 'A quote'],
                ['q' => 'What does "edit" mean?', 'a' => 'To write the first draft', 'b' => 'To fix mistakes', 'c' => 'To publish', 'd' => 'To read once'],
                ['q' => 'What is a conclusion paragraph?', 'a' => 'The opening paragraph', 'b' => 'The final summary', 'c' => 'The middle part', 'd' => 'A list of ideas'],
            ],
        ];

        $questions = $englishSets[$tIdx] ?? $englishSets[0];

        foreach ($questions as $qi => $qData) {
            $rightAnswer = 'a';

            Question::create([
                'question' => [
                    ['type' => 'text', 'content' => "{$qData['q']}"],
                ],
                'type' => 'regular',
                'right_answer' => $rightAnswer,
                'variant_a' => [
                    ['type' => 'text', 'content' => $qData['a']],
                ],
                'variant_b' => [
                    ['type' => 'text', 'content' => $qData['b']],
                ],
                'variant_c' => [
                    ['type' => 'text', 'content' => $qData['c']],
                ],
                'variant_d' => [
                    ['type' => 'text', 'content' => $qData['d']],
                ],
                'variant_e' => [
                    ['type' => 'text', 'content' => "I don't know"],
                ],
                'explanation' => [
                    ['type' => 'text', 'content' => "Doğru cavab: {$qData['a']}"],
                ],
                'difficulty_level' => $topic->difficulty_level->value,
                'lesson_id' => $lesson->id,
            ]);
        }

        // Open question
        Question::create([
            'question' => [
                ['type' => 'text', 'content' => "{$topic->name} mövzusunu izah edin və nümunələr verin."],
            ],
            'type' => 'open',
            'open_answer' => [
                ['type' => 'text', 'content' => "{$topic->name} mövzusu İngilis dili sahəsində vacib bir mövzudur."],
            ],
            'difficulty_level' => $topic->difficulty_level->value,
            'lesson_id' => $lesson->id,
        ]);
    }

    private function createOpenQuestions(Topic $topic, Lesson $lesson, int &$index): void
    {
        // open questions are created inside createRegularQuestions
    }

    private function createQuiz(Lesson $lesson, Topic $topic): void
    {
        $questionIds = Question::where('lesson_id', $lesson->id)->pluck('id')->toArray();

        $quiz = Quiz::create([
            'name' => "{$topic->name} — Test",
            'type' => 'topic_based',
            'lesson_id' => $lesson->id,
        ]);
        $quiz->questions()->attach($questionIds);
    }
}
