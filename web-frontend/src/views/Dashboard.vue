<template>
  <div>
    <!-- Main content -->
    <main>
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-600">Welcome to AL IBDAL TRADING LLC Car Rental Management System</p>
        </div>

        <!-- Loading indicator -->
        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>

        <div v-else>
          <!-- Overview Statistics Cards -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Business Overview</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <div class="bg-white overflow-hidden shadow rounded-lg border border-indigo-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">SR</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Service Requests
                        </dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.serviceRequests }}</div>
                          <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                            <span>+12.5%</span>
                          </div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-green-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">C</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Available Cars
                        </dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.availableCars }}</div>
                          <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                            <span>+3 this week</span>
                          </div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-yellow-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">R</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Active Rentals
                        </dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.activeRentals }}</div>
                          <div class="ml-2 flex items-baseline text-sm font-semibold text-yellow-600">
                            <span>+8.3%</span>
                          </div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-purple-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">B</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Branches
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                          {{ stats.branches }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Financial & Business Metrics -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Business Metrics</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <div class="bg-white overflow-hidden shadow rounded-lg border border-blue-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">U</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Total Users
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                          {{ stats.totalUsers }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-pink-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">C</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Total Contracts
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                          {{ stats.totalContracts }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">I</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Total Invoices
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                          {{ stats.totalInvoices }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-emerald-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">₹</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Total Revenue
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                          {{ formatCurrency(stats.totalRevenue) }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            <div class="bg-white p-5 rounded-lg shadow border border-gray-100">
              <h3 class="font-semibold text-gray-800 mb-4">Monthly Revenue</h3>
              <div class="dashboard-chart">
                <Line 
                  :data="revenueChartData"
                  :options="lineChartOptions"
                />
              </div>
            </div>
            
            <div class="bg-white p-5 rounded-lg shadow border border-gray-100">
              <h3 class="font-semibold text-gray-800 mb-4">Rentals by Branch</h3>
              <div class="dashboard-chart">
                <Bar 
                  :data="rentalsByBranchData"
                  :options="barChartOptions"
                />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="bg-white p-5 rounded-lg shadow border border-gray-100">
              <h3 class="font-semibold text-gray-800 mb-4">Car Fleet Status</h3>
              <div class="dashboard-chart">
                <Doughnut 
                  :data="carUsageChartData"
                  :options="doughnutChartOptions"
                />
              </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="bg-white p-5 rounded-lg shadow border border-gray-100 col-span-1 md:col-span-2">
              <h3 class="font-semibold text-gray-800 mb-4">Recent Activities</h3>
              <div class="space-y-4">
                <div v-for="activity in recentActivities" :key="activity.id" 
                    :class="['activity-item', activity.type]">
                  <p class="text-gray-900">{{ activity.description }}</p>
                  <p class="text-sm text-gray-500">{{ activity.time }}</p>
                </div>
              </div>
              <div class="mt-4 text-right">
                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">View all activities →</a>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white shadow rounded-lg border border-gray-100 mb-8">
            <div class="px-5 py-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <router-link
                  to="/service-requests"
                  class="group bg-gradient-to-br from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 p-4 rounded-lg border border-indigo-200 transition-all duration-300"
                >
                  <h4 class="font-medium text-indigo-900 group-hover:translate-x-1 transition-transform">View Service Requests</h4>
                  <p class="text-sm text-indigo-700">Manage customer rental requests</p>
                </router-link>
                
                <router-link
                  to="/cars"
                  class="group bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 p-4 rounded-lg border border-green-200 transition-all duration-300"
                >
                  <h4 class="font-medium text-green-900 group-hover:translate-x-1 transition-transform">Manage Cars</h4>
                  <p class="text-sm text-green-700">View and update car inventory</p>
                </router-link>
                
                <router-link
                  v-if="user?.role === 'admin'"
                  to="/branches"
                  class="group bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 p-4 rounded-lg border border-purple-200 transition-all duration-300"
                >
                  <h4 class="font-medium text-purple-900 group-hover:translate-x-1 transition-transform">Manage Branches</h4>
                  <p class="text-sm text-purple-700">Configure branch settings</p>
                </router-link>
                  <router-link
                    v-if="user?.role === 'admin'"
                    to="/users"
                    class="group bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 p-4 rounded-lg border border-blue-200 transition-all duration-300"
                  >
                    <h4 class="font-medium text-blue-900 group-hover:translate-x-1 transition-transform">Manage Users</h4>
                    <p class="text-sm text-blue-700">Add, edit, and remove users</p>
                  </router-link>
                  <router-link
                    to="/rentals"
                    class="group bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 p-4 rounded-lg border border-yellow-200 transition-all duration-300"
                  >
                    <h4 class="font-medium text-yellow-900 group-hover:translate-x-1 transition-transform">Manage Rentals</h4>
                    <p class="text-sm text-yellow-700">View and manage rental contracts</p>
                  </router-link>
                  <router-link
                    to="/contracts"
                    class="group bg-gradient-to-br from-pink-50 to-pink-100 hover:from-pink-100 hover:to-pink-200 p-4 rounded-lg border border-pink-200 transition-all duration-300"
                  >
                    <h4 class="font-medium text-pink-900 group-hover:translate-x-1 transition-transform">Manage Contracts</h4>
                    <p class="text-sm text-pink-700">View and manage contracts</p>
                  </router-link>
                  <router-link
                    to="/invoices"
                    class="group bg-gradient-to-br from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 p-4 rounded-lg border border-gray-200 transition-all duration-300"
                  >
                    <h4 class="font-medium text-gray-900 group-hover:translate-x-1 transition-transform">Manage Invoices</h4>
                    <p class="text-sm text-gray-700">View and manage invoices</p>
                  </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import { Bar, Line, Doughnut, Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement, ArcElement } from 'chart.js'

// Register ChartJS components
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, LineElement, PointElement, ArcElement)

const router = useRouter()
const authStore = useAuthStore()
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const user = computed(() => authStore.user)
const isLoading = ref(true)
const stats = ref({
  serviceRequests: 0,
  availableCars: 0,
  activeRentals: 0,
  branches: 4,
  totalUsers: 0,
  totalContracts: 0,
  totalInvoices: 0,
  totalRevenue: 0
})

// Chart data
const revenueChartData = reactive({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  datasets: [
    {
      label: 'Monthly Revenue (AED)',
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgba(75, 192, 192, 1)',
      borderWidth: 2,
      tension: 0.3,
      fill: true,
      data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
    }
  ]
})

const carUsageChartData = reactive({
  labels: ['Available', 'In Maintenance', 'Rented'],
  datasets: [
    {
      label: 'Car Status',
      backgroundColor: ['#10B981', '#F59E0B', '#3B82F6'],
      data: [0, 0, 0]
    }
  ]
})

const rentalsByBranchData = reactive({
  labels: [],
  datasets: [
    {
      label: 'Rentals by Branch',
      backgroundColor: ['#8B5CF6', '#EC4899', '#F97316', '#14B8A6', '#6366F1'],
      data: []
    }
  ]
})

// Chart options
const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top'
    },
    title: {
      display: true,
      text: 'Rentals by Branch'
    }
  }
}

const lineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top'
    },
    title: {
      display: true,
      text: 'Monthly Revenue'
    }
  }
}

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top'
    },
    title: {
      display: true,
      text: 'Car Fleet Status'
    }
  }
}

