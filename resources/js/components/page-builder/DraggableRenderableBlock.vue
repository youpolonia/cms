<script setup>
import { computed } from 'vue'
import BlockControls from './BlockControls.vue'

const props = defineProps({
    block: {
        type: Object,
        required: true
    },
    position: {
        type: Number,
        required: true  
    }
})

const emit = defineEmits([
    'update-content',
    'remove',
    'drag-start',  
    'drag-end'
])

function handleUpdate(content) {
    emit('update-content', content)
}

const components = {
    text: {
        component: 'TextBlockEditor',
        icon: 'text'
    },
    image: {
        component: 'ImageBlockEditor',
        icon: 'image'  
    },
    html: {
        component: 'HtmlCodeEditor', 
        icon: 'code'
    }
}

const currentRenderer = computed(() => {
    return components[props.block.type] || components.text
})
</script>

<template>
    <div 
        draggable="true"  
        @dragstart="emit('drag-start', position)"
        @dragend="emit('drag-end')"
        class="relative block-container hover:shadow-sm"
    >
        <div class="flex min-h-[80px] relative">
            <!-- Block Left Handle -->
            <div 
                class="w-4 flex items-center justify-center cursor-move drag-handle hover:bg-blue-50"
                @mousedown="$emit('drag-start', position)"
                @mouseup="$emit('drag-end')"
            >
                <Icon name="drag" size="16" />
            </div>

            <!-- Block Content Area -->
            <div class="flex-1">
                <component 
                    :is="currentRenderer.component"
                    :content="block.content"
                    :type="block.type"
                    @update="handleUpdate"
                />
            </div>

            <!-- Block Controls -->  
            <div class="absolute right-0 top-0 z-10">
                <BlockControls 
                    :type="block.type"
                    @remove="$emit('remove', position)"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.block-container {
    @apply my-2 bg-white border border-gray-200 rounded;
}

.block-container:hover {
    @apply border-blue-200;
}

.drag-handle {
    user-select: none;
    @apply border-r border-gray-200 text-grayField-500;
}
</style>