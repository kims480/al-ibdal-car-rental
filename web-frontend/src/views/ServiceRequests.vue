<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-2 sm:p-3">
    <div class="max-w-7xl mx-auto">
      <!-- Loading skeleton -->
      <CRUDTableSkeleton v-if="initialLoading" />
      
      <!-- Main content -->
      <div v-else>
      <!-- Page Header -->
      <div class="service-header rounded-lg p-3 mb-3">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <div class="header-icon">
                <WrenchScrewdriverIcon class="w-4 h-4" />
              </div>
              <h1 class="text-lg sm:text-xl font-bold text-gray-900">Rental Requests</h1>
            </div>
            <p class="text-sm text-gray-600">Manage car rental requests and assignments efficiently</p>
          </div>
          <button 
            @click="openCreateModal"
            class="create-btn flex items-center gap-1 px-3 py-2 rounded-lg font-medium transition-all duration-300"
          >
            <PlusIcon class="w-4 h-4" />
            New Rental Request
          </button>
        </div>
      </div>

      <!-- Enhanced Filters Section -->
      <div class="filter-section rounded-lg p-3 mb-3">
        <div class="flex items-center gap-2 mb-2">
          <FunnelIcon class="w-4 h-4 text-gray-600" />
          <h3 class="text-sm font-semibold text-gray-900">Filters</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-2">
          <div class="filter-input-group">
            <label class="filter-label">Status</label>
            <select v-model="filters.status" class="filter-select">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="assigned">Assigned</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="filter-input-group">
            <label class="filter-label">Priority</label>
            <select v-model="filters.priority" class="filter-select">
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="critical">Critical</option>
            </select>
          </div>
          <div class="filter-input-group">
            <label class="filter-label">Start Date</label>
            <input v-model="filters.start_date" type="date" class="filter-input">
          </div>
          <div class="filter-input-group">
            <label class="filter-label">End Date</label>
            <input v-model="filters.end_date" type="date" class="filter-input">
          </div>
          <div class="filter-input-group">
            <label class="filter-label invisible">Actions</label>
            <div class="flex gap-2">
              <button 
                @click="fetchServiceRequests"
                class="filter-btn primary"
                title="Apply Filters"
              >
                <MagnifyingGlassIcon class="w-4 h-4" />
                Filter
              </button>
              <button 
                @click="resetFilters"
                class="filter-btn secondary"
                title="Reset Filters"
              >
                <XMarkIcon class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Stats Cards -->
      <div class="stats-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-3">
        <div class="stat-card total">
          <div class="flex items-center justify-between">
            <div>
              <p class="stat-label">Total Requests</p>
              <p class="stat-value">{{ statistics.total }}</p>
              <p class="stat-trend text-blue-600">
                <ChartBarIcon class="w-3 h-3 inline mr-1" />
                All time
              </p>
            </div>
            <div class="stat-icon total">
              <ClipboardDocumentListIcon class="w-6 h-6" />
            </div>
          </div>
        </div>
        
        <div class="stat-card pending">
          <div class="flex items-center justify-between">
            <div>
              <p class="stat-label">Pending</p>
              <p class="stat-value">{{ statistics.pending }}</p>
              <p class="stat-trend text-amber-600">
                <ClockIcon class="w-4 h-4 inline mr-1" />
                Needs attention
              </p>
            </div>
            <div class="stat-icon pending">
              <ClockIcon class="w-6 h-6" />
            </div>
          </div>
        </div>
        
        <div class="stat-card completed">
          <div class="flex items-center justify-between">
            <div>
              <p class="stat-label">Completed</p>
              <p class="stat-value">{{ statistics.completed }}</p>
              <p class="stat-trend text-emerald-600">
                <CheckCircleIcon class="w-4 h-4 inline mr-1" />
                Resolved
              </p>
            </div>
            <div class="stat-icon completed">
              <CheckCircleIcon class="w-6 h-6" />
            </div>
          </div>
        </div>
        
        <div class="stat-card critical">
          <div class="flex items-center justify-between">
            <div>
              <p class="stat-label">Critical Issues</p>
              <p class="stat-value">{{ statistics.critical }}</p>
              <p class="stat-trend text-red-600">
                <ExclamationTriangleIcon class="w-4 h-4 inline mr-1" />
                High priority
              </p>
            </div>
            <div class="stat-icon critical">
              <ExclamationTriangleIcon class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Modern Service Requests Table -->
      <div class="table-container rounded-lg overflow-hidden">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <div class="loading-spinner"></div>
          <p class="loading-text">Loading service requests...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="serviceRequests.length === 0" class="empty-state">
          <div class="empty-icon">
            <ClipboardDocumentIcon class="w-12 h-12" />
          </div>
          <h3 class="empty-title">No service requests found</h3>
          <p class="empty-description">Create your first service request to get started</p>
          <button @click="openCreateModal" class="empty-action-btn">
            <PlusIcon class="w-4 h-4" />
            Create Request
          </button>
        </div>

        <!-- Table -->
        <div v-else class="table-wrapper">
          <table class="service-table">
            <thead>
              <tr>
                <th class="table-header">
                  <div class="header-content">
                    <HashtagIcon class="w-4 h-4" />
                    ID
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    <TruckIcon class="w-4 h-4" />
                    Vehicle
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    <WrenchScrewdriverIcon class="w-4 h-4" />
                    Service Type
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    <UserIcon class="w-4 h-4" />
                    Customer
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    <CalendarDaysIcon class="w-4 h-4" />
                    Date
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    Status
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    Priority
                  </div>
                </th>
                <th class="table-header">
                  <div class="header-content">
                    <UserIcon class="w-4 h-4" />
                    Technician
                  </div>
                </th>
                <th class="table-header text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="request in serviceRequests" :key="request.id" class="table-row">
                <td class="table-cell">
                  <div class="request-id">
                    #{{ request.id }}
                  </div>
                </td>
                <td class="table-cell">
                  <div class="vehicle-info">
                    <div class="vehicle-name">{{ request.car?.make }} {{ request.car?.model }}</div>
                    <div class="vehicle-plate">{{ request.car?.license_plate || 'N/A' }}</div>
                  </div>
                </td>
                <td class="table-cell">
                  <div class="service-type">
                    <div class="service-name">{{ request.service_type || request.issue_type || 'General Service' }}</div>
                    <div class="service-description">{{ truncateText(request.description, 40) }}</div>
                  </div>
                </td>
                <td class="table-cell">
                  <div class="customer-info">
                    <div class="customer-name">{{ request.customer?.name || request.customer_name || 'N/A' }}</div>
                    <div class="customer-phone">{{ request.customer?.phone || request.customer_phone || 'N/A' }}</div>
                    <div v-if="request.customer?.email || request.customer_email" class="customer-email text-xs text-gray-500">{{ request.customer?.email || request.customer_email }}</div>
                  </div>
                </td>
                <td class="table-cell">
                  <div class="request-date">{{ formatDate(request.created_at) }}</div>
                </td>
                <td class="table-cell">
                  <span :class="getStatusBadgeClass(request.status)" class="status-badge">
                    {{ formatStatus(request.status) }}
                  </span>
                </td>
                <td class="table-cell">
                  <span :class="getPriorityBadgeClass(request.priority)" class="priority-badge">
                    {{ formatPriority(request.priority) }}
                  </span>
                </td>
                <td class="table-cell">
                  <div class="technician-info">
                    <div v-if="request.technician?.name || request.assignee?.name" class="technician-avatar">
                      {{ (request.technician?.name || request.assignee?.name).charAt(0).toUpperCase() }}
                    </div>
                    <div class="technician-details">
                      <span class="technician-name">{{ request.technician?.name || request.assignee?.name || 'Unassigned' }}</span>
                      <span class="technician-role">{{ request.technician?.role || request.assignee?.role || 'Technician' }}</span>
                    </div>
                  </div>
                </td>
                <td class="table-cell text-center">
                  <div class="action-buttons">
                    <button 
                      @click="openWorkflowModal(request)"
                      class="action-btn workflow"
                      title="View Workflow"
                    >
                      <CogIcon class="w-4 h-4" />
                    </button>
                    <button 
                      @click="openViewModal(request)"
                      class="action-btn view"
                      title="View Details"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                    <button 
                      @click="openEditModal(request)"
                      class="action-btn edit"
                      title="Edit Request"
                    >
                      <PencilSquareIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="canDeleteRequest(request)"
                      @click="confirmDelete(request)"
                      class="action-btn delete"
                      title="Delete Request"
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
      </div> <!-- Close v-else main content div -->

      <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-gray-900">
            {{ isEditing ? 'Edit Rental Request' : 'Create New Rental Request' }}
          </h3>
          <p class="text-sm text-gray-600 mb-2">
            Submit a rental request. Once submitted, it will be automatically assigned to the appropriate branch based on your location.
          </p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
              <input v-model="form.customer_name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer Phone *</label>
              <input v-model="form.customer_phone" type="tel" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer Email</label>
              <input v-model="form.customer_email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="customer@example.com" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Location Details *</label>
              <input v-model="form.customer_location" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Detailed address or landmark" />
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Governorate *</label>
              <select 
                v-model="form.governorate_id"
                @change="onGovernorateChange"
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Wilayat *</label>
              <select 
                v-model="form.wilayat_id"
                required
                :disabled="!form.governorate_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Wilayat</option>
                <option v-for="wilayat in availableWilayats" :key="wilayat.id" :value="wilayat.id">
                  {{ wilayat.name_en }} - {{ wilayat.name_ar }}
                </option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone *</label>
              <input 
                v-model="form.customer_phone" 
                type="tel" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="+968 XXXX XXXX"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Number of Rent Days *</label>
              <input 
                v-model.number="form.rent_days" 
                type="number" 
                min="1" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter number of days"
              />
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Pickup Date *</label>
              <input 
                v-model="form.pickup_date" 
                type="date" 
                required 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Return Date</label>
              <input 
                v-model="form.return_date" 
                type="date" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :readonly="form.rent_days > 0"
              />
              <p class="text-xs text-gray-500 mt-1">Auto-calculated based on rent days</p>
            </div>
          </div>

          <!-- Branch Assignment Section -->
          <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 class="text-sm font-semibold text-blue-800 mb-2">Branch Assignment</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Branch</label>
                <select 
                  v-model="form.assigned_branch_id" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Auto-assign based on Wilayat</option>
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                    {{ branch.name }} - {{ branch.city }}
                  </option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Leave empty for automatic assignment</p>
              </div>
              <div v-if="suggestedBranch">
                <label class="block text-sm font-medium text-gray-700 mb-1">Suggested Branch</label>
                <div class="p-2 bg-green-50 border border-green-200 rounded">
                  <p class="text-green-800 font-medium">{{ suggestedBranch.name }}</p>
                  <p class="text-green-600 text-xs">{{ suggestedBranch.city }} - {{ suggestedBranch.address }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Note: Service Type, Priority, Vehicle, Technician, and Estimated Cost are removed from initial form -->
          <!-- These will be added after branch assignment -->
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service Description *</label>
            <textarea 
              v-model="form.description" 
              required 
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Describe your rental requirements, specific car preferences, or any special requests..."
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Rental Request' : 'Submit Rental Request') }}
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
            Service Request Details
          </h3>
        </div>

        <div v-if="selectedRequest" class="space-y-4">
          <div class="flex justify-between items-center">
            <div>
              <span class="text-sm font-medium text-gray-500">Request ID</span>
              <p class="text-xl font-bold text-gray-900">#{{ selectedRequest.id }}</p>
            </div>
            <div>
              <span :class="getStatusClass(selectedRequest.status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                {{ formatStatus(selectedRequest.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Car Information</h4>
              <p class="text-gray-900 font-semibold">{{ selectedRequest.car?.make }} {{ selectedRequest.car?.model }}</p>
              <p class="text-gray-600">{{ selectedRequest.car?.year }} | {{ selectedRequest.car?.license_plate }}</p>
              <p class="text-gray-600">{{ selectedRequest.car?.color }}</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Issue Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Issue Type:</p>
                <p class="text-gray-900">{{ selectedRequest.issue_type }}</p>
                <p class="text-gray-600">Priority:</p>
                <p>
                  <span :class="getPriorityClass(selectedRequest.priority)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ formatPriority(selectedRequest.priority) }}
                  </span>
                </p>
              </div>
            </div>
          </div>

          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedRequest.description }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Status Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Created:</p>
                <p class="text-gray-900">{{ formatDate(selectedRequest.created_at) }}</p>
                <p class="text-gray-600">Updated:</p>
                <p class="text-gray-900">{{ formatDate(selectedRequest.updated_at) }}</p>
                <p class="text-gray-600">Completed:</p>
                <p class="text-gray-900">{{ selectedRequest.completed_at ? formatDate(selectedRequest.completed_at) : 'Not completed' }}</p>
              </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">Assignment Information</h4>
              <div class="grid grid-cols-2 gap-2">
                <p class="text-gray-600">Requested By:</p>
                <p class="text-gray-900">{{ selectedRequest.requester?.name || 'N/A' }}</p>
                <p class="text-gray-600">Assigned To:</p>
                <p class="text-gray-900">{{ selectedRequest.assignee?.name || 'Unassigned' }}</p>
              </div>
            </div>
          </div>

          <div v-if="selectedRequest.resolution_notes" class="p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Resolution Notes</h4>
            <p class="text-gray-900 whitespace-pre-line">{{ selectedRequest.resolution_notes }}</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button 
              @click="closeViewModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
            >
              Close
            </button>
            <button 
              @click="openEditModal(selectedRequest)"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
            >
              Edit Request
            </button>
          </div>
        </div>
      </div> <!-- Close View Modal inner div -->
    </div> <!-- Close View Modal outer div -->

    <!-- Workflow Modal -->
    <div v-if="showWorkflowModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-4 mx-auto p-3 border w-full max-w-6xl shadow-lg rounded-lg bg-white min-h-[90vh]">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              Service Request Workflow
            </h3>
            <p class="text-sm text-gray-600">
              Request #{{ selectedWorkflowRequest?.id }} - {{ selectedWorkflowRequest?.issue_type }}
            </p>
          </div>
          <button 
            @click="closeWorkflowModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <div v-if="selectedWorkflowRequest" class="workflow-modal-content">
          <ServiceRequestWorkflow :service-request-id="selectedWorkflowRequest.id" />
        </div>
      </div>
    </div>
    </div> <!-- Close max-w-7xl div -->
  </div> <!-- Close min-h-screen div -->
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { toast } from '../composables/useToast'
import { useAuthStore } from '../stores/auth'
import CRUDTableSkeleton from '../components/CRUDTableSkeleton.vue'
import ServiceRequestWorkflow from '../components/ServiceRequestWorkflow.vue'
import { 
  PlusIcon,
  WrenchScrewdriverIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ClipboardDocumentListIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ChartBarIcon,
  ClipboardDocumentIcon,
  HashtagIcon,
  TruckIcon,
  CalendarDaysIcon,
  UserIcon,
  EyeIcon,
  PencilSquareIcon,
  TrashIcon,
  CogIcon
} from '@heroicons/vue/24/outline'
import axios from 'axios'