// Recent activities
const recentActivities = ref([])

const fetchDashboardStats = async () => {
  try {
    isLoading.value = true
    
    // In a real app, this would be fetched from your API
    // Example: const response = await axios.get(`${API_URL}/dashboard/stats`)
    
    // For demo purposes, simulate API response with random data
    setTimeout(() => {
      // Basic stats
      stats.value = {
        serviceRequests: Math.floor(Math.random() * 50) + 10,
        availableCars: Math.floor(Math.random() * 80) + 20,
        activeRentals: Math.floor(Math.random() * 40) + 5,
        branches: 4,
        totalUsers: Math.floor(Math.random() * 100) + 50,
        totalContracts: Math.floor(Math.random() * 200) + 100,
        totalInvoices: Math.floor(Math.random() * 300) + 150,
        totalRevenue: Math.floor(Math.random() * 500000) + 100000
      }
      
      // Revenue chart data
      revenueChartData.datasets[0].data = Array.from({ length: 12 }, () => 
        Math.floor(Math.random() * 50000) + 20000
      )
      
      // Car usage chart data
      carUsageChartData.datasets[0].data = [
        stats.value.availableCars,
        Math.floor(Math.random() * 10) + 2,
        stats.value.activeRentals
      ]
      
      // Rentals by branch data
      const branchNames = ['Dubai Main', 'Abu Dhabi', 'Sharjah', 'Al Ain']
      rentalsByBranchData.labels = branchNames
      rentalsByBranchData.datasets[0].data = branchNames.map(() => 
        Math.floor(Math.random() * 30) + 5
      )
      
      // Recent activities
      recentActivities.value = [
        { id: 1, type: 'rental', description: 'New rental created for customer Ahmed', time: '10 minutes ago' },
        { id: 2, type: 'return', description: 'Vehicle BMW X5 returned to Dubai branch', time: '45 minutes ago' },
        { id: 3, type: 'maintenance', description: 'Toyota Land Cruiser scheduled for maintenance', time: '2 hours ago' },
        { id: 4, type: 'invoice', description: 'Invoice #INV-2023-089 paid', time: '3 hours ago' },
        { id: 5, type: 'contract', description: 'New contract signed with corporate client', time: '5 hours ago' }
      ]
      
      isLoading.value = false
    }, 800)
    
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
    isLoading.value = false
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-AE', { style: 'currency', currency: 'AED' }).format(value)
}

// Logout handled in layout

onMounted(() => {
  // Redirect partners to their dashboard
  if (user.value?.role === 'partner') {
    router.push('/partner-dashboard')
    return
  }
  
  fetchDashboardStats()
})
</script>

<style scoped>
.dashboard-chart {
  height: 300px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1rem;
}

.activity-item {
  position: relative;
  padding-left: 1.5rem;
}

.activity-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.375rem;
  height: 0.75rem;
  width: 0.75rem;
  border-radius: 50%;
  background-color: #6366F1;
}

.activity-item.rental::before { background-color: #10B981; }
.activity-item.return::before { background-color: #3B82F6; }
.activity-item.maintenance::before { background-color: #F59E0B; }
.activity-item.invoice::before { background-color: #8B5CF6; }
.activity-item.contract::before { background-color: #EC4899; }
</style>