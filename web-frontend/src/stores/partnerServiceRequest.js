import { defineStore } from 'pinia'
import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

export const usePartnerServiceRequestStore = defineStore('partnerServiceRequest', {
  state: () => ({
    serviceRequests: [],
    currentRequest: null,
    loading: false,
    error: null,
    stats: {
      total: 0,
      pending: 0,
      assigned: 0,
      inProgress: 0,
      completed: 0
    }
  }),

  getters: {
    pendingRequests: (state) => state.serviceRequests.filter(req => req.status === 'pending'),
    completedRequests: (state) => state.serviceRequests.filter(req => req.status === 'completed'),
    inProgressRequests: (state) => state.serviceRequests.filter(req => ['assigned', 'in_progress'].includes(req.status))
  },

  actions: {
    // Get all service requests for the logged-in partner
    async getPartnerRequests() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`${API_BASE_URL}/service-requests`, {
          params: { partner_id: 'current' } // Backend should handle 'current' to mean the logged-in partner
        });
        
        if (response.data.success) {
          this.serviceRequests = response.data.data.data || response.data.data;
          this.calculateStats();
          return { success: true, data: this.serviceRequests };
        }
        
        return { success: false, error: 'Failed to fetch service requests' };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load service requests';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },
    
    // Create a new service request as a partner
    async createServiceRequest(requestData) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.post(`${API_BASE_URL}/service-requests`, requestData);
        
        if (response.data.success) {
          // Add the new request to the list
          this.serviceRequests.unshift(response.data.data);
          this.calculateStats();
          return { success: true, data: response.data.data };
        }
        
        return { success: false, error: 'Failed to create service request' };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create service request';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },
    
    // Get details of a specific service request
    async getRequestDetails(requestId) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get(`${API_BASE_URL}/service-requests/${requestId}`);
        
        if (response.data.success) {
          this.currentRequest = response.data.data;
          return { success: true, data: response.data.data };
        }
        
        return { success: false, error: 'Failed to fetch request details' };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load request details';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },
    
    // Update an existing service request
    async updateServiceRequest(requestId, updateData) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.put(`${API_BASE_URL}/service-requests/${requestId}`, updateData);
        
        if (response.data.success) {
          // Update the request in the list
          const index = this.serviceRequests.findIndex(req => req.id === requestId);
          if (index !== -1) {
            this.serviceRequests[index] = response.data.data;
          }
          this.calculateStats();
          return { success: true, data: response.data.data };
        }
        
        return { success: false, error: 'Failed to update service request' };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update service request';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },
    
    // Calculate statistics for the dashboard
    calculateStats() {
      this.stats = {
        total: this.serviceRequests.length,
        pending: this.serviceRequests.filter(req => req.status === 'pending').length,
        assigned: this.serviceRequests.filter(req => req.status === 'assigned').length,
        inProgress: this.serviceRequests.filter(req => req.status === 'in_progress').length,
        completed: this.serviceRequests.filter(req => req.status === 'completed').length
      };
    },
    
    // Cancel a service request
    async cancelServiceRequest(requestId) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.put(`${API_BASE_URL}/service-requests/${requestId}`, {
          status: 'cancelled'
        });
        
        if (response.data.success) {
          // Update the request in the list
          const index = this.serviceRequests.findIndex(req => req.id === requestId);
          if (index !== -1) {
            this.serviceRequests[index] = response.data.data;
          }
          this.calculateStats();
          return { success: true, data: response.data.data };
        }
        
        return { success: false, error: 'Failed to cancel service request' };
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to cancel service request';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    }
  }
})