<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Rentals Management</h1>
        <p class="text-gray-600 mt-1">Manage car rentals and bookings</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Rental
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
            <option value="confirmed">Confirmed</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <select 
            v-model="filters.branch_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}
            </option>
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
            @click="fetchRentals"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Rentals Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading rentals...</p>
      </div>

      <div v-else-if="rentals.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p>No rentals found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="rental in rentals" :key="rental.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ rental.user?.name }}</div>
                  <div class="text-sm text-gray-500">{{ rental.user?.email }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ rental.car?.make }} {{ rental.car?.model }}</div>
                  <div class="text-sm text-gray-500">{{ rental.car?.year }} | {{ rental.car?.license_plate }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>
                  <div>{{ formatDate(rental.start_date) }}</div>
                  <div>to {{ formatDate(rental.end_date) }}</div>
                  <div class="text-xs text-gray-500">{{ rental.rental_days }} days</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                OMR {{ rental.total_amount.toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(rental.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(rental.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="openViewModal(rental)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </button>
                  <button 
                    @click="openEditModal(rental)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </button>
                  <button 
                    v-if="user?.role === 'admin'"
                    @click="confirmDelete(rental)"
                    class="text-red-600 hover:text-red-900"
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
            {{ isEditing ? 'Edit Rental' : 'Create New Rental' }}
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
                <option v-for="car in availableCars" :key="car.id" :value="car.id">
                  {{ car.make }} {{ car.model }} ({{ car.year }}) - {{ car.branch?.name }}
                </option>
              </select>
            </div>

            <div v-if="user?.role === 'admin'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
              <select 
                v-model="form.user_id"
                :required="user?.role === 'admin'"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Customer</option>
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.name }} ({{ customer.email }})
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
              <input 
                v-model="form.start_date"
                type="date" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
              <input 
                v-model="form.end_date"
                type="date" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Location *</label>
              <input 
                v-model="form.pickup_location"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Return Location *</label>
              <input 
                v-model="form.return_location"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div v-if="isEditing && (user?.role === 'admin' || user?.role === 'branch_manager')">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select 
              v-model="form.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="active">Active</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Special Requests</label>
            <textarea
              v-model="form.special_requests"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Rental' : 'Create Rental') }}
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
            Rental Details
          </h3>
        </div>

        <div v-if="selectedRental" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Customer Information</h4>
              <p class="text-gray-900 font-semibold">{{ selectedRental.user?.name }}</p>
              <p class="text-gray-600">{{ selectedRental.user?.email }}</p>
              <p class="text-gray-600">{{ selectedRental.user?.phone }}</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Car Information</h4>
              <p class="text-gray-900 font-semibold">{{ selectedRental.car?.make }} {{ selectedRental.car?.model }} {{ selectedRental.car?.year }}</p>
              <p class="text-gray-600">License Plate: {{ selectedRental.car?.license_plate }}</p>
              <p class="text-gray-600">Color: {{ selectedRental.car?.color }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Rental Details</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Start Date:</p>
                <p class="text-gray-900">{{ formatDate(selectedRental.start_date) }}</p>
                <p class="text-gray-600">End Date:</p>
                <p class="text-gray-900">{{ formatDate(selectedRental.end_date) }}</p>
                <p class="text-gray-600">Days:</p>
                <p class="text-gray-900">{{ selectedRental.rental_days }}</p>
                <p class="text-gray-600">Total Amount:</p>
                <p class="text-gray-900 font-semibold">OMR {{ selectedRental.total_amount ? Number(selectedRental.total_amount).toFixed(2) : '0.00' }}</p>
              </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Status Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Status:</p>
                <p>
                  <span :class="getStatusClass(selectedRental.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ formatStatus(selectedRental.status) }}
                  </span>
                </p>
                <p class="text-gray-600">Created:</p>
                <p class="text-gray-900">{{ formatDate(selectedRental.created_at) }}</p>
                <p class="text-gray-600">Updated:</p>
                <p class="text-gray-900">{{ formatDate(selectedRental.updated_at) }}</p>
              </div>
            </div>
          </div>

          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Location Information</h4>
            <div class="grid grid-cols-2 gap-2">
              <p class="text-gray-600">Pickup Location:</p>
              <p class="text-gray-900">{{ selectedRental.pickup_location }}</p>
              <p class="text-gray-600">Return Location:</p>
              <p class="text-gray-900">{{ selectedRental.return_location }}</p>
            </div>
          </div>

          <div v-if="selectedRental.special_requests" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Special Requests</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedRental.special_requests }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              @click="closeViewModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Close
            </button>
            <button 
              v-if="canGenerateContract(selectedRental)"
              @click="generateContract(selectedRental)"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors"
            >
              Generate Contract
            </button>
            <button 
              v-if="canGenerateInvoice(selectedRental)"
              @click="generateInvoice(selectedRental)"
              class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition-colors"
            >
              Generate Invoice
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const user = computed(() => useAuthStore().user)

const rentals = ref([])
const branches = ref([])
const availableCars = ref([])
const customers = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedRental = ref(null)

const filters = reactive({
  status: '',
  branch_id: '',
  start_date: '',
  end_date: ''
})

const form = reactive({
  car_id: '',
  user_id: '',
  start_date: '',
  end_date: '',
  pickup_location: '',
  return_location: '',
  special_requests: '',
  status: 'pending'
})

// Fetch rentals from API
const fetchRentals = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.status) params.status = filters.status
    if (filters.branch_id) params.branch_id = filters.branch_id
    if (filters.start_date) params.start_date = filters.start_date
    if (filters.end_date) params.end_date = filters.end_date

    const response = await axios.get('/rentals', { params })
    
    if (response.data.success) {
      rentals.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching rentals:', error)
  } finally {
    loading.value = false
  }
}

// Fetch branches for filtering
const fetchBranches = async () => {
  try {
    const response = await axios.get('/branches')
    if (response.data.success) {
      branches.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching branches:', error)
  }
}

// Fetch available cars
const fetchAvailableCars = async () => {
  try {
    const response = await axios.get('/cars/available')
    if (response.data.success) {
      availableCars.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching cars:', error)
  }
}

// Fetch customers (for admin only)
const fetchCustomers = async () => {
  if (user.value?.role === 'admin') {
    try {
      const response = await axios.get('/users', { params: { role: 'customer' } })
      if (response.data.success) {
        customers.value = response.data.data.data || response.data.data
      }
    } catch (error) {
      console.error('Error fetching customers:', error)
    }
  }
}

// Modal handling
const openCreateModal = async () => {
  isEditing.value = false
  resetForm()
  await fetchAvailableCars()
  await fetchCustomers()
  showModal.value = true
}

const openEditModal = async (rental) => {
  isEditing.value = true
  selectedRental.value = rental
  await fetchAvailableCars()
  await fetchCustomers()
  
  form.car_id = rental.car_id
  form.user_id = rental.user_id
  form.start_date = rental.start_date.split('T')[0]
  form.end_date = rental.end_date.split('T')[0]
  form.pickup_location = rental.pickup_location
  form.return_location = rental.return_location
  form.special_requests = rental.special_requests
  form.status = rental.status
  
  showModal.value = true
}

const openViewModal = (rental) => {
  selectedRental.value = rental
  showViewModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedRental.value = null
}

const resetForm = () => {
  form.car_id = ''
  form.user_id = ''
  form.start_date = ''
  form.end_date = ''
  form.pickup_location = ''
  form.return_location = ''
  form.special_requests = ''
  form.status = 'pending'
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    const data = { ...form }
    
    // Use current user if not admin
    if (user.value?.role !== 'admin') {
      data.user_id = user.value.id
    }

    let response
    if (isEditing.value) {
      response = await axios.put(`/rentals/${selectedRental.value.id}`, data)
    } else {
      response = await axios.post('/rentals', data)
    }

    if (response.data.success) {
      closeModal()
      fetchRentals()
      alert(isEditing.value ? 'Rental updated successfully!' : 'Rental created successfully!')
    }
  } catch (error) {
    console.error('Error saving rental:', error)
    alert(error.response?.data?.message || 'Failed to save rental')
  } finally {
    submitting.value = false
  }
}

// Delete rental
const confirmDelete = (rental) => {
  if (confirm(`Are you sure you want to delete this rental? This action cannot be undone.`)) {
    deleteRental(rental)
  }
}

const deleteRental = async (rental) => {
  try {
    const response = await axios.delete(`/rentals/${rental.id}`)
    
    if (response.data.success) {
      fetchRentals()
      alert('Rental deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting rental:', error)
    alert(error.response?.data?.message || 'Failed to delete rental')
  }
}

// Generate contract and invoice
const canGenerateContract = (rental) => {
  return rental.status === 'confirmed' || rental.status === 'active'
}

const canGenerateInvoice = (rental) => {
  return rental.status === 'completed'
}

const generateContract = async (rental) => {
  try {
    const response = await axios.post(`/contracts`, { 
      rental_id: rental.id,
      car_id: rental.car_id,
      user_id: rental.user_id,
      start_date: rental.start_date,
      end_date: rental.end_date,
      total_amount: rental.total_amount
    }, { responseType: 'blob' })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `contract_${rental.id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    
    alert('Contract generated successfully!')
  } catch (error) {
    console.error('Error generating contract:', error)
    alert('Failed to generate contract')
  }
}

const generateInvoice = async (rental) => {
  try {
    const response = await axios.post(`/invoices`, { 
      rental_id: rental.id,
      car_id: rental.car_id,
      user_id: rental.user_id,
      amount: rental.total_amount,
      date: new Date().toISOString().split('T')[0]
    }, { responseType: 'blob' })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `invoice_${rental.id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    
    alert('Invoice generated successfully!')
  } catch (error) {
    console.error('Error generating invoice:', error)
    alert('Failed to generate invoice')
  }
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const getStatusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'confirmed':
      return 'bg-blue-100 text-blue-800'
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'completed':
      return 'bg-purple-100 text-purple-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

onMounted(() => {
  fetchRentals()
  fetchBranches()
})
</script>
