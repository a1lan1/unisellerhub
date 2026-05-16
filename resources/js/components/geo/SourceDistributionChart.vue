<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseDonutChart from '@/components/charts/BaseDonutChart.vue'

const props = defineProps<{
  data: Record<string, number>;
}>()

const isOpen = ref(false)

const chartData = computed(() => ({
  labels: Object.keys(props.data),
  datasets: Object.values(props.data)
}))
</script>

<template>
  <BaseDonutChart
    :series="chartData.datasets"
    :labels="chartData.labels"
    :height="400"
    @click="isOpen = true"
  />

  <v-dialog
    v-if="isOpen"
    v-model="isOpen"
    max-width="600"
    max-height="800"
    scrollable
  >
    <v-card>
      <v-card-actions>
        <v-card-title>Reviews by Source</v-card-title>
        <v-spacer />
        <v-icon
          icon="mdi-close"
          @click="isOpen = false"
        />
      </v-card-actions>

      <BaseDonutChart
        :series="chartData.datasets"
        :labels="chartData.labels"
        :height="500"
        class="mb-5"
      />
    </v-card>
  </v-dialog>
</template>
