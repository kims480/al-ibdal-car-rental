import { defineStore } from 'pinia'
import axios from 'axios'

const API_BASE_URL = 'http://127.0.0.1:8000/api'

// Configure axios
axios.defaults.baseURL = API_BASE_URL
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token'),
    isAuthenticated: false,
    loading: false,
    error: null
  }),

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/login', credentials)
        
        if (response.data.success) {
          this.token = response.data.token
          this.user = response.data.user
          this.isAuthenticated = true
          
          localStorage.setItem('auth_token', this.token)
          localStorage.setItem('user', JSON.stringify(this.user))
          
          return { success: true }
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async register(userData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await axios.post('/register', userData)
        
        if (response.data.success) {
          this.token = response.data.token
          this.user = response.data.user
          this.isAuthenticated = true
          
          localStorage.setItem('auth_token', this.token)
          localStorage.setItem('user', JSON.stringify(this.user))
          
          return { success: true }
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async checkAuth() {
      if (!this.token) return
      
      try {
        const response = await axios.get('/user')
        
        if (response.data.success) {
          this.user = response.data.user
          this.isAuthenticated = true
        }
      } catch (error) {
        this.logout()
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post('/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        this.isAuthenticated = false
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      }
    }
  }
})