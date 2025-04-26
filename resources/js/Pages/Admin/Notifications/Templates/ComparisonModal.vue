<template>
  <Modal :show="true" @close="$emit('close')">
    <div class="comparison-modal">
      <div class="modal-header">
        <h2>Comparing Versions</h2>
        <button @click="$emit('close')" class="close-btn">
          &times;
        </button>
      </div>

      <div class="version-info">
        <div class="version current">
          <h3>Current Version (v{{ current.version_number }})</h3>
          <div class="version-date">{{ formatDate(current.created_at) }}</div>
        </div>
        <div class="version selected">
          <h3>Selected Version (v{{ version.version_number }})</h3>
          <div class="version-date">{{ formatDate(version.created_at) }}</div>
        </div>
      </div>

      <div class="comparison-sections">
        <div class="section" v-if="hasChanges('subject')">
          <h4>Subject</h4>
          <div class="diff-content">
            <div class="diff-line" v-html="diffSubject"></div>
          </div>
        </div>

        <div class="section" v-if="hasChanges('content')">
          <h4>Content</h4>
          <div class="diff-content">
            <div 
              class="diff-line" 
              v-for="(line, index) in diffContent" 
              :key="index"
              v-html="line"
            ></div>
          </div>
        </div>

        <div class="section" v-if="hasChanges('variables')">
          <h4>Variables</h4>
          <div class="variables-comparison">
            <div class="variables-section">
              <h5>Added/Changed</h5>
              <div 
                class="variable-item" 
                v-for="(desc, varName) in addedVariables" 
                :key="'added-'+varName"
              >
                <span class="var-name">{{ varName }}</span>
                <span class="var-desc">{{ desc }}</span>
              </div>
              <div v-if="!Object.keys(addedVariables).length" class="no-changes">
                No variables added or changed
              </div>
            </div>

            <div class="variables-section">
              <h5>Removed</h5>
              <div 
                class="variable-item removed" 
                v-for="varName in removedVariables" 
                :key="'removed-'+varName"
              >
                <span class="var-name">{{ varName }}</span>
              </div>
              <div v-if="!removedVariables.length" class="no-changes">
                No variables removed
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button 
          @click="$emit('restore')" 
          class="btn btn-restore"
        >
          Restore This Version
        </button>
        <button 
          @click="$emit('close')" 
          class="btn btn-cancel"
        >
          Close
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import { formatDate } from '@/utils/date';
import * as Diff from 'diff';

const props = defineProps({
  current: {
    type: Object,
    required: true
  },
  version: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'restore']);

const hasChanges = (field) => {
  return props.version.changes.includes(field);
};

const diffSubject = computed(() => {
  const diff = Diff.diffWords(props.current.subject, props.version.subject);
  return diff.map(part => {
    const color = part.added ? 'green' : part.removed ? 'red' : 'grey';
    return `<span style="color:${color}">${part.value}</span>`;
  }).join('');
});

const diffContent = computed(() => {
  const currentLines = props.current.content.split('\n');
  const versionLines = props.version.content.split('\n');
  const diff = Diff.diffLines(props.current.content, props.version.content);
  
  return diff.map(part => {
    if (part.added) {
      return `<div class="added">+ ${part.value}</div>`;
    } else if (part.removed) {
      return `<div class="removed">- ${part.value}</div>`;
    }
    return `<div class="unchanged">${part.value}</div>`;
  });
});

const addedVariables = computed(() => {
  const added = {};
  for (const [key, value] of Object.entries(props.version.variables)) {
    if (props.current.variables[key] !== value || !props.current.variables[key]) {
      added[key] = value;
    }
  }
  return added;
});

const removedVariables = computed(() => {
  return Object.keys(props.current.variables).filter(
    key => !props.version.variables[key]
  );
});
</script>

<style scoped>
.comparison-modal {
  background: white;
  border-radius: 8px;
  width: 900px;
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #777;
}

.version-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  padding: 20px;
  background: #f9f9f9;
}

.version h3 {
  margin: 0 0 5px 0;
  font-size: 16px;
}

.version-date {
  color: #666;
  font-size: 14px;
}

.comparison-sections {
  padding: 20px;
}

.section {
  margin-bottom: 30px;
}

.section h4 {
  margin-bottom: 15px;
  color: #555;
  border-bottom: 1px solid #eee;
  padding-bottom: 8px;
}

.diff-content {
  background: #f5f5f5;
  border-radius: 4px;
  padding: 15px;
  font-family: monospace;
  white-space: pre-wrap;
}

.diff-line {
  margin-bottom: 5px;
  line-height: 1.5;
}

.added {
  background: #e6ffec;
  color: #22863a;
}

.removed {
  background: #ffebee;
  color: #cb2431;
}

.unchanged {
  color: #666;
}

.variables-comparison {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.variables-section {
  background: #f9f9f9;
  border-radius: 4px;
  padding: 15px;
}

.variables-section h5 {
  margin: 0 0 10px 0;
  font-size: 15px;
  color: #555;
}

.variable-item {
  padding: 8px;
  margin-bottom: 5px;
  background: white;
  border-radius: 3px;
  border-left: 3px solid #4CAF50;
}

.variable-item.removed {
  border-left-color: #F44336;
}

.var-name {
  font-weight: 500;
  color: #333;
}

.var-desc {
  color: #666;
  font-size: 13px;
  display: block;
  margin-top: 3px;
}

.no-changes {
  color: #999;
  font-style: italic;
  font-size: 14px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 20px;
  border-top: 1px solid #eee;
}

.btn {
  padding: 10px 20px;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
  border: none;
}

.btn-restore {
  background: #4CAF50;
  color: white;
}

.btn-cancel {
  background: #f5f5f5;
  color: #333;
}
</style>