import { router } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { snackbar } from '@/plugins/snackbar'
import { useAuthStore } from '@/stores/auth'
import { useInventoryStore } from '@/stores/inventory'
import { useOrderStore } from '@/stores/order'
import { useProductStore } from '@/stores/product'
import type { BaseFilter } from '@/types'

export type SyncType = 'products' | 'orders' | 'inventory';

interface UseSyncTableOptions {
  type: SyncType;
  preserveOnly: string[];
}

export function useSyncTable<T extends BaseFilter>(
  options: UseSyncTableOptions,
  initialFilters: T,
  initialPerPage: number
) {
  const authStore = useAuthStore()
  const { organizationId } = storeToRefs(authStore)

  const store = computed(() => {
    switch (options.type) {
    case 'products': return useProductStore()
    case 'orders': return useOrderStore()
    case 'inventory': return useInventoryStore()
    default: throw new Error(`Unknown sync type: ${options.type}`)
    }
  })

  const search = ref(initialFilters.search || '')
  const marketplaceFilter = ref(initialFilters.marketplace || null)
  const statusFilter = ref((initialFilters as any).status || null)
  const statusesFilter = ref<string[]>((initialFilters as any).statuses || [])
  const dateFromFilter = ref((initialFilters as any).date_from || null)
  const dateToFilter = ref((initialFilters as any).date_to || null)
  const itemsPerPage = ref(initialPerPage)

  const updateOptions = (dtOptions: any) => {
    const { page: currentPage, itemsPerPage: currentItemsPerPage, sortBy } = dtOptions

    const params: any = {
      page: currentPage,
      per_page: currentItemsPerPage,
      search: search.value || undefined,
      marketplace: marketplaceFilter.value || undefined,
      status: statusFilter.value || undefined,
      statuses: statusesFilter.value.length ? statusesFilter.value : undefined,
      date_from: dateFromFilter.value || undefined,
      date_to: dateToFilter.value || undefined
    }

    if (sortBy.length) {
      params.sort = sortBy[0].key
      params.direction = sortBy[0].order
    }

    router.get(window.location.pathname, params, {
      preserveState: true,
      preserveScroll: true,
      only: options.preserveOnly
    })
  }

  let timeout: any = null
  watch([search, marketplaceFilter, statusFilter, statusesFilter, dateFromFilter, dateToFilter], () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => {
      updateOptions({
        page: 1,
        itemsPerPage: itemsPerPage.value,
        sortBy: []
      })
    }, 500)
  }, { deep: true })

  onMounted(() => {
    if (organizationId.value) {
      const channel = echo().private(`organization.${organizationId.value}`)

      channel.listen(`.${options.type}.synced`, () => {
        store.value.setSyncing(false)
        router.reload({ only: options.preserveOnly })
      })

      channel.listen('.sync.failed', (e: any) => {
        const isInventoryError = options.type === 'inventory' && e.type === 'ms'

        if (e.type === options.type || isInventoryError) {
          store.value.setSyncing(false)

          if (snackbar) {
            snackbar.error({ text: e.message })
          }
        }
      })
    }
  })

  onUnmounted(() => {
    if (organizationId.value) {
      echo().leave(`organization.${organizationId.value}`)
    }
  })

  return {
    isSyncing: computed(() => store.value.isSyncing),
    search,
    marketplaceFilter,
    statusFilter,
    statusesFilter,
    dateFromFilter,
    dateToFilter,
    itemsPerPage,
    updateOptions
  }
}
