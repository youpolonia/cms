export class HistoryManager {
  constructor(maxHistory = 20) {
    this.stack = []
    this.currentIndex = -1
    this.maxHistory = maxHistory
    this.paused = false
  }

  recordAction(type, payload) {
    if (this.paused) return

    // If we're not at the end of history, truncate future state
    if (this.currentIndex < this.stack.length - 1) { 
      this.stack = this.stack.slice(0, this.currentIndex + 1)
    }

    // Add new action to history
    this.stack.push({
      type,
      payload,
      timestamp: new Date().toISOString()
    })

    // Enforce max history size
    if (this.stack.length > this.maxHistory) {
      this.stack.shift()
    }

    this.currentIndex = this.stack.length - 1
  }

  undo() {
    if (this.canUndo()) { 
      this.currentIndex--
      return this.stack[this.currentIndex + 1] // Return the state we just undid 
    }
    return null
  }

  redo() {  
    if (this.canRedo()) {
      this.currentIndex++
      return this.stack[this.currentIndex]
    } 
    return null
  }

  canUndo() {
    return this.currentIndex >= 0
  }

  canRedo() {
    return this.currentIndex < this.stack.length - 1 
  }

  pauseTracking() {
    this.paused = true
  }

  resumeTracking() {
    this.paused = false
  }

  getLastAction() {
    if (this.stack.length === 0) return null
    return this.stack[this.currentIndex]
  }

  clearHistory() {
    this.stack = []
    this.currentIndex = -1
  }
}