<template>
  <div class="animate-pulse" :class="containerClass">
    <!-- Card skeleton -->
    <div v-if="type === 'card'" class="bg-white overflow-hidden shadow rounded-lg border p-5">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
        </div>
        <div class="ml-5 flex-1 space-y-2">
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          <div class="h-6 bg-gray-200 rounded w-1/2"></div>
        </div>
      </div>
    </div>
    
    <!-- Chart skeleton -->
    <div v-else-if="type === 'chart'" class="bg-white p-5 rounded-lg shadow border border-gray-100">
      <div class="h-6 bg-gray-200 rounded w-1/3 mb-4"></div>
      <div class="h-64 bg-gray-200 rounded"></div>
    </div>
    
    <!-- List item skeleton -->
    <div v-else-if="type === 'list-item'" class="flex items-center space-x-3 p-3">
      <div class="w-3 h-3 bg-gray-200 rounded-full"></div>
      <div class="flex-1 space-y-1">
        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
      </div>
    </div>
    
    <!-- Table row skeleton -->
    <div v-else-if="type === 'table-row'" class="grid grid-cols-4 gap-4 p-4 border-b">
      <div class="h-4 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded"></div>
      <div class="h-4 bg-gray-200 rounded w-1/2"></div>
    </div>
    
    <!-- Text skeleton -->
    <div v-else-if="type === 'text'" class="space-y-2">
      <div v-for="i in lines" :key="i" class="h-4 bg-gray-200 rounded" :class="getLineWidth(i)"></div>
    </div>
    
    <!-- Button skeleton -->
    <div v-else-if="type === 'button'" class="h-10 bg-gray-200 rounded w-24"></div>
    
    <!-- Custom content -->
    <slot v-else></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'card',
    validator: (value) => ['card', 'chart', 'list-item', 'table-row', 'text', 'button', 'custom'].includes(value)
  },
  lines: {
    type: Number,
    default: 3
  },
  count: {
    type: Number,
    default: 1
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const getLineWidth = (lineNumber) => {
  const widths = ['w-full', 'w-5/6', 'w-4/6', 'w-3/6', 'w-2/6']
  return widths[lineNumber - 1] || 'w-full'
}
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>