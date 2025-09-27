<template>
  <div class="dashboard-layout min-h-screen flex" :class="[themeClass]">
    <!-- Sidebar -->
    <aside :class="['sticky top-0 h-screen transition-all duration-300', sidebarCollapsed ? 'w-12' : 'w-52']">
      <div class="h-full flex flex-col border-r border-gray-200/50 bg-surface">
        <div class="flex items-center justify-between p-2 border-b border-gray-200/50">
          <div class="flex items-center">
            <div class="w-6 h-6 bg-gradient-to-br from-purple-600 to-blue-600 rounded-md flex items-center justify-center text-white font-bold text-xs mr-2" v-if="!sidebarCollapsed">
              AI
            </div>
            <div v-if="!sidebarCollapsed" class="flex flex-col">
              <h1 class="text-xs font-semibold text-gray-900 dark:text-white">Al Ibdal</h1>
              <p class="text-xs text-gray-500 dark:text-gray-400">Car Rental</p>
            </div>
          </div>
          <button @click="toggleSidebar" class="p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <ChevronLeftIcon v-if="!sidebarCollapsed" class="w-3 h-3 text-gray-500" />
            <ChevronRightIcon v-else class="w-3 h-3 text-gray-500" />
          </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-1">
          <div class="space-y-0.5">
            <!-- Main Navigation -->            
            <RouterLink to="/dashboard" class="nav-item group" active-class="nav-item-active">
              <HomeIcon class="w-4 h-4 flex-shrink-0" />
              <span v-if="!sidebarCollapsed" class="transition-opacity duration-200">Dashboard</span>
              <div v-if="sidebarCollapsed" class="nav-tooltip">Dashboard</div>
            </RouterLink>

            <!-- Partner-specific routes -->
            <template v-if="user?.role === 'partner'">
              <RouterLink to="/partner-service-requests" class="nav-item group" active-class="nav-item-active">
                <ClipboardDocumentListIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">My Service Requests</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">My Service Requests</div>
              </RouterLink>
            </template>
            
            <!-- Admin/Staff routes -->
            <template v-else>
              <RouterLink to="/service-requests" class="nav-item group" active-class="nav-item-active">
                <WrenchScrewdriverIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Service Requests</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Service Requests</div>
              </RouterLink>
              
              <RouterLink to="/cars" class="nav-item group" active-class="nav-item-active">
                <TruckIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Cars</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Cars</div>
              </RouterLink>
              
              <RouterLink to="/rentals" class="nav-item group" active-class="nav-item-active">
                <ClockIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Rentals</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Rentals</div>
              </RouterLink>
              
              <RouterLink to="/customers" class="nav-item group" active-class="nav-item-active">
                <UserPlusIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Customers</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Customers</div>
              </RouterLink>

              <RouterLink to="/contracts" class="nav-item group" active-class="nav-item-active">
                <DocumentTextIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Contracts</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Contracts</div>
              </RouterLink>
              
              <RouterLink to="/invoices" class="nav-item group" active-class="nav-item-active">
                <ReceiptPercentIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Invoices</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Invoices</div>
              </RouterLink>
              
              <RouterLink to="/car-expenses" class="nav-item group" active-class="nav-item-active">
                <ChartBarIcon class="w-4 h-4 flex-shrink-0" />
                <span v-if="!sidebarCollapsed">Car Expenses</span>
                <div v-if="sidebarCollapsed" class="nav-tooltip">Car Expenses</div>
              </RouterLink>

              <!-- Admin-only routes -->
              <template v-if="user?.role === 'admin'">
                <RouterLink to="/branches" class="nav-item group" active-class="nav-item-active">
                  <BuildingOfficeIcon class="w-4 h-4 flex-shrink-0" />
                  <span v-if="!sidebarCollapsed">Branches</span>
                  <div v-if="sidebarCollapsed" class="nav-tooltip">Branches</div>
                </RouterLink>
                
                <RouterLink to="/users" class="nav-item group" active-class="nav-item-active">
                  <UsersIcon class="w-4 h-4 flex-shrink-0" />
                  <span v-if="!sidebarCollapsed">Users</span>
                  <div v-if="sidebarCollapsed" class="nav-tooltip">Users</div>
                </RouterLink>
                
                <RouterLink to="/subcontractors" class="nav-item group" active-class="nav-item-active">
                  <WrenchIcon class="w-4 h-4 flex-shrink-0" />
                  <span v-if="!sidebarCollapsed">Subcontractors</span>
                  <div v-if="sidebarCollapsed" class="nav-tooltip">Subcontractors</div>
                </RouterLink>
                
                <RouterLink to="/governorates-wilayats" class="nav-item group" active-class="nav-item-active">
                  <MapIcon class="w-4 h-4 flex-shrink-0" />
                  <span v-if="!sidebarCollapsed">Governorates</span>
                  <div v-if="sidebarCollapsed" class="nav-tooltip">Governorates</div>
                </RouterLink>
                
                <RouterLink to="/wilayat-branch-assignments" class="nav-item group" active-class="nav-item-active">
                  <MapPinIcon class="w-4 h-4 flex-shrink-0" />
                  <span v-if="!sidebarCollapsed">Wilayat Assignments</span>
                  <div v-if="sidebarCollapsed" class="nav-tooltip">Wilayat Assignments</div>
                </RouterLink>
              </template>
            </template>
          </div>
        </nav>
        <!-- Simplified sidebar footer - no user info -->
        <div class="p-2 border-t border-gray-200/50 bg-gray-50/50 dark:bg-gray-800/50">
          <div v-if="!sidebarCollapsed" class="text-center">
            <p class="text-xs text-gray-400 dark:text-gray-500">Al Ibdal Trading LLC</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">© {{ new Date().getFullYear() }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main -->
    <div class="flex-1 min-w-0 flex flex-col bg-app">
      <header class="h-14 border-b border-gray-200/50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-between px-4 sticky top-0 z-10">
        <div class="flex items-center gap-3">
          <button @click="toggleSidebar" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors lg:hidden">
            <Bars3Icon class="w-5 h-5 text-gray-600 dark:text-gray-300" />
          </button>
          
          <!-- Breadcrumb / Page Title -->
          <div class="flex items-center space-x-2">
            <div class="w-6 h-6 bg-gradient-to-br from-purple-600 to-blue-600 rounded-md flex items-center justify-center text-white font-bold text-xs lg:hidden">
              AI
            </div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ pageTitle }}</h1>
          </div>
        </div>
        
        <!-- Header Actions -->
        <div class="flex items-center space-x-2">
          <!-- Theme Switcher -->
          <div class="relative">
            <button 
              @click="showThemeDropdown = !showThemeDropdown"
              class="flex items-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              title="Change Theme"
            >
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
              </svg>
            </button>
            
            <!-- Theme Dropdown -->
            <div v-if="showThemeDropdown" class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
              <div class="p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3 px-1">Choose Theme</p>
                <div class="space-y-2">
                  <button 
                    v-for="themeOption in themes" 
                    :key="themeOption.value"
                    @click="selectTheme(themeOption.value)"
                    :class="['w-full text-left p-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-between border', 
                      theme === themeOption.value 
                        ? 'border-blue-500 bg-blue-50 text-blue-700 shadow-sm' 
                        : 'border-gray-200 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600']"
                  >
                    <div class="flex flex-col">
                      <span class="font-semibold">{{ themeOption.label }}</span>
                      <span class="text-xs opacity-75">{{ themeOption.colors }}</span>
                    </div>
                    <svg v-if="theme === themeOption.value" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </button>
                </div>
                <p class="text-xs text-gray-400 mt-3 px-1">Themes affect colors, backgrounds, and visual mood</p>
              </div>
            </div>
          </div>
          
          <!-- Notifications -->
          <button class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <BellIcon class="w-5 h-5 text-gray-600 dark:text-gray-300" />
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
          
          <!-- User Profile Dropdown -->
          <div class="relative">
            <button 
              @click="showUserDropdown = !showUserDropdown"
              class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-blue-600 flex items-center justify-center text-white text-sm font-bold">
                {{ user?.name?.charAt(0)?.toUpperCase() }}
              </div>
              <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ user?.role }}</p>
              </div>
              <ChevronDownIcon class="w-4 h-4 text-gray-400" />
            </button>
            
            <!-- User Dropdown -->
            <div v-if="showUserDropdown" class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
              <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-600 to-blue-600 flex items-center justify-center text-white text-lg font-bold">
                    {{ user?.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ user?.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate capitalize">{{ user?.role }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ user?.email }}</p>
                  </div>
                </div>
              </div>
              
              <div class="p-2">
                <RouterLink 
                  to="/profile" 
                  @click="showUserDropdown = false"
                  class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <UserIcon class="w-4 h-4 mr-3" />
                  View Profile
                </RouterLink>
                
                <RouterLink 
                  to="/settings" 
                  @click="showUserDropdown = false"
                  class="flex items-center w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <CogIcon class="w-4 h-4 mr-3" />
                  Settings
                </RouterLink>
                
                <hr class="my-2 border-gray-200 dark:border-gray-600" />
                
                <button 
                  @click="logout"
                  class="flex items-center w-full px-3 py-2 text-sm text-red-600 dark:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                >
                  <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3" />
                  Sign out
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="p-3 md:p-4">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useUIStore } from '../../stores/ui'
import {
  HomeIcon,
  UserIcon,
  UsersIcon,
  ClipboardDocumentListIcon,
  WrenchScrewdriverIcon,
  TruckIcon,
  ClockIcon,
  DocumentTextIcon,
  ReceiptPercentIcon,
  UserPlusIcon,
  ChartBarIcon,
  BuildingOfficeIcon,
  WrenchIcon,
  MapIcon,
  MapPinIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  BellIcon,
  ChevronDownIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const ui = useUIStore()
const route = useRoute()
const router = useRouter()

const user = computed(() => auth.user)
const sidebarCollapsed = computed(() => ui.sidebarCollapsed)
const showThemeDropdown = ref(false)
const showUserDropdown = ref(false)
const theme = computed({
  get: () => ui.theme,
  set: (val) => ui.setTheme(val)
})

const themes = [
  { label: '� Ocean Breeze', value: 'ocean-breeze', colors: 'Blue & Teal' },
  { label: '🌸 Cherry Blossom', value: 'cherry-blossom', colors: 'Pink & Purple' },
  { label: '� Golden Sunset', value: 'golden-sunset', colors: 'Orange & Yellow' },
  { label: '🌙 Dark Blue Neon', value: 'dark-blue-neon', colors: 'Dark & Neon Blue' }
]

const pageTitle = computed(() => {
  const routeNames = {
    dashboard: 'Dashboard',
    'service-requests': 'Service Requests',
    cars: 'Fleet Management',
    branches: 'Branches',
    users: 'Users',
    rentals: 'Rentals',
    contracts: 'Contracts',
    invoices: 'Invoices',
    'car-expenses': 'Expenses',
    subcontractors: 'Subcontractors',
    'governorates-wilayats': 'Locations',
    'wilayat-branch-assignments': 'Assignments',
    profile: 'Profile',
    'partner-service-requests': 'My Requests',
    'customers': 'Customers Management'
  }
  return routeNames[route.name] || 'Dashboard'
})

const themeClass = computed(() => `theme-${ui.theme}`)

watch(theme, (val) => {
  document.documentElement.setAttribute('data-theme', val)
}, { immediate: true })

function toggleSidebar() {
  ui.toggleSidebar()
}

function selectTheme(themeValue) {
  theme.value = themeValue
  showThemeDropdown.value = false
}

async function logout() {
  showUserDropdown.value = false
  await auth.logout()
  router.push('/login')
}

// Close dropdowns when clicking outside
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      showThemeDropdown.value = false
      showUserDropdown.value = false
    }
  })
})
</script>

