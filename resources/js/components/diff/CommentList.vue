<template>
  <div>
    <CommentForm
      v-if="showForm"
      :content-id="contentId"
      :version1-id="version1Id"
      :version2-id="version2Id"
      :content1-hash="content1Hash"
      :content2-hash="content2Hash"
      :diff-range="diffRange"
      :comment="editingComment"
      @submitted="fetchComments"
      @cancelled="cancelEdit"
    />

    <div v-if="loading" class="text-center py-4">
      <p>Loading comments...</p>
    </div>

    <div v-else>
      <div v-if="comments.length === 0" class="text-center py-4 text-gray-500">
        <p>No comments yet</p>
      </div>

      <div v-else class="space-y-4">
        <CommentItem
          v-for="comment in comments"
          :key="comment.id"
          :comment="comment"
          @edit="startEdit"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import CommentForm from './CommentForm.vue'
import CommentItem from './CommentItem.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  contentId: Number,
  version1Id: Number,
  version2Id: Number,
  content1Hash: String,
  content2Hash: String,
  diffRange: Object,
  showForm: {
    type: Boolean,
    default: true
  }
})

const comments = ref([])
const loading = ref(false)
const editingComment = ref(null)

const fetchComments = async () => {
  loading.value = true
  try {
    const params = {}
    if (props.contentId) params.content_id = props.contentId
    if (props.version1Id) params.version1_id = props.version1Id
    if (props.version2Id) params.version2_id = props.version2Id
    if (props.content1Hash) params.content_hash = props.content1Hash

    const response = await router.get('/api/comments', params)
    comments.value = response.props.comments || []
  } finally {
    loading.value = false
  }
}

const startEdit = (comment) => {
  editingComment.value = comment
}

const cancelEdit = () => {
  editingComment.value = null
}

onMounted(fetchComments)

watch(
  () => [props.contentId, props.version1Id, props.version2Id, props.content1Hash],
  fetchComments
)
</script>