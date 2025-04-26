import { ref } from 'vue'

export class ResponsiveService {
  constructor() {
    this.breakpoints = ref([
      { name: 'Mobile', width: 375, icon: 'phone' },
      { name: 'Tablet', width: 768, icon: 'tablet' },
      { name: 'Desktop', width: 1024, icon: 'monitor' }
    ])
    this.activeBreakpoint = ref('Desktop')
    this.isMobileFirst = ref(true)
  }

  getActiveBreakpoint() {
    return this.breakpoints.value.find(bp => bp.name === this.activeBreakpoint.value)
  }

  setBreakpoint(name) {
    this.activeBreakpoint.value = name
  }

  toggleMobileFirst() {
    this.isMobileFirst.value = !this.isMobileFirst.value
  }

  getBlockStyles(block, breakpointName) {
    return block.responsiveStyles?.[breakpointName] || {}
  }

  updateBlockStyles(block, styles, breakpointName) {
    if (!block.responsiveStyles) {
      block.responsiveStyles = {}
    }
    block.responsiveStyles[breakpointName] = styles
  }
}