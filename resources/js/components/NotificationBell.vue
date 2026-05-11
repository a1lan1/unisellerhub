<script setup lang="ts">
import { formatDistanceToNow } from 'date-fns'
import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'
import AllNotificationsModal from '@/components/AllNotificationsModal.vue'
import { useNotificationStore } from '@/stores/notification'
import type { Notification } from '@/types'

const store = useNotificationStore()
const { notifications, unreadCount, loading, storing, deleting } = storeToRefs(store)
const { fetchNotifications, removeNotification, markAsRead, markAllAsRead, clearAllNotifications } = store

const showAllNotificationsModal = ref(false)

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

const displayDate = (date?: string) => {
  return date
    ? formatDistanceToNow(new Date(date), { addSuffix: true })
    : date
}

onMounted(fetchNotifications)
</script>

<template>
  <v-menu
    :close-on-content-click="false"
    location="bottom end"
    transition="scale-transition"
  >
    <template #activator="{ props }">
      <v-btn
        icon
        v-bind="props"
        class="mr-2"
        variant="text"
        :loading="loading || storing || deleting"
      >
        <v-badge
          v-if="unreadCount > 0"
          color="error"
          :content="unreadCount"
          offset-x="2"
          offset-y="2"
        >
          <v-icon size="24">
            mdi-bell-outline
          </v-icon>
        </v-badge>
        <v-icon
          v-else
          size="24"
        >
          mdi-bell-outline
        </v-icon>
      </v-btn>
    </template>

    <v-card
      width="450"
      class="notification-card mx-auto"
      :loading="loading"
    >
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
        <div class="flex gap-1">
          <v-btn
            v-if="notifications.length > 0"
            variant="tonal"
            color="error"
            size="x-small"
            :loading="deleting"
            @click="clearAllNotifications"
          >
            Clear All
          </v-btn>
          <v-btn
            v-if="unreadCount > 0"
            variant="tonal"
            color="success"
            size="x-small"
            :loading="storing"
            @click="markAllAsRead"
          >
            Mark all read
          </v-btn>
        </div>
      </v-toolbar>

      <v-list
        lines="two"
        density="compact"
        variant="plain"
        color="success"
        max-height="450"
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
                  {{ displayDate(notification.created_at) }}
                </div>
                <v-btn
                  icon="mdi-close"
                  variant="plain"
                  size="x-small"
                  color="warning"
                  class="delete-btn"
                  :loading="deleting"
                  @click.stop="removeNotification(notification.id)"
                />
              </div>
            </template>
          </v-list-item>
          <v-divider v-if="index < notifications.length - 1" />
        </template>
      </v-list>

      <v-card-actions
        v-if="notifications.length > 0"
        class="p-0"
      >
        <v-btn
          variant="tonal"
          size="small"
          block
          color="primary"
          density="comfortable"
          @click="showAllNotificationsModal = true"
        >
          View all notifications
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>

  <AllNotificationsModal v-model="showAllNotificationsModal" />
</template>

<style scoped>
.notification-card {
  border-radius: 12px;
  overflow: hidden;
}

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

.delete-btn {
  opacity: 0;
  transition: opacity 0.2s;
}

.notification-item:hover .delete-btn {
  opacity: 1;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
