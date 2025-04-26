<template>
  <div class="form-preview">
    <div class="preview-header">
      <span class="preview-title">{{ block.name || 'Contact Form' }}</span>
    </div>
    <div class="form-fields">
      <div class="form-field preview-input">
        <label>Name</label>
        <input type="text" disabled>
      </div>
      <div class="form-field preview-input">
        <label>Email</label>
        <input type="email" disabled>
      </div>
      <div class="form-field preview-input">
        <label>Message</label>
        <textarea disabled></textarea>
      </div>
      <button class="preview-submit" disabled>Submit</button>
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
  }
}
</script>

<style scoped>
.form-preview {
  border: 2px dashed #ddd;
  border-radius: 6px;
  padding: 1rem;
}

.preview-header {
  margin-bottom: 1rem;
}

.preview-title {
  font-weight: bold;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.preview-input {
  border: 1px solid #eee;
  border-radius: 4px;
  padding: 0.5rem;
  background: #fafafa;
}

.preview-input label {
  display: block;
  margin-bottom: 0.25rem;
  font-size: 0.9rem;
  color: #666;
}

.preview-input input,
.preview-input textarea {
  width: 100%;
  border: none;
  background: transparent;
}

.preview-submit {
  padding: 0.5rem 1rem;
  background: #e0e0e0;
  color: #999;
  border: none;
  border-radius: 4px;
  cursor: not-allowed;
}
</style>