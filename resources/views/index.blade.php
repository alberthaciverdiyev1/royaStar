@extends('layouts.app', ['hideHeader' => true, 'hideNavbar' => true, 'isIndex' => true])
@section('title', 'Welcome')

@section('content')
<section class="hero-section">
    <div class="hero-gradient-overlay"></div>

    <div class="relative w-full max-w-4xl mx-auto z-10">
        <div class="logo-container">
            <div class="logo-star-icon">
                <span class="material-symbols-outlined !text-6xl md:!text-8xl">star</span>
            </div>
            <div class="logo-rocket-icon hidden sm:block">
                <span class="material-symbols-outlined !text-5xl md:!text-7xl">rocket_launch</span>
            </div>
            <div class="logo-frame group">
                <div class="logo-image-wrapper">
                    <img src="https://placehold.co/200x200/4885c1/white?text=Roya's+Stars" alt="Teacher Roya's Stars Logo" class="group-hover:scale-110" />
                </div>
            </div>
        </div>

        <div class="text-center space-y-6">
            <h2 class="hero-title">
                Ready to start your <br class="hidden md:block" />
                <span class="text-[rgb(var(--secondary))]">English</span> adventure?
            </h2>
            <p class="hero-subtitle">
                Unlock the galaxy of knowledge with Teacher Roya.
            </p>

            <div class="btn-container">
                <a href="{{ route('signup') }}" class="btn-primary">Sign Up</a>
                <a href="{{ route('login') }}" class="btn-secondary">Log In</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="material-symbols-outlined !text-3xl md:!text-5xl text-[rgb(var(--tertiary))]">auto_awesome</span>
                <span class="stat-card-text">500+ Stars Earned</span>
            </div>
            <div class="stat-card">
                <span class="material-symbols-outlined !text-3xl md:!text-5xl text-[rgb(var(--secondary))]">groups</span>
                <span class="stat-card-text">Active Learners</span>
            </div>
        </div>
    </div>
</section>
@endsection
