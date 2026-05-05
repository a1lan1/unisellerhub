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

    <v-btn
      v-if="hasOrganization"
      :loading="productStore.isSyncing"
      color="success"
      variant="elevated"
      density="compact"
      prepend-icon="mdi-sync"
      @click="productStore.sync"
    >
      Sync Products
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
