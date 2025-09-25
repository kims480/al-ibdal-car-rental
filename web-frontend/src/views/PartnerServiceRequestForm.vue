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
          <h1 class="text-2xl font-bold text-gray-900">New Service Request</h1>
          <p class="text-gray-600">Submit a new service or maintenance request</p>
        </div>

        <!-- Loading indicator -->
        <div v-if="loading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>

        <!-- Service Request Form -->
        <form @submit.prevent="submitRequest" v-if="!loading" class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
          <!-- Service Type -->
          <div class="mb-6">
            <label for="service-type" class="block text-sm font-medium text-gray-700 mb-2">Service Type</label>
            <select
              id="service-type"
              v-model="requestForm.service_type"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              required
            >
              <option value="" disabled>Select a service type</option>
              <option value="maintenance">Maintenance</option>
              <option value="repair">Repair</option>
              <option value="inspection">Inspection</option>
              <option value="other">Other</option>
            </select>
          </div>

          <!-- Car Selection -->
          <div class="mb-6">
            <label for="car-id" class="block text-sm font-medium text-gray-700 mb-2">Car</label>
            <select
              id="car-id"
              v-model="requestForm.car_id"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              required
            >
              <option value="" disabled>Select a car</option>
              <option v-for="car in availableCars" :key="car.id" :value="car.id">
                {{ car.make }} {{ car.model }} ({{ car.license_plate }})
              </option>
            </select>
          </div>

          <!-- Customer Location -->
          <div class="mb-6">
            <label for="customer-location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
            <input
              type="text"
              id="customer-location"
              v-model="requestForm.customer_location"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Enter your current location"
              required
            />
          </div>

          <!-- Service Period -->
          <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label for="rental-start" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
              <input
                type="date"
                id="rental-start"
                v-model="requestForm.rental_start"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :min="minDate"
                required
              />
            </div>
            <div>
              <label for="rental-end" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
              <input
                type="date"
                id="rental-end"
                v-model="requestForm.rental_end"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :min="requestForm.rental_start || minDate"
                required
              />
            </div>
          </div>

          <!-- Priority -->
          <div class="mb-6">
            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
            <select
              id="priority"
              v-model="requestForm.priority"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="low">Low</option>
              <option value="medium" selected>Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea
              id="notes"
              v-model="requestForm.notes"
              rows="4"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Provide details about your service request..."
            ></textarea>
          </div>

          <!-- Error message -->
          <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-red-700 text-sm">{{ error }}</p>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-4">
            <router-link
              to="/partner-service-requests"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="submitting"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:bg-indigo-300"
            >
              <span v-if="submitting">Submitting...</span>
              <span v-else>Submit Request</span>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { usePartnerServiceRequestStore } from '../stores/partnerServiceRequest'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const partnerServiceRequestStore = usePartnerServiceRequestStore()

const user = computed(() => authStore.user)
const loading = ref(true)
const submitting = ref(false)
const error = ref(null)
const availableCars = ref([])
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Get today's date in YYYY-MM-DD format for min date validation
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

// Initialize request form
const requestForm = reactive({
  service_type: '',
  car_id: '',
  customer_name: '',
  customer_phone: '',
  customer_location: '',
  rental_start: '',
  rental_end: '',
  priority: 'medium',
  notes: '',
})

// Fetch available cars for the partner
const fetchAvailableCars = async () => {
  try {
    loading.value = true
    const response = await axios.get(`${API_URL}/cars/available`)
    
    if (response.data.success) {
      availableCars.value = response.data.data
    } else {
      error.value = 'Failed to fetch available cars'
    }
    
  } catch (err) {
    console.error('Error fetching cars:', err)
    error.value = err.response?.data?.message || 'An error occurred while fetching cars'
  } finally {
    loading.value = false
  }
}

// Submit the service request
const submitRequest = async () => {
  error.value = null
  submitting.value = true
  
  try {
    // Set customer info from the logged-in partner
    requestForm.customer_name = user.value.name
    requestForm.customer_phone = user.value.phone || '' // Assuming phone is stored in user object
    
    const result = await partnerServiceRequestStore.createServiceRequest(requestForm)
    
    if (result.success) {
      router.push({
        path: '/partner-service-requests',
        query: { success: 'true', message: 'Service request submitted successfully' }
      })
    } else {
      error.value = result.error
    }
    
  } catch (err) {
    console.error('Error submitting request:', err)
    error.value = err.response?.data?.message || 'An error occurred while submitting your request'
  } finally {
    submitting.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  fetchAvailableCars()
})
</script>