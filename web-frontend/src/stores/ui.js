import { defineStore } from 'pinia'

export const useUIStore = defineStore('ui', {
  state: () => ({
    theme: localStorage.getItem('theme') || 'ocean-breeze',
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true'
  }),
  actions: {
    setTheme(newTheme) {
      this.theme = newTheme
      localStorage.setItem('theme', newTheme)
      document.documentElement.setAttribute('data-theme', newTheme)
    },
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
      localStorage.setItem('sidebarCollapsed', String(this.sidebarCollapsed))
    }
  },
  getters: {
    themeConfig: (state) => {
      const themes = {
        'ocean-breeze': {
          name: '🌅 Ocean Breeze',
          primary: '#3b82f6',
          secondary: '#14b8a6',
          accent: '#06b6d4',
          description: 'Calm and professional blue tones'
        },
        'cherry-blossom': {
          name: '🌸 Cherry Blossom',
          primary: '#ec4899',
          secondary: '#a855f7',
          accent: '#f472b6',
          description: 'Vibrant pink and purple palette'
        },
        'golden-sunset': {
          name: '🌟 Golden Sunset',
          primary: '#f59e0b',
          secondary: '#ea580c',
          accent: '#fbbf24',
          description: 'Warm orange and yellow hues'
        }
      }
      return themes[state.theme] || themes['ocean-breeze']
    }
  }
})
