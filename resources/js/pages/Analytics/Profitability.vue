<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Calculator } from 'lucide-vue-next'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { dashboard } from '@/routes'
import { formatCurrency } from '@/utils/formatters'
import { getMarketplaceColor } from '@/utils/marketplace'

interface ProfitabilityItem {
  id: number;
  marketplace: string;
  sku: string;
  name: string;
  price: number;
  cost_price: number;
  commission_percent: number;
  logistic_cost: number;
  profit: number;
  margin: number;
}

const props = defineProps<{
  items: ProfitabilityItem[];
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Profitability Calculator', href: '#' }
    ]
  }
})

const localItems = ref([...props.items])
const isUpdating = ref<number | null>(null)

const calculateRow = (item: ProfitabilityItem) => {
  const commission = item.price * (item.commission_percent / 100)
  item.profit = item.price - commission - item.logistic_cost - item.cost_price
  item.margin = item.price > 0 ? (item.profit / item.price) * 100 : 0
}

const saveFinance = async(item: ProfitabilityItem) => {
  isUpdating.value = item.id

  try {
    await api.patch('/api/analytics/update-finance', {
      listing_id: item.id,
      cost_price: item.cost_price,
      commission_percent: item.commission_percent,
      logistic_cost: item.logistic_cost
    })

    if (snackbar) {
      snackbar.success({ text: `Finance updated for ${item.sku}` })
    }
  } catch (e) {
    console.error(e)
  } finally {
    isUpdating.value = null
  }
}

const headers: Array<{
  title: string;
  key: string;
  sortable?: boolean;
  width?: string;
  align?: 'start' | 'end' | 'center';
}> = [
  { title: 'Product', key: 'name', sortable: true },
  { title: 'MP', key: 'marketplace', width: '100px' },
  { title: 'Price', key: 'price', align: 'end' },
  { title: 'Cost Price', key: 'cost_price', width: '120px' },
  { title: 'Comm %', key: 'commission_percent', width: '100px' },
  { title: 'Logistics', key: 'logistic_cost', width: '100px' },
  { title: 'Profit', key: 'profit', align: 'end' },
  { title: 'Margin', key: 'margin', align: 'end' },
  { title: '', key: 'actions', sortable: false, align: 'end' }
]

const getMarginColor = (margin: number) => {
  if (margin <= 0) {
    return 'text-red-600 font-bold'
  }

  if (margin < 15) {
    return 'text-orange-500'
  }

  return 'text-green-600 font-bold'
}
</script>

<template>
  <Head title="Profitability" />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <Calculator class="text-primary" />
      Profitability Calculator
    </h1>
  </div>

  <v-card
    border
    flat
  >
    <v-card-text class="pa-0">
      <v-data-table
        :headers="headers"
        :items="localItems"
        hover
        density="comfortable"
        fixed-header
        fixed-footer
        class="table-height"
      >
        <template #[`item.name`]="{ item }">
          <div class="py-2">
            <div class="font-weight-bold text-caption">
              {{ item.sku }}
            </div>
            <div
              class="text-truncate"
              style="max-width: 300px"
            >
              {{ item.name }}
            </div>
          </div>
        </template>

        <template #[`item.marketplace`]="{ item }">
          <v-chip
            :color="getMarketplaceColor(item.marketplace)"
            size="x-small"
            label
          >
            {{ item.marketplace.toUpperCase() }}
          </v-chip>
        </template>

        <template #[`item.price`]="{ item }">
          {{ formatCurrency(item.price) }}
        </template>

        <template #[`item.cost_price`]="{ item }">
          <v-text-field
            v-model.number="item.cost_price"
            type="number"
            density="compact"
            hide-details
            variant="underlined"
            @update:model-value="calculateRow(item)"
          />
        </template>

        <template #[`item.commission_percent`]="{ item }">
          <v-text-field
            v-model.number="item.commission_percent"
            type="number"
            density="compact"
            hide-details
            variant="underlined"
            suffix="%"
            @update:model-value="calculateRow(item)"
          />
        </template>

        <template #[`item.logistic_cost`]="{ item }">
          <v-text-field
            v-model.number="item.logistic_cost"
            type="number"
            density="compact"
            hide-details
            variant="underlined"
            @update:model-value="calculateRow(item)"
          />
        </template>

        <template #[`item.profit`]="{ item }">
          <span :class="item.profit < 0 ? 'text-red-600' : 'text-green-600'">
            {{ formatCurrency(item.profit) }}
          </span>
        </template>

        <template #[`item.margin`]="{ item }">
          <span :class="getMarginColor(item.margin)">
            {{ item.margin.toFixed(1) }}%
          </span>
        </template>

        <template #[`item.actions`]="{ item }">
          <v-btn
            icon="mdi-content-save"
            variant="text"
            size="small"
            color="primary"
            :loading="isUpdating === item.id"
            @click="saveFinance(item)"
          />
        </template>
      </v-data-table>
    </v-card-text>
  </v-card>
</template>
