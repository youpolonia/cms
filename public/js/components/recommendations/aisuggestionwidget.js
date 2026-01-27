class AISuggestionWidget {
    constructor(options = {}) {
        this.container = options.container || document.body;
        this.contentElement = options.contentElement;
        this.apiEndpoint = options.apiEndpoint || '/api/recommendations/suggest';
        this.maxSuggestions = options.maxSuggestions || 3;
        this.init();
    }

    init() {
        this.createUI();
        if (this.contentElement) {
            this.setupContentObserver();
        }
    }

    createUI() {
        this.widgetElement = document.createElement('div');
        this.widgetElement.className = 'ai-suggestion-widget';
        
        this.header = document.createElement('h3');
        this.header.textContent = 'AI Suggestions';
        
        this.suggestionsList = document.createElement('ul');
        this.suggestionsList.className = 'suggestions-list';
        
        this.widgetElement.append(this.header, this.suggestionsList);
        this.container.appendChild(this.widgetElement);
    }

    setupContentObserver() {
        const observer = new MutationObserver(() => {
            this.fetchSuggestions();
        });
        
        observer.observe(this.contentElement, {
            characterData: true,
            childList: true,
            subtree: true
        });
    }

    async fetchSuggestions() {
        const content = this.contentElement.textContent.trim();
        if (!content) return;

        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ content })
            });

            if (response.ok) {
                const data = await response.json();
                this.displaySuggestions(data.suggestions);
            }
        } catch (error) {
            console.error('AI Suggestion Error:', error);
        }
    }

    displaySuggestions(suggestions = []) {
        this.suggestionsList.innerHTML = '';
        
        suggestions.slice(0, this.maxSuggestions).forEach(suggestion => {
            const li = document.createElement('li');
            li.className = 'suggestion-item';
            li.innerHTML = `
                <p class="suggestion-text">${suggestion.text}</p>
                <button class="apply-btn" data-suggestion="${encodeURIComponent(suggestion.text)}">
                    Apply
                </button>
            `;
            li.querySelector('.apply-btn').addEventListener('click', (e) => {
                this.applySuggestion(e.target.dataset.suggestion);
            });
            this.suggestionsList.appendChild(li);
        });
    }

    applySuggestion(suggestion) {
        if (this.contentElement) {
            this.contentElement.textContent += '\n' + decodeURIComponent(suggestion);
        }
    }
}

// Export for module systems if available
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AISuggestionWidget;
}