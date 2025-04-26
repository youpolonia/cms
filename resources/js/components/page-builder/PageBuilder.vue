<template>
  <div class="page-builder">
    <BlockLockControl
      v-for="block in blocks"
      :key="`lock-${block.id}`"
      :block="block"
      @lock-toggle="handleLockToggle"
      v-show="!block.meta?.locked || isEditor"
    />
    <!-- Existing template content -->
  </div>
</template>

<script>
import BlockLockControl from './BlockLockControl.vue'

export default {
  components: {
    BlockLockControl
  },
  computed: {
    isEditor() {
      return this.currentUser?.permissions?.includes('edit-content') || false
    }
  },
  data() {
    return {
      collaborators: [],
      // ... existing data
    }
  },
  methods: {
    setupCollaboration() {
      this.collaborationChannel = window.Echo.join(`page-builder.${this.pageId}`)
        .here(users => {
          this.collaborators = users
        })
        .joining(user => {
          this.collaborators.push(user)
        })
        .leaving(user => {
          this.collaborators = this.collaborators.filter(u => u.id !== user.id)
        })
        .listen('BlockMoved', ({ blockId, x, y, userId }) => {
          // Skip updates from current user and for locked blocks
          const block = this.blocks.find(b => b.id === blockId)
          if (block && userId !== this.currentUser.id && !block.meta?.locked) {
            block.x = x
            block.y = y
          }
        })
        .listen('BlockLocked', ({ blockId, locked }) => {
          const block = this.blocks.find(b => b.id === blockId)
          if (block) {
            this.$set(block.meta, 'locked', locked)
          }
        }),
    handleDragStart(block) {
      if (block.meta?.locked && !this.isEditor) {
        return false // Prevent dragging locked blocks for non-editors
      }
      // Existing drag start logic
    },
    handleDragEnd(block) {
      if (!block.meta?.locked || this.isEditor) {
        this.broadcastBlockMove(block)
      }
      // Existing drag end logic
    },
    broadcastBlockMove(block) {
      if (this.collaborationChannel && (!block.meta?.locked || this.isEditor)) {
        this.collaborationChannel.whisper('BlockMoved', {
          blockId: block.id,
          x: block.x,
          y: block.y,
          userId: this.currentUser.id
        })
      }
    }
          const block = this.blocks.find(b => b.id === blockId)
          if (block) {
            block.x = x
            block.y = y
          }
        })
        // Other event listeners...
    },
    broadcastBlockMove(block) {
      if (this.collaborationChannel) {
        this.collaborationChannel.whisper('BlockMoved', {
          blockId: block.id,
          x: block.x,
          y: block.y
        })
      }
    },
    handleLockToggle({ blockId, locked }) {
      const block = this.blocks.find(b => b.id === blockId)
      if (block) {
        this.$set(block.meta, 'locked', locked)
        
        if (this.collaborationChannel) {
          this.collaborationChannel.whisper('BlockLocked', {
            blockId,
            locked,
            userId: this.currentUser.id
          })
        }
      }
    },
    
    // Update handleDragEnd to broadcast moves
    handleDragEnd(block) {
      this.broadcastBlockMove(block)
      // ... existing logic
    }
  },
  mounted() {
    this.setupCollaboration()
    // ... existing mounted logic
  },
  beforeUnmount() {
    if (this.collaborationChannel) {
      this.collaborationChannel.leave()
    }
    // ... existing cleanup
  }
}
</script>