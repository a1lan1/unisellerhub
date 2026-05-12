<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted, watch } from 'vue'
import { SidebarProvider } from '@/components/ui/sidebar'
import { snackbar } from '@/plugins/snackbar'
import { useAuthStore } from '@/stores/auth'
import { useMarketplaceStore } from '@/stores/marketplace'
import { useNotificationStore } from '@/stores/notification'
import type { AppVariant, FlashMessage } from '@/types'
import type { Notification } from '@/types'

type Props = {
  variant?: AppVariant;
}

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

const marketplaceStore = useMarketplaceStore()
const { fetchConnections } = marketplaceStore

const notificationStore = useNotificationStore()
const { addNotification } = notificationStore

watch(() => page.props.flash, handleFlashMessages, {
  deep: true,
  immediate: true
})

onMounted(() => {
  if (user.value) {
    // Load marketplace connections for sidebar
    fetchConnections()

    const userChannel = echo().private(`App.Models.User.${user.value.id}`)

    userChannel.notification((notification: Notification) => {
      addNotification(notification)
      snackbar.info({ text: `${notification.title}: ${notification.message}` })
    })

    // Listen for export.ready event to automatically download the file
    userChannel.listen('.export.ready', (e: { fileUrl: string; exportType: string }) => {
      window.open(e.fileUrl, '_blank')
      snackbar.success({ text: `${e.exportType} export file is ready and download started.` })
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
  <div
    v-if="variant === 'header'"
    class="flex min-h-screen w-full flex-col"
  >
    <slot />
  </div>
  <SidebarProvider
    v-else
    :default-open="page.props.sidebarOpen"
  >
    <slot />
  </SidebarProvider>
</template>
