import AssistModal from './AssistModal.vue';

export default {
  install(Vue, options = {}) {
    Vue.directive('ai-context-menu', {
      bind(el, binding) {
        const contextMenu = document.createElement('div');
        contextMenu.className = 'ai-context-menu';
        contextMenu.style.display = 'none';
        document.body.appendChild(contextMenu);

        const menuItems = [
          { label: 'Generate Content', action: 'generate' },
          { label: 'Improve Text', action: 'improve' },
          { label: 'Summarize', action: 'summarize' }
        ];

        menuItems.forEach(item => {
          const menuItem = document.createElement('div');
          menuItem.className = 'ai-context-menu-item';
          menuItem.textContent = item.label;
          menuItem.addEventListener('click', () => {
            showAssistantModal(item.action, binding.value);
            hideContextMenu();
          });
          contextMenu.appendChild(menuItem);
        });

        function showContextMenu(e) {
          e.preventDefault();
          contextMenu.style.display = 'block';
          contextMenu.style.left = `${e.pageX}px`;
          contextMenu.style.top = `${e.pageY}px`;
          document.addEventListener('click', hideContextMenu);
        }

        function hideContextMenu() {
          contextMenu.style.display = 'none';
          document.removeEventListener('click', hideContextMenu);
        }

        function showAssistantModal(action, context) {
          const ModalComponent = Vue.extend(AssistModal);
          const modal = new ModalComponent({
            propsData: {
              title: `AI ${action.charAt(0).toUpperCase() + action.slice(1)}`,
              sidebar: action === 'summarize'
            }
          }).$mount();
          
          document.body.appendChild(modal.$el);
        }

        el.addEventListener('contextmenu', showContextMenu);
        
        el._contextMenu = contextMenu;
        el._showContextMenu = showContextMenu;
        el._hideContextMenu = hideContextMenu;
      },
      unbind(el) {
        el.removeEventListener('contextmenu', el._showContextMenu);
        document.body.removeChild(el._contextMenu);
      }
    });
  }
};

// Add styles for context menu
const style = document.createElement('style');
style.textContent = `
.ai-context-menu {
  position: absolute;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  z-index: 1000;
  min-width: 160px;
}

.ai-context-menu-item {
  padding: 8px 16px;
  cursor: pointer;
  transition: background 0.2s;
}

.ai-context-menu-item:hover {
  background: #f5f5f5;
}
`;
document.head.appendChild(style);