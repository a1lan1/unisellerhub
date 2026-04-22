import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/plugins/axios'

export interface MarketplaceConnection {
  id: number;
  marketplace: string;
  name: string;
  is_active: boolean;
}

export const useMarketplaceStore = defineStore('marketplace', () => {
  const connections = ref<MarketplaceConnection[]>([])
  const isLoading = ref(false)

  const activeConnections = computed(() => connections.value.filter(c => c.is_active))

  const fetchConnections = async() => {
    isLoading.value = true

    try {
      const response = await api.get('/api/marketplace-connections')
      connections.value = response.data
    } catch (error) {
      console.error('Failed to fetch marketplace connections:', error)
    } finally {
      isLoading.value = false
    }
  }

  return {
    connections,
    activeConnections,
    isLoading,
    fetchConnections
  }
})
