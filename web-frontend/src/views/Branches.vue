<template>
  <div class="branches-container">
    <div class="branches-content">
      <!-- Header Section -->
      <div class="page-header">
        <div class="header-content">
          <h1 class="page-title">Branch Management</h1>
          <p class="page-subtitle">Manage your branch network and monitor performance</p>
          
          <button
            v-if="authStore.user?.role === 'admin'"
            @click="showCreateModal = true"
            class="add-branch-btn"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Branch
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="loading-section">
        <div class="loading-spinner">
          <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="loading-text">Loading branches...</p>
        </div>
      </div>

      <!-- Alert Messages -->
      <div v-if="error" class="alert alert-error">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ error }}</span>
      </div>

      <div v-if="successMessage" class="alert alert-success">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ successMessage }}</span>
      </div>

      <!-- Branches Grid -->
      <div v-if="!loading" class="branches-grid">
        <div v-for="branch in branches" :key="branch.id" class="branch-card">
          <div class="card-header">
            <div class="branch-info">
              <h3 class="branch-name">{{ branch.name }}</h3>
              <span 
                :class="[
                  'status-badge',
                  branch.active ? 'status-active' : 'status-inactive'
                ]"
              >
                {{ branch.active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          
          <div class="card-body">
            <div class="contact-info">
              <div class="info-item">
                <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <div>
                  <span class="info-label">City:</span>
                  <span class="info-value">{{ branch.city }}</span>
                </div>
              </div>
              
              <div class="info-item">
                <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <div>
                  <span class="info-label">Address:</span>
                  <span class="info-value">{{ branch.address }}</span>
                </div>
              </div>
              
              <div class="info-item">
                <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <div>
                  <span class="info-label">Phone:</span>
                  <span class="info-value">{{ branch.contact_phone }}</span>
                </div>
              </div>
              
              <div class="info-item">
                <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                </svg>
                <div>
                  <span class="info-label">Email:</span>
                  <span class="info-value">{{ branch.contact_email }}</span>
                </div>
              </div>
            </div>

            <!-- Statistics -->
            <div v-if="branch.statistics" class="statistics-section">
              <h4 class="stats-title">Branch Statistics</h4>
              <div class="stats-grid">
                <div class="stat-item">
                  <span class="stat-value">{{ branch.statistics.service_requests_count || 0 }}</span>
                  <span class="stat-label">Service Requests</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ branch.statistics.cars_count || 0 }}</span>
                  <span class="stat-label">Cars</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ branch.statistics.active_rentals_count || 0 }}</span>
                  <span class="stat-label">Active Rentals</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ branch.statistics.staff_count || 0 }}</span>
                  <span class="stat-label">Staff</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div v-if="authStore.user?.role === 'admin'" class="card-actions">
            <button @click="editBranch(branch)" class="action-btn action-edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Edit
            </button>
            <button @click="viewStatistics(branch)" class="action-btn action-view">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              Statistics
            </button>
            <button @click="confirmDeleteBranch(branch)" class="action-btn action-delete">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              Delete
            </button>
            <button
              @click="toggleBranchStatus(branch)"
              :class="[
                'action-btn',
                branch.active ? 'action-deactivate' : 'action-activate'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="branch.active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              {{ branch.active ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Create/Edit Branch Modal -->
      <div v-if="showCreateModal || showEditModal" class="modal-overlay">
        <div class="modal-container">
          <div class="modal-header">
            <h3 class="modal-title">
              {{ showCreateModal ? 'Add New Branch' : 'Edit Branch' }}
            </h3>
          </div>
          
          <form @submit.prevent="showCreateModal ? createBranch() : updateBranch()" class="modal-form">
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Name</label>
                <input
                  v-model="branchForm.name"
                  type="text"
                  required
                  class="form-input"
                  placeholder="Enter branch name"
                />
              </div>
              
              <div class="form-group">
                <label class="form-label">City</label>
                <input
                  v-model="branchForm.city"
                  type="text"
                  required
                  class="form-input"
                  placeholder="Enter city"
                />
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Address</label>
              <textarea
                v-model="branchForm.address"
                required
                rows="3"
                class="form-textarea"
                placeholder="Enter full address"
              ></textarea>
            </div>
            
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Phone</label>
                <input
                  v-model="branchForm.contact_phone"
                  type="tel"
                  required
                  class="form-input"
                  placeholder="Enter phone number"
                />
              </div>
              
              <div class="form-group">
                <label class="form-label">Email</label>
                <input
                  v-model="branchForm.contact_email"
                  type="email"
                  required
                  class="form-input"
                  placeholder="Enter email address"
                />
              </div>
            </div>
            
            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="branchForm.active"
                  type="checkbox"
                  class="form-checkbox"
                />
                <span class="checkbox-text">Active Branch</span>
              </label>
            </div>
            
            <div class="modal-actions">
              <button type="button" @click="cancelForm" class="btn-cancel">
                Cancel
              </button>
              <button type="submit" :disabled="submitting" class="btn-primary">
                <svg v-if="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Saving...' : (showCreateModal ? 'Create Branch' : 'Update Branch') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Statistics Modal -->
      <div v-if="showStatsModal && selectedBranch" class="modal-overlay">
        <div class="modal-container modal-wide">
          <div class="modal-header">
            <h3 class="modal-title">
              {{ selectedBranch.name }} - Detailed Statistics
            </h3>
          </div>
          
          <div v-if="selectedBranch.detailedStats" class="stats-modal-content">
            <!-- Summary Cards -->
            <div class="stats-summary-grid">
              <div class="summary-card summary-blue">
                <div class="summary-value">{{ selectedBranch.detailedStats.service_requests_count || 0 }}</div>
                <div class="summary-label">Service Requests</div>
              </div>
              <div class="summary-card summary-green">
                <div class="summary-value">{{ selectedBranch.detailedStats.cars_count || 0 }}</div>
                <div class="summary-label">Total Cars</div>
              </div>
              <div class="summary-card summary-yellow">
                <div class="summary-value">{{ selectedBranch.detailedStats.active_rentals_count || 0 }}</div>
                <div class="summary-label">Active Rentals</div>
              </div>
              <div class="summary-card summary-purple">
                <div class="summary-value">{{ selectedBranch.detailedStats.staff_count || 0 }}</div>
                <div class="summary-label">Staff Members</div>
              </div>
            </div>

            <!-- Revenue Information -->
            <div class="revenue-section">
              <h4 class="revenue-title">Revenue Overview</h4>
              <div class="revenue-grid">
                <div class="revenue-item">
                  <div class="revenue-amount">OMR {{ selectedBranch.detailedStats.monthly_revenue || '0.00' }}</div>
                  <div class="revenue-period">This Month</div>
                </div>
                <div class="revenue-item">
                  <div class="revenue-amount">OMR {{ selectedBranch.detailedStats.yearly_revenue || '0.00' }}</div>
                  <div class="revenue-period">This Year</div>
                </div>
                <div class="revenue-item">
                  <div class="revenue-amount">OMR {{ selectedBranch.detailedStats.total_revenue || '0.00' }}</div>
                  <div class="revenue-period">Total Revenue</div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-actions">
            <button @click="showStatsModal = false" class="btn-cancel">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const branches = ref([])
