<script setup lang="ts">
import { storeToRefs } from 'pinia'
import NotificationBell from '@/components/NotificationBell.vue'
import GlobalSearch from '@/components/search/GlobalSearch.vue'
import SyncAllBtn from '@/components/shared/SyncAllBtn.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import UserMenuContent from '@/components/UserMenuContent.vue'
import { getInitials } from '@/composables/useInitials'
import { useAuthStore } from '@/stores/auth'
import type { BreadcrumbItem } from '@/types'

type Props = {
  breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => []
})

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)
</script>

<template>
  <div class="header-sticky border-sidebar-border/80">
    <div class="flex flex-row w-full glass glass-border glass-shadow rounded-lg text-white h-14 justify-between items-center px-4">
      <slot />

      <div class="ml-auto flex items-center space-x-4">
        <!-- Search Widget -->
        <GlobalSearch />
        <!-- Sync All Button -->
        <SyncAllBtn />
        <!-- Notifications -->
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
                  v-if="user.avatar"
                  :src="user.avatar"
                  :alt="user.name"
                />
                <AvatarFallback
                  class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                >
                  {{ getInitials(user?.name) }}
                </AvatarFallback>
              </Avatar>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="end"
            class="w-56"
          >
            <UserMenuContent :user="user" />
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </div>
</template>
