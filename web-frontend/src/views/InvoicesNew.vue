<template>
  <div class="invoices-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-info">
          <h1 class="page-title">Invoice Management</h1>
          <p class="page-subtitle">Create and manage invoices for service requests and rentals</p>
        </div>
        <div class="header-actions">
          <button @click="openCreateModal" class="btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Invoice
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="form-group">
          <label class="form-label">Search</label>
          <input 
            v-model="filters.search" 
            type="text" 
            placeholder="Search invoices..."
            class="form-input"
          >
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select v-model="filters.status" class="form-select">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="sent">Sent</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select v-model="filters.type" class="form-select">
            <option value="">All Types</option>
            <option value="service_request">Service Request</option>
            <option value="rental">Rental</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Invoices List -->
    <div class="invoices-list">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading invoices...</p>
      </div>
      
      <div v-else-if="filteredInvoices.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3>No invoices found</h3>
        <p>Create your first invoice by clicking the "Create Invoice" button above.</p>
      </div>

      <div v-else class="invoices-grid">
        <div v-for="invoice in filteredInvoices" :key="invoice.id" class="invoice-card">
          <div class="invoice-header">
            <div class="invoice-number">{{ invoice.invoice_number }}</div>
            <div class="invoice-status" :class="`status-${invoice.status}`">
              {{ invoice.status }}
            </div>
          </div>
          
          <div class="invoice-details">
            <div class="customer-info">
              <div class="customer-name">
                {{ getCustomerName(invoice) }}
              </div>
              <div class="customer-contact">
                {{ getCustomerContact(invoice) }}
              </div>
            </div>
            
            <div class="invoice-type">
              <span class="type-badge" :class="`type-${getInvoiceType(invoice)}`">
                {{ getInvoiceTypeLabel(invoice) }}
              </span>
            </div>
            
            <div class="invoice-amount">
              <span class="amount">{{ formatCurrency(invoice.total_amount) }}</span>
            </div>
          </div>

          <div class="invoice-actions">
            <button @click="viewInvoice(invoice)" class="btn-secondary-small">
              View
            </button>
            <button @click="editInvoice(invoice)" class="btn-primary-small">
              Edit
            </button>
            <button @click="deleteInvoice(invoice.id)" class="btn-danger-small">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Invoice Modal -->
    <div v-if="showModal" class="modal-overlay">
      <div class="modal-container large">
        <div class="modal-header">
          <h3 class="modal-title">
            {{ isEditing ? 'Edit Invoice' : 'Create New Invoice' }}
          </h3>
          <button @click="closeModal" class="close-btn">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <!-- Module Selection -->
          <div class="form-group full-width">
            <label class="form-label">Link Invoice To *</label>
            <div class="radio-group">
              <label class="radio-option">
                <input 
                  type="radio" 
                  v-model="form.invoiceable_type" 
                  value="service_request"
                  @change="onModuleTypeChange"
                >
                <span>Service Request (User)</span>
              </label>
              <label class="radio-option">
                <input 
                  type="radio" 
                  v-model="form.invoiceable_type" 
                  value="rental"
                  @change="onModuleTypeChange"
                >
                <span>Rental (Customer)</span>
              </label>
            </div>
          </div>

          <!-- Service Request Selection -->
          <div v-if="form.invoiceable_type === 'service_request'" class="form-group full-width">
            <label class="form-label">Select Service Request *</label>
            <select 
              v-model="form.invoiceable_id" 
              class="form-select"
              required
              @change="onServiceRequestChange"
            >
              <option value="">Choose a service request</option>
              <option 
                v-for="sr in serviceRequests" 
                :key="sr.id" 
                :value="sr.id"
              >
                #{{ sr.id }} - {{ sr.customer_name }} ({{ sr.service_type }})
              </option>
            </select>
          </div>

          <!-- Rental Selection -->
          <div v-if="form.invoiceable_type === 'rental'" class="form-group full-width">
            <label class="form-label">Select Rental *</label>
            <select 
              v-model="form.invoiceable_id" 
              class="form-select"
              required
              @change="onRentalChange"
            >
              <option value="">Choose a rental</option>
              <option 
                v-for="rental in rentals" 
                :key="rental.id" 
                :value="rental.id"
              >
                #{{ rental.id }} - {{ rental.customer?.name }} ({{ rental.car?.make }} {{ rental.car?.model }})
              </option>
            </select>
          </div>

          <div class="form-grid">
            <!-- Auto-filled customer information -->
            <div class="form-group">
              <label class="form-label">Customer Name</label>
              <input 
                v-model="form.customer_name"
                type="text" 
                placeholder="Auto-filled from selection"
                class="form-input"
                readonly
              >
            </div>

            <div class="form-group">
              <label class="form-label">Customer Email</label>
              <input 
                v-model="form.customer_email"
                type="email" 
                placeholder="Auto-filled from selection"
                class="form-input"
                readonly
              >
            </div>

            <div class="form-group">
              <label class="form-label">Customer Phone</label>
              <input 
                v-model="form.customer_phone"
                type="tel" 
                placeholder="Auto-filled from selection"
                class="form-input"
                readonly
              >
            </div>

            <!-- Amount fields -->
            <div class="form-group">
              <label class="form-label">Base Amount *</label>
              <input 
                v-model="form.amount"
                type="number" 
                step="0.01"
                min="0"
                required
                placeholder="0.00"
                class="form-input"
                @input="calculateTotals"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Tax Amount</label>
              <input 
                v-model="form.tax_amount"
                type="number" 
                step="0.01"
                min="0"
                placeholder="0.00"
                class="form-input"
                @input="calculateTotals"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Discount Amount</label>
              <input 
                v-model="form.discount_amount"
                type="number" 
                step="0.01"
                min="0"
                placeholder="0.00"
                class="form-input"
                @input="calculateTotals"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Total Amount</label>
              <input 
                v-model="form.total_amount"
                type="number" 
                step="0.01"
                readonly
                class="form-input bg-gray-50"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-select">
                <option value="pending">Pending</option>
                <option value="sent">Sent</option>
                <option value="paid">Paid</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Notes</label>
            <textarea 
              v-model="form.notes"
              rows="3"
              placeholder="Additional notes..."
              class="form-textarea"
            ></textarea>
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
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Invoice' : 'Create Invoice') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Reactive data
const loading = ref(false)
const submitting = ref(false)
const showModal = ref(false)
const isEditing = ref(false)

