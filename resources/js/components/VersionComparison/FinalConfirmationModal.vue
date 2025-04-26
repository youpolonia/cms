<template>
  <div class="modal-overlay">
    <div class="modal-container">
      <div class="modal-header">
        <h3>Final Confirmation</h3>
        <button @click="$emit('cancel')" class="modal-close">
          &times;
        </button>
      </div>
      
      <div class="modal-body">
        <div class="warning-banner">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <span>This action cannot be undone</span>
        </div>

        <div class="confirmation-details">
          <h4>You are about to:</h4>
          <ul>
            <li>Rollback {{ changes.linesChanged }} lines of content</li>
            <li>Affect {{ impact.linkedContent.length }} linked items</li>
            <li>Modify {{ impact.scheduledPublishes.length }} scheduled publishes</li>
          </ul>
        </div>
      </div>

      <div class="modal-footer">
        <button @click="$emit('cancel')" class="btn btn-secondary">
          Cancel
        </button>
        <button @click="$emit('confirm')" class="btn btn-danger">
          Confirm Rollback
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    changes: {
      type: Object,
      required: true
    },
    impact: {
      type: Object,
      required: true
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50;
}

.modal-container {
  @apply bg-white rounded-lg shadow-xl w-full max-w-md;
}

.modal-header {
  @apply flex justify-between items-center p-4 border-b;
}

.modal-header h3 {
  @apply text-lg font-medium;
}

.modal-close {
  @apply text-gray-500 hover:text-gray-700 text-2xl;
}

.modal-body {
  @apply p-4;
}

.warning-banner {
  @apply bg-yellow-100 text-yellow-800 p-3 rounded flex items-center gap-2 mb-4;
}

.confirmation-details {
  @apply space-y-2;
}

.confirmation-details h4 {
  @apply font-medium;
}

.confirmation-details ul {
  @apply list-disc pl-5 space-y-1;
}

.modal-footer {
  @apply flex justify-end gap-2 p-4 border-t;
}

.btn-danger {
  @apply bg-red-600 hover:bg-red-700 text-white;
}
</style>