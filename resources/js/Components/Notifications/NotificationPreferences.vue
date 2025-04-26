<template>
    <div class="notification-preferences">
        <h2>Notification Preferences</h2>
        
        <div v-for="(pref, type) in preferences" :key="type" class="preference-item">
            <h3>{{ formatNotificationType(type) }}</h3>
            
            <div class="preference-options">
                <label>
                    <input type="checkbox" v-model="pref.in_app_enabled">
                    In-App Notifications
                </label>
                
                <label>
                    <input type="checkbox" v-model="pref.push_enabled">
                    Push Notifications  
                </label>
                
                <label>
                    <input type="checkbox" v-model="pref.email_enabled">
                    Email Notifications
                </label>
            </div>
        </div>
        
        <button @click="savePreferences">Save Preferences</button>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const preferences = ref({});

const formatNotificationType = (type) => {
    return type.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
};

const fetchPreferences = async () => {
    try {
        const response = await axios.get('/api/notification-preferences');
        preferences.value = response.data.preferences;
    } catch (error) {
        console.error('Error fetching preferences:', error);
    }
};

const savePreferences = async () => {
    try {
        await axios.put('/api/notification-preferences/bulk', {
            preferences: Object.entries(preferences.value).map(([type, pref]) => ({
                notification_type: type,
                ...pref
            }))
        });
        alert('Preferences saved successfully!');
    } catch (error) {
        console.error('Error saving preferences:', error);
        alert('Failed to save preferences');
    }
};

onMounted(fetchPreferences);
</script>

<style scoped>
.notification-preferences {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.preference-item {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.preference-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
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