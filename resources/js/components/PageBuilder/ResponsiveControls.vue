<template>
  <div class="responsive-controls">
    <div class="breakpoint-selector">
      <button 
        v-for="breakpoint in breakpoints" 
        :key="breakpoint.name"
        :class="{ active: activeBreakpoint === breakpoint.name }"
        @click="setBreakpoint(breakpoint)"
      >
        {{ breakpoint.label }}
      </button>
    </div>

    <div class="device-preview">
      <div 
        class="preview-container" 
        :style="{ width: activeBreakpointWidth }"
      >
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      activeBreakpoint: 'desktop',
      breakpoints: [
        { name: 'mobile', label: 'Mobile', width: '375px' },
        { name: 'tablet', label: 'Tablet', width: '768px' },
        { name: 'desktop', label: 'Desktop', width: '100%' }
      ]
    }
  },

  computed: {
    activeBreakpointWidth() {
      const bp = this.breakpoints.find(b => b.name === this.activeBreakpoint);
      return bp ? bp.width : '100%';
    }
  },

  methods: {
    setBreakpoint(breakpoint) {
      this.activeBreakpoint = breakpoint.name;
      this.$emit('breakpoint-change', breakpoint);
    },

    getCurrentStyles() {
      return {
        breakpoint: this.activeBreakpoint,
        width: this.activeBreakpointWidth
      };
    }
  }
}
</script>

<style scoped>
.responsive-controls {
  margin: 20px 0;
  border: 1px solid #eee;
  padding: 15px;
  background: #f9f9f9;
}

.breakpoint-selector {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.breakpoint-selector button {
  padding: 5px 10px;
  border: 1px solid #ddd;
  background: white;
  cursor: pointer;
}

.breakpoint-selector button.active {
  background: #4CAF50;
  color: white;
  border-color: #4CAF50;
}

.device-preview {
  display: flex;
  justify-content: center;
}

.preview-container {
  border: 1px solid #ddd;
  background: white;
  overflow: hidden;
  transition: width 0.3s ease;
}
</style>