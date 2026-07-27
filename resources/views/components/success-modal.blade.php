<div id="success-modal" class="fixed inset-0 z-[200] flex items-center justify-center px-6 opacity-0 pointer-events-none transition-all duration-500">
    <div class="absolute inset-0 bg-[rgb(var(--primary))/0.2] backdrop-blur-md"></div>

    <div class="bg-[rgb(var(--surface-container-lowest))] rounded-4xl p-10 md:p-16 w-full max-w-lg relative z-10 shadow-2xl border border-[rgb(var(--surface-container-high))] text-center transform scale-90 transition-transform duration-500" id="modal-card">

        <div class="relative w-32 h-32 mx-auto mb-8">
            <div class="absolute inset-0 bg-[rgb(var(--tertiary))/0.2] rounded-full blur-2xl animate-pulse"></div>
            <div id="rocket-container" class="relative z-10 flex items-center justify-center h-full">
                <span class="material-symbols-outlined !text-7xl text-[rgb(var(--secondary))] animate-bounce">rocket_launch</span>
            </div>
        </div>

        <h2 class="text-3xl font-black text-[rgb(var(--on-surface))] uppercase tracking-tighter mb-4">Feedback Received!</h2>
        <p class="text-[rgb(var(--on-surface))/0.5] font-medium mb-10 leading-relaxed text-sm md:text-base">
            Your review has been successfully sent to Teacher Roya. Thank you for helping us improve our galaxy!
        </p>

        <button id="close-modal" class="w-full py-5 bg-[rgb(var(--primary))] rounded-full font-black uppercase text-xs tracking-widest shadow-xl shadow-[rgb(var(--primary))/0.2] active:scale-95 transition-all text-white">
            Continue Journey
        </button>
    </div>
</div>

@push('styles')
<style>
    #success-modal.show { opacity: 1; pointer-events: auto; }
    #success-modal.show #modal-card { transform: scale(1); }
    @@keyframes rocket-fly {
        0% { transform: translateY(0) scale(1); opacity: 1; }
        100% { transform: translateY(-200px) scale(0.5); opacity: 0; }
    }
    .fly-away { animation: rocket-fly 0.8s ease-in forwards; }
</style>
@endpush
