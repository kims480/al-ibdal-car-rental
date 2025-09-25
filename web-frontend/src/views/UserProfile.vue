<template>
  <div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold text-title mb-4">User Profile</h1>
    <form @submit.prevent="saveProfile" class="space-y-6">
      <div>
        <label class="block text-sm font-medium text-muted mb-1">Name</label>
        <input v-model="form.name" type="text" class="the-input" disabled />
      </div>
      <div>
        <label class="block text-sm font-medium text-muted mb-1">Email</label>
        <input v-model="form.email" type="email" class="the-input" disabled />
      </div>
      <div>
        <label class="block text-sm font-medium text-muted mb-1">Phone</label>
        <input v-model="form.phone" type="tel" class="the-input" disabled />
      </div>
      <div>
        <label class="block text-sm font-medium text-muted mb-1">Favorite Theme</label>
        <select v-model="form.theme" class="the-input">
          <option value="light">Light</option>
          <option value="dark-blue">Dark Blue</option>
          <option value="dark-blue-neon">Dark Blue NEON</option>
        </select>
      </div>
      <div class="flex justify-end">
        <button type="submit" class="the-btn">Save</button>
      </div>
    </form>
    <div v-if="message" class="mt-4 text-green-600">{{ message }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import { useUIStore } from '../stores/ui'

const auth = useAuthStore()
const ui = useUIStore()
const form = ref({
  name: '',
  email: '',
  phone: '',
  theme: ui.theme
})
const message = ref('')

onMounted(() => {
  if (auth.user) {
    form.value.name = auth.user.name
    form.value.email = auth.user.email
    form.value.phone = auth.user.phone
    form.value.theme = auth.user.theme || ui.theme
  }
})

const saveProfile = async () => {
  try {
    // Save theme to backend
    await axios.put(`/users/${auth.user.id}`, { theme: form.value.theme })
    // Update local theme
    ui.setTheme(form.value.theme)
    message.value = 'Profile updated and theme saved!'
    // Optionally update auth.user
    auth.user.theme = form.value.theme
    localStorage.setItem('user', JSON.stringify(auth.user))
  } catch (e) {
    message.value = e.response?.data?.message || 'Failed to save profile.'
  }
}
</script>
