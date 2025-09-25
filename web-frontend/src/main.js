import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import 'virtual:uno.css'
import './style.css'
import App from './App.vue'

// Import views
import Home from './views/Home.vue'
import Login from './views/Login.vue'
import Dashboard from './views/Dashboard.vue'
import ServiceRequests from './views/ServiceRequests.vue'
import Cars from './views/Cars.vue'
import Branches from './views/Branches.vue'
import Users from './views/Users.vue'
import Rentals from './views/Rentals.vue'
import Contracts from './views/Contracts.vue'
import Invoices from './views/Invoices.vue'

// Import partner views
import PartnerDashboard from './views/PartnerDashboard.vue'
import PartnerServiceRequest from './views/PartnerServiceRequest.vue'
import PartnerServiceRequestForm from './views/PartnerServiceRequestForm.vue'

// Import components
import AppFooter from './components/AppFooter.vue'

// Router configuration
const routes = [
  { path: '/', component: Home, name: 'Home' },
  { path: '/login', component: Login, name: 'Login' },
  { 
    path: '/dashboard', 
    component: Dashboard, 
    name: 'Dashboard',
    meta: { requiresAuth: true }
  },
  { 
    path: '/service-requests', 
    component: ServiceRequests, 
    name: 'ServiceRequests',
    meta: { requiresAuth: true }
  },
  { 
    path: '/cars', 
    component: Cars, 
    name: 'Cars',
    meta: { requiresAuth: true }
  },
  { 
    path: '/branches', 
    component: Branches, 
    name: 'Branches',
    meta: { requiresAuth: true }
  },
  { 
    path: '/users', 
    component: Users, 
    name: 'Users',
    meta: { requiresAuth: true }
  },
  { 
    path: '/rentals', 
    component: Rentals, 
    name: 'Rentals',
    meta: { requiresAuth: true }
  },
  { 
    path: '/contracts', 
    component: Contracts, 
    name: 'Contracts',
    meta: { requiresAuth: true }
  },
  { 
    path: '/invoices', 
    component: Invoices, 
    name: 'Invoices',
    meta: { requiresAuth: true }
  },
  // Partner routes
  { 
    path: '/partner-dashboard', 
    component: PartnerDashboard, 
    name: 'PartnerDashboard',
    meta: { requiresAuth: true, partnerOnly: true }
  },
  { 
    path: '/partner-service-requests', 
    component: PartnerServiceRequest, 
    name: 'PartnerServiceRequest',
    meta: { requiresAuth: true, partnerOnly: true }
  },
  { 
    path: '/partner-service-requests/new', 
    component: PartnerServiceRequestForm, 
    name: 'NewPartnerServiceRequest',
    meta: { requiresAuth: true, partnerOnly: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token')
  const userStr = localStorage.getItem('user')
  let user = null
  
  if (userStr) {
    try {
      user = JSON.parse(userStr)
    } catch (e) {
      console.error('Error parsing user data:', e)
    }
  }
  
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!token) {
      next('/login')
    } else if (to.matched.some(record => record.meta.partnerOnly) && user?.role !== 'partner') {
      // Redirect non-partners away from partner pages
      next('/dashboard')
    } else {
      next()
    }
  } else {
    next()
  }
})

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
app.mount('#app')
