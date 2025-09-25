<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
        <p class="text-gray-600 mt-1">Manage system users and their roles</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add User
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
            placeholder="Search by name or email..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select 
            v-model="filters.role"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="branch_manager">Branch Manager</option>
            <option value="partner">Partner</option>
            <option value="customer">Customer</option>
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
            @click="fetchUsers"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading users...</p>
      </div>

      <div v-else-if="users.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
        </svg>
        <p>No users found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                  <div class="text-sm text-gray-500">{{ user.email }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getRoleClass(user.role)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatRole(user.role) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ user.branch?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ user.phone }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button 
                  @click="toggleUserStatus(user)"
                  :class="user.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer hover:opacity-75"
                >
                  {{ user.active ? 'Active' : 'Inactive' }}
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="openEditModal(user)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </button>
                  <button 
                    @click="confirmDelete(user)"
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
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-gray-900">
            {{ isEditing ? 'Edit User' : 'Create New User' }}
          </h3>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
              <input 
                v-model="form.name"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <input 
                v-model="form.email"
                type="email" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
              <input 
                v-model="form.phone"
                type="tel" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
              <select 
                v-model="form.role"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="branch_manager">Branch Manager</option>
                <option value="partner">Partner</option>
                <option value="customer">Customer</option>
              </select>
            </div>
          </div>

          <div v-if="form.role === 'branch_manager'" class="grid grid-cols-1 gap-4">
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

          <div v-if="!isEditing" class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
              <input 
                v-model="form.password"
                type="password" 
                :required="!isEditing"
                minlength="8"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <div v-if="isEditing" class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
              <input 
                v-model="form.password"
                type="password" 
                minlength="8"
                placeholder="Leave blank to keep current password"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue'
import axios from 'axios'

export default {
  name: 'Users',
  setup() {
    const users = ref([])
    const branches = ref([])
    const loading = ref(false)
    const showModal = ref(false)
    const isEditing = ref(false)
    const submitting = ref(false)
    const currentUser = ref(null)

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
      password: ''
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
        alert('Failed to fetch users')
      } finally {
        loading.value = false
      }
    }

    const fetchBranches = async () => {
      try {
        const response = await axios.get('/branches')
        if (response.data.success) {
          branches.value = response.data.data
        }
      } catch (error) {
        console.error('Error fetching branches:', error)
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
      form.password = ''
      
      showModal.value = true
    }

    const closeModal = () => {
      showModal.value = false
      resetForm()
      currentUser.value = null
    }

    const resetForm = () => {
      form.name = ''
      form.email = ''
      form.phone = ''
      form.role = ''
      form.branch_id = ''
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
          alert(isEditing.value ? 'User updated successfully!' : 'User created successfully!')
        }
      } catch (error) {
        console.error('Error saving user:', error)
        alert(error.response?.data?.message || 'Failed to save user')
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
          alert('User status updated successfully!')
        }
      } catch (error) {
        console.error('Error updating user status:', error)
        alert('Failed to update user status')
      }
    }

    const confirmDelete = (user) => {
      if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
        deleteUser(user)
      }
    }

    const deleteUser = async (user) => {
      try {
        const response = await axios.delete(`/users/${user.id}`)
        
        if (response.data.success) {
          fetchUsers()
          alert('User deleted successfully!')
        }
      } catch (error) {
        console.error('Error deleting user:', error)
        alert(error.response?.data?.message || 'Failed to delete user')
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

    onMounted(() => {
      fetchUsers()
      fetchBranches()
    })

    return {
      users,
      branches,
      loading,
      showModal,
      isEditing,
      submitting,
      filters,
      form,
      fetchUsers,
      openCreateModal,
      openEditModal,
      closeModal,
      submitForm,
      toggleUserStatus,
      confirmDelete,
      getRoleClass,
      formatRole
    }
  }
}
</script>