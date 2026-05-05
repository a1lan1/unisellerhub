<script setup lang="ts">
import type { ActivityLog } from '@/types'
import { getActivityIcon, getActivityIconColor } from '@/utils/activity'
import { getMarketplaceColor } from '@/utils/marketplace'

defineProps<{
  activities: ActivityLog[];
}>()

</script>

<template>
  <v-list
    lines="two"
    class="overflow-y-auto grow"
  >
    <v-list-item
      v-for="activity in activities"
      :key="activity.id"
      :subtitle="activity.human_time"
    >
      <template #prepend>
        <v-avatar
          :color="getActivityIconColor(activity.properties?.type || 'info')"
          size="small"
        >
          <v-icon
            :icon="getActivityIcon(activity.properties?.type || 'info')"
            color="white"
            size="x-small"
          />
        </v-avatar>
      </template>

      <v-list-item-title class="text-body-2">
        {{ activity.description }}
        <v-chip
          v-if="activity.properties?.marketplace"
          size="x-small"
          class="ml-1"
          label
          :color="getMarketplaceColor(activity.properties.marketplace)"
        >
          {{ String(activity.properties.marketplace).toUpperCase() }}
        </v-chip>
      </v-list-item-title>
    </v-list-item>

    <v-list-item
      v-if="activities.length === 0"
      class="text-center py-10"
    >
      <v-list-item-title class="text-neutral-400">
        No activity yet
      </v-list-item-title>
    </v-list-item>
  </v-list>
</template>
