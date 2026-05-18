<script setup lang="ts">
import AppContent from '@/components/AppContent.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'
import Background from '@/components/theme/Background.vue'
import type { BreadcrumbItem } from '@/types'

type Props = {
  breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => []
})
</script>

<template>
  <Background />
  <AppShell variant="sidebar">
    <AppContent variant="header">
      <AppHeader>
        <AppSidebarHeader :breadcrumbs />
      </AppHeader>

      <div class="p-2 space-y-4">
        <slot />
      </div>

      <AppSidebar />
    </AppContent>
  </AppShell>
</template>
