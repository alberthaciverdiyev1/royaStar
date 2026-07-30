function openAvatarModal() {
    var modal = document.getElementById('avatarModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeAvatarModal() {
    var modal = document.getElementById('avatarModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function previewSelectedPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('modalAvatarPreview');
            var placeholder = document.getElementById('modalAvatarPlaceholder');
            var previewText = document.getElementById('modalAvatarPreviewText');

            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                preview.style.setProperty('display', 'block', 'important');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
                placeholder.style.setProperty('display', 'none', 'important');
            }
            if (previewText) {
                previewText.style.setProperty('display', 'none', 'important');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function selectEmojiAvatar(emoji) {
    var modalInput = document.getElementById('modalEmojiInput');
    var emojiForm = document.getElementById('emojiAvatarForm');
    if (modalInput && emojiForm) {
        modalInput.value = emoji;
        emojiForm.submit();
    }
}

(function() {
    var btns = document.querySelectorAll('.profile-theme');
    var current = document.documentElement.getAttribute('data-theme') || 'default';

    btns.forEach(function(btn) {
        btn.classList.remove('active');
        var theme = btn.getAttribute('data-theme');
        if (theme === current) btn.classList.add('active');

        btn.addEventListener('click', function() {
            btns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            document.documentElement.setAttribute('data-theme', theme);
            try { localStorage.setItem('theme', theme); } catch(e) {}
        });
    });
})();