const loading = ref(true)
const error = ref('')
const successMessage = ref('')

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showStatsModal = ref(false)
const selectedBranch = ref(null)
const submitting = ref(false)

// Form data
const branchForm = ref({
  name: '',
  city: '',
  address: '',
  contact_phone: '',
  contact_email: '',
  active: true
})

// Methods
const fetchBranches = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const response = await axios.get('/branches', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    branches.value = response.data.data || response.data
    
    // Load basic statistics in background (non-blocking)
    loadBranchStatistics()
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch branches'
  } finally {
    loading.value = false
  }
}

const loadBranchStatistics = async () => {
  // Load statistics in parallel for better performance
  const statsPromises = branches.value.map(async (branch) => {
    try {
      const statsResponse = await axios.get(`/branches/${branch.id}/statistics`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      })
      branch.statistics = statsResponse.data.data || statsResponse.data
    } catch (err) {
      branch.statistics = {
        service_requests_count: 0,
        cars_count: 0,
        active_rentals_count: 0,
        staff_count: 0
      }
    }
  })
  
  // Wait for all statistics to load
  await Promise.allSettled(statsPromises)
}

const createBranch = async () => {
  try {
    submitting.value = true
    error.value = ''
    
    const response = await axios.post('/branches', branchForm.value, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    successMessage.value = 'Branch created successfully'
    showCreateModal.value = false
    resetForm()
    await fetchBranches()
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create branch'
  } finally {
    submitting.value = false
  }
}

const editBranch = (branch) => {
  selectedBranch.value = branch
  branchForm.value = { ...branch }
  showEditModal.value = true
}

const updateBranch = async () => {
  try {
    submitting.value = true
    error.value = ''
    
    const response = await axios.put(`/branches/${selectedBranch.value.id}`, branchForm.value, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    successMessage.value = 'Branch updated successfully'
    showEditModal.value = false
    resetForm()
    await fetchBranches()
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update branch'
  } finally {
    submitting.value = false
  }
}

const toggleBranchStatus = async (branch) => {
  try {
    const response = await axios.put(`/branches/${branch.id}`, 
      { active: !branch.active }, 
      {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      }
    )
    
    branch.active = !branch.active
    successMessage.value = `Branch ${branch.active ? 'activated' : 'deactivated'} successfully`
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to update branch status'
  }
}

const viewStatistics = async (branch) => {
  try {
    const response = await axios.get(`/branches/${branch.id}/statistics`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    selectedBranch.value = {
      ...branch,
      detailedStats: response.data.data || response.data
    }
    showStatsModal.value = true
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch branch statistics'
  }
}

const confirmDeleteBranch = (branch) => {
  if (confirm(`Are you sure you want to delete "${branch.name}" branch? This action cannot be undone and will affect all related data.`)) {
    deleteBranch(branch)
  }
}

const deleteBranch = async (branch) => {
  try {
    const response = await axios.delete(`/branches/${branch.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      }
    })
    
    successMessage.value = 'Branch deleted successfully'
    await fetchBranches() // Refresh the list
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete branch. It may have related data.'
  }
}

const resetForm = () => {
  branchForm.value = {
    name: '',
    city: '',
    address: '',
    contact_phone: '',
    contact_email: '',
    active: true
  }
  selectedBranch.value = null
}

const cancelForm = () => {
  showCreateModal.value = false
  showEditModal.value = false
  resetForm()
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

// Lifecycle
onMounted(() => {
  fetchBranches()
})
</script>

<style scoped>
/* Branches Container - Material Design */
.branches-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 1rem;
  position: relative;
}

.branches-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%" r="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.1)"/><stop offset="100%" style="stop-color:rgba(255,255,255,0.05)"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="300" cy="800" r="120" fill="url(%23a)"/></svg>') no-repeat;
  background-size: 100% 100%;
  opacity: 0.3;
}

.branches-content {
  position: relative;
  z-index: 10;
  max-width: 1280px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Page Header */
.page-header {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(156, 163, 175, 0.2);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

.page-subtitle {
  font-size: 1rem;
  color: #6b7280;
  margin: 0.5rem 0 0 0;
}

.add-branch-btn {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.add-branch-btn:hover {
  background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
  transform: translateY(-1px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

/* Loading Section */
.loading-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 0;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-spinner svg {
  color: #667eea;
}

.loading-text {
  font-size: 1rem;
  color: #6b7280;
  font-weight: 500;
}

/* Alert Messages */
.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border-radius: 0.75rem;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.alert-error {
  background: linear-gradient(135deg, rgba(248, 113, 113, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%);
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.alert-error svg {
  color: #ef4444;
  flex-shrink: 0;
}

.alert-success {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(22, 163, 74, 0.1) 100%);
  border: 1px solid #bbf7d0;
  color: #166534;
}

.alert-success svg {
  color: #22c55e;
  flex-shrink: 0;
}

/* Branches Grid */
.branches-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

/* Branch Card */
.branch-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(229, 231, 235, 0.8);
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
}

.branch-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.branch-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

/* Card Header */
.card-header {
  padding: 1.5rem 1.5rem 0;
}

.branch-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.branch-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.status-active {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(22, 163, 74, 0.15) 100%);
  color: #166534;
  border: 1px solid #bbf7d0;
}

.status-inactive {
  background: linear-gradient(135deg, rgba(248, 113, 113, 0.15) 0%, rgba(239, 68, 68, 0.15) 100%);
  color: #991b1b;
  border: 1px solid #fecaca;
}

/* Card Body */
.card-body {
  padding: 0 1.5rem 1.5rem;
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.info-icon {
  width: 1.125rem;
  height: 1.125rem;
  color: #667eea;
  margin-top: 0.125rem;
  flex-shrink: 0;
}

.info-label {
  font-weight: 500;
  color: #374151;
  margin-right: 0.5rem;
}

.info-value {
  color: #6b7280;
}

/* Statistics Section */
.statistics-section {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.stats-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin: 0 0 0.75rem 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.125rem;
}

/* Card Actions */
.card-actions {
  display: flex;
  gap: 0.5rem;
  padding: 0 1.5rem 1.5rem;
}

.action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-edit {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
}

.action-edit:hover {
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  transform: translateY(-1px);
}

.action-view {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.action-view:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-1px);
}

.action-delete {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: white;
}

.action-delete:hover {
  background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
  transform: translateY(-1px);
}

.action-activate {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.action-activate:hover {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-1px);
}

.action-deactivate {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.action-deactivate:hover {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  transform: translateY(-1px);
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 1000;
}

.modal-container {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(20px);
  border-radius: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-wide {
  max-width: 800px;
}

.modal-header {
  padding: 1.5rem 1.5rem 0;
  text-align: center;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Form Styling */
.modal-form {
  padding: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.form-input,
.form-textarea {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: rgba(249, 250, 251, 0.5);
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: white;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.form-checkbox {
  width: 1.125rem;
  height: 1.125rem;
  accent-color: #3b82f6;
}

.checkbox-text {
  font-size: 0.875rem;
  color: #374151;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(229, 231, 235, 0.5);
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-primary {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Statistics Modal Content */
.stats-modal-content {
  padding: 1.5rem;
}

.stats-summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.summary-card {
  padding: 1.5rem;
  border-radius: 0.75rem;
  text-align: center;
}

.summary-blue {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.summary-green {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(22, 163, 74, 0.1) 100%);
  border: 1px solid rgba(34, 197, 94, 0.2);
}

.summary-yellow {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.summary-purple {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
  border: 1px solid rgba(168, 85, 247, 0.2);
}

.summary-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.summary-blue .summary-value { color: #1d4ed8; }
.summary-green .summary-value { color: #059669; }
.summary-yellow .summary-value { color: #d97706; }
.summary-purple .summary-value { color: #9333ea; }

.summary-label {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

/* Revenue Section */
.revenue-section {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-radius: 0.75rem;
  padding: 1.5rem;
  border: 1px solid rgba(229, 231, 235, 0.8);
}

.revenue-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 1rem 0;
}

.revenue-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1.5rem;
}

.revenue-item {
  text-align: center;
}

.revenue-amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.25rem;
}

.revenue-period {
  font-size: 0.875rem;
  color: #6b7280;
}

/* Animations */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
  .branches-container {
    padding: 1rem;
  }
  
  .branches-content {
    padding: 1.5rem;
  }
  
  .branches-grid {
    grid-template-columns: 1fr;
  }
  
  .header-content {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .card-actions {
    flex-direction: column;
  }
  
  .modal-actions {
    flex-direction: column;
  }
}
</style>