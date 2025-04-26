<template>
  <div class="blocks-list">
    <div 
      v-for="block in blocks"
      :key="block.type"
      class="block-item"
      draggable="true"
      @dragstart="onDragStart(block.type)"
      @click="onBlockClick(block.type)"
    >
      <div class="block-icon">
        <component :is="block.icon" v-if="block.icon" />
        <span v-else>{{ block.type.charAt(0).toUpperCase() }}</span>
      </div>
      <div class="block-name">{{ block.name }}</div>
    </div>
  </div>
</template>

<script>
import AnimationManager from './AnimationManager'
import KeyboardShortcuts from './KeyboardShortcuts'

export default {
  mounted() {
    this.animationManager = new AnimationManager()
    this.animations = this.animationManager.getDefaultAnimations()
    this.initKeyboardNavigation()
  },
  beforeUnmount() {
    if (this.keyboardShortcuts) {
      this.keyboardShortcuts.destroy()
    }
  },
  emits: ['drag-start', 'block-selected'],
  data() {
    return {
      blocks: [
        {
          type: 'gallery',
          name: 'Image Gallery',
          icon: undefined
        },
        {
          type: 'form',
          name: 'Contact Form',
          icon: undefined
        },
        {
          type: 'code',
          name: 'Code Block',
          icon: undefined
        }
      ]
    }
  },
  methods: {
    onDragStart(blockType) {
      this.$emit('drag-start', blockType)
    },
    onBlockClick(blockType) {
      const blockElement = this.$el.querySelector(`[data-type="${blockType}"]`)
      this.animationManager.playAnimation('select', blockElement)
      
      const blockData = {
        ...this.blocks.find(b => b.type === blockType),
        preview: this.generatePreview(blockType),
        editorComponent: `${blockType}-editor`
      }
      this.$emit('block-selected', blockData)
    },
    initKeyboardNavigation() {
      this.keyboardShortcuts = new KeyboardShortcuts({
        'ArrowDown': () => this.navigateBlocks(1),
        'ArrowUp': () => this.navigateBlocks(-1),
        'Enter': () => {
          const block = this.$el.querySelector('.block-item-focused')
          if (block) {
            block.click()
          }
        }
      })
    },
    navigateBlocks(direction) {
      const focused = this.$el.querySelector('.block-item-focused')
      const blocks = [...this.$el.querySelectorAll('.block-item')]
      
      if (!focused) {
        blocks[0].classList.add('block-item-focused')
      } else {
        const index = blocks.indexOf(focused)
        const nextIndex = Math.min(Math.max(index + direction, 0), blocks.length - 1)
        focused.classList.remove('block-item-focused')
        blocks[nextIndex].classList.add('block-item-focused')
      }
    }
  },
  methods: {
    generatePreview(blockType) {
      const block = this.blocks.find(b => b.type === blockType)
      if (!block) {
        return { icon: '', label: '' }
      }
      
      return {
        icon: block.icon || `${blockType.charAt(0).toUpperCase()}`,
        label: `${blockType} block component`
      }
    }
  },
};
</script>

<style>
.blocks-list {
  width: 200px;
  padding: 10px;
  background: #f5f5f5;
  height: 100%;
  overflow-y: auto;
}

.block-item {
  padding: 10px;
  margin-bottom: 10px;
  background: white;
  border-radius: 4px;
  cursor: grab;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.block-item:active {
  cursor: grabbing;
}

.block-icon {
  width: 30px;
  height: 30px;
  background: #eee;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.block-item.highlight {
  animation: highlight 0.5s ease;
}

.block-item-focused {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
  position: relative;
  z-index: 1;
}

@keyframes highlight {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.block-name {
  font-weight: 500;
}
</style>