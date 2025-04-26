import { createApp } from 'vue'
import { BlockEditor } from './components/BlockEditor'

document.addEventListener('DOMContentLoaded', () => {
    const editorEl = document.getElementById('page-builder-editor')
    if (editorEl) {
        const app = createApp({
            components: { BlockEditor },
            template: `<BlockEditor :initial-blocks="blocks" @update="handleUpdate" />`,
            data() {
                return {
                    blocks: JSON.parse(document.getElementById('blocks-input').value)
                }
            },
            methods: {
                handleUpdate(blocks) {
                    document.getElementById('blocks-input').value = JSON.stringify(blocks)
                },
                async generateContent() {
                    const prompt = prompt('Enter content generation prompt:')
                    if (prompt) {
                        const response = await fetch('/api/page-builder/generate-content', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ prompt })
                        })
                        const data = await response.json()
                        document.getElementById('ai-output').innerHTML = data.content
                    }
                },
                async suggestBlocks() {
                    const response = await fetch('/api/page-builder/suggest-blocks', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            currentBlocks: this.blocks
                        })
                    })
                    const data = await response.json()
                    document.getElementById('ai-output').innerHTML =
                        `Suggested blocks: <pre>${JSON.stringify(data.blocks, null, 2)}</pre>`
                }
            }
        }).mount(editorEl)
        
        // Add event listeners for AI buttons
        document.getElementById('generate-content-btn')?.addEventListener('click', () => {
            app._instance.proxy.generateContent()
        })
        
        document.getElementById('suggest-blocks-btn')?.addEventListener('click', () => {
            app._instance.proxy.suggestBlocks()
        })
    }
})