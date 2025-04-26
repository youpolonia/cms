<script setup>
const props = defineProps({
  show: Boolean,
  block: Object,
  contentVersions: Array
})

const emit = defineEmits(['close', 'restore-version'])

const activeTab = ref('preview')
const compareWithIndex = ref(null)
const selectedContent = ref('current')

// Toggles between preview and version compare views
function toggleView() {
  activeTab.value = activeTab.value === 'preview' ? 'versions' : 'preview'
}

function handleRestore(versionId) {
  emit('restore-version', versionId)
}

function handleCompare(versionIndex) {
  compareWithIndex.value = versionIndex
  selectedContent.value = 'compare'
}
</script>

<template>
  <Modal :show="show" max-width="4xl">
    <div class="preview-modal">
      <!-- Header -->
      <div class="modal-header">
        <h3 class="font-medium text-lg">
          Block Preview - {{ block?.type }} Content
        </h3>
        
        <div class="flex items-center space-x-3">
          <button 
            class="text-sm" 
            @click="toggleView"
            :class="{ 'font-bold': activeTab === 'versions' }"
          >
            {{ activeTab === 'preview' ? 'Show Versions' : 'Show Preview' }}
          </button>
          
          <button @click="emit('close')">
            <Icon name="x" size="16" />
          </button>
        </div>
      </div>

      <!-- Content Area -->
      <div class="modal-content-area">
        <!-- Preview Mode -->
        <div v-if="activeTab === 'preview'" class="preview-mode">
          <RenderableBlock :block="block" simple-mode />
        </div>

        <!-- Versions Mode -->
        <div v-if="activeTab === 'versions'">
          <div class="versions-list">
            <div 
              v-for="(version, i) in contentVersions" 
              :key="version.id"
              class="version-item"
            >
              <div class="version-meta">
                <span class="version-number">{{ (i + 1) }}</span>
                <span class="version-date">{{ formatDate(version.created_at) }}</span>
              </div>
              
              <RenderableBlock 
                :block="{ type: block?.type, content: version.content }" 
                simple-mode
              />
              
              <div class="version-actions">
                <button 
                  @click="handleRestore(version.id)"
                  class="text-blue-600"
                >
                  Restore
                </button>
                
                <button 
                  @click="handleCompare(i)"
                  class="text-gray-600"
                >
                  Compare
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Compare View -->
        <VersionComparison
          v-if="selectedContent === 'compare' && contentVersions[compareWithIndex]"
          :current="block"
          :compareTo="{ 
            type: block?.type, 
            content: contentVersions[compareWithIndex].content 
          }"
          @close="selectedContent = 'current'"
        />
      </div>
    </div>
  </Modal>
</template>

<style scoped>
.preview-modal {
  @apply bg-white rounded-lg flex flex-col max-h-[90vh] overflow-hidden;
}

.modal-header {
  @apply px-6 py-4 flex items-center justify-between border-b bg-gray-50;
}

.preview-mode {
  @apply p-6;
}

.versions-list {
  @apply space-y-4 max-h-[70vh] overflow-y-auto p-4;
}

.version-item {
  @apply border rounded-lg p-4 space-y-3;
}

.version-meta {
  @apply flex items-center space-x-3 text-sm;
}

.version-number {
  @apply w-6 h-6 flex items-center justify-center rounded-full bg-blue-100 text-blue-600;
}

.version-actions {
  @apply flex justify-end space-x-4 text-sm;
}
</style>