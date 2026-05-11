<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import CreateOrganizationModal from '@/components/organization/CreateOrganizationModal.vue'
import OrdersTable from '@/components/tables/OrdersTable.vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { dashboard } from '@/routes'
import { useAuthStore } from '@/stores/auth'
import { useOrderStore } from '@/stores/order'
import type { Order, Pagination, OrderFilter } from '@/types'

const props = defineProps<{
  orders: Pagination<Order>;
  filters: OrderFilter;
}>()

const authStore = useAuthStore()
const { hasOrganization } = storeToRefs(authStore)

const orderStore = useOrderStore()
const { isSyncing } = storeToRefs(orderStore)
const { sync } = orderStore

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Orders', href: '#' }
    ]
  }
})

const showCreateOrgModal = ref(false)
const exportLoading = ref(false)

const exportOrders = async() => {
  exportLoading.value = true

  try {
    const { data } = await api.post('/api/exports/orders', props.filters)
    snackbar.success({ text: data.message })
  } catch (error) {
    console.error('Error exporting orders:', error)
    snackbar.error({ text: 'Failed to start orders export.' })
  } finally {
    exportLoading.value = false
  }
}
</script>

<template>
  <Head title="Orders" />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">
      Orders Management
    </h1>

    <div class="flex gap-2">
      <v-btn
        v-if="hasOrganization && orders.data.length"
        color="primary"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-microsoft-excel"
        :loading="exportLoading"
        @click="exportOrders"
      >
        Export Excel
      </v-btn>

      <v-btn
        v-if="hasOrganization"
        :loading="isSyncing"
        color="success"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-sync"
        @click="sync"
      >
        Sync Orders from MP
      </v-btn>
      <v-btn
        v-else
        color="warning"
        variant="tonal"
        density="compact"
        @click="showCreateOrgModal = true"
      >
        Create Organization
      </v-btn>
    </div>
  </div>

  <OrdersTable
    :orders="orders"
    :filters="filters"
  />

  <CreateOrganizationModal
    v-model="showCreateOrgModal"
    @created="router.reload()"
  />
</template>
