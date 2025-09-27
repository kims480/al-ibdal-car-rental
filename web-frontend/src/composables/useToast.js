import { ref, reactive } from 'vue'

// Global toast state
const toasts = ref([])
let toastId = 0

// Toast types
export const TOAST_TYPES = {
  SUCCESS: 'success',
  ERROR: 'error',
  WARNING: 'warning',
  INFO: 'info'
}

// Default options
const DEFAULT_OPTIONS = {
  type: TOAST_TYPES.INFO,
  title: '',
  message: '',
  duration: 5000,
  dismissible: true,
  actions: [],
  position: 'top-right',
  persistent: false
}

/**
 * Toast composable for managing global toast notifications
 */
export function useToast() {
  
  /**
   * Add a new toast notification
   * @param {string|object} messageOrOptions - Toast message or options object
   * @param {object} options - Additional options if first param is string
   * @returns {number} Toast ID
   */
  const addToast = (messageOrOptions, options = {}) => {
    const toastOptions = typeof messageOrOptions === 'string' 
      ? { ...DEFAULT_OPTIONS, message: messageOrOptions, ...options }
      : { ...DEFAULT_OPTIONS, ...messageOrOptions }

    const toast = {
      id: ++toastId,
      ...toastOptions,
      createdAt: Date.now()
    }

    toasts.value.push(toast)

    // Auto-remove toast if duration is set and not persistent
    if (toast.duration > 0 && !toast.persistent) {
      setTimeout(() => {
        removeToast(toast.id)
      }, toast.duration)
    }

    return toast.id
  }

  /**
   * Remove a toast by ID
   * @param {number} id - Toast ID
   */
  const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  /**
   * Remove all toasts
   */
  const clearToasts = () => {
    toasts.value = []
  }

  /**
   * Update an existing toast
   * @param {number} id - Toast ID
   * @param {object} updates - Properties to update
   */
  const updateToast = (id, updates) => {
    const toast = toasts.value.find(t => t.id === id)
    if (toast) {
      Object.assign(toast, updates)
    }
  }

  // Convenience methods for different toast types
  
  /**
   * Show success toast
   * @param {string|object} messageOrOptions - Message or options
   * @param {object} options - Additional options
   * @returns {number} Toast ID
   */
  const success = (messageOrOptions, options = {}) => {
    const config = typeof messageOrOptions === 'string'
      ? { message: messageOrOptions, type: TOAST_TYPES.SUCCESS, ...options }
      : { type: TOAST_TYPES.SUCCESS, ...messageOrOptions }
    
    return addToast(config)
  }

  /**
   * Show error toast
   * @param {string|object} messageOrOptions - Message or options
   * @param {object} options - Additional options
   * @returns {number} Toast ID
   */
  const error = (messageOrOptions, options = {}) => {
    const config = typeof messageOrOptions === 'string'
      ? { message: messageOrOptions, type: TOAST_TYPES.ERROR, duration: 8000, ...options }
      : { type: TOAST_TYPES.ERROR, duration: 8000, ...messageOrOptions }
    
    return addToast(config)
  }

  /**
   * Show warning toast
   * @param {string|object} messageOrOptions - Message or options
   * @param {object} options - Additional options
   * @returns {number} Toast ID
   */
  const warning = (messageOrOptions, options = {}) => {
    const config = typeof messageOrOptions === 'string'
      ? { message: messageOrOptions, type: TOAST_TYPES.WARNING, duration: 6000, ...options }
      : { type: TOAST_TYPES.WARNING, duration: 6000, ...messageOrOptions }
    
    return addToast(config)
  }

  /**
   * Show info toast
   * @param {string|object} messageOrOptions - Message or options
   * @param {object} options - Additional options
   * @returns {number} Toast ID
   */
  const info = (messageOrOptions, options = {}) => {
    const config = typeof messageOrOptions === 'string'
      ? { message: messageOrOptions, type: TOAST_TYPES.INFO, ...options }
      : { type: TOAST_TYPES.INFO, ...messageOrOptions }
    
    return addToast(config)
  }

  /**
   * Show confirmation toast with action buttons
   * @param {string} message - Confirmation message
   * @param {object} options - Options with onConfirm and onCancel handlers
   * @returns {number} Toast ID
   */
  const confirm = (message, options = {}) => {
    const { onConfirm, onCancel, title = 'Confirm Action', ...otherOptions } = options
    
    return addToast({
      type: TOAST_TYPES.WARNING,
      title,
      message,
      duration: 0,
      persistent: true,
      actions: [
        {
          label: 'Confirm',
          style: 'primary',
          handler: onConfirm || (() => {})
        },
        {
          label: 'Cancel',
          style: 'secondary',
          handler: onCancel || (() => {})
        }
      ],
      ...otherOptions
    })
  }

  /**
   * Show loading toast with optional progress
   * @param {string} message - Loading message
   * @param {object} options - Additional options
   * @returns {number} Toast ID
   */
  const loading = (message = 'Loading...', options = {}) => {
    return addToast({
      type: TOAST_TYPES.INFO,
      message,
      duration: 0,
      persistent: true,
      dismissible: false,
      ...options
    })
  }

  /**
   * Handle promise with toast notifications
   * @param {Promise} promise - Promise to handle
   * @param {object} options - Toast options for different states
   * @returns {Promise} Original promise
   */
  const promise = async (promise, options = {}) => {
    const {
      loading: loadingMsg = 'Processing...',
      success: successMsg = 'Operation completed successfully!',
      error: errorMsg = 'Operation failed!',
      ...otherOptions
    } = options

    const loadingToastId = loading(loadingMsg, otherOptions)

    try {
      const result = await promise
      removeToast(loadingToastId)
      success(successMsg, otherOptions)
      return result
    } catch (err) {
      removeToast(loadingToastId)
      error(typeof errorMsg === 'function' ? errorMsg(err) : errorMsg, otherOptions)
      throw err
    }
  }

  return {
    // State
    toasts,
    
    // Core methods
    addToast,
    removeToast,
    clearToasts,
    updateToast,
    
    // Convenience methods
    success,
    error,
    warning,
    info,
    confirm,
    loading,
    promise,
    
    // Constants
    TOAST_TYPES
  }
}

// Create global instance
const globalToast = useToast()

// Plugin installation function
export function installToast(app) {
  app.provide('toast', globalToast)
  app.config.globalProperties.$toast = globalToast
}

// Export global instance for direct import
export const toast = {
  success: globalToast.success,
  error: globalToast.error,
  warning: globalToast.warning,
  info: globalToast.info,
  confirm: globalToast.confirm,
  loading: globalToast.loading,
  promise: globalToast.promise,
  clear: globalToast.clearToasts,
  remove: globalToast.removeToast
}

export default useToast