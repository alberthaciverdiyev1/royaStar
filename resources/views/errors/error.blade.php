{{--
    Branded error page — rendered for HTTP error statuses (403, 404, 419, 429, 500, 503, ...).

    Expected variables (all optional):
        $status   int|null    HTTP status code. Defaults from $exception, else 500.
        $message  string|null Custom message override. Defaults to $exception->getMessage() when safe.
        $exception            The exception instance Laravel passes to error views.
--}}
@php
    $status = $status ?? ((($exception ?? null) instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) ? $exception->getStatusCode() : 500);
    $status = (int) $status;

    $config = [
        403 => ['icon' => 'lock',            'title' => 'Access Denied',          'desc' => 'You don\'t have permission to view this page. If you believe this is a mistake, please contact your teacher.', 'accent' => '--secondary', 'cta' => 'exam'],
        404 => ['icon' => 'explore_off',     'title' => 'Page Not Found',         'desc' => 'The page you are looking for doesn\'t exist or has been moved.', 'accent' => '--primary', 'cta' => 'home'],
        419 => ['icon' => 'hourglass_empty', 'title' => 'Session Expired',        'desc' => 'Your session has expired. Refresh the page and try again.', 'accent' => '--tertiary', 'cta' => 'home'],
        429 => ['icon' => 'speed',           'title' => 'Too Many Requests',      'desc' => 'You\'ve been moving too fast! Please wait a moment and try again.', 'accent' => '--secondary', 'cta' => 'home'],
        500 => ['icon' => 'error',           'title' => 'Something Went Wrong',   'desc' => 'An unexpected error occurred on our side. Please try again later.', 'accent' => '--error', 'cta' => 'home'],
        503 => ['icon' => 'construction',    'title' => 'Service Unavailable',    'desc' => 'We\'re doing some quick maintenance right now. Please check back soon.', 'accent' => '--primary', 'cta' => 'home'],
    ];
    $cfg = $config[$status] ?? ['icon' => 'error', 'title' => 'Something Went Wrong', 'desc' => 'An unexpected error occurred. Please try again.', 'accent' => '--error', 'cta' => 'home'];

    $exceptionMessage = (($exception ?? null) instanceof \Throwable) ? trim((string) $exception->getMessage()) : '';
    // Never leak raw exception details on 5xx responses outside of debug mode.
    $showMessage = ($status < 500 || config('app.debug')) && $exceptionMessage !== '';
    $message = $message ?? ($showMessage ? $exceptionMessage : null);

    $primaryLabel = $cfg['cta'] === 'exam' ? 'Back to Exams' : 'Go Home';
    $primaryHref  = $cfg['cta'] === 'exam' ? url('/exam') : url('/');
    $secondaryLabel = $cfg['cta'] === 'exam' ? 'Go Home' : 'Back to Exams';
    $secondaryHref  = $cfg['cta'] === 'exam' ? url('/') : url('/exam');
@endphp
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $cfg['title'] }} · Teacher Roya's Stars</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}" rel="stylesheet">

    <script>
        (function() {
            var theme = 'default';
            try { theme = localStorage.getItem('theme') || 'default'; } catch(e) {}
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            -webkit-tap-highlight-color: transparent;
        }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            background: radial-gradient(circle at top right, rgb(var(--surface-container-high)), rgb(var(--surface)));
            background-attachment: fixed;
        }
        .error-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: .25;
            pointer-events: none;
        }
        .error-orb--1 { width: 340px; height: 340px; top: -80px; right: -60px; background: rgb(var(--primary)); }
        .error-orb--2 { width: 260px; height: 260px; bottom: -60px; left: -40px; background: rgb(var(--secondary)); }
        .error-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: rgb(var(--surface-container-lowest));
            border: 2px solid rgb(var(--surface-container-high));
            border-radius: 2rem;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 24px 60px -20px rgba(0, 0, 0, .18);
        }
        .error-code {
            font-size: 5.5rem;
            line-height: 1;
            font-weight: 900;
            letter-spacing: -.04em;
            background: linear-gradient(135deg, rgb(var(--primary)), rgb(var(--secondary)));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .error-icon-circle {
            width: 4rem;
            height: 4rem;
            margin: 1rem auto 0;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgb(var(--accent) / .12);
            color: rgb(var(--accent));
        }
        .error-icon-circle .material-symbols-outlined { font-size: 2rem; }
        .error-title {
            margin-top: 1.25rem;
            font-size: 1.05rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgb(var(--on-surface));
        }
        .error-message {
            margin-top: 1rem;
            padding: .85rem 1rem;
            border-radius: 1rem;
            background: rgb(var(--surface));
            border: 1.5px solid rgb(var(--surface-container-high));
            font-weight: 700;
            font-size: .875rem;
            color: rgb(var(--on-surface));
        }
        .error-desc {
            margin-top: .9rem;
            font-size: .8125rem;
            font-weight: 600;
            line-height: 1.55;
            color: rgb(var(--on-surface-variant));
        }
        .error-actions {
            margin-top: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }
        .error-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            padding: .8rem 1.5rem;
            border-radius: 9999px;
            font-size: .72rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .08em;
            text-decoration: none;
            transition: all .2s ease;
        }
        .error-btn--primary {
            background: rgb(var(--primary));
            color: #fff;
            box-shadow: 0 8px 20px -8px rgb(var(--primary) / .5);
        }
        .error-btn--primary:hover { opacity: .92; transform: translateY(-1px); }
        .error-btn--ghost { background: rgb(var(--surface-container-high)); color: rgb(var(--on-surface)); }
        .error-btn--ghost:hover { opacity: .85; }
        @media (max-width: 480px) {
            .error-card { padding: 2rem 1.25rem; border-radius: 1.5rem; }
            .error-code { font-size: 4.5rem; }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-orb error-orb--1"></div>
        <div class="error-orb error-orb--2"></div>

        <div class="error-card">
            <div class="error-code">{{ $status }}</div>

            <div class="error-icon-circle" style="--accent: var({{ $cfg['accent'] }});">
                <span class="material-symbols-outlined">{{ $cfg['icon'] }}</span>
            </div>

            <h1 class="error-title">{{ $cfg['title'] }}</h1>

            @if($message)
            <div class="error-message">{{ $message }}</div>
            @endif

            <p class="error-desc">{{ $cfg['desc'] }}</p>

            <div class="error-actions">
                <a href="{{ $primaryHref }}" class="error-btn error-btn--primary">
                    <span class="material-symbols-outlined !text-sm">{{ $cfg['cta'] === 'exam' ? 'assignment' : 'home' }}</span>
                    {{ $primaryLabel }}
                </a>
                <a href="{{ $secondaryHref }}" class="error-btn error-btn--ghost">
                    {{ $secondaryLabel }}
                    <span class="material-symbols-outlined !text-sm">{{ $cfg['cta'] === 'exam' ? 'home' : 'assignment' }}</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
