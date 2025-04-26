<script setup>
import ConfirmationModal from '../ui/ConfirmationModal.vue'

const props = defineProps({
    type: String
})

const emit = defineEmits(['duplicate', 'remove'])
const showConfirmModal = ref(false)

function confirmRemove() {
    showConfirmModal.value = true
}

function handleRemove() {
    emit('remove')
    showConfirmModal.value = false
}
</script>

<template>
    <div class="inline-flex rounded-md shadow-sm">
        <button
            type="button"
            class="inline-flex items-center p-1 text-gray-700 hover:bg-gray-50"
            @click="emit('duplicate')"
        >
            <Icon name="copy" size="16" title="Duplicate block" />
        </button>
        <button
            type="button"
            class="inline-flex items-center p-1 text-red-600 hover:bg-red-50"
            @click="confirmRemove"
        >
            <Icon name="trash" size="16" title="Delete block" />
        </button>

        <ConfirmationModal
            :show="showConfirmModal"
            @cancel="showConfirmModal = false"
            @confirm="handleRemove"
        >
            <template #title>Remove Block?</template>
            <p>This will permanently delete this {{ type }} block.</p>
        </ConfirmationModal>
    </div>
</template>