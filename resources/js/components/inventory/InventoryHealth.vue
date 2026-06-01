<script setup lang="ts">
import { AlertCircle, PackageX } from 'lucide-vue-next'
import { ref } from 'vue'
import InventoryDetailModal from '@/components/inventory/InventoryDetailModal.vue'
import type { InventoryItem } from '@/types'

interface Props {
  stats: {
    out_of_stock: number;
    low_stock: number;
    total_items: number;
    out_of_stock_items: InventoryItem[];
    low_stock_items: InventoryItem[];
  }
}

defineProps<Props>()

const isOutOfStockModalOpen = ref(false)
const isLowStockModalOpen = ref(false)

const openOutOfStockModal = () => {
  isOutOfStockModalOpen.value = true
}

const openLowStockModal = () => {
  isLowStockModalOpen.value = true
}
</script>

<template>
  <v-card border>
    <v-card-title class="flex align-center">
      <v-icon
        icon="mdi-package-check"
        size="22"
      />
      Inventory Health
    </v-card-title>
    <v-card-text>
      <div class="grid grid-cols-2 gap-4">
        <!-- Out of Stock -->
        <div
          class="p-4 rounded-lg glass glass-border flex flex-col items-center text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          @click="openOutOfStockModal"
        >
          <PackageX class="h-8 w-8 text-red-600 mb-2" />
          <div class="text-2xl font-bold text-red-700">
            {{ stats.out_of_stock }}
          </div>
          <div class="text-xs text-red-600 uppercase font-medium">
            Out of Stock
          </div>
        </div>

        <!-- Low Stock -->
        <div
          class="p-4 rounded-lg glass glass-border flex flex-col items-center text-center cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
          @click="openLowStockModal"
        >
          <AlertCircle class="h-8 w-8 text-orange-600 mb-2" />
          <div class="text-2xl font-bold text-orange-700">
            {{ stats.low_stock }}
          </div>
          <div class="text-xs text-orange-600 uppercase font-medium">
            Low Stock
          </div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t">
        <div class="d-flex justify-space-between align-center text-sm">
          <span class="text-muted-foreground">Total Skus tracked:</span>
          <span class="font-bold">{{ stats.total_items }}</span>
        </div>
        <v-progress-linear
          class="mt-2"
          :model-value="((stats.total_items - stats.out_of_stock) / stats.total_items) * 100"
          color="success"
          height="6"
          rounded
        />
      </div>
    </v-card-text>
  </v-card>

  <InventoryDetailModal
    v-model="isOutOfStockModalOpen"
    title="Out of Stock Items"
    description="Detailed list of items that are currently out of stock."
    :items="stats.out_of_stock_items"
  />

  <InventoryDetailModal
    v-model="isLowStockModalOpen"
    title="Low Stock Items"
    description="Detailed list of items that are currently low on stock (quantity less than 10)."
    :items="stats.low_stock_items"
  />
</template>
