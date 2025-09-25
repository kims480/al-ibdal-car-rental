<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
          <div class="flex">
            <div class="flex flex-shrink-0 items-center">
              <h1 class="text-xl font-bold text-gray-900">AL IBDAL TRADING LLC</h1>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link
                to="/partner-dashboard"
                class="inline-flex items-center border-b-2 border-indigo-500 px-1 pt-1 text-sm font-medium text-gray-900"
              >
                Dashboard
              </router-link>
              <router-link
                to="/partner-service-requests"
                class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                My Service Requests
              </router-link>
            </div>
          </div>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <span class="text-sm text-gray-700">Welcome, {{ user?.name }}</span>
            </div>
            <button
              @click="handleLogout"
              class="ml-4 bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 rounded-md"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Partner Dashboard</h1>
          <p class="text-gray-600">Welcome to your AL IBDAL Partner Service Portal</p>
        </div>

        <!-- Loading indicator -->
        <div v-if="loading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>

        <div v-else>
          <!-- Overview Statistics Cards -->
          <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Service Request Overview</h2>
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
                          Total Requests
                        </dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.total }}</div>
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
                        <span class="text-white text-sm font-bold">P</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          Pending
                        </dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.pending }}</div>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white overflow-hidden shadow rounded-lg border border-blue-100 transition duration-300 hover:shadow-lg">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-bold">IP</span>
                      </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">
                          In Progress
                        </dt>
                        <dd class="flex items-baseline">
                          <div class="text-2xl font-semibold text-gray-900">{{ stats.inProgress }}</div>
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
                          Completed
                        </dt>
                        <dd class="text-2xl font-semibold text-gray-900">
                          {{ stats.completed }}
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Service Requests -->
          <div class="mb-8 bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-800">Recent Service Requests</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      ID
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Service Type
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-if="serviceRequests.length === 0">
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                      No service requests found
                    </td>
                  </tr>
                  <tr v-for="request in displayedRequests" :key="request.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ request.id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(request.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ request.service_type || 'General' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        {
                          'bg-yellow-100 text-yellow-800': request.status === 'pending',
                          'bg-blue-100 text-blue-800': request.status === 'assigned' || request.status === 'in_progress',
                          'bg-green-100 text-green-800': request.status === 'completed',
                          'bg-gray-100 text-gray-800': request.status === 'cancelled'
                        }
                      ]">
                        {{ formatStatus(request.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <router-link 
                        :to="`/partner-service-requests/${request.id}`"
                        class="text-indigo-600 hover:text-indigo-900 mr-2"
                      >
                        View Details
                      </router-link>
                      <button 
                        v-if="request.status === 'pending'"
                        @click="cancelRequest(request.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Cancel
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
              <router-link 
                to="/partner-service-requests"
                class="text-indigo-600 hover:text-indigo-900 font-medium"
              >
                View All Requests →
              </router-link>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white shadow rounded-lg border border-gray-100 mb-8">
            <div class="px-5 py-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <router-link
                  to="/partner-service-requests/new"
                  class="group bg-gradient-to-br from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 p-4 rounded-lg border border-indigo-200 transition-all duration-300"
                >
                  <h4 class="font-medium text-indigo-900 group-hover:translate-x-1 transition-transform">New Service Request</h4>
                  <p class="text-sm text-indigo-700">Submit a new service or maintenance request</p>
                </router-link>
                
                <router-link
                  to="/partner-service-requests"
                  class="group bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 p-4 rounded-lg border border-blue-200 transition-all duration-300"
                >
                  <h4 class="font-medium text-blue-900 group-hover:translate-x-1 transition-transform">Manage Requests</h4>
                  <p class="text-sm text-blue-700">View and manage all your service requests</p>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { usePartnerServiceRequestStore } from '../stores/partnerServiceRequest'

const router = useRouter()
const authStore = useAuthStore()
const partnerServiceRequestStore = usePartnerServiceRequestStore()

const user = computed(() => authStore.user)
const loading = ref(true)
const serviceRequests = computed(() => partnerServiceRequestStore.serviceRequests)
const stats = computed(() => partnerServiceRequestStore.stats)

// Only show the 5 most recent requests in the dashboard
const displayedRequests = computed(() => {
  return serviceRequests.value.slice(0, 5);
})

// Format date for display
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric'
  });
}

// Format status for display
const formatStatus = (status) => {
  switch (status) {
    case 'pending':
      return 'Pending';
    case 'assigned':
      return 'Assigned';
    case 'in_progress':
      return 'In Progress';
    case 'completed':
      return 'Completed';
    case 'cancelled':
      return 'Cancelled';
    default:
      return status.charAt(0).toUpperCase() + status.slice(1);
  }
}

// Cancel a service request
const cancelRequest = async (requestId) => {
  if (confirm('Are you sure you want to cancel this service request?')) {
    const result = await partnerServiceRequestStore.cancelServiceRequest(requestId);
    if (result.success) {
      alert('Service request cancelled successfully.');
    } else {
      alert(`Failed to cancel service request: ${result.error}`);
    }
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  loading.value = true;
  await partnerServiceRequestStore.getPartnerRequests();
  loading.value = false;
})
</script>