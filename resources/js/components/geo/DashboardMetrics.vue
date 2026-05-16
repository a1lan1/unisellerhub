<script setup lang="ts">
import RatingDynamicsChart from '@/components/geo/RatingDynamicsChart.vue'
import SourceDistributionChart from '@/components/geo/SourceDistributionChart.vue'
import type { ReviewMetrics } from '@/types/geo'

defineProps<{
  metrics: ReviewMetrics;
}>()
</script>

<template>
  <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
    <VCard v-if="metrics.average_rating > 0">
      <VCardTitle>Average Rating</VCardTitle>
      <VCardText class="text-center">
        <div class="mb-1 text-5xl font-bold">
          {{ metrics.average_rating }}
        </div>
        <VRating
          :model-value="metrics.average_rating"
          readonly
          size="x-large"
          color="warning"
          half-increments
          density="compact"
        />
        <div class="text-sm text-gray-500">
          Based on {{ metrics.total_reviews }} reviews
        </div>
      </VCardText>

      <v-divider />

      <VCardTitle>Sentiment</VCardTitle>
      <VCardText>
        <div class="mb-2 flex items-center justify-between">
          <span>Positive</span><span class="font-bold">
            {{ metrics.sentiment_distribution.positive }}
          </span>
        </div>
        <div class="mb-2 flex items-center justify-between">
          <span>Neutral</span><span class="font-bold">
            {{ metrics.sentiment_distribution.neutral }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span>Negative</span><span class="font-bold">
            {{ metrics.sentiment_distribution.negative }}
          </span>
        </div>
      </VCardText>
    </VCard>

    <VCard
      v-if="
        metrics.source_distribution &&
          Object.keys(metrics.source_distribution).length
      "
    >
      <VCardTitle>Reviews by Source</VCardTitle>
      <VCardText class="h-64">
        <SourceDistributionChart :data="metrics.source_distribution" />
      </VCardText>
    </VCard>

    <VCard
      v-if="metrics.rating_dynamics.length"
      class="lg:col-span-2"
    >
      <VCardTitle>Rating Dynamics</VCardTitle>
      <VCardText style="height: 270px">
        <RatingDynamicsChart :data="metrics.rating_dynamics" />
      </VCardText>
    </VCard>
  </div>
</template>
