<template>
  <div class="customer-form">
    <h2>Add New Customer</h2>
    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input v-model="form.name" id="name" type="text" required />
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input v-model="form.phone" id="phone" type="tel" required />
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input v-model="form.email" id="email" type="email" required />
      </div>
      <div class="form-group">
        <label for="city">City</label>
        <input v-model="form.city" id="city" type="text" required />
      </div>
      <div class="form-group">
        <label for="civilId">Civil ID Number</label>
        <input v-model="form.civilId" id="civilId" type="text" required />
      </div>
      <div class="form-group">
        <label for="licenseId">License ID Number</label>
        <input v-model="form.licenseId" id="licenseId" type="text" required />
      </div>
      <div class="form-group">
        <label for="civilIdFront">Civil ID Photo (Front)</label>
        <input id="civilIdFront" type="file" accept="image/*" @change="handleFileChange('civilIdFront', $event)" required />
      </div>
      <div class="form-group">
        <label for="civilIdBack">Civil ID Photo (Back)</label>
        <input id="civilIdBack" type="file" accept="image/*" @change="handleFileChange('civilIdBack', $event)" required />
      </div>
      <div class="form-group">
        <label for="licenseFront">License Photo (Front)</label>
        <input id="licenseFront" type="file" accept="image/*" @change="handleFileChange('licenseFront', $event)" required />
      </div>
      <div class="form-group">
        <label for="licenseBack">License Photo (Back)</label>
        <input id="licenseBack" type="file" accept="image/*" @change="handleFileChange('licenseBack', $event)" required />
      </div>
      <button type="submit">Add Customer</button>
    </form>
    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

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

function handleFileChange(field, event) {
  form.value[field] = event.target.files[0]
}

async function submitForm() {
  try {
    const formData = new FormData()
    for (const key in form.value) {
      formData.append(key, form.value[key])
    }
    // Replace with your API endpoint
    const response = await fetch('/api/customers', {
      method: 'POST',
      body: formData,
      headers: {
        // 'Content-Type': 'multipart/form-data' is set automatically by browser
      },
    })
    const result = await response.json()
    if (result.success) {
      successMessage.value = 'Customer added successfully.'
      errorMessage.value = ''
      form.value = {
        name: '', phone: '', email: '', city: '', civilId: '', licenseId: '', civilIdFront: null, civilIdBack: null, licenseFront: null, licenseBack: null
      }
    } else {
      errorMessage.value = result.message || 'Failed to add customer.'
      successMessage.value = ''
    }
  } catch (err) {
    errorMessage.value = 'Error submitting form.'
    successMessage.value = ''
  }
}
</script>

<style scoped>
.customer-form {
  max-width: 500px;
  margin: 0 auto;
  background: var(--bg);
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.form-group {
  margin-bottom: 1rem;
}
label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}
input[type="text"], input[type="email"], input[type="tel"], input[type="file"] {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}
button {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}
.success-message {
  color: #16a34a;
  margin-top: 1rem;
}
.error-message {
  color: #dc2626;
  margin-top: 1rem;
}
</style>
