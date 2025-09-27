<template>
  <div class="dashboard-view">
    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading dashboard data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">
        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 class="error-title">Failed to load dashboard</h3>
      <p class="error-message">{{ error }}</p>
      <button @click="loadDashboardData" class="retry-button">Try Again</button>
    </div>

    <!-- Dashboard Content -->
    <template v-else>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard Overview</h1>
      <button @click="loadDashboardData" class="refresh-button" :disabled="loading">
        <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>
    <!-- Stats Overview -->
    <div class="stats-grid">
      <div class="stat-compact">
        <div class="stat-icon bg-blue-500">
          <TruckIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.totalCars || 0 }}</h3>
          <p class="stat-label">Total Cars ({{ stats.availableCars || 0 }} available)</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-green-500">
          <ClockIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.activeRentals || 0 }}</h3>
          <p class="stat-label">Active Rentals</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-yellow-500">
          <WrenchScrewdriverIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.pendingServices || 0 }}</h3>
          <p class="stat-label">Pending Services</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-purple-500">
          <CurrencyDollarIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">${{ (stats.monthlyRevenue || 0).toFixed(2) }}</h3>
          <p class="stat-label">Monthly Revenue</p>
        </div>
      </div>
      
      <!-- Additional stats row -->
      <div class="stat-compact">
        <div class="stat-icon bg-indigo-500">
          <ReceiptPercentIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.totalInvoices || 0 }}</h3>
          <p class="stat-label">Total Invoices ({{ stats.pendingInvoices || 0 }} pending)</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-emerald-500">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.completedServices || 0 }}</h3>
          <p class="stat-label">Completed Services</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-red-500">
          <TruckIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.rentedCars || 0 }}</h3>
          <p class="stat-label">Cars Currently Rented</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-green-600">
          <CurrencyDollarIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">${{ (stats.totalRevenue || 0).toFixed(2) }}</h3>
          <p class="stat-label">Total Revenue</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h2 class="section-title">Quick Actions</h2>
      <div class="actions-grid">
        <router-link to="/rentals" class="action-card">
          <div class="action-icon bg-blue-100">
            <ClockIcon class="w-6 h-6 text-blue-600" />
          </div>
          <div class="action-content">
            <h3 class="action-title">New Rental</h3>
            <p class="action-desc">Create rental agreement</p>
          </div>
          <ChevronRightIcon class="action-arrow" />
        </router-link>

        <router-link to="/cars" class="action-card">
          <div class="action-icon bg-green-100">
            <TruckIcon class="w-6 h-6 text-green-600" />
          </div>
          <div class="action-content">
            <h3 class="action-title">Fleet Status</h3>
            <p class="action-desc">View car availability</p>
          </div>
          <ChevronRightIcon class="action-arrow" />
        </router-link>

        <router-link to="/service-requests" class="action-card">
          <div class="action-icon bg-yellow-100">
            <WrenchScrewdriverIcon class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="action-content">
            <h3 class="action-title">Service Center</h3>
            <p class="action-desc">Manage services</p>
          </div>
          <ChevronRightIcon class="action-arrow" />
        </router-link>

        <router-link to="/invoices" class="action-card">
          <div class="action-icon bg-purple-100">
            <ReceiptPercentIcon class="w-6 h-6 text-purple-600" />
          </div>
          <div class="action-content">
            <h3 class="action-title">Invoices</h3>
            <p class="action-desc">Billing & payments</p>
          </div>
          <ChevronRightIcon class="action-arrow" />
        </router-link>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
      <h2 class="section-title">Recent Activity</h2>
      <div class="activity-list">
        <div v-if="recentActivities.length === 0" class="empty-state">
          <div class="empty-icon">
            <ClockIcon class="w-12 h-12 text-gray-400" />
          </div>
          <p class="empty-text">No recent activity</p>
        </div>
        
        <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
          <div class="activity-icon">
            <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
          </div>
          <div class="activity-content">
            <p class="activity-title">{{ activity.title }}</p>
            <p class="activity-time">{{ formatTime(activity.created_at) }}</p>
          </div>
          <div v-if="activity.entity_type" class="activity-badge">
            <span :class="`badge-${activity.type}`">{{ activity.type }}</span>
          </div>
        </div>
      </div>
    </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import {
  TruckIcon,
  ClockIcon,
  WrenchScrewdriverIcon,
  CurrencyDollarIcon,
  ChevronRightIcon,
  ReceiptPercentIcon
} from '@heroicons/vue/24/outline'

const auth = useAuthStore()
const stats = ref({
  totalCars: 0,
  availableCars: 0,
  rentedCars: 0,
  maintenanceCars: 0,
  activeRentals: 0,
  pendingServices: 0,
  completedServices: 0,
  totalServiceRequests: 0,
  totalInvoices: 0,
  pendingInvoices: 0,
  sentInvoices: 0,
  paidInvoices: 0,
  monthlyRevenue: 0,
  totalRevenue: 0
})
const recentActivities = ref([])
const loading = ref(true)
const error = ref(null)

const user = computed(() => auth.user)

onMounted(() => {
  loadDashboardData()
})

