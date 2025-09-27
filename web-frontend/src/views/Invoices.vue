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
      <div class="filters-header">
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
        <div class="view-toggle">
          <label class="form-label">View</label>
          <div class="toggle-buttons">
            <button 
              @click="viewMode = 'tiles'"
              :class="['toggle-btn', { active: viewMode === 'tiles' }]"
              title="Tile View"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
              </svg>
              Tiles
            </button>
            <button 
              @click="viewMode = 'table'"
              :class="['toggle-btn', { active: viewMode === 'table' }]"
              title="Table View"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0V4a1 1 0 011-1h14a1 1 0 011 1v16a1 1 0 01-1 1H5a1 1 0 01-1-1z"></path>
              </svg>
              Table
            </button>
          </div>
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

      <!-- Table View -->
      <div v-else-if="viewMode === 'table'" class="invoices-table-container">
        <table class="invoices-table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Customer</th>
              <th>Type</th>
              <th>Status</th>
              <th>Amount</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="invoice-row">
              <td class="invoice-number-cell">
                <strong>{{ invoice.invoice_number }}</strong>
              </td>
              <td class="customer-cell">
                <div class="customer-info-compact">
                  <div class="customer-name">{{ getCustomerName(invoice) }}</div>
                  <div class="customer-contact">{{ getCustomerContact(invoice) }}</div>
                </div>
              </td>
              <td class="type-cell">
                <span class="type-badge-compact" :class="`type-${getInvoiceType(invoice)}`">
                  {{ getInvoiceTypeLabel(invoice) }}
                </span>
              </td>
              <td class="status-cell">
                <span class="status-badge-compact" :class="`status-${invoice.status}`">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="amount-cell">
                <strong>{{ formatCurrency(invoice.total_amount) }}</strong>
              </td>
              <td class="date-cell">
                {{ formatDate(invoice.created_at) }}
              </td>
              <td class="actions-cell">
                <div class="table-actions">
                  <button @click="viewInvoice(invoice)" class="btn-table-action" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button @click="downloadPdf(invoice)" class="btn-table-action" title="PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </button>
                  <button @click="emailPdf(invoice)" class="btn-table-action" title="Email">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </button>
                  <div class="dropdown">
                    <button class="btn-table-action dropdown-toggle" title="More">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"></path>
                      </svg>
                    </button>
                    <div class="dropdown-menu">
                      <button @click="editInvoice(invoice)" class="dropdown-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                      </button>
                      <button @click="printInvoice(invoice)" class="dropdown-item">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                      </button>
                      <button @click="deleteInvoice(invoice.id)" class="dropdown-item text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Tile View (Compact) -->
      <div v-else class="invoices-grid-compact">
        <div v-for="invoice in filteredInvoices" :key="invoice.id" class="invoice-card-compact">
          <div class="invoice-header-compact">
            <div class="invoice-number-compact">{{ invoice.invoice_number }}</div>
            <div class="invoice-status-compact" :class="`status-${invoice.status}`">
              {{ invoice.status }}
            </div>
          </div>
          
          <div class="invoice-details-compact">
            <div class="customer-info-compact">
              <div class="customer-name">{{ getCustomerName(invoice) }}</div>
              <div class="customer-contact">{{ getCustomerContact(invoice) }}</div>
            </div>
            
            <div class="invoice-meta-compact">
              <span class="type-badge-compact" :class="`type-${getInvoiceType(invoice)}`">
                {{ getInvoiceTypeLabel(invoice) }}
              </span>
              <span class="amount-compact">{{ formatCurrency(invoice.total_amount) }}</span>
            </div>
          </div>

          <div class="invoice-actions-compact">
            <button @click="viewInvoice(invoice)" class="btn-action-compact primary" title="View">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </button>
            <button @click="downloadPdf(invoice)" class="btn-action-compact info" title="PDF">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </button>
            <button @click="emailPdf(invoice)" class="btn-action-compact warning" title="Email">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </button>
            <div class="dropdown-compact">
              <button class="btn-action-compact" title="More actions">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"></path>
                </svg>
              </button>
              <div class="dropdown-menu-compact">
                <button @click="editInvoice(invoice)" class="dropdown-item-compact">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Edit
                </button>
                <button @click="printInvoice(invoice)" class="dropdown-item-compact">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                  </svg>
                  Print
                </button>
                <button @click="deleteInvoice(invoice.id)" class="dropdown-item-compact danger">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  Delete
                </button>
              </div>
            </div>
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

    <!-- View Invoice Modal -->
    <div v-if="showViewModal" class="modal-overlay">
      <div class="modal-container large">
        <div class="modal-header">
          <h3 class="modal-title">Invoice Details</h3>
          <div class="modal-header-actions">
            <button @click="downloadPdf(viewingInvoice)" class="btn-info-small" title="Download PDF">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Download PDF
            </button>
            <button @click="emailPdf(viewingInvoice)" class="btn-warning-small" title="Email PDF">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              Email PDF
            </button>
            <button @click="printInvoice(viewingInvoice)" class="btn-success-small" title="Print">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Print
            </button>
          </div>
          <button @click="closeViewModal" class="close-btn">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div class="modal-body" v-if="viewingInvoice">
          <!-- Invoice Header Info -->
          <div class="invoice-view-header">
            <div class="invoice-info">
              <h2 class="invoice-number">{{ viewingInvoice.invoice_number }}</h2>
              <div class="invoice-status-badge" :class="`status-${viewingInvoice.status}`">
                {{ viewingInvoice.status.toUpperCase() }}
              </div>
            </div>
            <div class="invoice-dates">
              <div class="date-item">
                <label>Created:</label>
                <span>{{ formatDate(viewingInvoice.created_at) }}</span>
              </div>
              <div class="date-item">
                <label>Updated:</label>
                <span>{{ formatDate(viewingInvoice.updated_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Customer Information -->
          <div class="info-section">
            <h4 class="section-title">Customer Information</h4>
            <div class="info-grid">
              <div class="info-item">
                <label>Name:</label>
                <span>{{ getCustomerName(viewingInvoice) }}</span>
              </div>
              <div class="info-item">
                <label>Email:</label>
                <span>{{ getCustomerEmail(viewingInvoice) || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>Phone:</label>
                <span>{{ getCustomerPhone(viewingInvoice) || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>Type:</label>
                <span class="type-badge" :class="`type-${getInvoiceType(viewingInvoice)}`">
                  {{ getInvoiceTypeLabel(viewingInvoice) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Related Entity Information -->
          <div class="info-section">
            <h4 class="section-title">{{ getInvoiceTypeLabel(viewingInvoice) }} Details</h4>
            <div class="info-grid" v-if="getInvoiceType(viewingInvoice) === 'rental'">
              <div class="info-item">
                <label>Car:</label>
                <span>{{ viewingInvoice.invoiceable?.car?.make }} {{ viewingInvoice.invoiceable?.car?.model }} ({{ viewingInvoice.invoiceable?.car?.year }})</span>
              </div>
              <div class="info-item">
                <label>Registration:</label>
                <span>{{ viewingInvoice.invoiceable?.car?.registration || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>Rental Period:</label>
                <span>{{ formatDate(viewingInvoice.invoiceable?.pickup_date) }} - {{ formatDate(viewingInvoice.invoiceable?.return_date) }}</span>
              </div>
              <div class="info-item">
                <label>Rental Status:</label>
                <span class="rental-status">{{ viewingInvoice.invoiceable?.status }}</span>
              </div>
            </div>
            <div class="info-grid" v-else>
              <div class="info-item">
                <label>Service Type:</label>
                <span>{{ viewingInvoice.invoiceable?.service_type || viewingInvoice.service_request?.service_type || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>Priority:</label>
                <span>{{ viewingInvoice.invoiceable?.priority || viewingInvoice.service_request?.priority || 'N/A' }}</span>
              </div>
              <div class="info-item">
                <label>Service Status:</label>
                <span>{{ viewingInvoice.invoiceable?.status || viewingInvoice.service_request?.status || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <!-- Financial Information -->
          <div class="info-section">
            <h4 class="section-title">Financial Details</h4>
            <div class="financial-summary">
              <div class="financial-row">
                <span class="label">Base Amount:</span>
                <span class="amount">{{ formatCurrency(viewingInvoice.amount) }}</span>
              </div>
              <div class="financial-row">
                <span class="label">Tax Amount:</span>
                <span class="amount">{{ formatCurrency(viewingInvoice.tax_amount) }}</span>
              </div>
              <div class="financial-row">
                <span class="label">Discount Amount:</span>
                <span class="amount discount">-{{ formatCurrency(viewingInvoice.discount_amount) }}</span>
              </div>
              <div class="financial-row total">
                <span class="label">Total Amount:</span>
                <span class="amount">{{ formatCurrency(viewingInvoice.total_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="info-section" v-if="viewingInvoice.notes">
            <h4 class="section-title">Notes</h4>
            <div class="notes-content">
              {{ viewingInvoice.notes }}
            </div>
          </div>

          <div class="modal-actions">
            <button @click="editInvoice(viewingInvoice)" class="btn-primary">
              Edit Invoice
            </button>
            <button @click="closeViewModal" class="btn-cancel">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container">
      <div 
        v-for="toast in toasts" 
        :key="toast.id"
        :class="['toast', `toast-${toast.type}`]"
        @click="removeToast(toast.id)"
      >
        <div class="toast-content">
          <div class="toast-icon" v-html="getToastIcon(toast.type)"></div>
          <div class="toast-message">{{ toast.message }}</div>
          <button @click="removeToast(toast.id)" class="toast-close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
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
const showViewModal = ref(false)
const viewingInvoice = ref(null)
const editingInvoice = ref(null)
const viewMode = ref('tiles') // 'tiles' or 'table'

// Toast notification system
const toasts = ref([])

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
      // Success handled by toast notification
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
  // Show confirmation via toast with a timeout
  showToast('Click this notification again within 5 seconds to confirm deletion', 'warning')
  
  // For now, we'll use the confirm dialog but in a production app
  // you might want to implement a custom confirmation modal
  if (!confirm('Are you sure you want to delete this invoice?')) {
    showToast('Invoice deletion cancelled', 'info')
    return
  }
  
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

// View Invoice Modal Methods
const viewInvoice = (invoice) => {
  viewingInvoice.value = invoice
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  viewingInvoice.value = null
}

// Edit Invoice Method
const editInvoice = (invoice) => {
  editingInvoice.value = { ...invoice }
  isEditing.value = true
  showModal.value = true
  
  // Populate form with existing data
  form.value = {
    invoiceable_type: invoice.invoiceable_type,
    invoiceable_id: invoice.invoiceable_id,
    customer_name: getCustomerName(invoice),
    customer_email: getCustomerEmail(invoice),
    customer_phone: getCustomerPhone(invoice),
    amount: invoice.amount,
    tax_amount: invoice.tax_amount,
    discount_amount: invoice.discount_amount,
    total_amount: invoice.total_amount,
    status: invoice.status,
    notes: invoice.notes || ''
  }
}

// PDF Download Method
const downloadPdf = async (invoice) => {
  try {
    const authToken = localStorage.getItem('auth_token')
    const headers = {
      'Accept': 'application/pdf',
    }
    
    if (authToken) {
      headers['Authorization'] = `Bearer ${authToken}`
    }
    
    const response = await fetch(`/api/invoices/${invoice.id}/pdf`, {
      method: 'GET',
      headers: headers,
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `invoice-${invoice.invoice_number}.pdf`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
      showToast('PDF downloaded successfully!', 'success')
    } else {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
  } catch (error) {
    console.error('Error downloading PDF:', error)
    showToast('Failed to download PDF: ' + error.message, 'error')
  }
}

// Email PDF Method
const emailPdf = async (invoice) => {
  try {
    const authToken = localStorage.getItem('auth_token')
    const headers = {
      'Content-Type': 'application/json',
    }
    
    if (authToken) {
      headers['Authorization'] = `Bearer ${authToken}`
    }
    
    const response = await fetch(`/api/invoices/${invoice.id}/email`, {
      method: 'POST',
      headers: headers,
    })

    if (response.ok) {
      showToast('PDF has been emailed successfully!', 'success')
    } else {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
  } catch (error) {
    console.error('Error emailing PDF:', error)
    showToast('Failed to email PDF: ' + error.message, 'error')
  }
}

// Print Invoice Method
const printInvoice = (invoice) => {
  try {
    // Create a new window for printing
    const printWindow = window.open('', '_blank')
    
    // Generate printable HTML content
    const printContent = generatePrintableInvoice(invoice)
    
    printWindow.document.write(printContent)
    printWindow.document.close()
    
    // Wait for content to load, then print
    printWindow.onload = function() {
      printWindow.print()
      printWindow.close()
    }
    
    showToast('Print dialog opened', 'info')
  } catch (error) {
    console.error('Error printing invoice:', error)
    showToast('Failed to print invoice: ' + error.message, 'error')
  }
}

// Generate Printable HTML for Invoice
const generatePrintableInvoice = (invoice) => {
  return `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Invoice ${invoice.invoice_number}</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .invoice-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .invoice-details { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .section { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .section h3 { margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .financial-summary { border-top: 2px solid #333; padding-top: 15px; margin-top: 20px; }
        .total-row { font-weight: bold; font-size: 1.2em; color: #333; }
        .financial-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .status-badge { background: #e3f2fd; color: #1976d2; padding: 5px 10px; border-radius: 15px; font-size: 12px; }
        @media print { body { margin: 0; } }
      </style>
    </head>
    <body>
      <div class="invoice-header">
        <h1>INVOICE</h1>
        <h2>${invoice.invoice_number}</h2>
        <span class="status-badge">${invoice.status.toUpperCase()}</span>
      </div>
      
      <div class="invoice-details">
        <div class="section">
          <h3>Customer Information</h3>
          <p><strong>Name:</strong> ${getCustomerName(invoice)}</p>
          <p><strong>Email:</strong> ${getCustomerEmail(invoice) || 'N/A'}</p>
          <p><strong>Phone:</strong> ${getCustomerPhone(invoice) || 'N/A'}</p>
        </div>
        
        <div class="section">
          <h3>${getInvoiceTypeLabel(invoice)} Details</h3>
          ${getInvoiceType(invoice) === 'rental' ? `
            <p><strong>Car:</strong> ${invoice.invoiceable?.car?.make || ''} ${invoice.invoiceable?.car?.model || ''}</p>
            <p><strong>Registration:</strong> ${invoice.invoiceable?.car?.registration || 'N/A'}</p>
            <p><strong>Period:</strong> ${formatDate(invoice.invoiceable?.pickup_date)} - ${formatDate(invoice.invoiceable?.return_date)}</p>
          ` : `
            <p><strong>Service:</strong> ${invoice.invoiceable?.service_type || 'N/A'}</p>
            <p><strong>Priority:</strong> ${invoice.invoiceable?.priority || 'N/A'}</p>
          `}
        </div>
      </div>
      
      <div class="section financial-summary">
        <h3>Financial Summary</h3>
        <div class="financial-row">
          <span>Amount:</span>
          <span>${formatCurrency(invoice.amount)}</span>
        </div>
        <div class="financial-row">
          <span>Tax:</span>
          <span>${formatCurrency(invoice.tax_amount)}</span>
        </div>
        <div class="financial-row">
          <span>Discount:</span>
          <span>-${formatCurrency(invoice.discount_amount)}</span>
        </div>
        <div class="financial-row total-row">
          <span>Total:</span>
          <span>${formatCurrency(invoice.total_amount)}</span>
        </div>
      </div>
      
      ${invoice.notes ? `
      <div class="section">
        <h3>Notes</h3>
        <p>${invoice.notes}</p>
      </div>
      ` : ''}
      
      <div style="margin-top: 40px; text-align: center; color: #666; font-size: 12px;">
        <p>Generated on ${new Date().toLocaleDateString()}</p>
      </div>
    </body>
    </html>
  `
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

const getCustomerEmail = (invoice) => {
  if (invoice.invoiceable && invoice.invoiceable.customer) {
    return invoice.invoiceable.customer.email
  }
  if (invoice.invoiceable && invoice.invoiceable.customer_email) {
    return invoice.invoiceable.customer_email
  }
  if (invoice.service_request) {
    return invoice.service_request.customer_email
  }
  return null
}

const getCustomerPhone = (invoice) => {
  if (invoice.invoiceable && invoice.invoiceable.customer) {
    return invoice.invoiceable.customer.phone
  }
  if (invoice.invoiceable && invoice.invoiceable.customer_phone) {
    return invoice.invoiceable.customer_phone
  }
  if (invoice.service_request) {
    return invoice.service_request.customer_phone
  }
  return null
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

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (error) {
    return 'Invalid Date'
  }
}

const showToast = (message, type = 'info') => {
  const id = Date.now() + Math.random()
  const toast = {
    id,
    message,
    type,
    timestamp: new Date()
  }
  
  toasts.value.push(toast)
  
  // Auto-remove toast after 5 seconds (success/info) or 8 seconds (error/warning)
  const timeout = ['error', 'warning'].includes(type) ? 8000 : 5000
  setTimeout(() => {
    removeToast(id)
  }, timeout)
}

const removeToast = (toastId) => {
  const index = toasts.value.findIndex(toast => toast.id === toastId)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

const getToastIcon = (type) => {
  switch (type) {
    case 'success':
      return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>`
    case 'error':
      return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>`
    case 'warning':
      return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>`
    case 'info':
    default:
      return `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>`
  }
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

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  flex: 1;
}

.view-toggle {
  min-width: 160px;
}

.toggle-buttons {
  display: flex;
  background: #f1f5f9;
  border-radius: 8px;
  padding: 2px;
}

.toggle-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: none;
  background: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.toggle-btn.active {
  background: white;
  color: #3b82f6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-btn:hover:not(.active) {
  color: #3b82f6;
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

.modal-container.large {
  max-width: 900px;
}

.modal-header-actions {
  display: flex;
  gap: 8px;
  margin-left: 20px;
}

.btn-info-small, .btn-warning-small, .btn-success-small {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-info-small {
  background: #e6f3ff;
  color: #0066cc;
  border: 1px solid #b3d9ff;
}

.btn-warning-small {
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}

.btn-success-small {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.btn-info-small:hover {
  background: #cce7ff;
}

.btn-warning-small:hover {
  background: #ffeaa7;
}

.btn-success-small:hover {
  background: #c3e6cb;
}

.invoice-view-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 2px solid #e2e8f0;
}

.invoice-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.invoice-number {
  font-size: 24px;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.invoice-status-badge {
  padding: 6px 12px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: #fef5e7;
  color: #744210;
  border: 1px solid #f6e05e;
}

.status-sent {
  background: #e6f3ff;
  color: #1a365d;
  border: 1px solid #63b3ed;
}

.status-paid {
  background: #c6f6d5;
  color: #22543d;
  border: 1px solid #68d391;
}

.status-cancelled {
  background: #fed7d7;
  color: #742a2a;
  border: 1px solid #fc8181;
}

.invoice-dates {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: right;
}

.date-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.date-item label {
  font-weight: 600;
  color: #4a5568;
  min-width: 60px;
}

.date-item span {
  color: #2d3748;
}

.info-section {
  margin-bottom: 24px;
  background: #f7fafc;
  border-radius: 8px;
  padding: 20px;
  border: 1px solid #e2e8f0;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 1px solid #cbd5e0;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item label {
  font-weight: 600;
  color: #4a5568;
  font-size: 14px;
}

.info-item span {
  color: #2d3748;
  font-size: 14px;
}

.type-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  display: inline-block;
  width: fit-content;
}

.type-rental {
  background: #e6f3ff;
  color: #1a365d;
  border: 1px solid #63b3ed;
}

.type-service_request {
  background: #f0fff4;
  color: #22543d;
  border: 1px solid #68d391;
}

.financial-summary {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 20px;
}

.financial-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
}

.financial-row:last-child {
  border-bottom: none;
}

.financial-row.total {
  border-top: 2px solid #2d3748;
  margin-top: 12px;
  padding-top: 16px;
  font-weight: 700;
  font-size: 16px;
  color: #2d3748;
}

.financial-row .label {
  font-weight: 600;
  color: #4a5568;
}

.financial-row .amount {
  font-weight: 600;
  color: #2d3748;
}

.financial-row .amount.discount {
  color: #e53e3e;
}

.notes-content {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 16px;
  color: #2d3748;
  line-height: 1.6;
  white-space: pre-wrap;
}

.rental-status {
  text-transform: capitalize;
  font-weight: 600;
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

  .filters-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .view-toggle {
    min-width: auto;
  }
}

/* Table View Styles */
.invoices-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.invoices-table {
  width: 100%;
  border-collapse: collapse;
}

.invoices-table th {
  background: #f8fafc;
  padding: 12px;
  text-align: left;
  font-weight: 600;
  color: #475569;
  border-bottom: 1px solid #e2e8f0;
  font-size: 13px;
}

.invoices-table td {
  padding: 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.invoice-row:hover {
  background: #f8fafc;
}

.invoice-number-cell strong {
  color: #1e293b;
  font-weight: 600;
}

.customer-info-compact .customer-name {
  font-weight: 500;
  color: #1e293b;
  margin-bottom: 2px;
}

.customer-info-compact .customer-contact {
  font-size: 12px;
  color: #64748b;
}

.type-badge-compact, .status-badge-compact {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.type-badge-compact.type-rental {
  background: #e0f2fe;
  color: #0c4a6e;
}

.type-badge-compact.type-service_request {
  background: #f0fdf4;
  color: #166534;
}

.status-badge-compact.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge-compact.status-sent {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge-compact.status-paid {
  background: #dcfce7;
  color: #166534;
}

.status-badge-compact.status-cancelled {
  background: #fee2e2;
  color: #dc2626;
}

.amount-cell strong {
  color: #059669;
  font-weight: 600;
}

.date-cell {
  color: #64748b;
  font-size: 13px;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-table-action {
  padding: 6px;
  border: none;
  background: #f1f5f9;
  border-radius: 6px;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-table-action:hover {
  background: #e2e8f0;
  color: #374151;
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 100%;
  background: white;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  padding: 4px 0;
  min-width: 120px;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-4px);
  transition: all 0.2s;
}

.dropdown:hover .dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: none;
  background: none;
  color: #374151;
  cursor: pointer;
  transition: background 0.15s;
  font-size: 13px;
  width: 100%;
  text-align: left;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.text-red-600 {
  color: #dc2626;
}

/* Compact Tile View Styles */
.invoices-grid-compact {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.invoice-card-compact {
  background: white;
  border-radius: 10px;
  padding: 16px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  transition: all 0.2s;
  border: 1px solid #f1f5f9;
}

.invoice-card-compact:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
  border-color: #e2e8f0;
}

.invoice-header-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.invoice-number-compact {
  font-weight: 600;
  color: #1e293b;
  font-size: 14px;
}

.invoice-status-compact {
  padding: 3px 8px;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
}

.invoice-details-compact {
  margin-bottom: 12px;
}

.invoice-meta-compact {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.amount-compact {
  font-weight: 600;
  color: #059669;
  font-size: 15px;
}

.invoice-actions-compact {
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-action-compact {
  padding: 6px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-action-compact.primary {
  background: #e0f2fe;
  color: #0c4a6e;
}

.btn-action-compact.info {
  background: #f0f9ff;
  color: #075985;
}

.btn-action-compact.warning {
  background: #fef3c7;
  color: #92400e;
}

.btn-action-compact:not(.primary):not(.info):not(.warning) {
  background: #f1f5f9;
  color: #64748b;
}

.btn-action-compact:hover {
  transform: translateY(-1px);
  opacity: 0.8;
}

.dropdown-compact {
  position: relative;
  display: inline-block;
}

.dropdown-menu-compact {
  position: absolute;
  right: 0;
  top: 100%;
  background: white;
  border-radius: 6px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  padding: 4px 0;
  min-width: 120px;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-4px);
  transition: all 0.2s;
}

.dropdown-compact:hover .dropdown-menu-compact {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item-compact {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 10px;
  border: none;
  background: none;
  color: #374151;
  cursor: pointer;
  transition: background 0.15s;
  font-size: 12px;
  width: 100%;
  text-align: left;
}

.dropdown-item-compact:hover {
  background: #f3f4f6;
}

.dropdown-item-compact.danger {
  color: #dc2626;
}

/* Toast Notification Styles */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
}

.toast {
  background: white;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  padding: 0;
  cursor: pointer;
  transform: translateX(100%);
  animation: slideIn 0.3s ease-out forwards;
  border-left: 4px solid;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast-success {
  border-left-color: #10b981;
}

.toast-error {
  border-left-color: #ef4444;
}

.toast-warning {
  border-left-color: #f59e0b;
}

.toast-info {
  border-left-color: #3b82f6;
}

.toast-content {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
}

.toast-icon {
  flex-shrink: 0;
  margin-top: 2px;
}

.toast-success .toast-icon {
  color: #10b981;
}

.toast-error .toast-icon {
  color: #ef4444;
}

.toast-warning .toast-icon {
  color: #f59e0b;
}

.toast-info .toast-icon {
  color: #3b82f6;
}

.toast-message {
  flex: 1;
  color: #374151;
  font-size: 14px;
  line-height: 1.4;
}

.toast-close {
  flex-shrink: 0;
  padding: 2px;
  border: none;
  background: none;
  color: #9ca3af;
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s;
}

.toast-close:hover {
  background: #f3f4f6;
  color: #6b7280;
}
</style>