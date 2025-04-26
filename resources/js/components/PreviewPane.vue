<template>
  <div class="border rounded p-4 bg-gray-50">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium">Content Preview</h3>
      <div class="flex flex-wrap gap-2 items-center">
        <button
          @click="previewMode = 'desktop'"
          class="px-3 py-1 text-sm rounded"
          :class="{'bg-blue-500 text-white': previewMode === 'desktop', 'bg-gray-200': previewMode !== 'desktop'}"
        >
          Desktop
        </button>
        <button
          @click="previewMode = 'tablet'"
          class="px-3 py-1 text-sm rounded"
          :class="{'bg-blue-500 text-white': previewMode === 'tablet', 'bg-gray-200': previewMode !== 'tablet'}"
        >
          Tablet
        </button>
        <button
          @click="previewMode = 'mobile'"
          class="px-3 py-1 text-sm rounded"
          :class="{'bg-blue-500 text-white': previewMode === 'mobile', 'bg-gray-200': previewMode !== 'mobile'}"
        >
          Mobile
        </button>
        <button
          v-if="previewMode !== 'desktop'"
          @click="toggleOrientation"
          class="px-3 py-1 text-sm rounded bg-gray-200"
        >
          {{ isLandscape ? 'Portrait' : 'Landscape' }}
        </button>
        <button
          @click="toggleDarkMode"
          class="px-3 py-1 text-sm rounded"
          :class="{'bg-gray-800 text-white': darkMode, 'bg-gray-200': !darkMode}"
        >
          {{ darkMode ? 'Light' : 'Dark' }}
        </button>
        <div class="flex items-center space-x-1">
          <button
            @click="zoomOut"
            class="px-2 py-1 text-sm rounded bg-gray-200"
            :disabled="zoomLevel <= 0.5"
          >
            -
          </button>
          <span class="text-sm w-12 text-center">{{ Math.round(zoomLevel * 100) }}%</span>
          <button
            @click="zoomIn"
            class="px-2 py-1 text-sm rounded bg-gray-200"
            :disabled="zoomLevel >= 2"
          >
            +
          </button>
          <button
            @click="resetZoom"
            class="px-2 py-1 text-sm rounded bg-gray-200 ml-2"
            :disabled="zoomLevel === 1"
          >
            Reset
          </button>
        </div>
        <select
          v-model="networkSpeed"
          class="px-3 py-1 text-sm rounded bg-gray-200"
          @change="simulateNetworkSpeed"
        >
          <option
            v-for="speed in networkSpeeds"
            :key="speed.value"
            :value="speed.value"
          >
            {{ speed.label }}
          </option>
        </select>
        <button
          @click="toggleFullscreen"
          class="ml-auto px-3 py-1 text-sm rounded bg-gray-200"
        >
          {{ isFullscreen ? 'Exit Fullscreen' : 'Fullscreen' }}
        </button>
        <button
          @click="captureScreenshot"
          class="px-3 py-1 text-sm rounded bg-gray-200"
        >
          Screenshot
        </button>
      </div>
    </div>
    <div
      ref="previewContainer"
      class="space-y-4 transition-all duration-200 origin-top relative"
      :class="{
        'max-w-md mx-auto border-8 border-black rounded-3xl': previewMode === 'mobile' && !isFullscreen && !isLandscape,
        'max-h-md mx-auto border-8 border-black rounded-3xl': previewMode === 'mobile' && !isFullscreen && isLandscape,
        'max-w-2xl mx-auto border-8 border-black rounded-3xl': previewMode === 'tablet' && !isFullscreen && !isLandscape,
        'max-h-2xl mx-auto border-8 border-black rounded-3xl': previewMode === 'tablet' && !isFullscreen && isLandscape,
        'bg-gray-900 text-gray-100': darkMode,
        'bg-white text-gray-800': !darkMode,
        'fixed inset-0 z-50 bg-white/90 dark:bg-gray-900/90 p-4 overflow-auto backdrop-blur-sm transition-opacity duration-200': isFullscreen
      }"
      :style="{
        transform: isFullscreen ? 'none' : `scale(${zoomLevel})`,
        'transform-origin': isFullscreen ? 'center' : 'top center'
      }"
    >
      <div class="absolute top-4 right-4 z-50 flex items-center space-x-2">
        <div class="flex space-x-2">
          <span class="text-sm bg-white/80 dark:bg-gray-800/80 px-2 py-1 rounded-md shadow-sm">
            ESC: exit
          </span>
          <span class="text-sm bg-white/80 dark:bg-gray-800/80 px-2 py-1 rounded-md shadow-sm">
            +/-: zoom
          </span>
          <span class="text-sm bg-white/80 dark:bg-gray-800/80 px-2 py-1 rounded-md shadow-sm">
            D: dark
          </span>
          <span class="text-sm bg-white/80 dark:bg-gray-800/80 px-2 py-1 rounded-md shadow-sm">
            M: mobile
          </span>
          <span class="text-sm bg-white/80 dark:bg-gray-800/80 px-2 py-1 rounded-md shadow-sm">
            T: tablet
          </span>
        </div>
        <button
          @click="toggleFullscreen"
          class="p-2 rounded-full bg-white/80 dark:bg-gray-800/80 hover:bg-white dark:hover:bg-gray-700 shadow-lg transition-colors"
          aria-label="Exit fullscreen"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div v-for="(block, index) in blocks" :key="index" class="border-b pb-4">
        <div v-if="block.type === 'text'" v-html="block.content"></div>
        <img v-if="block.type === 'image'" :src="block.content" class="max-w-full h-auto">
        <div v-if="block.type === 'video'" class="aspect-w-16 aspect-h-9">
          <iframe :src="block.content" class="w-full" frameborder="0" allowfullscreen></iframe>
        </div>

        <div v-if="block.type === 'columns'" class="grid grid-cols-2 gap-4">
          <div class="border p-2" v-html="block.content[0]"></div>
          <div class="border p-2" v-html="block.content[1]"></div>
        </div>
      </div>
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
  },
  data() {
    return {
      previewMode: 'desktop',
      darkMode: false,
      zoomLevel: 1,
      isFullscreen: false,
      touchStartX: 0,
      isLandscape: false,
      networkSpeed: 'online',
      networkSpeeds: [
        { value: 'online', label: 'Online' },
        { value: 'fast3g', label: 'Fast 3G' },
        { value: 'slow3g', label: 'Slow 3G' },
        { value: 'offline', label: 'Offline' }
      ]
    }
  },
  methods: {
    zoomIn() {
      this.zoomLevel = Math.min(2, this.zoomLevel + 0.1)
      this.savePreferences()
    },
    zoomOut() {
      this.zoomLevel = Math.max(0.5, this.zoomLevel - 0.1)
      this.savePreferences()
    },
    toggleDarkMode() {
      this.darkMode = !this.darkMode
      this.savePreferences()
    },
    simulateNetworkSpeed() {
      // This would integrate with a network throttling API in a real implementation
      console.log(`Simulating network speed: ${this.networkSpeed}`)
    },
    async captureScreenshot() {
      try {
        const canvas = await html2canvas(this.$refs.previewContainer, {
          scale: 2,
          logging: false,
          useCORS: true,
          allowTaint: true
        })
        
        const link = document.createElement('a')
        link.download = `screenshot-${new Date().toISOString().slice(0,10)}.png`
        link.href = canvas.toDataURL('image/png')
        link.click()
      } catch (error) {
        console.error('Error capturing screenshot:', error)
      }
    },
    savePreferences() {
      localStorage.setItem('previewPanePrefs', JSON.stringify({
        darkMode: this.darkMode,
        previewMode: this.previewMode,
        zoomLevel: this.zoomLevel,
        isLandscape: this.isLandscape,
        networkSpeed: this.networkSpeed
      }))
    },
    toggleOrientation() {
      this.isLandscape = !this.isLandscape
      this.savePreferences()
    },
    loadPreferences() {
      const prefs = localStorage.getItem('previewPanePrefs')
      if (prefs) {
        try {
          const { darkMode, previewMode, zoomLevel, isLandscape, networkSpeed } = JSON.parse(prefs)
          this.darkMode = darkMode
          this.previewMode = previewMode || 'desktop'
          this.zoomLevel = zoomLevel || 1
          this.isLandscape = isLandscape || false
          this.networkSpeed = networkSpeed || 'online'
        } catch (e) {
          console.error('Failed to load preferences', e)
        }
      }
    },
    zoomIn() {
      this.zoomLevel = Math.min(this.zoomLevel + 0.1, 2)
    },
    zoomOut() {
      this.zoomLevel = Math.max(this.zoomLevel - 0.1, 0.5)
    },
    resetZoom() {
      this.zoomLevel = 1
    },
    toggleFullscreen() {
      this.isFullscreen = !this.isFullscreen
      if (this.isFullscreen) {
        document.body.style.overflow = 'hidden'
        document.body.style.transition = 'all 0.3s ease'
      } else {
        document.body.style.overflow = ''
        document.body.style.transition = 'all 0.3s ease'
      }
      this.savePreferences()
    },
    handleKeyDown(e) {
      if (e.key === 'Escape' && this.isFullscreen) {
        this.toggleFullscreen()
      } else if (e.key === '+' || e.key === '=') {
        this.zoomLevel = Math.min(2, this.zoomLevel + 0.1)
      } else if (e.key === '-' || e.key === '_') {
        this.zoomLevel = Math.max(0.5, this.zoomLevel - 0.1)
      } else if (e.key.toLowerCase() === 'd') {
        this.darkMode = !this.darkMode
      } else if (e.key.toLowerCase() === 't') {
        this.previewMode = this.previewMode === 'tablet' ? 'desktop' : 'tablet'
      }
    },
    handleClickOutside(e) {
      if (this.isFullscreen && !this.$refs.previewContainer.contains(e.target)) {
        this.$refs.previewContainer.style.opacity = '0'
        setTimeout(() => this.toggleFullscreen(), 200)
      }
    },
    handleTouchStart(e) {
      this.touchStartX = e.touches[0].clientX
    },
    handleTouchMove(e) {
      if (!this.isFullscreen) return
      const touchX = e.touches[0].clientX
      const diff = this.touchStartX - touchX
      if (diff > 100) { // Swipe right to close
        this.$refs.previewContainer.style.transform = `translateX(${-diff}px)`
        this.$refs.previewContainer.style.opacity = `${1 - (diff / 300)}`
      }
    },
    handleTouchEnd(e) {
      if (!this.isFullscreen) return
      const touchX = e.changedTouches[0].clientX
      const diff = this.touchStartX - touchX
      if (diff > 150) { // Threshold to close
        this.toggleFullscreen()
      } else {
        this.$refs.previewContainer.style.transform = ''
        this.$refs.previewContainer.style.opacity = ''
      }
    }
  },
  mounted() {
    window.addEventListener('keydown', this.handleKeyDown)
    document.addEventListener('click', this.handleClickOutside)
    this.$refs.previewContainer.addEventListener('touchstart', this.handleTouchStart)
    this.$refs.previewContainer.addEventListener('touchmove', this.handleTouchMove)
    this.$refs.previewContainer.addEventListener('touchend', this.handleTouchEnd)
    this.loadPreferences()
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.handleKeyDown)
    document.removeEventListener('click', this.handleClickOutside)
    this.$refs.previewContainer.removeEventListener('touchstart', this.handleTouchStart)
    this.$refs.previewContainer.removeEventListener('touchmove', this.handleTouchMove)
    this.$refs.previewContainer.removeEventListener('touchend', this.handleTouchEnd)
  }
}
</script>