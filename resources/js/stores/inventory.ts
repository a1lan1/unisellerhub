import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { pull as pullInventoryRoute, update as updateInventoryRoute } from '@/routes/api/inventory'
import type { InventoryItem } from '@/types'

export const useInventoryStore = defineStore('inventory', () => {
  const isSyncing = ref(false)
  const isBulkUpdating = ref(false)

  const pullFromMarketplaces = async() => {
    isSyncing.value = true

    try {
      const response = await api.post(pullInventoryRoute().url)

      if (snackbar && response.data.message) {
        snackbar.info({ text: response.data.message })
      }
    } catch (error) {
      console.error(error)
      isSyncing.value = false
    }
  }

  const pullBulk = async(ids: number[]) => {
    isBulkUpdating.value = true

    try {
      const response = await api.post('/api/inventory/pull-bulk', { ids })

      if (snackbar && response.data.message) {
        snackbar.success({ text: response.data.message })
      }
    } catch (error) {
      console.error(error)
    } finally {
      isBulkUpdating.value = false
    }
  }

  const syncMoySklad = async() => {
    isSyncing.value = true

    try {
      const response = await api.post('/api/inventory/sync-ms')

      if (snackbar && response.data.message) {
        snackbar.info({ text: response.data.message })
      }
    } catch (error) {
      console.error(error)
      isSyncing.value = false
    }
  }

  const updateStock = async(item: InventoryItem) => {
    try {
      await api.patch(updateInventoryRoute().url, {
        id: item.id,
        quantity: item.quantity
      })

      if (snackbar) {
        snackbar.success({ text: 'Stock updated and pushed successfully!' })
      }
    } catch (error) {
      console.error(error)
    }
  }

  const setSyncing = (value: boolean) => {
    isSyncing.value = value
  }

  return {
    isSyncing,
    isBulkUpdating,
    pullFromMarketplaces,
    pullBulk,
    syncMoySklad,
    updateStock,
    setSyncing
  }
})