const invoices = ref([])
const serviceRequests = ref([])
const rentals = ref([])

const filters = ref({
  search: '',
  status: '',
  type: ''
})

const form = ref({
  invoiceable_type: '',
  invoiceable_id: '',
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  amount: 0,
  tax_amount: 0,
  discount_amount: 0,
  total_amount: 0,
  status: 'pending',
  notes: ''
})

// Computed properties
const filteredInvoices = computed(() => {
  let filtered = invoices.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(invoice => 
      invoice.invoice_number.toLowerCase().includes(search) ||
      getCustomerName(invoice).toLowerCase().includes(search)
    )
  }

  if (filters.value.status) {
    filtered = filtered.filter(invoice => invoice.status === filters.value.status)
  }

  if (filters.value.type) {
    filtered = filtered.filter(invoice => getInvoiceType(invoice) === filters.value.type)
  }

  return filtered
})

// Methods
const fetchInvoices = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/invoices')
    if (response.ok) {
      const data = await response.json()
      invoices.value = data.data || data
    } else {
      console.error('Failed to fetch invoices')
      // Mock data fallback
      invoices.value = []
    }
  } catch (error) {
    console.error('Error fetching invoices:', error)
    invoices.value = []
  } finally {
    loading.value = false
  }
}

const fetchServiceRequests = async () => {
  try {
    const response = await fetch('/api/service-requests')
    if (response.ok) {
      const data = await response.json()
      serviceRequests.value = data.data || data || []
    } else {
      console.warn('Failed to fetch service requests, using fallback')
      serviceRequests.value = []
    }
  } catch (error) {
    console.error('Error fetching service requests:', error)
    serviceRequests.value = []
  }
}

