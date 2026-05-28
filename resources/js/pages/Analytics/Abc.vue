<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { BarChart3 } from 'lucide-vue-next'
import { computed, ref, watch, onMounted } from 'vue'
import BaseDonutChart from '@/components/charts/BaseDonutChart.vue'
import DaysRangeSlider from '@/components/DaysRangeSlider.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { dashboard } from '@/routes'
import { abc as abcAnalytics } from '@/routes/analytics'
import type { AbcItem } from '@/types'

const props = defineProps<{
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

const currentSelectedDays = ref(props.days || 30)
const currentSelectedEndDate = ref(format(new Date(), 'yyyy-MM-dd'))

watch(currentSelectedDays, (newDays) => {
  router.get(abcAnalytics(), { endDate: currentSelectedEndDate.value, days: newDays }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
})

onMounted(() => {
  if (!props.days) {
    currentSelectedDays.value = 30
  }
})

const chartSeries = computed(() => [
  props.abc.summary.A,
  props.abc.summary.B,
  props.abc.summary.C
])

const chartLabels = ['Group A', 'Group B', 'Group C']
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
      days: currentSelectedDays.value,
      endDate: currentSelectedEndDate.value
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

  <DaysRangeSlider
    :initial-days="currentSelectedDays"
    @update:days="currentSelectedDays = $event"
  />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold flex items-center gap-2">
      <BarChart3 class="text-primary" />
      ABC Analysis ({{ currentSelectedDays }} Days ending {{ currentSelectedEndDate }})
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
            <span>{{ props.abc.summary.A }} SKUs</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="font-medium text-warning">Group B (Regulars)</span>
            <span>{{ props.abc.summary.B }} SKUs</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="font-medium text-error">Group C (Long Tail)</span>
            <span>{{ props.abc.summary.C }} SKUs</span>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="lg:col-span-2 gap-0">
      <CardHeader>
        <CardTitle>Performance by SKU</CardTitle>
      </CardHeader>
      <CardContent>
        <v-table
          hover
          density="comfortable"
          fixed-header
          style="height: calc(100vh - 330px)"
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
              v-for="item in props.abc.items"
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
                {{ item.revenue.formatted }}
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
            <tr v-if="props.abc.items.length === 0">
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
