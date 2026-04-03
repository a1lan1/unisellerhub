<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { echo } from '@laravel/echo-vue'
import { onMounted, onUnmounted } from 'vue'
import { SidebarProvider } from '@/components/ui/sidebar'
import type { AppVariant } from '@/types'
import type { UserNotificationEvent } from '@/types'

type Props = {
    variant?: AppVariant;
};

withDefaults(defineProps<Props>(), {
  variant: 'sidebar'
})

const page = usePage()

onMounted(() => {
  if (page.props.auth.user) {
    echo()
      .private(`App.Models.User.${page.props.auth.user.id}`)
      .listen('.user.notification', (e: UserNotificationEvent) => {
        console.log(e.message)
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
