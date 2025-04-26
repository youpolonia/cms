<template>
  <span 
    :class="[
      'inline-flex items-center',
      value > 0 ? 'text-red-600' : 'text-green-600'
    ]"
  >
    <svg 
      v-if="showArrow"
      :class="[
        'h-4 w-4',
        value > 0 ? 'rotate-0' : 'rotate-180'
      ]" 
      xmlns="http://www.w3.org/2000/svg" 
      fill="none" 
      viewBox="0 0 24 24" 
      stroke="currentColor"
    >
      <path 
        stroke-linecap="round" 
        stroke-linejoin="round" 
        stroke-width="2" 
        d="M5 10l7-7m0 0l7 7m-7-7v18" 
      />
    </svg>
    {{ formattedValue }}
  </span>
</template>

<script>
export default {
  props: {
    value: {
      type: Number,
      required: true
    },
    showArrow: {
      type: Boolean,
      default: false
    }
  },

  computed: {
    formattedValue() {
      const absValue = Math.abs(this.value)
      return this.value > 0 ? `+${absValue}%` : `${absValue}%`
    }
  }
}
</script>
