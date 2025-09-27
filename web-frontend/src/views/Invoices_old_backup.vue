<template>
  <div class="invoices-container">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-info">
          <h1 class="page-title">Invoice Management</h1>
          <p class="page-subtitle">Create, manage, and track customer invoices</p>
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

    <!-- Statistics Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-success">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ statistics.paid_count || 0 }}</div>
            <div class="stat-label">Paid Invoices</div>
            <div class="stat-amount success">{{ formatCurrency(statistics.paid_amount || 0) }}</div>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-warning">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ statistics.unpaid_count || 0 }}</div>
            <div class="stat-label">Unpaid Invoices</div>
            <div class="stat-amount warning">{{ formatCurrency(statistics.unpaid_amount || 0) }}</div>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-error">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ statistics.overdue_count || 0 }}</div>
            <div class="stat-label">Overdue Invoices</div>
            <div class="stat-amount error">{{ formatCurrency(statistics.overdue_amount || 0) }}</div>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-content">
          <div class="stat-icon stat-icon-info">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ statistics.total_count || 0 }}</div>
            <div class="stat-label">Total Invoices</div>
            <div class="stat-amount">{{ formatCurrency(statistics.total_amount || 0) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="form-group">
          <label class="form-label">Search</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Search by invoice number or customer..."
            class="form-input"
          >
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select v-model="filters.status" class="form-select">
            <option value="">All Statuses</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">From Date</label>
          <input 
            v-model="filters.from_date"
            type="date" 
            class="form-input"
          >
        </div>
        <div class="form-group">
          <label class="form-label">To Date</label>
          <input 
            v-model="filters.to_date"
            type="date" 
            class="form-input"
          >
        </div>
        <div class="form-group">
          <button @click="fetchInvoices" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
            </svg>
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="table-container">
      <div class="table-header">
        <h2 class="table-title">Invoices</h2>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Loading invoices...</p>
      </div>
      
      <div v-else-if="filteredInvoices.length === 0" class="empty-state">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3>No invoices found</h3>
        <p>Create your first invoice or adjust your filters</p>
      </div>

      <div v-else>
        <table class="enhanced-table">
          <thead>
            <tr>
              <th>Invoice Details</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Dates</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in filteredInvoices" :key="invoice.id">
              <td>
                <div class="font-semibold">{{ invoice.invoice_number }}</div>
                <div class="text-xs text-gray-500">Created: {{ formatDate(invoice.created_at) }}</div>
              </td>
              <td>
                <div class="font-medium">{{ invoice.service_request?.customer_name || 'N/A' }}</div>
                <div class="text-xs text-gray-500">{{ invoice.service_request?.customer_email || invoice.service_request?.customer_phone || 'N/A' }}</div>
              </td>
              <td>
                <div class="font-semibold">{{ formatCurrency(invoice.total_amount) }}</div>
              </td>
              <td>
                <div class="text-sm">Issue: {{ formatDate(invoice.invoice_date) }}</div>
                <div class="text-xs text-gray-500">Due: {{ formatDate(invoice.due_date) }}</div>
              </td>
              <td>
                <span :class="['status-badge', invoice.status]">
                  {{ formatStatus(invoice.status) }}
                </span>
              </td>
              <td>
                <div class="actions-grid">
                  <button @click="viewInvoice(invoice)" class="action-btn action-view" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button @click="editInvoice(invoice)" class="action-btn action-edit" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button @click="printInvoice(invoice)" class="action-btn action-print" title="Print">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                  </button>
                  <button @click="downloadInvoicePdf(invoice)" class="action-btn action-download" title="Download PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </button>
                  <button @click="emailInvoice(invoice)" class="action-btn action-email" title="Email PDF">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </button>
                  <button @click="deleteInvoice(invoice)" class="action-btn action-delete" title="Delete">
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

    <!-- Pagination -->
    <div v-if="filteredInvoices.length > 0" class="pagination-container">
      <div class="flex items-center justify-between">
        <div class="pagination-info">
          <p>Showing page {{ pagination.current_page }} of {{ pagination.last_page }}</p>
        </div>
        <div class="pagination-controls">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            class="pagination-btn"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="pagination-btn"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
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
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="modal-body">
          <div class="form-grid">
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

            <div class="form-group">
              <label class="form-label">Invoice Number *</label>
              <input 
                v-model="form.invoice_number"
                type="text" 
                required
                placeholder="Auto-generated"
                class="form-input"
                readonly
              >
            </div>

            <div class="form-group">
              <label class="form-label">Customer Name *</label>
              <input 
                v-model="form.customer_name"
                type="text" 
                required
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
          <div class="invoice-items-section">
            <div class="section-header">
              <h4 class="section-title">Invoice Items</h4>
              <button @click="addInvoiceItem" type="button" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Item
              </button>
            </div>

            <div class="items-list">
              <div v-for="(item, index) in form.items" :key="index" class="invoice-item">
                <div class="item-grid">
                  <div class="form-group">
                    <label class="form-label">Description *</label>
                    <input 
                      v-model="item.description"
                      type="text" 
                      required
                      placeholder="Item description"
                      class="form-input"
                    >
                  </div>

                  <div class="form-group">
                    <label class="form-label">Quantity *</label>
                    <input 
                      v-model="item.quantity"
                      type="number" 
                      required
                      min="1"
                      step="1"
                      placeholder="1"
                      class="form-input"
                      @input="updateItemTotal(index)"
                    >
                  </div>

                  <div class="form-group">
                    <label class="form-label">Unit Price *</label>
                    <input 
                      v-model="item.unit_price"
                      type="number" 
                      required
                      min="0"
                      step="0.01"
                      placeholder="0.00"
                      class="form-input"
                      @input="updateItemTotal(index)"
                    >
                  </div>

                  <div class="form-group">
                    <label class="form-label">Total</label>
                    <div class="total-display">{{ formatCurrency(item.total || 0) }}</div>
                  </div>

                  <div class="item-actions">
                    <button 
                      @click="removeInvoiceItem(index)" 
                      type="button" 
                      class="remove-item-btn"
                      title="Remove item"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Invoice Totals -->
            <div class="invoice-totals">
              <div class="totals-grid">
                <div class="total-row">
                  <span class="total-label">Subtotal:</span>
                  <span class="total-value">{{ formatCurrency(form.subtotal || 0) }}</span>
                </div>
                <div class="total-row">
                  <span class="total-label">Tax ({{ form.tax_rate || 0 }}%):</span>
                  <span class="total-value">{{ formatCurrency(form.tax_amount || 0) }}</span>
                </div>
                <div class="total-row final-total">
                  <span class="total-label">Total Amount:</span>
                  <span class="total-value">{{ formatCurrency(form.total_amount || 0) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Notes</label>
            <textarea 
              v-model="form.notes"
              rows="3"
              placeholder="Additional notes or terms..."
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
          <div class="modal-actions">
            <button @click="printInvoice(currentInvoice)" class="action-btn action-print" title="Print">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
            </button>
            <button @click="downloadInvoicePdf(currentInvoice)" class="action-btn action-download" title="Download">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </button>
            <button @click="emailInvoice(currentInvoice)" class="action-btn action-email" title="Email">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </button>
            <button @click="closeViewModal" class="close-btn">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <div class="invoice-preview" ref="invoiceToPrint">
          <div class="invoice-header">
            <div class="company-info">
              <h2 class="company-name">Al-Ibdal Car Rental</h2>
              <p class="company-details">
                123 Business Street<br>
                Muscat, Sultanate of Oman<br>
                Phone: +968 2123 4567<br>
                Email: info@al-ibdal.com
              </p>
            </div>
            <div class="invoice-meta">
              <h1 class="invoice-title">INVOICE</h1>
              <p class="invoice-number"># {{ currentInvoice?.invoice_number }}</p>
              <p class="invoice-date">Date: {{ formatDate(currentInvoice?.invoice_date) }}</p>
              <p class="due-date">Due: {{ formatDate(currentInvoice?.due_date) }}</p>
            </div>
          </div>

          <div class="customer-info">
            <h3>Bill To:</h3>
            <p class="customer-name">{{ currentInvoice?.service_request?.customer_name || 'N/A' }}</p>
            <p class="customer-email">{{ currentInvoice?.service_request?.customer_email || 'No email provided' }}</p>
            <p class="customer-phone" v-if="currentInvoice?.service_request?.customer_phone">{{ currentInvoice?.service_request?.customer_phone }}</p>
            <p class="customer-address" v-if="currentInvoice?.service_request?.customer_location">{{ currentInvoice?.service_request?.customer_location }}</p>
          </div>

          <div class="invoice-items">
            <table class="items-table">
              <thead>
                <tr>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Unit Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in currentInvoice?.items" :key="item.id || item.description">
                  <td>{{ item.description }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ formatCurrency(item.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="invoice-summary">
            <div class="summary-totals">
              <div class="total-line">
                <span>Subtotal:</span>
                <span>{{ formatCurrency(currentInvoice?.subtotal) }}</span>
              </div>
              <div class="total-line">
                <span>Tax ({{ currentInvoice?.tax_rate || 0 }}%):</span>
                <span>{{ formatCurrency(currentInvoice?.tax_amount) }}</span>
              </div>
              <div class="total-line final">
                <span>Total Amount:</span>
                <span>{{ formatCurrency(currentInvoice?.total_amount) }}</span>
              </div>
            </div>
          </div>

          <div class="invoice-notes" v-if="currentInvoice?.notes">
            <h4>Notes:</h4>
            <p>{{ currentInvoice.notes }}</p>
          </div>

          <div class="invoice-footer">
            <p>Thank you for your business!</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { format } from 'date-fns'
import axios from 'axios'
import { useToast } from '../composables/useToast'

const toast = useToast()

// State
const loading = ref(false)
const invoices = ref([])
const contracts = ref([])
const currentInvoice = ref(null)
const showModal = ref(false)
const showViewModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)
const invoiceToPrint = ref(null)
const apiBase = (axios.defaults && axios.defaults.baseURL) || 'http://127.0.0.1:8000/api'

// Pagination
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
})

// Statistics
const statistics = reactive({
  total_count: 0,
  total_amount: 0,
  paid_count: 0,
  paid_amount: 0,
  unpaid_count: 0,
  unpaid_amount: 0,
  overdue_count: 0,
  overdue_amount: 0
})

// Filters
const filters = reactive({
  search: '',
  status: '',
  from_date: '',
  to_date: '',
  page: 1
})

// Form data
const form = reactive({
  invoice_number: '',
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_address: '',
  invoice_date: format(new Date(), 'yyyy-MM-dd'),
  due_date: format(new Date(Date.now() + 30*24*60*60*1000), 'yyyy-MM-dd'),
  status: 'unpaid',
  tax_rate: 5.00,
  subtotal: 0,
  tax_amount: 0,
  total_amount: 0,
  notes: '',
  items: [
    { description: '', quantity: 1, unit_price: 0, total: 0 }
  ]
})

// Computed properties
const filteredInvoices = computed(() => {
  let filtered = [...invoices.value]
  
  if (filters.search) {
    const searchTerm = filters.search.toLowerCase()
    filtered = filtered.filter(invoice => 
      invoice.invoice_number?.toLowerCase().includes(searchTerm) || 
      invoice.customer_name?.toLowerCase().includes(searchTerm) ||
      invoice.customer_email?.toLowerCase().includes(searchTerm)
    )
  }
  
  if (filters.status) {
    filtered = filtered.filter(invoice => invoice.status === filters.status)
  }
  
  if (filters.from_date) {
    filtered = filtered.filter(invoice => 
      new Date(invoice.invoice_date) >= new Date(filters.from_date)
    )
  }
  
  if (filters.to_date) {
    filtered = filtered.filter(invoice => 
      new Date(invoice.invoice_date) <= new Date(filters.to_date)
    )
  }
  
  return filtered
})

// Fetch invoices from API
const fetchInvoices = async () => {
  loading.value = true
  try {
    const response = await axios.get('/invoices', {
      params: {
        page: filters.page,
        per_page: filters.per_page,
        search: filters.search,
        status: filters.status,
        sort_by: filters.sort_by,
        sort_order: filters.sort_order
      }
    })
    
    if (response.data.success) {
      invoices.value = response.data.data.data || response.data.data
      
      // Update pagination if available
      if (response.data.data.pagination) {
        pagination.current_page = response.data.data.pagination.current_page
        pagination.last_page = response.data.data.pagination.last_page
        pagination.total = response.data.data.pagination.total
      }
      
      updateStatistics()
    } else {
      toast.error('Failed to load invoices')
    }
    
  } catch (error) {
    console.error('Error fetching invoices:', error)
    toast.error('Failed to load invoices. Please try again.')
  } finally {
    loading.value = false
  }
}

// Update statistics
const updateStatistics = () => {
  const allInvoices = invoices.value
  
  statistics.total_count = allInvoices.length
  statistics.total_amount = allInvoices.reduce((sum, inv) => sum + (inv.total_amount || 0), 0)
  
  const paidInvoices = allInvoices.filter(inv => inv.status === 'paid')
  statistics.paid_count = paidInvoices.length
  statistics.paid_amount = paidInvoices.reduce((sum, inv) => sum + (inv.total_amount || 0), 0)
  
  const unpaidInvoices = allInvoices.filter(inv => inv.status === 'unpaid')
  statistics.unpaid_count = unpaidInvoices.length
  statistics.unpaid_amount = unpaidInvoices.reduce((sum, inv) => sum + (inv.total_amount || 0), 0)
  
  const overdueInvoices = allInvoices.filter(inv => inv.status === 'overdue')
  statistics.overdue_count = overdueInvoices.length
  statistics.overdue_amount = overdueInvoices.reduce((sum, inv) => sum + (inv.total_amount || 0), 0)
}

// Modal functions
const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const editInvoice = (invoice) => {
  isEditing.value = true
  
  // Populate form with invoice data
  form.invoice_number = invoice.invoice_number
  form.customer_name = invoice.customer_name
  form.customer_email = invoice.customer_email
  form.customer_phone = invoice.customer_phone || ''
  form.customer_address = invoice.customer_address || ''
  form.invoice_date = invoice.invoice_date
  form.due_date = invoice.due_date
  form.status = invoice.status
  form.tax_rate = invoice.tax_rate || 5.00
  form.subtotal = invoice.subtotal || 0
  form.tax_amount = invoice.tax_amount || 0
  form.total_amount = invoice.total_amount || 0
  form.notes = invoice.notes || ''
  form.items = JSON.parse(JSON.stringify(invoice.items || [{ description: '', quantity: 1, unit_price: 0, total: 0 }]))
  
  showModal.value = true
}

const viewInvoice = (invoice) => {
  currentInvoice.value = invoice
  showViewModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const closeViewModal = () => {
  showViewModal.value = false
  currentInvoice.value = null
}

const resetForm = () => {
  form.invoice_number = generateInvoiceNumber()
  form.customer_name = ''
  form.customer_email = ''
  form.customer_phone = ''
  form.customer_address = ''
  form.invoice_date = format(new Date(), 'yyyy-MM-dd')
  form.due_date = format(new Date(Date.now() + 30*24*60*60*1000), 'yyyy-MM-dd')
  form.status = 'unpaid'
  form.tax_rate = 5.00
  form.subtotal = 0
  form.tax_amount = 0
  form.total_amount = 0
  form.notes = ''
  form.items = [{ description: '', quantity: 1, unit_price: 0, total: 0 }]
}

// Invoice item functions
const addInvoiceItem = () => {
  form.items.push({ description: '', quantity: 1, unit_price: 0, total: 0 })
}

const removeInvoiceItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
    updateInvoiceTotals()
  }
}

const updateItemTotal = (index) => {
  const item = form.items[index]
  if (item) {
    item.total = (item.quantity || 0) * (item.unit_price || 0)
    updateInvoiceTotals()
  }
}

const updateInvoiceTotals = () => {
  // Calculate subtotal from items
  form.subtotal = form.items.reduce((sum, item) => sum + (item.total || 0), 0)
  
  // Calculate tax amount
  form.tax_amount = (form.subtotal * (form.tax_rate || 0)) / 100
  
  // Calculate total amount
  form.total_amount = form.subtotal + form.tax_amount
}

// Submit form
const submitForm = async () => {
  submitting.value = true
  try {
    // Ensure totals are calculated
    updateInvoiceTotals()
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    if (isEditing.value) {
      // Update existing invoice
      const existingIndex = invoices.value.findIndex(inv => inv.invoice_number === form.invoice_number)
      if (existingIndex !== -1) {
        invoices.value[existingIndex] = {
          ...invoices.value[existingIndex],
          ...form,
          items: JSON.parse(JSON.stringify(form.items))
        }
      }
    } else {
      // Create new invoice
      const newInvoice = {
        id: Date.now(),
        ...form,
        created_at: new Date().toISOString(),
        items: JSON.parse(JSON.stringify(form.items))
      }
      invoices.value.unshift(newInvoice)
    }
    
    closeModal()
    toast.success(isEditing.value ? 'Invoice updated successfully!' : 'Invoice created successfully!')
    updateStatistics()
    
  } catch (error) {
    console.error('Error saving invoice:', error)
    toast.error('An error occurred while saving the invoice. Please try again.')
  } finally {
    submitting.value = false
  }
}

// Delete invoice
const deleteInvoice = async (invoice) => {
  if (!await toast.confirm('Delete Invoice', `Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
    return
  }
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // Remove from local array
    invoices.value = invoices.value.filter(inv => inv.id !== invoice.id)
    
    toast.success('Invoice deleted successfully!')
    updateStatistics()
    
  } catch (error) {
    console.error('Error deleting invoice:', error)
    toast.error('An error occurred while deleting the invoice. Please try again.')
  }
}

// Print invoice
const printInvoice = (invoice = null) => {
  const invoiceToUse = invoice || currentInvoice.value
  
  if (!invoiceToUse) {
    toast.error('No invoice selected for printing')
    return
  }

  try {
    // Prefer server-rendered PDF if available
    if (invoiceToUse.id) {
      const url = `${apiBase}/invoices/${invoiceToUse.id}/pdf`
      const printWindow = window.open(url, '_blank')
      if (printWindow) {
        toast.success('Opening invoice for printing...')
        return
      }
    }
    
    // Fallback to client-side print of the modal content
    if (!invoiceToPrint.value) {
      toast.error('Invoice preview not available for printing')
      return
    }
    
    const html = `<!doctype html><html><head><title>Invoice #${invoiceToUse.invoice_number || ''}</title>
    <style>
      body{font-family:Arial, sans-serif; padding:20px; color: #333;}
      .invoice-header{display:flex; justify-content:space-between; margin-bottom:30px;}
      .company-name{font-size:24px; font-weight:bold; margin-bottom:10px;}
      .invoice-title{font-size:36px; font-weight:bold; color:#059669;}
      table{width:100%;border-collapse:collapse; margin:20px 0;}
      th,td{padding:12px 8px;text-align:left;border-bottom:1px solid #ddd}
      th{background:#f3f4f6; font-weight:600;}
      .total-line{display:flex; justify-content:space-between; padding:8px 0;}
      .final{font-weight:bold; font-size:18px; border-top:2px solid #333; margin-top:10px; padding-top:10px;}
      @media print { 
        body{padding:0;} 
        .no-print{display:none;}
      }
    </style>
    </head><body>${invoiceToPrint.value.innerHTML}</body></html>`
    
    const printWindow = window.open('', '_blank')
    if (!printWindow) {
      toast.error('Unable to open print window. Please check your browser settings.')
      return
    }
    
    printWindow.document.write(html)
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
    printWindow.close()
    
    toast.success('Invoice sent to printer')
  } catch (error) {
    console.error('Error printing invoice:', error)
    toast.error('Failed to print invoice. Please try again.')
  }
}

// Download invoice PDF
const downloadInvoicePdf = async (invoice) => {
  if (!invoice?.id) {
    toast.error('Invalid invoice selected')
    return
  }
  
  try {
    await toast.promise(
      axios.get(`/invoices/${invoice.id}/pdf`, {
        responseType: 'blob'
      }),
      {
        loading: 'Generating PDF...',
        success: 'PDF downloaded successfully',
        error: (err) => {
          console.error('Error downloading PDF:', err)
          return err.response?.data?.message || 'Failed to download PDF. Please try again.'
        }
      }
    ).then(response => {
      // Create blob URL and download
      const blob = new Blob([response.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `invoice-${invoice.invoice_number}.pdf`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
    })
  } catch (error) {
    // Error is already handled by toast.promise
  }
}

// Email invoice
const emailInvoice = async (invoice) => {
  if (!invoice?.id) {
    toast.error('Invalid invoice selected')
    return
  }
  
  try {
    await toast.promise(
      axios.post(`/invoices/${invoice.id}/email`),
      {
        loading: 'Sending email...',
        success: 'Invoice email sent successfully',
        error: (err) => {
          console.error('Error sending email:', err)
          if (err.response?.status === 404) {
            return 'Invoice not found. Please refresh the page and try again.'
          }
          return err.response?.data?.message || 'Failed to email invoice. Please try again.'
        }
      }
    )
  } catch (error) {
    // Error is already handled by toast.promise
  }
}

// Pagination
const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    filters.page = page
    fetchInvoices()
  }
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  return format(new Date(dateString), 'MMM dd, yyyy')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-OM', {
    style: 'currency',
    currency: 'OMR'
  }).format(amount || 0)
}

const formatStatus = (status) => {
  if (!status) return ''
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const generateInvoiceNumber = () => {
  const prefix = 'INV-'
  const year = new Date().getFullYear()
  const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
  return `${prefix}${year}-${randomNum}`
}

// Initialize
onMounted(() => {
  fetchInvoices()
})
</script>

<style scoped>
/* Material Design Color Palette */
:root {
  --primary-50: #f0f9ff;
  --primary-100: #e0f2fe;
  --primary-200: #bae6fd;
  --primary-500: #0ea5e9;
  --primary-600: #0284c7;
  --primary-700: #0369a1;
  --success-50: #f0fdf4;
  --success-100: #dcfce7;
  --success-500: #22c55e;
  --success-600: #16a34a;
  --warning-50: #fffbeb;
  --warning-100: #fef3c7;
  --warning-500: #f59e0b;
  --warning-600: #d97706;
  --error-50: #fef2f2;
  --error-100: #fee2e2;
  --error-500: #ef4444;
  --error-600: #dc2626;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
}

/* Container Styles */
.invoices-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

/* Page Header */
.page-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 2.25rem;
  font-weight: 700;
  color: var(--gray-800);
  margin: 0 0 0.5rem 0;
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  font-size: 1.125rem;
  color: var(--gray-600);
  margin: 0;
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.btn-secondary {
  background: var(--gray-100);
  color: var(--gray-700);
  border: 1px solid var(--gray-200);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.875rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--gray-200);
  transform: translateY(-1px);
}

.btn-cancel {
  background: var(--gray-200);
  color: var(--gray-700);
  border: 1px solid var(--gray-300);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: var(--gray-300);
}

/* Statistics Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 25px 35px -5px rgba(0, 0, 0, 0.15);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea, #764ba2);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon-success {
  background: linear-gradient(135deg, var(--success-100), var(--success-50));
  color: var(--success-600);
}

.stat-icon-warning {
  background: linear-gradient(135deg, var(--warning-100), var(--warning-50));
  color: var(--warning-600);
}

.stat-icon-error {
  background: linear-gradient(135deg, var(--error-100), var(--error-50));
  color: var(--error-600);
}

.stat-icon-info {
  background: linear-gradient(135deg, var(--primary-100), var(--primary-50));
  color: var(--primary-600);
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-800);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--gray-600);
  margin-bottom: 0.5rem;
}

.stat-amount {
  font-size: 1.125rem;
  font-weight: 600;
}

.stat-amount.success {
  color: var(--success-600);
}

.stat-amount.warning {
  color: var(--warning-600);
}

.stat-amount.error {
  color: var(--error-600);
}

/* Filters */
.filters-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

/* Table Container */
.table-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 0;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
}

.table-header {
  background: linear-gradient(135deg, var(--gray-50), #ffffff);
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--gray-200);
}

.table-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

/* Enhanced Table */
.enhanced-table {
  width: 100%;
  border-collapse: collapse;
}

.enhanced-table th {
  background: linear-gradient(135deg, var(--gray-50), #ffffff);
  color: var(--gray-700);
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 1rem 1.5rem;
  text-align: left;
  border-bottom: 2px solid var(--gray-200);
  white-space: nowrap;
}

.enhanced-table td {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--gray-100);
  color: var(--gray-700);
  font-size: 0.875rem;
}

.enhanced-table tbody tr {
  transition: all 0.2s ease;
}

.enhanced-table tbody tr:hover {
  background: linear-gradient(135deg, var(--primary-50), rgba(255, 255, 255, 0.8));
  transform: scale(1.01);
}

/* Status Badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.paid {
  background: var(--success-100);
  color: var(--success-700);
}

.status-badge.unpaid {
  background: var(--warning-100);
  color: var(--warning-700);
}

.status-badge.overdue {
  background: var(--error-100);
  color: var(--error-700);
}

.status-badge.cancelled {
  background: var(--gray-100);
  color: var(--gray-700);
}

/* Action Buttons */
.actions-grid {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: white;
  font-size: 0.75rem;
}

.action-view {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.action-edit {
  background: linear-gradient(135deg, #10b981, #059669);
}

.action-print {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.action-download {
  background: linear-gradient(135deg, #06b6d4, #0891b2);
}

.action-email {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.action-delete {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.action-btn:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Form Styles */
.form-group {
  margin-bottom: 1rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid var(--gray-200);
  border-radius: 12px;
  font-size: 0.875rem;
  color: var(--gray-700);
  background: white;
  transition: all 0.2s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: var(--primary-500);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-container {
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  max-width: 90vw;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.modal-container.large {
  width: 100%;
  max-width: 1200px;
}

.modal-header {
  background: linear-gradient(135deg, var(--gray-50), white);
  padding: 2rem;
  border-bottom: 1px solid var(--gray-200);
  border-radius: 20px 20px 0 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.close-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 10px;
  background: var(--gray-100);
  color: var(--gray-600);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: var(--gray-200);
  color: var(--gray-800);
  transform: scale(1.05);
}

.modal-body {
  padding: 2rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 2rem;
  border-top: 1px solid var(--gray-200);
  background: var(--gray-50);
  border-radius: 0 0 20px 20px;
}

/* Invoice Items Section */
.invoice-items-section {
  background: var(--gray-50);
  border-radius: 16px;
  padding: 1.5rem;
  margin: 1.5rem 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.invoice-item {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 1px solid var(--gray-200);
}

.item-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 1rem;
  align-items: end;
}

.total-display {
  background: var(--gray-100);
  border: 2px solid var(--gray-200);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  font-weight: 600;
  color: var(--gray-700);
  text-align: right;
}

.remove-item-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 10px;
  background: var(--error-100);
  color: var(--error-600);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.remove-item-btn:hover {
  background: var(--error-200);
  transform: scale(1.05);
}

/* Invoice Totals */
.invoice-totals {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  border: 2px solid var(--gray-200);
}

.totals-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 300px;
  margin-left: auto;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.total-row.final-total {
  border-top: 2px solid var(--gray-300);
  padding-top: 1rem;
  margin-top: 0.5rem;
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--gray-800);
}

.total-label {
  font-weight: 500;
  color: var(--gray-600);
}

.total-value {
  font-weight: 600;
  color: var(--gray-800);
}

/* Invoice Preview */
.invoice-preview {
  background: white;
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  font-size: 14px;
  line-height: 1.5;
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--gray-200);
}

.company-name {
  font-size: 24px;
  font-weight: 700;
  color: var(--gray-800);
  margin: 0 0 0.5rem 0;
}

.company-details {
  color: var(--gray-600);
  line-height: 1.6;
}

.invoice-meta {
  text-align: right;
}

.invoice-title {
  font-size: 32px;
  font-weight: 700;
  color: var(--primary-600);
  margin: 0 0 0.5rem 0;
}

.invoice-number {
  font-size: 18px;
  font-weight: 600;
  color: var(--gray-700);
  margin: 0;
}

.invoice-date,
.due-date {
  color: var(--gray-600);
  margin: 0.25rem 0;
}

.customer-info {
  margin-bottom: 2rem;
}

.customer-info h3 {
  font-size: 16px;
  font-weight: 600;
  color: var(--gray-700);
  margin: 0 0 0.5rem 0;
}

.customer-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.customer-email,
.customer-phone,
.customer-address {
  color: var(--gray-600);
  margin: 0.25rem 0;
}

/* Items Table */
.items-table {
  width: 100%;
  border-collapse: collapse;
  margin: 2rem 0;
}

.items-table th {
  background: var(--gray-100);
  color: var(--gray-700);
  font-weight: 600;
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  text-align: left;
}

.items-table td {
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  color: var(--gray-700);
}

.items-table tbody tr:nth-child(even) {
  background: var(--gray-50);
}

/* Invoice Summary */
.invoice-summary {
  display: flex;
  justify-content: flex-end;
  margin: 2rem 0;
}

.summary-totals {
  min-width: 300px;
}

.total-line {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--gray-200);
}

.total-line.final {
  border-bottom: none;
  border-top: 2px solid var(--gray-800);
  font-weight: 700;
  font-size: 16px;
  padding-top: 1rem;
  margin-top: 0.5rem;
}

.invoice-notes {
  margin: 2rem 0;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: 8px;
}

.invoice-notes h4 {
  font-weight: 600;
  color: var(--gray-700);
  margin: 0 0 0.5rem 0;
}

.invoice-footer {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1rem;
  border-top: 1px solid var(--gray-200);
  color: var(--gray-600);
}

/* Loading States */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  color: var(--gray-600);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--gray-200);
  border-top: 3px solid var(--primary-500);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--gray-500);
}

.empty-state svg {
  margin: 0 auto 1rem;
  color: var(--gray-300);
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-600);
  margin: 0 0 0.5rem 0;
}

/* Pagination */
.pagination-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  padding: 1.5rem 2rem;
  margin-top: 1.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.pagination-info {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border: 1px solid var(--gray-300);
  background: white;
  color: var(--gray-600);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--primary-50);
  border-color: var(--primary-300);
  color: var(--primary-600);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn.active {
  background: var(--primary-500);
  border-color: var(--primary-500);
  color: white;
}

/* Animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  animation: slideInUp 0.3s ease-out;
}

.stat-card {
  animation: fadeIn 0.5s ease-out;
}

.table-container {
  animation: fadeIn 0.4s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
  .invoices-container {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    text-align: center;
  }
  
  .page-title {
    font-size: 1.75rem;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filters-grid {
    grid-template-columns: 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .item-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .enhanced-table {
    font-size: 0.75rem;
  }
  
  .enhanced-table th,
  .enhanced-table td {
    padding: 0.75rem 0.5rem;
  }
  
  .modal-container {
    margin: 1rem;
    max-width: calc(100% - 2rem);
  }
  
  .modal-header,
  .modal-body,
  .modal-actions {
    padding: 1.5rem;
  }
  
  .actions-grid {
    flex-wrap: wrap;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.5rem;
  }
  
  .stat-content {
    flex-direction: column;
    text-align: center;
  }
  
  .stat-value {
    font-size: 1.5rem;
  }
}

/* Print Styles */
@media print {
  .invoices-container {
    background: white;
    padding: 0;
  }
  
  .page-header,
  .filters-section,
  .stats-grid,
  .pagination-container {
    display: none;
  }
  
  .modal-overlay {
    position: static;
    background: none;
    backdrop-filter: none;
  }
  
  .modal-container {
    box-shadow: none;
    border: none;
    max-width: none;
    max-height: none;
    border-radius: 0;
  }
  
  .modal-header,
  .modal-actions {
    display: none;
  }
  
  .modal-body {
    padding: 0;
  }
  
  .invoice-preview {
    padding: 0;
    max-width: none;
  }
  
  .action-btn {
    display: none;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .stat-card,
  .table-container,
  .filters-section,
  .modal-container {
    background: rgba(31, 41, 55, 0.95);
    color: #f3f4f6;
  }
  
  .page-header {
    background: rgba(31, 41, 55, 0.95);
  }
  
  .form-input,
  .form-select,
  .form-textarea {
    background: #374151;
    color: #f3f4f6;
    border-color: #4b5563;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .btn-primary {
    background: #000;
    border: 2px solid #000;
  }
  
  .status-badge {
    border: 2px solid currentColor;
  }
  
  .action-btn {
    border: 2px solid rgba(255, 255, 255, 0.8);
  }
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>