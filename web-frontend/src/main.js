import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
const UserProfile = () => import('./views/UserProfile.vue')
import 'virtual:uno.css'
import './style.css'
import App from './App.vue'

// Import views
// Lazy-loaded views for code splitting
const Home = () => import('./views/Home.vue')
const Login = () => import('./views/Login.vue')
const Dashboard = () => import('./views/Dashboard.vue')
const ServiceRequests = () => import('./views/ServiceRequests.vue')
const Cars = () => import('./views/Cars.vue')
const Branches = () => import('./views/Branches.vue')
const Users = () => import('./views/Users.vue')
const Rentals = () => import('./views/Rentals.vue')
const Contracts = () => import('./views/Contracts.vue')
const Invoices = () => import('./views/Invoices.vue')

// Partner views (lazy)
const PartnerDashboard = () => import('./views/PartnerDashboard.vue')
const PartnerServiceRequest = () => import('./views/PartnerServiceRequest.vue')
const PartnerServiceRequestForm = () => import('./views/PartnerServiceRequestForm.vue')

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
      path: '/profile', 
      component: UserProfile, 
      name: 'UserProfile',
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
    meta: { requiresAuth: true, adminOnly: true }
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
    { 
      path: '/customers/add', 
      component: () => import('./views/AddCustomer.vue'), 
      name: 'AddCustomer', 
      meta: { requiresAuth: true } 
    },
    { 
      path: '/car-expenses', 
      component: () => import('./views/CarExpensesTracking.vue'), 
      name: 'CarExpensesTracking', 
      meta: { requiresAuth: true } 
    },
    { 
      path: '/subcontractors', 
      component: () => import('./views/Subcontractors.vue'), 
      name: 'Subcontractors', 
      meta: { requiresAuth: true, adminOnly: true } 
    },
    { 
      path: '/governorates-wilayats', 
      component: () => import('./views/GovernoratesWilayats.vue'), 
      name: 'GovernoratesWilayats', 
      meta: { requiresAuth: true, adminOnly: true } 
    },
    { 
      path: '/wilayat-branch-assignments', 
      component: () => import('./views/WilayatBranchAssignments.vue'), 
      name: 'WilayatBranchAssignments', 
      meta: { requiresAuth: true, adminOnly: true } 
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
    } else if (to.matched.some(record => record.meta.adminOnly) && user?.role !== 'admin') {
      // Redirect non-admins away from admin pages
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
