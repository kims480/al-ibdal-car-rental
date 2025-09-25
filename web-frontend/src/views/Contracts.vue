<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Contracts Management</h1>
        <p class="text-gray-600 mt-1">Manage rental contracts and documentation</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Contract
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6 p-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Search by customer name..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
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
            @click="fetchContracts"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading contracts...</p>
      </div>

      <div v-else-if="contracts.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p>No contracts found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="contract in contracts" :key="contract.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                #{{ contract.contract_number || contract.id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ contract.user?.name }}</div>
                  <div class="text-sm text-gray-500">{{ contract.user?.email }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ contract.car?.make }} {{ contract.car?.model }}</div>
                  <div class="text-sm text-gray-500">{{ contract.car?.license_plate }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div>
                  <div>{{ formatDate(contract.start_date) }}</div>
                  <div>to {{ formatDate(contract.end_date) }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(contract.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(contract.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="viewPdf(contract)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View PDF
                  </button>
                  <button 
                    @click="openEditModal(contract)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </button>
                  <button 
                    @click="emailPdf(contract)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Email
                  </button>
                  <button 
                    v-if="user?.role === 'admin'"
                    @click="confirmDelete(contract)"
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
            {{ isEditing ? 'Edit Contract' : 'Create New Contract' }}
          </h3>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rental *</label>
              <select 
                v-model="form.rental_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Rental</option>
                <option v-for="rental in activeRentals" :key="rental.id" :value="rental.id">
                  {{ rental.user?.name }} - {{ rental.car?.make }} {{ rental.car?.model }}
                </option>
              </select>
            </div>
            
            <div v-if="!form.rental_id">
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
              <select 
                v-model="form.user_id"
                :required="!form.rental_id"
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
            <div v-if="!form.rental_id">
              <label class="block text-sm font-medium text-gray-700 mb-1">Car *</label>
              <select 
                v-model="form.car_id"
                :required="!form.rental_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Car</option>
                <option v-for="car in availableCars" :key="car.id" :value="car.id">
                  {{ car.make }} {{ car.model }} ({{ car.license_plate }})
                </option>
              </select>
            </div>

            <div v-if="isEditing">
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select 
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
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

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount *</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">OMR</span>
              </div>
              <input 
                v-model="form.total_amount"
                type="number"
                step="0.01" 
                required
                class="w-full pl-7 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Terms and Conditions</label>
            <textarea
              v-model="form.terms"
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Contract' : 'Create Contract') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const user = computed(() => useAuthStore().user)

const contracts = ref([])
const activeRentals = ref([])
const availableCars = ref([])
const customers = ref([])
const loading = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)

const filters = reactive({
  search: '',
  status: '',
  start_date: '',
  end_date: ''
})

const form = reactive({
  rental_id: '',
  user_id: '',
  car_id: '',
  start_date: '',
  end_date: '',
  total_amount: '',
  status: 'active',
  terms: ''
})

// Fetch contracts from API
const fetchContracts = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status
    if (filters.start_date) params.start_date = filters.start_date
    if (filters.end_date) params.end_date = filters.end_date

    const response = await axios.get('/contracts', { params })
    
    if (response.data.success) {
      contracts.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching contracts:', error)
  } finally {
    loading.value = false
  }
}

// Fetch active rentals for creating contracts
const fetchActiveRentals = async () => {
  try {
    const response = await axios.get('/rentals', { params: { status: 'confirmed,active' } })
    if (response.data.success) {
      activeRentals.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching active rentals:', error)
  }
}

// Fetch available cars
const fetchAvailableCars = async () => {
  try {
    const response = await axios.get('/cars')
    if (response.data.success) {
      availableCars.value = response.data.data.data || response.data.data
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
  await fetchActiveRentals()
  await fetchAvailableCars()
  await fetchCustomers()
  showModal.value = true
}

const openEditModal = (contract) => {
  isEditing.value = true
  
  form.rental_id = contract.rental_id
  form.user_id = contract.user_id
  form.car_id = contract.car_id
  form.start_date = contract.start_date.split('T')[0]
  form.end_date = contract.end_date.split('T')[0]
  form.total_amount = contract.total_amount
  form.status = contract.status
  form.terms = contract.terms
  
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  form.rental_id = ''
  form.user_id = ''
  form.car_id = ''
  form.start_date = ''
  form.end_date = ''
  form.total_amount = ''
  form.status = 'active'
  form.terms = ''
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    const data = { ...form }
    
    // Use rental data if rental_id is provided
    if (data.rental_id) {
      const rental = activeRentals.value.find(r => r.id === data.rental_id)
      if (rental) {
        data.user_id = rental.user_id
        data.car_id = rental.car_id
        if (!data.start_date) data.start_date = rental.start_date
        if (!data.end_date) data.end_date = rental.end_date
        if (!data.total_amount) data.total_amount = rental.total_amount
      }
    }

    let response
    if (isEditing.value) {
      const contractId = contracts.value.find(c => 
        c.user_id === form.user_id && 
        c.car_id === form.car_id && 
        c.start_date.split('T')[0] === form.start_date
      )?.id
      
      response = await axios.put(`/contracts/${contractId}`, data)
    } else {
      response = await axios.post('/contracts', data)
    }

    if (response.data.success) {
      closeModal()
      fetchContracts()
      alert(isEditing.value ? 'Contract updated successfully!' : 'Contract created successfully!')
    }
  } catch (error) {
    console.error('Error saving contract:', error)
    alert(error.response?.data?.message || 'Failed to save contract')
  } finally {
    submitting.value = false
  }
}

// Delete contract
const confirmDelete = (contract) => {
  if (confirm(`Are you sure you want to delete this contract? This action cannot be undone.`)) {
    deleteContract(contract)
  }
}

const deleteContract = async (contract) => {
  try {
    const response = await axios.delete(`/contracts/${contract.id}`)
    
    if (response.data.success) {
      fetchContracts()
      alert('Contract deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting contract:', error)
    alert(error.response?.data?.message || 'Failed to delete contract')
  }
}

// View and email PDF
const viewPdf = async (contract) => {
  try {
    const response = await axios.get(`/contracts/${contract.id}/pdf`, { responseType: 'blob' })
    
    const url = window.URL.createObjectURL(new Blob([response.data]))
    window.open(url, '_blank')
  } catch (error) {
    console.error('Error downloading contract:', error)
    alert('Failed to view contract PDF')
  }
}

const emailPdf = async (contract) => {
  try {
    const response = await axios.post(`/contracts/${contract.id}/email`)
    
    if (response.data.success) {
      alert('Contract sent via email successfully!')
    }
  } catch (error) {
    console.error('Error emailing contract:', error)
    alert(error.response?.data?.message || 'Failed to email contract')
  }
}

// Watch for rental_id changes to populate form
watch(() => form.rental_id, (newRentalId) => {
  if (newRentalId) {
    const rental = activeRentals.value.find(r => r.id === newRentalId)
    if (rental) {
      form.user_id = rental.user_id
      form.car_id = rental.car_id
      form.start_date = rental.start_date.split('T')[0]
      form.end_date = rental.end_date.split('T')[0]
      form.total_amount = rental.total_amount
    }
  }
})

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

const getStatusClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'completed':
      return 'bg-blue-100 text-blue-800'
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
  fetchContracts()
})
</script>
