<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import CreateOrganizationModal from '@/components/organization/CreateOrganizationModal.vue'
import InventoryTable from '@/components/tables/InventoryTable.vue'
import { dashboard } from '@/routes'
import { useAuthStore } from '@/stores/auth'
import { useInventoryStore } from '@/stores/inventory'
import type { InventoryItem, Pagination, InventoryFilter } from '@/types'

defineProps<{
  inventory: Pagination<InventoryItem>;
  filters: InventoryFilter;
}>()

const authStore = useAuthStore()
const { hasOrganization } = storeToRefs(authStore)

const inventoryStore = useInventoryStore()

defineOptions({
  layout: {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard()
      }
    ]
  }
})

const showCreateOrgModal = ref(false)

const exportInventory = () => {
  window.location.href = '/exports/inventory'
}
</script>

<template>
  <Head title="Inventory" />

  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold">Inventory & Stocks</h1>

      <div class="flex gap-2">
        <v-btn
          v-if="hasOrganization"
          color="secondary"
          variant="outlined"
          density="compact"
          prepend-icon="mdi-microsoft-excel"
          @click="exportInventory"
        >
          Export
        </v-btn>

        <v-btn
          v-if="hasOrganization"
          :loading="inventoryStore.isSyncing"
          color="secondary"
          variant="outlined"
          density="compact"
          prepend-icon="mdi-sync"
          @click="inventoryStore.syncMoySklad"
        >
          Sync from MoySklad
        </v-btn>

        <v-btn
          v-if="hasOrganization"
          :loading="inventoryStore.isSyncing"
          color="primary"
          variant="elevated"
          density="compact"
          @click="inventoryStore.pullFromMarketplaces"
        >
          Pull all Stocks from MP
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

    <InventoryTable
      :inventory="inventory"
      :filters="filters"
      :has-organization="hasOrganization"
    />

    <CreateOrganizationModal
      v-model="showCreateOrgModal"
      @created="router.reload()"
    />
  </div>
</template>
