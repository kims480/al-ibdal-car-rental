<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Subcontractors</h1>
        <p class="text-gray-600 mt-1">Manage maintenance and service subcontractors</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Subcontractor
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
            placeholder="Name, company, specialty..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
          <select 
            v-model="filters.service_type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="mechanical">Mechanical</option>
            <option value="electrical">Electrical</option>
            <option value="body_work">Body Work</option>
            <option value="tire_service">Tire Service</option>
            <option value="detailing">Detailing</option>
            <option value="towing">Towing</option>
            <option value="glass">Glass Repair</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="blacklisted">Blacklisted</option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="fetchSubcontractors"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Subcontractors Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading subcontractors...</p>
      </div>

      <div v-else-if="subcontractors.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <p>No subcontractors found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Info</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Details</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Services</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sub in subcontractors" :key="sub.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                    <span class="text-lg font-semibold text-gray-600">{{ getInitials(sub.company_name) }}</span>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ sub.company_name }}</div>
                    <div class="text-sm text-gray-500">{{ sub.years_in_business }} {{ sub.years_in_business === 1 ? 'year' : 'years' }} in business</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <div class="text-sm text-gray-900">{{ sub.contact_person }}</div>
                  <div class="text-sm text-gray-500">{{ sub.contact_email }}</div>
                  <div class="text-sm text-gray-500">{{ sub.contact_phone }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-for="(service, index) in getServiceTypes(sub.service_types)"
                    :key="index"
                    class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                  >
                    {{ service }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="flex flex-col">
                  <span>Hourly: OMR {{ sub.hourly_rate ? Number(sub.hourly_rate).toFixed(2) : '0.00' }}</span>
                  <span v-if="sub.flat_rate">Flat: OMR {{ sub.flat_rate ? Number(sub.flat_rate).toFixed(2) : '0.00' }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(sub.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(sub.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="openViewModal(sub)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    View
                  </button>
                  <button 
                    @click="openEditModal(sub)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit Subcontractor"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(sub)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Subcontractor"
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
            {{ isEditing ? 'Edit Subcontractor' : 'Add New Subcontractor' }}
          </h3>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company Name *</label>
              <input 
                v-model="form.company_name"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Years in Business</label>
              <input 
                v-model="form.years_in_business"
                type="number"
                min="0"
                step="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person *</label>
              <input 
                v-model="form.contact_person"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email *</label>
              <input 
                v-model="form.contact_email"
                type="email" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone *</label>
              <input 
                v-model="form.contact_phone"
                type="text" 
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
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="blacklisted">Blacklisted</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Types *</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.mechanical" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Mechanical</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.electrical" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Electrical</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.body_work" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Body Work</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.tire_service" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Tire Service</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.detailing" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Detailing</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.towing" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Towing</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.glass" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Glass Repair</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox" v-model="serviceTypeCheckboxes.other" class="form-checkbox h-4 w-4 text-blue-600">
                <span class="text-sm text-gray-900">Other</span>
              </label>
            </div>
            <div v-if="serviceTypeCheckboxes.other" class="mt-2">
              <input 
                v-model="form.other_service_type"
                type="text" 
                placeholder="Specify other service type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Hourly Rate (OMR)</label>
              <input 
                v-model="form.hourly_rate"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Flat Rate (OMR)</label>
              <input 
                v-model="form.flat_rate"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea
              v-model="form.address"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea
              v-model="form.notes"
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Subcontractor' : 'Add Subcontractor') }}
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
            Subcontractor Details
          </h3>
        </div>

        <div v-if="selectedSubcontractor" class="space-y-4">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-xl font-bold text-gray-900">{{ selectedSubcontractor.company_name }}</p>
              <p class="text-sm text-gray-500">{{ selectedSubcontractor.years_in_business }} {{ selectedSubcontractor.years_in_business === 1 ? 'year' : 'years' }} in business</p>
            </div>
            <div>
              <span :class="getStatusClass(selectedSubcontractor.status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                {{ formatStatus(selectedSubcontractor.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Contact Person:</p>
                <p class="text-gray-900">{{ selectedSubcontractor.contact_person }}</p>
                <p class="text-gray-600">Email:</p>
                <p class="text-gray-900">{{ selectedSubcontractor.contact_email }}</p>
                <p class="text-gray-600">Phone:</p>
                <p class="text-gray-900">{{ selectedSubcontractor.contact_phone }}</p>
              </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Service Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Service Types:</p>
                <div>
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="(service, index) in getServiceTypes(selectedSubcontractor.service_types)"
                      :key="index"
                      class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                    >
                      {{ service }}
                    </span>
                  </div>
                </div>
                <p class="text-gray-600">Hourly Rate:</p>
                <p class="text-gray-900">OMR {{ selectedSubcontractor.hourly_rate ? Number(selectedSubcontractor.hourly_rate).toFixed(2) : '0.00' }}</p>
                <p class="text-gray-600">Flat Rate:</p>
                <p class="text-gray-900">OMR {{ selectedSubcontractor.flat_rate ? Number(selectedSubcontractor.flat_rate).toFixed(2) : 'N/A' }}</p>
              </div>
            </div>
          </div>

          <div v-if="selectedSubcontractor.address" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Address</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedSubcontractor.address }}</p>
          </div>

          <div v-if="selectedSubcontractor.notes" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Notes</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedSubcontractor.notes }}</p>
          </div>

          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Performance Metrics</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
              <div class="bg-white p-3 rounded-lg text-center shadow-sm">
                <div class="text-xl font-semibold text-blue-600">{{ selectedSubcontractor.metrics?.completed_jobs || 0 }}</div>
                <div class="text-xs text-gray-500">Completed Jobs</div>
              </div>
              <div class="bg-white p-3 rounded-lg text-center shadow-sm">
                <div class="text-xl font-semibold text-green-600">{{ selectedSubcontractor.metrics?.on_time_percentage || 0 }}%</div>
                <div class="text-xs text-gray-500">On-Time</div>
              </div>
              <div class="bg-white p-3 rounded-lg text-center shadow-sm">
                <div class="text-xl font-semibold text-yellow-600">{{ selectedSubcontractor.metrics?.avg_response_hours || 0 }} hrs</div>
                <div class="text-xs text-gray-500">Avg Response</div>
              </div>
              <div class="bg-white p-3 rounded-lg text-center shadow-sm">
                <div class="text-xl font-semibold text-purple-600">{{ selectedSubcontractor.metrics?.rating || 0 }}/5</div>
                <div class="text-xs text-gray-500">Rating</div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              @click="closeViewModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Close
            </button>
            <button 
              @click="openEditModal(selectedSubcontractor)"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
            >
              Edit Subcontractor
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

const user = useAuthStore().user

const subcontractors = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedSubcontractor = ref(null)

const filters = reactive({
  search: '',
  service_type: '',
  status: ''
})

const form = reactive({
  company_name: '',
  years_in_business: 0,
  contact_person: '',
  contact_email: '',
  contact_phone: '',
  service_types: [],
  other_service_type: '',
  hourly_rate: '',
  flat_rate: '',
  address: '',
  status: 'active',
  notes: ''
})

const serviceTypeCheckboxes = reactive({
  mechanical: false,
  electrical: false,
  body_work: false,
  tire_service: false,
  detailing: false,
  towing: false,
  glass: false,
  other: false
})

// Watch for checkbox changes and update form.service_types accordingly
watch(serviceTypeCheckboxes, (newValues) => {
  const selectedTypes = []
  for (const [key, value] of Object.entries(newValues)) {
    if (value) {
      if (key === 'other' && form.other_service_type) {
        selectedTypes.push(form.other_service_type)
      } else {
        selectedTypes.push(key)
      }
    }
  }
  form.service_types = selectedTypes
}, { deep: true })

// Watch for changes to other_service_type and update service_types
watch(() => form.other_service_type, (newValue) => {
  if (serviceTypeCheckboxes.other) {
    // Remove old other value and add new one
    const withoutOther = form.service_types.filter(type => 
      !Object.keys(serviceTypeCheckboxes)
        .filter(key => key === 'other')
        .includes(type)
    )
    form.service_types = [...withoutOther, newValue]
  }
})

// Fetch subcontractors from API
const fetchSubcontractors = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.service_type) params.service_type = filters.service_type
    if (filters.status) params.status = filters.status

    const response = await axios.get('/subcontractors', { params })
    
    if (response.data.success) {
      subcontractors.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching subcontractors:', error)
  } finally {
    loading.value = false
  }
}

