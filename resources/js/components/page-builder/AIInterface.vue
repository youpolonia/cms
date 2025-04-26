<script setup>
const props = defineProps({
    service: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['generated-content'])

const state = reactive({
    isGenerating: false,
    prompt: '',
    variants: [],
    selectedContentType: 'paragraph',
    activeTab: 'text',
    tone: 'professional'
})

const contentTypes = [
    { key: 'heading', label: 'Heading' },
    { key: 'paragraph', label: 'Paragraph' },
    { key: 'list', label: 'List' }
]

const tones = [
    { key: 'professional', label: 'Professional' },
    { key: 'friendly', label: 'Friendly' },
    { key: 'casual', label: 'Casual' }
]

async function generate() {
    if (!state.prompt.trim()) return
    
    state.isGenerating = true
    try {
        const result = await props.service.generate(
            state.prompt, 
            state.selectedContentType,
            state.tone
        )
        
        state.variants = Array.isArray(result) ? result : [result]
    } finally {
        state.isGenerating = false
    }
}

function handleInsert(content) {
    const blockType = state.activeTab === 'text' ? 'text' : 'html'
    
    emit('generated-content', {
        type: blockType,
        content: content
    })
}
</script>

<template>
    <div class="ai-interface space-y-4">
        <div>
            <label class="block text-sm font-medium mb-2">What should AI generate?</label>
            <textarea
                v-model="state.prompt"
                placeholder="Describe the content you want..."
                class="w-full px-3 py-2 bg-slate-50 rounded-md"
            ></textarea>
        </div>

        <div class="flex gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Content type</label>
                <select v-model="state.selectedContentType">
                    <option v-for="type in contentTypes" :value="type.key">
                        {{ type.label }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tone</label>
                <select v-model="state.tone">
                    <option v-for="t in tones" :value="t.key">
                        {{ t.label }}
                    </option>
                </select>
            </div>
        </div>

        <div class="space-x-4">
            <label>Type:</label>
            <label v-for="tab in ['text', 'html', 'image']" :key="tab"
                   class="radio-label py-1 px-3 rounded-md" 
                   :class="{ 'radio-label-active': activeTab === tab }">
                <input type="radio" v-model="state.activeTab" :value="tab">
                {{ tab }}
            </label>
        </div>

        <div v-if="state.isGenerating" class="text-center py-8">
            <LoadingSpinner />
        </div>
        
        <div v-else-if="state.variants.length" class="mt-2">
            <h4 class="text-sm font-medium mb-2">AI Suggestions</h4>
            
            <div v-for="(variant, i) in variants" class="variant-item mb-2">
                <DeterminateContent :content="variant" :type="activeTab" />
                
                <div class="flex justify-end gap-2">
                    <CopyButton @click="copyToClipboard(variant)" />
                    <InsertButton @click="handleInsert(variant)" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ai-interface {
    padding: 1rem;
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
}

.radio-label {
    border: 1px solid #e4e7eb;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.radio-label-active {
    @apply bg-indigo-100 text-indigo-700;
}

.variant-item {
    @apply p-4 border border-gray-100 rounded space-y-3;
}
</style>