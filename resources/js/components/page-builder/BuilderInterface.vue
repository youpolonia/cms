<script setup>
import { ref, computed, onMounted } from 'vue'
import PageBuilderService from '@/services/page-builder/PageBuilderService'
import AIGeneratorService from '@/services/page-builder/AIGeneratorService'
import useDraggable from '@/composables/useDraggable'

const props = defineProps({
    draftId: String,
    isPublished: Boolean
})

const blocks = ref([])
const loading = ref(false)
const saveStatusType = ref('')

const { 
    dragItem,
    setDragItem,
    findDropArea 
} = useDraggable()

// Initialize services if we have draft ID, otherwise create new services
const pageBuilder = new PageBuilderService()
const aiGenerator = new AIGeneratorService()

function handleBlockAdd(type) {
    if (type === 'ai') {
        isAIOpen.value = true
    } else {
        pageBuilder.addBlock(type)
        blocks.value = [...pageBuilder.blocks]
    }
}

async function handleSave() {
    saveStatusType.value = 'loading'
    try {
        await pageBuilder.saveDraft((type, message) => {
            saveStatusType.value = type
        })
    } catch (error) {
        saveStatusType.value = 'error'
    }
}

function handlePublish() {
    if (props.isPublished) {
        return warnUserPublished()
    }

    return pageBuilder.publish()
        .then(() => navigateTo({ path: '/published-success' }))
        .catch(showError)
}

// Initialize 
onMounted(async () => {
    if (props.draftId) {
        loading.value = true
        try {
            const loadedBlocks = await pageBuilder.initialize(props.draftId)
            blocks.value = loadedBlocks
        } catch (error) {
            showError()
        } finally {
            loading.value = false
        }
    }
})
</script>

<template>
    <div class="layout-wrapper">
        <!-- Control Sidebar -->
        <aside class="control-sidebar">
            <BlockSelector 
                @add="handleBlockAdd"
                :block-types="availableBlockTypes" />
            
            <AIInterface 
                v-if="showAIPanel"
                :service="aiGenerator" 
                @generated-content="handleGeneratedContent" />
        </aside>

        <!-- Main Content Area -->
        <main class="droppable-area" @dragover.prevent @drop="handleBlockDrop">
            <ContentPreview v-if="false" :blocks="blocks" />
            
            <div class="block-container">
                <DraggableRenderableBlock
                    v-for="(block, index) in blocks"
                    :key="block.uid" 
                    :position="index"
                    :block="block"
                    @remove="pageBuilder.removeBlock"
                    @drag-start="setDragItem(index)"
                    @drag-end="handleDragEnd(index)"
                    @update-content="pageBuilder.updateBlock(index, $event)" />
            </div>
            
            <EmptyState 
                v-if="blocks.length === 0"
                :is-loading="loading" /> 
        </main>

        <!-- Actions Bar -->
        <footer class="footer-bar">
            <StatusMessage :type="saveStatusType" />

            <div class="action-group space-x-2">
                <ResetButton @click="pageBuilder.clearAll()" />
                
                <SaveButton 
                    :editing="!!draftId"
                    @click="handleSave()" />

                <PublishButton 
                    :is-published="isPublished"
                    @click="handlePublish()" />
            </div>
        </footer>
    </div>
</template>

<style scoped>
.layout-wrapper {
    display: grid;
    grid-template-columns: 270px 1fr;
    grid-template-rows: 1fr auto;
    min-height: 100vh;
}

.control-sidebar {
    background: white;
    border-right: 1px solid #e5e7eb;
    overflow-y: auto;
    padding: 1rem;
}

.droppable-area {
    min-height: calc(100vh - 80px);
    padding: 2rem;
    background-color: #f9fafb;
}

.footer-bar {
    grid-column: 1 / -1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background: white;
    border-top: 1px solid #e5e7eb;
}
</style>