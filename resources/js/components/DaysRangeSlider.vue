<script setup lang="ts">
import { ref, computed, watch } from 'vue'

const props = defineProps<{
  initialDays: number
}>()

const emit = defineEmits<{
  (e: 'update:days', value: number): void
}>()

const DAYS_OPTIONS = [7, 14, 30, 60, 90, 180, 365]

const initialIndex = DAYS_OPTIONS.indexOf(props.initialDays)
const value = ref(initialIndex !== -1 ? initialIndex : DAYS_OPTIONS.indexOf(30))

const selectedDays = computed(() => DAYS_OPTIONS[value.value])

const marks = computed(() => {
  const result: Record<number, string> = {}
  DAYS_OPTIONS.forEach((days, index) => {
    result[index] = `${days}d`
  })

  return result
})

watch(value, () => {
  emit('update:days', selectedDays.value)
})
</script>

<template>
  <div class="time-slider glass glass-border">
    <v-slider
      v-model="value"
      :min="0"
      :max="DAYS_OPTIONS.length - 1"
      :step="1"
      show-ticks="always"
      :ticks="marks"
      class="time-slider__slider"
      thumb-label="always"
    >
      <template #thumb-label>
        <span class="date">
          {{ selectedDays }} days
        </span>
      </template>
    </v-slider>
  </div>
</template>

<style scoped>
.time-slider {
  padding: 14px 18px;
  border-radius: 16px;
}

.date {
  font-weight: 600;
  font-size: 16px;
  color: #38bdf8;
  text-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
}

.time-slider__slider {
  --v-theme-primary: #38bdf8;
}

/* Customizing the track */
:deep(.v-slider-track__background) {
  background: linear-gradient(to right, rgba(56, 189, 248, 0.2), #38bdf8);
  height: 6px;
  border-radius: 999px;
}

:deep(.v-slider-track__fill) {
  background: linear-gradient(to right, #38bdf8, #22d3ee);
  box-shadow: 0 0 10px #38bdf8;
  height: 6px;
}

/* Style the thumb-label to be our custom thumb */
:deep(.v-slider-thumb__label) {
  min-width: 135px !important;
  background-color: rgb(30 31 34 / 0.9);
}

:deep(.v-slider-thumb__label:hover) {
  transform: translateX(-50%) translateY(-50%) scale(1.2) !important;
}
</style>
