<script setup lang="ts">
import { useSyncTable } from '@/composables/useSyncTable'
import type { Order, Pagination, OrderFilter } from '@/types'
import { getMarketplaceColor, marketplaceOptions } from '@/utils/marketplace'
import { getStatusColor, statusOptions } from '@/utils/order'

const props = defineProps<{
  orders: Pagination<Order>;
  filters: OrderFilter;
}>()

const {
  isSyncing,
  search,
  marketplaceFilter,
  statusesFilter,
  dateFromFilter,
  dateToFilter,
  itemsPerPage,
  updateOptions
} = useSyncTable({ type: 'orders', preserveOnly: ['orders'] }, props.filters, props.orders.meta.per_page)

const headers = [
  { title: 'Marketplace', key: 'marketplace', sortable: true },
  { title: 'Order ID', key: 'external_id', sortable: false },
  { title: 'Date', key: 'order_date', sortable: true },
  { title: 'Status', key: 'status', sortable: true },
  { title: 'Total Price', key: 'total_price', sortable: true },
  { title: 'Items Count', key: 'items_count', sortable: false }
]
</script>

<template>
  <v-card border flat>
    <div class="p-4 flex flex-col gap-2">
      <!-- First Row of Filters -->
      <div class="flex gap-4 flex-wrap items-center">
        <v-text-field
          v-model="search"
          label="Search by Order ID"
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
          clearable
        />
        <v-select
          v-model="statusesFilter"
          :items="statusOptions"
          label="Statuses"
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 200px"
          multiple
          chips
          closable-chips
          clearable
        />

        <div class="flex gap-4 flex-wrap items-center p-2 rounded-lg border border-dashed">
          <span class="text-xs font-medium text-neutral-500 uppercase px-2">Order Date Range:</span>
          <v-text-field
            v-model="dateFromFilter"
            type="date"
            label="From"
            variant="outlined"
            density="compact"
            hide-details
            style="max-width: 180px"
            clearable
          />
          <v-text-field
            v-model="dateToFilter"
            type="date"
            label="To"
            variant="outlined"
            density="compact"
            hide-details
            style="max-width: 180px"
            clearable
          />
        </div>
      </div>
    </div>

    <v-data-table-server
      v-model:items-per-page="itemsPerPage"
      :headers="headers"
      :items="orders.data"
      :items-length="orders.meta.total"
      :loading="isSyncing"
      hover
      @update:options="updateOptions"
    >
      <template v-slot:[`item.marketplace`]="{ item }">
        <v-chip
          :color="getMarketplaceColor(item.marketplace)"
          size="small"
          label
          class="font-weight-bold"
        >
          {{ item.marketplace.toUpperCase() }}
        </v-chip>
      </template>

      <template v-slot:[`item.external_id`]="{ item }">
        <code class="text-caption">{{ item.external_id }}</code>
      </template>

      <template v-slot:[`item.order_date`]="{ item }">
        <span class="text-sm">{{ item.order_date }}</span>
      </template>

      <template v-slot:[`item.status`]="{ item }">
        <v-chip
          :color="getStatusColor(item.status)"
          size="x-small"
          variant="flat"
        >
          {{ item.status }}
        </v-chip>
      </template>

      <template v-slot:[`item.total_price`]="{ item }">
        <span class="font-weight-bold">{{ item.formatted_total_price }}</span>
      </template>

      <template v-slot:[`item.items_count`]="{ item }">
        {{ item.items?.length || 0 }}
      </template>
    </v-data-table-server>
  </v-card>
</template>
