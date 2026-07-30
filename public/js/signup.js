// Password visibility toggle
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('.material-symbols-outlined');
        if (target.type === 'password') {
            target.type = 'text';
            icon.textContent = 'visibility';
        } else {
            target.type = 'password';
            icon.textContent = 'visibility_off';
        }
    });
});

// Client-side password match check
const form = document.getElementById('signupForm');
const password = document.getElementById('signup-password');
const confirm = document.getElementById('signup-confirm');
const matchError = document.getElementById('password-match-error');

if (form && password && confirm) {
    form.addEventListener('submit', (e) => {
        if (password.value !== confirm.value) {
            e.preventDefault();
            matchError.classList.remove('hidden');
            confirm.focus();
        } else {
            matchError.classList.add('hidden');
        }
    });
    confirm.addEventListener('input', () => {
        if (password.value === confirm.value) {
            matchError.classList.add('hidden');
        }
    });
}
