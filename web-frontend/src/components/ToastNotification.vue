<template>
  <teleport to="body">
    <transition-group 
      name="toast" 
      tag="div" 
      class="toast-container"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'toast-item',
          `toast-${toast.type}`,
          { 'toast-dismissible': toast.dismissible }
        ]"
        @click="toast.dismissible && removeToast(toast.id)"
      >
        <!-- Toast Icon -->
        <div class="toast-icon">
          <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <svg v-else-if="toast.type === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <svg v-else-if="toast.type === 'info'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>

        <!-- Toast Content -->
        <div class="toast-content">
          <div class="toast-title" v-if="toast.title">{{ toast.title }}</div>
          <div class="toast-message">{{ toast.message }}</div>
        </div>

        <!-- Toast Actions -->
        <div class="toast-actions" v-if="toast.actions && toast.actions.length">
          <button
            v-for="action in toast.actions"
            :key="action.label"
            :class="['toast-action-btn', action.style || 'primary']"
            @click.stop="handleAction(toast.id, action)"
          >
            {{ action.label }}
          </button>
        </div>

        <!-- Close Button -->
        <button 
          v-if="toast.dismissible"
          class="toast-close"
          @click.stop="removeToast(toast.id)"
          aria-label="Close notification"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <!-- Progress Bar -->
        <div 
          v-if="toast.duration > 0"
          class="toast-progress"
          :style="{ animationDuration: `${toast.duration}ms` }"
        ></div>
      </div>
    </transition-group>
  </teleport>
</template>

<script>
import { inject } from 'vue'

export default {
  name: 'ToastNotification',
  setup() {
    const { toasts, removeToast } = inject('toast')

    const handleAction = (toastId, action) => {
      if (action.handler) {
        action.handler()
      }
      if (action.close !== false) {
        removeToast(toastId)
      }
    }

    return {
      toasts,
      removeToast,
      handleAction
    }
  }
}
</script>

<style scoped>
/* Toast Container */
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-width: 420px;
  pointer-events: none;
}

/* Toast Item */
.toast-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 
    0 10px 25px rgba(0, 0, 0, 0.1),
    0 4px 10px rgba(0, 0, 0, 0.05),
    0 0 0 1px rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(10px);
  pointer-events: auto;
  position: relative;
  overflow: hidden;
  min-width: 300px;
  max-width: 420px;
  transform: translateX(0);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-dismissible {
  cursor: pointer;
}

.toast-dismissible:hover {
  transform: translateX(-4px);
  box-shadow: 
    0 20px 40px rgba(0, 0, 0, 0.15),
    0 8px 16px rgba(0, 0, 0, 0.1),
    0 0 0 1px rgba(0, 0, 0, 0.05);
}

/* Toast Types */
.toast-success {
  border-left: 4px solid #10b981;
}

.toast-success .toast-icon {
  color: #10b981;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%);
}

.toast-error {
  border-left: 4px solid #ef4444;
}

.toast-error .toast-icon {
  color: #ef4444;
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
}

.toast-warning {
  border-left: 4px solid #f59e0b;
}

.toast-warning .toast-icon {
  color: #f59e0b;
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
}

.toast-info {
  border-left: 4px solid #3b82f6;
}

.toast-info .toast-icon {
  color: #3b82f6;
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
}

/* Toast Icon */
.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 0.125rem;
}

/* Toast Content */
.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
  line-height: 1.25;
}

.toast-message {
  font-size: 0.875rem;
  color: #4b5563;
  line-height: 1.4;
  word-wrap: break-word;
}

/* Toast Actions */
.toast-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.75rem;
  margin-left: -0.25rem;
}

.toast-action-btn {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.toast-action-btn.primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.toast-action-btn.primary:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  transform: translateY(-1px);
}

.toast-action-btn.secondary {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.toast-action-btn.secondary:hover {
  background: #f9fafb;
  color: #374151;
}

/* Close Button */
.toast-close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  background: transparent;
  border: none;
  border-radius: 0.375rem;
  color: #9ca3af;
  cursor: pointer;
  transition: all 0.2s ease;
  opacity: 0;
}

.toast-item:hover .toast-close {
  opacity: 1;
}

.toast-close:hover {
  background: rgba(0, 0, 0, 0.05);
  color: #6b7280;
}

/* Progress Bar */
.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 2px;
  background: currentColor;
  border-radius: 0 0 0.75rem 0.75rem;
  animation: toast-progress linear forwards;
  opacity: 0.3;
}

@keyframes toast-progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Toast Transitions */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-move {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile Responsive */
@media (max-width: 640px) {
  .toast-container {
    left: 1rem;
    right: 1rem;
    max-width: none;
  }

  .toast-item {
    min-width: auto;
    max-width: none;
  }

  .toast-enter-from,
  .toast-leave-to {
    transform: translateY(-100%) scale(0.95);
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .toast-item {
    background: #1f2937;
    border-color: #374151;
    box-shadow: 
      0 10px 25px rgba(0, 0, 0, 0.3),
      0 4px 10px rgba(0, 0, 0, 0.2),
      0 0 0 1px rgba(255, 255, 255, 0.1);
  }

  .toast-title {
    color: #f9fafb;
  }

  .toast-message {
    color: #d1d5db;
  }

  .toast-close {
    color: #9ca3af;
  }

  .toast-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #d1d5db;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .toast-enter-active,
  .toast-leave-active,
  .toast-move {
    transition: none;
  }

  .toast-progress {
    animation: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .toast-item {
    border: 2px solid currentColor;
  }
}
</style>