import axios from 'axios'
import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface Notification {
  id: string
  data: {
    title: string
    message: string
    type: 'info' | 'success' | 'warning' | 'error'
    action_url?: string
    icon?: string
  }
  read_at: string | null
  created_at: string
}

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref<Notification[]>([])
  const unreadCount = ref(0)
  const loading = ref(false)

  const fetchNotifications = async() => {
    loading.value = true

    try {
      const response = await axios.get('/api/notifications')
      notifications.value = response.data.notifications
      unreadCount.value = response.data.unread_count
    } catch (error) {
      console.error('Failed to fetch notifications:', error)
    } finally {
      loading.value = false
    }
  }

  const markAsRead = async(id: string) => {
    try {
      await axios.post(`/api/notifications/${id}/read`)
      const notification = notifications.value.find(n => n.id === id)

      if (notification && !notification.read_at) {
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch (error) {
      console.error('Failed to mark notification as read:', error)
    }
  }

  const markAllAsRead = async() => {
    try {
      await axios.post('/api/notifications/read')
      notifications.value.forEach(n => n.read_at = new Date().toISOString())
      unreadCount.value = 0
    } catch (error) {
      console.error('Failed to mark all as read:', error)
    }
  }

  const removeNotification = async(id: string) => {
    try {
      await axios.delete(`/api/notifications/${id}`)
      const index = notifications.value.findIndex(n => n.id === id)

      if (index !== -1) {
        if (!notifications.value[index].read_at) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }

        notifications.value.splice(index, 1)
      }
    } catch (error) {
      console.error('Failed to delete notification:', error)
    }
  }

  const addNotification = (notification: Notification) => {
    notifications.value.unshift(notification)

    if (notifications.value.length > 20) {
      notifications.value.pop()
    }

    unreadCount.value++
  }

  return {
    notifications,
    unreadCount,
    loading,
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    removeNotification,
    addNotification
  }
})
