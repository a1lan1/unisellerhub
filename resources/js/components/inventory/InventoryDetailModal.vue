<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { InventoryItem, Product } from '@/types'

interface Props {
  modelValue: boolean;
  title: string;
  description: string;
  items: InventoryItem[];
}

const props = defineProps<Props>()
const emit = defineEmits(['update:modelValue'])

const dialogVisible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const navigateToProduct = (product: Product) => {
  router.visit(`/products?search=${product.sku}`)
}
</script>

<template>
  <v-dialog
    v-model="dialogVisible"
    max-width="800"
  >
    <v-card>
      <v-card-actions class="pb-0">
        <v-card-title class="headline">
          {{ title }}
        </v-card-title>
        <v-spacer />
        <v-icon
          icon="mdi-close"
          @click="dialogVisible = false"
        />
      </v-card-actions>

      <v-card-subtitle>{{ description }}</v-card-subtitle>

      <v-card-text>
        <v-table
          fixed-header
          height="400px"
          hover
        >
          <thead>
            <tr>
              <th class="text-left">
                Product Name
              </th>
              <th class="text-left">
                SKU
              </th>
              <th class="text-left">
                Warehouse
              </th>
              <th class="text-right">
                Quantity
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td
                colspan="4"
                class="text-center"
              >
                No items to display.
              </td>
            </tr>
            <tr
              v-for="item in items"
              :key="item.id"
              class="cursor-pointer"
              @click="navigateToProduct(item.listing.product)"
            >
              <td>{{ item.listing.product.name }}</td>
              <td>{{ item.listing.vendor_code }}</td>
              <td>{{ item.warehouse.name }}</td>
              <td class="text-right">
                {{ item.quantity }}
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>
