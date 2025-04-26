<template>
  <div class="recipient-segmentation">
    <div class="header">
      <h1>Recipient Segmentation</h1>
      <p>Define groups of recipients for targeted notifications</p>
    </div>

    <div class="segmentation-container">
      <div class="segment-builder">
        <div class="segment-name">
          <label>Segment Name</label>
          <input 
            type="text" 
            v-model="segment.name" 
            placeholder="e.g. Premium Customers, Active Users"
          >
        </div>

        <div class="rules-container">
          <div class="rules-group" v-for="(group, groupIndex) in segment.groups" :key="groupIndex">
            <div class="group-header">
              <span class="group-label">Group {{ groupIndex + 1 }}</span>
              <select v-model="group.operator" class="group-operator">
                <option value="and">All of the following</option>
                <option value="or">Any of the following</option>
              </select>
              <button 
                class="btn-remove-group"
                @click="removeGroup(groupIndex)"
                v-if="segment.groups.length > 1"
              >
                Remove Group
              </button>
            </div>

            <div class="rules-list">
              <div 
                class="rule" 
                v-for="(rule, ruleIndex) in group.rules" 
                :key="ruleIndex"
              >
                <div class="rule-fields">
                  <select v-model="rule.field" class="field-select">
                    <option 
                      v-for="field in availableFields" 
                      :key="field.value" 
                      :value="field.value"
                    >
                      {{ field.label }}
                    </option>
                  </select>

                  <select v-model="rule.operator" class="operator-select">
                    <option 
                      v-for="op in getOperatorsForField(rule.field)" 
                      :key="op.value" 
                      :value="op.value"
                    >
                      {{ op.label }}
                    </option>
                  </select>

                  <component 
                    :is="getInputComponent(rule.field)" 
                    v-model="rule.value"
                    :field="rule.field"
                    :options="getOptionsForField(rule.field)"
                  />

                  <button 
                    class="btn-remove-rule"
                    @click="removeRule(groupIndex, ruleIndex)"
                    v-if="group.rules.length > 1"
                  >
                    Remove
                  </button>
                </div>
              </div>

              <button 
                class="btn-add-rule"
                @click="addRule(groupIndex)"
              >
                + Add Rule
              </button>
            </div>
          </div>

          <button 
            class="btn-add-group"
            @click="addGroup"
          >
            + Add Group
          </button>
        </div>
      </div>

      <div class="preview-panel">
        <div class="preview-header">
          <h3>Audience Preview</h3>
          <div class="preview-actions">
            <button 
              class="btn-refresh"
              @click="refreshPreview"
              :disabled="previewLoading"
            >
              Refresh
            </button>
            <span class="count">
              {{ previewCount.toLocaleString() }} matching recipients
            </span>
          </div>
        </div>

        <div class="preview-content">
          <div v-if="previewLoading" class="loading">
            Loading preview...
          </div>
          <div v-else-if="previewError" class="error">
            Error loading preview: {{ previewError }}
          </div>
          <div v-else-if="previewData.length === 0" class="empty">
            No recipients match the current rules
          </div>
          <div v-else class="preview-list">
            <div 
              class="preview-item" 
              v-for="item in previewData" 
              :key="item.id"
            >
              <div class="item-name">{{ item.name }}</div>
              <div class="item-email">{{ item.email }}</div>
              <div class="item-meta">
                <span v-if="item.company">{{ item.company }}</span>
                <span v-if="item.role">{{ item.role }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="pagination" v-if="previewData.length > 0">
          <button 
            class="btn-prev"
            @click="prevPage"
            :disabled="previewPage === 1"
          >
            Previous
          </button>
          <span class="page-info">
            Page {{ previewPage }} of {{ totalPages }}
          </span>
          <button 
            class="btn-next"
            @click="nextPage"
            :disabled="previewPage === totalPages"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <div class="saved-segments">
      <h3>Saved Segments</h3>
      <div class="segments-list">
        <div 
          class="segment-item" 
          v-for="savedSegment in savedSegments" 
          :key="savedSegment.id"
        >
          <div class="segment-info">
            <div class="segment-name">{{ savedSegment.name }}</div>
            <div class="segment-count">
              {{ savedSegment.recipient_count.toLocaleString() }} recipients
            </div>
            <div class="segment-rules">
              {{ formatRulesSummary(savedSegment.rules) }}
            </div>
          </div>
          <div class="segment-actions">
            <button 
              class="btn-apply"
              @click="applySegment(savedSegment)"
            >
              Apply
            </button>
            <button 
              class="btn-delete"
              @click="deleteSegment(savedSegment.id)"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="actions">
      <button 
        class="btn-save"
        @click="saveSegment"
        :disabled="!segment.name || !hasRules"
      >
        Save Segment
      </button>
      <button 
        class="btn-cancel"
        @click="$router.back()"
      >
        Cancel
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import TextInput from './components/TextInput.vue';
import SelectInput from './components/SelectInput.vue';
import DateInput from './components/DateInput.vue';
import NumberInput from './components/NumberInput.vue';

const segment = ref({
  name: '',
  groups: [
    {
      operator: 'and',
      rules: [
        {
          field: 'email',
          operator: 'contains',
          value: ''
        }
      ]
    }
  ]
});

const availableFields = ref([
  { value: 'email', label: 'Email', type: 'string' },
  { value: 'name', label: 'Name', type: 'string' },
  { value: 'role', label: 'Role', type: 'string' },
  { value: 'company', label: 'Company', type: 'string' },
  { value: 'status', label: 'Status', type: 'string' },
  { value: 'last_active', label: 'Last Active', type: 'date' },
  { value: 'signup_date', label: 'Signup Date', type: 'date' },
  { value: 'login_count', label: 'Login Count', type: 'number' },
  { value: 'plan', label: 'Subscription Plan', type: 'string' },
  { value: 'country', label: 'Country', type: 'string' }
]);

const operators = {
  string: [
    { value: 'equals', label: 'equals' },
    { value: 'not_equals', label: 'does not equal' },
    { value: 'contains', label: 'contains' },
    { value: 'not_contains', label: 'does not contain' },
    { value: 'starts_with', label: 'starts with' },
    { value: 'ends_with', label: 'ends with' },
    { value: 'is_empty', label: 'is empty' },
    { value: 'is_not_empty', label: 'is not empty' }
  ],
  number: [
    { value: 'equals', label: 'equals' },
    { value: 'not_equals', label: 'does not equal' },
    { value: 'greater_than', label: 'greater than' },
    { value: 'less_than', label: 'less than' },
    { value: 'greater_or_equal', label: 'greater or equal' },
    { value: 'less_or_equal', label: 'less or equal' },
    { value: 'is_empty', label: 'is empty' },
    { value: 'is_not_empty', label: 'is not empty' }
  ],
  date: [
    { value: 'equals', label: 'is on' },
    { value: 'not_equals', label: 'is not on' },
    { value: 'before', label: 'is before' },
    { value: 'after', label: 'is after' },
    { value: 'between', label: 'is between' },
    { value: 'is_empty', label: 'is empty' },
    { value: 'is_not_empty', label: 'is not empty' }
  ]
};

const fieldOptions = {
  status: [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'pending', label: 'Pending' }
  ],
  plan: [
    { value: 'free', label: 'Free' },
    { value: 'basic', label: 'Basic' },
    { value: 'pro', label: 'Pro' },
    { value: 'enterprise', label: 'Enterprise' }
  ],
  country: [
    { value: 'us', label: 'United States' },
    { value: 'uk', label: 'United Kingdom' },
    { value: 'ca', label: 'Canada' },
    { value: 'au', label: 'Australia' },
    { value: 'de', label: 'Germany' }
  ]
};

