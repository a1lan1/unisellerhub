<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import CreateOrganizationModal from '@/components/organization/CreateOrganizationModal.vue'
import OrdersTable from '@/components/tables/OrdersTable.vue'
import { dashboard } from '@/routes'
import { useAuthStore } from '@/stores/auth'
import { useOrderStore } from '@/stores/order'
import type { Order, Pagination, OrderFilter } from '@/types'

defineProps<{
  orders: Pagination<Order>;
  filters: OrderFilter;
}>()

const authStore = useAuthStore()
const { hasOrganization } = storeToRefs(authStore)

const orderStore = useOrderStore()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Orders', href: '#' }
    ]
  }
})

const showCreateOrgModal = ref(false)

const exportOrders = () => {
  window.location.href = '/exports/orders'
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
        v-if="hasOrganization"
        color="primary"
        variant="elevated"
        density="compact"
        prepend-icon="mdi-microsoft-excel"
        @click="exportOrders"
      >
        Export Excel
      </v-btn>

      <v-btn
        v-if="hasOrganization"
        :loading="orderStore.isSyncing"
        color="success"
        variant="elevated"
        density="compact"
        prepend-icon="mdi-sync"
        @click="orderStore.sync"
      >
        Sync Orders from MP
      </v-btn>
      <v-btn
        v-else
        color="warning"
        variant="elevated"
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
