<template>
  <div class="text-settings">
    <div class="form-group">
      <label>Text Content</label>
      <textarea
        v-model="localContent"
        @input="updateContent"
        rows="4"
      ></textarea>
    </div>
    
    <div class="form-group">
      <label>Text Color</label>
      <input 
        type="color" 
        v-model="localContent.textColor"
        @change="updateContent"
      >
    </div>
    
    <div class="form-group">
      <label>Background Color</label>
      <input 
        type="color" 
        v-model="localContent.backgroundColor"
        @change="updateContent"
      >
    </div>
    
    <div class="form-group">
      <label>Font Size</label>
      <select v-model="localContent.fontSize" @change="updateContent">
        <option value="12px">Small</option>
        <option value="16px">Medium</option>
        <option value="20px">Large</option>
        <option value="24px">Extra Large</option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    content: {
      type: String,
      default: ''
    },
    textColor: {
      type: String,
      default: '#000000'
    },
    backgroundColor: {
      type: String,
      default: 'transparent'
    },
    fontSize: {
      type: String,
      default: '16px'
    }
  },
  data() {
    return {
      localContent: {
        content: this.content,
        textColor: this.textColor,
        backgroundColor: this.backgroundColor,
        fontSize: this.fontSize
      }
    }
  },
  methods: {
    updateContent() {
      this.$emit('update', this.localContent)
    }
  }
}
</script>

<style scoped>
.text-settings {
  padding: 10px 0;
}

textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  resize: vertical;
}

select {
  width: 100%;
  padding: 6px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>