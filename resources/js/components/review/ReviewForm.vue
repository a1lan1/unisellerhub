<script setup lang="ts">
import { storeToRefs } from 'pinia'
import { onMounted, ref, watch } from 'vue'
import { useZodValidation } from '@/composables/useZodValidation'
import { useGeoStore } from '@/stores/geo'
import { useReviewStore } from '@/stores/review'
import { reviewFormSchema  } from '@/types/schemas'
import type { ReviewForm , ReviewPayload  } from '@/types/schemas'

const props = defineProps<{
  locationId?: number | undefined;
  authorName: string;
}>()

const emit = defineEmits(['success'])

const reviewStore = useReviewStore()
const { storing } = storeToRefs(reviewStore)
const { storeReview } = reviewStore

const geoStore = useGeoStore()
const { locations } = storeToRefs(geoStore)
const { fetchLocations } = geoStore

const formData = ref<ReviewForm>({
  rating: 0,
  comment: '',
  selectedLocationId: props.locationId || 0
})

const { errors, validate } = useZodValidation(reviewFormSchema, formData)

watch(() => props.locationId, (newLocationId) => {
  formData.value.selectedLocationId = newLocationId || 0
})

const submit = async() => {
  if (!validate()) {
    return
  }

  const payload: ReviewPayload = {
    location_id: formData.value.selectedLocationId,
    rating: formData.value.rating,
    text: formData.value.comment,
    source: 'internal',
    author_name: props.authorName,
    external_id: crypto.randomUUID(),
    published_at: new Date().toISOString()
  }

  try {
    await storeReview(payload)
    // Reset form
    formData.value.rating = 0
    formData.value.comment = ''
    formData.value.selectedLocationId = props.locationId || 0
    emit('success')
  } catch (e: any) {
    console.error(e)
  }
}

onMounted(fetchLocations)
</script>

<template>
  <v-card border>
    <div class="flex justify-between px-4 pt-4">
      <span class="text-lg font-semibold">
        Write a Review
      </span>
      <v-select
        v-if="locations.length"
        v-model="formData.selectedLocationId"
        :items="locations"
        item-title="name"
        item-value="id"
        label="Location"
        clearable
        variant="solo"
        density="compact"
        class="max-w-xs"
        hide-details
        :error-messages="errors.selectedLocationId"
      />
    </div>

    <v-card-text>
      <v-form @submit.prevent="submit">
        <div class="mb-1">
          <div class="mb-1 text-sm text-gray-600">
            Your Rating
          </div>
          <v-rating
            v-model="formData.rating"
            hover
            color="warning"
            density="compact"
          />
          <div
            v-if="errors.rating"
            class="text-red-500 text-sm"
          >
            {{ errors.rating }}
          </div>
        </div>

        <VTextarea
          v-model="formData.comment"
          label="Your Review"
          variant="outlined"
          rows="3"
          auto-grow
          :error-messages="errors.comment"
        />

        <div class="flex justify-end">
          <VBtn
            type="submit"
            color="primary"
            variant="tonal"
            :loading="storing"
            prepend-icon="mdi-send"
          >
            Submit Review
          </VBtn>
        </div>
      </v-form>
    </v-card-text>
  </v-card>
</template>
