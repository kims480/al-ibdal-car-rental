<script setup>
import { RouterView, useRoute } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { computed, onMounted, watch } from 'vue'
import AppFooter from './components/AppFooter.vue'
import DashboardLayout from './components/layouts/DashboardLayout.vue'
import { useUIStore } from './stores/ui'

const authStore = useAuthStore()
const route = useRoute()
const ui = useUIStore()

// Determine if the current route is public (no dashboard chrome)
const isPublicRoute = computed(() => {
  return route.name === 'Home' || route.name === 'Login'
})

onMounted(() => {
  // Check for existing authentication token
  authStore.checkAuth()
  // Apply persisted theme
  document.documentElement.setAttribute('data-theme', ui.theme)
})
</script>

<template>
  <div id="app" :data-theme="ui.theme" :class="{'min-h-screen': !isPublicRoute}">
    <template v-if="isPublicRoute">
      <RouterView />
      <AppFooter v-if="route.name === 'Home'" />
    </template>
    <template v-else>
      <DashboardLayout>
        <RouterView />
      </DashboardLayout>
    </template>
    
    <!-- Global Toast Notifications -->
    <ToastNotification />
  </div>
</template>

<style>
#app {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
    'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Smooth scrolling for anchor links */
html {
  scroll-behavior: smooth;
}

/* Global Animation Classes */
.fade-in {
  opacity: 0;
  animation: fadeIn 0.8s ease-in forwards;
}

.slide-up {
  transform: translateY(30px);
  opacity: 0;
  animation: slideUp 0.8s ease-out forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { transform: translateY(30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>
