<template>
  <div class="p-3">
    <div class="flex justify-between items-center mb-3">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Wilayat-Branch Assignments</h1>
        <p class="text-gray-600 mt-1 text-sm">Assign wilayats to service branches for automatic routing</p>
      </div>
      <div class="flex gap-2">
        <button 
          @click="openBulkAssignModal"
          class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-colors text-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          Bulk Assign
        </button>
        <button 
          @click="openAssignmentModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-colors text-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          New Assignment
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
      <div class="bg-white p-3 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="p-1.5 bg-blue-100 rounded-md">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-600">Total Assignments</p>
            <p class="text-lg font-semibold text-gray-900">{{ assignments.length }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-3 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="p-1.5 bg-green-100 rounded-md">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-600">Assigned Wilayats</p>
            <p class="text-lg font-semibold text-gray-900">{{ assignedWilayatsCount }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-3 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="p-1.5 bg-yellow-100 rounded-md">
            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-600">Unassigned Wilayats</p>
            <p class="text-lg font-semibold text-gray-900">{{ unassignedWilayats.length }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-3 rounded-lg shadow-sm border">
        <div class="flex items-center">
          <div class="p-1.5 bg-purple-100 rounded-md">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-xs font-medium text-gray-600">Active Branches</p>
            <p class="text-lg font-semibold text-gray-900">{{ branches.length }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-3 p-3">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Search assignments..."
            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
          <select 
            v-model="filters.branch_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Branches</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }} - {{ branch.city }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Governorate</label>
          <select 
            v-model="filters.governorate_id"
            @change="onGovernorateChange"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Governorates</option>
            <option v-for="gov in governorates" :key="gov.id" :value="gov.id">
              {{ gov.name_en }}
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

    <!-- Unassigned Wilayats Alert -->
    <div v-if="unassignedWilayats.length > 0" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">
            {{ unassignedWilayats.length }} Unassigned Wilayats
          </h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>The following wilayats don't have primary branch assignments:</p>
            <ul class="mt-1 list-disc list-inside">
              <li v-for="wilayat in unassignedWilayats.slice(0, 5)" :key="wilayat.id">
                {{ wilayat.name_en }} ({{ wilayat.governorate?.name_en }})
              </li>
              <li v-if="unassignedWilayats.length > 5" class="text-xs">
                ...and {{ unassignedWilayats.length - 5 }} more
              </li>
            </ul>
          </div>
          <div class="mt-4">
            <button 
              @click="openBulkAssignModal"
              class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm transition-colors"
            >
              Assign These Wilayats
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignments Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Current Assignments</h3>
      </div>
      
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-600 mt-2">Loading assignments...</p>
      </div>
      
      <div v-else-if="filteredAssignments.length === 0" class="p-8 text-center text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p>No assignments found</p>
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Wilayat
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Branch
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Notes
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="assignment in filteredAssignments" :key="assignment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="font-medium text-gray-900">{{ assignment.wilayat?.name_en }}</div>
                  <div class="text-sm text-gray-500">{{ assignment.wilayat?.name_ar }}</div>
                  <div class="text-xs text-gray-400">{{ assignment.wilayat?.governorate?.name_en }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="font-medium text-gray-900">{{ assignment.branch?.name }}</div>
                  <div class="text-sm text-gray-500">{{ assignment.branch?.city }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="assignment.is_primary" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  Primary
                </span>
                <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                  Secondary
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="assignment.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ assignment.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate">
                  {{ assignment.notes || 'No notes' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                  <button 
                    @click="editAssignment(assignment)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit Assignment"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button 
                    @click="deleteAssignment(assignment.id)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Assignment"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Assignment Modal -->
    <div v-if="assignmentModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ editingAssignment ? 'Edit Assignment' : 'New Assignment' }}
          </h3>
          <button @click="closeAssignmentModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveAssignment" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Governorate</label>
            <select 
              v-model="selectedGovernorateId"
              @change="onModalGovernorateChange"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Governorate</option>
              <option v-for="gov in governorates" :key="gov.id" :value="gov.id">
                {{ gov.name_en }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Wilayat *</label>
            <select 
              v-model="assignmentForm.wilayat_id"
              required
              :disabled="!selectedGovernorateId"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Wilayat</option>
              <option v-for="wilayat in availableWilayats" :key="wilayat.id" :value="wilayat.id">
                {{ wilayat.name_en }} - {{ wilayat.name_ar }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
            <select 
              v-model="assignmentForm.branch_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Branch</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                {{ branch.name }} - {{ branch.city }}
              </option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center">
              <input 
                v-model="assignmentForm.is_active"
                type="checkbox"
                id="assignment_active"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="assignment_active" class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            <div class="flex items-center">
              <input 
                v-model="assignmentForm.is_primary"
                type="checkbox"
                id="assignment_primary"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="assignment_primary" class="ml-2 text-sm text-gray-700">Primary</label>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea 
              v-model="assignmentForm.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button 
              type="button" 
              @click="closeAssignmentModal"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="submittingAssignment"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
              {{ submittingAssignment ? 'Saving...' : (editingAssignment ? 'Update Assignment' : 'Create Assignment') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Assignment Modal -->
    <div v-if="bulkAssignModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl m-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Bulk Assign Wilayats</h3>
          <button @click="closeBulkAssignModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveBulkAssignment" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
            <select 
              v-model="bulkAssignForm.branch_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Branch</option>
              <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                {{ branch.name }} - {{ branch.city }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Select Wilayats</label>
            <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
              <div v-for="wilayat in unassignedWilayats" :key="wilayat.id" class="flex items-center">
                <input 
                  v-model="bulkAssignForm.wilayat_ids"
                  :value="wilayat.id"
                  type="checkbox"
                  :id="`wilayat_${wilayat.id}`"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label :for="`wilayat_${wilayat.id}`" class="ml-2 text-sm text-gray-900">
                  {{ wilayat.name_en }} - {{ wilayat.name_ar }} ({{ wilayat.governorate?.name_en }})
                </label>
              </div>
            </div>
            <div class="mt-2 flex gap-2">
              <button 
                type="button"
                @click="selectAllWilayats"
                class="text-sm text-blue-600 hover:text-blue-800"
              >
                Select All
              </button>
              <button 
                type="button"
                @click="clearWilayatSelection"
                class="text-sm text-gray-600 hover:text-gray-800"
              >
                Clear All
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center">
              <input 
                v-model="bulkAssignForm.is_primary"
                type="checkbox"
                id="bulk_primary"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="bulk_primary" class="ml-2 text-sm text-gray-700">Set as Primary</label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea 
              v-model="bulkAssignForm.notes"
              rows="3"
              placeholder="Optional notes for all assignments..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button 
              type="button" 
              @click="closeBulkAssignModal"
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="submittingBulkAssignment || bulkAssignForm.wilayat_ids.length === 0"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 transition-colors"
            >
              {{ submittingBulkAssignment ? 'Assigning...' : `Assign ${bulkAssignForm.wilayat_ids.length} Wilayat${bulkAssignForm.wilayat_ids.length !== 1 ? 's' : ''}` }}
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
import { useAPICache } from '../composables/useAPICache.js'

// Data
const assignments = ref([])
const branches = ref([])
const governorates = ref([])
const wilayats = ref([])
const unassignedWilayats = ref([])
const loading = ref(true)
const { fetchBranches } = useAPICache()

// Filters
const filters = reactive({
  search: '',
  branch_id: '',
  governorate_id: '',
  active: ''
})

// Assignment Modal
const assignmentModalOpen = ref(false)
const editingAssignment = ref(null)
const submittingAssignment = ref(false)
const selectedGovernorateId = ref('')
const assignmentForm = reactive({
  wilayat_id: '',
  branch_id: '',
  is_active: true,
  is_primary: false,
  notes: ''
})

// Bulk Assignment Modal
const bulkAssignModalOpen = ref(false)
const submittingBulkAssignment = ref(false)
const bulkAssignForm = reactive({
  branch_id: '',
  wilayat_ids: [],
  is_primary: true,
  notes: ''
})

// Computed
const assignedWilayatsCount = computed(() => {
  return new Set(assignments.value.filter(a => a.is_primary).map(a => a.wilayat_id)).size
})

const availableWilayats = computed(() => {
  if (!selectedGovernorateId.value) return []
  return wilayats.value.filter(w => w.governorate_id == selectedGovernorateId.value)
})

const filteredAssignments = computed(() => {
  let result = assignments.value

  if (filters.search) {
    const search = filters.search.toLowerCase()
    result = result.filter(assignment => 
      assignment.wilayat?.name_en.toLowerCase().includes(search) ||
      assignment.wilayat?.name_ar.toLowerCase().includes(search) ||
      assignment.branch?.name.toLowerCase().includes(search) ||
      assignment.branch?.city.toLowerCase().includes(search)
    )
  }

  if (filters.branch_id) {
    result = result.filter(assignment => assignment.branch_id == filters.branch_id)
  }

  if (filters.governorate_id) {
    result = result.filter(assignment => assignment.wilayat?.governorate_id == filters.governorate_id)
  }

  if (filters.active !== '') {
    const isActive = filters.active === 'true'
    result = result.filter(assignment => assignment.is_active === isActive)
  }

  return result
})

// Methods
const fetchData = async () => {
  try {
    loading.value = true
    
    // Fetch branches using cache
    const branchesResult = await fetchBranches()
    branches.value = branchesResult.data
    
    // Fetch other data in parallel
    const [assignmentsRes, governoratesRes, wilayatsRes, unassignedRes] = await Promise.all([
      axios.get('/wilayat-branch-assignments', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
      }),
      axios.get('/governorates'),
      axios.get('/wilayats'),
      axios.get('/wilayat-branch-assignments/unassigned/wilayats', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
      })
    ])

    assignments.value = assignmentsRes.data.data
    governorates.value = governoratesRes.data.data
    wilayats.value = wilayatsRes.data.data
    unassignedWilayats.value = unassignedRes.data.data
    
    console.log('Branches loaded:', branches.value.length) // Debug log
  } catch (error) {
    console.error('Error fetching data:', error)
    alert('Failed to fetch data')
  } finally {
    loading.value = false
  }
}

const onGovernorateChange = () => {
  // Clear wilayat filter when governorate changes
}

const onModalGovernorateChange = () => {
  assignmentForm.wilayat_id = ''
}

// Assignment Modal Methods
const openAssignmentModal = () => {
  resetAssignmentForm()
  editingAssignment.value = null
  assignmentModalOpen.value = true
}

const closeAssignmentModal = () => {
  assignmentModalOpen.value = false
  editingAssignment.value = null
  resetAssignmentForm()
}

const resetAssignmentForm = () => {
  selectedGovernorateId.value = ''
  assignmentForm.wilayat_id = ''
  assignmentForm.branch_id = ''
  assignmentForm.is_active = true
  assignmentForm.is_primary = false
  assignmentForm.notes = ''
}

const editAssignment = (assignment) => {
  editingAssignment.value = assignment
  selectedGovernorateId.value = assignment.wilayat?.governorate_id || ''
  assignmentForm.wilayat_id = assignment.wilayat_id
  assignmentForm.branch_id = assignment.branch_id
  assignmentForm.is_active = assignment.is_active
  assignmentForm.is_primary = assignment.is_primary
  assignmentForm.notes = assignment.notes || ''
  assignmentModalOpen.value = true
}

const saveAssignment = async () => {
  try {
    submittingAssignment.value = true
    
    const url = editingAssignment.value 
      ? `/wilayat-branch-assignments/${editingAssignment.value.id}`
      : '/wilayat-branch-assignments'
    const method = editingAssignment.value ? 'put' : 'post'
    
    const response = await axios[method](url, assignmentForm, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })

    await fetchData()
    closeAssignmentModal()
    alert(response.data.message)
  } catch (error) {
    console.error('Error saving assignment:', error)
    alert(error.response?.data?.message || 'Failed to save assignment')
  } finally {
    submittingAssignment.value = false
  }
}

const deleteAssignment = async (id) => {
  if (!confirm('Are you sure you want to delete this assignment?')) return

  try {
    const response = await axios.delete(`/wilayat-branch-assignments/${id}`, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })
    
    await fetchData()
    alert(response.data.message)
  } catch (error) {
    console.error('Error deleting assignment:', error)
    alert(error.response?.data?.message || 'Failed to delete assignment')
  }
}

// Bulk Assignment Methods
const openBulkAssignModal = () => {
  bulkAssignForm.branch_id = ''
  bulkAssignForm.wilayat_ids = []
  bulkAssignForm.is_primary = true
  bulkAssignForm.notes = ''
  bulkAssignModalOpen.value = true
}

const closeBulkAssignModal = () => {
  bulkAssignModalOpen.value = false
}

const selectAllWilayats = () => {
  bulkAssignForm.wilayat_ids = unassignedWilayats.value.map(w => w.id)
}

const clearWilayatSelection = () => {
  bulkAssignForm.wilayat_ids = []
}

const saveBulkAssignment = async () => {
  try {
    submittingBulkAssignment.value = true
    
    const response = await axios.post('/wilayat-branch-assignments/bulk-assign', bulkAssignForm, {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
    })

    await fetchData()
    closeBulkAssignModal()
    alert(response.data.message)
  } catch (error) {
    console.error('Error saving bulk assignments:', error)
    alert(error.response?.data?.message || 'Failed to save bulk assignments')
  } finally {
    submittingBulkAssignment.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>