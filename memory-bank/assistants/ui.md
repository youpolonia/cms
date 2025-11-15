# AI Assistants UI Integration

## Overview
Provides consistent UI components and patterns for all AI assistant integrations.

## Components

### AssistButton.vue
Reusable button component for AI assistant actions.

**Props:**
- `label` (String): Button text
- `tooltip` (String): Hover tooltip text
- `disabled` (Boolean): Disabled state

**Events:**
- `click`: Emitted on button click

### AssistModal.vue
Base modal/sidebar component for assistant previews.

**Props:**
- `title` (String): Modal title
- `sidebar` (Boolean): Toggle between modal/sidebar mode

**Events:**
- `close`: Emitted when modal closes
- `apply`: Emitted when apply button clicked

### Context Menu
Right-click integration for AI actions.

**Usage:**
```html
<div v-ai-context-menu="{ content: selectedText }">
  <!-- Content here -->
</div>
```

**Actions:**
- Generate Content
- Improve Text
- Summarize

## Setup
1. Import components:
```js
import { AssistButton, AssistModal } from '@/admin/ai-assist';
import contextMenu from '@/admin/ai-assist/context-menu';

Vue.use(contextMenu);
Vue.component('AssistButton', AssistButton);
Vue.component('AssistModal', AssistModal);
```

2. Include styles:
```js
import '@/admin/ai-assist/styles.css';
```

## Implementation Example
```vue
<template>
  <AssistButton 
    label="Generate Content"
    @click="showModal('generate')"
  />

  <AssistModal
    v-if="showModal"
    title="Generate Content"
    @close="showModal = false"
    @apply="applyContent"
  >
    <template #content>
      <!-- Assistant content here -->
    </template>
  </AssistModal>
</template>