<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { BarChart3 } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import BaseDonutChart from '@/components/charts/BaseDonutChart.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { dashboard } from '@/routes'
import { formatCurrency } from '@/utils/formatters'

interface AbcItem {
  sku: string;
  name: string;
  revenue: number;
  share: number;
  group: 'A' | 'B' | 'C';
}

const { abc, days } = defineProps<{
  abc: {
    summary: { A: number; B: number; C: number };
    items: AbcItem[];
  };
  days: number;
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'ABC Analysis', href: '#' }
    ]
  }
})

const selectedDays = ref(days || 30)

const chartSeries = computed(() => [
  abc.summary.A,
  abc.summary.B,
  abc.summary.C
])

const chartLabels = ['Group A (80%)', 'Group B (15%)', 'Group C (5%)']
const chartColors = ['#10b981', '#f59e0b', '#ef4444']

const getGroupColor = (group: string) => {
  switch (group) {
  case 'A': return 'success'
  case 'B': return 'warning'
  case 'C': return 'error'
  default: return 'grey'
  }
}

const isGeneratingReport = ref(false)

const generateReport = async() => {
  isGeneratingReport.value = true

  try {
    await api.post('/api/exports/analytics', {
      report_type: 'product_revenue_analysis',
      days: selectedDays.value
    })
    snackbar.success({ text: 'Product Revenue Analysis report generation started. You will be notified when it\'s ready.' })
  } catch (error) {
    console.error('Error generating report:', error)
    snackbar.error({ text: 'Failed to start report generation.' })
  } finally {
    isGeneratingReport.value = false
  }
}
</script>

<template>
  <Head title="ABC Analysis" />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <BarChart3 class="text-primary" />
      ABC Analysis ({{ selectedDays }} Days)
    </h1>
    <div class="flex items-center gap-2">
      <v-btn
        color="primary"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-microsoft-excel"
        :loading="isGeneratingReport"
        :disabled="isGeneratingReport"
        @click="generateReport"
      >
        Export Report
      </v-btn>
      <v-tooltip location="bottom">
        <template #activator="{ props }">
          <v-btn
            icon="mdi-help-circle-outline"
            variant="text"
            v-bind="props"
          />
        </template>
        <span>Classifies products by revenue contribution: A (80%), B (15%), C (5%)</span>
      </v-tooltip>
    </div>
  </div>

  <div class="grid gap-6 grid-cols-1 lg:grid-cols-3">
    <!-- Summary Chart -->
    <Card class="lg:col-span-1">
      <CardHeader>
        <CardTitle>Product Distribution</CardTitle>
      </CardHeader>
      <CardContent>
        <BaseDonutChart
          :series="chartSeries"
          :labels="chartLabels"
          :colors="chartColors"
          :height="400"
        />
        <div class="mt-4 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="font-medium text-success">Group A (Cash Cows)</span>
            <span>{{ abc.summary.A }} SKUs</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="font-medium text-warning">Group B (Regulars)</span>
            <span>{{ abc.summary.B }} SKUs</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="font-medium text-error">Group C (Long Tail)</span>
            <span>{{ abc.summary.C }} SKUs</span>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Details Table -->
    <Card class="lg:col-span-2">
      <CardHeader>
        <CardTitle>Performance by SKU</CardTitle>
      </CardHeader>
      <CardContent>
        <v-table
          hover
          density="comfortable"
          fixed-header
          style="height: calc(100vh - 265px)"
        >
          <thead>
            <tr>
              <th class="text-left">
                SKU / Name
              </th>
              <th class="text-right">
                Revenue
              </th>
              <th class="text-right">
                Share (%)
              </th>
              <th class="text-center">
                Group
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in abc.items"
              :key="item.sku"
            >
              <td>
                <div class="font-weight-bold text-caption">
                  {{ item.sku }}
                </div>
                <div
                  class="text-truncate"
                  style="max-width: 250px"
                >
                  {{ item.name }}
                </div>
              </td>
              <td class="text-right font-mono">
                {{ formatCurrency(item.revenue) }}
              </td>
              <td class="text-right">
                {{ item.share }}%
              </td>
              <td class="text-center">
                <v-chip
                  :color="getGroupColor(item.group)"
                  size="x-small"
                  label
                  class="font-weight-bold"
                >
                  {{ item.group }}
                </v-chip>
              </td>
            </tr>
            <tr v-if="abc.items.length === 0">
              <td
                colspan="4"
                class="text-center py-10 text-muted-foreground"
              >
                No sales data for the selected period.
              </td>
            </tr>
          </tbody>
        </v-table>
      </CardContent>
    </Card>
  </div>
</template>