const savedSegments = ref([]);
const previewData = ref([]);
const previewCount = ref(0);
const previewPage = ref(1);
const previewLoading = ref(false);
const previewError = ref(null);
const totalPages = ref(1);

const hasRules = computed(() => {
  return segment.value.groups.some(group => group.rules.length > 0);
});

const getOperatorsForField = (field) => {
  const fieldType = availableFields.value.find(f => f.value === field)?.type || 'string';
  return operators[fieldType] || operators.string;
};

const getInputComponent = (field) => {
  const fieldType = availableFields.value.find(f => f.value === field)?.type || 'string';
  switch (fieldType) {
    case 'number': return NumberInput;
    case 'date': return DateInput;
    default: 
      return fieldOptions[field] ? SelectInput : TextInput;
  }
};

const getOptionsForField = (field) => {
  return fieldOptions[field] || [];
};

const addGroup = () => {
  segment.value.groups.push({
    operator: 'and',
    rules: [
      {
        field: 'email',
        operator: 'contains',
        value: ''
      }
    ]
  });
};

const removeGroup = (index) => {
  segment.value.groups.splice(index, 1);
};

const addRule = (groupIndex) => {
  segment.value.groups[groupIndex].rules.push({
    field: 'email',
    operator: 'contains',
    value: ''
  });
};

const removeRule = (groupIndex, ruleIndex) => {
  segment.value.groups[groupIndex].rules.splice(ruleIndex, 1);
};

