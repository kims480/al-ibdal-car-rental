<template>
  <div class="car-expenses-tracking">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-title">Car Expenses & Revenue Tracking</h1>
      <p class="text-muted">Track maintenance expenses and rental revenue for each car</p>
    </div>

    <!-- Filter Controls -->
    <div class="bg-surface p-6 rounded-lg shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-title mb-2">Select Car</label>
          <select v-model="selectedCar" @change="fetchData" class="w-full the-input">
            <option value="">All Cars</option>
            <option v-for="car in cars" :key="car.id" :value="car.id">
              {{ car.make }} {{ car.model }} ({{ car.license_plate }})
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-title mb-2">Select Month</label>
          <input v-model="selectedMonth" @change="fetchData" type="month" class="w-full the-input" />
        </div>
        <div class="flex items-end">
          <button @click="showAddExpenseModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Add Expense
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-surface p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-title mb-2">Total Revenue</h3>
        <p class="text-2xl font-bold text-green-600">OMR {{ totalRevenue.toFixed(2) }}</p>
      </div>
      <div class="bg-surface p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-title mb-2">Total Expenses</h3>
        <p class="text-2xl font-bold text-red-600">OMR {{ totalExpenses.toFixed(2) }}</p>
      </div>
      <div class="bg-surface p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-title mb-2">Net Profit</h3>
        <p class="text-2xl font-bold" :class="netProfit >= 0 ? 'text-green-600' : 'text-red-600'">
          OMR {{ netProfit.toFixed(2) }}
        </p>
      </div>
    </div>

    <!-- Car-wise Revenue Table -->
    <div class="bg-surface rounded-lg shadow mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-title">Revenue by Car</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Car</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rentals Count</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expenses</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="car in carRevenues" :key="car.id">
              <td class="px-6 py-4 text-sm text-title">{{ car.make }} {{ car.model }}</td>
              <td class="px-6 py-4 text-sm text-muted">{{ car.rentalsCount }}</td>
              <td class="px-6 py-4 text-sm text-green-600">OMR {{ car.revenue.toFixed(2) }}</td>
              <td class="px-6 py-4 text-sm text-red-600">OMR {{ car.expenses.toFixed(2) }}</td>
              <td class="px-6 py-4 text-sm" :class="car.net >= 0 ? 'text-green-600' : 'text-red-600'">
                OMR {{ car.net.toFixed(2) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Expenses List -->
    <div class="bg-surface rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-title">Expenses Details</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Car</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="expense in expenses" :key="expense.id">
              <td class="px-6 py-4 text-sm text-title">{{ formatDate(expense.expense_date) }}</td>
              <td class="px-6 py-4 text-sm text-muted">{{ getCarName(expense.car_id) }}</td>
              <td class="px-6 py-4 text-sm text-muted">{{ expense.expense_type }}</td>
              <td class="px-6 py-4 text-sm text-red-600">OMR {{ expense.amount.toFixed(2) }}</td>
              <td class="px-6 py-4 text-sm text-muted">{{ expense.description || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Expense Modal -->
    <div v-if="showAddExpenseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mb-4">
          <h3 class="text-lg font-bold text-gray-900">Add Car Expense</h3>
        </div>
        <form @submit.prevent="addExpense" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Car *</label>
            <select v-model="expenseForm.car_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
              <option value="">Select Car</option>
              <option v-for="car in cars" :key="car.id" :value="car.id">
                {{ car.make }} {{ car.model }} ({{ car.license_plate }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Expense Type *</label>
            <select v-model="expenseForm.expense_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
              <option value="">Select Type</option>
              <option value="maintenance">Maintenance</option>
              <option value="repair">Repair</option>
              <option value="insurance">Insurance</option>
              <option value="fuel">Fuel</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (OMR) *</label>
            <input v-model.number="expenseForm.amount" type="number" step="0.01" min="0" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
            <input v-model="expenseForm.expense_date" type="date" required 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="expenseForm.description" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
          </div>
          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeExpenseModal" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md">
              Cancel
            </button>
            <button type="submit" :disabled="submitting" 
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50">
              {{ submitting ? 'Saving...' : 'Add Expense' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const cars = ref([])
const expenses = ref([])
const carRevenues = ref([])
const selectedCar = ref('')
const selectedMonth = ref(new Date().toISOString().slice(0, 7)) // Current month
const showAddExpenseModal = ref(false)
const submitting = ref(false)

const expenseForm = ref({
  car_id: '',
  expense_type: '',
  amount: 0,
  description: '',
  expense_date: new Date().toISOString().slice(0, 10)
})

const totalRevenue = computed(() => 
  carRevenues.value.reduce((sum, car) => sum + car.revenue, 0)
)

const totalExpenses = computed(() => 
  expenses.value.reduce((sum, expense) => sum + expense.amount, 0)
)

const netProfit = computed(() => totalRevenue.value - totalExpenses.value)

const fetchData = async () => {
  await Promise.all([
    fetchExpenses(),
    fetchCarRevenues()
  ])
}

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

const fetchExpenses = async () => {
  try {
    const params = { month: selectedMonth.value }
    if (selectedCar.value) params.car_id = selectedCar.value
    
    const response = await axios.get('/car-expenses', { params })
    if (response.data.success) {
      expenses.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching expenses:', error)
  }
}

const fetchCarRevenues = async () => {
  try {
    const revenues = []
    for (const car of cars.value) {
      const params = { car_id: car.id, month: selectedMonth.value }
      const response = await axios.get('/car-expenses/revenue', { params })
      if (response.data.success) {
        const carExpenses = expenses.value
          .filter(e => e.car_id === car.id)
          .reduce((sum, e) => sum + e.amount, 0)
        
        revenues.push({
          id: car.id,
          make: car.make,
          model: car.model,
          revenue: response.data.revenue || 0,
          expenses: carExpenses,
          net: (response.data.revenue || 0) - carExpenses,
          rentalsCount: 0 // Would need additional API call for actual count
        })
      }
    }
    carRevenues.value = revenues
  } catch (error) {
    console.error('Error fetching car revenues:', error)
  }
}

const addExpense = async () => {
  submitting.value = true
  try {
    const response = await axios.post('/car-expenses', expenseForm.value)
    if (response.data.success) {
      closeExpenseModal()
      await fetchData()
      alert('Expense added successfully!')
    }
  } catch (error) {
    console.error('Error adding expense:', error)
    alert('Error adding expense')
  } finally {
    submitting.value = false
  }
}

const closeExpenseModal = () => {
  showAddExpenseModal.value = false
  expenseForm.value = {
    car_id: '',
    expense_type: '',
    amount: 0,
    description: '',
    expense_date: new Date().toISOString().slice(0, 10)
  }
}

const getCarName = (carId) => {
  const car = cars.value.find(c => c.id === carId)
  return car ? `${car.make} ${car.model}` : 'Unknown Car'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

onMounted(async () => {
  await fetchCars()
  await fetchData()
})
</script>