import { router } from '@inertiajs/vue3'
import { readonly, ref } from 'vue'

const loading = ref(false)

function setLoading(value: boolean) {
  loading.value = value
}

// Inertia
router.on('start', () => setLoading(true))
router.on('finish', () => setLoading(false))
router.on('error', () => setLoading(false))

export function useInertiaLoading() {
  return {
    loading: readonly(loading),
    setLoading
  }
}
