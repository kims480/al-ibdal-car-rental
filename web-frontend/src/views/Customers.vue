<template>
  <div class="customers-container">
    <!-- Header Section -->
    <div class="customers-header">
      <div class="header-content">
        <div class="welcome-section">
          <h1 class="page-title">Customer Management</h1>
          <p class="page-subtitle">Manage, view, and register customers with comprehensive information</p>
        </div>
        <div class="header-stats">
          <div class="stat-card">
            <div class="stat-icon">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 015.196-2.121M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div class="stat-content">
              <p class="stat-number">{{ totalCustomers }}</p>
              <p class="stat-label">Total Customers</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="tabs-container">
      <div class="tabs-wrapper">
        <button 
          v-for="tab in tabs" 
          :key="tab.key"
          v-show="tab.key !== 'edit' || isEditing"
          @click="activeTab = tab.key"
          :class="['tab-button', { 'active': activeTab === tab.key }]"
        >
          <div class="tab-icon">
            <component :is="tab.icon" class="w-5 h-5" />
          </div>
          <span>{{ tab.label }}</span>
        </button>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Customers List Tab -->
      <div v-show="activeTab === 'list'" class="customers-list-content">
        <!-- Search and Filter Bar -->
        <div class="search-filter-bar">
          <div class="search-section">
            <div class="search-input-wrapper">
              <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input 
                v-model="searchTerm"
                type="text" 
                class="search-input"
                placeholder="Search by name, phone, or email..."
              />
            </div>
          </div>
          <div class="filter-actions">
            <button @click="refreshCustomers" class="filter-btn">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Refresh
            </button>
          </div>
        </div>

        <!-- Customers Table -->
        <div class="table-container">
          <div class="table-wrapper">
            <table class="customers-table">
              <thead>
                <tr>
                  <th>Customer Info</th>
                  <th>Contact Details</th>
                  <th>Location</th>
                  <th>Documents</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in filteredCustomers" :key="customer.id" class="table-row">
                  <td>
                    <div class="customer-info">
                      <div class="customer-avatar">
                        <span>{{ customer.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <div class="customer-details">
                        <div class="font-medium">{{ customer.name }}</div>
                        <div class="text-xs text-gray-500">ID: {{ customer.id }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="contact-info">
                      <div class="font-medium">{{ customer.phone }}</div>
                      <div class="text-xs text-gray-500">{{ customer.email }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="location-info">
                      <div class="font-medium">{{ customer.city }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="documents-status">
                      <div class="document-badges">
                        <span class="document-badge">Civil ID</span>
                        <span class="document-badge">License</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="date-info">
                      <div class="font-medium">{{ formatDate(customer.created_at) }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <button @click="viewCustomer(customer)" class="action-btn view-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button @click="editCustomer(customer)" class="action-btn edit-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="loading-state">
            <div class="loading-spinner">
              <svg class="animate-spin w-8 h-8" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <p>Loading customers...</p>
          </div>

          <!-- Empty State -->
          <div v-if="!loading && customers.length === 0" class="empty-state">
            <div class="empty-icon">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 015.196-2.121M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <h3>No Customers Found</h3>
            <p>Start by adding your first customer using the "Add Customer" tab.</p>
            <button @click="activeTab = 'add'" class="btn-primary">
              Add First Customer
            </button>
          </div>
        </div>
      </div>

      <!-- Add Customer Tab -->
      <div v-show="activeTab === 'add'" class="add-customer-content">
        <div class="customer-form-container">
          <form @submit.prevent="submitForm" class="customer-form">
            <!-- Personal Information Section -->
            <div class="form-section">
              <h3 class="section-title">Personal Information</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="name" class="form-label">Full Name *</label>
                  <input 
                    v-model="form.name" 
                    id="name" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter full name"
                  />
                </div>
                <div class="form-group">
                  <label for="phone" class="form-label">Phone Number *</label>
                  <input 
                    v-model="form.phone" 
                    id="phone" 
                    type="tel" 
                    required 
                    class="form-input"
                    placeholder="+968 XXXX XXXX"
                  />
                </div>
                <div class="form-group">
                  <label for="email" class="form-label">Email Address *</label>
                  <input 
                    v-model="form.email" 
                    id="email" 
                    type="email" 
                    required 
                    class="form-input"
                    placeholder="customer@example.com"
                  />
                </div>
                <div class="form-group">
                  <label for="city" class="form-label">City *</label>
                  <input 
                    v-model="form.city" 
                    id="city" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter city"
                  />
                </div>
              </div>
            </div>

            <!-- Identification Section -->
            <div class="form-section">
              <h3 class="section-title">Identification</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="civilId" class="form-label">Civil ID Number *</label>
                  <input 
                    v-model="form.civilId" 
                    id="civilId" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter civil ID number"
                  />
                </div>
                <div class="form-group">
                  <label for="licenseId" class="form-label">License ID Number *</label>
                  <input 
                    v-model="form.licenseId" 
                    id="licenseId" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter license ID number"
                  />
                </div>
              </div>
            </div>

            <!-- Document Upload Section -->
            <div class="form-section">
              <h3 class="section-title">Required Documents</h3>
              <div class="documents-grid">
                <div class="document-group">
                  <label for="civilIdFront" class="form-label">Civil ID Photo (Front) *</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="civilIdFront" 
                      type="file" 
                      accept="image/*" 
                      @change="handleFileChange('civilIdFront', $event)" 
                      required 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose file or drag here</span>
                    </div>
                  </div>
                </div>
                
                <div class="document-group">
                  <label for="civilIdBack" class="form-label">Civil ID Photo (Back) *</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="civilIdBack" 
                      type="file" 
                      accept="image/*" 
                      @change="handleFileChange('civilIdBack', $event)" 
                      required 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose file or drag here</span>
                    </div>
                  </div>
                </div>
                
                <div class="document-group">
                  <label for="licenseFront" class="form-label">License Photo (Front) *</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="licenseFront" 
                      type="file" 
                      accept="image/*" 
                      @change="handleFileChange('licenseFront', $event)" 
                      required 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose file or drag here</span>
                    </div>
                  </div>
                </div>
                
                <div class="document-group">
                  <label for="licenseBack" class="form-label">License Photo (Back) *</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="licenseBack" 
                      type="file" 
                      accept="image/*" 
                      @change="handleFileChange('licenseBack', $event)" 
                      required 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose file or drag here</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="resetForm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear Form
              </button>
              <button type="submit" class="submit-btn" :disabled="submitting">
                <svg v-if="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Adding Customer...' : 'Add Customer' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Edit Customer Tab -->
      <div v-show="activeTab === 'edit'" class="edit-customer-content">
        <div v-if="!editingCustomer" class="empty-edit-state">
          <div class="empty-icon">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </div>
          <h3>No Customer Selected</h3>
          <p>Please select a customer from the list to edit their information.</p>
          <button @click="activeTab = 'list'" class="btn-primary">
            Go to Customer List
          </button>
        </div>
        
        <div v-else class="customer-form-container">
          <div class="edit-header">
            <h3 class="section-title">Edit Customer: {{ editingCustomer.name }}</h3>
            <button @click="cancelEdit" class="cancel-edit-btn">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cancel Edit
            </button>
          </div>
          
          <form @submit.prevent="updateCustomer" class="customer-form">
            <!-- Personal Information Section -->
            <div class="form-section">
              <h3 class="section-title">Personal Information</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="editName" class="form-label">Full Name *</label>
                  <input 
                    v-model="editForm.name" 
                    id="editName" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter full name"
                  />
                </div>
                <div class="form-group">
                  <label for="editPhone" class="form-label">Phone Number *</label>
                  <input 
                    v-model="editForm.phone" 
                    id="editPhone" 
                    type="tel" 
                    required 
                    class="form-input"
                    placeholder="+968 XXXX XXXX"
                  />
                </div>
                <div class="form-group">
                  <label for="editEmail" class="form-label">Email Address *</label>
                  <input 
                    v-model="editForm.email" 
                    id="editEmail" 
                    type="email" 
                    required 
                    class="form-input"
                    placeholder="customer@example.com"
                  />
                </div>
                <div class="form-group">
                  <label for="editCity" class="form-label">City *</label>
                  <input 
                    v-model="editForm.city" 
                    id="editCity" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter city"
                  />
                </div>
              </div>
            </div>

            <!-- Identification Section -->
            <div class="form-section">
              <h3 class="section-title">Identification</h3>
              <div class="form-grid">
                <div class="form-group">
                  <label for="editCivilId" class="form-label">Civil ID Number *</label>
                  <input 
                    v-model="editForm.civilId" 
                    id="editCivilId" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter civil ID number"
                  />
                </div>
                <div class="form-group">
                  <label for="editLicenseId" class="form-label">License ID Number *</label>
                  <input 
                    v-model="editForm.licenseId" 
                    id="editLicenseId" 
                    type="text" 
                    required 
                    class="form-input"
                    placeholder="Enter license ID number"
                  />
                </div>
              </div>
            </div>

            <!-- Document Update Section -->
            <div class="form-section">
              <h3 class="section-title">Update Documents (Optional)</h3>
              <p class="section-note">Leave blank to keep existing documents</p>
              <div class="documents-grid">
                <div class="document-group">
                  <label for="editCivilIdFront" class="form-label">Civil ID Photo (Front)</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="editCivilIdFront" 
                      type="file" 
                      accept="image/*" 
                      @change="handleEditFileChange('civilIdFront', $event)" 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose new file or keep existing</span>
                    </div>
                  </div>
                </div>
                
                <div class="document-group">
                  <label for="editCivilIdBack" class="form-label">Civil ID Photo (Back)</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="editCivilIdBack" 
                      type="file" 
                      accept="image/*" 
                      @change="handleEditFileChange('civilIdBack', $event)" 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose new file or keep existing</span>
                    </div>
                  </div>
                </div>
                
                <div class="document-group">
                  <label for="editLicenseFront" class="form-label">License Photo (Front)</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="editLicenseFront" 
                      type="file" 
                      accept="image/*" 
                      @change="handleEditFileChange('licenseFront', $event)" 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose new file or keep existing</span>
                    </div>
                  </div>
                </div>
                
                <div class="document-group">
                  <label for="editLicenseBack" class="form-label">License Photo (Back)</label>
                  <div class="file-upload-wrapper">
                    <input 
                      id="editLicenseBack" 
                      type="file" 
                      accept="image/*" 
                      @change="handleEditFileChange('licenseBack', $event)" 
                      class="file-input"
                    />
                    <div class="file-upload-display">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      <span>Choose new file or keep existing</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="cancelEdit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
              </button>
              <button type="submit" class="submit-btn" :disabled="submitting">
                <svg v-if="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Updating Customer...' : 'Update Customer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Customer Details Modal -->
    <div v-if="selectedCustomer" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">Customer Details</h3>
          <button @click="closeModal" class="modal-close">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="customer-details-grid">
            <div class="detail-item">
              <label>Name</label>
              <p>{{ selectedCustomer.name }}</p>
            </div>
            <div class="detail-item">
              <label>Phone</label>
              <p>{{ selectedCustomer.phone }}</p>
            </div>
            <div class="detail-item">
              <label>Email</label>
              <p>{{ selectedCustomer.email }}</p>
            </div>
            <div class="detail-item">
              <label>City</label>
              <p>{{ selectedCustomer.city }}</p>
            </div>
            <div class="detail-item">
              <label>Civil ID</label>
              <p>{{ selectedCustomer.civil_id }}</p>
            </div>
            <div class="detail-item">
              <label>License ID</label>
              <p>{{ selectedCustomer.license_id }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from '../composables/useToast.js'

const toast = useToast()

// Tab management
const activeTab = ref('list')
const tabs = [
  { 
    key: 'list', 
    label: 'Customers List',
    icon: 'svg'
  },
  { 
    key: 'add', 
    label: 'Add Customer',
    icon: 'svg'
  },
  { 
    key: 'edit', 
    label: 'Edit Customer',
    icon: 'svg'
  }
]

// Data
const customers = ref([])
const loading = ref(false)
const searchTerm = ref('')
const selectedCustomer = ref(null)

// Form data
const form = ref({
  name: '',
  phone: '',
  email: '',
  city: '',
  civilId: '',
  licenseId: '',
  civilIdFront: null,
  civilIdBack: null,
  licenseFront: null,
  licenseBack: null,
})

const submitting = ref(false)
const editingCustomer = ref(null)
const isEditing = ref(false)

// Edit form data
const editForm = ref({
  id: null,
  name: '',
  phone: '',
  email: '',
  city: '',
  civilId: '',
  licenseId: '',
  civilIdFront: null,
  civilIdBack: null,
  licenseFront: null,
  licenseBack: null,
})

// Computed properties
const totalCustomers = computed(() => customers.value.length)

const filteredCustomers = computed(() => {
  if (!searchTerm.value) return customers.value
  
  const term = searchTerm.value.toLowerCase()
  return customers.value.filter(customer =>
    customer.name.toLowerCase().includes(term) ||
    customer.phone.toLowerCase().includes(term) ||
    customer.email.toLowerCase().includes(term)
  )
})

// Methods
async function fetchCustomers() {
  try {
    loading.value = true
    const response = await axios.get('/customers')
    customers.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching customers:', error)
    toast.error('Failed to load customers')
    customers.value = []
  } finally {
    loading.value = false
  }
}

function refreshCustomers() {
  fetchCustomers()
  toast.success('Customers list refreshed')
}

function handleFileChange(field, event) {
  const file = event.target.files[0]
  if (file) {
    form.value[field] = file
    // Update the display to show filename
    const display = event.target.nextElementSibling
    const span = display.querySelector('span')
    if (span) {
      span.textContent = file.name
    }
  }
}

function resetForm() {
  form.value = {
    name: '',
    phone: '',
    email: '',
    city: '',
    civilId: '',
    licenseId: '',
    civilIdFront: null,
    civilIdBack: null,
    licenseFront: null,
    licenseBack: null,
  }
  
  // Reset file inputs
  const fileInputs = document.querySelectorAll('input[type="file"]')
  fileInputs.forEach(input => {
    input.value = ''
    const display = input.nextElementSibling
    const span = display.querySelector('span')
    if (span) {
      span.textContent = 'Choose file or drag here'
    }
  })
}

async function submitForm() {
  try {
    submitting.value = true
    
    const formData = new FormData()
    
    // Add all form fields to FormData
    for (const key in form.value) {
      if (form.value[key] !== null) {
        formData.append(key, form.value[key])
      }
    }
    
    const response = await axios.post('/customers', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    if (response.data) {
      toast.success('Customer added successfully!')
      resetForm()
      fetchCustomers() // Refresh the list
      activeTab.value = 'list' // Switch to list view
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    
    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to add customer. Please try again.')
    }
  } finally {
    submitting.value = false
  }
}

function viewCustomer(customer) {
  selectedCustomer.value = customer
}

function editCustomer(customer) {
  // Set the editing customer and populate the edit form
  editingCustomer.value = customer
  editForm.value = {
    id: customer.id,
    name: customer.name,
    phone: customer.phone,
    email: customer.email,
    city: customer.city,
    civilId: customer.civil_id,
    licenseId: customer.license_id,
    civilIdFront: null,
    civilIdBack: null,
    licenseFront: null,
    licenseBack: null,
  }
  activeTab.value = 'edit'
  isEditing.value = true
}

function cancelEdit() {
  editingCustomer.value = null
  editForm.value = {
    id: null,
    name: '',
    phone: '',
    email: '',
    city: '',
    civilId: '',
    licenseId: '',
    civilIdFront: null,
    civilIdBack: null,
    licenseFront: null,
    licenseBack: null,
  }
  isEditing.value = false
  activeTab.value = 'list'
  
  // Reset file inputs in edit form
  const editFileInputs = document.querySelectorAll('#editCivilIdFront, #editCivilIdBack, #editLicenseFront, #editLicenseBack')
  editFileInputs.forEach(input => {
    if (input) {
      input.value = ''
      const display = input.nextElementSibling
      const span = display?.querySelector('span')
      if (span) {
        span.textContent = 'Choose new file or keep existing'
      }
    }
  })
}

function handleEditFileChange(field, event) {
  const file = event.target.files[0]
  if (file) {
    editForm.value[field] = file
    // Update the display to show filename
    const display = event.target.nextElementSibling
    const span = display.querySelector('span')
    if (span) {
      span.textContent = file.name
    }
  }
}

async function updateCustomer() {
  try {
    submitting.value = true
    
    const formData = new FormData()
    
    // Add text fields
    formData.append('name', editForm.value.name)
    formData.append('phone', editForm.value.phone)
    formData.append('email', editForm.value.email)
    formData.append('city', editForm.value.city)
    formData.append('civilId', editForm.value.civilId)
    formData.append('licenseId', editForm.value.licenseId)
    
    // Add file fields only if they have been changed
    if (editForm.value.civilIdFront) {
      formData.append('civilIdFront', editForm.value.civilIdFront)
    }
    if (editForm.value.civilIdBack) {
      formData.append('civilIdBack', editForm.value.civilIdBack)
    }
    if (editForm.value.licenseFront) {
      formData.append('licenseFront', editForm.value.licenseFront)
    }
    if (editForm.value.licenseBack) {
      formData.append('licenseBack', editForm.value.licenseBack)
    }
    
    // Add _method for PUT request (Laravel requirement for file uploads)
    formData.append('_method', 'PUT')
    
    const response = await axios.post(`/customers/${editForm.value.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    if (response.data) {
      toast.success('Customer updated successfully!')
      cancelEdit()
      fetchCustomers() // Refresh the list
    }
  } catch (error) {
    console.error('Error updating customer:', error)
    
    if (error.response?.data?.message) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to update customer. Please try again.')
    }
  } finally {
    submitting.value = false
  }
}

function closeModal() {
  selectedCustomer.value = null
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  fetchCustomers()
})
</script>

<style scoped>
/* Main Container */
.customers-container {
  min-height: 100vh;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Header Styles */
.customers-header {
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 69, 19, 0.1) 100%);
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  backdrop-filter: blur(10px);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.header-stats {
  display: flex;
  gap: 1rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 0.75rem;
  border: 1px solid rgba(229, 231, 235, 0.5);
}

.stat-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #22c55e 0%, #86efac 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-number {
  font-size: 1.25rem;
  font-weight: bold;
  color: #1f2937;
  margin: 0;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
}

/* Tab Styles */
.tabs-container {
  margin-bottom: 1.5rem;
}

.tabs-wrapper {
  display: flex;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 0.75rem;
  padding: 0.25rem;
  border: 1px solid rgba(229, 231, 235, 0.5);
  backdrop-filter: blur(10px);
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: transparent;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.3s ease;
  flex: 1;
  justify-content: center;
}

.tab-button:hover {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.tab-button.active {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.tab-icon svg {
  width: 20px;
  height: 20px;
}

/* Tab Content */
.tab-content {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
  border: 1px solid rgba(229, 231, 235, 0.5);
  border-radius: 1rem;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Search and Filter Bar */
.search-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.search-section {
  flex: 1;
}

.search-input-wrapper {
  position: relative;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 0.75rem 0.75rem 0.75rem 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  background: white;
}

.search-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-btn:hover {
  background: #f9fafb;
  border-color: #6366f1;
}

/* Table Styles */
.table-container {
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.table-wrapper {
  overflow-x: auto;
}

.customers-table {
  width: 100%;
  border-collapse: collapse;
}

.customers-table th {
  background: #f8fafc;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
}

.customers-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.table-row:hover {
  background: #f8fafc;
}

.customer-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.customer-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 0.875rem;
}

.customer-details {
  flex: 1;
}

.contact-info,
.location-info,
.date-info {
  font-size: 0.875rem;
}

.document-badges {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.document-badge {
  padding: 0.25rem 0.5rem;
  background: linear-gradient(135deg, #22c55e 0%, #86efac 100%);
  color: white;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.5rem;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-btn {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.view-btn:hover {
  background: rgba(59, 130, 246, 0.2);
}

.edit-btn {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.edit-btn:hover {
  background: rgba(245, 158, 11, 0.2);
}

/* Loading and Empty States */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #6b7280;
}

.loading-spinner {
  color: #6366f1;
  margin-bottom: 1rem;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
}

.empty-icon {
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

/* Form Styles (reused from AddCustomer) */
.customer-form-container {
  max-width: 1000px;
  margin: 0 auto;
}

/* Edit Tab Specific Styles */
.empty-edit-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
}

.empty-edit-state .empty-icon {
  color: #d1d5db;
  margin-bottom: 1rem;
}

.empty-edit-state h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-edit-state p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.edit-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.edit-header .section-title {
  margin-bottom: 0;
  border-bottom: none;
  padding-bottom: 0;
}

.cancel-edit-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-edit-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.3);
}

.section-note {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 1rem;
  font-style: italic;
}

.edit-customer-content {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.customer-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid rgba(99, 102, 241, 0.1);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.form-group,
.document-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  background: white;
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* File Upload Styles */
.file-upload-wrapper {
  position: relative;
}

.file-input {
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.file-upload-display {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  border: 2px dashed #d1d5db;
  border-radius: 0.5rem;
  background: #f9fafb;
  transition: all 0.2s ease;
  cursor: pointer;
  min-height: 100px;
}

.file-upload-display:hover {
  border-color: #6366f1;
  background: rgba(99, 102, 241, 0.05);
}

.file-upload-display svg {
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.file-upload-display span {
  font-size: 0.875rem;
  color: #6b7280;
  text-align: center;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.cancel-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  cursor: pointer;
}

.cancel-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
  color: #374151;
}

.submit-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: white;
  border-radius: 1rem;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.5rem 0;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 1.5rem;
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.25rem;
}

.modal-close:hover {
  color: #374151;
}

.modal-body {
  padding: 0 1.5rem 1.5rem;
}

.customer-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
}

.detail-item p {
  font-size: 0.875rem;
  color: #1f2937;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .customers-container {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .search-filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .tabs-wrapper {
    flex-direction: column;
  }
  
  .tab-button {
    justify-content: flex-start;
  }
  
  .form-grid,
  .documents-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .customer-details-grid {
    grid-template-columns: 1fr;
  }
}

/* Animations */
.customers-container {
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>