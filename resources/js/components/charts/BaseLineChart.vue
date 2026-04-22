<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  series: { name: string; data: number[] }[];
  categories: string[];
  height?: number | string;
  colors?: string[];
  title?: string;
}

const props = withDefaults(defineProps<Props>(), {
  height: 350,
  colors: () => ['#9333ea', '#2563eb', '#dc2626', '#ca8a04', '#16a34a']
})

const chartOptions = computed(() => ({
  chart: {
    type: 'line',
    toolbar: { show: false },
    zoom: { enabled: false },
    fontFamily: 'inherit'
  },
  colors: props.colors,
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 3 },
  title: {
    text: props.title,
    align: 'left',
    style: { fontSize: '16px', fontWeight: 'bold' }
  },
  grid: {
    borderColor: '#f1f1f1',
    row: { opacity: 0.5 }
  },
  xaxis: {
    categories: props.categories,
    labels: { style: { colors: '#94a3b8' } }
  },
  yaxis: {
    labels: { style: { colors: '#94a3b8' } }
  },
  legend: { position: 'top', horizontalAlign: 'right' },
  tooltip: { theme: 'light' }
}))
</script>

<template>
  <div class="base-line-chart">
    <apexchart
      :height="height"
      type="line"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>
