import { ref } from 'vue';
import axios from 'axios';
import { useDraggable } from '@vueuse/core';
import axios from 'axios';

export default function useBlocks(pageId) {
  const blocks = ref([]);
  const templates = ref([]);
  const containerRef = ref(null);

  const fetchBlocks = async () => {
    try {
      const response = await axios.get(`/api/pages/${pageId}/blocks`);
      blocks.value = response.data;
    } catch (error) {
      console.error('Error fetching blocks:', error);
    }
  };

  const addBlock = async (type) => {
    try {
      const response = await axios.post(`/api/pages/${pageId}/blocks`, {
        type,
        content: getDefaultContent(type),
        order: blocks.value.length
      });
      blocks.value.push(response.data);
    } catch (error) {
      console.error('Error adding block:', error);
    }
  };

  const updateBlock = async (updatedBlock) => {
    try {
      await axios.put(`/api/blocks/${updatedBlock.id}`, updatedBlock);
      const index = blocks.value.findIndex(b => b.id === updatedBlock.id);
      if (index !== -1) {
        blocks.value[index] = updatedBlock;
      }
    } catch (error) {
      console.error('Error updating block:', error);
    }
  };

  const deleteBlock = async (blockId) => {
    try {
      await axios.delete(`/api/blocks/${blockId}`);
      blocks.value = blocks.value.filter(b => b.id !== blockId);
    } catch (error) {
      console.error('Error deleting block:', error);
    }
  };

  const saveTemplate = async () => {
    try {
      await axios.post('/api/block-templates', {
        name: templateName.value,
        blocks: blocks.value
      });
      await getTemplates();
    } catch (error) {
      console.error('Error saving template:', error);
    }
  };

  const loadTemplate = async (templateId) => {
    try {
      const response = await axios.get(`/api/block-templates/${templateId}`);
      blocks.value = response.data.blocks;
    } catch (error) {
      console.error('Error loading template:', error);
    }
  };

  const getTemplates = async () => {
    try {
      const response = await axios.get('/api/block-templates');
      templates.value = response.data;
    } catch (error) {
      console.error('Error fetching templates:', error);
    }
  };

  const getDefaultContent = (type) => {
    const defaults = {
      text: { text: '', style: 'paragraph', bold: false, italic: false },
      image: { url: '', alt: '', width: '100%' },
      video: { url: '', width: '100%', autoplay: false }
    };
    return defaults[type] || {};
  };

  // Setup draggable functionality
  const { positions } = useDraggable(containerRef, {
    onEnd: async (positions) => {
      const updatedBlocks = positions.map((pos, index) => ({
        ...blocks.value[index],
        order: pos
      }));
      
      try {
        await axios.put(`/api/pages/${pageId}/blocks/reorder`, {
          blocks: updatedBlocks
        });
        blocks.value = updatedBlocks;
      } catch (error) {
        console.error('Error reordering blocks:', error);
      }
    }
  });

  return {
    blocks,
    templates,
    containerRef,
    addBlock,
    updateBlock,
    deleteBlock,
    saveTemplate,
    loadTemplate,
    getTemplates
  };
}