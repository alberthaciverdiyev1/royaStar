<?php

namespace App\Console\Commands;

use App\Modules\Star\Models\UserStar;
use App\Modules\User\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetMonthlyStarsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stars:reset-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes monthly star leaderboard cycle transition at the end of each month at 23:59.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $currentMonth = now()->format('Y-m');
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $topStudents = User::select(
                'users.id',
                'users.name',
                DB::raw('COALESCE(SUM(stars.point), 0) as month_stars')
            )
            ->join('user_stars', 'users.id', '=', 'user_stars.user_id')
            ->join('stars', 'user_stars.star_id', '=', 'stars.id')
            ->whereBetween('user_stars.created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('month_stars')
            ->limit(3)
            ->get();

        Log::info("Monthly Star Leaderboard Reset completed for period {$currentMonth}.", [
            'period' => $currentMonth,
            'top_students' => $topStudents->toArray(),
        ]);

        $this->info("Monthly Star Leaderboard reset for {$currentMonth} completed successfully.");

        return Command::SUCCESS;
    }
}
