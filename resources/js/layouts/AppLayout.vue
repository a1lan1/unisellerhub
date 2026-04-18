<script setup lang="ts">
import Snackbar from '@/components/Snackbar.vue'
import AppLayout from '@/layouts/app/AppHeaderLayout.vue'
import type { BreadcrumbItem } from '@/types'

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>()
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Snackbar />
    <slot />
  </AppLayout>
</template>
