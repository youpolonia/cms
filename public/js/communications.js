/**
 * AJAX Utilities for Communications Module
 */

class Communications {
    static getCSRFToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        const inputToken = document.querySelector('input[name="_token"]');
        return (metaToken ? metaToken.content : '') ||
               (inputToken ? inputToken.value : '');
    }

    static setupAjaxDefaults() {
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': this.getCSRFToken(),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }

    static handleAjaxError(xhr, status, error) {
        console.error('AJAX Error:', status, error, xhr.responseText);
        
        if (xhr.status === 419) { // CSRF token mismatch
            alert('Session expired. Please refresh the page.');
            window.location.reload();
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            alert(xhr.responseJSON.message);
        } else {
            alert('An error occurred. Please try again.');
        }
    }

    static escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&")
            .replace(/</g, "<")
            .replace(/>/g, ">")
            .replace(/"/g, """)
            .replace(/'/g, "&#039;");
    }

    static formatDate(dateString) {
        try {
            if (!dateString) throw new Error('Invalid date string');
            const date = new Date(dateString);
            if (isNaN(date.getTime())) throw new Error('Invalid date');
            return date.toLocaleDateString('en-US', {
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
        } catch (error) {
            console.error('Date formatting error:', error);
            return 'Invalid date';
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    Communications.setupAjaxDefaults();
});