async function loadDashboardData() {
  try {
    loading.value = true
    error.value = null
    
    // Fetch dashboard data from API
    const response = await axios.get('/dashboard')
    
    if (response.data.success) {
      stats.value = response.data.data.stats
      recentActivities.value = response.data.data.recent_activity || []
    } else {
      throw new Error(response.data.message || 'Failed to load dashboard data')
    }
  } catch (err) {
    console.error('Failed to load dashboard data:', err)
    error.value = err.message || 'Failed to load dashboard data'
    
    // Fallback to default values
    stats.value = {
      totalCars: 0,
      availableCars: 0,
      rentedCars: 0,
      maintenanceCars: 0,
      activeRentals: 0,
      pendingServices: 0,
      completedServices: 0,
      totalServiceRequests: 0,
      totalInvoices: 0,
      pendingInvoices: 0,
      sentInvoices: 0,
      paidInvoices: 0,
      monthlyRevenue: 0,
      totalRevenue: 0
    }
    recentActivities.value = []
  } finally {
    loading.value = false
  }
}

function getActivityIcon(type) {
  const icons = {
    rental: ClockIcon,
    service: WrenchScrewdriverIcon,
    car: TruckIcon,
    invoice: ReceiptPercentIcon
  }
  return icons[type] || ClockIcon
}

function formatTime(timestamp) {
  if (!timestamp) return ''
  
  try {
    const date = new Date(timestamp)
    const now = new Date()
    const diffInMinutes = Math.floor((now - date) / (1000 * 60))
    
    if (diffInMinutes < 1) return 'Just now'
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`
    
    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) return `${diffInHours}h ago`
    
    const diffInDays = Math.floor(diffInHours / 24)
    if (diffInDays < 7) return `${diffInDays}d ago`
    
    return date.toLocaleDateString()
  } catch (error) {
    console.error('Error formatting time:', error)
    return ''
  }
}
</script>

<style scoped>
.dashboard-view {
  max-width: 7xl;
  margin: 0 auto;
  padding: 0;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--theme-border, #e5e7eb);
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--theme-text, #1f2937);
  margin: 0;
}

.refresh-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--theme-primary, #3b82f6);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.refresh-button:hover:not(:disabled) {
  background: var(--theme-primary-hover, #2563eb);
  transform: translateY(-1px);
}

.refresh-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-compact {
  background: var(--theme-card, white);
  border: 1px solid var(--theme-border, #e5e7eb);
  border-radius: 8px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.2s ease;
}

.stat-compact:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--theme-text, #1f2937);
  margin: 0 0 0.25rem 0;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--theme-text-secondary, #6b7280);
  margin: 0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--theme-text, #1f2937);
  margin: 0 0 1rem 0;
}

.quick-actions {
  margin-bottom: 2rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.action-card {
  background: var(--theme-card, white);
  border: 1px solid var(--theme-border, #e5e7eb);
  border-radius: 8px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  transition: all 0.2s ease;
}

.action-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  text-decoration: none;
}

.action-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.action-content {
  flex: 1;
}

.action-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--theme-text, #1f2937);
  margin: 0 0 0.25rem 0;
}

.action-desc {
  font-size: 0.875rem;
  color: var(--theme-text-secondary, #6b7280);
  margin: 0;
}

.action-arrow {
  width: 1.25rem;
  height: 1.25rem;
  color: var(--theme-text-secondary, #6b7280);
  flex-shrink: 0;
}

.recent-activity {
  background: var(--theme-card, white);
  border: 1px solid var(--theme-border, #e5e7eb);
  border-radius: 8px;
  padding: 1.5rem;
}

.activity-list {
  margin-top: 1rem;
}

.empty-state {
  text-align: center;
  padding: 2rem 0;
}

.empty-icon {
  margin-bottom: 0.75rem;
}

.empty-text {
  color: var(--theme-text-secondary, #6b7280);
  font-size: 0.875rem;
  margin: 0;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--theme-border, #f3f4f6);
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 2rem;
  height: 2rem;
  background: var(--theme-primary-50, #eff6ff);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--theme-primary-600, #2563eb);
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--theme-text, #1f2937);
  margin: 0 0 0.25rem 0;
}

.activity-time {
  font-size: 0.75rem;
  color: var(--theme-text-secondary, #6b7280);
  margin: 0;
}

.activity-badge {
  flex-shrink: 0;
}

.badge-rental {
  background: #dbeafe;
  color: #1e40af;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.badge-service {
  background: #fef3c7;
  color: #92400e;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.badge-invoice {
  background: #f3e8ff;
  color: #7c3aed;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.badge-car {
  background: #d1fae5;
  color: #047857;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

/* Theme Responsiveness */
@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-compact,
  .action-card {
    padding: 1rem;
  }
}

/* Loading and Error States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.loading-spinner {
  width: 3rem;
  height: 3rem;
  border: 3px solid var(--theme-border, #e5e7eb);
  border-top: 3px solid var(--theme-primary, #3b82f6);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.error-icon {
  margin-bottom: 1rem;
}

.error-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--theme-text, #1f2937);
  margin: 0 0 0.5rem 0;
}

.error-message {
  color: var(--theme-text-secondary, #6b7280);
  margin: 0 0 2rem 0;
  max-width: 400px;
}

.retry-button {
  background: var(--theme-primary, #3b82f6);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.retry-button:hover {
  background: var(--theme-primary-hover, #2563eb);
  transform: translateY(-1px);
}
</style>