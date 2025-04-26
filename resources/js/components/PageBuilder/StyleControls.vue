<template>
  <div class="style-controls">
    <h4>Block Styling</h4>
    
    <div class="control-group">
      <label>Background Color</label>
      <input type="color" v-model="styles.backgroundColor" @change="updateStyles">
    </div>

    <div class="control-group">
      <label>Text Color</label>
      <input type="color" v-model="styles.color" @change="updateStyles">
    </div>

    <div class="control-group">
      <label>Padding (px)</label>
      <input type="number" v-model.number="styles.padding" @input="updateStyles">
    </div>

    <div class="control-group">
      <label>Margin (px)</label>
      <input type="number" v-model.number="styles.margin" @input="updateStyles">
    </div>

    <div class="control-group">
      <label>Border Radius (px)</label>
      <input type="number" v-model.number="styles.borderRadius" @input="updateStyles">
    </div>

    <div class="control-group">
      <label>Border Width (px)</label>
      <input type="number" v-model.number="styles.borderWidth" @input="updateStyles">
    </div>

    <div class="control-group">
      <label>Border Color</label>
      <input type="color" v-model="styles.borderColor" @change="updateStyles">
    </div>
  </div>
</template>

<script>
export default {
  props: {
    block: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      styles: {
        backgroundColor: this.block.styles?.backgroundColor || '#ffffff',
        color: this.block.styles?.color || '#000000',
        padding: this.block.styles?.padding || 10,
        margin: this.block.styles?.margin || 0,
        borderRadius: this.block.styles?.borderRadius || 0,
        borderWidth: this.block.styles?.borderWidth || 0,
        borderColor: this.block.styles?.borderColor || '#000000'
      }
    }
  },

  methods: {
    updateStyles() {
      this.$emit('update', { 
        ...this.block,
        styles: { ...this.styles }
      });
    }
  }
}
</script>

<style scoped>
.style-controls {
  padding: 15px;
  background: #f5f5f5;
  border-radius: 5px;
  margin-top: 20px;
}

.control-group {
  margin-bottom: 15px;
}

.control-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.control-group input[type="color"] {
  width: 100%;
  height: 40px;
}

.control-group input[type="number"] {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>