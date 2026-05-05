<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ShoppingCart, Package } from 'lucide-vue-next'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { dashboard } from '@/routes'
import type { MarketplaceConnection, ActivityLog } from '@/types'
import ActivityFeed from '../../components/activity/ActivityFeed.vue'

defineProps<{
  connection: MarketplaceConnection;
  stats: {
    total_products: number;
    total_orders: number;
    recent_activity: ActivityLog[];
  };
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Marketplace Details', href: '#' }
    ]
  }
})
</script>

<template>
  <Head :title="connection.name" />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">
      {{ connection.name }} Dashboard
    </h1>
    <v-chip
      size="small"
      label
      class="font-weight-bold"
      :color="connection.marketplace === 'wb' ? 'purple' : (connection.marketplace === 'ozon' ? 'blue' : 'grey')"
    >
      {{ connection.marketplace.toUpperCase() }}
    </v-chip>
  </div>

  <!-- Top Stats Widgets -->
  <div class="grid gap-4 md:grid-cols-2">
    <Card>
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">
          Total Products
        </CardTitle>
        <Package class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">
          {{ stats.total_products }}
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium text-muted-foreground">
          Total Orders
        </CardTitle>
        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">
          {{ stats.total_orders }}
        </div>
      </CardContent>
    </Card>
  </div>

  <!-- Recent Activity -->
  <div class="grid gap-6 grid-cols-1">
    <ActivityFeed :initial-activities="stats.recent_activity" />
  </div>
</template>
