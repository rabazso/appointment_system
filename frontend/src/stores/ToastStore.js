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
    show(message) {
      this.message = message
      this.variant = 'success'
      this.duration = 3000
      this.visible = true
      this.id += 1
    },
    showError(message) {
      this.message = message
      this.variant = 'error'
      this.duration = 3000
      this.visible = true
      this.id += 1
    },
    hide() {
      this.visible = false
    },
  },
})
