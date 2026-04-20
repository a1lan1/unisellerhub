<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import NotificationBell from '@/components/NotificationBell.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import UserMenuContent from '@/components/UserMenuContent.vue'
import { getInitials } from '@/composables/useInitials'
import type { BreadcrumbItem } from '@/types'

type Props = {
  breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => []
})

const page = usePage()
const auth = computed(() => page.props.auth)
</script>

<template>
  <div class="border-b border-sidebar-border/80">
    <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
      <slot />

      <div class="ml-auto flex items-center space-x-4">
        <NotificationBell />

        <DropdownMenu>
          <DropdownMenuTrigger :as-child="true">
            <Button
              variant="ghost"
              size="icon"
              class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
            >
              <Avatar class="size-8 overflow-hidden rounded-full">
                <AvatarImage
                  v-if="auth.user.avatar"
                  :src="auth.user.avatar"
                  :alt="auth.user.name"
                />
                <AvatarFallback
                  class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                >
                  {{ getInitials(auth.user?.name) }}
                </AvatarFallback>
              </Avatar>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="end"
            class="w-56"
          >
            <UserMenuContent :user="auth.user" />
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </div>
</template>
