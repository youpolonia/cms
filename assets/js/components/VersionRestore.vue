<template>
  <div class="version-restore-modal" v-if="showModal">
    <div class="modal-overlay" @click="closeModal"></div>
    
    <div class="modal-content">
      <div class="modal-header">
        <h3>Confirm Version Restoration</h3>
        <button class="close-btn" @click="closeModal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="version-meta">
          <p><strong>Version:</strong> {{ versionData.version_number }}</p>
          <p><strong>Created:</strong> {{ formatDate(versionData.created_at) }}</p>
          <p><strong>Author:</strong> {{ versionData.author_name }}</p>
        </div>

        <div class="diff-preview" v-if="diffContent">
          <h4>Changes:</h4>
          <pre>{{ diffContent }}</pre>
        </div>
        <div v-else class="loading">Loading diff...</div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-cancel" @click="closeModal">Cancel</button>
        <button 
          class="btn btn-confirm" 
          @click="confirmRestore"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Restoring...' : 'Confirm Restore' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VersionRestore',
  props: {
    versionId: {
      type: [String, Number],
      required: true
    },
    showModal: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      versionData: {},
      diffContent: '',
      isLoading: false,
      error: null
    }
  },
  watch: {
    showModal(newVal) {
      if (newVal) {
        this.fetchVersionData()
        this.fetchDiffPreview()
      }
    }
  },
  methods: {
    async fetchVersionData() {
      try {
        const response = await fetch(`/api/content-versions/${this.versionId}`)
        if (!response.ok) throw new Error('Failed to fetch version data')
        this.versionData = await response.json()
      } catch (err) {
        this.error = err.message
        console.error('Error fetching version data:', err)
      }
    },
    async fetchDiffPreview() {
      try {
        const response = await fetch(`/api/content-versions/${this.versionId}/diff`)
        if (!response.ok) throw new Error('Failed to fetch diff preview')
        this.diffContent = await response.text()
      } catch (err) {
        this.error = err.message
        console.error('Error fetching diff preview:', err)
      }
    },
    async confirmRestore() {
      this.isLoading = true
      try {
        const response = await fetch(`/api/content-versions/${this.versionId}/restore`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })

        if (!response.ok) throw new Error('Restoration failed')
        
        this.$emit('restored')
        this.closeModal()
      } catch (err) {
        this.error = err.message
        console.error('Error restoring version:', err)
      } finally {
        this.isLoading = false
      }
    },
    closeModal() {
      this.$emit('close')
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleString()
    }
  }
}
</script>

<style scoped>
.version-restore-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 8px;
  width: 80%;
  max-width: 800px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
  padding: 16px 24px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 24px;
}

.version-meta {
  margin-bottom: 20px;
}

.version-meta p {
  margin: 5px 0;
}

.diff-preview {
  background: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 12px;
  max-height: 300px;
  overflow-y: auto;
}

.loading {
  text-align: center;
  padding: 20px;
  color: #666;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn {
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.btn-cancel {
  background: #f1f1f1;
  border: 1px solid #ddd;
}

.btn-confirm {
  background: #4CAF50;
  color: white;
  border: none;
}

.btn-confirm:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>