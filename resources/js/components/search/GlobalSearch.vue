<script setup lang="ts">
import GlobalSearchInput from '@/components/search/GlobalSearchInput.vue'
import GlobalSearchResults from '@/components/search/GlobalSearchResults.vue'
import { useGlobalSearch } from '@/composables/useGlobalSearch'

const { isOpen, search, results, isLoading, activeResultIndex, navigateTo, closeSearch } = useGlobalSearch()
</script>

<template>
  <div class="relative">
    <v-btn
      variant="tonal"
      rounded="lg"
      class="text-none font-weight-regular justify-start text-muted-foreground"
      width="200"
      @click="isOpen = true"
    >
      <template v-slot:prepend>
        <Search class="mr-2 size-4" />
      </template>
      Search...
      <template v-slot:append>
        <span class="ml-2 rounded border px-1 text-[10px] opacity-50">⌘K</span>
      </template>
    </v-btn>

    <v-dialog v-model="isOpen" max-width="600" scrollable @after-leave="closeSearch">
      <v-card class="overflow-hidden rounded-xl">
        <GlobalSearchInput
          v-model="search"
          :is-loading="isLoading"
          :is-open="isOpen"
          @close="closeSearch"
        />

        <GlobalSearchResults
          :results="results"
          :search-query="search"
          :is-loading="isLoading"
          :active-result-index="activeResultIndex"
          @navigate="navigateTo"
        />

        <v-divider />

        <div class="flex justify-between bg-neutral-50 px-4 py-2 text-[10px] text-muted-foreground">
          <span>↑↓ to navigate</span>
          <span>↵ to select</span>
          <span>esc to close</span>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>
