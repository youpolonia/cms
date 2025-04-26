<template>
  <modal :show="modelValue" @close="$emit('update:modelValue', false)">
    <template #header>
      <h3 class="text-lg font-medium">Bulk Content Operations</h3>
    </template>

    <div class="space-y-6">
      <div>
        <h4 class="font-medium mb-2">Add Multiple Contents</h4>
        <select-content 
          v-model="contentsToAdd"
          multiple
          placeholder="Select contents to add"
        />
      </div>

      <div>
        <h4 class="font-medium mb-2">Remove Multiple Contents</h4>
        <select-content 
          v-model="contentsToRemove"
          :options="currentContents"
          multiple
          placeholder="Select contents to remove"
        />
      </div>
    </div>

    <template #footer>
      <div class="flex justify-between">
        <button @click="$emit('update:modelValue', false)" class="btn btn-secondary">
          Cancel
        </button>
        <button @click="applyBulkOperations" class="btn btn-primary">
          Apply Changes
        </button>
      </div>
    </template>
  </modal>
</template>

<script>
export default {
  props: {
    modelValue: {
      type: Boolean,
      required: true
    },
    currentContents: {
      type: Array,
      default: () => []
    }
  },

  emits: ['update:modelValue', 'add', 'remove'],

  data() {
    return {
      contentsToAdd: [],
      contentsToRemove: []
    }
  },

  methods: {
    applyBulkOperations() {
      if (this.contentsToAdd.length > 0) {
        this.$emit('add', this.contentsToAdd)
      }
      if (this.contentsToRemove.length > 0) {
        this.$emit('remove', this.contentsToRemove)
      }
      this.$emit('update:modelValue', false)
    }
  }
}
</script>