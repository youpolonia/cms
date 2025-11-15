class AnalyticsWebSocket {
  constructor() {
    this.socket = null;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.reconnectDelay = 3000;
    this.subscribedChannels = new Set();
    this.eventHandlers = {};
  }

  connect() {
    const protocol = window.location.protocol === 'https:' ? 'wss://' : 'ws://';
    const host = window.location.hostname;
    const port = 8080; // Should match server config
    
    this.socket = new WebSocket(`${protocol}${host}:${port}`);

    this.socket.onopen = () => {
      this.reconnectAttempts = 0;
      this.resubscribeChannels();
      this.dispatchEvent('connect');
    };

    this.socket.onmessage = (event) => {
      const data = JSON.parse(event.data);
      this.dispatchEvent(data.type || 'message', data);
    };

    this.socket.onclose = () => {
      this.dispatchEvent('disconnect');
      this.attemptReconnect();
    };

    this.socket.onerror = (error) => {
      this.dispatchEvent('error', error);
    };
  }

  subscribe(channel) {
    if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
      this.subscribedChannels.add(channel);
      return false;
    }

    this.socket.send(JSON.stringify({
      action: 'subscribe',
      channel: channel
    }));
    this.subscribedChannels.add(channel);
    return true;
  }

  unsubscribe(channel) {
    if (!this.socket || this.socket.readyState !== WebSocket.OPEN) {
      this.subscribedChannels.delete(channel);
      return false;
    }

    this.socket.send(JSON.stringify({
      action: 'unsubscribe',
      channel: channel
    }));
    this.subscribedChannels.delete(channel);
    return true;
  }

  resubscribeChannels() {
    this.subscribedChannels.forEach(channel => {
      this.subscribe(channel);
    });
  }

  attemptReconnect() {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      setTimeout(() => this.connect(), this.reconnectDelay);
    }
  }

  on(event, handler) {
    if (!this.eventHandlers[event]) {
      this.eventHandlers[event] = [];
    }
    this.eventHandlers[event].push(handler);
  }

  dispatchEvent(event, data) {
    if (this.eventHandlers[event]) {
      this.eventHandlers[event].forEach(handler => handler(data));
    }
  }

  close() {
    if (this.socket) {
      this.socket.close();
    }
  }
}

// Singleton instance
const analyticsWebSocket = new AnalyticsWebSocket();
export default analyticsWebSocket;