import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { sync as syncProductsRoute } from '@/routes/api/products'

export const useProductStore = defineStore('product', () => {
  const isSyncing = ref(false)
  const isBulkUpdating = ref(false)

  const sync = async() => {
    isSyncing.value = true

    try {
      const { data } = await api.post(syncProductsRoute().url)

      if (data.message) {
        snackbar.info({ text: data.message })
      }
    } catch (error) {
      console.error(error)
      isSyncing.value = false
    }
  }

  const syncBulk = async(ids: number[]) => {
    isBulkUpdating.value = true

    try {
      const { data } = await api.post('/api/products/sync-bulk', { ids })

      if (data.message) {
        snackbar.success({ text: data.message })
      }
    } catch (error) {
      console.error(error)
    } finally {
      isBulkUpdating.value = false
    }
  }

  const setSyncing = (value: boolean) => {
    isSyncing.value = value
  }

  return {
    isSyncing,
    isBulkUpdating,
    sync,
    syncBulk,
    setSyncing
  }
})
