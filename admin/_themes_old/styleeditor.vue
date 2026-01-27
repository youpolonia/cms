<template>
  <div class="style-editor">
    <div class="editor-header">
      <h3>Style Editor</h3>
      <div class="preset-selector">
        <label>Presets:</label>
        <select v-model="selectedPreset" @change="loadPreset">
          <option v-for="preset in presets" :key="preset.id" :value="preset.id">
            {{ preset.name }}
          </option>
        </select>
      </div>
    </div>

    <div class="editor-container">
      <div class="css-editor">
        <textarea v-model="customCss" @input="updatePreview"></textarea>
      </div>
      
      <div class="editor-actions">
        <button @click="saveStyles" class="btn-primary">Save Styles</button>
        <button @click="resetStyles" class="btn-secondary">Reset</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    themeId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      presets: [],
      selectedPreset: null,
      customCss: '',
      originalCss: ''
    }
  },
  methods: {
    async loadPresets() {
      const response = await fetch('/api/themes/presets');
      this.presets = await response.json();
    },
    async loadPreset() {
      if (!this.selectedPreset) return;
      
      const response = await fetch(`/api/themes/preset?id=${this.selectedPreset}`);
      const preset = await response.json();
      this.customCss = preset.css;
      this.originalCss = preset.css;
      this.updatePreview();
    },
    updatePreview() {
      this.$emit('css-updated', this.customCss);
    },
    async saveStyles() {
      await fetch('/api/themes/save-styles', {
        method: 'POST',
        body: JSON.stringify({
          themeId: this.themeId,
          css: this.customCss
        })
      });
    },
    resetStyles() {
      this.customCss = this.originalCss;
      this.updatePreview();
    }
  },
  mounted() {
    this.loadPresets();
  }
}
</script>

<style scoped>
.style-editor {
  margin-top: 30px;
  border: 1px solid #eee;
  padding: 20px;
  border-radius: 4px;
}
.editor-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}
.preset-selector {
  display: flex;
  align-items: center;
}
.preset-selector label {
  margin-right: 10px;
}
.css-editor textarea {
  width: 100%;
  height: 300px;
  padding: 10px;
  font-family: monospace;
  border: 1px solid #ddd;
  border-radius: 4px;
  resize: vertical;
}
.editor-actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
}
</style>