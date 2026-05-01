<script setup lang="ts">
import { Package, ShoppingCart, Search } from 'lucide-vue-next'
import type { SearchResultItem } from '@/types'

defineProps<{
  results: SearchResultItem[];
  searchQuery: string;
  isLoading: boolean;
  activeResultIndex: number;
}>()

const emit = defineEmits(['navigate'])

const navigateToItem = (url: string) => {
  emit('navigate', url)
}
</script>

<template>
  <v-card-text class="pa-0" style="max-height: 400px">
    <v-list v-if="results.length > 0" lines="two">
      <v-list-item
        v-for="(item, i) in results"
        :key="item.id"
        :title="item.title"
        :subtitle="item.subtitle"
        :active="i === activeResultIndex"
        @click="navigateToItem(item.url)"
      >
        <template v-slot:prepend>
          <v-avatar size="36" color="neutral-100" class="mr-3">
            <Package
              v-if="item.type === 'product'"
              class="size-4 text-neutral-600"
            />
            <ShoppingCart v-else class="size-4 text-neutral-600" />
          </v-avatar>
        </template>

        <template v-slot:append>
          <v-chip size="x-small" label class="text-uppercase">
            {{ item.type }}
          </v-chip>
        </template>
      </v-list-item>
    </v-list>
    <div
      v-else-if="searchQuery.length >= 2 && !isLoading"
      class="flex h-full flex-col items-center justify-center text-muted-foreground opacity-50"
    >
      <Search class="mb-4 size-12" />
      <p>No results found for "{{ searchQuery }}"</p>
    </div>
    <div
      v-else
      class="flex h-full flex-col items-center justify-center text-muted-foreground opacity-30"
    >
      <p>Type at least 2 characters to search</p>
      <p class="mt-2 text-xs italic">Search is powered by Meilisearch</p>
    </div>
  </v-card-text>
</template>
