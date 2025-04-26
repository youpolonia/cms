import { ref } from 'vue'
import Echo from 'laravel-echo'

export class CollaborationService {
  constructor(pageId) {
    this.pageId = pageId
    this.users = ref([])
    this.lockedBlocks = ref({})
    this.echo = new Echo({
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
      forceTLS: true
    })

    this.subscribeToChannels()
  }

  subscribeToChannels() {
    this.echo.join(`page.${this.pageId}`)
      .here(users => this.users.value = users)
      .joining(user => this.users.value.push(user))  
      .leaving(user => {
        this.users.value = this.users.value.filter(u => u.id !== user.id)
        this.releaseUserLocks(user.id)
      })

    this.echo.listen('BlockLocked', ({userId, blockId}) => {
      this.lockedBlocks.value[blockId] = userId
    })

    this.echo.listen('BlockReleased', (blockId) => {
      delete this.lockedBlocks.value[blockId]
    })
  }

  lockBlock(blockId) {
    if (!this.isBlockLocked(blockId)) {
      this.echo.private(`page.${this.pageId}`).whisper('BlockLocked', {
        userId: this.currentUser.id,
        blockId  
      })
      return true
    }
    return false
  }

  releaseBlock(blockId) {
    this.echo.private(`page.${this.pageId}`).whisper('BlockReleased', {blockId})
  }

  releaseUserLocks(userId) {
    Object.keys(this.lockedBlocks.value).forEach(blockId => {
      if (this.lockedBlocks.value[blockId] === userId) {
        delete this.lockedBlocks.value[blockId]
      }
    })
  }

  isBlockLocked(blockId) {
    return !!this.lockedBlocks.value[blockId] && 
      this.lockedBlocks.value[blockId] !== this.currentUser.id
  }

  getOnlineUsers() {
    return [...this.users.value]
  }

  destroy() {
    this.echo.leave(`page.${this.pageId}`)
    this.releaseAllBlocks()
  }
}