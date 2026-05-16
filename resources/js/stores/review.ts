import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/plugins/axios'
import { snackbar } from '@/plugins/snackbar'
import type { Pagination } from '@/types'
import type { Review, ReviewFilters, ReviewMetrics } from '@/types/geo'
import type { ReviewPayload } from '@/types/schemas'

export const useReviewStore = defineStore('review', () => {
  const reviews = ref<Review[]>([])
  const metrics = ref<ReviewMetrics | null>(null)
  const pagination = ref<Pagination<Review>['meta'] | null>(null)
  const loading = ref(false)
  const storing = ref(false)

  const addOrUpdateReview = (review: Review) => {
    const index = reviews.value.findIndex((r: Review) => r.id === review.id)

    if (index !== -1) {
      reviews.value[index] = review
    } else {
      reviews.value.unshift(review)
    }
  }

  const fetchReviews = async(filters: ReviewFilters = {}) => {
    loading.value = true

    try {
      const { data } = await api.get<Pagination<Review>>('/api/geo/reviews', {
        params: filters
      })
      reviews.value = data.data
      pagination.value = data.meta
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to fetch reviews' })
    } finally {
      loading.value = false
    }
  }

  const storeReview = async(form: ReviewPayload) => {
    storing.value = true

    try {
      const { data } = await api.post<Review>('/api/geo/reviews', form)
      addOrUpdateReview(data)
      snackbar.success({ text: 'Review submitted successfully' })
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to submit review' })

      throw e
    } finally {
      storing.value = false
    }
  }

  const fetchMetrics = async(filters: Omit<ReviewFilters, 'page'> = {}) => {
    try {
      const { data } = await api.get<ReviewMetrics>('/api/geo/metrics', {
        params: filters
      })
      metrics.value = data
    } catch (e: any) {
      snackbar.error({ text: e.response?.data?.message || 'Failed to fetch metrics' })
    }
  }

  return {
    reviews,
    metrics,
    pagination,
    loading,
    storing,
    fetchReviews,
    storeReview,
    addOrUpdateReview,
    fetchMetrics
  }
})
