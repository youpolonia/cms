<template>
  <div class="rollback-confirmation">
    <div v-if="step === 'compare'" class="compare-step">
      <h2 class="step-title">Version Comparison</h2>
      <div class="version-selectors">
        <VersionSelector
          label="Current Version"
          :versions="[{id: currentVersion.id, name: `v${currentVersion.version_number}`, createdAt: currentVersion.created_at}]"
          :model-value="currentVersion.id"
          disabled
        />
        <VersionSelector
          label="Target Version"
          :versions="availableVersions"
          :model-value="selectedVersionId"
          @update:modelValue="selectVersion"
        />
      </div>

      <div v-if="selectedVersion" class="comparison-container">
        <ComparisonVisualization
          :version-a="currentVersion"
          :version-b="selectedVersion"
          :unified-diff="diffData"
          :stats="comparisonStats"
        />

        <div class="reason-input">
          <label for="rollback-reason">Reason for rollback:</label>
          <textarea
            id="rollback-reason"
            v-model="reason"
            placeholder="Explain why you're rolling back to this version..."
            rows="3"
          ></textarea>
        </div>

        <div class="action-buttons">
          <button @click="cancel" class="btn btn-secondary">Cancel</button>
          <button @click="prepareRollback" class="btn btn-primary">
            Prepare Rollback
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="step === 'confirm'" class="confirm-step">
      <h2 class="step-title">Confirm Rollback</h2>
      <div class="confirmation-details">
        <h3>Rollback Summary</h3>
        <div class="detail-row">
          <span class="detail-label">From:</span>
          <span>v{{ currentVersion.version_number }} ({{ formatDate(currentVersion.created_at) }})</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">To:</span>
          <span>v{{ selectedVersion.version_number }} ({{ formatDate(selectedVersion.created_at) }})</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Changes:</span>
          <span>{{ comparisonStats.linesChanged }} lines, {{ comparisonStats.wordsChanged }} words</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Similarity:</span>
          <span>{{ comparisonStats.similarity }}%</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Reason:</span>
          <span>{{ reason }}</span>
        </div>
      </div>

      <div class="impact-analysis">
        <h3>Impact Analysis</h3>
        <div class="impact-items">
          <div v-if="impactAnalysis.linkedContent.length > 0" class="impact-item">
            <span class="impact-icon">🔗</span>
            <span>{{ impactAnalysis.linkedContent.length }} linked content items affected</span>
          </div>
          <div v-if="impactAnalysis.scheduledPublishes.length > 0" class="impact-item">
            <span class="impact-icon">⏰</span>
            <span>{{ impactAnalysis.scheduledPublishes.length }} scheduled publishes affected</span>
          </div>
          <div v-if="impactAnalysis.mediaReferences.length > 0" class="impact-item">
            <span class="impact-icon">🖼️</span>
            <span>{{ impactAnalysis.mediaReferences.length }} media references affected</span>
          </div>
        </div>
      </div>

      <div class="diff-preview">
        <h3>Key Changes</h3>
        <div class="diff-lines">
          <div
            v-for="(change, index) in significantChanges"
            :key="index"
            :class="['diff-line', `diff-line-${change.type}`]"
          >
            <span class="change-type">{{ change.type.toUpperCase() }}</span>
            <span class="change-content">{{ change.content }}</span>
          </div>
        </div>
      </div>

      <div class="notification-options">
        <h3>Notification Preferences</h3>
        <div class="notification-item">
          <input
            type="checkbox"
            id="notify-team"
            v-model="notifyTeam"
          />
          <label for="notify-team">Notify team members</label>
        </div>
        <div class="notification-item">
          <input
            type="checkbox"
            id="create-ticket"
            v-model="createTicket"
          />
          <label for="create-ticket">Create support ticket</label>
        </div>
      </div>

      <div class="action-buttons">
        <button @click="step = 'compare'" class="btn btn-secondary">Back</button>
        <button @click="showFinalConfirmation = true" class="btn btn-primary">
          Confirm Rollback
        </button>
      </div>

      <FinalConfirmationModal
        v-if="showFinalConfirmation"
        @confirm="confirmRollback"
        @cancel="showFinalConfirmation = false"
        :changes="comparisonStats"
        :impact="impactAnalysis"
      />
    </div>

    <div v-else-if="step === 'complete'" class="complete-step">
      <div class="success-message">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <h2>Rollback Completed Successfully</h2>
        <p>The content has been restored to version v{{ selectedVersion.version_number }}.</p>
        <p>A backup of the current version was created as v{{ newVersionNumber }}.</p>
      </div>

      <div class="action-buttons">
        <button @click="close" class="btn btn-primary">Done</button>
      </div>
    </div>
  </div>
