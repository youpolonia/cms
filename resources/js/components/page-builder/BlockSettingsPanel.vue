<template>
  <div class="settings-panel" v-if="block">
    <h3>Block Settings</h3>
    
    <div class="settings-group">
      <label>Position</label>
      <div class="position-controls">
        <div class="control-row">
          <span>X:</span>
          <input type="number" v-model.number="block.x" @change="updateBlock">
        </div>
        <div class="control-row">
          <span>Y:</span>
          <input type="number" v-model.number="block.y" @change="updateBlock">
        </div>
      </div>
    </div>

    <div class="settings-group">
      <label>Size</label>
      <div class="size-controls">
        <div class="control-row">
          <span>Width:</span>
          <input type="number" v-model.number="block.width" @change="updateBlock">
        </div>
        <div class="control-row">
          <span>Height:</span>
          <input type="number" v-model.number="block.height" @change="updateBlock">
        </div>
      </div>
    </div>

    <div class="settings-group" v-if="block.type === 'text'">
      <label>Text Content</label>
      <textarea v-model="block.text" @input="updateBlock"></textarea>
    </div>

    <div class="settings-group" v-if="block.type === 'image'">
      <label>Image Source</label>
      <input type="text" v-model="block.src" @input="updateBlock">
    </div>

    <button class="delete-button" @click="deleteBlock">Delete Block</button>
  </div>
</template>

<script>
export default {
  props: {
    block: {
      type: Object,
      default: null
    }
  },
  emits: ['update', 'delete'],
  methods: {
    updateBlock() {
      this.$emit('update', this.block)
    },
    deleteBlock() {
      this.$emit('delete', this.block)
    }
  }
}
</script>

<style scoped>
.settings-panel {
  padding: 16px;
  background: #fff;
  border-left: 1px solid #eee;
}

.settings-group {
  margin-bottom: 16px;
}

.settings-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.control-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
}

.control-row span {
  width: 50px;
}

input, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.delete-button {
  margin-top: 16px;
  padding: 8px 16px;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>