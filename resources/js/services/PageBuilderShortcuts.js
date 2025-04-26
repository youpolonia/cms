import { ref, onMounted, onUnmounted } from 'vue'

export function usePageBuilderShortcuts(pageBuilder) {
  const usingKeybinds = ref(false)

  const keyBindings = {
    'Tab': () => pageBuilder.focusNextBlock(),
    'Shift+Tab': () => pageBuilder.focusPreviousBlock(),
    'Delete': () => pageBuilder.deleteFocusedBlock(),
    'ArrowUp': () => pageBuilder.moveBlockUp(),
    'ArrowDown': () => pageBuilder.moveBlockDown(),
    'Alt+ArrowUp': () => pageBuilder.createPresetForCurrentBlock(),
    'Alt+p': () => pageBuilder.togglePresetPanel()
  }

  function handleKeyDown(e) {
    const keys = []
    if (e.ctrlKey) keys.push('Ctrl')
    if (e.metaKey) keys.push('Meta')  
    if (e.altKey) keys.push('Alt')
    if (e.shiftKey) keys.push('Shift')
    if (e.key !== 'Control' && e.key !== 'Meta' && e.key !== 'Alt' && e.key !== 'Shift') {
      keys.push(e.key)
    }

    const shortcut = keys.join('+')
    
    if (keyBindings[shortcut]) {
      e.preventDefault()
      keyBindings[shortcut]()
      return true
    }
    return false
  }

  function setupShortcuts() {
    window.addEventListener('keydown', handleKeyDown)
    usingKeybinds.value = true
  }

  function disableShortcuts() {
    window.removeEventListener('keydown', handleKeyDown)
    usingKeybinds.value = false
  }

  return {
    usingKeybinds,
    setupShortcuts,
    disableShortcuts
  }
}