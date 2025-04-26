<template>
  <div class="sound-settings">
    <h2>Notification Sounds</h2>
    
    <div v-for="(sound, type) in sounds" :key="type" class="sound-item">
      <h3>{{ formatNotificationType(type) }}</h3>
      
      <div class="sound-controls">
        <label>
          <input type="checkbox" v-model="sound.enabled">
          Enable Sound
        </label>

        <div v-if="sound.enabled" class="sound-options">
          <div class="sound-preview">
            <audio ref="audioPlayers" :src="getSoundPath(sound.sound_file)" preload="auto"></audio>
            <button @click="playSound(type)">▶ Preview</button>
          </div>

          <div class="volume-control">
            <label>Volume: {{ Math.round(sound.volume * 100) }}%</label>
            <input type="range" v-model="sound.volume" min="0" max="1" step="0.01">
          </div>

          <div class="sound-upload">
            <input type="file" ref="fileInput" @change="uploadSound(type)" accept=".mp3,.wav">
            <button @click="triggerFileInput(type)">Upload Custom Sound</button>
          </div>
        </div>
      </div>
    </div>

    <button @click="saveSettings">Save Sound Settings</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const sounds = ref({});
const audioPlayers = ref({});
const fileInput = ref(null);

const formatNotificationType = (type) => {
  return type.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ');
};

const getSoundPath = (filename) => {
  return filename ? `/storage/${filename}` : '/sounds/default.mp3';
};

const playSound = (type) => {
  const player = audioPlayers.value[type];
  if (player) {
    player.volume = sounds.value[type].volume;
    player.currentTime = 0;
    player.play();
  }
};

const triggerFileInput = (type) => {
  fileInput.value[type].click();
};

const uploadSound = async (type) => {
  const file = fileInput.value[type].files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('notification_type', type);
  formData.append('sound', file);

  try {
    const response = await axios.post('/api/notification-sounds/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    sounds.value[type].sound_file = response.data.sound_file;
  } catch (error) {
    console.error('Error uploading sound:', error);
  }
};

const fetchSounds = async () => {
  try {
    const response = await axios.get('/api/notification-sounds');
    sounds.value = response.data.sounds;
  } catch (error) {
    console.error('Error fetching sounds:', error);
  }
};

const saveSettings = async () => {
  try {
    await axios.put('/api/notification-sounds', {
      sounds: Object.entries(sounds.value).map(([type, sound]) => ({
        notification_type: type,
        ...sound
      }))
    });
    alert('Sound settings saved successfully!');
  } catch (error) {
    console.error('Error saving sound settings:', error);
    alert('Failed to save sound settings');
  }
};

onMounted(fetchSounds);
</script>

<style scoped>
.sound-settings {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.sound-item {
  margin-bottom: 20px;
  padding: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.sound-controls {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.sound-preview button {
  padding: 5px 10px;
  margin-right: 10px;
}

.volume-control {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.sound-upload input[type="file"] {
  display: none;
}

button {
  margin-top: 20px;
  padding: 10px 20px;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background: #45a049;
}
</style>