// Modal handling
const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const openEditModal = (subcontractor) => {
  isEditing.value = true
  selectedSubcontractor.value = subcontractor
  
  form.company_name = subcontractor.company_name
  form.years_in_business = subcontractor.years_in_business || 0
  form.contact_person = subcontractor.contact_person
  form.contact_email = subcontractor.contact_email
  form.contact_phone = subcontractor.contact_phone
  form.service_types = subcontractor.service_types || []
  form.hourly_rate = subcontractor.hourly_rate || ''
  form.flat_rate = subcontractor.flat_rate || ''
  form.address = subcontractor.address || ''
  form.status = subcontractor.status
  form.notes = subcontractor.notes || ''
  
  // Set checkboxes based on service_types
  resetServiceTypeCheckboxes()
  form.service_types.forEach(type => {
    if (Object.keys(serviceTypeCheckboxes).includes(type)) {
      serviceTypeCheckboxes[type] = true
    } else {
      // If it's not a standard type, it's an 'other' type
      serviceTypeCheckboxes.other = true
      form.other_service_type = type
    }
  })
  
  showModal.value = true
}

const openViewModal = (subcontractor) => {
  selectedSubcontractor.value = subcontractor
  showViewModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedSubcontractor.value = null
}

const resetForm = () => {
  form.company_name = ''
  form.years_in_business = 0
  form.contact_person = ''
  form.contact_email = ''
  form.contact_phone = ''
  form.service_types = []
  form.other_service_type = ''
  form.hourly_rate = ''
  form.flat_rate = ''
  form.address = ''
  form.status = 'active'
  form.notes = ''
  
  resetServiceTypeCheckboxes()
}

