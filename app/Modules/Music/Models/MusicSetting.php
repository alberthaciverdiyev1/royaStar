<?php

namespace App\Modules\Music\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;

/**
 * Single-row general music settings: which website pages may play music.
 */
class MusicSetting extends Model
{
    use SerializesDates;

    /**
     * Website areas an admin can target. Keys are stored in `pages`;
     * labels are shown in the admin panel.
     */
    public const PAGES = [
        'home' => 'Ana səhifə',
        'topics' => 'Mövzular',
        'lesson' => 'Dərslər',
        'quiz' => 'Quizlər',
        'exam' => 'İmtahanlar',
        'achievements' => 'Ulduzlar',
        'accepted-students' => 'Uğurlu tələbələr',
        'profile' => 'Profil',
        'auth' => 'Giriş / Qeydiyyat',
    ];

    /**
     * Maps every web route name to one of the PAGES keys above.
     */
    public const ROUTE_PAGE_MAP = [
        'home' => 'home',
        'welcome' => 'home',
        'topics' => 'topics',
        'topics.detail' => 'topics',
        'lesson' => 'lesson',
        'lesson.rate' => 'lesson',
        'lesson.progress' => 'lesson',
        'quiz' => 'quiz',
        'quiz.check-answer' => 'quiz',
        'quiz.submit' => 'quiz',
        'quiz.result' => 'quiz',
        'exam' => 'exam',
        'exam.grade' => 'exam',
        'exam.detail' => 'exam',
        'exam.start' => 'exam',
        'exam.submit' => 'exam',
        'exam.result' => 'exam',
        'grade9' => 'exam',
        'final-exam' => 'exam',
        'achievements' => 'achievements',
        'accepted-students' => 'accepted-students',
        'profile' => 'profile',
        'profile.update' => 'profile',
        'profile.avatar' => 'profile',
        'profile.password' => 'profile',
        'login' => 'auth',
        'login.post' => 'auth',
        'signup' => 'auth',
        'signup.post' => 'auth',
        'pending' => 'auth',
        'logout' => 'auth',
    ];

    protected $fillable = [
        'play_on_all',
        'pages',
    ];

    protected function casts(): array
    {
        return [
            'play_on_all' => 'boolean',
            'pages' => 'array',
        ];
    }

    /**
     * The settings row, or sensible defaults when nothing has been saved yet.
     */
    public static function current(): self
    {
        return self::query()->firstOrNew([], [
            'play_on_all' => true,
            'pages' => [],
        ]);
    }

    public static function pageForRoute(?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        return self::ROUTE_PAGE_MAP[$routeName] ?? null;
    }

    public function allowsPage(?string $page): bool
    {
        if ($this->play_on_all) {
            return true;
        }

        if ($page === null) {
            return false;
        }

        return in_array($page, $this->pages ?? [], true);
    }

    public function toSettingsArray(): array
    {
        return [
            'play_on_all' => $this->play_on_all,
            'pages' => $this->pages ?? [],
            'page_options' => collect(self::PAGES)
                ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
                ->values()
                ->all(),
        ];
    }
}
