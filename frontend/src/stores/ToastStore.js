import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', {
  state: () => ({
    visible: false,
    message: '',
    variant: 'success',
    duration: 3000,
    id: 0,
  }),
  actions: {
    show(message, options = {}) {
      this.message = message
      this.variant = options.variant || 'success'
      this.duration = options.duration || 3000
      this.visible = true
      this.id += 1
    },
    showError(message, options = {}) {
      this.show(message, { ...options, variant: 'error' })
    },
    hide() {
      this.visible = false
    },
  },
})
