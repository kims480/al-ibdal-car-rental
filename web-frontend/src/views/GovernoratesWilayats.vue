<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Governorates & Wilayats</h1>
        <p class="text-gray-600 mt-1">Manage Oman administrative divisions</p>
      </div>
      <div class="flex gap-3">
        <button 
          @click="openWilayatModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Add Wilayat
        </button>
        <button 
          @click="openGovernorateModal"
          class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Add Governorate
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6 p-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Search governorates and wilayats..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Governorate</label>
          <select 
            v-model="filters.governorate_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Governorates</option>
            <option v-for="gov in governorates" :key="gov.id" :value="gov.id">
              {{ gov.name_en }} - {{ gov.name_ar }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select 
            v-model="filters.active"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All</option>
            <option value="true">Active</option>
            <option value="false">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Governorates List -->
    <div class="space-y-6">
      <div v-for="governorate in filteredGovernorates" :key="governorate.id" class="bg-white rounded-lg shadow-sm border">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ governorate.name_en }}</h3>
              <p class="text-lg text-gray-600 mb-2">{{ governorate.name_ar }}</p>
              <div class="flex items-center gap-4 text-sm text-gray-500">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">{{ governorate.code }}</span>
                <span v-if="governorate.latitude && governorate.longitude">
                  📍 {{ governorate.latitude }}, {{ governorate.longitude }}
                </span>
                <span :class="governorate.is_active ? 'text-green-600' : 'text-red-600'">
                  {{ governorate.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="text-gray-400">{{ governorate.wilayats?.length || 0 }} wilayats</span>
              </div>
            </div>
            <div class="flex gap-2">
              <button 
                @click="editGovernorate(governorate)"
                class="p-2 text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                title="Edit Governorate"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button 
                @click="deleteGovernorate(governorate.id)"
                class="p-2 text-red-600 hover:bg-red-50 rounded-md transition-colors"
                title="Delete Governorate"
                v-if="!governorate.wilayats || governorate.wilayats.length === 0"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Wilayats for this governorate -->
        <div v-if="governorate.wilayats && governorate.wilayats.length > 0" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="wilayat in governorate.wilayats" 
              :key="wilayat.id"
              class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900">{{ wilayat.name_en }}</h4>
                  <p class="text-sm text-gray-600">{{ wilayat.name_ar }}</p>
                  <span class="inline-block mt-1 bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                    {{ wilayat.code }}
                  </span>
                </div>
                <div class="flex gap-1">
                  <button 
                    @click="editWilayat(wilayat)"
                    class="p-1 text-blue-600 hover:bg-blue-50 rounded transition-colors"
                    title="Edit Wilayat"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="deleteWilayat(wilayat.id)"
                    class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
                    title="Delete Wilayat"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                <span :class="wilayat.is_active ? 'text-green-600' : 'text-red-600'">
                  {{ wilayat.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span v-if="wilayat.latitude && wilayat.longitude">
                  📍 {{ wilayat.latitude.toFixed(2) }}, {{ wilayat.longitude.toFixed(2) }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="p-6 text-center text-gray-500">
          <p>No wilayats found for this governorate</p>
        </div>
      </div>
    </div>

    <!-- Governorate Modal -->
    <div v-if="governorateModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ editingGovernorate ? 'Edit Governorate' : 'Add New Governorate' }}
          </h3>
          <button @click="closeGovernorateModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveGovernorate" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">English Name *</label>
            <input 
              v-model="governorateForm.name_en"
              type="text" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arabic Name *</label>
            <input 
              v-model="governorateForm.name_ar"
              type="text" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
            <input 
              v-model="governorateForm.code"
              type="text" 
              required
              maxlength="10"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="governorateForm.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
              <input 
                v-model="governorateForm.latitude"
                type="number"
                step="any"
                min="-90"
                max="90"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
              <input 
                v-model="governorateForm.longitude"
                type="number"
                step="any"
                min="-180"
                max="180"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>
          <div class="flex items-center">
            <input 
              v-model="governorateForm.is_active"
              type="checkbox"
              id="gov_active"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label for="gov_active" class="ml-2 text-sm text-gray-700">Active</label>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button 
              type="button" 
              @click="closeGovernorateModal"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="submittingGovernorate"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              {{ submittingGovernorate ? 'Saving...' : (editingGovernorate ? 'Update Governorate' : 'Add Governorate') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Wilayat Modal -->
    <div v-if="wilayatModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ editingWilayat ? 'Edit Wilayat' : 'Add New Wilayat' }}
          </h3>
          <button @click="closeWilayatModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveWilayat" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Governorate *</label>
            <select 
              v-model="wilayatForm.governorate_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Governorate</option>
              <option v-for="gov in governorates" :key="gov.id" :value="gov.id">
                {{ gov.name_en }} - {{ gov.name_ar }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">English Name *</label>
            <input 
              v-model="wilayatForm.name_en"
              type="text" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Arabic Name *</label>
            <input 
              v-model="wilayatForm.name_ar"
              type="text" 
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
            <input 
              v-model="wilayatForm.code"
              type="text" 
              required
              maxlength="10"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea 
              v-model="wilayatForm.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
              <input 
                v-model="wilayatForm.latitude"
                type="number"
                step="any"
                min="-90"
                max="90"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
              <input 
                v-model="wilayatForm.longitude"
                type="number"
                step="any"
                min="-180"
                max="180"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>
          <div class="flex items-center">
            <input 
              v-model="wilayatForm.is_active"
              type="checkbox"
              id="wilayat_active"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            >
            <label for="wilayat_active" class="ml-2 text-sm text-gray-700">Active</label>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button 
              type="button" 
              @click="closeWilayatModal"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="submittingWilayat"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              {{ submittingWilayat ? 'Saving...' : (editingWilayat ? 'Update Wilayat' : 'Add Wilayat') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

// Data
const governorates = ref([])
const loading = ref(true)

// Filters
const filters = reactive({
  search: '',
  governorate_id: '',
  active: ''
})

// Governorate Modal
const governorateModalOpen = ref(false)
const editingGovernorate = ref(null)
const submittingGovernorate = ref(false)
const governorateForm = reactive({
  name_en: '',
  name_ar: '',
  code: '',
  description: '',
  latitude: null,
  longitude: null,
  is_active: true
})

// Wilayat Modal
const wilayatModalOpen = ref(false)
const editingWilayat = ref(null)
const submittingWilayat = ref(false)
const wilayatForm = reactive({
  governorate_id: '',
  name_en: '',
  name_ar: '',
  code: '',
  description: '',
  latitude: null,
  longitude: null,
  is_active: true
})

// Computed
const filteredGovernorates = computed(() => {
  let result = governorates.value

  if (filters.search) {
    const search = filters.search.toLowerCase()
    result = result.filter(gov => 
      gov.name_en.toLowerCase().includes(search) ||
      gov.name_ar.toLowerCase().includes(search) ||
      gov.code.toLowerCase().includes(search) ||
      (gov.wilayats && gov.wilayats.some(w => 
        w.name_en.toLowerCase().includes(search) ||
        w.name_ar.toLowerCase().includes(search) ||
        w.code.toLowerCase().includes(search)
      ))
    )
  }

  if (filters.governorate_id) {
    result = result.filter(gov => gov.id == filters.governorate_id)
  }

  if (filters.active !== '') {
    const isActive = filters.active === 'true'
    result = result.filter(gov => gov.is_active === isActive)
  }

  return result
})

// Methods
const fetchData = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/governorates', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    governorates.value = response.data.data
  } catch (error) {
    console.error('Error fetching data:', error)
    alert('Failed to fetch governorates and wilayats')
  } finally {
    loading.value = false
  }
}

// Governorate Modal Methods
const openGovernorateModal = () => {
  resetGovernorateForm()
  editingGovernorate.value = null
  governorateModalOpen.value = true
}

const closeGovernorateModal = () => {
  governorateModalOpen.value = false
  editingGovernorate.value = null
  resetGovernorateForm()
}

const resetGovernorateForm = () => {
  governorateForm.name_en = ''
  governorateForm.name_ar = ''
  governorateForm.code = ''
  governorateForm.description = ''
  governorateForm.latitude = null
  governorateForm.longitude = null
  governorateForm.is_active = true
}

const editGovernorate = (governorate) => {
  editingGovernorate.value = governorate
  governorateForm.name_en = governorate.name_en
  governorateForm.name_ar = governorate.name_ar
  governorateForm.code = governorate.code
  governorateForm.description = governorate.description || ''
  governorateForm.latitude = governorate.latitude
  governorateForm.longitude = governorate.longitude
  governorateForm.is_active = governorate.is_active
  governorateModalOpen.value = true
}

const saveGovernorate = async () => {
  try {
    submittingGovernorate.value = true
    
    const url = editingGovernorate.value 
      ? `/api/governorates/${editingGovernorate.value.id}`
      : '/api/governorates'
    const method = editingGovernorate.value ? 'put' : 'post'
    
    const response = await axios[method](url, governorateForm, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })

    await fetchData()
    closeGovernorateModal()
    alert(response.data.message)
  } catch (error) {
    console.error('Error saving governorate:', error)
    alert(error.response?.data?.message || 'Failed to save governorate')
  } finally {
    submittingGovernorate.value = false
  }
}

const deleteGovernorate = async (id) => {
  if (!confirm('Are you sure you want to delete this governorate?')) return

  try {
    const response = await axios.delete(`/api/governorates/${id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    await fetchData()
    alert(response.data.message)
  } catch (error) {
    console.error('Error deleting governorate:', error)
    alert(error.response?.data?.message || 'Failed to delete governorate')
  }
}

// Wilayat Modal Methods
const openWilayatModal = () => {
  resetWilayatForm()
  editingWilayat.value = null
  wilayatModalOpen.value = true
}

const closeWilayatModal = () => {
  wilayatModalOpen.value = false
  editingWilayat.value = null
  resetWilayatForm()
}

const resetWilayatForm = () => {
  wilayatForm.governorate_id = ''
  wilayatForm.name_en = ''
  wilayatForm.name_ar = ''
  wilayatForm.code = ''
  wilayatForm.description = ''
  wilayatForm.latitude = null
  wilayatForm.longitude = null
  wilayatForm.is_active = true
}

const editWilayat = (wilayat) => {
  editingWilayat.value = wilayat
  wilayatForm.governorate_id = wilayat.governorate_id
  wilayatForm.name_en = wilayat.name_en
  wilayatForm.name_ar = wilayat.name_ar
  wilayatForm.code = wilayat.code
  wilayatForm.description = wilayat.description || ''
  wilayatForm.latitude = wilayat.latitude
  wilayatForm.longitude = wilayat.longitude
  wilayatForm.is_active = wilayat.is_active
  wilayatModalOpen.value = true
}

const saveWilayat = async () => {
  try {
    submittingWilayat.value = true
    
    const url = editingWilayat.value 
      ? `/api/wilayats/${editingWilayat.value.id}`
      : '/api/wilayats'
    const method = editingWilayat.value ? 'put' : 'post'
    
    const response = await axios[method](url, wilayatForm, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })

    await fetchData()
    closeWilayatModal()
    alert(response.data.message)
  } catch (error) {
    console.error('Error saving wilayat:', error)
    alert(error.response?.data?.message || 'Failed to save wilayat')
  } finally {
    submittingWilayat.value = false
  }
}

const deleteWilayat = async (id) => {
  if (!confirm('Are you sure you want to delete this wilayat?')) return

  try {
    const response = await axios.delete(`/api/wilayats/${id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    await fetchData()
    alert(response.data.message)
  } catch (error) {
    console.error('Error deleting wilayat:', error)
    alert(error.response?.data?.message || 'Failed to delete wilayat')
  }
}

onMounted(() => {
  fetchData()
})
</script>