const user = computed(() => useAuthStore().user)

// Reactive data declarations first
const serviceRequests = ref([])
const cars = ref([])
const technicians = ref([])
const governorates = ref([])
const wilayats = ref([])
const branches = ref([])
const loading = ref(false)
const initialLoading = ref(true)
const showModal = ref(false)
const showViewModal = ref(false)
const showWorkflowModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const selectedRequest = ref(null)
const selectedWorkflowRequest = ref(null)

const statistics = reactive({
  total: 0,
  pending: 0,
  completed: 0,
  critical: 0
})

const filters = reactive({
  status: '',
  priority: '',
  start_date: '',
  end_date: ''
})

const form = reactive({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  customer_location: '',
  governorate_id: '',
  wilayat_id: '',
  rent_days: 1,
  pickup_date: '',
  return_date: '',
  assigned_branch_id: '',
  description: ''
  // Removed: service_type, priority, car_id, technician_id, estimated_cost, scheduled_date
})

// Filter wilayats based on selected governorate
const availableWilayats = computed(() => {
  if (!form.governorate_id) {
    return []
  }
  return wilayats.value.filter(wilayat => wilayat.governorate_id == form.governorate_id)
})

// Get suggested branch based on selected wilayat
const suggestedBranch = computed(() => {
  if (!form.wilayat_id) {
    return null
  }
  // Find branch assignment for the wilayat
  const wilayat = wilayats.value.find(w => w.id == form.wilayat_id)
  if (wilayat && wilayat.branch) {
    return wilayat.branch
  }
  // Fallback to closest branch based on governorate
  const governorate = governorates.value.find(g => g.id == form.governorate_id)
  if (governorate) {
    return branches.value.find(b => b.governorate_id == governorate.id) || branches.value[0]
  }
  return null
})

