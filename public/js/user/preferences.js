document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('preferences-form');
    const previewSection = document.getElementById('preferences-preview');
    const saveButton = document.getElementById('save-preferences');
    const statusMessage = document.getElementById('status-message');

    // Handle preference changes for live preview
    form.addEventListener('change', function(e) {
        if (e.target.classList.contains('preference-input')) {
            updatePreview(e.target.name, e.target.value);
        }
    });

    // Save all preferences
    saveButton.addEventListener('click', function() {
        const inputs = form.querySelectorAll('.preference-input');
        const preferences = {};

        inputs.forEach(input => {
            preferences[input.name] = input.value;
        });

        savePreferences(preferences);
    });

    function updatePreview(key, value) {
        const previewElement = previewSection.querySelector(`[data-pref="${key}"]`);
        if (previewElement) {
            previewElement.textContent = value;
        }
    }

    function savePreferences(preferences) {
        statusMessage.textContent = 'Saving...';
        statusMessage.className = 'status-saving';

        // Save each preference individually
        const promises = Object.entries(preferences).map(([key, value]) => {
            return fetch('/user/preferences/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ key, value })
            })
            .then(response => response.json());
        });

        Promise.all(promises)
            .then(results => {
                const allSuccess = results.every(r => r.success);
                if (allSuccess) {
                    statusMessage.textContent = 'Preferences saved successfully!';
                    statusMessage.className = 'status-success';
                } else {
                    statusMessage.textContent = 'Some preferences failed to save';
                    statusMessage.className = 'status-error';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                statusMessage.textContent = 'Error saving preferences';
                statusMessage.className = 'status-error';
            });
    }
});