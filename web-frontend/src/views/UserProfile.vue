<template>
  <div class="profile-container">
    <!-- Header Section -->
    <div class="profile-header">
      <div class="header-content">
        <div class="welcome-section">
          <h1 class="profile-title">User Profile</h1>
          <p class="profile-subtitle">Manage your account settings and preferences</p>
        </div>
        <div class="header-icon">
          <div class="profile-avatar">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="profile-content">
      <div class="profile-card">
        <form @submit.prevent="saveProfile" class="profile-form">
          <div class="form-section">
            <h3 class="section-title">Personal Information</h3>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  class="form-input" 
                  :disabled="!canEditProfile"
                  :class="{ 'disabled': !canEditProfile }"
                />
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input 
                  v-model="form.email" 
                  type="email" 
                  class="form-input" 
                  :disabled="!canEditEmail"
                  :class="{ 'disabled': !canEditEmail }"
                />
                <p v-if="!canEditEmail" class="form-help">Only administrators can modify email addresses</p>
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input 
                  v-model="form.phone" 
                  type="tel" 
                  class="form-input" 
                  :disabled="!canEditProfile"
                  :class="{ 'disabled': !canEditProfile }"
                />
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="section-title">
              <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
              </svg>
              Theme Preferences
            </h3>
            <p class="section-description">Choose your preferred color theme for the dashboard interface</p>
            
            <div class="theme-selector">
              <div 
                v-for="themeOption in themes" 
                :key="themeOption.value"
                @click="selectTheme(themeOption.value)"
                :class="['theme-option', { 'theme-active': form.theme === themeOption.value }]"
              >
                <div class="theme-preview" :data-theme="themeOption.value">
                  <div class="preview-header"></div>
                  <div class="preview-sidebar"></div>
                  <div class="preview-content">
                    <div class="preview-card"></div>
                    <div class="preview-card small"></div>
                  </div>
                </div>
                <div class="theme-info">
                  <div class="theme-icon">{{ themeOption.emoji }}</div>
                  <h4 class="theme-name">{{ themeOption.label }}</h4>
                  <p class="theme-description">{{ themeOption.colors }}</p>
                </div>
                <div v-if="form.theme === themeOption.value" class="theme-selected">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
            </div>
            
            <div class="theme-actions">
              <button type="button" @click="resetTheme" class="theme-action-btn secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset to Default
              </button>
              <button type="button" @click="applyTheme" class="theme-action-btn primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Apply Theme
              </button>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="save-btn" :disabled="saving">
              <svg v-if="saving" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>

        <!-- Success/Error Messages -->
        <div v-if="message" class="message-container">
          <div class="success-message">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ message }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
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
const saving = ref(false)

// Theme options matching the DashboardLayout
const themes = ref([
  { 
    label: 'Ocean Breeze', 
    value: 'ocean-breeze', 
    colors: 'Blue & Teal',
    emoji: '🌊',
    description: 'Professional blue and teal theme'
  },
  { 
    label: 'Cherry Blossom', 
    value: 'cherry-blossom', 
    colors: 'Pink & Purple',
    emoji: '🌸',
    description: 'Elegant pink and purple theme'
  },
  { 
    label: 'Golden Sunset', 
    value: 'golden-sunset', 
    colors: 'Orange & Yellow',
    emoji: '🌅',
    description: 'Warm orange and yellow theme'
  },
  { 
    label: 'Dark Blue Neon', 
    value: 'dark-blue-neon', 
    colors: 'Dark & Neon Blue',
    emoji: '🌙',
    description: 'Modern dark theme with neon accents'
  }
])

// Check if user can edit email (only admin can edit emails)
const canEditEmail = computed(() => {
  return auth.user && auth.user.role === 'admin'
})

// Check if user can edit profile (not customer or restricted roles)
const canEditProfile = computed(() => {
  return auth.user && ['admin', 'branch_manager'].includes(auth.user.role)
})

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
    saving.value = true
    
    // Prepare update data based on permissions
    const updateData = { theme: form.value.theme }
    
    if (canEditProfile.value) {
      updateData.name = form.value.name
      updateData.phone = form.value.phone
    }
    
    if (canEditEmail.value) {
      updateData.email = form.value.email
    }
    
    // Save profile to backend
    await axios.put(`/users/${auth.user.id}`, updateData)
    
    // Update local theme
    ui.setTheme(form.value.theme)
    
    // Update auth user data
    if (canEditProfile.value || canEditEmail.value) {
      auth.user = { ...auth.user, ...updateData }
      localStorage.setItem('user', JSON.stringify(auth.user))
    }
    
    message.value = 'Profile updated successfully!'
    
    // Clear message after 3 seconds
    setTimeout(() => {
      message.value = ''
    }, 3000)
  } catch (e) {
    message.value = e.response?.data?.message || 'Failed to save profile.'
  } finally {
    saving.value = false
  }
}

