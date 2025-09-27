<template>
  <div class="dashboard-view">
    <!-- Stats Overview -->
    <div class="stats-grid">
      <div class="stat-compact">
        <div class="stat-icon bg-blue-500">
          <TruckIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">{{ stats.totalCars || 0 }}</h3>
          <p class="stat-label">Total Cars</p>
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
          <p class="stat-label">Service Requests</p>
        </div>
      </div>
      
      <div class="stat-compact">
        <div class="stat-icon bg-purple-500">
          <CurrencyDollarIcon class="w-5 h-5 text-white" />
        </div>
        <div class="stat-info">
          <h3 class="stat-value">${{ stats.monthlyRevenue || 0 }}</h3>
          <p class="stat-label">Monthly Revenue</p>
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
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
  activeRentals: 0,
  pendingServices: 0,
  monthlyRevenue: 0
})
const recentActivities = ref([])

const user = computed(() => auth.user)

onMounted(() => {
  loadDashboardData()
})

async function loadDashboardData() {
  try {
    // Load stats
    stats.value = {
      totalCars: 24,
      activeRentals: 8,
      pendingServices: 3,
      monthlyRevenue: 15200
    }
    
    // Load recent activities
    recentActivities.value = []
  } catch (error) {
    console.error('Failed to load dashboard data:', error)
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
  return new Date(timestamp).toLocaleString()
}
</script>

<style scoped>
.dashboard-view {
  max-width: 7xl;
  margin: 0 auto;
  padding: 0;
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
</style>