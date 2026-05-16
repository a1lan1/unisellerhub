<script setup lang="ts">
import { computed, ref } from 'vue'
import BaseLineChart from '@/components/charts/BaseLineChart.vue'

const props = defineProps<{
  data: { date: string; average_rating: number }[];
}>()

const isOpen = ref(false)

const chartData = computed(() => ({
  labels: props.data.map((item) => new Date(item.date).toLocaleDateString()),
  datasets: [{
    name: 'Average Rating',
    data: props.data.map((item) => parseInt(String(item.average_rating)))
  }]
}))
</script>

<template>
  <BaseLineChart
    :series="chartData.datasets"
    :categories="chartData.labels"
    :height="300"
    @click="isOpen = true"
  />

  <v-dialog
    v-if="isOpen"
    v-model="isOpen"
    max-width="1200"
    max-height="800"
    scrollable
  >
    <v-card>
      <v-card-actions>
        <v-card-title>Rating Dynamics</v-card-title>
        <v-spacer />
        <v-icon
          icon="mdi-close"
          @click="isOpen = false"
        />
      </v-card-actions>

      <BaseLineChart
        zoom-enabled
        :series="chartData.datasets"
        :categories="chartData.labels"
      />
    </v-card>
  </v-dialog>
</template>
