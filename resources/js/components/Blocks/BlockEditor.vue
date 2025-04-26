<template>
  <form @submit.prevent="handleSubmit" class="block-form">
    <div v-if="fields" class="space-y-4">
      <div v-for="field in fields" :key="field.name" class="form-group">
        <label :for="field.name" class="block text-sm font-medium text-gray-700">
          {{ field.label }}
        </label>
        
        <input
          v-if="field.type === 'text'"
          v-model="formData[field.name]"
          :type="field.type"
          :id="field.name"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
        />
        
        <textarea
          v-else-if="field.type === 'textarea'"
          v-model="formData[field.name]"
          :id="field.name"
          rows="4"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
        ></textarea>
        
        <select
          v-else-if="field.type === 'select'"
          v-model="formData[field.name]"
          :id="field.name"
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option v-for="option in field.options" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </div>
    
    <div v-else class="text-gray-500 italic">
      Loading block fields...
    </div>
    
    <div class="mt-4 flex justify-end">
      <button 
        type="submit" 
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Saving...' : 'Save Block' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toast-notification';

const props = defineProps({
  block: {
    type: Object,
    required: true
  },
  pageId: {
    type: String,
    required: true
  }
});

const emit = defineEmits(['update']);

const fields = ref(null);
const formData = ref({});
const isSubmitting = ref(false);
const toast = useToast();

onMounted(async () => {
  try {
    const response = await fetch(`/api/pages/${props.pageId}/blocks/fields/${props.block.type}`);
    fields.value = await response.json();
    
    // Initialize form data with block content
    formData.value = { ...props.block.content };
  } catch (error) {
    console.error('Error loading block fields:', error);
    toast.error('Failed to load block fields');
  }
});

const handleSubmit = async () => {
  isSubmitting.value = true;
  
  try {
    const url = props.block.id.startsWith('new') 
      ? `/api/pages/${props.pageId}/blocks`
      : `/api/pages/${props.pageId}/blocks/${props.block.id}`;
    
    const method = props.block.id.startsWith('new') ? 'POST' : 'PUT';
    
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        type: props.block.type,
        content: formData.value
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      toast.success('Block saved successfully');
      emit('update', {
        ...props.block,
        id: props.block.id.startsWith('new') ? data.block_id : props.block.id,
        content: formData.value
      });
    }
  } catch (error) {
    console.error('Error saving block:', error);
    toast.error('Failed to save block');
  } finally {
    isSubmitting.value = false;
  }
};
</script>