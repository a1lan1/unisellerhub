import { defineStore } from 'pinia'
import type { SnackbarMessage } from '@/plugins/snackbar'

interface State {
  messages: SnackbarMessage[];
}

export const useSnackbarStore = defineStore('snackbar', {
  state: (): State => ({
    messages: []
  }),

  actions: {
    showMessage(item: SnackbarMessage) {
      const defaults: Partial<SnackbarMessage> = {
        color: 'info',
        timeout: 5000
      }
      this.messages.push({
        ...defaults,
        ...item
      })
    }
  }
})
