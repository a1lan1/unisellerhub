<script setup lang="ts">
import { echo } from '@laravel/echo-vue'
import { ref, watchEffect } from 'vue'
import ReviewReplyDialog from '@/components/review/ReviewReplyDialog.vue'
import SentimentChip from '@/components/shared/SentimentChip.vue'
import { useReviewStore } from '@/stores/review'
import type { Review } from '@/types/geo'

const props = defineProps<{
  locationId?: number;
  reviews: Review[];
  loading?: boolean;
}>()

const showReplyDialog = ref(false)
const selectedReview = ref<Review | null>(null)

const reviewStore = useReviewStore()
const { addOrUpdateReview } = reviewStore

const openReplyDialog = (review: Review) => {
  selectedReview.value = review
  showReplyDialog.value = true
}

watchEffect((onCleanup) => {
  if (!props.locationId) {
    return
  }

  const channelName = `reviews.location_id.${props.locationId}`

  echo()
    .channel(channelName)
    .listen('.review.saved', (e: { review: Review }) => {
      addOrUpdateReview(e.review)
    })

  onCleanup(() => {
    echo().leave(channelName)
  })
})
</script>

<template>
  <VCard title="Reviews">
    <VCardText>
      <div
        v-if="loading"
        class="flex justify-center py-8"
      >
        <VProgressCircular
          indeterminate
          color="primary"
        />
      </div>
      <VEmptyState
        v-else-if="!reviews.length"
        headline="No reviews found"
        title="No reviews match the current filters."
        icon="mdi-comment-question-outline"
      />
      <div
        v-else
        class="space-y-4 overflow-y-auto grow"
      >
        <div
          v-for="review in reviews"
          :key="review.id"
          class="border-b pb-4 last:border-0"
        >
          <div class="mb-2 flex items-start justify-between">
            <div>
              <div class="font-bold">
                {{ review.author_name }}

                <v-chip
                  size="small"
                  color="primary"
                  density="compact"
                >
                  {{ review.source }}
                </v-chip>

                <v-chip
                  size="small"
                  color="success"
                  density="compact"
                >
                  {{ new Date(review.published_at).toLocaleDateString() }}
                </v-chip>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <VRating
                :model-value="review.rating"
                readonly
                density="compact"
                color="warning"
              />
              <SentimentChip :sentiment="review.sentiment" />
              <VBtn
                size="small"
                variant="tonal"
                @click="openReplyDialog(review)"
              >
                Reply
              </VBtn>
            </div>
          </div>
          <p class="text-gray-300">
            {{ review.text }}
          </p>
        </div>
      </div>
    </VCardText>
  </VCard>

  <ReviewReplyDialog
    v-if="showReplyDialog"
    v-model="showReplyDialog"
    :review="selectedReview"
  />
</template>
