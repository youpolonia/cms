<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Button from '@/Components/Button.vue'
import Input from '@/Components/Forms/Input.vue'
import Select from '@/Components/Forms/Select.vue'
import Textarea from '@/Components/Forms/Textarea.vue'
import MultiSelect from '@/Components/MultiSelect.vue'
import InputError from '@/Components/Forms/InputError.vue'
import Modal from '@/Components/Modal.vue'
import { useToast } from '@/Composables/useToast'

const { showToast } = useToast()
const showAIModal = ref(false)
const isLoading = ref(false)
const suggestionPrompt = ref('')
const selectedTemplate = ref('content_suggestion')

const templates = [
  { value: 'content_suggestion', label: 'Content Suggestion' },
  { value: 'seo_optimization', label: 'SEO Optimization' },
  { value: 'content_enhancement', label: 'Content Enhancement' },
  { value: 'content_summary', label: 'Content Summary' }
]

const getAISuggestions = async () => {
  if (!suggestionPrompt.value) return
  
  isLoading.value = true
  try {
    const response = await axios.post(route('api.ai.content.suggestions'), {
      prompt: suggestionPrompt.value,
      template: selectedTemplate.value,
      context: {
        title: form.value.title,
        currentContent: form.value.content
      }
    })
    
    if (response.data.success) {
      form.value.content = response.data.suggestions
      showToast('AI suggestions applied successfully')
      showAIModal.value = false
    }
  } catch (error) {
    showToast(error.response?.data?.message || 'Failed to get AI suggestions', 'error')
  } finally {
    isLoading.value = false
  }
}

const props = defineProps({
  content: {
    type: Object,
    default: () => ({
      title: '',
      content: '',
      content_type: 'post',
      categories: []
    })
  },
  categories: {
    type: Array,
    default: () => []
  },
  errors: Object
})

const form = ref({
  title: props.content.title,
  content: props.content.content,
  content_type: props.content.content_type,
  categories: props.content.categories.map(c => c.id)
})

const contentTypes = [
  { value: 'post', label: 'Post' },
  { value: 'page', label: 'Page' },
  { value: 'custom', label: 'Custom' }
]

const submit = () => {
  const method = props.content.id ? 'put' : 'post'
  const url = props.content.id 
    ? route('contents.update', props.content.id)
    : route('contents.store')

  router[method](url, form.value, {
    onSuccess: () => {
      router.visit(route('contents.index'))
    }
  })
}
</script>

<template>
  <AdminLayout :title="content.id ? 'Edit Content' : 'Create Content'">
    <template #header>
      <h1 class="text-2xl font-semibold text-gray-900">
        {{ content.id ? 'Edit Content' : 'Create Content' }}
      </h1>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit">
              <div class="space-y-6">
                <div class="flex justify-end">
                  <Button
                    type="button"
                    variant="outline"
                    @click="showAIModal = true"
                  >
                    Get AI Suggestions
                  </Button>
                </div>
                <div>
                  <Input
                    v-model="form.title"
                    label="Title"
                    required
                  />
                  <InputError :message="errors?.title" />
                </div>

                <div>
                  <Select
                    v-model="form.content_type"
                    label="Content Type"
                    :options="contentTypes"
                    required
                  />
                  <InputError :message="errors?.content_type" />
                </div>

                <div>
                  <MultiSelect
                    v-model="form.categories"
                    label="Categories"
                    :options="categories"
                    option-value="id"
                    option-label="name"
                  />
                  <InputError :message="errors?.categories" />
                </div>

                <div>
                  <Textarea
                    v-model="form.content"
                    label="Content"
                    rows="10"
                    required
                  />
                  <InputError :message="errors?.content" />
                </div>

                <div class="flex justify-end space-x-3">
                  <Button
                    type="button"
                    variant="secondary"
                    @click="router.visit(route('contents.index'))"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    variant="primary"
                  >
                    {{ content.id ? 'Update' : 'Create' }}
                  </Button>
                </div>
              </div>
            </form>

            <Modal :show="showAIModal" @close="showAIModal = false">
              <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                  AI Content Suggestions
                </h2>
                
                <div class="space-y-4">
                  <Select
                    v-model="selectedTemplate"
                    label="Suggestion Type"
                    :options="templates"
                  />
                  
                  <Textarea
                    v-model="suggestionPrompt"
                    label="Prompt"
                    placeholder="What kind of content are you looking for?"
                    rows="3"
                    required
                  />
                  
                  <div class="flex justify-end space-x-3">
                    <Button
                      type="button"
                      variant="secondary"
                      @click="showAIModal = false"
                    >
                      Cancel
                    </Button>
                    <Button
                      type="button"
                      variant="primary"
                      @click="getAISuggestions"
                      :disabled="isLoading || !suggestionPrompt"
                    >
                      <span v-if="isLoading">Generating...</span>
                      <span v-else>Get Suggestions</span>
                    </Button>
                  </div>
                </div>
              </div>
            </Modal>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>