<template>
  <div class="customer-container">
    <!-- Header Section -->
    <div class="customer-header">
      <div class="header-content">
        <div class="welcome-section">
          <h1 class="customer-title">Add New Customer</h1>
          <p class="customer-subtitle">Register a new customer with all required documentation</p>
        </div>
        <div class="header-icon">
          <div class="customer-avatar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Form -->
    <div class="customer-content">
      <div class="customer-card">
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

        <!-- Messages -->
        <div v-if="successMessage || errorMessage" class="message-container">
          <div v-if="successMessage" class="success-message">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ successMessage }}
          </div>
          <div v-if="errorMessage" class="error-message">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ errorMessage }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

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

const successMessage = ref('')
const errorMessage = ref('')
const submitting = ref(false)

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
  
  successMessage.value = ''
  errorMessage.value = ''
}

async function submitForm() {
  try {
    submitting.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    const formData = new FormData()
    
    // Add all form fields to FormData
    for (const key in form.value) {
      if (form.value[key] !== null) {
        formData.append(key, form.value[key])
      }
    }
    
    // Use axios with correct API endpoint (without /api prefix since axios base URL includes it)
    const response = await axios.post('/customers', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
    
    if (response.data) {
      successMessage.value = 'Customer added successfully!'
      resetForm()
      
      // Clear success message after 5 seconds
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
    }
  } catch (error) {
    console.error('Error submitting form:', error)
    
    if (error.response) {
      // Server responded with error
      errorMessage.value = error.response.data.message || 'Failed to add customer. Please try again.'
    } else if (error.request) {
      // Request was made but no response
      errorMessage.value = 'Network error. Please check your connection and try again.'
    } else {
      // Something else happened
      errorMessage.value = 'An unexpected error occurred. Please try again.'
    }
    
    // Clear error message after 7 seconds
    setTimeout(() => {
      errorMessage.value = ''
    }, 7000)
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
/* Customer Container */
.customer-container {
  min-height: 100vh;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Header Styles */
.customer-header {
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

.customer-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.customer-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.customer-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #22c55e 0%, #86efac 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

/* Customer Content */
.customer-content {
  max-width: 1200px;
  margin: 0 auto;
}

.customer-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
  border: 1px solid rgba(229, 231, 235, 0.5);
  border-radius: 1rem;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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

.form-input::placeholder {
  color: #9ca3af;
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

.file-upload-display:hover span {
  color: #6366f1;
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

/* Message Styles */
.message-container {
  margin-top: 1rem;
}

.success-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(134, 239, 172, 0.1) 100%);
  border: 1px solid rgba(34, 197, 94, 0.2);
  border-radius: 0.5rem;
  color: #059669;
  font-size: 0.875rem;
  font-weight: 500;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(252, 165, 165, 0.1) 100%);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 0.5rem;
  color: #dc2626;
  font-size: 0.875rem;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .customer-container {
    padding: 1rem;
  }
  
  .customer-header {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .form-grid,
  .documents-grid {
    grid-template-columns: 1fr;
  }
  
  .customer-title {
    font-size: 1.25rem;
  }
  
  .form-actions {
    flex-direction: column;
  }
}

@media (max-width: 1024px) {
  .form-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .documents-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Animations */
.customer-card {
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

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
