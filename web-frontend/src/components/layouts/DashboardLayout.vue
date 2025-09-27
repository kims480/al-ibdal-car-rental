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
        <div class="flex items-center space-x-1">
          <!-- Theme Switcher -->
          <div class="relative">
            <button 
              @click="showThemeDropdown = !showThemeDropdown"
              class="nav-control-btn"
              title="Change Theme"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
              </svg>
            </button>
            
            <!-- Theme Dropdown -->
            <div v-if="showThemeDropdown" class="material-dropdown theme-dropdown">
              <div class="dropdown-header">
                <h3 class="dropdown-title">Choose Theme</h3>
                <p class="dropdown-subtitle">Personalize your dashboard experience</p>
              </div>
              
              <div class="theme-options">
                <button 
                  v-for="themeOption in themes" 
                  :key="themeOption.value"
                  @click="selectTheme(themeOption.value)"
                  :class="['theme-option-btn', { 'active': theme === themeOption.value }]"
                >
                  <div class="theme-preview-mini" :data-theme="themeOption.value">
                    <div class="mini-header"></div>
                    <div class="mini-content">
                      <div class="mini-bar"></div>
                      <div class="mini-bar short"></div>
                    </div>
                  </div>
                  <div class="theme-info">
                    <span class="theme-name">{{ themeOption.label }}</span>
                    <span class="theme-desc">{{ themeOption.colors }}</span>
                  </div>
                  <div v-if="theme === themeOption.value" class="theme-check">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Notifications -->
          <button class="nav-control-btn relative" title="Notifications">
            <BellIcon class="w-5 h-5" />
            <span class="notification-badge">3</span>
          </button>
          
          <!-- User Profile Dropdown -->
          <div class="relative">
            <button 
              @click="showUserDropdown = !showUserDropdown"
              class="user-profile-btn"
            >
              <div class="user-avatar">
                {{ user?.name?.charAt(0)?.toUpperCase() }}
              </div>
              <div class="hidden sm:block user-info">
                <p class="user-name">{{ user?.name }}</p>
                <p class="user-role">{{ user?.role }}</p>
              </div>
              <ChevronDownIcon class="w-4 h-4 chevron-icon" />
            </button>
            
            <!-- User Dropdown -->
            <div v-if="showUserDropdown" class="material-dropdown user-dropdown">
              <div class="dropdown-user-header">
                <div class="user-avatar-large">
                  {{ user?.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div class="user-details">
                  <p class="user-name-large">{{ user?.name }}</p>
                  <p class="user-role-large">{{ user?.role }}</p>
                  <p class="user-email">{{ user?.email }}</p>
                </div>
              </div>
              
              <div class="dropdown-divider"></div>
              
              <div class="dropdown-menu">
                <RouterLink 
                  to="/profile" 
                  @click="showUserDropdown = false"
                  class="dropdown-item"
                >
                  <div class="item-icon">
                    <UserIcon class="w-4 h-4" />
                  </div>
                  <span class="item-text">View Profile</span>
                </RouterLink>
                
                <RouterLink 
                  to="/settings" 
                  @click="showUserDropdown = false"
                  class="dropdown-item"
                >
                  <div class="item-icon">
                    <CogIcon class="w-4 h-4" />
                  </div>
                  <span class="item-text">Settings</span>
                </RouterLink>
                
                <div class="dropdown-divider"></div>
                
                <button 
                  @click="logout"
                  class="dropdown-item danger"
                >
                  <div class="item-icon">
                    <ArrowRightOnRectangleIcon class="w-4 h-4" />
                  </div>
                  <span class="item-text">Sign Out</span>
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

/* ============================================
   🎨 MATERIAL DESIGN NAVBAR CONTROLS
   ============================================ */

/* === NAVIGATION CONTROL BUTTONS === */
.nav-control-btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--theme-card, rgba(255, 255, 255, 0.8));
  border: 1px solid var(--theme-border, rgba(229, 231, 235, 0.5));
  color: var(--theme-text-secondary, #6b7280);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(8px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.nav-control-btn:hover {
  background: var(--theme-primary-50, #eff6ff);
  color: var(--theme-primary-600, #2563eb);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.nav-control-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

/* === NOTIFICATION BADGE === */
.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 18px;
  height: 18px;
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border-radius: 50%;
  font-size: 10px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--theme-bg, white);
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
  animation: pulse-notification 2s infinite;
}

@keyframes pulse-notification {
  0%, 100% { 
    transform: scale(1); 
  }
  50% { 
    transform: scale(1.1); 
  }
}

/* === USER PROFILE BUTTON === */
.user-profile-btn {
  display: flex;
  align-items: center;
  padding: 4px;
  gap: 8px;
  border-radius: 20px;
  background: var(--theme-card, rgba(255, 255, 255, 0.8));
  border: 1px solid var(--theme-border, rgba(229, 231, 235, 0.5));
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(8px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.user-profile-btn:hover {
  background: var(--theme-primary-50, #eff6ff);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--theme-primary-500, #3b82f6), var(--theme-secondary-500, #8b5cf6));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.user-info {
  text-align: left;
  margin-right: 4px;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--theme-text, #1f2937);
  line-height: 1.2;
  margin: 0;
}

.user-role {
  font-size: 11px;
  color: var(--theme-text-secondary, #6b7280);
  text-transform: capitalize;
  line-height: 1.2;
  margin: 0;
}

.chevron-icon {
  color: var(--theme-text-secondary, #6b7280);
  transition: transform 0.2s ease;
}

.user-profile-btn:hover .chevron-icon {
  transform: rotate(180deg);
}

/* === MATERIAL DESIGN DROPDOWNS === */
.material-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  min-width: 280px;
  background: var(--theme-card, white);
  border: 1px solid var(--theme-border, #e5e7eb);
  border-radius: 12px;
  box-shadow: 
    0 10px 25px rgba(0, 0, 0, 0.15),
    0 4px 12px rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(12px);
  z-index: 50;
  overflow: hidden;
  transform: scale(0.95) translateY(-10px);
  opacity: 0;
  animation: dropdown-appear 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes dropdown-appear {
  to {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

.dropdown-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--theme-border, #f3f4f6);
  background: linear-gradient(135deg, var(--theme-primary-50, #eff6ff) 0%, var(--theme-secondary-50, #faf5ff) 100%);
}

.dropdown-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--theme-text, #1f2937);
  margin: 0 0 4px 0;
}

.dropdown-subtitle {
  font-size: 12px;
  color: var(--theme-text-secondary, #6b7280);
  margin: 0;
}

/* === THEME DROPDOWN SPECIFIC === */
.theme-dropdown {
  width: 320px;
}

.theme-options {
  padding: 12px;
  max-height: 300px;
  overflow-y: auto;
}

.theme-option-btn {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 12px;
  margin-bottom: 8px;
  border: 2px solid var(--theme-border, #e5e7eb);
  border-radius: 8px;
  background: var(--theme-surface, white);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.theme-option-btn:last-child {
  margin-bottom: 0;
}

.theme-option-btn:hover {
  border-color: var(--theme-primary-300, #93c5fd);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.theme-option-btn.active {
  border-color: var(--theme-primary-500, #3b82f6);
  background: linear-gradient(135deg, var(--theme-primary-50, #eff6ff) 0%, var(--theme-secondary-50, #faf5ff) 100%);
  box-shadow: 0 4px 12px var(--theme-primary-200, rgba(59, 130, 246, 0.3));
}

.theme-preview-mini {
  width: 40px;
  height: 32px;
  border-radius: 6px;
  margin-right: 12px;
  position: relative;
  overflow: hidden;
  border: 1px solid var(--theme-border, #e5e7eb);
  flex-shrink: 0;
}

/* Theme Preview Colors */
.theme-preview-mini[data-theme="ocean-breeze"] { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #e0f2fe 100%); }
.theme-preview-mini[data-theme="cherry-blossom"] { background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #faf5ff 100%); }
.theme-preview-mini[data-theme="golden-sunset"] { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fed7aa 100%); }
.theme-preview-mini[data-theme="dark-blue-neon"] { background: linear-gradient(135deg, #0a0f1c 0%, #1a1f2e 50%, #2a2f3e 100%); }

.mini-header {
  height: 8px;
  background: rgba(255, 255, 255, 0.8);
  margin: 2px;
  border-radius: 2px;
}

.theme-preview-mini[data-theme="dark-blue-neon"] .mini-header {
  background: rgba(42, 47, 62, 0.8);
}

.mini-content {
  position: absolute;
  bottom: 2px;
  left: 2px;
  right: 2px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.mini-bar {
  height: 6px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 2px;
}

.mini-bar.short {
  width: 60%;
}

.theme-preview-mini[data-theme="dark-blue-neon"] .mini-bar {
  background: rgba(42, 47, 62, 0.6);
}

.theme-info {
  flex: 1;
  text-align: left;
}

.theme-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--theme-text, #1f2937);
  margin: 0 0 2px 0;
}

.theme-desc {
  font-size: 12px;
  color: var(--theme-text-secondary, #6b7280);
  margin: 0;
}

.theme-check {
  width: 20px;
  height: 20px;
  background: var(--theme-primary-500, #3b82f6);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* === USER DROPDOWN SPECIFIC === */
.user-dropdown {
  width: 280px;
}

.dropdown-user-header {
  padding: 20px;
  background: linear-gradient(135deg, var(--theme-primary-50, #eff6ff) 0%, var(--theme-secondary-50, #faf5ff) 100%);
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-avatar-large {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--theme-primary-500, #3b82f6), var(--theme-secondary-500, #8b5cf6));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  flex-shrink: 0;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name-large {
  font-size: 16px;
  font-weight: 600;
  color: var(--theme-text, #1f2937);
  margin: 0 0 2px 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-role-large {
  font-size: 12px;
  color: var(--theme-text-secondary, #6b7280);
  text-transform: capitalize;
  margin: 0 0 4px 0;
}

.user-email {
  font-size: 11px;
  color: var(--theme-text-secondary, #9ca3af);
  margin: 0;
  word-break: break-all;
}

.dropdown-divider {
  height: 1px;
  background: var(--theme-border, #e5e7eb);
  margin: 0;
}

.dropdown-menu {
  padding: 8px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.dropdown-item:hover {
  background: var(--theme-primary-50, #eff6ff);
  transform: translateX(4px);
}

.dropdown-item.danger {
  color: #dc2626;
}

.dropdown-item.danger:hover {
  background: #fef2f2;
  color: #b91c1c;
}

.item-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  color: var(--theme-text-secondary, #6b7280);
  flex-shrink: 0;
}

.dropdown-item.danger .item-icon {
  color: inherit;
}

.item-text {
  font-size: 14px;
  font-weight: 500;
  color: var(--theme-text, #1f2937);
}

.dropdown-item.danger .item-text {
  color: inherit;
}

/* === RIPPLE EFFECT === */
.nav-control-btn::after,
.user-profile-btn::after,
.theme-option-btn::after,
.dropdown-item::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  transform: translate(-50%, -50%);
  transition: width 0.3s ease, height 0.3s ease;
}

.nav-control-btn:active::after,
.user-profile-btn:active::after,
.theme-option-btn:active::after,
.dropdown-item:active::after {
  width: 200px;
  height: 200px;
}

/* === DARK BLUE NEON THEME OVERRIDES === */
[data-theme="dark-blue-neon"] .nav-control-btn {
  background: rgba(42, 47, 62, 0.8);
  border-color: rgba(51, 65, 85, 0.8);
  color: #94a3b8;
}

[data-theme="dark-blue-neon"] .nav-control-btn:hover {
  background: rgba(0, 212, 255, 0.1);
  color: #00d4ff;
  box-shadow: 0 4px 20px rgba(0, 212, 255, 0.2);
}

[data-theme="dark-blue-neon"] .user-profile-btn {
  background: rgba(42, 47, 62, 0.8);
  border-color: rgba(51, 65, 85, 0.8);
}

[data-theme="dark-blue-neon"] .user-profile-btn:hover {
  background: rgba(0, 212, 255, 0.1);
  box-shadow: 0 4px 20px rgba(0, 212, 255, 0.2);
}

[data-theme="dark-blue-neon"] .material-dropdown {
  background: rgba(26, 31, 46, 0.95);
  border-color: rgba(51, 65, 85, 0.8);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
}

[data-theme="dark-blue-neon"] .dropdown-header {
  background: linear-gradient(135deg, rgba(0, 212, 255, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
  border-color: rgba(51, 65, 85, 0.8);
}

[data-theme="dark-blue-neon"] .theme-option-btn {
  background: rgba(42, 47, 62, 0.8);
  border-color: rgba(51, 65, 85, 0.8);
}

[data-theme="dark-blue-neon"] .theme-option-btn:hover {
  border-color: rgba(0, 212, 255, 0.5);
  box-shadow: 0 4px 20px rgba(0, 212, 255, 0.2);
}

[data-theme="dark-blue-neon"] .theme-option-btn.active {
  border-color: #00d4ff;
  background: rgba(0, 212, 255, 0.1);
  box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
}

[data-theme="dark-blue-neon"] .dropdown-item:hover {
  background: rgba(0, 212, 255, 0.1);
}

[data-theme="dark-blue-neon"] .dropdown-user-header {
  background: linear-gradient(135deg, rgba(0, 212, 255, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
}
</style>
