class WebSocketClient {
    constructor(url, options = {}) {
        this.url = url;
        this.options = {
            reconnectInterval: 1000,
            maxReconnectAttempts: 5,
            ...options
        };
        this.socket = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.eventListeners = {};
    }

    connect(token) {
        this.token = token;
        this.createWebSocket();
    }

    createWebSocket() {
        this.socket = new WebSocket(this.url);

        this.socket.onopen = (event) => {
            console.log('WebSocket connected');
            this.isConnected = true;
            this.reconnectAttempts = 0;
            this.sendAuthentication();
            this.triggerEvent('open', event);
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.triggerEvent(data.type, data);
        };

        this.socket.onclose = (event) => {
            console.log('WebSocket disconnected');
            this.isConnected = false;
            this.triggerEvent('close', event);
            this.reconnect();
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.triggerEvent('error', error);
        };
    }

    reconnect() {
        if (this.reconnectAttempts >= this.options.maxReconnectAttempts) {
            console.error('Max reconnection attempts reached');
            return;
        }

        this.reconnectAttempts++;
        console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.options.maxReconnectAttempts})...`);

        setTimeout(() => {
            this.createWebSocket();
        }, this.options.reconnectInterval);
    }

    sendAuthentication() {
        this.send('reconnect', { token: this.token });
    }

    send(type, data) {
        if (!this.isConnected) {
            console.error('WebSocket is not connected');
            return;
        }

        this.socket.send(JSON.stringify({ type, ...data }));
    }

    on(eventType, callback) {
        if (!this.eventListeners[eventType]) {
            this.eventListeners[eventType] = [];
        }
        this.eventListeners[eventType].push(callback);
    }

    triggerEvent(eventType, data) {
        const listeners = this.eventListeners[eventType] || [];
        listeners.forEach(callback => callback(data));
    }

    close() {
        if (this.socket) {
            this.socket.close();
        }
    }
}

// Usage example:
// const wsClient = new WebSocketClient('wss://your-domain.com/ws', { reconnectInterval: 2000, maxReconnectAttempts: 10 });
// wsClient.connect('user-auth-token');
// wsClient.on('userEvent', (data) => console.log('Received user event:', data));
// wsClient.send('userEvent', { action: 'update', userId: 123 });