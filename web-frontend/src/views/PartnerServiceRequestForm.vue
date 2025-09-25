<template>
  <div>
    <main class="mx-auto max-w-3xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-title">New Rent Request</h1>
          <p class="text-muted">Please fill in the details below</p>
        </div>

        <!-- Rent Request Form -->
        <form @submit.prevent="submitRequest" class="bg-surface shadow border border-gray-200/50 overflow-hidden rounded-lg p-6 space-y-6">
          <!-- Requester Name -->
          <div>
            <label for="customer-name" class="block text-sm font-medium text-title mb-2">Rent Requester Name</label>
            <input
              type="text"
              id="customer-name"
              v-model="requestForm.customer_name"
              class="w-full the-input"
              placeholder="Your full name"
              required
            />
          </div>

          <!-- Phone -->
          <div>
            <label for="customer-phone" class="block text-sm font-medium text-title mb-2">Phone</label>
            <input
              type="tel"
              id="customer-phone"
              v-model="requestForm.customer_phone"
              class="w-full the-input"
              placeholder="e.g., 77307045"
              required
            />
          </div>

          <!-- Region / Location -->
          <div>
            <label for="customer-location" class="block text-sm font-medium text-title mb-2">Region / Location</label>
            <input
              type="text"
              id="customer-location"
              v-model="requestForm.customer_location"
              class="w-full the-input"
              placeholder="City/Region in Oman"
              required
            />
          </div>

          <!-- Rent Period (with time) -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label for="rental-start" class="block text-sm font-medium text-title mb-2">Rent Start</label>
              <input
                type="datetime-local"
                id="rental-start"
                v-model="requestForm.rental_start"
                class="w-full the-input"
                :min="minDateTime"
                required
              />
            </div>
            <div>
              <label for="rental-end" class="block text-sm font-medium text-title mb-2">Rent End</label>
              <input
                type="datetime-local"
                id="rental-end"
                v-model="requestForm.rental_end"
                class="w-full the-input"
                :min="requestForm.rental_start || minDateTime"
                required
              />
            </div>
          </div>

          <!-- Error message -->
          <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
            <p class="text-red-700 text-sm">{{ error }}</p>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-4">
            <router-link
              to="/partner-service-requests"
              class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-title bg-surface hover:bg-gray-50"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="submitting"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300"
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

const router = useRouter()
const authStore = useAuthStore()
const partnerServiceRequestStore = usePartnerServiceRequestStore()

const user = computed(() => authStore.user)
const submitting = ref(false)
const error = ref(null)

// Min datetime for date-time inputs
const minDateTime = computed(() => {
  const now = new Date()
  const tzOffset = now.getTimezoneOffset() * 60000
  const localISO = new Date(now - tzOffset).toISOString().slice(0, 16)
  return localISO
})

// Initialize request form
const requestForm = reactive({
  customer_name: '',
  customer_phone: '',
  customer_location: '',
  rental_start: '',
  rental_end: '',
})

// Submit the service request
const submitRequest = async () => {
  error.value = null
  submitting.value = true
  
  try {
    // Basic validation: end must be after start
    if (requestForm.rental_start && requestForm.rental_end) {
      const start = new Date(requestForm.rental_start)
      const end = new Date(requestForm.rental_end)
      if (end <= start) {
        error.value = 'End time must be after start time.'
        submitting.value = false
        return
      }
    }

    // Normalize datetime-local (YYYY-MM-DDTHH:mm) to space-separated format for backend if needed
    const normalize = (v) => (typeof v === 'string' ? v.replace('T', ' ').replace(/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2})(?!:)/, '$1:00') : v)

    const payload = {
      customer_name: requestForm.customer_name?.trim() || '',
      customer_phone: requestForm.customer_phone?.toString().trim() || '',
      customer_location: requestForm.customer_location?.trim() || '',
      rental_start: normalize(requestForm.rental_start),
      rental_end: normalize(requestForm.rental_end),
    }

    const result = await partnerServiceRequestStore.createServiceRequest(payload)
    
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

onMounted(() => {
  // Prefill name and phone from logged-in user if available
  if (user.value) {
    requestForm.customer_name = user.value.name || ''
    requestForm.customer_phone = user.value.phone || ''
  }
})
</script>