<style scoped>
/* ============================================
   🎨 THREE-THEME SYSTEM: COLORFUL & COMPACT
   ============================================ */

/* === THEME 1: OCEAN BREEZE (Blue & Teal) === */
.theme-ocean-breeze {
  --primary-50: #eff6ff;
  --primary-100: #dbeafe;
  --primary-200: #bfdbfe;
  --primary-500: #3b82f6;
  --primary-600: #2563eb;
  --primary-700: #1d4ed8;
  --secondary-500: #14b8a6;
  --secondary-600: #0d9488;
  --accent-500: #06b6d4;
  --surface-bg: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #e6fffa 100%);
  --header-bg: rgba(255, 255, 255, 0.9);
  --sidebar-bg: rgba(248, 250, 252, 0.95);
  --card-bg: rgba(255, 255, 255, 0.8);
  --text-primary: #0f172a;
  --text-secondary: #475569;
  --border-color: #e2e8f0;
}

/* === THEME 2: CHERRY BLOSSOM (Pink & Purple) === */
.theme-cherry-blossom {
  --primary-50: #fdf2f8;
  --primary-100: #fce7f3;
  --primary-200: #fbcfe8;
  --primary-500: #ec4899;
  --primary-600: #db2777;
  --primary-700: #be185d;
  --secondary-500: #a855f7;
  --secondary-600: #9333ea;
  --accent-500: #f472b6;
  --surface-bg: linear-gradient(135deg, #fdf2f8 0%, #faf5ff 50%, #fef7ff 100%);
  --header-bg: rgba(255, 255, 255, 0.9);
  --sidebar-bg: rgba(253, 242, 248, 0.95);
  --card-bg: rgba(255, 255, 255, 0.8);
  --text-primary: #3c1361;
  --text-secondary: #7c2d92;
  --border-color: #f3e8ff;
}

/* === THEME 3: GOLDEN SUNSET (Orange & Yellow) === */
.theme-golden-sunset {
  --primary-50: #fffbeb;
  --primary-100: #fef3c7;
  --primary-200: #fde68a;
  --primary-500: #f59e0b;
  --primary-600: #d97706;
  --primary-700: #b45309;
  --secondary-500: #ea580c;
  --secondary-600: #dc2626;
  --accent-500: #fbbf24;
  --surface-bg: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fed7aa 100%);
  --header-bg: rgba(255, 255, 255, 0.9);
  --sidebar-bg: rgba(255, 251, 235, 0.95);
  --card-bg: rgba(255, 255, 255, 0.8);
  --text-primary: #451a03;
  --text-secondary: #9a3412;
  --border-color: #fed7aa;
}

