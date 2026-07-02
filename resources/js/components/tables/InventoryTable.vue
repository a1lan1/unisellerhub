<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { ref, computed } from 'vue'
import { useSyncTable } from '@/composables/useSyncTable'
import { useInventoryStore } from '@/stores/inventory'
import type { InventoryItem, Pagination, InventoryFilter } from '@/types'
import { getMarketplaceColor, marketplaceOptions } from '@/utils/marketplace'

const props = defineProps<{
    inventory: Pagination<InventoryItem>;
    filters: InventoryFilter;
    hasOrganization: boolean;
}>()

const inventoryStore = useInventoryStore()
const { isBulkUpdating } = storeToRefs(inventoryStore)
const { pullBulk, updateStock } = inventoryStore

const selected = ref<number[]>([])

const {
  isSyncing, search, marketplaceFilter, itemsPerPage, updateOptions
} = useSyncTable({ type: 'inventory', preserveOnly: ['inventory'] }, props.filters, props.inventory.meta.per_page)

const headers = [
  { title: 'Marketplace', key: 'marketplace', sortable: true },
  { title: 'Product Name', key: 'product_name', sortable: true },
  { title: 'SKU', key: 'sku', sortable: true },
  { title: 'Warehouse', key: 'warehouse_name', sortable: true },
  { title: 'Quantity', key: 'quantity', sortable: true, width: '150px' },
  { title: 'Reserved', key: 'reserved', sortable: true },
  { title: 'Available', key: 'available', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false }
]

const hasSelection = computed(() => selected.value.length > 0)

const bulkPull = async() => {
  if (selected.value.length === 0) {
    return
  }

  await pullBulk(selected.value)
  selected.value = []
}
</script>

<template>
  <div class="space-y-4">
    <!-- Selection Action Bar -->
    <v-expand-transition>
      <div
        v-if="hasSelection"
        class="bg-primary-50 border border-primary-200 rounded-lg p-3 flex items-center justify-between shadow-sm"
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
            prepend-icon="mdi-download"
            variant="tonal"
            :loading="isBulkUpdating"
            @click="bulkPull"
          >
            Pull Stocks for Selected
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
          label="Search by Product or SKU"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 300px"
          clearable
        />
        <v-select
          v-model="marketplaceFilter"
          :items="marketplaceOptions"
          label="Marketplace"
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
        :items="inventory.data"
        :items-length="inventory.meta.total"
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

        <template #[`item.product_name`]="{ item }">
          <div class="font-weight-medium">
            {{ item.product_name }}
          </div>
        </template>

        <template #[`item.warehouse_name`]="{ item }">
          <span class="text-caption text-medium-emphasis">{{ item.warehouse_name }}</span>
        </template>

        <template #[`item.quantity`]="{ item }">
          <v-text-field
            v-model.number="item.quantity"
            type="number"
            density="compact"
            hide-details
            variant="outlined"
            min="0"
            :disabled="!hasOrganization"
          />
        </template>

        <template #[`item.actions`]="{ item }">
          <v-btn
            size="small"
            variant="tonal"
            color="secondary"
            :disabled="!hasOrganization"
            @click="updateStock(item)"
          >
            Update
          </v-btn>
        </template>
      </v-data-table-server>
    </v-card>
  </div>
</template>
