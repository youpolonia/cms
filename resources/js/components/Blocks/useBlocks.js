import { ref, computed } from 'vue';
import { useToast } from 'vue-toast-notification';
import { useSortable } from '@vueuse/integrations/useSortable';

export default function useBlocks(pageId, initialBlocks = []) {
  const templates = ref([]);
  const blocks = ref(initialBlocks);
  const toast = useToast();

  const addBlock = async (type) => {
    try {
      const newBlock = {
        id: `new-${Date.now()}`,
        type,
        content: {}
      };
      
      blocks.value.push(newBlock);
      return newBlock;
    } catch (error) {
      console.error('Error adding block:', error);
      toast.error('Failed to add block');
    }
  };

  const updateBlock = (updatedBlock) => {
    const index = blocks.value.findIndex(b => b.id === updatedBlock.id);
    if (index !== -1) {
      blocks.value[index] = updatedBlock;
    }
  };

  const deleteBlock = async (blockId) => {
    try {
      await fetch(`/api/pages/${pageId}/blocks/${blockId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      });
      
      blocks.value = blocks.value.filter(b => b.id !== blockId);
      toast.success('Block deleted successfully');
    } catch (error) {
      console.error('Error deleting block:', error);
      toast.error('Failed to delete block');
    }
  };

  const reorderBlocks = async (newOrder) => {
    try {
      await fetch(`/api/pages/${pageId}/blocks/reorder`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ order: newOrder })
      });
      
      // Update local blocks order
      blocks.value = newOrder.map(id => 
        blocks.value.find(b => b.id === id)
      ).filter(Boolean);
    } catch (error) {
      console.error('Error reordering blocks:', error);
      toast.error('Failed to reorder blocks');
    }
  };

  // Setup drag and drop sorting
  const containerRef = ref(null);
  useSortable(containerRef, blocks, {
    animation: 150,
    handle: '.block-handle',
    onEnd: ({ newIndex, oldIndex }) => {
      const newOrder = [...blocks.value];
      const [removed] = newOrder.splice(oldIndex, 1);
      newOrder.splice(newIndex, 0, removed);
      reorderBlocks(newOrder.map(b => b.id));
    }
  });

  const saveTemplate = async (templateName) => {
    try {
      const response = await fetch('/api/block-templates', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          name: templateName,
          blocks: blocks.value
        })
      });
      
      const data = await response.json();
      templates.value.push(data);
      toast.success('Template saved successfully');
      return data;
    } catch (error) {
      console.error('Error saving template:', error);
      toast.error('Failed to save template');
    }
  };

  const loadTemplate = async (templateId) => {
    try {
      const response = await fetch(`/api/block-templates/${templateId}`);
      const data = await response.json();
      blocks.value = data.blocks;
      toast.success('Template loaded successfully');
      return data.blocks;
    } catch (error) {
      console.error('Error loading template:', error);
      toast.error('Failed to load template');
    }
  };

  const getTemplates = async () => {
    try {
      const response = await fetch('/api/block-templates');
      templates.value = await response.json();
      return templates.value;
    } catch (error) {
      console.error('Error fetching templates:', error);
      toast.error('Failed to fetch templates');
    }
  };

  const exportBlock = (block) => {
    const dataStr = JSON.stringify(block, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportName = `block-${block.type}-${Date.now()}.json`;
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportName);
    linkElement.click();
  };

  const importBlock = async (file) => {
    return new Promise((resolve, reject) => {
      const fileReader = new FileReader();
      
      fileReader.onload = (event) => {
        try {
          const blockData = JSON.parse(event.target.result);
          const newBlock = {
            ...blockData,
            id: `imported-${Date.now()}`
          };
          blocks.value.push(newBlock);
          toast.success('Block imported successfully');
          resolve(newBlock);
        } catch (error) {
          toast.error('Invalid block file');
          reject(error);
        }
      };
      
      fileReader.onerror = (error) => {
        toast.error('Error reading file');
        reject(error);
      };
      
      fileReader.readAsText(file);
    });
  };

  return {
    blocks,
    templates,
    containerRef,
    addBlock,
    updateBlock,
    deleteBlock,
    reorderBlocks,
    saveTemplate,
    loadTemplate,
    getTemplates,
    exportBlock,
    importBlock
  };
}