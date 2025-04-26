import { ref, computed } from 'vue'

export function useHistory(initialState) {
  const history = ref([JSON.parse(JSON.stringify(initialState))])
  const currentIndex = ref(0)

  const currentState = computed(() => history.value[currentIndex.value])
  
  const canUndo = computed(() => currentIndex.value > 0)
  const canRedo = computed(() => currentIndex.value < history.value.length - 1)

  function undo() {
    if (canUndo.value) {
      currentIndex.value--
    }
  }

  function redo() {
    if (canRedo.value) {
      currentIndex.value++
    }
  }

  function onChange(newState) {
    // Remove any future states if we're not at the end
    if (currentIndex.value < history.value.length - 1) {
      history.value = history.value.slice(0, currentIndex.value + 1)
    }

    // Add new state to history
    history.value.push(JSON.parse(JSON.stringify(newState)))
    currentIndex.value = history.value.length - 1
  }

  return {
    state: currentState,
    undo,
    redo,
    canUndo,
    canRedo,
    onChange
  }
}