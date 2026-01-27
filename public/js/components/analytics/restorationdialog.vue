<template>
  <div class="restoration-dialog-overlay" v-if="visible">
    <div class="restoration-dialog">
      <h3>Confirm Version Restoration</h3>
      <p>Are you sure you want to restore version {{ version }}?</p>
      <div class="dialog-actions">
        <button class="cancel-btn" @click="$emit('cancel')">Cancel</button>
        <button class="confirm-btn" @click="$emit('confirm')">Confirm</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    visible: Boolean,
    version: [String, Number]
  }
}
</script>

<style scoped>
.restoration-dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.restoration-dialog {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
}
.dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}
.cancel-btn {
  padding: 0.5rem 1rem;
  background: #f5f5f5;
  border: 1px solid #ddd;
  cursor: pointer;
}
.confirm-btn {
  padding: 0.5rem 1rem;
  background: #dc3545;
  color: white;
  border: none;
  cursor: pointer;
}
</style>