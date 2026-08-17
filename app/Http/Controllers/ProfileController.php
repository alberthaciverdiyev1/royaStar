<?php

namespace App\Http\Controllers;

use App\Modules\City\Models\City;
use App\Modules\Grade\Models\Grade;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Star\Models\Star;
use App\Modules\Star\Models\UserStar;
use App\Modules\Star\Services\StarService;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(
        private readonly StarService $starService,
    ) {}

    public function achievements(Request $request)
    {
        $user = Auth::user();
        $student = $user?->student;
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // Guard against malformed month query params that would crash Carbon::parse
        // further down (e.g. ?month=invalid or ?month=2026-13) and return a 500.
        if (!is_string($selectedMonth) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $selectedMonth)) {
            $selectedMonth = 'all';
        }

        // Generate past 6 months list + All Time option
        $availableMonths = [
            'all' => 'All Time (All Stars)',
        ];
        for ($i = 0; $i < 6; $i++) {
            $d = now()->subMonths($i);
            $key = $d->format('Y-m');
            $label = $d->format('F Y') . ($i === 0 ? ' (Current Month)' : '');
            $availableMonths[$key] = $label;
        }

        // Total stars (filtered by month if specified)
        $totalStars = $user ? $this->starService->getUserTotalStars($user->id, $selectedMonth === 'all' ? null : $selectedMonth) : 0;
        $allTimeStars = $user ? $this->starService->getUserTotalStars($user->id, null) : 0;

        // User stars query
        $userStarsQuery = $user ? UserStar::where('user_id', $user->id)->with('star') : null;
        if ($userStarsQuery && $selectedMonth !== 'all') {
            $s = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $e = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $userStarsQuery->whereBetween('created_at', [$s, $e]);
        }
        $earnedUserStars = $userStarsQuery ? $userStarsQuery->latest()->get() : collect();
        $earnedStarIds = $user ? UserStar::where('user_id', $user->id)->pluck('star_id')->unique()->toArray() : [];

        $allStars = Star::all();

        $quizCount = $student ? StudentQuiz::where('student_id', $student->id)->select('quiz_id')->distinct()->count() : 0;
        $examCount = $student ? StudentExam::where('student_id', $student->id)->select('exam_id')->distinct()->count() : 0;
        $correctAnswersCount = $student ? StudentQuiz::where('student_id', $student->id)->where('is_correct', true)->count() : 0;

        // Leaderboard query filtered by selected month
        $leaderboardQuery = User::select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COALESCE(SUM(stars.point), 0) as total_stars')
            )
            ->leftJoin('user_stars', 'users.id', '=', 'user_stars.user_id')
            ->leftJoin('stars', 'user_stars.star_id', '=', 'stars.id');

        if ($selectedMonth !== 'all') {
            $s = \Carbon\Carbon::parse($selectedMonth . '-01')->startOfMonth();
            $e = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth();
            $leaderboardQuery->whereBetween('user_stars.created_at', [$s, $e]);
        }

        $leaderboard = $leaderboardQuery->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_stars')
            ->limit(50)
            ->get();

        return view('pages.achievements', [
            'totalStars' => $totalStars,
            'allTimeStars' => $allTimeStars,
            'allStars' => $allStars,
            'earnedUserStars' => $earnedUserStars,
            'earnedStarIds' => $earnedStarIds,
            'quizCount' => $quizCount,
            'examCount' => $examCount,
            'correctAnswersCount' => $correctAnswersCount,
            'leaderboard' => $leaderboard,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        $student = $user?->student;

        $totalStars = $user ? $this->starService->getUserTotalStars($user->id) : 0;
        $starHistory = $user ? $this->starService->getStarHistory($user->id) : collect();

        return view('pages.profile', [
            'profile' => [
                'name' => $user?->name ?? 'Star Student',
                'email' => $user?->email ?? 'student@example.com',
                'phone' => $user?->phone ?? '+994 XX XXX XX XX',
                'avatar' => $user?->avatar,
            ],
            'user' => $user,
            'student' => $student,
            'totalStars' => $totalStars,
            'starHistory' => $starHistory,
            'cities' => City::all(),
            'grades' => Grade::all(),
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string|max:500',
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'city_id' => 'nullable|exists:cities,id',
            'grade_id' => 'nullable|exists:grades,id',
            'school_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        if ($user) {
            $userData = $request->only(['name', 'email', 'phone']);

            if ($request->hasFile('avatar_file')) {
                $userData['avatar'] = $this->compressAndStoreAvatar($request->file('avatar_file'));
            } elseif ($request->filled('avatar')) {
                $userData['avatar'] = $request->input('avatar');
            }

            $user->update($userData);

            $student = $user->student;
            if ($student) {
                $student->update($request->only(['city_id', 'grade_id', 'school_name', 'birth_date']));
            }
        }

        return redirect()->route('profile')
            ->with('success', 'Profiliniz və şəkiliniz uğurla yeniləndi!');
    }

    public function avatarUpload(Request $request)
    {
        $request->validate([
            'avatar_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'avatar' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if ($user) {
            if ($request->hasFile('avatar_file')) {
                $user->avatar = $this->compressAndStoreAvatar($request->file('avatar_file'));
            } elseif ($request->filled('avatar')) {
                $user->avatar = $request->input('avatar');
            }
            $user->save();
        }

        return redirect()->route('profile')
            ->with('success', 'Profil şəkliniz uğurla yeniləndi!');
    }

    /**
     * Compress photo with 256x256 center-crop & WebP 75% compression (~15KB)
     */
    private function compressAndStoreAvatar($file): string
    {
        $filename = 'avatar_' . Auth::id() . '_' . time() . '.webp';
        $storageDir = storage_path('app/public/avatars');
        $publicDir = public_path('storage/avatars');

        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        if (!file_exists($publicDir)) {
            @mkdir($publicDir, 0755, true);
        }

        $destinationPath = $storageDir . '/' . $filename;
        $publicPath = $publicDir . '/' . $filename;

        if (extension_loaded('gd')) {
            $imageContent = file_get_contents($file->getRealPath());
            $srcImage = @imagecreatefromstring($imageContent);

            if ($srcImage !== false) {
                $srcWidth = imagesx($srcImage);
                $srcHeight = imagesy($srcImage);
                $targetSize = 256;

                // Center crop calculations
                if ($srcWidth > $srcHeight) {
                    $cropX = (int) round(($srcWidth - $srcHeight) / 2);
                    $cropY = 0;
                    $cropSize = $srcHeight;
                } else {
                    $cropX = 0;
                    $cropY = (int) round(($srcHeight - $srcWidth) / 2);
                    $cropSize = $srcWidth;
                }

                $dstImage = imagecreatetruecolor($targetSize, $targetSize);

                // Preserve alpha transparency
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);

                imagecopyresampled(
                    $dstImage,
                    $srcImage,
                    0, 0,
                    $cropX, $cropY,
                    $targetSize, $targetSize,
                    $cropSize, $cropSize
                );

                if (function_exists('imagewebp')) {
                    imagewebp($dstImage, $destinationPath, 75);
                    @imagewebp($dstImage, $publicPath, 75);
                } else {
                    $filename = 'avatar_' . Auth::id() . '_' . time() . '.jpg';
                    $destinationPath = $storageDir . '/' . $filename;
                    $publicPath = $publicDir . '/' . $filename;
                    imagejpeg($dstImage, $destinationPath, 75);
                    @imagejpeg($dstImage, $publicPath, 75);
                }

                imagedestroy($srcImage);
                imagedestroy($dstImage);

                return '/storage/avatars/' . $filename;
            }
        }

        // Fallback store
        $path = $file->store('avatars', 'public');
        @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
        return '/storage/' . $path;
    }

    public function profilePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return redirect()->route('profile')
                ->with('error', 'Current password is incorrect.');
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('profile')
            ->with('success', 'Password updated successfully!');
    }
}
