<script setup lang="ts">
import { formatDistanceToNow } from 'date-fns'
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'

const store = useNotificationStore()
const { notifications, unreadCount, loading } = storeToRefs(store)
const { fetchNotifications, removeNotification, markAsRead, markAllAsRead } = store

const handleNotificationClick = async(notification: any) => {
  if (!notification.read_at) {
    await markAsRead(notification.id)
  }

  if (notification.data.action_url) {
    window.open(notification.data.action_url, '_blank')
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

onMounted(() => {
  fetchNotifications()
})
</script>

<template>
    <v-menu
        :close-on-content-click="false"
        location="bottom end"
        transition="scale-transition"
    >
        <template v-slot:activator="{ props }">
            <v-btn icon v-bind="props" class="mr-2" variant="text">
                <v-badge
                    v-if="unreadCount > 0"
                    color="error"
                    :content="unreadCount"
                    offset-x="2"
                    offset-y="2"
                >
                    <v-icon size="24">mdi-bell-outline</v-icon>
                </v-badge>
                <v-icon v-else size="24">mdi-bell-outline</v-icon>
            </v-btn>
        </template>

        <v-card
            width="380"
            class="notification-card mx-auto"
            :loading="loading"
        >
            <template v-slot:loader="{ isActive }">
                <v-progress-linear
                    :active="isActive"
                    color="primary"
                    height="2"
                    indeterminate
                />
            </template>

            <v-toolbar color="primary" density="compact" flat>
                <v-toolbar-title class="text-subtitle-1 font-weight-bold"
                    >Notifications</v-toolbar-title
                >
                <v-spacer />
                <v-btn
                    variant="text"
                    size="small"
                    @click="markAllAsRead"
                    v-if="unreadCount > 0"
                >
                    Mark all read
                </v-btn>
            </v-toolbar>

            <v-list lines="three" max-height="450" class="pa-0 overflow-y-auto">
                <v-list-item
                    v-if="notifications.length === 0 && !loading"
                    class="py-8 text-center"
                >
                    <v-list-item-title class="text-grey"
                        >No notifications yet</v-list-item-title
                    >
                </v-list-item>

                <template v-for="(item, index) in notifications" :key="item.id">
                    <v-list-item
                        :class="{ 'unread-item': !item.read_at }"
                        class="notification-item py-3"
                        @click="handleNotificationClick(item)"
                    >
                        <template v-slot:prepend>
                            <v-avatar
                                :color="getIconColor(item.data.type)"
                                size="36"
                                class="mr-3"
                            >
                                <v-icon color="white" size="20">{{
                                    item.data.icon || 'mdi-information'
                                }}</v-icon>
                            </v-avatar>
                        </template>

                        <v-list-item-title
                            class="text-subtitle-2 font-weight-bold mb-1"
                        >
                            {{ item.data.title }}
                        </v-list-item-title>

                        <v-list-item-subtitle class="text-caption line-clamp-2">
                            {{ item.data.message }}
                        </v-list-item-subtitle>

                        <template v-slot:append>
                            <div class="d-flex flex-column align-end">
                                <div class="text-grey-darken-1 text-xxs mb-2">
                                    {{
                                        formatDistanceToNow(
                                            new Date(item.created_at),
                                            { addSuffix: true },
                                        )
                                    }}
                                </div>
                                <v-btn
                                    icon="mdi-close"
                                    variant="text"
                                    size="x-small"
                                    color="grey-lighten-1"
                                    class="delete-btn"
                                    @click.stop="removeNotification(item.id)"
                                />
                            </div>
                        </template>
                    </v-list-item>
                    <v-divider
                        v-if="index < notifications.length - 1"
                    ></v-divider>
                </template>
            </v-list>

            <v-divider></v-divider>
            <v-card-actions class="pa-2">
                <v-btn variant="text" size="small" block color="primary"
                    >View all notifications</v-btn
                >
            </v-card-actions>
        </v-card>
    </v-menu>
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
