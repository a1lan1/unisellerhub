import { router } from '@inertiajs/vue3'
import { useDebounceFn, useMagicKeys } from '@vueuse/core'
import { ref, watch } from 'vue'
import { api } from '@/plugins/axios'
import type { SearchResultItem } from '@/types'

export function useGlobalSearch() {
  const isOpen = ref(false)
  const search = ref('')
  const results = ref<SearchResultItem[]>([])
  const isLoading = ref(false)
  const activeResultIndex = ref(-1)

  const performSearch = useDebounceFn(async(q: string) => {
    if (q.length < 2) {
      results.value = []

      return
    }

    isLoading.value = true
    activeResultIndex.value = -1 // Reset active index on new search

    try {
      const { data } = await api.get('/api/search', { params: { q } })
      results.value = data.results
    } catch (e) {
      console.error('Search API error:', e)
      results.value = []
    } finally {
      isLoading.value = false
    }
  }, 300)

  watch(search, (newVal) => {
    performSearch(newVal)
  })

  const navigateTo = (url: string) => {
    isOpen.value = false
    search.value = ''
    results.value = []
    activeResultIndex.value = -1
    router.visit(url)
  }

  const closeSearch = () => {
    isOpen.value = false
    search.value = ''
    results.value = []
    activeResultIndex.value = -1
  }

  // Keyboard navigation
  const keys = useMagicKeys()
  const cmdK = keys['cmd+k']
  const ctrlK = keys['ctrl+k']
  const escape = keys.escape
  const arrowUp = keys.arrowup
  const arrowDown = keys.arrowdown
  const enter = keys.enter

  watch([cmdK, ctrlK], ([cmdKVal, ctrlKVal]) => {
    if (cmdKVal || ctrlKVal) {
      isOpen.value = true
    }
  })

  watch(escape, (v) => {
    if (v && isOpen.value) {
      closeSearch()
    }
  })

  watch(arrowDown, (v) => {
    if (v && isOpen.value && results.value.length > 0) {
      activeResultIndex.value = (activeResultIndex.value + 1) % results.value.length
    }
  })

  watch(arrowUp, (v) => {
    if (v && isOpen.value && results.value.length > 0) {
      activeResultIndex.value = (activeResultIndex.value - 1 + results.value.length) % results.value.length
    }
  })

  watch(enter, (v) => {
    if (v && isOpen.value && activeResultIndex.value !== -1) {
      const selectedResult = results.value[activeResultIndex.value]

      if (selectedResult) {
        navigateTo(selectedResult.url)
      }
    }
  })

  return {
    isOpen,
    search,
    results,
    isLoading,
    activeResultIndex,
    navigateTo,
    closeSearch
  }
}
