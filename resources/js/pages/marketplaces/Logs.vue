<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { Card, CardContent } from '@/components/ui/card'
import { dashboard } from '@/routes'
import type { MarketplaceConnection, ActivityLog } from '@/types'
import { getActivityIcon, getActivityIconColor } from '@/utils/activity'
import { getMarketplaceColor } from '@/utils/marketplace'

defineProps<{
  connection: MarketplaceConnection;
  logs: {
    data: ActivityLog[];
    links: any[];
    meta: any;
  };
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Sync Logs', href: '#' }
    ]
  }
})
</script>

<template>
  <Head title="Sync Logs" />

  <div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
      <v-btn
        icon="mdi-arrow-left"
        variant="text"
        size="small"
        @click="router.visit(`/marketplaces/${connection.id}`)"
      />

      <h1 class="text-2xl font-bold flex align-center">
        Full Activity History
      </h1>
    </div>
    <v-chip
      size="small"
      label
      class="font-weight-bold"
      :color="getMarketplaceColor(connection.marketplace)"
    >
      {{ connection.marketplace.toUpperCase() }}
    </v-chip>
  </div>

  <Card
    border
    flat
  >
    <CardContent>
      <v-table
        hover
        fixed-header
        class="table-height"
      >
        <thead>
          <tr>
            <th class="text-left">
              Event
            </th>
            <th class="text-left">
              Type
            </th>
            <th class="text-left">
              Date
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="log in logs.data"
            :key="log.id"
          >
            <td>{{ log.description }}</td>
            <td>
              <v-chip
                size="x-small"
                :color="getActivityIconColor(log.properties?.type || 'info')"
                label
              >
                <v-icon
                  start
                  :icon="getActivityIcon(log.properties?.type || 'info')"
                  size="x-small"
                />
                {{ (log.properties?.type || 'INFO').toUpperCase() }}
              </v-chip>
            </td>
            <td class="text-caption text-medium-emphasis">
              {{ log.created_at }} ({{ log.human_time }})
            </td>
          </tr>
          <tr v-if="logs.data.length === 0">
            <td
              colspan="3"
              class="text-center py-10 text-muted-foreground"
            >
              No logs found for this marketplace.
            </td>
          </tr>
        </tbody>
      </v-table>

      <!-- Pagination -->
      <div class="mt-4 flex justify-center">
        <v-pagination
          v-if="logs.meta?.last_page > 1"
          :model-value="logs.meta.current_page"
          :length="logs.meta.last_page"
          @update:model-value="router.visit(`/marketplaces/${connection.id}/logs?page=${$event}`)"
        />
      </div>
    </CardContent>
  </Card>
</template>
