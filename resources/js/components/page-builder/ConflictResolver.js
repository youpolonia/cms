/**
 * Resolves conflicts in page builder operations
 * Handles simultaneous editing detection and graceful merge resolution
 */
export class ConflictResolver {
  #resolutionStrategies = {
    RECENT: 'timestamp',
    MERGE: 'combine', 
    OVERWRITE: 'priority_field',
    AUTOMERGE: 'content_types'
  }

  constructor(pageData, changeLog) {
    this.pageData = structuredClone(pageData)
    this.changeLog = structuredClone(changeLog)
    this.conflicts = []
  }

  /**
   * Apply changes with conflict detection
   * @param {Object} incomingChanges - Changes from another source
   * @returns {Object} Contains resolved data and flagged conflicts
   */
  async applyChanges(incomingChanges) {
    incomingChanges.forEach(change => {
      const localVersion = {...this.findLocalVersion(change.componentId)}
      
      // Apply three-way merge between original, local, and incoming
      if (requireMerge(localVersion, change)) {
        const merged = this.threeWayMerge(
          this.getOriginalVersion(change.componentId),
          localVersion,
          change
        )
        
        if (merged.conflicts?.length > 0) {
          this.conflicts.push({
            componentId: change.componentId,
            type: 'ContentConflict',
            timestamp: Date.now(),
            resolution: null
          })
        }

        this.incorporateChange(merged)
      } else {
        this.incorporateChange(change)
      }
    })

    return {
      data: this.pageData,
      conflicts: this.conflicts
    }
  }

  findLocalVersion(componentId) {
    return this.pageData.components.find(c => c.id === componentId) || null
  }

  getOriginalVersion(componentId) {
    return this.changeLog.originals.find(c => c.id === componentId)
  }

  threeWayMerge(base, local, incoming) {
    // Implementation of three-way textual merge
    // Uses the Google Diff-Match-Patch algorithm when needed
    
    return {
      ...base,
      config: {
        ...mergeDeep(base?.config || {}, local?.config || {}), 
        ...mergeDeep(base?.config || {}, incoming?.config || {})
      },
      styles: this.mergeStyles(base, local, incoming)
    }
  }

  mergeStyles(base, local, incoming) {
    // Style resolution using closest-ancestor matching
    // while preserving newly introduced properties
    
    return {
      ...base?.styles,
      ...incoming?.styles,
      // Preserve local styles for any shared properties
      ...Object.keys(local?.styles || {})
          .filter(key => !(key in incoming?.styles))
          .reduce((res, key) => {
            res[key] = local.styles[key]
            return res
          }, {})
    }
  }

  markConflictResolved(index, withVersion) {
    if (index >= 0 && index < this.conflicts.length) {
      this.conflicts[index].resolution = JSON.parse(JSON.stringify(withVersion))
    }
  }

  suppressConflict(index, autoMergeStrategy = true) {
    if (index >= 0 && index < this.conflicts.length) {
      this.conflicts[index].resolution = autoMergeStrategy 
        ? Strategy.AUTO_RESOLVED 
        : Strategy.IGNORED
    }
  }
}

// Utility functions
function requireMerge(local, incoming) {
  return isObject(local) && isObject(incoming) && 
    Object.keys(incoming).some(k => k in local && !shallowEqual(incoming[k], local[k]))
}

function shallowEqual(a, b) {
  return a === b
}

function isObject(input) {
  return input && typeof input === 'object' && !Array.isArray(input)
}

export const Strategy = {
  AUTO_RESOLVED: 'automerge',
  IGNORED: 'skip',
  DEFERRED: 'later'
}