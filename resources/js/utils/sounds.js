import axios from 'axios';

const soundCache = {};

export const playNotificationSound = async (type) => {
  try {
    // Get user's sound preferences
    const response = await axios.get('/api/notification-sounds');
    const sounds = response.data.sounds;
    const defaultSounds = response.data.defaults;
    
    // Determine which sound to play
    const soundConfig = sounds[type] || defaultSounds[type] || defaultSounds.system_notification;
    
    if (!soundConfig || !soundConfig.enabled) return;
    
    // Play the sound
    const audio = new Audio(`/storage/${soundConfig.sound_file}`);
    audio.volume = soundConfig.volume || 0.7;
    await audio.play();
    
  } catch (error) {
    console.error('Error playing notification sound:', error);
  }
};

export const preloadSounds = async () => {
  try {
    const response = await axios.get('/api/notification-sounds');
    const allSounds = { ...response.data.sounds, ...response.data.defaults };
    
    for (const [type, config] of Object.entries(allSounds)) {
      if (config.enabled && config.sound_file) {
        const audio = new Audio(`/storage/${config.sound_file}`);
        soundCache[type] = audio;
        await audio.load();
      }
    }
  } catch (error) {
    console.error('Error preloading sounds:', error);
  }
};