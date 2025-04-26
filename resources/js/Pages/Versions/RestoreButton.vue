<template>
    <button @click="showRestoreModal = true" class="btn-secondary">
        Restore This Version
    </button>

    <Modal :show="showRestoreModal" @close="showRestoreModal = false">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Confirm Version Restoration
            </h2>
            <p class="mt-4 text-gray-600">
                This will create a new draft version based on this historical version.
            </p>
            <div class="mt-6 flex justify-end space-x-4">
                <button @click="showRestoreModal = false" class="btn-secondary">
                    Cancel
                </button>
                <button @click="restoreVersion" class="btn-primary">
                    Restore
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    versionId: {
        type: Number,
        required: true
    }
});

const showRestoreModal = ref(false);

const restoreVersion = () => {
    axios.post('/api/versions/restore', {
        version_id: props.versionId
    }).then(() => {
        router.reload();
    });
    showRestoreModal.value = false;
};
</script>