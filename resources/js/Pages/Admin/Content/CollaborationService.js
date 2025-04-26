import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export default class CollaborationService {
  constructor(pageId) {
    this.pageId = pageId;
    this.collaborators = [];
    this.echo = new Echo({
      broadcaster: 'pusher',
      key: process.env.MIX_PUSHER_APP_KEY,
      cluster: process.env.MIX_PUSHER_APP_CLUSTER,
      forceTLS: true,
      authEndpoint: '/broadcasting/auth'
    });

    this.channel = this.echo.private(`page.${this.pageId}`);
  }

  initialize() {
    this.listenForCollaborators();
    this.listenForBlockUpdates();
    this.listenForSelectionChanges();
    this.registerPresence();
  }

  registerPresence() {
    this.presenceChannel = this.echo.join(`presence.page.${this.pageId}`)
      .here((users) => {
        this.collaborators = users;
      })
      .joining((user) => {
        this.collaborators.push(user);
      })
      .leaving((user) => {
        this.collaborators = this.collaborators.filter(u => u.id !== user.id);
      });
  }

  listenForCollaborators() {
    this.channel.listen('.collaborator.joined', (data) => {
      console.log('Collaborator joined:', data.user);
    });

    this.channel.listen('.collaborator.left', (data) => {
      console.log('Collaborator left:', data.user);
    });
  }

  listenForBlockUpdates() {
    this.channel.listen('.block.updated', (data) => {
      this.onBlockUpdated(data.block);
    });

    this.channel.listen('.block.added', (data) => {
      this.onBlockAdded(data.block);
    });

    this.channel.listen('.block.removed', (data) => {
      this.onBlockRemoved(data.blockId);
    });
  }

  listenForSelectionChanges() {
    this.channel.listen('.selection.changed', (data) => {
      this.onSelectionChanged(data.userId, data.selection);
    });
  }

  broadcastBlockUpdate(block) {
    this.channel.whisper('block-updated', {
      block,
      userId: window.currentUser.id
    });
  }

  broadcastSelection(selection) {
    this.channel.whisper('selection-changed', {
      selection,
      userId: window.currentUser.id
    });
  }

  onBlockUpdated(block) {
    // Implement in parent component
  }

  onBlockAdded(block) {
    // Implement in parent component
  }

  onBlockRemoved(blockId) {
    // Implement in parent component
  }

  onSelectionChanged(userId, selection) {
    // Implement in parent component
  }

  destroy() {
    this.echo.leave(`presence.page.${this.pageId}`);
    this.echo.leave(`page.${this.pageId}`);
  }
}