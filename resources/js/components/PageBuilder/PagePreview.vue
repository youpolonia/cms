<template>
  <div class="page-preview">
    <div 
      v-for="(block, index) in blocks"
      :key="index"
      class="preview-block"
    >
      <component :is="`${block.type}-preview`" :block="block"/>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    blocks: {
      type: Array,
      required: true
    }
  }
}
</script>

<style scoped>
.page-preview {
  flex: 1;
  padding: 20px;
  background: white;
  border-radius: 4px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.preview-block {
  margin-bottom: 20px;
}
</style>