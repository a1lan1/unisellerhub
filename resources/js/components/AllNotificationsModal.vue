<script setup lang="ts">
import { formatDistanceToNow } from 'date-fns'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import type { Notification } from '@/types'

const props = defineProps<{
  modelValue: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const store = useNotificationStore()
const { notifications, loading, storing, deleting } = storeToRefs(store)
const { markAsRead, markAllAsRead, removeNotification, clearAllNotifications } = store

const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const handleNotificationClick = async(notification: Notification) => {
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }

  if (notification.action_url) {
    window.open(notification.action_url, '_blank')
  }
}

const getIconColor = (type: string) => {
  switch (type) {
  case 'success':
    return 'success'
  case 'error':
    return 'error'
  case 'warning':
    return 'warning'
  default:
    return 'info'
  }
}
</script>

<template>
  <v-dialog
    v-model="dialog"
    max-width="1200"
    scrollable
  >
    <v-card :loading="loading">
      <template #loader="{ isActive }">
        <v-progress-linear
          :active="isActive"
          color="primary"
          height="2"
          indeterminate
        />
      </template>

      <v-toolbar
        density="compact"
        flat
      >
        <v-toolbar-title>Notifications</v-toolbar-title>
        <v-spacer />
        <v-btn
          icon
          @click="dialog = false"
        >
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="pa-0">
        <v-list
          lines="two"
          density="compact"
          variant="plain"
          max-height="500"
          class="p-0 overflow-y-auto"
        >
          <v-list-item
            v-if="notifications.length === 0 && !loading"
            class="py-8 text-center"
          >
            <v-list-item-title class="text-grey">
              No notifications yet
            </v-list-item-title>
          </v-list-item>

          <template
            v-for="(notification, index) in notifications"
            :key="notification.id"
          >
            <v-list-item
              :class="{ 'unread-item': !notification.read_at }"
              class="notification-item"
              @click="handleNotificationClick(notification)"
            >
              <template #prepend>
                <v-avatar
                  :color="getIconColor(notification.type)"
                  size="36"
                >
                  <v-icon
                    color="white"
                    size="20"
                  >
                    {{ notification.icon || 'mdi-information' }}
                  </v-icon>
                </v-avatar>
              </template>

              <v-list-item-title class="text-subtitle-2 font-weight-bold mb-1">
                {{ notification.title }}
              </v-list-item-title>

              <v-list-item-subtitle class="text-caption line-clamp-2">
                {{ notification.message }}
              </v-list-item-subtitle>

              <template #append>
                <div class="align-center text-right">
                  <div class="text-xxs">
                    {{
                      formatDistanceToNow(new Date(notification.created_at), {
                        addSuffix: true,
                      })
                    }}
                  </div>

                  <div class="flex justify-end gap-1">
                    <v-btn
                      v-if="notification.action_url"
                      variant="tonal"
                      color="success"
                      size="x-small"
                      :loading="storing"
                      @click="handleNotificationClick"
                    >
                      Link
                    </v-btn>

                    <v-btn
                      variant="tonal"
                      size="x-small"
                      color="error"
                      :loading="deleting"
                      @click.stop="removeNotification(notification.id)"
                    >
                      <v-icon icon="mdi-close" />
                    </v-btn>
                  </div>
                </div>
              </template>
            </v-list-item>
            <v-divider v-if="index < notifications.length - 1" />
          </template>
        </v-list>
      </v-card-text>

      <v-divider />

      <v-card-actions class="flex justify-end pa-0">
        <v-btn
          variant="tonal"
          size="small"
          color="error"
          :loading="deleting"
          @click="clearAllNotifications"
        >
          Clear All
        </v-btn>
        <v-btn
          variant="tonal"
          size="small"
          color="success"
          :loading="storing"
          @click="markAllAsRead"
        >
          Mark All As Read
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.notification-item {
  cursor: pointer;
  transition: background-color 0.2s;
}

.notification-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.unread-item {
  background-color: rgba(var(--v-theme-primary), 0.03);
  border-left: 4px solid rgb(var(--v-theme-primary));
}

.text-xxs {
  font-size: 0.65rem;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
