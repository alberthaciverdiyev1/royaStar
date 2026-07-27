@extends('layouts.app')
@section('title', 'Welcome Star Student')

@section('content')
<section class="welcome-section">
    <div class="welcome-star-container group">
        <div class="welcome-star-glow group-hover:opacity-40"></div>
        <span class="material-symbols-outlined !text-[120px] text-[rgb(var(--tertiary))] relative z-10 drop-shadow-lg">
            stars
        </span>
    </div>

    <div class="space-y-4 px-4">
        <h1 class="welcome-title">
            You're a <span class="text-[rgb(var(--secondary))]">Star</span> Now!
        </h1>
        <p class="welcome-subtitle">
            Your registration is successfully completed. Start your first lesson now by choosing an option below!
        </p>
    </div>

    <div class="welcome-grid">
        <a href="{{ route('topics') }}" class="welcome-card group hover:border-[rgb(var(--secondary))]">
            <div class="card-icon-box icon-red group-hover:scale-110">
                <span class="material-symbols-outlined">auto_stories</span>
            </div>
            <div class="flex-grow">
                <h4 class="card-title">Start Learning</h4>
                <p class="card-subtitle">First Topic: Nouns</p>
            </div>
            <span class="material-symbols-outlined ml-auto text-[rgb(var(--surface-container-high))] group-hover:text-[rgb(var(--secondary))] group-hover:translate-x-1.5 transition-all !text-xl">arrow_forward</span>
        </a>

        <a href="{{ route('profile') }}" class="welcome-card group hover:border-[rgb(var(--primary))]">
            <div class="card-icon-box icon-blue group-hover:scale-110">
                <span class="material-symbols-outlined">person_pin</span>
            </div>
            <div class="flex-grow">
                <h4 class="card-title">Setup Profile</h4>
                <p class="card-subtitle">Set your avatar</p>
            </div>
            <span class="material-symbols-outlined ml-auto text-[rgb(var(--surface-container-high))] group-hover:text-[rgb(var(--primary))] group-hover:translate-x-1.5 transition-all !text-xl">arrow_forward</span>
        </a>
    </div>

    <p class="text-2xs font-black text-[rgb(var(--on-surface-variant))/0.4] uppercase tracking-widest pt-8 px-6">
        Navigate through the grammar galaxies using the navigation bar below
    </p>

    <div class="absolute -top-10 -left-10 w-40 h-40 bg-[rgb(var(--surface-container-lowest))/0.5] blur-3xl rounded-full -z-10"></div>
    <div class="absolute bottom-20 -right-10 w-64 h-64 bg-[rgb(var(--surface-container-high))/0.2] blur-3xl rounded-full -z-10"></div>
</section>
@endsection
