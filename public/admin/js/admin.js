/**
 * Admin JavaScript
 * Core functionality for admin interface
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions with CSRF tokens
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.querySelector('[name="csrf_token"]')) {
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = 'csrf_token';
                token.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                this.appendChild(token);
            }
        });
    });

    // Confirm destructive actions
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Flash message auto-dismiss
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});