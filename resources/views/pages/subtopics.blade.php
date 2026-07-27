@extends('layouts.app')
@section('title', 'Nouns - Subtopics')

@section('content')
<section class="subtopics-header">
    <h2 class="subtopics-title">Topic 01: Nouns</h2>
    <p class="subtopics-subtitle">
        Explore the different types of nouns in our grammar galaxy.
    </p>
</section>

<div class="subtopics-container">
    <x-card variant="red" badgeText="Lesson 01" title="Compound Nouns" description="Completed on Oct 12" progress="100" iconName="star" />

    <x-card variant="white" href="{{ route('lesson', ['id' => 1]) }}" badgeText="Lesson 02" title="Countable & Uncountable" description="Continue where you left off" progress="65" iconName="bolt" />

    <x-card variant="gray" badgeText="Lesson 03" title="Proper & Common Nouns" description="Unlock after Lesson 02" iconName="lock" />
</div>

<div class="motivation-wrapper">
    <div class="motivation-card group hover:shadow-xl">
        <div class="motivation-gradient"></div>
        <div class="motivation-icon-circle group-hover:scale-110">
            <span class="material-symbols-outlined !text-5xl text-[rgb(var(--tertiary))]">rocket_launch</span>
        </div>
        <h4 class="motivation-title">Keep reaching for the stars!</h4>
        <p class="motivation-subtitle">
            You're doing great,<br/>star student.
        </p>
    </div>
</div>
@endsection
