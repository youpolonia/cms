<template>
  <div class="user-avatar">
    <img 
      v-if="user.avatar_url"
      :src="user.avatar_url" 
      :alt="user.name"
      class="rounded-full"
      :class="{
        'h-8 w-8': size === 'sm',
        'h-10 w-10': size === 'md',
        'h-12 w-12': size === 'lg'
      }"
    >
    <div 
      v-else
      class="bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-medium"
      :class="{
        'h-8 w-8 text-xs': size === 'sm',
        'h-10 w-10 text-sm': size === 'md',
        'h-12 w-12 text-base': size === 'lg'
      }"
    >
      {{ user.name.charAt(0).toUpperCase() }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    user: {
      type: Object,
      required: true
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
  }
}
</script>

<style scoped>
.user-avatar {
  display: inline-block;
  vertical-align: middle;
}
</style>