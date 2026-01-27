class EventMonitor {
    constructor() {
        this.lastEventId = 0;
        this.isLive = false;
        this.pollInterval = 2000;
        this.pollTimeout = null;

        this.initElements();
        this.bindEvents();
    }

    initElements() {
        this.toggleBtn = document.getElementById('toggle-live');
        this.eventsContainer = document.getElementById('events-container');
    }

    bindEvents() {
        this.toggleBtn.addEventListener('click', () => this.toggleLive());
    }

    toggleLive() {
        this.isLive = !this.isLive;
        
        if (this.isLive) {
            this.toggleBtn.classList.remove('btn-primary');
            this.toggleBtn.classList.add('btn-danger');
            this.toggleBtn.textContent = 'Stop Live';
            this.startPolling();
        } else {
            this.toggleBtn.classList.remove('btn-danger');
            this.toggleBtn.classList.add('btn-primary');
            this.toggleBtn.textContent = 'Start Live';
            this.stopPolling();
        }
    }

    startPolling() {
        this.fetchEvents();
        this.pollTimeout = setTimeout(() => this.startPolling(), this.pollInterval);
    }

    stopPolling() {
        if (this.pollTimeout) {
            clearTimeout(this.pollTimeout);
            this.pollTimeout = null;
        }
    }

    async fetchEvents() {
        try {
            const response = await fetch(`/admin/?action=dev-tools&live=1&lastId=${this.lastEventId}`);
            const events = await response.json();

            if (events.length > 0) {
                this.lastEventId = events[events.length - 1].id;
                this.renderEvents(events);
            }
        } catch (error) {
            console.error('Error fetching events:', error);
        }
    }

    renderEvents(events) {
        events.forEach(event => {
            const eventEl = document.createElement('div');
            eventEl.className = `event-item ${event.success ? 'success' : 'error'}`;
            eventEl.innerHTML = `
                <strong>${escapeHtml(event.event)}</strong>
                <span>${new Date(event.timestamp * 1000).toLocaleString()}</span>
                <div class="handler">Handler: ${escapeHtml(event.handler)}</div>
                ${!event.success ? `<div class="error-msg">Error: ${escapeHtml(event.error)}</div>` : ''}
                <pre>${escapeHtml(JSON.stringify(event.payload, null, 2))}</pre>
            `;
            this.eventsContainer.prepend(eventEl);
        });
    }
}

function escapeHtml(unsafe) {
    return unsafe?.toString()?.replace(/&/g, '&')
        .replace(/</g, '<')
        .replace(/>/g, '>')
        .replace(/"/g, '"')
        .replace(/'/g, '&#039;') || '';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new EventMonitor();
});