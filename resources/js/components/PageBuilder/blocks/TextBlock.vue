<template>
  <div class="text-block" :style="block.styles">
    <textarea 
      v-model="block.content" 
      @input="emitUpdate"
      placeholder="Enter text content..."
    ></textarea>
    <div class="block-controls">
      <button @click="emitRemove">Remove</button>
      <input type="color" v-model="block.styles.color" @change="emitUpdate">
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
  methods: {
    emitUpdate() {
      this.$emit('update', this.block);
    },
    emitRemove() {
      this.$emit('remove', this.block.id);
    }
  }
};
</script>

<style scoped>
.text-block {
  margin: 10px 0;
  padding: 10px;
  border: 1px dashed #ccc;
  background: white;
}

textarea {
  width: 100%;
  min-height: 100px;
  padding: 8px;
  border: 1px solid #ddd;
}

.block-controls {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
}
</style>