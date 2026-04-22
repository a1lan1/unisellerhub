<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ShoppingCart, TrendingUp, Wallet } from 'lucide-vue-next'
import { computed } from 'vue'
import ActivityFeed from '@/components/ActivityFeed.vue'
import BaseDonutChart from '@/components/charts/BaseDonutChart.vue'
import BaseLineChart from '@/components/charts/BaseLineChart.vue'
import InventoryHealth from '@/components/InventoryHealth.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { dashboard } from '@/routes'
import { getMarketplaceColor } from '@/utils/marketplace'

interface Activity {
  id: number;
  description: string;
  properties: any;
  created_at: string;
  human_time: string;
}

interface InventoryStats {
  out_of_stock: number;
  low_stock: number;
  total_items: number;
}

const props = defineProps<{
  stats: {
    today_orders: number;
    today_sales: number;
    trend: { date: string; aggregate: number }[];
    distribution: { marketplace: string; count: number }[];
  };
  inventory_stats: InventoryStats;
  activities: Activity[];
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard()
      }
    ]
  }
})

// Prepare data for Sales Trend Chart
const trendSeries = computed(() => [{
  name: 'Total Sales',
  data: props.stats.trend.map(t => t.aggregate)
}])

const trendCategories = computed(() => props.stats.trend.map(t => t.date))

// Prepare data for Distribution Chart
const distributionSeries = computed(() => props.stats.distribution.map(d => d.count))
const distributionLabels = computed(() => props.stats.distribution.map(d => d.marketplace.toUpperCase()))
const distributionColors = computed(() => props.stats.distribution.map(d => getMarketplaceColor(d.marketplace)))

</script>

<template>
  <Head title="Dashboard" />

  <div class="pb-6 px-2 space-y-2">
    <!-- Top Stats Widgets -->
    <div class="grid gap-4 md:grid-cols-3">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle class="text-sm font-medium text-muted-foreground">Today's Sales</CardTitle>
          <Wallet class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.today_sales }} ₽</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle class="text-sm font-medium text-muted-foreground">Today's Orders</CardTitle>
          <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.today_orders }}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0">
          <CardTitle class="text-sm font-medium text-muted-foreground">Average Check</CardTitle>
          <TrendingUp class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            {{ stats.today_orders > 0 ? (stats.today_sales / stats.today_orders).toFixed(0) : 0 }} ₽
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Main Content Area -->
    <div class="grid gap-4 grid-cols-1 lg:grid-cols-3">
      <!-- Left Column: Charts -->
      <div class="lg:col-span-2 space-y-2">
        <Card>
          <CardHeader>
            <CardTitle>Sales Trend</CardTitle>
          </CardHeader>
          <CardContent>
            <BaseLineChart
              :series="trendSeries"
              :categories="trendCategories"
              title="Revenue over time"
              :height="300"
            />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Orders Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <BaseDonutChart
              :series="distributionSeries"
              :labels="distributionLabels"
              :colors="distributionColors"
              title="By Marketplace"
              :height="300"
            />
          </CardContent>
        </Card>
      </div>

      <!-- Right Column: Stats & Feed -->
      <div class="lg:col-span-1 space-y-2">
        <InventoryHealth :stats="inventory_stats" />
        <ActivityFeed :initial-activities="activities" />
      </div>
    </div>
  </div>
</template>
