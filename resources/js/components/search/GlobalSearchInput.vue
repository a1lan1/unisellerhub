<script setup lang="ts">
import { useFocus } from '@vueuse/core'
import { Search, Loader2 } from 'lucide-vue-next'
import { nextTick, ref, watch } from 'vue'

const props = defineProps<{
  modelValue: string;
  isLoading: boolean;
  isOpen: boolean;
}>()

const emit = defineEmits(['update:modelValue', 'close'])

const searchInput = ref<HTMLInputElement | null>(null)
const { focused } = useFocus(searchInput)

watch(
  () => props.isOpen,
  (val) => nextTick(() => (focused.value = val)),
  { immediate: true }
)

const updateSearch = (event: Event) => {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>

<template>
  <div class="flex items-center gap-3 border-b p-4">
    <Search class="size-5 text-muted-foreground" />
    <input
      ref="searchInput"
      :value="modelValue"
      @input="updateSearch"
      type="text"
      placeholder="Search SKU, products or orders..."
      class="grow border-none bg-transparent text-lg outline-none"
    />
    <Loader2 v-if="isLoading" class="size-5 animate-spin text-primary" />
    <v-btn
      icon="mdi-close"
      variant="text"
      size="small"
      @click="emit('close')"
    />
  </div>
</template>
