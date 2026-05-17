<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'
import SellerList from '@/components/geo/SellerList.vue'
import ReviewForm from '@/components/review/ReviewForm.vue'
import ReviewList from '@/components/review/ReviewList.vue'
import { dashboard } from '@/routes'
import { useAuthStore } from '@/stores/auth'
import { useReviewStore } from '@/stores/review'
import type { Seller } from '@/types'

defineProps<{
  seller: Seller;
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Seller', href: '#' }
    ]
  }
})

const authStore = useAuthStore()
const { user } = storeToRefs(authStore)

const reviewStore = useReviewStore()
const { reviews, loading } = storeToRefs(reviewStore)
const { fetchReviews } = reviewStore

onMounted(fetchReviews)
</script>

<template>
  <SellerList :active-seller="seller" />

  <VContainer>
    <!-- Seller Info -->
    <VCard
      border
      :loading
      class="mb-2"
    >
      <VCardText>
        <div class="flex flex-col items-center md:flex-row md:items-start">
          <VAvatar
            size="120"
            class="border"
          >
            <v-img
              :src="seller.avatar"
              :alt="seller.name"
            />
          </VAvatar>

          <div class="flex-1 text-center md:text-left">
            <div class="flex items-center justify-between">
              <v-card-title>{{ seller.name }}</v-card-title>
              <VChip
                color="success"
                variant="flat"
                prepend-icon="mdi-check-bold"
              >
                Verified Seller
              </VChip>
            </div>

            <v-card-subtitle class="text-gray-200">
              Member since {{ seller.created_at }}
            </v-card-subtitle>

            <v-card-text class="flex items-center gap-2">
              <VRating
                :model-value="seller.average_rating"
                readonly
                half-increments
                density="compact"
                color="warning"
              />
              <span class="font-weight-bold text-xl">
                {{ seller.average_rating }}
              </span>
              <span class="text-gray-500">({{ seller.reviews_count }} reviews)</span>
            </v-card-text>
          </div>
        </div>
      </VCardText>
    </VCard>

    <!-- Reviews -->
    <VRow>
      <VCol cols="12">
        <ReviewForm
          v-if="user?.id !== seller.id"
          :location-id="seller.id"
          :author-name="user.name"
        />
        <v-alert
          v-else-if="!user"
          type="info"
          variant="tonal"
          class="mb-6"
        >
          You must be logged in to write a review.
        </v-alert>

        <ReviewList
          :reviews
          :loading
        />
      </VCol>
    </VRow>
  </VContainer>
</template>
