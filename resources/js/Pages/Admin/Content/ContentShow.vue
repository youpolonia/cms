<script setup>
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Button from '@/Components/Button.vue'
import { onMounted, ref } from 'vue'
import AnalyticsCacheService from '@/services/AnalyticsCacheService'

const props = defineProps({
  content: Object
})

const analytics = new AnalyticsCacheService()
const timeSpent = ref(0)
const scrollDepth = ref(0)

const deleteContent = () => {
  if (confirm('Are you sure you want to delete this content?')) {
    router.delete(route('contents.destroy', props.content.id))
  }
}

// Track initial view
onMounted(() => {
  recordView()
  startTimeTracking()
  setupScrollTracking()
})

const recordView = () => {
  const viewData = {
    content_id: props.content.id,
    viewed_at: new Date().toISOString(),
    user_agent: navigator.userAgent
  }
  
  analytics.set(`content_view_${props.content.id}`, viewData)
}

const startTimeTracking = () => {
  const interval = setInterval(() => {
    timeSpent.value += 1
    analytics.set(`time_spent_${props.content.id}`, {
      content_id: props.content.id,
      seconds: timeSpent.value
    }, 3600000) // 1 hour TTL
  }, 1000)

  onUnmounted(() => clearInterval(interval))
}

const setupScrollTracking = () => {
  window.addEventListener('scroll', () => {
    const scrollPercentage = Math.round(
      (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100
    )
    scrollDepth.value = scrollPercentage
    analytics.set(`scroll_depth_${props.content.id}`, {
      content_id: props.content.id,
      percentage: scrollPercentage
    })
  }, { passive: true })
}
</script>

<template>
  <AdminLayout title="Content Details">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">
          Content Details
        </h1>
        <div class="flex space-x-2">
          <Button 
            :href="route('contents.edit', content.id)"
            variant="secondary"
          >
            Edit
          </Button>
          <Button 
            @click="deleteContent"
            variant="danger"
          >
            Delete
          </Button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="space-y-6">
              <div>
                <h2 class="text-xl font-semibold">{{ content.title }}</h2>
                <div class="mt-1 text-sm text-gray-500">
                  <span class="px-2 py-1 rounded-full" 
                    :class="{
                      'bg-blue-100 text-blue-800': content.content_type === 'page',
                      'bg-green-100 text-green-800': content.content_type === 'post',
                      'bg-purple-100 text-purple-800': content.content_type === 'custom'
                    }">
                    {{ content.content_type }}
                  </span>
                </div>
              </div>

              <div v-if="content.categories.length > 0">
                <h3 class="text-sm font-medium text-gray-500">Categories</h3>
                <div class="mt-1 flex flex-wrap gap-2">
                  <span 
                    v-for="category in content.categories" 
                    :key="category.id"
                    class="px-3 py-1 rounded-full bg-gray-100 text-gray-800 text-sm"
                  >
                    {{ category.name }}
                  </span>
                </div>
              </div>

              <div>
                <h3 class="text-sm font-medium text-gray-500">Content</h3>
                <div class="mt-1 prose max-w-none" v-html="content.content"></div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <h3 class="text-sm font-medium text-gray-500">Created</h3>
                  <p class="mt-1 text-sm text-gray-900">
                    {{ new Date(content.created_at).toLocaleString() }}
                  </p>
                </div>
                <div>
                  <h3 class="text-sm font-medium text-gray-500">Last Updated</h3>
                  <p class="mt-1 text-sm text-gray-900">
                    {{ new Date(content.updated_at).toLocaleString() }}
                  </p>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-200">
                <Button 
                  :href="route('contents.index')"
                  variant="secondary"
                >
                  Back to List
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>