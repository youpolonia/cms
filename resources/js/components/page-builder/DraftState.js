export class DraftManager {
  constructor() {
    this.states = {
      CLEAN: 0,
      DIRTY: 1,
      PENDING: 2,
      CONFLICT: 3
    }
    this.currentState = this.states.CLEAN
    this.pendingChanges = []
  }

  logStateChange(newState) {
    const timestamp = new Date().toISOString()
    console.log(`${timestamp}|DraftState| ${this.getStateName(newState)}`)
  }

  registerChange(change) {
    this.pendingChanges.push(change)
    this.transitionState(this.states.DIRTY)
  }

  transitionState(newState) {
    this.logStateChange(newState)
    this.currentState = newState
  }

  saveDraft() {
    if (this.currentState === this.states.CLEAN) {
      return false
    }

    this.transitionState(this.states.PENDING)
    return true
  }  

  resolveConflicts() {
    this.transitionState(this.states.DIRTY)
  }

  getStateName(state) {
    return Object.keys(this.states).find(key => this.states[key] === state)
  }

  syncDrafts(serverTimestamps = []) {
    this.removeCommitsConsistentAt(serverTimestamps)
    if (this.pendingChanges.length === 0) {
      this.transitionState(this.states.CLEAN)
    }
    return true
  }

  resetDrafts() {
    this.clearLocalCommitIdentifiers()
    this.pendingChanges = []
    this.transitionState(this.states.CLEAN)
  }
}