// Auto-calculate return date based on rent days and pickup date
const autoCalculateReturnDate = () => {
  if (form.pickup_date && form.rent_days > 0) {
    const pickupDate = new Date(form.pickup_date)
    const returnDate = new Date(pickupDate)
    returnDate.setDate(pickupDate.getDate() + parseInt(form.rent_days))
    form.return_date = returnDate.toISOString().split('T')[0]
  }
}

// Watch for changes in pickup date or rent days
watch([() => form.pickup_date, () => form.rent_days], () => {
  autoCalculateReturnDate()
})

// Auto-assign branch when wilayat changes
watch(() => form.wilayat_id, (newWilayatId) => {
  if (newWilayatId && !form.assigned_branch_id) {
    const suggested = suggestedBranch.value
    if (suggested) {
      form.assigned_branch_id = suggested.id
    }
  }
})

// Reset filters
const resetFilters = () => {
  filters.status = ''
  filters.priority = ''
  filters.start_date = ''
  filters.end_date = ''
  fetchServiceRequests()
}

// Fetch service requests from API
const fetchServiceRequests = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.status) params.status = filters.status
    if (filters.priority) params.priority = filters.priority
    if (filters.start_date) params.start_date = filters.start_date
    if (filters.end_date) params.end_date = filters.end_date

    const response = await axios.get('/service-requests', { params })
    
    if (response.data.success) {
      serviceRequests.value = response.data.data.data || response.data.data
      updateStatistics()
    }
  } catch (error) {
    console.error('Error fetching service requests:', error)
  } finally {
    loading.value = false
  }
}