// Theme selection functions
const selectTheme = (themeValue) => {
  form.value.theme = themeValue
}

const resetTheme = () => {
  form.value.theme = 'ocean-breeze'
  ui.setTheme('ocean-breeze')
  message.value = 'Theme reset to Ocean Breeze'
  setTimeout(() => {
    message.value = ''
  }, 3000)
}

const applyTheme = () => {
  ui.setTheme(form.value.theme)
  message.value = `Theme applied: ${themes.value.find(t => t.value === form.value.theme)?.label}`
  setTimeout(() => {
    message.value = ''
  }, 3000)
}
</script>

<style scoped>
/* Profile Container */
.profile-container {
  min-height: 100vh;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Header Styles */
.profile-header {
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

.profile-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.profile-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.profile-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

/* Profile Content */
.profile-content {
  max-width: 800px;
  margin: 0 auto;
}

.profile-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
  border: 1px solid rgba(229, 231, 235, 0.5);
  border-radius: 1rem;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.profile-form {
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
  display: flex;
  align-items: center;
}

.section-description {
  color: #6b7280;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

/* Theme Selector Styles */
.theme-selector {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
  margin: 1rem 0;
}

.theme-option {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
  position: relative;
  overflow: hidden;
}

.theme-option:hover {
  border-color: #6366f1;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.15);
}

.theme-option.theme-active {
  border-color: #6366f1;
  background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 69, 19, 0.05) 100%);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.2);
}

.theme-preview {
  height: 80px;
  border-radius: 8px;
  margin-bottom: 0.75rem;
  position: relative;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

/* Theme Preview Styles */
.theme-preview[data-theme="ocean-breeze"] {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #e0f2fe 100%);
}

.theme-preview[data-theme="cherry-blossom"] {
  background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #faf5ff 100%);
}

.theme-preview[data-theme="golden-sunset"] {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fed7aa 100%);
}

.theme-preview[data-theme="dark-blue-neon"] {
  background: linear-gradient(135deg, #0a0f1c 0%, #1a1f2e 50%, #2a2f3e 100%);
}

.preview-header {
  height: 16px;
  background: rgba(255, 255, 255, 0.8);
  margin: 4px;
  border-radius: 4px;
}

.theme-preview[data-theme="dark-blue-neon"] .preview-header {
  background: rgba(42, 47, 62, 0.8);
}

.preview-sidebar {
  position: absolute;
  left: 4px;
  top: 24px;
  bottom: 4px;
  width: 24px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 4px;
}

.theme-preview[data-theme="dark-blue-neon"] .preview-sidebar {
  background: rgba(16, 20, 31, 0.8);
}

.preview-content {
  position: absolute;
  right: 4px;
  top: 24px;
  bottom: 4px;
  left: 32px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.preview-card {
  background: rgba(255, 255, 255, 0.7);
  border-radius: 4px;
  flex: 1;
}

.theme-preview[data-theme="dark-blue-neon"] .preview-card {
  background: rgba(42, 47, 62, 0.6);
}

.preview-card.small {
  flex: 0.5;
}

.theme-info {
  text-align: center;
}

.theme-icon {
  font-size: 1.5rem;
  margin-bottom: 0.25rem;
}

.theme-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.25rem 0;
}

.theme-description {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
}

.theme-selected {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.theme-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  margin-top: 1rem;
}

.theme-action-btn {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
  border: none;
}

.theme-action-btn.secondary {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.theme-action-btn.secondary:hover {
  background: #e5e7eb;
}

.theme-action-btn.primary {
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

.theme-action-btn.primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.form-group {
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

.form-input.disabled {
  background: #f9fafb;
  color: #6b7280;
  cursor: not-allowed;
}

.form-help {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.save-btn {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

.save-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

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

/* Responsive Design */
@media (max-width: 768px) {
  .profile-container {
    padding: 1rem;
  }
  
  .profile-header {
    padding: 1rem;
  }
  
  .header-content {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .profile-title {
    font-size: 1.25rem;
  }
  
  .theme-selector {
    grid-template-columns: 1fr;
  }
  
  .theme-actions {
    flex-direction: column;
  }
  
  .theme-action-btn {
    justify-content: center;
  }
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.profile-card {
  animation: fadeIn 0.6s ease-out;
}
</style>