const resetServiceTypeCheckboxes = () => {
  for (const key in serviceTypeCheckboxes) {
    serviceTypeCheckboxes[key] = false
  }
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    if (serviceTypeCheckboxes.other && form.other_service_type) {
      // Ensure the other service type is in the service_types array
      if (!form.service_types.includes(form.other_service_type)) {
        form.service_types.push(form.other_service_type)
      }
    }
    
    let response
    if (isEditing.value) {
      response = await axios.put(`/subcontractors/${selectedSubcontractor.value.id}`, form)
    } else {
      response = await axios.post('/subcontractors', form)
    }

    if (response.data.success) {
      closeModal()
      fetchSubcontractors()
      alert(isEditing.value ? 'Subcontractor updated successfully!' : 'Subcontractor added successfully!')
    }
  } catch (error) {
    console.error('Error saving subcontractor:', error)
    alert(error.response?.data?.message || 'Failed to save subcontractor')
  } finally {
    submitting.value = false
  }
}

// Delete subcontractor
const confirmDelete = (subcontractor) => {
  if (confirm(`Are you sure you want to delete ${subcontractor.company_name}? This action cannot be undone.`)) {
    deleteSubcontractor(subcontractor)
  }
}

const deleteSubcontractor = async (subcontractor) => {
  try {
    const response = await axios.delete(`/subcontractors/${subcontractor.id}`)
    
    if (response.data.success) {
      fetchSubcontractors()
      alert('Subcontractor deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting subcontractor:', error)
    alert(error.response?.data?.message || 'Failed to delete subcontractor')
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
    case 'active':
      return 'bg-green-100 text-green-800'
    case 'inactive':
      return 'bg-yellow-100 text-yellow-800'
    case 'blacklisted':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getServiceTypes = (types) => {
  if (!types || !Array.isArray(types) || types.length === 0) return ['Not specified']
  
  return types.map(type => {
    const serviceTypeMappings = {
      'mechanical': 'Mechanical',
      'electrical': 'Electrical',
      'body_work': 'Body Work',
      'tire_service': 'Tire Service',
      'detailing': 'Detailing',
      'towing': 'Towing',
      'glass': 'Glass Repair'
    }
    
    return serviceTypeMappings[type] || type.charAt(0).toUpperCase() + type.slice(1)
  })
}

const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

onMounted(() => {
  fetchSubcontractors()
})
</script>