<template>
  <div>
    <main>
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p class="text-gray-600">Welcome to AL IBDAL TRADING LLC Car Rental Management System</p>
        </div>

        <div v-if="isLoading" class="space-y-6">
          <div class="flex justify-center items-center h-64">
            <div class="text-center">
              <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600"></div>
              <p class="mt-4 text-gray-600">Loading Dashboard...</p>
            </div>
          </div>
        </div>

        <div v-else>
          <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Overview</h3>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Service Requests Card -->
              <div class="bg-white overflow-hidden shadow rounded-lg border">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">SR</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Service Requests</dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.serviceRequests }}</div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Available Cars Card -->
              <div class="bg-white overflow-hidden shadow rounded-lg border">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">C</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Available Cars</dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.availableCars }}</div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Active Rentals Card -->
              <div class="bg-white overflow-hidden shadow rounded-lg border">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">R</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Rentals</dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.activeRentals }}</div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Total Revenue Card -->
              <div class="bg-white overflow-hidden shadow rounded-lg border">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">₹</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.totalRevenue) }}</div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions Section -->
          <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
              <router-link to="/service-requests" class="group bg-gradient-to-br from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 p-4 rounded-lg border border-indigo-200 transition-all duration-300">
                <h4 class="font-medium text-indigo-900 group-hover:translate-x-1 transition-transform">Service Requests</h4>
                <p class="text-sm text-indigo-700">Manage requests</p>
              </router-link>

              <router-link to="/cars" class="group bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 p-4 rounded-lg border border-green-200 transition-all duration-300">
                <h4 class="font-medium text-green-900 group-hover:translate-x-1 transition-transform">Manage Cars</h4>
                <p class="text-sm text-green-700">Fleet management</p>
              </router-link>

              <router-link to="/rentals" class="group bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 p-4 rounded-lg border border-yellow-200 transition-all duration-300">
                <h4 class="font-medium text-yellow-900 group-hover:translate-x-1 transition-transform">Active Rentals</h4>
                <p class="text-sm text-yellow-700">View rentals</p>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const isLoading = ref(true)

const stats = ref({
  serviceRequests: 0,
  availableCars: 0,
  activeRentals: 0,
  totalRevenue: 0
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-AE', { style: 'currency', currency: 'AED' }).format(value)
}

const fetchDashboardStats = async () => {
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    stats.value = {
      serviceRequests: 25,
      availableCars: 18,
      activeRentals: 7,
      totalRevenue: 15430.50
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchDashboardStats()
})
</script>

<style scoped>
.dashboard-chart {
  height: 300px;
}
</style>
