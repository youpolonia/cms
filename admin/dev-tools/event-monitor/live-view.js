class EventMonitor {
    constructor() {
        this.eventSource = null;
        this.isLive = false;
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
        this.toggleBtn.textContent = this.isLive ? 'Stop Live' : 'Start Live';
        
        if (this.isLive) {
            this.startLiveUpdates();
        } else {
            this.stopLiveUpdates();
        }
    }

    startLiveUpdates() {
        this.eventSource = new EventSource('/api/dev-tools/event-stream');
        
        this.eventSource.addEventListener('message', (e) => {
            const event = JSON.parse(e.data);
            this.addEventToView(event);
        });

        this.eventSource.addEventListener('error', () => {
            this.stopLiveUpdates();
        });
    }

    stopLiveUpdates() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }

    addEventToView(event) {
        const eventEl = document.createElement('div');
        eventEl.className = 'event-item';
        eventEl.innerHTML = `
            <strong>${event.name}</strong>
            <span>${new Date(event.timestamp).toLocaleString()}</span>
            <pre>${JSON.stringify(event.data, null, 2)}</pre>
        `;
        this.eventsContainer.prepend(eventEl);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new EventMonitor();
});