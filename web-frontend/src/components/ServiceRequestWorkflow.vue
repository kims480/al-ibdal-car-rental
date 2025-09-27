<template>
  <div class="workflow-container">
    <!-- Workflow Diagram Section -->
    <div class="workflow-diagram">
      <div class="diagram-header">
        <h3 class="diagram-title">Service Request Workflow</h3>
        <p class="diagram-subtitle">Track progress through each stage</p>
      </div>
      
      <div class="stages-container">
        <div 
          v-for="(stage, index) in workflowStages" 
          :key="stage.id"
          class="stage-wrapper"
        >
          <!-- Stage Node -->
          <div 
            :class="[
              'stage-node',
              getStageClass(stage, index)
            ]"
            @click="selectStage(stage)"
          >
            <div class="stage-icon">
              <component :is="getStageIcon(stage.key)" class="w-5 h-5" />
            </div>
            <div class="stage-info">
              <div class="stage-name">{{ stage.name }}</div>
              <div class="stage-description">{{ stage.description }}</div>
              <div v-if="stage.assigned_to" class="stage-assignee">
                <UserIcon class="w-3 h-3" />
                {{ stage.assigned_to.name }}
              </div>
            </div>
            <div class="stage-status">
              <div :class="['status-indicator', getStatusIndicatorClass(stage, index)]"></div>
            </div>
          </div>
          
          <!-- Connector Arrow -->
          <div 
            v-if="index < workflowStages.length - 1" 
            :class="['stage-connector', getConnectorClass(stage, index)]"
          >
            <ChevronRightIcon class="w-4 h-4" />
          </div>
        </div>
      </div>
    </div>

    <!-- Service Request Details Section -->
    <div class="sr-details-section">
      <div class="details-header">
        <h3 class="details-title">Service Request Details</h3>
        <div class="sr-status-badge" :class="getStatusBadgeClass(serviceRequest.status)">
          {{ formatStatus(serviceRequest.status) }}
        </div>
      </div>
      
      <div class="details-grid">
        <!-- Basic Information -->
        <div class="detail-card">
          <h4 class="card-title">Basic Information</h4>
          <div class="detail-items">
            <div class="detail-item">
              <span class="detail-label">Request ID:</span>
              <span class="detail-value">{{ serviceRequest.id || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Type:</span>
              <span class="detail-value">{{ serviceRequest.service_type || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Priority:</span>
              <span class="detail-value priority-badge" :class="getPriorityClass(serviceRequest.priority)">
                {{ formatPriority(serviceRequest.priority) }}
              </span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Created:</span>
              <span class="detail-value">{{ formatDate(serviceRequest.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="detail-card">
          <h4 class="card-title">Customer Information</h4>
          <div class="detail-items">
            <div class="detail-item">
              <span class="detail-label">Name:</span>
              <span class="detail-value">{{ serviceRequest.customer?.name || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Phone:</span>
              <span class="detail-value">{{ serviceRequest.customer?.phone || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Email:</span>
              <span class="detail-value">{{ serviceRequest.customer?.email || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Vehicle Information -->
        <div class="detail-card">
          <h4 class="card-title">Vehicle Information</h4>
          <div class="detail-items">
            <div class="detail-item">
              <span class="detail-label">Make & Model:</span>
              <span class="detail-value">{{ serviceRequest.car?.make }} {{ serviceRequest.car?.model }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">License Plate:</span>
              <span class="detail-value">{{ serviceRequest.car?.license_plate || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Year:</span>
              <span class="detail-value">{{ serviceRequest.car?.year || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Service Details -->
        <div class="detail-card full-width">
          <h4 class="card-title">Service Details</h4>
          <div class="detail-items">
            <div class="detail-item full-width">
              <span class="detail-label">Description:</span>
              <span class="detail-value">{{ serviceRequest.description || 'N/A' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Estimated Cost:</span>
              <span class="detail-value cost-value">OMR {{ serviceRequest.estimated_cost || '0.00' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Actual Cost:</span>
              <span class="detail-value cost-value">OMR {{ serviceRequest.actual_cost || '0.00' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Workflow History Section -->
    <div class="history-section">
      <div class="history-header">
        <h3 class="history-title">Workflow History</h3>
        <p class="history-subtitle">Track all actions and comments</p>
      </div>
      
      <div class="history-timeline">
        <div 
          v-for="(entry, index) in workflowHistory" 
          :key="entry.id || index"
          class="timeline-entry"
        >
          <div class="timeline-marker">
            <div :class="['marker-dot', getHistoryMarkerClass(entry.action)]"></div>
          </div>
          <div class="timeline-content">
            <div class="timeline-header">
              <span class="timeline-action">{{ formatAction(entry.action) }}</span>
              <span class="timeline-timestamp">{{ formatDateTime(entry.created_at) }}</span>
            </div>
            <div class="timeline-details">
              <div class="timeline-user">
                <UserIcon class="w-4 h-4" />
                {{ entry.user?.name || 'System' }} ({{ entry.user?.role || 'System' }})
              </div>
              <div v-if="entry.from_stage || entry.to_stage" class="timeline-stages">
                <span v-if="entry.from_stage" class="stage-from">{{ entry.from_stage }}</span>
                <ChevronRightIcon v-if="entry.from_stage && entry.to_stage" class="w-3 h-3 mx-1" />
                <span v-if="entry.to_stage" class="stage-to">{{ entry.to_stage }}</span>
              </div>
              <div v-if="entry.comment" class="timeline-comment">
                <ChatBubbleLeftIcon class="w-4 h-4" />
                {{ entry.comment }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Stage Actions (for authorized users) -->
    <div v-if="canProcessCurrentStage" class="stage-actions-section">
      <div class="actions-header">
        <h3 class="actions-title">Stage Actions</h3>
        <p class="actions-subtitle">Process current stage: {{ getCurrentStageName() }}</p>
      </div>
      
      <div class="actions-form">
        <div class="form-group">
          <label class="form-label">Remarks/Comments</label>
          <textarea 
            v-model="stageAction.remarks"
            class="form-textarea"
            rows="3"
            placeholder="Add your comments or remarks..."
          ></textarea>
        </div>
        
        <div class="action-buttons">
          <button 
            @click="processStage('approve')"
            :disabled="processing"
            class="action-btn approve-btn"
          >
            <CheckIcon class="w-4 h-4" />
            {{ processing && actionType === 'approve' ? 'Approving...' : 'Approve' }}
          </button>
          <button 
            @click="processStage('reject')"
            :disabled="processing"
            class="action-btn reject-btn"
          >
            <XMarkIcon class="w-4 h-4" />
            {{ processing && actionType === 'reject' ? 'Rejecting...' : 'Reject' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { toast } from '../composables/useToast'
import { 
  WrenchScrewdriverIcon,
  ClockIcon,
  CogIcon,
  CheckCircleIcon,
  XCircleIcon,
  UserIcon,
  ChevronRightIcon,
  ChatBubbleLeftIcon,
  CheckIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  serviceRequestId: {
    type: [String, Number],
    required: true
  }
})

// Reactive data
const serviceRequest = ref({})
const workflowStages = ref([])
const workflowHistory = ref([])
const currentStage = ref(null)
const stageAction = ref({
  remarks: '',
  action: ''
})
const processing = ref(false)
const actionType = ref('')

// Computed properties
const canProcessCurrentStage = computed(() => {
  // Check if current user can process the current stage
  return currentStage.value && currentStage.value.can_process
})

// Methods
const fetchWorkflowData = async () => {
  try {
    // Fetch service request details
    const srResponse = await axios.get(`/service-requests/${props.serviceRequestId}`)
    serviceRequest.value = srResponse.data.data || srResponse.data
    
    // Fetch workflow stages
    const stagesResponse = await axios.get(`/service-requests/${props.serviceRequestId}/workflow`)
    workflowStages.value = stagesResponse.data.data || stagesResponse.data
    
    // Fetch workflow history
    const historyResponse = await axios.get(`/service-requests/${props.serviceRequestId}/history`)
    workflowHistory.value = historyResponse.data.data || historyResponse.data
    
    // Find current stage
    currentStage.value = workflowStages.value.find(stage => stage.status === 'current')
    
  } catch (error) {
    console.error('Error fetching workflow data:', error)
  }
}

const getStageClass = (stage, index) => {
  if (stage.status === 'completed') return 'stage-completed'
  if (stage.status === 'current') return 'stage-current'
  return 'stage-pending'
}

const getStatusIndicatorClass = (stage, index) => {
  if (stage.status === 'completed') return 'status-completed'
  if (stage.status === 'current') return 'status-current'
  return 'status-pending'
}

const getConnectorClass = (stage, index) => {
  if (stage.status === 'completed') return 'connector-completed'
  return 'connector-pending'
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

const getStatusBadgeClass = (status) => {
  const classMap = {
    'pending': 'status-pending-badge',
    'in_progress': 'status-progress-badge',
    'completed': 'status-completed-badge',
    'cancelled': 'status-cancelled-badge'
  }
  return classMap[status] || 'status-default-badge'
}

const getPriorityClass = (priority) => {
  const classMap = {
    'high': 'priority-high',
    'medium': 'priority-medium',
    'low': 'priority-low'
  }
  return classMap[priority] || 'priority-medium'
}

const getHistoryMarkerClass = (action) => {
  const classMap = {
    'created': 'marker-created',
    'approved': 'marker-approved',
    'rejected': 'marker-rejected',
    'assigned': 'marker-assigned',
    'commented': 'marker-commented'
  }
  return classMap[action] || 'marker-default'
}

const formatStatus = (status) => {
  return status ? status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'
}

const formatPriority = (priority) => {
  return priority ? priority.charAt(0).toUpperCase() + priority.slice(1) : 'Medium'
}

const formatAction = (action) => {
  return action ? action.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Action'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getCurrentStageName = () => {
  return currentStage.value ? currentStage.value.name : 'Unknown Stage'
}

const selectStage = (stage) => {
  // Handle stage selection if needed
  console.log('Selected stage:', stage)
}

const processStage = async (action) => {
  if (!stageAction.value.remarks.trim()) {
    toast.warning('Please add remarks before processing the stage')
    return
  }
  
  processing.value = true
  actionType.value = action
  
  try {
    const response = await axios.post(`/service-requests/${props.serviceRequestId}/workflow/process`, {
      action: action,
      remarks: stageAction.value.remarks,
      stage_id: currentStage.value.id
    })
    
    // Refresh workflow data
    await fetchWorkflowData()
    
    // Clear form
    stageAction.value.remarks = ''
    
    // Show success message
    toast.success(`Stage ${action}d successfully!`)
    
  } catch (error) {
    console.error('Error processing stage:', error)
    toast.error('Failed to process stage. Please try again.')
  } finally {
    processing.value = false
    actionType.value = ''
  }
}

// Lifecycle
onMounted(() => {
  fetchWorkflowData()
})
</script>

<style scoped>
/* Workflow Container */
.workflow-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  padding: 1rem;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

/* Workflow Diagram */
.workflow-diagram {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.diagram-header {
  margin-bottom: 2rem;
  text-align: center;
}

.diagram-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.diagram-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.stages-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  overflow-x: auto;
  padding: 1rem 0;
}

.stage-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Stage Nodes */
.stage-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem;
  border-radius: 1rem;
  border: 2px solid;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 150px;
  position: relative;
}

.stage-completed {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  border-color: #10b981;
  color: #065f46;
}

.stage-current {
  background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
  border-color: #f97316;
  color: #9a3412;
  box-shadow: 0 0 20px rgba(249, 115, 22, 0.3);
}

.stage-pending {
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  border-color: #d1d5db;
  color: #6b7280;
}

.stage-icon {
  margin-bottom: 0.5rem;
}

.stage-info {
  text-align: center;
}

.stage-name {
  font-weight: 600;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.stage-description {
  font-size: 0.75rem;
  opacity: 0.8;
  margin-bottom: 0.5rem;
}

.stage-assignee {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  opacity: 0.9;
}

.stage-status {
  position: absolute;
  top: -8px;
  right: -8px;
}

.status-indicator {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid white;
}

.status-completed {
  background: #10b981;
}

.status-current {
  background: #f97316;
}

.status-pending {
  background: #d1d5db;
}

/* Stage Connectors */
.stage-connector {
  color: #d1d5db;
  transition: color 0.3s ease;
}

.connector-completed {
  color: #10b981;
}

/* Service Request Details */
.sr-details-section {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.details-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.details-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.sr-status-badge {
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 600;
}

.status-pending-badge {
  background: #fef3c7;
  color: #92400e;
}

.status-progress-badge {
  background: #dbeafe;
  color: #1e40af;
}

.status-completed-badge {
  background: #d1fae5;
  color: #065f46;
}

.status-cancelled-badge {
  background: #fee2e2;
  color: #991b1b;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.detail-card {
  background: #f8fafc;
  border-radius: 0.75rem;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
}

.full-width {
  grid-column: 1 / -1;
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
}

.detail-items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.detail-item.full-width {
  flex-direction: column;
  align-items: flex-start;
}

.detail-label {
  font-weight: 500;
  color: #6b7280;
  min-width: fit-content;
}

.detail-value {
  color: #1f2937;
  text-align: right;
  word-break: break-word;
}

.detail-item.full-width .detail-value {
  text-align: left;
  margin-top: 0.25rem;
}

.cost-value {
  font-weight: 600;
  color: #059669;
}

.priority-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.priority-high {
  background: #fee2e2;
  color: #991b1b;
}

.priority-medium {
  background: #fef3c7;
  color: #92400e;
}

.priority-low {
  background: #d1fae5;
  color: #065f46;
}

/* Workflow History */
.history-section {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.history-header {
  margin-bottom: 1.5rem;
  text-align: center;
}

.history-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.history-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.history-timeline {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
}

.history-timeline::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e5e7eb;
}

.timeline-entry {
  display: flex;
  gap: 1rem;
  position: relative;
}

.timeline-marker {
  position: relative;
  z-index: 1;
}

.marker-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 0 0 2px #e5e7eb;
}

.marker-created {
  background: #3b82f6;
}

.marker-approved {
  background: #10b981;
}

.marker-rejected {
  background: #ef4444;
}

.marker-assigned {
  background: #8b5cf6;
}

.marker-commented {
  background: #f59e0b;
}

.marker-default {
  background: #6b7280;
}

.timeline-content {
  flex: 1;
  background: #f8fafc;
  border-radius: 0.75rem;
  padding: 1rem;
  border: 1px solid #e2e8f0;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.timeline-action {
  font-weight: 600;
  color: #1f2937;
}

.timeline-timestamp {
  font-size: 0.875rem;
  color: #6b7280;
}

.timeline-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.timeline-user {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.timeline-stages {
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  color: #374151;
}

.stage-from {
  color: #ef4444;
  font-weight: 500;
}

.stage-to {
  color: #10b981;
  font-weight: 500;
}

.timeline-comment {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  color: #374151;
  background: white;
  padding: 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

/* Stage Actions */
.stage-actions-section {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  border: 2px solid #f97316;
}

.actions-header {
  margin-bottom: 1.5rem;
  text-align: center;
}

.actions-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.actions-subtitle {
  color: #f97316;
  font-size: 0.875rem;
  font-weight: 600;
}

.actions-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-weight: 600;
  color: #374151;
}

.form-textarea {
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  resize: vertical;
  font-family: inherit;
}

.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.approve-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.approve-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.reject-btn {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.reject-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
  .workflow-container {
    gap: 1rem;
    padding: 0.5rem;
  }
  
  .stages-container {
    flex-direction: column;
    align-items: stretch;
  }
  
  .stage-wrapper {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .stage-connector {
    transform: rotate(90deg);
    align-self: center;
  }
  
  .details-grid {
    grid-template-columns: 1fr;
  }
  
  .details-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .detail-item {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .detail-value {
    text-align: left;
  }
}
</style>