<template>
  <div class="login-container">
    <div class="login-content">
      <!-- Login Card -->
      <div class="login-card">
        <!-- Header Section -->
        <div class="login-header">
          <div class="company-logo">
            <div class="logo-icon">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
            <h1 class="company-name">AL IBDAL TRADING LLC</h1>
          </div>
          <div class="login-title-section">
            <h2 class="login-title">Welcome Back</h2>
            <p class="login-subtitle">Sign in to your Car Rental Management System</p>
          </div>
        </div>

        <!-- Login Form -->
        <form class="login-form" @submit.prevent="handleLogin">
          <!-- Error Message -->
          <div v-if="error" class="error-banner">
            <div class="error-content">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="error-text">{{ error }}</p>
            </div>
          </div>
          
          <!-- Form Fields -->
          <div class="form-fields">
            <div class="form-group">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-wrapper">
                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                </svg>
                <input
                  id="email"
                  v-model="form.email"
                  name="email"
                  type="email"
                  required
                  class="form-input"
                  placeholder="Enter your email"
                />
              </div>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <div class="input-wrapper">
                <svg class="input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <input
                  id="password"
                  v-model="form.password"
                  name="password"
                  type="password"
                  required
                  class="form-input"
                  placeholder="Enter your password"
                />
              </div>
            </div>
          </div>

          <!-- Login Button -->
          <button
            type="submit"
            :disabled="loading"
            class="login-button"
          >
            <svg v-if="loading" class="animate-spin w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <!-- Footer -->
        <div class="login-footer">
          <p class="footer-text">Secure access to your rental management dashboard</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  
  const result = await authStore.login(form.value)
  
  if (result.success) {
    // Redirect based on user role
    if (authStore.user?.role === 'partner') {
      router.push('/partner-dashboard')
    } else {
      router.push('/dashboard')
    }
  } else {
    error.value = result.error
  }
  
  loading.value = false
}
</script>

<style scoped>
/* Login Container - Full viewport with gradient background */
.login-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

.login-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%" r="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.1)"/><stop offset="100%" style="stop-color:rgba(255,255,255,0.05)"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="300" cy="800" r="120" fill="url(%23a)"/></svg>') no-repeat;
  background-size: 100% 100%;
  opacity: 0.3;
}

.login-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 28rem;
}

/* Login Card - Modern card design with blur effect */
.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 1rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.login-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

/* Header Section */
.login-header {
  margin-bottom: 2rem;
  text-align: center;
}

.company-logo {
  margin-bottom: 1.5rem;
}

.logo-icon {
  margin: 0 auto 0.75rem;
  width: 4rem;
  height: 4rem;
  background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.logo-icon svg {
  color: white;
}

.company-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-title-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.login-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.login-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}

/* Form Styling */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.error-banner {
  background: linear-gradient(135deg, rgba(248, 113, 113, 0.1) 0%, rgba(239, 68, 68, 0.1) 100%);
  border: 1px solid #fecaca;
  border-radius: 0.75rem;
  padding: 1rem;
}

.error-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.error-content svg {
  color: #ef4444;
  flex-shrink: 0;
}

.error-text {
  font-size: 0.875rem;
  color: #b91c1c;
  font-weight: 500;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.25rem;
  height: 1.25rem;
  color: #9ca3af;
  pointer-events: none;
  transition: color 0.2s ease;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  background: rgba(249, 250, 251, 0.5);
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  color: #111827;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
  border-color: #3b82f6;
  background: white;
}

.form-input:focus + .input-icon {
  color: #3b82f6;
}

.form-input:hover {
  background: #f9fafb;
}

/* Login Button */
.login-button {
  width: 100%;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
  color: white;
  font-weight: 600;
  border-radius: 0.75rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
  transform: translateY(0);
}

.login-button:hover:not(:disabled) {
  background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
  transform: translateY(-1px);
}

.login-button:active:not(:disabled) {
  transform: translateY(0) scale(0.98);
}

.login-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.login-button:focus {
  outline: 2px solid rgba(59, 130, 246, 0.5);
  outline-offset: 2px;
}

/* Footer */
.login-footer {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f3f4f6;
  text-align: center;
}

.footer-text {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Animation for loading spinner */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 640px) {
  .login-container {
    padding: 1rem;
  }
  
  .login-card {
    padding: 1.5rem;
  }
  
  .company-name {
    font-size: 1.125rem;
  }
  
  .login-title {
    font-size: 1.25rem;
  }
}

/* Enhanced focus states for accessibility */
.form-input:focus,
.login-button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Smooth animations */
* {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>