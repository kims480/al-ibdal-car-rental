<template>
  <div class="users-container">
    <div class="users-content">
      <!-- Loading skeleton -->
      <CRUDTableSkeleton v-if="initialLoading" />
      
      <!-- Main content -->
      <div v-else>
        <!-- Header Section -->
        <div class="page-header">
          <div class="header-content">
            <div>
              <h1 class="page-title">User Management</h1>
              <p class="page-subtitle">Manage system users and their roles</p>
            </div>
            <div class="header-actions">
              <button @click="openBranchAssignments" class="branch-assign-btn">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Manage Branch Assignments
              </button>
              <button @click="openCreateModal" class="add-user-btn">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add User
              </button>
            </div>
          </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-card">
          <div class="filters-grid">
            <div class="filter-group">
              <label class="filter-label">Search</label>
              <div class="input-wrapper">
                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input 
                  v-model="filters.search"
                  type="text" 
                  placeholder="Search by name or email..."
                  class="filter-input"
                />
              </div>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">Role</label>
              <select v-model="filters.role" class="filter-select">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="branch_manager">Branch Manager</option>
                <option value="partner">Partner</option>
                <option value="customer">Customer</option>
              </select>
            </div>
            
            <div class="filter-group">
              <label class="filter-label">Branch</label>
              <select v-model="filters.branch_id" class="filter-select">
                <option value="">All Branches</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ branch.name }}
                </option>
              </select>
            </div>
            
            <div class="filter-group filter-action">
              <button @click="fetchUsers" class="filter-btn">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter
              </button>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="table-card">
          <!-- Loading State -->
          <div v-if="loading" class="loading-section">
            <div class="loading-spinner">
              <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="loading-text">Loading users...</p>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="users.length === 0" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <p class="empty-text">No users found</p>
            <p class="empty-subtext">Try adjusting your filters or add a new user</p>
          </div>

          <!-- Users Table -->
          <div v-else class="table-wrapper">
            <table class="users-table">
              <thead class="table-header">
                <tr>
                  <th class="table-th">User</th>
                  <th class="table-th">Role</th>
                  <th class="table-th">Branch</th>
                  <th class="table-th">Phone</th>
                  <th class="table-th">Status</th>
                  <th class="table-th">Actions</th>
                </tr>
              </thead>
              <tbody class="table-body">
                <tr v-for="user in users" :key="user.id" class="table-row">
                  <td class="table-cell">
                    <div class="user-info">
                      <div class="user-avatar">
                        <svg class="avatar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                      </div>
                      <div class="user-details">
                        <div class="user-name">{{ user.name }}</div>
                        <div class="user-email">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="table-cell">
                    <span :class="getRoleClass(user.role)" class="role-badge">
                      {{ formatRole(user.role) }}
                    </span>
                  </td>
                  <td class="table-cell">
                    <span class="branch-name">{{ user.branch?.name || '-' }}</span>
                  </td>
                  <td class="table-cell">
                    <span class="phone-number">{{ user.phone }}</span>
                  </td>
                  <td class="table-cell">
                    <button 
                      @click="toggleUserStatus(user)"
                      :class="[
                        'status-toggle',
                        user.active ? 'status-active' : 'status-inactive'
                      ]"
                    >
                      {{ user.active ? 'Active' : 'Inactive' }}
                    </button>
                  </td>
                  <td class="table-cell">
                    <div class="action-buttons">
                      <button @click="quickAssignBranch(user)" class="action-btn action-assign">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Assign
                      </button>
                      <button @click="openEditModal(user)" class="action-btn action-edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                      </button>
                      <button @click="confirmDelete(user)" class="action-btn action-delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
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
        <div v-if="showModal" class="modal-overlay">
          <div class="modal-container">
            <div class="modal-header">
              <h3 class="modal-title">
                {{ isEditing ? 'Edit User' : 'Create New User' }}
              </h3>
            </div>

            <form @submit.prevent="submitForm" class="modal-form">
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Name *</label>
                  <input 
                    v-model="form.name"
                    type="text" 
                    required
                    class="form-input"
                    placeholder="Enter full name"
                  />
                </div>
                
                <div class="form-group">
                  <label class="form-label">Email *</label>
                  <input 
                    v-model="form.email"
                    type="email" 
                    required
                    class="form-input"
                    placeholder="Enter email address"
                  />
                </div>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">Phone *</label>
                  <input 
                    v-model="form.phone"
                    type="tel" 
                    required
                    class="form-input"
                    placeholder="Enter phone number"
                  />
                </div>
                
                <div class="form-group">
                  <label class="form-label">Role *</label>
                  <select 
                    v-model="form.role"
                    required
                    class="form-select"
                  >
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="branch_manager">Branch Manager</option>
                    <option value="partner">Partner</option>
                    <option value="customer">Customer</option>
                  </select>
                </div>
              </div>

              <div v-if="['branch_manager', 'employee', 'technician', 'supervisor'].includes(form.role)" class="form-group">
                <label class="form-label">Branch Assignment *</label>
                <select 
                  v-model="form.branch_id"
                  required
                  class="form-select"
                >
                  <option value="">Select Branch</option>
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }} - {{ branch.location }}
                  </option>
                </select>
              </div>

              <div v-if="form.branch_id && form.role !== 'branch_manager'" class="form-group">
                <label class="form-label">Role in Branch</label>
                <select 
                  v-model="form.branch_role"
                  class="form-select"
                >
                  <option value="">Select Role</option>
                  <option value="assistant_manager">Assistant Manager</option>
                  <option value="employee">Employee</option>
                  <option value="technician">Technician</option>
                  <option value="supervisor">Supervisor</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">
                  {{ isEditing ? 'New Password (optional)' : 'Password *' }}
                </label>
                <input 
                  v-model="form.password"
                  type="password" 
                  :required="!isEditing"
                  minlength="8"
                  class="form-input"
                  :placeholder="isEditing ? 'Leave blank to keep current password' : 'Enter password (min 8 characters)'"
                />
              </div>

              <div class="modal-actions">
                <button type="button" @click="closeModal" class="btn-cancel">
                  Cancel
                </button>
                <button type="submit" :disabled="submitting" class="btn-primary">
                  <svg v-if="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ submitting ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Branch Assignments Management Modal -->
        <div v-if="showBranchAssignments" class="modal-overlay">
          <div class="branch-modal-container">
            <div class="modal-header">
              <h3 class="modal-title">User-Branch Assignment Management</h3>
              <button @click="closeBranchAssignments" class="close-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            
            <div class="branch-modal-content">
              <UserBranchAssignment />
            </div>
          </div>
        </div>

        <!-- Quick Assign Branch Modal -->
        <div v-if="showQuickAssignModal" class="modal-overlay">
          <div class="modal-container">
            <div class="modal-header">
              <h3 class="modal-title">
                Quick Assign Branch: {{ selectedUserForAssign?.name }}
              </h3>
              <button @click="showQuickAssignModal = false" class="close-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            
            <form @submit.prevent="submitQuickAssign" class="modal-body">
              <div class="form-group">
                <label class="form-label">Branch *</label>
                <select 
                  v-model="quickAssignForm.branch_id"
                  required
                  class="form-select"
                >
                  <option value="">Select Branch</option>
                  <option 
                    v-for="branch in branches" 
                    :key="branch.id" 
                    :value="branch.id"
                  >
                    {{ branch.name }}
                  </option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Role *</label>
                <select 
                  v-model="quickAssignForm.role"
                  required
                  class="form-select"
                >
                  <option value="staff">Staff</option>
                  <option value="assistant_manager">Assistant Manager</option>
                  <option value="supervisor">Supervisor</option>
                  <option value="technician">Technician</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Start Date *</label>
                <input 
                  v-model="quickAssignForm.start_date"
                  type="date"
                  required
                  class="form-input"
                />
              </div>

              <div class="modal-actions">
                <button type="button" @click="showQuickAssignModal = false" class="btn-cancel">
                  Cancel
                </button>
                <button type="submit" :disabled="submitting" class="btn-primary">
                  <svg v-if="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ submitting ? 'Assigning...' : 'Assign to Branch' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useAPICache } from '../composables/useAPICache'
