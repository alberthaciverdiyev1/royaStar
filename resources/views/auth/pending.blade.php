@extends('layouts.app', ['hideHeader' => true, 'hideNavbar' => true, 'isIndex' => true])
@section('title', text('pending.page_title'))

@section('content')
<section class="login-section">
    <div class="mb-8 md:mb-12 text-center">
        <div class="flex items-center justify-center mb-6">
            <div class="p-4 bg-[rgb(var(--tertiary))] rounded-3xl shadow-2xl shadow-[rgb(var(--primary))/0.2]">
                <span class="material-symbols-outlined text-white text-4xl">hourglass_top</span>
            </div>
        </div>
        <h1 class="text-2xl md:text-4xl font-black text-[rgb(var(--on-surface))] tracking-tighter uppercase italic">
            {{ text('pending.title_1') }} <span class="text-[rgb(var(--tertiary))]">{{ text('pending.title_2') }}</span>
        </h1>
        <p class="text-[rgb(var(--on-surface))/0.5] font-medium mt-2 text-xs md:text-sm italic">{{ text('pending.subtitle') }}</p>
    </div>

    <div class="login-card text-center">
        <div class="mb-8">
            <span class="material-symbols-outlined !text-6xl text-[rgb(var(--tertiary))] mb-4">pending_actions</span>
            <h2 class="text-xl md:text-2xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tight italic mb-4">
                {{ text('pending.almost') }}
            </h2>
            <p class="text-[rgb(var(--on-surface))/0.6] text-sm leading-relaxed max-w-sm mx-auto">
                {{ text('pending.desc_1') }}
            </p>
            <p class="text-[rgb(var(--on-surface))/0.6) text-sm leading-relaxed max-w-sm mx-auto mt-4">
                {{ text('pending.desc_2') }}
            </p>
        </div>

        @if(session('success'))
        <div class="bg-[rgb(var(--success))/0.1] text-[rgb(var(--success))] text-2xs font-black uppercase tracking-widest px-5 py-4 rounded-2xl border border-[rgb(var(--success))/0.2] text-center mb-6">
            {{ session('success') }}
        </div>
        @endif

        <a href="{{ route('login') }}" class="btn-login no-underline">
            <span class="material-symbols-outlined !text-xl">arrow_back</span>
            {{ text('pending.back') }}
        </a>

        <div class="mt-8 pt-6 border-t border-[rgb(var(--surface-container-high))]">
            <p class="text-xs text-[rgb(var(--on-surface))/0.4) font-medium">
                {{ text('pending.help') }} <a href="#" class="text-[rgb(var(--secondary))] font-black hover:underline">{{ text('pending.contact') }}</a>
            </p>
        </div>
    </div>
</section>
@endsection