const refreshPreview = async () => {
  previewLoading.value = true;
  previewError.value = null;
  
  try {
    const response = await axios.post('/api/segments/preview', {
      rules: segment.value,
      page: previewPage.value,
      per_page: 10
    });
    
    previewData.value = response.data.items;
    previewCount.value = response.data.total;
    totalPages.value = Math.ceil(response.data.total / 10);
  } catch (error) {
    previewError.value = error.response?.data?.message || error.message;
  } finally {
    previewLoading.value = false;
  }
};

const prevPage = () => {
  if (previewPage.value > 1) {
    previewPage.value--;
    refreshPreview();
  }
};

const nextPage = () => {
  if (previewPage.value < totalPages.value) {
    previewPage.value++;
    refreshPreview();
  }
};

const loadSavedSegments = async () => {
  try {
    const response = await axios.get('/api/segments');
    savedSegments.value = response.data;
  } catch (error) {
    console.error('Error loading saved segments:', error);
  }
};

const applySegment = (savedSegment) => {
  segment.value = JSON.parse(JSON.stringify(savedSegment.rules));
  segment.value.name = savedSegment.name;
  previewPage.value = 1;
  refreshPreview();
};

const saveSegment = async () => {
  try {
    await axios.post('/api/segments', {
      name: segment.value.name,
      rules: segment.value
    });
    await loadSavedSegments();
  } catch (error) {
    console.error('Error saving segment:', error);
  }
};

const deleteSegment = async (id) => {
  try {
    await axios.delete(`/api/segments/${id}`);
    await loadSavedSegments();
  } catch (error) {
    console.error('Error deleting segment:', error);
  }
};

const formatRulesSummary = (rules) => {
  if (!rules || !rules.groups || rules.groups.length === 0) return 'No rules defined';
  
  const firstGroup = rules.groups[0];
  if (!firstGroup.rules || firstGroup.rules.length === 0) return 'No rules defined';
  
  const firstRule = firstGroup.rules[0];
  return `${firstRule.field} ${firstRule.operator} ${firstRule.value}` + 
    (rules.groups.length > 1 || firstGroup.rules.length > 1 ? '...' : '');
};

onMounted(() => {
  loadSavedSegments();
  refreshPreview();
});
</script>

<style scoped>
.recipient-segmentation {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  margin-bottom: 30px;
}

.header h1 {
  margin-bottom: 5px;
}

.segmentation-container {
  display: flex;
  gap: 20px;
}

.segment-builder {
  flex: 1;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.segment-name {
  margin-bottom: 20px;
}

.segment-name label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.segment-name input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.rules-group {
  margin-bottom: 20px;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 6px;
}

.group-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.group-label {
  font-weight: 500;
}

.group-operator {
  padding: 5px 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
}

.btn-remove-group {
  margin-left: auto;
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 13px;
}

.rules-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.rule {
  padding: 10px;
  background: white;
  border-radius: 4px;
  border: 1px solid #eee;
}

.rule-fields {
  display: flex;
  align-items: center;
  gap: 10px;
}

.field-select,
.operator-select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
}

.btn-remove-rule {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 13px;
  margin-left: auto;
}

.btn-add-rule {
  background: #f5f5f5;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  margin-top: 10px;
}

.btn-add-group {
  background: #f5f5f5;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.preview-panel {
  flex: 1;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.preview-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-refresh {
  background: #f5f5f5;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.count {
  font-size: 13px;
  color: #666;
}

.preview-content {
  min-height: 300px;
  border: 1px solid #eee;
  border-radius: 6px;
  padding: 10px;
}

.loading,
.error,
.empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 300px;
  color: #666;
}

.error {
  color: #ef4444;
}

.preview-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.preview-item {
  padding: 10px;
  border-bottom: 1px solid #eee;
}

.preview-item:last-child {
  border-bottom: none;
}

.item-name {
  font-weight: 500;
}

.item-email {
  font-size: 13px;
  color: #666;
}

.item-meta {
  display: flex;
  gap: 10px;
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 15px;
  margin-top: 15px;
}

.btn-prev,
.btn-next {
  background: #f5f5f5;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.page-info {
  font-size: 13px;
  color: #666;
}

.saved-segments {
  margin-top: 30px;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.segments-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 15px;
}

.segment-item {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  background: #f9f9f9;
  border-radius: 6px;
}

.segment-info {
  flex: 1;
}

.segment-name {
  font-weight: 500;
  margin-bottom: 5px;
}

.segment-count {
  font-size: 13px;
  color: #666;
  margin-bottom: 5px;
}

.segment-rules {
  font-size: 13px;
  color: #666;
}

.segment-actions {
  display: flex;
  gap: 10px;
}

.btn-apply {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
}

.btn-delete {
  background: #ef4444;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
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