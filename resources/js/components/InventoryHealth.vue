<script setup lang="ts">
import { AlertCircle, PackageSearch, PackageX } from 'lucide-vue-next'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

interface Props {
  stats: {
    out_of_stock: number;
    low_stock: number;
    total_items: number;
  }
}

defineProps<Props>()
</script>

<template>
  <Card border flat>
    <CardHeader>
      <CardTitle class="text-subtitle-1 font-weight-bold flex align-center">
        <PackageSearch class="mr-2 h-4 w-4" />
        Inventory Health
      </CardTitle>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-2 gap-4">
        <!-- Out of Stock -->
        <div class="p-4 rounded-lg bg-red-50 border border-red-100 flex flex-col items-center text-center">
          <PackageX class="h-8 w-8 text-red-500 mb-2" />
          <div class="text-2xl font-bold text-red-700">{{ stats.out_of_stock }}</div>
          <div class="text-xs text-red-600 uppercase font-medium">Out of Stock</div>
        </div>

        <!-- Low Stock -->
        <div class="p-4 rounded-lg bg-orange-50 border border-orange-100 flex flex-col items-center text-center">
          <AlertCircle class="h-8 w-8 text-orange-500 mb-2" />
          <div class="text-2xl font-bold text-orange-700">{{ stats.low_stock }}</div>
          <div class="text-xs text-orange-600 uppercase font-medium">Low Stock</div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t">
        <div class="flex justify-between items-center text-sm">
          <span class="text-muted-foreground">Total Skus tracked:</span>
          <span class="font-bold">{{ stats.total_items }}</span>
        </div>
        <v-progress-linear
          class="mt-2"
          :model-value="((stats.total_items - stats.out_of_stock) / stats.total_items) * 100"
          color="success"
          height="6"
          rounded
        ></v-progress-linear>
      </div>
    </CardContent>
  </Card>
</template>
