<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/welcome', '/')->name('welcome');

// Auth
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost'])->name('login.post')->middleware('throttle:10,1');
Route::get('/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signupPost'])->name('signup.post')->middleware('throttle:5,1');
Route::get('/pending', [AuthController::class, 'pending'])->name('pending');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Topics (public)
Route::get('/topics', [CurriculumController::class, 'topics'])->name('topics');
Route::get('/topics/{topic}', [CurriculumController::class, 'topicDetail'])->name('topics.detail');

// Authenticated student routes
Route::middleware('auth')->group(function () {
    // Lesson
    Route::get('/lesson/{id}', [CurriculumController::class, 'lesson'])->name('lesson');
    Route::post('/lesson/{id}/rate', [CurriculumController::class, 'lessonRate'])->name('lesson.rate');

    // Quiz
    Route::get('/quiz/{id}', [QuizController::class, 'quiz'])->name('quiz');
    Route::post('/quiz/{id}/check-answer', [QuizController::class, 'quizCheckAnswer'])->name('quiz.check-answer');
    Route::post('/quiz/{id}/submit', [QuizController::class, 'quizSubmit'])->name('quiz.submit');
    Route::get('/quiz/{id}/result', [QuizController::class, 'quizResult'])->name('quiz.result');

    // Exam
    Route::get('/exam', [ExamController::class, 'exam'])->name('exam');
    Route::get('/exam/grade/{grade}', [ExamController::class, 'examGrade'])->whereNumber('grade')->name('exam.grade');
    Route::get('/exam/{exam}', [ExamController::class, 'examDetail'])->whereNumber('exam')->name('exam.detail');
    Route::get('/exam/{exam}/start', [ExamController::class, 'examStart'])->name('exam.start');
    Route::post('/exam/{exam}/submit', [ExamController::class, 'examSubmit'])->name('exam.submit');
    Route::get('/exam/{exam}/result', [ExamController::class, 'examResult'])->name('exam.result');

    // Achievements
    Route::get('/achievements', [ProfileController::class, 'achievements'])->name('achievements');

    // Profile
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'profileUpdate'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatarUpload'])->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'profilePassword'])->name('profile.password');
});

// Legacy redirects
Route::get('/exam/grade-9', fn() => redirect()->route('exam'))->name('grade9');
Route::get('/exam/final', fn() => redirect()->route('exam'))->name('final-exam');
