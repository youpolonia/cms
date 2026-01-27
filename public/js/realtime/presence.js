class PresenceManager {
    constructor(documentId, userId) {
        this.documentId = documentId;
        this.userId = userId;
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000;
        this.activeUsers = new Map();
    }

    connect() {
        const protocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
        const host = window.location.host;
        this.socket = new WebSocket(`${protocol}${host}/ws/presence`);

        this.socket.onopen = () => {
            this.reconnectAttempts = 0;
            this.sendPresenceUpdate('connect');
            this.startHeartbeat();
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.type === 'presence') {
                this.updateActiveUsers(data.users);
            }
        };

        this.socket.onclose = () => {
            this.stopHeartbeat();
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                setTimeout(() => {
                    this.reconnectAttempts++;
                    this.connect();
                }, this.reconnectDelay);
            }
        };
    }

    sendPresenceUpdate(action) {
        if (this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                type: 'presence',
                action: action,
                userId: this.userId,
                documentId: this.documentId
            }));
        }
    }

    updateActiveUsers(users) {
        this.activeUsers = new Map(Object.entries(users));
        this.renderActiveUsers();
    }

    renderActiveUsers() {
        // Implementation depends on your UI framework
        console.log('Active users:', this.activeUsers);
    }

    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            this.sendPresenceUpdate('activity');
        }, 30000);
    }

    stopHeartbeat() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }
    }

    disconnect() {
        this.stopHeartbeat();
        this.sendPresenceUpdate('disconnect');
        this.socket.close();
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PresenceManager;
}