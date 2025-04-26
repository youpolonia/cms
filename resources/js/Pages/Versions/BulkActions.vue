<template>
    <div class="space-y-4">
        <div class="flex items-center space-x-4">
            <button @click="showDeleteModal = true" class="btn-danger">
                Delete Selected
            </button>
            <button @click="showExportModal = true" class="btn-secondary">
                Export Selected
            </button>
            <select v-model="status" class="form-select" @change="updateStatus">
                <option value="">Change Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
            </select>
        </div>

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Confirm Bulk Deletion
                </h2>
                <p class="mt-4 text-gray-600">
                    Are you sure you want to delete the selected versions?
                </p>
                <div class="mt-6 flex justify-end space-x-4">
                    <button @click="showDeleteModal = false" class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="deleteVersions" class="btn-danger">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>

        <Modal :show="showExportModal" @close="showExportModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Export Options
                </h2>
                <div class="mt-4 space-y-4">
                    <label class="flex items-center space-x-2">
                        <input type="radio" v-model="exportFormat" value="csv" class="form-radio">
                        <span>CSV</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" v-model="exportFormat" value="json" class="form-radio">
                        <span>JSON</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="radio" v-model="exportFormat" value="pdf" class="form-radio">
                        <span>PDF</span>
                    </label>
                </div>
                <div class="mt-6 flex justify-end space-x-4">
                    <button @click="showExportModal = false" class="btn-secondary">
                        Cancel
                    </button>
                    <button @click="exportVersions" class="btn-primary">
                        Export
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    selectedVersions: {
        type: Array,
        required: true
    }
});

const showDeleteModal = ref(false);
const showExportModal = ref(false);
const exportFormat = ref('csv');
const status = ref('');

const deleteVersions = () => {
    axios.post('/api/versions/bulk/delete', {
        versions: props.selectedVersions
    }).then(() => {
        router.reload();
    });
    showDeleteModal.value = false;
};

const exportVersions = () => {
    axios.post('/api/versions/bulk/export', {
        versions: props.selectedVersions,
        format: exportFormat.value
    }).then(() => {
        // Handle export download
    });
    showExportModal.value = false;
};

const updateStatus = () => {
    if (status.value) {
        axios.post('/api/versions/bulk/status', {
            versions: props.selectedVersions,
            status: status.value
        }).then(() => {
            router.reload();
        });
    }
    status.value = '';
};
</script>