import { toast } from '../composables/useToast'
import CRUDTableSkeleton from '../components/CRUDTableSkeleton.vue'
import UserBranchAssignment from '../components/UserBranchAssignment.vue'
import axios from 'axios'

export default {
  name: 'Users',
  components: {
    CRUDTableSkeleton,
    UserBranchAssignment
  },
  setup() {
    const { fetchBranches, getBranches, fetchUsers: fetchCachedUsers, getUsers } = useAPICache()
    
    const users = computed(() => getUsers.value)
    const branches = computed(() => getBranches.value)
    const loading = ref(false)
    const initialLoading = ref(true)
    const showModal = ref(false)
    const showBranchAssignments = ref(false)
    const showQuickAssignModal = ref(false)
    const isEditing = ref(false)
    const submitting = ref(false)
    const currentUser = ref(null)
    const selectedUserForAssign = ref(null)

    const filters = reactive({
      search: '',
      role: '',
      branch_id: ''
    })

    const form = reactive({
      name: '',
      email: '',
      phone: '',
      role: '',
      branch_id: '',
      branch_role: '',
      password: ''
    })

    const quickAssignForm = reactive({
      branch_id: '',
      role: 'staff',
      start_date: new Date().toISOString().split('T')[0]
    })

    const fetchUsers = async () => {
      loading.value = true
      try {
        const params = {}
        if (filters.search) params.search = filters.search
        if (filters.role) params.role = filters.role
        if (filters.branch_id) params.branch_id = filters.branch_id

        const response = await axios.get('/users', { params })
        
        if (response.data.success) {
          users.value = response.data.data.data || response.data.data
        }
      } catch (error) {
        console.error('Error fetching users:', error)
        toast.error('Failed to fetch users')
      } finally {
        loading.value = false
      }
    }

    const openCreateModal = () => {
      isEditing.value = false
      resetForm()
      showModal.value = true
    }

    const openEditModal = (user) => {
      isEditing.value = true
      currentUser.value = user
      
      form.name = user.name
      form.email = user.email
      form.phone = user.phone
      form.role = user.role
      form.branch_id = user.branch_id || ''
      form.branch_role = user.branch_role || ''
      form.password = ''
      
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      resetForm()
      currentUser.value = null
    }

    const openBranchAssignments = () => {
      showBranchAssignments.value = true
    }

    const closeBranchAssignments = () => {
      showBranchAssignments.value = false
    }

    const quickAssignBranch = (user) => {
      selectedUserForAssign.value = user
      quickAssignForm.branch_id = ''
      quickAssignForm.role = 'staff'
      quickAssignForm.start_date = new Date().toISOString().split('T')[0]
      showQuickAssignModal.value = true
    }

    const submitQuickAssign = async () => {
      if (!selectedUserForAssign.value || !quickAssignForm.branch_id) {
        toast.warning('Please select a branch')
        return
      }

      try {
        submitting.value = true
        const response = await axios.post('/user-branch-assignments', {
          user_id: selectedUserForAssign.value.id,
          ...quickAssignForm
        })

        if (response.data.success) {
          toast.success('Branch assignment successful!')
          showQuickAssignModal.value = false
          await fetchUsers()
        }
      } catch (error) {
        console.error('Error assigning branch:', error)
        toast.error('Failed to assign branch: ' + (error.response?.data?.message || error.message))
      } finally {
        submitting.value = false
      }
    }

    const resetForm = () => {
      form.name = ''
      form.email = ''
      form.phone = ''
      form.role = ''
      form.branch_id = ''
      form.branch_role = ''
      form.password = ''
    }

    const submitForm = async () => {
      submitting.value = true
      try {
        const data = { ...form }
        if (data.role !== 'branch_manager') {
          data.branch_id = null
        }
        if (isEditing.value && !data.password) {
          delete data.password
        }

        let response
        if (isEditing.value) {
          response = await axios.put(`/users/${currentUser.value.id}`, data)
        } else {
          response = await axios.post('/users', data)
        }

        if (response.data.success) {
          closeModal()
          fetchUsers()
          toast.success(isEditing.value ? 'User updated successfully!' : 'User created successfully!')
        }
      } catch (error) {
        console.error('Error saving user:', error)
        toast.error(error.response?.data?.message || 'Failed to save user')
      } finally {
        submitting.value = false
      }
    }

    const toggleUserStatus = async (user) => {
      try {
        const response = await axios.patch(`/users/${user.id}/status`, {
          active: !user.active
        })

        if (response.data.success) {
          user.active = !user.active
          toast.success('User status updated successfully!')
        }
      } catch (error) {
        console.error('Error updating user status:', error)
        toast.error('Failed to update user status')
      }
    }

    const confirmDelete = (user) => {
      toast.confirm(
        `Are you sure you want to delete ${user.name}? This action cannot be undone.`,
        {
          title: 'Delete User',
          onConfirm: () => deleteUser(user)
        }
      )
    }

    const deleteUser = async (user) => {
      try {
        const response = await axios.delete(`/users/${user.id}`)
        
        if (response.data.success) {
          fetchUsers()
          toast.success('User deleted successfully!')
        }
      } catch (error) {
        console.error('Error deleting user:', error)
        toast.error(error.response?.data?.message || 'Failed to delete user')
      }
    }

    const getRoleClass = (role) => {
      switch (role) {
        case 'admin':
          return 'bg-purple-100 text-purple-800'
        case 'branch_manager':
          return 'bg-blue-100 text-blue-800'
        case 'partner':
          return 'bg-green-100 text-green-800'
        case 'customer':
          return 'bg-gray-100 text-gray-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    }

    const formatRole = (role) => {
      return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
    }

    // Watch for role changes to reset branch_id
    watch(() => form.role, (newRole) => {
      if (newRole !== 'branch_manager') {
        form.branch_id = ''
      }
    })

    onMounted(async () => {
      // Start both API calls in parallel for better performance
      try {
        const [usersPromise, branchesPromise] = await Promise.all([
          fetchCachedUsers(),
          fetchBranches()
        ])
      } catch (error) {
        console.error('Error loading initial data:', error)
      } finally {
        initialLoading.value = false
      }
    })

    return {
      users,
      branches,
      loading,
      initialLoading,
      showModal,
      showBranchAssignments,
      showQuickAssignModal,
      isEditing,
      submitting,
      selectedUserForAssign,
      filters,
      form,
      quickAssignForm,
      fetchUsers,
      openCreateModal,
      openEditModal,
      closeModal,
      openBranchAssignments,
      closeBranchAssignments,
      quickAssignBranch,
      submitQuickAssign,
      submitForm,
      toggleUserStatus,
      confirmDelete,
      getRoleClass,
      formatRole
    }
  }
}
</script>

<style scoped>
/* Users Container - Material Design */
.users-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 1rem;
  position: relative;
}

.users-container::before {
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

.users-content {
  position: relative;
  z-index: 10;
  max-width: 1400px;
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

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.branch-assign-btn {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
}

.branch-assign-btn:hover {
  background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  transform: translateY(-1px);
  box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
}

.add-user-btn {
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

.add-user-btn:hover {
  background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
  transform: translateY(-1px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
}

/* Filters Section */
.filters-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(229, 231, 235, 0.8);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-action {
  align-self: end;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1rem;
  height: 1rem;
  color: #9ca3af;
  pointer-events: none;
}

.filter-input,
.filter-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: rgba(249, 250, 251, 0.5);
}

.filter-input {
  padding-left: 2.5rem;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: white;
}

.filter-btn {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-btn:hover {
  background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
  transform: translateY(-1px);
}

/* Table Card */
.table-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(229, 231, 235, 0.8);
  overflow: hidden;
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

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-text {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-subtext {
  font-size: 0.875rem;
  color: #6b7280;
}

/* Table */
.table-wrapper {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

.table-th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid rgba(229, 231, 235, 0.8);
}

.table-body {
  background: white;
}

.table-row {
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
  transition: all 0.2s ease;
}

.table-row:hover {
  background: rgba(249, 250, 251, 0.5);
}

.table-cell {
  padding: 1rem 1.5rem;
  vertical-align: middle;
}

/* User Info */
.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: #6366f1;
}

.user-details {
  min-width: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.125rem;
}

.user-email {
  font-size: 0.75rem;
  color: #6b7280;
}

/* Role Badge */
.role-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.role-badge.bg-purple-100 {
  background: linear-gradient(135deg, rgba(168, 85, 247, 0.15) 0%, rgba(147, 51, 234, 0.15) 100%);
  color: #7c3aed;
  border: 1px solid rgba(168, 85, 247, 0.2);
}

.role-badge.bg-blue-100 {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.15) 100%);
  color: #1d4ed8;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.role-badge.bg-green-100 {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.15) 0%, rgba(22, 163, 74, 0.15) 100%);
  color: #059669;
  border: 1px solid rgba(34, 197, 94, 0.2);
}

.role-badge.bg-gray-100 {
  background: linear-gradient(135deg, rgba(156, 163, 175, 0.15) 0%, rgba(107, 114, 128, 0.15) 100%);
  color: #374151;
  border: 1px solid rgba(156, 163, 175, 0.2);
}

/* Table Text */
.branch-name,
.phone-number {
  font-size: 0.875rem;
  color: #374151;
}

/* Status Toggle */
.status-toggle {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
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

.status-toggle:hover {
  opacity: 0.8;
  transform: scale(0.98);
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.75rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-assign {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.action-assign:hover {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.2) 100%);
  transform: translateY(-1px);
}

.action-edit {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
  color: #1d4ed8;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.action-edit:hover {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.2) 100%);
  transform: translateY(-1px);
}

.action-delete {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.action-delete:hover {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.2) 100%);
  transform: translateY(-1px);
}

/* Modal */
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
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
.form-select {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: rgba(249, 250, 251, 0.5);
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  background: white;
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
@media (max-width: 1024px) {
  .users-container {
    padding: 1rem;
  }
  
  .users-content {
    padding: 1.5rem;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: stretch;
  }
  
  .table-wrapper {
    font-size: 0.875rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .modal-actions {
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .table-th,
  .table-cell {
    padding: 0.75rem;
  }
  
  .user-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .user-avatar {
    width: 2rem;
    height: 2rem;
  }
  
  .avatar-icon {
    width: 1rem;
    height: 1rem;
  }
}

/* Branch Assignments Modal */
.branch-modal-container {
  background: white;
  border-radius: 1rem;
  width: 95vw;
  max-width: 1200px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
}

.branch-modal-content {
  max-height: calc(90vh - 80px);
  overflow-y: auto;
  padding: 0;
}

.close-btn {
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  border: none;
  background: transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

@media (max-width: 768px) {
  .header-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  
  .branch-assign-btn,
  .add-user-btn {
    justify-content: center;
  }
  
  .branch-modal-container {
    width: 100vw;
    max-width: 100vw;
    margin: 0;
    border-radius: 0;
  }
}
</style>