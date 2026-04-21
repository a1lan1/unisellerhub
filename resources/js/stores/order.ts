import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { sync as syncOrdersRoute } from '@/routes/api/orders'

export const useOrderStore = defineStore('order', () => {
  const isSyncing = ref(false)

  const sync = async() => {
    isSyncing.value = true

    try {
      const response = await api.post(syncOrdersRoute().url)

      if (snackbar && response.data.message) {
        snackbar.info({ text: response.data.message })
      }
    } catch (error) {
      console.error(error)
      isSyncing.value = false
    }
  }

  const setSyncing = (value: boolean) => {
    isSyncing.value = value
  }

  return {
    isSyncing,
    sync,
    setSyncing
  }
})
