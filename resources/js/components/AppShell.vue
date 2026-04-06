<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { onMounted, onUnmounted, watch } from 'vue'
import { SidebarProvider } from '@/components/ui/sidebar'
import { snackbar } from '@/plugins/snackbar'
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

watch(() => page.props.flash, handleFlashMessages, { deep: true, immediate: true })

onMounted(() => {
  if (page.props.auth.user) {
    echo()
      .private(`App.Models.User.${page.props.auth.user.id}`)
      .listen('.user.notification', (e: UserNotificationEvent) => {
        if (snackbar) {
          snackbar.info({
            text: e.message
          })
        }
      })
  }
})

onUnmounted(() => {
  if (page.props.auth.user) {
    echo().leave(`App.Models.User.${page.props.auth.user.id}`)
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
