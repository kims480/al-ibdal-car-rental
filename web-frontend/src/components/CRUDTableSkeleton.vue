<template>
  <div class="animate-pulse">
    <!-- Header skeleton -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <div class="h-8 bg-gray-200 rounded w-48 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-32"></div>
      </div>
      <div class="h-10 bg-gray-200 rounded w-32"></div>
    </div>

    <!-- Filters skeleton -->
    <div class="bg-white rounded-lg shadow-sm border mb-6 p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="h-10 bg-gray-200 rounded"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
      </div>
    </div>

    <!-- Statistics cards skeleton (if showStats is true) -->
    <div v-if="showStats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div v-for="n in 4" :key="n" class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between">
          <div>
            <div class="h-6 bg-gray-200 rounded w-16 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-20"></div>
          </div>
          <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
        </div>
      </div>
    </div>

    <!-- Table skeleton -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <!-- Table header -->
      <div class="bg-gray-50 px-6 py-3 border-b">
        <div class="grid grid-cols-6 gap-4">
          <div v-for="n in 6" :key="n" class="h-4 bg-gray-200 rounded"></div>
        </div>
      </div>
      
      <!-- Table rows -->
      <div v-for="n in rows" :key="n" class="px-6 py-4 border-b border-gray-200 last:border-b-0">
        <div class="grid grid-cols-6 gap-4">
          <div v-for="col in 6" :key="col" class="h-4 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CRUDTableSkeleton',
  props: {
    rows: {
      type: Number,
      default: 5
    },
    showStats: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>