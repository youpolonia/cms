import { createApp } from 'vue';
import BlocksList from './components/Blocks/BlocksList.vue';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';

document.addEventListener('DOMContentLoaded', () => {
    const blocksContainer = document.getElementById('blocks-container');
    if (!blocksContainer) return;

    // Initialize Vue app
    const app = createApp(BlocksList, {
        pageId: blocksContainer.dataset.pageId,
        initialBlocks: JSON.parse(blocksContainer.dataset.blocks || '[]')
    });

    // Configure toast notifications
    app.config.globalProperties.$toast = useToast({
        position: 'bottom-right',
        duration: 3000
    });

    // Mount the app
    app.mount(blocksContainer);
});