<script setup lang="ts">
import { ref } from 'vue'
import ReviewForm from '@/components/review/ReviewForm.vue'

defineProps<{
  locationId?: number | undefined;
  authorName: string;
}>()

const model = ref(false)
</script>

<template>
  <VDialog
    v-model="model"
    max-width="600px"
  >
    <template #activator="{ props }">
      <VBtn
        v-bind="props"
        color="primary"
        variant="tonal"
        prepend-icon="mdi-comment-plus"
      >
        Review Form
      </VBtn>
    </template>

    <ReviewForm
      :location-id
      :author-name
      @success="model = false"
    />
  </VDialog>
</template>
