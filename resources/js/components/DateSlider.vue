<script setup lang="ts">
import {
  parseISO,
  format,
  subDays,
  differenceInCalendarDays,
  isValid
} from 'date-fns'
import { ref, computed, watch } from 'vue'

const props = defineProps<{
  initialDate: string // YYYY-MM-DD
}>()

const emit = defineEmits<{
  (e: 'update:date', value: string): void
}>()

const DAYS_RANGE = 30

const today = new Date()

const parsedInitialDate = computed(() => {
  const date = parseISO(props.initialDate)

  return isValid(date) ? date : today
})

const initialOffset = differenceInCalendarDays(today, parsedInitialDate.value)

const value = ref(DAYS_RANGE - Math.min(Math.max(initialOffset, 0), DAYS_RANGE))

const currentDate = computed(() => {
  return subDays(today, DAYS_RANGE - value.value)
})

const marks = computed(() => {
  const result: Record<number, string> = {}

  for (let i = 0; i <= DAYS_RANGE; i += 3) {
    result[i] = format(subDays(today, DAYS_RANGE - i), 'dd MMM')
  }

  return result
})

watch(value, () => {
  emit('update:date', format(currentDate.value, 'yyyy-MM-dd'))
})
</script>

<template>
  <div class="time-slider glass glass-border">
    <v-slider
      v-model="value"
      :min="0"
      :max="DAYS_RANGE"
      :step="1"
      show-ticks="always"
      :ticks="marks"
      class="time-slider__slider"
      thumb-label="always"
    >
      <template #thumb-label>
        <span class="date">
          {{ format(currentDate, 'dd MMMM yyyy') }}
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
