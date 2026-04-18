<script setup lang="ts">
import AppContent from '@/components/AppContent.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'
import type { BreadcrumbItem } from '@/types'

type Props = {
  breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => []
})
</script>

<template>
  <AppShell variant="sidebar">
    <AppContent variant="header">
      <AppHeader>
        <AppSidebarHeader :breadcrumbs="breadcrumbs" />
      </AppHeader>

      <slot />

      <AppSidebar />
    </AppContent>
  </AppShell>
</template>
