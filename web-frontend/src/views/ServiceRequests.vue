<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Service Requests</h1>
        <p class="text-gray-600 mt-1">Manage maintenance and service tickets</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Request
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6 p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="assigned">Assigned</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
          <select 
            v-model="filters.priority"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Priorities</option>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="critical">Critical</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
          <div class="flex gap-2">
            <input 
              v-model="filters.start_date"
              type="date" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <input 
              v-model="filters.end_date"
              type="date" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
        </div>
        <div class="flex items-end">
          <button 
            @click="fetchServiceRequests"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-blue-100 p-3 mr-4">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Total Requests</p>
          <p class="text-xl font-bold">{{ statistics.total }}</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-yellow-100 p-3 mr-4">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Pending</p>
          <p class="text-xl font-bold">{{ statistics.pending }}</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-green-100 p-3 mr-4">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Completed</p>
          <p class="text-xl font-bold">{{ statistics.completed }}</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-red-100 p-3 mr-4">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Critical Issues</p>
          <p class="text-xl font-bold">{{ statistics.critical }}</p>
        </div>
      </div>
    </div>

    <!-- Service Requests Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading service requests...</p>
      </div>

      <div v-else-if="serviceRequests.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
        </svg>
        <p>No service requests found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="request in serviceRequests" :key="request.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                #{{ request.id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ request.car?.make }} {{ request.car?.model }}</div>
                  <div class="text-sm text-gray-500">{{ request.car?.license_plate }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ request.issue_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(request.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(request.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(request.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getPriorityClass(request.priority)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatPriority(request.priority) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ request.assignee?.name || 'Unassigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="openViewModal(request)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    View
                  </button>
                  <button 
                    @click="openEditModal(request)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit Request"
                  >
                    Edit
                  </button>
                  <button
                    v-if="canDeleteRequest(request)"
                    @click="confirmDelete(request)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Request"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-gray-900">
            {{ isEditing ? 'Edit Service Request' : 'Create New Service Request' }}
          </h3>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Car *</label>
              <select 
                v-model="form.car_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Car</option>
                <option v-for="car in cars" :key="car.id" :value="car.id">
                  {{ car.make }} {{ car.model }} ({{ car.license_plate }})
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Issue Type *</label>
              <select 
                v-model="form.issue_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Issue Type</option>
                <option value="mechanical">Mechanical</option>
                <option value="electrical">Electrical</option>
                <option value="body_damage">Body Damage</option>
                <option value="tire_wheel">Tire/Wheel</option>
                <option value="interior">Interior</option>
                <option value="routine_maintenance">Routine Maintenance</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority *</label>
              <select 
                v-model="form.priority"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
            <div v-if="isEditing || user?.role === 'admin' || user?.role === 'manager'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select 
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="pending">Pending</option>
                <option value="assigned">Assigned</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div v-if="isEditing || user?.role === 'admin' || user?.role === 'manager'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
            <select 
              v-model="form.assignee_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Unassigned</option>
              <option v-for="technician in technicians" :key="technician.id" :value="technician.id">
                {{ technician.name }} ({{ technician.role }})
              </option>
            </select>
          </div>

          <div v-if="isEditing">
            <label class="block text-sm font-medium text-gray-700 mb-1">Resolution Notes</label>
            <textarea
              v-model="form.resolution_notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div v-if="isEditing && form.status === 'completed'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Completion Date</label>
            <input 
              v-model="form.completed_at"
              type="date" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Request' : 'Submit Request') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-gray-900">
            Service Request Details
          </h3>
        </div>

        <div v-if="selectedRequest" class="space-y-4">
          <div class="flex justify-between items-center">
            <div>
              <span class="text-sm font-medium text-gray-500">Request ID</span>
              <p class="text-xl font-bold text-gray-900">#{{ selectedRequest.id }}</p>
            </div>
            <div>
              <span :class="getStatusClass(selectedRequest.status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                {{ formatStatus(selectedRequest.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Car Information</h4>
              <p class="text-gray-900 font-semibold">{{ selectedRequest.car?.make }} {{ selectedRequest.car?.model }}</p>
              <p class="text-gray-600">{{ selectedRequest.car?.year }} | {{ selectedRequest.car?.license_plate }}</p>
              <p class="text-gray-600">{{ selectedRequest.car?.color }}</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Issue Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Issue Type:</p>
                <p class="text-gray-900">{{ selectedRequest.issue_type }}</p>
                <p class="text-gray-600">Priority:</p>
                <p>
                  <span :class="getPriorityClass(selectedRequest.priority)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ formatPriority(selectedRequest.priority) }}
                  </span>
                </p>
              </div>
            </div>
          </div>

          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedRequest.description }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Status Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Created:</p>
                <p class="text-gray-900">{{ formatDate(selectedRequest.created_at) }}</p>
                <p class="text-gray-600">Updated:</p>
                <p class="text-gray-900">{{ formatDate(selectedRequest.updated_at) }}</p>
                <p class="text-gray-600">Completed:</p>
                <p class="text-gray-900">{{ selectedRequest.completed_at ? formatDate(selectedRequest.completed_at) : 'Not completed' }}</p>
              </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Assignment Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Requested By:</p>
                <p class="text-gray-900">{{ selectedRequest.requester?.name || 'N/A' }}</p>
                <p class="text-gray-600">Assigned To:</p>
                <p class="text-gray-900">{{ selectedRequest.assignee?.name || 'Unassigned' }}</p>
              </div>
            </div>
          </div>

          <div v-if="selectedRequest.resolution_notes" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Resolution Notes</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedRequest.resolution_notes }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              @click="closeViewModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Close
            </button>
            <button 
              @click="openEditModal(selectedRequest)"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
            >
              Edit Request
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const user = computed(() => useAuthStore().user)

const serviceRequests = ref([])
const cars = ref([])
const technicians = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedRequest = ref(null)

const statistics = reactive({
  total: 0,
  pending: 0,
  completed: 0,
  critical: 0
})

const filters = reactive({
  status: '',
  priority: '',
  start_date: '',
  end_date: ''
})

const form = reactive({
  car_id: '',
  issue_type: '',
  description: '',
  priority: 'medium',
  status: 'pending',
  assignee_id: '',
  resolution_notes: '',
  completed_at: ''
})

// Fetch service requests from API
const fetchServiceRequests = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.status) params.status = filters.status
    if (filters.priority) params.priority = filters.priority
    if (filters.start_date) params.start_date = filters.start_date
    if (filters.end_date) params.end_date = filters.end_date

    const response = await axios.get('/service-requests', { params })
    
    if (response.data.success) {
      serviceRequests.value = response.data.data.data || response.data.data
      updateStatistics()
    }
  } catch (error) {
    console.error('Error fetching service requests:', error)
  } finally {
    loading.value = false
  }
}

