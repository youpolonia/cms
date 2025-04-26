document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;

    let autosaveTimer;
    const AUTOSAVE_INTERVAL = 30000; // 30 seconds
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const notification = document.getElementById('autosave-notification');
    const timeElement = document.getElementById('autosave-time');

    form.addEventListener('input', function() {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(performAutosave, AUTOSAVE_INTERVAL);
    });

    function performAutosave() {
        const formData = new FormData(form);
        const contentId = form.dataset.contentId;

        fetch(`/api/content/${contentId}/autosave`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: formData.get('title'),
                content_type_id: formData.get('content_type_id'),
                body: formData.get('body')
            })
        })
        .then(response => response.json())
        .then(data => {
            showNotification();
        })
        .catch(error => {
            console.error('Autosave error:', error);
        });
    }

    function showNotification() {
        const now = new Date();
        timeElement.textContent = now.toLocaleTimeString();
        notification.classList.remove('hidden');
        
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
});