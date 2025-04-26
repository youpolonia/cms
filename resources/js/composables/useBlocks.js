import { ref } from 'vue';
import axios from 'axios';

export default function useBlocks(pageId, initialBlocks = []) {
    const blocks = ref(initialBlocks);
    const isLoading = ref(false);
    const error = ref(null);

    const fetchBlocks = async () => {
        try {
            isLoading.value = true;
            const response = await axios.get(`/api/pages/${pageId}/blocks`);
            blocks.value = response.data;
        } catch (err) {
            error.value = err;
        } finally {
            isLoading.value = false;
        }
    };

    const addBlock = async (type) => {
        try {
            const response = await axios.post('/api/blocks', {
                page_id: pageId,
                type,
                content: {}
            });
            blocks.value.push(response.data);
        } catch (err) {
            error.value = err;
        }
    };

    const updateBlock = async (block) => {
        try {
            await axios.put(`/api/blocks/${block.id}`, block);
            const index = blocks.value.findIndex(b => b.id === block.id);
            if (index !== -1) {
                blocks.value[index] = block;
            }
        } catch (err) {
            error.value = err;
        }
    };

    const deleteBlock = async (blockId) => {
        try {
            await axios.delete(`/api/blocks/${blockId}`);
            blocks.value = blocks.value.filter(b => b.id !== blockId);
        } catch (err) {
            error.value = err;
        }
    };

    const reorderBlocks = async (newOrder) => {
        try {
            await axios.post('/api/blocks/reorder', {
                page_id: pageId,
                blocks: newOrder.map((block, index) => ({
                    id: block.id,
                    order: index
                }))
            });
            blocks.value = newOrder;
        } catch (err) {
            error.value = err;
        }
    };

    const duplicateBlock = async (blockId) => {
        try {
            const { data } = await axios.post(`/api/blocks/${blockId}/duplicate`);
            blocks.value.push(data);
            return data;
        } catch (err) {
            error.value = err;
            throw err;
        }
    };

    return {
        blocks,
        isLoading,
        error,
        fetchBlocks,
        addBlock,
        updateBlock,
        deleteBlock,
        reorderBlocks,
        duplicateBlock
    };
}