<template>
  <div class="theme-manager">
    <h2>Theme Management</h2>
    
    <div class="theme-actions">
      <button @click="exportTheme" class="btn btn-primary">
        Export Current Theme
      </button>
      
      <button @click="showImportDialog = true" class="btn btn-secondary">
        Import Theme
      </button>
      
      <input 
        type="file" 
        ref="importFile" 
        @change="handleFileSelect" 
        accept=".zip"
        style="display: none"
      >
    </div>

    <div v-if="showImportDialog" class="import-dialog">
      <h3>Import Theme</h3>
      <p>Select a theme zip file to import:</p>
      <input type="file" @change="handleFileSelect" accept=".zip">
      <button @click="importTheme" :disabled="!selectedFile">Import</button>
      <button @click="showImportDialog = false">Cancel</button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      showImportDialog: false,
      selectedFile: null
    }
  },
  methods: {
    exportTheme() {
      // Call backend export endpoint
      this.$emit('export-theme');
    },
    handleFileSelect(event) {
      this.selectedFile = event.target.files[0];
    },
    importTheme() {
      if (this.selectedFile) {
        this.$emit('import-theme', this.selectedFile);
        this.showImportDialog = false;
        this.selectedFile = null;
      }
    }
  }
}
</script>

<style scoped>
.theme-manager {
  padding: 20px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.theme-actions {
  display: flex;
  gap: 10px;
  margin-top: 15px;
}

.import-dialog {
  margin-top: 20px;
  padding: 15px;
  background: #f5f5f5;
  border-radius: 4px;
}
</style>