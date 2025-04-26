<template>
  <div class="share-template">
    <div class="share-header">
      <h1>Share Template: {{ template.name }}</h1>
    </div>

    <div class="share-form">
      <div class="form-section">
        <h3>Share With</h3>
        
        <div class="share-options">
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="shareScope" 
                value="team"
              >
              Team Members
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="shareScope" 
                value="role"
              >
              By Role
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="radio" 
                v-model="shareScope" 
                value="specific"
              >
              Specific Users
            </label>
          </div>
        </div>

        <div class="target-selection" v-if="shareScope !== 'public'">
          <multi-select
            v-if="shareScope === 'team'"
            v-model="selectedTeams"
            :options="availableTeams"
            label="name"
            track-by="id"
            :multiple="true"
            placeholder="Select teams"
          />
          
          <multi-select
            v-if="shareScope === 'role'"
            v-model="selectedRoles"
            :options="availableRoles"
            label="name"
            track-by="id"
            :multiple="true"
            placeholder="Select roles"
          />
          
          <multi-select
            v-if="shareScope === 'specific'"
            v-model="selectedUsers"
            :options="availableUsers"
            label="name"
            track-by="id"
            :multiple="true"
            placeholder="Select users"
          />
        </div>
      </div>

      <div class="form-section">
        <h3>Permissions</h3>
        
        <div class="permission-options">
          <div class="option">
            <label>
              <input 
                type="checkbox" 
                v-model="permissions.view"
              >
              View
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="checkbox" 
                v-model="permissions.edit"
              >
              Edit
            </label>
          </div>
          
          <div class="option">
            <label>
              <input 
                type="checkbox" 
                v-model="permissions.copy"
              >
              Create Copies
            </label>
          </div>
        </div>
      </div>

      <div class="form-section">
        <h3>Sharing History</h3>
        
        <div class="history-list">
          <div 
            class="history-item" 
            v-for="share in sharingHistory" 
            :key="share.id"
          >
            <div class="share-info">
              <span class="recipient">{{ getRecipientName(share) }}</span>
              <span class="permissions">{{ formatPermissions(share) }}</span>
            </div>
            <div class="share-meta">
              <span class="date">{{ formatDate(share.created_at) }}</span>
              <span class="sharer">Shared by {{ share.sharer.name }}</span>
            </div>
            <button 
              class="btn-revoke"
              @click="revokeShare(share)"
            >
              Revoke
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <button class="btn-save" @click="saveSharing">
        Save Sharing Settings
      </button>
      <button class="btn-cancel" @click="$router.back()">
        Cancel
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import MultiSelect from '@/Components/MultiSelect.vue';

const props = defineProps({
  templateId: {
    type: [String, Number],
    required: true
  }
});

const template = ref({});
const shareScope = ref('team');
const selectedTeams = ref([]);
const selectedRoles = ref([]);
const selectedUsers = ref([]);
const availableTeams = ref([]);
const availableRoles = ref([]);
const availableUsers = ref([]);
const sharingHistory = ref([]);

const permissions = ref({
  view: true,
  edit: false,
  copy: false
});

const loadTemplate = async () => {
  try {
    const response = await axios.get(`/api/notification-templates/${props.templateId}`);
    template.value = response.data;
  } catch (error) {
    console.error('Error loading template:', error);
  }
};

const loadTeams = async () => {
  try {
    const response = await axios.get('/api/teams');
    availableTeams.value = response.data;
  } catch (error) {
    console.error('Error loading teams:', error);
  }
};

const loadRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    availableRoles.value = response.data;
  } catch (error) {
    console.error('Error loading roles:', error);
  }
};

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users');
    availableUsers.value = response.data;
  } catch (error) {
    console.error('Error loading users:', error);
  }
};

const loadSharingHistory = async () => {
  try {
    const response = await axios.get(`/api/notification-templates/${props.templateId}/sharing`);
    sharingHistory.value = response.data;
  } catch (error) {
    console.error('Error loading sharing history:', error);
  }
};

const saveSharing = async () => {
  try {
    const payload = {
      scope: shareScope.value,
      permissions: permissions.value
    };

    if (shareScope.value === 'team') {
      payload.teams = selectedTeams.value.map(t => t.id);
    } else if (shareScope.value === 'role') {
      payload.roles = selectedRoles.value.map(r => r.id);
    } else if (shareScope.value === 'specific') {
      payload.users = selectedUsers.value.map(u => u.id);
    }

    await axios.post(
      `/api/notification-templates/${props.templateId}/share`,
      payload
    );
    loadSharingHistory();
  } catch (error) {
    console.error('Error saving sharing settings:', error);
  }
};

const revokeShare = async (share) => {
  try {
    await axios.delete(
      `/api/notification-templates/${props.templateId}/sharing/${share.id}`
    );
    loadSharingHistory();
  } catch (error) {
    console.error('Error revoking share:', error);
  }
};

const getRecipientName = (share) => {
  if (share.team) return share.team.name;
  if (share.role) return share.role.name;
  if (share.user) return share.user.name;
  return 'Unknown';
};

const formatPermissions = (share) => {
  const perms = [];
  if (share.can_view) perms.push('View');
  if (share.can_edit) perms.push('Edit');
  if (share.can_copy) perms.push('Copy');
  return perms.join(', ');
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

onMounted(() => {
  loadTemplate();
  loadTeams();
  loadRoles();
  loadUsers();
  loadSharingHistory();
});
</script>

<style scoped>
.share-template {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.share-header {
  margin-bottom: 30px;
}

.share-form {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-section {
  margin-bottom: 30px;
}

.form-section h3 {
  margin-bottom: 15px;
  font-size: 18px;
}

.share-options {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.option label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.target-selection {
  margin-top: 15px;
}

.permission-options {
  display: flex;
  gap: 20px;
}

.history-list {
  border: 1px solid #eee;
  border-radius: 6px;
}

.history-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #eee;
}

.history-item:last-child {
  border-bottom: none;
}

.share-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.recipient {
  font-weight: 500;
  margin-bottom: 5px;
}

.permissions {
  font-size: 14px;
  color: #666;
}

.share-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin: 0 20px;
  font-size: 14px;
  color: #666;
}

.btn-revoke {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 14px;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
}

.btn-save {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}
</style>