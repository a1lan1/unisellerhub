<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted, watch } from 'vue'
import { SidebarProvider } from '@/components/ui/sidebar'
import { snackbar } from '@/plugins/snackbar'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import type { AppVariant, FlashMessage } from '@/types'
import type { UserNotificationEvent } from '@/types'

type Props = {
  variant?: AppVariant;
};

withDefaults(defineProps<Props>(), {
  variant: 'sidebar'
})

const page = usePage()

const handleFlashMessages = (flash: FlashMessage) => {
  if (!snackbar || !flash) {
    return
  }

  if (flash.success) {
    snackbar.success({
      text: flash.success
    })
  }

  if (flash.error) {
    snackbar.error({
      text: flash.error
    })
  }

  if (flash.message) {
    snackbar.info({
      text: flash.message
    })
  }
}

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

const notificationStore = useNotificationStore()
const { addNotification } = notificationStore

watch(() => page.props.flash, handleFlashMessages, {
  deep: true,
  immediate: true
})

onMounted(() => {
  if (user.value) {
    const userChannel = echo().private(`App.Models.User.${user.value.id}`)

    userChannel.notification((notification: any) => {
      addNotification({
        id: notification.id,
        data: notification,
        read_at: null,
        created_at: new Date().toISOString()
      })

      if (snackbar) {
        snackbar.info({
          text: `${notification.title}: ${notification.message}`
        })
      }
    })

    userChannel.listen('.user.notification', (e: UserNotificationEvent) => {
      if (snackbar) {
        snackbar.info({ text: e.message })
      }
    })
  }
})

onUnmounted(() => {
  if (user.value) {
    echo().leave(`App.Models.User.${user.value.id}`)
  }
})
</script>

<template>
  <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
    <slot />
  </div>
  <SidebarProvider v-else :default-open="page.props.sidebarOpen">
    <slot />
  </SidebarProvider>
</template>
