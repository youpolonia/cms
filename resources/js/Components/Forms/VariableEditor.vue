<template>
  <div class="variable-editor">
    <div class="header">
      <h3>Template Variables</h3>
      <button @click="addVariable" class="btn btn-sm btn-primary">
        Add Variable
      </button>
    </div>

    <div class="variables-list">
      <div v-for="(desc, varName, index) in variables" :key="index" class="variable-item">
        <div class="variable-inputs">
          <input
            v-model="variableNames[index]"
            type="text"
            placeholder="Variable name"
            @change="updateVariableName(index)"
          >
          <input
            v-model="variableDescs[index]"
            type="text"
            placeholder="Description"
            @change="updateVariableDesc(index)"
          >
          <button @click="removeVariable(index)" class="btn btn-sm btn-danger">
            Remove
          </button>
        </div>
      </div>

      <div v-if="Object.keys(variables).length === 0" class="empty-state">
        No variables defined. Add variables that can be used in the template content.
      </div>
    </div>

    <div class="help-text">
      <p>Use variables in your template with <code>{{"{{variable_name}}"}}</code> syntax.</p>
      <p>Example: <code>Hello {{"{{first_name}}"}}, welcome to our platform!</code></p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  variables: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update']);

const variableNames = ref([]);
const variableDescs = ref([]);

const syncArrays = () => {
  variableNames.value = Object.keys(props.variables);
  variableDescs.value = Object.values(props.variables);
};

const addVariable = () => {
  const newVars = { ...props.variables, ['new_variable']: '' };
  emit('update', newVars);
};

const updateVariableName = (index) => {
  const newVars = { ...props.variables };
  const oldKey = Object.keys(newVars)[index];
  const value = newVars[oldKey];
  delete newVars[oldKey];
  newVars[variableNames.value[index]] = value;
  emit('update', newVars);
};

const updateVariableDesc = (index) => {
  const newVars = { ...props.variables };
  const key = variableNames.value[index];
  newVars[key] = variableDescs.value[index];
  emit('update', newVars);
};

const removeVariable = (index) => {
  const newVars = { ...props.variables };
  const key = variableNames.value[index];
  delete newVars[key];
  emit('update', newVars);
};

watch(() => props.variables, syncArrays, { immediate: true });
</script>

<style scoped>
.variable-editor {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 15px;
  background: #f9f9f9;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.header h3 {
  margin: 0;
  font-size: 16px;
  color: #555;
}

.variables-list {
  margin-bottom: 15px;
}

.variable-item {
  margin-bottom: 10px;
}

.variable-inputs {
  display: grid;
  grid-template-columns: 1fr 2fr auto;
  gap: 10px;
  align-items: center;
}

.variable-inputs input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.empty-state {
  padding: 15px;
  text-align: center;
  color: #777;
  font-size: 14px;
  border: 1px dashed #ddd;
  border-radius: 4px;
  background: white;
}

.help-text {
  font-size: 13px;
  color: #666;
  margin-top: 15px;
}

.help-text code {
  background: #f0f0f0;
  padding: 2px 4px;
  border-radius: 3px;
  font-family: monospace;
}

.btn {
  padding: 6px 12px;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
  border: none;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 12px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-danger {
  background: #fee2e2;
  color: #dc2626;
}
</style>