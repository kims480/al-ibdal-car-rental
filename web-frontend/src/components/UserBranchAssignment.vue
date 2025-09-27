<template>
  <div class="branch-assignment-container">
    <!-- Header Section -->
    <div class="assignment-header">
      <div class="header-content">
        <div>
          <h3 class="header-title">User-Branch Assignment</h3>
          <p class="header-subtitle">Assign users to branches for access control and management</p>
        </div>
        <div class="header-actions">
          <button @click="openBulkAssignModal" class="bulk-assign-btn">
            <UsersIcon class="w-5 h-5" />
            Bulk Assign
          </button>
          <button @click="openAssignModal" class="assign-btn">
            <PlusIcon class="w-5 h-5" />
            New Assignment
          </button>
        </div>
      </div>
    </div>

    <!-- Filter and Search -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="filter-group">
          <label class="filter-label">Branch</label>
          <select v-model="filters.branch_id" @change="fetchAssignments" class="filter-select">
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }} ({{ branch.location }})
            </option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Role</label>
          <select v-model="filters.role" @change="fetchAssignments" class="filter-select">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="branch_manager">Branch Manager</option>
            <option value="employee">Employee</option>
            <option value="technician">Technician</option>
            <option value="partner">Partner</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Status</label>
          <select v-model="filters.status" @change="fetchAssignments" class="filter-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Search</label>
          <div class="search-wrapper">
            <MagnifyingGlassIcon class="search-icon" />
            <input 
              v-model="filters.search"
              @input="debounceSearch"
              type="text"
              placeholder="Search users..."
              class="search-input"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Branch Overview Cards -->
    <div class="branch-overview">
      <h4 class="overview-title">Branch Overview</h4>
      <div class="branch-cards-grid">
        <div 
          v-for="branch in branchesWithStats" 
          :key="branch.id"
          class="branch-card"
          @click="selectBranch(branch)"
          :class="{ 'selected': selectedBranch?.id === branch.id }"
        >
          <div class="branch-card-header">
            <div class="branch-icon">
              <BuildingOfficeIcon class="w-6 h-6" />
            </div>
            <div class="branch-info">
              <h5 class="branch-name">{{ branch.name }}</h5>
              <p class="branch-location">{{ branch.location }}</p>
            </div>
          </div>
          
          <div class="branch-stats">
            <div class="stat-item">
              <span class="stat-label">Total Users</span>
              <span class="stat-value">{{ branch.user_count || 0 }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Managers</span>
              <span class="stat-value">{{ branch.manager_count || 0 }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Active</span>
              <span class="stat-value">{{ branch.active_count || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignments Table -->
    <div class="assignments-table-section">
      <div class="table-header">
        <h4 class="table-title">
          {{ selectedBranch ? `${selectedBranch.name} - User Assignments` : 'All User-Branch Assignments' }}
        </h4>
        <div class="table-actions">
          <button @click="exportAssignments" class="export-btn">
            <ArrowDownTrayIcon class="w-4 h-4" />
            Export
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>Loading assignments...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="assignments.length === 0" class="empty-state">
        <UserGroupIcon class="empty-icon" />
        <h5 class="empty-title">No assignments found</h5>
        <p class="empty-description">Start by assigning users to branches</p>
        <button @click="openAssignModal" class="empty-action-btn">
          <PlusIcon class="w-4 h-4" />
          Create First Assignment
        </button>
      </div>

      <!-- Assignments Table -->
      <div v-else class="table-wrapper">
        <table class="assignments-table">
          <thead>
            <tr>
              <th class="table-header-cell">
                <input 
                  type="checkbox" 
                  @change="toggleSelectAll"
                  :checked="selectedAssignments.length === assignments.length"
                  class="table-checkbox"
                />
              </th>
              <th class="table-header-cell">User</th>
              <th class="table-header-cell">Branch</th>
              <th class="table-header-cell">Role</th>
              <th class="table-header-cell">Permissions</th>
              <th class="table-header-cell">Assignment Date</th>
              <th class="table-header-cell">Status</th>
              <th class="table-header-cell">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="assignment in assignments" 
              :key="`${assignment.user_id}-${assignment.branch_id}`"
              class="table-row"
            >
              <td class="table-cell">
                <input 
                  type="checkbox" 
                  :value="assignment.id"
                  v-model="selectedAssignments"
                  class="table-checkbox"
                />
              </td>
              <td class="table-cell">
                <div class="user-cell">
                  <div class="user-avatar">
                    {{ assignment.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                  </div>
                  <div class="user-info">
                    <div class="user-name">{{ assignment.user?.name || 'Unknown User' }}</div>
                    <div class="user-email">{{ assignment.user?.email || 'No Email' }}</div>
                  </div>
                </div>
              </td>
              <td class="table-cell">
                <div class="branch-cell">
                  <div class="branch-name">{{ assignment.branch?.name || 'Unknown Branch' }}</div>
                  <div class="branch-location">{{ assignment.branch?.location || 'No Location' }}</div>
                </div>
              </td>
              <td class="table-cell">
                <span :class="getRoleBadgeClass(assignment.role)" class="role-badge">
                  {{ formatRole(assignment.role) }}
                </span>
              </td>
              <td class="table-cell">
                <div class="permissions-list">
                  <span 
                    v-for="permission in getPermissions(assignment)" 
                    :key="permission"
                    class="permission-tag"
                  >
                    {{ permission }}
                  </span>
                </div>
              </td>
              <td class="table-cell">
                <div class="assignment-date">
                  {{ formatDate(assignment.created_at) }}
                </div>
              </td>
              <td class="table-cell">
                <button 
                  @click="toggleAssignmentStatus(assignment)"
                  :class="[
                    'status-toggle',
                    assignment.is_active ? 'status-active' : 'status-inactive'
                  ]"
                >
                  {{ assignment.is_active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="table-cell">
                <div class="action-buttons">
                  <button 
                    @click="editAssignment(assignment)"
                    class="action-btn edit"
                    title="Edit Assignment"
                  >
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button 
                    @click="viewAssignmentHistory(assignment)"
                    class="action-btn history"
                    title="View History"
                  >
                    <ClockIcon class="w-4 h-4" />
                  </button>
                  <button 
                    @click="confirmDeleteAssignment(assignment)"
                    class="action-btn delete"
                    title="Remove Assignment"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedAssignments.length > 0" class="bulk-actions">
        <div class="bulk-info">
          <span>{{ selectedAssignments.length }} assignment(s) selected</span>
        </div>
        <div class="bulk-buttons">
          <button @click="bulkActivate" class="bulk-btn activate">
            <CheckCircleIcon class="w-4 h-4" />
            Activate
          </button>
          <button @click="bulkDeactivate" class="bulk-btn deactivate">
            <XCircleIcon class="w-4 h-4" />
            Deactivate
          </button>
          <button @click="bulkDelete" class="bulk-btn delete">
            <TrashIcon class="w-4 h-4" />
            Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Assignment Modal -->
    <div v-if="showAssignmentModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">
            {{ isEditingAssignment ? 'Edit Assignment' : 'Create New Assignment' }}
          </h3>
          <button @click="closeAssignmentModal" class="modal-close">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitAssignment" class="assignment-form">
          <div class="form-section">
            <h4 class="section-title">Assignment Details</h4>
            
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">User *</label>
                <select 
                  v-model="assignmentForm.user_id" 
                  required 
                  class="form-select"
                  :disabled="isEditingAssignment"
                >
                  <option value="">Select User</option>
                  <option 
                    v-for="user in availableUsers" 
                    :key="user.id" 
                    :value="user.id"
                  >
                    {{ user.name }} - {{ user.email }}
                  </option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Branch *</label>
                <select 
                  v-model="assignmentForm.branch_id" 
                  required 
                  class="form-select"
                  :disabled="isEditingAssignment"
                >
                  <option value="">Select Branch</option>
                  <option 
                    v-for="branch in branches" 
                    :key="branch.id" 
                    :value="branch.id"
                  >
                    {{ branch.name }} - {{ branch.location }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Role in Branch *</label>
                <select 
                  v-model="assignmentForm.role" 
                  required 
                  class="form-select"
                >
                  <option value="">Select Role</option>
                  <option value="branch_manager">Branch Manager</option>
                  <option value="assistant_manager">Assistant Manager</option>
                  <option value="employee">Employee</option>
                  <option value="technician">Technician</option>
                  <option value="supervisor">Supervisor</option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Access Level</label>
                <select 
                  v-model="assignmentForm.access_level" 
                  class="form-select"
                >
                  <option value="full">Full Access</option>
                  <option value="limited">Limited Access</option>
                  <option value="read_only">Read Only</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Permissions</label>
              <div class="permissions-grid">
                <label 
                  v-for="permission in availablePermissions" 
                  :key="permission.key"
                  class="permission-checkbox"
                >
                  <input 
                    type="checkbox" 
                    :value="permission.key"
                    v-model="assignmentForm.permissions"
                  />
                  <span class="permission-name">{{ permission.name }}</span>
                  <span class="permission-desc">{{ permission.description }}</span>
                </label>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Assignment Notes</label>
              <textarea 
                v-model="assignmentForm.notes"
                rows="3"
                class="form-textarea"
                placeholder="Additional notes about this assignment..."
              ></textarea>
            </div>

            <div class="form-checkboxes">
              <label class="checkbox-label">
                <input 
                  type="checkbox" 
                  v-model="assignmentForm.is_active"
                />
                <span>Active Assignment</span>
              </label>
              
              <label class="checkbox-label">
                <input 
                  type="checkbox" 
                  v-model="assignmentForm.send_notification"
                />
                <span>Send notification to user</span>
              </label>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeAssignmentModal" class="btn-cancel">
              Cancel
            </button>
            <button type="submit" :disabled="submittingAssignment" class="btn-primary">
              <span v-if="submittingAssignment" class="loading-spinner-small"></span>
              {{ submittingAssignment ? 'Saving...' : (isEditingAssignment ? 'Update Assignment' : 'Create Assignment') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Assignment Modal -->
    <div v-if="showBulkModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Bulk User Assignment</h3>
          <button @click="closeBulkModal" class="modal-close">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitBulkAssignment" class="bulk-form">
          <div class="form-section">
            <h4 class="section-title">Select Users and Branch</h4>
            
            <div class="form-group">
              <label class="form-label">Target Branch *</label>
              <select v-model="bulkForm.branch_id" required class="form-select">
                <option value="">Select Branch</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ branch.name }} - {{ branch.location }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Users to Assign *</label>
              <div class="users-selection">
                <div class="users-filter">
                  <input 
                    v-model="bulkUsersFilter"
                    type="text"
                    placeholder="Search users..."
                    class="filter-input"
                  />
                </div>
                <div class="users-list">
                  <label 
                    v-for="user in filteredBulkUsers" 
                    :key="user.id"
                    class="user-checkbox"
                  >
                    <input 
                      type="checkbox" 
                      :value="user.id"
                      v-model="bulkForm.user_ids"
                    />
                    <div class="user-info">
                      <div class="user-name">{{ user.name }}</div>
                      <div class="user-email">{{ user.email }}</div>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Default Role</label>
                <select v-model="bulkForm.role" class="form-select">
                  <option value="employee">Employee</option>
                  <option value="technician">Technician</option>
                  <option value="supervisor">Supervisor</option>
                </select>
              </div>
              
              <div class="form-group">
                <label class="form-label">Access Level</label>
                <select v-model="bulkForm.access_level" class="form-select">
                  <option value="limited">Limited Access</option>
                  <option value="full">Full Access</option>
                  <option value="read_only">Read Only</option>
                </select>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" @click="closeBulkModal" class="btn-cancel">
              Cancel
            </button>
            <button type="submit" :disabled="submittingBulk || bulkForm.user_ids.length === 0" class="btn-primary">
              <span v-if="submittingBulk" class="loading-spinner-small"></span>
              Assign {{ bulkForm.user_ids.length }} Users
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { toast } from '../composables/useToast'
import {
  PlusIcon,
  UsersIcon,
  MagnifyingGlassIcon,
  BuildingOfficeIcon,
  UserGroupIcon,
  ArrowDownTrayIcon,
  PencilSquareIcon,
  ClockIcon,
  TrashIcon,
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import axios from 'axios'

// Reactive data
const assignments = ref([])
const branches = ref([])
const availableUsers = ref([])
const selectedBranch = ref(null)
const selectedAssignments = ref([])
const loading = ref(false)
const showAssignmentModal = ref(false)
const showBulkModal = ref(false)
const isEditingAssignment = ref(false)
const submittingAssignment = ref(false)
const submittingBulk = ref(false)
const currentAssignment = ref(null)
const bulkUsersFilter = ref('')

// Form data
const assignmentForm = reactive({
  user_id: '',
  branch_id: '',
  role: '',
  access_level: 'limited',
  permissions: [],
  notes: '',
  is_active: true,
  send_notification: true
})

const bulkForm = reactive({
  branch_id: '',
  user_ids: [],
  role: 'employee',
  access_level: 'limited'
})

const filters = reactive({
  branch_id: '',
  role: '',
  status: '',
  search: ''
})

// Available permissions
const availablePermissions = ref([
  { key: 'view_dashboard', name: 'View Dashboard', description: 'Access to branch dashboard' },
  { key: 'manage_users', name: 'Manage Users', description: 'Add/edit branch users' },
  { key: 'manage_cars', name: 'Manage Cars', description: 'Add/edit branch vehicles' },
  { key: 'manage_rentals', name: 'Manage Rentals', description: 'Process rental requests' },
  { key: 'manage_service', name: 'Manage Service', description: 'Handle service requests' },
  { key: 'view_reports', name: 'View Reports', description: 'Access branch reports' },
  { key: 'manage_settings', name: 'Manage Settings', description: 'Modify branch settings' }
])

// Computed properties
const branchesWithStats = computed(() => {
  return branches.value.map(branch => {
    const branchAssignments = assignments.value.filter(a => a.branch_id === branch.id)
    return {
      ...branch,
      user_count: branchAssignments.length,
      manager_count: branchAssignments.filter(a => a.role === 'branch_manager').length,
      active_count: branchAssignments.filter(a => a.is_active).length
    }
  })
})

const filteredBulkUsers = computed(() => {
  if (!bulkUsersFilter.value) return availableUsers.value
  
  const filter = bulkUsersFilter.value.toLowerCase()
  return availableUsers.value.filter(user => 
    user.name.toLowerCase().includes(filter) || 
    user.email.toLowerCase().includes(filter)
  )
})

// Methods
const fetchAssignments = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.branch_id) params.append('branch_id', filters.branch_id)
    if (filters.role) params.append('role', filters.role)
    if (filters.status) params.append('status', filters.status)
    if (filters.search) params.append('search', filters.search)
    
    const response = await axios.get(`/user-branch-assignments?${params}`)
    assignments.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching assignments:', error)
  } finally {
    loading.value = false
  }
}

const fetchBranches = async () => {
  try {
    const response = await axios.get('/branches')
    branches.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching branches:', error)
  }
}

const fetchAvailableUsers = async () => {
  try {
    const response = await axios.get('/users?active=1')
    availableUsers.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const selectBranch = (branch) => {
  selectedBranch.value = selectedBranch.value?.id === branch.id ? null : branch
  filters.branch_id = selectedBranch.value?.id || ''
  fetchAssignments()
}

const getRoleBadgeClass = (role) => {
  const classMap = {
    'branch_manager': 'role-manager',
    'assistant_manager': 'role-assistant',
    'employee': 'role-employee',
    'technician': 'role-technician',
    'supervisor': 'role-supervisor'
  }
  return classMap[role] || 'role-default'
}

const formatRole = (role) => {
  return role ? role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Unknown'
}

const getPermissions = (assignment) => {
  if (!assignment.permissions) return ['View']
  return assignment.permissions.slice(0, 3)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const debounceSearch = (() => {
  let timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      fetchAssignments()
    }, 500)
  }
})()

// Modal methods
const openAssignModal = async () => {
  isEditingAssignment.value = false
  currentAssignment.value = null
  resetAssignmentForm()
  await fetchAvailableUsers()
  showAssignmentModal.value = true
}

const openBulkAssignModal = async () => {
  bulkForm.branch_id = ''
  bulkForm.user_ids = []
  bulkForm.role = 'employee'
  bulkForm.access_level = 'limited'
  await fetchAvailableUsers()
  showBulkModal.value = true
}

const closeAssignmentModal = () => {
  showAssignmentModal.value = false
  resetAssignmentForm()
}

const closeBulkModal = () => {
  showBulkModal.value = false
}

const resetAssignmentForm = () => {
  assignmentForm.user_id = ''
  assignmentForm.branch_id = ''
  assignmentForm.role = ''
  assignmentForm.access_level = 'limited'
  assignmentForm.permissions = []
  assignmentForm.notes = ''
  assignmentForm.is_active = true
  assignmentForm.send_notification = true
}

const editAssignment = async (assignment) => {
  isEditingAssignment.value = true
  currentAssignment.value = assignment
  
  assignmentForm.user_id = assignment.user_id
  assignmentForm.branch_id = assignment.branch_id
  assignmentForm.role = assignment.role
  assignmentForm.access_level = assignment.access_level || 'limited'
  assignmentForm.permissions = assignment.permissions || []
  assignmentForm.notes = assignment.notes || ''
  assignmentForm.is_active = assignment.is_active !== false
  assignmentForm.send_notification = false
  
  await fetchAvailableUsers()
  showAssignmentModal.value = true
}

const submitAssignment = async () => {
  submittingAssignment.value = true
  try {
    let response
    
    if (isEditingAssignment.value && currentAssignment.value) {
      response = await axios.put(`/user-branch-assignments/${currentAssignment.value.id}`, assignmentForm)
    } else {
      response = await axios.post('/user-branch-assignments', assignmentForm)
    }
    
    if (response.data.success) {
      closeAssignmentModal()
      fetchAssignments()
      toast.success(isEditingAssignment.value ? 'Assignment updated successfully!' : 'User assigned successfully!')
    }
  } catch (error) {
    console.error('Error saving assignment:', error)
    toast.error(error.response?.data?.message || 'Failed to save assignment')
  } finally {
    submittingAssignment.value = false
  }
}

const submitBulkAssignment = async () => {
  submittingBulk.value = true
  try {
    const response = await axios.post('/user-branch-assignments/bulk', bulkForm)
    
    if (response.data.success) {
      closeBulkModal()
      fetchAssignments()
      toast.success(`Successfully assigned ${bulkForm.user_ids.length} users to the branch!`)
    }
  } catch (error) {
    console.error('Error bulk assigning:', error)
    toast.error(error.response?.data?.message || 'Failed to bulk assign users')
  } finally {
    submittingBulk.value = false
  }
}

const toggleAssignmentStatus = async (assignment) => {
  try {
    const response = await axios.patch(`/user-branch-assignments/${assignment.id}/toggle-status`)
    
    if (response.data.success) {
      fetchAssignments()
      toast.success(`Assignment ${assignment.is_active ? 'deactivated' : 'activated'} successfully!`)
    }
  } catch (error) {
    console.error('Error toggling status:', error)
    toast.error('Failed to update assignment status')
  }
}

const confirmDeleteAssignment = (assignment) => {
  toast.confirm(
    `Remove ${assignment.user?.name} from ${assignment.branch?.name}?`,
    {
      title: 'Remove Assignment',
      onConfirm: () => deleteAssignment(assignment)
    }
  )
}

const deleteAssignment = async (assignment) => {
  try {
    const response = await axios.delete(`/user-branch-assignments/${assignment.id}`)
    
    if (response.data.success) {
      fetchAssignments()
      toast.success('Assignment removed successfully!')
    }
  } catch (error) {
    console.error('Error deleting assignment:', error)
    toast.error('Failed to remove assignment')
  }
}

const toggleSelectAll = (event) => {
  if (event.target.checked) {
    selectedAssignments.value = assignments.value.map(a => a.id)
  } else {
    selectedAssignments.value = []
  }
}

const bulkActivate = async () => {
  await bulkUpdateStatus(true)
}

const bulkDeactivate = async () => {
  await bulkUpdateStatus(false)
}

const bulkUpdateStatus = async (isActive) => {
  try {
    const response = await axios.patch('/user-branch-assignments/bulk-status', {
      assignment_ids: selectedAssignments.value,
      is_active: isActive
    })
    
    if (response.data.success) {
      const count = selectedAssignments.value.length
      fetchAssignments()
      selectedAssignments.value = []
      toast.success(`${isActive ? 'Activated' : 'Deactivated'} ${count} assignments!`)
    }
  } catch (error) {
    console.error('Error bulk updating status:', error)
    toast.error('Failed to update assignments')
  }
}

const bulkDelete = async () => {
  const count = selectedAssignments.value.length
  toast.confirm(
    `Delete ${count} assignments? This action cannot be undone.`,
    {
      title: 'Delete Assignments',
      onConfirm: async () => {
        try {
          const response = await axios.delete('/user-branch-assignments/bulk', {
            data: { assignment_ids: selectedAssignments.value }
          })
          
          if (response.data.success) {
            fetchAssignments()
            selectedAssignments.value = []
            toast.success('Assignments deleted successfully!')
          }
        } catch (error) {
          console.error('Error bulk deleting:', error)
          toast.error('Failed to delete assignments')
        }
      }
    }
  )
}

const exportAssignments = () => {
  // Implementation for exporting assignments to CSV/Excel
  console.log('Export assignments functionality')
}

const viewAssignmentHistory = (assignment) => {
  // Implementation for viewing assignment history
  console.log('View assignment history:', assignment)
}

// Lifecycle
onMounted(() => {
  fetchBranches()
  fetchAssignments()
})
</script>

<style scoped>
/* Container */
.branch-assignment-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  min-height: 100vh;
}

/* Header */
.assignment-header {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.header-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.header-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.bulk-assign-btn, .assign-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.bulk-assign-btn {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.assign-btn {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
}

.bulk-assign-btn:hover, .assign-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

/* Filters */
.filters-section {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.filter-select, .search-input {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.filter-select:focus, .search-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1rem;
  height: 1rem;
  color: #6b7280;
}

.search-input {
  padding-left: 2.5rem;
}

/* Branch Overview */
.branch-overview {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.overview-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
}

.branch-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.branch-card {
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8fafc;
}

.branch-card:hover {
  border-color: #6366f1;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
}

.branch-card.selected {
  border-color: #6366f1;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
}

.branch-card-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.branch-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.branch-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.branch-location {
  font-size: 0.875rem;
  color: #6b7280;
}

.branch-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
}

.stat-item {
  text-align: center;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 0.5rem;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.stat-value {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

/* Assignments Table */
.assignments-table-section {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.table-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.table-actions {
  display: flex;
  gap: 1rem;
}

.export-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  color: #374151;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.export-btn:hover {
  background: #e5e7eb;
}

/* Loading and Empty States */
.loading-state, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #6b7280;
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid #e5e7eb;
  border-top: 3px solid #6366f1;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

.empty-icon {
  width: 3rem;
  height: 3rem;
  color: #d1d5db;
  margin: 0 auto 1rem;
}

.empty-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-description {
  margin-bottom: 1.5rem;
}

.empty-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* Table */
.table-wrapper {
  overflow-x: auto;
}

.assignments-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header-cell {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  border-bottom: 1px solid #e5e7eb;
}

.table-row {
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(229, 231, 235, 0.3);
}

.table-row:hover {
  background: rgba(99, 102, 241, 0.02);
}

.table-cell {
  padding: 1rem;
  vertical-align: middle;
}

.table-checkbox {
  width: 1rem;
  height: 1rem;
  border-radius: 0.25rem;
}

/* User Cell */
.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.875rem;
}

.user-email {
  color: #6b7280;
  font-size: 0.75rem;
}

/* Branch Cell */
.branch-cell {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.branch-name {
  font-weight: 500;
  color: #1f2937;
  font-size: 0.875rem;
}

.branch-location {
  color: #6b7280;
  font-size: 0.75rem;
}

/* Role Badges */
.role-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.role-manager {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.1) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.role-assistant {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(165, 180, 252, 0.1) 100%);
  color: #6366f1;
  border: 1px solid rgba(99, 102, 241, 0.3);
}

.role-employee {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(134, 239, 172, 0.1) 100%);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.role-technician {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.role-supervisor {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(196, 181, 253, 0.1) 100%);
  color: #a855f7;
  border: 1px solid rgba(168, 85, 247, 0.3);
}

.role-default {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

/* Permissions */
.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}

.permission-tag {
  padding: 0.25rem 0.5rem;
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
  border-radius: 0.375rem;
  font-size: 0.625rem;
  font-weight: 500;
}

/* Status Toggle */
.status-toggle {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid;
  cursor: pointer;
  transition: all 0.3s ease;
}

.status-active {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(134, 239, 172, 0.1) 100%);
  color: #16a34a;
  border-color: rgba(34, 197, 94, 0.3);
}

.status-inactive {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border-color: rgba(107, 114, 128, 0.3);
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  border: 1px solid;
  cursor: pointer;
}

.action-btn.edit {
  background: rgba(168, 85, 247, 0.1);
  border-color: rgba(168, 85, 247, 0.3);
  color: #a855f7;
}

.action-btn.history {
  background: rgba(59, 130, 246, 0.1);
  border-color: rgba(59, 130, 246, 0.3);
  color: #3b82f6;
}

.action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

.action-btn:hover {
  transform: scale(1.1);
}

/* Bulk Actions */
.bulk-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 0.5rem;
  margin-top: 1rem;
  border: 1px solid #e5e7eb;
}

.bulk-info {
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.bulk-buttons {
  display: flex;
  gap: 0.5rem;
}

.bulk-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  border: 1px solid;
  cursor: pointer;
  transition: all 0.3s ease;
}

.bulk-btn.activate {
  background: rgba(34, 197, 94, 0.1);
  color: #16a34a;
  border-color: rgba(34, 197, 94, 0.3);
}

.bulk-btn.deactivate {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-color: rgba(245, 158, 11, 0.3);
}

.bulk-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-color: rgba(239, 68, 68, 0.3);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal-container {
  background: white;
  border-radius: 1rem;
  width: 100%;
  max-width: 2rem;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.modal-close {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-close:hover {
  background: #f3f4f6;
  color: #374151;
}

/* Form Styles */
.assignment-form, .bulk-form {
  padding: 1.5rem;
}

.form-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.form-select, .form-textarea {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

/* Permissions Grid */
.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 0.75rem;
}

.permission-checkbox {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.permission-checkbox:hover {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.02);
}

.permission-checkbox:has(input:checked) {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

.permission-name {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.permission-desc {
  color: #6b7280;
  font-size: 0.75rem;
}

/* Form Checkboxes */
.form-checkboxes {
  display: flex;
  gap: 1.5rem;
  margin-top: 1rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
}

/* Bulk Users Selection */
.users-selection {
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  overflow: hidden;
}

.users-filter {
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f8fafc;
}

.filter-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.users-list {
  max-height: 200px;
  overflow-y: auto;
}

.user-checkbox {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: background 0.2s ease;
}

.user-checkbox:hover {
  background: #f8fafc;
}

.user-checkbox:has(input:checked) {
  background: rgba(99, 102, 241, 0.05);
}

/* Modal Actions */
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
  background: #f8fafc;
}

.btn-cancel, .btn-primary {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-cancel {
  background: #f3f4f6;
  color: #374151;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-primary {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-spinner-small {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Animations */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .branch-assignment-container {
    padding: 0.5rem;
    gap: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .header-actions {
    flex-direction: column;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
  
  .branch-cards-grid {
    grid-template-columns: 1fr;
  }
  
  .modal-container {
    max-width: 100%;
    margin: 1rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .permissions-grid {
    grid-template-columns: 1fr;
  }
  
  .form-checkboxes {
    flex-direction: column;
    gap: 1rem;
  }
  
  .bulk-actions {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .table-wrapper {
    font-size: 0.875rem;
  }
  
  .table-header-cell, .table-cell {
    padding: 0.5rem;
  }
}
</style>