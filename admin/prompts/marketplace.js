document.addEventListener('DOMContentLoaded', function() {
    // Handle prompt import
    window.importPrompt = function(promptId) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                id: promptId,
                url: `${PromptMarketplace.MARKETPLACE_URL}/${promptId}`
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Prompt imported successfully');
            } else {
                showToast(data.error || 'Import failed', 'error');
            }
        })
        .catch(error => {
            showToast('Network error: ' + error.message, 'error');
        });
    };

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});