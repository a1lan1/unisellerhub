<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { storeToRefs } from 'pinia'
import { computed, onMounted, ref, watch } from 'vue'
import DashboardMetrics from '@/components/geo/DashboardMetrics.vue'
import ReviewFormDialog from '@/components/review/ReviewFormDialog.vue'
import ReviewList from '@/components/review/ReviewList.vue'
import { dashboard } from '@/routes'
import { useGeoStore } from '@/stores/geo'
import { useReviewStore } from '@/stores/review'

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Geo', href: '#' }
    ]
  }
})

const page = usePage()
const auth = computed(() => page.props.auth)

const geoStore = useGeoStore()
const { locations } = storeToRefs(geoStore)
const { fetchLocations } = geoStore

const reviewStore = useReviewStore()
const { reviews, metrics, loading: reviewsLoading } = storeToRefs(reviewStore)
const { fetchReviews, fetchMetrics } = reviewStore

const selectedLocationId = ref<number | undefined>(undefined)

const loadDashboardData = async() => {
  const filters = selectedLocationId.value
    ? { location_id: selectedLocationId.value }
    : {}

  await Promise.all([
    fetchReviews(filters),
    fetchMetrics(filters)
  ])
}

watch(selectedLocationId, loadDashboardData)

onMounted(async() => {
  await Promise.all([
    fetchLocations(),
    loadDashboardData()
  ])
})
</script>

<template>
  <div>
    <DashboardMetrics
      v-if="metrics"
      :metrics="metrics"
    />

    <v-spacer />

    <div
      v-if="locations.length"
      class="flex justify-between items-center my-2"
    >
      <VSelect
        v-model="selectedLocationId"
        :items="locations"
        item-title="name"
        item-value="id"
        label="Filter by Location"
        clearable
        variant="solo"
        density="compact"
        class="max-w-xs"
        hide-details
      />

      <ReviewFormDialog
        :location-id="selectedLocationId"
        :author-name="auth.user.name"
      />
    </div>

    <ReviewList
      :location-id="selectedLocationId"
      :reviews="reviews"
      :loading="reviewsLoading"
    />
  </div>
</template>
