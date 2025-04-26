class NotificationSounds {
    constructor() {
        this.sounds = {
            'upcoming': new Audio('/sounds/notification-upcoming.mp3'),
            'conflict': new Audio('/sounds/notification-conflict.mp3'),
            'completed': new Audio('/sounds/notification-completed.mp3'),
            'changed': new Audio('/sounds/notification-changed.mp3')
        };
        this.enabled = true;
    }

    play(type) {
        if (!this.enabled) return;
        
        try {
            // Stop any currently playing sound
            Object.values(this.sounds).forEach(sound => {
                sound.pause();
                sound.currentTime = 0;
            });

            // Play the requested sound
            if (this.sounds[type]) {
                this.sounds[type].play().catch(e => console.error('Sound playback failed:', e));
            }
        } catch (e) {
            console.error('Notification sound error:', e);
        }
    }

    toggle(enabled) {
        this.enabled = enabled;
        localStorage.setItem('notificationSoundsEnabled', enabled);
    }

    init() {
        // Load preference from storage
        const storedPref = localStorage.getItem('notificationSoundsEnabled');
        this.enabled = storedPref !== null ? JSON.parse(storedPref) : true;
    }
}

// Export singleton instance
export const notificationSounds = new NotificationSounds();
notificationSounds.init();