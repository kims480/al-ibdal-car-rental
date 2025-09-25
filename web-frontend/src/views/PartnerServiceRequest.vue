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
                class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                Dashboard
              </router-link>
              <router-link
                to="/partner-service-requests"
                class="inline-flex items-center border-b-2 border-indigo-500 px-1 pt-1 text-sm font-medium text-gray-900"
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
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">My Service Requests</h1>
            <p class="text-gray-600">View and manage all your service and maintenance requests</p>
          </div>
          <router-link
            to="/partner-service-requests/new"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            New Request
          </router-link>
        </div>

        <!-- Loading indicator -->
        <div v-if="loading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>

        <!-- Filters -->
        <div v-else class="bg-white shadow rounded-lg mb-6 p-4">
          <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
              <label for="status-filter" class="block text-sm font-medium text-gray-700 mr-2">Status:</label>
              <select
                id="status-filter"
                v-model="filters.status"
                class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="assigned">Assigned</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="flex items-center">
              <label for="type-filter" class="block text-sm font-medium text-gray-700 mr-2">Type:</label>
              <select
                id="type-filter"
                v-model="filters.serviceType"
                class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All</option>
                <option value="maintenance">Maintenance</option>
                <option value="repair">Repair</option>
                <option value="inspection">Inspection</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="flex items-center">
              <label for="search-filter" class="block text-sm font-medium text-gray-700 mr-2">Search:</label>
              <input
                type="text"
                id="search-filter"
                v-model="filters.search"
                placeholder="Search requests..."
                class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <button
              @click="resetFilters"
              class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
            >
              Reset Filters
            </button>
          </div>
        </div>

        <!-- Service Requests Table -->
        <div v-if="!loading" class="bg-white shadow rounded-lg overflow-hidden">
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
                    Priority
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Assigned To
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="filteredRequests.length === 0">
                  <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                    No service requests found
                  </td>
                </tr>
                <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-gray-50">
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
                        'bg-green-100 text-green-800': request.priority === 'low',
                        'bg-blue-100 text-blue-800': request.priority === 'medium',
                        'bg-yellow-100 text-yellow-800': request.priority === 'high',
                        'bg-red-100 text-red-800': request.priority === 'urgent'
                      }
                    ]">
                      {{ formatPriority(request.priority) }}
                    </span>
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
                    {{ request.subcontractor?.name || 'Not Assigned' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button
                      @click="viewRequestDetails(request.id)"
                      class="text-indigo-600 hover:text-indigo-900 mr-2"
                    >
                      View
                    </button>
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
        </div>

        <!-- View Request Modal -->
        <div v-if="showViewModal" class="fixed inset-0 flex items-center justify-center z-50">
          <div class="fixed inset-0 bg-black opacity-50"></div>
          <div class="relative bg-white rounded-lg max-w-2xl w-full mx-4 shadow-xl p-6">
            <div class="flex justify-between items-start">
              <h3 class="text-lg font-semibold text-gray-900">Service Request Details</h3>
              <button 
                @click="showViewModal = false" 
                class="text-gray-400 hover:text-gray-500"
              >
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <div v-if="loading" class="mt-4 flex justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
            </div>
            
            <div v-else-if="currentRequest" class="mt-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Request ID</h4>
                  <p class="text-gray-900">{{ currentRequest.id }}</p>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Status</h4>
                  <p :class="[
                    'inline-block px-2 py-1 text-xs font-medium rounded-full',
                    {
                      'bg-yellow-100 text-yellow-800': currentRequest.status === 'pending',
                      'bg-blue-100 text-blue-800': currentRequest.status === 'assigned' || currentRequest.status === 'in_progress',
                      'bg-green-100 text-green-800': currentRequest.status === 'completed',
                      'bg-gray-100 text-gray-800': currentRequest.status === 'cancelled'
                    }
                  ]">
                    {{ formatStatus(currentRequest.status) }}
                  </p>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Created On</h4>
                  <p class="text-gray-900">{{ formatDate(currentRequest.created_at) }}</p>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Service Type</h4>
                  <p class="text-gray-900">{{ currentRequest.service_type || 'General' }}</p>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-500">Priority</h4>
                  <p :class="[
                    'inline-block px-2 py-1 text-xs font-medium rounded-full',
                    {
                      'bg-green-100 text-green-800': currentRequest.priority === 'low',
                      'bg-blue-100 text-blue-800': currentRequest.priority === 'medium',
                      'bg-yellow-100 text-yellow-800': currentRequest.priority === 'high',
                      'bg-red-100 text-red-800': currentRequest.priority === 'urgent'
                    }
                  ]">
                    {{ formatPriority(currentRequest.priority) }}
                  </p>
                </div>
                <div v-if="currentRequest.subcontractor">
                  <h4 class="text-sm font-medium text-gray-500">Assigned To</h4>
                  <p class="text-gray-900">{{ currentRequest.subcontractor.name }}</p>
                </div>
              </div>

              <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-500 mb-1">Notes</h4>
                <p class="text-gray-900 whitespace-pre-line p-3 bg-gray-50 rounded">
                  {{ currentRequest.notes || 'No notes provided' }}
                </p>
              </div>

              <div v-if="currentRequest.status === 'pending'" class="mt-6 flex justify-end">
                <button
                  @click="cancelRequest(currentRequest.id)"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  Cancel Request
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { usePartnerServiceRequestStore } from '../stores/partnerServiceRequest'

