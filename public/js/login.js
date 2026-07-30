document.querySelectorAll('.toggle-password-login').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        const icon = btn.querySelector('.material-symbols-outlined');
        if (target.type === 'password') {
            target.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            target.type = 'password';
            icon.textContent = 'visibility';
        }
    });
});