// Update statistics based on service requests data
const updateStatistics = () => {
  statistics.total = serviceRequests.value.length
  statistics.pending = serviceRequests.value.filter(req => req.status === 'pending' || req.status === 'assigned').length
  statistics.completed = serviceRequests.value.filter(req => req.status === 'completed').length
  statistics.critical = serviceRequests.value.filter(req => req.priority === 'critical').length
}

// Fetch cars for service request creation
const fetchCars = async () => {
  try {
    const response = await axios.get('/cars')
    if (response.data.success) {
      cars.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching cars:', error)
  }
}

// Fetch technicians for assignment
const fetchTechnicians = async () => {
  try {
    const response = await axios.get('/users', { params: { role: 'technician,mechanic' } })
    if (response.data.success) {
      technicians.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Error fetching technicians:', error)
  }
}

// Fetch governorates for service request location
const fetchGovernorates = async () => {
  try {
    const response = await axios.get('/governorates', { params: { active: true } })
    if (response.data.status === 'success') {
      governorates.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching governorates:', error)
  }
}

// Fetch wilayats for service request location
const fetchWilayats = async () => {
  try {
    const response = await axios.get('/wilayats', { params: { active: true } })
    if (response.data.status === 'success') {
      wilayats.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching wilayats:', error)
  }
}

// Fetch branches for assignment
const fetchBranches = async () => {
  try {
    const response = await axios.get('/branches', { params: { active: true } })
    if (response.data.status === 'success') {
      branches.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching branches:', error)
  }
}

// Handle governorate selection change
const onGovernorateChange = () => {
  form.wilayat_id = '' // Reset wilayat selection when governorate changes
}

// Modal handling
const openCreateModal = async () => {
  isEditing.value = false
  resetForm()
  await fetchCars()
  await fetchTechnicians()
  await fetchGovernorates()
  await fetchWilayats()
  showModal.value = true
}

const openEditModal = async (request) => {
  isEditing.value = true
  selectedRequest.value = request
  await fetchCars()
  await fetchTechnicians()
  await fetchGovernorates()
  await fetchWilayats()
  
  form.car_id = request.car_id
  form.issue_type = request.issue_type
  form.description = request.description
  form.priority = request.priority
  form.status = request.status
  form.assignee_id = request.assignee_id || ''
  form.resolution_notes = request.resolution_notes || ''
  form.completed_at = request.completed_at ? request.completed_at.split('T')[0] : ''
  form.governorate_id = request.governorate_id || ''
  form.wilayat_id = request.wilayat_id || ''
  
  // Map Service Request specific fields
  form.customer_name = request.customer_name || request.customer?.name || ''
  form.customer_phone = request.customer_phone || request.customer?.phone || ''
  form.customer_email = request.customer_email || request.customer?.email || ''
  form.customer_location = request.customer_location || ''
  form.service_type = request.service_type || request.issue_type || ''
  form.technician_id = request.technician_id || request.assignee_id || ''
  form.scheduled_date = request.scheduled_date || ''
  form.estimated_cost = request.estimated_cost || 0
  
  showModal.value = true
}

const openViewModal = (request) => {
  selectedRequest.value = request
  showViewModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedRequest.value = null
}

const openWorkflowModal = (request) => {
  selectedWorkflowRequest.value = request
  showWorkflowModal.value = true
}

const closeWorkflowModal = () => {
  showWorkflowModal.value = false
  selectedWorkflowRequest.value = null
}

const resetForm = () => {
  form.customer_name = ''
  form.customer_phone = ''
  form.customer_email = ''
  form.customer_location = ''
  form.governorate_id = ''
  form.wilayat_id = ''
  form.rent_days = 1
  form.pickup_date = ''
  form.return_date = ''
  form.assigned_branch_id = ''
  form.description = ''
  // Removed fields: service_type, priority, car_id, technician_id, estimated_cost, scheduled_date
}

// Form submission
const submitForm = async () => {
  submitting.value = true
  try {
    // Basic validation
    if (!form.customer_name || !form.customer_phone || !form.pickup_date || !form.return_date) {
      toast.error('Please fill in all required fields')
      submitting.value = false
      return
    }
    
    // Map frontend fields to backend expected fields
    const data = { 
      ...form,
      rental_start: form.pickup_date,
      rental_end: form.return_date
    }
    // Remove frontend-specific fields that backend doesn't expect
    delete data.pickup_date
    delete data.return_date
    
    let response
    if (isEditing.value) {
      response = await axios.put(`/service-requests/${selectedRequest.value.id}`, data)
    } else {
      response = await axios.post('/service-requests', data)
    }
    if (response.data.success) {
      closeModal()
      fetchServiceRequests()
      toast.success(isEditing.value ? 'Service request updated successfully!' : 'Service request submitted successfully!')
    }
  } catch (error) {
    console.error('Error saving service request:', error)
    toast.error(error.response?.data?.message || 'Failed to save service request')
  } finally {
    submitting.value = false
  }
}

// Delete service request
const canDeleteRequest = (request) => {
  return user.value?.role === 'admin' || 
         (user.value?.id === request.requester_id && request.status === 'pending')
}

const confirmDelete = (request) => {
  if (confirm(`Are you sure you want to delete this service request? This action cannot be undone.`)) {
    deleteRequest(request)
  }
}

const deleteRequest = async (request) => {
  try {
    const response = await axios.delete(`/service-requests/${request.id}`)
    
    if (response.data.success) {
      fetchServiceRequests()
      toast.success('Service request deleted successfully!')
    }
  } catch (error) {
    console.error('Error deleting service request:', error)
    toast.error(error.response?.data?.message || 'Failed to delete service request')
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
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'assigned':
      return 'bg-blue-100 text-blue-800'
    case 'in_progress':
      return 'bg-indigo-100 text-indigo-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'cancelled':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'pending':
      return 'status-pending'
    case 'assigned':
      return 'status-assigned'
    case 'in_progress':
      return 'status-in-progress'
    case 'completed':
      return 'status-completed'
    case 'cancelled':
      return 'status-cancelled'
    default:
      return 'status-default'
  }
}

const formatStatus = (status) => {
  if (status === 'in_progress') return 'In Progress'
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getPriorityClass = (priority) => {
  switch (priority) {
    case 'low':
      return 'bg-gray-100 text-gray-800'
    case 'medium':
      return 'bg-blue-100 text-blue-800'
    case 'high':
      return 'bg-orange-100 text-orange-800'
    case 'critical':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getPriorityBadgeClass = (priority) => {
  switch (priority) {
    case 'low':
      return 'priority-low'
    case 'medium':
      return 'priority-medium'
    case 'high':
      return 'priority-high'
    case 'critical':
      return 'priority-critical'
    default:
      return 'priority-default'
  }
}

const formatPriority = (priority) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1)
}

const truncateText = (text, maxLength) => {
  if (!text) return 'N/A'
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

onMounted(async () => {
  // Start all API calls in parallel for better performance  
  try {
    const promises = [
      fetchServiceRequests(),
      fetchCars(),
      fetchTechnicians(),
      fetchGovernoratesAndWilayats(),
      fetchBranches()
    ]
    await Promise.all(promises)
  } catch (error) {
    console.error('Error loading initial data:', error)
  } finally {
    initialLoading.value = false
  }
})

// Combined fetch function for governorates and wilayats
const fetchGovernoratesAndWilayats = async () => {
  await Promise.all([
    fetchGovernoratesData(),
    fetchWilayats()
  ])
}

const fetchGovernoratesData = async () => {
  try {
    const response = await axios.get('/governorates', { params: { active: true } })
    if (response.data.status === 'success') {
      governorates.value = response.data.data || []
    }
  } catch (error) {
    console.error('Error fetching governorates:', error)
  }
}
</script>

<style scoped>
/* Page Header */
.service-header {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 69, 19, 0.1) 100%);
  border: 1px solid rgba(99, 102, 241, 0.2);
  backdrop-filter: blur(10px);
}

.header-icon {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.create-btn {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border: 1px solid rgba(99, 102, 241, 0.3);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.create-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

/* Enhanced Filters */
.filter-section {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
  border: 1px solid rgba(229, 231, 235, 0.5);
  backdrop-filter: blur(10px);
}

.filter-input-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.125rem;
}

.filter-select, .filter-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background: white;
  transition: all 0.3s ease;
  font-size: 0.75rem;
}

.filter-select:focus, .filter-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-btn {
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  font-weight: 500;
  font-size: 0.75rem;
  transition: all 0.3s ease;
  border: 1px solid;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  cursor: pointer;
}

.filter-btn.primary {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-color: #6366f1;
  color: white;
}

.filter-btn.primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.filter-btn.secondary {
  background: white;
  border-color: #e5e7eb;
  color: #6b7280;
}

.filter-btn.secondary:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Enhanced Stats Cards */
.stats-grid {
  gap: 0.5rem;
}

.stat-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
  border: 1px solid rgba(229, 231, 235, 0.6);
  border-radius: 0.5rem;
  padding: 0.75rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(10px);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.stat-card.total {
  border-color: rgba(59, 130, 246, 0.3);
}

.stat-card.total:hover {
  border-color: rgba(59, 130, 246, 0.5);
  box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
}

.stat-card.pending {
  border-color: rgba(245, 158, 11, 0.3);
}

.stat-card.pending:hover {
  border-color: rgba(245, 158, 11, 0.5);
  box-shadow: 0 10px 25px rgba(245, 158, 11, 0.15);
}

.stat-card.completed {
  border-color: rgba(34, 197, 94, 0.3);
}

.stat-card.completed:hover {
  border-color: rgba(34, 197, 94, 0.5);
  box-shadow: 0 10px 25px rgba(34, 197, 94, 0.15);
}

.stat-card.critical {
  border-color: rgba(239, 68, 68, 0.3);
}

.stat-card.critical:hover {
  border-color: rgba(239, 68, 68, 0.5);
  box-shadow: 0 10px 25px rgba(239, 68, 68, 0.15);
}

.stat-label {
  color: #6b7280;
  font-size: 0.75rem;
  font-weight: 500;
  margin-bottom: 0.125rem;
}

.stat-value {
  color: #111827;
  font-size: 1.25rem;
  font-weight: bold;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-trend {
  font-size: 0.625rem;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.stat-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon.total {
  background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 100%);
}

.stat-icon.pending {
  background: linear-gradient(135deg, #f59e0b 0%, #fcd34d 100%);
}

.stat-icon.completed {
  background: linear-gradient(135deg, #22c55e 0%, #86efac 100%);
}

.stat-icon.critical {
  background: linear-gradient(135deg, #ef4444 0%, #fca5a5 100%);
}

/* Enhanced Table */
.table-container {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
  border: 1px solid rgba(229, 231, 235, 0.5);
  backdrop-filter: blur(10px);
}

.loading-state {
  padding: 1.5rem;
  text-align: center;
  color: #6b7280;
}

.loading-spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 3px solid #e5e7eb;
  border-top: 3px solid #6366f1;
  border-radius: 50%;
  margin: 0 auto 0.75rem;
  animation: spin 1s linear infinite;
}

.loading-text {
  font-size: 0.875rem;
  color: #6b7280;
}

.empty-state {
  padding: 2rem 1rem;
  text-align: center;
  color: #6b7280;
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
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
}

.empty-action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.table-wrapper {
  overflow-x: auto;
}

.service-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  border-bottom: 1px solid #e5e7eb;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

.request-id {
  font-weight: 600;
  color: #6366f1;
  font-size: 0.875rem;
}

.vehicle-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.vehicle-name {
  font-weight: 500;
  color: #111827;
  font-size: 0.875rem;
}

.vehicle-plate {
  color: #6b7280;
  font-size: 0.75rem;
}

.service-type {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.service-name {
  font-weight: 500;
  color: #111827;
  font-size: 0.875rem;
}

.service-description {
  color: #6b7280;
  font-size: 0.75rem;
}

.customer-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.customer-name {
  font-weight: 500;
  color: #111827;
  font-size: 0.875rem;
}

.customer-phone {
  color: #6b7280;
  font-size: 0.75rem;
}

.issue-type {
  color: #374151;
  font-size: 0.875rem;
}

.request-date {
  color: #6b7280;
  font-size: 0.875rem;
}

.assignee-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.assignee-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.75rem;
}

.assignee-name {
  color: #374151;
  font-size: 0.875rem;
}

.technician-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.technician-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.75rem;
}

.technician-details {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.technician-name {
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
}

.technician-role {
  color: #6b7280;
  font-size: 0.75rem;
}

.action-buttons {
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

.action-btn.workflow {
  background: rgba(99, 102, 241, 0.1);
  border-color: rgba(99, 102, 241, 0.3);
  color: #6366f1;
}

.action-btn.workflow:hover {
  background: rgba(99, 102, 241, 0.2);
  transform: scale(1.1);
}

.action-btn.view {
  background: rgba(59, 130, 246, 0.1);
  border-color: rgba(59, 130, 246, 0.3);
  color: #3b82f6;
}

.action-btn.view:hover {
  background: rgba(59, 130, 246, 0.2);
  transform: scale(1.1);
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

.action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

.action-btn.delete:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: scale(1.1);
}

/* Status Badges */
.status-badge, .priority-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-pending {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-assigned {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%);
  color: #2563eb;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.status-in-progress {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(165, 180, 252, 0.1) 100%);
  color: #6366f1;
  border: 1px solid rgba(99, 102, 241, 0.3);
}

.status-completed {
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(134, 239, 172, 0.1) 100%);
  color: #16a34a;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.status-cancelled {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.1) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-default {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

/* Priority Badges */
.priority-low {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

.priority-medium {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.1) 100%);
  color: #2563eb;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.priority-high {
  background: linear-gradient(135deg, rgba(251, 146, 60, 0.1) 0%, rgba(254, 215, 170, 0.1) 100%);
  color: #ea580c;
  border: 1px solid rgba(251, 146, 60, 0.3);
}

.priority-critical {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.1) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.priority-default {
  background: linear-gradient(135deg, rgba(107, 114, 128, 0.1) 0%, rgba(156, 163, 175, 0.1) 100%);
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.3);
}

/* Animations */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Workflow Modal Styles */
.workflow-modal-content {
  max-height: calc(90vh - 100px);
  overflow-y: auto;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .stat-value {
    font-size: 1.75rem;
  }
}

@media (max-width: 768px) {
  .service-header {
    padding: 1.25rem;
  }
  
  .filter-section {
    padding: 1.25rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    padding: 1.25rem;
  }
  
  .table-header,
  .table-cell {
    padding: 0.75rem 0.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .action-btn {
    width: 28px;
    height: 28px;
  }
}

@media (max-width: 640px) {
  .filter-input-group {
    grid-column: 1 / -1;
  }
  
  .create-btn {
    width: 100%;
    justify-content: center;
  }
  
  .assignee-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .technician-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .customer-info,
  .service-type {
    align-items: flex-start;
  }
}
</style>