const fetchRentals = async () => {
  try {
    const response = await fetch('/api/rentals')
    if (response.ok) {
      const data = await response.json()
      rentals.value = data.data || data || []
    } else {
      console.warn('Failed to fetch rentals, using fallback')
      rentals.value = []
    }
  } catch (error) {
    console.error('Error fetching rentals:', error)
    rentals.value = []
  }
}

const openCreateModal = async () => {
  resetForm()
  isEditing.value = false
  showModal.value = true
  
  // Fetch related data
  await Promise.all([
    fetchServiceRequests(),
    fetchRentals()
  ])
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  form.value = {
    invoiceable_type: '',
    invoiceable_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    amount: 0,
    tax_amount: 0,
    discount_amount: 0,
    total_amount: 0,
    status: 'pending',
    notes: ''
  }
}

const onModuleTypeChange = () => {
  form.value.invoiceable_id = ''
  form.value.customer_name = ''
  form.value.customer_email = ''
  form.value.customer_phone = ''
}

const onServiceRequestChange = () => {
  const sr = serviceRequests.value.find(sr => sr.id === parseInt(form.value.invoiceable_id))
  if (sr) {
    form.value.customer_name = sr.customer_name
    form.value.customer_email = sr.customer_email
    form.value.customer_phone = sr.customer_phone
  }
}

const onRentalChange = () => {
  const rental = rentals.value.find(r => r.id === parseInt(form.value.invoiceable_id))
  if (rental && rental.customer) {
    form.value.customer_name = rental.customer.name
    form.value.customer_email = rental.customer.email
    form.value.customer_phone = rental.customer.phone
  }
}

const calculateTotals = () => {
  const amount = parseFloat(form.value.amount) || 0
  const taxAmount = parseFloat(form.value.tax_amount) || 0
  const discountAmount = parseFloat(form.value.discount_amount) || 0
  
  form.value.total_amount = amount + taxAmount - discountAmount
}

const submitForm = async () => {
  try {
    submitting.value = true
    
    const payload = {
      invoiceable_type: form.value.invoiceable_type,
      invoiceable_id: form.value.invoiceable_id,
      amount: form.value.amount,
      tax_amount: form.value.tax_amount,
      discount_amount: form.value.discount_amount,
      notes: form.value.notes
    }

    const response = await fetch('/api/invoices', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    })

    if (response.ok) {
      const data = await response.json()
      console.log('Invoice created:', data)
      closeModal()
      fetchInvoices()
      showToast('Invoice created successfully!', 'success')
    } else {
      const errorData = await response.json()
      console.error('Failed to create invoice:', errorData)
      showToast(errorData.message || 'Failed to create invoice', 'error')
    }
  } catch (error) {
    console.error('Error creating invoice:', error)
    showToast('Error creating invoice', 'error')
  } finally {
    submitting.value = false
  }
}

const deleteInvoice = async (invoiceId) => {
  if (!confirm('Are you sure you want to delete this invoice?')) return
  
  try {
    const response = await fetch(`/api/invoices/${invoiceId}`, {
      method: 'DELETE'
    })
    
    if (response.ok) {
      showToast('Invoice deleted successfully!', 'success')
      fetchInvoices()
    } else {
      showToast('Failed to delete invoice', 'error')
    }
  } catch (error) {
    console.error('Error deleting invoice:', error)
    showToast('Error deleting invoice', 'error')
  }
}

