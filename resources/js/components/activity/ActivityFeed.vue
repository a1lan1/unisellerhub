<script setup lang="ts">
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { ref, onMounted, onUnmounted } from 'vue'
import ActivityList from '@/components/activity/ActivityList.vue'
import { useAuthStore } from '@/stores/auth'
import type { ActivityLog } from '@/types'

const props = defineProps<{
  initialActivities: ActivityLog[];
}>()

const activities = ref<ActivityLog[]>([...props.initialActivities])
const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

const isOpen = ref(false)

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
  <v-card
    border
    class="flex flex-col"
  >
    <v-card-title
      class="flex align-center cursor-pointer"
      @click="isOpen = true"
    >
      <v-icon
        start
        icon="mdi-history"
        size="small"
      />
      Recent Activity
    </v-card-title>

    <v-divider />

    <ActivityList
      :activities
      style="height: calc(100vh - 360px)"
    />
  </v-card>

  <v-dialog
    v-model="isOpen"
    max-width="600"
    max-height="800"
    scrollable
  >
    <v-card>
      <v-card-actions>
        <v-card-title>Activity Log</v-card-title>
        <v-spacer />
        <v-icon
          icon="mdi-close"
          @click="isOpen = false"
        />
      </v-card-actions>

      <ActivityList :activities />
    </v-card>
  </v-dialog>
</template>
