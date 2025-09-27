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
                <div class="font-medium">{{ invoice.customer_name }}</div>
                <div class="text-xs text-gray-500">{{ invoice.customer_email }}</div>
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
            <div class="form-group">
              <label class="form-label">Invoice Number *</label>
              <input 
                v-model="form.invoice_number"
                type="text" 
                required
                placeholder="INV-001"
                class="form-input"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Customer Name *</label>
              <input 
                v-model="form.customer_name"
                type="text" 
                required
                placeholder="Customer name"
                class="form-input"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Customer Email *</label>
              <input 
                v-model="form.customer_email"
                type="email" 
                required
                placeholder="customer@example.com"
                class="form-input"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Customer Phone</label>
              <input 
                v-model="form.customer_phone"
                type="tel" 
                placeholder="+968 9876 5432"
                class="form-input"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Invoice Date *</label>
              <input 
                v-model="form.invoice_date"
                type="date" 
                required
                class="form-input"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Due Date *</label>
              <input 
                v-model="form.due_date"
                type="date" 
                required
                class="form-input"
              >
            </div>

            <div class="form-group">
              <label class="form-label">Status</label>
              <select v-model="form.status" class="form-select">
                <option value="unpaid">Unpaid</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Tax Rate (%)</label>
              <input 
                v-model="form.tax_rate"
                type="number" 
                step="0.01"
                min="0"
                max="100"
                placeholder="5.00"
                class="form-input"
                @input="updateInvoiceTotals"
              >
            </div>
          </div>

          <div class="form-group full-width">
            <label class="form-label">Customer Address</label>
            <textarea 
              v-model="form.customer_address"
              rows="3"
              placeholder="Customer address..."
              class="form-textarea"
            ></textarea>
          </div>

          <!-- Invoice Items -->
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
            <p class="customer-name">{{ currentInvoice?.customer_name }}</p>
            <p class="customer-email">{{ currentInvoice?.customer_email }}</p>
            <p class="customer-phone" v-if="currentInvoice?.customer_phone">{{ currentInvoice?.customer_phone }}</p>
            <p class="customer-address" v-if="currentInvoice?.customer_address">{{ currentInvoice?.customer_address }}</p>
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