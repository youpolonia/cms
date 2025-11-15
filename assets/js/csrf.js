/**
 * CSRF Token Handler
 * Manages CSRF tokens for forms and AJAX requests
 */
class CSRFTokenHandler {
    constructor() {
        this.token = null;
        this.tokenEndpoint = '/api/csrf-token';
        this.init();
    }

    async init() {
        await this.fetchToken();
        this.injectIntoForms();
        this.setupAjaxHeaders();
    }

    async fetchToken() {
        try {
            const response = await fetch(this.tokenEndpoint);
            if (!response.ok) throw new Error('Failed to fetch CSRF token');
            
            const data = await response.json();
            this.token = data.token;
            document.cookie = `XSRF-TOKEN=${this.token}; path=/`;
        } catch (error) {
            console.error('CSRF Token Error:', error);
        }
    }

    injectIntoForms() {
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                if (!form.querySelector('input[name="_csrf_token"]')) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '_csrf_token';
                    input.value = this.token;
                    form.appendChild(input);
                }
            });
        });
    }

    setupAjaxHeaders() {
        const originalFetch = window.fetch;
        window.fetch = async (url, options = {}) => {
            const headers = new Headers(options.headers || {});
            headers.set('X-CSRF-TOKEN', this.token);
            
            try {
                const response = await originalFetch(url, {
                    ...options,
                    headers
                });

                if (response.status === 403) {
                    await this.fetchToken();
                    return originalFetch(url, {
                        ...options,
                        headers: {
                            ...options.headers,
                            'X-CSRF-TOKEN': this.token
                        }
                    });
                }

                return response;
            } catch (error) {
                console.error('Fetch Error:', error);
                throw error;
            }
        };
    }
}

// Initialize CSRF handler
new CSRFTokenHandler();