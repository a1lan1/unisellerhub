<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import CreateOrganizationModal from '@/components/organization/CreateOrganizationModal.vue'
import InventoryTable from '@/components/tables/InventoryTable.vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import { dashboard } from '@/routes'
import { useAuthStore } from '@/stores/auth'
import { useInventoryStore } from '@/stores/inventory'
import type { InventoryItem, Pagination, InventoryFilter } from '@/types'

const props = defineProps<{
  inventory: Pagination<InventoryItem>;
  filters: InventoryFilter;
}>()

const authStore = useAuthStore()
const { hasOrganization } = storeToRefs(authStore)

const inventoryStore = useInventoryStore()
const { isSyncing } = storeToRefs(inventoryStore)
const { syncMoySklad, pullFromMarketplaces } = inventoryStore

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Inventory', href: '#' }
    ]
  }
})

const showCreateOrgModal = ref(false)
const exportLoading = ref(false)

const exportInventory = async() => {
  exportLoading.value = true

  try {
    const { data } = await api.post('/api/exports/inventory', props.filters)
    snackbar.success({ text: data.message })
  } catch (error) {
    console.error('Error exporting inventory:', error)
    snackbar.error({ text: 'Failed to start inventory export.' })
  } finally {
    exportLoading.value = false
  }
}
</script>

<template>
  <Head title="Inventory" />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">
      Inventory & Stocks
    </h1>

    <div class="flex gap-2">
      <v-btn
        v-if="hasOrganization"
        color="primary"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-microsoft-excel"
        :loading="exportLoading"
        @click="exportInventory"
      >
        Export
      </v-btn>

      <v-btn
        v-if="hasOrganization"
        :loading="isSyncing"
        color="success"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-sync"
        @click="syncMoySklad"
      >
        Sync from MoySklad
      </v-btn>

      <v-btn
        v-if="hasOrganization"
        :loading="isSyncing"
        color="success"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-sync"
        @click="pullFromMarketplaces"
      >
        Pull Stocks from MP
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

  <InventoryTable
    :inventory="inventory"
    :filters="filters"
    :has-organization="hasOrganization"
  />

  <CreateOrganizationModal
    v-model="showCreateOrgModal"
    @created="router.reload()"
  />
</template>
