import './bootstrap';

import Alpine from 'alpinejs';
import NotificationBell from './components/NotificationBell.vue';
import NotificationItem from './components/NotificationItem.vue';

window.Alpine = Alpine;

Alpine.start();

import '../css/version-comparison.css';

// Register components globally
window.Vue = require('vue').default;
Vue.component('notification-bell', NotificationBell);
Vue.component('notification-item', NotificationItem);
Vue.component('suggestion-panel', require('./components/SuggestionPanel.vue').default);

// Page Builder Components
import PageBuilder from './components/PageBuilder/PageBuilder.vue';
import TextBlock from './components/PageBuilder/blocks/TextBlock.vue';
import ImageBlock from './components/PageBuilder/blocks/ImageBlock.vue';
import VideoBlock from './components/PageBuilder/blocks/VideoBlock.vue';

Vue.component('page-builder', PageBuilder);
Vue.component('text-block', TextBlock);
Vue.component('image-block', ImageBlock);
Vue.component('video-block', VideoBlock);
