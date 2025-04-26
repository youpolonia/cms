<script setup>
defineProps({
    blockTypes: {
        type: Array,
        default: () => [
            { type: 'text', icon: 'text', label: 'Text' },
            { type: 'image', icon: 'image', label: 'Image' }, 
            // Other block types
        ]
    }
})

const emit = defineEmits(['add'])
</script>

<template>
    <div class="space-y-2">
        <h3 class="font-medium">Content Blocks</h3>
        
        <div class="grid grid-cols-2 gap-2">
            <button
                v-for="blockType in blockTypes"
                :key="blockType.type"
                @click="emit('add', blockType)"
                class="p-2 border rounded hover:bg-gray-50 transition"
            >
                <div class="flex items-center gap-2">
                    <Icon :name="blockType.icon" size="16" />
                    <span class="text-sm">{{ blockType.label }}</span>
                </div>
            </button>
        </div>

        <div class="pt-4">
            <button 
                @click="emit('add','ai')"
                class="w-full btn-gradient p-2 rounded-md text-white text-sm"
            >
                Generate with AI
            </button>
        </div>
    </div>
</template>

<style scoped>
.btn-gradient {
    background: linear-gradient(90deg, #4F46E5 0%, #7C3AED 100%);
    transition: all 0.2s ease;
}
.btn-gradient:hover {
    opacity: 0.9;
}
</style>