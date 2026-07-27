@extends('layouts.app')
@section('title', 'Topics')

@section('content')
<section class="universe-banner group hover:shadow-[rgb(var(--primary))/0.4]">
    <div class="bg-decor-star">
        <span class="material-symbols-outlined !text-4xl text-[rgb(var(--tertiary))]">star</span>
    </div>
    <div class="bg-decor-rocket">
        <span class="material-symbols-outlined !text-6xl text-[rgb(var(--primary))]">rocket_launch</span>
    </div>

    <div class="relative z-10">
        <h2 class="universe-title">Your Learning<br/>Universe</h2>
        <p class="universe-text">
            Embark on your journey through the grammar galaxies. Every star earned brings you closer to mastery!
        </p>
    </div>

    <div class="banner-icon-magic group-hover:rotate-45 group-hover:scale-110">
        <span class="material-symbols-outlined !text-[140px] md:!text-[180px]">auto_awesome</span>
    </div>
    <div class="banner-icon-rocket group-hover:translate-x-4 group-hover:-translate-y-4 transition-transform duration-1000">
        <span class="material-symbols-outlined !text-[180px] md:!text-[220px] text-white">rocket_launch</span>
    </div>
</section>

<div class="max-w-6xl mx-auto">
    <div class="modules-grid">
        <x-card variant="red" href="{{ route('subtopics') }}" badgeText="Topic 01" title="Nouns & Objects" description="Mastering the building blocks of every sentence in the universe." progress="100" iconName="star" />

        <x-card variant="white" href="#" badgeText="Topic 02" title="Action Verbs" description="Giving life and motion to your stories through powerful verbs." progress="20" iconName="star" />

        <x-card variant="gray" badgeText="Topic 03" title="Sentence Building" description="Unlock this module by completing the previous journey." iconName="lock" />
    </div>
</div>

<div class="pagination-container">
    <div class="dot dot-active"></div>
    <div class="dot dot-inactive"></div>
    <div class="dot dot-inactive"></div>
</div>
@endsection
