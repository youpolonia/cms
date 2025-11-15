// Alert resolution system
document.addEventListener('DOMContentLoaded', () => {
    // Handle alert resolution
    document.addEventListener('click', (e) => {
        const target = e.target.closest('.resolve-alert-btn, .resolve-form') || e.target;
        if (!target.matches('.resolve-alert-btn, .resolve-form')) return;

        const alertId = target.dataset.alertId || 
            target.querySelector('[name="alert_id"]')?.value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!alertId || !csrfToken) {
            showToast('Missing required data', 'error');
            return;
        }

        fetch('/admin/alerts/resolve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ alert_id: alertId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Alert resolved successfully');
                document.querySelector(`[data-alert-id="${alertId}"]`)?.remove();
            } else {
                showToast(data.error || 'Failed to resolve alert', 'error');
            }
        })
        .catch(error => {
            showToast('Network error occurred', 'error');
            console.error('Error:', error);
        });
    });

    // Toast notification helper
    function showToast(message, type = 'success') {
        if (typeof window.showAdminToast === 'function') {
            window.showAdminToast(message, type);
        } else {
            alert(`${type}: ${message}`);
        }
    }
});