const router = useRouter()
const authStore = useAuthStore()
const partnerServiceRequestStore = usePartnerServiceRequestStore()

const user = computed(() => authStore.user)
const loading = ref(true)
const serviceRequests = computed(() => partnerServiceRequestStore.serviceRequests)
const showViewModal = ref(false)
const currentRequest = ref(null)

// Filters
const filters = reactive({
  status: '',
  serviceType: '',
  search: ''
})

// Filtered requests based on filter settings
const filteredRequests = computed(() => {
  return serviceRequests.value.filter(request => {
    // Status filter
    if (filters.status && request.status !== filters.status) {
      return false
    }
    
    // Service type filter
    if (filters.serviceType && request.service_type !== filters.serviceType) {
      return false
    }
    
    // Search filter
    if (filters.search) {
      const searchTerm = filters.search.toLowerCase()
      const requestData = [
        request.id?.toString(),
        request.service_type?.toLowerCase(),
        request.notes?.toLowerCase(),
        request.subcontractor?.name?.toLowerCase()
      ].filter(Boolean).join(' ')
      
      if (!requestData.includes(searchTerm)) {
        return false
      }
    }
    
    return true
  })
})

// Format date for display
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric'
  })
}

// Format status for display
const formatStatus = (status) => {
  switch (status) {
    case 'pending':
      return 'Pending'
    case 'assigned':
      return 'Assigned'
    case 'in_progress':
      return 'In Progress'
    case 'completed':
      return 'Completed'
    case 'cancelled':
      return 'Cancelled'
    default:
      return status?.charAt(0).toUpperCase() + status?.slice(1) || ''
  }
}

// Format priority for display
const formatPriority = (priority) => {
  switch (priority) {
    case 'low':
      return 'Low'
    case 'medium':
      return 'Medium'
    case 'high':
      return 'High'
    case 'urgent':
      return 'Urgent'
    default:
      return priority?.charAt(0).toUpperCase() + priority?.slice(1) || 'Medium'
  }
}

// Reset filters
const resetFilters = () => {
  filters.status = ''
  filters.serviceType = ''
  filters.search = ''
}

// View request details
const viewRequestDetails = async (requestId) => {
  loading.value = true
  const result = await partnerServiceRequestStore.getRequestDetails(requestId)
  if (result.success) {
    currentRequest.value = result.data
    showViewModal.value = true
  } else {
    alert(`Error loading request details: ${result.error}`)
  }
  loading.value = false
}

// Cancel a service request
const cancelRequest = async (requestId) => {
  if (confirm('Are you sure you want to cancel this service request?')) {
    loading.value = true
    const result = await partnerServiceRequestStore.cancelServiceRequest(requestId)
    if (result.success) {
      if (showViewModal.value) {
        showViewModal.value = false
      }
      alert('Service request cancelled successfully.')
    } else {
      alert(`Failed to cancel service request: ${result.error}`)
    }
    loading.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  loading.value = true
  await partnerServiceRequestStore.getPartnerRequests()
  loading.value = false
})
</script>