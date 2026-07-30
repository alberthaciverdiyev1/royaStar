(function() {
    // Fade-up sections
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    // Counter animation
    const counterObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target);
                let cur = 0;
                const step = Math.ceil(target / 35);
                const t = setInterval(() => {
                    cur += step;
                    if (cur >= target) { el.textContent = target; clearInterval(t); }
                    else { el.textContent = cur; }
                }, 35);
                counterObs.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('.counter').forEach(el => counterObs.observe(el));
})();
