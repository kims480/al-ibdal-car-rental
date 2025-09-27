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
        <div class="stat-icon stat-icon-success">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ statistics.paid_count }}</div>
          <div class="stat-label">Paid Invoices</div>
          <div class="stat-amount success">{{ formatCurrency(statistics.paid_amount) }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-warning">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ statistics.unpaid_count }}</div>
          <div class="stat-label">Unpaid Invoices</div>
          <div class="stat-amount warning">{{ formatCurrency(statistics.unpaid_amount) }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-error">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ statistics.overdue_count }}</div>
          <div class="stat-label">Overdue Invoices</div>
          <div class="stat-amount error">{{ formatCurrency(statistics.overdue_amount) }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon stat-icon-info">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ statistics.total_count }}</div>
          <div class="stat-label">Total Invoices</div>
          <div class="stat-amount info">{{ formatCurrency(statistics.total_amount) }}</div>
        </div>
      </div>
    </div>

    <!-- Filters Card -->
    <div class="filters-card">
      <h3 class="filters-title">Filter & Search</h3>
      <div class="filters-grid">
        <div class="form-group">
          <label class="form-label">Search Invoices</label>
          <div class="input-with-icon">
            <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
              v-model="filters.search"
              type="text" 
              placeholder="Search by invoice #, customer name or email..."
              class="form-input"
            >
          </div>
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
          <label class="form-label">Amount Range</label>
          <select v-model="filters.amount_range" class="form-select">
            <option value="">All Amounts</option>
            <option value="0-100">$0 - $100</option>
            <option value="100-500">$100 - $500</option>
            <option value="500-1000">$500 - $1000</option>
            <option value="1000+">$1000+</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Actions</label>
          <div class="filter-actions">
            <button @click="applyFilters" class="btn-secondary">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
              </svg>
              Apply Filters
            </button>
            <button @click="clearFilters" class="btn-outline">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Clear
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="table-card">
      <div class="table-header">
        <h3 class="table-title">Invoices List</h3>
        <div class="table-actions">
          <button @click="refreshInvoices" class="btn-icon" title="Refresh">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
          </button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Due Date</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in paginatedInvoices" :key="invoice.id" class="table-row">
              <td class="table-cell">
                <div class="invoice-number">{{ invoice.invoice_number }}</div>
              </td>
              <td class="table-cell">
                <div class="customer-info">
                  <div class="customer-name">{{ invoice.customer_name }}</div>
                  <div class="customer-email">{{ invoice.customer_email }}</div>
                </div>
              </td>
              <td class="table-cell">
                <div class="date-info">{{ formatDate(invoice.invoice_date) }}</div>
              </td>
              <td class="table-cell">
                <div class="date-info">{{ formatDate(invoice.due_date) }}</div>
              </td>
              <td class="table-cell">
                <div class="amount-info">{{ formatCurrency(invoice.total_amount) }}</div>
              </td>
              <td class="table-cell">
                <span :class="['status-badge', `status-${invoice.status}`]">
                  {{ formatStatus(invoice.status) }}
                </span>
              </td>
              <td class="table-cell">
                <div class="action-buttons">
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
                  <button @click="printInvoice(invoice)" class="action-btn action-print" title="Print">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                  </button>
                  <button @click="confirmDeleteInvoice(invoice)" class="action-btn action-delete" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="loading" class="loading-state">
          <div class="loading-spinner"></div>
          <p>Loading invoices...</p>
        </div>

        <div v-else-if="paginatedInvoices.length === 0" class="empty-state">
          <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <h3>No invoices found</h3>
          <p>Create your first invoice or adjust your filters</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button 
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="pagination-btn"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Previous
        </button>

        <div class="pagination-info">
          Page {{ pagination.current_page }} of {{ pagination.last_page }}
        </div>

        <button 
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="pagination-btn"
        >
          Next
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input 
            v-model="filters.to_date"
            type="date" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
        </div>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-emerald-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Loading invoices...</p>
      </div>

      <div v-else-if="invoices.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p>No invoices found</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="font-medium text-gray-900">{{ invoice.invoice_number }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ invoice.customer_name }}</div>
                  <div class="text-xs text-gray-500">{{ invoice.customer_email }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                {{ formatCurrency(invoice.total_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.issue_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.due_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(invoice.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(invoice.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex flex-wrap gap-3">
                  <button @click="viewInvoice(invoice)" class="text-indigo-600 hover:text-indigo-900">View</button>
                  <button @click="openEditModal(invoice)" class="text-blue-600 hover:text-blue-900">Edit</button>
                  <button @click="confirmDelete(invoice)" class="text-red-600 hover:text-red-900">Delete</button>
                  <button @click="downloadInvoicePdf(invoice)" class="text-emerald-600 hover:text-emerald-800">Download PDF</button>
                  <button @click="emailInvoice(invoice)" class="text-amber-600 hover:text-amber-800">Email PDF</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="invoices.length > 0" class="px-6 py-3 flex items-center justify-between border-t border-gray-200">
        <div class="text-sm text-gray-700">
          Showing <span class="font-medium">{{ pagination.from }}</span> to <span class="font-medium">{{ pagination.to }}</span> of <span class="font-medium">{{ pagination.total }}</span> invoices
        </div>
        <div class="flex space-x-2">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 border rounded text-sm disabled:opacity-50"
          >
            Previous
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 border rounded text-sm disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
          <h3 class="text-lg font-bold text-gray-900">
            {{ isEditing ? 'Edit Invoice' : 'Create New Invoice' }}
          </h3>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number *</label>
              <input 
                v-model="form.invoice_number"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Related Contract *</label>
              <select 
                v-model="form.contract_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }} - {{ contract.customer_name }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
              <input 
                v-model="form.customer_name"
                type="text" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Customer Email *</label>
              <input 
                v-model="form.customer_email"
                type="email" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date *</label>
              <input 
                v-model="form.issue_date"
                type="date" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label>
              <input 
                v-model="form.due_date"
                type="date" 
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
              <select 
                v-model="form.status"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
              >
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Items</label>
            <div class="border rounded-md overflow-hidden mb-2">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Quantity</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Unit Price</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Amount</th>
                    <th class="px-4 py-2 w-16"></th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                    <td class="px-4 py-2">
                      <input 
                        v-model="item.description"
                        type="text" 
                        required
                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                      >
                    </td>
                    <td class="px-4 py-2">
                      <input 
                        v-model.number="item.quantity"
                        type="number" 
                        min="1"
                        required
                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                        @change="calculateItemTotal(index)"
                      >
                    </td>
                    <td class="px-4 py-2">
                      <input 
                        v-model.number="item.unit_price"
                        type="number" 
                        step="0.01"
                        min="0"
                        required
                        class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                        @change="calculateItemTotal(index)"
                      >
                    </td>
                    <td class="px-4 py-2 text-right text-sm">
                      {{ formatCurrency(item.total) }}
                    </td>
                    <td class="px-4 py-2">
                      <button 
                        type="button"
                        @click="removeInvoiceItem(index)"
                        class="text-red-500 hover:text-red-700"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <button 
              type="button"
              @click="addInvoiceItem"
              class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-800"
            >
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Add Item
            </button>
          </div>

          <div class="bg-gray-50 p-4 rounded-md">
            <div class="flex justify-between py-1">
              <span class="text-sm text-gray-600">Subtotal:</span>
              <span class="text-sm font-medium">{{ formatCurrency(calculateSubtotal()) }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-sm text-gray-600">Tax (5%):</span>
              <span class="text-sm font-medium">{{ formatCurrency(calculateTax()) }}</span>
            </div>
            <div class="flex justify-between py-1 border-t border-gray-200 mt-1 pt-2">
              <span class="text-base font-semibold text-gray-700">Total:</span>
              <span class="text-base font-semibold text-gray-900">{{ formatCurrency(form.total_amount) }}</span>
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
              class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Saving...' : (isEditing ? 'Update Invoice' : 'Create Invoice') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Invoice View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-900">
            Invoice Details
          </h3>
          <div class="flex items-center gap-2">
            <button 
              @click="printInvoice" 
              class="p-2 bg-blue-50 rounded-md text-blue-600 hover:bg-blue-100"
              title="Print Invoice"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
            </button>
            <button 
              @click="downloadInvoicePdf(currentInvoice)" 
              class="p-2 bg-emerald-50 rounded-md text-emerald-600 hover:bg-emerald-100"
              title="Download PDF"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
              </svg>
            </button>
            <button 
              @click="emailInvoice(currentInvoice)" 
              class="p-2 bg-amber-50 rounded-md text-amber-600 hover:bg-amber-100"
              title="Email PDF"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-2 8H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v6a2 2 0 01-2 2z" />
              </svg>
            </button>
            <button 
              @click="closeViewModal" 
              class="p-2 bg-gray-50 rounded-md text-gray-500 hover:bg-gray-100"
              title="Close"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <div class="invoice-details p-6 bg-white rounded-md border" ref="invoiceToPrint">
          <!-- Invoice Header -->
          <div class="flex justify-between items-start mb-8">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">INVOICE</h2>
              <p class="text-gray-600 mt-1"># {{ currentInvoice?.invoice_number }}</p>
            </div>
            <div class="text-right">
              <h3 class="text-lg font-semibold text-gray-900">AL IBDAL TRADING LLC</h3>
              <p class="text-gray-600">Sheikh Zayed Road, Dubai, UAE</p>
              <p class="text-gray-600">info@alibdaltrading.com</p>
              <p class="text-gray-600">+971 4 123 4567</p>
            </div>
          </div>

          <!-- Invoice Info -->
          <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
              <h4 class="font-semibold text-gray-700 mb-2">Bill To:</h4>
              <p class="text-gray-900 font-medium">{{ currentInvoice?.customer_name }}</p>
              <p class="text-gray-600">{{ currentInvoice?.customer_email }}</p>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-gray-600">Invoice Date:</span>
                <span class="text-gray-900">{{ formatDate(currentInvoice?.issue_date) }}</span>
              </div>
              <div class="flex justify-between mb-1">
                <span class="text-gray-600">Due Date:</span>
                <span class="text-gray-900">{{ formatDate(currentInvoice?.due_date) }}</span>
              </div>
              <div class="flex justify-between mb-1">
                <span class="text-gray-600">Status:</span>
                <span :class="getStatusClass(currentInvoice?.status)" class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ formatStatus(currentInvoice?.status) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Invoice Items -->
          <div class="mb-8">
            <h4 class="font-semibold text-gray-700 mb-2">Invoice Items:</h4>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in currentInvoice?.items" :key="index">
                  <td class="px-4 py-2 text-sm text-gray-900">{{ item.description }}</td>
                  <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ item.quantity }}</td>
                  <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ formatCurrency(item.unit_price) }}</td>
                  <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ formatCurrency(item.total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Invoice Summary -->
          <div class="bg-gray-50 p-4 rounded-md">
            <div class="flex justify-between py-1">
              <span class="text-gray-600">Subtotal:</span>
              <span class="font-medium">{{ formatCurrency(currentInvoice?.subtotal) }}</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-gray-600">Tax (5%):</span>
              <span class="font-medium">{{ formatCurrency(currentInvoice?.tax_amount) }}</span>
            </div>
            <div class="flex justify-between py-2 border-t border-gray-200 mt-1 pt-2">
              <span class="font-semibold text-gray-700">Total:</span>
              <span class="font-semibold text-gray-900">{{ formatCurrency(currentInvoice?.total_amount) }}</span>
            </div>
          </div>

          <!-- Payment Info and Notes -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h4 class="font-semibold text-gray-700 mb-2">Payment Information:</h4>
            <p class="text-gray-600 mb-4">Please make payment via bank transfer to the following account:</p>
            <div class="bg-gray-50 p-4 rounded-md mb-4">
              <p class="text-gray-700"><strong>Bank Name:</strong> Emirates NBD</p>
              <p class="text-gray-700"><strong>Account Name:</strong> AL IBDAL TRADING LLC</p>
              <p class="text-gray-700"><strong>Account Number:</strong> 1234567890</p>
              <p class="text-gray-700"><strong>IBAN:</strong> AE123456789012345678901</p>
              <p class="text-gray-700"><strong>SWIFT Code:</strong> EBILAEAD</p>
            </div>
            <p class="text-gray-600">Thank you for your business. For any queries regarding this invoice, please contact our accounts department.</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button 
            @click="closeViewModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
          >
            Close
          </button>
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
const stats = reactive({
  total: 0,
  paid: 0,
  unpaid: 0,
  overdue: 0
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
  contract_id: '',
  customer_name: '',
  customer_email: '',
  issue_date: format(new Date(), 'yyyy-MM-dd'),
  due_date: format(new Date(Date.now() + 30*24*60*60*1000), 'yyyy-MM-dd'), // 30 days from now
  status: 'unpaid',
  items: [
    { description: '', quantity: 1, unit_price: 0, total: 0 }
  ],
  subtotal: 0,
  tax_amount: 0,
  tax_rate: 0.05, // 5% tax
  total_amount: 0
})

// Fetch invoices from API
const fetchInvoices = async () => {
  loading.value = true
  try {
    const params = { ...filters }
    
    // In a real app, this would be fetched from your API
    // Example: const response = await axios.get('/api/invoices', { params })
    
    // For demonstration purposes, simulate API response with sample data
    setTimeout(() => {
      // Sample data
      const sampleInvoices = [
        {
          id: 1,
          invoice_number: 'INV-2023-001',
          contract_id: 1,
          customer_name: 'Mohammed Ali',
          customer_email: 'mali@example.com',
          issue_date: '2023-09-01',
          due_date: '2023-09-30',
          status: 'paid',
          subtotal: 1500,
          tax_amount: 75,
          total_amount: 1575,
          items: [
            { description: 'Car Rental - Toyota Camry', quantity: 5, unit_price: 300, total: 1500 }
          ]
        },
        {
          id: 2,
          invoice_number: 'INV-2023-002',
          contract_id: 2,
          customer_name: 'Sara Ahmed',
          customer_email: 'sara@example.com',
          issue_date: '2023-09-05',
          due_date: '2023-10-05',
          status: 'unpaid',
          subtotal: 2100,
          tax_amount: 105,
          total_amount: 2205,
          items: [
            { description: 'Car Rental - Mercedes C-Class', quantity: 3, unit_price: 700, total: 2100 }
          ]
        },
        {
          id: 3,
          invoice_number: 'INV-2023-003',
          contract_id: 3,
          customer_name: 'John Smith',
          customer_email: 'john@example.com',
          issue_date: '2023-08-15',
          due_date: '2023-09-15',
          status: 'overdue',
          subtotal: 1800,
          tax_amount: 90,
          total_amount: 1890,
          items: [
            { description: 'Car Rental - BMW X5', quantity: 2, unit_price: 900, total: 1800 }
          ]
        },
        {
          id: 4,
          invoice_number: 'INV-2023-004',
          contract_id: 4,
          customer_name: 'Fatima Hassan',
          customer_email: 'fatima@example.com',
          issue_date: '2023-09-10',
          due_date: '2023-10-10',
          status: 'unpaid',
          subtotal: 1250,
          tax_amount: 62.5,
          total_amount: 1312.5,
          items: [
            { description: 'Car Rental - Honda Accord', quantity: 5, unit_price: 250, total: 1250 }
          ]
        }
      ]

      // Filter by status if specified
      let filteredInvoices = [...sampleInvoices]
      if (filters.status) {
        filteredInvoices = filteredInvoices.filter(invoice => invoice.status === filters.status)
      }

      // Filter by search term if specified
      if (filters.search) {
        const searchTerm = filters.search.toLowerCase()
        filteredInvoices = filteredInvoices.filter(invoice => 
          invoice.invoice_number.toLowerCase().includes(searchTerm) || 
          invoice.customer_name.toLowerCase().includes(searchTerm)
        )
      }

      // Filter by date range if specified
      if (filters.from_date) {
        filteredInvoices = filteredInvoices.filter(invoice => 
          new Date(invoice.issue_date) >= new Date(filters.from_date)
        )
      }
      
      if (filters.to_date) {
        filteredInvoices = filteredInvoices.filter(invoice => 
          new Date(invoice.issue_date) <= new Date(filters.to_date)
        )
      }

      // Update invoices and pagination
      invoices.value = filteredInvoices
      
      // Calculate statistics
      stats.total = sampleInvoices.length
      stats.paid = sampleInvoices
        .filter(invoice => invoice.status === 'paid')
        .reduce((sum, invoice) => sum + invoice.total_amount, 0)
      stats.unpaid = sampleInvoices
        .filter(invoice => invoice.status === 'unpaid')
        .reduce((sum, invoice) => sum + invoice.total_amount, 0)
      stats.overdue = sampleInvoices
        .filter(invoice => invoice.status === 'overdue')
        .reduce((sum, invoice) => sum + invoice.total_amount, 0)

      // Update pagination info
      pagination.current_page = 1
      pagination.last_page = 1
      pagination.total = filteredInvoices.length
      pagination.from = filteredInvoices.length > 0 ? 1 : 0
      pagination.to = filteredInvoices.length
      
      loading.value = false
    }, 500)
    
  } catch (error) {
    console.error('Error fetching invoices:', error)
    loading.value = false
  }
}

// Fetch contracts from API
const fetchContracts = async () => {
  try {
    // In a real app, this would be fetched from your API
    // Example: const response = await axios.get('/api/contracts')
    
    // For demonstration purposes, simulate API response with sample data
    setTimeout(() => {
      contracts.value = [
        { id: 1, contract_number: 'CTR-2023-001', customer_name: 'Mohammed Ali' },
        { id: 2, contract_number: 'CTR-2023-002', customer_name: 'Sara Ahmed' },
        { id: 3, contract_number: 'CTR-2023-003', customer_name: 'John Smith' },
        { id: 4, contract_number: 'CTR-2023-004', customer_name: 'Fatima Hassan' },
      ]
    }, 300)
    
  } catch (error) {
    console.error('Error fetching contracts:', error)
  }
}

// Modal functions
const openCreateModal = () => {
  isEditing.value = false
  resetForm()
  showModal.value = true
}

const openEditModal = (invoice) => {
  isEditing.value = true
  
  // Deep clone the invoice to avoid modifying the original
  form.invoice_number = invoice.invoice_number
  form.contract_id = invoice.contract_id
  form.customer_name = invoice.customer_name
  form.customer_email = invoice.customer_email
  form.issue_date = invoice.issue_date
  form.due_date = invoice.due_date
  form.status = invoice.status
  form.subtotal = invoice.subtotal
  form.tax_amount = invoice.tax_amount
  form.total_amount = invoice.total_amount
  
  // Copy the items array
  form.items = JSON.parse(JSON.stringify(invoice.items || []))
  
  if (form.items.length === 0) {
    addInvoiceItem() // Ensure at least one item
  }
  
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
  form.contract_id = ''
  form.customer_name = ''
  form.customer_email = ''
  form.issue_date = format(new Date(), 'yyyy-MM-dd')
  form.due_date = format(new Date(Date.now() + 30*24*60*60*1000), 'yyyy-MM-dd') // 30 days from now
  form.status = 'unpaid'
  form.items = [
    { description: '', quantity: 1, unit_price: 0, total: 0 }
  ]
  form.subtotal = 0
  form.tax_amount = 0
  form.total_amount = 0
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

const calculateItemTotal = (index) => {
  const item = form.items[index]
  item.total = item.quantity * item.unit_price
  updateInvoiceTotals()
}

const calculateSubtotal = () => {
  return form.items.reduce((sum, item) => sum + (item.total || 0), 0)
}

const calculateTax = () => {
  return calculateSubtotal() * form.tax_rate
}

const updateInvoiceTotals = () => {
  form.subtotal = calculateSubtotal()
  form.tax_amount = calculateTax()
  form.total_amount = form.subtotal + form.tax_amount
}

// Submit form
const submitForm = async () => {
  submitting.value = true
  
  try {
    updateInvoiceTotals() // Ensure totals are up to date
    
    // In a real app, this would submit to your API
    // Example: 
    // if (isEditing.value) {
    //   await axios.put(`/api/invoices/${currentInvoice.value.id}`, form)
    // } else {
    //   await axios.post('/api/invoices', form)
    // }
    
    // For demonstration purposes, simulate API call
    await new Promise(resolve => setTimeout(resolve, 800))
    
    // Update local data
    if (isEditing.value) {
      const index = invoices.value.findIndex(i => i.id === currentInvoice.value.id)
      if (index !== -1) {
        // Replace the invoice in the array
        invoices.value[index] = {
          ...invoices.value[index],
          ...form,
          items: JSON.parse(JSON.stringify(form.items))
        }
      }
    } else {
      // Add new invoice to array
      const newId = Math.max(0, ...invoices.value.map(i => i.id)) + 1
      invoices.value.unshift({
        id: newId,
        ...form,
        items: JSON.parse(JSON.stringify(form.items))
      })
    }
    
    closeModal()
    toast.success(isEditing.value ? 'Invoice updated successfully!' : 'Invoice created successfully!')
    
    // Recalculate statistics
    const paidInvoices = invoices.value.filter(invoice => invoice.status === 'paid')
    const unpaidInvoices = invoices.value.filter(invoice => invoice.status === 'unpaid')
    const overdueInvoices = invoices.value.filter(invoice => invoice.status === 'overdue')
    
    stats.total = invoices.value.length
    stats.paid = paidInvoices.reduce((sum, invoice) => sum + invoice.total_amount, 0)
    stats.unpaid = unpaidInvoices.reduce((sum, invoice) => sum + invoice.total_amount, 0)
    stats.overdue = overdueInvoices.reduce((sum, invoice) => sum + invoice.total_amount, 0)
    
  } catch (error) {
    console.error('Error saving invoice:', error)
    toast.error('An error occurred while saving the invoice. Please try again.')
  } finally {
    submitting.value = false
  }
}

// Delete invoice
const confirmDelete = (invoice) => {
  if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}? This action cannot be undone.`)) {
    deleteInvoice(invoice)
  }
}

const deleteInvoice = async (invoice) => {
  try {
    // In a real app, this would call your API
    // Example: await axios.delete(`/api/invoices/${invoice.id}`)
    
    // For demonstration purposes, simulate API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // Update local data
    invoices.value = invoices.value.filter(i => i.id !== invoice.id)
    
    // Recalculate statistics
    const paidInvoices = invoices.value.filter(i => i.status === 'paid')
    const unpaidInvoices = invoices.value.filter(i => i.status === 'unpaid')
    const overdueInvoices = invoices.value.filter(i => i.status === 'overdue')
    
    stats.total = invoices.value.length
    stats.paid = paidInvoices.reduce((sum, i) => sum + i.total_amount, 0)
    stats.unpaid = unpaidInvoices.reduce((sum, i) => sum + i.total_amount, 0)
    stats.overdue = overdueInvoices.reduce((sum, i) => sum + i.total_amount, 0)
    
    toast.success('Invoice deleted successfully!')
    
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

const downloadInvoicePdf = async (invoice) => {
  if (!invoice?.id) {
    toast.error('Invalid invoice selected')
    return
  }
  
  try {
    toast.loading('Generating PDF...')
    const response = await axios.get(`/invoices/${invoice.id}/pdf`, {
      responseType: 'blob'
    })
    
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
    
    toast.success('PDF downloaded successfully')
  } catch (error) {
    console.error('Error downloading PDF:', error)
    toast.error(error.response?.data?.message || 'Failed to download PDF. Please try again.')
  }
}

const emailInvoice = async (invoice) => {
  if (!invoice?.id) {
    toast.error('Invalid invoice selected')
    return
  }
  
  try {
    toast.loading('Sending email...')
    await axios.post(`/invoices/${invoice.id}/email`, {
      customer_email: invoice.customer_email,
      customer_name: invoice.customer_name,
      invoice_number: invoice.invoice_number
    })
    toast.success('Invoice email sent successfully')
  } catch (error) {
    console.error('Error sending email:', error)
    if (error.response?.status === 404) {
      toast.error('Invoice not found. Please refresh the page and try again.')
    } else {
      toast.error(error.response?.data?.message || 'Failed to email invoice. Please try again.')
    }
  }
}

// Pagination
const changePage = (page) => {
  if (page < 1 || page > pagination.last_page) return
  
  filters.page = page
  fetchInvoices()
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  return format(new Date(dateString), 'MMM dd, yyyy')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-AE', {
    style: 'currency',
    currency: 'AED'
  }).format(amount || 0)
}

const formatStatus = (status) => {
  if (!status) return ''
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusClass = (status) => {
  switch (status) {
    case 'paid':
      return 'bg-green-100 text-green-800'
    case 'unpaid':
      return 'bg-yellow-100 text-yellow-800'
    case 'overdue':
      return 'bg-red-100 text-red-800'
    case 'cancelled':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
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
  fetchContracts()
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
  justify-content: between;
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
  
  .modal-header {
    padding: 1.5rem;
  }
  
  .modal-body {
    padding: 1.5rem;
  }
  
  .modal-actions {
    padding: 1.5rem;
    flex-direction: column;
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
