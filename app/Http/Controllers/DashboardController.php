<?php

namespace App\Http\Controllers;

use App\Modules\City\Models\City;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Grade\Models\Grade;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\LessonReview;
use App\Modules\Question\Models\Question;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Student\Models\Student;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        // Basic counts
        $cities = City::count();
        $grades = Grade::count();
        $topics = Topic::count();
        $lessons = Lesson::count();
        $questions = Question::count();
        $quizzes = Quiz::count();
        $exams = Exam::count();
        $students = Student::count();
        $teachers = User::role('teacher')->count();
        $users = User::count();
        $pendingUsers = User::where('is_approved', false)->count();

        // Engagement
        $totalQuizAttempts = StudentQuiz::count();
        $totalExamAttempts = StudentExam::count();
        $totalReviews = LessonReview::count();
        $averageRating = round(LessonReview::avg('rating') ?? 0, 1);

        // Gamification (via user_stars + stars tables)
        $totalXp = DB::table('user_stars')
            ->join('stars', 'user_stars.star_id', '=', 'stars.id')
            ->sum('stars.point');

        // Growth
        $newUsersToday = User::whereDate('created_at', today())->count();
        $newUsersWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()])->count();
        $newUsersMonth = User::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Last 7 days signups
        $weeklySignups = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('created_at', $date)->count();
            $weeklySignups->push([
                'date' => $date->format('D'),
                'count' => $count,
            ]);
        }

        // User type distribution
        $userTypeDistribution = User::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderByDesc('count')
            ->get()
            ->map(fn($item) => ['type' => $item->type, 'count' => $item->count]);

        // Recent registrations
        $recentUsers = User::select('id', 'name', 'email', 'avatar', 'type', 'created_at')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'type' => $u->type,
                'created_at' => $u->created_at,
            ]);

        // Top students by star points
        $topStudents = User::select(
            'users.id',
            'users.name',
            'users.email',
            'users.avatar',
            DB::raw('COALESCE(SUM(stars.point), 0) as total_points')
        )
            ->join('user_stars', 'users.id', '=', 'user_stars.user_id')
            ->join('stars', 'user_stars.star_id', '=', 'stars.id')
            ->where('users.type', 'student')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        return apiResponse(data: [
            'cities' => $cities,
            'grades' => $grades,
            'topics' => $topics,
            'lessons' => $lessons,
            'questions' => $questions,
            'quizzes' => $quizzes,
            'exams' => $exams,
            'students' => $students,
            'teachers' => $teachers,
            'users' => $users,
            'pending_users' => $pendingUsers,
            'total_quiz_attempts' => $totalQuizAttempts,
            'total_exam_attempts' => $totalExamAttempts,
            'total_reviews' => $totalReviews,
            'average_rating' => $averageRating,
            'total_xp' => $totalXp,
            'new_users_today' => $newUsersToday,
            'new_users_week' => $newUsersWeek,
            'new_users_month' => $newUsersMonth,
            'weekly_signups' => $weeklySignups,
            'user_type_distribution' => $userTypeDistribution,
            'recent_users' => $recentUsers,
            'top_students' => $topStudents,
        ]);
    }
}
