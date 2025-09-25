<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
          <div class="flex">
            <div class="flex flex-shrink-0 items-center">
              <h1 class="text-xl font-bold text-gray-900">AL IBDAL TRADING LLC</h1>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link
                to="/dashboard"
                class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                Dashboard
              </router-link>
              <router-link
                to="/service-requests"
                class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                Service Requests
              </router-link>
              <router-link
                to="/cars"
                class="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
              >
                Cars
              </router-link>
              <router-link
                to="/branches"
                class="inline-flex items-center border-b-2 border-indigo-500 px-1 pt-1 text-sm font-medium text-gray-900"
              >
                Branches
              </router-link>
            </div>
          </div>
          <div class="flex items-center">
            <span class="mr-4 text-sm text-gray-700">{{ authStore.user?.name }} ({{ authStore.user?.role }})</span>
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

    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Branch Management</h1>
          <button
            v-if="authStore.user?.role === 'admin'"
            @click="showCreateModal = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Add New Branch
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
          <p class="text-red-600">{{ error }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
          <p class="text-green-600">{{ successMessage }}</p>
        </div>

        <!-- Branches Grid -->
        <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <div v-for="branch in branches" :key="branch.id" class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ branch.name }}</h3>
                <span 
                  :class="branch.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ branch.active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              
              <div class="space-y-2 mb-4">
                <p class="text-sm text-gray-600">
                  <span class="font-medium">City:</span> {{ branch.city }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Address:</span> {{ branch.address }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Phone:</span> {{ branch.contact_phone }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Email:</span> {{ branch.contact_email }}
                </p>
              </div>

              <!-- Branch Statistics -->
              <div v-if="branch.statistics" class="bg-gray-50 rounded-lg p-4 mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Statistics</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-gray-500">Service Requests:</span>
                    <span class="font-medium ml-1">{{ branch.statistics.service_requests_count || 0 }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Cars:</span>
                    <span class="font-medium ml-1">{{ branch.statistics.cars_count || 0 }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Active Rentals:</span>
                    <span class="font-medium ml-1">{{ branch.statistics.active_rentals_count || 0 }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Staff:</span>
                    <span class="font-medium ml-1">{{ branch.statistics.staff_count || 0 }}</span>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div v-if="authStore.user?.role === 'admin'" class="flex space-x-2">
                <button
                  @click="editBranch(branch)"
                  class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700"
                >
                  Edit
                </button>
                <button
                  @click="viewStatistics(branch)"
                  class="flex-1 bg-green-600 text-white px-3 py-2 rounded-md text-sm hover:bg-green-700"
                >
                  View Stats
                </button>
                <button
                  @click="toggleBranchStatus(branch)"
                  :class="branch.active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                  class="flex-1 text-white px-3 py-2 rounded-md text-sm"
                >
                  {{ branch.active ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Create/Edit Branch Modal -->
        <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
              <h3 class="text-lg font-medium text-gray-900 text-center">
                {{ showCreateModal ? 'Add New Branch' : 'Edit Branch' }}
              </h3>
              <div class="mt-6">
                <form @submit.prevent="showCreateModal ? createBranch() : updateBranch()">
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                    <input
                      v-model="branchForm.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input
                      v-model="branchForm.city"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea
                      v-model="branchForm.address"
                      required
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    ></textarea>
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input
                      v-model="branchForm.contact_phone"
                      type="tel"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                  </div>
                  
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input
                      v-model="branchForm.contact_email"
                      type="email"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                  </div>
                  
                  <div class="mb-6">
                    <label class="flex items-center">
                      <input
                        v-model="branchForm.active"
                        type="checkbox"
                        class="form-checkbox h-4 w-4 text-indigo-600"
                      >
                      <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                  </div>
                  
                  <div class="flex justify-end space-x-3">
                    <button
                      type="button"
                      @click="cancelForm"
                      class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="submitting"
                      class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                      {{ submitting ? 'Saving...' : (showCreateModal ? 'Create' : 'Update') }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics Modal -->
        <div v-if="showStatsModal && selectedBranch" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div class="relative top-20 mx-auto p-5 border w-2/3 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
              <h3 class="text-lg font-medium text-gray-900 text-center mb-6">
                {{ selectedBranch.name }} - Detailed Statistics
              </h3>
              
              <div v-if="selectedBranch.detailedStats" class="space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ selectedBranch.detailedStats.service_requests_count || 0 }}</div>
                    <div class="text-sm text-blue-800">Service Requests</div>
                  </div>
                  <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ selectedBranch.detailedStats.cars_count || 0 }}</div>
                    <div class="text-sm text-green-800">Total Cars</div>
                  </div>
                  <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">{{ selectedBranch.detailedStats.active_rentals_count || 0 }}</div>
                    <div class="text-sm text-yellow-800">Active Rentals</div>
                  </div>
                  <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ selectedBranch.detailedStats.staff_count || 0 }}</div>
                    <div class="text-sm text-purple-800">Staff Members</div>
                  </div>
                </div>

                <!-- Revenue Information -->
                <div class="bg-gray-50 p-6 rounded-lg">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Revenue Overview</h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <div class="text-lg font-semibold text-gray-900">OMR {{ selectedBranch.detailedStats.monthly_revenue || '0.00' }}</div>
                      <div class="text-sm text-gray-600">This Month</div>
                    </div>
                    <div>
                      <div class="text-lg font-semibold text-gray-900">OMR {{ selectedBranch.detailedStats.yearly_revenue || '0.00' }}</div>
                      <div class="text-sm text-gray-600">This Year</div>
                    </div>
                    <div>
                      <div class="text-lg font-semibold text-gray-900">OMR {{ selectedBranch.detailedStats.total_revenue || '0.00' }}</div>
                      <div class="text-sm text-gray-600">Total Revenue</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="flex justify-center mt-6">
                <button
                  @click="showStatsModal = false"
                  class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200"
                >
                  Close
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const branches = ref([])
const loading = ref(true)
const error = ref('')
const successMessage = ref('')

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showStatsModal = ref(false)
const selectedBranch = ref(null)
const submitting = ref(false)

// Form data
const branchForm = ref({
  name: '',
  city: '',
  address: '',
  contact_phone: '',
  contact_email: '',
  active: true
})

// Methods
const fetchBranches = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const response = await axios.get('/branches', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    branches.value = response.data.data || response.data
    
    // Fetch statistics for each branch
    for (const branch of branches.value) {
      try {
        const statsResponse = await axios.get(`/branches/${branch.id}/statistics`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        })
        branch.statistics = statsResponse.data.data || statsResponse.data
      } catch (err) {
        branch.statistics = {}
      }
    }
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch branches'
  } finally {
    loading.value = false
  }
}

const createBranch = async () => {
  try {
    submitting.value = true
    error.value = ''
    
    const response = await axios.post('/branches', branchForm.value, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    successMessage.value = 'Branch created successfully'
    showCreateModal.value = false
    resetForm()
    await fetchBranches()
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create branch'
  } finally {
    submitting.value = false
  }
}

const editBranch = (branch) => {
  selectedBranch.value = branch
  branchForm.value = { ...branch }
  showEditModal.value = true
}

const updateBranch = async () => {
  try {
    submitting.value = true
    error.value = ''
    
    const response = await axios.put(`/branches/${selectedBranch.value.id}`, branchForm.value, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    successMessage.value = 'Branch updated successfully'
    showEditModal.value = false
    resetForm()
    await fetchBranches()
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update branch'
  } finally {
    submitting.value = false
  }
}

const toggleBranchStatus = async (branch) => {
  try {
    const response = await axios.put(`/branches/${branch.id}`, 
      { active: !branch.active }, 
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      }
    )
    
    branch.active = !branch.active
    successMessage.value = `Branch ${branch.active ? 'activated' : 'deactivated'} successfully`
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update branch status'
  }
}

const viewStatistics = async (branch) => {
  try {
    const response = await axios.get(`/branches/${branch.id}/statistics`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    selectedBranch.value = {
      ...branch,
      detailedStats: response.data.data || response.data
    }
    showStatsModal.value = true
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch branch statistics'
  }
}

const resetForm = () => {
  branchForm.value = {
    name: '',
    city: '',
    address: '',
    contact_phone: '',
    contact_email: '',
    active: true
  }
  selectedBranch.value = null
}

const cancelForm = () => {
  showCreateModal.value = false
  showEditModal.value = false
  resetForm()
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchBranches()
})
</script>