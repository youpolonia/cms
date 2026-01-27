<template>
  <div class="conflict-dialog" v-if="show">
    <div class="dialog-content">
      <h3>Content Edit Conflict</h3>
      <p>This content was modified by {{ modifiedBy }} while you were editing.</p>
      
      <div class="version-comparison">
        <div class="version">
          <h4>Your Changes</h4>
          <pre>{{ yourChanges }}</pre>
        </div>
        <div class="version">
          <h4>Current Version</h4>
          <pre>{{ currentVersion }}</pre>
        </div>
      </div>

      <div class="actions">
        <button @click="keepMine" class="btn-primary">Keep My Changes</button>
        <button @click="keepTheirs" class="btn-secondary">Keep Their Version</button>
        <button @click="merge" class="btn-tertiary">Merge Changes</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: {
      type: Boolean,
      required: true
    },
    modifiedBy: {
      type: String,
      required: true  
    },
    yourChanges: {
      type: String,
      required: true
    },
    currentVersion: {
      type: String,
      required: true
    }
  },
  methods: {
    keepMine() {
      this.$emit('resolve', 'keep_mine');
    },
    keepTheirs() {
      this.$emit('resolve', 'keep_theirs');  
    },
    merge() {
      this.$emit('resolve', 'merge');
    }
  }
}
</script>

<style scoped>
.conflict-dialog {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.dialog-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  max-width: 800px;
  width: 90%;
}

.version-comparison {
  display: flex;
  gap: 20px;
  margin: 20px 0;
}

.version {
  flex: 1;
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 4px;
}

pre {
  white-space: pre-wrap;
  background: #f8f8f8;
  padding: 10px;
  border-radius: 4px;
}

.actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}
</style>