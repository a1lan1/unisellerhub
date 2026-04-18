import { ref, onMounted } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'

export function useConnections() {
  const connections = ref<any[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const isDeleting = ref(false)
  const errors = ref<Record<string, string>>({})

  const fetchConnections = async() => {
    isLoading.value = true

    try {
      const response = await api.get('/api/marketplace-connections')
      connections.value = response.data
    } catch (error) {
      console.error(error)
      snackbar?.error({ text: 'Failed to fetch connections' })
    } finally {
      isLoading.value = false
    }
  }

  const saveConnection = async(form: any) => {
    isSaving.value = true
    errors.value = {}

    try {
      await api.post('/api/marketplace-connections', form)
      await fetchConnections()
      snackbar?.success({ text: 'Connection saved successfully' })
    } catch (error: any) {
      if (error.response?.status === 422) {
        errors.value = error.response.data.errors
      }

      throw error
    } finally {
      isSaving.value = false
    }
  }

  const deleteConnection = async(id: number) => {
    if (!confirm('Are you sure you want to delete this connection?')) {
      return
    }

    isDeleting.value = true

    try {
      await api.delete(`/api/marketplace-connections/${id}`)
      await fetchConnections()
      snackbar?.success({ text: 'Connection deleted successfully' })
    } catch (error) {
      console.error(error)
      snackbar?.error({ text: 'Failed to delete connection' })
    } finally {
      isDeleting.value = false
    }
  }

  onMounted(fetchConnections)

  return {
    connections,
    isLoading,
    isSaving,
    isDeleting,
    errors,
    fetchConnections,
    deleteConnection,
    saveConnection
  }
}
