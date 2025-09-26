<template>
  <div :class="containerClass">
    <div class="flex flex-col items-center space-y-4">
      <!-- Main spinner -->
      <div class="relative">
        <div :class="`w-${size} h-${size} border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin`"></div>
        <div 
          :class="`absolute inset-0 w-${size} h-${size} border-4 border-transparent border-r-yellow-400 rounded-full animate-spin`" 
          style="animation-direction: reverse; animation-duration: 1.5s;"
        ></div>
      </div>
      
      <!-- Loading text -->
      <div v-if="showText" class="text-center">
        <div :class="`text-${textSize} font-semibold text-gray-700 animate-pulse`">{{ text }}</div>
        <div v-if="subText" :class="`text-${subTextSize} text-gray-500 mt-1`">{{ subText }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: '16'
  },
  text: {
    type: String,
    default: 'Loading...'
  },
  subText: {
    type: String,
    default: ''
  },
  showText: {
    type: Boolean,
    default: true
  },
  overlay: {
    type: Boolean,
    default: false
  },
  fullscreen: {
    type: Boolean,
    default: false
  },
  textSize: {
    type: String,
    default: 'lg'
  },
  subTextSize: {
    type: String,
    default: 'sm'
  }
})

const containerClass = computed(() => {
  const classes = []
  
  if (props.fullscreen) {
    classes.push('fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50')
  } else if (props.overlay) {
    classes.push('absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10')
  } else {
    classes.push('flex items-center justify-center p-8')
  }
  
  return classes.join(' ')
})
</script>

<style scoped>
/* Custom animation for dual spinner */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>