/* === THEME 4: DARK BLUE NEON === */
.theme-dark-blue-neon {
  --primary-50: #1a2332;
  --primary-100: #243447;
  --primary-200: #2e455c;
  --primary-500: #00d4ff;
  --primary-600: #00b8e6;
  --primary-700: #009cc7;
  --secondary-500: #7c3aed;
  --secondary-600: #6d28d9;
  --accent-500: #a855f7;
  --surface-bg: linear-gradient(135deg, #0a0f1c 0%, #1a1f2e 50%, #2a2f3e 100%);
  --header-bg: rgba(26, 31, 46, 0.95);
  --sidebar-bg: rgba(16, 20, 31, 0.98);
  --card-bg: rgba(42, 47, 62, 0.8);
  --text-primary: #e2e8f0;
  --text-secondary: #94a3b8;
  --border-color: #334155;
}

.theme-dark-blue-neon .nav-item {
  color: var(--text-secondary);
}

.theme-dark-blue-neon .nav-item:hover {
  color: var(--primary-500);
  background: rgba(0, 212, 255, 0.1);
  box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
}

.theme-dark-blue-neon .nav-item-active {
  background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
  color: #0a0f1c !important;
  box-shadow: 0 0 20px rgba(0, 212, 255, 0.4);
}

.theme-dark-blue-neon header {
  color: var(--text-primary);
}

.theme-dark-blue-neon .w-8.h-8.rounded-full,
.theme-dark-blue-neon .w-10.h-10.rounded-full {
  border-color: var(--primary-500);
  box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
}

/* === BASE LAYOUT STYLES === */
.bg-app { 
  background: var(--surface-bg);
  min-height: 100vh;
}

.bg-surface { 
  background: var(--sidebar-bg);
  backdrop-filter: blur(10px);
  border-right: 1px solid var(--border-color);
}

/* === COMPACT NAVIGATION === */
.nav-item {
  display: flex;
  align-items: center;
  position: relative;
  border-radius: 6px;
  padding: 6px 8px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  color: var(--text-secondary);
  text-decoration: none;
  margin: 1px 0;
}

.nav-item:hover { 
  color: var(--text-primary);
  background: var(--primary-50);
  text-decoration: none;
  transform: translateX(2px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.nav-item-active { 
  background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
  color: white !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transform: translateX(3px);
  text-decoration: none;
}

.nav-item-active:hover {
  background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
  text-decoration: none;
}

/* === TOOLTIP STYLES === */
.nav-tooltip {
  position: absolute;
  left: 3rem;
  background: var(--text-primary);
  color: white;
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
  z-index: 50;
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.group:hover .nav-tooltip {
  opacity: 1;
}

/* === HEADER STYLES === */
header {
  background: var(--header-bg);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--border-color);
}

/* === COMPACT SCROLLBARS === */
nav::-webkit-scrollbar {
  width: 3px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: var(--primary-200);
  border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: var(--primary-300);
}

/* === DROPDOWN STYLES === */
.relative button:hover {
  background: var(--primary-50);
  color: var(--primary-700);
}

.relative .absolute {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  backdrop-filter: blur(8px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.relative .absolute button:hover {
  background: var(--primary-50);
  color: var(--primary-700);
}

.relative .absolute .bg-blue-600 {
  background: var(--primary-600) !important;
}

/* === FORM & INTERACTION ELEMENTS === */
.icon-btn { 
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  border-radius: 6px;
  background: var(--primary-50);
  color: var(--text-secondary);
  transition: all 0.2s;
}

.icon-btn:hover {
  background: var(--primary-100);
  color: var(--primary-700);
  transform: translateY(-1px);
}

/* === RESPONSIVE DESIGN === */
@media (max-width: 1024px) {
  aside {
    position: fixed;
    z-index: 40;
    transform: translateX(0);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  aside.collapsed {
    transform: translateX(-100%);
  }
}

@media (max-width: 640px) {
  .nav-item {
    font-size: 12px;
    padding: 5px 6px;
  }
  
  header {
    height: 3rem;
    padding: 0 12px;
  }
  
  main {
    padding: 12px;
  }
}

/* === ANIMATIONS === */
@keyframes slideIn {
  from {
    transform: translateX(-10px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.transition-opacity {
  animation: slideIn 0.3s ease-out;
}

/* === MODERN BUTTON STYLES === */
button {
  border-radius: 6px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

button:hover {
  transform: translateY(-1px);
}

/* === AVATAR STYLES === */
.w-8.h-8.rounded-full {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 2px solid var(--primary-100);
}

.w-10.h-10.rounded-full {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border: 2px solid var(--primary-100);
}

/* === REMOVE ALL UNDERLINES === */
a { 
  text-decoration: none !important; 
}
a:hover { 
  text-decoration: none !important; 
}

/* === THEME TRANSITIONS === */
* {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* === COMPACT SPACING === */
.space-y-0\.5 > :not([hidden]) ~ :not([hidden]) {
  margin-top: 2px;
}

/* === ENHANCED SHADOWS === */
.shadow-lg {
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* === BACKDROP BLUR ENHANCEMENT === */
.backdrop-blur-sm {
  backdrop-filter: blur(6px);
}
</style>
