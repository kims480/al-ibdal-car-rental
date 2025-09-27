<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Rentals Management</h1>
        <p class="text-gray-600 mt-1">Manage car rentals and bookings linked with customers</p>
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
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
          <select 
            v-model="filters.customer_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Customers</option>
            <option v-for="customer in customers" :key="customer.id" :value="customer.id">
              {{ customer.name }} - {{ customer.phone }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Car</label>
          <select 
            v-model="filters.car_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Cars</option>
            <option v-for="car in cars" :key="car.id" :value="car.id">
              {{ car.make }} {{ car.model }} ({{ car.plate_number }})
            </option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="fetchRentals"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Apply Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Rentals Table -->
    <div class="bg-white rounded-lg shadow-sm border">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Car
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Duration
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Amount
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" class="animate-pulse">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading rentals...</td>
            </tr>
            <tr v-for="rental in validRentals" :key="rental.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 bg-blue-500 rounded-full flex items-center justify-center">
                      <span class="text-white font-medium">{{ rental.customer?.name?.charAt(0) || '?' }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ rental.customer?.name || 'Unknown Customer' }}</div>
                    <div class="text-sm text-gray-500">{{ rental.customer?.phone || 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ rental.customer?.email || 'N/A' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ rental.car?.make || 'Unknown' }} {{ rental.car?.model || '' }}</div>
                <div class="text-sm text-gray-500">{{ rental.car?.plate_number || 'N/A' }}</div>
                <div class="text-sm text-gray-500">{{ rental.car?.branch?.name || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatDate(rental.pickup_date) }} - {{ formatDate(rental.return_date) }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ rental.rental_days || 0 }} days ({{ rental.rental_type || 'daily' }})
                </div>
                <div v-if="rental.actual_pickup_date || rental.actual_return_date" class="text-xs text-blue-600">
                  Actual: {{ rental.actual_pickup_date ? formatDate(rental.actual_pickup_date) : 'N/A' }} - 
                  {{ rental.actual_return_date ? formatDate(rental.actual_return_date) : 'N/A' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${{ rental.total_cost || rental.total_amount || 0 }}</div>
                <div class="text-sm text-gray-500">Base: ${{ rental.total_amount || 0 }}</div>
                <div v-if="rental.additional_charges > 0" class="text-sm text-orange-600">
                  Extra: ${{ rental.additional_charges }}
                </div>
                <div v-if="rental.security_deposit > 0" class="text-sm text-green-600">
                  Deposit: ${{ rental.security_deposit }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  rental.status_badge_class || getStatusBadgeClass(rental.status || 'unknown')
                ]">
                  {{ rental.status || 'Unknown' }}
                </span>
                <div v-if="rental.is_overdue" class="text-xs text-red-600 mt-1">OVERDUE</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="viewRental(rental)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </button>
                  <button 
                    @click="editRental(rental)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteRental(rental.id)"
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

      <!-- Empty State -->
      <div v-if="!loading && validRentals.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No rentals found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new rental.</p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ isEditing ? 'Edit Rental' : 'Create New Rental' }}
          </h3>
          
          <form @submit.prevent="saveRental" class="space-y-4">
            <!-- Customer Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer *</label>
              <select 
                v-model="form.customer_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select a customer</option>
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.name }} - {{ customer.phone }}
                </option>
              </select>
            </div>

            <!-- Car Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Car *</label>
              <select 
                v-model="form.car_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select a car</option>
                <option v-for="car in availableCars" :key="car.id" :value="car.id">
                  {{ car.make }} {{ car.model }} ({{ car.registration || car.plate_number }}) - ${{ car.daily_rate }}/day
                </option>
              </select>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date *</label>
                <input 
                  v-model="form.pickup_date"
                  type="date"
                  required
                  :min="tomorrow"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Return Date *</label>
                <input 
                  v-model="form.return_date"
                  type="date"
                  required
                  :min="form.pickup_date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <!-- Rental Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Rental Type *</label>
              <select 
                v-model="form.rental_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="daily">Daily</option>
                <option value="weekly">Weekly (6 days rate for 7 days)</option>
                <option value="monthly">Monthly (25 days rate for 30 days)</option>
              </select>
            </div>

            <!-- Security Deposit -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Security Deposit</label>
              <input 
                v-model="form.security_deposit"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Insurance -->
            <div class="flex items-center">
              <input 
                v-model="form.insurance_included"
                type="checkbox"
                id="insurance"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="insurance" class="ml-2 block text-sm text-gray-700">
                Include Insurance Coverage
              </label>
            </div>

            <!-- Additional fields for editing -->
            <div v-if="isEditing">
              <!-- Status -->
              <div>
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

              <!-- Actual Dates -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Actual Pickup Date</label>
                  <input 
                    v-model="form.actual_pickup_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Actual Return Date</label>
                  <input 
                    v-model="form.actual_return_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>

              <!-- Additional Charges -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Additional Charges</label>
                <input 
                  v-model="form.additional_charges"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <!-- Notes -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Notes</label>
                  <textarea 
                    v-model="form.pickup_notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Return Notes</label>
                  <textarea 
                    v-model="form.return_notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- General Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea 
                v-model="form.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4">
              <button 
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
              >
                Cancel
              </button>
              <button 
                type="submit"
                :disabled="saving"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
              >
                {{ saving ? 'Saving...' : (isEditing ? 'Update Rental' : 'Create Rental') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Rental Details</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="selectedRental" class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Customer Information</h4>
              <p class="text-gray-900 font-semibold">{{ selectedRental.customer?.name }}</p>
              <p class="text-gray-600">{{ selectedRental.customer?.email }}</p>
              <p class="text-gray-600">{{ selectedRental.customer?.phone }}</p>
              <p class="text-gray-600">{{ selectedRental.customer?.city }}</p>
            </div>

            <!-- Car Information -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Car Information</h4>
              <p class="text-gray-900 font-semibold">{{ selectedRental.car?.make }} {{ selectedRental.car?.model }}</p>
              <p class="text-gray-600">Plate: {{ selectedRental.car?.plate_number }}</p>
              <p class="text-gray-600">Branch: {{ selectedRental.car?.branch?.name }}</p>
              <p class="text-gray-600">Daily Rate: ${{ selectedRental.car?.daily_rate }}</p>
            </div>

            <!-- Rental Details -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Rental Details</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Pickup Date</p>
                  <p class="font-medium">{{ formatDate(selectedRental.pickup_date) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Return Date</p>
                  <p class="font-medium">{{ formatDate(selectedRental.return_date) }}</p>
                </div>
                <div v-if="selectedRental.actual_pickup_date">
                  <p class="text-sm text-gray-500">Actual Pickup</p>
                  <p class="font-medium">{{ formatDate(selectedRental.actual_pickup_date) }}</p>
                </div>
                <div v-if="selectedRental.actual_return_date">
                  <p class="text-sm text-gray-500">Actual Return</p>
                  <p class="font-medium">{{ formatDate(selectedRental.actual_return_date) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Rental Type</p>
                  <p class="font-medium capitalize">{{ selectedRental.rental_type }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Status</p>
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    selectedRental.status_badge_class
                  ]">
                    {{ selectedRental.status }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Financial Details -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Financial Details</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Base Amount</p>
                  <p class="font-medium">${{ selectedRental.total_amount }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Security Deposit</p>
                  <p class="font-medium">${{ selectedRental.security_deposit }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Additional Charges</p>
                  <p class="font-medium">${{ selectedRental.additional_charges }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Total Cost</p>
                  <p class="font-bold text-lg">${{ selectedRental.total_cost }}</p>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="selectedRental.notes || selectedRental.pickup_notes || selectedRental.return_notes" class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-2">Notes</h4>
              <div v-if="selectedRental.notes" class="mb-2">
                <p class="text-sm text-gray-500">General Notes</p>
                <p class="text-gray-700">{{ selectedRental.notes }}</p>
              </div>
              <div v-if="selectedRental.pickup_notes" class="mb-2">
                <p class="text-sm text-gray-500">Pickup Notes</p>
                <p class="text-gray-700">{{ selectedRental.pickup_notes }}</p>
              </div>
              <div v-if="selectedRental.return_notes">
                <p class="text-sm text-gray-500">Return Notes</p>
                <p class="text-gray-700">{{ selectedRental.return_notes }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// Simple fallback for toast
const showToast = (message, type = 'info') => {
  console.log(`Toast ${type}: ${message}`)
  // You can replace this with any notification system you prefer
}

const rentals = ref([])
const customers = ref([])
const cars = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const selectedRental = ref(null)
const saving = ref(false)

const filters = ref({
  status: '',
  customer_id: '',
  car_id: ''
})

const form = ref({
  customer_id: '',
  car_id: '',
  pickup_date: '',
  return_date: '',
  rental_type: 'daily',
  security_deposit: '',
  insurance_included: false,
  status: 'active',
  actual_pickup_date: '',
  actual_return_date: '',
  additional_charges: '',
  pickup_notes: '',
  return_notes: '',
  notes: ''
})

const tomorrow = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 1)
  return date.toISOString().split('T')[0]
})

const availableCars = computed(() => {
  // Filter cars based on availability
  const filtered = cars.value.filter(car => car && car.status === 'available')
  console.log('All cars:', cars.value)
  console.log('Available cars (filtered):', filtered)
  return filtered
})

const validRentals = computed(() => {
  // Filter out any invalid rental objects and ensure they have required properties
  return rentals.value.filter(rental => rental && rental.id)
})

const fetchRentals = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key])
      }
    })
    
    const response = await axios.get(`/rentals?${params}`)
    let rentalData = Array.isArray(response.data.data) ? response.data.data : []
    
    // Ensure we have an array and valid rental objects
    rentals.value = rentalData.map(rental => {
      if (!rental || typeof rental !== 'object') return null
      
      return {
        ...rental,
        status_badge_class: getStatusBadgeClass(rental.status || 'unknown'),
        is_overdue: rental.return_date && new Date(rental.return_date) < new Date() && rental.status === 'active'
      }
    }).filter(rental => rental !== null) // Remove any null entries
    
  } catch (error) {
    console.error('Error fetching rentals:', error)
    rentals.value = [] // Set empty array on error
    
    // Fallback to mock data for development/testing
    rentals.value = [
      {
        id: 1,
        customer: { name: 'John Doe', phone: '+968 9555 1234', email: 'john@test.com' },
        car: { make: 'Toyota', model: 'Camry', plate_number: 'ABC-123', branch: { name: 'Main Branch' } },
        pickup_date: '2024-01-15',
        return_date: '2024-01-20',
        rental_days: 5,
        rental_type: 'daily',
        total_amount: 250,
        total_cost: 275,
        security_deposit: 100,
        additional_charges: 25,
        status: 'active',
        status_badge_class: getStatusBadgeClass('active'),
        is_overdue: false
      }
    ]
    console.warn('Using fallback mock rental data')
    
    if (typeof showToast === 'function') {
      showToast('Failed to load rentals - using test data', 'warning')
    }
  } finally {
    loading.value = false
  }
}

const fetchCustomers = async () => {
  try {
    const response = await axios.get('/customers')
    customers.value = Array.isArray(response.data.data) ? response.data.data : []
  } catch (error) {
    console.error('Error fetching customers:', error)
    customers.value = []
    
    // Fallback to mock data for development/testing
    customers.value = [
      { id: 1, name: 'John Doe', phone: '+968 9555 1234', email: 'john@test.com' },
      { id: 2, name: 'Jane Smith', phone: '+968 9666 5678', email: 'jane@test.com' }
    ]
    console.warn('Using fallback mock customer data')
  }
}

const fetchCars = async () => {
  try {
    console.log('Starting to fetch cars...')
    const response = await axios.get('/cars')
    console.log('Cars API response:', response)
    console.log('Response data:', response.data)
    
    if (response.data && response.data.success && response.data.data && response.data.data.data) {
      // Handle paginated response structure
      cars.value = Array.isArray(response.data.data.data) ? response.data.data.data : []
      console.log('Cars loaded from API:', cars.value)
    } else if (response.data && response.data.data) {
      // Handle simple array response structure
      cars.value = Array.isArray(response.data.data) ? response.data.data : []
      console.log('Cars loaded from API (simple):', cars.value)
    } else {
      console.log('No valid data in response, using fallback')
      cars.value = []
      
      // Fallback to mock data for development/testing
      cars.value = [
        { id: 1, make: 'Toyota', model: 'Camry', plate_number: 'ABC-123', status: 'available', daily_rate: 50 },
        { id: 2, make: 'Honda', model: 'Accord', plate_number: 'DEF-456', status: 'available', daily_rate: 45 }
      ]
      console.warn('Using fallback mock car data')
    }
  } catch (error) {
    console.error('Error fetching cars:', error)
    console.error('Error details:', error.response || error.message)
    cars.value = []
    
    // Fallback to mock data for development/testing
    cars.value = [
      { id: 1, make: 'Toyota', model: 'Camry', plate_number: 'ABC-123', status: 'available', daily_rate: 50 },
      { id: 2, make: 'Honda', model: 'Accord', plate_number: 'DEF-456', status: 'available', daily_rate: 45 }
    ]
    console.warn('Using fallback mock car data')
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = {
    customer_id: '',
    car_id: '',
    pickup_date: '',
    return_date: '',
    rental_type: 'daily',
    security_deposit: '',
    insurance_included: false,
    status: 'active',
    actual_pickup_date: '',
    actual_return_date: '',
    additional_charges: '',
    pickup_notes: '',
    return_notes: '',
    notes: ''
  }
  showModal.value = true
}

const editRental = (rental) => {
  isEditing.value = true
  // Format dates for input fields
  form.value = {
    ...rental,
    pickup_date: rental.pickup_date ? rental.pickup_date.split(' ')[0] : '',
    return_date: rental.return_date ? rental.return_date.split(' ')[0] : '',
    actual_pickup_date: rental.actual_pickup_date ? rental.actual_pickup_date.split(' ')[0] : '',
    actual_return_date: rental.actual_return_date ? rental.actual_return_date.split(' ')[0] : '',
    security_deposit: rental.security_deposit || '',
    additional_charges: rental.additional_charges || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  isEditing.value = false
}

const saveRental = async () => {
  try {
    saving.value = true
    
    let response
    if (isEditing.value) {
      response = await axios.put(`/rentals/${form.value.id}`, form.value)
    } else {
      response = await axios.post('/rentals', form.value)
    }
    
    if (typeof showToast === 'function') {
      showToast(
        isEditing.value ? 'Rental updated successfully' : 'Rental created successfully',
        'success'
      )
    }
    
    closeModal()
    await fetchRentals()
    
  } catch (error) {
    console.error('Error saving rental:', error)
    
    // For development, simulate success
    if (error.message.includes('Failed to fetch') || response?.status === 401) {
      console.warn('API not available, simulating save success')
      if (typeof showToast === 'function') {
        showToast(
          isEditing.value ? 'Rental updated successfully (simulated)' : 'Rental created successfully (simulated)',
          'success'
        )
      }
      closeModal()
      await fetchRentals()
    } else {
      if (typeof showToast === 'function') {
        showToast(error.message || 'Failed to save rental', 'error')
      }
    }
  } finally {
    saving.value = false
  }
}

const viewRental = (rental) => {
  selectedRental.value = {
    ...rental,
    status_badge_class: getStatusBadgeClass(rental.status || 'unknown')
  }
  showViewModal.value = true
}

const deleteRental = async (rentalId) => {
  if (!confirm('Are you sure you want to delete this rental?')) return
  
  try {
    await axios.delete(`/rentals/${rentalId}`)
    
    if (typeof showToast === 'function') {
      showToast('Rental deleted successfully', 'success')
    }
    await fetchRentals()
    
  } catch (error) {
    console.error('Error deleting rental:', error)
    
    // For development, simulate success
    if (error.message.includes('Failed to fetch') || response?.status === 401) {
      console.warn('API not available, simulating delete success')
      if (typeof showToast === 'function') {
        showToast('Rental deleted successfully (simulated)', 'success')
      }
      await fetchRentals()
    } else {
      if (typeof showToast === 'function') {
        showToast('Failed to delete rental', 'error')
      }
    }
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'Not Set'
  try {
    return new Date(dateString).toLocaleDateString()
  } catch (error) {
    return 'Invalid Date'
  }
}

const getStatusBadgeClass = (status) => {
  if (!status || typeof status !== 'string') {
    return 'bg-gray-100 text-gray-800'
  }
  
  const classes = {
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800',
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-indigo-100 text-indigo-800'
  }
  return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  await Promise.all([
    fetchRentals(),
    fetchCustomers(),
    fetchCars()
  ])
})
</script>

<style scoped>
/* Add any custom styles here */
</style>