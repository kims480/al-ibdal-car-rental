<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Invoices Management</h1>
        <p class="text-gray-600 mt-1">Manage and track all customer invoices</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Create Invoice
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border mb-6 p-4">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Invoice #, customer name..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="">All Statuses</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
            <option value="overdue">Overdue</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input 
            v-model="filters.from_date"
            type="date" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input 
            v-model="filters.to_date"
            type="date" 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
        </div>
        <div class="flex items-end">
          <button 
            @click="fetchInvoices"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition-colors"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="text-gray-500 text-sm mb-1">Total Invoices</div>
        <div class="text-2xl font-semibold">{{ stats.total || 0 }}</div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="text-gray-500 text-sm mb-1">Paid Amount</div>
        <div class="text-2xl font-semibold text-emerald-600">{{ formatCurrency(stats.paid || 0) }}</div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="text-gray-500 text-sm mb-1">Unpaid Amount</div>
        <div class="text-2xl font-semibold text-orange-500">{{ formatCurrency(stats.unpaid || 0) }}</div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="text-gray-500 text-sm mb-1">Overdue Amount</div>
        <div class="text-2xl font-semibold text-red-500">{{ formatCurrency(stats.overdue || 0) }}</div>
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
                <div class="flex space-x-3">
                  <button 
                    @click="viewInvoice(invoice)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    View
                  </button>
                  <button 
                    @click="openEditModal(invoice)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                  <button 
                    @click="confirmDelete(invoice)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
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
    alert(isEditing.value ? 'Invoice updated successfully!' : 'Invoice created successfully!')
    
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
    alert('An error occurred while saving the invoice. Please try again.')
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
    
    alert('Invoice deleted successfully!')
    
  } catch (error) {
    console.error('Error deleting invoice:', error)
    alert('An error occurred while deleting the invoice. Please try again.')
  }
}

// Print invoice
const printInvoice = () => {
  if (!invoiceToPrint.value) return
  
  const printContents = invoiceToPrint.value.innerHTML
  const originalContents = document.body.innerHTML
  
  // Open a new window with just the invoice content
  const printWindow = window.open('', '_blank')
  printWindow.document.write(`
    <html>
      <head>
        <title>Invoice #${currentInvoice.value.invoice_number}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          table { width: 100%; border-collapse: collapse; }
          th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
          th { background-color: #f3f4f6; }
        </style>
      </head>
      <body>
        ${printContents}
      </body>
    </html>
  `)
  
  printWindow.document.close()
  printWindow.focus()
  printWindow.print()
  printWindow.close()
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