// Helper methods
const getCustomerName = (invoice) => {
  if (invoice.invoiceable && invoice.invoiceable.customer) {
    return invoice.invoiceable.customer.name
  }
  if (invoice.invoiceable && invoice.invoiceable.customer_name) {
    return invoice.invoiceable.customer_name
  }
  if (invoice.service_request) {
    return invoice.service_request.customer_name
  }
  return 'N/A'
}

const getCustomerContact = (invoice) => {
  if (invoice.invoiceable && invoice.invoiceable.customer) {
    return invoice.invoiceable.customer.email || invoice.invoiceable.customer.phone
  }
  if (invoice.invoiceable && invoice.invoiceable.customer_email) {
    return invoice.invoiceable.customer_email
  }
  if (invoice.service_request) {
    return invoice.service_request.customer_email || invoice.service_request.customer_phone
  }
  return 'N/A'
}

const getInvoiceType = (invoice) => {
  if (invoice.invoiceable_type) {
    return invoice.invoiceable_type.includes('Rental') ? 'rental' : 'service_request'
  }
  return 'service_request' // Default for legacy invoices
}

const getInvoiceTypeLabel = (invoice) => {
  const type = getInvoiceType(invoice)
  return type === 'rental' ? 'Rental' : 'Service Request'
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'OMR',
    minimumFractionDigits: 2
  }).format(amount || 0)
}

const showToast = (message, type = 'info') => {
  console.log(`${type.toUpperCase()}: ${message}`)
  // You can integrate with your toast system here
}

// Lifecycle
onMounted(() => {
  fetchInvoices()
})
</script>

<style scoped>
/* Component styles */
.invoices-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.page-header {
  margin-bottom: 24px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1a202c;
  margin: 0;
}

.page-subtitle {
  font-size: 16px;
  color: #4a5568;
  margin: 4px 0 0 0;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #3182ce;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: #2c5aa0;
}

.filters-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 24px;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-size: 14px;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 6px;
}

.form-input, .form-select, .form-textarea {
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #3182ce;
  box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

.radio-group {
  display: flex;
  gap: 16px;
  margin-top: 6px;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.radio-option input[type="radio"] {
  margin: 0;
}

.invoices-list {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.invoices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.invoice-card {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  transition: box-shadow 0.2s;
}

.invoice-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.invoice-number {
  font-size: 16px;
  font-weight: 600;
  color: #2d3748;
}

.invoice-status {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: #fed7aa;
  color: #c2410c;
}

.status-sent {
  background: #bfdbfe;
  color: #1d4ed8;
}

.status-paid {
  background: #bbf7d0;
  color: #166534;
}

.status-cancelled {
  background: #fecaca;
  color: #dc2626;
}

.invoice-details {
  margin-bottom: 16px;
}

.customer-name {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 4px;
}

.customer-contact {
  font-size: 14px;
  color: #4a5568;
  margin-bottom: 8px;
}

.type-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.type-service_request {
  background: #e0e7ff;
  color: #3730a3;
}

.type-rental {
  background: #f3e8ff;
  color: #6b21a8;
}

.invoice-amount {
  text-align: right;
  margin-top: 8px;
}

.amount {
  font-size: 18px;
  font-weight: 700;
  color: #059669;
}

.invoice-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.btn-secondary-small, .btn-primary-small, .btn-danger-small {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-secondary-small {
  background: #f7fafc;
  color: #4a5568;
  border: 1px solid #e2e8f0;
}

.btn-primary-small {
  background: #3182ce;
  color: white;
}

.btn-danger-small {
  background: #e53e3e;
  color: white;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 40px 20px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #3182ce;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-icon {
  color: #a0aec0;
  margin-bottom: 16px;
}

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
  padding: 20px;
}

.modal-container {
  background: white;
  border-radius: 12px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-title {
  font-size: 20px;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #718096;
  cursor: pointer;
  padding: 4px;
}

.modal-body {
  padding: 20px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
}

.btn-cancel {
  padding: 10px 20px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #4a5568;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: stretch;
  }
  
  .invoices-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
}
</style>