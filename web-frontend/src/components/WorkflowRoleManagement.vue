<template>
  <div class="role-management-container">
    <!-- Header Section -->
    <div class="management-header">
      <div>
        <h3 class="management-title">Workflow Role Management</h3>
        <p class="management-subtitle">Configure roles and users for each workflow stage</p>
      </div>
      <button 
        @click="openCreateRoleModal"
        class="create-role-btn"
      >
        <PlusIcon class="w-4 h-4" />
        Add Role Assignment
      </button>
    </div>

    <!-- Workflow Stages Overview -->
    <div class="stages-overview">
      <h4 class="overview-title">Workflow Stages Overview</h4>
      <div class="stages-grid">
        <div 
          v-for="stage in workflowStages" 
          :key="stage.id"
          class="stage-card"
          @click="selectStage(stage)"
          :class="{ 'stage-selected': selectedStage?.id === stage.id }"
        >
          <div class="stage-card-header">
            <div class="stage-icon">
              <component :is="getStageIcon(stage.key)" class="w-5 h-5" />
            </div>
            <div class="stage-info">
              <h5 class="stage-name">{{ stage.name }}</h5>
              <p class="stage-description">{{ stage.description }}</p>
            </div>
          </div>
          
          <div class="stage-assignments">
            <div class="assignments-header">
              <UsersIcon class="w-4 h-4" />
              <span>{{ stage.role_assignments?.length || 0 }} Assignments</span>
            </div>
            <div class="assignment-list">
              <div 
                v-for="assignment in (stage.role_assignments || []).slice(0, 3)" 
                :key="assignment.id"
                class="assignment-item"
              >
                <div class="user-avatar">
                  {{ assignment.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                </div>
                <div class="assignment-details">
                  <span class="user-name">{{ assignment.user?.name || 'Unknown' }}</span>
                  <span class="user-role">{{ assignment.role?.name || 'No Role' }}</span>
                </div>
              </div>
              <div v-if="(stage.role_assignments?.length || 0) > 3" class="assignment-more">
                +{{ (stage.role_assignments?.length || 0) - 3 }} more
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Selected Stage Details -->
    <div v-if="selectedStage" class="stage-details">
      <div class="details-header">
        <div class="stage-title-section">
          <div class="stage-title-icon">
            <component :is="getStageIcon(selectedStage.key)" class="w-6 h-6" />
          </div>
          <div>
            <h4 class="selected-stage-title">{{ selectedStage.name }}</h4>
            <p class="selected-stage-description">{{ selectedStage.description }}</p>
          </div>
        </div>
        <button 
          @click="openAssignRoleModal(selectedStage)"
          class="assign-role-btn"
        >
          <PlusIcon class="w-4 h-4" />
          Assign Role
        </button>
      </div>

      <!-- Role Assignments Table -->
      <div class="assignments-table-container">
        <div v-if="(selectedStage.role_assignments || []).length === 0" class="empty-assignments">
          <div class="empty-icon">
            <UsersIcon class="w-8 h-8" />
          </div>
          <h5 class="empty-title">No role assignments</h5>
          <p class="empty-description">Assign roles and users to this workflow stage</p>
          <button 
            @click="openAssignRoleModal(selectedStage)"
            class="empty-action-btn"
          >
            <PlusIcon class="w-4 h-4" />
            Assign First Role
          </button>
        </div>

        <div v-else class="assignments-table">
          <table class="role-table">
            <thead>
              <tr>
                <th class="table-header">User</th>
                <th class="table-header">Role</th>
                <th class="table-header">Permissions</th>
                <th class="table-header">Assigned Date</th>
                <th class="table-header">Status</th>
                <th class="table-header text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="assignment in selectedStage.role_assignments" 
                :key="assignment.id"
                class="table-row"
              >
                <td class="table-cell">
                  <div class="user-cell">
                    <div class="user-avatar-large">
                      {{ assignment.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                    </div>
                    <div class="user-details">
                      <span class="user-name-large">{{ assignment.user?.name || 'Unknown User' }}</span>
                      <span class="user-email">{{ assignment.user?.email || 'No email' }}</span>
                    </div>
                  </div>
                </td>
                <td class="table-cell">
                  <span class="role-badge" :class="getRoleBadgeClass(assignment.role?.key)">
                    {{ assignment.role?.name || 'No Role' }}
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
                  <span class="assigned-date">{{ formatDate(assignment.created_at) }}</span>
                </td>
                <td class="table-cell">
                  <span 
                    class="status-indicator"
                    :class="assignment.is_active ? 'status-active' : 'status-inactive'"
                  >
                    {{ assignment.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="table-cell text-center">
                  <div class="assignment-actions">
                    <button 
                      @click="editAssignment(assignment)"
                      class="action-btn edit"
                      title="Edit Assignment"
                    >
                      <PencilSquareIcon class="w-4 h-4" />
                    </button>
                    <button 
                      @click="toggleAssignmentStatus(assignment)"
                      :class="[
                        'action-btn',
                        assignment.is_active ? 'deactivate' : 'activate'
                      ]"
                      :title="assignment.is_active ? 'Deactivate' : 'Activate'"
                    >
                      <component 
                        :is="assignment.is_active ? EyeSlashIcon : EyeIcon" 
                        class="w-4 h-4" 
                      />
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
      </div>
    </div>

    <!-- Role Assignment Modal -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-gray-900">
            {{ isEditingAssignment ? 'Edit Role Assignment' : 'Assign Role to Workflow Stage' }}
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Stage: {{ selectedStage?.name || 'Unknown Stage' }}
          </p>
        </div>

        <form @submit.prevent="submitRoleAssignment" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">User *</label>
              <select 
                v-model="roleForm.user_id" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
              <select 
                v-model="roleForm.role_id" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Role</option>
                <option 
                  v-for="role in availableRoles" 
                  :key="role.id" 
                  :value="role.id"
                >
                  {{ role.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
            <div class="permission-checkboxes">
              <label 
                v-for="permission in availablePermissions" 
                :key="permission.key"
                class="permission-checkbox"
              >
                <input 
                  type="checkbox" 
                  :value="permission.key"
                  v-model="roleForm.permissions"
                  class="permission-input"
                />
                <span class="permission-label">{{ permission.name }}</span>
                <span class="permission-description">{{ permission.description }}</span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select 
                v-model="roleForm.priority" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="1">Low Priority</option>
                <option value="2">Normal Priority</option>
                <option value="3">High Priority</option>
              </select>
            </div>
            <div class="flex items-center">
              <label class="flex items-center space-x-2">
                <input 
                  type="checkbox" 
                  v-model="roleForm.is_active"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <span class="text-sm font-medium text-gray-700">Active Assignment</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea 
              v-model="roleForm.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Additional notes about this role assignment..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              type="button"
              @click="closeRoleModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="submittingRole"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors disabled:opacity-50"
            >
              {{ submittingRole ? 'Saving...' : (isEditingAssignment ? 'Update Assignment' : 'Assign Role') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { 
  PlusIcon,
  UsersIcon,
  PencilSquareIcon,
  TrashIcon,
  EyeIcon,
  EyeSlashIcon,
  WrenchScrewdriverIcon,
  ClockIcon,
  CogIcon,
  CheckCircleIcon,
  XCircleIcon,
  UserIcon
} from '@heroicons/vue/24/outline'
import axios from 'axios'

// Props
const props = defineProps({
  workflowId: {
    type: [String, Number],
    default: null
  }
})

// Reactive data
const workflowStages = ref([])
const selectedStage = ref(null)
const availableUsers = ref([])
const availableRoles = ref([])
const showRoleModal = ref(false)
const isEditingAssignment = ref(false)
const submittingRole = ref(false)
const currentAssignment = ref(null)

const availablePermissions = ref([
  {
    key: 'approve',
    name: 'Approve',
    description: 'Can approve stage requests'
  },
  {
    key: 'reject',
    name: 'Reject',
    description: 'Can reject stage requests'
  },
  {
    key: 'assign',
    name: 'Assign',
    description: 'Can assign requests to users'
  },
  {
    key: 'comment',
    name: 'Comment',
    description: 'Can add comments and remarks'
  },
  {
    key: 'view_all',
    name: 'View All',
    description: 'Can view all requests in this stage'
  },
  {
    key: 'edit',
    name: 'Edit',
    description: 'Can edit request details'
  }
])

const roleForm = reactive({
  user_id: '',
  role_id: '',
  stage_id: '',
  permissions: [],
  priority: 2,
  is_active: true,
  notes: ''
})

// Methods
const fetchWorkflowStages = async () => {
  try {
    const response = await axios.get('/workflow-stages')
    workflowStages.value = response.data.data || response.data
    
    // Fetch role assignments for each stage
    for (const stage of workflowStages.value) {
      await fetchStageAssignments(stage.id)
    }
  } catch (error) {
    console.error('Error fetching workflow stages:', error)
  }
}

const fetchStageAssignments = async (stageId) => {
  try {
    const response = await axios.get(`/workflow-stages/${stageId}/role-assignments`)
    const stage = workflowStages.value.find(s => s.id === stageId)
    if (stage) {
      stage.role_assignments = response.data.data || response.data
    }
  } catch (error) {
    console.error(`Error fetching assignments for stage ${stageId}:`, error)
  }
}

const fetchUsers = async () => {
  try {
    const response = await axios.get('/users', { params: { active: true } })
    availableUsers.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const fetchRoles = async () => {
  try {
    const response = await axios.get('/roles', { params: { active: true } })
    availableRoles.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching roles:', error)
  }
}

const selectStage = (stage) => {
  selectedStage.value = stage
}

const getStageIcon = (stageKey) => {
  const iconMap = {
    'request_received': WrenchScrewdriverIcon,
    'initial_review': ClockIcon,
    'assigned': UserIcon,
    'in_progress': CogIcon,
    'quality_check': CheckCircleIcon,
    'completed': CheckCircleIcon,
    'cancelled': XCircleIcon
  }
  return iconMap[stageKey] || WrenchScrewdriverIcon
}

const getRoleBadgeClass = (roleKey) => {
  const classMap = {
    'admin': 'role-admin',
    'manager': 'role-manager',
    'technician': 'role-technician',
    'reviewer': 'role-reviewer'
  }
  return classMap[roleKey] || 'role-default'
}

const getPermissions = (assignment) => {
  return assignment.permissions || ['View', 'Comment']
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const openCreateRoleModal = () => {
  if (!selectedStage.value) {
    alert('Please select a workflow stage first')
    return
  }
  openAssignRoleModal(selectedStage.value)
}

const openAssignRoleModal = async (stage) => {
  selectedStage.value = stage
  isEditingAssignment.value = false
  currentAssignment.value = null
  resetRoleForm()
  roleForm.stage_id = stage.id
  
  await fetchUsers()
  await fetchRoles()
  showRoleModal.value = true
}

const editAssignment = async (assignment) => {
  isEditingAssignment.value = true
  currentAssignment.value = assignment
  
  roleForm.user_id = assignment.user_id
  roleForm.role_id = assignment.role_id
  roleForm.stage_id = assignment.stage_id
  roleForm.permissions = assignment.permissions || []
  roleForm.priority = assignment.priority || 2
  roleForm.is_active = assignment.is_active !== false
  roleForm.notes = assignment.notes || ''
  
  await fetchUsers()
  await fetchRoles()
  showRoleModal.value = true
}

const closeRoleModal = () => {
  showRoleModal.value = false
  resetRoleForm()
  isEditingAssignment.value = false
  currentAssignment.value = null
}

const resetRoleForm = () => {
  roleForm.user_id = ''
  roleForm.role_id = ''
  roleForm.stage_id = ''
  roleForm.permissions = []
  roleForm.priority = 2
  roleForm.is_active = true
  roleForm.notes = ''
}

const submitRoleAssignment = async () => {
  submittingRole.value = true
  try {
    let response
    
    const data = { ...roleForm }
    
    if (isEditingAssignment.value && currentAssignment.value) {
      response = await axios.put(`/workflow-role-assignments/${currentAssignment.value.id}`, data)
    } else {
      response = await axios.post('/workflow-role-assignments', data)
    }
    
    if (response.data.success) {
      closeRoleModal()
      await fetchStageAssignments(selectedStage.value.id)
      alert(isEditingAssignment.value ? 'Role assignment updated successfully!' : 'Role assigned successfully!')
    }
  } catch (error) {
    console.error('Error saving role assignment:', error)
    alert(error.response?.data?.message || 'Failed to save role assignment')
  } finally {
    submittingRole.value = false
  }
}

const toggleAssignmentStatus = async (assignment) => {
  try {
    const response = await axios.patch(`/workflow-role-assignments/${assignment.id}/toggle-status`)
    
    if (response.data.success) {
      await fetchStageAssignments(selectedStage.value.id)
      alert(`Assignment ${assignment.is_active ? 'deactivated' : 'activated'} successfully!`)
    }
  } catch (error) {
    console.error('Error toggling assignment status:', error)
    alert(error.response?.data?.message || 'Failed to update assignment status')
  }
}

const confirmDeleteAssignment = (assignment) => {
  if (confirm(`Are you sure you want to remove the role assignment for ${assignment.user?.name}? This action cannot be undone.`)) {
    deleteAssignment(assignment)
  }
}

const deleteAssignment = async (assignment) => {
  try {
    const response = await axios.delete(`/workflow-role-assignments/${assignment.id}`)
    
    if (response.data.success) {
      await fetchStageAssignments(selectedStage.value.id)
      alert('Role assignment removed successfully!')
    }
  } catch (error) {
    console.error('Error deleting assignment:', error)
    alert(error.response?.data?.message || 'Failed to remove assignment')
  }
}

// Lifecycle
onMounted(() => {
  fetchWorkflowStages()
})
</script>

<style scoped>
/* Role Management Container */
.role-management-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

/* Management Header */
.management-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: white;
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.management-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.management-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.create-role-btn {
  display: flex;
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

.create-role-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

/* Stages Overview */
.stages-overview {
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

.stages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

/* Stage Cards */
.stage-card {
  border: 2px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 1.25rem;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f8fafc;
}

.stage-card:hover {
  border-color: #6366f1;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
}

.stage-selected {
  border-color: #6366f1;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
}

.stage-card-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.stage-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stage-info {
  flex: 1;
}

.stage-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.stage-description {
  font-size: 0.875rem;
  color: #6b7280;
}

.stage-assignments {
  border-top: 1px solid #e5e7eb;
  padding-top: 1rem;
}

.assignments-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
}

.assignment-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.assignment-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
}

.assignment-details {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.user-name {
  font-size: 0.75rem;
  font-weight: 500;
  color: #1f2937;
}

.user-role {
  font-size: 0.625rem;
  color: #6b7280;
}

.assignment-more {
  font-size: 0.75rem;
  color: #6b7280;
  font-style: italic;
}

/* Stage Details */
.stage-details {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.details-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.stage-title-section {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.stage-title-icon {
  width: 48px;
  height: 48px;
  border-radius: 0.75rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.selected-stage-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.selected-stage-description {
  color: #6b7280;
  font-size: 0.875rem;
}

.assign-role-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.assign-role-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

/* Assignments Table */
.assignments-table-container {
  background: #f8fafc;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.empty-assignments {
  padding: 2rem;
  text-align: center;
}

.empty-icon {
  color: #d1d5db;
  margin: 0 auto 1rem;
  display: flex;
  justify-content: center;
}

.empty-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #6b7280;
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

.empty-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
}

.role-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  border-bottom: 1px solid #e5e7eb;
}

.table-row {
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
}

.table-row:hover {
  background: rgba(99, 102, 241, 0.02);
}

.table-cell {
  padding: 1rem;
  vertical-align: middle;
}

/* User Cell */
.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar-large {
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

.user-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name-large {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.875rem;
}

.user-email {
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

.role-admin {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.1) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.role-manager {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(165, 180, 252, 0.1) 100%);
  color: #6366f1;
  border: 1px solid rgba(99, 102, 241, 0.3);
}

.role-technician {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(134, 239, 172, 0.1) 100%);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.role-reviewer {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.role-default {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

/* Permissions List */
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

/* Status Indicators */
.status-indicator {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-active {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(134, 239, 172, 0.1) 100%);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-inactive {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

.assigned-date {
  color: #6b7280;
  font-size: 0.875rem;
}

/* Assignment Actions */
.assignment-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
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

.action-btn.edit:hover {
  background: rgba(168, 85, 247, 0.2);
  transform: scale(1.1);
}

.action-btn.activate {
  background: rgba(34, 197, 94, 0.1);
  border-color: rgba(34, 197, 94, 0.3);
  color: #22c55e;
}

.action-btn.activate:hover {
  background: rgba(34, 197, 94, 0.2);
  transform: scale(1.1);
}

.action-btn.deactivate {
  background: rgba(245, 158, 11, 0.1);
  border-color: rgba(245, 158, 11, 0.3);
  color: #f59e0b;
}

.action-btn.deactivate:hover {
  background: rgba(245, 158, 11, 0.2);
  transform: scale(1.1);
}

.action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

.action-btn.delete:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: scale(1.1);
}

/* Permission Checkboxes */
.permission-checkboxes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.permission-checkbox {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.permission-checkbox:hover {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.02);
}

.permission-checkbox:has(.permission-input:checked) {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

.permission-input {
  width: 1rem;
  height: 1rem;
  border-radius: 0.25rem;
  border: 2px solid #d1d5db;
  margin-bottom: 0.5rem;
}

.permission-input:checked {
  background-color: #6366f1;
  border-color: #6366f1;
}

.permission-label {
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
}

.permission-description {
  color: #6b7280;
  font-size: 0.75rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .management-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .create-role-btn {
    width: 100%;
    justify-content: center;
  }
  
  .stages-grid {
    grid-template-columns: 1fr;
  }
  
  .stage-card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .details-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .assign-role-btn {
    width: 100%;
    justify-content: center;
  }
  
  .table-header,
  .table-cell {
    padding: 0.75rem 0.5rem;
  }
  
  .permission-checkboxes {
    grid-template-columns: 1fr;
  }
  
  .assignment-actions {
    flex-direction: column;
    gap: 0.25rem;
  }
}
</style>