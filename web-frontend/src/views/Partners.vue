<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Partners Management</h1>
        <p class="text-gray-600 mt-1">Manage business partners and collaborations</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        New Partner
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
            placeholder="Partner name, company..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Partnership Type</label>
          <select 
            v-model="filters.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Types</option>
            <option value="supplier">Supplier</option>
            <option value="distributor">Distributor</option>
            <option value="service_provider">Service Provider</option>
            <option value="agency">Agency</option>
            <option value="vendor">Vendor</option>
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
            <option value="pending">Pending</option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="fetchPartners"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Partners Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading partners...</p>
      </div>

      <div v-else-if="partners.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p>No partners found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partner Info</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Details</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partnership Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="partner in partners" :key="partner.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                    <span class="text-lg font-semibold text-gray-600">{{ getInitials(partner.company_name) }}</span>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ partner.company_name }}</div>
                    <div class="text-sm text-gray-500">{{ partner.website || 'No website provided' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div>
                  <div class="text-sm text-gray-900">{{ partner.contact_person }}</div>
                  <div class="text-sm text-gray-500">{{ partner.contact_email }}</div>
                  <div class="text-sm text-gray-500">{{ partner.contact_phone }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatPartnershipType(partner.partnership_type) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(partner.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(partner.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div v-if="partner.contract_start_date && partner.contract_end_date">
                  <div>{{ formatDate(partner.contract_start_date) }}</div>
                  <div>to {{ formatDate(partner.contract_end_date) }}</div>
                  <div v-if="isContractExpiringSoon(partner)" class="mt-1">
                    <span class="text-xs text-orange-600 font-medium bg-orange-100 px-2 py-0.5 rounded">Expires soon</span>
                  </div>
                  <div v-if="isContractExpired(partner)" class="mt-1">
                    <span class="text-xs text-red-600 font-medium bg-red-100 px-2 py-0.5 rounded">Expired</span>
                  </div>
                </div>
                <div v-else>
                  No contract dates
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="openViewModal(partner)"
                    class="text-blue-600 hover:text-blue-900"
                    title="View Details"
                  >
                    View
                  </button>
                  <button 
                    @click="openEditModal(partner)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Edit Partner"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(partner)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Partner"
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
            {{ isEditing ? 'Edit Partner' : 'Add New Partner' }}
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Partnership Type *</label>
              <select 
                v-model="form.partnership_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Type</option>
                <option value="supplier">Supplier</option>
                <option value="distributor">Distributor</option>
                <option value="service_provider">Service Provider</option>
                <option value="agency">Agency</option>
                <option value="vendor">Vendor</option>
              </select>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
              <input 
                v-model="form.website"
                type="url"
                placeholder="https://..."
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

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
              <select 
                v-model="form.status"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending">Pending</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contract Start Date</label>
              <input 
                v-model="form.contract_start_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contract End Date</label>
              <input 
                v-model="form.contract_end_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Partner' : 'Add Partner') }}
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
            Partner Details
          </h3>
        </div>

        <div v-if="selectedPartner" class="space-y-4">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-xl font-bold text-gray-900">{{ selectedPartner.company_name }}</p>
              <p class="text-sm text-gray-500">{{ selectedPartner.website || 'No website provided' }}</p>
            </div>
            <div>
              <span :class="getStatusClass(selectedPartner.status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                {{ formatStatus(selectedPartner.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Contact Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Contact Person:</p>
                <p class="text-gray-900 font-semibold">{{ selectedPartner.contact_person }}</p>
                <p class="text-gray-600">Email:</p>
                <p class="text-gray-900">{{ selectedPartner.contact_email }}</p>
                <p class="text-gray-600">Phone:</p>
                <p class="text-gray-900">{{ selectedPartner.contact_phone }}</p>
              </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Partnership Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Type:</p>
                <p class="text-gray-900">{{ formatPartnershipType(selectedPartner.partnership_type) }}</p>
                <p class="text-gray-600">Created On:</p>
                <p class="text-gray-900">{{ formatDate(selectedPartner.created_at) }}</p>
                <p class="text-gray-600">Last Updated:</p>
                <p class="text-gray-900">{{ formatDate(selectedPartner.updated_at) }}</p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Contract Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Start Date:</p>
                <p class="text-gray-900">{{ selectedPartner.contract_start_date ? formatDate(selectedPartner.contract_start_date) : 'N/A' }}</p>
                <p class="text-gray-600">End Date:</p>
                <p class="text-gray-900">{{ selectedPartner.contract_end_date ? formatDate(selectedPartner.contract_end_date) : 'N/A' }}</p>
                <p class="text-gray-600">Status:</p>
                <p class="text-gray-900">
                  <span v-if="isContractExpired(selectedPartner)" class="text-xs text-red-600 font-medium bg-red-100 px-2 py-0.5 rounded">Expired</span>
                  <span v-else-if="isContractExpiringSoon(selectedPartner)" class="text-xs text-orange-600 font-medium bg-orange-100 px-2 py-0.5 rounded">Expires soon</span>
                  <span v-else-if="selectedPartner.contract_end_date" class="text-xs text-green-600 font-medium bg-green-100 px-2 py-0.5 rounded">Active</span>
                  <span v-else class="text-xs text-gray-600 font-medium bg-gray-100 px-2 py-0.5 rounded">No contract</span>
                </p>
              </div>
            </div>
            
            <div v-if="selectedPartner.address" class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Address</h4>
              <p class="text-gray-900 whitespace-pre-line">{{ selectedPartner.address }}</p>
            </div>
          </div>

          <div v-if="selectedPartner.notes" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Notes</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedPartner.notes }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              @click="closeViewModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Close
            </button>
            <button 
              @click="openEditModal(selectedPartner)"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
            >
              Edit Partner
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const user = useAuthStore().user

const partners = ref([])
const loading = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedPartner = ref(null)

const filters = reactive({
  search: '',
  type: '',
  status: ''
})

const form = reactive({
  company_name: '',
  partnership_type: '',
  contact_person: '',
  contact_email: '',
  contact_phone: '',
  website: '',
  address: '',
  status: 'active',
  contract_start_date: '',
  contract_end_date: '',
  notes: ''
})

// Fetch partners from API
const fetchPartners = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.type) params.type = filters.type
    if (filters.status) params.status = filters.status

    const response = await axios.get('/partners', { params })
    
    if (response.data.success) {
      partners.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching partners:', error)
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

const openEditModal = (partner) => {
  isEditing.value = true
  selectedPartner.value = partner
  
  form.company_name = partner.company_name
  form.partnership_type = partner.partnership_type
  form.contact_person = partner.contact_person
  form.contact_email = partner.contact_email
  form.contact_phone = partner.contact_phone
  form.website = partner.website || ''
  form.address = partner.address || ''
  form.status = partner.status
  form.contract_start_date = partner.contract_start_date ? partner.contract_start_date.split('T')[0] : ''
  form.contract_end_date = partner.contract_end_date ? partner.contract_end_date.split('T')[0] : ''
  form.notes = partner.notes || ''
  
  showModal.value = true
}

const openViewModal = (partner) => {
  selectedPartner.value = partner
  showViewModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedPartner.value = null
}

const resetForm = () => {
  form.company_name = ''
  form.partnership_type = ''
  form.contact_person = ''
  form.contact_email = ''
  form.contact_phone = ''
  form.website = ''
  form.address = ''
  form.status = 'active'
  form.contract_start_date = ''
  form.contract_end_date = ''
  form.notes = ''
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    let response
    if (isEditing.value) {
      response = await axios.put(`/partners/${selectedPartner.value.id}`, form)
    } else {
      response = await axios.post('/partners', form)
    }

    if (response.data.success) {
      closeModal()
      fetchPartners()
      alert(isEditing.value ? 'Partner updated successfully!' : 'Partner added successfully!')
    }
  } catch (error) {
    console.error('Error saving partner:', error)
    alert(error.response?.data?.message || 'Failed to save partner')
  } finally {
    submitting.value = false
  }
}

// Delete partner
const confirmDelete = (partner) => {
  if (confirm(`Are you sure you want to delete ${partner.company_name}? This action cannot be undone.`)) {
    deletePartner(partner)
  }
}

const deletePartner = async (partner) => {
  try {
    const response = await axios.delete(`/partners/${partner.id}`)
    
    if (response.data.success) {
      fetchPartners()
      alert('Partner deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting partner:', error)
    alert(error.response?.data?.message || 'Failed to delete partner')
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
      return 'bg-red-100 text-red-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const formatPartnershipType = (type) => {
  if (!type) return 'N/A'
  
  const types = {
    'supplier': 'Supplier',
    'distributor': 'Distributor',
    'service_provider': 'Service Provider',
    'agency': 'Agency',
    'vendor': 'Vendor'
  }
  
  return types[type] || type.charAt(0).toUpperCase() + type.slice(1)
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

const isContractExpired = (partner) => {
  if (!partner.contract_end_date) return false
  
  const endDate = new Date(partner.contract_end_date)
  const today = new Date()
  
  return endDate < today
}

const isContractExpiringSoon = (partner) => {
  if (!partner.contract_end_date) return false
  if (isContractExpired(partner)) return false
  
  const endDate = new Date(partner.contract_end_date)
  const today = new Date()
  
  // Check if contract expires within 30 days
  const thirtyDaysFromNow = new Date()
  thirtyDaysFromNow.setDate(today.getDate() + 30)
  
  return endDate <= thirtyDaysFromNow && endDate > today
}

onMounted(() => {
  fetchPartners()
})
</script>