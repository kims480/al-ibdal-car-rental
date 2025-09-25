<template>
  <div class="dashboard-layout min-h-screen flex" :class="[themeClass]">
    <!-- Sidebar -->
    <aside :class="['sticky top-0 h-screen transition-all duration-300', sidebarCollapsed ? 'w-16' : 'w-64']">
      <div class="h-full flex flex-col border-r border-gray-200/50 bg-surface">
        <div class="flex items-center p-4">
          <img src="/src/assets/logo.svg" alt="Logo" class="h-8 w-auto mr-2" v-if="!sidebarCollapsed" />
        </div>
        <nav class="flex-1 overflow-y-auto">
          <ul class="px-2 space-y-1">
            <li>
              <RouterLink to="/dashboard" class="nav-item" active-class="nav-item-active">
                <span class="i-heroicons-home text-lg"></span>
                <span v-if="!sidebarCollapsed">Dashboard</span>
              </RouterLink>
            </li>
            <li>
              <RouterLink to="/profile" class="nav-item" active-class="nav-item-active">
                <span class="i-heroicons-users text-lg"></span>
                <span v-if="!sidebarCollapsed">Profile</span>
              </RouterLink>
            </li>
            <template v-if="user?.role === 'partner'">
              <li>
                <RouterLink to="/partner-service-requests" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-clipboard-document-list text-lg"></span>
                  <span v-if="!sidebarCollapsed">My Service Requests</span>
                </RouterLink>
              </li>
            </template>
            <template v-else>
              <li>
                <RouterLink to="/service-requests" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-wrench-screwdriver text-lg"></span>
                  <span v-if="!sidebarCollapsed">Service Requests</span>
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/cars" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-truck text-lg"></span>
                  <span v-if="!sidebarCollapsed">Cars</span>
                </RouterLink>
              </li>
              <li v-if="user?.role === 'admin'">
                <RouterLink to="/branches" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-building-office text-lg"></span>
                  <span v-if="!sidebarCollapsed">Branches</span>
                </RouterLink>
              </li>
              <li v-if="user?.role === 'admin'">
                <RouterLink to="/users" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-users text-lg"></span>
                  <span v-if="!sidebarCollapsed">Users</span>
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/rentals" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-clock text-lg"></span>
                  <span v-if="!sidebarCollapsed">Rentals</span>
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/contracts" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-document-text text-lg"></span>
                  <span v-if="!sidebarCollapsed">Contracts</span>
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/invoices" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-receipt-percent text-lg"></span>
                  <span v-if="!sidebarCollapsed">Invoices</span>
                </RouterLink>
              </li>
              <li>
                <RouterLink to="/customers/add" class="nav-item" active-class="nav-item-active">
                  <span class="i-heroicons-user-plus text-lg"></span>
                  <span v-if="!sidebarCollapsed">Add Customer</span>
                </RouterLink>
              </li>
            </template>
          </ul>
        </nav>
        <div class="p-3 border-t border-gray-200/50">
          <div class="space-y-2">
            <label class="block text-xs font-semibold uppercase tracking-wide text-muted" v-if="!sidebarCollapsed">Theme</label>
            <select class="w-full the-input" :title="'Theme'" v-model="theme">
              <option value="light">Light</option>
              <option value="dark-blue">Dark Blue</option>
              <option value="dark-blue-neon">Dark Blue NEON</option>
            </select>
            <button class="w-full the-btn mt-2" @click="logout">Logout</button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 min-w-0 flex flex-col bg-app">
      <header class="h-14 border-b border-gray-200/50 bg-surface flex items-center justify-between px-4">
        <div class="flex items-center gap-3">
          <button @click="toggleSidebar" class="icon-btn md:hidden">
            <span class="i-heroicons-bars-3 text-xl"></span>
          </button>
          <img src="/src/assets/logo.svg" alt="Logo" class="h-7 w-auto mr-2" />
          <h2 class="text-title font-semibold">{{ pageTitle }}</h2>
        </div>
        <div class="text-sm text-muted">Welcome, {{ user?.name }}</div>
      </header>

      <main class="p-4 md:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useUIStore } from '../../stores/ui'

const auth = useAuthStore()
const ui = useUIStore()
const route = useRoute()
const router = useRouter()

const user = computed(() => auth.user)
const sidebarCollapsed = computed(() => ui.sidebarCollapsed)
const theme = computed({
  get: () => ui.theme,
  set: (val) => ui.setTheme(val)
})

const pageTitle = computed(() => route.name || 'Dashboard')
const themeClass = computed(() => `theme-${ui.theme}`)

watch(theme, (val) => {
  document.documentElement.setAttribute('data-theme', val)
}, { immediate: true })

function toggleSidebar() {
  ui.toggleSidebar()
}

async function logout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
/* Sidebar/Topbar theming using CSS vars coming from data-theme */
.bg-app { background: var(--bg-app); }
.bg-surface { background: var(--bg-surface); }
.text-title { color: var(--text-title); }
.text-muted { color: var(--text-muted); }
.border-gray-200\/50 { border-color: var(--border-color); }

.nav-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.625rem 0.75rem; border-radius: 0.5rem;
  color: var(--nav-fg);
  text-decoration: none;
}
.nav-item:hover { background: var(--nav-hover-bg); color: var(--nav-hover-fg); text-decoration: none; }
.nav-item-active { background: var(--nav-active-bg); color: var(--nav-active-fg); text-decoration: none; }

.icon-btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.375rem; border-radius: 0.5rem; background: var(--btn-ghost-bg); color: var(--btn-ghost-fg); }
.icon-btn:hover { background: var(--btn-ghost-hover-bg); }

.the-input { background: var(--input-bg); color: var(--input-fg); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.5rem; }
.the-btn { background: var(--primary); color: #fff; border-radius: 0.5rem; padding: 0.5rem; }
.the-btn:hover { filter: brightness(1.05); }

/* Remove underline from any links inside header */
a { text-decoration: none; }
a:hover { text-decoration: none; }
</style>
