<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { ref, computed } from 'vue'
import { useSyncTable } from '@/composables/useSyncTable'
import { useProductStore } from '@/stores/product'
import type { Product, Pagination, ProductFilter } from '@/types'
import { formatCurrency } from '@/utils/formatters'
import { getMarketplaceColor, marketplaceOptions } from '@/utils/marketplace'

const props = defineProps<{
  products: Pagination<Product>;
  filters: ProductFilter;
  vectorSearchEnabled: boolean;
}>()

const productStore = useProductStore()
const { isBulkUpdating } = storeToRefs(productStore)
const { syncBulk } = productStore

const selected = ref<number[]>([])

const {
  isSyncing,
  search,
  semanticSearch,
  marketplaceFilter,
  itemsPerPage,
  updateOptions
} = useSyncTable({ type: 'products', preserveOnly: ['products'] }, props.filters, props.products.meta.per_page)

const headers = [
  { title: 'Marketplace', key: 'marketplace', sortable: true },
  { title: 'Name', key: 'name', sortable: true },
  { title: 'SKU', key: 'sku', sortable: true },
  { title: 'External ID', key: 'external_id', sortable: false },
  { title: 'Price', key: 'price', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Last Synced', key: 'last_synced_at', sortable: true }
]

const hasSelection = computed(() => selected.value.length > 0)

const bulkSync = async() => {
  if (selected.value.length === 0) {
    return
  }

  await syncBulk(selected.value)
  selected.value = []
}
</script>

<template>
  <div class="space-y-4">
    <!-- Selection Action Bar -->
    <v-expand-transition>
      <div
        v-if="hasSelection"
        class="glass glass-border rounded-lg border border-primary-200 p-3 flex items-center justify-between shadow-sm"
      >
        <div class="flex items-center gap-3">
          <v-chip
            color="primary"
            size="small"
          >
            {{ selected.length }} items selected
          </v-chip>
          <span class="text-sm font-medium text-primary-900">Bulk Actions:</span>
        </div>
        <div class="flex gap-2">
          <v-btn
            size="small"
            color="primary"
            prepend-icon="mdi-sync"
            variant="tonal"
            :loading="isBulkUpdating"
            @click="bulkSync"
          >
            Sync Selected
          </v-btn>
          <v-btn
            size="small"
            variant="outlined"
            color="secondary"
            @click="selected = []"
          >
            Clear
          </v-btn>
        </div>
      </div>
    </v-expand-transition>

    <v-card
      border
      flat
    >
      <div class="p-4 flex gap-4 flex-wrap">
        <v-text-field
          v-model="search"
          label="Search by Name or SKU"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 300px"
          clearable
        />
        <v-text-field
          v-if="vectorSearchEnabled"
          v-model="semanticSearch"
          label="Semantic Search (e.g., 'gaming laptop')"
          prepend-inner-icon="mdi-brain"
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 300px"
          clearable
        />
        <v-select
          v-model="marketplaceFilter"
          :items="marketplaceOptions"
          label="Filter by Marketplace"
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 200px"
        />
      </div>

      <v-data-table-server
        v-model="selected"
        v-model:items-per-page="itemsPerPage"
        :headers="headers"
        :items="products.data"
        :items-length="products.meta.total"
        :loading="isSyncing"
        show-select
        hover
        fixed-header
        fixed-footer
        class="table-height"
        @update:options="updateOptions"
      >
        <template #[`item.marketplace`]="{ item }">
          <v-chip
            :color="getMarketplaceColor(item.marketplace)"
            size="small"
            label
            class="font-weight-bold"
          >
            {{ item.marketplace.toUpperCase() }}
          </v-chip>
        </template>

        <template #[`item.name`]="{ item }">
          <div class="font-weight-medium">
            {{ item.name }}
          </div>
        </template>

        <template #[`item.external_id`]="{ item }">
          <code class="text-caption">{{ item.external_id }}</code>
        </template>

        <template #[`item.price`]="{ item }">
          {{ formatCurrency(item.price) }}
        </template>

        <template #[`item.status`]="{ item }">
          <v-chip
            :color="item.status === 'active' ? 'success' : 'error'"
            size="x-small"
            variant="outlined"
          >
            {{ item.status }}
          </v-chip>
        </template>

        <template #[`item.last_synced_at`]="{ item }">
          <span class="text-caption text-medium-emphasis">
            {{ item.last_synced_at || 'Never' }}
          </span>
        </template>
      </v-data-table-server>
    </v-card>
  </div>
</template>
