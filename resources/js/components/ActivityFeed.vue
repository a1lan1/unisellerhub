<script setup lang="ts">
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { ActivityLog } from '@/types'
import { getActivityIcon, getActivityIconColor } from '@/utils/activity'
import { getMarketplaceColor } from '@/utils/marketplace'

const props = defineProps<{
  initialActivities: ActivityLog[];
}>()

const activities = ref<ActivityLog[]>([...props.initialActivities])
const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

onMounted(() => {
  if (user.value?.organization_id) {
    echo()
      .private(`organization.${user.value.organization_id}`)
      .listen('.activity.new', (e: any) => {
        activities.value.unshift(e.activity)

        if (activities.value.length > 20) {
          activities.value.pop()
        }
      })
  }
})

onUnmounted(() => {
  if (user.value?.organization_id) {
    echo().leave(`organization.${user.value.organization_id}`)
  }
})
</script>

<template>
  <v-card class="flex flex-col">
    <v-card-title class="text-subtitle-1 font-weight-bold d-flex align-center">
      <v-icon start icon="mdi-history" size="small"></v-icon>
      Recent Activity
    </v-card-title>

    <v-divider />

    <v-list lines="two" class="overflow-y-auto flex-grow-1" style="max-height: 470px">
      <v-list-item
        v-for="activity in activities"
        :key="activity.id"
        :subtitle="activity.human_time"
      >
        <template v-slot:prepend>
          <v-avatar :color="getActivityIconColor(activity.properties?.type || 'info')" size="small">
            <v-icon :icon="getActivityIcon(activity.properties?.type || 'info')" color="white" size="x-small"></v-icon>
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

      <v-list-item v-if="activities.length === 0" class="text-center py-10">
        <v-list-item-title class="text-neutral-400">No activity yet</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-card>
</template>