</template>

<script>
import VersionSelector from './VersionSelector.vue'
import ComparisonVisualization from './ComparisonVisualization.vue'
import { format } from 'date-fns'

export default {
  components: {
    VersionSelector,
    ComparisonVisualization
  },
  props: {
    contentId: {
      type: Number,
      required: true
    },
    currentVersion: {
      type: Object,
      required: true
    },
    availableVersions: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      step: 'compare',
      selectedVersionId: null,
      reason: '',
      diffData: '',
      comparisonStats: {
        linesChanged: 0,
        wordsChanged: 0,
        similarity: 0
      },
      rollbackId: null,
      newVersionNumber: null,
      significantChanges: [],
      impactAnalysis: {
        linkedContent: [],
        scheduledPublishes: [],
        mediaReferences: []
      },
      notifyTeam: true,
      createTicket: false,
      showFinalConfirmation: false
    }
  },
  computed: {
    selectedVersion() {
      return this.availableVersions.find(v => v.id === this.selectedVersionId)
    }
  },
  methods: {
    formatDate(date) {
      return format(new Date(date), 'MMM d, yyyy HH:mm')
    },
    selectVersion(versionId) {
      this.selectedVersionId = versionId
      this.fetchComparison()
    },
    async fetchComparison() {
      if (!this.selectedVersionId) return

      try {
        const [comparisonResponse, impactResponse] = await Promise.all([
          this.$axios.get(`/api/contents/${this.contentId}/versions/${this.selectedVersionId}/compare`),
          this.$axios.get(`/api/contents/${this.contentId}/versions/${this.selectedVersionId}/impact`)
        ])
        
        this.diffData = comparisonResponse.data.differences
        this.comparisonStats = {
          linesChanged: Object.keys(comparisonResponse.data.differences).length,
          wordsChanged: this.countWordsChanged(comparisonResponse.data.differences),
          similarity: this.calculateSimilarity(
            comparisonResponse.data.current_content,
            comparisonResponse.data.version_content
          )
        }
        this.significantChanges = this.getSignificantChanges(comparisonResponse.data.differences)
        this.impactAnalysis = impactResponse.data
      } catch (error) {
        console.error('Failed to fetch comparison:', error)
        this.$toast.error('Failed to load version comparison')
      }
    },
    countWordsChanged(differences) {
      return Object.values(differences).reduce((total, diff) => {
        if (diff.action === 'change') {
          return total + 
            (diff.old?.split(' ').length || 0) + 
            (diff.new?.split(' ').length || 0)
        }
        return total + (diff.new?.split(' ').length || diff.old?.split(' ').length || 0)
      }, 0)
    },
    calculateSimilarity(current, target) {
      if (!current || !target) return 0
      const currentStr = JSON.stringify(current)
      const targetStr = JSON.stringify(target)
      const maxLength = Math.max(currentStr.length, targetStr.length)
      if (maxLength === 0) return 100
      return Math.round(
        (1 - this.levenshteinDistance(currentStr, targetStr) / maxLength * 100
      )
    },
    levenshteinDistance(a, b) {
      const matrix = []
      for (let i = 0; i <= b.length; i++) {
        matrix[i] = [i]
      }
      for (let j = 0; j <= a.length; j++) {
        matrix[0][j] = j
      }
      for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
          if (b.charAt(i - 1) === a.charAt(j - 1)) {
            matrix[i][j] = matrix[i - 1][j - 1]
          } else {
            matrix[i][j] = Math.min(
              matrix[i - 1][j - 1] + 1,
              matrix[i][j - 1] + 1,
              matrix[i - 1][j] + 1
            )
          }
        }
      }
      return matrix[b.length][a.length]
    },
    getSignificantChanges(differences) {
      return Object.entries(differences)
        .slice(0, 10)
        .map(([key, diff]) => ({
          key,
          type: diff.action,
          content: diff.new || diff.old
        }))
    },
    async prepareRollback() {
      if (!this.reason.trim()) {
        this.$toast.error('Please provide a reason for the rollback')
        return
      }

      try {
        const response = await this.$axios.post(
          `/api/contents/${this.contentId}/versions/${this.selectedVersionId}/prepare-restore`,
          { reason: this.reason }
        )
        this.rollbackId = response.data.rollback_id
        this.step = 'confirm'
      } catch (error) {
        console.error('Failed to prepare rollback:', error)
        this.$toast.error('Failed to prepare rollback')
      }
    },
    async confirmRollback() {
      try {
        const payload = {
          rollback_id: this.rollbackId,
          notify_team: this.notifyTeam,
          create_ticket: this.createTicket
        }
        
        const response = await this.$axios.post(
          `/api/contents/${this.contentId}/versions/${this.selectedVersionId}/confirm-restore`,
          payload
        )
        
        this.newVersionNumber = response.data.new_version_number
        this.step = 'complete'
        this.$emit('rollback-completed', {
          newVersion: this.newVersionNumber,
          oldVersion: this.currentVersion.version_number
        })
      } catch (error) {
        console.error('Failed to confirm rollback:', error)
        this.$toast.error('Failed to complete rollback')
      }
    },
    cancel() {
      this.$emit('cancel')
    },
    close() {
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
.rollback-confirmation {
  @apply p-6 bg-white rounded-lg shadow-lg max-w-4xl mx-auto;
}

.step-title {
  @apply text-2xl font-bold mb-6 text-center;
}

.version-selectors {
  @apply grid grid-cols-1 md:grid-cols-2 gap-4 mb-6;
}

.comparison-container {
  @apply mt-6;
}

.reason-input {
  @apply mt-6;
}

.reason-input label {
  @apply block text-sm font-medium text-gray-700 mb-1;
}

.reason-input textarea {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.action-buttons {
  @apply flex justify-end gap-3 mt-6;
}

.confirmation-details {
  @apply bg-gray-50 p-4 rounded-lg mb-6;
}

.confirmation-details h3 {
  @apply text-lg font-medium mb-3;
}

.detail-row {
  @apply flex gap-2 mb-2;
}

.detail-label {
  @apply font-medium text-gray-700;
}

.diff-preview {
  @apply border rounded-lg overflow-hidden;
}

.diff-preview h3 {
  @apply bg-gray-100 p-3 text-lg font-medium;
}

.diff-lines {
  @apply divide-y divide-gray-200;
}

.diff-line {
  @apply p-3 flex gap-4;
}

.diff-line-add {
  @apply bg-green-50;
}

.diff-line-del {
  @apply bg-red-50;
}

.diff-line-mod {
  @apply bg-yellow-50;
}

.change-type {
  @apply font-mono text-xs px-2 py-1 rounded self-start;
}

.diff-line-add .change-type {
  @apply bg-green-100 text-green-800;
}

.diff-line-del .change-type {
  @apply bg-red-100 text-red-800;
}

.diff-line-mod .change-type {
  @apply bg-yellow-100 text-yellow-800;
}

.change-content {
  @apply flex-1;
}

.success-message {
  @apply text-center py-8;
}

.success-message h2 {
  @apply text-xl font-bold mt-4 mb-2;
}

.success-message p {
  @apply text-gray-600;
}

.impact-analysis {
  @apply bg-blue-50 p-4 rounded-lg mb-6;
}

.impact-analysis h3 {
  @apply text-lg font-medium mb-3;
}

.impact-items {
  @apply space-y-2;
}

.impact-item {
  @apply flex items-center gap-2;
}

.impact-icon {
  @apply text-lg;
}

.notification-options {
  @apply bg-gray-50 p-4 rounded-lg mb-6;
}

.notification-options h3 {
  @apply text-lg font-medium mb-3;
}

.notification-item {
  @apply flex items-center gap-2 mb-2;
}

.notification-item input {
  @apply h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded;
}

.notification-item label {
  @apply text-sm text-gray-700;
}
</style>