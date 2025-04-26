<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Button from '@/Components/Button.vue'
import SearchInput from '@/Components/Forms/SearchInput.vue'
import Dropdown from '@/Components/Dropdown.vue'

const props = defineProps({
  contents: Object,
  filters: Object
})

const search = ref(props.filters.search || '')
const contentType = ref(props.filters.content_type || '')

const filteredContents = computed(() => {
  return props.contents.data.filter(content => {
    return (
      (search.value === '' || 
       content.title.toLowerCase().includes(search.value.toLowerCase())) &&
      (contentType.value === '' || 
       content.content_type === contentType.value)
    )
  })
})

const deleteContent = (id) => {
  if (confirm('Are you sure you want to delete this content?')) {
    router.delete(route('contents.destroy', id))
  }
}
</script>

<template>
  <AdminLayout title="Content Management">
    <template #header>
      <h1 class="text-2xl font-semibold text-gray-900">
        Content Management
      </h1>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
              <SearchInput 
                v-model="search"
                placeholder="Search content..."
                class="w-64"
              />
              <div class="flex space-x-2">
                <Dropdown>
                  <template #trigger>
                    <Button variant="secondary">
                      Filter
                    </Button>
                  </template>
                  <template #content>
                    <div class="p-2">
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Content Type
                      </label>
                      <select 
                        v-model="contentType"
                        class="w-full border-gray-300 rounded-md shadow-sm"
                      >
                        <option value="">All Types</option>
                        <option value="page">Page</option>
                        <option value="post">Post</option>
                        <option value="custom">Custom</option>
                      </select>
                    </div>
                  </template>
                </Dropdown>
                <Button 
                  :href="route('contents.create')"
                  variant="primary"
                >
                  Create Content
                </Button>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Title
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Categories
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Last Updated
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="content in filteredContents" :key="content.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ content.title }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                        :class="{
                          'bg-blue-100 text-blue-800': content.content_type === 'page',
                          'bg-green-100 text-green-800': content.content_type === 'post',
                          'bg-purple-100 text-purple-800': content.content_type === 'custom'
                        }">
                        {{ content.content_type }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex flex-wrap gap-1">
                        <span 
                          v-for="category in content.categories" 
                          :key="category.id"
                          class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"
                        >
                          {{ category.name }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ new Date(content.updated_at).toLocaleString() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex justify-end space-x-2">
                        <Button 
                          :href="route('contents.show', content.id)"
                          variant="secondary"
                          size="sm"
                        >
                          View
                        </Button>
                        <Button 
                          :href="route('contents.edit', content.id)"
                          variant="secondary"
                          size="sm"
                        >
                          Edit
                        </Button>
                        <Button 
                          @click="deleteContent(content.id)"
                          variant="danger"
                          size="sm"
                        >
                          Delete
                        </Button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <Pagination 
              v-if="contents.meta.last_page > 1"
              :links="contents.links"
              class="mt-4"
            />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>