<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import CreateOrganizationModal from '@/components/organization/CreateOrganizationModal.vue'
import ProductsTable from '@/components/tables/ProductsTable.vue'
import { dashboard } from '@/routes'
import { useAuthStore } from '@/stores/auth'
import { useProductStore } from '@/stores/product'
import type { Product, Pagination, ProductFilter } from '@/types'

defineProps<{
  products: Pagination<Product>;
  filters: ProductFilter;
}>()

const vectorSearchEnabled = import.meta.env.VITE_VECTOR_SEARCH_ENABLED === 'true'

const authStore = useAuthStore()
const { hasOrganization } = storeToRefs(authStore)

const productStore = useProductStore()
const { isSyncing, isAnalyzingPrices } = storeToRefs(productStore)
const { sync, analyzePrices } = productStore

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Products', href: '#' }
    ]
  }
})

const showCreateOrgModal = ref(false)
</script>

<template>
  <Head title="Products" />

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold">
      Product Listings
    </h1>

    <div class="flex gap-2">
      <v-btn
        v-if="hasOrganization"
        :loading="isAnalyzingPrices"
        color="primary"
        variant="tonal"
        density="compact"
        prepend-icon="mdi-chart-line"
        @click="analyzePrices"
      >
        Analyze Prices
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
        Sync Products
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

  <ProductsTable
    :products="products"
    :filters="filters"
    :vector-search-enabled="vectorSearchEnabled"
  />

  <CreateOrganizationModal
    v-model="showCreateOrgModal"
    @created="router.reload()"
  />
</template>
