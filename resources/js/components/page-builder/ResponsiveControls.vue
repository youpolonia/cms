<template>
  <div class="responsive-controls">
    <div class="breakpoint-toggle">
      <button 
        v-for="bp in responsiveService.breakpoints" 
        :key="bp.name"
        :class="{ active: responsiveService.activeBreakpoint === bp.name }"
        @click="responsiveService.setBreakpoint(bp.name)"
      >
        <span class="icon">{{ bp.icon }}</span>
        {{ bp.name }}
      </button>
    </div>
    <div class="mobile-first-toggle">
      <label>
        <input 
          type="checkbox" 
          v-model="responsiveService.isMobileFirst"
          @change="responsiveService.toggleMobileFirst()"
        >
        Mobile First
      </label>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    responsiveService: {
      type: Object,
      required: true
    }
  }
}
</script>

<style scoped>
.responsive-controls {
  display: flex;
  gap: 20px;
  padding: 10px;
  background: #f5f5f5;
  border-radius: 5px;
  margin-bottom: 20px;
}

.breakpoint-toggle {
  display: flex;
  gap: 5px;
}

button {
  padding: 5px 10px;
  border: 1px solid #ddd;
  border-radius: 3px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
}

button.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.icon {
  font-family: 'Material Icons';
}

.mobile-first-toggle {
  display: flex;
  align-items: center;
}
</style>