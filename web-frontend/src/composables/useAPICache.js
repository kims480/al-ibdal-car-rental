import { ref, computed } from 'vue'
import axios from 'axios'

// Global cache state
const cache = ref({
  branches: { data: null, loading: false, error: null, timestamp: null },
  users: { data: null, loading: false, error: null, timestamp: null }
})

// Cache expiry time (5 minutes)
const CACHE_EXPIRY = 5 * 60 * 1000

const isCacheValid = (cacheItem) => {
  if (!cacheItem.data || !cacheItem.timestamp) return false
  return Date.now() - cacheItem.timestamp < CACHE_EXPIRY
}

export function useAPICache() {
  
  const fetchBranches = async (force = false) => {
    const branchCache = cache.value.branches
    
    // Return cached data if valid and not forcing refresh
    if (!force && isCacheValid(branchCache) && branchCache.data) {
      return { data: branchCache.data, fromCache: true }
    }
    
    // Don't make concurrent requests
    if (branchCache.loading) {
      // Wait for ongoing request
      return new Promise((resolve) => {
        const checkLoading = () => {
          if (!branchCache.loading) {
            resolve({ data: branchCache.data, fromCache: true })
          } else {
            setTimeout(checkLoading, 100)
          }
        }
        checkLoading()
      })
    }
    
    branchCache.loading = true
    branchCache.error = null
    
    try {
      const response = await axios.get('/branches')
      if (response.data.success) {
        branchCache.data = response.data.data.data || response.data.data
        branchCache.timestamp = Date.now()
        return { data: branchCache.data, fromCache: false }
      }
    } catch (error) {
      branchCache.error = error.message
      console.error('Error fetching branches:', error)
      throw error
    } finally {
      branchCache.loading = false
    }
  }

  const fetchUsers = async (force = false) => {
    const userCache = cache.value.users
    
    // Return cached data if valid and not forcing refresh
    if (!force && isCacheValid(userCache) && userCache.data) {
      return { data: userCache.data, fromCache: true }
    }
    
    // Don't make concurrent requests
    if (userCache.loading) {
      // Wait for ongoing request
      return new Promise((resolve) => {
        const checkLoading = () => {
          if (!userCache.loading) {
            resolve({ data: userCache.data, fromCache: true })
          } else {
            setTimeout(checkLoading, 100)
          }
        }
        checkLoading()
      })
    }
    
    userCache.loading = true
    userCache.error = null
    
    try {
      const response = await axios.get('/users')
      if (response.data.success) {
        userCache.data = response.data.data.data || response.data.data
        userCache.timestamp = Date.now()
        return { data: userCache.data, fromCache: false }
      }
    } catch (error) {
      userCache.error = error.message
      console.error('Error fetching users:', error)
      throw error
    } finally {
      userCache.loading = false
    }
  }

  const getBranches = computed(() => cache.value.branches.data || [])
  const getUsers = computed(() => cache.value.users.data || [])
  const isBranchesLoading = computed(() => cache.value.branches.loading)
  const isUsersLoading = computed(() => cache.value.users.loading)
  
  const clearCache = () => {
    cache.value.branches = { data: null, loading: false, error: null, timestamp: null }
    cache.value.users = { data: null, loading: false, error: null, timestamp: null }
  }

  return {
    fetchBranches,
    fetchUsers,
    getBranches,
    getUsers,
    isBranchesLoading,
    isUsersLoading,
    clearCache
  }
}