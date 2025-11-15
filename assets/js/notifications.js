class NotificationPoller {
    constructor() {
        this.pollInterval = 30000; // 30 seconds
        this.lastCheck = null;
        this.badgeElement = document.getElementById('notification-badge');
        this.statusElement = document.getElementById('polling-status');
        this.init();
    }

    init() {
        this.checkNotifications();
        setInterval(() => this.checkNotifications(), this.pollInterval);
        this.updatePollingStatus(true);
        
        // Mark notifications as read when clicked
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-item')) {
                this.markAsRead(e.target.closest('.notification-item').dataset.id);
            }
        });
    }

    async checkNotifications() {
        try {
            this.updatePollingStatus(true);
            const response = await fetch('/api/notifications?unread_only=true');
            const data = await response.json();
            
            if (data.length > 0) {
                this.updateBadge(data.length);
                
                // Only show desktop notifications for new alerts since last check
                if (this.lastCheck) {
                    const newNotifications = data.filter(n => new Date(n.created_at) > this.lastCheck);
                    newNotifications.forEach(n => this.showDesktopNotification(n));
                }
            } else {
                this.updateBadge(0);
            }
            
            this.lastCheck = new Date();
        } catch (error) {
            console.error('Error checking notifications:', error);
            this.updatePollingStatus(false, 'Error polling notifications');
        } finally {
            this.updatePollingStatus(false);
        }
    }
    
    updatePollingStatus(isActive, error = null) {
        if (!this.statusElement) return;
        
        this.statusElement.classList.toggle('polling-active', isActive);
        this.statusElement.title = isActive
            ? 'Checking for new notifications...'
            : (error || 'Last checked: ' + new Date().toLocaleTimeString());
    }

    updateBadge(count) {
        if (this.badgeElement) {
            this.badgeElement.textContent = count;
            this.badgeElement.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    showDesktopNotification(notification) {
        if (Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.message,
                icon: '/assets/images/notification-icon.png'
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showDesktopNotification(notification);
                }
            });
        }
    }

    async markAsRead(id) {
        try {
            await fetch(`/api/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            this.checkNotifications(); // Refresh count
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notification-badge')) {
        new NotificationPoller();
    }
});