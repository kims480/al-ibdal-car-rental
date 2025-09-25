<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Car Inventory</h1>
        <p class="text-gray-600 mt-1">Manage car fleet and availability</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Car
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
            placeholder="Make, model, plate..."
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
            <option value="available">Available</option>
            <option value="rented">Rented</option>
            <option value="maintenance">Maintenance</option>
            <option value="reserved">Reserved</option>
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
        <div class="flex items-end">
          <button 
            @click="fetchCars"
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
          <p class="text-sm text-gray-600">Total Cars</p>
          <p class="text-xl font-bold">{{ statistics.total }}</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-green-100 p-3 mr-4">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Available</p>
          <p class="text-xl font-bold">{{ statistics.available }}</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-yellow-100 p-3 mr-4">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Rented</p>
          <p class="text-xl font-bold">{{ statistics.rented }}</p>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border p-4 flex items-center">
        <div class="rounded-full bg-red-100 p-3 mr-4">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm text-gray-600">Maintenance</p>
          <p class="text-xl font-bold">{{ statistics.maintenance }}</p>
        </div>
      </div>
    </div>

    <!-- Cars Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading cars...</p>
      </div>

      <div v-else-if="cars.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <p>No cars found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daily Rate</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="car in cars" :key="car.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ car.make }} {{ car.model }}</div>
                    <div class="text-sm text-gray-500">{{ car.year }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                    <div class="text-sm text-gray-900">{{ car.registration }}</div>
                  <div class="text-sm text-gray-500">{{ car.color }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(car.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(car.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                OMR {{ car.daily_rate ? Number(car.daily_rate).toFixed(2) : '0.00' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ car.branch?.name || 'Unassigned' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="openViewModal(car)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    View
                  </button>
                  <button 
                    @click="openEditModal(car)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit Car"
                  >
                    Edit
                  </button>
                  <button
                    v-if="car.status !== 'rented'"
                    @click="confirmDelete(car)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Car"
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
            {{ isEditing ? 'Edit Car' : 'Add New Car' }}
          </h3>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Make *</label>
              <input 
                v-model="form.make"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Model *</label>
              <input 
                v-model="form.model"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
              <input 
                v-model="form.year"
                type="number"
                min="1950"
                :max="new Date().getFullYear() + 1" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">License Plate *</label>
              <input 
                  v-model="form.registration"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
              <input 
                v-model="form.color"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">VIN #</label>
              <input 
                v-model="form.vin"
                type="text" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Daily Rate (OMR) *</label>
              <input 
                v-model="form.daily_rate"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
              <select 
                v-model="form.status"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="available">Available</option>
                <option value="maintenance">Maintenance</option>
                <option value="reserved">Reserved</option>
                <option value="rented">Rented</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
              <select 
                v-model="form.branch_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Branch</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ branch.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Features</label>
            <textarea
              v-model="form.features"
              rows="3"
              placeholder="AC, Navigation, Bluetooth, Leather seats, etc."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="2"
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Car' : 'Add Car') }}
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
            Car Details
          </h3>
        </div>

        <div v-if="selectedCar" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Basic Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Make:</p>
                <p class="text-gray-900 font-semibold">{{ selectedCar.make }}</p>
                <p class="text-gray-600">Model:</p>
                <p class="text-gray-900">{{ selectedCar.model }}</p>
                <p class="text-gray-600">Year:</p>
                <p class="text-gray-900">{{ selectedCar.year }}</p>
                <p class="text-gray-600">Color:</p>
                <p class="text-gray-900">{{ selectedCar.color }}</p>
              </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Registration Details</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">License Plate:</p>
                  <p class="text-gray-900 font-semibold">{{ selectedCar.registration }}</p>
                <p class="text-gray-600">VIN:</p>
                <p class="text-gray-900">{{ selectedCar.vin || 'N/A' }}</p>
                <p class="text-gray-600">Status:</p>
                <p>
                  <span :class="getStatusClass(selectedCar.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ formatStatus(selectedCar.status) }}
                  </span>
                </p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Business Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Daily Rate:</p>
                <p class="text-gray-900 font-semibold">OMR {{ selectedCar.daily_rate ? Number(selectedCar.daily_rate).toFixed(2) : '0.00' }}</p>
                <p class="text-gray-600">Branch:</p>
                <p class="text-gray-900">{{ selectedCar.branch?.name || 'Unassigned' }}</p>
                <p class="text-gray-600">Added:</p>
                <p class="text-gray-900">{{ formatDate(selectedCar.created_at) }}</p>
              </div>
            </div>
            
            <div v-if="rentalHistory.length > 0" class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Rental History</h4>
              <div class="text-sm">
                <div v-for="(rental, index) in rentalHistory" :key="index" class="mb-1 pb-1" :class="{'border-b border-gray-200': index < rentalHistory.length - 1}">
                  <div class="flex justify-between">
                    <span class="text-gray-900">{{ rental.customer }}</span>
                    <span class="text-gray-500">{{ rental.date }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="selectedCar.features" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Features</h4>
            <p class="text-gray-900">{{ selectedCar.features }}</p>
          </div>

          <div v-if="selectedCar.notes" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Notes</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedCar.notes }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              @click="closeViewModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Close
            </button>
            <button 
              @click="openEditModal(selectedCar)"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
            >
              Edit Car
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

const cars = ref([])
const branches = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedCar = ref(null)
const rentalHistory = ref([])

const statistics = reactive({
  total: 0,
  available: 0,
  rented: 0,
  maintenance: 0
})

const filters = reactive({
  search: '',
  status: '',
  branch_id: ''
})

const form = reactive({
  make: '',
  model: '',
  year: new Date().getFullYear(),
  license_plate: '',
  vin: '',
  color: '',
  daily_rate: '',
  status: 'available',
  branch_id: '',
  features: '',
  notes: ''
})

// Fetch cars from API
const fetchCars = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status
    if (filters.branch_id) params.branch_id = filters.branch_id

    const response = await axios.get('/cars', { params })
    
    if (response.data.success) {
      cars.value = response.data.data.data || response.data.data
      updateStatistics()
    }
  } catch (error) {
    console.error('Error fetching cars:', error)
  } finally {
    loading.value = false
  }
}

// Update statistics based on cars data
const updateStatistics = () => {
  statistics.total = cars.value.length
  statistics.available = cars.value.filter(car => car.status === 'available').length
  statistics.rented = cars.value.filter(car => car.status === 'rented').length
  statistics.maintenance = cars.value.filter(car => car.status === 'maintenance').length
}

// Fetch branches for filtering and assignment
const fetchBranches = async () => {
  try {
    const response = await axios.get('/branches')
    if (response.data.success) {
      branches.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching branches:', error)
  }
}

// Fetch rental history for a specific car
const fetchRentalHistory = async (carId) => {
  try {
    const response = await axios.get(`/cars/${carId}/rentals`)
    if (response.data.success) {
      const rentals = response.data.data.data || response.data.data
      rentalHistory.value = rentals.map(rental => ({
        customer: rental.user?.name || 'Unknown',
        date: `${formatDate(rental.start_date)} - ${formatDate(rental.end_date)}`,
        status: rental.status
      }))
    }
  } catch (error) {
    console.error('Error fetching rental history:', error)
    rentalHistory.value = []
  }
}

// Modal handling
const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const openEditModal = (car) => {
  isEditing.value = true
  selectedCar.value = car
  
  form.make = car.make
  form.model = car.model
  form.year = car.year
  form.registration = car.registration
  form.vin = car.vin || ''
  form.color = car.color
  form.daily_rate = car.daily_rate
  form.status = car.status
  form.branch_id = car.branch_id
  form.features = car.features || ''
  form.notes = car.notes || ''
  
  showModal.value = true
}

const openViewModal = async (car) => {
  selectedCar.value = car
  showViewModal.value = true
  await fetchRentalHistory(car.id)
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedCar.value = null
  rentalHistory.value = []
}

const resetForm = () => {
  form.make = ''
  form.model = ''
  form.year = new Date().getFullYear()
  form.registration = ''
  form.vin = ''
  form.color = ''
  form.daily_rate = ''
  form.status = 'available'
  form.branch_id = ''
  form.features = ''
  form.notes = ''
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    let response
    // Create a copy of the form data with correct field mappings
    const formData = {
      make: form.make,
      model: form.model,
      year: form.year,
      registration: form.registration,
      color: form.color,
      daily_rate: form.daily_rate,
      status: form.status,
      branch_id: form.branch_id,
        description: form.notes,
        vin: form.vin,
        features: form.features,
        notes: form.notes
    }
    
    if (isEditing.value) {
      response = await axios.put(`/cars/${selectedCar.value.id}`, formData)
    } else {
      response = await axios.post('/cars', formData)
    }

    if (response.data.success) {
      closeModal()
      fetchCars()
      alert(isEditing.value ? 'Car updated successfully!' : 'Car added successfully!')
    }
  } catch (error) {
    console.error('Error saving car:', error)
    alert(error.response?.data?.message || 'Failed to save car')
  } finally {
    submitting.value = false
  }
}

// Delete car
const confirmDelete = (car) => {
  if (confirm(`Are you sure you want to delete ${car.make} ${car.model} (${car.registration})? This action cannot be undone.`)) {
    deleteCar(car)
  }
}

const deleteCar = async (car) => {
  try {
    const response = await axios.delete(`/cars/${car.id}`)
    
    if (response.data.success) {
      fetchCars()
      alert('Car deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting car:', error)
    alert(error.response?.data?.message || 'Failed to delete car. The car might be in use.')
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
    case 'available':
      return 'bg-green-100 text-green-800'
    case 'rented':
      return 'bg-blue-100 text-blue-800'
    case 'maintenance':
      return 'bg-red-100 text-red-800'
    case 'reserved':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

onMounted(() => {
  fetchCars()
  fetchBranches()
})
</script>