<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', [PageController::class, 'index'])->name('home');
Route::redirect('/welcome', '/')->name('welcome');

// Auth
Route::get('/login', [PageController::class, 'login'])->name('login');
Route::post('/login', [PageController::class, 'loginPost'])->name('login.post');
Route::get('/signup', [PageController::class, 'signup'])->name('signup');
Route::post('/signup', [PageController::class, 'signupPost'])->name('signup.post');
Route::get('/pending', [PageController::class, 'pending'])->name('pending');
Route::post('/logout', [PageController::class, 'logout'])->name('logout');

// Topics (public)
Route::get('/topics', [PageController::class, 'topics'])->name('topics');
Route::get('/topics/{topic}', [PageController::class, 'topicDetail'])->name('topics.detail');

// Authenticated student routes
Route::middleware('auth')->group(function () {
    // Lesson
    Route::get('/lesson/{id}', [PageController::class, 'lesson'])->name('lesson');
    Route::post('/lesson/{id}/rate', [PageController::class, 'lessonRate'])->name('lesson.rate');

    // Quiz
    Route::get('/quiz/{id}', [PageController::class, 'quiz'])->name('quiz');
    Route::post('/quiz/{id}/submit', [PageController::class, 'quizSubmit'])->name('quiz.submit');
    Route::get('/quiz/{id}/result', [PageController::class, 'quizResult'])->name('quiz.result');

    // Exam
    Route::get('/exam', [PageController::class, 'exam'])->name('exam');
    Route::get('/exam/{exam}', [PageController::class, 'examDetail'])->name('exam.detail');
    Route::get('/exam/{exam}/start', [PageController::class, 'examStart'])->name('exam.start');
    Route::post('/exam/{exam}/submit', [PageController::class, 'examSubmit'])->name('exam.submit');
    Route::get('/exam/{exam}/result', [PageController::class, 'examResult'])->name('exam.result');

    // Achievements
    Route::get('/achievements', [PageController::class, 'achievements'])->name('achievements');

    // Profile
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::put('/profile', [PageController::class, 'profileUpdate'])->name('profile.update');
    Route::post('/profile/avatar', [PageController::class, 'avatarUpload'])->name('profile.avatar');
    Route::put('/profile/password', [PageController::class, 'profilePassword'])->name('profile.password');
});

// Legacy redirects
Route::get('/exam/grade-9', fn() => redirect()->route('exam'))->name('grade9');
Route::get('/exam/final', fn() => redirect()->route('exam'))->name('final-exam');
