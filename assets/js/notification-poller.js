class NotificationPoller {
    constructor(options) {
        this.options = {
            endpoint: '/api/notifications/poll',
            interval: 2000,
            maxInterval: 30000,
            backoffFactor: 1.5,
            ...options
        };
        this.currentInterval = this.options.interval;
        this.lastId = 0;
        this.isPolling = false;
    }

    start() {
        if (this.isPolling) return;
        this.isPolling = true;
        this.poll();
    }

    stop() {
        this.isPolling = false;
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }
    }

    async poll() {
        if (!this.isPolling) return;

        try {
            const response = await fetch(`${this.options.endpoint}?last_id=${this.lastId}`);
            const data = await response.json();
            
            if (data.notifications.length > 0) {
                this.lastId = data.last_id;
                this.options.onNotifications?.(data.notifications);
                this.currentInterval = this.options.interval; // Reset on new notifications
            } else {
                // Increase interval when no new notifications
                this.currentInterval = Math.min(
                    this.currentInterval * this.options.backoffFactor,
                    this.options.maxInterval
                );
            }
        } catch (error) {
            console.error('Notification poll error:', error);
            // Backoff on errors
            this.currentInterval = Math.min(
                this.currentInterval * this.options.backoffFactor,
                this.options.maxInterval
            );
        }

        this.timeoutId = setTimeout(() => this.poll(), this.currentInterval);
    }
}

// Example usage:
// const poller = new NotificationPoller({
//     onNotifications: (notifs) => {
//         // Update UI with new notifications
//     }
// });
// poller.start();