// Update statistics based on service requests data
const updateStatistics = () => {
  statistics.total = serviceRequests.value.length
  statistics.pending = serviceRequests.value.filter(req => req.status === 'pending' || req.status === 'assigned').length
  statistics.completed = serviceRequests.value.filter(req => req.status === 'completed').length
  statistics.critical = serviceRequests.value.filter(req => req.priority === 'critical').length
}

// Fetch cars for service request creation
const fetchCars = async () => {
  try {
    const response = await axios.get('/cars')
    if (response.data.success) {
      cars.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching cars:', error)
  }
}

// Fetch technicians for assignment
const fetchTechnicians = async () => {
  try {
    const response = await axios.get('/users', { params: { role: 'technician,mechanic' } })
    if (response.data.success) {
      technicians.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching technicians:', error)
  }
}

// Modal handling
const openCreateModal = async () => {
  isEditing.value = false
  resetForm()
  await fetchCars()
  await fetchTechnicians()
  showModal.value = true
}

const openEditModal = async (request) => {
  isEditing.value = true
  selectedRequest.value = request
  await fetchCars()
  await fetchTechnicians()
  
  form.car_id = request.car_id
  form.issue_type = request.issue_type
  form.description = request.description
  form.priority = request.priority
  form.status = request.status
  form.assignee_id = request.assignee_id || ''
  form.resolution_notes = request.resolution_notes || ''
  form.completed_at = request.completed_at ? request.completed_at.split('T')[0] : ''
  
  showModal.value = true
}

const openViewModal = (request) => {
  selectedRequest.value = request
  showViewModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedRequest.value = null
}

const resetForm = () => {
  form.car_id = ''
  form.issue_type = ''
  form.description = ''
  form.priority = 'medium'
  form.status = 'pending'
  form.assignee_id = ''
  form.resolution_notes = ''
  form.completed_at = ''
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    const data = { ...form }
    
    // Set requester ID
    if (!isEditing.value) {
      data.requester_id = user.value.id
    }

    // Handle completed_at date
    if (data.status === 'completed' && !data.completed_at) {
      data.completed_at = new Date().toISOString().split('T')[0]
    }

    let response
    if (isEditing.value) {
      response = await axios.put(`/service-requests/${selectedRequest.value.id}`, data)
    } else {
      response = await axios.post('/service-requests', data)
    }

    if (response.data.success) {
      closeModal()
      fetchServiceRequests()
      alert(isEditing.value ? 'Service request updated successfully!' : 'Service request submitted successfully!')
    }
  } catch (error) {
    console.error('Error saving service request:', error)
    alert(error.response?.data?.message || 'Failed to save service request')
  } finally {
    submitting.value = false
  }
}

// Delete service request
const canDeleteRequest = (request) => {
  return user.value?.role === 'admin' || 
         (user.value?.id === request.requester_id && request.status === 'pending')
}

const confirmDelete = (request) => {
  if (confirm(`Are you sure you want to delete this service request? This action cannot be undone.`)) {
    deleteRequest(request)
  }
}

const deleteRequest = async (request) => {
  try {
    const response = await axios.delete(`/service-requests/${request.id}`)
    
    if (response.data.success) {
      fetchServiceRequests()
      alert('Service request deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting service request:', error)
    alert(error.response?.data?.message || 'Failed to delete service request')
  }
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const getStatusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'assigned':
      return 'bg-blue-100 text-blue-800'
    case 'in_progress':
      return 'bg-indigo-100 text-indigo-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatStatus = (status) => {
  if (status === 'in_progress') return 'In Progress'
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'low':
      return 'bg-gray-100 text-gray-800'
    case 'medium':
      return 'bg-blue-100 text-blue-800'
    case 'high':
      return 'bg-orange-100 text-orange-800'
    case 'critical':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatPriority = (priority) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1)
}

onMounted(() => {
  fetchServiceRequests()
  fetchCars()
  fetchTechnicians()
})
</script>