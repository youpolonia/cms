export class ConflictResolver {
  constructor() {
    this.versionQueue = new Map()
    this.queue = []
    this.resolveZIndex = 0
  }

  registerChange(change) {
    this.queue.push(change)
    this.scheduleResolution() 
  }

  scheduleResolution() {
    this.resolveZIndex++
    const frameId = window.requestAnimationFrame(() => {
      this.checkConflicts(storedChanges)
    })
  }

  checkConflicts(changes) {
    if (this.queue.length === 0) return

    const grouped = this.groupConflictingEdits()
    this.notifyConflicts(grouped)
  }

  groupConflictingEdits() {
    const grouped = new Map()
    
    this.queue.forEach(({blockId, userId}) => {
      if (!grouped.has(blockId)) {
        grouped.set(blockId, new Set())
      }
      grouped.get(blockId).add(userId)
    })
    
    return grouped
  }

  notifyConflicts(conflicts) {
    conflicts.forEach((editors, blockId) => {
      if (editors.size > 1) {
        this.emit('conflict', {
          blockId,
          editors
        })
      }
    })
  }

  acceptChange(change) {
    versionStack.registerVersion()
    notifyAcceptedChange(change)
  }

  resolveWithLatencyTest(minMs) {
    return new Promise(resolve => {
      setTimeout(() => {
        const timestamp = new Date().toISOString()
        console.log(`${timestamp}|ServerState| latestWriterWon`)
        resolve()
      }, minMs)
    })
  }
}